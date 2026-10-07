# Checkout Pepper — Dramas Clube

Produto: `puyx0gjeg1`
Oferta/plano: `cyatn`
Valor: R$ 61,90
Método: PIX
Recorrência: semanal, conforme configuração da oferta Pepper.

## Corrigir o erro "Não foi possível criar o PIX"

Em hospedagens PHP comuns, variáveis como `PEPPER_TOKEN` não ficam disponíveis automaticamente. Por isso este pacote usa `api/config.local.php`.

1. Envie a pasta inteira para uma hospedagem que execute PHP.
2. Abra `api/config.local.php` no servidor.
3. Coloque o novo Token e Webhook Secret da Pepper nos campos indicados.
4. Troque `https://SEU-DOMINIO-AQUI` pelo endereço real do checkout.
5. Acesse `https://seu-dominio/api/health.php` e confirme `token_configured: true` e `curl: true`.
6. Só depois teste `GERAR PIX`.

O `index.html` chama `./api/create-pix.php`. Portanto, publicar apenas o HTML ou usar hospedagem estática não executará a API.

Nunca coloque o Token/Webhook Secret dentro do JavaScript do navegador.
