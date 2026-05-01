# ERP Brand New Ideas Company

<details>
<summary>🇺🇸 English</summary>

Laravel 10 ERP application — backend API, Blade frontend, modular architecture.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ≥ 8.1 (8.3 recommended) |
| Composer | ≥ 2.x |
| Node.js | ≥ 18 (22 recommended) |
| npm | ≥ 9 |
| MySQL / MariaDB | ≥ 8.0 |
| Redis | ≥ 7 (optional — file driver works) |

### Required PHP extensions

`bcmath` · `exif` · `gd` · `intl` · `mbstring` · `pcntl` · `pdo_mysql` · `xml` · `zip`

---

## Setup

### 1. Install dependencies

```bash
composer install
npm ci
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — at minimum set:

```dotenv
DB_DATABASE=erp_brand_new_ideas_company_db
DB_USERNAME=test
DB_PASSWORD=test
```

### 3. Database

Create the database and user in MySQL:

```sql
CREATE DATABASE IF NOT EXISTS erp_brand_new_ideas_company_db;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL PRIVILEGES ON erp_brand_new_ideas_company_db.* TO 'test'@'localhost';
FLUSH PRIVILEGES;
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

### 4. Clear caches (after changes)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload -o
```

---

## Running

### Development server

```bash
php artisan serve
# → http://localhost:8000
```

### Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
# → http://localhost:8080 (nginx)
```

Services started by `docker compose`:

| Service | Container | Port |
|---|---|---|
| PHP-FPM | `erp-brand-new-ideas-company-app` | 9000 (internal) |
| Nginx | `erp-brand-new-ideas-company-nginx` | 8080 → 80 |
| MySQL 8.0 | `erp-brand-new-ideas-company-db` | 3306 |
| Redis 7 | `erp-brand-new-ideas-company-redis` | 6379 |

To stop:

```bash
docker compose down           # keep volumes
docker compose down -v        # destroy volumes
```

### Frontend assets

```bash
npm run dev          # watch mode
npm run production   # production build
```

---

## Project structure

```
app/
├── Config/Constants/    # ViewsConstants, MiddlewareConstants, DataConstants, …
├── Http/
│   ├── Controllers/     # Domain-grouped: Activity, Bills, Individuals, Planning, …
│   ├── Middleware/       # XSS, Revalidate, locale, etc.
│   └── Requests/        # Form request validation
├── Models/              # Domain-grouped, mirrors Controllers layout
│   └── Traits/          # ChecksLogin, ChecksPermissions, HasCurrency, …
├── Providers/           # AppServiceProvider, RouteServiceProvider, …
└── Traits/              # Controller-level traits

Modules/
└── LandingPage/         # nWidart module — landing page, terms, privacy, about

resources/
├── views/               # Blade templates (layouts, partials, pages)
└── lang/                # 16 JSON translation files (en, pt-br, ru, …)

routes/
├── web.php              # Main web routes (~2000 lines)
├── api.php              # API routes
└── auth.php             # Auth / Fortify routes

database/
├── migrations/          # Schema migrations
└── seeders/             # Mock data + production seeders

public/
└── assets/
    ├── js/core/         # ERPGuard, ERPUtils, RouteGuard (legacy)
    ├── js/routes/       # Per-page JS (login, dashboard, …)
    └── css/             # Component and route-specific CSS

tests/
├── Unit/                # PHPUnit unit tests
├── Feature/             # PHPUnit feature / HTTP tests
└── frontend/js/         # Jest + TypeScript frontend tests
```

---

## Authentication

The app uses **Laravel Fortify** with a custom route prefix:

- Login: `POST /fortify-login`
- Logout: `POST /fortify-logout`
- Register: `POST /fortify-register`

Fortify's default routes are disabled via `Fortify::ignoreRoutes()` in `AppServiceProvider::register()`.

User IDs are **UUIDs** (string), not integers.

---

## Testing

### Latest Results (2026-04-01)

| Tool | Result |
|------|--------|
| PHPUnit | 176 PASS suites, 874 ✓, 7 FAIL suites (10 ⨯), 40 WARN |
| Jest | 28/28 suites, 652/652 tests passed |
| PHPStan | 0 errors |
| Playwright | 9 passed, 3 skipped |
| TSC | 1 error (casing conflict in ts/dist/) |
| flake8 | 147 issues (style) |
| mypy | 37 errors in 6 files |

### PHPUnit (backend)

```bash
php artisan test                           # all suites
php artisan test --filter=UserTest         # specific class
php artisan test --testsuite=Unit          # unit only
php artisan test --testsuite=Feature       # feature only
```

### Jest (frontend)

```bash
npx jest --config jest.config.cjs          # all (652 tests)
npx jest --config jest.config.cjs --testPathPattern="erp-guard"  # specific file
```

### PHPStan (static analysis)

