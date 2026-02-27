<section class="campaign-comments">
    <h3 class="mb-2">Comentarios</h3>

    @auth
        <form class="campaign-comment-form" method="POST" action="{{ route('web.campaign.comments.store', ['campaign' => $campaignCommentable->id]) }}">
            @csrf
            <textarea name="content" placeholder="Escribe tu comentario..." required>{{ old('content') }}</textarea>
            @error('content')
                <p class="text-danger mt-1 mb-0">{{ $message }}</p>
            @enderror
            <button type="submit">Publicar comentario</button>
        </form>
    @else
        <p class="mb-0 text-muted">Debes iniciar sesión para comentar este post.</p>
    @endauth

    @if($campaignComments && $campaignComments->count())
        <div class="campaign-comments-list">
            @foreach($campaignComments as $comment)
                @php
                    $user = $comment->user;
                    $userName = $user?->habbo_name ?: $user?->username ?: 'Usuario';
                    $hotel = $user?->habbo_hotel ?: 'es';
                    $profileImage = 'https://www.habbo.' . $hotel . '/habbo-imaging/avatarimage?user=' . urlencode($userName) . '&direction=2&head_direction=2&headonly=1&size=l';
                @endphp
                <article class="campaign-comment-item">
                    <div class="campaign-comment-head">
                        <img src="{{ $profileImage }}" alt="{{ $userName }} perfil">
                    </div>
                    <div class="campaign-comment-bubble">
                        <b>{{ $userName }}</b>
                        <p class="campaign-comment-content">{{ $comment->content }}</p>
                        <p class="campaign-comment-meta">{{ optional($comment->created_at)->format('d/m/Y H:i') }}</p>
                        @if(auth()->check() && (int) auth()->id() === (int) $comment->user_id)
                            <form class="campaign-comment-edit" method="POST" action="{{ route('web.campaign.comments.update', ['campaign' => $campaignCommentable->id, 'comment' => $comment->id]) }}">
                                @csrf
                                @method('PUT')
                                <textarea name="content" required>{{ $comment->content }}</textarea>
                                <button type="submit">Guardar cambios</button>
                            </form>
                            <div class="campaign-comment-actions">
                                <form method="POST" action="{{ route('web.campaign.comments.destroy', ['campaign' => $campaignCommentable->id, 'comment' => $comment->id]) }}" onsubmit="return confirm('¿Eliminar este comentario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Eliminar</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-3">
            {{ $campaignComments->links() }}
        </div>
    @else
        <p class="text-muted mt-3 mb-0">Todavía no hay comentarios en este post.</p>
    @endif
</section>
