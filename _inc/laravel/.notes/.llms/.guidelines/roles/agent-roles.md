# Perfis de Subagentes / Agent Role Definitions

> Combinações predefinidas de guidelines para subagentes especializados.

## Perfis Disponíveis

### 1. Backend Engineer — Billing

**Leia antes de operar:**

- `backend/erp-architecture.md` — visão geral
- `backend/services/delegation-patterns.md` — padrão de serviços
- `backend/controllers/controller-patterns.md` — padrões de controller
- `modules/billing/billing-guide.md` — domínio de faturamento
- `database/database-guide.md` — schema e migrations
- `_inc/laravel/utils/prompts/.guidelines/php/financial.md` — regras financeiras
- `testing/test-architecture.json` — como testar

### 2. Backend Engineer — HRM

**Leia antes de operar:**

- `backend/erp-architecture.md`
- `backend/models/model-conventions.md`
- `modules/hrm/hrm-guide.md`
- `database/database-guide.md`
- `_inc/laravel/utils/prompts/.guidelines/php/models.md`
- `_inc/laravel/utils/prompts/.guidelines/php/controllers.md`

### 3. Backend Engineer — CRM

**Leia antes de operar:**

- `backend/erp-architecture.md`
- `modules/crm/crm-guide.md`
- `backend/models/model-conventions.md`
- `database/database-guide.md`

### 4. Backend Engineer — Accounting

**Leia antes de operar:**

- `backend/services/delegation-patterns.md`
- `modules/accounting/accounting-guide.md`
- `modules/billing/billing-guide.md`
- `_inc/laravel/utils/prompts/.guidelines/php/financial.md`
- `database/database-guide.md`

### 5. Frontend Engineer — Blade/JS

**Leia antes de operar:**

- `frontend/blade/blade-conventions.md`
- `frontend/assets/js-singletons.md`
- `frontend/esm-iife-strategy.md`
- `_inc/laravel/utils/prompts/.guidelines/javascript/coding-rules.md`
- `_inc/laravel/utils/prompts/.guidelines/css/styling.md`

### 6. Frontend Engineer — TypeScript

**Leia antes de operar:**

- `frontend/typescript/typescript-guide.md`
- `frontend/assets/js-singletons.md`
- `_inc/laravel/utils/prompts/.guidelines/typescript/type-safety.md`
- `_inc/laravel/utils/prompts/.guidelines/javascript/singletons.md`
- `testing/typescript-test-harness.md`

### 7. QA Engineer — PHPUnit

**Leia antes de operar:**

- `testing/test-architecture.json`
- `testing/test_suites.xml`
- `_inc/laravel/utils/prompts/.guidelines/php/testing.md`
- `backend/services/delegation-patterns.md` (para entender stubs)
- `database/database-guide.md` (para seeding)

### 8. QA Engineer — Playwright E2E

**Leia antes de operar:**

- `testing/test-architecture.json`
- `testing/ci.yml`
- `frontend/blade/blade-conventions.md`
- `security/security-patterns.xml`

### 9. DevOps — Infrastructure

**Leia antes de operar:**

- `infrastructure/server.toml`
- `testing/ci.yml`
- `security/security-patterns.xml`
- `database/database-guide.md`

### 10. Full-Stack — Service Delegation

**Leia antes de operar:**

- `backend/services/delegation-patterns.md`
- `backend/models/model-conventions.md`
- `backend/controllers/controller-patterns.md`
- Módulo relevante em `modules/`
- `testing/test-architecture.json`
- `_inc/laravel/utils/prompts/.guidelines/php/testing.md`

## Como Injetar Contexto

Para subagente carregar perfil automaticamente:

```
Prompt: "Você é um Backend Engineer — Billing.
Leia os seguintes arquivos de guideline antes de operar:
[lista de arquivos do perfil acima]"
```

## Fork / Handoff Checklist

Ao passar contexto para outro agente, mantenha curto e verificável:

1. Branch, HEAD, e status do worktree.
2. Arquivos alterados e arquivos deliberadamente não tocados.
3. Comandos executados com resultado exato.
4. Último teste amplo confiável e qualquer teste que ainda falte rodar.
5. Restrições ativas: não editar migrations, não editar `_inc/.seeders/`,
   não usar `php artisan test`.
6. Próximo passo concreto, não uma lista longa de possibilidades.

## Regras Universais (todos os perfis)

1. Comentários em português brasileiro
2. Imports agrupados com `{}`, ordem alfabética
3. `static::` ao invés de `self::`
4. UUID PKs em todos os modelos
5. `DB::transaction()` para operações financeiras
6. Nunca deletar métodos de `Utility.php`
7. `@test` annotations em PHPDoc (não prefix `test_`)
8. Named log channels: `Log::channel('nome')`