```bash
vendor/bin/phpstan analyse --memory-limit=2G  # full codebase (2G required)
vendor/bin/phpstan analyse app/Models/        # specific directory
```

---

## Key artisan commands

```bash
php artisan route:list --sort=uri          # all registered routes
php artisan migrate:status                 # migration status
php artisan tinker                         # REPL
php artisan queue:work                     # process queued jobs
php artisan schedule:run                   # run scheduled tasks
```

---

## Logging

Custom log channels write to `storage/logs/`:

| Channel | File pattern |
|---|---|
| error | `error-YYYY-MM-DD.log` |
| warning | `warning-YYYY-MM-DD.log` |
| notice | `notice-YYYY-MM-DD.log` |
| critical_trace | `critical_trace-YYYY-MM-DD.log` |
| error_trace | `error_trace-YYYY-MM-DD.log` |
| volatile | `volatile-YYYY-MM-DD.log` |
| short_lived | `short_lived-YYYY-MM-DD.log` |

No default `laravel.log` — all output is routed to the channels above.

---

## Deployment checklist

1. Set `APP_ENV=production`, `APP_DEBUG=false`
2. Set a strong `APP_KEY` (generate with `php artisan key:generate`)
3. Configure real `DB_*`, `MAIL_*`, `REDIS_*` credentials
4. Run `composer install --no-dev --optimize-autoloader`
5. Run `npm run production`
6. Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Ensure `storage/` and `bootstrap/cache/` are writable by the web server
8. **Do NOT deploy** `obf.js`, `_test.*` files, or `phpstan.neon`

---

## Licence

See [LICENSE](../../LICENSE).

</details>

<details>
<summary>🇪🇸 Español</summary>

Aplicación ERP con Laravel 10 — API backend, frontend Blade, arquitectura modular.

---

## Requisitos

| Dependencia | Versión |
|---|---|
| PHP | ≥ 8.1 (8.3 recomendado) |
| Composer | ≥ 2.x |
| Node.js | ≥ 18 (22 recomendado) |
| npm | ≥ 9 |
| MySQL / MariaDB | ≥ 8.0 |
| Redis | ≥ 7 (opcional — driver file funciona) |

### Extensiones PHP necesarias

`bcmath` · `exif` · `gd` · `intl` · `mbstring` · `pcntl` · `pdo_mysql` · `xml` · `zip`

---

## Configuración

### 1. Instalar dependencias

```bash
composer install
npm ci
```

### 2. Entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` — como mínimo configurar:

```dotenv
DB_DATABASE=erp_brand_new_ideas_company_db
DB_USERNAME=test
DB_PASSWORD=test
```

### 3. Base de datos

Crear la base de datos y el usuario en MySQL:

```sql
CREATE DATABASE IF NOT EXISTS erp_brand_new_ideas_company_db;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL PRIVILEGES ON erp_brand_new_ideas_company_db.* TO 'test'@'localhost';
FLUSH PRIVILEGES;
```

Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

### 4. Limpiar caches (después de cambios)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload -o
```

---

## Ejecución

### Servidor de desarrollo

```bash
php artisan serve
# → http://localhost:8000
```

### Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
# → http://localhost:8080 (nginx)
```

Servicios iniciados por `docker compose`:

| Servicio | Contenedor | Puerto |
|---|---|---|
| PHP-FPM | `erp-brand-new-ideas-company-app` | 9000 (interno) |
| Nginx | `erp-brand-new-ideas-company-nginx` | 8080 → 80 |
| MySQL 8.0 | `erp-brand-new-ideas-company-db` | 3306 |
| Redis 7 | `erp-brand-new-ideas-company-redis` | 6379 |

Para detener:

```bash
docker compose down           # mantener volúmenes
docker compose down -v        # destruir volúmenes
```

### Assets frontend

```bash
npm run dev          # modo watch
npm run production   # build de producción
```

---

## Estructura del proyecto

```
app/
├── Config/Constants/    # ViewsConstants, MiddlewareConstants, DataConstants, …
├── Http/
│   ├── Controllers/     # Agrupados por dominio: Activity, Bills, Individuals, Planning, …
│   ├── Middleware/       # XSS, Revalidate, locale, etc.
│   └── Requests/        # Validación de form requests
├── Models/              # Agrupados por dominio, espeja la estructura de Controllers
│   └── Traits/          # ChecksLogin, ChecksPermissions, HasCurrency, …
├── Providers/           # AppServiceProvider, RouteServiceProvider, …
└── Traits/              # Traits a nivel de controller

Modules/
└── LandingPage/         # Módulo nWidart — landing page, términos, privacidad, sobre

resources/
├── views/               # Templates Blade (layouts, partials, páginas)
└── lang/                # 16 archivos JSON de traducción (en, pt-br, ru, …)

routes/
├── web.php              # Rutas web principales (~2000 líneas)
├── api.php              # Rutas API
└── auth.php             # Rutas Auth / Fortify

database/
├── migrations/          # Migraciones de esquema
└── seeders/             # Datos de prueba + seeders de producción

tests/
├── Unit/                # Tests unitarios PHPUnit
├── Feature/             # Tests de integración / HTTP PHPUnit
└── frontend/js/         # Tests Jest + TypeScript frontend
```

