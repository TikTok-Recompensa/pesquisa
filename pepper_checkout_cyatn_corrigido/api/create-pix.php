<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

if ($config['pepper_token'] === 'SEU_TOKEN_AQUI' || $config['pepper_product_hash'] === '') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Configure PEPPER_TOKEN no servidor.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido.']);
    exit;
}

$name = trim((string)($input['name'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$phone = preg_replace('/\D+/', '', (string)($input['phone_number'] ?? ''));
$document = preg_replace('/\D+/', '', (string)($input['document'] ?? ''));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $document === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Preencha nome, e-mail, celular e CPF/CNPJ corretamente.']);
    exit;
}

// O plano/oferta informado pelo usuário é o plano Pepper cyatn.
$offerHash = (string)$config['pepper_offer_hash'];
if ($offerHash === '') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Configure PEPPER_OFFER_HASH no servidor.']);
    exit;
}

$payload = [
    'amount' => 6190,
    'payment_method' => 'pix',
    'cart' => [[
        'offer_hash' => $offerHash,
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
    'webhook_url' => rtrim((string)(getenv('PUBLIC_BASE_URL') ?: ''), '/') . '/api/webhook.php',
];

if ($payload['webhook_url'] === '/api/webhook.php') {
    unset($payload['webhook_url']);
}

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
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Falha ao conectar à Pepper.', 'details' => $curlError]);
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
    echo json_encode(['ok' => false, 'error' => $data['message'] ?? 'A Pepper recusou a criação do PIX.', 'pepper' => $data]);
    exit;
}

echo json_encode([
    'ok' => true,
    'hash' => $data['hash'] ?? null,
    'transaction' => $data['transaction'] ?? null,
    'payment_status' => $data['payment_status'] ?? null,
    'pix_qr_code' => $data['pix']['pix_qr_code'] ?? null,
    'pix_url' => $data['pix']['pix_url'] ?? null,
    'qr_code_base64' => $data['pix']['qr_code_base64'] ?? null,
    'success_url' => $config['success_url'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
