<?php

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

use App\Api\ApiRouter;

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['status' => false, 'error' => $errstr]);
    exit;
});

set_exception_handler(function(Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'error' => $e->getMessage()]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $router = new ApiRouter();
    $result = $router->dispatch();
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'error' => $e->getMessage()]);
}
