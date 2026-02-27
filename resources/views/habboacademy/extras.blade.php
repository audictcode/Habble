@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .extras-shell {
        padding: 28px 0 36px;
    }

    .extras-grid {
        display: grid;
        grid-template-columns: 1.25fr 1fr;
        gap: 18px;
    }

    .extras-card {
        background: transparent;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        padding: 16px;
        color: #000;
        box-shadow: none;
    }

    .extras-card h3 {
        font-size: 17px;
        margin: 0 0 10px;
    }

    .extras-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .extras-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .extras-field label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #000;
        margin: 0;
    }

    .extras-field input,
    .extras-field select {
        background: transparent;
        border: 1px solid rgba(0, 0, 0, 0.25);
        color: #000;
        border-radius: 8px;
        padding: 8px 10px;
        width: 100%;
        font-size: 13px;
    }

    .extras-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .extras-actions.main-actions {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
    }

    .extras-btn {
        border: 0;
        border-radius: 8px;
        padding: 8px 10px;
        color: #fff;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        width: 100%;
    }

    .extras-btn.primary { background: #1f7aff; }
    .extras-btn.success { background: #2fbf71; }
    .extras-btn.dark { background: #25324b; }

    .extras-preview-wrap {
        display: grid;
        place-items: center;
        min-height: 290px;
        background: transparent;
        border: 1px dashed rgba(0, 0, 0, 0.25);
        border-radius: 12px;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .extras-preview-wrap img {
        image-rendering: pixelated;
        max-width: 100%;
        max-height: 260px;
    }

    .extras-url {
        display: block;
        width: 100%;
        background: transparent;
        color: #000;
        border: 1px solid rgba(0, 0, 0, 0.25);
        border-radius: 8px;
        padding: 9px 10px;
        font-size: 12px;
    }

    .extras-helper {
        margin-top: 8px;
        font-size: 12px;
        color: #000;
    }

    .extras-inline {
        display: flex;
        gap: 8px;
    }

    .extras-inline > * {
        flex: 1;
    }

    .extras-badge-preview {
        display: grid;
        place-items: center;
        min-height: 108px;
        background: transparent;
        border: 1px dashed rgba(0, 0, 0, 0.25);
        border-radius: 12px;
        margin-top: 10px;
    }

    .extras-badge-preview img {
        width: 60px;
        height: 60px;
        image-rendering: pixelated;
    }

    .extras-stepper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .extras-stepper-btn {
        border: 1px solid rgba(0, 0, 0, 0.3);
        background: transparent;
        color: #000;
        border-radius: 8px;
        width: 32px;
        height: 32px;
        cursor: pointer;
        font-weight: 700;
        line-height: 1;
    }

    .extras-stepper-value {
        flex: 1;
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.25);
        border-radius: 8px;
        padding: 6px 10px;
        min-height: 32px;
        display: grid;
        place-items: center;
        background: transparent;
        font-size: 13px;
    }

    .extras-cell-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
        margin-top: 6px;
    }

    .extras-cell {
        border: 1px solid rgba(0, 0, 0, 0.28);
        background: transparent;
        color: #000;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 12px;
        cursor: pointer;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .extras-cell.is-active {
        border-color: #000;
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .extras-grid {
            grid-template-columns: 1fr;
        }

        .extras-actions.main-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
<section class="extras-shell">
    <div class="container">
        <div class="extras-grid">
            <article class="extras-card">
                <h3>Avatar Generator</h3>
                <div class="extras-form-grid">
                    <div class="extras-field">
                        <label for="avatarName">Habbo Name</label>
                        <input id="avatarName" type="text" value="Habble" maxlength="32">
                    </div>
                    <div class="extras-field">
                        <label for="avatarHotel">Hotel</label>
                        <select id="avatarHotel">
                            <option value="habbo.es">hotel.es</option>
                            <option value="habbo.com">hotel.com</option>
                            <option value="habbo.com.br">hotel.com.br</option>
                            <option value="habbo.de">hotel.de</option>
                            <option value="habbo.fi">hotel.fi</option>
                            <option value="habbo.fr">hotel.fr</option>
                            <option value="habbo.it">hotel.it</option>
                            <option value="habbo.nl">hotel.nl</option>
                        </select>
                    </div>
                    <div class="extras-field">
                        <label for="avatarSize">Size</label>
                        <select id="avatarSize">
                            <option value="s">Small</option>
                            <option value="m">Medium</option>
                            <option value="l" selected>Large</option>
                        </select>
                    </div>
                    <div class="extras-field">
                        <label for="avatarHeadOnly">Type</label>
                        <select id="avatarHeadOnly">
                            <option value="0" selected>Full Avatar</option>
                            <option value="1">Head Only</option>
                        </select>
                    </div>
                    <div class="extras-field">
                        <label for="avatarDirection">Direction</label>
                        <input id="avatarDirection" type="hidden" value="3">
                        <div class="extras-stepper" data-stepper="avatarDirection">
                            <button type="button" class="extras-stepper-btn" data-step="-1" aria-label="Direction left">&larr;</button>
                            <div class="extras-stepper-value" id="avatarDirectionDisplay">3</div>
                            <button type="button" class="extras-stepper-btn" data-step="1" aria-label="Direction right">&rarr;</button>
                        </div>
                    </div>
                    <div class="extras-field">
                        <label for="avatarHeadDirection">Head Direction</label>
                        <input id="avatarHeadDirection" type="hidden" value="3">
                        <div class="extras-stepper" data-stepper="avatarHeadDirection">
                            <button type="button" class="extras-stepper-btn" data-step="-1" aria-label="Head direction left">&larr;</button>
                            <div class="extras-stepper-value" id="avatarHeadDirectionDisplay">3</div>
                            <button type="button" class="extras-stepper-btn" data-step="1" aria-label="Head direction right">&rarr;</button>
                        </div>
                    </div>
                    <div class="extras-field">
                        <label for="avatarAction">Action</label>
                        <input id="avatarAction" type="text" value="wlk" maxlength="12" placeholder="wlk, sit, lay">
                        <div class="extras-cell-grid" data-cells-for="avatarAction">
                            <button type="button" class="extras-cell is-active" data-value="wlk">wlk</button>
                            <button type="button" class="extras-cell" data-value="std">std</button>
                            <button type="button" class="extras-cell" data-value="sit">sit</button>
                            <button type="button" class="extras-cell" data-value="lay">lay</button>
                        </div>
                    </div>
                    <div class="extras-field">
                        <label for="avatarGesture">Gesture</label>
                        <input id="avatarGesture" type="text" value="sml" maxlength="12" placeholder="sml, agr, srp">
                        <div class="extras-cell-grid" data-cells-for="avatarGesture">
                            <button type="button" class="extras-cell is-active" data-value="sml">sml</button>
                            <button type="button" class="extras-cell" data-value="agr">agr</button>
                            <button type="button" class="extras-cell" data-value="srp">srp</button>
                            <button type="button" class="extras-cell" data-value="sad">sad</button>
                        </div>
                    </div>
                    <div class="extras-field" style="grid-column: 1 / -1;">
                        <label for="avatarFigure">Figure (optional)</label>
                        <input id="avatarFigure" type="text" placeholder="hd-180-1.ch-3030-66...">
                        <div class="extras-cell-grid" data-cells-for="avatarFigure">
                            <button type="button" class="extras-cell" data-value="">default</button>
                            <button type="button" class="extras-cell" data-value="hd-180-1.ch-255-66.lg-285-82.sh-290-62">example 1</button>
                            <button type="button" class="extras-cell" data-value="hd-180-10.ch-3030-66.lg-3023-64.sh-3089-62">example 2</button>
                            <button type="button" class="extras-cell" data-value="hd-190-10.ch-3215-92.lg-3023-82.sh-290-64">example 3</button>
                        </div>
                    </div>
                </div>
                <div class="extras-actions main-actions">
                    <button type="button" id="avatarApply" class="extras-btn primary">Generate</button>
                    <button type="button" id="avatarCopy" class="extras-btn dark">Copy URL</button>
                    <button type="button" id="avatarOpen" class="extras-btn dark">Open</button>
                    <button type="button" id="avatarProfile" class="extras-btn dark">Open Profile</button>
                    <button type="button" id="avatarDownload" class="extras-btn success">Download</button>
                </div>
            </article>

            <aside class="extras-card">
                <h3>Live Preview</h3>
                <div class="extras-preview-wrap">
                    <img id="avatarPreview" src="" alt="Avatar preview">
                </div>
                <input id="avatarUrl" class="extras-url" type="text" readonly>
            </aside>
        </div>

        <div class="extras-grid mt-3">
            <article class="extras-card">
                <h3>Badge Preview</h3>
                <div class="extras-inline">
                    <div class="extras-field">
                        <label for="badgeCode">Badge Code</label>
                        <input id="badgeCode" type="text" value="ADM" maxlength="40">
                    </div>
                    <div class="extras-actions align-items-end">
                        <button type="button" id="badgeShow" class="extras-btn primary">Show Badge</button>
                    </div>
                </div>
                <div class="extras-badge-preview">
                    <img id="badgePreview" src="https://www.habboassets.com/assets/badges/ADM.gif" alt="Badge preview">
                </div>
            </article>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var avatarName = document.getElementById('avatarName');
        var avatarHotel = document.getElementById('avatarHotel');
        var avatarSize = document.getElementById('avatarSize');
        var avatarHeadOnly = document.getElementById('avatarHeadOnly');
        var avatarDirection = document.getElementById('avatarDirection');
        var avatarHeadDirection = document.getElementById('avatarHeadDirection');
        var avatarAction = document.getElementById('avatarAction');
        var avatarGesture = document.getElementById('avatarGesture');
        var avatarFigure = document.getElementById('avatarFigure');
        var avatarDirectionDisplay = document.getElementById('avatarDirectionDisplay');
        var avatarHeadDirectionDisplay = document.getElementById('avatarHeadDirectionDisplay');
        var avatarPreview = document.getElementById('avatarPreview');
        var avatarUrl = document.getElementById('avatarUrl');
        var avatarApply = document.getElementById('avatarApply');
        var avatarCopy = document.getElementById('avatarCopy');
        var avatarOpen = document.getElementById('avatarOpen');
        var avatarProfile = document.getElementById('avatarProfile');
        var avatarDownload = document.getElementById('avatarDownload');
        var badgeCode = document.getElementById('badgeCode');
        var badgeShow = document.getElementById('badgeShow');
        var badgePreview = document.getElementById('badgePreview');
        var directionValues = ['2', '3', '4'];
        var allowedHotels = ['habbo.es', 'habbo.com', 'habbo.com.br', 'habbo.de', 'habbo.fi', 'habbo.fr', 'habbo.it', 'habbo.nl'];
        var avatarErrorStage = 0;

        function resolveHotelDomain(rawHotel) {
            var normalized = String(rawHotel || '').trim().toLowerCase();
            if (normalized.indexOf('www.') === 0) {
                normalized = normalized.slice(4);
            }
            if (allowedHotels.indexOf(normalized) >= 0) {
                return normalized;
            }
            if (normalized === 'es') return 'habbo.es';
            if (normalized === 'com') return 'habbo.com';
            if (normalized === 'com.br') return 'habbo.com.br';
            if (normalized === 'de') return 'habbo.de';
            if (normalized === 'fi') return 'habbo.fi';
            if (normalized === 'fr') return 'habbo.fr';
            if (normalized === 'it') return 'habbo.it';
            if (normalized === 'nl') return 'habbo.nl';
            return 'habbo.es';
        }

        function buildAvatarUrl() {
            var name = (avatarName.value || '').trim() || 'Habble';
            var hotelDomain = resolveHotelDomain(avatarHotel.value);
            var params = new URLSearchParams();
            var figure = (avatarFigure.value || '').trim();

            params.set('headonly', avatarHeadOnly.value || '0');
            params.set('direction', avatarDirection.value || '3');
            params.set('head_direction', avatarHeadDirection.value || '3');
            params.set('size', avatarSize.value || 'l');
            if (figure.length > 0) {
                params.set('figure', figure);
            } else {
                params.set('user', name);
            }
            var action = (avatarAction.value || '').trim();
            var gesture = (avatarGesture.value || '').trim();
            if (action.length > 0) {
                params.set('action', action);
            }
            if (gesture.length > 0) {
                params.set('gesture', gesture);
            }

            return 'https://www.' + hotelDomain + '/habbo-imaging/avatarimage?' + params.toString();
        }

        function renderAvatar() {
            var url = buildAvatarUrl();
            avatarErrorStage = 0;
            avatarPreview.src = url;
            avatarUrl.value = url;
        }

        function buildAvatarFallbackUrl() {
            var name = (avatarName.value || '').trim() || 'Habble';
            var hotelDomain = resolveHotelDomain(avatarHotel.value);
            var params = new URLSearchParams();
            params.set('user', name);
            params.set('headonly', avatarHeadOnly.value || '0');
            params.set('direction', avatarDirection.value || '3');
            params.set('head_direction', avatarHeadDirection.value || '3');
            params.set('size', avatarSize.value || 'l');
            return 'https://www.' + hotelDomain + '/habbo-imaging/avatarimage?' + params.toString();
        }

        function buildAvatarFigureFallbackUrl() {
            var hotelDomain = resolveHotelDomain(avatarHotel.value);
            var params = new URLSearchParams();
            params.set('figure', 'hd-180-1.ch-255-66.lg-285-82.sh-290-62');
            params.set('headonly', avatarHeadOnly.value || '0');
            params.set('direction', avatarDirection.value || '3');
            params.set('head_direction', avatarHeadDirection.value || '3');
            params.set('size', avatarSize.value || 'l');
            return 'https://www.' + hotelDomain + '/habbo-imaging/avatarimage?' + params.toString();
        }

        function buildProfileUrl() {
            var name = (avatarName.value || '').trim() || 'Habble';
            var hotelDomain = resolveHotelDomain(avatarHotel.value);
            return 'https://www.' + hotelDomain + '/profile/' + encodeURIComponent(name);
        }

        function copyUrl() {
            if (!avatarUrl.value) {
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(avatarUrl.value);
                return;
            }
            avatarUrl.select();
            document.execCommand('copy');
        }

        function openUrl() {
            if (avatarUrl.value) {
                window.open(avatarUrl.value, '_blank', 'noopener,noreferrer');
            }
        }

        function openProfileUrl() {
            var profileUrl = buildProfileUrl();
            window.open(profileUrl, '_blank', 'noopener,noreferrer');
        }

        function downloadAvatar() {
            if (!avatarUrl.value) {
                return;
            }

            var link = document.createElement('a');
            link.href = avatarUrl.value;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.download = (avatarName.value || 'avatar') + '.png';
            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        function renderBadge() {
            var code = (badgeCode.value || '').trim().toUpperCase() || 'ADM';
            badgePreview.src = 'https://www.habboassets.com/assets/badges/' + encodeURIComponent(code) + '.gif';
            badgePreview.alt = code + ' badge preview';
        }

        function updateStepperDisplay() {
            avatarDirectionDisplay.textContent = avatarDirection.value || '3';
            avatarHeadDirectionDisplay.textContent = avatarHeadDirection.value || '3';
        }

        function moveDirection(input, step) {
            var currentIndex = directionValues.indexOf(input.value || '3');
            var nextIndex = currentIndex + step;
            if (nextIndex < 0) {
                nextIndex = directionValues.length - 1;
            }
            if (nextIndex >= directionValues.length) {
                nextIndex = 0;
            }
            input.value = directionValues[nextIndex];
            updateStepperDisplay();
            renderAvatar();
        }

        function bindExampleCells() {
            var groups = document.querySelectorAll('[data-cells-for]');
            groups.forEach(function (group) {
                var targetId = group.getAttribute('data-cells-for');
                var input = document.getElementById(targetId);
                if (!input) {
                    return;
                }

                var cells = group.querySelectorAll('.extras-cell');
                cells.forEach(function (cell) {
                    cell.addEventListener('click', function () {
                        input.value = cell.getAttribute('data-value') || '';
                        cells.forEach(function (btn) { btn.classList.remove('is-active'); });
                        cell.classList.add('is-active');
                        renderAvatar();
                    });
                });
            });
        }

        document.querySelectorAll('[data-stepper="avatarDirection"] .extras-stepper-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                moveDirection(avatarDirection, Number(button.getAttribute('data-step')) || 0);
            });
        });

        document.querySelectorAll('[data-stepper="avatarHeadDirection"] .extras-stepper-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                moveDirection(avatarHeadDirection, Number(button.getAttribute('data-step')) || 0);
            });
        });

        avatarApply.addEventListener('click', renderAvatar);
        avatarCopy.addEventListener('click', copyUrl);
        avatarOpen.addEventListener('click', openUrl);
        avatarProfile.addEventListener('click', openProfileUrl);
        avatarDownload.addEventListener('click', downloadAvatar);
        badgeShow.addEventListener('click', renderBadge);

        avatarPreview.addEventListener('error', function () {
            if (avatarErrorStage === 0) {
                var userFallback = buildAvatarFallbackUrl();
                avatarErrorStage = 1;
                avatarPreview.src = userFallback;
                avatarUrl.value = userFallback;
                return;
            }

            if (avatarErrorStage === 1) {
                var figureFallback = buildAvatarFigureFallbackUrl();
                avatarErrorStage = 2;
                avatarPreview.src = figureFallback;
                avatarUrl.value = figureFallback;
            }
        });

        badgePreview.addEventListener('error', function () {
            badgePreview.src = 'https://www.habboassets.com/assets/badges/ADM.gif';
            badgePreview.alt = 'ADM badge preview';
        });

        [
            avatarName, avatarHotel, avatarSize, avatarHeadOnly, avatarAction, avatarGesture, avatarFigure
        ].forEach(function (input) {
            input.addEventListener('input', renderAvatar);
            input.addEventListener('change', renderAvatar);
        });

        bindExampleCells();
        updateStepperDisplay();
        renderAvatar();
    })();
</script>
@endpush
