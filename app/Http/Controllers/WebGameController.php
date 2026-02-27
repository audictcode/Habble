<?php

namespace App\Http\Controllers;

use App\Models\WebGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WebGameController extends Controller
{
    public function show(WebGame $game)
    {
        if (!Schema::hasTable('web_games')) {
            abort(404);
        }

        if (!$game->active) {
            abort(404);
        }
        if (Schema::hasColumn('web_games', 'published_at') && $game->published_at && $game->published_at->isFuture()) {
            abort(404);
        }
        if (Schema::hasColumn('web_games', 'participation_ends_at') && $game->participation_ends_at && $game->participation_ends_at->isPast()) {
            abort(404);
        }

        $alreadyRewarded = false;
        $quizPassed = false;
        if (auth()->check()) {
            $alreadyRewarded = DB::table('user_web_game_rewards')
                ->where('user_id', auth()->id())
                ->where('web_game_id', $game->id)
                ->exists();
            $quizPassed = (bool) session()->get($this->quizSessionKey($game, auth()->id()), false);
        }

        $quizQuestions = collect($game->quiz_questions ?? [])
            ->filter(fn ($q) => filled($q['question'] ?? null))
            ->values()
            ->all();

        return view('habboacademy.games.play', compact('game', 'alreadyRewarded', 'quizPassed', 'quizQuestions'));
    }

    public function completeQuiz(Request $request, WebGame $game)
    {
        $user = $request->user();
        if (!$user || $game->game_type !== 'quiz' || !$game->active) {
            abort(403);
        }

        $total = max(1, (int) $request->input('total', 0));
        $score = max(0, (int) $request->input('score', 0));
        $passRate = ($score / $total) * 100;
        $passed = $passRate >= 60;

        session()->put($this->quizSessionKey($game, $user->id), $passed);

        return redirect()
            ->back()
            ->with($passed ? 'success' : 'error', $passed
                ? 'Quiz completado. Ya puedes reclamar recompensa.'
                : 'No alcanzaste el puntaje mínimo del 60%. Vuelve a intentarlo.');
    }

    public function claimReward(Request $request, WebGame $game)
    {
        if (!Schema::hasTable('web_games') || !Schema::hasTable('user_web_game_rewards')) {
            return redirect()
                ->back()
                ->with('error', 'El sistema de juegos web aún no está inicializado.');
        }

        $user = $request->user();
        if (!$user || !$game->active) {
            abort(403);
        }
        if (Schema::hasColumn('web_games', 'published_at') && $game->published_at && $game->published_at->isFuture()) {
            abort(403);
        }
        if (Schema::hasColumn('web_games', 'participation_ends_at') && $game->participation_ends_at && $game->participation_ends_at->isPast()) {
            return redirect()
                ->back()
                ->with('error', 'Este juego ya cerró su fecha máxima de participación.');
        }

        if ($game->game_type === 'quiz') {
            $quizPassed = (bool) session()->get($this->quizSessionKey($game, $user->id), false);
            if (!$quizPassed) {
                return redirect()
                    ->back()
                    ->with('error', 'Debes completar el quiz con al menos 60% para reclamar.');
            }
        }

        $alreadyRewarded = DB::table('user_web_game_rewards')
            ->where('user_id', $user->id)
            ->where('web_game_id', $game->id)
            ->exists();

        if ($alreadyRewarded) {
            return redirect()
                ->back()
                ->with('error', 'Ya reclamaste la recompensa de este juego.');
        }

        DB::transaction(function () use ($user, $game) {
            DB::table('user_web_game_rewards')->insert([
                'user_id' => $user->id,
                'web_game_id' => $game->id,
                'rewarded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->increment('web_experience', (int) $game->xp_reward);
            $user->increment('astros', (int) $game->astros_reward);
            $user->increment('stelas', (int) $game->stelas_reward);
            $user->increment('lunaris', (int) $game->lunaris_reward);
            $user->increment('cosmos', (int) $game->cosmos_reward);
        });

        session()->forget($this->quizSessionKey($game, $user->id));

        return redirect()
            ->back()
            ->with('success', 'Recompensa obtenida correctamente.');
    }

    private function quizSessionKey(WebGame $game, int $userId): string
    {
        return 'quiz_passed_game_' . $game->id . '_user_' . $userId;
    }
}
