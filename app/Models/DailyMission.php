<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'intro_text',
        'published_at',
        'xp_reward',
        'astros_reward',
        'stelas_reward',
        'lunaris_reward',
        'cosmos_reward',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'published_at' => 'datetime',
    ];
}
