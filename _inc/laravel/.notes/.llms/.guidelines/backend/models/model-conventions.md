# Convenções de Modelos Eloquent / Model Conventions

> Para subagentes trabalhando com modelos em `app/Models/`.

## Estrutura de Diretórios

```
app/Models/
├── Activity/       # Purchases, Expenses, Activity, ActivityLog
├── Bills/          # Invoices, Bills, Taxes, Payments, Proposals
├── Companies/      # Customers, Vendors, BankAccounts
├── Configs/        # Settings, Permissions, Roles, Plans
├── Individuals/    # Users, Employees, Contacts
├── Planning/       # Projects, Tasks, Contracts, Goals, Deals
├── Products/       # ProductService, ProductServiceCategory, Warehouses
├── Shapes/         # Timesheets, Attendance
├── Ssr/            # Offer letters, Job applications
└── utils/          # Utility.php (central helper)
```

## Regras

### 1. UUID Primary Keys

Todos os modelos usam UUIDs como PK. O `DEFAULT_UUID` é `'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'`.

### 2. Constants Classes (11 aliases)

```php
use App\Constants\{
    ActivityConstants as AC,       // Não confundir com Artisan alias
    BillsConstants as BC,
    CrmConstants as CPC,           // CRM + Pipeline
    DatabaseConstants as DC,
    EloquentConstants as ELC,
    ErrorConstants as EC,
    FilesConstants as FC,
    LanguageConstants as LC,
    PaymentMethodConstants as PMC,
    ProjectConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
```

### 3. Relações

- Usar `belongsTo()`, `hasMany()`, `hasOne()` com tipos de retorno explícitos: `public function user(): BelongsTo`
- Evitar colisão de nome de relação com colunas DB (ex: `->getAttributes()['col']` vs `->col`)
- Usar `static::` ao invés de `self::` para herança correta

### 4. Guarded vs Fillable

- `id` está em `$guarded` na maioria dos modelos — `::create(['id' => $x])` ignora silenciosamente o id
- Para setar ID manualmente: `$m = new Model(); $m->id = $id; $m->saveQuietly();`

### 5. SoftDeletes

Verificar se a tabela tem coluna `deleted_at` antes de usar `SoftDeletes` trait.

### 6. Crash Prevention

Modelos do branch `agent` usam try/catch em accessors/mutators com `Log::error()`. Padrão:

```php
public function getFullNameAttribute(): string
{
    try {
        return "{$this->first_name} {$this->last_name}";
    } catch (\Throwable $e) {
        Log::error("Model accessor error: {$e->getMessage()}");
        return '';
    }
}
```

### 7. Comentários em Português

- Comentários inline devem ser em pt-BR usando `//`
- Docblocks `@param`, `@return`, `@throws` em inglês técnico
