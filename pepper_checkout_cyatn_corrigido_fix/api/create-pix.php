<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

function out(int $status, array $body): never {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(405, ['ok'=>false, 'error'=>'Método não permitido.']);
}

$config = require __DIR__ . '/config.php';

if ($config['pepper_token'] === '' || $config['pepper_token'] === 'COLE_SEU_NOVO_TOKEN_AQUI') {
    out(500, ['ok'=>false, 'error'=>'PEPPER_TOKEN não configurado. Preencha api/config.local.php no servidor.']);
}
if (!function_exists('curl_init')) {
    out(500, ['ok'=>false, 'error'=>'cURL do PHP não está habilitado no servidor.']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw ?: '', true);
if (!is_array($input)) out(400, ['ok'=>false, 'error'=>'JSON inválido.']);

$name = trim((string)($input['name'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$phone = preg_replace('/\D+/', '', (string)($input['phone_number'] ?? ''));
$document = preg_replace('/\D+/', '', (string)($input['document'] ?? ''));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(422, ['ok'=>false, 'error'=>'Nome e e-mail inválidos.']);
}
if (!in_array(strlen($phone), [10,11], true)) {
    out(422, ['ok'=>false, 'error'=>'Celular inválido. Informe DDD + número.']);
}
if (strlen($document) !== 11) {
    out(422, ['ok'=>false, 'error'=>'CPF inválido.']);
}

$payload = [
    // A documentação atual mostra este campo no corpo do exemplo além do Bearer.
    'api_token' => $config['pepper_token'],
    'amount' => 6190,
    'payment_method' => 'pix',
    'cart' => [[
        'offer_hash' => 'cyatn',
        'price' => 6190,
        'quantity' => 1,
        'operation_type' => 1,
        'title' => 'Dramas Clube: Plano Vitalício',
    ]],
    'installments' => 1,
    'customer' => [
        'name' => $name,
        'email' => $email,
        'phone_number' => $phone,
        'document' => $document,
    ],
    'tracking' => [
        'src' => $_SERVER['HTTP_REFERER'] ?? null,
    ],
    'webhook_url' => $config['public_base_url'] !== '' ? $config['public_base_url'] . '/api/webhook.php' : null,
];

$ch = curl_init($config['api_base'] . '/transactions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $config['pepper_token'],
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    out(502, ['ok'=>false, 'error'=>'Falha ao conectar à Pepper.', 'details'=>$curlError]);
}

$data = json_decode($response, true);
if (!is_array($data)) {
    out(502, ['ok'=>false, 'error'=>'A Pepper retornou uma resposta que não é JSON.', 'http_status'=>$status, 'raw'=>substr($response,0,1000)]);
}

if ($status < 200 || $status >= 300) {
    $msg = $data['message'] ?? ($data['errors'][0] ?? 'A Pepper recusou a criação do PIX.');
    out($status ?: 502, ['ok'=>false, 'error'=>$msg, 'http_status'=>$status, 'pepper'=>$data]);
}

echo json_encode([
    'ok' => true,
    'hash' => $data['hash'] ?? null,
    'payment_status' => $data['payment_status'] ?? null,
    'pix_qr_code' => $data['pix']['pix_qr_code'] ?? null,
    'pix_url' => $data['pix']['pix_url'] ?? null,
    'qr_code_base64' => $data['pix']['qr_code_base64'] ?? null,
    'offer' => $data['offer'] ?? null,
    'product' => $data['product'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
