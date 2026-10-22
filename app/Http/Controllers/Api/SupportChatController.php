<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupportChatController extends Controller
{
    public function show(Request $request)
    {
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
            'data' => $conversation->messages()->orderBy('id')->limit(200)->get()->map(fn ($message) => $this->formatMessage($message)),
        ]);
    }

    public function media(Request $request, SupportMessage $message)
    {
        abort_unless($message->conversation()->where('user_id', $request->user()->id)->exists(), 404);
        $disk = Storage::disk(config('support-chat.disk', 'local'));
        abort_unless($message->media_path && $disk->exists($message->media_path), 404);

        if (config('support-chat.disk') === 's3') {
            abort_unless(method_exists($disk, 'temporaryUrl'), 503, 'Private object storage is not configured for temporary URLs.');
            return redirect()->away($disk->temporaryUrl($message->media_path, now()->addMinutes(config('support-chat.presigned_url_minutes', 5))));
        }

        return response()->file($disk->path($message->media_path), ['Content-Type' => $message->media_mime ?: 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,audio/webm,audio/ogg,audio/mp4,audio/mpeg'],
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
            $data['media_path'] = $file->store('support-chat/' . $conversation->id, config('support-chat.disk', 'local'));
            $data['media_mime'] = $file->getMimeType();
            $data['media_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }
        return $conversation->messages()->create($data);
    }

    private function formatMessage(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender' => $message->sender_type,
            'body' => $message->body,
            'media_url' => $message->media_path
                ? (config('support-chat.disk') === 's3'
                    ? Storage::disk(config('support-chat.disk', 'local'))->temporaryUrl($message->media_path, now()->addMinutes(config('support-chat.presigned_url_minutes', 5)))
                    : url('/api/v1/support-chat/media/' . $message->id))
                : null,
            'media_mime' => $message->media_mime,
            'media_name' => $message->media_name,
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
