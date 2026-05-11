# Finance/Admin Learning ERP

<details>
<summary>🇺🇸 English</summary>

> **Portfolio project notice:** this Laravel application is part of a developer
> portfolio for learning finance, administration, banking, and accounting
> workflows while training with Laravel, SQL, and Kubernetes. It is a learning
> application, not a production financial, banking, or accounting system.

Laravel 10 ERP-style learning application — backend API, Blade frontend,
modular architecture, and test/tooling experiments.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ≥ 8.1 (8.4 local runtime) |
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
├── Contracts/           # CalendarGateway interface, etc.
├── Exceptions/          # Handler.php, custom exceptions
├── Http/
│   ├── Controllers/     # Domain-grouped: Activity, Bills, Individuals, Planning, …
│   ├── Middleware/       # XSS, Revalidate, locale, etc.
│   └── Requests/        # Form request validation
├── Models/              # Domain-grouped, mirrors Controllers layout
│   └── Traits/          # ChecksLogin, ChecksPermissions, HasCurrency, …
├── Providers/           # AppServiceProvider, RouteServiceProvider, …
├── Services/            # Calendar, Utility delegation services, etc.
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
├── frontend/js/         # Jest + TypeScript frontend tests
├── e2e/                 # Playwright E2E specs
└── python/              # Python test scripts

utils/                   # Developer tooling workspace
├── .llms/               # LLM context and session artifacts
├── scripts/             # Shell, Python, JS, PHP, TS-harness scripts
├── prompts/             # Coding-style guidelines per language
├── cli/ grep/ find/ regex/ cmds/  Dated command logs
└── README.md            # Full documentation

.notes/                  # Durable team notes (issues, plans, work journal)
├── KNOWN_ISSUES.md      # Open issues
├── CURRENT_WORKING_ISSUES.md     # Bug-fix session log
├── CURRENT_WORKING_ISSUES_WORK.md # Try/fail/success journal
├── NEXT_STEPS.md        # Immediate/deferred tasks
├── RESOLVED_ISSUES.md   # Resolved issues archive
├── TODO_LATER.MD        # Deferred items
├── README.md / README_INFRA.md / README_UTILS.md
└── .llms/               # Guideline tree, history, migration notes
```

---

## Reliability foundation

The application now has a generic reliability layer for high-impact business
operations:

- `app/Services/Reliability/CriticalOperationService.php` wraps critical work
  in an operation ledger, step log, DB transaction, and optional durable outbox.
- `OutboxService` and `InboxService` cover publish-after-commit and idempotent
  receive-side tracking.
- `OperationalEventService` stores volatile low-impact events in memory and
  persists medium/high/critical events to `operational_events`.
- `Retry` and `CircuitBreaker` provide Spring-like builder APIs for guarded
  call paths. Retry emits success/retry/failure events and defaults to capped
  exponential backoff; circuit breaker persists state and sliding-window calls
  for medium+ work, emits state-change/opened/rejected events, and stays
  disabled by default for trivial/low work.
- `ReliabilityRetentionService` prunes expired finished rows and can compress
  verbose event timelines.
- `app/Services/Ledger/LedgerActionService.php` is the first production
  integration and records finance ledger postings as critical operations.
- `FinanceOperationService` and `FinanceOutboxDispatcher` now cover the
  finance-only monolith callback slice for invoice/bill payment create/delete:
  the payment mutation, operation ledger, step log, and outbox intent commit
  together; post-commit signals are guarded by retry/circuit breaker controls
  and then accepted for journal control, banking API shells, communication API
  shells, ledger reversal review, and webhook shells.
- `php artisan reliability:dispatch-finance-outbox` drains pending finance
  outbox rows without requiring a broker. The named status route is
  `reliability.operations.show`; the app route pluralizer renders the URI as
  `reliabilities/operations/{operation}`.
- `HrmOperationService` and `HrmOutboxDispatcher` cover the first HRM slice:
  salary/payroll updates, termination lifecycle create/update/delete, and leave
  status decisions. HRM outbox signals target payroll, finance payroll bridge,
  RBAC/access reconciliation, calendar availability, communication, employee
  record projections, and webhook shells.
- `php artisan reliability:dispatch-hrm-outbox` drains pending HRM outbox rows.
  HRM quarantine accepts employees without linked users, but can route
  persistent linked-user/RBAC mismatches or payroll/lifecycle corruption to
  manual review after repeated instability signals.
- `WarehouseOperationService` and `WarehouseOutboxDispatcher` cover the first
  warehouse/products slice: manual stock adjustments, decisive product/service
  catalog changes, imports, warehouse transfer create/update/delete, guarded
  warehouse deletion, purchase stock commits/reversals including purchase-line
  deletion, and POS stock commit. Warehouse outbox signals target stock
  projection, reconciliation, replica-sync, logistics, valuation, catalog
  replica, finance bridges, customer/supplier stock projections, and webhooks.
- `php artisan reliability:dispatch-warehouse-outbox` drains pending warehouse
  outbox rows. Warehouse quarantine is manual-review only and requires
  persistent instability in high-impact stock/product/warehouse rows.
- `CrmOperationService` and `CrmOutboxDispatcher` cover durable lead/deal
  lifecycle decisions, stage/status movement, lead conversion,
  customer/vendor/client relationship records, and deal user/client/permission
  sub-actions. Routine CRM activity stays low-overhead unless a caller promotes
  it.
- `PlanningOperationService` and `PlanningOutboxDispatcher` cover final project
  status, project deletion, milestone final/delete paths, task completion/final
  progress, and completed/final task deletion while routine project-board
  metadata stays low-overhead.
- `HeavyIoOperationService` and `HeavyIoOutboxDispatcher` cover shared Python
  import/export subprocess boundaries and configured webhook delivery while
  preserving legacy return shapes and keeping subprocess calls outside SQL
  transactions.
- `php artisan reliability:dispatch-crm-outbox`,
  `php artisan reliability:dispatch-planning-outbox`, and
  `php artisan reliability:dispatch-heavy-io-outbox` drain those pending
  monolith-local outbox rows.

The policy is intentionally domain-neutral. Finance commits and payroll/lifecycle
HR decisions usually need the highest controls, but project
finalization/deletion, warehouse commits, product/stock commits, CRM decisions,
and heavy system operations should use the same layer when their business impact
is comparable.

Post-commit dispatch failures first pass through the in-process retry/circuit
guards, then use durable outbox retry scheduling. Exhausted attempts move the
outbox row to `dead_letter`, mark the operation ledger `compensating`, and
create a durable domain compensation event such as
`finance.compensation.required`, `hrm.compensation.required`,
`warehouse.compensation.required`, `crm.compensation.required`,
`planning.compensation.required`, or `heavy_io.compensation.required` so
rollback/reversal work is visible to operators and later workers.

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

### Latest Results (2026-05-11)

| Tool | Result |
|------|--------|
| PHPUnit Unit | 10,637 tests, 20,685 assertions, 0 errors, 0 failures |
| Reliability service tests | 54 tests, 297 assertions, 0 errors, 0 failures |
| HRM touched controller tests | 136 tests, 163 assertions, 0 errors, 0 failures |
| Warehouse/product touched controller tests | 410 tests, 486 assertions, 0 errors, 0 failures |
| CRM relationship touched controller tests | 594 tests, 703 assertions, 0 errors, 0 failures |
| Planning touched controller tests | 354 tests, 450 assertions, 0 errors, 0 failures |
| Heavy-I/O shared boundary tests | 37 tests, 77 assertions, 0 errors, 0 failures |
| PHPStan | clean (`composer phpstan`) |
| ESLint | clean (`npx --no-install eslint . --max-warnings=50`) |
| Jest | see current CI / package scripts |
| Playwright | see current CI / package scripts |
| tsc | see current CI / package scripts |
| pytest | see current CI / package scripts |

### PHPUnit (backend)

```bash
php vendor/bin/phpunit --testsuite=Unit --no-coverage
php vendor/bin/phpunit tests/Unit --no-coverage
php vendor/bin/phpunit --filter=UserTest --no-coverage
php vendor/bin/phpunit tests/Unit/app/Models/bills --no-coverage
```

Do not run `php artisan test` here; it can target the wrong database for this
project's seeded local workflow.

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
├── Contracts/           # Interfaz CalendarGateway, etc.
├── Exceptions/          # Handler.php, excepciones personalizadas
├── Http/
│   ├── Controllers/     # Agrupados por dominio: Activity, Bills, Individuals, Planning, …
│   ├── Middleware/       # XSS, Revalidate, locale, etc.
│   └── Requests/        # Validación de form requests
├── Models/              # Agrupados por dominio, espeja la estructura de Controllers
│   └── Traits/          # ChecksLogin, ChecksPermissions, HasCurrency, …
├── Providers/           # AppServiceProvider, RouteServiceProvider, …
├── Services/            # Calendar, servicios de delegación Utility, etc.
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
├── frontend/js/         # Tests Jest + TypeScript frontend
├── e2e/                 # Especificaciones Playwright E2E
└── python/              # Scripts de prueba Python

utils/                   # Espacio de trabajo de herramientas
├── .llms/               # Contexto LLM y artefactos de sesión
├── scripts/             # Scripts sh/ py/ js/ php/ ts-harness/
├── prompts/             # Guías de estilo por lenguaje
├── cli/ grep/ find/ regex/ cmds/  Registros de comandos fechados
└── README.md            # Documentación completa

.notes/                  # Notas duraderas del equipo
├── KNOWN_ISSUES.md      # Problemas abiertos
├── CURRENT_WORKING_ISSUES.md     # Registro de sesiones de depuración
├── CURRENT_WORKING_ISSUES_WORK.md # Bitácora de intentos/éxitos
├── NEXT_STEPS.md        # Tareas inmediatas/diferidas
├── RESOLVED_ISSUES.md   # Archivo de problemas resueltos
├── TODO_LATER.MD        # Elementos diferidos
├── README.md / README_INFRA.md / README_UTILS.md
└── .llms/               # Árbol de guías, historial, notas de migración
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
├── Contracts/           # Interface CalendarGateway, etc.
├── Exceptions/          # Handler.php, exceções personalizadas
├── Http/
│   ├── Controllers/     # Agrupados por domínio: Activity, Bills, Individuals, Planning, …
│   ├── Middleware/       # XSS, Revalidate, locale, etc.
│   └── Requests/        # Validação de form requests
├── Models/              # Agrupados por domínio, espelha a estrutura dos Controllers
│   └── Traits/          # ChecksLogin, ChecksPermissions, HasCurrency, …
├── Providers/           # AppServiceProvider, RouteServiceProvider, …
├── Services/            # Calendar, serviços de delegação Utility, etc.
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
├── frontend/js/         # Testes Jest + TypeScript frontend
├── e2e/                 # Especificações Playwright E2E
└── python/              # Scripts de teste Python

utils/                   # Espaço de trabalho de ferramentas
├── .llms/               # Contexto LLM e artefatos de sessão
├── scripts/             # Scripts sh/ py/ js/ php/ ts-harness/
├── prompts/             # Guias de estilo por linguagem
├── cli/ grep/ find/ regex/ cmds/  Registros de comandos datados
└── README.md            # Documentação completa

.notes/                  # Notas duráveis da equipe
├── KNOWN_ISSUES.md      # Problemas abertos
├── CURRENT_WORKING_ISSUES.md     # Registro de sessões de depuração
├── CURRENT_WORKING_ISSUES_WORK.md # Diário de tentativas/sucessos
├── NEXT_STEPS.md        # Tarefas imediatas/adiadas
├── RESOLVED_ISSUES.md   # Arquivo de problemas resolvidos
├── TODO_LATER.MD        # Itens adiados
├── README.md / README_INFRA.md / README_UTILS.md
└── .llms/               # Árvore de guias, histórico, notas de migração
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
