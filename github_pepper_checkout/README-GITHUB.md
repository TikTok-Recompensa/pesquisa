# Checkout Pepper no GitHub Pages

O GitHub Pages hospeda o `index.html`, mas nao executa PHP. Portanto o frontend precisa chamar um backend externo (por exemplo, Hostinger ou Cloudflare Worker).

## 1. Configure o backend

Na sua Hostinger, publique a pasta `api/` do pacote Pepper e configure `api/config.local.php` com o Token e Webhook Secret da Pepper.

## 2. Configure este index.html

Abra o `index.html` e altere:

```js
apiBase: 'https://SEU-BACKEND-HOSTINGER/api',
```

para a URL real do backend, por exemplo:

```js
apiBase: 'https://meudominio.com.br/api',
```

Sem isso, o GitHub Pages nao consegue chamar `create-pix.php`.

## 3. GitHub Pages

Suba apenas o `index.html` (e seus assets) no repositorio. O checkout continua no endereco do GitHub Pages.

## 4. Credenciais

Nao coloque o Token Pepper ou Webhook Secret no `index.html`. Eles devem permanecer no servidor backend.
