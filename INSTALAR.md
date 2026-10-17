# Como instalar o Sistema Clínico no seu site

Guia para colocar o app no **seu próprio domínio**, com **sua senha** e **seus pacientes**. Ninguém mais vê os seus dados, nem quem já usa o app em outro site.

**Tempo:** cerca de 20 minutos. Você não precisa saber programar nem editar nenhum arquivo.

**Você vai precisar de:**
- uma hospedagem com cPanel (por exemplo, Hostgator) com o seu domínio;
- o arquivo **`clinica-app.zip`**, que veio junto com este guia.

---

## O que o app faz

- **Prontuário:** ficha do paciente, anamnese, plano terapêutico e anotações de cada sessão (salvas enquanto você digita).
- **Agenda:** sessões agendadas, mensagem pronta de confirmação pelo WhatsApp, link do Meet.
- **Financeiro:** pago ou pendente, falta cobrada, lista de quem está em aberto, cobrança pelo WhatsApp com sua chave Pix.
- **Recibo** pronto para imprimir ou PDF, com CPF e valor por extenso (vale para o Imposto de Renda do paciente).
- **Celular e computador sincronizados**, com backup automático todo dia.
- **Google Agenda** (opcional): as sessões aparecem sozinhas na sua agenda, com lembretes no celular.

---

## PASSO 1: Entrar no cPanel

1. Entre na área de cliente da sua hospedagem. Na Hostgator: https://cliente.hostgator.com.br
2. Clique em **cPanel**.

## PASSO 2: Conferir o cadeado (SSL)

1. No cPanel, procure **"SSL/TLS Status"**.
2. Veja se o seu domínio aparece com **cadeado verde**.
3. Se não aparecer, clique em **"Run AutoSSL"** e espere uns 10 minutos.

> O app só funciona com o cadeado (`https://`). Isso é proposital: anotações de pacientes nunca podem trafegar sem proteção.

## PASSO 3: Descobrir a pasta do seu domínio

1. No cPanel, clique em **"Domínios"**.
2. Encontre o seu domínio na lista e anote o que aparece em **"Raiz do documento"** (Document Root).
   - Se for o domínio principal da conta, normalmente é **`public_html`**.
   - Se for um domínio adicional, costuma ser uma pasta com o nome dele, por exemplo **`seudominio.com.br`**.

## PASSO 4: Enviar o app

1. No cPanel, abra o **"Gerenciador de Arquivos"**.
2. Entre na pasta que você anotou no Passo 3.
3. Clique em **"Carregar"** (Upload) e envie o arquivo **`clinica-app.zip`**.
4. Volte para a pasta, clique com o botão direito no `clinica-app.zip` e escolha **"Extract"** (Extrair).
5. Vai aparecer uma pasta chamada **`app`**. Pode apagar o `.zip`.

Confira se dentro de `app` existem o arquivo **`.htaccess`** (começa com ponto) e a pasta **`dados`**. Se o `.htaccess` não aparecer, clique em **Configurações** (canto superior direito do Gerenciador) e marque **"Mostrar arquivos ocultos"**.

## PASSO 5: Criar sua senha (faça logo em seguida!)

1. Abra no navegador: **`https://seudominio.com.br/app`** (troque pelo seu domínio).
2. Vai aparecer a tela **"Primeiro acesso"**. Crie a sua senha, com pelo menos 8 caracteres, números e símbolos.
3. Pronto, você já está dentro do app.

> ⚠️ Faça este passo **logo depois** do Passo 4. Enquanto a senha não existir, quem abrir o endereço primeiro é quem cria a senha.

## PASSO 6: Preencher seus dados

No menu do app, clique em **Configurações** e preencha:
- **Nome profissional** e **Título** (ex.: "Psicólogo", "Psicanalista");
- **CPF**, **cidade** e **chave Pix**.

Esses dados aparecem no app, nos recibos e nas mensagens de WhatsApp.

---

## No celular: instalar como aplicativo

