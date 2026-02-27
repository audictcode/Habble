<?php
declare(strict_types=1);

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (!is_file($autoloadPath)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Project Setup Required</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: #f4f6f8;
            color: #1f2a37;
        }
        .wrap {
            max-width: 720px;
            margin: 60px auto;
            background: #ffffff;
            border: 1px solid #d9e0e6;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
        }
        code {
            background: #eef2f7;
            border: 1px solid #d2dbe5;
            border-radius: 4px;
            padding: 2px 6px;
        }
        pre {
            background: #0f172a;
            color: #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <h1>Laravel dependencies are missing</h1>
        <p>The file <code>vendor/autoload.php</code> was not found, so the app cannot boot yet.</p>
        <p>Run these commands in <code>C:\xampp\htdocs</code>:</p>
        <pre>composer install
copy .env.example .env
php artisan key:generate</pre>
    </main>
</body>
</html>
<?php
    exit;
}

require __DIR__ . '/public/index.php';
