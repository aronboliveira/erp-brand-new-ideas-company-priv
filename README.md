# 🗂 Principais Diretórios

<details>
  <summary><strong>📁 Estrutura dos Diretórios</strong></summary>

- **\_inc/**  
   Versão para testar módulos de desenvolvimento:

  - `django/` – [Arquivado] Em sua maior parte desatualizado, mas módulos para exportar/manipular planilhas (ou ML) serão usados como microsserviços
  - `erp-prestech-frontend/` – [Arquivado] Mantido somente para consulta. Movido para laravel/frontend/
  - `laravel/` – [CORE] Será usado tanto para backend quanto para frontend
  - `utils/` – [Adicional] Scripts auxiliares para gerenciamento do ambiente de trabalho e produtividade no terminal + Prompts para LLMs

- **\_old/**  
  Versão "fork" da original com ajustes que podem causar incompatibilidades (para testes)

- **origin/**  
  Fork original (sem alterações)

</details>

# ⚙️ Instruções de Setup

<details>
  <summary><strong>1. 🔧 Instalação de Dependências</strong></summary>

## Backend

```bash
composer update --with-all-dependencies
composer install
```

## Frontend

```bash
npm update
npm i
npx tailwind init
```

</details>

<details>
<summary><strong>2. 📅 Instalação do Banco de Dados </strong></summary>
    
## Criar banco de dados e usuário

```bash
CREATE DATABASE IF NOT EXISTS erp_prestech_db;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL PRIVILEGES ON test.* TO 'test'@'localhost';
FLUSH PRIVILEGES;
```

## Limpar logs (opcional)

```bash
composer clear-logs
```

## Limpar caches e otimizar

```bash
php artisan clear-compiled
php artisan permission:clear-cache
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
### Experimental: POWERSHELL -> composer artclrs-ps / UNIX -> composer artclrs-sh
### Experimental: artclrs inclui comandos até route:list e aciona php artisan serve
```

## Remover arquivos em cache

```bash
rm -f bootstrap/cache/{compiled,services,packages,routes}.php
rm -rf storage/framework/cache/data/\*
```

## Reconstruir autoloader e banco de dados

```bash
composer dump-autoload -o
php artisan migrate:reset || php artisan db:wipe
php artisan migrate:fresh --seed
```

# Checar saúde de controladores e rotas disponíveis

```bash
php artisan route:list --sort=uri
```

</details>
<details>
<summary><strong>3. 🔛 Execução </strong></summary>

## Backend

```bash
php artisan serve
#  public/index.php >
#  bootstrap/app.php >
#  register app/Providers/AppServiceProvider.php >
#  register app/Providers/BroadcastServieProvider.php >
#  register LandingPageServiceProvider >
#  register AddMenuProvider >
#  boot app/Providers/RouteServiceProvider.php >
#  map routes/* >
#  hit HttpKernel >
#  map routes/* >
#  register Modules/LandingPage/Providers/RouteServiceProvider >
#  map routes/* >
#  hit HttpKernel >
#  map routes/* >
#  boot AppServiceProvider >
#  boot AuthServiceProvider >
#  boot app/Providers/BroadcastServiceProvider >
#  boot app/Providers/EventServiceProvider >
#  boot app/Providers/RouteServiceProvider.php >
#  configure app/Providers/RouteServiceProvider.php >
#  boot Modules/LandingPage/Providers/RouteServiceProvider >
#  configure Modules/LandingPage/Providers/RouteServiceProvider >
#  boot Modules/LandingPage/LandingPageServiceProvider
```

## Frontend

```bash
npm run dev
```

</details>

<details>

# 💹 Fluxos

<details>
<summary><strong>1. 🔐 Login</strong></summary>
- Calls the ApiController::login method
</details>

</details>

# NOTAS

-> Setting do not have a model
-> PersonalAcessToken do not have a model
-> GeneratePayslipOptions do not have a model
-> FailedJobs do not have a model

---

# 🧪 Test Status (2026-03-05)

| Suite | Result |
|-------|--------|
| **ESLint** (frontend) | 0 errors · 0 warnings on modified files |
| **Jest** (core + frontend) | 10 / 10 passed |
| **Pytest** | 53 / 53 passed |
| **Playwright RBAC hardening** | 5 previously-failing → fixed |
| **HTTP batch** (1050 routes) | 0 × 500 (all previously-500 routes fixed) |
| **MySQL** | 8.4.7 · 21 tables · DB healthy |

See `.notes/CURRENT_WORKING_ISSUES.md` for change history and `.tmp/FIXES_20260305.md` for this session's fix log.
