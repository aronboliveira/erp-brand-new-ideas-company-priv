<?php

declare(strict_types=1);

namespace App\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * CalendarGateway — abstraction layer for calendar provider interactions.
 *
 * Implementations:
 * - {@see \App\Services\Calendar\GoogleCalendarGateway} — real Spatie/Google Calendar API
 * - {@see \App\Services\Calendar\MockCalendarGateway}   — in-memory mock for testing
 *
 * TODO: Once the real Google API credentials are configured, integration tests
 *       should verify GoogleCalendarGateway against the live API. Until then,
 *       MockCalendarGateway should be used for all unit/feature test suites.
 *
 * ! IMPORTANT: This interface emulates the subset of the Google Calendar API
 *   (Events.list, Events.insert) used by this project. Real API responses
 *   include additional fields (htmlLink, creator, organizer, reminders, etc.)
 *   that are NOT modeled here — extend when needed.
 */
interface CalendarGateway
{
    /**
     * Configure the calendar provider credentials and target calendar.
     *
     * For Google Calendar:
     * - Sets service account JSON path
     * - Sets calendar ID
     * - Pushes to Laravel config
     *
     * For mock:
     * - No-op or loads fixture data
     */
    public function configure(): void;

    /**
     * Create a calendar event.
     *
     * Maps to Google Calendar API Events.insert:
     * @see https://developers.google.com/calendar/api/v3/reference/events/insert
     *
     * @param string      $name     Event summary/title
     * @param Carbon      $start    Start date-time
     * @param Carbon      $end      End date-time
     * @param int         $colorId  Google Calendar colorId (1-11)
     * @param array<string,mixed> $extra  Additional fields (description, location, etc.)
     *
     * @return array{id: string, status: string} Created event data
     *
     * ! TODO: Google Calendar API returns a full Event resource on insert,
     *   including htmlLink, etag, iCalUID, sequence. We only return id+status
     *   for now. Extend the return type when richer response data is needed.
     */
    public function createEvent(
        string $name,
        Carbon $start,
        Carbon $end,
        int    $colorId,
        array  $extra = []
    ): array;

    /**
     * Retrieve all calendar events.
     *
     * Maps to Google Calendar API Events.list:
     * @see https://developers.google.com/calendar/api/v3/reference/events/list
     *
     * Returns a Collection of event objects with at minimum:
     * - id: string
     * - summary: string
     * - startDateTime: string (ISO 8601)
     * - endDateTime: string (ISO 8601)
     * - colorId: string
     *
     * ! TODO: Google Calendar API supports pagination (pageToken, nextPageToken),
     *   timeMin/timeMax filters, singleEvents, orderBy, etc. This method
     *   currently fetches ALL events without filtering. Add filtering params
     *   when calendar usage grows.
     *
     * @return Collection<int, object>
     */
    public function getEvents(): Collection;
}
