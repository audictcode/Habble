<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebGame extends Model
{
    use HasFactory;

    public const CATEGORY_OPTIONS = [
        'arcade' => 'Arcade',
        'quiz' => 'Quiz',
        'puzzle' => 'Puzzle',
        'rpg' => 'RPG',
        'trivia' => 'Trivia',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail_url',
        'game_url',
        'category',
        'game_type',
        'intro_text',
        'info_text',
        'option_title',
        'option_description',
        'option_reward_text',
        'quiz_questions',
        'published_at',
        'participation_ends_at',
        'xp_reward',
        'astros_reward',
        'stelas_reward',
        'lunaris_reward',
        'cosmos_reward',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'quiz_questions' => 'array',
        'published_at' => 'datetime',
        'participation_ends_at' => 'datetime',
    ];
}