**iPhone (Safari):** abra o endereço, faça login, toque em **Compartilhar** (quadrado com seta) e depois em **"Adicionar à Tela de Início"**.

**Android (Chrome):** abra o endereço, faça login, toque nos **3 pontinhos** e depois em **"Instalar app"** (ou "Adicionar à tela inicial").

Vai aparecer um ícone dourado com o símbolo Ψ.

---

## Google Agenda com lembretes (opcional)

Assim as sessões aparecem sozinhas no seu Google Agenda, com aviso no celular **1 hora** e **10 minutos** antes. As anotações clínicas **nunca** vão para o Google, só nome, data, horário e link do Meet.

1. No app, vá em **Configurações**, procure o cartão **"Google Agenda"** e clique em **"Gerar programa do Google Agenda"**.
2. Clique em **"Copiar programa"**.
3. Abra **https://script.google.com/create** (entre com a sua conta Google).
4. Clique na área do código, aperte **Ctrl+A** e depois **Ctrl+V** para colar.
5. Aperte **Ctrl+S** para salvar.
6. No topo, confira se está escolhida a função **"instalar"** e clique em **"Executar"**.
7. O Google vai pedir autorização:
   - clique em **"Revisar permissões"** e escolha sua conta;
   - na tela **"O Google não verificou este app"**, clique em **"Avançado"** (embaixo, à esquerda) e depois em **"Acessar … (não seguro)"**. É normal: o programa é seu e só acessa a sua própria agenda;
   - marque **"Selecionar tudo"** e clique em **"Continuar"**.
8. Clique em **"Executar"** de novo. Embaixo deve aparecer **"Execução concluída"**.

Pronto: no Google Agenda vai aparecer uma agenda nova, **"Clínica – Atendimentos"**. Ela se atualiza sozinha a cada 15 minutos.

> Se a janela de autorização não abrir, verifique se o navegador não bloqueou pop-ups (aparece um ícone na barra de endereço).

---

## No dia a dia

- **Depois da sessão:** abra o paciente, depois **Sessões**, mude o status para *Realizada* e escreva as anotações. Tudo salva sozinho; o topo mostra **✓ Salvo**.
- **Falta:** escolha *Falta cobrada* (entra na cobrança) ou *Faltou (sem cobrança)*.
- **Recebeu:** clique em **Marcar Pago** e escolha a forma de pagamento.
- **Fim do mês:** em **Financeiro**, a lista **"Em aberto"** mostra quem deve, com os botões **Cobrar no WhatsApp** e **Marcar tudo pago**.
- **Recibo:** no paciente, na aba **Dados**, use **"Gerar recibo do mês"**.

## Segurança e backup

- Os dados ficam só no seu servidor, na pasta `app/dados`, bloqueada para acesso pela internet.
- O servidor guarda uma cópia por dia (últimos 60 dias).
- **Uma vez por mês**, clique em **"Baixar Backup"** e guarde o arquivo num lugar seguro. Ele contém as anotações clínicas.
- **Esqueceu a senha?** No Gerenciador de Arquivos, apague o arquivo `app/dados/senha.php`. No próximo acesso o app pede uma senha nova. Os pacientes **não** são apagados.

## Atualizar para uma versão nova

Quando receber um `clinica-app.zip` novo, repita o **Passo 4** na mesma pasta e confirme que quer **substituir** os arquivos. A pasta `dados` (pacientes, senha, backups) **não é apagada**. Mesmo assim, baixe um backup antes, por garantia.

## Problemas comuns

**Erro 403 / "Acesso negado" ao abrir o app**
Confira se a pasta se chama `app` e está dentro da pasta do seu domínio (Passo 3). Se ainda der erro: no Gerenciador de Arquivos, clique com o botão direito na pasta `app`, escolha **"Change Permissions"** e deixe **755**.

**O site fica redirecionando e não abre**
O cadeado (SSL) ainda não está ativo. Volte ao Passo 2.

**"Não foi possível carregar os dados"**
Verifique a internet. Se continuar, confira se a pasta `app/dados` existe.
