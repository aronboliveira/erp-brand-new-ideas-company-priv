<?php

namespace App\Http\Controllers\Individuals;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MiddlewaresConstants as MWC,
    PermissionsConstants as PMC,
    SettingsConstants as SC,
    UsersConstants as UC,
    ViewsConstants as VW
};
use App\Traits\ChecksLogin;
use App\Models\{
    ClientDeal,
    ClientPermission,
    Contract,
    CustomField,
    Estimation,
    Invoice,
    Plan,
    Role,
    User,
    Utility
};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Hash, Mail, View as ViewFacade};
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\defaultPermissionDenial;
use App\Traits\DefinesResourceActions;
class ClientController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin;
    private const SINGULAR = 'client';

    public function __construct()
    {
        $this->middleware([MWC::AUTH, MWC::XSS]);
    }

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action, $function) {
            try {
                $t = microtime(true);
                self::_authorize($request, PMC::MNG_CLT);
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $t = microtime(true);
                $clients = User::where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->where(UC::COL_TP, self::SINGULAR)
                    ->get();
                $this->logExecutionTime($t, $action . '::loadClients', 'completed');

                $view = VW::CLT . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                return view($view, compact(DC::TABLE_CLIENTS));
            } catch (\Throwable $e) {
                return defaultPermissionDenial($request, $e, $action);
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function create(Request $request): RedirectResponse|View|JsonResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action, $function) {
            try {
                $t = microtime(true);
                self::_authorize($request, PMC::CR_CLT);
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $isAjax = method_exists($request, 'ajax') ? $request->ajax() : ($request->ajax ?? false);

                if ($isAjax) {
                    $view = VW::CLT . '.create_ajax';
                    $t = microtime(true);
                    if (!ViewFacade::exists($view)) {
                        $this->logExecutionTime($t, $action . '::viewCheckAjax', 'missing');
                        throw new \RuntimeException("View [$view] not found");
                    }
                    $this->logExecutionTime($t, $action . '::viewCheckAjax', 'ok');
                    return view($view);
                }

                $t = microtime(true);
                $customFields = CustomField::where('module', self::SINGULAR)->get();
                $this->logExecutionTime($t, $action . '::loadCustomFields', 'completed');

                $view = VW::CLT . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                return view($view, compact('customFields'));
            } catch (\Throwable $e) {
                return (method_exists($request, 'ajax') ? $request->ajax() : ($request->ajax ?? false))
                    ? response()->json(['error' => __('Permission Denied.')], 401)
                    : defaultPermissionDenial($request, $e, $action);
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                if (($uor = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) {
                    return $uor;
                }
                $creator = $uor;

                $t = microtime(true);
                self::_authorize($request, PMC::CR_CLT);
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $t = microtime(true);
                $defaultLang = DB::table(DC::TABLE_SETTINGS)
                    ->where('name', SC::DEF_LNG)
                    ->where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->value('value') ?? DC::DEFAULT_LANG;
                $this->logExecutionTime($t, $action . '::loadDefaultLang', 'completed');

                $t = microtime(true);
                $request->validate([
                    'name'     => 'required|string',
                    'email'    => 'required|email|unique:users',
                    'password' => 'required|string',
                ]);
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
                $plan  = Plan::find($creator->plan());
                $total = User::where(DC::COL_TABLE_CREATOR, $creator->creatorId())
                    ->where(UC::COL_TP, self::SINGULAR)
                    ->count();
                $this->logExecutionTime($t, $action . '::planCheck', 'completed');

                if ($plan->max_clients !== -1 && $total >= $plan->max_clients) {
                    throw new \Exception(__('Your user limit is over. Please upgrade your plan.'));
                }

                $t = microtime(true);
                $client = User::create([
                    'name'              => $request->name,
                    'email'             => $request->email,
                    'job_title'         => $request->job_title,
                    'password'          => Hash::make($request->password),
                    UC::COL_TP => self::SINGULAR,
                    'lang'              => $defaultLang,
                    DC::COL_TABLE_CREATOR => $creator->creatorId(),
                    'email_verified_at' => now()->toDateTimeString(),
                ]);
                $this->logExecutionTime($t, $action . '::persistClient', 'completed');

                $resp = [];
                if ((Utility::settings()['new_client'] ?? 0) == 1) {
                    $t = microtime(true);
                    $client->assignRole(Role::findByName(self::SINGULAR));
                    $resp = Utility::sendEmailTemplate(
                        'new_client',
                        [$client->email],
                        [
                            'client_name'     => $client->name,
                            'client_email'    => $client->email,
                            'client_password' => $request->password,
                        ]
                    );
                    $this->logExecutionTime($t, $action . '::sendEmail', 'completed');
                }

                return redirect()->route(VW::CLT . '.index')->with(
                    'success',
                    __('Client successfully added.')
                        . ((($resp['is_success'] ?? true) === false && ($resp['error'] ?? false))
                            ? '<br><span class="text-danger">' . $resp['error'] . '</span>'
                            : ''
                        )
                );
            } catch (AuthorizationException $e) {
                return (method_exists($request, 'ajax') ? $request->ajax() : ($request->ajax ?? false))
                    ? response()->json(['error' => __('Permission Denied.')], 401)
                    : defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function show(Request $request, User $client): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $client, $action, $function) {
            try {
                $t = microtime(true);
                self::_authorize($request, 'view client');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $t = microtime(true);
                $ownerOk = ($client->creatorId() === $request->user()->id) && ($client->type === self::SINGULAR);
                $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
                if (!$ownerOk) {
                    throw new \Exception('Invalid Client');
                }

                $t = microtime(true);
                $estimations = $client->clientEstimations()->orderByDesc('id')->get();
                $contracts   = $client->clientContracts()->orderByDesc('id')->get();
                $this->logExecutionTime($t, $action . '::loadRelations', 'completed');

                $summary = fn($col, $getter) => [
                    'total'        => $getter($col),
                    'this_month'   => $getter($col)->whereMonth($col, now()->month),
                    'this_week'    => $getter($col)->whereBetween($col, [now()->startOfWeek(), now()->endOfWeek()]),
                    'last_30days'  => $getter($col)->whereDate($col, '>=', now()->subDays(30)),
                ];

                $t = microtime(true);
                $cntEst = array_map(
                    fn($set) => Estimation::getEstimationSummary($set),
                    $summary('issue_date', fn($c) => $c)
                );
                $cntEst += [
                    'cnt_' . array_key_first($cntEst) => $estimations->count(),
                    'cnt_this_month' => $estimations->whereMonth('issue_date', now()->month)->count(),
                    'cnt_this_week'  => $estimations->whereBetween('issue_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'cnt_last_30days' => $estimations->whereDate('issue_date', '>=', now()->subDays(30))->count(),
                ];

                $cntCont = array_map(
                    fn($set) => Contract::getContractSummary($set),
                    $summary('start_date', fn($c) => $c)
                );
                $cntCont += [
                    'cnt_total'       => $contracts->count(),
                    'cnt_this_month'  => $contracts->whereMonth('start_date', now()->month)->count(),
                    'cnt_this_week'   => $contracts->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'cnt_last_30days' => $contracts->whereDate('start_date', '>=', now()->subDays(30))->count(),
                ];
                $this->logExecutionTime($t, $action . '::summaries', 'completed');

                $view = VW::CLT . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                return view($view, compact(
                    self::SINGULAR,
                    'estimations',
                    'cntEst',
                    DC::TABLE_CONTRACTS,
                    'cntCont'
                ));
            } catch (\Throwable $e) {
                return defaultPermissionDenial($request, $e, $action);
            }
        }, ['client_id' => $client->id, 'uri' => $request->getRequestUri()]);
    }

    public function edit(Request $request, User $client): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $client, $action, $function) {
            try {
                $t = microtime(true);
                self::_authorize($request, 'edit client');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $ownerOk = $client->created_by === $request->user()->creatorId();
                $this->logExecutionTime(microtime(true), $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
                if (!$ownerOk) {
                    throw new \Exception('Invalid Client');
                }

                $t = microtime(true);
                $customFields = CustomField::where('module', self::SINGULAR)->get();
                tap($client)->customField = CustomField::getData($client, self::SINGULAR);
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');

                $view = VW::CLT . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                return view($view, [
                    self::SINGULAR => $client,
                    'customFields' => $customFields,
                ]);
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 401);
            }
        }, ['client_id' => $client->id]);
    }

    public function update(Request $request, User $client): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $client, $action) {
            try {
                $t = microtime(true);
                self::_authorize($request, 'edit client');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $ownerOk = $client->created_by === $request->user()->creatorId();
                $this->logExecutionTime(microtime(true), $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
                if (!$ownerOk) {
                    throw new \Exception('Invalid Client');
                }

                $t = microtime(true);
                $rules = [
                    'name'  => 'required|string',
                    'email' => 'required|email|unique:users,email,' . $client->id,
                ];
                if ($request->filled('password')) {
                    $rules['password'] = 'required|confirmed';
                }
                $request->validate($rules);
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
                $client->fill([
                    'name'  => $request->name,
                    'email' => $request->email,
                    'password' => $request->filled('password')
                        ? Hash::make($request->password)
                        : $client->password,
                ])->save();
                $this->logExecutionTime($t, $action . '::persistClient', 'completed');

                $t = microtime(true);
                CustomField::saveData($client, $request->customField);
                $this->logExecutionTime($t, $action . '::persistCustomFields', 'completed');

                return redirect()->back()->with('success', __('Client Updated Successfully!'));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }, ['client_id' => $client->id, 'uri' => $request->getRequestUri()]);
    }

    public function destroy(Request $request, User $client): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $client, $action) {
            try {
                $t = microtime(true);
                self::_authorize($request, 'delete client');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $ownerOk = $client->created_by === $request->user()->creatorId();
                $this->logExecutionTime(microtime(true), $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
                if (!$ownerOk) {
                    throw new \Exception('Invalid Client');
                }

                $t = microtime(true);
                if (Estimation::where('client_id', $client->id)->exists()) {
                    $this->logExecutionTime($t, $action . '::checkDependencies', 'blocked');
                    throw new \Exception(__('This client has assigned some estimation.'));
                }
                $this->logExecutionTime($t, $action . '::checkDependencies', 'ok');

                $t = microtime(true);
                $client->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return redirect()->back()->with('success', __('Client Deleted Successfully!'));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }, ['client_id' => $client->id, 'uri' => $request->getRequestUri()]);
    }

    public const CLT_PSW = 'clientPassword';
    public function clientPassword(string|int $id, Request $request): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($id, $request, $action) {
            if (($uor = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) {
                return $uor;
            }
            $u = $uor;

            try {
                $t = microtime(true);
                self::_authorize($request, 'edit client');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $t = microtime(true);
                $user = User::findOrFail(Crypt::decrypt($id));
                $client = ($u->creatorId() === $user->creatorId() && $user->type == self::SINGULAR)
                    ? $user
                    : throw new \Exception('Invalid Client');
                $this->logExecutionTime($t, $action . '::loadClient', 'completed');

                $view = VW::CLT . '.reset';
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                return view($view, compact('user', self::SINGULAR));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }, ['client_enc_id' => $id, 'uri' => $request->getRequestUri()]);
    }

    public const CLT_PSW_R = 'clientPasswordReset';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function clientPasswordReset(string|int $id, Request $request): RedirectResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($id, $request, $action) {
            try {
                $t = microtime(true);
                self::_authorize($request, 'edit client');
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $t = microtime(true);
                $request->validate(['password' => 'required|confirmed']);
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
                User::findOrFail($id)->update(['password' => Hash::make($request->password)]);
                $this->logExecutionTime($t, $action . '::persistPassword', 'completed');

                return redirect()->route(VW::CLT . '.index')
                    ->with('success', 'Client Password successfully updated.');
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }, ['client_id' => $id, 'uri' => $request->getRequestUri()]);
    }

    protected static function _authorize(
        Request $request,
        string  $permission
    ): void {
        if (!$request->user()->can($permission)) {
            throw new AuthorizationException($permission);
        }
    }
}
