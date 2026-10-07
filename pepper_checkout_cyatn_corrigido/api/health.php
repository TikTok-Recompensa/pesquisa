<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);
echo json_encode([
  'ok' => true,
  'php' => PHP_VERSION,
  'message' => 'Backend Pepper acessível.'
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
