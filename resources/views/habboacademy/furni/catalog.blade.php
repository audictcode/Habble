@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .furni-catalog-wrap {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 10px;
        background: #fff;
        padding: 16px;
    }
    .furni-catalog-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .furni-catalog-item {
        position: relative;
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
        vertical-align: middle;
        width: 48px;
    }
    .furni-catalog-link {
        align-items: center;
        display: inline-flex;
        height: 100%;
        justify-content: center;
        width: 100%;
    }
    .furni-catalog-item img {
        width: 42px;
        height: 42px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .furni-catalog-info-btn {
        position: absolute;
        right: -4px;
        bottom: -4px;
        width: 16px;
        height: 16px;
        border-radius: 999px;
        border: 1px solid #1d4ed8;
        background: #2563eb;
        color: #fff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .2);
    }
    .furni-info-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 16px;
    }
    .furni-info-overlay.is-open { display: flex; }
    .furni-info-modal {
        width: min(760px, 100%);
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        box-shadow: 0 18px 35px rgba(0, 0, 0, .35);
        padding: 14px;
    }
    .furni-info-top {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 6px;
    }
    .furni-info-close {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #cfd7e3;
        background: #f8fbff;
        color: #1f2a37;
        cursor: pointer;
    }
    .furni-info-body {
        display: grid;
        grid-template-columns: 170px 1fr;
        gap: 12px;
        align-items: start;
    }
    .furni-info-preview {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 10px;
        background: #f6f9ff;
        min-height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .furni-info-preview img {
        width: 120px;
        height: 120px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .furni-info-cells {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }
    .furni-info-cell {
        border: 1px solid rgba(47, 111, 171, .2);
        background: #f8fbff;
        border-radius: 8px;
        padding: 8px;
    }
    .furni-info-cell label {
        display: block;
        margin: 0 0 2px;
        font-size: 10px;
        color: #5c6b7c;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .furni-info-cell span {
        font-size: 12px;
        color: #1f2a37;
        font-weight: 700;
        display: block;
        text-align: left;
        word-break: break-word;
    }
    @media (max-width: 576px) {
        .furni-info-body { grid-template-columns: 1fr; }
        .furni-info-cells { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-2">{{ $title }}</h2>
        @if($targetCategory)
            <p class="text-muted mb-3">Categoría vinculada desde HK: <b>{{ $targetCategory }}</b></p>
        @endif

        <div class="px-4 py-4 sm:px-6">
            <div>
                <form method="GET" action="{{ url()->current() }}">
                    <div>
                        <label for="query" class="block text-sm font-medium leading-6 text-gray-900">Query</label>
                        <div class="mt-2">
                            <input type="text" name="query" id="query" value="{{ $query ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="E.g... Throne">
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-6">
                @if($furnis->count())
                    <div class="furni-catalog-wrap">
                        <div class="furni-catalog-grid">
                        @foreach($furnis as $furni)
                            @php
                                $rawImage = (string) ($furni->image_path ?: $furni->icon_path ?: '');
                                $imageUrl = trim($rawImage);
                                if ($imageUrl !== '' && !\Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://', '//', '/'])) {
                                    $path = ltrim(str_replace('\\', '/', $imageUrl), '/');
                                    if (\Illuminate\Support\Str::startsWith($path, 'public/')) {
                                        $path = substr($path, 7);
                                    }
                                    if (\Illuminate\Support\Str::startsWith($path, 'storage/')) {
                                        $imageUrl = asset($path);
                                    } else {
                                        $imageUrl = asset('storage/' . $path);
                                    }
                                } elseif (\Illuminate\Support\Str::startsWith($imageUrl, '//')) {
                                    $imageUrl = 'https:' . $imageUrl;
                                }
                                $sourceId = $furni->habboassets_furni_id ?: '-';
                                $categoryName = optional($furni->category)->name ?? 'Sin categoría';
                                $priceLabel = $furni->price !== null
                                    ? ($furni->price . ' ' . strtoupper((string) $furni->price_type))
                                    : 'Sin valor en HK';
                                $provider = $furni->source_provider ?: 'manual';
                                $hotel = $furni->habboassets_hotel ?: 'N/A';
                                $importedAt = optional($furni->imported_from_habboassets_at ?: $furni->habbofurni_imported_at ?: $furni->updated_at)->format('d/m/Y H:i:s') ?? 'Sin datos';
                            @endphp
                            <div class="furni-catalog-item items-center bg-gray-200 rounded inline-flex h-12 justify-center mb-1 mr-0 mt-0 p-2 text-center align-middle w-12 shadow ">
                                <a href="#" class="furni-catalog-link" rel="nofollow noopener" data-furni-info-open
                                   data-name="{{ $furni->name }}"
                                   data-source-id="{{ $sourceId }}"
                                   data-category="{{ $categoryName }}"
                                   data-price="{{ $priceLabel }}"
                                   data-provider="{{ $provider }}"
                                   data-hotel="{{ $hotel }}"
                                   data-imported-at="{{ $importedAt }}"
                                   data-image="{{ $imageUrl }}"
                                   onclick="if (window.openFurniInfo) { window.openFurniInfo(this); } return false;"
                                >
                                    <img src="{{ $imageUrl }}" alt="{{ $furni->name }}" loading="lazy">
                                </a>
                                <a href="#" class="furni-catalog-info-btn" data-furni-info-open
                                   data-name="{{ $furni->name }}"
                                   data-source-id="{{ $sourceId }}"
                                   data-category="{{ $categoryName }}"
                                   data-price="{{ $priceLabel }}"
                                   data-provider="{{ $provider }}"
                                   data-hotel="{{ $hotel }}"
                                   data-imported-at="{{ $importedAt }}"
                                   data-image="{{ $imageUrl }}"
                                   onclick="if (window.openFurniInfo) { window.openFurniInfo(this); } return false;"
                                   title="Info"
                                ><i class="fa-solid fa-info"></i></a>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    <div class="mt-3">{{ $furnis->links() }}</div>
                @else
                    <p class="mb-0 text-muted">No hay furnis en esta categoría todavía.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="furni-info-overlay" data-furni-info-overlay>
    <div class="furni-info-modal" role="dialog" aria-modal="true" aria-label="Información de furni">
        <div class="furni-info-top">
            <button type="button" class="furni-info-close" data-furni-info-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="furni-info-body">
            <div class="furni-info-preview">
                <img src="" alt="Furni preview" data-furni-info-image>
            </div>
            <div class="furni-info-cells">
                <div class="furni-info-cell"><label>Nombre</label><span data-furni-info-name>-</span></div>
                <div class="furni-info-cell"><label>ID HabboAssets</label><span data-furni-info-source-id>-</span></div>
                <div class="furni-info-cell"><label>Categoría</label><span data-furni-info-category>-</span></div>
                <div class="furni-info-cell"><label>Precio</label><span data-furni-info-price>-</span></div>
                <div class="furni-info-cell"><label>Provider</label><span data-furni-info-provider>-</span></div>
                <div class="furni-info-cell"><label>Hotel</label><span data-furni-info-hotel>-</span></div>
                <div class="furni-info-cell"><label>Importado</label><span data-furni-info-imported-at>-</span></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function getNodes() {
            var overlay = document.querySelector('[data-furni-info-overlay]');
            if (!overlay) return null;
            return {
                overlay: overlay,
                imageEl: overlay.querySelector('[data-furni-info-image]'),
                nameEl: overlay.querySelector('[data-furni-info-name]'),
                sourceIdEl: overlay.querySelector('[data-furni-info-source-id]'),
                categoryEl: overlay.querySelector('[data-furni-info-category]'),
                priceEl: overlay.querySelector('[data-furni-info-price]'),
                providerEl: overlay.querySelector('[data-furni-info-provider]'),
                hotelEl: overlay.querySelector('[data-furni-info-hotel]'),
                importedAtEl: overlay.querySelector('[data-furni-info-imported-at]')
            };
        }

        window.closeFurniInfo = function () {
            var nodes = getNodes();
            if (!nodes) return;
            nodes.overlay.classList.remove('is-open');
        };

        window.openFurniInfo = function (trigger) {
            if (!trigger) return;
            var nodes = getNodes();
            if (!nodes) return;

            nodes.imageEl.src = trigger.getAttribute('data-image') || '';
            nodes.nameEl.textContent = trigger.getAttribute('data-name') || '-';
            nodes.sourceIdEl.textContent = trigger.getAttribute('data-source-id') || '-';
            nodes.categoryEl.textContent = trigger.getAttribute('data-category') || '-';
            nodes.priceEl.textContent = trigger.getAttribute('data-price') || '-';
            nodes.providerEl.textContent = trigger.getAttribute('data-provider') || '-';
            nodes.hotelEl.textContent = trigger.getAttribute('data-hotel') || '-';
            nodes.importedAtEl.textContent = trigger.getAttribute('data-imported-at') || '-';

            nodes.overlay.classList.add('is-open');
        };

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-furni-info-open]');
            if (trigger) {
                window.openFurniInfo(trigger);
                return;
            }

            var closeTrigger = event.target.closest('[data-furni-info-close]');
            if (closeTrigger) {
                window.closeFurniInfo();
                return;
            }

            var nodes = getNodes();
            if (nodes && event.target === nodes.overlay) {
                window.closeFurniInfo();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeFurniInfo();
            }
        });
    })();
</script>
@endpush