---

## Autenticación

La aplicación usa **Laravel Fortify** con un prefijo de ruta personalizado:

- Login: `POST /fortify-login`
- Logout: `POST /fortify-logout`
- Register: `POST /fortify-register`

Las rutas por defecto de Fortify están deshabilitadas vía `Fortify::ignoreRoutes()` en `AppServiceProvider::register()`.

Los IDs de usuario son **UUIDs** (string), no integers.

---

## Testing

### PHPUnit (backend)

```bash
php artisan test                           # todas las suites
php artisan test --filter=UserTest         # clase específica
php artisan test --testsuite=Unit          # solo unit
php artisan test --testsuite=Feature       # solo feature
```

### Jest (frontend)

```bash
npm test                                   # todos (510+ tests)
npm test -- --testPathPattern="erp-guard"  # archivo específico
npm test -- --watch                        # modo watch
```

### PHPStan (análisis estático)

```bash
vendor/bin/phpstan analyse                 # todo el código
vendor/bin/phpstan analyse app/Models/     # directorio específico
```

---

## Comandos artisan clave

```bash
php artisan route:list --sort=uri          # todas las rutas registradas
php artisan migrate:status                 # estado de migraciones
php artisan tinker                         # REPL
php artisan queue:work                     # procesar trabajos en cola
php artisan schedule:run                   # ejecutar tareas programadas
```

---

## Logging

Los canales de log personalizados escriben en `storage/logs/`:

| Canal | Patrón de archivo |
|---|---|
| error | `error-YYYY-MM-DD.log` |
| warning | `warning-YYYY-MM-DD.log` |
| notice | `notice-YYYY-MM-DD.log` |
| critical_trace | `critical_trace-YYYY-MM-DD.log` |
| error_trace | `error_trace-YYYY-MM-DD.log` |
| volatile | `volatile-YYYY-MM-DD.log` |
| short_lived | `short_lived-YYYY-MM-DD.log` |

Sin `laravel.log` por defecto — toda la salida se dirige a los canales anteriores.

---

## Checklist de despliegue

1. Configurar `APP_ENV=production`, `APP_DEBUG=false`
2. Establecer un `APP_KEY` fuerte (generar con `php artisan key:generate`)
3. Configurar credenciales reales de `DB_*`, `MAIL_*`, `REDIS_*`
4. Ejecutar `composer install --no-dev --optimize-autoloader`
5. Ejecutar `npm run production`
6. Ejecutar `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Asegurar que `storage/` y `bootstrap/cache/` sean escribibles por el servidor web
8. **NO desplegar** `obf.js`, archivos `_test.*`, ni `phpstan.neon`

---

## Licencia

Ver [LICENSE](../../LICENSE).

</details>

---

Aplicação ERP com Laravel 10 — API backend, frontend Blade, arquitetura modular.

---

## Requisitos

| Dependência | Versão |
|---|---|
| PHP | ≥ 8.1 (8.3 recomendado) |
| Composer | ≥ 2.x |
| Node.js | ≥ 18 (22 recomendado) |
| npm | ≥ 9 |
| MySQL / MariaDB | ≥ 8.0 |
| Redis | ≥ 7 (opcional — driver file funciona) |

### Extensões PHP necessárias

`bcmath` · `exif` · `gd` · `intl` · `mbstring` · `pcntl` · `pdo_mysql` · `xml` · `zip`

---

## Configuração

### 1. Instalar dependências

```bash
composer install
npm ci
```

### 2. Ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` — no mínimo configurar:

```dotenv
DB_DATABASE=erp_brand_new_ideas_company_db
DB_USERNAME=test
DB_PASSWORD=test
```

### 3. Banco de dados

Criar o banco de dados e o usuário no MySQL:

```sql
CREATE DATABASE IF NOT EXISTS erp_brand_new_ideas_company_db;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL PRIVILEGES ON erp_brand_new_ideas_company_db.* TO 'test'@'localhost';
FLUSH PRIVILEGES;
```

Executar migrações e seeders:

```bash
php artisan migrate --seed
```

### 4. Limpar caches (após alterações)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload -o
```

---

## Execução

### Servidor de desenvolvimento

```bash
php artisan serve
# → http://localhost:8000
```

### Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
# → http://localhost:8080 (nginx)
```

Serviços iniciados pelo `docker compose`:

