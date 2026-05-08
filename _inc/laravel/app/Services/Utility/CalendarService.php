<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Models\Utility;
use Illuminate\Support\Facades\{Config, Log};
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Carbon\Carbon;

/**
 * CalendarService — extracted from Utility.php
 *
 * Handles Google Calendar API interactions:
 * - Configuration from DB settings
 * - Event creation via Spatie\GoogleCalendar
 * - Event retrieval with color-based filtering
 *
 * @see \App\Models\Utility — delegates to this service
 *
 * TODO: Replace live Google API calls with a testable abstraction layer.
 *       Consider creating a CalendarGateway interface with GoogleCalendarGateway
 *       and MockCalendarGateway implementations for proper DI-based testing.
 * TODO: Fix typo in DB settings key: 'google_clender_id' → 'google_calendar_id'
 *       (requires data migration for existing production installations)
 */
class CalendarService
{
    /**
     * Test seam — when set, addEvent() invokes this callable instead of
     * GoogleEvent::save(). The callable receives the constructed
     * GoogleEvent (with name/start/end/colorId already populated).
     *
     * Tests should set this to a closure that records the event
     * (e.g. inserts a local row into a fake `google_events` table)
     * and reset it to null in tearDown.
     *
     * Production code MUST leave this null — bypassing the real save
     * would suppress live calendar writes silently.
     */
    public static ?\Closure $saveEventOverride = null;

    /**
     * Test seam — when set, getEvents() invokes this callable instead of
     * GoogleEvent::get(). The callable receives no arguments and must
     * return an iterable of objects exposing
     * `id`, `summary`, `startDateTime`, `endDateTime`, `colorId`.
     *
     * Production code MUST leave this null.
     */
    public static ?\Closure $fetchEventsOverride = null;

    /**
     * Reset both seam overrides to null. Tests should call this in
     * tearDown to avoid bleeding overrides across tests.
     */
    public static function resetTestSeams(): void
    {
        self::$saveEventOverride = null;
        self::$fetchEventsOverride = null;
    }

    /**
     * Configure google-calendar package from DB settings.
     *
     * Reads the service account credentials file path and calendar ID
     * from the `settings` table and pushes them into Laravel config.
     *
     * TODO: The settings key uses 'google_clender_id' (typo). A migration
     *       should rename it to 'google_calendar_id' and update this code.
     */
    public static function configure(): void
    {
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
     * Add a calendar event via Google Calendar API.
     *
     * TODO: This method calls `GoogleEvent::save()` directly — it should
     *       be refactored to use a CalendarGateway interface so the API
     *       call can be mocked without overloading the class.
     *
     * @param object $request Expected to have: title, start_date, end_date
     * @param string $type    Event type for color mapping (e.g. 'event', 'task')
     */
    public static function addEvent(object $request, string $type): void
    {
        self::configure();

        try {
            $event                = new GoogleEvent();
            $event->name          = $request->title;
            $event->startDateTime = Carbon::parse($request->start_date);
            $event->endDateTime   = Carbon::parse($request->end_date);
            $event->colorId       = Utility::colorCodeData($type);

            if (self::$saveEventOverride !== null) {
                (self::$saveEventOverride)($event);
            } else {
                $event->save();
            }
        } catch (\Throwable $e) {
            Log::error(self::class . '::addEvent — failed adding calendar event: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve calendar events filtered by color code for the given type.
     *
     * TODO: GoogleEvent::get() makes a live HTTP call to Google APIs.
     *       This should be abstracted behind a CalendarGateway interface.
     *
     * @param string $type Event type for color filtering
     * @return list<array{id: string, title: string, start: string, end: string, className: string, allDay: bool}>
     */
    public static function getEvents(string $type): array
    {
        self::configure();

        try {
            $events = self::$fetchEventsOverride !== null
                ? (self::$fetchEventsOverride)()
                : GoogleEvent::get();
        } catch (\Throwable $e) {
            Log::error(self::class . '::getEvents — failed fetching calendar events: ' . $e->getMessage());
            return [];
        }

        if ($events === null) {
            return [];
        }

        $colorId = (string) Utility::colorCodeData($type);
        $result  = [];

        foreach ($events as $ev) {
            $endDate = date_create($ev->endDateTime);
            date_add($endDate, date_interval_create_from_date_string('1 days'));

            if ($ev->colorId === $colorId) {
                $result[] = [
                    'id'        => $ev->id,
                    'title'     => $ev->summary,
                    'start'     => $ev->startDateTime,
                    'end'       => date_format($endDate, 'Y-m-d H:i:s'),
                    'className' => Utility::$colorCode[(int) $colorId] ?? '',
                    'allDay'    => true,
                ];
            }
        }

        return $result;
    }
}
