@extends('layouts.app')

@section('title', $title)

@php
    $globoBanner = [
        'title' => 'Globo De La Suerte',
        'text' => 'Aviso destacado de sala activa en Habbo. Entra directamente desde aqui.',
        'image' => 'https://i.imgur.com/QccPFa1.png',
        'url' => 'https://www.habbo.es/room/125772597',
    ];

    $isLocutorPage = $slug === 'se-locutor';

    $openDjModal = !$isLocutorPage
        && ($errors->has('name')
        || $errors->has('email')
        || $errors->has('habbo_user')
        || $errors->has('message'));
@endphp

@push('styles')
<style>
    .radio-submenu-grid {
        margin-top: 14px;
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .radio-submenu-card {
        border-radius: 10px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
    }
    .radio-submenu-card {
        min-height: 190px;
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
    .radio-globo-full {
        margin-top: 16px;
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
    }
    .radio-globo-banner {
        display: block;
        width: 100%;
        background: #0f2340;
    }
    .radio-globo-banner img {
        display: block;
        width: 100%;
        height: auto;
        object-fit: contain;
        object-position: center;
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
    .room__enter-button:hover {
        filter: brightness(1.03);
    }
    .room__enter-button i {
        color: #d7ecff;
    }
    .room__enter-button__text {
        color: #fff;
        font-weight: 700;
    }
    .dj-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1060;
    }
    .dj-modal.is-open { display: flex; }
    .dj-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(7, 18, 37, .64);
    }
    .dj-modal__panel {
        position: relative;
        width: min(760px, calc(100vw - 24px));
        max-height: calc(100vh - 24px);
        overflow: auto;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, .15);
        box-shadow: 0 16px 40px rgba(0, 0, 0, .25);
        background: #fff;
        padding: 14px;
    }
    .dj-modal__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .dj-modal__title {
        margin: 0;
        color: #1e2f4d;
        font-size: 20px;
    }
    .dj-modal__close {
        border: 0;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #edf3ff;
        color: #1d2d49;
        font-size: 16px;
        cursor: pointer;
    }
    .dj-grid {
        display: grid;
        gap: 10px;
        grid-template-columns: 1fr 1fr;
    }
    .dj-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .dj-field--full {
        grid-column: 1 / -1;
    }
    .dj-label {
        font-size: 12px;
        color: #2a3b59;
        font-weight: 700;
    }
    .dj-input,
    .dj-textarea {
        border: 1px solid rgba(0, 0, 0, .16);
        border-radius: 8px;
        padding: 9px 10px;
        font-size: 13px;
        color: #1f2f4a;
        background: #fff;
    }
    .dj-textarea {
        min-height: 110px;
        resize: vertical;
    }
    .dj-submit {
        border: 1px solid #2d6ea8;
        border-radius: 8px;
        background: linear-gradient(180deg, #6caee8, #3a80c0);
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 12px;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .dj-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@if (!$isLocutorPage)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('djApplicationModal');
        if (!modal) {
            return;
        }

        var openButtons = document.querySelectorAll('.js-open-dj-modal');
        var closeButtons = modal.querySelectorAll('.js-close-dj-modal');

        var openModal = function () {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        };

        var closeModal = function () {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        };

        openButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                openModal();
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal();
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target.classList.contains('dj-modal__backdrop')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        @if ($openDjModal)
        openModal();
        @endif
    });
