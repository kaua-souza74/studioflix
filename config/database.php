<?php
declare(strict_types=1);

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('STUDIOFLIX_DB_HOST') ?: '127.0.0.1';
    $database = getenv('STUDIOFLIX_DB_NAME') ?: 'studioflix';
    $username = getenv('STUDIOFLIX_DB_USER') ?: 'root';
    $password = getenv('STUDIOFLIX_DB_PASS') ?: '';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $database);
    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
