# Guia de Upgrade — spatie/laravel-permission

**De:** v5.11.1 → **Para:** v6.x  
**Laravel:** 10.49.0 (framework ^10.10) | **PHP:** 8.4.5  
**Data:** 2026-03-25  
**Risco:** 🟡 Médio — envolve migrações de banco e mudanças de API em modelos

---

## 1. Pré-requisitos

| Requisito              | Status Atual | Ação                     |
| ---------------------- | ------------ | ------------------------ |
| Laravel ≥ 10.x         | ✅ 10.49.0   | Nenhuma                  |
| PHP ≥ 8.1              | ✅ 8.4.5     | Nenhuma                  |
| Banco de dados migrado | ✅           | Backup obrigatório antes |

## 2. Breaking Changes (v5 → v6)

### 2.1 Remoção do método `getPermissionNames()` e `getRoleNames()` cache

No v6, `getRoleNames()` e `getPermissionNames()` retornam collections frescas ao invés de cachear automaticamente no atributo. Se o código depende de caching implícito, adicionar `.remember()` explícito.

### 2.2 Migração de tabelas — nova coluna `team_id`

O v6 adiciona suporte a **teams** como feature nativa. Se `teams` não for utilizado, deve-se garantir que a migração não quebre a estrutura existente.

```bash
# Publicar migração de upgrade
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-migrations"
```

### 2.3 Type hints mais estritos

Métodos como `hasRole()`, `hasAllRoles()`, `hasExactRoles()` agora declaram `?string $guard = null` explicitamente (o que já fizemos via patch). O patch de implicit-nullable (`patches/spatie-laravel-permission-implicit-nullable.patch`) pode ser **removido** após o upgrade.

### 2.4 Namespace do model de Role

A configuração em `config/permission.php` usa um model customizado:

```php
'role' => App\Models\Role::class,
```

Verificar que `App\Models\Individuals\Role` (ou `App\Models\Role`) estende corretamente `Spatie\Permission\Models\Role` no v6.

### 2.5 Registro de permissões via gate

`register_permission_check_method` (atualmente `true` em `config/permission.php`) pode ter comportamento alterado. Testar que `Gate::check()` continua funcionando após upgrade.

## 3. Arquivos Impactados

### 3.1 Modelos (3 arquivos)

| Arquivo                               | Uso            | Ação                               |
| ------------------------------------- | -------------- | ---------------------------------- |
| `app/Models/Individuals/User.php`     | `use HasRoles` | Verificar compatibilidade do trait |
| `app/Models/Individuals/Customer.php` | `use HasRoles` | Idem                               |
| `app/Models/Companies/Vendor.php`     | `use HasRoles` | Idem                               |

### 3.2 Modelos customizados (2 arquivos)

| Arquivo                                 | Uso                        | Ação                               |
| --------------------------------------- | -------------------------- | ---------------------------------- |
| `app/Models/Individuals/Permission.php` | Estende `SpatiePermission` | Verificar assinatura do construtor |
| `app/Models/Individuals/Role.php`       | Estende `SpatieRole`       | Verificar assinatura do construtor |

### 3.3 Serviços e seeders

| Arquivo                                       | Uso                                            | Ação                  |
| --------------------------------------------- | ---------------------------------------------- | --------------------- |
| `app/Services/Utility/TenantSetupService.php` | `Permission::firstOrCreate()`, `Role::where()` | Verificar API estável |
| Seeders                                       | `Role::create()`, `givePermissionTo()`         | Testar seeding        |

### 3.4 Trait de autorização

| Arquivo                            | Uso                                        | Ação                           |
| ---------------------------------- | ------------------------------------------ | ------------------------------ |
| `app/Traits/ChecksPermissions.php` | `$user->hasPermissionTo()`, `$user->can()` | Teste de regressão obrigatório |

### 3.5 Testes (8+ controladores)

Testes que mockam `hasPermissionTo()` devem permanecer compatíveis (interface não muda).

## 4. Passos do Upgrade

### Passo 1 — Backup

```bash
# Backup do banco
mysqldump -u root erp_prestech > /tmp/erp_backup_pre_permission_upgrade.sql

# Backup das tabelas de permissão especificamente
mysqldump -u root erp_prestech roles permissions model_has_permissions model_has_roles role_has_permissions > /tmp/permission_tables_backup.sql
```

### Passo 2 — Atualizar composer.json

```bash
composer require spatie/laravel-permission:^6.0 --no-interaction
```

### Passo 3 — Publicar migrações do v6

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-migrations"
```

Revisar as novas migrações antes de rodar. Se houver coluna `team_id`, e teams não é utilizado, pode-se ignorar ou adaptar.

### Passo 4 — Executar migrações

```bash
php artisan migrate
```

### Passo 5 — Remover patch de implicit-nullable

Após confirmar que o v6 tem os tipos corretos nativamente:

1. Remover entrada em `composer.json` → `extra.patches.spatie/laravel-permission`
2. Deletar `patches/spatie-laravel-permission-implicit-nullable.patch`
3. Rodar `composer update spatie/laravel-permission`

### Passo 6 — Limpar cache de permissões

```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Passo 7 — Testes de regressão

```bash
# Testes unitários
php artisan test --filter=Permission
php artisan test --filter=Role
php artisan test --filter=User

# Testes E2E
npx playwright test tests/e2e/module-pages.spec.cjs
npx playwright test tests/e2e/financial.spec.cjs
```

### Passo 8 — Verificação manual

- Login como super admin → acessar todas as rotas protegidas
- Login como usuário comum → verificar que permissões bloqueiam acesso
- Criar novo usuário → atribuir roles → verificar herança de permissões

## 5. Rollback

```bash
# Reverter para v5 se necessário
composer require spatie/laravel-permission:^5.5 --no-interaction

# Restaurar banco se migrações causarem problemas
mysql -u root erp_prestech < /tmp/erp_backup_pre_permission_upgrade.sql
```

## 6. Estimativa de Impacto

| Área        | Impacto                                                          |
| ----------- | ---------------------------------------------------------------- |
| Modelos     | Baixo — trait HasRoles compatível                                |
| Migrações   | Médio — nova coluna team_id (optional)                           |
| Config      | Baixo — poucos campos novos                                      |
| Blade/Views | Nenhum — diretivas `@can` inalteradas                            |
| Testes      | Baixo — ajustar mocks se API mudar                               |
| Patches     | Pode remover `spatie-laravel-permission-implicit-nullable.patch` |

## 7. Referências

- [Changelog v6](https://github.com/spatie/laravel-permission/blob/main/CHANGELOG.md)
- [Upgrade Guide](https://spatie.be/docs/laravel-permission/v6/installation-laravel)
- [Teams Documentation](https://spatie.be/docs/laravel-permission/v6/basic-usage/teams-permissions)
