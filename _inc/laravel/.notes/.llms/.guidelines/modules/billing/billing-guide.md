# Módulo de Faturamento / Billing Module Guide

> Invoices, Bills, Taxes, Payments, Proposals, Revenue.

## Arquivos Chave

### Models (`app/Models/Bills/`)

- `Invoice`, `InvoiceProduct`, `InvoicePayment`
- `Bill`, `BillProduct`, `BillPayment`, `BillAccount`
- `Tax`, `Revenue`, `Payment`
- `Proposal`, `ProposalProduct`
- `SaturationDeduction` (campo corrigido: `$saturationDeductionType`)

### Controllers (`app/Http/Controllers/Bills/`)

- `InvoiceController`, `BillController`
- `ExpenseController` — cuidado com `selectRaw()` bindings (array, não string)
- `BenefitPaymentController` — cuidado com dot separador em route names

### Service (`app/Services/Utility/FinanceBillingService.php`)

- 14 métodos delegados do Utility
- Imports necessários: `DC`, `PMC`, `SC` constants + modelos de billing
- `totalQuantity()` — opera em InvoiceProduct/BillProduct, não ProductService
- `invoiceNumberFormat()` / `billNumberFormat()` — lê formato de `utility_settings`

## Formatos de Número

Definidos em tabela `utility_settings`:

- Invoices: `INV-` prefix (não `#`)
- Bills: `BILL-` prefix
- Proposals: `PROP-` prefix
- Payments: prefixo configurável

Testes devem usar o formato correto do BD, não hardcodar `#`.

## Transações Financeiras

Operações financeiras DEVEM usar `DB::transaction()`:

```php
DB::transaction(function () use ($data) {
    $invoice = Invoice::create($data);
    foreach ($data['items'] as $item) {
        InvoiceProduct::create([...]);
    }
    JournalItem::create([...]);
});
```

## Log Channels

Usar `Log::channel('billing')` ou canal nomeado para rastreabilidade.
