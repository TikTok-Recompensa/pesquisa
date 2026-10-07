<?php
declare(strict_types=1);

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

// The public Pepper docs supplied do not document a signature/header format for
// validating the Webhook Secret, so this endpoint does not invent one.
// Keep the secret server-side and add the exact documented verification header
// once Pepper provides it for the webhook integration used by your account.

$entry = [
    'received_at' => gmdate('c'),
    'event' => is_array($payload) ? ($payload['event'] ?? null) : null,
    'payload' => $payload,
];

@file_put_contents(
    $config['webhook_log'],
    json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

echo json_encode(['received' => true]);
