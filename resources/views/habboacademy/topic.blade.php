@extends('layouts.app')

@section('title', $topic->title)

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4 mb-4">
        <p class="text-muted mb-2">
            Categoría: {{ optional($topic->category)->name ?? '-' }}
        </p>
        <h1 class="mb-2">{{ $topic->title }}</h1>
        <div>{!! $topic->content !!}</div>
    </div>

    <div class="default-box full p-4 mb-4">
        <h3 class="mb-3">Comentarios</h3>
        @forelse($comments as $comment)
            <div class="mb-3 pb-3 border-bottom">
                <strong>{{ optional($comment->user)->username ?? 'Usuario' }}</strong>
                <p class="mb-0">{!! $comment->content !!}</p>
            </div>
        @empty
            <p class="mb-0 text-muted">Todavía no hay comentarios.</p>
        @endforelse
        <div class="mt-3">
            {{ $comments->links() }}
        </div>
    </div>

    @auth
    <div class="default-box full p-4">
        <h3 class="mb-3">Agregar comentario</h3>
        <form action="{{ route('web.topics.comments.store', ['id' => $topic->id, 'slug' => $topic->slug]) }}" method="POST">
            @csrf
            <div class="form-group">
                <textarea name="content" rows="4" class="form-control @error('content') is-invalid @enderror" placeholder="Escribe tu comentario...">{{ old('content') }}</textarea>
                @error('content')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Publicar comentario</button>
        </form>
    </div>
    @endauth
</div>
@endsection
