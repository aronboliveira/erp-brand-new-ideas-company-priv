# PHP Commands — 2026-02-10 — Batch 23 Fixes

## Tinker commands used for debugging

```php
// Encrypt ID for PDF route testing
echo \Illuminate\Support\Facades\Crypt::encrypt('10b52a5d-5499-4aba-9eb8-1d83c61d5391');

// Check Bill model has items relationship
$bill = \App\Models\Bills\Bill::first();
$bill->items()->count(); // Should work after adding items() HasMany

// Check Utility billItemStats method exists
method_exists(\App\Models\Utility::class, 'billItemStats'); // true

// Verify payslip view exists
view()->exists('payslips.payslip_pdf'); // true (snake_case, not camelCase)

// Check contract layout view exists
view()->exists('layouts.contract_header'); // true (after dot separator fix)

// Check DNS2D barcode works via instance (not facade)
$dns2d = new \Milon\Barcode\DNS2D();
$dns2d->getBarcodeHTML('test', 'QRCODE', 2, 2); // returns HTML string
```

## Artisan commands

```bash
# Clear all caches (run after code changes)
php artisan view:clear && php artisan route:clear && php artisan config:clear

# Check a specific route exists
php artisan route:list --path=invoices/export

# Check all export routes
php artisan route:list | grep export

# Check PDF routes
php artisan route:list | grep pdf
```

## Key patterns discovered

```php
// DNS2D facade is BROKEN — do NOT use static calls
// BAD:  \DNS2D::getBarcodeHTML($url, 'QRCODE', 2, 2)
// GOOD: $dns2d = new \Milon\Barcode\DNS2D(); $dns2d->getBarcodeHTML($url, 'QRCODE', 2, 2);

// View constants must have trailing dot for template paths
// BAD:  'bills.templates'  (concatenates as 'bills.templatestemplate1')
// GOOD: 'bills.templates.' (concatenates as 'bills.templates.template1')

// Export routes MUST be declared BEFORE resource() routes
// Otherwise /{id} wildcard captures 'export' as an ID
// Route::get('invoices/export', ...);  // FIRST
// Route::resource('invoices', ...);    // AFTER

// UUID models need int|string type hints, not just int
// public function show(int|string $id) { ... }
```
