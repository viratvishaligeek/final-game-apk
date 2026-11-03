<?php
namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SupportConversationAdminRead extends Model {
    use BelongsToSite;

    protected $fillable = ['conversation_id', 'admin_id', 'last_read_message_id', 'last_read_at'];
    protected function casts(): array { return ['last_read_at' => 'datetime']; }
    public function conversation(): BelongsTo { return $this->belongsTo(SupportConversation::class, 'conversation_id'); }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
}
