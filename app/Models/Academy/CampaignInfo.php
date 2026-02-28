<?php

namespace App\Models\Academy;

use App\Models\User;
use App\Models\Academy\CampaignInfoComment;
use App\Models\Article\ArticleCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'target_page',
        'category_id',
        'month_label',
        'excerpt',
        'banner_image_path',
        'body_html',
        'content_html',
        'info_cells',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'primary_button_color',
        'secondary_button_color',
        'use_custom_html',
        'created_by_user_id',
        'author_name',
        'author_avatar_url',
        'active',
        'published_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'use_custom_html' => 'boolean',
        'info_cells' => 'array',
        'published_at' => 'datetime',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function comments()
    {
        return $this->hasMany(CampaignInfoComment::class);
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
}
