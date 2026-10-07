<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

$hash = trim((string)($_GET['hash'] ?? ''));
if ($hash === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Hash da transação ausente.']);
    exit;
}

$url = $config['api_base'] . '/transactions/' . rawurlencode($hash);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $config['pepper_token'],
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 20,
]);
$response = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Falha ao consultar a Pepper.']);
    exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Resposta inválida da Pepper.']);
    exit;
}

if ($status < 200 || $status >= 300) {
    http_response_code($status ?: 502);
    echo json_encode(['ok' => false, 'pepper' => $data]);
    exit;
}

echo json_encode([
    'ok' => true,
    'payment_status' => $data['payment_status'] ?? $data['status'] ?? null,
    'pepper' => $data,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
