<?php
// Credenciais e configurações devem ficar somente no servidor.
return [
    'pepper_token' => getenv('PEPPER_TOKEN') ?: 'SEU_TOKEN_AQUI',
    'pepper_webhook_secret' => getenv('PEPPER_WEBHOOK_SECRET') ?: 'SEU_WEBHOOK_SECRET_AQUI',
    // Hash do PRODUTO Pepper informado pelo usuário.
    'pepper_product_hash' => getenv('PEPPER_PRODUCT_HASH') ?: 'puyx0gjeg1',
    'pepper_offer_hash' => getenv('PEPPER_OFFER_HASH') ?: 'cyatn',
    'success_url' => getenv('SUCCESS_URL') ?: '',
    'api_base' => 'https://api.cloud.pepperpay.com.br/public/v1',
    'webhook_log' => __DIR__ . '/../storage/webhooks.log',
];
