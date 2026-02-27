<?php

namespace App\Models;

use App\Models\Article\{
    ArticleComment,
    ArticleCategory
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'slug', 'image_path',
        'content', 'published_at', 'reviewed', 'reviewer', 'status', 'fixed'
    ];

    protected $casts = [
        'reviewed' => 'boolean',
        'status' => 'boolean',
        'fixed' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'reviewer', 'reviewed', 'status', 'user_id'
    ];

    protected const INDEX_PAGINATION_LIMIT = 8;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function comments()
    {
        return $this->hasMany(ArticleComment::class);
    }

    public static function search($filter = null)
    {
        return Article::query()
            ->where(function($query) use ($filter) {
                return $query->where('title', 'LIKE', "%{$filter}%")
                             ->orWhere('description', 'LIKE', "%{$filter}%");
            })
            ->latest()
            ->paginate(35);
    }

    public static function resultsFromApi(?string $category, ?string $search)
    {
        $query = Article::query()
            ->with(['user:id,username', 'category'])
            ->whereReviewed(true)
            ->whereStatus(true)
            ->orderByDesc('fixed');

        if (Schema::hasColumn('articles', 'published_at')) {
            $query->where(function ($builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })->orderByDesc('published_at');
        }

        $query->orderByDesc('id');

        if($category) {
            $query->where('category_id', $category);
        }

        if($search) {
            $query->where('title', $search);
        }

        $items = $query->paginate(self::INDEX_PAGINATION_LIMIT);

        if(count($items->items()) <= 0) {
            return $items;
        }

        $filteredItems = collect($items->items())->map(
            function($article) {
                $article->stringTime = dateToString($article->created_at);
                $article->route = route('web.articles.show', ['id' => $article->id, 'slug' => $article->slug]);
                $article->image_path = \Str::contains($article->image_path, 'articles') ? asset("storage/{$article->image_path}") : $article->image_path;

                return $article;
            }
        );

        return [
            "current_page" => $items->currentPage(),
            "last_page" => $items->lastPage(),
            "data" => $filteredItems
        ];
    }

    public static function getArticle($id, $slug)
    {
        $query = Article::query()
            ->with('user')
            ->where('id', $id)
            ->whereSlug($slug)
            ->whereReviewed(true)
            ->whereStatus(true);

        if (Schema::hasColumn('articles', 'published_at')) {
            $query->where(function ($builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        return $query->first();
    }

    public static function getRelatedArticles(Article $article)
    {
        $query = Article::query()
            ->with(['user', 'category'])
            ->where('category_id', $article->category_id)
            ->where('id', '<>', $article->id);

        if (Schema::hasColumn('articles', 'published_at')) {
            $query->where(function ($builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })->orderByDesc('published_at');
        }

        return $query->orderByDesc('id')
            ->limit(4)
            ->get();
    }
}
