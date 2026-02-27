@extends('layouts.app')

@section('title', "Inicio")

@push('styles')
<style>
    .home-slides {
        margin-bottom: 16px;
    }
    .home-slides .indexSlider {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
        background: #0d1b2a;
    }
    .home-slide {
        min-height: 220px;
        display: block;
        position: relative;
        background-size: cover;
        background-position: center;
        text-decoration: none !important;
    }
    .home-slide::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, .12), rgba(0, 0, 0, .6));
    }
    .home-slide-content {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 14px;
        color: #fff;
        z-index: 1;
    }
    .home-slide-content h3 {
        margin: 0 0 4px;
        font-size: 20px;
        font-weight: 700;
    }
    .home-slide-content p {
        margin: 0;
        font-size: 13px;
        opacity: .95;
    }
    .home-swiper-pagination {
        bottom: 8px !important;
    }
    .home-badges {
        margin-top: 16px;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
    }
    .home-badges h3 {
        margin: 0 0 10px;
        font-size: 18px;
        color: #202733;
    }
    .home-badges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(78px, 1fr));
        gap: 12px;
    }
    .home-badges-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }
    .home-badges-action-btn {
        flex: 1 1 0;
        min-height: 40px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
    }
    .home-badges-action-btn.more {
        background: linear-gradient(180deg, #38a74b, #218838);
        border-color: #1c6f2d;
        color: #fff;
    }
    .home-badges-action-btn.verify {
        background: linear-gradient(180deg, #4f93cf, #2f6fab);
        border-color: #255b8f;
        color: #fff;
    }
    .home-badge-item {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        width: 100%;
        max-width: 90px;
        margin: 0 auto;
        vertical-align: top;
    }
    .home-badge-thumb-cell {
        align-items: center;
        background: #e5e7eb;
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .1);
        display: inline-flex;
        height: 48px;
        justify-content: center;
        padding: 8px;
        text-align: center;
        width: 100%;
        max-width: 64px;
    }
    .home-badge-hotel {
        font-size: 10px;
        font-weight: 700;
        color: #2f6fab;
        line-height: 1.1;
        text-align: center;
        margin-bottom: 6px;
    }
    .home-badge-item img {
        width: 100%;
        max-width: 48px;
        height: 48px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .home-badge-icons {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 2px;
        width: 100%;
        max-width: 64px;
    }
    .home-badge-icons.single {
        grid-template-columns: 1fr;
    }
    .home-badge-icon-btn {
        align-items: center;
        background: #e5e7eb;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 4px;
        color: #fff !important;
        display: inline-flex;
        height: 18px;
        justify-content: center;
        font-size: 9px;
        text-decoration: none !important;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .15);
    }
    .home-badge-icon-btn.book {
        border: 1px solid #334155;
        background: #475569;
    }
    .home-badge-icon-btn.info {
        border: 1px solid #1d4ed8;
        background: #2563eb;
    }
    .home-badge-item span {
        font-size: 11px;
        color: #2a394d;
        text-align: center;
        word-break: break-all;
    }
    .home-badge-info-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 16px;
    }
    .home-badge-info-overlay.is-open {
        display: flex;
    }
    .home-badge-info-modal {
        width: min(760px, 100%);
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        box-shadow: 0 18px 35px rgba(0, 0, 0, .35);
        padding: 14px 24px 14px 14px;
    }
    .home-badge-info-top {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 6px;
    }
    .home-badge-info-close {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #cfd7e3;
        background: #f8fbff;
        color: #1f2a37;
        cursor: pointer;
    }
    .home-badge-info-body {
        display: grid;
        grid-template-columns: 170px 1fr;
        gap: 12px;
        align-items: start;
    }
    .home-badge-info-preview {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 10px;
        background: #f6f9ff;
        min-height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .home-badge-info-preview img {
        width: 120px;
        height: 120px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .home-badge-info-cells {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .home-badge-info-cell {
        border: 1px solid rgba(47, 111, 171, .2);
        background: #f8fbff;
        border-radius: 8px;
        padding: 8px;
    }
    .home-badge-info-cell label {
        display: block;
        margin: 0 0 2px;
        font-size: 10px;
        color: #5c6b7c;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .home-badge-info-cell span {
        font-size: 12px;
        color: #1f2a37;
        font-weight: 700;
        display: block;
        text-align: left;
        word-break: break-word;
    }
    .home-badge-info-cell.is-title span,
    .home-badge-info-cell.is-description span {
        min-height: 58px;
        display: flex;
        align-items: flex-start;
    }
    @media (max-width: 576px) {
        .home-badges-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .home-badge-info-body {
            grid-template-columns: 1fr;
        }
        .home-badge-info-cells {
            grid-template-columns: 1fr;
        }
        .home-badges {
            padding: 14px;
        }
    }
</style>
@endpush

@section('content')
<section class="home-shell">
    <div class="container">
        <div class="home-slides">
            @if(isset($slides) && $slides->count())
                <div class="swiper-container indexSlider">
                    <div class="swiper-wrapper">
                        @foreach($slides as $slide)
                            @php
                                $slideImage = (string) ($slide->image_path ?? '');
                                $slideImageUrl = academyMediaUrl($slideImage);

                                $slideLink = trim((string) ($slide->slug ?? ''));
                                $slideUrl = filled($slideLink)
                                    ? (\Illuminate\Support\Str::startsWith($slideLink, ['http://', 'https://', '/']) ? $slideLink : url($slideLink))
                                    : null;
                            @endphp
                            <div class="swiper-slide">
                                @if($slideUrl)
                                    <a
                                        href="{{ $slideUrl }}"
                                        class="home-slide"
                                        style="background-image:url('{{ $slideImageUrl }}')"
                                        @if((bool) ($slide->new_tab ?? false)) target="_blank" rel="noopener noreferrer" @endif
                                    >
                                        <div class="home-slide-content">
                                            <h3>{{ $slide->title }}</h3>
                                            <p>{{ $slide->description }}</p>
                                        </div>
                                    </a>
                                @else
                                    <div class="home-slide" style="background-image:url('{{ $slideImageUrl }}')">
                                        <div class="home-slide-content">
                                            <h3>{{ $slide->title }}</h3>
                                            <p>{{ $slide->description }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination home-swiper-pagination"></div>
                </div>
            @endif
        </div>

        <div class="home-hero">
            <div class="home-copy">
                <h1>Bienvenido a Habble</h1>
                <p>Todo el contenido, comunidad y novedades de Habbo en un solo lugar.</p>
                <div class="home-actions">
                    <a href="{{ url('/home') }}" class="btn btn-primary btn-sm">Home</a>
                    @guest
                        <a href="{{ url('/login') }}" class="btn btn-outline-light btn-sm">Iniciar sesión</a>
                        <a href="{{ url('/register') }}" class="btn btn-success btn-sm">Registrarse</a>
                    @else
                        <a href="{{ url('/user/edit') }}" class="btn btn-outline-light btn-sm">Mi perfil</a>
                    @endguest
                </div>
            </div>
            <div class="home-mascot">
                <img src="{{ asset('images/planeta.webp') }}" alt="Habbo icon">
            </div>
        </div>

        <div class="home-cards">
            <a class="home-card" href="{{ url('/pages/noticias') }}">
                <img src="{{ asset('images/menu/contenidos.png') }}" alt="Noticias">
                <h3>Noticias</h3>
                <p>Últimas novedades de la comunidad.</p>
            </a>
            <a class="home-card" href="{{ url('/pages/foro') }}">
                <img src="{{ asset('images/menu/fancenter.png') }}" alt="Foro">
                <h3>Foro</h3>
                <p>Comparte ideas, dudas y guías.</p>
            </a>
            <a class="home-card" href="{{ url('/pages/radio') }}">
                <img src="{{ asset('images/menu/radio.png') }}" alt="Radio">
                <h3>Radio</h3>
                <p>Música y programas para la comunidad.</p>
            </a>
        </div>

        @if(isset($latestBadges) && $latestBadges->count())
            <div class="home-badges">
                <h3>Últimas placas añadidas</h3>
                <div class="home-badges-grid">
                    @foreach($latestBadges as $badge)
                        @php
                            $badgeImageUrl = 'https://www.habboassets.com/assets/badges/' . urlencode((string) $badge->code) . '.gif';
                            $codeUpper = strtoupper((string) ($badge->code ?? ''));
                            $hotelLabels = [
                                'ES' => 'Habbo España',
                                'US' => 'Habbo America',
                                'COM' => 'Habbo America',
                                'BR' => 'Habbo Brasil',
                                'DE' => 'Habbo Alemania',
                                'FR' => 'Habbo Francia',
                                'IT' => 'Habbo Italia',
                                'NL' => 'Habbo Países Bajos',
                                'FI' => 'Habbo Finlandia',
                                'TR' => 'Habbo Turquía',
                                'PT' => 'Habbo Portugal',
                            ];
                            $hotelRegion = 'Habbo Global';
                            foreach ($hotelLabels as $prefix => $regionName) {
                                if (\Illuminate\Support\Str::startsWith($codeUpper, $prefix)) {
                                    $hotelRegion = $regionName;
                                    break;
                                }
                            }
                            $rawFoundHotel = strtoupper(trim((string) ($badge->habboassets_hotel ?? '')));
                            $foundInHotel = $hotelLabels[$rawFoundHotel] ?? $hotelRegion;
                            $transferredAt = optional($badge->imported_from_habboassets_at ?: $badge->published_at ?: $badge->created_at)->format('d/m/Y H:i:s') ?? 'Sin Datos';
                            $habboassetsPublishedAt = optional($badge->habboassets_source_created_at ?: $badge->habbo_published_at)->format('d/m/Y H:i:s') ?? 'Sin Datos';
                            $habboassetsId = $badge->habboassets_badge_id ?: '-';
                            $badgeTitle = filled($badge->title) ? (string) $badge->title : 'Sin título';
                            $badgeDescriptionRaw = filled($badge->description) ? trim((string) $badge->description) : '';
                            $badgeDescription = $badgeDescriptionRaw !== '' ? $badgeDescriptionRaw : 'Sin descripción';
                            if (\Illuminate\Support\Str::contains(strtolower($badgeDescription), [
                                '/applications/mamp/htdocs',
                                '\\applications\\mamp\\htdocs',
                                '/var/www/',
                                '\\xampp\\htdocs',
                            ])) {
                                $badgeDescription = 'Sin descripción';
                            }
                            $contentSlug = trim((string) ($badge->content_slug ?? ''));
                            $guideUrl = filled($contentSlug)
                                ? (\Illuminate\Support\Str::startsWith($contentSlug, ['http://', 'https://', '/']) ? $contentSlug : url($contentSlug))
                                : ('https://www.google.com/search?q=' . urlencode('como conseguir placa habbo ' . $badge->code));

                            if (!empty($badgeReferralParams) && \Illuminate\Support\Str::startsWith($guideUrl, ['http://', 'https://'])) {
                                $guideUrl .= (str_contains($guideUrl, '?') ? '&' : '?') . http_build_query($badgeReferralParams);
                            }

                            $hasReferralCode = !empty($badgeReferralParams) && $habboassetsId !== '-';
                        @endphp
                        <div class="home-badge-item" title="{{ $badge->code }}">
                            <div class="home-badge-thumb-cell">
                                <a
                                    href="#"
                                    class="home-badge-link"
                                    rel="nofollow"
                                    data-home-badge-info-open
                                    data-source-id="{{ $habboassetsId }}"
                                    data-code="{{ $badge->code }}"
                                    data-found="{{ $foundInHotel }}"
                                    data-transfer-date="{{ $transferredAt }}"
                                    data-source-published-date="{{ $habboassetsPublishedAt }}"
                                    data-has-guide="{{ $hasReferralCode ? '1' : '0' }}"
                                    data-title="{{ $badgeTitle }}"
                                    data-description="{{ $badgeDescription }}"
                                    data-image="{{ $badgeImageUrl }}"
                                    onclick="if (window.openHomeBadgeInfo) { window.openHomeBadgeInfo(this); } return false;"
                                    title="Información"
                                >
                                    <img src="{{ $badgeImageUrl }}" alt="{{ $badge->code }}">
                                </a>
                            </div>
                            <div class="home-badge-icons {{ $hasReferralCode ? '' : 'single' }}">
                                @if($hasReferralCode)
                                    <a class="home-badge-icon-btn book" href="{{ $guideUrl }}" target="_blank" rel="noopener noreferrer" title="Cómo conseguirla">
                                        <i class="fa-solid fa-book"></i>
                                    </a>
                                @endif
                                <a
                                    href="#"
                                    class="home-badge-icon-btn info"
                                    rel="nofollow"
                                    data-home-badge-info-open
                                    data-source-id="{{ $habboassetsId }}"
                                    data-code="{{ $badge->code }}"
                                    data-found="{{ $foundInHotel }}"
                                    data-transfer-date="{{ $transferredAt }}"
                                    data-source-published-date="{{ $habboassetsPublishedAt }}"
                                    data-has-guide="{{ $hasReferralCode ? '1' : '0' }}"
                                    data-title="{{ $badgeTitle }}"
                                    data-description="{{ $badgeDescription }}"
                                    data-image="{{ $badgeImageUrl }}"
                                    onclick="if (window.openHomeBadgeInfo) { window.openHomeBadgeInfo(this); } return false;"
                                    title="Información"
                                >
                                    <i class="fa-solid fa-info"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="home-badges-actions">
                    <a class="home-badges-action-btn more" href="{{ url('/pages/placas') }}">Ver más</a>
                    <a class="home-badges-action-btn verify" href="{{ url('/pages/verificacion-placas') }}">Verificar</a>
                </div>
            </div>
        @endif
    </div>
</section>

<div class="home-badge-info-overlay" data-home-badge-info-overlay>
    <div class="home-badge-info-modal" role="dialog" aria-modal="true" aria-label="Información de placa">
        <div class="home-badge-info-top">
            <button type="button" class="home-badge-info-close" data-home-badge-info-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="home-badge-info-body">
            <div class="home-badge-info-preview">
                <img src="" alt="Badge preview" data-home-badge-info-image>
            </div>
            <div class="home-badge-info-cells">
                <div class="home-badge-info-cell">
                    <label>ID Placa</label>
                    <span data-home-badge-info-source-id>-</span>
                </div>
                <div class="home-badge-info-cell">
                    <label>Código</label>
                    <span data-home-badge-info-code>-</span>
                </div>
                <div class="home-badge-info-cell is-title">
                    <label>Título</label>
                    <span data-home-badge-info-title>-</span>
                </div>
                <div class="home-badge-info-cell is-description">
                    <label>Descripción</label>
                    <span data-home-badge-info-description>-</span>
                </div>
                <div class="home-badge-info-cell">
                    <label>Publicado En Hotel</label>
                    <span data-home-badge-info-source-published>-</span>
                </div>
                <div class="home-badge-info-cell">
                    <label>Transferido a tu web</label>
                    <span data-home-badge-info-transfer>-</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function getNodes() {
            var overlay = document.querySelector('[data-home-badge-info-overlay]');
            if (!overlay) return null;

            return {
                overlay: overlay,
                imageEl: overlay.querySelector('[data-home-badge-info-image]'),
                sourceIdEl: overlay.querySelector('[data-home-badge-info-source-id]'),
                codeEl: overlay.querySelector('[data-home-badge-info-code]'),
                titleEl: overlay.querySelector('[data-home-badge-info-title]'),
                descriptionEl: overlay.querySelector('[data-home-badge-info-description]'),
                sourcePublishedEl: overlay.querySelector('[data-home-badge-info-source-published]'),
                transferEl: overlay.querySelector('[data-home-badge-info-transfer]')
            };
        }

        window.closeHomeBadgeInfo = function () {
            var nodes = getNodes();
            if (!nodes) return;
            nodes.overlay.classList.remove('is-open');
        };

        window.openHomeBadgeInfo = function (trigger) {
            if (!trigger) return;
            var nodes = getNodes();
            if (!nodes) return;

            nodes.imageEl.src = trigger.getAttribute('data-image') || '';
            nodes.sourceIdEl.textContent = trigger.getAttribute('data-source-id') || '-';
            nodes.codeEl.textContent = trigger.getAttribute('data-code') || '-';
            nodes.titleEl.textContent = trigger.getAttribute('data-title') || '-';
            nodes.descriptionEl.textContent = trigger.getAttribute('data-description') || '-';
            nodes.sourcePublishedEl.textContent = trigger.getAttribute('data-source-published-date') || '-';
            nodes.transferEl.textContent = trigger.getAttribute('data-found') || '-';

            nodes.overlay.classList.add('is-open');
        };

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-home-badge-info-open]');
            if (trigger) {
                window.openHomeBadgeInfo(trigger);
                return;
            }

            var closeTrigger = event.target.closest('[data-home-badge-info-close]');
            if (closeTrigger) {
                window.closeHomeBadgeInfo();
                return;
            }

            var nodes = getNodes();
            if (nodes && event.target === nodes.overlay) {
                window.closeHomeBadgeInfo();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeHomeBadgeInfo();
            }
        });
    })();
</script>
@endpush
