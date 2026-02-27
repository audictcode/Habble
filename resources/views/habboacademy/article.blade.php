@extends('layouts.app')

@section('title', $article->title)

@push('styles')
<style>
    .article-hero {
        min-height: 260px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        background-size: cover;
        background-position: center;
        margin-bottom: 14px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
    }
    .article-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, .12), rgba(0, 0, 0, .72));
    }
    .article-hero.no-image {
        background: linear-gradient(180deg, #345c97, #1e3256);
    }
    .article-hero-content {
        position: absolute;
        left: 16px;
        right: 16px;
        top: 14px;
        bottom: 14px;
        z-index: 1;
        display: flex;
        flex-direction: column;
        color: #fff;
    }
    .article-hero-content h1 {
        margin: 0 0 6px;
        color: #fff;
    }
    .article-hero-content p {
        margin: 0;
        color: #e7f0ff;
    }
    .article-author-card {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid #d4e0f7;
        background: #f6f9ff;
        width: fit-content;
    }
    .article-author-card img {
        width: 80px;
        height: 80px;
        border-radius: 0;
        border: 0;
        background: transparent;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .article-author-card-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        text-align: left;
        line-height: 1.2;
    }
    .article-author-card-name {
        color: #1d3560;
        font-size: 13px;
        font-weight: 700;
    }
    .article-author-card-date {
        font-size: 12px;
        color: #5f759f;
    }
    .article-comments {
        margin-top: 2px;
    }
    .article-comment-form {
        margin-bottom: 12px;
    }
    .article-comment-form-row {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 10px;
        align-items: start;
    }
    .article-comment-form-avatar {
        width: 100px;
        height: 100px;
        object-fit: contain;
        border-radius: 0;
        border: 0;
        background: transparent;
        image-rendering: pixelated;
    }
    .article-comment-form textarea {
        width: 100%;
        min-height: 90px;
        border: 1px solid #c8d8f8;
        border-radius: 10px;
        padding: 10px;
        resize: vertical;
    }
    .article-comment-form button {
        margin-top: 8px;
        border: 0;
        border-radius: 8px;
        background: #2f6fab;
        color: #fff;
        font-weight: 700;
        padding: 8px 12px;
    }
    .article-comments-list {
        display: grid;
        gap: 12px;
        margin-top: 12px;
    }
    .article-comment-item {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 8px;
        align-items: start;
    }
    .article-comment-head {
        display: block;
    }
    .article-comment-head img {
        width: 100px;
        height: 100px;
        object-fit: contain;
        border-radius: 0;
        border: 0;
        background: transparent;
        image-rendering: pixelated;
    }
    .article-comment-bubble {
        position: relative;
        background: #f6f9ff;
        border: 1px solid #d4e0f7;
        border-radius: 12px;
        padding: 10px 12px;
        color: #223760;
    }
    .article-comment-bubble::before {
        content: '';
        position: absolute;
        top: 18px;
        left: -7px;
        width: 12px;
        height: 12px;
        background: #f6f9ff;
        border-left: 1px solid #d4e0f7;
        border-top: 1px solid #d4e0f7;
        transform: rotate(-45deg);
    }
    .article-comment-user {
        margin: 0 0 4px;
        color: #1d3560;
        font-size: 14px;
        font-weight: 700;
    }
    .article-comment-content {
        margin: 0;
        text-align: left;
        white-space: pre-line;
    }
    .article-comment-meta {
        margin-top: 8px;
        font-size: 12px;
        color: #5f759f;
        text-align: right;
    }
    .article-comment-actions {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    .article-comment-actions button {
        border: 1px solid #c8d8f8;
        background: #fff;
        color: #2f6fab;
        border-radius: 8px;
        padding: 5px 9px;
        font-size: 12px;
        font-weight: 700;
    }
    .article-comment-actions form {
        margin: 0;
    }
    .article-comment-edit {
        margin-top: 8px;
        display: grid;
        gap: 6px;
    }
    .article-comment-edit textarea {
        width: 100%;
        min-height: 80px;
        border: 1px solid #c8d8f8;
        border-radius: 10px;
        padding: 8px 10px;
        resize: vertical;
    }
    .article-comment-edit button {
        justify-self: end;
        border: 0;
        border-radius: 8px;
        background: #2f6fab;
        color: #fff;
        font-weight: 700;
        padding: 6px 10px;
        font-size: 12px;
    }
    .article-content {
        border-top: 1px solid #d6e2fb;
        padding-top: 12px;
        margin-top: 8px;
    }
    .article-content :is(h1, h2, h3, h4, h5, h6, p, ul, ol) {
        max-width: 100%;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
    }
    .article-content iframe {
        max-width: 100%;
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4 mb-4">
        @php
            $bannerUrl = \Illuminate\Support\Str::contains((string) $article->image_path, 'articles')
                ? asset('storage/' . ltrim((string) $article->image_path, '/'))
                : (string) $article->image_path;
            $author = $article->user;
            $authorName = $author?->habbo_name ?: $author?->username ?: ((string) ($article->reviewer ?: 'Equipo HK'));
            $authorHotel = $author?->habbo_hotel ?: 'es';
            $authorAvatar = 'https://www.habbo.' . $authorHotel . '/habbo-imaging/avatarimage?user=' . urlencode($authorName) . '&direction=2&head_direction=2&headonly=1&size=l';
        @endphp
        <article class="article-hero {{ filled($bannerUrl) ? '' : 'no-image' }}" @if(filled($bannerUrl)) style="background-image:url('{{ $bannerUrl }}')" @endif>
            <div class="article-hero-content">
                <h1>{{ $article->title }}</h1>
                <p>{{ $article->description }}</p>
            </div>
        </article>
        <div class="article-content">{!! $article->content !!}</div>
        <div class="article-author-card">
            <img src="{{ $authorAvatar }}" alt="{{ $authorName }} avatar">
            <span class="article-author-card-meta">
                <span class="article-author-card-name">{{ $authorName }}</span>
                <span class="article-author-card-date">{{ optional($article->published_at ?: $article->created_at)->format('d/m/Y H:i') }}</span>
            </span>
        </div>
    </div>

    @if(isset($articles) && $articles->count())
        <div class="default-box full p-4 mb-4">
            <h3 class="mb-3">Artículos relacionados</h3>
            <ul class="mb-0">
                @foreach($articles as $related)
                    <li>
                        <a href="{{ route('web.articles.show', ['id' => $related->id, 'slug' => $related->slug]) }}">
                            {{ $related->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="default-box full p-4 article-comments">
        <h3 class="mb-3">Comentarios</h3>

        @auth
            @php
                $viewer = auth()->user();
                $viewerName = $viewer?->habbo_name ?: $viewer?->username ?: 'Usuario';
                $viewerHotel = $viewer?->habbo_hotel ?: 'es';
                $viewerAvatar = 'https://www.habbo.' . $viewerHotel . '/habbo-imaging/avatarimage?user=' . urlencode($viewerName) . '&direction=2&head_direction=2&headonly=1&size=l';
            @endphp
            <form class="article-comment-form" method="POST" action="{{ route('web.articles.comments.store', ['id' => $article->id, 'slug' => $article->slug]) }}">
                @csrf
                <div class="article-comment-form-row">
                    <img class="article-comment-form-avatar" src="{{ $viewerAvatar }}" alt="{{ $viewerName }} perfil">
                    <textarea name="content" placeholder="Escribe tu comentario..." required>{{ old('content') }}</textarea>
                </div>
                @error('content')
                    <p class="text-danger mt-1 mb-0">{{ $message }}</p>
                @enderror
                <button type="submit">Publicar</button>
            </form>
        @else
            <p class="mb-0 text-muted">Debes iniciar sesión para comentar esta noticia.</p>
        @endauth

        @if($comments->count())
            <div class="article-comments-list">
                @foreach($comments as $comment)
                    @php
                        $user = $comment->user;
                        $userName = $user?->habbo_name ?: $user?->username ?: 'Usuario';
                        $hotel = $user?->habbo_hotel ?: 'es';
                        $profileImage = 'https://www.habbo.' . $hotel . '/habbo-imaging/avatarimage?user=' . urlencode($userName) . '&direction=2&head_direction=2&headonly=1&size=l';
                    @endphp
                    <article class="article-comment-item">
                        <div class="article-comment-head">
                            <img src="{{ $profileImage }}" alt="{{ $userName }} perfil">
                        </div>
                        <div class="article-comment-bubble">
                            <p class="article-comment-user">{{ $userName }}</p>
                            <p class="article-comment-content">{{ $comment->content }}</p>
                            <p class="article-comment-meta">{{ optional($comment->created_at)->format('d/m/Y H:i') }}</p>
                            @if(auth()->check() && (int) auth()->id() === (int) $comment->user_id)
                                <form class="article-comment-edit" method="POST" action="{{ route('web.articles.comments.update', ['id' => $article->id, 'slug' => $article->slug, 'comment' => $comment->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="content" required>{{ $comment->content }}</textarea>
                                    <button type="submit">Guardar cambios</button>
                                </form>
                                <div class="article-comment-actions">
                                    <form method="POST" action="{{ route('web.articles.comments.destroy', ['id' => $article->id, 'slug' => $article->slug, 'comment' => $comment->id]) }}" onsubmit="return confirm('¿Eliminar este comentario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="mb-0 text-muted">Todavía no hay comentarios.</p>
        @endif
        <div class="mt-3">
            {{ $comments->links() }}
        </div>
    </div>
</div>
@endsection
