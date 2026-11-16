# Studioflix

Aplicação local de streaming em PHP e MySQL. O catálogo é público; login é necessário para abrir a tela de trailers.

## Iniciar com XAMPP

1. Abra o XAMPP Control Panel e inicie Apache e MySQL.
2. Importe database/schema.sql pelo phpMyAdmin.
3. Abra http://localhost/studioflix.

Se você já tinha importado uma versão anterior, importe `database/schema.sql` novamente para atualizar os filmes. O script não apaga contas nem histórico.

O projeto usa as configurações padrão do XAMPP (root, sem senha). Para mudar a conexão, defina STUDIOFLIX_DB_HOST, STUDIOFLIX_DB_NAME, STUDIOFLIX_DB_USER e STUDIOFLIX_DB_PASS no ambiente do PHP.

## Iniciar pelo terminal do VS Code

Na pasta do projeto, execute:

    C:\xampp\php\php.exe -S localhost:8000 -t . router.php

Depois acesse http://localhost:8000. O roteador local bloqueia o acesso direto às pastas de configuração e SQL. O MySQL ainda precisa estar iniciado pelo XAMPP.

## Conta administrativa local

- E-mail: admin@studioflix.local
- Senha: Cinema#2026

Essa conta é apenas para a avaliação local. Cadastre usuários comuns pela tela de cadastro.

## Redefinição de senha

O fluxo cria tokens aleatórios de uso único, com validade de 30 minutos. Para enviar e-mails, configure o PHP mail() e a variável STUDIOFLIX_MAIL_FROM. Sem SMTP, o link de recuperação de uma conta cadastrada fica no arquivo temporário privado studioflix-reset-outbox.log do Windows (%TEMP%). Esse log existe para desenvolvimento local; não use esse modo num servidor público.

## Trailers e pôsteres

O catálogo mostra filmes reais com pôsteres carregados das fontes de imagem de cada título. As imagens de destaque usam miniaturas dos trailers no YouTube. As imagens e os trailers dependem de internet; os trailers só aparecem depois que o usuário abre uma sessão autenticada.

O catálogo de demonstração inclui A Chegada, Interestelar, Duna: Parte Dois, The Batman, Everest, O Segredo do Abismo e A Origem. Os links de pôsteres podem ser trocados em `database/schema.sql` e `includes/functions.php`.

## Acesso de QA pela rede local

O link de redefinição usa o host com que a página foi aberta. Para testar por outro computador, acesse o Studioflix pelo IPv4 do desenvolvedor e use esse mesmo endereço durante o pedido de recuperação. O Firewall do Windows precisa permitir a conexão ao Apache ou à porta local escolhida.
