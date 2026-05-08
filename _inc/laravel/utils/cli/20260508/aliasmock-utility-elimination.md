# aliasMock-on-Utility elimination — 2026-05-08

Eliminates the remaining 6 aliasMock-driven skips in
`tests/Unit/app/Models/utils/UtilityTest.php` by replacing static-class
mocks with one of three patterns:

1. **Static-Closure seam over service classes** (Twilio, Spatie GoogleCalendar)
2. **Real DB seeding** instead of `aliasMock(Language::class)`
3. **Real Auth + real DB rows** instead of `aliasMock(Utility::class)`

## The pattern

The project's longstanding constraint
(`.notes/.llms/.guidelines/constraints.md` → "Never use aliasMock on
Utility") is now extended to all hot-path service classes. The static
`?\Closure` seam established for `CalendarService` in the prior commit
(`a0d1e7938`) is reused here for `NotificationService`.

## What changed in production code

### `app/Services/Utility/NotificationService.php`

Added two new public statics:

```php
public static ?\Closure $sendTwilioOverride = null;
public static function resetTestSeams(): void
{
    self::$sendTwilioOverride = null;
}
```

Inside `sendTwilioMsg()` the live `new TwilioClient(...)` /
`->messages->create(...)` chain is now guarded:

```php
if (self::$sendTwilioOverride !== null) {
    (self::$sendTwilioOverride)($sid, $token, $to, $fromNumber, $msg);
} else {
    $client = new TwilioClient($sid, $token);
    $client->messages->create($to, ['from' => $fromNumber, 'body' => $msg]);
}
```

When the override is null (production default), the live Twilio call
runs unchanged. Tests set the closure to a recorder and assert on the
captured arguments, then reset in `try/finally`.

## Tests flipped (6)

| Test | Pattern |
|---|---|
| `test_send_twilio_msg_sends_message` | NotificationService seam (`$sendTwilioOverride`) |
| `test_add_and_get_calendar_data_end_to_end` | CalendarService seam (existing `$saveEventOverride` + `$fetchEventsOverride`) |
| `test_languages_with_table_present_and_no_disabled_languages_returns_all_languages` | Real `DB::table('languages')->insertOrIgnore(...)` instead of `aliasMock(Language::class)->shouldReceive('pluck')` |
| `test_languages_with_table_present_and_disabled_languages_excludes_codes` | Same — real DB seeding |
| `test_get_calendar_data_filters_by_color_id` | CalendarService seam (`$fetchEventsOverride`) |
| `test_g_default_and_override` | Real `User::create` + `Auth::login` + real settings rows; covers both the no-row default branch and the user-scoped override branch of `Utility::g()` |

## Secret-mock markers

Per project convention (`## ! MOCKING REAL PROD SECRET`):

- `test_send_twilio_msg_sends_message` — Twilio SID / auth token /
  from-number are real production secrets.
- `test_get_calendar_data_filters_by_color_id` — `google_calendar_json_file`
  + `google_clender_id` are the live service-account credentials path
  and Google Calendar ID.

## Note on the Twilio template

Original test used `'SMS {msg}'`. `{msg}` is **not** in
`NotificationService::replaceVariable()`'s allowlist, so the template
was returning the literal `'SMS {msg}'` regardless of the context
array. Changed to `'SMS {customer_name}'` (which IS in the allowlist),
so the test asserts the real substitution behavior, not the
unsubstituted literal.

## Verification

### Targeted (6 modified tests)

```
APP_ENV=testing php vendor/bin/phpunit tests/Unit/app/Models/utils/UtilityTest.php \
  --filter='test_send_twilio_msg_sends_message|test_add_and_get_calendar_data_end_to_end|test_languages_with_table_present_and_no_disabled_languages_returns_all_languages|test_languages_with_table_present_and_disabled_languages_excludes_codes|test_get_calendar_data_filters_by_color_id|test_g_default_and_override' \
  --no-coverage
# → Tests: 6, Assertions: 26, Deprecations: 38
```

### Suite-level (Models-Utils)

| Metric | Before | After |
|---|---:|---:|
| Tests | 423 | 423 |
| Assertions | 2205 | **2231** |
| Failures | 4 | 4 (same pre-existing — not touched) |
| Skipped | 10 | **4** |

Net: **6 skip sites eliminated**, 26 new assertions, no new failures.

## Out of scope for this commit

Remaining 4 skips in Models-Utils belong to the architectural tier
flagged by the triage doc as "permanent":

- aliasMock-on-final-class limitations (e.g., immutable vendor classes)
- warehouse_products UNIQUE constraint
- Settings cache race
- ProductService fillable
