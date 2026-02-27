<?php

namespace App\Services\Habbo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
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
            $response = $this->request($baseUrl . '/api/public/users', ['name' => $habboName]);

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
                $profileResponse = $this->request($baseUrl . '/api/public/users/' . $data['uniqueId'] . '/profile');

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
            report($exception);

            return [
                'ok' => false,
                'message' => 'No se pudo conectar con Habbo para validar la misión.',
            ];
        }
    }

    private function request(string $url, array $query = []): Response
    {
        $timeout = max(1, (int) config('habbo.http_timeout', 10));
        $verifySsl = (bool) config('habbo.verify_ssl', true);

        try {
            return $this->buildRequest($timeout, $verifySsl)->get($url, $query);
        } catch (\Throwable $exception) {
            if ($verifySsl && $this->isSslCertificateIssue($exception)) {
                return $this->buildRequest($timeout, false)->get($url, $query);
            }

            throw $exception;
        }
    }

    private function buildRequest(int $timeout, bool $verifySsl): PendingRequest
    {
        $request = Http::timeout($timeout)
            ->acceptJson();

        if (!$verifySsl) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function isSslCertificateIssue(\Throwable $exception): bool
    {
        $message = $exception->getMessage();

        return stripos($message, 'cURL error 60') !== false
            || stripos($message, 'SSL certificate problem') !== false;
    }
}
