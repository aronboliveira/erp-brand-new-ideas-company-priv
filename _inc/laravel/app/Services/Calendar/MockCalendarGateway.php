<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use App\Contracts\CalendarGateway;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Config, Log};
use Illuminate\Support\Str;

/**
 * MockCalendarGateway — in-memory mock implementation for testing.
 *
 * Emulates Google Calendar API behavior without HTTP calls.
 * Stores events in memory and provides the same interface as
 * GoogleCalendarGateway for drop-in test replacement.
 *
 * ! IMPORTANT: This mock emulates a SUBSET of Google Calendar API behavior.
 *   Real Google Calendar API responses differ in:
 *   - Event IDs are opaque strings, not UUIDs
 *   - Timestamps use RFC 3339 format with timezone offsets
 *   - Events include htmlLink, creator, organizer, reminders, recurrence
 *   - Color IDs are strings "1"-"11", mapped to specific hex colors
 *   - Events have etag for conflict detection
 *   The mock returns simplified objects sufficient for this project's tests.
 *
 * ! TODO: Add support for these Google Calendar API features when needed:
 *   - Recurring events (recurrence rules, RRULE)
 *   - Attendees with RSVP status
 *   - Event attachments
 *   - All-day events (date vs dateTime)
 *   - Event update (Events.patch / Events.update)
 *   - Event delete (Events.delete)
 *   - Calendar list (CalendarList.list)
 *   - Watch/push notifications (Events.watch)
 *
 * Google Calendar API color reference:
 * @see https://developers.google.com/calendar/api/v3/reference/colors/get
 *
 * colorId mapping (Google Calendar event colors):
 *   1  = Lavender    (#7986CB)
 *   2  = Sage        (#33B679)
 *   3  = Grape       (#8E24AA)
 *   4  = Flamingo    (#E67C73)
 *   5  = Banana      (#F6BF26)
 *   6  = Tangerine   (#F4511E)
 *   7  = Peacock     (#039BE5)
 *   8  = Graphite    (#616161)
 *   9  = Blueberry   (#3F51B5)
 *   10 = Basil       (#0B8043)
 *   11 = Tomato      (#D50000)
 *
 * @see \App\Contracts\CalendarGateway
 */
class MockCalendarGateway implements CalendarGateway
{
    /**
     * In-memory store of created events.
     *
     * @var list<object>
     */
    private array $events = [];

    /**
     * Whether configure() was called (for assertion in tests).
     */
    private bool $configured = false;

    /**
     * Optional fixture events to pre-populate.
     *
     * @var list<object>
     */
    private array $fixtures = [];

    /**
     * Create a new MockCalendarGateway, optionally with fixture data.
     *
     * @param list<array{id?: string, summary: string, startDateTime: string, endDateTime: string, colorId: string|int}> $fixtures
     */
    public function __construct(array $fixtures = [])
    {
        foreach ($fixtures as $f) {
            $this->fixtures[] = (object) array_merge([
                'id'            => Str::uuid()->toString(),
                'status'        => 'confirmed',
                'htmlLink'      => 'https://www.google.com/calendar/event?eid=' . Str::random(12),
                'created'       => now()->toIso8601String(),
                'updated'       => now()->toIso8601String(),
                'creator'       => (object) ['email' => 'mock@test.local'],
                'organizer'     => (object) ['email' => 'calendar@test.local', 'displayName' => 'Test Calendar'],
                'etag'          => '"' . Str::random(16) . '"',
                'iCalUID'       => Str::uuid()->toString() . '@google.com',
                'sequence'      => 0,
                'reminders'     => (object) ['useDefault' => true],
            ], $f);
        }
    }

