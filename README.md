# ERP Prestech — Monorepo

<details>
<summary>🇺🇸 English</summary>

Enterprise Resource Planning system for **Nova Prestech**. This repository is the team-wide monorepo that holds the production Laravel application, original reference code, working notes, utility scripts and LLM prompts used during development.

---

## Repository layout

```
.
├── _inc/                          # Active development
│   ├── laravel/                   # ⭐ Main application (Laravel 10 + PHP 8.3)
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
│   └── utils/                     # Developer tooling
│       ├── regexes.md             #    Regex patterns for codebase searches
│       ├── greps.md               #    Recommended grep commands
│       ├── finds.md               #    Recommended find commands
│       ├── prompts/               #    LLM prompt templates (migration, seeding, etc.)
│       └── py/                    #    Python helper scripts (compare funcs, rearrange)
│
├── _old/                          # Fork with experimental modifications (reference only)
│   ├── app/                       #    Controllers, Models with manual edits
│   ├── routes/                    #    Route files with inline changes
│   └── ...                        #    Mirrors original structure
│
├── origin/                        # Unmodified upstream fork (ERPGo)
│   └── erp/erpgo/                 #    Original source tree — read-only reference
│
├── notes/                         # Team documentation
│   ├── CURRENT_WORKING_ISSUES.md  #    Route health report & active bugs
│   ├── KNOWN_ISSUES.md            #    Migration naming, known constraints
│   ├── CONSTANTS_AUDIT_REPORT.md  #    Constants refactoring audit
│   ├── CONSTANTS_AUDIT_FIX_LOG.md #    Constants fix changelog
│   └── modules_v2.html            #    Module dependency graph (visual)
│
├── obf.js                         # Route-map obfuscation layer (DO NOT deploy)
├── LICENSE                        # Project licence
├── .gitignore                     # Ignores: ._DEPRECATED_*, .vscode, vendor, node_modules, logs
└── README.md                      # ← You are here
```

> **`._DEPRECATED_*` folders** (django, flutter, frontend, erp-prestech-frontend) are **git-ignored** and purged from history. They remain on disk for local reference only.

---

## Guidelines & LLM context map

All conventions, architecture decisions, and agent instructions live under three guideline trees.
See [`where-to-update-and-read.yml`](where-to-update-and-read.yml) for the canonical **filesystem architecture map** with the full directory layout and every path an LLM agent or developer must check.

| Tree | Path | Scope |
|---|---|---|
| Primary guidelines | [`.notes/.llms/.guidelines/`](.notes/.llms/.guidelines/) | Architecture, backend, frontend, DB, modules, security, testing, roles |
| Coding-style guides | [`_inc/utils/prompts/.guidelines/`](_inc/utils/prompts/.guidelines/) | Per-language rules (PHP, JS, TS, Python, CSS, React) in md/xml/yml/toml |
| App-specific guides | [`_inc/laravel/.notes/.llms/.guidelines/`](_inc/laravel/.notes/.llms/.guidelines/) | Security roleplay profiles, test maps |
| Agent config | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` | Copilot/agent behaviour |
| LLM session context | [`_inc/utils/.llms/`](_inc/utils/.llms/) | CLI logs, agent context, working notes |

---

## Tech stack

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.3 |
| Framework | Laravel | 10.x |
| Module system | nWidart/laravel-modules | 10.x |
| Database | MySQL / MariaDB | 8.0+ |
| Cache / Queue | Redis (optional, file driver default) | 7.x |
| Frontend bundler | Laravel Mix (Webpack) | 6.x |
| CSS framework | Tailwind CSS + Bootstrap 5 | — |
| JS architecture | Vanilla ES2022 (IIFE singletons) | — |
| Testing (backend) | PHPUnit | 10.x |
| Testing (frontend) | Jest + ts-jest (jsdom) | 29.x |
| Static analysis | PHPStan (level 5) | 2.x |
| Containerisation | Docker + Docker Compose | — |
| Auth | Laravel Fortify (custom `/fortify-*` prefix) | — |
| IDs | UUID (not auto-increment) | — |

---

## Key conventions

- **Controllers** are organised by domain under `app/Http/Controllers/{Activity,Bills,Bugs,Charts,Companies,Configs,Contact,Individuals,Info,Planning,Products,Shapes,Ssr}/`.
- **Models** mirror the same domain structure under `app/Models/`.
- **Constants** live in dedicated `*Constants.php` classes (ViewsConstants, MiddlewareConstants, DataConstants, etc.) — raw strings are avoided in routes and controllers.
- **Traits** (`ChecksLogin`, `ChecksPermissions`, `HasCurrency`, `MeasuresPerformance`, …) are mixed into controllers and models.
- **Middleware** constants: `MWC::AUTH`, `MWC::VF`, `MWC::XSS`, `MWC::REV`, `MWC::WEB`.
- **Translations** use JSON files at `resources/lang/{en,pt-br,ru,…}.json` (16 languages, ~3,000 keys each).

---

## Quick start (development)

```bash
# 1. Clone
git clone <repo-url> && cd erp_prestech

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
php artisan test                          # all suites
php artisan test --filter=UserTest        # specific test
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

