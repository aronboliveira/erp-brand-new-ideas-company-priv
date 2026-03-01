# Route Smoke Testing Framework - Setup Complete

## Date: 2026-02-22

## Purpose: Track route health and identify areas needing work

---

## 📦 What Was Created

### Test Scripts (`.tmp/` - temporary)

1. **`smoke_test_routes.py`** - Unauthenticated route testing
   - Tests 377 GET/HEAD routes with 4 different curl configurations
   - Scenarios: basic status, redirects, timing, headers
   - Excludes parameterized routes and debug routes

2. **`smoke_test_authenticated.py`** - Authenticated route testing
   - Attempts login with admin credentials
   - Tests routes with session cookies
   - Categorizes routes by response type

3. **`smoke_test.sh`** - Convenient shell wrapper
   - Quick access: `./smoke_test.sh [basic|auth|results|help]`
   - Supports viewing results and cleaning up

### Persistent Documentation (`_inc/utils/.llms/`)

1. **`routes_smoke_test_analysis.md`** - Comprehensive analysis
   - Test methodology
   - Critical findings (99.5% error rate)
   - Prioritized routes for fixing
   - Next steps and recommendations

2. **`smoke_test_summary.txt`** - Human-readable results (unauthenticated)
3. **`smoke_test_results.json`** - Machine-readable data (unauthenticated)
4. **`smoke_test_authenticated_summary.txt`** - Authenticated results summary
5. **`smoke_test_authenticated_results.json`** - Authenticated results data

### Project Scripts Updates

- **composer.json**: Added `smoke-routes` and `smoke-routes-auth` scripts
- **package.json**: Added `smoke:routes` and `smoke:routes:auth` scripts

---

## 🚀 How to Use

### Option 1: Direct Python

```bash
cd /path/to/erp_prestech
python3 .tmp/smoke_test_routes.py              # Basic tests
python3 .tmp/smoke_test_authenticated.py       # Authenticated tests
```

### Option 2: Shell Script

```bash
cd /path/to/erp_prestech
.tmp/smoke_test.sh basic     # or: unauthenticated
.tmp/smoke_test.sh auth      # or: authenticated
.tmp/smoke_test.sh both      # Run both suites
.tmp/smoke_test.sh results   # View summary
.tmp/smoke_test.sh help      # Full usage
```

### Option 3: Composer (from \_inc/laravel)

```bash
composer smoke-routes         # Unauthenticated
composer smoke-routes-auth    # Authenticated
```

### Option 4: NPM (from \_inc/laravel)

```bash
npm run smoke:routes           # Unauthenticated
npm run smoke:routes:auth      # Authenticated
```

---

## 📊 Current Test Results (2026-02-22)

### Summary

- **Total Routes Tested**: 377 (excluding parameterized routes)
- **Success Rate**: 0.5% (2 non-500 responses)
- **Server Errors**: 375 routes (99.5%)
- **Auth Errors**: 1 route (401 on `/api/landingpage`)
- **Client Errors**: 1 route (400)

### Critical Finding

**The application is currently non-functional for route testing.**

- Homepage `/` returns 500 error
- Login endpoint `/login` returns 500 error
- Public routes (about_us, contact_us) return 500 errors
- Nearly all routes affected

### Hypothesis

Systemic issue preventing route execution:

- Database connectivity failure
- Corrupted Laravel caches (config/route/view)
- Missing environment configuration
- Session driver misconfiguration

### Recommended Immediate Actions

1. Check `storage/logs/laravel.log`
2. Verify database connection
3. Clear all caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
4. Verify `.env` settings (DB*\*, APP_KEY, SESSION*\*)

---

## 📝 Routes Needing Work (Top Priority)

Once systemic issues are resolved, these routes have highest business impact:

### Critical Business Routes

- `/` - Main dashboard
- `/login`, `/register` - Auth gates
- `/account-dashboard` - User dashboard
- `/bills`, `/proposals`, `/pos` - Financial
- `/customers`, `/venders` - CRM
- `/products`, `/productservice` - Inventory
- `/attendances`, `/payslips` - HR

### Public Routes (Should work without auth)

- `/about_us`
- `/contact_us`
- `/privacy_policy`
- `/terms_and_conditions`

---

