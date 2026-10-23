<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
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

    public function conversations()
    {
        return response()->json([
            'data' => SupportConversation::query()
                ->with(['user:id,name,phone'])
                ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_type', 'user')->whereNull('read_at')])
                ->withMax('messages', 'created_at')
                ->orderByDesc('updated_at')
                ->limit(500)
                ->get()
                ->map(fn ($conversation) => [
                    'id' => $conversation->id,
                    'user' => $conversation->user?->only(['id', 'name', 'phone']),
                    'unread_count' => $conversation->unread_count,
                    'last_message_at' => $conversation->messages_max_created_at,
                ]),
        ]);
    }

    public function messages(Request $request, SupportConversation $conversation)
    {
        $request->validate(['after_id' => ['sometimes', 'nullable', 'integer', 'min:0']]);
        $conversation->messages()->where('sender_type', 'user')->whereNull('read_at')->update(['read_at' => now()]);
        $conversation->forceFill(['admin_last_read_at' => now(), 'assigned_admin_id' => auth('admin')->id()])->save();

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
        $message = $conversation->messages()->create($data);
        $conversation->forceFill(['assigned_admin_id' => auth('admin')->id()])->touch();

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