| File | Purpose |
|---|---|
| `_inc/utils/regexes.md` | Regex patterns for codebase audits |
| `_inc/utils/greps.md` | `grep` one-liners for debugging |
| `_inc/utils/finds.md` | `find` one-liners for file discovery |
| `_inc/utils/py/compare_funcs_models.py` | Compare method signatures between old/new models |
| `_inc/utils/py/compare_funcs_names.py` | Diff function names across directories |
| `_inc/utils/py/rearrange.py` | Rearrange import statements |
| `_inc/utils/py/read_deps.py` | Parse composer/package dependency trees |
| `_inc/utils/prompts/` | XML/Markdown prompt templates for LLM-assisted migration |

---

## Notes for the team

- **`_old/`** contains a manually-edited fork kept for diffing against the new codebase. Do not develop here.
- **`origin/erp/erpgo/`** is the untouched upstream source. Do not modify — use it for `diff` comparisons.
- **`notes/`** holds living documents about known issues, constants audits, and module graphs. Update them as you work.
- **`obf.js`** is a route-map obfuscation file. **Must be git-ignored in production deployments** (see the security alert inside the file).
- **`_test.*` files** at root are scratch pads for quick experiments. They are git-ignored.

---

## Licence

See [LICENSE](LICENSE).

</details>

<details>
<summary>🇪🇸 Español</summary>

Sistema de Planificación de Recursos Empresariales para **Nova Prestech**. Este repositorio es el monorepo del equipo que contiene la aplicación Laravel de producción, el código de referencia original, notas de trabajo, scripts utilitarios y prompts de LLM utilizados durante el desarrollo.

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
│   └── utils/                     # Herramientas para desarrolladores
│       ├── regexes.md             #    Patrones regex para auditorías del código
│       ├── greps.md               #    Comandos grep recomendados
│       ├── finds.md               #    Comandos find recomendados
│       ├── prompts/               #    Templates de prompts LLM (migración, seeding, etc.)
│       └── py/                    #    Scripts auxiliares en Python
│
├── _old/                          # Fork con modificaciones experimentales (solo referencia)
├── origin/                        # Fork upstream sin modificar (ERPGo)
├── notes/                         # Documentación del equipo
├── obf.js                         # Capa de ofuscación del mapa de rutas (NO desplegar)
├── LICENSE                        # Licencia del proyecto
├── .gitignore                     # Ignora: ._DEPRECATED_*, .vscode, vendor, node_modules, logs
└── README.md                      # ← Estás aquí
```

> **Las carpetas `._DEPRECATED_*`** (django, flutter, frontend, erp-prestech-frontend) están **git-ignored** y purgadas del historial. Permanecen en disco solo como referencia local.

---

## Mapa de guías y contexto LLM

Todas las convenciones, decisiones arquitectónicas e instrucciones para agentes están en tres árboles de guías.
Consulte [`where-to-update-and-read.yml`](where-to-update-and-read.yml) para el **mapa de arquitectura del sistema de archivos** con el diseño completo y cada ruta que un agente LLM o desarrollador debe verificar.

| Árbol | Ruta | Alcance |
|---|---|---|
| Guías primarias | `.notes/.llms/.guidelines/` | Arquitectura, backend, frontend, BD, módulos, seguridad, testing, roles |
| Guías de estilo | `_inc/utils/prompts/.guidelines/` | Reglas por lenguaje (PHP, JS, TS, Python, CSS, React) |
| Guías de la app | `_inc/laravel/.notes/.llms/.guidelines/` | Perfiles de roleplay de seguridad, mapas de tests |
| Config de agente | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` | Comportamiento del agente |
| Contexto LLM | `_inc/utils/.llms/` | Logs de CLI, contexto de agentes, notas de trabajo |

