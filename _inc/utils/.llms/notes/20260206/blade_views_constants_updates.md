# Blade Views Constants Updates - 2026-02-06

## Summary

Updated multiple blade view files to replace hardcoded CSS class strings with constants from `ViewClassNamesConstants` (aliased as `VC`) for consistency and maintainability.

## Files Updated

### LandingPage Module (`_inc/laravel/Modules/LandingPage/Resources/views/`)

1. **landingpage/faqs/index.blade.php**
   - Replaced `class="btn btn-sm btn-primary"` with `{{ VC::BT_SM_PM }}`
   - Replaced `class="row align-items-center"` with `{{ VC::R_ALC }}`

2. **landingpage/testimonials/index.blade.php**
   - Replaced `class="btn btn-sm btn-primary"` with `{{ VC::BT_SM_PM }}`
   - Replaced `class="row align-items-center"` with `{{ VC::R_ALC }}`

3. **landingpage/features/index.blade.php**
   - Replaced `class="row align-items-center"` with `{{ VC::R_ALC }}`

### Main Resources Views (`_inc/laravel/resources/views/`)

4. **leaves/create.blade.php**
   - Replaced `class="form-control select"` with `{{ VC::FM_CT_SL }}`
   - Added proper null-safe iteration with `@forelse/@empty`
   - Replaced hardcoded HTML `div.row/col-md-12/form-group` with constants

5. **form_builders/index.blade.php**
   - Replaced `class="btn btn-sm btn-primary"` with `{{ VC::BT_SM_PM }}`
   - Replaced `class="ti ti-plus"` with `{{ VC::TI_PLS }}`

6. **trainings/index.blade.php**
   - Replaced `class="btn btn-sm btn-primary"` with `{{ ViewClassNamesConstants::BT_SM_PM }}`
   - Replaced `class="ti ti-plus"` with `{{ ViewClassNamesConstants::TI_PLS }}`
   - Replaced `class="action-btn bg-info ms-2"` with `{{ ViewClassNamesConstants::ACT_BTN_INF }}`
   - Replaced `class="mx-3 btn btn-sm align-items-center"` with `{{ ViewClassNamesConstants::BT_SM_CT }}`
   - Replaced `class="ti ti-eye text-white"` with `{{ ViewClassNamesConstants::TI_EYE_WT }}`

7. **reports/payable_report.blade.php**
   - Replaced `class="btn btn-sm btn-primary"` with `{{ VC::BT_SM_PM }}`

8. **reports/statement_report.blade.php**
   - Replaced `class="btn btn-sm btn-primary"` with `{{ VC::BT_SM_PM }}`
   - Replaced `class="ti ti-file-export"` with `{{ VC::TI_EXP }}`

9. **layouts/landing.blade.php**
   - Replaced `class="d-flex align-items-center"` with `{{ ViewClassNamesConstants::DFL_AIC }}` (3 occurrences)

## Constants Reference

| Hardcoded Class                      | Constant          |
| ------------------------------------ | ----------------- |
| `btn btn-sm btn-primary`             | `VC::BT_SM_PM`    |
| `form-control select`                | `VC::FM_CT_SL`    |
| `row align-items-center`             | `VC::R_ALC`       |
| `d-flex align-items-center`          | `VC::DFL_AIC`     |
| `ti ti-plus`                         | `VC::TI_PLS`      |
| `ti ti-file-export`                  | `VC::TI_EXP`      |
| `ti ti-eye text-white`               | `VC::TI_EYE_WT`   |
| `action-btn bg-info ms-2`            | `VC::ACT_BTN_INF` |
| `mx-3 btn btn-sm align-items-center` | `VC::BT_SM_CT`    |

## Remaining Work

There are still additional files that could benefit from constant updates. The following patterns are still found in some files:

- `class="d-flex align-items-center"` - 20+ occurrences across various files
- `class="row align-items-center"` - 20+ occurrences
- `class="ti ti-plus"` - 20+ occurrences
- Some template/layout files with hardcoded classes

These can be addressed in future iterations as needed.

## Notes

