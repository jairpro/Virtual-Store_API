# Virtual-Store_API-REST
 API REST em PHP para loja virtual.

 Até o momento, implementa:
 Cadastro de administradores com autenticação JWT;

### Erros conhecidos:
 *ATENÇÃO:* implementado verificação de duplicidade na alteração de administrador com _WHERE_ sem _params_. Risco de SQL injection. Corrigir isso em _AdminController->update()_ e implementando em _MyModel->parseWhere()_.

### Falta implementar:
 - Exclusão de administrador;
 - Visualização específica de um administrador (detalhes);
 - Alteração de senha;
 - Recuperação de senha;
 - Dados do website;
 - Categorias;
 - Produtos;
 - Clientes;
 - E mais...
 
## Composição:
 1) Módulo principal;
 2) Módulo express-php-lite (inspirado no *Express* para node.js);
 3) Módulo my-jwt (dependêcias requerem *Composer*);
 4) my-model (inspirado no *Sequelize* para node.js);
 5) MyLog;


## Instalação:

### Configurar my-jwt:
 
 1) Aplicar `composer install -d modules/my-jwt/` para instalar a dependência *Carbon*;

 2) Na pasta `/modules/my-jwt/` copiar `.config.example.php` para `.config.php`;

 3) Configurar `.config.php`:

  *MY_JWT_SECRET* -> Chave para gerar o JWT das requisições autenticadas da API;
    
  Dica: Sugere-se usar `MyJWT::generateKey()`.
  A constante *JWT_TESTES* (em `/.env.php`) com valor verdadeiro habilita uso da rota para gerar keys:
       
      /util/jwt/generate-key

### Configurar my-model:

  1) Criar usuário e banco de dados no *MySQL* (pode-se adaptar *MyModel* para outros drivers);

  2) Na pasta `/modules/my-model/` copiar `.config.example.php` para `.config.php` e definir as constantes de conexão ao banco de dados;

### Configurar módulo principal:
 
 1) Copiar `.env.example.php` para `.env.php`;

 2) Configurar uma `.env.php` para o ambiente desejado;

 3) Executar os script SQL para criar as tabelas no banco de dados da pasta `/src/database/create_tables`;

 Dica: Para testes das rotas recomenda-se o *Insomnia*;


## Rotas:

    get / (Home)
    post /admin/login (entrada do administrador)
  
    get /util/jwt/validate (testa validação de token JWT)
    get /util/jwt/generate-key (gera uma chave privada de token JWT)
    get /util/jwt/generate-token (gera um token JWT)

  *As seguintes rotas requerem autenticação:*

    put /admin/:id (modifica um administrador pelo identificador)
    get /admin (Lista todos os administradores)
    post /admin (adiciona novo administrador)


## <a href="LICENSE">Licença MIT</a>
