@extends('layouts.app', ['ignoreDefaultContainers' => true])

@section('title', "Iniciar sesión")

@php
    $usesCaptcha = config('academy.site.register.captchaActivated', false);
@endphp

@section('content')
    <div class="register-container login">
        <div class="container justify-content-center pt-0">
            <div class="row m-0">
                <h2 class="text-white font-weight-bold w-100 text-center">Iniciar sesión</h2>
                <h6 class="w-100 text-center text-light">La diversión y tus nuevos amigos te están esperando.</h6>
            </div>
            <form action="{{ route('web.login') }}" method="post">
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
                    <i class="password"></i>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Tu contraseña" name="password" id="password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="auth-actions">
                    <button class="login" type="submit">Entrar</button>
                    <p class="text-white mb-0 pb-0 mt-2">
                        ¿Todavía no tienes una cuenta?
                        <a href="{{ route('web.register') }}">Regístrate aquí</a>.
                    </p>
                    <a class="secondary-auth-btn" href="{{ route('web.password.request') }}">
                        ¿Olvidaste la Contraseña? Restablecela aquí
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
