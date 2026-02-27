@extends('layouts.app')

@section('title', 'Mis temas')

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Mis temas</h2>
            <a href="{{ route('web.topics.create') }}" class="btn btn-primary btn-sm">Nuevo tema</a>
        </div>

        @forelse($topics as $topic)
            <div class="mb-3 pb-3 border-bottom">
                <a href="{{ route('web.topics.show', ['id' => $topic->id, 'slug' => $topic->slug]) }}">
                    <strong>{{ $topic->title }}</strong>
                </a>
                <div class="text-muted">
                    {{ $topic->comments_count }} comentarios
                </div>
            </div>
        @empty
            <p class="mb-0 text-muted">No has creado temas todavía.</p>
        @endforelse

        <div class="mt-3">
            {{ $topics->links() }}
        </div>
    </div>
</div>
@endsection
