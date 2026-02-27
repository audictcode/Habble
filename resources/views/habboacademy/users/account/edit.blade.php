@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
@php
    $walletThreshold = 25;
    $walletStats = [
        ['label' => 'Astros', 'value' => (int) ($user->astros ?? 0)],
        ['label' => 'Auroras', 'value' => (int) ($user->stelas ?? 0)],
        ['label' => 'Solarix', 'value' => (int) ($user->lunaris ?? 0)],
        ['label' => 'Cosmos', 'value' => (int) ($user->cosmos ?? 0)],
    ];
@endphp
<div class="container mt-4 mb-4">
    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="default-box full p-4">
                <h2 class="mb-3">Editar perfil</h2>
                <form action="{{ route('web.users.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="birth_date">Fecha de nacimiento</label>
                        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}" class="form-control @error('birth_date') is-invalid @enderror">
                        @error('birth_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="habbo_name">Usuario Habbo</label>
                        <input id="habbo_name" name="habbo_name" type="text" value="{{ old('habbo_name', $user->habbo_name) }}" class="form-control @error('habbo_name') is-invalid @enderror">
                        @error('habbo_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="habbo_hotel">Hotel Habbo</label>
                        <select id="habbo_hotel" name="habbo_hotel" class="form-control @error('habbo_hotel') is-invalid @enderror">
                            <option value="">Selecciona tu hotel</option>
                            @foreach ($habboHotels as $hotel)
                                <option value="{{ $hotel }}" @selected(old('habbo_hotel', $user->habbo_hotel) === $hotel)>habbo.{{ $hotel }}</option>
                            @endforeach
                        </select>
                        @error('habbo_hotel')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="avatar">Avatar</label>
                        <input id="avatar" name="avatar" type="file" class="form-control-file @error('avatar') is-invalid @enderror">
                        @error('avatar')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Nueva contraseña</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="default-box full p-4">
                <h2 class="mb-3">Economía</h2>
                @foreach ($walletStats as $stat)
                    @php $walletVerified = $stat['value'] >= $walletThreshold; @endphp
                    <p class="mb-2">
                        <strong>{{ $stat['label'] }}:</strong> {{ $stat['value'] }}
                        @if ($walletVerified)
                            <span class="badge ml-2 badge-success">Verificado</span>
                        @endif
                    </p>
                @endforeach

                <h2 class="mb-3">Verificación Habbo</h2>
                <p class="mb-2"><strong>Usuario:</strong> {{ $user->habbo_name ?? 'No definido' }}</p>
                <p class="mb-2"><strong>Hotel:</strong> {{ $user->habbo_hotel ? 'habbo.' . $user->habbo_hotel : 'No definido' }}</p>
                <p class="mb-2"><strong>Código:</strong> {{ $user->habbo_verification_code ?? 'No generado' }}</p>
                @if ($user->habbo_verified_at)
                    <p class="text-success"><strong>Estado:</strong> Verificado</p>
                @else
                    <p class="text-warning"><strong>Estado:</strong> Pendiente</p>
                @endif
                <a href="{{ route('web.users.habbo-verification.show') }}" class="btn btn-outline-primary btn-sm mb-4">Ir a verificación</a>

                <h2 class="mb-3">Firma del foro</h2>
                <form action="{{ route('web.users.forumUpdate') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <textarea name="forumSignature" rows="8" class="form-control @error('forumSignature') is-invalid @enderror">{{ old('forumSignature', str_replace(['<br />', '<br>'], PHP_EOL, $user->forum_signature)) }}</textarea>
                        @error('forumSignature')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Guardar firma</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