## 🔧 Test Configuration

### Test Scenarios

1. **Basic**: Simple status code check (`-s -o /dev/null -w "%{http_code}"`)
2. **With Redirects**: Follow redirects (`-sL`)
3. **Timing**: Measure performance (`-w "%{time_total}"`)
4. **Headers**: HEAD requests only (`-sI`)

### Routes Excluded

- Parameterized routes (`{id}`, `{slug}`, etc.) - ~200 routes
- Debug routes (`_debugbar`, `_ignition`)
- System routes (`sanctum/csrf`, `.well-known/*`)

### Authentication

- **Email**: `u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local`
- **Password**: `Admin@1234`
- **Method**: Cookie-based session (attempts CSRF + POST /login)

---

## 📈 Next Steps

### Immediate (Before Further Testing)

1. ✅ Smoke test framework created
2. ⚠️ Fix systemic 500 errors
3. ⚠️ Verify database connectivity
4. ⚠️ Clear all caches
5. ⚠️ Restore login functionality

### After System Stabilization

1. Re-run smoke tests to establish baseline
2. Test parameterized routes with sample IDs
3. Add POST/PUT/DELETE endpoint testing
4. Implement permission boundary tests
5. Add load testing for critical APIs

### Ongoing Maintenance

- Run smoke tests after deployments
- Monitor for new routes needing constants
- Track slow routes (>2s threshold)
- Update test scenarios as needed

---

## 🗂️ File Locations

### Scripts

```
/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/
├── .tmp/
│   ├── smoke_test_routes.py           # Unauthenticated tests
│   ├── smoke_test_authenticated.py    # Authenticated tests
│   ├── smoke_test.sh                  # Shell wrapper
│   ├── smoke_test_run.log             # Execution logs
│   └── smoke_test_auth_run.log
│
├── _inc/utils/.llms/
│   ├── routes_smoke_test_analysis.md          # This analysis
│   ├── smoke_test_summary.txt                 # Human results
│   ├── smoke_test_results.json                # Machine results
│   ├── smoke_test_authenticated_summary.txt
│   └── smoke_test_authenticated_results.json
│
└── _inc/laravel/
    ├── composer.json    # Added smoke-routes* scripts
    └── package.json     # Added smoke:routes* scripts
```

---

## 🔍 Sample Output

### Console Output

```
Starting comprehensive route smoke tests...
Base URL: http://localhost
Test scenarios: 4
Extracting routes from Laravel...
Found 377 testable GET routes

=== Testing scenario: basic (Basic status code check) ===
Progress: 50/377
...
Progress: 350/377

Scenario 'basic' results:
  Status codes: {'500': 375, '401': 1, '400': 1}
  Failed routes: 376
```

### Summary File

```
Route Smoke Test Summary
Generated: 2026-02-22T18:44:22.216931
Total Routes Tested: 377

Status Code Distribution:
  400: 1
  401: 1
  500: 375

Failed Routes (376):
  [500] /about_us (about_us)
  [500] /account-dashboard (dashboard)
  ...
```

---

## ⚙️ Customization

### Adding New Test Scenarios

Edit `smoke_test_routes.py`, add to `TEST_SCENARIOS`:

```python
{
    "name": "ssl_check",
    "flags": ["-s", "-k", "-w", "%{ssl_verify_result}"],
    "description": "SSL certificate validation"
}
```

### Changing Test URLs

Edit `BASE_URL` constant in both scripts:

```python
BASE_URL = "http://localhost"  # Change to your server
```

### Adjusting Timeouts

Edit `timeout=10` parameter in `test_route()` functions.

---

## 📚 Related Documentation

- Prior curl testing: `_inc/utils/.llms/scripts/curl_route_tester.py`
- Route constants audit: `_inc/utils/.llms/scripts/audit_route_constants.py`
- Project setup: `README.md`

---

## ✅ Status

- [x] Smoke test scripts created
- [x] Analysis document written
- [x] Composer/NPM scripts added
- [x] Shell wrapper created
- [ ] **Systemic 500 errors resolved** ← Blocking further progress
- [ ] Baseline test results established
- [ ] Parameterized route testing
- [ ] POST/PUT/DELETE testing

**Current State**: Framework ready, awaiting application stability for meaningful test results.
