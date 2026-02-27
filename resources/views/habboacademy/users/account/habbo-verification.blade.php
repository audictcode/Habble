@extends('layouts.app')

@section('title', 'Verificación Habbo')

@section('content')
<div class="register-container verification-page">
    <div class="container">
        <div class="row m-0">
            <h2 class="text-white font-weight-bold w-100 text-center">Verificación Habbo</h2>
            <h6 class="w-100 text-center text-light">Vincula tu cuenta verificando tu misión en Habbo.</h6>
        </div>

        <div class="default-box full p-4 verification-box">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('verification'))
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first('verification') }}
                </div>
            @endif

            <p class="mb-2 text-white"><strong>Habbo vinculado:</strong> {{ $user->habbo_name }} (habbo.{{ $user->habbo_hotel }})</p>
            <p class="mb-3 text-white"><strong>Código de verificación:</strong> <code>{{ $user->habbo_verification_code }}</code></p>

            <ol class="pl-3 mb-3 text-white text-left">
                <li>Copia el código y pégalo en la misión de tu personaje en Habbo.</li>
                <li>Guarda los cambios en el cliente de Habbo.</li>
                <li>Pulsa el botón de verificar en esta página.</li>
            </ol>

            <div class="verification-actions mb-2">
                <a href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer" class="verify-btn profile-btn">Abrir perfil de Habbo</a>

                @if (!$user->habbo_verified_at)
                    <form action="{{ route('web.users.habbo-verification.check') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="verify-btn verify-now-btn">Verificar misión ahora</button>
                    </form>
                @endif
            </div>

            @if ($user->habbo_verified_at)
                <p class="text-success mb-0">
                    <strong>Estado:</strong> Verificado el {{ $user->habbo_verified_at->format('d/m/Y H:i') }}
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
