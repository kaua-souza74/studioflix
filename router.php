<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$blocked = preg_match('#^/(config|includes|database|storage)(/|$)#i', $requestPath) === 1;

if ($blocked) {
    http_response_code(404);
    echo 'Não encontrado.';
    exit;
}

if ($requestPath === '/') {
    require __DIR__ . '/index.php';
    exit;
}

$requestedFile = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $requestPath);
if (strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION)) === 'php' && is_file($requestedFile)) {
    require $requestedFile;
    exit;
}

if (is_file($requestedFile)) {
    return false;
}

http_response_code(404);
echo 'Não encontrado.';
