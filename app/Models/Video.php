<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'youtube_id',
        'title',
        'description',
        'embed_url',
        'views',
        'likes',
        'user_id',
    ];
}
