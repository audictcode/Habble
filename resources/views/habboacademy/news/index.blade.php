@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .news-list {
        display: grid;
        gap: 14px;
    }
    .news-slide-card {
        min-height: 220px;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        background-size: cover;
        background-position: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
    }
    .news-slide-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, .12), rgba(0, 0, 0, .72));
    }
    .news-slide-card.no-image {
        background: linear-gradient(180deg, #345c97, #1e3256);
    }
    .news-slide-link {
        display: block;
        min-height: 220px;
        text-decoration: none !important;
        position: relative;
        z-index: 1;
    }
    .news-slide-content {
        position: absolute;
        inset: 14px 16px;
        color: #fff;
    }
    .news-slide-head {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        max-width: min(78%, 520px);
        text-align: left;
    }
    .news-slide-content h3 {
        margin: 0 0 4px;
        font-size: 21px;
        font-weight: 700;
        color: #fff;
    }
    .news-slide-meta {
        margin: 0 0 8px;
        font-size: 12px;
        opacity: .95;
        color: #d9e8ff;
    }
    .news-author {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        position: absolute;
        right: 0;
        bottom: 0;
        padding: 0;
        border-radius: 0;
        background: transparent;
        width: fit-content;
    }
    .news-author-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        text-align: right;
        line-height: 1.2;
    }
    .news-author-name {
        font-size: 12px;
        color: #fff;
    }
    .news-author-date {
        font-size: 11px;
        color: #d9e8ff;
    }
    .news-author img {
        width: 100px;
        height: 100px;
        border-radius: 0;
        border: 0;
        background: transparent;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .news-slide-content p {
        margin: 0;
        font-size: 13px;
        color: #f3f7ff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, .35);
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">{{ $title }}</h2>

        <div class="news-list">
            @forelse($articles as $article)
                @php
                    $bannerUrl = \Illuminate\Support\Str::contains((string) $article->image_path, 'articles')
                        ? asset('storage/' . ltrim((string) $article->image_path, '/'))
                        : (string) $article->image_path;
                    $author = $article->user;
                    $authorName = $author?->habbo_name ?: $author?->username ?: ((string) ($article->reviewer ?: 'Equipo HK'));
                    $authorHotel = $author?->habbo_hotel ?: 'es';
                    $authorAvatar = 'https://www.habbo.' . $authorHotel . '/habbo-imaging/avatarimage?user=' . urlencode($authorName) . '&direction=2&head_direction=2&headonly=1&size=l';
                @endphp
                <article class="news-slide-card {{ filled($bannerUrl) ? '' : 'no-image' }}" @if(filled($bannerUrl)) style="background-image:url('{{ $bannerUrl }}')" @endif>
                    <a class="news-slide-link" href="{{ route('web.articles.show', ['id' => $article->id, 'slug' => $article->slug]) }}">
                        <div class="news-slide-content">
                            <div class="news-slide-head">
                                <h3>{{ $article->title }}</h3>
                                <p>{{ $article->description }}</p>
                            </div>
                            <p class="news-author">
                                <img src="{{ $authorAvatar }}" alt="{{ $authorName }} avatar">
                                <span class="news-author-meta">
                                    <span class="news-author-name">{{ $authorName }}</span>
                                    <span class="news-author-date">{{ optional($article->published_at ?: $article->created_at)->format('d/m/Y H:i') }}</span>
                                </span>
                            </p>
                        </div>
                    </a>
                </article>
            @empty
                <p class="mb-0 text-muted">Todavía no hay noticias publicadas desde HK.</p>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $articles->links('habboacademy.utils.custom_paginator') }}
        </div>
    </div>
</div>
@endsection
