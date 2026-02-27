@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .badge-filters {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 12px;
    }
    .badge-filter-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid rgba(0, 0, 0, .15);
        background: #eef5ff;
        color: #1f2a37;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none !important;
    }
    .badge-filter-cell.is-active {
        background: #2f6fab;
        border-color: #2f6fab;
        color: #fff;
    }
    .badge-catalog-wrap {
        padding: 16px;
        border: 1px solid rgba(0, 0, 0, .1);
        border-radius: 10px;
        background: #fff;
    }
    .badge-catalog-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .badge-catalog-item {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        width: 52px;
        vertical-align: top;
    }
    .badge-catalog-thumb-cell {
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
        width: 48px;
    }
    .badge-catalog-link {
        align-items: center;
        display: inline-flex;
        height: 100%;
        justify-content: center;
        width: 100%;
    }
    .badge-catalog-item img {
        width: 42px;
        height: 42px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .badge-catalog-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 2px;
        width: 48px;
    }
    .badge-catalog-actions.single {
        grid-template-columns: 1fr;
    }
    .badge-catalog-action-cell {
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
    .badge-catalog-book-btn {
        border: 1px solid #334155;
        background: #475569;
    }
    .badge-catalog-info-btn {
        border: 1px solid #1d4ed8;
        background: #2563eb;
    }
    .badge-info-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 16px;
    }
    .badge-info-overlay.is-open {
        display: flex;
    }
    .badge-info-modal {
        width: min(760px, 100%);
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        box-shadow: 0 18px 35px rgba(0, 0, 0, .35);
        padding: 14px 24px 14px 14px;
    }
    .badge-info-modal-top {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 6px;
    }
    .badge-info-close {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #cfd7e3;
        background: #f8fbff;
        color: #1f2a37;
        cursor: pointer;
    }
    .badge-info-back {
        min-width: 64px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #2f6fab;
        background: #eef5ff;
        color: #2f6fab;
        cursor: pointer;
        font-weight: 700;
        margin-right: 6px;
    }
    .badge-info-modal-body {
        display: grid;
        grid-template-columns: 170px 1fr;
        gap: 12px;
        align-items: start;
    }
    .badge-info-preview {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 10px;
        background: #f6f9ff;
        min-height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .badge-info-preview img {
        width: 120px;
        height: 120px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .badge-info-cells {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .badge-info-cell {
        border: 1px solid rgba(47, 111, 171, .2);
        background: #f8fbff;
        border-radius: 8px;
        padding: 8px;
    }
    .badge-info-cell label {
        display: block;
        margin: 0 0 2px;
        font-size: 10px;
        color: #5c6b7c;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .badge-info-cell span {
        font-size: 12px;
        color: #1f2a37;
        font-weight: 700;
        display: block;
        text-align: left;
    }
    .badge-info-cell.is-description {
    }
    .badge-info-cell.is-title span,
    .badge-info-cell.is-description span {
        min-height: 58px;
        display: flex;
        align-items: flex-start;
        word-break: break-word;
    }
    .badge-section-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 6px 10px;
        border-radius: 8px;
        background: #2f6fab;
        border: 1px solid #2f6fab;
        color: #fff !important;
        font-weight: 700;
        font-size: 12px;
        text-decoration: none !important;
    }
    @media (max-width: 992px) {
        .badge-filters { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .badge-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .badge-info-modal-body { grid-template-columns: 1fr; }
        .badge-info-cells { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">{{ $title }}</h2>

        <div class="badge-filters">
            <a class="badge-filter-cell {{ $category === 'todos' ? 'is-active' : '' }}" href="{{ url('/pages/placas?categoria=todos') }}">Todas</a>
            <a class="badge-filter-cell {{ $category === 'hoteles' ? 'is-active' : '' }}" href="{{ url('/pages/placas?categoria=hoteles') }}">Hoteles</a>
            <a class="badge-filter-cell {{ $category === 'juegos' ? 'is-active' : '' }}" href="{{ url('/pages/placas?categoria=juegos') }}">Juegos</a>
            <a class="badge-filter-cell {{ $category === 'eventos' ? 'is-active' : '' }}" href="{{ url('/pages/placas?categoria=eventos') }}">Eventos</a>
            <a class="badge-filter-cell {{ $category === 'fansites' ? 'is-active' : '' }}" href="{{ url('/pages/placas?categoria=fansites') }}">Fansites</a>
        </div>

        <div class="badge-catalog-wrap">
            <div class="badge-catalog-grid">
                @foreach($badges as $badge)
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
                        $sectionLabel = 'Todas';
                        $sectionUrl = url('/pages/placas?categoria=todos');
                        if (\Illuminate\Support\Str::startsWith($codeUpper, ['FS', 'FAN', 'FSC'])) {
                            $sectionLabel = 'Fansites';
                            $sectionUrl = url('/pages/placas?categoria=fansites');
                        } elseif (\Illuminate\Support\Str::startsWith($codeUpper, ['GAM', 'GAME', 'JUEGO', 'WOB', 'BB'])) {
                            $sectionLabel = 'Juegos';
                            $sectionUrl = url('/pages/placas?categoria=juegos');
                        } elseif (($badge->rarity ?? null) === 'event' || \Illuminate\Support\Str::startsWith($codeUpper, ['EV', 'XMAS', 'HWEEN'])) {
                            $sectionLabel = 'Eventos';
                            $sectionUrl = url('/pages/placas?categoria=eventos');
                        }
                        $contentSlug = trim((string) ($badge->content_slug ?? ''));
                        $guideUrl = filled($contentSlug)
                            ? (\Illuminate\Support\Str::startsWith($contentSlug, ['http://', 'https://', '/']) ? $contentSlug : url($contentSlug))
                            : ('https://www.google.com/search?q=' . urlencode('como conseguir placa habbo ' . $badge->code));

                        if (!empty($badgeReferralParams) && \Illuminate\Support\Str::startsWith($guideUrl, ['http://', 'https://'])) {
                            $guideUrl .= (str_contains($guideUrl, '?') ? '&' : '?') . http_build_query($badgeReferralParams);
                        }
                    @endphp
                    <div class="badge-catalog-item">
                        @php
                            $hasReferralCode = !empty($badgeReferralParams) && $habboassetsId !== '-';
                        @endphp
                        <div class="badge-catalog-thumb-cell">
                            <a
                                href="#"
                                class="badge-catalog-link"
                                rel="nofollow"
                                data-badge-info-open
                                data-source-id="{{ $habboassetsId }}"
                                data-code="{{ $badge->code }}"
                                data-found="{{ $foundInHotel }}"
                                data-transfer-date="{{ $transferredAt }}"
                                data-source-published-date="{{ $habboassetsPublishedAt }}"
                                data-section-label="{{ $sectionLabel }}"
                                data-section-url="{{ $sectionUrl }}"
                                data-guide-url="{{ $guideUrl }}"
                                data-has-guide="{{ $hasReferralCode ? '1' : '0' }}"
                                data-title="{{ $badgeTitle }}"
                                data-description="{{ $badgeDescription }}"
                                data-image="{{ $badgeImageUrl }}"
                                title="{{ $badgeTitle }} ({{ $badge->code }})"
                                onclick="if (window.openCatalogBadgeInfo) { window.openCatalogBadgeInfo(this); } return false;"
                            >
                                <img src="{{ $badgeImageUrl }}" alt="{{ $badgeTitle }}">
                            </a>
                        </div>
                        <div class="badge-catalog-actions {{ $hasReferralCode ? '' : 'single' }}">
                            @if($hasReferralCode)
                                <a
                                    href="{{ $guideUrl }}"
                                    class="badge-catalog-action-cell badge-catalog-book-btn"
                                    rel="noopener noreferrer"
                                    target="_blank"
                                    title="Cómo conseguir la placa"
                                >
                                    <i class="fa-solid fa-book"></i>
                                </a>
                            @endif
                            <a
                                href="#"
                                class="badge-catalog-action-cell badge-catalog-info-btn"
                                rel="nofollow"
                                data-badge-info-open
                                data-source-id="{{ $habboassetsId }}"
                                data-code="{{ $badge->code }}"
                                data-found="{{ $foundInHotel }}"
                                data-transfer-date="{{ $transferredAt }}"
                                data-source-published-date="{{ $habboassetsPublishedAt }}"
                                data-section-label="{{ $sectionLabel }}"
                                data-section-url="{{ $sectionUrl }}"
                                data-guide-url="{{ $guideUrl }}"
                                data-has-guide="{{ $hasReferralCode ? '1' : '0' }}"
                                data-title="{{ $badgeTitle }}"
                                data-description="{{ $badgeDescription }}"
                                data-image="{{ $badgeImageUrl }}"
                                title="Info"
                                onclick="if (window.openCatalogBadgeInfo) { window.openCatalogBadgeInfo(this); } return false;"
                            >
                                <i class="fa-solid fa-info"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-3">
            {{ $badges->links() }}
        </div>
    </div>
</div>

<div class="badge-info-overlay" data-badge-info-overlay>
    <div class="badge-info-modal" role="dialog" aria-modal="true" aria-label="Información de placa">
        <div class="badge-info-modal-top">
            <button type="button" class="badge-info-back" data-badge-info-close>Volver</button>
            <button type="button" class="badge-info-close" data-badge-info-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="badge-info-modal-body">
            <div class="badge-info-preview">
                <img src="" alt="Badge preview" data-badge-info-image>
            </div>
            <div class="badge-info-cells">
                <div class="badge-info-cell">
                    <label>ID Placa</label>
                    <span data-badge-info-source-id>-</span>
                </div>
                <div class="badge-info-cell">
                    <label>Código</label>
                    <span data-badge-info-code>-</span>
                </div>
                <div class="badge-info-cell is-title">
                    <label>Título</label>
                    <span data-badge-info-title>-</span>
                </div>
                <div class="badge-info-cell is-description">
                    <label>Descripción</label>
                    <span data-badge-info-description>-</span>
                </div>
                <div class="badge-info-cell">
                    <label>Publicado En Hotel</label>
                    <span data-badge-info-source-published>-</span>
                </div>
                <div class="badge-info-cell">
                    <label>Transferido a tu web</label>
                    <span data-badge-info-transfer>-</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var ONE_HOUR_MS = 60 * 60 * 1000;

        setInterval(function () {
            window.location.reload();
        }, ONE_HOUR_MS);

        function getNodes() {
            var overlay = document.querySelector('[data-badge-info-overlay]');
            if (!overlay) return null;

            return {
                overlay: overlay,
                imageEl: overlay.querySelector('[data-badge-info-image]'),
                sourceIdEl: overlay.querySelector('[data-badge-info-source-id]'),
                codeEl: overlay.querySelector('[data-badge-info-code]'),
                titleEl: overlay.querySelector('[data-badge-info-title]'),
                descriptionEl: overlay.querySelector('[data-badge-info-description]'),
                sourcePublishedEl: overlay.querySelector('[data-badge-info-source-published]'),
                transferEl: overlay.querySelector('[data-badge-info-transfer]')
            };
        }

        window.closeCatalogBadgeInfo = function () {
            var nodes = getNodes();
            if (!nodes) return;
            nodes.overlay.classList.remove('is-open');
        };

        window.openCatalogBadgeInfo = function (trigger) {
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
            var trigger = event.target.closest('[data-badge-info-open]');
            if (trigger) {
                window.openCatalogBadgeInfo(trigger);
                return;
            }

            var closeTrigger = event.target.closest('[data-badge-info-close]');
            if (closeTrigger) {
                window.closeCatalogBadgeInfo();
                return;
            }

            var nodes = getNodes();
            if (nodes && event.target === nodes.overlay) {
                window.closeCatalogBadgeInfo();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeCatalogBadgeInfo();
            }
        });
    })();
</script>
@endpush
