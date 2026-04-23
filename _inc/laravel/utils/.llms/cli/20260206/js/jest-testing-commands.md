# Jest Testing Commands - Frontend JavaScript/TypeScript

## Test Execution Commands

### Run All Tests

```bash
cd _inc/laravel/tests/frontend/js
npm test
```

### Run Tests Without Coverage

```bash
npm test --no-coverage
```

### Run Tests With Coverage Report

```bash
npm test -- --coverage
```

### Run Specific Test File

```bash
npm test -- --testPathPattern="dash"
npm test -- --testPathPattern="erp-guard"
npm test -- --testPathPattern="landingpage"
```

### Run Tests in Watch Mode

```bash
npm test -- --watch
```

### Run Tests with Verbose Output

```bash
npm test -- --verbose
```

### Run Tests and Update Snapshots

```bash
npm test -- -u
```

## Test File Patterns

### Test Files Location

- **Test Directory**: `_inc/laravel/tests/frontend/js/`
- **Test Pattern**: `**/*.test.ts`
- **Test Files**:
  - `core/erp-guard.test.ts` - ERPGuard singleton tests (210 tests)
  - `core/erp-utils.test.ts` - ERPUtils utility tests (99 tests)
  - `pages/dash.test.ts` - DashboardController tests (97 tests)
  - `pages/landingpage-dash.test.ts` - LandingDashboardController tests (59 tests)
  - `pages/route-guard.test.ts` - RouteGuard legacy tests (27 tests)
  - `pages/checkMounted.test.ts` - RecoveryOverlay tests (18 tests)

## Common Test Scenarios

### After Code Changes - Verify Build

```bash
# Run specific test suite
npm test -- --testPathPattern="dash" --no-coverage

# Run all tests
npm test --no-coverage
```

### Before Git Commit

```bash
# Run full test suite with coverage
npm test

# Check for failures
echo $?  # Should be 0 if all pass
```

### Debug Failing Test

```bash
# Run only failing test file
npm test -- --testPathPattern="erp-guard" --verbose

# Run single test by name
npm test -- -t "should initialize ERPGuard"
```

## Coverage Analysis

### Generate HTML Coverage Report

```bash
npm test -- --coverage --coverageReporters=html

# Open coverage report
xdg-open coverage/index.html  # Linux
open coverage/index.html      # macOS
```

### Check Coverage Thresholds

```bash
# View coverage summary
npm test -- --coverage --coverageReporters=text-summary
```

## Test Configuration

### Jest Config Location

- **Config File**: `_inc/laravel/tests/frontend/js/jest.config.js`
- **Setup File**: `_inc/laravel/tests/frontend/js/setup.ts`
- **Test Environment**: jsdom (browser simulation)
- **Transform**: ts-jest (TypeScript support)

### Key Configuration Options

```javascript
{
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  testMatch: ['**/*.test.ts'],
  setupFilesAfterEnv: ['<rootDir>/setup.ts']
}
```

## Common Issues & Solutions

### Issue: Tests use eval() - Coverage shows 0%

**Cause**: Tests dynamically load JS via `eval()` to test IIFE-wrapped code
**Solution**: Tests are comprehensive (510 tests) but coverage metrics aren't accurate

### Issue: Import errors for .js files

**Solution**: Tests use `eval()` pattern to load source files:

```typescript
const source = fs.readFileSync(JS_FILE_PATH, "utf-8");
eval(source);
```

### Issue: Window/document not defined

**Solution**: Using jsdom environment + setup.ts provides DOM globals

## Test Writing Patterns

### Standard Test Structure

```typescript
describe("ComponentName", () => {
  beforeEach(() => {
    resetDOM();
    // Setup mocks
  });

  afterEach(() => {
    resetDOM();
  });

  it("should do something", () => {
    // Arrange
    document.body.innerHTML = `<div>...</div>`;

    // Act
    const result = functionUnderTest();

    // Assert
    expect(result).toBe(expected);
  });
});
```

### DOM Setup Pattern

```typescript
function createTestLayout() {
  document.body.innerHTML = `
    <div class="component">
      <button class="action">Click</button>
    </div>
  `;
}
```

### Mock Pattern

```typescript
const mockFunction = jest.fn().mockReturnValue("result");
(window as any).SomeGlobal = mockFunction;
```

## Performance

### Fast Test Runs

```bash
# Run tests in parallel (default)
npm test -- --maxWorkers=4

# Run tests serially
npm test -- --runInBand
```

### Selective Test Execution

```bash
# Only changed files
npm test -- --onlyChanged

# Related to specific file
npm test -- --findRelatedTests path/to/file.ts
```

## CI/CD Integration

### GitHub Actions / GitLab CI

```yaml
- name: Run Frontend Tests
  run: |
    cd _inc/laravel/tests/frontend/js
    npm ci
    npm test -- --coverage --ci
```

### Exit Codes

- `0` - All tests passed
- `1` - Tests failed or errors occurred

## Total Test Count

**Current Total**: 510 tests passing

- ERPGuard: 210 tests
- ERPUtils: 99 tests
- DashboardController: 97 tests
- LandingDashboardController: 59 tests
- RouteGuard: 27 tests
- RecoveryOverlay: 18 tests
