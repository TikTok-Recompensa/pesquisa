<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
$curlLoaded = function_exists('curl_init');
$tokenConfigured = $config['pepper_token'] !== '' && $config['pepper_token'] !== 'COLE_SEU_NOVO_TOKEN_AQUI';
$publicConfigured = $config['public_base_url'] !== '' && strpos($config['public_base_url'], 'SEU-DOMINIO-AQUI') === false;

$errors = [];
if (!$curlLoaded) $errors[] = 'A extensão cURL do PHP não está habilitada.';
if (!$tokenConfigured) $errors[] = 'PEPPER_TOKEN não está configurado em api/config.local.php (ou variável de ambiente).';
if (!$publicConfigured) $errors[] = 'PUBLIC_BASE_URL não está configurado; o webhook pode não ser enviado.';

echo json_encode([
  'ok' => count(array_filter($errors, fn($e) => str_contains($e, 'PEPPER_TOKEN') || str_contains($e, 'cURL'))) === 0,
  'php' => PHP_VERSION,
  'curl' => $curlLoaded,
  'token_configured' => $tokenConfigured,
  'public_base_url_configured' => $publicConfigured,
  'product_hash' => $config['pepper_product_hash'],
  'offer_hash' => $config['pepper_offer_hash'],
  'errors' => $errors,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
