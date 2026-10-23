<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\PushNotificationService;
use App\Services\ProjectStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupportChatController extends Controller
{
    public function show(Request $request)
    {
        $request->validate(['after_id' => ['sometimes', 'nullable', 'integer', 'min:0']]);
        $user = $request->user();
        $conversation = SupportConversation::firstOrCreate(['user_id' => $user->id]);
        $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $conversation->forceFill(['user_last_read_at' => now()])->save();

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'unread_count' => $conversation->messages()->where('sender_type', 'admin')->whereNull('read_at')->count(),
            'data' => $conversation->messages()
                ->when($request->query('after_id') !== null, fn ($query) => $query->where('id', '>', (int) $request->query('after_id')))
                ->orderBy('id')
                ->limit(200)
                ->get()
                ->map(fn ($message) => $this->formatMessage($message)),
        ]);
    }

    public function media(Request $request, SupportMessage $message)
    {
        abort_unless($message->conversation()->where('user_id', $request->user()->id)->exists(), 404);
        $diskName = app(ProjectStorage::class)->privateDiskName();
        $disk = Storage::disk($diskName);
        abort_unless($message->media_path && $disk->exists($message->media_path), 404);

        if (config('filesystems.disks.' . $diskName . '.driver') === 's3') {
            abort_unless(method_exists($disk, 'temporaryUrl'), 503, 'Private object storage is not configured for temporary URLs.');
            return redirect()->away($disk->temporaryUrl($message->media_path, now()->addMinutes(config('support-chat.presigned_url_minutes', 5))));
        }

        return response()->file($disk->path($message->media_path), ['Content-Type' => $message->media_mime ?: 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:15360', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,audio/webm,audio/ogg,audio/mp4,audio/mpeg'],
        ]);
        if (blank($validated['body'] ?? null) && !$request->hasFile('attachment')) {
            throw ValidationException::withMessages(['body' => 'Write a message or attach a supported file.']);
        }

        $conversation = SupportConversation::firstOrCreate(['user_id' => $request->user()->id]);
        $message = $this->persistMessage($request, $conversation, 'user', (int) $request->user()->id, $validated);
        $conversation->touch();

        app(PushNotificationService::class)->sendToAdmins(
            'New support message',
            $message->body ?: 'A user sent a support attachment.'
        );

        return response()->json(['success' => true, 'data' => $this->formatMessage($message)], 201);
    }

    private function persistMessage(Request $request, SupportConversation $conversation, string $senderType, int $senderId, array $validated): SupportMessage
    {
        $data = [
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'body' => trim((string) ($validated['body'] ?? '')) ?: null,
        ];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['media_path'] = app(ProjectStorage::class)->storePrivateFile($file, 'support-chat/' . $conversation->id);
            $data['media_mime'] = $file->getMimeType();
            $data['media_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }
        return $conversation->messages()->create($data);
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
                    : url('/api/v1/support-chat/media/' . $message->id))
                : null,
            'media_mime' => $message->media_mime,
            'media_name' => $message->media_name,
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
