@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Notificaciones</h2>
            @if($notifications->count())
                <form action="{{ route('web.users.notifications.deleteAll') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar todas</button>
                </form>
            @endif
        </div>

        @forelse($notifications as $notification)
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge {{ $notification->getNotificationColor() }}">{{ $notification->getNotificationType() }}</span>
                        <p class="mb-1 mt-2">{{ $notification->title }}</p>
                        @if($notification->slug)
                            <a href="{{ $notification->slug }}">Abrir</a>
                        @endif
                    </div>
                    <form action="{{ route('web.users.notifications.delete', ['id' => $notification->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="mb-0 text-muted">No tienes notificaciones.</p>
        @endforelse

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
