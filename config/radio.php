<?php

return [
    'stream_url' => env('RADIO_STREAM_URL', 'https://a1.asurahosting.com/listen/ravr/radio.mp3'),
    'station_id' => (int) env('RADIO_STATION_ID', 402),
    'habbo_hotel' => env('RADIO_HABBO_HOTEL', 'es'),
    'live_status_url' => env('RADIO_LIVE_STATUS_URL', null),
    'metadata_url' => env('RADIO_METADATA_URL', 'https://a1.asurahosting.com/public/ravr'),
    'metadata_fallback_url' => env('RADIO_METADATA_FALLBACK_URL', 'https://a1.asurahosting.com/api/nowplaying/402'),
    'fallback_name' => env('RADIO_FALLBACK_NAME', 'Habble'),
    'fallback_habbo_user' => env('RADIO_FALLBACK_HABBO_USER', 'Habble'),
    'loading_text' => env('RADIO_LOADING_TEXT', 'Loading...'),
];