---

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Lenguaje | PHP | 8.3 |
| Framework | Laravel | 10.x |
| Sistema de módulos | nWidart/laravel-modules | 10.x |
| Base de datos | MySQL / MariaDB | 8.0+ |
| Cache / Cola | Redis (opcional, driver file por defecto) | 7.x |
| Bundler frontend | Laravel Mix (Webpack) | 6.x |
| Framework CSS | Tailwind CSS + Bootstrap 5 | — |
| Arquitectura JS | Vanilla ES2022 (IIFE singletons) | — |
| Testing (backend) | PHPUnit | 10.x |
| Testing (frontend) | Jest + ts-jest (jsdom) | 29.x |
| Análisis estático | PHPStan (level 5) | 2.x |
| Contenedorización | Docker + Docker Compose | — |
| Auth | Laravel Fortify (prefijo personalizado `/fortify-*`) | — |
| IDs | UUID (no auto-increment) | — |

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
git clone <repo-url> && cd erp_prestech

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
php artisan test                          # todas las suites
php artisan test --filter=UserTest        # test específico
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

| Archivo | Propósito |
|---|---|
| `_inc/utils/regexes.md` | Patrones regex para auditorías del código |
| `_inc/utils/greps.md` | Comandos `grep` para depuración |
| `_inc/utils/finds.md` | Comandos `find` para descubrimiento de archivos |
| `_inc/utils/py/compare_funcs_models.py` | Comparar firmas de métodos entre modelos old/new |
| `_inc/utils/py/compare_funcs_names.py` | Diff de nombres de funciones entre directorios |
| `_inc/utils/py/rearrange.py` | Reorganizar sentencias de import |
| `_inc/utils/py/read_deps.py` | Parsear árboles de dependencias composer/package |
| `_inc/utils/prompts/` | Templates de prompts para migración asistida por LLM |

---

## Notas para el equipo

- **`_old/`** contiene un fork editado manualmente para hacer diff contra el nuevo código. No desarrollar aquí.
- **`origin/erp/erpgo/`** es la fuente upstream sin tocar. No modificar — usar para comparaciones con `diff`.
- **`notes/`** contiene documentos activos sobre problemas conocidos, auditorías de constantes y grafos de módulos.
- **`obf.js`** es un archivo de ofuscación del mapa de rutas. **Debe estar git-ignored en despliegues de producción**.
- Los archivos **`_test.*`** en la raíz son para experimentos rápidos. Están git-ignored.

---

## Licencia

Ver [LICENSE](LICENSE).

</details>

---

