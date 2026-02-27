<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function topics()
    {
        $user = \Auth::user();

        return view('habboacademy.users.topics.index', [
            'topics' => $user->topics()->withCount('comments')->latest()->paginate()
        ]);
    }
    
    public function edit()
    {
        return view('habboacademy.users.account.edit', [
            'user' => \Auth::user(),
            'habboHotels' => config('habbo.hotels', ['es', 'com', 'com.br', 'fr', 'de', 'it', 'nl', 'fi', 'tr']),
        ]);
    }

    public function update(Request $request)
    {
        $user = \Auth::user();

        if(!$user) {
            return redirect()->route('web.academy.index');
        }

        $validations = [
            'name' => ['nullable', 'min:3', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'habbo_name' => ['nullable', 'string', 'min:2', 'max:50', 'regex:/^[A-Za-z0-9\\-\\._]+$/', 'required_with:habbo_hotel'],
            'habbo_hotel' => ['nullable', Rule::in(config('habbo.hotels', ['es', 'com', 'com.br', 'fr', 'de', 'it', 'nl', 'fi', 'tr'])), 'required_with:habbo_name'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'min:6', 'max:255', 'confirmed']
        ];

        if ($request->filled('habbo_name') && $request->filled('habbo_hotel')) {
            $validations['habbo_name'][] = Rule::unique('users', 'habbo_name')
                ->where(function ($query) use ($request) {
                    return $query->where('habbo_hotel', $request->input('habbo_hotel'));
                })
                ->ignore($user->id);
        }

        $data = $request->validate($validations);

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $userHasCustomAvatar = $user->profile_image_path && $user->profile_image_path != config('academy.defaultProfileImagePath');

            if ($userHasCustomAvatar) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            $image = $request->file('avatar');
            $imageName = time() . \Str::random(10) . "." . $image->extension();

            $user->profile_image_path = $image->storeAs('profiles', $imageName);
        }
        
        $user->name = $request->name;
        $user->email = $data['email'];
        $user->birth_date = $data['birth_date'] ?? null;

        $user->password = (
            isset($data['password']) ? Hash::make($data['password']) : $user->password
        );

        $newHabboName = $data['habbo_name'] ?? null;
        $newHabboHotel = $data['habbo_hotel'] ?? null;

        if (!$newHabboName || !$newHabboHotel) {
            $newHabboName = null;
            $newHabboHotel = null;
        }

        $habboChanged = $newHabboName !== $user->habbo_name || $newHabboHotel !== $user->habbo_hotel;

        $user->habbo_name = $newHabboName;
        $user->habbo_hotel = $newHabboHotel;

        if ($habboChanged) {
            $user->habbo_verified_at = null;
            $user->habbo_verification_code = $newHabboName && $newHabboHotel
                ? $this->generateHabboVerificationCode((int) $user->id)
                : null;
        }

        $user->save();

        $message = 'Perfil actualizado.';
        if ($habboChanged && $user->habbo_verification_code) {
            $message .= ' Se generó un nuevo código de verificación para tu Habbo.';
        }

        return redirect()
            ->route('web.users.edit')
            ->with('success', $message);
    }

    public function forumUpdate(Request $request)
    {
        $data = $request->validate([
            'forumSignature' => ['nullable', 'string', 'max:1000']
        ]);

        $user = \Auth::user();

        $user->update([
            'forum_signature' => nl2br(htmlspecialchars($data['forumSignature']))
        ]);

        return redirect()
            ->route('web.users.edit')
            ->with('success', 'La firma del foro fue actualizada.');
    }

    private function generateHabboVerificationCode(int $userId): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomBlock = '';

        for ($index = 0; $index < 4; $index++) {
            $randomBlock .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $prefix = strtoupper((string) config('habbo.verification_prefix', 'HLE'));

        return sprintf('%s-%s-%d', $prefix, $randomBlock, $userId);
    }
}
