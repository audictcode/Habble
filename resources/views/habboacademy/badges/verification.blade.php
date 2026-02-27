@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">{{ $title }}</h2>
        <p class="mb-3 text-dark">
            Aquí puedes verificar placas y revisar si están correctamente registradas en la web.
        </p>
        <a class="btn btn-primary btn-sm" href="{{ url('/pages/placas') }}">Ir a todas las placas</a>
    </div>
</div>
@endsection

