@extends('layouts.app')

@section('title', $title)

@php
    $schedule = [
        ['day' => 'Lunes', 'time' => '18:00 - 19:00', 'show' => 'Apertura Musical', 'dj' => 'AutoDJ', 'status' => 'Programado'],
        ['day' => 'Lunes', 'time' => '20:00 - 22:00', 'show' => 'Globo De La Suerte', 'dj' => 'Equipo Eventos', 'status' => 'Principal'],
        ['day' => 'Martes', 'time' => '19:00 - 21:00', 'show' => 'Cabina Hits', 'dj' => 'DJ Invitado', 'status' => 'Programado'],
        ['day' => 'Miercoles', 'time' => '20:00 - 22:00', 'show' => 'Noche Retro', 'dj' => 'DJ Retro', 'status' => 'Programado'],
        ['day' => 'Jueves', 'time' => '19:00 - 21:00', 'show' => 'Top Recomendados', 'dj' => 'AutoDJ', 'status' => 'Programado'],
        ['day' => 'Viernes', 'time' => '21:00 - 23:30', 'show' => 'Especial Weekend', 'dj' => 'Equipo Radio', 'status' => 'Destacado'],
        ['day' => 'Sabado', 'time' => '17:00 - 20:00', 'show' => 'Tarde Gamer', 'dj' => 'DJ Play', 'status' => 'Destacado'],
        ['day' => 'Domingo', 'time' => '18:00 - 20:00', 'show' => 'Cierre Semanal', 'dj' => 'AutoDJ', 'status' => 'Programado'],
    ];
@endphp

@push('styles')
<style>
    .schedule-table-wrap {
        margin-top: 12px;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(0, 0, 0, .08);
        overflow: hidden;
    }
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 680px;
    }
    .schedule-table thead th {
        background: #173f6e;
        color: #fff;
        padding: 10px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .schedule-table tbody td {
        padding: 10px;
        border-top: 1px solid rgba(0, 0, 0, .08);
        color: #1f2f4a;
        font-size: 13px;
    }
    .schedule-table tbody tr:nth-child(even) {
        background: #f7faff;
    }
    .schedule-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgba(0, 0, 0, .1);
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        background: #edf3ff;
        color: #23406b;
    }
    .schedule-chip--featured {
        background: #ffeec7;
        color: #7c4f00;
        border-color: rgba(124, 79, 0, .2);
    }
    .schedule-note {
        margin-top: 10px;
        font-size: 12px;
        color: #4c5f7d;
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">{{ $title }}</h2>
        <p class="mb-3 text-dark">
            Consulta la parrilla semanal de radio y eventos en vivo. Estructura inspirada en el panel de programacion de estaciones online.
        </p>
        <div class="d-flex flex-wrap" style="gap: 10px;">
            <a class="btn btn-primary btn-sm" href="{{ url('/pages/radio') }}">Ir a Radio</a>
            <a class="btn btn-outline-primary btn-sm" href="{{ url('/pages/se-locutor') }}">Se locutor</a>
            <a class="btn btn-outline-primary btn-sm" href="{{ url('/pages/juegos') }}">Juegos</a>
        </div>

        <div class="schedule-table-wrap" role="region" aria-label="Horario semanal de radio">
            <div style="overflow-x:auto;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Horario</th>
                            <th>Programa</th>
                            <th>Locutor</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedule as $row)
                            <tr>
                                <td>{{ $row['day'] }}</td>
                                <td>{{ $row['time'] }}</td>
                                <td>{{ $row['show'] }}</td>
                                <td>{{ $row['dj'] }}</td>
                                <td>
                                    <span class="schedule-chip {{ in_array($row['status'], ['Destacado', 'Principal'], true) ? 'schedule-chip--featured' : '' }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="schedule-note mb-0">Puedes ajustar esta tabla desde la vista para reflejar emisiones reales por dia y franja horaria.</p>
    </div>
</div>
@endsection
