<?php
declare(strict_types=1);

$localFile = __DIR__ . '/config.local.php';
$local = is_file($localFile) ? require $localFile : [];
$local = is_array($local) ? $local : [];

return [
    'pepper_token' => (string)($local['pepper_token'] ?? getenv('PEPPER_TOKEN') ?: ''),
    'pepper_webhook_secret' => (string)($local['pepper_webhook_secret'] ?? getenv('PEPPER_WEBHOOK_SECRET') ?: ''),
    'pepper_product_hash' => 'puyx0gjeg1',
    'pepper_offer_hash' => 'cyatn',
    'success_url' => (string)($local['success_url'] ?? getenv('SUCCESS_URL') ?: ''),
    'public_base_url' => rtrim((string)($local['public_base_url'] ?? getenv('PUBLIC_BASE_URL') ?: ''), '/'),
    'api_base' => 'https://api.cloud.pepperpay.com.br/public/v1',
    'webhook_log' => __DIR__ . '/../storage/webhooks.log',
];
