<nav class="menu">
    <div class="container">
        <ul class="principal-list">
            @foreach (getNavigations() as $navigation)
            @php
                $navigationSlug = $navigation->slug;
                $navigationLabelSlug = \Illuminate\Support\Str::slug($navigation->label);
                $navigationLabelLower = \Illuminate\Support\Str::lower($navigation->label);
                $navigationDisplayLabel = in_array($navigationLabelLower, ['habboacademy', 'habbo academy'])
                    ? 'Habble'
                    : ($navigationLabelLower === 'placas' ? 'Habbo' : $navigation->label);
                $iconPath = parse_url((string) $navigation->hover_icon, PHP_URL_PATH);
                $fallbackIconsByLabel = [
                    'inicio' => 'images/menu/inicio.png',
                    'home' => 'images/menu/inicio.png',
                    'habboacademy' => 'images/menu/habble.png',
                    'habbo academy' => 'images/menu/habble.png',
                    'habble' => 'images/menu/habble.png',
                    'placas' => 'images/menu/habbo.png',
                    'contenidos' => 'images/menu/contenidos.png',
                    'conteudos' => 'images/menu/contenidos.png',
                    'fan center' => 'images/menu/fancenter.png',
                    'radio' => 'images/menu/radio.png',
                ];
                $defaultIcon = $fallbackIconsByLabel[$navigationLabelLower] ?? 'images/menu/inicio.png';
                $navigationIconUrl = asset($defaultIcon);
                $fixedPathsByLabel = [
                    'inicio' => '/home',
                    'home' => '/home',
                    'habboacademy' => '/pages/noticias',
                    'habbo academy' => '/pages/noticias',
                    'habble' => '/pages/noticias',
                    'placas' => '/pages/placas',
                    'habbo' => '/pages/placas',
                    'contenidos' => '/pages/noticias',
                    'conteudos' => '/pages/noticias',
                    'contents' => '/pages/noticias',
                    'fan center' => '/pages/generador-de-avatar',
                    'fancenter' => '/pages/generador-de-avatar',
                    'radio' => '/pages/radio',
                ];

                $normalizeSlugToPath = static function (?string $rawSlug): string {
                    $slug = trim((string) $rawSlug);
                    if ($slug === '' || $slug === '/' || $slug === 'index.php' || $slug === '/index.php') {
                        return '/home';
                    }
                    if (\Illuminate\Support\Str::startsWith($slug, ['/index.php/', 'index.php/'])) {
                        $slug = preg_replace('#^/?index\.php/#', '', $slug) ?? '';
                    }
                    if ($slug === '') {
                        return '/home';
                    }
                    return '/' . ltrim($slug, '/');
                };

                if (isset($fixedPathsByLabel[$navigationLabelLower])) {
                    $navigationUrl = url($fixedPathsByLabel[$navigationLabelLower]);
                } elseif (filled($navigationSlug)) {
                    $navigationUrl = \Illuminate\Support\Str::startsWith($navigationSlug, ['http://', 'https://'])
                        ? $navigationSlug
                        : url($normalizeSlugToPath($navigationSlug));
                } else {
                    $navigationUrl = url('/pages/' . $navigationLabelSlug);
                }
                $viewerRank = auth()->check() ? (int) (auth()->user()->rank ?? 0) : 0;
            @endphp
            <li class="item-menu">
                <a href="{{ $navigationUrl }}" class="title-menu">
                    <span class="menu-text">
                        <img class="menu-inline-icon" src="{{ $navigationIconUrl }}" alt="{{ $navigationDisplayLabel }} icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                        <i class="{{ $navigation->small_icon }} menu-inline-fallback"></i>
                        <span class="menu-label">{{ $navigationDisplayLabel }}</span>
                    </span>
                </a>
                @if ($navigation->subNavigations->count())
                <div class="drop-menu">
                    <ul>
                        @foreach ($navigation->subNavigations as $subNavigation)
                        @php
                            $requiredRank = (int) ($subNavigation->min_rank ?? 0);
                            if ($requiredRank > 0 && $viewerRank < $requiredRank) {
                                continue;
                            }
                            $subNavigationSlug = $subNavigation->slug;
                            $subNavigationUrl = filled($subNavigationSlug)
                                ? (\Illuminate\Support\Str::startsWith($subNavigationSlug, ['http://', 'https://']) ? $subNavigationSlug : url($normalizeSlugToPath($subNavigationSlug)))
                                : url('/pages/' . \Illuminate\Support\Str::slug($subNavigation->label));
                        @endphp
                        <li>
                            <a @if ($subNavigation->new_tab) target="_blank" @endif href="{{ $subNavigationUrl }}">
                                {{ $subNavigation->label }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </li>
            @endforeach
            <li class="item-menu">
                <a href="{{ url('/pages/coleccionables') }}" class="title-menu">
                    <span class="menu-text">
                        <img class="menu-inline-icon" src="https://collectibles.habbo.com/collectibles.png" alt="Coleccionables icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                        <i class="fa-solid fa-gem menu-inline-fallback"></i>
                        <span class="menu-label">Coleccionables</span>
                    </span>
                </a>
            </li>
            <li class="user-club">
                @guest
                    <div class="guest-auth-actions">
                        <a class="club-button" href="{{ url('/login') }}">
                            Login
                            <i class="fa-regular fa-circle-user ml-2"></i>
                        </a>
                        <span class="text-muted guest-auth-sep">o</span>
                        <a class="club-button register" href="{{ url('/register') }}">
                            Registrarse
                            <i class="fa-regular fa-share-from-square ml-2"></i>
                        </a>
                    </div>
                @else
                    @php
                        $loggedUser = auth()->user();
                        $profileHabboName = $loggedUser?->habbo_name ?: $loggedUser?->username;
                        $profileHabboHotel = $loggedUser?->habbo_hotel ?: 'es';
                        $profileHeadUrl = 'https://www.habbo.' . $profileHabboHotel . '/habbo-imaging/avatarimage?user=' . urlencode($profileHabboName) . '&headonly=1&head_direction=3&size=l';
                        $userXp = (int) ($loggedUser?->web_experience ?? 0);
                        $xpPerLevel = 1000;
                        $currentLevel = (int) floor($userXp / $xpPerLevel) + 1;
                        $xpInLevel = $userXp % $xpPerLevel;
                        $xpProgressPercent = (int) round(($xpInLevel / $xpPerLevel) * 100);
                        $canAccessHk = !$loggedUser?->disabled && ((int) ($loggedUser?->rank ?? 0) >= 7);
                        $hkPath = '/' . ltrim((string) config('filament.path', 'hk'), '/');
                    @endphp
                    <div class="profile-menu" data-profile-menu>
                        <button type="button" class="profile-chip profile-chip-toggle" data-profile-toggle aria-expanded="false">
                            <span class="profile-chip-name">{{ $profileHabboName }}</span>
                            <img class="profile-head" src="{{ $profileHeadUrl }}" alt="{{ $profileHabboName }} head" loading="lazy">
                        </button>
                        <nav id="profile-panel" class="profile-sidepanel" data-profile-dropdown>
                            <ul class="profile-sidepanel-list">
                                <li>
                                    <button type="button" class="profile-sidepanel-option close" data-profile-close>
                                        <i class="far fa-times-circle"></i> Cerrar
                                    </button>
                                </li>

                                <li class="profile-sidepanel-header" data-wallet-toggle>
                                    <div class="profile-sidepanel-avatar-wrap">
                                        <img src="{{ $profileHeadUrl }}" alt="{{ $profileHabboName }} avatar">
                                    </div>
                                    <div class="profile-sidepanel-exp">
                                        <div class="profile-sidepanel-exp-head">
                                            <strong>Nivel {{ $currentLevel }}</strong>
                                            <span>{{ $xpInLevel }}/{{ $xpPerLevel }} XP</span>
                                        </div>
                                        <div class="profile-sidepanel-exp-track">
                                            <div class="profile-sidepanel-exp-bar" style="width: {{ $xpProgressPercent }}%;"></div>
                                        </div>
                                    </div>
                                </li>
                                <li class="profile-sidepanel-wallet" data-wallet-grid>
                                    <div class="profile-wallet-cell"><i class="fas fa-sun"></i> <b>{{ (int) ($loggedUser?->astros ?? 0) }}</b><span>Astros</span></div>
                                    <div class="profile-wallet-cell"><i class="fas fa-star"></i> <b>{{ (int) ($loggedUser?->stelas ?? 0) }}</b><span>Auroras</span></div>
                                    <div class="profile-wallet-cell"><i class="fas fa-moon"></i> <b>{{ (int) ($loggedUser?->lunaris ?? 0) }}</b><span>Solarix</span></div>
                                    <div class="profile-wallet-cell"><i class="fas fa-atom"></i> <b>{{ (int) ($loggedUser?->cosmos ?? 0) }}</b><span>Cosmos</span></div>
                                </li>

                                <li><a href="{{ url('/user/edit') }}" class="profile-sidepanel-option"><i class="far fa-id-card fa-fw"></i> Ver perfil</a></li>
                                <li><a href="{{ url('/pages/inventario') }}" class="profile-sidepanel-option"><i class="fas fa-box-open fa-fw"></i> Inventario</a></li>
                                <li><a href="{{ url('/user/notifications') }}" class="profile-sidepanel-option"><i class="fas fa-bell fa-fw"></i> Notificaciones</a></li>
                                <li><a href="{{ url('/pages/misiones') }}" class="profile-sidepanel-option"><i class="fas fa-trophy fa-fw"></i> Misiones</a></li>
                                <li><a href="{{ url('/pages/juegos') }}" class="profile-sidepanel-option"><i class="fas fa-gamepad fa-fw"></i> Juegos</a></li>

                                <li class="profile-sidepanel-division"></li>

                                <li class="profile-sidepanel-submenu">
                                    <button type="button" class="profile-sidepanel-option submenu-toggle" data-submenu-toggle>
                                        <i class="fas fa-user-circle fa-fw"></i> Mi cuenta
                                        <i class="fas fa-chevron-down caret"></i>
                                    </button>
                                    <ul class="profile-sidepanel-submenu-list">
                                        <li><a href="{{ url('/user/edit') }}" class="profile-sidepanel-option"><i class="fas fa-user-pen fa-fw"></i> Editar perfil</a></li>
                                        <li><a href="{{ url('/user/habbo-verification') }}" class="profile-sidepanel-option"><i class="fas fa-shield-alt fa-fw"></i> Seguridad</a></li>
                                        <li><a href="{{ url('/pages/misiones-diarias') }}" class="profile-sidepanel-option"><i class="fas fa-list-check fa-fw"></i> Misiones diarias</a></li>
                                    </ul>
                                </li>

                                <li class="profile-sidepanel-division"></li>

                                <li>
                                    <form action="{{ url('/logout') }}" method="post">
                                        @csrf
                                        <button type="submit" class="profile-sidepanel-option logout">
                                            <i class="fas fa-sign-out-alt fa-fw"></i> Cerrar sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    @if ($canAccessHk)
                        <a class="club-button hk-button" href="{{ url($hkPath) }}" data-turbolinks="false" data-no-turbolink="true" title="Panel administrativo" aria-label="Panel administrativo">
                            Panel administrativo
                        </a>
                    @endif
                @endguest
            </li>
        </ul>
    </div>
</nav>
@auth
<script>
    (function () {
        var menu = document.querySelector('[data-profile-menu]');
        if (!menu || menu.dataset.bound === '1') {
            return;
        }
        menu.dataset.bound = '1';

        var toggle = menu.querySelector('[data-profile-toggle]');
        var dropdown = menu.querySelector('[data-profile-dropdown]');
        var closeBtn = menu.querySelector('[data-profile-close]');
        var walletToggle = menu.querySelector('[data-wallet-toggle]');
        var walletGrid = menu.querySelector('[data-wallet-grid]');
        if (!toggle || !dropdown) {
            return;
        }

        function closeMenu() {
            menu.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function openMenu() {
            menu.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', function () {
            if (menu.classList.contains('is-open')) {
                closeMenu();
                return;
            }
            openMenu();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeMenu);
        }

        if (walletToggle && walletGrid) {
            walletToggle.addEventListener('click', function () {
                walletGrid.classList.toggle('is-open');
            });
        }

        dropdown.querySelectorAll('[data-submenu-toggle]').forEach(function (submenuToggle) {
            submenuToggle.addEventListener('click', function () {
                var parent = submenuToggle.closest('.profile-sidepanel-submenu');
                if (!parent) {
                    return;
                }
                parent.classList.toggle('is-open');
            });
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    })();
</script>
@endauth
