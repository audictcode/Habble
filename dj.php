<?php
declare(strict_types=1);

$panelDir = __DIR__ . '/public/dj';

if (!is_dir($panelDir)) {
    http_response_code(404);
    exit('DJ panel not found.');
}

chdir($panelDir);
require $panelDir . '/dj.php';
