@extends('layouts.app')

@section('title', $game->title)

@push('styles')
<style>
    .game-play-shell { padding: 24px 0 34px; }
    .game-play-head {
        border: 1px solid rgba(0, 0, 0, .12);
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
    }
    .game-play-title { margin: 0; font-size: 20px; color: #1b2e4d; }
    .game-play-desc { margin: 4px 0 0; color: #506385; font-size: 13px; }
    .game-play-frame-wrap {
        border: 1px solid rgba(0, 0, 0, .12);
        background: #fff;
        border-radius: 12px;
        padding: 10px;
    }
    .game-play-frame {
        width: 100%;
        min-height: 540px;
        border: 0;
        border-radius: 8px;
        background: #edf2fb;
    }
    .game-reward-bar {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .game-reward-chip {
        border-radius: 999px;
        border: 1px solid rgba(0, 0, 0, .1);
        background: #f5f8ff;
        color: #20304d;
        font-size: 12px;
        padding: 6px 10px;
        font-weight: 700;
    }
    .game-claim-btn {
        margin-left: auto;
        border: 0;
        border-radius: 8px;
        background: #2fbf71;
        color: #fff;
        padding: 9px 12px;
        font-weight: 700;
        font-size: 13px;
    }
    .game-claim-note { margin-left: auto; color: #2f7a4c; font-weight: 700; font-size: 12px; }

    .quiz-shell { display: grid; gap: 10px; }
    .quiz-card {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 10px;
        background: #f8fbff;
        padding: 10px;
    }
    .quiz-title { margin: 0 0 4px; font-size: 16px; color: #163052; }
    .quiz-desc { margin: 0; font-size: 13px; color: #526587; }
    .quiz-reward { margin-top: 8px; font-size: 12px; color: #2f6fab; font-weight: 700; }
    .quiz-counts { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
    .quiz-count-btn {
        border: 1px solid rgba(0, 0, 0, .15);
        border-radius: 8px;
        background: #fff;
        min-width: 42px;
        height: 34px;
        font-weight: 700;
    }
    .quiz-count-btn.is-active { background: #2f6fab; color: #fff; border-color: #2f6fab; }
    .quiz-start-btn {
        margin-top: 8px;
        border: 0;
        border-radius: 8px;
        background: #2fbf71;
        color: #fff;
        padding: 9px 12px;
        font-weight: 700;
        font-size: 13px;
    }
    .quiz-question-text { margin: 0 0 10px; font-size: 16px; color: #1b2e4d; font-weight: 700; }
    .quiz-options { display: grid; grid-template-columns: 1fr; gap: 8px; }
    .quiz-option-btn {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 8px;
        background: #fff;
        text-align: left;
        padding: 9px 10px;
        font-size: 13px;
        color: #1f304e;
    }
    .quiz-result { font-size: 14px; color: #1f304e; font-weight: 700; }
    .quiz-answer-list { margin-top: 8px; display: grid; gap: 6px; }
    .quiz-answer-item {
        border: 1px solid rgba(0,0,0,.1);
        border-radius: 8px;
        background: #fff;
        padding: 8px;
        font-size: 12px;
        color: #1f304e;
    }
    .quiz-info-cell {
        border: 1px solid rgba(47, 111, 171, .2);
        border-radius: 8px;
        background: #f6faff;
        padding: 10px;
        min-height: 76px;
        color: #425a7a;
        font-size: 13px;
        white-space: pre-wrap;
    }
</style>
@endpush

@section('content')
<section class="game-play-shell">
    <div class="container">
        <div class="game-play-head">
            <h2 class="game-play-title">{{ $game->title }}</h2>
            <p class="game-play-desc">{{ $game->description ?: 'Completa el juego y reclama tu recompensa.' }}</p>
            <p class="game-play-desc">Publicado: {{ optional($game->published_at ?: $game->created_at)->format('d/m/Y H:i') }} · Fin de participación: {{ optional($game->participation_ends_at)->format('d/m/Y H:i') ?? 'Sin límite' }}</p>
        </div>

        <div class="game-play-frame-wrap">
            @if ($game->game_type === 'quiz')
                @php
                    $questions = collect($quizQuestions ?? [])->values();
                    $availableCounts = [1, 2, 3, 5, 10, 20];
                @endphp
                <div class="quiz-shell" data-quiz-shell data-quiz-questions='@json($questions)'>
                    <div class="quiz-card" data-quiz-intro>
                        <h3 class="quiz-title">{{ $game->intro_text ?: 'Bienvenido al Quiz' }}</h3>
                        <p class="quiz-desc">{{ $game->description ?: 'Responde correctamente y desbloquea recompensas.' }}</p>
                    </div>

                    <div class="quiz-card" data-quiz-intro-options>
                        <h3 class="quiz-title">{{ $game->option_title ?: 'Opciones del Quiz' }}</h3>
                        <p class="quiz-desc">{{ $game->option_description ?: 'Selecciona cuántas preguntas quieres responder para comenzar.' }}</p>
                        <div class="quiz-reward">{{ $game->option_reward_text ?: 'Completa el quiz para habilitar la recompensa.' }}</div>

                        <div class="quiz-counts" data-quiz-counts>
                            @foreach($availableCounts as $count)
                                <button
                                    type="button"
                                    class="quiz-count-btn"
                                    data-quiz-count="{{ $count }}"
                                    @if($count > $questions->count()) disabled @endif
                                >{{ $count }}</button>
                            @endforeach
                        </div>

                        <button type="button" class="quiz-start-btn" data-quiz-start>Comenzar quiz</button>
                    </div>

                    <div class="quiz-card" data-quiz-question-wrap style="display:none;">
                        <p class="quiz-question-text" data-quiz-question-text></p>
                        <div class="quiz-options" data-quiz-options></div>
                    </div>

                    <div class="quiz-card" data-quiz-result-wrap style="display:none;">
                        <p class="quiz-result" data-quiz-result-text></p>
                        <div class="quiz-answer-list" data-quiz-answer-list></div>
                        @auth
                            @if(!$alreadyRewarded)
                                <form action="{{ route('web.games.quiz-complete', ['game' => $game->slug]) }}" method="post" data-quiz-complete-form>
                                    @csrf
                                    <input type="hidden" name="score" value="0" data-quiz-score>
                                    <input type="hidden" name="total" value="0" data-quiz-total>
                                    <button class="game-claim-btn" type="submit">Validar quiz</button>
                                </form>
                            @else
                                <span class="game-claim-note">Recompensa ya reclamada</span>
                            @endif
                        @else
                            <a class="game-claim-btn" href="{{ route('web.login') }}">Inicia sesión para reclamar</a>
                        @endauth
                    </div>

                    <div class="quiz-card">
                        <h4 class="quiz-title">Información final</h4>
                        <div class="quiz-info-cell">{{ $game->info_text ?: 'Sin información adicional.' }}</div>
                    </div>
                </div>
            @else
                @if (filled($game->game_url))
                    <iframe class="game-play-frame" src="{{ $game->game_url }}" loading="lazy" allowfullscreen></iframe>
                @else
                    <div class="p-4 text-muted">Este juego no tiene URL configurada todavía.</div>
                @endif
            @endif

            <div class="game-reward-bar">
                <span class="game-reward-chip">+{{ (int) $game->xp_reward }} XP</span>
                <span class="game-reward-chip">+{{ (int) $game->astros_reward }} Astros</span>
                <span class="game-reward-chip">+{{ (int) $game->stelas_reward }} Auroras</span>
                <span class="game-reward-chip">+{{ (int) $game->lunaris_reward }} Solarix</span>
                <span class="game-reward-chip">+{{ (int) $game->cosmos_reward }} Cosmos</span>

                @if ($game->game_type !== 'quiz')
                    @auth
                        @if (!$alreadyRewarded)
                            <form action="{{ route('web.games.claim-reward', ['game' => $game->slug]) }}" method="post">
                                @csrf
                                <button class="game-claim-btn" type="submit">Reclamar recompensa</button>
                            </form>
                        @else
                            <span class="game-claim-note">Recompensa ya reclamada</span>
                        @endif
                    @else
                        <a class="game-claim-btn" href="{{ route('web.login') }}">Inicia sesión para reclamar</a>
                    @endauth
                @else
                    @auth
                        @if ($quizPassed && !$alreadyRewarded)
                            <form action="{{ route('web.games.claim-reward', ['game' => $game->slug]) }}" method="post">
                                @csrf
                                <button class="game-claim-btn" type="submit">Reclamar recompensa</button>
                            </form>
                        @elseif($alreadyRewarded)
                            <span class="game-claim-note">Recompensa ya reclamada</span>
                        @else
                            <span class="game-claim-note">Completa y valida el quiz para reclamar</span>
                        @endif
                    @else
                        <a class="game-claim-btn" href="{{ route('web.login') }}">Inicia sesión para reclamar</a>
                    @endauth
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var shell = document.querySelector('[data-quiz-shell]');
        if (!shell) return;

        var rawQuestions = [];
        try {
            rawQuestions = JSON.parse(shell.getAttribute('data-quiz-questions') || '[]');
        } catch (e) {
            rawQuestions = [];
        }
        if (!Array.isArray(rawQuestions) || rawQuestions.length === 0) return;

        var intro = shell.querySelector('[data-quiz-intro]');
        var introOptions = shell.querySelector('[data-quiz-intro-options]');
        var questionWrap = shell.querySelector('[data-quiz-question-wrap]');
        var questionText = shell.querySelector('[data-quiz-question-text]');
        var optionsWrap = shell.querySelector('[data-quiz-options]');
        var resultWrap = shell.querySelector('[data-quiz-result-wrap]');
        var resultText = shell.querySelector('[data-quiz-result-text]');
        var answerList = shell.querySelector('[data-quiz-answer-list]');
        var startBtn = shell.querySelector('[data-quiz-start]');
        var scoreInput = shell.querySelector('[data-quiz-score]');
        var totalInput = shell.querySelector('[data-quiz-total]');

        var selectedCount = 1;
        var currentIndex = 0;
        var score = 0;
        var selectedQuestions = [];

        function shuffle(list) {
            var arr = list.slice();
            for (var i = arr.length - 1; i > 0; i--) {
                var j = Math.floor(Math.random() * (i + 1));
                var tmp = arr[i];
                arr[i] = arr[j];
                arr[j] = tmp;
            }
            return arr;
        }

        function renderQuestion() {
            var q = selectedQuestions[currentIndex];
            if (!q) return;
            questionText.textContent = (currentIndex + 1) + '. ' + (q.question || 'Pregunta');
            optionsWrap.innerHTML = '';

            [
                { key: 'a', text: q.option_a },
                { key: 'b', text: q.option_b },
                { key: 'c', text: q.option_c },
                { key: 'd', text: q.option_d }
            ].forEach(function (option) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'quiz-option-btn';
                btn.textContent = option.key.toUpperCase() + ') ' + (option.text || '');
                btn.addEventListener('click', function () {
                    if ((q.correct_option || '').toLowerCase() === option.key) {
                        score += 1;
                    }
                    currentIndex += 1;
                    if (currentIndex >= selectedQuestions.length) {
                        finishQuiz();
                        return;
                    }
                    renderQuestion();
                });
                optionsWrap.appendChild(btn);
            });
        }

        function finishQuiz() {
            questionWrap.style.display = 'none';
            resultWrap.style.display = 'block';
            var percent = Math.round((score / selectedQuestions.length) * 100);
            resultText.textContent = 'Resultado: ' + score + '/' + selectedQuestions.length + ' (' + percent + '%).';
            if (answerList) {
                answerList.innerHTML = '';
                selectedQuestions.forEach(function (q, idx) {
                    var item = document.createElement('div');
                    item.className = 'quiz-answer-item';
                    var correctKey = (q.correct_option || '').toLowerCase();
                    var correctText = '';
                    if (correctKey === 'a') correctText = q.option_a || '';
                    if (correctKey === 'b') correctText = q.option_b || '';
                    if (correctKey === 'c') correctText = q.option_c || '';
                    if (correctKey === 'd') correctText = q.option_d || '';
                    item.innerHTML = '<strong>' + (idx + 1) + '. ' + (q.question || '') + '</strong><br>Respuesta: ' + correctText;
                    answerList.appendChild(item);
                });
            }
            if (scoreInput) scoreInput.value = String(score);
            if (totalInput) totalInput.value = String(selectedQuestions.length);
        }

        shell.querySelectorAll('[data-quiz-count]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.disabled) return;
                shell.querySelectorAll('[data-quiz-count]').forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');
                selectedCount = parseInt(btn.getAttribute('data-quiz-count') || '1', 10);
            });
        });

        if (startBtn) {
            startBtn.addEventListener('click', function () {
                var available = rawQuestions.length;
                var count = Math.min(selectedCount, available);
                if (count < 1) return;

                selectedQuestions = shuffle(rawQuestions).slice(0, count);
                currentIndex = 0;
                score = 0;

                intro.style.display = 'none';
                if (introOptions) introOptions.style.display = 'none';
                resultWrap.style.display = 'none';
                questionWrap.style.display = 'block';
                renderQuestion();
            });
        }
    })();
</script>
@endpush
