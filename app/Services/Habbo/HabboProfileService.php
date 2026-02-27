<?php

namespace App\Services\Habbo;

use Illuminate\Support\Facades\Http;

class HabboProfileService
{
    public function fetchProfile(string $hotel, string $habboName): array
    {
        $hotel = trim(strtolower($hotel));
        $habboName = trim($habboName);

        if ($hotel === '' || $habboName === '') {
            return [
                'ok' => false,
                'message' => 'Los datos de Habbo no están completos.',
            ];
        }

        $baseUrl = sprintf('https://www.habbo.%s', $hotel);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($baseUrl . '/api/public/users', ['name' => $habboName]);

            if (!$response->ok()) {
                return [
                    'ok' => false,
                    'message' => 'No pudimos consultar Habbo en este momento.',
                ];
            }

            $data = $response->json();

            if (!is_array($data) || empty($data['uniqueId'])) {
                return [
                    'ok' => false,
                    'message' => 'No encontramos ese usuario en habbo.' . $hotel,
                ];
            }

            $motto = isset($data['motto']) ? (string) $data['motto'] : '';

            if ($motto === '') {
                $profileResponse = Http::timeout(10)
                    ->acceptJson()
                    ->get($baseUrl . '/api/public/users/' . $data['uniqueId'] . '/profile');

                if ($profileResponse->ok()) {
                    $profileData = $profileResponse->json();

                    if (is_array($profileData)) {
                        $motto = isset($profileData['motto']) ? (string) $profileData['motto'] : '';
                    }
                }
            }

            return [
                'ok' => true,
                'name' => isset($data['name']) ? (string) $data['name'] : $habboName,
                'motto' => $motto,
                'profile_url' => $baseUrl . '/profile/' . rawurlencode($habboName),
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'No se pudo conectar con Habbo para validar la misión.',
            ];
        }
    }
}
