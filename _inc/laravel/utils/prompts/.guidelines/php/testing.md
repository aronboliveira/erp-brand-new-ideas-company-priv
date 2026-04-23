# PHPUnit Test Creation Guidelines

## @test Annotation

All test methods must use the `@test` PHPDoc annotation instead of (or in addition to)
the `test_` prefix. This makes intent explicit and allows shorter method names:

```php
/** @test */
public function itCreatesAnInvoice(): void
{
    // ...
}
```

## Better Comments Highlighting

Use the `**` prefix in PHPDoc blocks to trigger Better Comments extension highlighting
for important test documentation:

```php
/**
 * @test
 * ** Verifies that duplicate slugs are rejected with a 422 response.
 */
public function itRejectsDuplicateSlugs(): void
{
    // ...
}
```

## phpunit.xml Configuration

The project uses a `phpunit.xml` at the Laravel root. Key configuration points:

- Test suites are organized by module (`Unit`, `Feature`, and per-module suites).
- Environment variables are set in `<php>` blocks (e.g., `APP_ENV=testing`, `DB_CONNECTION=sqlite`).
- Coverage is configured via `<coverage>` element when needed.

Always verify your test appears in the correct suite by checking the `<testsuite>` directory
configuration.

## composer.json Autoload-Dev

Test classes must be autoloaded via `composer.json`'s `autoload-dev` section:

```json
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/",
        "Modules\\ModuleName\\Tests\\": "Modules/ModuleName/Tests/"
    }
}
```

Run `composer dump-autoload` after adding new test namespaces.
