# Desafio T4Tech - API de Gerenciamento de Esportes

API RESTful desenvolvida em Laravel para gerenciamento de informações da NBA (times e jogadores), com integração à API pública BallDontLie.

## 📋 Requisitos

- PHP 8.4
- Composer
- MySQL 8.0 
- Redis (opcional, para filas assíncronas)
- Docker & Docker Compose (opcional)

## 🚀 Instalação e Configuração

### Opção 1: Usando Docker 🐳 (Recomendado)

1. Clone o repositório:
```bash
git clone https://github.com/vinistanoga/desafio-t4tech.git
cd desafio-t4tech
```

2. Copie o arquivo de ambiente:
```bash
cp .env.example .env
```

3. Configure a API Key do BallDontLie no `.env`:
```env
BALLDONTLIE_API_KEY="eabe28..."
```

4. Instale as dependências do Composer (via Docker):
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```
5. Faça o build dos containers: 
```bash
./vendor/bin/sail build
```

6. Suba os containers com Laravel Sail:
```bash
./vendor/bin/sail up -d
```

7. Gere a chave da aplicação:
```bash
./vendor/bin/sail artisan key:generate
```

8. Rode as migrations:
```bash
./vendor/bin/sail artisan migrate
```

9. (Opcional) Popule o banco com usuários de teste:
```bash
./vendor/bin/sail artisan db:seed
```

Pronto! A API estará rodando em `http://localhost`

### Opção 2: Instalação Local

1. Clone o repositório e entre na pasta
2. Copie o `.env.example` para `.env` e configure o banco de dados
3. Instale as dependências: `composer install`
4. Gere a chave: `php artisan key:generate`
5. Rode as migrations: `php artisan migrate`
6. Rode os seeders: `php artisan db:seed`
7. Inicie o servidor: `php artisan serve`

## 🔐 Autenticação

A API usa dois tipos de autenticação:

1. **Laravel Sanctum** - Token Bearer para autenticação principal
2. **X-Authorization** - Token adicional para acesso externo

### Usuários de Teste

Após rodar o seeder, você terá:

```
Admin:
- Email: admin@test.com
- Senha: password

Usuário Regular:
- Email: user@test.com
- Senha: password
```

### Como Autenticar

1. Faça login para obter o token Sanctum:
```bash
POST /api/login
Content-Type: application/json

{
  "email": "admin@test.com",
  "password": "password"
}
```

2. Gere o token X-Authorization:
```bash
POST /api/x-auth/generate
Authorization: Bearer {seu_token_sanctum}
Content-Type: application/json
```

3. Use ambos os tokens nas requisições:
```bash
GET /api/teams
Authorization: Bearer {token_sanctum}
X-Authorization: {x_authorization_token}
Accept: application/json
```

## 📚 Endpoints da API

### Autenticação
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/me` - Dados do usuário autenticado
- `POST /api/x-auth/generate` - Gerar token X-Authorization
- `GET /api/x-auth/token` - Ver token X-Authorization atual
- `DELETE /api/x-auth/revoke` - Revogar token X-Authorization

### Times (Teams)
- `GET /api/teams` - Listar times (com paginação e filtros)
- `GET /api/teams/{id}` - Detalhes de um time
- `POST /api/teams` - Criar time
- `PUT /api/teams/{id}` - Atualizar time
- `DELETE /api/teams/{id}` - Deletar time (apenas admin)

**Filtros disponíveis:**
- `conference` - Filtrar por conferência (East/West)
- `division` - Filtrar por divisão

### Jogadores (Players)
- `GET /api/players` - Listar jogadores (com paginação e filtros)
- `GET /api/players/{id}` - Detalhes de um jogador
- `POST /api/players` - Criar jogador
- `PUT /api/players/{id}` - Atualizar jogador
- `DELETE /api/players/{id}` - Deletar jogador (apenas admin)

**Filtros disponíveis:**
- `search` - Busca por nome 
- `team_ids[]` - Filtrar por IDs externos de times
- `player_ids[]` - Filtrar por IDs externos de jogadores

## 🔄 Importação de Dados

A aplicação importa dados da API BallDontLie usando **Jobs assíncronos**. Os comandos abaixo disparam os jobs na fila:

```bash
# Importar todos os times
./vendor/bin/sail artisan import:teams

# Importar jogadores com limite (recomendado)
./vendor/bin/sail artisan import:players --per-page=100 --limit=500

# Importar todos os jogadores 
./vendor/bin/sail artisan import:players
```

**Importante:** Os comandos apenas **disparam os jobs**. Para processar as importações, você precisa rodar o worker da fila:

```bash
# Processar jobs da fila
./vendor/bin/sail artisan queue:work
```

**Observações:**
- A API gratuita do BallDontLie permite 5 requisições por minuto
- O sistema já implementa rate limiting (12 segundos entre requests)
- **Sem `--limit`**: Importa TODOS os jogadores (milhares) - pode demorar muito tempo
- **Com `--limit=500`**: Importa apenas 500 jogadores (recomendado para testes)
- Os logs de importação ficam em `storage/logs/laravel.log`
- Os jobs têm 3 tentativas automáticas em caso de falha
- Teams: timeout de 5 minutos | Players: timeout de 10 minutos

## 🧪 Testes

Rodar todos os testes:
```bash
./vendor/bin/sail artisan test
```

Rodar testes específicos:
```bash
# Testes de feature
./vendor/bin/sail artisan test --testsuite=Feature

# Testes unitários
./vendor/bin/sail artisan test --testsuite=Unit

# Teste específico
./vendor/bin/sail artisan test --filter=TeamTest
```

A aplicação possui **73 testes** cobrindo:
- Autenticação (Sanctum + X-Authorization)
- CRUD de Times e Jogadores
- Permissões (admin vs usuário regular)
- Serviços
- Jobs

## 🔒 Permissões

### Admin
- Pode criar, ler, atualizar e **deletar** registros

### Usuário Regular
- Pode criar, ler e atualizar registros
- **Não pode deletar** registros

## 🐳 Docker

O projeto usa Laravel Sail, que já vem configurado com:
- PHP 8.4
- MySQL 8.0
- Redis

Comandos úteis:
```bash
# Subir containers
./vendor/bin/sail up -d

# Parar containers
./vendor/bin/sail down

# Ver logs
./vendor/bin/sail logs -f

# Acessar container
./vendor/bin/sail shell

# Rodar artisan
./vendor/bin/sail artisan {comando}
```

## 📝 Notas

- A aplicação está configurada para usar **Redis** como driver de filas
- Para ambiente de desenvolvimento, você pode usar `sync` no `.env` (não requer Redis)
- Todas as rotas da API retornam JSON no formato padronizado
- **Importante**: Todas as requisições devem incluir os headers `Content-Type: application/json` (para POST/PUT) e `Accept: application/json`