</script>
@endpush
@endif

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-2">{{ $title }}</h2>
        @if ($isLocutorPage)
            <p class="mb-3 text-dark">Completa el formulario para enviar tu solicitud al equipo de radio.</p>
            <form method="POST" action="{{ url('/radio/dj-application') }}">
                @csrf
                <div class="dj-grid">
                    <div class="dj-field">
                        <label class="dj-label" for="dj_name_inline">Nombre</label>
                        <input class="dj-input" id="dj_name_inline" name="name" type="text" value="{{ old('name') }}" required>
                    </div>

                    <div class="dj-field">
                        <label class="dj-label" for="dj_email_inline">Correo</label>
                        <input class="dj-input" id="dj_email_inline" name="email" type="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="dj-field">
                        <label class="dj-label" for="dj_habbo_user_inline">Usuario Habbo</label>
                        <input class="dj-input" id="dj_habbo_user_inline" name="habbo_user" type="text" value="{{ old('habbo_user') }}" required>
                    </div>

                    <div class="dj-field">
                        <label class="dj-label" for="dj_programs_inline">Programas para emitir</label>
                        <input class="dj-input" id="dj_programs_inline" name="programs" type="text" value="{{ old('programs') }}" placeholder="Ej: OBS Studio, SAM Broadcaster, VirtualDJ">
                    </div>

                    <div class="dj-field">
                        <label class="dj-label" for="dj_availability_inline">Disponibilidad</label>
                        <input class="dj-input" id="dj_availability_inline" name="availability" type="text" value="{{ old('availability') }}" placeholder="Ej: Lunes a viernes 20:00 a 22:00">
                    </div>

                    <div class="dj-field dj-field--full">
                        <label class="dj-label" for="dj_experience_inline">Experiencia</label>
                        <textarea class="dj-textarea" id="dj_experience_inline" name="experience" placeholder="Cuantanos si ya has locutado antes">{{ old('experience') }}</textarea>
                    </div>

                    <div class="dj-field dj-field--full">
                        <label class="dj-label" for="dj_message_inline">Mensaje</label>
                        <textarea class="dj-textarea" id="dj_message_inline" name="message" placeholder="Presentate y explica por que quieres unirte. Dinos ejemplo de programas que utilizas para emitir en vivo." required>{{ old('message') }}</textarea>
                    </div>

                    <div class="dj-field dj-field--full">
                        <button type="submit" class="dj-submit">Enviar a support@habble.org</button>
                    </div>
                </div>
            </form>
        @else
            <p class="mb-2 text-dark">Panel principal de radio con accesos rapidos a Eventos, Juegos y solicitud para unirte como DJ.</p>

            <div class="radio-submenu-grid">
                <article class="radio-submenu-card events">
                    <div class="radio-submenu-card-inner">
                        <h3 class="radio-submenu-title">Eventos</h3>
                        <p class="radio-submenu-text">Consulta horarios, emisiones en vivo y actividades especiales de la radio.</p>
                        <a class="room__enter-button" href="{{ url('/pages/horarios') }}">
                            <span class="room__enter-button__text">Ver eventos</span>
                        </a>
                    </div>
                </article>

                <article class="radio-submenu-card games">
                    <div class="radio-submenu-card-inner">
                        <h3 class="radio-submenu-title">Juegos</h3>
                        <p class="radio-submenu-text">Accede al panel de juegos y participa para ganar recompensas web.</p>
                        <a class="room__enter-button" href="{{ url('/pages/juegos') }}">
                            <span class="room__enter-button__text">Ir a juegos</span>
                        </a>
                    </div>
                </article>

                <article class="radio-submenu-card dj">
                    <div class="radio-submenu-card-inner">
                        <h3 class="radio-submenu-title">Unete de DJ</h3>
                        <p class="radio-submenu-text">Completa la solicitud y la enviaremos al equipo de soporte para evaluacion.</p>
                        <button type="button" class="room__enter-button js-open-dj-modal">
                            <span class="room__enter-button__text">Enviar solicitud</span>
                        </button>
                    </div>
                </article>
            </div>
        @endif
    </div>
</div>

@if (!$isLocutorPage)
<section class="radio-globo-full">
    <a class="radio-globo-banner" href="{{ $globoBanner['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $globoBanner['title'] }}">
        <img src="{{ $globoBanner['image'] }}" alt="{{ $globoBanner['title'] }}">
    </a>
</section>
@endif

@if (!$isLocutorPage)
<div class="dj-modal" id="djApplicationModal" aria-hidden="true">
    <div class="dj-modal__backdrop js-close-dj-modal"></div>
    <div class="dj-modal__panel" role="dialog" aria-modal="true" aria-labelledby="djModalTitle">
        <div class="dj-modal__header">
            <h3 class="dj-modal__title" id="djModalTitle">Solicitud para unirte como DJ</h3>
            <button type="button" class="dj-modal__close js-close-dj-modal" aria-label="Cerrar formulario">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ url('/radio/dj-application') }}">
            @csrf
            <div class="dj-grid">
                <div class="dj-field">
                    <label class="dj-label" for="dj_name">Nombre</label>
                    <input class="dj-input" id="dj_name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div class="dj-field">
                    <label class="dj-label" for="dj_email">Correo</label>
                    <input class="dj-input" id="dj_email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="dj-field">
                    <label class="dj-label" for="dj_habbo_user">Usuario Habbo</label>
                    <input class="dj-input" id="dj_habbo_user" name="habbo_user" type="text" value="{{ old('habbo_user') }}" required>
                </div>

                <div class="dj-field">
                    <label class="dj-label" for="dj_programs">Programas para emitir</label>
                    <input class="dj-input" id="dj_programs" name="programs" type="text" value="{{ old('programs') }}" placeholder="Ej: OBS Studio, SAM Broadcaster, VirtualDJ">
                </div>

                <div class="dj-field">
                    <label class="dj-label" for="dj_availability">Disponibilidad</label>
                    <input class="dj-input" id="dj_availability" name="availability" type="text" value="{{ old('availability') }}" placeholder="Ej: Lunes a viernes 20:00 a 22:00">
                </div>

                <div class="dj-field dj-field--full">
                    <label class="dj-label" for="dj_experience">Experiencia</label>
                    <textarea class="dj-textarea" id="dj_experience" name="experience" placeholder="Cuantanos si ya has locutado antes">{{ old('experience') }}</textarea>
                </div>

                <div class="dj-field dj-field--full">
                    <label class="dj-label" for="dj_message">Mensaje</label>
                    <textarea class="dj-textarea" id="dj_message" name="message" placeholder="Presentate y explica por que quieres unirte. Dinos ejemplo de programas que utilizas para emitir en vivo." required>{{ old('message') }}</textarea>
                </div>

                <div class="dj-field dj-field--full">
                    <button type="submit" class="dj-submit">Enviar a support@habble.org</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
