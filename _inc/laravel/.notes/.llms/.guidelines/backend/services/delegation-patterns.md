# Padrões de Delegação de Serviços / Service Delegation Patterns

> Referência para subagentes trabalhando com a camada de serviços em `app/Services/`.

## Arquitetura de Delegação

O `Utility.php` (1.828 linhas) mantém stubs de delegação para 68 métodos que foram extraídos para 6 classes de serviço em `app/Services/Utility/`.

### Regras de Delegação

1. **Stubs preservam assinatura**: O método em `Utility.php` mantém exatamente os mesmos parâmetros, tipos de retorno e nome. A única mudança é o corpo que delega para o serviço.

2. **Padrão de delegação**:

```php
// Em Utility.php
public static function invoiceNumberFormat(string $number): string
{
    /** @see FinanceBillingService::invoiceNumberFormat() */
    return FinanceBillingService::invoiceNumberFormat($number);
}
```

3. **Cada serviço usa `ChecksLogin`**: Para manter acesso ao contexto de autenticação.

4. **Nunca deletar do Utility**: Apenas adicionar stubs — código existente que chama `Utility::method()` deve continuar funcionando.

5. **`@see` obrigatório**: Todo stub deve ter `@see ServiceClass::method()` no docblock.

## Classes de Serviço

| Classe                  | Namespace               | Métodos | Domínio                                      |
| ----------------------- | ----------------------- | ------- | -------------------------------------------- |
| `AccountingService`     | `App\Services\Utility`  | 12      | CoA, journal, trial balance, balance sheet   |
| `FileStorageService`    | `App\Services\Utility`  | 10      | Upload/download, S3/Wasabi, storage settings |
| `FinanceBillingService` | `App\Services\Utility`  | 14      | Invoices, bills, taxes, payments, proposals  |
| `LocalizationService`   | `App\Services\Utility`  | 12      | Languages, currency, phone, date/time        |
| `ModelLookupService`    | `App\Services\Utility`  | 10      | Settings lookups, plan, model finders        |
| `NotificationService`   | `App\Services\Utility`  | 10      | Email, Twilio, Pusher, notifications         |
| `TenantSetupService`    | `App\Services\Utility`  | —       | Tenant bootstrap (pré-existente)             |
| `CalendarService`       | `App\Services\Calendar` | —       | Google Calendar via DI gateway               |

## Imports — Regras

- Agrupar com `{}`: `use App\Constants\{DC, SC, UC};`
- Ordem alfabética dentro dos grupos
- Remover imports não utilizados imediatamente
- `BelongsTo` é import legítimo (usado como return type em 7 métodos de Utility)

## Armadilhas Conhecidas / Known Pitfalls

- `ChartOfAccountType.id` é guarded — usar `$rec = new ChartOfAccountType(); $rec->id = $id; $rec->saveQuietly();`
- `totalQuantity()` opera em `InvoiceProduct`/`BillProduct` — não confundir com `ProductService`
- `sendEmailTemplate()` usa `self::_checkLogin()` (não `static::`) — anonymous class override não funciona
- Settings cache estáticos (`$getSettings`, `$getSettingsId`) persistem entre testes — sempre chamar `resetSettingsCache()`
