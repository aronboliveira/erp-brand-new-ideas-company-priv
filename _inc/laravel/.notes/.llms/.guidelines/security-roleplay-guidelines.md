# Security Roleplay Test Guidelines

> XML profile definitions: `.guidelines/security-roleplay-profiles.xml`  
> Audit report: `security-testing-audit.md`  
> Generated: 2026-03-31 · Updated: 2026-03-31

---

## Purpose

Each security test folder under `tests/{e2e,Feature,Unit}/security/roleplay/<actor>/`
contains tests that **simulate a specific type of actor** interacting with the system.
The actor's skill level, motivations, and methodology determine the test style.

Each actor also maintains **attack/evaluation scripts** in multiple languages
(JS, Python, PHP, Bash, WASM) that simulate what that actor would actually write.
These scripts produce breaches or audit results that the test files validate.

## Actors at a Glance

| Actor                | One-liner                                         | Test Tone                                | Scripts |
| -------------------- | ------------------------------------------------- | ---------------------------------------- | ------- |
| `green-hat/`         | Script kiddie copying payloads from the internet  | Simple, naive, smoke                     | 3       |
| `white-hat/`         | Ethical pentester following OWASP methodology     | Structured, categorized, thorough        | 6       |
| `black-hat/`         | Malicious attacker with advanced evasion skills   | Obfuscated, destructive, **GIT-IGNORED** | 9       |
| `ciso/`              | Executive verifying compliance & policy adherence | Audit checklists, metrics, governance    | 5       |
| `qa/`                | QA tester persistently poking the UI for bugs     | Client-side, edge cases, user-facing     | 3       |
| `backend-developer/` | Internal dev doing secure code review             | ORM patterns, config, static analysis    | 4       |

> Script count correlates with the actor's security expertise and involvement.

## Language × Framework Matrix

### Test Files

| Subfolder | Language   | Framework                      | File Pattern                 |
| --------- | ---------- | ------------------------------ | ---------------------------- |
| `php/`    | PHP 8.4    | PHPUnit 10                     | `*Test.php`                  |
| `js/`     | JavaScript | Jest (Unit) / Playwright (e2e) | `*.test.cjs` / `*.spec.cjs` |
| `py/`     | Python 3   | pytest                         | `test_*.py`                  |

### Attack/Evaluation Scripts

| Subfolder      | Language     | Executable       | Purpose                          |
| -------------- | ------------ | ---------------- | -------------------------------- |
| `js/scripts/`  | JavaScript   | `node script.cjs`| Client-side attacks, DOM probing |
| `py/scripts/`  | Python 3     | `python3 x.py`  | HTTP attacks, data extraction    |
| `php/scripts/` | PHP 8.4      | `php script.php` | Server-side probing              |
| `bash/scripts/`| Bash         | `bash script.sh` | Orchestration, curl chains       |
| `wasm/scripts/`| C→WebAssembly| `node loader.cjs`| Binary encoding, obfuscation     |

### Which framework goes where?

- **`tests/Unit/security/roleplay/<actor>/php/`** → PHPUnit unit tests (static analysis, model scanning)
- **`tests/Unit/security/roleplay/<actor>/js/`** → Jest unit tests (DOM, sanitization, client-side)
- **`tests/Feature/security/roleplay/<actor>/php/`** → PHPUnit feature tests (HTTP requests + DB)
- **`tests/Feature/security/roleplay/<actor>/py/`** → pytest HTTP tests (via `requests` library)
- **`tests/Feature/security/roleplay/<actor>/{lang}/scripts/`** → Executable attack/audit scripts
- **`tests/e2e/security/roleplay/<actor>/js/`** → Playwright browser tests

## Mock Apps

Target applications for attack scripts live in `tests/mock-apps/`:

| App                | File                       | Purpose                                      |
| ------------------ | -------------------------- | -------------------------------------------- |
| Vulnerable Form    | `vulnerable-form/index.html` | Form with intentional client-side weaknesses |
| Session Test       | `session-test/index.html`    | Exposes session/cookie handling for probing  |
| API Endpoint       | `api-test/index.html`        | Simulates API calls for interception tests   |

Existing frontend mocks in `tests/frontend/js/pages/mocks/` are referenced via
`tests/mock-apps/manifest.json` for unified access.

## Script Weight by Actor

The number and sophistication of scripts correlates with the actor's security expertise:

```
black-hat  ████████████████████ 9 scripts (CRITICAL — most advanced toolkit)
white-hat  ████████████████     6 scripts (systematic OWASP methodology)
ciso       █████████████        5 scripts (compliance/audit scanning)
backend-dev████████████         4 scripts (code review tools)
qa         ██████████           3 scripts (form fuzzing, edge cases)
green-hat  ██████████           3 scripts (naive, copy-paste)
```

## Black-Hat Git Policy

All `**/black-hat/` directories are `.gitignore`d. These files contain:

- Destructive payloads (DROP TABLE, TRUNCATE)
- Evasion techniques that could be weaponized
- Database exfiltration patterns
- Multi-stage attack chains (bash→js→wasm)

**These files exist only on local volumes** and must never be pushed to origin.

## Writing Style Per Actor

### green-hat

```php
// Simples. Copiou do StackOverflow. Não sabe o que faz.
$this->post('/login', ['email' => "' OR '1'='1", 'password' => 'x']);
// "Será que funciona?" — verifica só se o status não é 200/302:
$response->assertStatus(422);
```

### white-hat

```php
// Categorizado. Segue OWASP OTG-INPVAL-005. Documenta cada vetor.
/** @dataProvider unionBasedPayloads */
public function test_union_based_sqli_is_blocked(string $payload): void {
    $response = $this->actingAs($this->admin)->get("/invoices?search={$payload}");
    $response->assertStatus(200); // Não crashar
    $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
}
```

### black-hat

```php
// Ofuscado. Técnicas de evasão WAF. Tenta destruir.
$obfuscated = '/*!50000UnIoN*/+/*!50000SeLeCt*/+1,2,CONCAT(user(),0x3a,version())--';
$this->get("/invoices/" . urlencode($obfuscated));
// Tenta exfiltrar via time-based:
$this->get("/invoices?id=1'+AND+IF(1=1,SLEEP(5),0)--");
```

### ciso

```php
// Compliance. Não ataca — verifica que as defesas existem.
public function test_all_post_routes_have_csrf_middleware(): void { ... }
public function test_security_headers_are_present(): void { ... }
public function test_app_debug_is_disabled(): void { ... }
```

### qa

```js
// Preenche formulários como usuário final com inputs inesperados.
await page.fill('input[name="name"]', "O'Brien & Co. <test>");
await page.click('button[type="submit"]');
// Verifica que não aparece stack trace:
await expect(page.locator("body")).not.toContainText("SQLSTATE");
```

### backend-developer

```php
// Code review estático. Verifica padrões no código-fonte.
public function test_no_raw_queries_without_binding(): void { ... }
public function test_no_env_calls_outside_config(): void { ... }
public function test_no_debug_functions_in_production(): void { ... }
```
