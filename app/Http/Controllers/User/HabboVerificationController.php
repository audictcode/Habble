<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Habbo\HabboProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HabboVerificationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return view('habboacademy.users.account.habbo-verification', [
            'user' => $user,
            'profileUrl' => $this->makeHabboProfileUrl($user->habbo_hotel, $user->habbo_name),
        ]);
    }

    public function check(Request $request, HabboProfileService $habboProfileService)
    {
        $user = $request->user();

        if (!$user->habbo_name || !$user->habbo_hotel || !$user->habbo_verification_code) {
            return redirect()
                ->route('web.users.habbo-verification.show')
                ->withErrors(['verification' => 'Tu cuenta no tiene datos de Habbo para verificar.']);
        }

        if ($user->habbo_verified_at) {
            return redirect()
                ->route('web.users.habbo-verification.show')
                ->with('success', 'Tu perfil de Habbo ya está verificado.');
        }

        $profileData = $habboProfileService->fetchProfile($user->habbo_hotel, $user->habbo_name);

        if (!($profileData['ok'] ?? false)) {
            return redirect()
                ->route('web.users.habbo-verification.show')
                ->withErrors(['verification' => $profileData['message'] ?? 'No se pudo validar tu misión de Habbo.']);
        }

        $motto = Str::upper((string) ($profileData['motto'] ?? ''));
        $verificationCode = Str::upper((string) $user->habbo_verification_code);

        if (!Str::contains($motto, $verificationCode)) {
            return redirect()
                ->route('web.users.habbo-verification.show')
                ->withErrors(['verification' => 'No encontramos el código en tu misión actual. Añade ' . $user->habbo_verification_code . ' en Habbo y vuelve a verificar.']);
        }

        $user->update([
            'habbo_verified_at' => now(),
            'astros' => ((int) $user->astros) + 25,
        ]);

        return redirect()
            ->route('web.users.habbo-verification.show')
            ->with('success', 'Perfil verificado correctamente. Tu Habbo quedó vinculado a la cuenta y recibiste 25 astros.');
    }

    private function makeHabboProfileUrl(?string $hotel, ?string $habboName): string
    {
        if (!$hotel || !$habboName) {
            return '#';
        }

        $hotel = strtolower(trim($hotel));
        $allowedHotels = config('habbo.hotels', ['es', 'com', 'com.br', 'fr', 'de', 'it', 'nl', 'fi', 'tr']);

        if (!in_array($hotel, $allowedHotels, true)) {
            $hotel = 'es';
        }

        return sprintf('https://www.habbo.%s/profile/%s', $hotel, rawurlencode($habboName));
    }
}
