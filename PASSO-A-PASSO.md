# PASSO A PASSO — Hospedar o App na Hostgator
# Thomaz Almeida · Sistema Clínico
# =====================================================

## ESTRUTURA DE ARQUIVOS (o que você vai fazer upload)

```
app/                    ← pasta que você cria no servidor
├── index.php           ← tela de LOGIN (senha aqui)
├── app.php             ← proteção de sessão
├── app.html            ← o aplicativo em si
├── sair.php            ← botão de logout
└── .htaccess           ← bloqueio de acesso direto
```

---

## PASSO 1 — Definir sua senha

Abra o arquivo `index.php` em qualquer editor de texto
(Bloco de Notas, TextEdit, VS Code)

Procure a linha:
```
$SENHA = 'thomaz2025';
```

Troque `thomaz2025` pela senha que você quiser usar.
Use algo forte: letras + números + símbolo.
Exemplo: `Thom@z2024!`

Salve o arquivo.

---

## PASSO 2 — Acessar o cPanel da Hostgator

1. Acesse: https://www.hostgator.com.br
2. Clique em "Minha Conta" → "Acessar cPanel"
3. Faça login com seu usuário e senha da Hostgator

---

## PASSO 3 — Criar a pasta do app

Dentro do cPanel:

1. Clique em **"Gerenciador de Arquivos"**
2. Navegue até a pasta `public_html`
3. Clique em **"Nova Pasta"**
4. Digite o nome: `app`
   (o endereço final vai ser: `thomazalmeida.com/app`)
5. Clique em "Criar Nova Pasta"

---

## PASSO 4 — Fazer upload dos arquivos

Ainda no Gerenciador de Arquivos:

1. Abra a pasta `app` que você acabou de criar
2. Clique no botão **"Upload"** (no topo)
3. Faça upload dos 5 arquivos:
   - `index.php`
   - `app.php`
   - `app.html`
   - `sair.php`
   - `.htaccess`

⚠️ ATENÇÃO: O arquivo `.htaccess` começa com ponto.
No Windows pode ficar oculto. Se não aparecer, ative
"Mostrar arquivos ocultos" no Gerenciador de Arquivos
do cPanel (opção no topo direito).

---

## PASSO 5 — Testar

Acesse no navegador:
```
https://thomazalmeida.com/app
```

Você deve ver a tela de login com cadeado.
Digite sua senha → você entra no app.

Se outra pessoa acessar o mesmo link sem a senha → vê
apenas a tela de login e não consegue entrar.

---

## PASSO 6 — Ativar HTTPS (SSL gratuito)

Isso garante que sua senha e dados dos pacientes
trafeguem criptografados.

1. No cPanel → procure **"SSL/TLS"** ou **"Let's Encrypt"**
2. Selecione seu domínio `thomazalmeida.com`
3. Clique em "Instalar" ou "Ativar"
4. Aguarde 5 minutos

Depois de ativado, abra o `.htaccess` e remova o `#`
das últimas 3 linhas (que forçam o HTTPS).

---

## COMO FUNCIONA A SEGURANÇA

| Situação | O que acontece |
|----------|---------------|
| Qualquer pessoa acessa thomazalmeida.com/app | Vê só a tela de login |
| Alguém tenta acessar thomazalmeida.com/app/app.html diretamente | Bloqueado (403 Proibido) |
| Você entra com a senha correta | Acessa o app normalmente |
| Você fecha o navegador | Sessão dura até 8 horas |
| Fica 8h sem usar | Faz logout automático |
| Senha errada | Espera 1 segundo (dificulta tentativas automáticas) |

---

## NO CELULAR — Como salvar como atalho (sem precisar de senha toda vez)

No iPhone (Safari):
1. Acesse thomazalmeida.com/app
2. Faça login uma vez
3. Toque no ícone de compartilhar (quadrado com seta)
4. "Adicionar à Tela de Início"
5. Nomeie "Clínica" e confirme

No Android (Chrome):
1. Acesse thomazalmeida.com/app
2. Faça login uma vez
3. Menu (3 pontos) → "Adicionar à tela inicial"
4. Confirme

A partir daí, o ícone na tela inicial abre direto o app.
A sessão dura 8 horas — você só precisará logar novamente
se ficar mais de 8 horas sem usar.

---

## PROBLEMAS COMUNS

**"Tela em branco após o login"**
→ Verifique se o app.html foi enviado corretamente.
→ Confira se o arquivo app.php está na mesma pasta.

**"Erro 403 ao acessar /app"**
→ O .htaccess pode estar mal configurado.
→ Verifique se foi enviado corretamente (começa com ponto).

**"Página não encontrada (404)"**
→ Confirme que os arquivos estão dentro de public_html/app/
→ O endereço correto é thomazalmeida.com/app (sem barra final)

**"Não consigo ver o .htaccess no upload"**
→ No Gerenciador de Arquivos do cPanel, clique em
  "Configurações" (canto superior direito) e ative
  "Mostrar arquivos ocultos (dotfiles)"

---

## SUPORTE HOSTGATOR
Chat 24h: https://www.hostgator.com.br/suporte
Telefone: 0800 722 8092
