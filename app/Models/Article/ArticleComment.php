<?php

namespace App\Models\Article;

use App\Models\{
    User,
    Article
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleComment extends Model
{
    use HasFactory;

    protected $table = 'articles_comments';
    
    protected $fillable = [
        'user_id',
        'article_id',
        'content',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
