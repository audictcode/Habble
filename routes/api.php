<?php

use App\Models\Article;
use App\Models\Article\ArticleCategory;
use App\Models\Badge;
use App\Models\FurniValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('v1.')->prefix('v1')->group(function() {

    // Furni Values API Route
    Route::get('furnis/values', fn (Request $request) => FurniValue::resultsForApi($request->search));

    // Last Badges API Route
    Route::get('badges/latest', fn (Request $request) => Badge::resultsForApi($request->search));

    // Articles Categories API Route
    Route::get('articles/categories', fn () => ArticleCategory::all());

    // Articles Categories API Route
    Route::get('articles', fn (Request $request) => Article::resultsFromApi($request->search, $request->category));

});

Route::get('radio/live-dj', function (Request $request) {
    $statusUrl = config('radio.live_status_url');
    $stationId = (int) config('radio.station_id', 402);
    $metadataUrls = array_values(array_filter([
        config('radio.metadata_url', 'https://a1.asurahosting.com/public/ravr'),
        'https://a1.asurahosting.com/api/nowplaying/' . $stationId,
        'https://a1.asurahosting.com/api/nowplaying',
        config('radio.metadata_fallback_url', 'https://a1.asurahosting.com/api/nowplaying/402'),
        'https://a1.asurahosting.com/station/402/reports/timeline',
        'https://a1.asurahosting.com/public/ravr',
    ]));

    $fallbackName = (string) config('radio.fallback_name', 'AutoDJ (Habble)');
    $fallbackHabboUser = (string) config('radio.fallback_habbo_user', 'Habble');
    $fallbackArtist = 'Loading...';
    $fallbackTitle = 'Loading...';

    $extractData = static function (array $payload) use ($stationId): array {
        $isList = static function (array $value): bool {
            if ($value === []) {
                return true;
            }

            return array_keys($value) === range(0, count($value) - 1);
        };

        if (isset($payload['timeline']) && is_array($payload['timeline']) && !empty($payload['timeline'])) {
            $last = end($payload['timeline']);
            $payload = is_array($last) ? $last : $payload;
        }

        if (isset($payload['data']) && is_array($payload['data']) && !empty($payload['data'])) {
            $last = end($payload['data']);
            $payload = is_array($last) ? $last : $payload;
        }

        if ($isList($payload) && isset($payload[0]) && is_array($payload[0])) {
            $stationMatch = null;
            $firstPlayable = null;

            foreach ($payload as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $itemStationId = (int) data_get($item, 'station.id', 0);
                if ($stationId > 0 && $itemStationId === $stationId) {
                    $stationMatch = $item;
                    break;
                }

                if ($firstPlayable === null) {
                    $hasSongText = trim((string) data_get($item, 'now_playing.song.text', '')) !== '';
                    $hasArtist = trim((string) data_get($item, 'now_playing.song.artist', '')) !== '';
                    $hasTitle = trim((string) data_get($item, 'now_playing.song.title', '')) !== '';

                    if ($hasSongText || $hasArtist || $hasTitle) {
                        $firstPlayable = $item;
                    }
                }
            }

            if (is_array($stationMatch)) {
                $payload = $stationMatch;
            } elseif (is_array($firstPlayable)) {
                $payload = $firstPlayable;
            } else {
                $payload = $payload[0];
            }
        }

        $trackArtist = trim((string) data_get(
            $payload,
            'artist',
            data_get(
                $payload,
                'track_artist',
                data_get($payload, 'song.artist', data_get($payload, 'now_playing.song.artist', ''))
            )
        ));
        $trackTitle = trim((string) data_get(
            $payload,
            'title',
            data_get(
                $payload,
                'track_title',
                data_get($payload, 'song.title', data_get($payload, 'now_playing.song.title', ''))
            )
        ));
        $track = trim((string) data_get(
            $payload,
            'track',
            data_get($payload, 'song.text', data_get($payload, 'song', data_get($payload, 'now_playing.song.text', '')))
        ));

        $listeners = (int) data_get(
            $payload,
            'listeners',
            data_get(
                $payload,
                'listener_count',
                data_get(
                    $payload,
                    'current_listeners',
                    data_get(
                        $payload,
                        'listeners.current',
                        data_get($payload, 'listeners.total', data_get($payload, 'now_playing.listeners.current', 0))
                    )
                )
            )
        );

        if ($track === '' && ($trackArtist !== '' || $trackTitle !== '')) {
            $track = trim($trackArtist . ($trackArtist !== '' && $trackTitle !== '' ? ' - ' : '') . $trackTitle);
        }

        return [
            'live' => (bool) data_get($payload, 'live', data_get($payload, 'is_live', data_get($payload, 'live.is_live', data_get($payload, 'connected', data_get($payload, 'online', false))))),
            'name' => trim((string) data_get($payload, 'name', data_get($payload, 'dj_name', data_get($payload, 'live.streamer_name', data_get($payload, 'username', data_get($payload, 'habbo_name', '')))))),
            'habbo_name' => trim((string) data_get($payload, 'habbo_name', data_get($payload, 'name', ''))),
            'artist' => $trackArtist,
            'title' => $trackTitle,
            'track' => $track,
            'listeners' => max($listeners, 0),
        ];
    };

    $parseRawMetadata = static function (string $body): array {
        $parsed = [
            'artist' => '',
            'title' => '',
            'track' => '',
            'listeners' => 0,
        ];

        $raw = trim($body);
        if ($raw === '') {
            return $parsed;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/songtitle\\s*=\\s*[\'"]([^\'"]+)[\'"]/i', $raw, $matches)) {
            $parsed['track'] = trim((string) $matches[1]);
        }

        if ($parsed['track'] === '' && preg_match('/StreamTitle=\\s*[\'"]([^\'"]+)[\'"]/i', $raw, $matches)) {
            $parsed['track'] = trim((string) $matches[1]);
        }

        if (preg_match('/currentlisteners\\s*=\\s*[\'"]?(\\d+)/i', $raw, $matches)) {
            $parsed['listeners'] = max(0, (int) $matches[1]);
        }

        // HTML fallback (AzuraCast public pages/widgets).
        if ($parsed['title'] === '' && preg_match('/now-playing-title[^>]*>(.*?)<\\/h4>/is', $raw, $matches)) {
            $parsed['title'] = trim(html_entity_decode(strip_tags((string) $matches[1]), ENT_QUOTES, 'UTF-8'));
        }

        if ($parsed['artist'] === '' && preg_match('/now-playing-artist[^>]*>(.*?)<\\/h5>/is', $raw, $matches)) {
            $parsed['artist'] = trim(html_entity_decode(strip_tags((string) $matches[1]), ENT_QUOTES, 'UTF-8'));
        }

        if ($parsed['track'] === '' && ($parsed['artist'] !== '' || $parsed['title'] !== '')) {
            $parsed['track'] = trim(
                $parsed['artist'] . ($parsed['artist'] !== '' && $parsed['title'] !== '' ? ' - ' : '') . $parsed['title']
            );
        }

        if (
            $parsed['track'] === '' &&
            isset($raw[0]) &&
            $raw[0] === '<' &&
            function_exists('simplexml_load_string')
        ) {
            $xml = @simplexml_load_string($raw);

            if ($xml !== false) {
                $sources = [];

                if (isset($xml->source)) {
                    $sources[] = $xml->source;
                }

                if (isset($xml->icestats) && isset($xml->icestats->source)) {
                    $sources[] = $xml->icestats->source;
                }

                foreach ($sources as $source) {
                    foreach ($source as $node) {
                        $nodeArtist = trim((string) ($node->artist ?? ''));
                        $nodeTitle = trim((string) ($node->title ?? ''));
                        $nodeTrack = trim((string) ($node->songtitle ?? $node->title ?? ''));
                        $nodeListeners = max(
                            0,
                            (int) ($node->listeners ?? $node->currentlisteners ?? 0)
                        );

                        if ($parsed['artist'] === '' && $nodeArtist !== '') {
                            $parsed['artist'] = $nodeArtist;
                        }
                        if ($parsed['title'] === '' && $nodeTitle !== '') {
                            $parsed['title'] = $nodeTitle;
                        }
                        if ($parsed['track'] === '' && $nodeTrack !== '') {
                            $parsed['track'] = $nodeTrack;
                        }
                        if ($nodeListeners >= 0) {
                            $parsed['listeners'] = $nodeListeners;
                        }

                        if ($parsed['track'] !== '' || ($parsed['artist'] !== '' && $parsed['title'] !== '')) {
                            break 2;
                        }
                    }
                }
            }
        }

        if ($parsed['track'] !== '' && ($parsed['artist'] === '' || $parsed['title'] === '')) {
            $parts = explode(' - ', $parsed['track'], 2);
            if (count($parts) === 2) {
                $parsed['artist'] = trim($parts[0]);
                $parsed['title'] = trim($parts[1]);
            }
        }

        return $parsed;
    };

    $result = [
        'live' => false,
        'name' => $fallbackName,
        'habbo_name' => $fallbackHabboUser,
        'artist' => $fallbackArtist,
        'title' => $fallbackTitle,
        'track' => $fallbackArtist . ' - ' . $fallbackTitle,
        'listeners' => 0,
    ];

    $debug = (bool) $request->query('debug', false);
    $debugInfo = [
        'station_id' => $stationId,
        'metadata_urls' => $metadataUrls,
        'attempts' => [],
    ];

    // 1) Try metadata endpoint first (AutoDJ / now playing metadata).
    if (!empty($metadataUrls)) {
        foreach ($metadataUrls as $metadataUrl) {
            try {
                $metadataResponse = Http::connectTimeout(5)
                    ->timeout(8)
                    ->retry(2, 200)
                    ->acceptJson()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0',
                        'Accept' => 'text/html,application/json,text/plain,*/*',
                    ])
                    ->get($metadataUrl);

                if ($debug) {
                    $debugInfo['attempts'][] = [
                        'url' => $metadataUrl,
                        'status' => $metadataResponse->status(),
                        'ok' => $metadataResponse->ok(),
                        'content_type' => $metadataResponse->header('Content-Type'),
                        'body_head' => substr((string) $metadataResponse->body(), 0, 200),
                    ];
                }

                if ($metadataResponse->ok()) {
                    $metadataPayload = $metadataResponse->json();
                    if (is_array($metadataPayload)) {
                        $metadataData = $extractData($metadataPayload);
                    } else {
                        $metadataData = $parseRawMetadata((string) $metadataResponse->body());
                    }

                    if ($metadataData['artist'] !== '') {
                        $result['artist'] = $metadataData['artist'];
                    }
                    if ($metadataData['title'] !== '') {
                        $result['title'] = $metadataData['title'];
                    }
                    if ($metadataData['track'] !== '') {
                        $result['track'] = $metadataData['track'];
                    }
                    if ($metadataData['listeners'] >= 0) {
                        $result['listeners'] = $metadataData['listeners'];
                    }

                    // If we already have a valid track, no need to keep trying fallback URLs.
                    if ($result['artist'] !== 'Loading...' || $result['title'] !== 'Loading...') {
                        break;
                    }
                }
            } catch (\Throwable $exception) {
                if ($debug) {
                    $debugInfo['attempts'][] = [
                        'url' => $metadataUrl,
                        'error' => $exception->getMessage(),
                    ];
                }
                // Try next metadata URL.
            }
        }
    }

    // 2) If live endpoint is available, override with live DJ state and metadata if present.
    if ($statusUrl) {
        try {
            $liveResponse = Http::timeout(8)->acceptJson()->get($statusUrl);
            if ($liveResponse->ok()) {
                $livePayload = $liveResponse->json();
                if (!is_array($livePayload)) {
                    $livePayload = $parseRawMetadata((string) $liveResponse->body());
                }

                $liveData = $extractData((array) $livePayload);

                if ($liveData['artist'] !== '') {
                    $result['artist'] = $liveData['artist'];
                }
                if ($liveData['title'] !== '') {
                    $result['title'] = $liveData['title'];
                }
                if ($liveData['track'] !== '') {
                    $result['track'] = $liveData['track'];
                }
                if ($liveData['listeners'] >= 0) {
                    $result['listeners'] = $liveData['listeners'];
                }

                if ($liveData['live'] && $liveData['name'] !== '') {
                    $result['live'] = true;
                    $result['name'] = $liveData['name'];
                    $result['habbo_name'] = $liveData['habbo_name'] !== '' ? $liveData['habbo_name'] : $liveData['name'];
                }
            }
        } catch (\Throwable $exception) {
            // Silent fallback.
        }
    }

    if ($result['artist'] === '') {
        $result['artist'] = $fallbackArtist;
    }
    if ($result['title'] === '') {
        $result['title'] = $fallbackTitle;
    }
    if ($result['track'] === '') {
        $result['track'] = $result['artist'] . ' - ' . $result['title'];
    }

    if ($debug) {
        $result['_debug'] = $debugInfo;
    }

    return response()
        ->json($result)
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->name('radio.live-dj');
