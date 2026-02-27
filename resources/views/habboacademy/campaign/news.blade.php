@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .campaign-article {
        border: 1px solid #d8e4ff;
        border-radius: 12px;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        box-shadow: 0 8px 20px rgba(34, 73, 160, 0.12);
        padding: 16px;
    }
    .campaign-banner-wrap {
        width: 100%;
        display: block;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        margin-bottom: 14px;
    }
    .campaign-banner {
        width: 100%;
        max-height: 320px;
        object-fit: cover;
        object-position: center;
        border-radius: 12px;
        border: 2px solid #c3d7ff;
        background: #eef4ff;
    }
    .campaign-banner-overlay {
        position: absolute;
        inset: 14px;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, .35);
    }
    .campaign-banner-top {
        position: absolute;
        left: 0;
        top: 0;
        max-width: min(78%, 540px);
        text-align: left;
    }
    .campaign-banner-overlay .campaign-title {
        margin: 0 0 6px;
        color: #fff;
        font-size: 1.45rem;
    }
    .campaign-banner-excerpt {
        margin: 0;
        font-size: 13px;
        color: #f3f7ff;
    }
    .campaign-banner-bottom {
        position: absolute;
        right: 0;
        bottom: 0;
    }
    .campaign-banner-author {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 0;
        background: transparent;
    }
    .campaign-banner-author img {
        width: 100px;
        height: 100px;
        border-radius: 0;
        border: 0;
        background: transparent;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .campaign-banner-author-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.2;
    }
    .campaign-banner-author-name {
        font-size: 12px;
        color: #fff;
    }
    .campaign-banner-author-date {
        font-size: 11px;
        color: #d9e8ff;
    }
    .campaign-title {
        margin: 0 0 10px 0;
        font-size: 1.6rem;
        color: #12306b;
    }
    .campaign-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }
    .campaign-meta-badge {
        background: #e8f1ff;
        border: 1px solid #c8dcff;
        color: #1b3972;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .85rem;
    }
    .campaign-meta-badge.top {
        background: #ffe8a6;
        border-color: #f3ce62;
        color: #6a4f07;
    }
    .campaign-author-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f3f8ff;
        border: 1px solid #d1e1ff;
        border-radius: 10px;
        padding: 8px 10px;
        margin-bottom: 12px;
    }
    .campaign-author-cell img {
        width: 100px;
        height: 100px;
        border-radius: 0;
        border: 0;
        background: transparent;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .campaign-author-cell p {
        margin: 0;
        font-size: .82rem;
        color: #4a5e86;
    }
    .campaign-post-meta {
        margin-top: 14px;
    }
    .campaign-excerpt {
        background: #f8fbff;
        border-left: 4px solid #5a8dff;
        padding: 10px;
        border-radius: 8px;
        color: #2a3c62;
    }
    .campaign-cells-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin: 14px 0;
    }
    .campaign-cell {
        border: 2px solid #375196;
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        box-shadow: 0 0 5px rgba(0, 0, 0, .15);
    }
    .campaign-cell h4 {
        margin: 0 0 6px 0;
        color: #1c3f8e;
    }
    .campaign-cell p {
        margin: 0;
        white-space: pre-line;
    }
    .campaign-body {
        margin-top: 10px;
        color: #203355;
    }
    .campaign-body img {
        max-width: 100%;
        height: auto;
    }
    .campaign-body table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 10px !important;
    }
    .campaign-body table td {
        border: 1px solid #d1e1ff !important;
        background: #f8fbff !important;
        border-radius: 10px !important;
        padding: 10px !important;
        color: #2a3c62;
    }
    .campaign-body table td:first-child {
        text-align: center !important;
        vertical-align: middle !important;
    }
    .campaign-body table td:first-child img,
    .campaign-body table td img.wp-image-251771 {
        display: block !important;
        margin: 0 auto !important;
    }
    .campaign-actions {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .campaign-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 140px;
        padding: 9px 14px;
        border-radius: 7px;
        text-decoration: none;
        color: #fff;
        font-weight: 700;
        background: var(--btn-color, #0095ff);
        border: 1px solid rgba(0, 0, 0, .15);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .35), 0 1px 2px rgba(0, 0, 0, .2);
    }
    .campaign-news-list {
        display: grid;
        gap: 14px;
    }
    .campaign-news-item {
        border-radius: 12px;
        min-height: 220px;
        overflow: hidden;
        position: relative;
        background-size: cover;
        background-position: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
    }
    .campaign-news-item::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, .12), rgba(0, 0, 0, .72));
    }
    .campaign-news-item.no-image {
        background: linear-gradient(180deg, #345c97, #1e3256);
    }
    .campaign-news-link {
        min-height: 220px;
        display: block;
        text-decoration: none !important;
        position: relative;
        z-index: 1;
    }
    .campaign-news-content {
        position: absolute;
        inset: 14px 16px;
        color: #fff;
    }
    .campaign-news-head {
        position: absolute;
        left: 0;
        top: 0;
        max-width: min(78%, 520px);
        text-align: left;
    }
    .campaign-news-title {
        margin: 0 0 6px 0;
        color: #fff;
    }
    .campaign-news-excerpt {
        margin: 0;
        font-size: 13px;
        color: #f3f7ff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, .35);
    }
    .campaign-news-foot {
        position: absolute;
        right: 0;
        bottom: 0;
    }
    .campaign-news-meta {
        margin: 0 0 8px 0;
        color: #d9e8ff;
        font-size: .8rem;
    }
    .campaign-news-author {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 8px;
        padding: 0;
        border-radius: 0;
        background: transparent;
    }
    .campaign-news-author img {
        width: 100px;
        height: 100px;
        border-radius: 0;
        border: 0;
        background: transparent;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .campaign-news-author span {
        font-size: 12px;
        color: #fff;
    }
    .campaign-news-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        border: 1px solid rgba(255, 255, 255, .35);
        color: #fff;
        font-weight: 700;
        text-decoration: none;
    }
    .campaign-comments {
        margin-top: 18px;
        border-top: 1px solid #d6e2fb;
        padding-top: 14px;
    }
    .campaign-comment-form textarea {
        width: 100%;
        min-height: 90px;
        border: 1px solid #c8d8f8;
        border-radius: 10px;
        padding: 10px;
        resize: vertical;
    }
    .campaign-comment-form button {
        margin-top: 8px;
        border: 0;
        border-radius: 8px;
        background: #2f6fab;
        color: #fff;
        font-weight: 700;
        padding: 8px 12px;
    }
    .campaign-comments-list {
        display: grid;
        gap: 12px;
        margin-top: 12px;
    }
    .campaign-comment-item {
        display: grid;
        gap: 6px;
    }
    .campaign-comment-head {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .campaign-comment-head img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #c9d9f5;
        background: #eef4ff;
    }
    .campaign-comment-head b {
        color: #1d3560;
        font-size: 14px;
    }
    .campaign-comment-bubble {
        position: relative;
        margin-left: 58px;
        background: #f6f9ff;
        border: 1px solid #d4e0f7;
        border-radius: 12px;
        padding: 10px 12px;
        color: #223760;
    }
    .campaign-comment-bubble::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 14px;
        width: 12px;
        height: 12px;
        background: #f6f9ff;
        border-left: 1px solid #d4e0f7;
        border-top: 1px solid #d4e0f7;
        transform: rotate(45deg);
    }
    .campaign-comment-content {
        margin: 0;
        text-align: left;
        white-space: pre-line;
    }
    .campaign-comment-meta {
        margin-top: 8px;
        font-size: 12px;
        color: #5f759f;
        text-align: right;
    }
    .campaign-comment-actions {
        margin-top: 8px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .campaign-comment-actions button {
        border: 1px solid #c8d8f8;
        background: #fff;
        color: #2f6fab;
        border-radius: 8px;
        padding: 5px 9px;
        font-size: 12px;
        font-weight: 700;
    }
    .campaign-comment-edit {
        margin-top: 8px;
        display: grid;
        gap: 6px;
    }
    .campaign-comment-edit textarea {
        width: 100%;
        min-height: 80px;
        border: 1px solid #c8d8f8;
        border-radius: 10px;
        padding: 8px 10px;
        resize: vertical;
    }
    .campaign-comment-edit button {
        justify-self: end;
        border: 0;
        border-radius: 8px;
        background: #2f6fab;
        color: #fff;
        font-weight: 700;
        padding: 6px 10px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">{{ $title }}</h2>

        @if($selectedNews)
            @include('habboacademy.campaign.partials.article-card', ['campaign' => $selectedNews])
            @include('habboacademy.campaign.partials.comments', ['campaignCommentable' => $selectedNews, 'campaignComments' => $campaignComments])
        @else
            @if($campaignNews->count())
                <div class="campaign-news-list">
                        @foreach($campaignNews as $campaign)
                            @php
                                $bannerValue = academyMediaUrl((string) ($campaign->banner_image_path ?? ''));
                                $detailUrl = url('/pages/noticias-campana?entry=' . $campaign->id);
                                $authorName = $campaign->author_name ?: 'Equipo HK';
                                $authorAvatar = $campaign->author_avatar_url ?: ('https://www.habbo.es/habbo-imaging/avatarimage?user=' . urlencode($authorName) . '&direction=2&head_direction=2&headonly=1&size=l');
                            @endphp
                            <article class="campaign-news-item {{ filled($bannerValue) ? '' : 'no-image' }}" @if(filled($bannerValue)) style="background-image:url('{{ $bannerValue }}')" @endif>
                                <a class="campaign-news-link" href="{{ $detailUrl }}">
                                    <div class="campaign-news-content">
                                        <div class="campaign-news-head">
                                            <h3 class="campaign-news-title">{{ $campaign->title }}</h3>
                                            @if(filled($campaign->excerpt))
                                                <p class="campaign-news-excerpt">{{ $campaign->excerpt }}</p>
                                            @endif
                                        </div>
                                        <div class="campaign-news-foot">
                                            <p class="campaign-news-author">
                                                <img src="{{ $authorAvatar }}" alt="{{ $authorName }} avatar">
                                                <span class="campaign-banner-author-meta">
                                                    <span class="campaign-banner-author-name">{{ $authorName }}</span>
                                                    <span class="campaign-banner-author-date">{{ optional($campaign->published_at ?: $campaign->created_at)->format('d/m/Y H:i') }}</span>
                                                </span>
                                            </p>
                                            <span class="campaign-news-button">Leer noticia</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $campaignNews->links() }}
                </div>
            @else
                <p class="text-muted mb-0">Todavía no hay noticias de campaña publicadas.</p>
            @endif
        @endif
    </div>
</div>
@endsection
