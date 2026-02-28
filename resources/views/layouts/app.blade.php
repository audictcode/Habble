<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - @yield('title', 'Página')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="shortcut icon" href="/favicon.png" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/iziToast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" data-turbolinks-track="true">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/theme-refresh.css') }}" data-turbolinks-track="true">

    <script src="https://polyfill.io/v3/polyfill.min.js"></script>
    <script src="{{ asset('js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" data-turbolinks-track="false" defer></script>
    <script src="{{ asset('js/radio-player.js') }}" defer></script>
    @stack('scripts')
</head>
<body>
    <div class="container-fluid p-0">
        <header class="customTransition">
            <div class="container">
                <a href="{{ url('/') }}" class="logo" aria-label="Home"></a>
                <div class="slider"></div>
                <div
                    class="header-radio-widget"
                    data-stream-url="{{ config('radio.stream_url', 'https://stream.zeno.fm/c8yg2qm7a0quv') }}"
                    data-dj-endpoint="{{ url('/api/radio/live-dj') }}"
                    data-hotel="{{ config('radio.habbo_hotel', 'es') }}"
                    data-loading-text="{{ config('radio.loading_text', 'Loading...') }}"
                >
                    <div class="header-radio-dj-box">
                        <a class="header-radio-dj-link" href="https://www.habbo.es/profile/Habble" target="_blank" rel="noopener noreferrer">
                            <img
                                class="header-radio-dj-avatar"
                                src="https://www.habbo.es/habbo-imaging/avatarimage?user=Habble&direction=2&head_direction=3&size=l"
                                alt="AutoDJ Habble avatar"
                            >
                        </a>
                        <div class="header-radio-dj-meta">
                            <div class="header-radio-dj-info-cell">
                                <span class="header-radio-dj-label">ON AIR</span>
                                <span class="header-radio-dj-name">AutoDJ (Habble)</span>
                            </div>
                        </div>
                    </div>
                    <div class="header-radio-text-cell">
                        <div class="header-radio-track-marquee" aria-live="polite">
                            <span class="header-radio-track-text">Artist - Song</span>
                        </div>
                        <span class="header-radio-listeners">Live listeners: 0</span>
                    </div>
                    <div class="header-radio-controls">
                        <button type="button" class="header-radio-btn header-radio-play" aria-label="Play radio">Play</button>
                        <button type="button" class="header-radio-btn header-radio-stop" aria-label="Stop radio">Stop</button>
                        <div class="header-radio-volume-control" role="group" aria-label="Volume control">
                            <button type="button" class="header-radio-btn header-radio-volume" aria-label="Mute or unmute">Vol</button>
                            <input class="header-radio-volume-range" type="range" min="0" max="100" value="65" aria-label="Volume slider">
                        </div>
                    </div>
                    <audio class="header-radio-audio" preload="none"></audio>
                </div>
            </div>
        </header>

        @include('habboacademy.layouts.menu')

        <main id="app">
            @include('habboacademy.utils.alerts')
            @yield('content')
        </main>
    </div>
@if (config('app.env') == 'local')
<script src="http://localhost:35729/livereload.js"></script>
@endif
</body>
</html>
