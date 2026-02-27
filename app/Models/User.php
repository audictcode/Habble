<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User\UserBan;
use App\Models\User\UserLog;
use App\Models\User\UserWarning;
use App\Models\WebGame;
use App\Models\DailyMission;
use App\Models\Topic\TopicComment;
use App\Models\Traits\FilamentTrait;
use App\Models\User\UserNotification;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasFactory, Notifiable, FilamentTrait;

    public const RANK_LABELS = [
        1 => 'Usuario',
        2 => 'DJ',
        3 => 'Moderador',
        4 => 'Supervisor',
        5 => 'Diseñador',
        6 => 'Admin',
        7 => 'Founder',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'habbo_name',
        'habbo_hotel',
        'habbo_verification_code',
        'habbo_verified_at',
        'ip_register',
        'ip_last_login',
        'last_login',
        'password',
        'name',
        'birth_date',
        'astros',
        'stelas',
        'lunaris',
        'cosmos',
        'web_experience',
        'profile_image_path',
        'disabled',
        'rank',
        'forum_signature'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'habbo_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'birth_date' => 'date',
        'disabled' => 'boolean',
        'rank' => 'integer',
        'web_experience' => 'integer',
    ];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function logs()
    {
        return $this->hasMany(UserLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class, 'to_user_id');
    }

    public function bans()
    {
        return $this->hasMany(UserBan::class);
    }

    public function warnings()
    {
        return $this->hasMany(UserWarning::class);
    }

    public function topicComments()
    {
        return $this->hasMany(TopicComment::class);
    }

    public function getCommentsCount()
    {
        return $this->topicComments()->count();
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function rewardedWebGames()
    {
        return $this->belongsToMany(WebGame::class, 'user_web_game_rewards')
            ->withPivot(['rewarded_at'])
            ->withTimestamps();
    }

    public function rewardedDailyMissions()
    {
        return $this->belongsToMany(DailyMission::class, 'user_daily_mission_rewards')
            ->withPivot(['mission_date', 'rewarded_at'])
            ->withTimestamps();
    }

    public function checkLastTopicTime()
    {
        return $this->topics()
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->latest()
            ->limit(1)
            ->exists();
    }

    public function isAdmin()
    {
        return !$this->disabled && ((int) $this->rank >= 7);
    }

    public static function rankOptions(int $min = 1, int $max = 7): array
    {
        return collect(self::RANK_LABELS)
            ->filter(fn ($label, $rank) => $rank >= $min && $rank <= $max)
            ->mapWithKeys(fn ($label, $rank) => [(string) $rank => $label . ' (Rango ' . $rank . ')'])
            ->all();
    }

    public function getRankLabelAttribute(): string
    {
        $rank = (int) $this->rank;

        return self::RANK_LABELS[$rank] ?? ('Rango ' . $rank);
    }

    public function getFilamentName(): string
    {
        return Str::length($this->name, 'utf-8') >= 1 ? $this->name : $this->username;
    }

    public function getNotificationsByTopic(Topic $topic)
    {
        return $this->notifications()
            ->where('user_saw', false)
            ->where('slug', route('web.topics.show', ['id' => $topic->id, 'slug' => $topic->slug]))
            ->get();
    }
}
