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
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Spatie\GoogleCalendar\Event as GoogleEvent;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class HolidayController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin;

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::HLD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_HLD)) return defaultPermissionDenial($request, null, $action);
            $q = Holiday::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
            if ($request->filled('start_date')) $q->where('date', '>=', $request->start_date);
            if ($request->filled('end_date')) $q->where('date', '<=', $request->end_date);
            $holidays = $q->get();
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('holidays'));
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::HLD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('create holiday')) return defaultPermissionDenial($request, null, $action);
            $settings = Utility::settings();
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('settings'));
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('create holiday')) return defaultPermissionDenial($request, null, $action);
            $data = $request->validate([
                'date'             => 'required|date',
                'end_date'         => 'nullable|date',
                'occasion'         => 'required|string',
                'synchronize_type' => 'nullable|string',
            ]);
            $holiday = Holiday::create([
                'date'       => $data['date'],
                'end_date'   => $data['end_date'] ?? $data['date'],
                'occasion'   => $data['occasion'],
                DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
            ]);
            $setting = Utility::settingsById($user?->creatorId());
            $notifyData = ['holiday_title' => $holiday->occasion, 'holiday_date' => $holiday->date];
            if (!empty($setting['holiday_notification'])) Utility::sendSlackMsg('new_holiday', $notifyData);
            if (!empty($setting['telegram_holiday_notification'])) Utility::sendTelegramMsg('new_holiday', $notifyData);
            if (($data['synchronize_type'] ?? null) === 'google_calendar') Utility::addCalendarData($holiday, 'holiday');
            if ($webhook = Utility::webhookSetting('New Holiday')) {
                $ok = Utility::webhookCall($webhook['url'], json_encode($holiday), $webhook['method']);
                if (!$ok) return redirect()->route(ViewsConstants::HLD . '.index')->with('error', __('Webhook call failed.'));
            }
            return redirect()->route(ViewsConstants::HLD . '.index')->with('success', __('Holiday successfully created.'));
        });
    }

    public function show(Request $request, Holiday $holiday): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::HLD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $holiday, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('show holiday')) return defaultPermissionDenial($request, null, $action);
            if ($holiday->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, $action);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('holiday'));
        });
    }

    public function edit(Request $request, Holiday $holiday): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::HLD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $holiday, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('edit holiday')) return defaultPermissionDenial($request, null, $action);
            if ($holiday->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, $action);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('holiday'));
        });
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $holiday, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('edit holiday')) return defaultPermissionDenial($request, null, $action);
            if ($holiday->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, $action);
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
            return redirect()->route(ViewsConstants::HLD . '.index')->with('success', __('Holiday successfully updated.'));
        });
    }

    public function destroy(Request $request, Holiday $holiday): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $holiday, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('delete holiday')) return defaultPermissionDenial($request, null, $action);
            if ($holiday->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, $action);
            $holiday->delete();
            return redirect()->route(ViewsConstants::HLD . '.index')->with('success', __('Holiday successfully deleted.'));
        });
    }

    public function calendar(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::HLD . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_HLD)) return defaultPermissionDenial($request, null, $action);
            $q = Holiday::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
            if ($request->filled('start_date')) $q->where('date', '>=', $request->start_date);
            if ($request->filled('end_date')) $q->where('date', '<=', $request->end_date);
            $holidays = $q->get();
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
            $arrHolidays = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrHolidays)));
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
            return view($view, compact('arrHolidays', 'transDate', 'holidays'));
        });
    }

    public const GET_HL_D = 'getHolidayData';
    public function getHolidayData(Request $request): array|JsonResponse|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PermissionsConstants::MNG_HLD)) return defaultPermissionDenial($request, null, $action);
            $calendarType = $request->get('calendar_type');
            if ($calendarType === 'google_calendar') return Utility::getCalendarData('holiday');
            $data = Holiday::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
            $arrayJson = [];
            foreach ($data as $val) {
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
            }
            return $arrayJson;
        });
    }
}
