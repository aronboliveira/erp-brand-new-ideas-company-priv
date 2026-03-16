<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use App\Contracts\CalendarGateway;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Config, DB, Log};
use Spatie\GoogleCalendar\Event as GoogleEvent;

/**
 * GoogleCalendarGateway — production implementation of CalendarGateway.
 *
 * Uses spatie/laravel-google-calendar to interact with the Google Calendar API.
 *
 * Google Calendar API reference:
 * @see https://developers.google.com/calendar/api/v3/reference
 *
 * Authentication:
 * - Uses a Service Account with domain-wide delegation
 * - Credentials JSON stored in storage/ and referenced in `settings` table
 * - Calendar ID also stored in `settings` table as 'google_clender_id'
 *
 * ! TODO: The settings key 'google_clender_id' is a typo for 'google_calendar_id'.
 *   A data migration is needed to rename it in production before fixing here.
 *
 * ! TODO: Google Calendar API rate limits apply:
 *   - 1,000,000 queries/day per project
 *   - 500 queries/100 seconds/user
 *   Add exponential backoff / retry logic for production resilience.
 *
 * ! TODO: Google Calendar API Events.list returns paginated results.
 *   Spatie's `Event::get()` handles basic retrieval but may miss events
 *   if the calendar has >250 items. Consider implementing pagination.
 *
 * @see \App\Contracts\CalendarGateway
 */
class GoogleCalendarGateway implements CalendarGateway
{
    /**
     * {@inheritdoc}
     *
     * Pushes Google service account credentials and calendar ID into
     * Laravel config from DB settings.
     */
    public function configure(): void
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
     * {@inheritdoc}
     *
     * Creates an event via Google Calendar API Events.insert.
     *
     * Google API request body (subset):
     * ```json
     * {
     *   "summary": "Event Name",
     *   "start": { "dateTime": "2025-06-15T09:00:00-07:00" },
     *   "end":   { "dateTime": "2025-06-15T10:00:00-07:00" },
     *   "colorId": "1"
     * }
     * ```
     *
     * Google API response (subset):
     * ```json
     * {
     *   "id": "abc123",
     *   "status": "confirmed",
     *   "htmlLink": "https://www.google.com/calendar/event?eid=...",
     *   "summary": "Event Name",
     *   "colorId": "1",
     *   "start": { "dateTime": "2025-06-15T09:00:00-07:00" },
     *   "end":   { "dateTime": "2025-06-15T10:00:00-07:00" }
     * }
     * ```
     *
     * ! TODO: GoogleEvent::save() doesn't return the API response directly.
     *   We build a minimal return from what Spatie provides. For full API
     *   response, consider using the google/apiclient SDK directly.
     */
    public function createEvent(
        string $name,
        Carbon $start,
        Carbon $end,
        int    $colorId,
        array  $extra = []
    ): array {
        $this->configure();

        $event                = new GoogleEvent();
        $event->name          = $name;
        $event->startDateTime = $start;
        $event->endDateTime   = $end;
        $event->colorId       = $colorId;

        // Apply extra fields (description, location, attendees, etc.)
        foreach ($extra as $key => $value) {
            $event->{$key} = $value;
        }

        DB::transaction(function () use ($event) {
            $event->save();
        });

        return [
            'id'     => $event->id ?? uniqid('gcal_'),
            'status' => 'confirmed',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Retrieves events via Google Calendar API Events.list.
     *
     * ! TODO: GoogleEvent::get() fetches events from now onwards by default.
     *   To retrieve past events, you'd need to pass Carbon dates to the method.
     *   Consider adding $startDate/$endDate params to the interface.
     */
    public function getEvents(): Collection
    {
        $this->configure();

        try {
            $events = GoogleEvent::get();
            return $events instanceof Collection ? $events : collect($events);
        } catch (\Throwable $e) {
            Log::error(self::class . '::getEvents — failed: ' . $e->getMessage());
            return collect();
        }
    }
}
