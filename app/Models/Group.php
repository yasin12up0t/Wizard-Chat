<?php


// app/Models/Group.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'group_pic',
        'group_cover',
        'conditions',
        'chat_open',
        'open',
    ];


    // Relationship with User for the creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with User for members
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Relationship with GroupMessage
    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }
}
