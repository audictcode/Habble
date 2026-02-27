<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Article\ArticleComment;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show($id, $slug)
    {
        if(! $article = Article::getArticle($id, $slug)) {
            return redirect()->route('web.academy.index');
        }

        $comments = $article->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        $relatedArticles = Article::getRelatedArticles($article);

        return view('habboacademy.article', [
            'article' => $article,
            'articles' => $relatedArticles,
            'comments' => $comments
        ]);
    }

    public function storeComment($id, $slug, Request $request)
    {
        if (! $article = Article::getArticle($id, $slug)) {
            return redirect()->route('web.academy.index');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $article->comments()->create([
            'user_id' => (int) auth()->id(),
            'content' => trim($data['content']),
        ]);

        return back()->with('success', 'Comentario publicado correctamente.');
    }

    public function updateComment($id, $slug, ArticleComment $comment, Request $request)
    {
        if (! $article = Article::getArticle($id, $slug)) {
            return redirect()->route('web.academy.index');
        }

        if ((int) $comment->article_id !== (int) $article->id) {
            abort(404);
        }

        if ((int) $comment->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $comment->update([
            'content' => trim($data['content']),
        ]);

        return back()->with('success', 'Comentario actualizado correctamente.');
    }

    public function destroyComment($id, $slug, ArticleComment $comment)
    {
        if (! $article = Article::getArticle($id, $slug)) {
            return redirect()->route('web.academy.index');
        }

        if ((int) $comment->article_id !== (int) $article->id) {
            abort(404);
        }

        if ((int) $comment->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comentario eliminado correctamente.');
    }
}
