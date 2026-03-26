# Guia de Upgrade — nwidart/laravel-modules

**De:** v9.0.6 → **Para:** v10.x / v11.x  
**Laravel:** 10.49.0 (framework ^10.10) | **PHP:** 8.4.5  
**Data:** 2026-03-25  
**Risco:** 🟢 Baixo — uso limitado ao módulo LandingPage; mudanças de API mínimas

---

## 1. Pré-requisitos

| Requisito             | Status Atual   | Ação                |
| --------------------- | -------------- | ------------------- |
| Laravel ≥ 10.x        | ✅ 10.49.0     | Nenhuma             |
| PHP ≥ 8.1             | ✅ 8.4.5       | Nenhuma             |
| Módulos em `Modules/` | ✅ LandingPage | Verificar estrutura |

## 2. Escopo de Uso no Projeto

O pacote é utilizado de forma **limitada**:

| Uso                      | Localização                             | Quantidade   |
| ------------------------ | --------------------------------------- | ------------ |
| `Module::asset()`        | Blade views do LandingPage              | ~23 chamadas |
| `modules_statuses.json`  | Raiz do projeto                         | 1 arquivo    |
| Config `modules.php`     | `config/modules.php`                    | 1 arquivo    |
| Seeder com Module facade | `Modules/LandingPage/Database/Seeders/` | 1 arquivo    |
| Estrutura de módulo      | `Modules/LandingPage/`                  | 1 módulo     |

## 3. Breaking Changes (v9 → v10/v11)

### 3.1 Mudança no FileActivator

O v10 refatorou o `FileActivator` para suportar status de módulos via banco de dados (opcional). O `FileActivator` padrão continua funcionando, mas a importação pode mudar.

**Arquivo afetado:** `config/modules.php`

```php
// v9 (atual)
use Nwidart\Modules\Activators\FileActivator;

// v10+ — verificar se a classe mantém o mesmo namespace
```

### 3.2 Mudança no `modules_statuses.json`

O formato do arquivo de status permanece compatível, mas o path default pode mudar. Verificar que `config/modules.php` → `statuses-file` aponta para o path correto.

### 3.3 Assinatura de `Json` class

Os métodos `Json::__construct()`, `Json::make()` e `Json::toJsonPretty()` tinham parâmetros implicit-nullable que já foram corrigidos via patch. O v10+ corrige nativamente:

| Método                                              | v9 (original) | v10+ (nativo)                       |
| --------------------------------------------------- | ------------- | ----------------------------------- |
| `__construct($path, Filesystem $filesystem = null)` | ❌ implícito  | ✅ `?Filesystem $filesystem = null` |
| `make($path, Filesystem $filesystem = null)`        | ❌ implícito  | ✅ `?Filesystem $filesystem = null` |
| `toJsonPretty(array $data = null)`                  | ❌ implícito  | ✅ `?array $data = null`            |

### 3.4 Comandos Artisan

Alguns comandos artisan de módulos podem ter mudado de nome:

- `module:make` → verificar opções
- `module:enable` / `module:disable` → manter compatível
- `module:migrate` → verificar flags

## 4. Passos do Upgrade

### Passo 1 — Backup

```bash
# Backup da estrutura de módulos
cp modules_statuses.json modules_statuses.json.bak
cp config/modules.php config/modules.php.bak
```

### Passo 2 — Verificar versão target

```bash
# v10 suporta Laravel 10.x
# v11 requer Laravel 11.x (não aplicável ao projeto atual)
composer show nwidart/laravel-modules --available | head -20
```

Para **Laravel 10**, o target é **v10.x**:

```bash
composer require nwidart/laravel-modules:^10.0 --no-interaction
```

### Passo 3 — Remover patch de implicit-nullable

Após confirmar que o v10 corrige os tipos nativamente:

1. Remover entrada em `composer.json` → `extra.patches.nwidart/laravel-modules`
2. Deletar `patches/nwidart-laravel-modules-implicit-nullable.patch`
3. Rodar `composer update nwidart/laravel-modules`

### Passo 4 — Publicar e comparar config

```bash
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider" --tag="config" --force
```

Comparar o novo `config/modules.php` com o backup:

```bash
diff config/modules.php.bak config/modules.php
```

Manter customizações existentes (namespace, paths, statuses-file).

### Passo 5 — Verificar Module::asset() calls

Os ~23 usos de `Module::asset()` no LandingPage devem continuar funcionando. Testar:

```bash
php artisan serve &
# Acessar /landing-page e verificar que CSS/JS/fonts carregam
curl -s http://localhost:8000/ | grep -c "Module::asset\|/modules/"
```

### Passo 6 — Testes de regressão

```bash
# Verificar que módulo carrega
php artisan module:list

# Rodar seeder do módulo
php artisan module:seed LandingPage

# Testes E2E relevantes
npx playwright test tests/e2e/module-pages.spec.cjs
```

## 5. Arquivos que Precisam de Revisão

| Arquivo                                                              | Motivo                             |
| -------------------------------------------------------------------- | ---------------------------------- |
| `config/modules.php`                                                 | Comparar com defaults do v10       |
| `modules_statuses.json`                                              | Formato deve permanecer compatível |
| `Modules/LandingPage/Resources/views/layouts/landingpage.blade.php`  | 15+ `Module::asset()` — verificar  |
| `Modules/LandingPage/Resources/views/layouts/custompage.blade.php`   | 8+ `Module::asset()` — verificar   |
| `Modules/LandingPage/Database/Seeders/LandingPageDatabaseSeeder.php` | Usa `Module` facade                |
| `composer.json`                                                      | Remover entrada de patches         |

## 6. Rollback

```bash
# Reverter para v9 se necessário
composer require "nwidart/laravel-modules:9.*" --no-interaction

# Restaurar config
cp config/modules.php.bak config/modules.php
cp modules_statuses.json.bak modules_statuses.json
```

## 7. Estimativa de Impacto

| Área                  | Impacto                                                        |
| --------------------- | -------------------------------------------------------------- |
| Config                | Baixo — poucas mudanças                                        |
| Module facade (asset) | Baixo — API estável                                            |
| Migrações             | Nenhum — não há migrações do pacote                            |
| Blade views           | Baixo — verificar 23 chamadas                                  |
| Patches               | Pode remover `nwidart-laravel-modules-implicit-nullable.patch` |

## 8. Referências

- [Changelog nwidart/laravel-modules](https://github.com/nWidart/laravel-modules/releases)
- [Upgrade Guide v9 → v10](https://docs.laravelmodules.com/v10/upgrade)
- [Documentação oficial](https://docs.laravelmodules.com/)
