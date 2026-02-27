<?php

namespace App\Http\Controllers;

use App\Models\DailyMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DailyMissionController extends Controller
{
    public function claim(Request $request, DailyMission $mission)
    {
        if (!Schema::hasTable('daily_missions') || !Schema::hasTable('user_daily_mission_rewards')) {
            return redirect()->back()->with('error', 'El sistema de misiones diarias no está disponible.');
        }

        $user = $request->user();
        if (!$user || !$mission->active) {
            abort(403);
        }

        $today = now()->toDateString();

        $alreadyClaimed = DB::table('user_daily_mission_rewards')
            ->where('user_id', $user->id)
            ->where('daily_mission_id', $mission->id)
            ->where('mission_date', $today)
            ->exists();

        if ($alreadyClaimed) {
            return redirect()->back()->with('error', 'Esta misión ya fue reclamada hoy.');
        }

        DB::transaction(function () use ($user, $mission, $today) {
            DB::table('user_daily_mission_rewards')->insert([
                'user_id' => $user->id,
                'daily_mission_id' => $mission->id,
                'mission_date' => $today,
                'rewarded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->increment('web_experience', (int) $mission->xp_reward);
            $user->increment('astros', (int) $mission->astros_reward);
            $user->increment('stelas', (int) $mission->stelas_reward);
            $user->increment('lunaris', (int) $mission->lunaris_reward);
            $user->increment('cosmos', (int) $mission->cosmos_reward);
        });

        return redirect()->back()->with('success', 'Misión diaria reclamada correctamente.');
    }
}

