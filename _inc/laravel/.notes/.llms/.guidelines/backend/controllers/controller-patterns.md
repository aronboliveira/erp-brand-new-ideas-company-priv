# Padrões de Controllers / Controller Patterns

> Para subagentes trabalhando com controllers em `app/Http/Controllers/`.

## Estrutura

```
app/Http/Controllers/
├── Activity/      # ReportController, ExpenseController, PurchaseController
├── Bills/         # InvoiceController, BillController, BenefitPaymentController
├── Companies/     # CustomerController, VendorController, BankAccountController
├── Configs/       # SettingsController, SystemController, PermissionController
├── Helpers/       # ErrorHandlers, UtilityController
├── Individuals/   # UserController, EmployeeController, ContactController
├── Planning/      # ProjectController, DealController, ContractController, SetSalaryController
├── Products/      # ProductServiceController, WarehouseController
├── Shapes/        # TimesheetController
└── Ssr/           # JobApplicationController
```

## Regras

### 1. Constantes de Método

Cada método composto deve ter `public const ABBREV = 'methodName'` antes dele:

```php
public const STK_EXP = 'stockExport';
public function stockExport(Request $request): BinaryFileResponse
{
    // ...
}
```

Routes usam `[ControllerClass::class, ControllerClass::CONST]` ao invés de strings.

### 2. camelCase Obrigatório

- `stockExport()` ✅ — `stock_export()` ❌
- PHP dispatch é case-insensitive, mas convenção é camelCase

### 3. Guard Pattern

```php
// CORRETO
if (($deny = $this->guard('permission-name')) !== true) {
    return $deny;
}

// ERRADO — true é truthy, entra no if quando autorizado
if ($deny = $this->guard('permission-name')) {
    return $deny;
}
```

### 4. Return Types

- Index/Show: `View|RedirectResponse`
- Store/Update: `RedirectResponse`
- API: `JsonResponse`
- Exports: `BinaryFileResponse`
- Catch blocks: manter mesmo tipo de retorno do método (não retornar redirect de método JSON)

### 5. Import Groups (ordem)

```php
use App\Constants\{AC, BC, DC, SC};          // 1. Constants
use App\Models\{Customer, Invoice, User};     // 2. Models
use App\Services\{AccountingService};         // 3. Services
use Illuminate\Http\{JsonResponse, Request};  // 4. Framework
use Illuminate\Support\Facades\{DB, Log};     // 5. Facades
```

### 6. View Constants

Rotas de view usam `ViewConstants` (alias `VW`):

- `VW::INV` = `'invoices'`
- `VW::BIL` = `'bills'`
- `VW::PRJ` = `'projects'`

Cuidado: `VW::INV . 'link.copy'` = `'invoiceslink.copy'` — falta o `.` separador.
