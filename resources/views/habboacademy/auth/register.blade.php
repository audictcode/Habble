@extends('layouts.app', ['ignoreDefaultContainers' => true])

@section('title', "Registro de cuenta")

@php
    $usesCaptcha = config('academy.site.register.captchaActivated', false);
    $hotelLabels = [
        'es' => 'Habbo Hotel España',
        'com' => 'Habbo Hotel America',
        'com.br' => 'Habbo Hotel Brasil',
        'fr' => 'Habbo Hotel Francia',
        'de' => 'Habbo Hotel Alemania',
        'it' => 'Habbo Hotel Italia',
        'nl' => 'Habbo Hotel Holanda',
        'fi' => 'Habbo Hotel Finlandia',
        'tr' => 'Habbo Hotel Turquía',
    ];
@endphp

@if ($usesCaptcha)
    @push('scripts')
        {!! ReCaptcha::htmlScriptTagJsApi() !!}
    @endpush
@endif

@section('content')
    <div class="register-container">
        <div class="container">
            <div class="row m-0">
                <h2 class="text-white font-weight-bold w-100 text-center">Crea tu cuenta ahora</h2>
                <h6 class="w-100 text-center text-light">La diversión y tus nuevos amigos te están esperando.</h6>
            </div>
            <form action="{{ route('web.register') }}" method="post">
                @csrf
                <div class="default-box full">
                    <i class="user" style="top: 30px"></i>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Tu usuario" name="username" id="username" value="{{ old('username') }}" autocomplete="username" autofocus>
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="default-box full">
                    <i class="letter"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Tu correo electrónico" name="email" id="email" value="{{ old('email') }}" autocomplete="email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="default-box full">
                    <i class="user"></i>
                    <input type="text" class="form-control @error('habbo_name') is-invalid @enderror" placeholder="Tu nombre en Habbo" name="habbo_name" id="habbo_name" value="{{ old('habbo_name') }}" autocomplete="off">
                    @error('habbo_name')
                        <span class="invalid-feedback" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="default-box full">
                    <i class="letter"></i>
                    <select class="form-control @error('habbo_hotel') is-invalid @enderror" name="habbo_hotel" id="habbo_hotel">
                        <option value="">Selecciona tu hotel Habbo</option>
                        @foreach ($habboHotels as $hotel)
                            <option value="{{ $hotel }}" @selected(old('habbo_hotel') === $hotel)>
                                habbo.{{ $hotel }} - {{ $hotelLabels[$hotel] ?? 'Habbo Hotel' }}
                            </option>
                        @endforeach
                    </select>
                    @error('habbo_hotel')
                        <span class="invalid-feedback" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <p class="text-light text-center mt-2 mb-0">
                    Después del registro te mostraremos un código <strong>HLE-XXXX-ID</strong> para verificar tu perfil de Habbo con tu misión.
                </p>
                <div class="row separator-arrow mt-4"><i class="fas fa-angle-double-down fa-2x"></i></div>
                <div class="default-box full">
                    <i class="password"></i>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Tu contraseña" name="password" id="password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="default-box full">
                    <i class="password"></i>
                    <input type="password" class="form-control" placeholder="Confirma la contraseña" name="password_confirmation" id="password-confirm">
                </div>
                @if ($usesCaptcha)
                <div class="default-box full d-flex justify-content-center">
                    {!! htmlFormSnippet() !!}
                </div>
                @endif
                <div class="default-box full">
                    <button class="join" type="submit">Registrarse</button>
                    <p class="text-white mb-0 pb-0 mt-2">
                        ¿Todavía no tienes una cuenta?
                        <a href="{{ route('web.login') }}">Inicia sesión</a>.
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection
