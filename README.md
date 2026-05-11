# Finance/Admin Learning ERP — Portfolio Monorepo

<details>
<summary>🇺🇸 English</summary>

> **Portfolio project notice:** this repository is a portfolio project by a
> developer learning technology for finance, administration, banking, and
> accounting procedures while training with Laravel, SQL, and Kubernetes. It
> is not a production banking, accounting, or financial-advice system.

Learning-focused Enterprise Resource Planning system. This monorepo holds the
active Laravel application, working notes, utility scripts, infrastructure
experiments, and LLM prompts used during development.

---

## Repository layout

```
.
├── _inc/                          # Active development
│   ├── laravel/                   # ⭐ Main application (Laravel 10 + PHP 8.4 runtime)
│   │   ├── app/                   #    Backend: Controllers, Models, Traits, Providers
│   │   ├── Modules/LandingPage/   #    nWidart module (landing, terms, privacy, about)
│   │   ├── resources/views/       #    Blade templates
│   │   ├── public/assets/         #    JS (ERPGuard, ERPUtils, dash) + CSS
│   │   ├── routes/                #    web.php, api.php, auth.php
│   │   ├── database/migrations/   #    Schema migrations
│   │   ├── database/seeders/      #    Mock + production seeders
│   │   ├── tests/                 #    PHPUnit (Unit+Feature) + Jest/TS frontend
│   │   ├── Dockerfile             #    Multi-stage build (Node + PHP-FPM)
│   │   ├── docker-compose.yml     #    app · nginx · mysql · redis
│   │   └── README.md              #    Project-specific setup & run guide
│   │   └── utils/                 #    Developer tooling workspace (scripts, prompts, audits)
│   │       ├── scripts/           #    sh/ py/ js/ php/ ts-harness/
│   │       ├── prompts/           #    Coding-style guidelines per language
│   │       ├── cli/ grep/ find/ regex/ cmds/  Dated command logs
│   │       └── ...                #    See _inc/laravel/utils/README.md
│   └── utils/                     # Monorepo-level context (minimal)
│       └── .llms/
│
├── _old/                          # Fork with experimental modifications (reference only)
│   ├── app/                       #    Controllers, Models with manual edits
│   ├── routes/                    #    Route files with inline changes
│   └── ...                        #    Mirrors original structure
│
├── origin/                        # Unmodified upstream fork (upstream source)
│   └── erp/                       #    Original source tree — read-only reference
│
├── notes/                         # Team documentation (older copies — see _inc/laravel/.notes/)
│   ├── CURRENT_WORKING_ISSUES.md  #    Route health report & active bugs (2026-02-07)
│   └── KNOWN_ISSUES.md            #    Migration naming, known constraints
│
├── .notes/                        # Root-level durable notes (mostly migrated to _inc/laravel/.notes/)
│   ├── .llms/.guidelines/         #    Legacy guideline tree (deprecated — mirrored in _inc/laravel/.notes/)
│   ├── MOVED_README.md            #    Migration notice
│   └── README.md
│
├── obf.js                         # Route-map obfuscation layer (DO NOT deploy)
├── LICENSE                        # Project licence
├── .gitignore                     # Ignores: ._DEPRECATED_*, .vscode, vendor, node_modules, logs
└── README.md                      # ← You are here
```

> **`._DEPRECATED_*` folders** (django, flutter, frontend, erp-brand-new-ideas-company-frontend) are **git-ignored** and purged from history. They remain on disk for local reference only.

---

## Guidelines & LLM context map

All conventions, architecture decisions, and agent instructions live under three guideline trees.
See [`where-to-update-and-read.yml`](where-to-update-and-read.yml) for the canonical **filesystem architecture map** with the full directory layout and every path an LLM agent or developer must check.

| Tree                | Path                                                                                 | Scope                                                                   |
| ------------------- | ------------------------------------------------------------------------------------ | ----------------------------------------------------------------------- |
| Primary guidelines  | [`_inc/laravel/.notes/.llms/.guidelines/`](_inc/laravel/.notes/.llms/.guidelines/)   | Architecture, backend, frontend, DB, modules, security, testing, roles  |
| Coding-style guides | [`_inc/laravel/utils/prompts/.guidelines/`](_inc/laravel/utils/prompts/.guidelines/) | Per-language rules (PHP, JS, TS, Python, CSS, React) in md/xml/yml/toml |
| App-specific guides | [`_inc/laravel/.notes/.llms/`](_inc/laravel/.notes/.llms/)                           | App-scoped reports, roleplay maps, and test guidance                    |
| Agent config        | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md`              | Copilot/agent behaviour                                                 |
| LLM session context | [`_inc/laravel/utils/.llms/`](_inc/laravel/utils/.llms/)                             | CLI logs, agent context, working notes                                  |

---

## Update & archive flow

- Start from [`where-to-update-and-read.yml`](where-to-update-and-read.yml) before changing docs, scripts, or routes.
- Keep durable documentation under [`README.md`](README.md), [`_inc/laravel/.notes/`](_inc/laravel/.notes/), and [`_inc/laravel/utils/`](_inc/laravel/utils/).
- Treat all `.history/` paths as archived local context: keep files on disk, but do not track them in git.
- Keep reusable automation in [`_inc/laravel/utils/scripts/`](_inc/laravel/utils/scripts/); move one-off repair scripts to the nearest `.history/` tree.

---

## Tech Stack

| Layer              | Technology                                   | Version |
| ------------------ | -------------------------------------------- | ------- |
| Language           | PHP                                          | 8.4 runtime; composer allows >=8.1 |
| Framework          | Laravel                                      | 10.x    |
| Module system      | nWidart/laravel-modules                      | 10.x    |
| Database           | MySQL / MariaDB                              | 8.0+    |
| Cache / Queue      | Redis (optional, file driver default)        | 7.x     |
| Frontend bundler   | Laravel Mix (Webpack)                        | 6.x     |
| CSS framework      | Tailwind CSS + Bootstrap 5                   | —       |
| JS architecture    | Vanilla ES2022 (IIFE singletons)             | —       |
| Testing (backend)  | PHPUnit                                      | 10.x    |
| Testing (frontend) | Jest + ts-jest (jsdom)                       | 29.x    |
| Static analysis    | PHPStan (level 5)                            | 2.x     |
| Containerisation   | Docker + Docker Compose                      | —       |
| Auth               | Laravel Fortify (custom `/fortify-*` prefix) | —       |
| IDs                | UUID (not auto-increment)                    | —       |

---

## Key conventions

- **Controllers** are organised by domain under `app/Http/Controllers/{Activity,Bills,Bugs,Charts,Companies,Configs,Contact,Individuals,Info,Planning,Products,Shapes,Ssr}/`.
- **Models** mirror the same domain structure under `app/Models/`.
- **Constants** live in dedicated `*Constants.php` classes (ViewsConstants, MiddlewareConstants, DataConstants, etc.) — raw strings are avoided in routes and controllers.
- **Traits** (`ChecksLogin`, `ChecksPermissions`, `HasCurrency`, `MeasuresPerformance`, …) are mixed into controllers and models.
- **Middleware** constants: `MWC::AUTH`, `MWC::VF`, `MWC::XSS`, `MWC::REV`, `MWC::WEB`.
- **Translations** use JSON files at `resources/lang/{en,pt-br,ru,…}.json` (16 languages, ~3,000 keys each).

## Reliability foundation

Critical business procedures now have a shared reliability layer under
`_inc/laravel/app/Services/Reliability/` and durable tables for operation
ledgers, operation steps, outbox/inbox messages, operational events, and
circuit breaker state/call history.
Financial ledger posting and invoice/bill payment create/delete were the first
integrated paths. The HRM slice now covers salary/payroll updates, termination
lifecycle decisions, and leave status decisions. Finance and HRM outbox
dispatch are currently monolith-local: they accept journal, banking-shell,
payroll, RBAC/access-control, calendar, communication, and webhook signals
without requiring a broker. Dispatch is guarded by Spring-like `Retry` and
`CircuitBreaker` builders that emit operational events for success, retry,
final failure, state changes, and rejected calls. Retry intervals default to
capped exponential backoff. The same pattern is intended for any high-impact
workflow: irreversible project closures, warehouse commits, heavy I/O tasks,
and other state changes where replay, auditability, or retry control matters.

Use the severity policy consistently: trivial/low work can stay in memory,
medium-and-up work gets durable operation rows, and critical operations should
pair durable outbox records with a transaction isolation level appropriate to
the underlying SQL procedure. Circuit breakers stay disabled by default for
trivial/low work. Post-commit signal failures first pass through the in-process
retry/circuit guards, then use durable outbox retry scheduling and move to
compensation-required state when attempts are exhausted.

Finance has stricter defaults than ordinary modules: every financial
transaction is retry-eligible, with retry attempts increasing by amount and
other risk signals such as reversal/transfer type, external origin, privileged
actor, approval requirement, or high user risk score. Post-write validation
starts at amount `3,200`; quarantine is not the default validation result and is
reserved for persistent corrupted state after repeated failures, circuit
instability, dead letters, or long stuck processing.

HRM quarantine follows the same narrow standard. An employee without a linked
login user is valid, but a persisted linked-user/RBAC mismatch can be escalated
only after repeated retry/circuit/dead-letter/failed-ledger instability in a
critical payroll, lifecycle, or identity/access procedure.

---

## Quick start (development)

```bash
# 1. Clone
git clone <repo-url> && cd finance-admin-learning-erp

# 2. Enter the project
cd _inc/laravel

# 3. Install dependencies
composer install
npm ci

# 4. Environment
cp .env.example .env          # then edit DB_*, APP_KEY, etc.
php artisan key:generate

# 5. Database
php artisan migrate --seed

# 6. Serve
php artisan serve              # http://localhost:8000
```

Or with Docker:

```bash
cd _inc/laravel
docker compose up -d --build
docker compose exec app php artisan migrate --seed
# → http://localhost:8080
```

See [\_inc/laravel/README.md](_inc/laravel/README.md) for the full project-specific guide.

---

## Testing

### Backend (PHPUnit)

```bash
cd _inc/laravel
php -d memory_limit=1G vendor/bin/phpunit --testsuite Unit --no-coverage
php -d memory_limit=1G vendor/bin/phpunit tests/Unit --no-coverage
php -d memory_limit=1G vendor/bin/phpunit --filter=UserTest --no-coverage
# Never run `php artisan test` in this project; use vendor/bin/phpunit directly.
```

### Frontend (Jest)

```bash
cd _inc/laravel
npm test                                  # 510+ tests
npm test -- --testPathPattern="erp-guard" # specific suite
```

### Static analysis (PHPStan)

```bash
cd _inc/laravel
vendor/bin/phpstan analyse
```

### Security roleplay (Jest)

```bash
cd _inc/laravel
npx jest --config=jest.config.cjs --testPathPatterns="tests/Unit/security/roleplay"
# 17 suites · 187 tests — multi-language (JS, WASM, PHP, Python, Bash)
# Roles: black-hat, green-hat, white-hat, CISO, backend-dev, QA
```

See [`_inc/laravel/tests/Feature/security/roleplay/README.md`](_inc/laravel/tests/Feature/security/roleplay/README.md) for the full framework docs.

---

## Utility scripts

| File                                                             | Purpose                                                  |
| ---------------------------------------------------------------- | -------------------------------------------------------- |
| `_inc/laravel/utils/regexes.txt`                                 | Reusable regex patterns for codebase audits              |
| `_inc/laravel/utils/cli/`                                        | Dated CLI command logs                                   |
| `_inc/laravel/utils/grep/`                                       | Dated grep command notes                                 |
| `_inc/laravel/utils/find/`                                       | Dated find command notes                                 |
| `_inc/laravel/utils/regex/`                                      | Dated regex pattern notes                                |
| `_inc/laravel/utils/scripts/py/analysis/compare_funcs_models.py` | Compare method signatures between old/new models         |
| `_inc/laravel/utils/scripts/py/analysis/compare_funcs_names.py`  | Diff function names across directories                   |
| `_inc/laravel/utils/scripts/py/analysis/rearrange.py`            | Rearrange import statements                              |
| `_inc/laravel/utils/scripts/py/analysis/read_deps.py`            | Parse composer/package dependency trees                  |
| `_inc/laravel/utils/prompts/`                                    | XML/Markdown prompt templates for LLM-assisted migration |
| `_inc/laravel/utils/README.md`                                   | Full guide to the utilities workspace                    |

---

## Notes For Developers And Agents

- **`_old/`** contains a manually-edited fork kept for diffing against the new codebase. Do not develop here.
- **`origin/erp/`** is the untouched upstream source. Do not modify — use it for `diff` comparisons.
- **`notes/`** holds older copies of working issues. The canonical versions live in **`_inc/laravel/.notes/`**. Update those instead.
- **`obf.js`** is a route-map obfuscation file. **Must be git-ignored in production deployments** (see the security alert inside the file).
- **`_test.*` files** at root are scratch pads for quick experiments. They are git-ignored.
- **`AGENTS.md`** is the short fork document for new agents. Keep it current when
  test baselines, hard constraints, or handoff locations change.

---

## Licence

See [LICENSE](LICENSE).

</details>

<details>
<summary>🇪🇸 Español</summary>

Sistema de Planificación de Recursos Empresariales para **Nova Brand New Ideas Company**. Este repositorio es el monorepo del equipo que contiene la aplicación Laravel de producción, el código de referencia original, notas de trabajo, scripts utilitarios y prompts de LLM utilizados durante el desarrollo.

---

## Estructura del repositorio

```
.
├── _inc/                          # Desarrollo activo
│   ├── laravel/                   # ⭐ Aplicación principal (Laravel 10 + PHP 8.3)
│   │   ├── app/                   #    Backend: Controllers, Models, Traits, Providers
│   │   ├── Modules/LandingPage/   #    Módulo nWidart (landing, términos, privacidad, sobre)
│   │   ├── resources/views/       #    Templates Blade
│   │   ├── public/assets/         #    JS (ERPGuard, ERPUtils, dash) + CSS
│   │   ├── routes/                #    web.php, api.php, auth.php
│   │   ├── database/migrations/   #    Migraciones de esquema
│   │   ├── database/seeders/      #    Seeders de prueba + producción
│   │   ├── tests/                 #    PHPUnit (Unit+Feature) + Jest/TS frontend
│   │   ├── Dockerfile             #    Build multi-etapa (Node + PHP-FPM)
│   │   ├── docker-compose.yml     #    app · nginx · mysql · redis
│   │   └── README.md              #    Guía de configuración y ejecución del proyecto
│   │   └── utils/                 #    Espacio de trabajo de herramientas (scripts, prompts, auditorías)
│   │       ├── scripts/           #    sh/ py/ js/ php/ ts-harness/
│   │       ├── prompts/           #    Guías de estilo por lenguaje
│   │       ├── cli/ grep/ find/ regex/ cmds/  Registros de comandos fechados
│   │       └── ...                #    Ver _inc/laravel/utils/README.md
│   └── utils/                     # Contexto a nivel de monorepo (mínimo)
│       └── .llms/
│
├── _old/                          # Fork con modificaciones experimentales (solo referencia)
├── origin/                        # Fork upstream sin modificar (upstream source)
├── notes/                         # Documentación del equipo (copias antiguas — ver _inc/laravel/.notes/)
├── obf.js                         # Capa de ofuscación del mapa de rutas (NO desplegar)
├── LICENSE                        # Licencia del proyecto
├── .gitignore                     # Ignora: ._DEPRECATED_*, .vscode, vendor, node_modules, logs
└── README.md                      # ← Estás aquí
```

> **Las carpetas `._DEPRECATED_*`** (django, flutter, frontend, erp-brand-new-ideas-company-frontend) están **git-ignored** y purgadas del historial. Permanecen en disco solo como referencia local.

---

## Mapa de guías y contexto LLM

Todas las convenciones, decisiones arquitectónicas e instrucciones para agentes están en tres árboles de guías.
Consulte [`where-to-update-and-read.yml`](where-to-update-and-read.yml) para el **mapa de arquitectura del sistema de archivos** con el diseño completo y cada ruta que un agente LLM o desarrollador debe verificar.

| Árbol            | Ruta                                                                    | Alcance                                                                 |
| ---------------- | ----------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| Guías primarias  | `_inc/laravel/.notes/.llms/.guidelines/`                                | Arquitectura, backend, frontend, BD, módulos, seguridad, testing, roles |
| Guías de estilo  | `_inc/laravel/utils/prompts/.guidelines/`                               | Reglas por lenguaje (PHP, JS, TS, Python, CSS, React)                   |
| Guías de la app  | `_inc/laravel/.notes/.llms/`                                            | Reportes de app, mapas de roleplay y guías de prueba                    |
| Config de agente | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` | Comportamiento del agente                                               |
| Contexto LLM     | `_inc/laravel/utils/.llms/`                                             | Logs de CLI, contexto de agentes, notas de trabajo                      |

---

## Stack tecnológico

| Capa               | Tecnología                                           | Versión |
| ------------------ | ---------------------------------------------------- | ------- |
| Lenguaje           | PHP                                                  | 8.3     |
| Framework          | Laravel                                              | 10.x    |
| Sistema de módulos | nWidart/laravel-modules                              | 10.x    |
| Base de datos      | MySQL / MariaDB                                      | 8.0+    |
| Cache / Cola       | Redis (opcional, driver file por defecto)            | 7.x     |
| Bundler frontend   | Laravel Mix (Webpack)                                | 6.x     |
| Framework CSS      | Tailwind CSS + Bootstrap 5                           | —       |
| Arquitectura JS    | Vanilla ES2022 (IIFE singletons)                     | —       |
| Testing (backend)  | PHPUnit                                              | 10.x    |
| Testing (frontend) | Jest + ts-jest (jsdom)                               | 29.x    |
| Análisis estático  | PHPStan (level 5)                                    | 2.x     |
| Contenedorización  | Docker + Docker Compose                              | —       |
| Auth               | Laravel Fortify (prefijo personalizado `/fortify-*`) | —       |
| IDs                | UUID (no auto-increment)                             | —       |

---

## Convenciones clave

- Los **Controllers** están organizados por dominio en `app/Http/Controllers/{Activity,Bills,Bugs,...}/`.
- Los **Models** siguen la misma estructura de dominio en `app/Models/`.
- Las **Constants** están en clases dedicadas `*Constants.php` — se evitan strings literales en rutas y controllers.
- Los **Traits** (`ChecksLogin`, `ChecksPermissions`, `HasCurrency`, …) se mezclan en controllers y models.
- Constantes de **Middleware**: `MWC::AUTH`, `MWC::VF`, `MWC::XSS`, `MWC::REV`, `MWC::WEB`.
- Las **Traducciones** usan archivos JSON en `resources/lang/{en,pt-br,ru,…}.json` (16 idiomas, ~3.000 claves cada uno).

---

## Inicio rápido (desarrollo)

```bash
# 1. Clonar
git clone <repo-url> && cd erp_brand_new_ideas_company

# 2. Entrar al proyecto
cd _inc/laravel

# 3. Instalar dependencias
composer install
npm ci

# 4. Entorno
cp .env.example .env          # luego editar DB_*, APP_KEY, etc.
php artisan key:generate

# 5. Base de datos
php artisan migrate --seed

# 6. Servir
php artisan serve              # http://localhost:8000
```

O con Docker:

```bash
cd _inc/laravel
docker compose up -d --build
docker compose exec app php artisan migrate --seed
# → http://localhost:8080
```

Consulte [\_inc/laravel/README.md](_inc/laravel/README.md) para la guía completa del proyecto.

---

## Testing

### Backend (PHPUnit)

```bash
cd _inc/laravel
composer run test:unit                    # suite de unidad (seguro — usa DB de prueba)
composer run test:feature                 # suite de features (seguro — usa DB de prueba)
php -d memory_limit=1G vendor/bin/phpunit --testsuite Unit --no-coverage
php -d memory_limit=1G vendor/bin/phpunit --filter=UserTest --no-coverage
# ⚠️  Nunca usar `php artisan test` — usa la DB live y puede borrar datos
```

### Frontend (Jest)

```bash
cd _inc/laravel
npm test                                  # 510+ tests
npm test -- --testPathPattern="erp-guard" # suite específica
```

### Análisis estático (PHPStan)

```bash
cd _inc/laravel
vendor/bin/phpstan analyse
```

### Roleplay de seguridad (Jest)

```bash
cd _inc/laravel
npx jest --config=jest.config.cjs --testPathPatterns="tests/Unit/security/roleplay"
# 17 suites · 187 tests — multi-lenguaje (JS, WASM, PHP, Python, Bash)
# Roles: black-hat, green-hat, white-hat, CISO, backend-dev, QA
```

Ver [`_inc/laravel/tests/Feature/security/roleplay/README.md`](_inc/laravel/tests/Feature/security/roleplay/README.md) para la documentación completa del framework.

---

## Scripts utilitarios

| Archivo                                                          | Propósito                                            |
| ---------------------------------------------------------------- | ---------------------------------------------------- |
| `_inc/laravel/utils/regexes.txt`                                 | Patrones regex reutilizables para auditorías         |
| `_inc/laravel/utils/cli/`                                        | Registros de comandos CLI fechados                   |
| `_inc/laravel/utils/grep/`                                       | Notas de comandos grep fechadas                      |
| `_inc/laravel/utils/find/`                                       | Notas de comandos find fechadas                      |
| `_inc/laravel/utils/regex/`                                      | Notas de patrones regex fechados                     |
| `_inc/laravel/utils/scripts/py/analysis/compare_funcs_models.py` | Comparar firmas de métodos entre modelos old/new     |
| `_inc/laravel/utils/scripts/py/analysis/compare_funcs_names.py`  | Diff de nombres de funciones entre directorios       |
| `_inc/laravel/utils/scripts/py/analysis/rearrange.py`            | Reorganizar sentencias de import                     |
| `_inc/laravel/utils/scripts/py/analysis/read_deps.py`            | Parsear árboles de dependencias composer/package     |
| `_inc/laravel/utils/prompts/`                                    | Templates de prompts para migración asistida por LLM |
| `_inc/laravel/utils/README.md`                                   | Guía completa del espacio de trabajo de utilidades   |

---

## Notas para el equipo

- **`_old/`** contiene un fork editado manualmente para hacer diff contra el nuevo código. No desarrollar aquí.
- **`origin/erp/`** es la fuente upstream sin tocar. No modificar — usar para comparaciones con `diff`.
- **`notes/`** contiene copias antiguas de problemas conocidos. Las versiones canónicas están en **`_inc/laravel/.notes/`**. Actualizar esas.
- **`obf.js`** es un archivo de ofuscación del mapa de rutas. **Debe estar git-ignored en despliegues de producción**.
- Los archivos **`_test.*`** en la raíz son para experimentos rápidos. Están git-ignored.

---

## Licencia

Ver [LICENSE](LICENSE).

</details>

---

Sistema de Planejamento de Recursos Empresariais para **Nova Brand New Ideas Company**. Este repositório é o monorepo da equipe que contém a aplicação Laravel de produção, código de referência original, notas de trabalho, scripts utilitários e prompts de LLM utilizados durante o desenvolvimento.

---

## Estrutura do repositório

```
.
├── _inc/                          # Desenvolvimento ativo
│   ├── laravel/                   # ⭐ Aplicação principal (Laravel 10 + PHP 8.3)
│   │   ├── app/                   #    Backend: Controllers, Models, Traits, Providers
│   │   ├── Modules/LandingPage/   #    Módulo nWidart (landing, termos, privacidade, sobre)
│   │   ├── resources/views/       #    Templates Blade
│   │   ├── public/assets/         #    JS (ERPGuard, ERPUtils, dash) + CSS
│   │   ├── routes/                #    web.php, api.php, auth.php
│   │   ├── database/migrations/   #    Migrações de esquema
│   │   ├── database/seeders/      #    Seeders de teste + produção
│   │   ├── tests/                 #    PHPUnit (Unit+Feature) + Jest/TS frontend
│   │   ├── Dockerfile             #    Build multi-estágio (Node + PHP-FPM)
│   │   ├── docker-compose.yml     #    app · nginx · mysql · redis
│   │   └── README.md              #    Guia de configuração e execução do projeto
│   │   └── utils/                 #    Espaço de trabalho de ferramentas (scripts, prompts, auditorias)
│   │       ├── scripts/           #    sh/ py/ js/ php/ ts-harness/
│   │       ├── prompts/           #    Guias de estilo por linguagem
│   │       ├── cli/ grep/ find/ regex/ cmds/  Registros de comandos datados
│   │       └── ...                #    Ver _inc/laravel/utils/README.md
│   └── utils/                     # Contexto a nível de monorepo (mínimo)
│       └── .llms/
│
├── _old/                          # Fork com modificações experimentais (apenas referência)
│   ├── app/                       #    Controllers, Models com edições manuais
│   ├── routes/                    #    Arquivos de rotas com alterações inline
│   └── ...                        #    Espelha a estrutura original
│
├── origin/                        # Fork upstream não modificado (upstream source)
│   └── erp/                       #    Árvore fonte original — somente leitura
│
├── notes/                         # Documentação da equipe (cópias antigas — ver _inc/laravel/.notes/)
│   ├── CURRENT_WORKING_ISSUES.md  #    Relatório de saúde das rotas (2026-02-07)
│   └── KNOWN_ISSUES.md            #    Problemas conhecidos
│
├── obf.js                         # Camada de ofuscação do mapa de rotas (NÃO fazer deploy)
├── LICENSE                        # Licença do projeto
├── .gitignore                     # Ignora: ._DEPRECATED_*, .vscode, vendor, node_modules, logs
└── README.md                      # ← Você está aqui
```

