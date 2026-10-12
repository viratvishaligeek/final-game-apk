<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\ValidationException;

class SupportChatController extends Controller
{
    public function index()
    {
        return view('backend.support-chat.index');
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
        $conversation->messages()->where('sender_type', 'user')->whereNull('read_at')->update(['read_at' => now()]);
        $conversation->forceFill(['admin_last_read_at' => now(), 'assigned_admin_id' => auth('admin')->id()])->save();

        return response()->json([
            'data' => $conversation->messages()->orderBy('id')->limit(200)->get()->map(fn ($message) => $this->formatMessage($message)),
        ]);
    }

    public function media(SupportMessage $message): StreamedResponse
    {
        abort_unless($message->media_path && Storage::disk('local')->exists($message->media_path), 404);

        return Storage::disk('local')->download($message->media_path, $message->media_name ?: 'attachment');
    }

    public function send(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,audio/webm,audio/ogg,audio/mp4,audio/mpeg'],
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
            $data['media_path'] = $file->store('support-chat/' . $conversation->id, 'local');
            $data['media_mime'] = $file->getMimeType();
            $data['media_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }
        $message = $conversation->messages()->create($data);
        $conversation->forceFill(['assigned_admin_id' => auth('admin')->id()])->touch();

        return response()->json(['data' => $this->formatMessage($message)], 201);
    }

    private function formatMessage(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender' => $message->sender_type,
            'body' => $message->body,
            'media_url' => $message->media_path ? url('/admin/support-chat/media/' . $message->id) : null,
            'media_mime' => $message->media_mime,
            'media_name' => $message->media_name,
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
