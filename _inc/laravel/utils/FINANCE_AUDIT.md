# Finance Module Audit — ViewsConstants & Route Fixes

> **Date**: Session 10+
> **Scope**: All finance-related GET endpoints (196 discovered, 84 tested non-parameterized)
> **PHPUnit outcome**: 989 tests OK (275 financial + 714 route/view)
> **Playwright outcome**: 132 passed (63 finance-render + 35 financial + 34 reports), 1 fixme

---

## 1. Bugs Found & Fixed

### 1.1 CRITICAL — PayslipTypeController Route Collision

**File**: `routes/web.php` line 978
**Problem**: `R::resource(VW::PY_SLP, PYSTC::class)` registered PayslipTypeController under `payslips` — the same prefix as PayslipController (line 1000). Laravel's last-wins rule meant PayslipTypeController was completely unreachable.
**Fix**: Changed to `R::resource(VW::PY_SLP_TP, PYSTC::class)` → routes now at `payslip_types/*`.
**Impact**: PayslipType CRUD (index, create, edit, destroy) restored. Views in `resources/views/payslip_types/` now properly loaded.

### 1.2 CRITICAL — PayslipTypeController Using Wrong View Constant

**File**: `app/Http/Controllers/Bills/PayslipTypeController.php` (12 occurrences)
**Problem**: All `VW::PY_SLP` (→ `'payslips'`) references should be `VW::PY_SLP_TP` (→ `'payslip_types'`). Was loading views from `resources/views/payslips/` instead of `resources/views/payslip_types/` (which has index, create, edit blade templates).
**Fix**: Replaced all 12 occurrences: guard() calls, view() calls, and redirect()->route() calls.

### 1.3 HIGH — CustomerController Wrong Redirect Target

**File**: `app/Http/Controllers/Individuals/CustomerController.php` line 53
**Problem**: `private const REDIRECT_INDEX = VW::JB . '.index'` resolved to `'jobs.index'`. When permission denied on customer payment/transaction, users were redirected to the Jobs page.
**Fix**: `VW::JB` → `VW::CST` (→ `'customers.index'`).

### 1.4 HIGH — PayslipController Nonexistent Route Name

**File**: `app/Http/Controllers/Bills/PayslipController.php` line 311
**Problem**: `route('employee.show', ...)` — no route named `employee.show` exists. Actual name is `employees.show` (plural).
**Fix**: `'employee.show'` → `VW::EMP . '.show'` (VW::EMP = `'employees'`).

### 1.5 MEDIUM — VendorController Hardcoded View String

**File**: `app/Http/Controllers/Companies/VendorController.php` line 35
**Problem**: `private const SINGULAR = 'vendors'` — hardcoded instead of using `VW::VND`.
**Fix**: `'vendors'` → `VW::VND`.

---

## 2. Files Modified

| File                                                                  | Change                                            | Severity |
| --------------------------------------------------------------------- | ------------------------------------------------- | -------- |
| `routes/web.php` L978                                                 | `VW::PY_SLP` → `VW::PY_SLP_TP` for PYSTC resource | CRITICAL |
| `app/Http/Controllers/Bills/PayslipTypeController.php` (12 locations) | `VW::PY_SLP` → `VW::PY_SLP_TP`                    | CRITICAL |
| `app/Http/Controllers/Individuals/CustomerController.php` L53         | `VW::JB` → `VW::CST`                              | HIGH     |
| `app/Http/Controllers/Bills/PayslipController.php` L311               | `'employee.show'` → `VW::EMP . '.show'`           | HIGH     |
| `app/Http/Controllers/Companies/VendorController.php` L35             | `'vendors'` → `VW::VND`                           | MEDIUM   |

## 3. Files Created

| File                                | Description                                                  |
| ----------------------------------- | ------------------------------------------------------------ |
| `tests/e2e/finance-render.spec.cjs` | 63 Playwright tests covering all finance index/create routes |

---

## 3. Curl Sweep Results

**84 non-parameterized finance GET routes tested:**

- Batch 1 (37 routes): 35× 200, `proposals` → 404 (correct: singular `proposal`), `debit_notes/bill` → 422 (API needing params)
- Batch 2 (47 routes): 47× 200
- New `payslip_types` and `payslip_types/create`: both 200

---

## 4. ViewsConstants Audit Summary

25+ finance controllers confirmed **clean** (using VW constants correctly):
`InvoiceController`, `BillController`, `PaymentController`, `ExpenseController`, `RevenueController`, `CreditNoteController`, `DebitNoteController`, `ProposalController`, `BankAccountController`, `BankTransferController`, `ChartOfAccountController`, `JournalEntryController`, `TaxController`, `BudgetController`, `GoalController`, `GoalTypeController`, `GoalTrackingController`, `AllowanceController`, `CommissionController`, `LoanController`, `OvertimeController`, `OtherPaymentController`, `SaturationDeductionController`, `SetSalaryController`, `PricingPlanController`

---

## 5. Key Constants Reference

| Constant        | Value             | Used By                                       |
| --------------- | ----------------- | --------------------------------------------- |
| `VW::PY_SLP`    | `'payslips'`      | PayslipController                             |
| `VW::PY_SLP_TP` | `'payslip_types'` | PayslipTypeController (FIXED)                 |
| `VW::CST`       | `'customers'`     | CustomerController (FIXED)                    |
| `VW::JB`        | `'jobs'`          | JobController only                            |
| `VW::VND`       | `'vendors'`       | VendorController (FIXED)                      |
| `VW::EMP`       | `'employees'`     | EmployeeController, PayslipController (FIXED) |
