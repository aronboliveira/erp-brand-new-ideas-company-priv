<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{Holiday, Utility};
use App\Traits\ChecksLogin;
use Carbon\Carbon;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\View\View;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class HolidayController extends Controller
{
    use ChecksLogin;

    /**
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_HLD))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $query = Holiday::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId());
            $request->filled('start_date')
                ? $query->where('date', '>=', $request->start_date)
                : null;
            $request->filled('end_date')
                ? $query->where('date', '<=', $request->end_date)
                : null;
            $holidays = $query->get();
            return view(ViewsConstants::HLD . '.' . __FUNCTION__, compact('holidays'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    /**
     * @return View|RedirectResponse
     */
    public function create(Request $request): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('create holiday'))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $settings = Utility::settings();
            return view(ViewsConstants::HLD . '.' . __FUNCTION__, compact('settings'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    /**
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('create holiday'))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $data = $request->validate([
                'date'       => 'required|date',
                'end_date'   => 'nullable|date',
                'occasion'   => 'required|string',
                'synchronize_type' => 'nullable|string',
            ]);
            $holiday = Holiday::create([
                'date'       => $data['date'],
                'end_date'   => $data['end_date'] ?? $data['date'],
                'occasion'   => $data['occasion'],
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            // notifications + google calendar + webhook (extract to service)
            $setting = Utility::settings($user?->creatorId());
            $notifyData = [
                'holiday_title' => $holiday->occasion,
                'holiday_date'  => $holiday->date,
            ];
            isset($setting['holiday_notification']) && $setting['holiday_notification'] == 1
                ? Utility::sendSlackMsg('new_holiday', $notifyData)
                : null;
            isset($setting['telegram_holiday_notification']) && $setting['telegram_holiday_notification'] == 1
                ? Utility::sendTelegramMsg('new_holiday', $notifyData)
                : null;
            $data['synchronize_type'] === 'google_calendar'
                ? Utility::addCalendarData($holiday, 'holiday')
                : null;
            $module = 'New Holiday';
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $status = Utility::webhookCall(
                    $webhook['url'],
                    json_encode($holiday),
                    $webhook['method']
                );
                if (!$status)
                    return redirect()
                        ->route(ViewsConstants::HLD . '.index')
                        ->with('error', __('Webhook call failed.'));
            }
            return redirect()
                ->route(ViewsConstants::HLD . '.index')
                ->with('success', __('Holiday successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::HLD . '.index')
            );
        }
    }

    /**
     * @return View|RedirectResponse
     */
    public function show(Request $request, Holiday $holiday): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('show holiday'))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            if ($holiday->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            return view(ViewsConstants::HLD . '.' . __FUNCTION__, compact('holiday'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * @return View|RedirectResponse
     */
    public function edit(Request $request, Holiday $holiday): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('edit holiday'))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            if ($holiday->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            return view(ViewsConstants::HLD . '.' . __FUNCTION__, compact('holiday'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('edit holiday'))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            if ($holiday->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $data = $request->validate([
                'date'     => 'required|date',
                'end_date' => 'nullable|date',
                'occasion' => 'required|string',
            ]);
            $holiday->update([
                'date'     => $data['date'],
                'end_date' => $data['end_date'] ?? $data['date'],
                'occasion' => $data['occasion'],
            ]);
            return redirect()
                ->route(ViewsConstants::HLD . '.index')
                ->with('success', __('Holiday successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::HLD . '.index')
            );
        }
    }

    /**
     * @return RedirectResponse
     */
    public function destroy(Request $request, Holiday $holiday): RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('delete holiday'))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            if ($holiday->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $holiday->delete();
            return redirect()
                ->route(ViewsConstants::HLD . '.index')
                ->with('success', __('Holiday successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::HLD . '.index')
            );
        }
    }

    /**
     * @return View|RedirectResponse
     */
    public function calendar(Request $request): View|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_HLD))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $query = Holiday::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId());
            $request->filled('start_date')
                ? $query->where('date', '>=', $request->start_date)
                : null;
            $request->filled('end_date')
                ? $query->where('date', '<=', $request->end_date)
                : null;
            $holidays  = $query->get();
            $transDate = date('Y-m-d');
            $arrHolidays = [];
            foreach ($holidays as $h) {
                $arrHolidays[] = [
                    'id'        => $h->id,
                    'title'     => $h->occasion,
                    'start'     => $h->date,
                    'end'       => $h->end_date,
                    'className' => 'event-primary',
                    'url'       => route(ViewsConstants::HLD . '.edit', $h->id),
                ];
            }
            $arrHolidays = str_replace(
                '"[',
                '[',
                str_replace(']"', ']', json_encode($arrHolidays))
            );
            return view(
                ViewsConstants::HLD . '.' . __FUNCTION__,
                compact('arrHolidays', 'transDate', 'holidays')
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * @return array|JsonResponse
     */
    public function getHolidayData(Request $request): array|JsonResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_HLD))
                return defaultPermissionDenial($request, null, __CLASS__ . '::' . __FUNCTION__);
            $calendarType = $request->get('calendar_type');
            if ($calendarType === 'google_calendar')
                return Utility::getCalendarData('holiday');
            $data     = Holiday::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            $arrayJson = [];
            foreach ($data as $val)
                $arrayJson[] = [
                    'id'        => $val->id,
                    'title'     => $val->occasion,
                    'start'     => $val->date,
                    'end'       => Carbon::parse($val->end_date)->addDay()->format('Y-m-d H:i:s'),
                    'className' => 'event-primary',
                    'textColor' => '#51459d',
                    'url'       => route(ViewsConstants::HLD . '.edit', $val->id),
                    'allDay'    => true,
                ];
            return $arrayJson;
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }
}
