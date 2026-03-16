<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Contracts\CalendarGateway;
use App\Models\Utility;
use App\Services\Calendar\{GoogleCalendarGateway, MockCalendarGateway};
use Carbon\Carbon;
use Illuminate\Support\Facades\{App, Log};

/**
 * CalendarService — extracted from Utility.php
 *
 * Handles Google Calendar API interactions via a CalendarGateway abstraction:
 * - Configuration from DB settings
 * - Event creation
 * - Event retrieval with color-based filtering
 *
 * Uses dependency injection via Laravel's service container. By default binds
 * GoogleCalendarGateway in production and MockCalendarGateway in testing.
 *
 * @see \App\Models\Utility — delegates to this service
 * @see \App\Contracts\CalendarGateway — abstraction interface
 * @see \App\Services\Calendar\GoogleCalendarGateway — production implementation
 * @see \App\Services\Calendar\MockCalendarGateway — test mock implementation
 *
 * ! TODO: Register CalendarGateway binding in AppServiceProvider for explicit
 *   environment-based resolution. Currently uses runtime fallback.
 * ! TODO: Fix typo in DB settings key: 'google_clender_id' → 'google_calendar_id'
 *   (requires data migration for existing production installations)
 */
class CalendarService
{
    /**
     * Resolve the CalendarGateway implementation from the container,
     * falling back to a sensible default based on environment.
     *
     * ! TODO: Move this binding to AppServiceProvider::register() for cleaner DI:
     *   $this->app->bind(CalendarGateway::class, function ($app) {
     *       return $app->environment('testing')
     *           ? new MockCalendarGateway()
     *           : new GoogleCalendarGateway();
     *   });
     */
    public static function gateway(): CalendarGateway
    {
        if (App::bound(CalendarGateway::class)) {
            return App::make(CalendarGateway::class);
        }

        // Fallback: use mock in testing, real gateway in production
        if (App::environment('testing')) {
            return new MockCalendarGateway();
        }

        return new GoogleCalendarGateway();
    }

    /**
     * Inject a specific gateway instance (useful in tests).
     *
     * Usage in tests:
     * ```php
     * $mock = new MockCalendarGateway([
     *     ['summary' => 'Test Event', 'startDateTime' => '...', 'endDateTime' => '...', 'colorId' => '1'],
     * ]);
     * CalendarService::setGateway($mock);
     * ```
     */
    public static function setGateway(CalendarGateway $gateway): void
    {
        App::instance(CalendarGateway::class, $gateway);
    }

    /**
     * Configure google-calendar package from DB settings.
     *
     * @see CalendarGateway::configure()
     */
    public static function configure(): void
    {
        self::gateway()->configure();
    }

    /**
     * Add a calendar event via the configured gateway.
     *
     * ! TODO: The return value from gateway->createEvent() is currently
     *   discarded. Consider returning it so callers can get the event ID.
     *
     * @param object $request Expected to have: title, start_date, end_date
     * @param string $type    Event type for color mapping (e.g. 'event', 'task')
     */
    public static function addEvent(object $request, string $type): void
    {
        try {
            self::gateway()->createEvent(
                name:    $request->title,
                start:   Carbon::parse($request->start_date),
                end:     Carbon::parse($request->end_date),
                colorId: Utility::colorCodeData($type),
            );
        } catch (\Throwable $e) {
            Log::error(self::class . '::addEvent — failed adding calendar event: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve calendar events filtered by color code for the given type.
     *
     * @param string $type Event type for color filtering
     * @return list<array{id: string, title: string, start: string, end: string, className: string, allDay: bool}>
     */
    public static function getEvents(string $type): array
    {
        try {
            $events = self::gateway()->getEvents();
        } catch (\Throwable $e) {
            Log::error(self::class . '::getEvents — failed fetching calendar events: ' . $e->getMessage());
            return [];
        }

        if ($events->isEmpty()) {
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
