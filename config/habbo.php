<?php

return [
    'hotels' => ['es', 'com', 'com.br', 'fr', 'de', 'it', 'nl', 'fi', 'tr'],
    'verification_prefix' => 'HLE',
    'http_timeout' => (int) env('HABBO_HTTP_TIMEOUT', 10),
    'verify_ssl' => env('HABBO_VERIFY_SSL', true),
];
