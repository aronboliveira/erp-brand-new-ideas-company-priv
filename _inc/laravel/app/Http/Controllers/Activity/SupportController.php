<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    SupportsConstants,
    UsersConstants
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

class SupportController extends Controller
{
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
        $viewPath = self::ENTITY . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'owner_id' => $user?->creatorId(), 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $ownerId = $user?->creatorId();
                $query = Support::with([DatabaseConstants::TABLE_CREATOR, ProjectsConstants::COL_ASGN])->where(DatabaseConstants::TABLE_CREATOR, $ownerId);
                if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN) $query->where(SupportsConstants::COL_USR, $user?->id);
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $supports = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchSupports');
                $countStart = microtime(true);
                $countAll = $query->count();
                $countOpen = (clone $query)->where(ActivitiesConstants::COL_TSK_STT, 'open')->count();
                $countOnHold = (clone $query)->where(ActivitiesConstants::COL_TSK_STT, 'on hold')->count();
                $countClosed = (clone $query)->where(ActivitiesConstants::COL_TSK_STT, 'close')->count();
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
        $viewPath = self::ENTITY . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", ['user_id' => $user?->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'create support', self::INDEX_ROUTE)) return $denial;
            $listsStart = microtime(true);
            $priority = Support::priorityList();
            $status = Support::statusList();
            $this->logExecutionTime($listsStart, $action, 'loadLists');
            $usersStart = microtime(true);
            $users = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)->pluck(UsersConstants::COL_NM, 'id');
            $this->logExecutionTime($usersStart, $action, 'fetchUsers');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [ProjectsConstants::COL_PRT, ActivitiesConstants::COL_TSK_STT, DatabaseConstants::TABLE_USERS]]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(ProjectsConstants::COL_PRT, ActivitiesConstants::COL_TSK_STT, DatabaseConstants::TABLE_USERS));
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
        $viewPath = self::ENTITY . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $support, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $user?->id, 'support_id' => $support->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'view support', self::INDEX_ROUTE)) return $denial;
            try {
                $authStart = microtime(true);
                if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
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
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'create support', self::INDEX_ROUTE)) return $denial;
            $valStart = microtime(true);
            $v = Validator::make($req->all(), [SupportsConstants::COL_SBJ => 'required|string', ProjectsConstants::COL_PRT => 'required|in:0,1,2,3']);
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
                        SupportsConstants::COL_SBJ => $req->input(SupportsConstants::COL_SBJ),
                        ProjectsConstants::COL_PRT => $req->input(ProjectsConstants::COL_PRT),
                        ProjectsConstants::COL_E_DT => $req->input(ProjectsConstants::COL_E_DT),
                        SupportsConstants::COL_TKT_CD => now()->format('His'),
                        ActivitiesConstants::COL_TSK_STT => 'open',
                        DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                        SupportsConstants::COL_TKT_CR => $user?->id,
                        SupportsConstants::COL_USR => $user[UsersConstants::COL_TP] === PermissionsConstants::CL ? $user?->id : $req->input(SupportsConstants::COL_USR),
                        ActivitiesConstants::COL_DESC => $req->input(ActivitiesConstants::COL_DESC),
                    ]);
                    $this->logExecutionTime($buildStart, $action, 'buildModel');
                    if ($req->hasFile(SupportsConstants::COL_ATC)) {
                        $uStart = microtime(true);
                        $size = $req->file(SupportsConstants::COL_ATC)->getSize();
                        if (Utility::updateStorageLimit($user?->creatorId(), $size) !== 1) throw new \RuntimeException('Storage limit exceeded');
                        $name = time() . '_' . $req->file(SupportsConstants::COL_ATC)->getClientOriginalName();
                        $support[SupportsConstants::COL_ATC] = $name;
                        Utility::uploadFile($req, SupportsConstants::COL_ATC, $name, 'uploads/supports', []);
                        $this->logExecutionTime($uStart, $action, 'uploadAttachment');
                    }
                    $saveStart = microtime(true);
                    $support->save();
                    $this->logExecutionTime($saveStart, $action, 'saveSupport');
                    Log::info("[{$base}::{$action}] support created", ['support_id' => $support->id]);
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                $settingStart = microtime(true);
                $setting = Utility::settings($user?->creatorId());
                $prioLabel = Support::$priority[$support[ProjectsConstants::COL_PRT]] ?? $support[ProjectsConstants::COL_PRT];
                $targetUser = User::find($support[SupportsConstants::COL_USR]);
                $notifyPayload = ['support_priority' => $prioLabel, 'support_user_name' => $targetUser[UsersConstants::COL_NM]];
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
                    'support_name' => $targetUser[UsersConstants::COL_NM],
                    'support_title' => $support[SupportsConstants::COL_SBJ],
                    'support_priority' => $prioLabel,
                    'support_end_date' => $support[ProjectsConstants::COL_E_DT],
                    'support_description' => $support[ActivitiesConstants::COL_DESC],
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
        $viewPath = self::ENTITY . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $support, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, self::ENTITY => $support->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'edit support', self::INDEX_ROUTE)) return $denial;
            if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
            $listsStart = microtime(true);
            $priority = Support::priorityList();
            $status = Support::statusList();
            $this->logExecutionTime($listsStart, $action, 'loadLists');
            $usersStart = microtime(true);
            $users = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)->pluck(UsersConstants::COL_NM, 'id');
            $this->logExecutionTime($usersStart, $action, 'fetchUsers');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => [self::ENTITY, ProjectsConstants::COL_PRT, ActivitiesConstants::COL_TSK_STT, DatabaseConstants::TABLE_USERS]]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(self::ENTITY, ProjectsConstants::COL_PRT, ActivitiesConstants::COL_TSK_STT, DatabaseConstants::TABLE_USERS));
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
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, self::ENTITY => $support->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'edit support', self::INDEX_ROUTE)) return $denial;
            if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
            $valStart = microtime(true);
            $v = Validator::make($req->all(), [
                SupportsConstants::COL_SBJ => 'required|string',
                ProjectsConstants::COL_PRT => 'required|in:0,1,2,3',
                ActivitiesConstants::COL_TSK_STT => 'required|in:open,on hold,close',
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
                        SupportsConstants::COL_SBJ,
                        ProjectsConstants::COL_PRT,
                        ActivitiesConstants::COL_TSK_STT,
                        ProjectsConstants::COL_E_DT,
                        ActivitiesConstants::COL_DESC,
                        SupportsConstants::COL_USR,
                    ]));
                    $this->logExecutionTime($fillStart, $action, 'fillModel');
                    if ($req->hasFile(SupportsConstants::COL_ATC)) {
                        $uStart = microtime(true);
                        $size = $req->file(SupportsConstants::COL_ATC)->getSize();
                        if (Utility::updateStorageLimit($support->creatorId(), $size) !== 1) throw new \RuntimeException('Storage limit exceeded');
                        $name = time() . '_' . $req->file(SupportsConstants::COL_ATC)->getClientOriginalName();
                        $support->attachment = $name;
                        Utility::uploadFile($req, SupportsConstants::COL_ATC, $name, 'uploads/supports', []);
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
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, self::ENTITY => $support->id, 'method' => $method]);
            if ($denial = $this->guard($req, 'delete support', self::INDEX_ROUTE)) return $denial;
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($support, $user, $action, $base) {
                    if ($support[SupportsConstants::COL_ATC]) {
                        $attStart = microtime(true);
                        Utility::changeStorageLimit($user?->creatorId(), '/uploads/supports/' . $support[SupportsConstants::COL_ATC]);
                        File::delete(storage_path('uploads/supports/' . $support[SupportsConstants::COL_ATC]));
                        $this->logExecutionTime($attStart, $action, 'deleteAttachment');
                        Log::info("[{$base}::{$action}] attachment deleted", ['file' => $support[SupportsConstants::COL_ATC]]);
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
        $viewPath = self::ENTITY . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $encryptedId, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, 'encrypted_id' => $encryptedId, 'method' => $method]);
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
                $support = Support::with([ProjectsConstants::COL_ASGN, DatabaseConstants::TABLE_CREATOR])->findOrFail($id);
                $this->logExecutionTime($fetchStart, $action, 'fetchSupport');
                if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) return defaultPermissionDenial($req, new \Exception('owner'), $class . '::' . $action, route(self::INDEX_ROUTE), false);
                $repliesStart = microtime(true);
                $replies = SupportReply::where('support_id', $id)->with(DatabaseConstants::TABLE_USERS)->get();
                $this->logExecutionTime($repliesStart, $action, 'fetchReplies');
                $txnStart = microtime(true);
                DB::transaction(function () use ($replies, $action) {
                    $loopStart = microtime(true);
                    foreach ($replies as $r) $r->markAsRead();
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
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, self::ENTITY => $id, 'method' => $method]);
            if ($denial = $this->guard($req, 'reply support', self::INDEX_ROUTE)) return $denial;
            $valStart = microtime(true);
            $v = Validator::make($req->all(), [ActivitiesConstants::COL_DESC => 'required|string']);
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
                        SupportsConstants::COL_USR => $user?->id,
                        ActivitiesConstants::COL_DESC => $req[ActivitiesConstants::COL_DESC],
                        DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
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
        $viewPath = self::ENTITY . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($user = $this->requireLogin($req)) instanceof RedirectResponse) return $user;
            Log::info("[{$base}::{$action}] start", [SupportsConstants::COL_USR => $user?->id, 'method' => $method]);
            try {
                $ownerId = $user?->creatorId();
                $buildStart = microtime(true);
                $query = Support::with([ProjectsConstants::COL_ASGN, DatabaseConstants::TABLE_CREATOR])->where(DatabaseConstants::TABLE_CREATOR, $ownerId);
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::CL || $user[UsersConstants::COL_TP] === 'Employee') $query->where(function ($q) use ($user) {
                    $q->where(SupportsConstants::COL_USR, $user?->id)->orWhere(SupportsConstants::COL_TKT_CR, $user?->id);
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
            : self::_checkLogin();
    }
}
