# Módulo de Contabilidade / Accounting Module Guide

> Chart of Accounts, Journal Entries, Trial Balance, Balance Sheet.

## Arquivos Chave

### Service (`app/Services/Utility/AccountingService.php`)

- 12 métodos delegados do Utility
- `getAccountBalance()`, `journalEntryData()`, `trialBalanceData()`, `balanceSheetData()`

### Models

- `ChartOfAccountType` — **id é guarded**, usar `saveQuietly()` para setar manualmente
- `JournalItem` — relações renomeadas para evitar conflitos de nome
- `BillAccount` — join com chart of accounts

## Padrão de Seeding para CoA

```php
// CORRETO — id é guarded
$rec = new ChartOfAccountType();
$rec->id = $uuid;
$rec->name = 'Assets';
$rec->saveQuietly();

// ERRADO — id é silenciosamente ignorado
ChartOfAccountType::create(['id' => $uuid, 'name' => 'Assets']);
```

## Testes — Falhas Pré-Existentes (~21)

Todas as falhas de PHPUnit em contabilidade/finanças são pré-existentes:

- **14 Chart of Accounts**: Testes usavam IDs inteiros mas tabela usa UUID PKs
- **20 Balance Sheet / Trial Balance**: Dependem de CoA seeding correto
- **Não são regressões** — pré-datam o audit atual

## Transações

TODAS operações contábeis devem estar em `DB::transaction()`:

```php
DB::transaction(function () {
    JournalItem::create([...]);
    // atualizar saldos
});
```

## Log Channel

Usar `Log::channel('accounting')` para rastreabilidade de operações financeiras.
