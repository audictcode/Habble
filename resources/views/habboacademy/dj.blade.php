@extends('layouts.app')

@section('title', 'DJ')

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h2 class="mb-2 mb-md-0">DJ</h2>
            <a href="{{ url('/dj.php') }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">
                Abrir panel en nueva pestaña
            </a>
        </div>

        <iframe
            src="{{ url('/dj.php') }}"
            title="DJ Panel"
            loading="lazy"
            style="width:100%; min-height:900px; border:0; border-radius:8px; background:#fff;"
        ></iframe>
    </div>
</div>
@endsection