Sistema de Planejamento de Recursos Empresariais para **Nova Prestech**. Este repositório é o monorepo da equipe que contém a aplicação Laravel de produção, código de referência original, notas de trabalho, scripts utilitários e prompts de LLM utilizados durante o desenvolvimento.

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
│   └── utils/                     # Ferramentas para desenvolvedores
│       ├── regexes.md             #    Padrões regex para auditorias do código
│       ├── greps.md               #    Comandos grep recomendados
│       ├── finds.md               #    Comandos find recomendados
│       ├── prompts/               #    Templates de prompts LLM (migração, seeding, etc.)
│       └── py/                    #    Scripts auxiliares em Python (comparar funções, reorganizar)
│
├── _old/                          # Fork com modificações experimentais (apenas referência)
│   ├── app/                       #    Controllers, Models com edições manuais
│   ├── routes/                    #    Arquivos de rotas com alterações inline
│   └── ...                        #    Espelha a estrutura original
│
├── origin/                        # Fork upstream não modificado (ERPGo)
│   └── erp/erpgo/                 #    Árvore fonte original — somente leitura
│
├── notes/                         # Documentação da equipe
│   ├── CURRENT_WORKING_ISSUES.md  #    Relatório de saúde das rotas e bugs ativos
│   ├── KNOWN_ISSUES.md            #    Nomes de migração, restrições conhecidas
│   ├── CONSTANTS_AUDIT_REPORT.md  #    Auditoria de refatoração de constantes
│   ├── CONSTANTS_AUDIT_FIX_LOG.md #    Changelog de correções de constantes
│   └── modules_v2.html            #    Grafo de dependências de módulos (visual)
│
├── obf.js                         # Camada de ofuscação do mapa de rotas (NÃO fazer deploy)
├── LICENSE                        # Licença do projeto
├── .gitignore                     # Ignora: ._DEPRECATED_*, .vscode, vendor, node_modules, logs
└── README.md                      # ← Você está aqui
```

> **As pastas `._DEPRECATED_*`** (django, flutter, frontend, erp-prestech-frontend) estão **git-ignored** e purgadas do histórico. Permanecem em disco apenas como referência local.

---

## Mapa de guias e contexto LLM

Todas as convenções, decisões arquiteturais e instruções para agentes estão em três árvores de guias.
Consulte [`where-to-update-and-read.yml`](where-to-update-and-read.yml) para o **mapa de arquitetura do sistema de arquivos** com o layout completo e cada caminho que um agente LLM ou desenvolvedor deve verificar.

| Árvore | Caminho | Escopo |
|---|---|---|
| Guias primárias | `.notes/.llms/.guidelines/` | Arquitetura, backend, frontend, BD, módulos, segurança, testes, papéis |
| Guias de estilo | `_inc/utils/prompts/.guidelines/` | Regras por linguagem (PHP, JS, TS, Python, CSS, React) |
| Guias da app | `_inc/laravel/.notes/.llms/.guidelines/` | Perfis de roleplay de segurança, mapas de testes |
| Config de agente | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` | Comportamento do agente |
| Contexto LLM | `_inc/utils/.llms/` | Logs de CLI, contexto de agentes, notas de trabalho |

---

## Stack tecnológico

| Camada | Tecnologia | Versão |
|---|---|---|
| Linguagem | PHP | 8.3 |
| Framework | Laravel | 10.x |
| Sistema de módulos | nWidart/laravel-modules | 10.x |
| Banco de dados | MySQL / MariaDB | 8.0+ |
| Cache / Fila | Redis (opcional, driver file por padrão) | 7.x |
| Bundler frontend | Laravel Mix (Webpack) | 6.x |
| Framework CSS | Tailwind CSS + Bootstrap 5 | — |
| Arquitetura JS | Vanilla ES2022 (IIFE singletons) | — |
| Testes (backend) | PHPUnit | 10.x |
| Testes (frontend) | Jest + ts-jest (jsdom) | 29.x |
| Análise estática | PHPStan (level 5) | 2.x |
| Conteinerização | Docker + Docker Compose | — |
| Auth | Laravel Fortify (prefixo personalizado `/fortify-*`) | — |
| IDs | UUID (não auto-increment) | — |

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
git clone <repo-url> && cd erp_prestech

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
php artisan test                          # todas as suites
php artisan test --filter=UserTest        # teste específico
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

| Arquivo | Finalidade |
|---|---|
| `_inc/utils/regexes.md` | Padrões regex para auditorias do código |
| `_inc/utils/greps.md` | Comandos `grep` para depuração |
| `_inc/utils/finds.md` | Comandos `find` para descoberta de arquivos |
| `_inc/utils/py/compare_funcs_models.py` | Comparar assinaturas de métodos entre modelos old/new |
| `_inc/utils/py/compare_funcs_names.py` | Diff de nomes de funções entre diretórios |
| `_inc/utils/py/rearrange.py` | Reorganizar sentenças de import |
| `_inc/utils/py/read_deps.py` | Parsear árvores de dependências composer/package |
| `_inc/utils/prompts/` | Templates de prompts XML/Markdown para migração assistida por LLM |

---

## Notas para a equipe

- **`_old/`** contém um fork editado manualmente mantido para fazer diff contra o código novo. Não desenvolver aqui.
- **`origin/erp/erpgo/`** é a fonte upstream intocada. Não modificar — usar para comparações com `diff`.
- **`notes/`** contém documentos vivos sobre problemas conhecidos, auditorias de constantes e grafos de módulos. Atualizar conforme o trabalho avança.
- **`obf.js`** é um arquivo de ofuscação do mapa de rotas. **Deve estar git-ignored em deploys de produção** (veja o alerta de segurança dentro do arquivo).
- Os arquivos **`_test.*`** na raiz são rascunhos para experimentos rápidos. Estão git-ignored.

---

## Licença

Veja [LICENSE](LICENSE).
