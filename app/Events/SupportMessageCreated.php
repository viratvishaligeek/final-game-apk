<?php

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageCreated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $messageId) {}

    public function broadcastOn(): array
    {
        $message = SupportMessage::query()
            ->with('conversation:id,user_id')
            ->find($this->messageId);

        if (!$message || !$message->conversation) {
            return [];
        }

        return [
            new PrivateChannel('admin.support-chat'),
            new PrivateChannel('users.' . $message->conversation->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'support.message.created';
    }

    public function broadcastWith(): array
    {
        $message = SupportMessage::query()
            ->with('conversation.user:id,name,phone')
            ->find($this->messageId);

        if (!$message) {
            return [];
        }

        return [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender' => $message->sender_type,
            'body' => $message->body,
            'user_id' => $message->conversation?->user_id,
            'user_name' => $message->conversation?->user?->name,
            'phone' => $message->conversation?->user?->phone,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}
