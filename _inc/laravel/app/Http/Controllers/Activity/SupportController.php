<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    SupportsConstants as SC,
    UsersConstants as UC
};
use App\Models\{Support, SupportReply, User, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{
    Crypt,
    DB,
    File,
    Log,
    Route,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class SupportController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'support';
    private const INDEX_ROUTE = self::ENTITY . '.index';

    public function index(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = self::ENTITY . 's.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'owner_id' => $user?->creatorId(), 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $ownerId = $user?->creatorId();
                $query = Support::with([DC::COL_TABLE_CREATOR, PJC::COL_ASGN])->where(DC::COL_TABLE_CREATOR, $ownerId);
                if ($user[UC::COL_TP] !== PMC::CPN) $query->where(SC::COL_USR, $user?->id);
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $supports = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchSupports');
                $countStart = microtime(true);
                $countAll = $query->count();
                $countOpen = (clone $query)->where(AC::COL_TSK_STT, 'open')->count();
                $countOnHold = (clone $query)->where(AC::COL_TSK_STT, 'on hold')->count();
                $countClosed = (clone $query)->where(AC::COL_TSK_STT, 'close')->count();
                $this->logExecutionTime($countStart, $action, 'countStatuses');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['supports', 'countAll', 'countOpen', 'countOnHold', 'countClosed']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('supports', 'countAll', 'countOpen', 'countOnHold', 'countClosed'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                Log::info("[{$base}::{$action}] complete", ['count_all' => $countAll, 'open' => $countOpen, 'on_hold' => $countOnHold, 'closed' => $countClosed, 'view_path' => $viewPath]);
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = self::ENTITY . 's.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'method' => $method]);
            if (($denial = $this->guard($req, 'create support', self::INDEX_ROUTE)) !== true) return $denial;
            $listsStart = microtime(true);
            $prioListAvailable = is_callable([Support::class, 'priorityList']);
            $priority = $prioListAvailable ? Support::priorityList() : [];
            if (!$prioListAvailable) Log::warning("[{$base}::{$action}] priority list method missing", []);
            $statusListAvailable = is_callable([Support::class, 'statusList']);
            $status = $statusListAvailable ? Support::statusList() : [];
            if (!$statusListAvailable) Log::warning("[{$base}::{$action}] status list method missing", []);
            $this->logExecutionTime($listsStart, $action, 'loadLists');
            $usersStart = microtime(true);
            $users = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, '!=', PMC::CL)->pluck(UC::COL_NM, 'id');
            $this->logExecutionTime($usersStart, $action, 'fetchUsers');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [PJC::COL_PRT, AC::COL_TSK_STT, DC::TABLE_USERS]]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(PJC::COL_PRT, AC::COL_TSK_STT, DC::TABLE_USERS));
            $this->logExecutionTime($renderStart, $action, 'renderCreate');
            Log::info("[{$base}::{$action}] complete", ['view_path' => $viewPath]);
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, Support $support): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = self::ENTITY . 's.' . $action;
        return $this->measureProfile($action, function () use ($req, $support, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [UC::COL_USER_ID => $user?->id, 'support_id' => $support->id, 'method' => $method]);
            if (($denial = $this->guard($req, 'view support', self::INDEX_ROUTE)) !== true) return $denial;
            try {
                $authStart = microtime(true);
                if ($support[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$base}::{$action}] authorized", ['support_id' => $support->id]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [self::ENTITY]]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact(self::ENTITY));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                Log::info("[{$base}::{$action}] complete", ['view_path' => $viewPath]);
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'support_id' => $support->id]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'support_id' => $support->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, 'method' => $method]);
            if (($denial = $this->guard($req, 'create support', self::INDEX_ROUTE)) !== true) return $denial;
            $valStart = microtime(true);
            $v = Validator::make($req->all(), [SC::COL_SBJ => 'required|string', PJC::COL_PRT => 'required|in:0,1,2,3']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                $support = null;
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $user, &$support, $action, $base) {
                    $buildStart = microtime(true);
                    $support = new Support([
                        SC::COL_SBJ => $req->input(SC::COL_SBJ),
                        PJC::COL_PRT => $req->input(PJC::COL_PRT),
                        PJC::COL_E_DT => $req->input(PJC::COL_E_DT),
                        SC::COL_TKT_CD => now()->format('His'),
                        AC::COL_TSK_STT => 'open',
                        DC::COL_TABLE_CREATOR => $user?->creatorId(),
                        SC::COL_TKT_CR => $user?->id,
                        SC::COL_USR => $user[UC::COL_TP] === PMC::CL ? $user?->id : $req->input(SC::COL_USR),
                        AC::COL_DESC => $req->input(AC::COL_DESC),
                    ]);
                    $this->logExecutionTime($buildStart, $action, 'buildModel');
                    if ($req->hasFile(SC::COL_ATC)) {
                        $uStart = microtime(true);
                        $size = $req->file(SC::COL_ATC)->getSize();
                        if (Utility::updateStorageLimit($user?->creatorId(), $size) !== 1) throw new \RuntimeException('Storage limit exceeded');
                        $name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $req->file(SC::COL_ATC)->getClientOriginalName());
                        $support[SC::COL_ATC] = $name;
                        Utility::uploadFile($req, SC::COL_ATC, $name, 'uploads/supports', []);
                        $this->logExecutionTime($uStart, $action, 'uploadAttachment');
                    }
                    $saveStart = microtime(true);
                    $support->save();
                    $this->logExecutionTime($saveStart, $action, 'saveSupport');
                    Log::info("[{$base}::{$action}] support created", ['support_id' => $support->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                $settingStart = microtime(true);
                $setting = Utility::settingsById($user?->creatorId());
                $prioLabel = Support::$priority?->{$support[PJC::COL_PRT]} ?? $support[PJC::COL_PRT];
                $targetUser = User::find($support[SC::COL_USR]);
                $notifyPayload = ['support_priority' => $prioLabel, 'support_user_name' => $targetUser[UC::COL_NM]];
                $this->logExecutionTime($settingStart, $action, 'prepareNotifications');
                if (!$support) {
                    Log::warning("[{$base}::{$action}] support not found");
                    return;
                }
                if (!empty($setting['support_notification'])) {
                    $slStart = microtime(true);
                    Utility::send_slack_msg('new_support_ticket', $notifyPayload);
                    $this->logExecutionTime($slStart, $action, 'sendSlack');
                    Log::info("[{$base}::{$action}] slack notification sent", ['support_id' => $support->id]);
                }
                if (!empty($setting['telegram_support_notification'])) {
                    $tgStart = microtime(true);
                    Utility::send_telegram_msg('new_support_ticket', $notifyPayload);
                    $this->logExecutionTime($tgStart, $action, 'sendTelegram');
                    Log::info("[{$base}::{$action}] telegram notification sent", ['support_id' => $support->id]);
                }
                $mailStart = microtime(true);
                $emailResp = Utility::sendEmailTemplate('new_support_ticket', [$targetUser->id => $targetUser->email], [
                    'support_name' => $targetUser[UC::COL_NM],
                    'support_title' => $support[SC::COL_SBJ],
                    'support_priority' => $prioLabel,
                    'support_end_date' => $support[PJC::COL_E_DT],
                    'support_description' => $support[AC::COL_DESC],
                ]);
                $this->logExecutionTime($mailStart, $action, 'sendEmail');
                if (!$emailResp['is_success']) Log::warning("[{$base}::{$action}] email notification failed", ['error' => $emailResp['error']]);
                $whStart = microtime(true);
                $webhook = Utility::webhookSetting('New Support Ticket');
                $this->logExecutionTime($whStart, $action, 'loadWebhook');
                if ($webhook) {
                    $callStart = microtime(true);
                    $ok = Utility::WebhookCall($webhook['url'], $support->toJson(), $webhook['method']);
                    $this->logExecutionTime($callStart, $action, 'callWebhook');
                    if (!$ok) {
                        Log::warning("[{$base}::{$action}] webhook call failed", ['support_id' => $support->id]);
                        return redirect()->route(self::INDEX_ROUTE)->with('success', __('Support successfully added.'))->with('error', __('Webhook call failed.'));
                    }
                    Log::info("[{$base}::{$action}] webhook call succeeded", ['support_id' => $support->id]);
                }
                return redirect()->route(self::INDEX_ROUTE)->with('success', __('Support successfully added.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] store failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $request, Support $support)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = self::ENTITY . 's.' . $action;
        return $this->measureProfile($action, function () use ($req, $support, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, self::ENTITY => $support->id, 'method' => $method]);
            if (($denial = $this->guard($req, 'edit support', self::INDEX_ROUTE)) !== true) return $denial;
            if ($support[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
            $listsStart = microtime(true);
            $prioListAvailable = is_callable([Support::class, 'priorityList']);
            $priority = $prioListAvailable ? Support::priorityList() : [];
            if (!$prioListAvailable) Log::warning("[{$base}::{$action}] priority list method missing", []);
            $statusListAvailable = is_callable([Support::class, 'statusList']);
            $status = $statusListAvailable ? Support::statusList() : [];
            if (!$statusListAvailable) Log::warning("[{$base}::{$action}] status list method missing", []);
            $this->logExecutionTime($listsStart, $action, 'loadLists');
            $usersStart = microtime(true);
            $users = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, '!=', PMC::CL)->pluck(UC::COL_NM, 'id');
            $this->logExecutionTime($usersStart, $action, 'fetchUsers');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [self::ENTITY, PJC::COL_PRT, AC::COL_TSK_STT, DC::TABLE_USERS]]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(self::ENTITY, PJC::COL_PRT, AC::COL_TSK_STT, DC::TABLE_USERS));
            $this->logExecutionTime($renderStart, $action, 'renderEdit');
            Log::info("[{$base}::{$action}] complete", ['view_path' => $viewPath, 'support_id' => $support->id]);
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'support_id' => $support->id]);
    }

    public function update(Request $request, Support $support): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $support, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, self::ENTITY => $support->id, 'method' => $method]);
            if (($denial = $this->guard($req, 'edit support', self::INDEX_ROUTE)) !== true) return $denial;
            if ($support[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
            $valStart = microtime(true);
            $v = Validator::make($req->all(), [
                SC::COL_SBJ => 'required|string',
                PJC::COL_PRT => 'required|in:0,1,2,3',
                AC::COL_TSK_STT => 'required|in:open,on hold,close',
            ]);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all()), 'support_id' => $support->id]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $support, $action, $base) {
                    $fillStart = microtime(true);
                    $support->fill($req->only([
                        SC::COL_SBJ,
                        PJC::COL_PRT,
                        AC::COL_TSK_STT,
                        PJC::COL_E_DT,
                        AC::COL_DESC,
                        SC::COL_USR,
                    ]));
                    $this->logExecutionTime($fillStart, $action, 'fillModel');
                    if ($req->hasFile(SC::COL_ATC)) {
                        $uStart = microtime(true);
                        $size = $req->file(SC::COL_ATC)->getSize();
                        if (Utility::updateStorageLimit($support->creatorId(), $size) !== 1) throw new \RuntimeException('Storage limit exceeded');
                        $name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $req->file(SC::COL_ATC)->getClientOriginalName());
                        $support->attachment = $name;
                        Utility::uploadFile($req, SC::COL_ATC, $name, 'uploads/supports', []);
                        $this->logExecutionTime($uStart, $action, 'uploadAttachment');
                    }
                    $saveStart = microtime(true);
                    $support->save();
                    $this->logExecutionTime($saveStart, $action, 'saveSupport');
                    Log::info("[{$base}::{$action}] support updated", ['support_id' => $support->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::INDEX_ROUTE)->with('success', __('Support successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] update failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'support_id' => $support->id, 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'support_id' => $support->id]);
    }

    public function destroy(Request $request, Support $support): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $support, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, self::ENTITY => $support->id, 'method' => $method]);
            if (($denial = $this->guard($req, 'delete support', self::INDEX_ROUTE)) !== true) return $denial;
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($support, $user, $action, $base) {
                    if ($support[SC::COL_ATC]) {
                        $attStart = microtime(true);
                        Utility::changeStorageLimit($user?->creatorId(), '/uploads/supports/' . $support[SC::COL_ATC]);
                        File::delete(storage_path('uploads/supports/' . $support[SC::COL_ATC]));
                        $this->logExecutionTime($attStart, $action, 'deleteAttachment');
                        Log::info("[{$base}::{$action}] attachment deleted", ['file' => $support[SC::COL_ATC]]);
                    }
                    $repStart = microtime(true);
                    $support->replies()->delete();
                    $this->logExecutionTime($repStart, $action, 'deleteReplies');
                    $delStart = microtime(true);
                    $support->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteSupport');
                    Log::info("[{$base}::{$action}] support deleted", ['support_id' => $support->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->route(self::INDEX_ROUTE)->with('success', __('Support successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] destroy failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'support_id' => $support->id]);
                return defaultUndefinedException($req, $e, $class . '::destroy');
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'support_id' => $support->id]);
    }

    public function reply(Request $request, string $encryptedId)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = self::ENTITY . 's.' . $action;
        return $this->measureProfile($action, function () use ($req, $encryptedId, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, 'encrypted_id' => $encryptedId, 'method' => $method]);
            $decStart = microtime(true);
            try {
                $id = Crypt::decrypt($encryptedId);
                $this->logExecutionTime($decStart, $action, 'decryptId');
            } catch (\Throwable $e) {
                Log::warning("[{$base}::{$action}] invalid support id", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] decrypt context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'encrypted_id' => $encryptedId]);
                return redirect()->back()->with('error', __('Support Not Found.'));
            }
            try {
                $fetchStart = microtime(true);
                $support = Support::with([PJC::COL_ASGN, DC::COL_TABLE_CREATOR])->findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchSupport');
                if ($support[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
                $repliesStart = microtime(true);
                $replies = SupportReply::where('support_id', $id)->with(DC::TABLE_USERS)->get();
                $this->logExecutionTime($repliesStart, $action, 'fetchReplies');
                $txnStart = microtime(true);
                DB::transaction(function () use ($replies, $action) {
                    $loopStart = microtime(true);
                    foreach ($replies as $r) $r->markRead();
                    $this->logExecutionTime($loopStart, $action, 'markRepliesRead');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] replies fetched", ['count' => $replies->count(), 'support_id' => $support->id]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [self::ENTITY, 'replies']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact(self::ENTITY, 'replies'));
                $this->logExecutionTime($renderStart, $action, 'renderReply');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] reply failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), 'support_id' => isset($support) ? $support->id : null]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'encrypted_id' => $encryptedId]);
    }

    public function replyAnswer(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, self::ENTITY => $id, 'method' => $method]);
            if (($denial = $this->guard($req, 'reply support', self::INDEX_ROUTE)) !== true) return $denial;
            $valStart = microtime(true);
            $v = Validator::make($req->all(), [AC::COL_DESC => 'required|string']);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($v->fails()) {
                Log::warning("[{$base}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all()), self::ENTITY => $id]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $id, $user, $action, $base) {
                    $createStart = microtime(true);
                    SupportReply::create([
                        'support_id' => $id,
                        SC::COL_USR => $user?->id,
                        AC::COL_DESC => $req[AC::COL_DESC],
                        DC::COL_TABLE_CREATOR => $user?->creatorId(),
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createReply');
                    Log::info("[{$base}::{$action}] reply saved", [self::ENTITY => $id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                return redirect()->back()->with('success', __('Support reply successfully sent.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] reply save failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName(), self::ENTITY => $id, 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, self::ENTITY => $id]);
    }

    public function grid(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = self::ENTITY . 's.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SC::COL_USR => $user?->id, 'method' => $method]);
            try {
                $ownerId = $user?->creatorId();
                $buildStart = microtime(true);
                $query = Support::with([PJC::COL_ASGN, DC::COL_TABLE_CREATOR])->where(DC::COL_TABLE_CREATOR, $ownerId);
                if ($user[UC::COL_TP] === PMC::CL || strtolower($user[UC::COL_TP]) === 'employee') $query->where(function ($q) use ($user) {
                    $q->where(SC::COL_USR, $user?->id)->orWhere(SC::COL_TKT_CR, $user?->id);
                });
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $supports = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchSupports');
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['supports']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('supports'));
                $this->logExecutionTime($renderStart, $action, 'renderGrid');
                Log::info("[{$base}::{$action}] complete", ['count' => $supports->count(), 'view_path' => $viewPath]);
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    private function requireLogin(Request $r)
    {
        return self::_checkLogin() instanceof RedirectResponse
            ? self::_checkLogin()
            : $r->user();
    }
}