    /**
     * {@inheritdoc}
     *
     * Reads DB settings and sets Config values, same as GoogleCalendarGateway,
     * so tests asserting on config('google-calendar.*') work correctly.
     * The only difference: warns instead of returning early when file is missing.
     */
    public function configure(): void
    {
        $this->configured = true;

        $settings = Utility::settings();
        $path     = storage_path($settings['google_calendar_json_file'] ?? '');

        if (!file_exists($path)) {
            Log::warning(self::class . '::configure — credentials file not found at ' . $path);
            return;
        }

        Config::set([
            'google-calendar.default_auth_profile'                           => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.credentials_json'           => $path,
            'google-calendar.auth_profiles.oauth.token_json'                 => $path,
            'google-calendar.calendar_id'                                    => $settings['google_clender_id'] ?? '',
            'google-calendar.user_to_impersonate'                            => '',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * Stores event in memory and returns a mock response matching
     * the structure of a Google Calendar API Events.insert response.
     *
     * ! TODO: Real Google Calendar API generates event IDs server-side
     *   and returns a full Event resource. This mock uses a UUID.
     *
     * ! TODO: Real API returns additional fields:
     *   - htmlLink: URL to view event in Google Calendar
     *   - etag: version identifier for conflict detection
     *   - iCalUID: globally unique identifier across calendars
     *   - sequence: revision number
     *   - creator / organizer: participant info
     *   - reminders: override or default reminder settings
     */
    public function createEvent(
        string $name,
        Carbon $start,
        Carbon $end,
        int    $colorId,
        array  $extra = []
    ): array {
        $id = Str::uuid()->toString();

        $event = (object) array_merge([
            'id'            => $id,
            'summary'       => $name,
            'startDateTime' => $start->toIso8601String(),
            'endDateTime'   => $end->toIso8601String(),
            'colorId'       => (string) $colorId,
            'status'        => 'confirmed',
            'htmlLink'      => "https://www.google.com/calendar/event?eid={$id}",
            'created'       => now()->toIso8601String(),
            'updated'       => now()->toIso8601String(),
            'creator'       => (object) ['email' => 'mock@test.local'],
            'organizer'     => (object) ['email' => 'calendar@test.local'],
            'etag'          => '"mock_' . Str::random(12) . '"',
            'iCalUID'       => $id . '@google.com',
            'sequence'      => 0,
            'reminders'     => (object) ['useDefault' => true],
        ], $extra);

        $this->events[] = $event;

        return [
            'id'     => $id,
            'status' => 'confirmed',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Returns all events: fixtures + dynamically created events.
     */
    public function getEvents(): Collection
    {
        return collect(array_merge($this->fixtures, $this->events));
    }

    // ─────────────────────────────────────────────────────────
    //  Test helper methods (not part of CalendarGateway contract)
    // ─────────────────────────────────────────────────────────

    /**
     * Check whether configure() was called.
     */
    public function wasConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Get all events currently stored (both fixtures and created).
     *
     * @return list<object>
     */
    public function getStoredEvents(): array
    {
        return array_merge($this->fixtures, $this->events);
    }

    /**
     * Get only dynamically created events (not fixtures).
     *
     * @return list<object>
     */
    public function getCreatedEvents(): array
    {
        return $this->events;
    }

    /**
     * Reset all stored events and state.
     */
    public function reset(): void
    {
        $this->events     = [];
        $this->configured = false;
    }

    /**
     * Generate a fixture event object with dynamic values matching
     * Google Calendar API Events resource structure.
     *
     * @param array<string,mixed> $overrides  Fields to override defaults
     * @return object
     *
     * ! TODO: Real Google Calendar event structure includes these
     *   fields we don't emulate yet:
     *   - recurrence: array of RRULE strings
     *   - attendees: array of { email, responseStatus, displayName }
     *   - conferenceData: for Google Meet links
     *   - attachments: file attachments
     *   - extendedProperties: custom key/value pairs
     *   - source: { url, title } source of the event
     *   - visibility: 'default', 'public', 'private', 'confidential'
     *   - transparency: 'opaque', 'transparent'
     */
    public static function makeEvent(array $overrides = []): object
    {
        $id    = $overrides['id'] ?? Str::uuid()->toString();
        $start = $overrides['startDateTime'] ?? now()->addHour()->toIso8601String();
        $end   = $overrides['endDateTime'] ?? now()->addHours(2)->toIso8601String();

        return (object) array_merge([
            'id'            => $id,
            'summary'       => 'Mock Event ' . Str::random(4),
            'startDateTime' => $start,
            'endDateTime'   => $end,
            'colorId'       => '1',
            'status'        => 'confirmed',
            'htmlLink'      => "https://www.google.com/calendar/event?eid={$id}",
            'created'       => now()->toIso8601String(),
            'updated'       => now()->toIso8601String(),
            'creator'       => (object) ['email' => 'mock@test.local'],
            'organizer'     => (object) ['email' => 'calendar@test.local', 'displayName' => 'Test Calendar'],
            'etag'          => '"mock_' . Str::random(12) . '"',
            'iCalUID'       => $id . '@google.com',
            'sequence'      => 0,
            'reminders'     => (object) ['useDefault' => true],
        ], $overrides);
    }
}
