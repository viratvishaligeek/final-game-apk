<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\SupportConversationAdminRead;
use App\Models\User;
use App\Events\SupportMessageCreated;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Services\ProjectStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Validation\ValidationException;

class SupportChatController extends Controller
{
    public function index(\App\Services\AppSettingsService $settings)
    {
        $chatSoundEnabled = (string) $settings->value('notification_sound_chat', 'default') !== 'mute';
        return view('backend.support-chat.index', compact('chatSoundEnabled'));
    }

    public function searchUsers(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));

        if ($term === '') {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->where(function ($query) use ($term) {
                $query->where('phone', 'like', '%' . $term . '%')
                    ->orWhere('name', 'like', '%' . $term . '%');
                if (ctype_digit($term)) {
                    $query->orWhereKey((int) $term);
                }
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'phone', 'status']);

        $conversations = SupportConversation::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->get(['id', 'user_id'])
            ->keyBy('user_id');

        return response()->json([
            'data' => $users->map(function (User $user) use ($conversations) {
                $conversation = $conversations->get($user->id);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'conversation_id' => $conversation?->id,
                    'has_conversation' => (bool) $conversation,
                ];
            })->values(),
        ]);
    }

    public function startConversation(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
        ]);
        $adminId = (int) auth('admin')->id();

        $conversation = DB::transaction(function () use ($validated, $adminId) {
            // Lock the user row so simultaneous admin starts serialize before
            // the unique support_conversations.user_id constraint is checked.
            $user = User::query()->lockForUpdate()->findOrFail($validated['user_id']);

            return SupportConversation::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['assigned_admin_id' => $adminId]
            );
        });

        $conversation->load('user:id,name,phone');

        return response()->json([
            'data' => [
                'id' => $conversation->id,
                'user' => $conversation->user?->only(['id', 'name', 'phone']),
                'created' => $conversation->wasRecentlyCreated,
            ],
        ], $conversation->wasRecentlyCreated ? 201 : 200);
    }

    public function conversations(Request $request)
    {
        $adminId = (int) auth('admin')->id();
        $validated = $request->validate([
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);
        $phone = trim((string) ($validated['phone'] ?? ''));

        return response()->json([
            'data' => SupportConversation::query()
                ->with(['user:id,name,phone', 'latestMessage:id,conversation_id,body,sender_type,created_at'])
                ->when($phone !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('phone', 'like', '%' . $phone . '%')))
                ->withCount(['messages as unread_count' => function ($query) use ($adminId) {
                    $query->where('sender_type', 'user')
                        ->whereRaw(
                            'id > COALESCE((SELECT last_read_message_id FROM support_conversation_admin_reads WHERE support_conversation_admin_reads.conversation_id = support_messages.conversation_id AND support_conversation_admin_reads.admin_id = ?), 0)',
                            [$adminId]
                        );
                }])
                ->withMax('messages', 'created_at')
                ->orderByDesc('updated_at')
                ->limit(500)
                ->get()
                ->map(fn ($conversation) => [
                    'id' => $conversation->id,
                    'user' => $conversation->user?->only(['id', 'name', 'phone']),
                    'unread_count' => $conversation->unread_count,
                    'last_message_at' => $conversation->messages_max_created_at,
                    'last_message_preview' => $conversation->latestMessage?->body
                        ?: ($conversation->latestMessage ? 'Attachment' : ''),
                ]),
        ]);
    }

    public function messages(Request $request, SupportConversation $conversation)
    {
        $request->validate(['after_id' => ['sometimes', 'nullable', 'integer', 'min:0']]);
        $adminId = (int) auth('admin')->id();
        $conversation->messages()->where('sender_type', 'user')->whereNull('read_at')->update(['read_at' => now()]);
        $lastReadMessageId = (int) ($conversation->messages()->max('id') ?? 0);
        SupportConversationAdminRead::query()->updateOrCreate(
            ['conversation_id' => $conversation->id, 'admin_id' => $adminId],
            ['last_read_message_id' => $lastReadMessageId, 'last_read_at' => now()]
        );
        $conversation->forceFill(['admin_last_read_at' => now(), 'assigned_admin_id' => $adminId])->save();

        return response()->json([
            'data' => $conversation->messages()
                ->when($request->query('after_id') !== null, fn ($query) => $query->where('id', '>', (int) $request->query('after_id')))
                ->orderBy('id')
                ->limit(200)
                ->get()
                ->map(fn ($message) => $this->formatMessage($message)),
        ]);
    }

    public function media(SupportMessage $message)
    {
        $diskName = app(ProjectStorage::class)->privateDiskName();
        $disk = Storage::disk($diskName);
        abort_unless($message->media_path && $disk->exists($message->media_path), 404);

        if (config('filesystems.disks.' . $diskName . '.driver') === 's3') {
            abort_unless(method_exists($disk, 'temporaryUrl'), 503, 'Private object storage is not configured for temporary URLs.');
            return redirect()->away($disk->temporaryUrl($message->media_path, now()->addMinutes(config('support-chat.presigned_url_minutes', 5))));
        }

        return response()->file($disk->path($message->media_path), ['Content-Type' => $message->media_mime ?: 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function send(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:15360', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,audio/webm,audio/ogg,audio/mp4,audio/mpeg'],
        ]);
        if (blank($validated['body'] ?? null) && !$request->hasFile('attachment')) {
            throw ValidationException::withMessages(['body' => 'Write a message or attach a supported file.']);
        }

        $data = [
            'sender_type' => 'admin',
            'sender_id' => (int) auth('admin')->id(),
            'body' => trim((string) ($validated['body'] ?? '')) ?: null,
        ];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['media_path'] = app(ProjectStorage::class)->storePrivateFile($file, 'support-chat/' . $conversation->id);
            $data['media_mime'] = $file->getMimeType();
            $data['media_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }
        $message = DB::transaction(function () use ($conversation, $data) {
            $message = $conversation->messages()->create($data);
            $conversation->forceFill(['assigned_admin_id' => auth('admin')->id()])->touch();
            return $message;
        });
        SupportMessageCreated::dispatch($message->id);

        // Persist the reply in the user's notification history and attempt push delivery.
        if ($conversation->user) {
            app(NotificationService::class)->create(
                $conversation->user,
                'Support replied',
                $message->body ?: 'Support sent you an attachment.'
            );
        }

        return response()->json(['data' => $this->formatMessage($message)], 201);
    }

    private function formatMessage(SupportMessage $message): array
    {
        $diskName = app(ProjectStorage::class)->privateDiskName();
        $isRemotePrivateDisk = config('filesystems.disks.' . $diskName . '.driver') === 's3';

        return [
            'id' => $message->id,
            'sender' => $message->sender_type,
            'body' => $message->body,
            'media_url' => $message->media_path
                ? ($isRemotePrivateDisk
                    ? Storage::disk($diskName)->temporaryUrl($message->media_path, now()->addMinutes(config('support-chat.presigned_url_minutes', 5)))
                    : url('/admin/support-chat/media/' . $message->id))
                : null,
            'media_mime' => $message->media_mime,
            'media_name' => $message->media_name,
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
