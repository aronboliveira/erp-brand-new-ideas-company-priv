# Guia de Banco de Dados / Database Guide

> Para subagentes trabalhando com migrations, seeders e schema.

## Visão Geral

- **211 tabelas** no schema `erp_prestech_db`
- **215 migrations** em `database/migrations/`
- **87 factories** em `database/factories/`
- **160+ seeders** em `database/seeders/`
- MySQL 8.4.7, charset `utf8mb4`, collation `utf8mb4_unicode_ci`

## Schema

O arquivo de dump está em `database/schema/mysql-schema.sql`. Este arquivo usa sintaxe MySQL condicional (`/*!40101 SET ... */`) que causa falsos positivos no linter SQL do VS Code — por isso `.vscode/settings.json` o associa a `plaintext`.

## Convenções

### Primary Keys

- Todos os modelos usam UUID (`CHAR(36)`) como PK
- `DEFAULT_UUID = 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'`
- Não usar IDs inteiros em testes

### Migrations

- Formato de nome: `yyyy_mm_dd_hhmmss_description_table.php`
- Usar `Schema::create()` / `Schema::table()` com lambda
- Sempre incluir `$table->softDeletes()` se modelo usa `SoftDeletes`
- `$table->uuid('id')->primary()` para PKs
- FKs: `$table->foreignUuid('user_id')->constrained()->cascadeOnDelete()`

### Seeders

- Usar `DB::table()->insertOrIgnore()` para idempotência
- Batch sizes reduzidos (100) para evitar OOM
- Seeders do branch `agent` têm crash-prevention wrapping
- `SeedersTemplating.php` centraliza criação de permissões e settings

### Factories

- 87 factories disponíveis
- Usar `Str::random(6)` suffix para nomes únicos (evitar colisões)
- `Hash::make('default')` para passwords (não usar `employeeDetails()` que double-hashes)

## Tabelas Importantes

| Tabela                   | Modelo             | Notas                                                    |
| ------------------------ | ------------------ | -------------------------------------------------------- |
| `settings`               | Settings           | Unique key `(name, created_by)`. Usar `updateOrInsert()` |
| `chart_of_account_types` | ChartOfAccountType | `id` é guarded, usar `saveQuietly()`                     |
| `utility_settings`       | —                  | Formatos de número (`INV-`, `BILL-`, etc.)               |
| `users`                  | User               | UUID PK, `type` field para roles                         |
| `warehouse_products`     | WarehouseProduct   | Composite unique key adicionada                          |

## Cuidados em Testes

- `RefreshDatabase` + `DB::transaction()` = conflito de SAVEPOINT
- Settings cache estáticos persistem entre testes — chamar `Utility::resetSettingsCache()`
- `insertOrIgnore` falha silenciosamente se registro existe — preferir `updateOrInsert`