> **As pastas `._DEPRECATED_*`** (django, flutter, frontend, erp-brand-new-ideas-company-frontend) estão **git-ignored** e purgadas do histórico. Permanecem em disco apenas como referência local.

---

## Mapa de guias e contexto LLM

Todas as convenções, decisões arquiteturais e instruções para agentes estão em três árvores de guias.
Consulte [`where-to-update-and-read.yml`](where-to-update-and-read.yml) para o **mapa de arquitetura do sistema de arquivos** com o layout completo e cada caminho que um agente LLM ou desenvolvedor deve verificar.

| Árvore           | Caminho                                                                 | Escopo                                                                 |
| ---------------- | ----------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| Guias primárias  | `_inc/laravel/.notes/.llms/.guidelines/`                                | Arquitetura, backend, frontend, BD, módulos, segurança, testes, papéis |
| Guias de estilo  | `_inc/laravel/utils/prompts/.guidelines/`                               | Regras por linguagem (PHP, JS, TS, Python, CSS, React)                 |
| Guias da app     | `_inc/laravel/.notes/.llms/`                                            | Relatórios da app, mapas de roleplay e guias de teste                  |
| Config de agente | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` | Comportamento do agente                                                |
| Contexto LLM     | `_inc/laravel/utils/.llms/`                                             | Logs de CLI, contexto de agentes, notas de trabalho                    |

---

## Stack tecnológico

| Camada             | Tecnologia                                           | Versão |
| ------------------ | ---------------------------------------------------- | ------ |
| Linguagem          | PHP                                                  | 8.3    |
| Framework          | Laravel                                              | 10.x   |
| Sistema de módulos | nWidart/laravel-modules                              | 10.x   |
| Banco de dados     | MySQL / MariaDB                                      | 8.0+   |
| Cache / Fila       | Redis (opcional, driver file por padrão)             | 7.x    |
| Bundler frontend   | Laravel Mix (Webpack)                                | 6.x    |
| Framework CSS      | Tailwind CSS + Bootstrap 5                           | —      |
| Arquitetura JS     | Vanilla ES2022 (IIFE singletons)                     | —      |
| Testes (backend)   | PHPUnit                                              | 10.x   |
| Testes (frontend)  | Jest + ts-jest (jsdom)                               | 29.x   |
| Análise estática   | PHPStan (level 5)                                    | 2.x    |
| Conteinerização    | Docker + Docker Compose                              | —      |
| Auth               | Laravel Fortify (prefixo personalizado `/fortify-*`) | —      |
| IDs                | UUID (não auto-increment)                            | —      |

---

## Convenções principais

- Os **Controllers** são organizados por domínio em `app/Http/Controllers/{Activity,Bills,Bugs,Charts,Companies,Configs,Contact,Individuals,Info,Planning,Products,Shapes,Ssr}/`.
- Os **Models** espelham a mesma estrutura de domínio em `app/Models/`.
- As **Constants** ficam em classes dedicadas `*Constants.php` (ViewsConstants, MiddlewareConstants, DataConstants, etc.) — strings literais são evitadas em rotas e controllers.
- Os **Traits** (`ChecksLogin`, `ChecksPermissions`, `HasCurrency`, `MeasuresPerformance`, …) são mixados em controllers e models.
- Constantes de **Middleware**: `MWC::AUTH`, `MWC::VF`, `MWC::XSS`, `MWC::REV`, `MWC::WEB`.
- As **Traduções** usam arquivos JSON em `resources/lang/{en,pt-br,ru,…}.json` (16 idiomas, ~3.000 chaves cada).

---

## Início rápido (desenvolvimento)

```bash
# 1. Clonar
git clone <repo-url> && cd erp_brand_new_ideas_company