| Serviço | Container | Porta |
|---|---|---|
| PHP-FPM | `erp-brand-new-ideas-company-app` | 9000 (interno) |
| Nginx | `erp-brand-new-ideas-company-nginx` | 8080 → 80 |
| MySQL 8.0 | `erp-brand-new-ideas-company-db` | 3306 |
| Redis 7 | `erp-brand-new-ideas-company-redis` | 6379 |

Para parar:

```bash
docker compose down           # manter volumes
docker compose down -v        # destruir volumes
```

### Assets frontend

```bash
npm run dev          # modo watch
npm run production   # build de produção
```

---

## Estrutura do projeto

```
app/
├── Config/Constants/    # ViewsConstants, MiddlewareConstants, DataConstants, …
├── Http/
│   ├── Controllers/     # Agrupados por domínio: Activity, Bills, Individuals, Planning, …
│   ├── Middleware/       # XSS, Revalidate, locale, etc.
│   └── Requests/        # Validação de form requests
├── Models/              # Agrupados por domínio, espelha a estrutura dos Controllers
│   └── Traits/          # ChecksLogin, ChecksPermissions, HasCurrency, …
├── Providers/           # AppServiceProvider, RouteServiceProvider, …
└── Traits/              # Traits a nível de controller

Modules/
└── LandingPage/         # Módulo nWidart — landing page, termos, privacidade, sobre

resources/
├── views/               # Templates Blade (layouts, partials, páginas)
└── lang/                # 16 arquivos JSON de tradução (en, pt-br, ru, …)

routes/
├── web.php              # Rotas web principais (~2000 linhas)
├── api.php              # Rotas API
└── auth.php             # Rotas Auth / Fortify

database/
├── migrations/          # Migrações de esquema
└── seeders/             # Dados de teste + seeders de produção

public/
└── assets/
    ├── js/core/         # ERPGuard, ERPUtils, RouteGuard (legado)
    ├── js/routes/       # JS por página (login, dashboard, …)
    └── css/             # CSS por componente e rota

tests/
├── Unit/                # Testes unitários PHPUnit
├── Feature/             # Testes de integração / HTTP PHPUnit
└── frontend/js/         # Testes Jest + TypeScript frontend
```

---

## Autenticação

A aplicação usa **Laravel Fortify** com um prefixo de rota personalizado:

- Login: `POST /fortify-login`
- Logout: `POST /fortify-logout`
- Register: `POST /fortify-register`

As rotas padrão do Fortify estão desabilitadas via `Fortify::ignoreRoutes()` em `AppServiceProvider::register()`.

Os IDs de usuário são **UUIDs** (string), não integers.

---

## Testes

### PHPUnit (backend)

```bash
php artisan test                           # todas as suites
php artisan test --filter=UserTest         # classe específica
php artisan test --testsuite=Unit          # somente unit
php artisan test --testsuite=Feature       # somente feature
```

### Jest (frontend)

```bash
npm test                                   # todos (510+ testes)
npm test -- --testPathPattern="erp-guard"  # arquivo específico
npm test -- --watch                        # modo watch
```

### PHPStan (análise estática)

```bash
vendor/bin/phpstan analyse                 # código completo
vendor/bin/phpstan analyse app/Models/     # diretório específico
```

---

## Comandos artisan principais

```bash
php artisan route:list --sort=uri          # todas as rotas registradas
php artisan migrate:status                 # status das migrações
php artisan tinker                         # REPL
php artisan queue:work                     # processar jobs em fila
php artisan schedule:run                   # executar tarefas agendadas
```

---

## Logging

Os canais de log personalizados escrevem em `storage/logs/`:

| Canal | Padrão de arquivo |
|---|---|
| error | `error-YYYY-MM-DD.log` |
| warning | `warning-YYYY-MM-DD.log` |
| notice | `notice-YYYY-MM-DD.log` |
| critical_trace | `critical_trace-YYYY-MM-DD.log` |
| error_trace | `error_trace-YYYY-MM-DD.log` |
| volatile | `volatile-YYYY-MM-DD.log` |
| short_lived | `short_lived-YYYY-MM-DD.log` |

Sem `laravel.log` padrão — toda a saída é direcionada aos canais acima.

---

## Checklist de deploy

1. Configurar `APP_ENV=production`, `APP_DEBUG=false`
2. Definir um `APP_KEY` forte (gerar com `php artisan key:generate`)
3. Configurar credenciais reais de `DB_*`, `MAIL_*`, `REDIS_*`
4. Executar `composer install --no-dev --optimize-autoloader`
5. Executar `npm run production`
6. Executar `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Garantir que `storage/` e `bootstrap/cache/` sejam graváveis pelo servidor web
8. **NÃO fazer deploy** de `obf.js`, arquivos `_test.*` ou `phpstan.neon`

---

## Licença

Veja [LICENSE](../../LICENSE).
