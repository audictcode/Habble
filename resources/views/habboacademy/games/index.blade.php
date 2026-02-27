@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .games-shell { padding: 26px 0 34px; }
    .radio-submenu-grid {
        margin-bottom: 14px;
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .radio-submenu-card {
        border-radius: 10px;
        position: relative;
        overflow: hidden;
        min-height: 190px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
    }
    .radio-submenu-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, .16), rgba(0, 0, 0, .72));
    }
    .radio-submenu-card.events { background: linear-gradient(180deg, #4d84ca, #275087); }
    .radio-submenu-card.games { background: linear-gradient(180deg, #56a17f, #2f5f49); }
    .radio-submenu-card.dj { background: linear-gradient(180deg, #8f73c9, #4f3f77); }
    .radio-submenu-card-inner {
        position: relative;
        z-index: 1;
        height: 100%;
        padding: 12px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .radio-submenu-title {
        margin: 0 0 6px;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
    }
    .radio-submenu-text {
        margin: 0 0 10px;
        color: #e7f0ff;
        font-size: 13px;
        min-height: 38px;
    }
    .room__enter-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #2d6ea8;
        border-radius: 8px;
        background: linear-gradient(180deg, #6caee8, #3a80c0);
        color: #fff !important;
        text-decoration: none !important;
        font-weight: 700;
        font-size: 12px;
        padding: 8px 10px;
        cursor: pointer;
    }
    .room__enter-button:hover { filter: brightness(1.03); }
    .room__enter-button__text { color: #fff; font-weight: 700; }
    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 14px;
    }
    .game-card {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }
    .game-thumb {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        display: block;
        background: #e8eef9;
    }
    .game-body { padding: 12px; }
    .game-title {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 700;
        color: #1e2f4d;
    }
    .game-meta {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .game-meta-chip {
        border: 1px solid rgba(0, 0, 0, .1);
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 700;
        color: #1f2f4c;
        background: #f5f8ff;
    }
    .game-desc {
        margin: 0 0 10px;
        font-size: 13px;
        color: #4d5f7d;
        min-height: 36px;
    }
    .game-rewards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-bottom: 10px;
    }
    .game-reward {
        border: 1px solid rgba(0, 0, 0, .1);
        border-radius: 8px;
        font-size: 12px;
        padding: 6px 8px;
        color: #22324f;
        background: #f5f8ff;
    }
    .game-action {
        width: 100%;
        border: 0;
        border-radius: 8px;
        background: #4a69bd;
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        padding: 9px 10px;
        text-decoration: none !important;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }
</style>
@endpush

@section('content')
<section class="games-shell">
    <div class="container">
        <div class="radio-submenu-grid">
            <article class="radio-submenu-card events">
                <div class="radio-submenu-card-inner">
                    <h3 class="radio-submenu-title">Eventos</h3>
                    <p class="radio-submenu-text">Consulta horarios y emisiones destacadas de la radio en vivo.</p>
                    <a class="room__enter-button" href="{{ url('/pages/horarios') }}">
                        <span class="room__enter-button__text">Ver eventos</span>
                    </a>
                </div>
            </article>
            <article class="radio-submenu-card games">
                <div class="radio-submenu-card-inner">
                    <h3 class="radio-submenu-title">Juegos</h3>
                    <p class="radio-submenu-text">Estas en el panel principal de juegos activos y recompensas.</p>
                    <a class="room__enter-button" href="{{ url('/pages/juegos') }}">
                        <span class="room__enter-button__text">Panel actual</span>
                    </a>
                </div>
            </article>
            <article class="radio-submenu-card dj">
                <div class="radio-submenu-card-inner">
                    <h3 class="radio-submenu-title">Unete de DJ</h3>
                    <p class="radio-submenu-text">Accede al formulario de solicitud para unirte al equipo de radio.</p>
                    <a class="room__enter-button" href="{{ url('/pages/se-locutor') }}">
                        <span class="room__enter-button__text">Enviar solicitud</span>
                    </a>
                </div>
            </article>
        </div>

        <div class="games-grid">
            @forelse ($games as $game)
                <article class="game-card">
                    <img class="game-thumb" src="{{ $game->thumbnail_url ?: asset('images/background.webp') }}" alt="{{ $game->title }} cover">
                    <div class="game-body">
                        <h3 class="game-title">{{ $game->title }}</h3>
                        <div class="game-meta">
                            <span class="game-meta-chip">{{ \App\Models\WebGame::CATEGORY_OPTIONS[$game->category] ?? 'Arcade' }}</span>
                            <span class="game-meta-chip">{{ $game->game_type === 'quiz' ? 'Quiz' : 'Externo' }}</span>
                            <span class="game-meta-chip">Publicado: {{ optional($game->published_at ?: $game->created_at)->format('d/m/Y H:i') }}</span>
                            <span class="game-meta-chip">Fin: {{ optional($game->participation_ends_at)->format('d/m/Y H:i') ?? 'Sin límite' }}</span>
                        </div>
                        <p class="game-desc">{{ $game->description ?: 'Juega y gana recompensas web.' }}</p>
                        <div class="game-rewards">
                            <div class="game-reward">XP: +{{ (int) $game->xp_reward }}</div>
                            <div class="game-reward">Astros: +{{ (int) $game->astros_reward }}</div>
                            <div class="game-reward">Auroras: +{{ (int) $game->stelas_reward }}</div>
                            <div class="game-reward">Solarix: +{{ (int) $game->lunaris_reward }}</div>
                        </div>
                        <a class="game-action" href="{{ route('web.games.show', ['game' => $game->slug]) }}">Ir al juego</a>
                    </div>
                </article>
            @empty
                <article class="game-card">
                    <div class="game-body">
                        <h3 class="game-title">Sin juegos publicados</h3>
                        <p class="game-desc">Publica juegos desde HK/Admin para mostrarlos aquí.</p>
                    </div>
                </article>
            @endforelse
        </div>
    </div>
</section>
@endsection
