<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
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
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Hash, Mail};
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;

class ClientController extends Controller
{
    use ChecksLogin;
    private const SINGULAR = 'client';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(Request $request)
    {
        try {
            self::_authorize($request, PermissionsConstants::MNG_CLT);
            $clients = User::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->where(UsersConstants::COL_TP, self::SINGULAR)
                ->get();
            return view(
                ViewsConstants::CLT . '.' . __FUNCTION__,
                compact(DatabaseConstants::TABLE_CLIENTS)
            );
        } catch (\Throwable $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request)
    {
        try {
            self::_authorize($request, 'create client');
            return $request->ajax
                ? view(ViewsConstants::CLT . '.create_ajax')
                : view(
                    ViewsConstants::CLT . '.' . __FUNCTION__,
                    ['customFields' => CustomField::where('module', self::SINGULAR)->get()]
                );
        } catch (\Throwable $e) {
            return $request->ajax
                ? response()->json(['error' => __('Permission Denied.')], 401)
                : defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request)
    {
        try {
            if (
                ($uor = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return $uor;
            $creator = $uor;
            self::_authorize($request, 'create client');
            $defaultLang = DB::table(DatabaseConstants::TABLE_SETTINGS)
                ->where('name', SettingsConstants::DEF_LNG)
                ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->value('value') ?? DatabaseConstants::DEFAULT_LANG;
            $request->validate([
                'name'     => 'required|string',
                'email'    => 'required|email|unique:users',
                'password' => 'required|string',
            ]);
            $plan      = Plan::find($creator->plan());
            $total     = User::where(DatabaseConstants::TABLE_CREATOR, $creator->creatorId())
                ->where(UsersConstants::COL_TP, self::SINGULAR)->count();
            if ($plan->max_clients !== -1 && $total >= $plan->max_clients)
                throw new \Exception(__('Your user limit is over. Please upgrade your plan.'));
            $client = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'job_title'         => $request->job_title,
                'password'          => Hash::make($request->password),
                UsersConstants::COL_TP              => self::SINGULAR,
                'lang'              => $defaultLang,
                DatabaseConstants::TABLE_CREATOR        => $creator->creatorId(),
                'email_verified_at' => now()->toDateTimeString(),
            ]);
            $resp = [];
            if (Utility::settings()['new_client'] == 1) {
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
            }
            return redirect()->route(ViewsConstants::CLT . '.index')
                ->with(
                    'success',
                    __('Client successfully added.')
                        . (($resp['is_success'] === false && $resp['error'] ?? false)
                            ? '<br><span class="text-danger">' . $resp['error'] . '</span>'
                            : ''
                        )
                );
        } catch (AuthorizationException $e) {
            return $request->ajax
                ? response()->json(['error' => __('Permission Denied.')], 401)
                : defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, User $client)
    {
        try {
            self::_authorize($request, 'view client');
            if ($client->creatorId() !== $request->user()->id || $client->type !== self::SINGULAR) {
                throw new \Exception('Invalid Client');
            }
            $estimations = $client->clientEstimations()->orderByDesc('id')->get();
            $contracts  = $client->clientContracts()->orderByDesc('id')->get();
            $summary = fn ($col, $getter) => [
                'total' => $getter($col),
                'this_month' => $getter($col)->whereMonth(
                    $col,
                    now()->month
                ),
                'this_week' => $getter($col)->whereBetween(
                    $col,
                    [now()->startOfWeek(), now()->endOfWeek()]
                ),
                'last_30days' => $getter($col)->whereDate(
                    $col,
                    '>=',
                    now()->subDays(30)
                ),
            ];
            $cntEst = array_map(
                fn ($set) => Estimation::getEstimationSummary($set),
                $summary('issue_date', fn ($c) => $c)
            );
            $cntEst += [
                'cnt_' . array_key_first($cntEst) => $estimations->count(),
                'cnt_this_month' => $estimations->whereMonth('issue_date', now()->month)->count(),
                'cnt_this_week' => $estimations->whereBetween(
                    'issue_date',
                    [now()->startOfWeek(), now()->endOfWeek()]
                )->count(),
                'cnt_last_30days' => $estimations->whereDate(
                    'issue_date',
                    '>=',
                    now()->subDays(30)
                )->count(),
            ];

            $cntCont = array_map(
                fn ($set) => Contract::getContractSummary($set),
                $summary('start_date', fn ($c) => $c)
            );
            $cntCont += [
                'cnt_total' => $contracts->count(),
                'cnt_this_month' => $contracts->whereMonth('start_date', now()->month)->count(),
                'cnt_this_week' => $contracts->whereBetween(
                    'start_date',
                    [now()->startOfWeek(), now()->endOfWeek()]
                )->count(),
                'cnt_last_30days' => $contracts->whereDate(
                    'start_date',
                    '>=',
                    now()->subDays(30)
                )->count(),
            ];

            return view(
                ViewsConstants::CLT . '.' . __FUNCTION__,
                compact(
                    self::SINGULAR,
                    'estimations',
                    'cntEst',
                    DatabaseConstants::TABLE_CONTRACTS,
                    'cntCont'
                )
            );
        } catch (\Throwable $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(Request $request, User $client)
    {
        try {
            self::_authorize($request, 'edit client');
            if ($client->created_by !== $request->user()->creatorId()) {
                throw new \Exception('Invalid Client');
            }

            return view(
                ViewsConstants::CLT . '.' . __FUNCTION__,
                [
                    self::SINGULAR       => tap($client)
                        ->customField = CustomField::getData($client, self::SINGULAR),
                    'customFields' => CustomField::where('module', self::SINGULAR)->get()
                ]
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function update(Request $request, User $client)
    {
        try {
            self::_authorize($request, 'edit client');
            if ($client->created_by !== $request->user()->creatorId()) {
                throw new \Exception('Invalid Client');
            }

            $rules = [
                'name'  => 'required|string',
                'email' => 'required|email|unique:users,email,' . $client->id,
            ];
            if ($request->filled('password')) {
                $rules['password'] = 'required|confirmed';
            }

            $request->validate($rules);

            $client->fill([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->filled('password')
                    ? Hash::make($request->password)
                    : $client->password
            ])->save();

            CustomField::saveData($client, $request->customField);

            return redirect()->back()
                ->with('success', __('Client Updated Successfully!'));
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, User $client)
    {
        try {
            self::_authorize($request, 'delete client');
            if ($client->created_by !== $request->user()->creatorId()) {
                throw new \Exception('Invalid Client');
            }
            if (Estimation::where('client_id', $client->id)->exists()) {
                throw new \Exception(__('This client has assigned some estimation.'));
            }
            $client->delete();

            return redirect()->back()
                ->with('success', __('Client Deleted Successfully!'));
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function clientPassword(string|int $id, Request $request)
    {
        if (
            ($uor = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $uor;
        $u = $uor;
        try {
            self::_authorize($request, 'edit client');
            $user  = User::findOrFail(Crypt::decrypt($id));
            $client = $u->creatorId() === $user->creatorId() && $user->type == self::SINGULAR
                ? $user
                : throw new \Exception('Invalid Client');

            return view(ViewsConstants::CLT . '.reset', compact('user', self::SINGULAR));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function clientPasswordReset(string|int $id, Request $request)
    {
        try {
            self::_authorize($request, 'edit client');
            $request->validate(['password' => 'required|confirmed']);

            User::findOrFail($id)
                ->update(['password' => Hash::make($request->password)]);

            return redirect()->route(ViewsConstants::CLT . '.index')
                ->with('success', 'Client Password successfully updated.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
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
