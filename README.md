Laravel JWT Authentication API Youtube
Este projeto é uma API baseada em Laravel que utiliza autenticação JWT (JSON Web Tokens). Ela é construída com Docker Compose para facilitar o gerenciamento de dependências, como banco de dados PostgreSQL e Redis, juntamente com o Nginx para servir a aplicação.

Requisitos
Docker
Docker Compose
Instruções para Configuração
1. Clone o Repositório
bash
Copiar código
git clone https://github.com/seu-usuario/seu-projeto.git
cd seu-projeto
2. Configure o Arquivo .env
Copie o arquivo .env.example para .env e modifique as variáveis necessárias:

bash
Copiar código
cp .env.example .env
Certifique-se de que as seguintes variáveis estão corretamente configuradas no .env:

env
Copiar código
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:chave_gerada
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=netshow
DB_USERNAME=postgres
DB_PASSWORD=1234

JWT_SECRET=chave_jwt
Você pode gerar a chave JWT com o seguinte comando:

bash
Copiar código
php artisan jwt:secret
3. Configure o Docker
Certifique-se de que o arquivo docker-compose.yml esteja configurado corretamente. O docker-compose.yml define os serviços necessários, incluindo app, db, nginx e redis.

4. Subir os Contêineres com Docker
Inicie o Docker Compose para construir e iniciar a aplicação, banco de dados, Nginx e Redis:

bash
Copiar código
docker-compose up --build
Este comando irá:

Esperar o banco de dados Postgres ficar disponível.
Executar as migrações do banco de dados.
Iniciar o PHP-FPM e o Nginx.
5. Acessar a Aplicação
Depois que todos os serviços estiverem em execução, você pode acessar a aplicação no navegador:

arduino
Copiar código
http://localhost:8000
6. Testar os Endpoints
6.1 Registro de Usuário
Faça uma requisição POST para /api/register para registrar um novo usuário.

URL: /api/register
Método: POST
Body (JSON):
json
Copiar código
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password",
  "password_confirmation": "password"
}
6.2 Login de Usuário
Faça uma requisição POST para /api/login para autenticar um usuário.

URL: /api/login
Método: POST
Body (JSON):
json
Copiar código
{
  "email": "test@example.com",
  "password": "password"
}
A resposta incluirá o access_token que deve ser usado nas próximas requisições.