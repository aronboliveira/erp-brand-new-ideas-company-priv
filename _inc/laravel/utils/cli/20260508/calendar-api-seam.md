# Google Calendar API seam — 2026-05-08

Eliminates the 6 "Requires live Google Calendar API credentials" skips
in `tests/Unit/app/Models/utils/UtilityTest.php` by introducing a test
seam over the live `Spatie\GoogleCalendar\Event` calls.

## The TODO that motivated this

`CalendarService.php` carried explicit TODOs:
> Replace live Google API calls with a testable abstraction layer.
> Consider creating a CalendarGateway interface with GoogleCalendarGateway
> and MockCalendarGateway implementations for proper DI-based testing.

A full DI gateway is the right long-term answer, but it's a substantial
refactor across `Utility::addCalendarData`/`getCalendarData` callers
(controllers, schedulers, etc). For now this commit takes the **minimum
viable seam** that unblocks the tests without changing any behavior in
production:

## The seam

`App\Services\Utility\CalendarService` now exposes two static
`?\Closure` overrides:

```php
public static ?\Closure $saveEventOverride = null;     // intercepts $event->save()
public static ?\Closure $fetchEventsOverride = null;   // intercepts GoogleEvent::get()
public static function resetTestSeams(): void;        // resets both to null
```

When null (production default), the original Spatie calls run. When
set, the closure replaces the live call. Tests use a `try/finally` to
guarantee the seams are reset — no bleed across tests.

This matches the project's "static cache" pattern (e.g.
`Utility::$getSettings`) the previous agent established for similar
non-aliasable cases — see `.notes/.llms/.guidelines/constraints.md`.

## Tests flipped

All 6 `it_*` tests in `UtilityTest.php` that previously skipped:
- `it_manages_calendar_functions` (line 5822)
- `it_fetches_calendar_events_filtered_by_color` (line 6970)
- `it_adds_calendar_event_and_retrieves_by_type` (line 7913)
- `it_retrieves_calendar_data_for_given_type` (line 8724)
- `it_manages_google_calendar_events` (line 10205)
- `it_adds_and_retrieves_google_calendar_events_by_type` (line 13190)

Each test now sets the seams in setup, runs the real
`Utility::addCalendarData` / `getCalendarData` call paths
(exercising `CalendarService::configure` + `addEvent` + `getEvents`),
and asserts on the structured output.

## Secret-mock markers

Per project convention, every site that mocks a value that **is** a
real production secret (or shaped like one) is preceded by:

```php
// ## ! MOCKING REAL PROD SECRET — google_calendar_json_file is the
// path to the service-account credentials JSON in production.
```

This appears in 5 of the 6 modified tests (the 6th — `it_retrieves_…`
— uses the seam without mocking settings, so no secret is mocked).

## Verification

### Targeted run (just the 6 tests)
```bash
APP_ENV=testing php vendor/bin/phpunit tests/Unit/app/Models/utils/UtilityTest.php \
  --filter='it_manages_calendar_functions|it_fetches_calendar_events_filtered_by_color|...' \
  --no-coverage
# → 6/6 passed, 25 assertions
```

### Suite-level (Models-Utils)
| Metric | Baseline | After |
|---|---:|---:|
| Tests | 423 | 423 |
| Assertions | 2180 | **2205** |
| Failures | 4 | 4 (same pre-existing — not touched) |
| Skipped | **16** | **10** |

Net: **6 skip sites eliminated**, 25 new assertions, no new failures.

The remaining 10 skips in Models-Utils are all in the architectural
tier flagged by the triage doc as "permanent": aliasMock-on-final-class
limitations (4), Spatie double-mock (1), Twilio overload (1),
warehouse_products UNIQUE constraint (1), Settings cache race (2),
ProductService fillable (1).

## Out of scope for this commit

- **Twilio overload mock (1 skip)**: same architectural family —
  could likely be solved with a similar seam over the Twilio SDK call,
  but the test uses `@runInSeparateProcess` semantics that conflict
  with the rest of the file's lifecycle. Deferred.
- **Spatie double-mock (1 skip)**: aliasMock + overload conflict —
  needs the tests to be moved to a dedicated process, separate from
  the Calendar tests in this commit. Deferred.
