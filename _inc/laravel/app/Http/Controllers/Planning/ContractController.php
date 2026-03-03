<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Traits\ChecksLogin;
use App\Models\{
    Contract,
    ContractAttachment,
    ContractComment,
    ContractNote,
    ContractType,
    Project,
    User,
    Utility
};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{Auth, Log, Storage};
use Illuminate\View\View;

class ContractController extends Controller
{
    use ChecksLogin;

    private const ENTITY = 'contracts';
    public function index(Request $request): RedirectResponse|View
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $function) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can(PMC::MNG_CTC)) return defaultPermissionDenial($request, null, $action);
            try {
                $creator = $user?->creatorId();
                // company overview
                if ($user->type == PMC::CPN) {
                    $companyStart = microtime(true);
                    $all = Contract::with([DC::TABLE_CLIENTS, DC::TABLE_PROJECTS, 'types'])
                        ->where(DC::COL_TABLE_CREATOR, $creator)->get();
                    $byMonth = Contract::where(DC::COL_TABLE_CREATOR, $creator)
                        ->whereMonth(PJC::COL_S_DT, now()->month)->get();
                    $byWeek = Contract::where(DC::COL_TABLE_CREATOR, $creator)
                        ->whereBetween(PJC::COL_S_DT, [now()->startOfWeek(), now()->endOfWeek()])->get();
                    $last30 = Contract::where(DC::COL_TABLE_CREATOR, $creator)
                        ->whereDate(PJC::COL_S_DT, '>', now()->subDays(30))->get();
                    $summarize = fn($set) => \App\Models\Contract::getContractSummary($set);
                    $cnt = [
                        'total' => $summarize($all),
                        'this_month' => $summarize($byMonth),
                        'this_week' => $summarize($byWeek),
                        'last_30days' => $summarize($last30),
                    ];
                    $this->logExecutionTime($companyStart, $action . '::companyOverview', 'completed');
                    return view(self::ENTITY . '.' . $function, compact('all', 'cnt'));
                }

                // client overview
                if ($user->type == PMC::CL) {
                    $clientStart = microtime(true);
                    $all = Contract::with('types')->where(PJC::COL_CLIENT_NAME, $user?->id)->get();
                    $byMonth = Contract::where(PJC::COL_CLIENT_NAME, $user?->id)
                        ->whereMonth(PJC::COL_S_DT, now()->month)->get();
                    $byWeek = Contract::where(PJC::COL_CLIENT_NAME, $user?->id)
                        ->whereBetween(PJC::COL_S_DT, [now()->startOfWeek(), now()->endOfWeek()])->get();
                    $last30 = Contract::where(PJC::COL_CLIENT_NAME, $user?->id)
                        ->whereDate(PJC::COL_S_DT, '>', now()->subDays(30))->get();
                    $summarize = fn($set) => \App\Models\Contract::getContractSummary($set);
                    $cnt = [
                        'total' => $summarize($all),
                        'this_month' => $summarize($byMonth),
                        'this_week' => $summarize($byWeek),
                        'last_30days' => $summarize($last30),
                    ];
                    $this->logExecutionTime($clientStart, $action . '::clientOverview', 'completed');
                    return view(self::ENTITY . '.' . $function, compact('all', 'cnt'));
                }
                // fallback: all for other user types
                $allStart = microtime(true);
                $all = Contract::with([DC::TABLE_CLIENTS, DC::TABLE_PROJECTS, 'types'])
                    ->where(DC::COL_TABLE_CREATOR, $creator)->get();
                $this->logExecutionTime($allStart, $action . '::fallbackAll', 'completed');
                return view(self::ENTITY . '.' . $function, compact('all'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, [UC::COL_USER_ID => Auth::id()]);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        Log::debug($method . ' - start', [UC::COL_USER_ID => auth()->id()]);
        return $this->measureProfile($method, function () use ($request, $method, $function) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can('create contract')) return defaultPermissionDenial($request, null, $method);
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $creator = $user?->creatorId();
            $stepStart = microtime(true);
            $contractTypes = ContractType::where(DC::COL_TABLE_CREATOR, $creator)
                ->pluck('name', 'id');
            $this->logExecutionTime($stepStart, 'fetchContractTypes', 'completed');
            $stepStart = microtime(true);
            $clients = User::where(UC::COL_TP, PMC::CL)
                ->where(DC::COL_TABLE_CREATOR, $creator)
                ->pluck(UC::COL_NM, 'id');
            $clients->prepend(__('Select Client'), 0);
            $this->logExecutionTime($stepStart, 'fetchClients', 'completed');
            $stepStart = microtime(true);
            $projects = Project::where(DC::COL_TABLE_CREATOR, $creator)
                ->pluck(PJC::COL_NM, 'id');
            $this->logExecutionTime($stepStart, 'fetchProjects', 'completed');
            return view(self::ENTITY . '.' . $function, compact('contractTypes', DC::TABLE_CLIENTS, DC::TABLE_PROJECTS));
        }, [UC::COL_USER_ID => auth()->id()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');

            if (!$user?->can('create contract')) {
                Log::warning($method . ' permission denied', [UC::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }

            $startValidation = microtime(true);
            $v = validator($request->all(), [
                PJC::COL_CLIENT_NAME => 'required',
                'subject'     => 'required',
                'type'        => 'required',
                'value'       => 'required|numeric',
                PJC::COL_S_DT  => 'required|date',
                PJC::COL_E_DT    => 'required|date',
            ]);
            if ($v->fails()) {
                $this->logExecutionTime($startValidation, $function . '::validation', 'failed');
                return redirect()->route(static::ENTITY . '.index')
                    ->with('error', $v->errors()->first());
            }
            $this->logExecutionTime($startValidation, $function . '::validation', 'completed');

            try {
                $startCreate = microtime(true);
                $c = Contract::create([
                    PJC::COL_CLIENT_NAME => $request->{PJC::COL_CLIENT_NAME},
                    'subject'     => $request->subject,
                    PJC::COL_PJ_ID  => $request->{PJC::COL_PJ_ID},
                    'type'        => $request->type,
                    'value'       => $request->value,
                    PJC::COL_S_DT  => $request->{PJC::COL_S_DT},
                    PJC::COL_E_DT    => $request->{PJC::COL_E_DT},
                    'description' => $request->description,
                    DC::COL_TABLE_CREATOR  => $user?->creatorId(),
                ]);
                $this->logExecutionTime($startCreate, $function . '::createContract', 'completed');

                $settings = Utility::settings($user?->creatorId());
                $client  = User::findOrFail($c->{PJC::COL_CLIENT_NAME});
                $payload = [
                    'contract_subject'    => $c->subject,
                    'contract_client'     => $client->name,
                    'contract_value'      => $user?->priceFormat($c->value),
                    'contract_start_date' => $user?->dateFormat($c->{PJC::COL_S_DT}),
                    'contract_end_date'   => $user?->dateFormat($c->{PJC::COL_E_DT}),
                    'user_name'           => $user?->name,
                ];

                foreach (
                    [
                        'new_contract'                   => fn() => Utility::sendEmailTemplate(
                            'new_contract',
                            [$client->id => $client->email],
                            $payload
                        ),
                        'contract_notification'          => fn() => Utility::sendSlackMsg(
                            'new_contract',
                            $payload
                        ),
                        'telegram_contract_notification' => fn() => Utility::sendTelegramMsg(
                            'new_contract',
                            $payload
                        ),
                    ] as $flag => $action
                )
                    if (!empty($settings[$flag])) $action();

                if ($hook = Utility::webhookSetting('New Contract')) {
                    Utility::webhookCall(
                        $hook['url'],
                        $c->toJson(),
                        $hook['method']
                    );
                }

                return redirect()->route(static::ENTITY . '.index')
                    ->with('success', __('Contract successfully created!'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public function show(Request $request, int|string $id): RedirectResponse|View
    {
        $class = static::class;
        $function = __FUNCTION__;
        $action = "{$class}::{$function}";
        return $this->measureProfile($action, function () use ($request, $id, $action, $function) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can('show contract')) return defaultPermissionDenial($request, null, $action);
                $c = Contract::findOrFail($id);
                if ($c->created_by !== $user?->creatorId()) return defaultPermissionDenial($request, null, $action);
                $client = $c->client;
                $this->logExecutionTime($stepStart, 'view contract', 'completed');
                return view(self::ENTITY . '.' . $function, compact('c', PMC::CL));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function edit(Request $request, int|string $id): RedirectResponse|View
    {
        $function = __FUNCTION__;
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id, $function) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!$user?->can('edit contract')) return defaultPermissionDenial($request, null, $action);
            try {
                $findStart = microtime(true);
                $c = Contract::findOrFail($id);
                $this->logExecutionTime($findStart, $action . '::findOrFail', 'completed');
                $creator = $user?->creatorId();
                $typesStart = microtime(true);
                $types = ContractType::where(DC::COL_TABLE_CREATOR, $creator)->pluck('name', 'id');
                $this->logExecutionTime($typesStart, $action . '::types', 'completed');
                $clientsStart = microtime(true);
                $clients = User::where('type', PMC::CL)
                    ->where(DC::COL_TABLE_CREATOR, $creator)
                    ->pluck(UC::COL_NM, 'id');
                $this->logExecutionTime($clientsStart, $action . '::clients', 'completed');
                $projectsStart = microtime(true);
                $projects = Project::where(DC::COL_TABLE_CREATOR, $creator)
                    ->pluck(PJC::COL_NM, 'id');
                $this->logExecutionTime($projectsStart, $action . '::projects', 'completed');
                return view(self::ENTITY . '.' . $function, compact('c', 'types', DC::TABLE_CLIENTS, DC::TABLE_PROJECTS));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['id' => $id]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can('edit contract')) return defaultPermissionDenial($request, null, $method);
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $stepStart = microtime(true);
            $v = validator($request->all(), [
                PJC::COL_CLIENT_NAME => 'required',
                'subject' => 'required',
                'type' => 'required',
                'value' => 'required|numeric',
                PJC::COL_S_DT => 'required|date',
                PJC::COL_E_DT => 'required|date',
            ]);
            $this->logExecutionTime($stepStart, 'validateRequest', 'completed');
            if ($v->fails())
                return redirect()->route(self::ENTITY . '.index')->with('error', $v->errors()->first());
            $stepStart = microtime(true);
            try {
                $c = Contract::findOrFail($id);
                $c->update($request->only([
                    PJC::COL_CLIENT_NAME,
                    'subject',
                    PJC::COL_PJ_ID,
                    'type',
                    'value',
                    PJC::COL_S_DT,
                    PJC::COL_E_DT,
                    'description'
                ]));
                $this->logExecutionTime($stepStart, 'updateContract', 'completed');
                return redirect()->route(self::ENTITY . '.index')
                    ->with('success', __('Contract successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method);
            }
        }, [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            if (!$user?->can('delete contract')) {
                Log::warning($method . ' permission denied', [UC::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            try {
                $startDelete = microtime(true);
                Contract::findOrFail($id)->delete();
                $this->logExecutionTime($startDelete, $function . '::delete', 'completed');
                return redirect()->route(static::ENTITY . '.index')
                    ->with('success', __('Contract successfully deleted.'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public function description(Request $request, int|string $id): RedirectResponse|View
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can('show contract')) return defaultPermissionDenial($request, null, $action);
                $c = Contract::findOrFail($id);
                $this->logExecutionTime($stepStart, 'view contract description', 'completed');
                return view(self::ENTITY . '.description', compact('c'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function grid(Request $request): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (!in_array($user?->type, [PMC::SA, PMC::CPN, PMC::CL]))
                return defaultPermissionDenial($request, null, $action);
            try {
                $qryStart = microtime(true);
                $qry = Contract::query();
                $qry->where(
                    ($user->type == PMC::CPN || $user->type == PMC::SA)
                        ? DC::COL_TABLE_CREATOR
                        : 'client_id',
                    ($user->type == PMC::CPN || $user->type == PMC::SA)
                        ? $user?->creatorId()
                        : $user?->id
                );
                $this->logExecutionTime($qryStart, $action . '::query', 'completed');
                $listStart = microtime(true);
                $list = $qry->get();
                $this->logExecutionTime($listStart, $action . '::getList', 'completed');
                return view(self::ENTITY . '.grid', compact('list'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage()]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, []);
    }

    public function fileUpload(Request $request, int|string $id): JsonResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => 'Permission denied'], 401);
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can(PMC::MNG_CTC))
                return response()->json(['error' => 'Permission denied'], 401);
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $stepStart = microtime(true);
            $request->validate(['file' => 'required|file']);
            $this->logExecutionTime($stepStart, 'validateRequest', 'completed');
            try {
                $stepStart = microtime(true);
                $c = Contract::findOrFail($id);
                $size = $request->file('file')->getSize();
                Utility::updateStorageLimit($user?->creatorId(), $size);
                $this->logExecutionTime($stepStart, 'updateStorageLimit', 'completed');
                $stepStart = microtime(true);
                $name = $id . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $request->file('file')->getClientOriginalName());
                $dir = 'contract_attachment/';
                $path = Utility::uploadFile($request, 'file', $name, $dir, []);
                $this->logExecutionTime($stepStart, 'fileUpload', 'completed');
                if ($path['flag'] !== 1)
                    return response()->json(['error' => __($path['msg'])], 500);
                $attach = ContractAttachment::create([
                    PJC::COL_CTC_ID => $c->id,
                    UC::COL_USER_ID => $user?->id,
                    'files' => $name,
                ]);
                return response()->json([
                    'download' => route(DC::TABLE_CONTRACTS . '.file.download', [$c->id, $attach->id]),
                    'delete' => route(DC::TABLE_CONTRACTS . '.file.delete', [$c->id, $attach->id]),
                    'is_success' => true
                ], 200);
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $method, '/', false, []);
            }
        }, [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
    }

    public function fileDownload(Request $request, int|string $id, int|string $fileId): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $fileId, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            if (!$user?->can(PMC::MNG_CTC)) {
                Log::warning($method . ' permission denied', [UC::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }
            try {
                $startFetch = microtime(true);
                $file = ContractAttachment::findOrFail($fileId);
                $this->logExecutionTime($startFetch, $function . '::fetchFile', 'completed');
                $path = storage_path("contract_attachment/{$file->files}");

                $startDownload = microtime(true);
                $response = response()->download($path, $file->files, [
                    'Content-Length' => filesize($path)
                ]);
                $this->logExecutionTime($startDownload, $function . '::download', 'completed');
                return $response;
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public function fileDelete(Request $request, int|string $id, int|string $fileId): JsonResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $fileId, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'id' => $id, 'fileId' => $fileId]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Permission denied'], 401);
                $user = $userOrRedirect;
                if (!$user?->can(PMC::MNG_CTC))
                    return response()->json(['error' => 'Permission denied'], 401);
                $file = ContractAttachment::findOrFail($fileId);
                @unlink(storage_path("contract_attachment/{$file->files}"));
                $file->delete();
                $this->logExecutionTime($stepStart, 'file delete', 'completed');
                return response()->json(['is_success' => true], 200);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'fileId' => $fileId]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'fileId' => $fileId]);
                return defaultUndefinedException($request, $e, $action, '/', false, []);
            }
        });
    }

    public function contractStatusEdit(Request $request, int|string $id): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse)
                return response()->json(['error' => 'Permission denied'], 401);
            $user = $userOrRedirect;

            if (!$user?->can('edit contract'))
                return response()->json(['error' => 'Permission denied'], 401);

            try {
                $findStart = microtime(true);
                $c = Contract::findOrFail($id);
                $this->logExecutionTime($findStart, $action . '::findOrFail', 'completed');
                $c->status = $request->status;
                $saveStart = microtime(true);
                $c->save();
                $this->logExecutionTime($saveStart, $action . '::save', 'completed');
                return response()->json(['is_success' => true], 200);
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'id' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, '/', false, []);
            }
        }, ['id' => $id]);
    }

    public function commentStore(Request $request, int|string $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can(PMC::MNG_CTC)) return defaultPermissionDenial($request, null, $method);
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $stepStart = microtime(true);
            $c = new ContractComment();
            $c->comment = $request->comment;
            $c->{PJC::COL_CTC_ID} = $id;
            $c->{UC::COL_USER_ID} = $user?->id;
            $c->save();
            $this->logExecutionTime($stepStart, 'saveComment', 'completed');
            return redirect()->back()->with('success', __('Comment added.'));
        }, [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
    }

    public function contractDescriptionStore(Request $request, int|string $id): JsonResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return response()->json(['error' => 'Permission denied'], 401);
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            if (!$user?->can('edit contract')) {
                Log::warning($method . ' permission denied', [UC::COL_USER_ID => $user?->id]);
                return response()->json(['error' => 'Permission denied'], 401);
            }
            try {
                $startFetch = microtime(true);
                $c = Contract::findOrFail($id);
                $this->logExecutionTime($startFetch, $function . '::fetchContract', 'completed');
                $c->contract_description = $request->contract_description;
                $startSave = microtime(true);
                $c->save();
                $this->logExecutionTime($startSave, $function . '::saveContract', 'completed');
                return response()->json([
                    'is_success' => true,
                    'message' => __('Contract description saved.')
                ], 200);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method, '/', false, []);
            }
        }, func_get_args());
    }

    public function commentDestroy(Request $request, int|string $id): RedirectResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can(PMC::MNG_CTC)) return defaultPermissionDenial($request, null, $action);
                ContractComment::findOrFail($id)->delete();
                $this->logExecutionTime($stepStart, 'delete comment', 'completed');
                return redirect()->back()->with('success', __('Comment deleted.'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function noteStore(Request $request, int|string $id): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (!$user?->can(PMC::MNG_CTC)) return defaultPermissionDenial($request, null, $action);

            try {
                $saveStart = microtime(true);
                $n = new ContractNote();
                $n->{PJC::COL_CTC_ID} = $id;
                $n->notes = $request->notes;
                $n->{UC::COL_USER_ID} = $user?->id;
                $n->save();
                $this->logExecutionTime($saveStart, $action . '::saveNote', 'completed');
                return redirect()->back()->with('success', __('Note saved.'));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'id' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['id' => $id]);
    }

    public function noteDestroy(Request $request, int|string $id): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
        return $this->measureProfile($method, function () use ($request, $id, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can(PMC::MNG_CTC)) return defaultPermissionDenial($request, null, $method);
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            try {
                $stepStart = microtime(true);
                ContractNote::findOrFail($id)->delete();
                $this->logExecutionTime($stepStart, 'deleteNote', 'completed');
                return redirect()->back()->with('success', __('Note deleted.'));
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
    }

    public function clientWiseProject(Request $request, int|string $clientId): JsonResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $clientId, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return response()->json([], 401);
            }
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');
            try {
                $startFetch = microtime(true);
                $projects = Project::where('client_id', $clientId)->get();
                $this->logExecutionTime($startFetch, $function . '::fetchProjects', 'completed');
                $out = $projects->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->project_name
                ]);
                return response()->json($out, 200);
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return response()->json([], 500);
            }
        }, func_get_args());
    }

    public function printContract(Request $request, int|string $id): RedirectResponse|View
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can('show contract')) return defaultPermissionDenial($request, null, $action);
                $c = Contract::findOrFail($id);
                $settings = Utility::settings();
                $logoDir = asset(Storage::url('uploads/logo/'));
                $companyLogo = Utility::getValByName(SC::CPN_LG) ?: SC::CPN_LG_DK_DEF;
                $img = "$logoDir/$companyLogo";
                $color = '#' . $settings['invoice_color'];
                $fontColor = Utility::getFontColor($color);
                $this->logExecutionTime($stepStart, 'view contract preview', 'completed');
                return view(self::ENTITY . '.preview', compact('c', 'settings', 'img', 'color', 'fontColor'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function copyContract(Request $request, int|string $id): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($action, $request, $id) {
            $checkStart = microtime(true);
            $userOrRedirect = self::_checkLogin();
            $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
            if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (!$user?->can('create contract')) return defaultPermissionDenial($request, null, $action);

            try {
                $findStart = microtime(true);
                $c = Contract::findOrFail($id);
                $this->logExecutionTime($findStart, $action . '::findOrFail', 'completed');
                $creator = $user?->creatorId();

                $clientsStart = microtime(true);
                $clients = User::where('type', PMC::CL)
                    ->where(DC::COL_TABLE_CREATOR, $creator)
                    ->pluck('name', 'id');
                $this->logExecutionTime($clientsStart, $action . '::clients', 'completed');

                $typesStart = microtime(true);
                $types = ContractType::where(DC::COL_TABLE_CREATOR, $creator)
                    ->pluck('name', 'id');
                $this->logExecutionTime($typesStart, $action . '::types', 'completed');

                $projectsStart = microtime(true);
                $projects = Project::where(DC::COL_TABLE_CREATOR, $creator)
                    ->pluck('title', 'id');
                $this->logExecutionTime($projectsStart, $action . '::projects', 'completed');

                $c->date_range = "{$c->{PJC::COL_S_DT}} to {$c->{PJC::COL_E_DT}}";
                Log::info("[$action] loading copy contract view", [PJC::COL_CTC_ID => $id]);
                return view(self::ENTITY . '.copy', compact(
                    'c',
                    DC::TABLE_CLIENTS,
                    'types',
                    DC::TABLE_PROJECTS
                ));
            } catch (\Throwable $e) {
                Log::error("[$action] failed", ['error' => $e->getMessage(), 'id' => $id]);
                Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['id' => $id]);
    }

    public function copyContractStore(Request $request): RedirectResponse
    {
        $method = __METHOD__;
        Log::debug($method . ' - start', [UC::COL_USER_ID => auth()->id()]);
        return $this->measureProfile($method, function () use ($request, $method) {
            $stepStart = microtime(true);
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
            $stepStart = microtime(true);
            if (!$user?->can('create contract')) return defaultPermissionDenial($request, null, $method);
            $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
            $stepStart = microtime(true);
            $v = validator($request->all(), [
                PMC::CL => 'required',
                'subject' => 'required',
                PJC::COL_PJ_ID => 'required|array',
                'type' => 'required',
                'value' => 'required|numeric',
                PJC::COL_S_DT => 'required|date',
                PJC::COL_E_DT => 'required|date',
            ]);
            $this->logExecutionTime($stepStart, 'validateRequest', 'completed');
            if ($v->fails()) {
                return redirect()->route(self::ENTITY . '.index')->with('error', $v->errors()->first());
            }
            try {
                $stepStart = microtime(true);
                $c = Contract::create([
                    PJC::COL_CLIENT_NAME => $request->client,
                    'subject' => $request->subject,
                    PJC::COL_PJ_ID => implode(',', $request->{PJC::COL_PJ_ID}),
                    'type' => $request->type,
                    'value' => $request->value,
                    PJC::COL_S_DT => $request->{PJC::COL_S_DT},
                    PJC::COL_E_DT => $request->{PJC::COL_E_DT},
                    'description' => $request->description,
                    DC::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                $this->logExecutionTime($stepStart, 'createContract', 'completed');
                // --- prepare notification payload once ---
                $stepStart = microtime(true);
                $settings = Utility::settings($user?->creatorId());
                $client = User::findOrFail($c->{PJC::COL_CLIENT_NAME});
                $payload = [
                    'contract_subject' => $c->subject,
                    'contract_client' => $client->name,
                    'contract_value' => $user?->priceFormat($c->value),
                    'contract_start_date' => $user?->dateFormat($c->{PJC::COL_S_DT}),
                    'contract_end_date' => $user?->dateFormat($c->{PJC::COL_E_DT}),
                ];
                $this->logExecutionTime($stepStart, 'prepareNotification', 'completed');

                // email
                $stepStart = microtime(true);
                if (!empty($settings['new_contract'])) {
                    Utility::sendEmailTemplate(
                        'new_contract',
                        [$client->id => $client->email],
                        $payload
                    );
                }
                $this->logExecutionTime($stepStart, 'sendEmailNotification', 'completed');

                // Slack
                $stepStart = microtime(true);
                if (!empty($settings['contract_notification'])) {
                    Utility::sendSlackMsg('new_contract', $payload);
                }
                $this->logExecutionTime($stepStart, 'sendSlackNotification', 'completed');

                // Telegram
                $stepStart = microtime(true);
                if (!empty($settings['telegram_contract_notification'])) {
                    Utility::sendTelegramMsg('new_contract', $payload);
                }
                $this->logExecutionTime($stepStart, 'sendTelegramNotification', 'completed');

                return redirect()->route(self::ENTITY . '.index')
                    ->with('success', __('Contract successfully created.'));
            } catch (\Throwable $e) {
                Log::debug($method . ' - exception details', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, [UC::COL_USER_ID => auth()->id()]);
    }

    public function sendMailContract(Request $request, int|string $id): RedirectResponse
    {
        $function = __FUNCTION__;
        return $this->measureProfile($function, function () use ($request, $id, $function) {
            $method = static::class . '::' . $function;
            $startLogin = microtime(true);
            if (($userOrRedirect = static::_checkLogin()) instanceof RedirectResponse) {
                $this->logExecutionTime($startLogin, $function . '::login', 'failed');
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $this->logExecutionTime($startLogin, $function . '::login', 'completed');

            if (!$user?->can(PMC::MNG_CTC)) {
                Log::warning($method . ' permission denied', [UC::COL_USER_ID => $user?->id]);
                return defaultPermissionDenial($request, null, $method);
            }

            try {
                $startFetch = microtime(true);
                $c = Contract::findOrFail($id);
                $client = User::findOrFail($c->{PJC::COL_CLIENT_NAME});
                $this->logExecutionTime($startFetch, $function . '::fetchContractAndClient', 'completed');

                $settings = Utility::settings($user?->creatorId());
                if (!empty($settings['new_contract'])) {
                    $payload = [
                        'contract_subject'    => $c->subject,
                        'contract_client'     => $client->name,
                        'contract_start_date' => $c->{PJC::COL_S_DT},
                        'contract_end_date'   => $c->{PJC::COL_E_DT},
                    ];
                    $startEmail = microtime(true);
                    $resp = Utility::sendEmailTemplate(
                        'new_contract',
                        [$client->id => $client->email],
                        $payload
                    );
                    $this->logExecutionTime($startEmail, $function . '::sendEmail', 'completed');
                }

                return redirect()->route(static::ENTITY . '.show', $c->id)
                    ->with('success', __('Email sent.'));
            } catch (\Throwable $e) {
                $timeError = microtime(true);
                $this->logExecutionTime($timeError, $function . '::exception', 'failed');
                Log::error($method . ' failed', ['error' => $e->getMessage()]);
                Log::debug($method . ' debug exception', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $method);
            }
        }, func_get_args());
    }

    public function signature(Request $request, int|string $id): RedirectResponse|View
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $id, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), 'id' => $id]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can('show contract')) return defaultPermissionDenial($request, null, $action);
                $c = Contract::findOrFail($id);
                $this->logExecutionTime($stepStart, 'view contract signature', 'completed');
                return view(self::ENTITY . '.signature', compact('c'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), 'id' => $id]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'id' => $id]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function signatureStore(Request $request): JsonResponse
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), PJC::COL_CTC_ID => $request->{PJC::COL_CTC_ID}]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                    return response()->json(['error' => 'Permission denied'], 401);
                $user = $userOrRedirect;
                if (!$user?->can('edit contract'))
                    return response()->json(['error' => 'Permission denied'], 401);

                $c = Contract::findOrFail($request->{PJC::COL_CTC_ID});
                $field = $user->type == PMC::CPN ? 'company_signature' : 'client_signature';
                $c->$field = $request->$field;
                $c->save();

                $this->logExecutionTime($stepStart, 'contract signature update', 'completed');
                return response()->json([
                    'success' => true,
                    'message' => __('Contract signed.')
                ], 200);
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all()]);
                Log::error("$action failed", ['error' => $e->getMessage(), PJC::COL_CTC_ID => $request->{PJC::COL_CTC_ID}]);
                return defaultUndefinedException($request, $e, $action, '/', false, []);
            }
        });
    }

    public function pdfFromContract(Request $request, string $contractId): RedirectResponse|View
    {
        $class = static::class;
        $method = __FUNCTION__;
        $action = "{$class}::{$method}";
        return $this->measureProfile($action, function () use ($request, $contractId, $action) {
            Log::info("$action called", [UC::COL_USER_ID => auth()->id(), PJC::COL_CTC_ID => $contractId]);
            $stepStart = microtime(true);
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $user = $userOrRedirect;
                if (!$user?->can('show contract')) return defaultPermissionDenial($request, null, $action);

                $id = \Illuminate\Support\Facades\Crypt::decrypt($contractId);
                $c = Contract::findOrFail($id);

                $this->logExecutionTime($stepStart, 'view contract template', 'completed');
                return view(self::ENTITY . '.template', compact('c'));
            } catch (\Throwable $e) {
                Log::debug("$action exception trace", ['exception' => $e, 'request' => $request->all(), PJC::COL_CTC_ID => $contractId]);
                Log::error("$action failed", ['error' => $e->getMessage(), PJC::COL_CTC_ID => $contractId]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }
}
