<?php

// app/Models/GroupMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'message',
        'audio_path',
        'attachments',
        'reply_to_message_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(GroupMessage::class, 'reply_to_message_id');
    }

    public function repliedMessage()
    {
        return $this->belongsTo(GroupMessage::class, 'reply_to_message_id');
    }

}
