# Integração Pepper — checkout próprio

Produto Pepper: **puyx0gjeg1**  
Nome no checkout: **Dramas Clube: Plano Vitalício**  
Preço inicial: **R$ 61,90**  
Método exposto no checkout: **PIX**  
Recorrência esperada: **semanal**

## Como o Offer Hash é escolhido

O backend consulta `GET /products/puyx0gjeg1` e procura automaticamente uma oferta que esteja:

- ativa (`status = 1`);
- com `price = 6190`;
- com `interval = weekly`.

O hash dessa oferta é então usado no `POST /transactions` com `payment_method = pix`. A documentação Pepper confirma que cada produto possui ofertas associadas e que a oferta tem seu próprio `hash`, `price`, `status` e `interval`.

**Importante:** o endpoint de consulta do produto não expõe no exemplo público os `payment_methods` da oferta. Portanto, o código garante que o checkout use PIX, mas a configuração de “somente PIX” deve estar correta na oferta criada dentro da Pepper.

## Arquivos

```text
/index.html
/api/create-pix.php
/api/transaction-status.php
/api/webhook.php
/api/health.php
/api/config.php
```

## Credenciais

Configure no ambiente do servidor:

`PEPPER_TOKEN`  
`PEPPER_WEBHOOK_SECRET`  
`PEPPER_PRODUCT_HASH=puyx0gjeg1`  
`PUBLIC_BASE_URL`  
`SUCCESS_URL` (opcional)

Nunca coloque o Token ou Webhook Secret no JavaScript do navegador.

## Recorrência

A Pepper documenta `interval: weekly` como intervalo válido para ofertas de assinatura. A primeira cobrança é criada pelo `POST /transactions` usando o `offer_hash` da oferta; os ciclos da assinatura ficam vinculados à oferta Pepper.
