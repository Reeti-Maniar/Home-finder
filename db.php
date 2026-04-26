<?php
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'homefinder');
define('DB_USER', 'root');
define('DB_PASS', '');
define('ADMIN_SECRET', 'HOMEFINDER_ADMIN_2024');
define('APP_BASE_PATH', '/homefinder');

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}

