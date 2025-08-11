<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Mail\SendLeadEmail;
use App\Models\{
    ClientDeal,
    Deal,
    DealCall,
    DealDiscussion,
    DealEmail,
    DealFile,
    Label,
    Lead,
    LeadActivityLog,
    LeadCall,
    LeadDiscussion,
    LeadEmail,
    LeadFile,
    LeadStage,
    Pipeline,
    ProductService,
    Role,
    Source,
    Stage,
    User,
    UserDeal,
    UserLead,
    Utility,
    WebhookSetting
};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    File,
    Hash,
    Mail,
    Response as ResponseFacade
};
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    Response
};

class LeadController extends Controller
{
    /** @var array{lead: ?\App\Models\Lead, id: int|null} */
    private static array $leadData = ['lead' => null, 'id' => null];

    public function index(Request $request): \Illuminate\View\View|RedirectResponse
    {
        try {
            self::_authorize($request, PermissionsConstants::MNG_LD);
            $user     = $request->user();
            $creatorId = $user?->creatorId();
            $pipeline = $user[UsersConstants::COL_DPL]
                ? Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('id', $user[UsersConstants::COL_DPL])
                ->first()
                ?? Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->first()
                : Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->first();
            $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(ProjectsConstants::COL_PPL_NM, 'id');
            return view(ViewsConstants::LD . '.' . __FUNCTION__, compact('pipelines', 'pipeline'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function leadList(Request $request): \Illuminate\View\View|RedirectResponse
    {
        try {
            self::_authorize($request, PermissionsConstants::MNG_LD);
            $user     = $request->user();
            $creatorId = $user?->creatorId();
            $pipeline = $user[UsersConstants::COL_DPL]
                ? Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('id', $user[UsersConstants::COL_DPL])
                ->first()
                ?? Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->first()
                : Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->first();
            $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(ProjectsConstants::COL_PPL_NM, 'id');
            $leads    = Lead::select(ViewsConstants::LD . '.*')
                ->join('user_leads', 'user_leads.lead_id', '=', ViewsConstants::LD . '.id')
                ->where('user_leads.' . UsersConstants::COL_USER_ID, $user?->id)
                ->where(ViewsConstants::LD . '.' . ProjectsConstants::COL_PPL_ID, $pipeline->id)
                ->orderBy(ViewsConstants::LD . '.' . ActivitiesConstants::COL_OD)
                ->get();
            return view(ViewsConstants::LD . '.list', compact('pipelines', 'pipeline', 'leads'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): \Illuminate\View\View|JsonResponse
    {
        try {
            self::_authorize($request, 'create lead');
            $creatorId = $request->user()->creatorId();
            $users = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->whereNotIn(UsersConstants::COL_TP, [
                    PermissionsConstants::CL,
                    PermissionsConstants::CPN
                ])
                ->where('id', '<>', $request->user()->id)
                ->pluck(UsersConstants::COL_NM, 'id')
                ->prepend(__('Select User'), '');
            return view(ViewsConstants::LD . '.' . __FUNCTION__, compact('users'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            self::_authorize($request, 'create lead');
            $data = $request->validate([
                'subject' => 'required',
                'name'    => 'required',
                'email'   => 'required|unique:leads,email',
            ]);
            $user     = $request->user();
            $creatorId = $user?->creatorId();
            $pipeline = $user[UsersConstants::COL_DPL]
                ? Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('id', $user[UsersConstants::COL_DPL])
                ->first()
                ?? Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->first()
                : Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->first();
            $stage = LeadStage::where(ProjectsConstants::COL_PPL_ID, $pipeline->id)->first();
            if (!$stage) {
                return redirect()->back()
                    ->with('error', __('Please Create Stage for This Pipeline.'));
            }
            $lead = Lead::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'phone'       => $request->input('phone'),
                'subject'     => $data['subject'],
                UsersConstants::COL_USER_ID     => $request->input(UsersConstants::COL_USER_ID),
                ProjectsConstants::COL_PPL_ID => $pipeline->id,
                'stage_id'    => $stage->id,
                DatabaseConstants::TABLE_CREATOR  => $creatorId,
                'date'        => now()->toDateString(),
            ]);
            $userIds = array_unique(array_filter([
                $user?->id,
                $request->input(UsersConstants::COL_USER_ID) !== $user?->id ?
                    $request->input(UsersConstants::COL_USER_ID) : null,
            ]));
            foreach ($userIds as $uid)
                UserLead::create([UsersConstants::COL_USER_ID => $uid, 'lead_id' => $lead->id]);
            if (Utility::settings($creatorId)['lead_assigned'] ?? 0) {
                Utility::sendEmailTemplate(
                    'lead_assigned',
                    [$lead->user_id => User::find($lead->user_id)->email],
                    [
                        'lead_name'     => $lead->name,
                        'lead_email'    => $lead->email,
                        'lead_subject'  => $lead->subject,
                        'lead_pipeline' => $pipeline->name,
                        'lead_stage'    => $stage->name,
                    ]
                );
            }
            $notif = Utility::settings($creatorId);
            $arr  = [
                'user_name'  => $user?->name,
                'lead_name'  => $lead->name,
                'lead_email' => $lead->email,
            ];
            ($notif['lead_notification'] ?? 0) && Utility::sendSlackMsg('new_lead', $arr);
            ($notif['telegram_lead_notification'] ?? 0) && Utility::sendTelegramMsg('new_lead', $arr);
            if ($hook = Utility::webhookSetting('New Lead')) {
                $ok = Utility::webhookCall($hook['url'], json_encode($lead), $hook['method']);
                $ok ?: redirect()->back()->with('error', __('Webhook call failed.'));
            }
            return redirect()->back()
                ->with('success', __('Lead successfully created!'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $request, Lead $lead): \Illuminate\View\View|RedirectResponse
    {
        try {
            self::_authorizeOwner($request, $lead, 'view lead');
            if (!$lead->isActive)
                return redirect()->back()->with('error', __('Permission Denied.'));
            $deal      = Deal::find($lead->isConverted);
            $stageIds  = LeadStage::where(ProjectsConstants::COL_PPL_ID, $lead[ProjectsConstants::COL_PPL_ID])
                ->where(DatabaseConstants::TABLE_CREATOR, $lead[DatabaseConstants::TABLE_CREATOR])
                ->pluck('id');
            $position  = $stageIds->search($lead->stageId) + 1;
            $percentage = number_format($position * 100 / $stageIds->count());
            $calendarTasks = [];
            return view(ViewsConstants::LD . '.' . __FUNCTION__, compact('lead', 'calendarTasks', 'deal', 'percentage'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $request, Lead $lead): \Illuminate\View\View|JsonResponse
    {
        try {
            self::_authorizeOwner($request, $lead, 'edit lead');
            $creatorId = $request->user()->creatorId();
            $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(ProjectsConstants::COL_PPL_NM, 'id')
                ->prepend(__('Select Pipeline'), '');
            $sources  = Source::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id');
            $products = ProductService::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id');
            $users    = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->whereNotIn(UsersConstants::COL_TP, [PermissionsConstants::CL, PermissionsConstants::CPN])
                ->where('id', '<>', $request->user()->id)
                ->pluck(UsersConstants::COL_NM, 'id');
            $lead->sources = explode(',', $lead->sources);
            $lead->products = explode(',', $lead->products);
            return view(ViewsConstants::LD . '.' . __FUNCTION__, compact(
                'lead',
                'pipelines',
                'sources',
                'products',
                'users'
            ));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        try {
            self::_authorizeOwner($request, $lead, 'edit lead');
            $data = $request->validate([
                'subject'     => 'required',
                'name'        => 'required',
                'email'       => 'required|unique:leads,email,' . $lead->id,
                ProjectsConstants::COL_PPL_ID => 'required',
                UsersConstants::COL_USER_ID     => 'required',
                'stage_id'    => 'required',
                'sources'     => 'required',
                'products'    => 'required',
            ]);
            $lead->fill([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'phone'       => $request->input('phone'),
                'subject'     => $data['subject'],
                UsersConstants::COL_USER_ID     => $data[UsersConstants::COL_USER_ID],
                ProjectsConstants::COL_PPL_ID => $data[ProjectsConstants::COL_PPL_ID],
                'stage_id'    => $data['stage_id'],
                'sources'     => implode(',', array_filter($request->input('sources'))),
                'products'    => implode(',', array_filter($request->input('products'))),
                'notes'       => $request->input('notes'),
            ])->save();
            return redirect()->back()->with('success', __('Lead successfully updated!'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse
    {
        try {
            self::_authorizeOwner($request, $lead, 'delete lead');
            LeadDiscussion::where('lead_id', $lead->id)->delete();
            LeadFile::where('lead_id', $lead->id)->delete();
            UserLead::where('lead_id', $lead->id)->delete();
            LeadActivityLog::where('lead_id', $lead->id)->delete();
            $lead->delete();
            return redirect()->back()->with('success', __('Lead successfully deleted!'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function json(Request $request): JsonResponse
    {
        try {
            self::_authorize($request, PermissionsConstants::MNG_LD);
            $stages = $request->input(ProjectsConstants::COL_PPL_ID)
                ? LeadStage::where(ProjectsConstants::COL_PPL_ID, $request->input(ProjectsConstants::COL_PPL_ID))->pluck('name', 'id')
                : [];
            return response()->json($stages, 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function fileUpload(Request $request, string|int $id): JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $request->validate(['file' => 'required']);
            $size   = $request->file('file')->getSize();
            $limitOk = Utility::updateStorageLimit($request->user()->creatorId(), $size) == 1;
            $name   = $request->file->getClientOriginalName();
            $path   = "{$id}_" . md5(time()) . "_{$name}";
            $file   = LeadFile::create([
                'lead_id'  => $id,
                'file_name' => $name,
                'file_path' => $path,
            ]);
            $ret = ['is_success' => true];
            if ($limitOk) {
                $request->file->storeAs('lead_files', $path);
                $ret['download'] = route(ViewsConstants::LD . '.file.download', [$id, $file->id]);
                $ret['delete']  = route(ViewsConstants::LD . '.file.delete', [$id, $file->id]);
            } else {
                $ret['status']     = 1;
                $ret['success_msg'] = '';
            }
            LeadActivityLog::create([
                UsersConstants::COL_USER_ID => $request->user()->id,
                'lead_id' => $lead->id,
                'log_type' => 'Upload File',
                'remark' => json_encode(['file_name' => $name]),
            ]);
            return response()->json($ret, 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        } catch (\Throwable $e) {
            return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
        }
    }

    public function fileDownload(Request $request, string|int $id, string|int $fileId): BinaryFileResponse|RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $file = LeadFile::findOrFail($fileId);
            $full = storage_path("lead_files/{$file->filePath}");
            return ResponseFacade::download($full, $file->fileName, [
                'Content-Length: ' . filesize($full)
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function fileDelete(Request $request, string|int $id, string|int $fileId): JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $file = LeadFile::findOrFail($fileId);
            Utility::changeStorageLimit(
                $request->user()->creatorId(),
                "lead_files/{$file->filePath}"
            );
            $full = storage_path("lead_files/{$file->filePath}");
            if (File::exists($full)) File::delete($full);
            $file->delete();
            return response()->json(['is_success' => true], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(
                ['is_success' => false, 'error' => __('Permission Denied.')],
                401
            );
        } catch (\Throwable $e) {
            return response()->json(
                ['is_success' => false, 'error' => __('An unexpected error occurred.')],
                500
            );
        }
    }

    public function noteStore(Request $request, string|int $id): JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $lead->notes = $request->input('notes');
            $lead->save();
            return response()->json(
                ['is_success' => true, 'success' => __('Note successfully saved!')],
                200
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(
                ['is_success' => false, 'error' => __('Permission Denied.')],
                401
            );
        } catch (\Throwable $e) {
            return response()->json(
                ['is_success' => false, 'error' => __('An unexpected error occurred.')],
                500
            );
        }
    }

    public function labels(Request $request, string|int $id): \Illuminate\View\View|JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $labels  = Label::where(ProjectsConstants::COL_PPL_ID, $lead[ProjectsConstants::COL_PPL_ID])
                ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->get();
            $selected = $lead->labels()?->pluck('name', 'id')->toArray() ?? [];
            return view(ViewsConstants::LD . '.' . __FUNCTION__, compact('lead', 'labels', 'selected'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function labelStore(Request $request, string|int $id): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $lead->labels = $request->input('labels')
                ? implode(',', array_filter($request->input('labels')))
                : '';
            $lead->save();
            return redirect()->back()
                ->with('success', __('Labels successfully updated!'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function userEdit(Request $request, string|int $id): \Illuminate\View\View|JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $users = User::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->whereNotIn(UsersConstants::COL_TP, [PermissionsConstants::CL, PermissionsConstants::CPN])
                ->whereNotIn('id', function ($q) use ($lead) {
                    $q->select(UsersConstants::COL_USER_ID)->from('user_leads')
                        ->where('lead_id', $lead->id);
                })->pluck(UsersConstants::COL_NM, 'id');
            return view(ViewsConstants::LD . '.users', compact('lead', 'users'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function userUpdate(Request $request, string|int $id): RedirectResponse
    {
        try {
            self::_authorize($request, 'edit lead');
            $user = $request->user();
            $lead = Lead::findOrFail($id);
            if ($lead[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                throw new \Illuminate\Auth\Access\AuthorizationException();
            $ids = array_filter($request->input('users', []));
            if ($ids) {
                foreach ($ids as $uid)
                    UserLead::create(['lead_id' => $lead->id, UsersConstants::COL_USER_ID => $uid]);
                return redirect()->back()
                    ->with('success', __('Users successfully updated!'));
            }
            return redirect()->back()
                ->with('error', __('Please Select Valid User!'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function userDestroy(Request $request, string|int $id, int $userId): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            UserLead::where('lead_id', $lead->id)
                ->where(UsersConstants::COL_USER_ID, $userId)
                ->delete();
            return redirect()->back()
                ->with('success', __('User successfully deleted!'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function productEdit(Request $request, string|int $id): \Illuminate\View\View|JsonResponse
    {
        try {
            $lead     = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $creatorId = $request->user()->creatorId();
            $excluded = explode(',', $lead->products);
            $products = ProductService::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->whereNotIn('id', $excluded)
                ->pluck('name', 'id');
            return view(ViewsConstants::LD . '.products', compact('lead', 'products'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function productUpdate(Request $request, string|int $id): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $new = array_filter($request->input('products', []));
            if ($new) {
                $all      = array_merge(explode(',', $lead->products), $new);
                $lead->products = implode(',', array_unique($all));
                $lead->save();
                $names = ProductService::whereIn('id', $new)->pluck('name')->toArray();
                LeadActivityLog::create([
                    UsersConstants::COL_USER_ID => $request->user()->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'Add Product',
                    'remark' => json_encode(['title' => implode(',', $names)]),
                ]);
                return redirect()->back()
                    ->with('success', __('Products successfully updated!'))
                    ->with('status', 'products');
            }
            return redirect()->back()
                ->with('error', __('Please Select Valid Product!'))
                ->with('status', 'general');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function productDestroy(Request $request, string|int $id, int $productId): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $keep = array_filter(explode(',', $lead->products), fn ($p) => $p != $productId);
            $lead->products = implode(',', $keep);
            $lead->save();
            return redirect()->back()
                ->with('success', __('Products successfully deleted!'))
                ->with('status', 'products');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function sourceEdit(Request $request, string|int $id): \Illuminate\View\View|JsonResponse
    {
        try {
            $lead     = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $creatorId = $request->user()->creatorId();
            $sources  = Source::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');
            $selected = $lead->sources()?->pluck('name', 'id')->toArray() ?? [];
            return view(ViewsConstants::LD . '.sources', compact('lead', 'sources', 'selected'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function sourceUpdate(Request $request, string|int $id): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $arr = array_filter($request->input('sources', []));
            $lead->sources = $arr ? implode(',', $arr) : '';
            $lead->save();
            LeadActivityLog::create([
                UsersConstants::COL_USER_ID => $request->user()->id,
                'lead_id' => $lead->id,
                'log_type' => 'Update Sources',
                'remark' => json_encode(['title' => 'Update Sources']),
            ]);
            return redirect()->back()
                ->with('success', __('Sources successfully updated!'))
                ->with('status', 'sources');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function sourceDestroy(Request $request, string|int $id, int $sourceId): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $keep = array_filter(explode(',', $lead->sources), fn ($s) => $s != $sourceId);
            $lead->sources = implode(',', $keep);
            $lead->save();
            return redirect()->back()
                ->with('success', __('Sources successfully deleted!'))
                ->with('status', 'sources');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function discussionCreate(Request $request, string|int $id): \Illuminate\View\View|JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            return view(ViewsConstants::LD . '.discussions', compact('lead'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function discussionStore(Request $request, string|int $id): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead');
            $disc = new LeadDiscussion([
                'comment' => $request->input('comment'),
                'lead_id' => $lead->id,
                DatabaseConstants::TABLE_CREATOR => $request->user()->id,
            ]);
            $disc->save();
            return redirect()->back()
                ->with('success', __('Message successfully added!'))
                ->with('status', 'discussion');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function order(Request $request): JsonResponse
    {
        try {
            self::_authorize($request, 'move lead');
            $post  = $request->all();
            $lead  = $this->lead($post['lead_id']);
            if ($lead->stageId != $post['stage_id']) {
                $new = LeadStage::findOrFail($post['stage_id']);
                LeadActivityLog::create([
                    UsersConstants::COL_USER_ID => $request->user()->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'Move',
                    'remark' => json_encode([
                        'title' => $lead->name,
                        'old_status' => $lead->stage->name,
                        'new_status' => $new->name,
                    ]),
                ]);
                Utility::sendEmailTemplate('Move Lead', $lead->users->pluck('email', 'id')->toArray(), [
                    'lead_name' => $lead->name,
                    'lead_pipeline' => $lead->pipeline->name,
                    'lead_stage' => $lead->stage->name,
                    'lead_old_stage' => $lead->stage->name,
                    'lead_new_stage' => $new->name,
                ]);
            }
            foreach ($post['order'] as $k => $item) {
                $l = $this->lead($item);
                $l->order  = $k;
                $l->stageId = $post['stage_id'];
                $l->save();
            }
            return response()->json(['success' => true], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => __('Permission Denied.')], 401);
        } catch (\Throwable $e) {
            return response()->json(['error' => __('An unexpected error occurred.')], 500);
        }
    }

    public function showConvertToDeal(Request $request, string|int $id): \Illuminate\View\View|JsonResponse
    {
        try {
            $lead      = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'convert lead');
            $creatorId = $request->user()->creatorId();
            $exist     = User::where(UsersConstants::COL_TP, PermissionsConstants::CL)
                ->where(UsersConstants::COL_EM, $lead->email)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->first();
            $clients   = User::where(UsersConstants::COL_TP, PermissionsConstants::CL)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(UsersConstants::COL_NM, 'id');
            return view(ViewsConstants::LD . '.convert', compact('lead', 'exist', 'clients'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function convertToDeal(Request $request, string|int $id): RedirectResponse
    {
        try {
            $lead     = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'convert lead');
            $user     = $request->user();
            $creatorId = $user?->creatorId();
            if ($request->input('client_check') === 'exist') {
                $clientId = $request->validate(['clients' => 'required'])['clients'];
                $client  = User::where(UsersConstants::COL_TP, PermissionsConstants::CL)
                    ->where(UsersConstants::COL_EM, $clientId)
                    ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                    ->firstOrFail();
            } else {
                $data = $request->validate([
                    'client_name' => 'required',
                    'client_email' => 'required|email|unique:users,email',
                    'client_password' => 'required',
                ]);
                $client = User::create([
                    UsersConstants::COL_NM => $data['client_name'],
                    UsersConstants::COL_EM => $data['client_email'],
                    UsersConstants::COL_PW => Hash::make($data['client_password']),
                    UsersConstants::COL_TP => PermissionsConstants::CL,
                    UsersConstants::COL_LG =>  DatabaseConstants::DEFAULT_LANG,
                    DatabaseConstants::TABLE_CREATOR => $creatorId,
                ]);
                $client->assignRole(Role::findByName(PermissionsConstants::CL));
                Utility::sendEmailTemplate('New User', [$client->id => $client->email], [
                    UsersConstants::COL_EM => $data['client_email'],
                    'password' => $data['client_password'],
                ]);
            }
            $stage = Stage::where(ProjectsConstants::COL_PPL_ID, $lead[ProjectsConstants::COL_PPL_ID])->firstOrFail();
            $deal = Deal::create([
                'name' => $request->input('name'),
                'price' => $request->input('price', 0),
                ProjectsConstants::COL_PPL_ID => $lead[ProjectsConstants::COL_PPL_ID],
                'stage_id' => $stage->id,
                'sources' => $request->input('is_transfer', []) ? implode(',', $lead->sourcesArray) : '',
                'products' => $request->input('is_transfer', []) ? implode(',', $lead->productsArray) : '',
                'notes' => $request->input('is_transfer', []) ? $lead->notes : '',
                'labels' => $lead->labels,
                'status' => 'Active',
                DatabaseConstants::TABLE_CREATOR => $lead[DatabaseConstants::TABLE_CREATOR],
            ]);
            ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $client->id]);
            Utility::sendEmailTemplate('Assign Deal', [$client->id => $client->email], [
                'deal_name' => $deal->name,
                'deal_pipeline' => Pipeline::find($lead[ProjectsConstants::COL_PPL_ID])->name,
                'deal_stage' => $stage->name,
                'deal_status' => $deal->status,
                'deal_price' => $user?->priceFormat($deal->price),
            ]);
            foreach (UserLead::where('lead_id', $lead->id)->pluck(UsersConstants::COL_USER_ID) as $uid)
                UserDeal::create([UsersConstants::COL_USER_ID => $uid, 'deal_id' => $deal->id]);
            if (in_array('discussion', $request->input('is_transfer', [])))
                foreach (LeadDiscussion::where('lead_id', $lead->id)->get() as $d)
                    DealDiscussion::create($d->only(['comment', DatabaseConstants::TABLE_CREATOR]) + ['deal_id' => $deal->id]);
            if (in_array('files', $request->input('is_transfer', []))) {
                foreach (LeadFile::where('lead_id', $lead->id)->get() as $f) {
                    copy(
                        storage_path("lead_files/{$f->filePath}"),
                        storage_path("deal_files/{$f->filePath}")
                    );
                    DealFile::create($f->only(['file_name', 'file_path']) + ['deal_id' => $deal->id]);
                }
            }
            if (in_array('calls', $request->input('is_transfer', [])))
                foreach (LeadCall::where('lead_id', $lead->id)->get() as $c)
                    DealCall::create($c->only(['subject', 'call_type', 'duration', UsersConstants::COL_USER_ID, 'description', 'call_result']) + ['deal_id' => $deal->id]);
            if (in_array('emails', $request->input('is_transfer', [])))
                foreach (LeadEmail::where('lead_id', $lead->id)->get() as $e)
                    DealEmail::create($e->only(['to', 'subject', 'description']) + ['deal_id' => $deal->id]);
            $lead->isConverted = $deal->id;
            $lead->save();
            $notif = Utility::settings($creatorId);
            $arr  = ['lead_user_name' => $lead->name, 'lead_name' => $lead->name, 'lead_email' => $lead->email];
            ($notif['leadtodeal_notification'] ?? 0) && Utility::sendSlackMsg('lead_to_deal_conversion', $arr);
            ($notif['telegram_leadtodeal_notification'] ?? 0) && Utility::sendTelegramMsg('lead_to_deal_conversion', $arr);
            if ($hook = Utility::webhookSetting('Lead to Deal Conversion')) {
                $hookOk = Utility::webhookCall($hook['url'], json_encode($lead), $hook['method']);
                $hookOk ?: redirect()->back()->with('error', __('Webhook call failed.'));
            }
            return redirect()->back()->with('success', __('Lead successfully converted'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function callCreate(Request $request, int $id): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'create lead call');
            $users = UserLead::where('lead_id', $lead->id)->get();
            return view(ViewsConstants::LD . '.calls', compact('lead', 'users'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function callStore(Request $request, int $id): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'create lead call');
            $data = $request->validate([
                'subject'   => 'required',
                'call_type' => 'required',
                UsersConstants::COL_USER_ID   => 'required',
            ]);
            $call = LeadCall::create([
                'lead_id'     => $lead->id,
                'subject'     => $data['subject'],
                'call_type'   => $data['call_type'],
                'duration'    => $request->input('duration'),
                UsersConstants::COL_USER_ID     => $data[UsersConstants::COL_USER_ID],
                'description' => $request->input('description'),
                'call_result' => $request->input('call_result'),
            ]);
            LeadActivityLog::create([
                UsersConstants::COL_USER_ID => $request->user()->id,
                'lead_id' => $lead->id,
                'log_type' => 'Create Lead Call',
                'remark'  => json_encode(['title' => $call->subject]),
            ]);
            return redirect()->back()
                ->with('success', __('Call successfully created!'))
                ->with('status', 'calls');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->with('status', 'calls');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function callEdit(Request $request, int $id, int $callId): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead call');
            $call = LeadCall::findOrFail($callId);
            $users = UserLead::where('lead_id', $lead->id)->get();
            return view(ViewsConstants::LD . '.calls', compact('call', 'lead', 'users'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function callUpdate(Request $request, int $id, int $callId): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'edit lead call');
            $data = $request->validate([
                'subject'   => 'required',
                'call_type' => 'required',
                UsersConstants::COL_USER_ID   => 'required',
            ]);
            $call = LeadCall::findOrFail($callId);
            $call->update([
                'subject'     => $data['subject'],
                'call_type'   => $data['call_type'],
                'duration'    => $request->input('duration'),
                UsersConstants::COL_USER_ID     => $data[UsersConstants::COL_USER_ID],
                'description' => $request->input('description'),
                'call_result' => $request->input('call_result'),
            ]);
            return redirect()->back()
                ->with('success', __('Call successfully updated!'))
                ->with('status', 'calls');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->with('status', 'calls');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function callDestroy(Request $request, int $id, int $callId): RedirectResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'delete lead call');
            $call = LeadCall::findOrFail($callId);
            $call->delete();
            return redirect()->back()
                ->with('success', __('Call successfully deleted!'))
                ->with('status', 'calls');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function emailCreate(Request $request, int $id): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        try {
            $lead = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'create lead email');
            return view(ViewsConstants::LD . '.emails', compact('lead'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function emailStore(Request $request, int $id): RedirectResponse
    {
        try {
            $lead    = Lead::findOrFail($id);
            self::_authorizeOwner($request, $lead, 'create lead email');
            $data    = $request->validate([
                'to'          => 'required|email',
                'subject'     => 'required',
                'description' => 'required',
            ]);
            $settings = Utility::settings();
            $email   = LeadEmail::create([
                'lead_id'    => $lead->id,
                'to'         => $data['to'],
                'subject'    => $data['subject'],
                'description' => $data['description'],
            ]);
            try {
                Mail::to($data['to'])->send(new SendLeadEmail($email->toArray(), $settings));
            } catch (\Exception $e) {
                $smtpError = __('E-Mail has not been sent due to SMTP configuration');
            }
            LeadActivityLog::create([
                UsersConstants::COL_USER_ID => $request->user()->id,
                'lead_id' => $lead->id,
                'log_type' => 'Create Lead Email',
                'remark'  => json_encode(['title' => 'Create new Lead Email']),
            ]);
            return redirect()->back()
                ->with('success', __('Email successfully created!')
                    . ($smtpError ? '<br><span class="text-danger">' . $smtpError . '</span>' : ''))
                ->with('status', 'emails');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->with('status', 'emails');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function lead(int|string $leadId): \App\Models\Lead
    {
        if (self::$leadData['lead'] === null || self::$leadData['id'] !== (int)$leadId) {
            self::$leadData['lead'] = \App\Models\Lead::findOrFail($leadId);
            self::$leadData['id']  = self::$leadData['lead']->getKey();
        }
        return self::$leadData['lead'];
    }

    protected static function _authorize(Request $request, string $permission): void
    {
        if (!$request->user()->can($permission)) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }
    }

    protected static function _authorizeOwner(Request $request, Lead $lead, string $permission): void
    {
        self::_authorize($request, $permission);
        if ($lead[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }
    }
}
