# Virtual-Store_API-REST
 API REST em PHP para loja virtual.

 Até o momento, implementa:
 Cadastro de administradores com autenticação JWT;

### Falta implementar:
 - Dados do website;
 - Categorias;
 - Produtos;
 - Clientes;
 - E mais...
 
## Módulos:
 1) Principal;
 2) express-php-lite (inspirado no *Express* para node.js);
 3) my-jwt (dependêcias requerem *Composer*);
 4) my-model (inspirado no *Sequelize* para node.js);
 5) MyLog;
 6) my-sendgrid (Usa Sendgrid para o envio de e-mails);


## Instalação:

### Configurar my-jwt:
 
 1) Aplicar `composer install -d modules/my-jwt/` para instalar a dependência *Carbon*;

 2) Na pasta `/modules/my-jwt/` copiar `.config.example.php` para `.config.php`;

 3) Configurar `.config.php`:

Constante | Descrição
--------- | ---------
MY_JWT_SECRET | Chave para gerar o JWT das requisições autenticadas da API
MY_JWT_TIMEOUT | Tempo em segundos para expirar o Token
    
  Dica: Sugere-se usar `MyJWT::generateKey()`.
  A constante *JWT_TESTES* (em `/.env.php`) com valor verdadeiro habilita uso da rota para gerar keys:
       
      /util/jwt/generate-key

### Configurar my-model:

  1) Criar usuário e banco de dados no *MySQL* (pode-se adaptar *MyModel* para outros drivers);

  2) Na pasta `/modules/my-model/` copiar `.config.example.php` para `.config.php` e definir as constantes de conexão ao banco de dados;

Constante | Descrição
--------- | ---------
DB_DRIVER | Driver do banco de dados: *mysql* (padrão), *pgsql* (Postgres), *firebird* e *sqlite*. Poderá implementar outros drivers mais.
DB_HOST | Nome do servidor do banco de dados. Padrão: *localhost*
DB_NAME | Nome do banco de dados.
DB_USER | Nome do usuário do banco de dados.
DB_PASS | Senha do usuário do banco de dados.

  3) No banco de dados MySQL aplicar os scripts ordenadamente em: `src/database/migrations/` e em: `src/database/seeds/`

  Assim serão criadas as tabelas, atualizada as estruturas e adicionado o usuário desenvolvedor, que permite o primeiro login na rota `POST /admin/login`:
  
    user: dev
    password: secret

  4) Tão logo, alterar a senha pela rota `PUT /admin/password`.

  5) Alterar seu administrador (já pela rota `PUT /admin/:id`) e informar no campo email o seu próprio email para possibilitar a recuperação de senha pela API;

NOTA: Novas migrations e seeds poderão ser criados. Para cada nova migration criar o undo correspondente com mesmo nome de arquivo na pasta: `src/database/migrations_undo/`.


### Configurar módulo principal:
 
 1) Copiar `.env.example.php` para `.env.php`;
 
 2) Configurar uma `.env.php` para o ambiente desejado;

Constante | Descrição
--------- | ---------
ENV | Ambiente do sistema: *development* (padrão), *prodution*. Esse valor afeta determinadas funcionalidades. Verifique no código.
JWT_TESTS | Quando verdadeiro permite uso das rotas de testes do JWT.
RESET_JWT_SECRET | Chave para gerar o JWT para redefinir a senha
RESET_JWT_TIMEOUT | Tempo em segundos para expirar o Token de redefinição de senha

 3) Executar os script SQL para criar as tabelas no banco de dados da pasta `/src/database/create_tables`;

 Dica: Para testes das rotas recomenda-se o *Insomnia*;


### Configurar módulo my-sendgrid:

 1) Aplicar `composer install -d modules/my-sendgrid/` para instalar a dependência *sendgrid*;

 2) Na pasta `modules/my-sendgrid/` copiar `.env.example.php` para `.env.php` e definir as constantes do my-sendgrid:

Constante | Descrição
--------- | ---------
SENDGRID_API_KEY | Chave da API
MAIL_SERVICE_NAME | Nome do serviço (Example)
MAIL_FROM_EMAIL | E-mail do remetente (noreplay@example.com)
MAIL_FROM_NAME | Nome do remetente (pode-se utilizar MAIL_SERVICE_NAME)
MAIL_REPLY_TO_EMAIL | E-mail para resposta
MAIL_REPLY_TO_NAME | Nome para resposta
MAIL_API_DOMAIN | Nome de domínio da API (api.example.com)
MAIL_API_URL | Endereço URL da API (https://api.example.com)
MAIL_APP_DOMAIN | Nome de domínio do website (example.com)
MAIL_APP_URL | Endereço URL do website (https://example.com/)
MAIL_APP_FRIENDLY_DOMAIN | Nome amigável do domínio do website (Example.com)
MAIL_SERVICE_URL | Endereço URL do website com parâmetros
MAIL_LOGO_ALT | Texto alternativo a imagem da logo
MAIL_LOGO_SRC | URL da imagem da logo
MAIL_RESET_ADMIN_URL | URL para o formulário de redefinição de senha de administrador, sem o Token
MAIL_RESET_ADMIN_URL | URL para o formulário de redefinição de senha de cliente, sem o Token
MAIL_FROM_NAME_SIGNATURE | Nome do remetente na assinatura (Equipe Example)
MAIL_SLOGAN | Slogan usado na assinatura da mensagem
MAIL_FROM_DETAILS | Detalhes do remetente
MAIL_CANCEL_URL | URL de cancelamento de mensagens, sem o Token


## Rotas:

Caminho | Método | Descrição | JWT
------- | ------ | --------- | ---
/ | GET | Home |
/admin/login | POST | Entrada do administrador |
/util/jwt/generate-key | GET | Gera uma chave privada de Token JWT |
/util/jwt/generate-token | GET | Gera um Token JWT |
/util/jwt/validate | GET | Testa validação de Token JWT |
/admin/forgot_password | POST | Solicita redefinição de senha por email|
/admin/reset_password | PUT | Redefine a senha com Token de recuperação | S
/admin/logout | DELETE | Finaliza sessão do administrador | S
/admin/password | PUT | Modifica a senha do administrador | S
/admin | GET | Lista todos os administradores | S
/admin/:id | GET | Detalhes do administrador pelo identificador | S
/admin | POST | Adiciona novo administrador | S
/admin/:id | PUT | Modifica um administrador pelo identificador | S
/admin/:id | DELETE | Exclui um administrador pelo identificador | S


## <a href="LICENSE">Licença MIT</a>
