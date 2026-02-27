@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">{{ $title }}</h2>

        @forelse($topics as $topic)
            <article class="mb-4 pb-3 border-bottom">
                <h4 class="mb-1">
                    <a href="{{ route('web.topics.show', ['id' => $topic->id, 'slug' => $topic->slug]) }}">
                        {{ $topic->title }}
                    </a>
                </h4>
                <p class="text-muted mb-2">
                    Por {{ optional($topic->user)->username ?? 'Usuario' }}
                    · {{ optional($topic->created_at)->format('d/m/Y H:i') }}
                </p>
            </article>
        @empty
            <p class="mb-0 text-muted">Todavía no hay temas publicados en el foro.</p>
        @endforelse

        <div class="mt-3">
            {{ $topics->links() }}
        </div>
    </div>
</div>
@endsection

