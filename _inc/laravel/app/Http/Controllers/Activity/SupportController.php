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
    Validator
};

class SupportController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ENTITY = 'support';
    private const INDEX_ROUTE = self::ENTITY . '.index';

    public function index(Request $request)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id]);
        // show tickets scoped by user type
        $ownerId = $user?->creatorId();
        $base   = Support::with([DatabaseConstants::TABLE_CREATOR, ProjectsConstants::COL_ASGN])
            ->where(DatabaseConstants::TABLE_CREATOR, $ownerId);
        if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN)
            $base->where(SupportsConstants::COL_USR, $user?->id);
        $supports       = $base->get();
        $countAll       = $base->count();
        $countOpen      = (clone $base)->where(ActivitiesConstants::COL_TSK_STT, 'open')->count();
        $countOnHold    = (clone $base)->where(ActivitiesConstants::COL_TSK_STT, 'on hold')->count();
        $countClosed    = (clone $base)->where(ActivitiesConstants::COL_TSK_STT, 'close')->count();
        return view(self::ENTITY . '.' . __FUNCTION__, compact(
            'supports',
            'countAll',
            'countOpen',
            'countOnHold',
            'countClosed'
        ));
    }

    public function create(Request $request)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id]);
        if ($denial = $this->guard($request, 'create support', self::INDEX_ROUTE))
            return $denial;
        $priority = Support::priorityList();
        $status  = Support::statusList();
        $users   = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
            ->pluck(UsersConstants::COL_NM, 'id');
        return view(self::ENTITY . '.' . __FUNCTION__, compact(
            ProjectsConstants::COL_PRT,
            ActivitiesConstants::COL_TSK_STT,
            DatabaseConstants::TABLE_USERS
        ));
    }

    public function show(Request $request, Support $support): \Illuminate\View\View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = $this->requireLogin($request))
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' start', [
            UsersConstants::COL_USER_ID    => $user?->id,
            'support_id' => $support->id,
        ]);
        if (
            $denial = $this->guard(
                $request,
                'view support',
                self::INDEX_ROUTE
            )
        ) return $denial;
        try {
            if (
                $support[DatabaseConstants::TABLE_CREATOR]
                !== $user?->creatorId()
            ) return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::INDEX_ROUTE),
                false
            );
            Log::info(__METHOD__ . ' authorized', [
                'support_id' => $support->id,
            ]);
            return view(self::ENTITY . '.' . __FUNCTION__, compact(self::ENTITY));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                'error' => $e->getMessage(),
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id, 'input' => $request->all()]);
        if ($denial = $this->guard($request, 'create support', self::INDEX_ROUTE))
            return $denial;
        $v = Validator::make($request->all(), [
            SupportsConstants::COL_SBJ  => 'required|string',
            ProjectsConstants::COL_PRT => 'required|in:0,1,2,3',
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in store', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }

        try {
            DB::transaction(function () use ($request, $user, &$support) {
                $support = new Support([
                    SupportsConstants::COL_SBJ      => $request->input(SupportsConstants::COL_SBJ),
                    ProjectsConstants::COL_PRT      => $request->input(ProjectsConstants::COL_PRT),
                    ProjectsConstants::COL_E_DT     => $request->input(ProjectsConstants::COL_E_DT),
                    SupportsConstants::COL_TKT_CD   => now()->format('His'),
                    ActivitiesConstants::COL_TSK_STT => 'open',
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                    SupportsConstants::COL_TKT_CR   => $user?->id,
                    SupportsConstants::COL_USR      => $user[UsersConstants::COL_TP] === PermissionsConstants::CL
                        ? $user?->id
                        : $request->input(SupportsConstants::COL_USR),
                    ActivitiesConstants::COL_DESC   => $request->input(ActivitiesConstants::COL_DESC),
                ]);
                if ($request->hasFile(SupportsConstants::COL_ATC)) {
                    $size = $request->file(SupportsConstants::COL_ATC)->getSize();
                    if (Utility::updateStorageLimit($user?->creatorId(), $size) !== 1)
                        throw new \RuntimeException('Storage limit exceeded');
                    $name               = time() . '_' . $request->file(SupportsConstants::COL_ATC)->getClientOriginalName();
                    $support[SupportsConstants::COL_ATC] = $name;
                    Utility::uploadFile($request, SupportsConstants::COL_ATC, $name, 'uploads/supports', []);
                }
                $support->save();
                Log::info('Support ticket created', ['id' => $support->id]);
            });
            $setting     = Utility::settings($user?->creatorId());
            $prioLabel   = Support::$priority[$support[ProjectsConstants::COL_PRT]] ?? $support[ProjectsConstants::COL_PRT];
            $targetUser  = User::find($support[SupportsConstants::COL_USR]);
            $notifyPayload = [
                'support_priority'  => $prioLabel,
                'support_user_name' => $targetUser[UsersConstants::COL_NM],
            ];
            if (!empty($setting['support_notification'])) {
                Utility::send_slack_msg('new_support_ticket', $notifyPayload);
                Log::info('Slack notification sent', ['ticket' => $support->id]);
            }
            if (!empty($setting['telegram_support_notification'])) {
                Utility::send_telegram_msg('new_support_ticket', $notifyPayload);
                Log::info('Telegram notification sent', ['ticket' => $support->id]);
            }
            $emailResp = Utility::sendEmailTemplate(
                'new_support_ticket',
                [$targetUser->id => $targetUser->email],
                [
                    'support_name'        => $targetUser[UsersConstants::COL_NM],
                    'support_title'       => $support[SupportsConstants::COL_SBJ],
                    'support_priority'    => $prioLabel,
                    'support_end_date'    => $support[ProjectsConstants::COL_E_DT],
                    'support_description' => $support[ActivitiesConstants::COL_DESC],
                ]
            );
            if (!$emailResp['is_success'])
                Log::warning('Email notification failed', ['error' => $emailResp['error']]);
            $webhook = Utility::webhookSetting('New Support Ticket');
            if ($webhook) {
                $ok = Utility::WebhookCall($webhook['url'], $support->toJson(), $webhook['method']);
                if (!$ok) {
                    Log::warning('Webhook call failed', ['ticket' => $support->id]);
                    return redirect()->route(self::INDEX_ROUTE)
                        ->with('success', __('Support successfully added.'))
                        ->with('error', __('Webhook call failed.'));
                }
                Log::info('Webhook call succeeded', ['ticket' => $support->id]);
            }
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Support successfully added.'));
        } catch (\Throwable $e) {
            Log::error('Support store failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $request, Support $support)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id, self::ENTITY => $support->id]);
        if ($denial = $this->guard($request, 'edit support', self::INDEX_ROUTE))
            return $denial;
        if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::INDEX_ROUTE),
                false
            );
        $priority = Support::priorityList();
        $status  = Support::statusList();
        $users   = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
            ->pluck(UsersConstants::COL_NM, 'id');
        return view(self::ENTITY . '.' . __FUNCTION__, compact(
            self::ENTITY,
            ProjectsConstants::COL_PRT,
            ActivitiesConstants::COL_TSK_STT,
            DatabaseConstants::TABLE_USERS
        ));
    }

    public function update(Request $request, Support $support): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [
            SupportsConstants::COL_USR => $user?->id, self::ENTITY => $support->id,
            'input' => $request->all()
        ]);
        if ($denial = $this->guard($request, 'edit support', self::INDEX_ROUTE))
            return $denial;
        if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::INDEX_ROUTE),
                false
            );
        $v = Validator::make($request->all(), [
            SupportsConstants::COL_SBJ  => 'required|string',
            ProjectsConstants::COL_PRT => 'required|in:0,1,2,3',
            ActivitiesConstants::COL_TSK_STT   => 'required|in:open,on hold,close',
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in update', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }
        try {
            DB::transaction(function () use ($request, $support) {
                $support->fill($request->only([
                    SupportsConstants::COL_SBJ,
                    ProjectsConstants::COL_PRT,
                    ActivitiesConstants::COL_TSK_STT,
                    ProjectsConstants::COL_E_DT,
                    ActivitiesConstants::COL_DESC,
                    SupportsConstants::COL_USR,
                ]));
                if ($request->hasFile(SupportsConstants::COL_ATC)) {
                    $size = $request->file(SupportsConstants::COL_ATC)->getSize();
                    if (Utility::updateStorageLimit($support->creatorId(), $size) !== 1)
                        throw new \RuntimeException('Storage limit exceeded');
                    $name               = time() . '_' . $request->file(SupportsConstants::COL_ATC)->getClientOriginalName();
                    $support->attachment = $name;
                    Utility::uploadFile($request, SupportsConstants::COL_ATC, $name, 'uploads/supports', []);
                }
                $support->save();
                Log::info('Support ticket updated', ['id' => $support->id]);
            });
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Support successfully updated.'));
        } catch (\Throwable $e) {
            Log::error('Support update failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, Support $support): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id, self::ENTITY => $support->id]);
        if ($denial = $this->guard($request, 'delete support', self::INDEX_ROUTE))
            return $denial;
        try {
            DB::transaction(function () use ($support, $user) {
                if ($support[SupportsConstants::COL_ATC]) {
                    Utility::changeStorageLimit($user?->creatorId(), '/uploads/supports/' . $support[SupportsConstants::COL_ATC]);
                    File::delete(storage_path('uploads/supports/' . $support[SupportsConstants::COL_ATC]));
                    Log::info('Attachment deleted', ['file' => $support[SupportsConstants::COL_ATC]]);
                }
                $support->replies()->delete();
                $support->delete();
                Log::info('Support ticket deleted', ['id' => $support->id]);
            });
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Support successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error('Support destroy failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::destroy');
        }
    }

    public function reply(Request $request, string $encryptedId)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id, 'encrypted' => $encryptedId]);
        try {
            $id = Crypt::decrypt($encryptedId);
        } catch (\Throwable $e) {
            Log::warning('Invalid support ID in reply', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Support Not Found.'));
        }
        $support = Support::with([ProjectsConstants::COL_ASGN, DatabaseConstants::TABLE_CREATOR])->findOrFail($id);
        if ($support[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::INDEX_ROUTE),
                false
            );
        $replies = SupportReply::where('support_id', $id)
            ->with(DatabaseConstants::TABLE_USERS)->get();
        DB::transaction(function () use ($replies) {
            foreach ($replies as $r) {
                $r->markAsRead();
            }
        });
        Log::info('Fetched replies', ['count' => $replies->count()]);

        return view(self::ENTITY . '.' . __FUNCTION__, compact(self::ENTITY, 'replies'));
    }

    public function replyAnswer(Request $request, int $id): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [
            SupportsConstants::COL_USR => $user?->id, self::ENTITY => $id,
            'input' => $request->all()
        ]);
        if ($denial = $this->guard($request, 'reply support', self::INDEX_ROUTE))
            return $denial;

        $v = Validator::make($request->all(), [
            ActivitiesConstants::COL_DESC => 'required|string',
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in replyAnswer', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }
        try {
            DB::transaction(function () use ($request, $id, $user) {
                SupportReply::create([
                    'support_id' => $id,
                    SupportsConstants::COL_USR       => $user?->id,
                    ActivitiesConstants::COL_DESC => $request[ActivitiesConstants::COL_DESC],
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                ]);
                Log::info('Reply saved', [self::ENTITY => $id]);
            });
            return redirect()->back()->with('success', __('Support reply successfully sent.'));
        } catch (\Throwable $e) {
            Log::error('Reply save failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::replyAnswer');
        }
    }

    public function grid(Request $request)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, [SupportsConstants::COL_USR => $user?->id]);
        $ownerId = $user?->creatorId();
        $query  = Support::with([ProjectsConstants::COL_ASGN, DatabaseConstants::TABLE_CREATOR])
            ->where(DatabaseConstants::TABLE_CREATOR, $ownerId);
        if ($user[UsersConstants::COL_TP] === PermissionsConstants::CL || $user[UsersConstants::COL_TP] === 'Employee') {
            $query->where(function ($q) use ($user) {
                $q->where(SupportsConstants::COL_USR, $user?->id)
                    ->orWhere(SupportsConstants::COL_TKT_CR, $user?->id);
            });
        }
        $supports = $query->get();
        return view(self::ENTITY . '.' . __FUNCTION__, compact('supports'));
    }

    private function requireLogin(Request $r)
    {
        return self::_checkLogin() instanceof RedirectResponse
            ? self::_checkLogin()
            : self::_checkLogin();
    }
}