# 2. Entrar no projeto
cd _inc/laravel

# 3. Instalar dependências
composer install
npm ci

# 4. Ambiente
cp .env.example .env          # depois editar DB_*, APP_KEY, etc.
php artisan key:generate

# 5. Banco de dados
php artisan migrate --seed

# 6. Servir
php artisan serve              # http://localhost:8000
```

Ou com Docker:

```bash
cd _inc/laravel
docker compose up -d --build
docker compose exec app php artisan migrate --seed
# → http://localhost:8080
```

Veja [\_inc/laravel/README.md](_inc/laravel/README.md) para o guia completo do projeto.

---

## Testes

### Backend (PHPUnit)

```bash
cd _inc/laravel
composer run test:unit                    # suite de unidades (seguro — usa DB de teste)
composer run test:feature                 # suite de features (seguro — usa DB de teste)
php -d memory_limit=1G vendor/bin/phpunit --testsuite Unit --no-coverage
php -d memory_limit=1G vendor/bin/phpunit --filter=UserTest --no-coverage
# ⚠️  Nunca usar `php artisan test` — usa a DB live e pode apagar dados
```

### Frontend (Jest)

```bash
cd _inc/laravel
npm test                                  # 510+ testes
npm test -- --testPathPattern="erp-guard" # suite específica
```

### Análise estática (PHPStan)

```bash
cd _inc/laravel
vendor/bin/phpstan analyse
```

### Roleplay de segurança (Jest)

```bash
cd _inc/laravel
npx jest --config=jest.config.cjs --testPathPatterns="tests/Unit/security/roleplay"
# 17 suites · 187 testes — multi-linguagem (JS, WASM, PHP, Python, Bash)
# Roles: black-hat, green-hat, white-hat, CISO, backend-dev, QA
```

Ver [`_inc/laravel/tests/Feature/security/roleplay/README.md`](_inc/laravel/tests/Feature/security/roleplay/README.md) para a documentação completa do framework.

---

## Scripts utilitários

| Arquivo                                                          | Finalidade                                                        |
| ---------------------------------------------------------------- | ----------------------------------------------------------------- |
| `_inc/laravel/utils/regexes.txt`                                 | Padrões regex reutilizáveis para auditorias                       |
| `_inc/laravel/utils/cli/`                                        | Registros de comandos CLI datados                                 |
| `_inc/laravel/utils/grep/`                                       | Notas de comandos grep datadas                                    |
| `_inc/laravel/utils/find/`                                       | Notas de comandos find datadas                                    |
| `_inc/laravel/utils/regex/`                                      | Notas de padrões regex datados                                    |
| `_inc/laravel/utils/scripts/py/analysis/compare_funcs_models.py` | Comparar assinaturas de métodos entre modelos old/new             |
| `_inc/laravel/utils/scripts/py/analysis/compare_funcs_names.py`  | Diff de nomes de funções entre diretórios                         |
| `_inc/laravel/utils/scripts/py/analysis/rearrange.py`            | Reorganizar sentenças de import                                   |
| `_inc/laravel/utils/scripts/py/analysis/read_deps.py`            | Parsear árvores de dependências composer/package                  |
| `_inc/laravel/utils/prompts/`                                    | Templates de prompts XML/Markdown para migração assistida por LLM |
| `_inc/laravel/utils/README.md`                                   | Guia completo do espaço de trabalho de utilidades                 |

---

## Notas para a equipe

- **`_old/`** contém um fork editado manualmente mantido para fazer diff contra o código novo. Não desenvolver aqui.
- **`origin/erp/`** é a fonte upstream intocada. Não modificar — usar para comparações com `diff`.
- **`notes/`** contém cópias antigas de problemas conhecidos. As versões canônicas estão em **`_inc/laravel/.notes/`**. Atualizar essas.
- **`obf.js`** é um arquivo de ofuscação do mapa de rotas. **Deve estar git-ignored em deploys de produção** (veja o alerta de segurança dentro do arquivo).
- Os arquivos **`_test.*`** na raiz são rascunhos para experimentos rápidos. Estão git-ignored.

---

## Licença

Veja [LICENSE](LICENSE).
