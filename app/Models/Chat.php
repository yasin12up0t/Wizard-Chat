<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'message',
        'audio_path',
        'attachments',
        'seen',
        'reply_to_message_id',

    ];

    // Define relationships
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // Method to mark a message as seen
    public function markAsSeen()
    {
        $this->update(['seen' => true]);
    }
}