- All files already had proper error handling with try/catch blocks
- Imports were already sorted alphabetically in most files
- Variable initialization with `??=` pattern was already in place

---

# Second Round: Namespace Aliasing Audit - 2026-02-06

## Summary

Performed a comprehensive audit to ensure consistent namespace aliasing across blade views, controllers, entities, and models. Updated files that were using full constant class names instead of the standard aliases.

## Aliasing Convention

All constant dictionaries should use the following aliases:

| Full Class Name             | Alias  |
| --------------------------- | ------ |
| `DatabaseConstants`         | `DC`   |
| `SettingsConstants`         | `SC`   |
| `ViewClassNamesConstants`   | `VC`   |
| `ViewsConstants`            | `VW`   |
| `PermissionsConstants`      | `PMC`  |
| `StacksConstants`           | `ST`   |
| `YieldingConstants`         | `YC`   |
| `ExtendingLayoutsConstants` | `ELC`  |
| `LandingPageConstants`      | `LPGC` |

## Files Updated

### Auth Views (`_inc/laravel/resources/views/auth/`)

1. **verify.blade.php**
   - Updated imports to use grouped aliases: `DC, SC, ST, VC, VW, YC, ELC`
   - Replaced ~15 instances of full class names with aliases

2. **register.blade.php**
   - Updated imports to use grouped aliases: `DC, SC, ST, VC, VW, YC, ELC`
   - Replaced ~40+ instances including recaptcha settings, form groups, language bar

### Layout Views (`_inc/laravel/resources/views/layouts/`)

3. **landing.blade.php**
   - Updated imports to use: `DC, SC, VC, ELC`
   - Replaced ~30+ instances across navbar, header, sections, footer

4. **hrm_setup.blade.php**
   - Updated imports to use: `PMC, VC, VW`
   - Updated nav items array and class constants

5. **account_setup.blade.php**
   - Updated imports to use: `VC, VW`
   - Updated form element class constants

6. **share_project.blade.php**
   - Updated imports to use: `DC, SC, VC`
   - Updated settings and view constants

### Bills Views (`_inc/laravel/resources/views/bills/`)

7. **customer_bill.blade.php**
   - Updated imports to use: `DC, SC, VC, VW, YC, ELC`
   - Updated settings data extraction, PDF routes, footer text

### Email Views (`_inc/laravel/resources/views/email/`)

8. **lead_mail.blade.php**
   - Updated `SettingsConstants` → `SC`

9. **test_mail.blade.php**
   - Updated `SettingsConstants` → `SC`

10. **common.blade.php**
    - Updated `SettingsConstants` → `SC`

11. **deal_mail.blade.php**
    - Updated `SettingsConstants` → `SC`

### LandingPage Module Entities (`_inc/laravel/Modules/LandingPage/Entities/`)

12. **LandingPageSetting.php**
    - Updated imports to use: `DC, LPGC, SC`
    - Updated table constant, fillable columns, uploadFile methods

## Scanned & Already Compliant

The following files were scanned and found to already use proper aliasing:

### Controllers

- `_inc/laravel/Modules/LandingPage/Http/Controllers/LandingPageController.php` ✓
- `_inc/laravel/Modules/LandingPage/Http/Controllers/FaqController.php` ✓

### Models

- All models at `_inc/laravel/app/Models/` ✓

## Import Pattern Example

**Before:**

```php
@php
use App\Config\Constants\DatabaseConstants;
use App\Config\Constants\SettingsConstants;
use App\Config\Constants\Views\ViewClassNamesConstants;
@endphp
// Usage: {{ DatabaseConstants::DEFAULT_LANG }}
```

**After:**

```php
@php
use App\Config\Constants\{
  DatabaseConstants as DC,
  SettingsConstants as SC
};
use App\Config\Constants\Views\ViewClassNamesConstants as VC;
@endphp
// Usage: {{ DC::DEFAULT_LANG }}
```

## Total Changes

- **12 files updated** with proper namespace aliasing
- **~100+ constant references** converted to use aliases
- **Controllers and Models** confirmed compliant (no changes needed)
