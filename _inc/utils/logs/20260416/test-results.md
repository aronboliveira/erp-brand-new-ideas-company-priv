# Relatório de Testes — 2026-04-16

> Execução completa de todas as suítes de teste do projeto ERP Prestech.
> Ambiente: PHP 8.4.5, Node v22.22.0, Python 3.13.3 (sem MySQL local ativo)

---

## Resumo Geral

| Suíte         | Resultado                                | Status      |
|---------------|------------------------------------------|-------------|
| PHPStan       | 2 erros (métodos estáticos não encontrados) | ⚠️ Warning  |
| ESLint        | 10.035 problemas (9.986 erros, 49 warnings) | ❌ Erros    |
| tsc           | ~15 erros TS7006/TS7005                   | ⚠️ Warning  |
| Flake8        | 29 issues (17 E501, 7 F401, 4 W293)      | ⚠️ Warning  |
| mypy          | Diretório `resources/python/` não encontrado | ⚠️ Warning  |
| Jest (CJS)    | 34 suítes, **724 testes — TODOS PASSARAM** | ✅ Pass     |
| Jest (TS)     | 11 suítes, 949 testes — 944 passed, 5 failed | ⚠️ Warning  |
| Playwright    | **9 passed**, 3 skipped (4.3 min)        | ✅ Pass     |
| pytest        | **268 passed**, 171 skipped, 5 warnings  | ✅ Pass     |
| PHPUnit       | 12.832 testes, 2.129 assertions, 12.134 errors | ❌ Erros¹   |

> ¹ PHPUnit: Erros causados por `SQLSTATE[HY000] [2002] Connection refused` — sem servidor MySQL local ativo. Os 698 testes que não dependem de conexão com banco passaram normalmente.

---

## Detalhes por Suíte

### PHPStan (nível: phpstan.neon)
- **Memória**: Necessita ≥2G (OOM com 1G)
- **Erros encontrados**: 2
  1. `Http/Controllers/Individuals/JobApplicationController.php:161` — `App\Models\JobApplicationNote::whereApplicationId()` (staticMethod.notFound)
  2. `Services/Utility/AccountingService.php:309` — `App\Models\Revenue::withoutEagerLoads()` (staticMethod.notFound)

### ESLint
- **Problemas**: 10.035 (9.986 erros, 49 warnings)
- **Erros dominantes**: `no-undef` em arquivos minificados/bundled no diretório `public/`
- **Nota**: A maioria são globais do browser (jQuery, $, DataTable, etc.) em arquivos legados

### TypeScript Compiler (tsc)
- **Erros**: ~15 TS7006/TS7005 em `rbac-test-utils.ts`
- **Tipo**: Parâmetros e variáveis com tipo `any` implícito

### Flake8
- 17× E501 (linha muito longa)
- 7× F401 (imports não utilizados)
- 4× W293 (whitespace antes de indentação)
- 1× E902 (diretório ausente)

### mypy
- Erro: `resources/python/` não encontrado como source directory
- Não há código Python em `resources/python/` para analisar

### Jest CJS (testes backend/unit)
- **34 suítes, 724 testes — TODOS PASSARAM** ✅
- Tempo: 13.9s

### Jest TS (testes frontend)
- **11 suítes, 949 testes**
- 944 passed, 5 failed
- Tempo: 21.6s

### Playwright (testes e2e)
- **9 passed, 3 skipped**
- Tempo: 4 minutos 18 segundos
- Testes de formulário, kanban e JS console errors passaram

### pytest (testes Python)
- **268 passed, 171 skipped**, 5 warnings
- Skips: Testes de SQLi que dependem de servidor ativo
- Warnings: `PytestUnknownMarkWarning` para `@mark.timeout`

### PHPUnit
- **12.832 testes**, 2.129 assertions
- **12.134 errors** — todos `Connection refused` (MySQL não disponível localmente)
- **698 testes efetivos** passaram (sem dependência de DB)
- Tempo: ~21 minutos
- Memória: ~1.1GB pico

---

## Testes Não Executados

- `curl/wget` (smoke-routes, test-curl, test-curl-fail) — necessitam servidor HTTP ativo
- `mysql` combinations — necessitam servidor MySQL ativo
- Esses testes estarão disponíveis após `composer serve-k8--soft` ou `php artisan serve`

---

## Comando Utilizado para Execução

```bash
# PHPStan
php vendor/bin/phpstan analyse --memory-limit=2G --no-progress

# ESLint
npx eslint "resources/**/*.{js,cjs,mjs}" "public/**/*.js" --no-error-on-unmatched-pattern

# tsc
npx tsc --noEmit

# Flake8
python3 -m flake8 resources/python/ tests/python/

# mypy
python3 -m mypy resources/python/ --ignore-missing-imports

# Jest CJS
npx jest --config jest.config.cjs --verbose

# Jest TS
cd tests/frontend/js && npx jest --verbose

# Playwright
npx playwright test --reporter=list

# pytest
python3 -m pytest tests/python/ -v --tb=short

# PHPUnit
php -d memory_limit=2G vendor/bin/phpunit --no-coverage --stop-on-failure
```
