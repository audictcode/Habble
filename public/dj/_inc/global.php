<?php
ini_set('display_errors', '1'); // Set as 1 for debug

$Mysql = array("localhost", "root", "hello", "deejay"); // Fallback: hostname, username, password, database
$Radio = array("109.73.71.126", "8000"); // Change to your radio ip and port format-> IP, Port
$Salt = "6n3547Ko8I77414vxF8o167V7DE292uS"; // Change this for password hashing security

function djFindEnvFile() {
    $candidates = array(
        dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.env',
        dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env',
        dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . '.env',
    );

    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '' && file_exists($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function djParseEnvFile($path) {
    $values = array();

    if (!is_string($path) || $path === '' || !file_exists($path)) {
        return $values;
    }

    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return $values;
    }

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if ($key === '') {
            continue;
        }

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        $values[$key] = $value;
    }

    return $values;
}

function djEnsureTables() {
    // Legacy DJ panel table used in dashboard.
    @mysql_query(
        "CREATE TABLE IF NOT EXISTS home_news (
            ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            content MEDIUMTEXT NULL,
            author VARCHAR(255) NULL,
            date VARCHAR(50) NULL,
            PRIMARY KEY (ID)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    );

    // Optional compatibility table if warnings migration was not executed.
    @mysql_query(
        "CREATE TABLE IF NOT EXISTS users_warnings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            admin_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            reason VARCHAR(255) NULL,
            warning_score INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY users_warnings_user_id_index (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    );
}

$envPath = djFindEnvFile();
$env = djParseEnvFile($envPath);

if (isset($env['DB_HOST']) && trim((string) $env['DB_HOST']) !== '') {
    $dbHost = trim((string) $env['DB_HOST']);
    $dbPort = isset($env['DB_PORT']) ? trim((string) $env['DB_PORT']) : '';

    if ($dbPort !== '' && strpos($dbHost, ':') === false) {
        $dbHost .= ':' . $dbPort;
    }

    $Mysql[0] = $dbHost;
}

if (isset($env['DB_USERNAME']) && trim((string) $env['DB_USERNAME']) !== '') {
    $Mysql[1] = trim((string) $env['DB_USERNAME']);
}

if (array_key_exists('DB_PASSWORD', $env)) {
    $Mysql[2] = (string) $env['DB_PASSWORD'];
}

if (isset($env['DB_DATABASE']) && trim((string) $env['DB_DATABASE']) !== '') {
    $Mysql[3] = trim((string) $env['DB_DATABASE']);
}

if (isset($env['DJ_SALT']) && trim((string) $env['DJ_SALT']) !== '') {
    $Salt = trim((string) $env['DJ_SALT']);
} elseif (isset($env['APP_KEY']) && trim((string) $env['APP_KEY']) !== '') {
    // Stable fallback salt derived from APP_KEY.
    $Salt = substr(hash('sha256', (string) $env['APP_KEY']), 0, 32);
}

// DO NOT EDIT BELOW //

include_once('_inc/db.inc.php');
include_once('_inc/core.inc.php');
include_once('_inc/session.inc.php');
include_once('_inc/users.inc.php');

$DB = new db($Mysql[0], $Mysql[1], $Mysql[2], $Mysql[3]);
djEnsureTables();
$Core = new core($Radio);
$Session = new session();
$Users = new users($DB, $Session, $Salt);
