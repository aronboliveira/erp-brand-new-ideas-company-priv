# Accounting Mock Data

## Status: Seeded

## Date: 2026-02-07

### Problem

The payables/receivables reports fail with "Unable to build" when no accounting data exists.

### Fix

- Mock data seeded via existing seeders for invoices, bills, payments, revenues
- Reports now have enough data to render
- Added defensive null-coalescing in aging calculations
- Replaced abort(500) with redirect()->back()->with('error', ...) for user-friendly messages

### TODO

- Verify report calculations match expected outputs with seeded data
- Add more diverse test data for edge cases (partial payments, multi-currency, etc.)
