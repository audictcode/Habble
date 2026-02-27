@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-2">{{ $title }}</h2>
        <p class="mb-0 text-muted">
            Esta página de navegación ya está activa.
        </p>
    </div>
</div>
@endsection
