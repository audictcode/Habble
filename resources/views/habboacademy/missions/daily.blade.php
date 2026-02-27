@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .daily-missions-shell { padding: 24px 0 34px; }
    .daily-missions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }
    .daily-mission-card {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        background: #fff;
        padding: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }
    .daily-mission-title {
        margin: 0 0 4px;
        color: #1b2e4d;
        font-size: 16px;
        font-weight: 700;
    }
    .daily-mission-text {
        margin: 0 0 10px;
        color: #4f6283;
        font-size: 13px;
    }
    .daily-mission-rewards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 10px;
    }
    .daily-mission-reward {
        border: 1px solid rgba(0, 0, 0, .1);
        border-radius: 8px;
        font-size: 12px;
        padding: 6px;
        text-align: center;
        background: #f5f8ff;
        color: #20304d;
        font-weight: 700;
    }
    .daily-mission-action {
        width: 100%;
        border: 0;
        border-radius: 8px;
        background: #2fbf71;
        color: #fff;
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 700;
    }
    .daily-mission-action.done {
        background: #7f8ca3;
    }
</style>
@endpush

@section('content')
<section class="daily-missions-shell">
    <div class="container">
        <div class="daily-missions-grid">
            @forelse($missions as $mission)
                @php
                    $claimed = in_array((int) $mission->id, $claimedToday ?? [], true);
                @endphp
                <article class="daily-mission-card">
                    <h3 class="daily-mission-title">{{ $mission->title }}</h3>
                    <p class="daily-mission-text">{{ $mission->description ?: 'Completa la misión y reclama tus recompensas diarias.' }}</p>
                    <div class="daily-mission-rewards">
                        <div class="daily-mission-reward">+{{ (int) $mission->xp_reward }} XP</div>
                        <div class="daily-mission-reward">+{{ (int) $mission->astros_reward }} Astros</div>
                        <div class="daily-mission-reward">+{{ (int) $mission->stelas_reward }} Auroras</div>
                        <div class="daily-mission-reward">+{{ (int) $mission->lunaris_reward }} Solarix</div>
                        <div class="daily-mission-reward">+{{ (int) $mission->cosmos_reward }} Cosmos</div>
                    </div>

                    @auth
                        @if(!$claimed)
                            <form method="post" action="{{ route('web.daily-missions.claim', ['mission' => $mission->id]) }}">
                                @csrf
                                <button class="daily-mission-action" type="submit">Reclamar misión diaria</button>
                            </form>
                        @else
                            <button class="daily-mission-action done" type="button" disabled>Ya reclamada hoy</button>
                        @endif
                    @else
                        <a class="daily-mission-action" href="{{ route('web.login') }}">Inicia sesión para reclamar</a>
                    @endauth
                </article>
            @empty
                <article class="daily-mission-card">
                    <h3 class="daily-mission-title">Sin misiones diarias</h3>
                    <p class="daily-mission-text">Configura misiones diarias desde HK para mostrarlas aquí.</p>
                </article>
            @endforelse
        </div>
    </div>
</section>
@endsection
