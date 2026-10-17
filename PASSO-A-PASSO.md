# Passo a passo: colocar o app na Hostgator

Thomaz Almeida · Sistema Clínico

Leva uns 15 minutos. Você não precisa editar nenhum arquivo.

---

## O que mudou nesta versão

| Antes | Agora |
|---|---|
| Os dados ficavam só no navegador. O que você anotava no celular não aparecia no computador. | Os dados ficam no servidor da Hostgator e sincronizam entre celular e computador. |
| A senha estava escrita no código, num GitHub público. | No primeiro acesso, o app pede para você **criar** a senha. Ela não fica escrita em lugar nenhum. |
| O `.htaccess` estava com o nome `download`, e o login podia ser pulado. | Proteção ativa: sem senha, ninguém vê nada. |
| Falta não entrava no financeiro. | Novo status **Falta cobrada**, que entra na cobrança. |
| — | Lista **Em aberto** com todas as pendências, botão **Cobrar no WhatsApp** (com sua chave Pix) e **Marcar tudo pago**. |
| — | **Recibo** pronto para imprimir/PDF, com CPF e valor por extenso (vale para o Imposto de Renda do paciente). |
| Mudar o valor da sessão alterava o histórico. | Cada sessão guarda o próprio valor. Reajustes não mexem no passado. |
| — | Backup automático diário no servidor (últimos 60 dias). |

---

## PASSO 1: Entrar no cPanel

1. Acesse https://financeiro.hostgator.com.br e faça login.
2. Clique no seu plano de hospedagem e depois em **cPanel**.

## PASSO 2: Conferir o SSL (cadeado)

1. No cPanel, procure **SSL/TLS Status** (ou "Let's Encrypt" / "AutoSSL").
2. Confira se o seu domínio `thomazalmeida.com` aparece com o cadeado verde.
3. Se não aparecer, clique em **Run AutoSSL** e espere uns minutos.

> O app obriga o uso de `https://`. Sem SSL ativo ele não abre. Isso é proposital: as anotações dos pacientes nunca devem trafegar sem criptografia.

## PASSO 3: Enviar o arquivo ZIP

1. No cPanel, abra o **Gerenciador de Arquivos**.
2. Entre na pasta `public_html`.
3. Se já existir uma pasta `app` da tentativa anterior, **apague** essa pasta (ou renomeie para `app-antigo`).
4. Clique em **Carregar** (Upload) e envie o arquivo **`clinica-app.zip`**.
5. Volte para `public_html`, clique com o botão direito no `clinica-app.zip` e escolha **Extract** (Extrair).
6. Isso cria a pasta `public_html/app` com tudo dentro. Pode apagar o `.zip` depois.

Confira se, dentro de `app`, existem os arquivos `.htaccess` (com ponto) e a pasta `dados`.
Se o `.htaccess` não aparecer: clique em **Configurações** (canto superior direito) e marque **Mostrar arquivos ocultos**.

## PASSO 4: Criar sua senha

1. Acesse **https://thomazalmeida.com/app**
2. Como é o primeiro acesso, o app pede para você **criar uma senha**.
   Use uma frase com números e símbolos, com pelo menos 8 caracteres. Exemplo de formato: `Divã-azul-2026!`
3. Pronto, você já está dentro.

> ⚠️ Faça o Passo 4 **logo depois** do Passo 3. Até a senha ser criada, quem abrir o endereço primeiro é quem define a senha.

## PASSO 5: Preencher suas Configurações

No menu, clique em **Configurações** e preencha nome, CPF, cidade e chave Pix. Isso aparece nos recibos e nas mensagens de cobrança.

## PASSO 6: Trazer os dados antigos (se você já tinha usado o app)

- Se você usou a versão antiga **neste mesmo navegador**, o app vai perguntar automaticamente se quer importar. É só clicar OK.
- Se usou em outro aparelho: abra a versão antiga lá, clique em **Backup JSON**, depois aqui use **Importar Backup**.

---

## No celular: instalar como app

**iPhone (Safari):**
1. Abra `https://thomazalmeida.com/app` e faça login.
2. Toque em **Compartilhar** (quadrado com seta) e depois em **Adicionar à Tela de Início**.

**Android (Chrome):**
1. Abra `https://thomazalmeida.com/app` e faça login.
2. Toque nos 3 pontinhos e depois em **Instalar app** (ou "Adicionar à tela inicial").

Vai aparecer o ícone dourado com Ψ. O login dura até 8 horas sem uso.

---

## Como usar no dia a dia

- **Depois da sessão:** abra o paciente, depois **Sessões**, mude o status para *Realizada* e escreva as anotações. Tudo salva sozinho enquanto você digita. O indicador no topo mostra **✓ Salvo**.
- **Falta:** escolha *Falta cobrada* (entra na cobrança) ou *Faltou (sem cobrança)*.
- **Recebeu:** clique em **Marcar Pago** na sessão e escolha a forma (Pix, dinheiro…).
- **Fim do mês:** vá em **Financeiro**. Na lista **Em aberto** estão todos que devem, com os botões **Cobrar no WhatsApp** e **Marcar tudo pago**.
- **Recibo:** no paciente, na aba **Dados**, use **Gerar recibo do mês** e salve como PDF ou imprima.
- **Valor diferente numa sessão** (desconto, valor social): edite o valor direto na sessão.

---

## Segurança e backup

- Os dados ficam em `public_html/app/dados/`, bloqueados para acesso pela internet.
- Todo dia que você usa o app, uma cópia é guardada em `dados/backups/` (últimos 60 dias).
- **Uma vez por mês**, clique em **Baixar Backup** e guarde o arquivo no seu computador ou num pendrive. Esse arquivo contém as anotações clínicas, então guarde com cuidado.
- **Esqueceu a senha?** No Gerenciador de Arquivos, apague o arquivo `app/dados/senha.php`. No próximo acesso o app pede para criar uma nova. Os dados dos pacientes **não** são apagados.
- Para trocar a senha: menu, depois **Trocar Senha**.

---

## Problemas comuns

**A página dá erro 403 ou "Forbidden"**
Confira se a pasta se chama `app` e se o `index.php` está dentro dela (e não dentro de `app/app`).

**Aparece "Não foi possível carregar os dados"**
Verifique a internet. Se continuar, confira se a pasta `app/dados` existe. Se não existir, crie a pasta pelo Gerenciador de Arquivos.

**O site não abre e fica redirecionando**
O SSL não está ativo. Volte ao Passo 2.

**Abri no celular e no computador ao mesmo tempo**
Pode. Se os dois editarem ao mesmo tempo, o app avisa e pergunta qual versão manter.

---

Suporte Hostgator: chat 24h em https://www.hostgator.com.br/suporte · 0800 722 8092
