<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants as DC,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants as UC,
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
    WebhookSettings
};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    File,
    Hash,
    Log,
    Mail,
    Response as ResponseFacade,
    Route,
    View as ViewFacade,
};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    Response
};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
class LeadController extends Controller
{
    use HasCrudConstants;

    /** @var array{lead: ?\App\Models\Lead, id: int|null} */
    private static array $leadData = ['lead' => null, 'id' => null];

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, PermissionsConstants::MNG_LD);
                $this->logExecutionTime($authStart, $action, 'authorize');
                $user = $req->user();
                $creatorId = $user?->creatorId();
                Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId, 'user_id' => $user?->id]);
                $pipeSelStart = microtime(true);
                $pipeline = $user[UC::COL_DPL] ? Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->where('id', $user[UC::COL_DPL])->first() ?? Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->first() : Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->first();
                $this->logExecutionTime($pipeSelStart, $action, 'selectPipeline');
                $pipesStart = microtime(true);
                $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck(ProjectsConstants::COL_PPL_NM, 'id');
                $this->logExecutionTime($pipesStart, $action, 'pluckPipelines');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['pipelines_count' => count($pipelines ?? []), 'selected_pipeline_id' => $pipeline?->id]);
                return view($viewPath, compact('pipelines', 'pipeline'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] debug auth error", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'user_id' => $req->user()->id ?? null, 'creator_id' => $req->user()->creatorId() ?? null, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function leadList(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.list';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, PermissionsConstants::MNG_LD);
                $this->logExecutionTime($authStart, $action, 'authorize');
                $user = $req->user();
                $creatorId = $user?->creatorId();
                Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId, 'user_id' => $user?->id]);
                $pipeSelStart = microtime(true);
                $pipeline = $user[UC::COL_DPL] ? Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->where('id', $user[UC::COL_DPL])->first() ?? Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->first() : Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->first();
                $this->logExecutionTime($pipeSelStart, $action, 'selectPipeline');
                $pipesStart = microtime(true);
                $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck(ProjectsConstants::COL_PPL_NM, 'id');
                $this->logExecutionTime($pipesStart, $action, 'pluckPipelines');
                $leadsStart = microtime(true);
                $leads = Lead::select(ViewsConstants::LD . '.*')->join('user_leads', 'user_leads.lead_id', '=', ViewsConstants::LD . '.id')->where('user_leads.' . UC::COL_USER_ID, $user?->id)->where(ViewsConstants::LD . '.' . ProjectsConstants::COL_PPL_ID, $pipeline->id)->orderBy(ViewsConstants::LD . '.' . ActivitiesConstants::COL_OD)->get();
                $this->logExecutionTime($leadsStart, $action, 'fetchLeads');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['pipelines_count' => count($pipelines ?? []), 'leads_count' => $leads->count(), 'selected_pipeline_id' => $pipeline?->id]);
                return view($viewPath, compact('pipelines', 'pipeline', 'leads'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] debug auth error", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'user_id' => $req->user()->id ?? null, 'creator_id' => $req->user()->creatorId() ?? null, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function create(Request $request): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, 'create lead');
                $this->logExecutionTime($authStart, $action, 'authorize');
                $creatorId = $req->user()->creatorId();
                $userFetchStart = microtime(true);
                $users = User::where(DC::COL_TABLE_CREATOR, $creatorId)->whereNotIn(UC::COL_TP, [PermissionsConstants::CL, PermissionsConstants::CPN])->where('id', '<>', $req->user()->id)->pluck(UC::COL_NM, 'id')->prepend(__('Select User'), '');
                $this->logExecutionTime($userFetchStart, $action, 'fetchAssignableUsers');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['creator_id' => $creatorId, 'users_count' => count($users ?? [])]);
                return view($viewPath, compact('users'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] debug auth error", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'user_id' => $req->user()->id ?? null, 'creator_id' => $req->user()->creatorId() ?? null, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, 'create lead');
                $this->logExecutionTime($authStart, $action, 'authorize');
                $valStart = microtime(true);
                $data = $req->validate(['subject' => 'required', 'name' => 'required', 'email' => 'required|unique:leads,email']);
                $this->logExecutionTime($valStart, $action, 'validate');
                $user = $req->user();
                $creatorId = $user?->creatorId();
                Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId, 'user_id' => $user?->id]);
                $pipeSelStart = microtime(true);
                $pipeline = $user[UC::COL_DPL] ? Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->where('id', $user[UC::COL_DPL])->first() ?? Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->first() : Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->first();
                $this->logExecutionTime($pipeSelStart, $action, 'selectPipeline');
                $stageStart = microtime(true);
                $stage = LeadStage::where(ProjectsConstants::COL_PPL_ID, $pipeline->id)->first();
                $this->logExecutionTime($stageStart, $action, 'fetchStage');
                if (!$stage) return redirect()->back()->with('error', __('Please Create Stage for This Pipeline.'));
                $createStart = microtime(true);
                $lead = Lead::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $req->input('phone'), 'subject' => $data['subject'], UC::COL_USER_ID => $req->input(UC::COL_USER_ID), ProjectsConstants::COL_PPL_ID => $pipeline->id, 'stage_id' => $stage->id, DC::COL_TABLE_CREATOR => $creatorId, 'date' => now()->toDateString()]);
                $this->logExecutionTime($createStart, $action, 'createLead');
                $uidsStart = microtime(true);
                $userIds = array_unique(array_filter([$user?->id, $req->input(UC::COL_USER_ID) !== $user?->id ? $req->input(UC::COL_USER_ID) : null]));
                foreach ($userIds as $uid) UserLead::create([UC::COL_USER_ID => $uid, 'lead_id' => $lead->id]);
                $this->logExecutionTime($uidsStart, $action, 'linkUsersToLead');
                $mailCheckStart = microtime(true);
                if (Utility::settingsById($creatorId)['lead_assigned'] ?? 0) Utility::sendEmailTemplate('lead_assigned', [$lead->user_id => User::find($lead->user_id)->email], ['lead_name' => $lead->name, 'lead_email' => $lead->email, 'lead_subject' => $lead->subject, 'lead_pipeline' => $pipeline->name, 'lead_stage' => $stage->name]);
                $this->logExecutionTime($mailCheckStart, $action, 'maybeSendAssignedEmail');
                $notifStart = microtime(true);
                $notif = Utility::settingsById($creatorId);
                $arr = ['user_name' => $user?->name, 'lead_name' => $lead->name, 'lead_email' => $lead->email];
                ($notif['lead_notification'] ?? 0) && Utility::sendSlackMsg('new_lead', $arr);
                ($notif['telegram_lead_notification'] ?? 0) && Utility::sendTelegramMsg('new_lead', $arr);
                $this->logExecutionTime($notifStart, $action, 'sendNotifications');
                $hookStart = microtime(true);
                if ($hook = Utility::webhookSetting('New Lead')) {
                    $ok = Utility::webhookCall($hook['url'], json_encode($lead), $hook['method']);
                    $ok ?: redirect()->back()->with('error', __('Webhook call failed.'));
                }
                $this->logExecutionTime($hookStart, $action, 'maybeCallWebhook');
                Log::info("[{$class}::{$action}] created", ['lead_id' => $lead->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id]);
                return redirect()->back()->with('success', __('Lead successfully created!'));
            } catch (ValidationException $e) {
                Log::debug("[{$class}::{$action}] validation exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return redirect()->back()->with('error', $e->getMessage());
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function show(Request $request, Lead $lead): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $lead, $action, $method, $class, $viewPath) {
            try {
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'view lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                if (!$lead->isActive) return redirect()->back()->with('error', __('Permission Denied.'));
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id]);
                $dealStart = microtime(true);
                $deal = Deal::find($lead->isConverted);
                $this->logExecutionTime($dealStart, $action, 'findDeal');
                $stageStart = microtime(true);
                $stageIds = LeadStage::where(ProjectsConstants::COL_PPL_ID, $lead[ProjectsConstants::COL_PPL_ID])->where(DC::COL_TABLE_CREATOR, $lead[DC::COL_TABLE_CREATOR])->pluck('id');
                $this->logExecutionTime($stageStart, $action, 'pluckStageIds');
                $position = $stageIds->search($lead->stageId) + 1;
                $percentage = number_format($position * 100 / $stageIds->count());
                $calendarTasks = [];
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'deal_id' => $deal?->id, 'percentage' => $percentage]);
                return view($viewPath, compact('lead', 'calendarTasks', 'deal', 'percentage'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $lead->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $lead->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $lead->id]);
    }

    public function edit(Request $request, Lead $lead): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $lead, $action, $method, $class, $viewPath) {
            try {
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $creatorId = $req->user()->creatorId();
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id, 'creator_id' => $creatorId]);
                $pipesStart = microtime(true);
                $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck(ProjectsConstants::COL_PPL_NM, 'id')->prepend(__('Select Pipeline'), '');
                $this->logExecutionTime($pipesStart, $action, 'pluckPipelines');
                $srcStart = microtime(true);
                $sources = Source::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
                $this->logExecutionTime($srcStart, $action, 'pluckSources');
                $prdStart = microtime(true);
                $products = ProductService::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
                $this->logExecutionTime($prdStart, $action, 'pluckProducts');
                $usrStart = microtime(true);
                $users = User::where(DC::COL_TABLE_CREATOR, $creatorId)->whereNotIn(UC::COL_TP, [PermissionsConstants::CL, PermissionsConstants::CPN])->where('id', '<>', $req->user()->id)->pluck(UC::COL_NM, 'id');
                $this->logExecutionTime($usrStart, $action, 'pluckUsers');
                $lead->sources = explode(',', $lead->sources);
                $lead->products = explode(',', $lead->products);
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'pipelines' => count($pipelines ?? []), 'sources' => count($sources ?? []), 'products' => count($products ?? []), 'users' => count($users ?? [])]);
                return view($viewPath, compact('lead', 'pipelines', 'sources', 'products', 'users'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $lead->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $lead->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $lead->id]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $lead, $action, $method, $class) {
            try {
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id]);
                $valStart = microtime(true);
                $data = $req->validate(['subject' => 'required', 'name' => 'required', 'email' => 'required|unique:leads,email,' . $lead->id, ProjectsConstants::COL_PPL_ID => 'required', UC::COL_USER_ID => 'required', 'stage_id' => 'required', 'sources' => 'required', 'products' => 'required']);
                $this->logExecutionTime($valStart, $action, 'validate');
                $payload = ['name' => $data['name'], 'email' => $data['email'], 'phone' => $req->input('phone'), 'subject' => $data['subject'], UC::COL_USER_ID => $data[UC::COL_USER_ID], ProjectsConstants::COL_PPL_ID => $data[ProjectsConstants::COL_PPL_ID], 'stage_id' => $data['stage_id'], 'sources' => implode(',', array_filter($req->input('sources'))), 'products' => implode(',', array_filter($req->input('products'))), 'notes' => $req->input('notes')];
                $saveStart = microtime(true);
                $lead->fill($payload)->save();
                $this->logExecutionTime($saveStart, $action, 'saveLead');
                Log::info("[{$class}::{$action}] updated", ['lead_id' => $lead->id]);
                return redirect()->back()->with('success', __('Lead successfully updated!'));
            } catch (ValidationException $e) {
                Log::debug("[{$class}::{$action}] validation exception", ['lead_id' => $lead->id, 'message' => $e->getMessage(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return redirect()->back()->with('error', $e->getMessage());
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $lead->id, 'message' => $e->getMessage(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $lead->id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all())]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $lead->id]);
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $lead, $action, $method, $class) {
            try {
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'delete lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id]);
                $t1 = microtime(true);
                LeadDiscussion::where('lead_id', $lead->id)->delete();
                $this->logExecutionTime($t1, $action, 'deleteLeadDiscussions');
                $t2 = microtime(true);
                LeadFile::where('lead_id', $lead->id)->delete();
                $this->logExecutionTime($t2, $action, 'deleteLeadFiles');
                $t3 = microtime(true);
                UserLead::where('lead_id', $lead->id)->delete();
                $this->logExecutionTime($t3, $action, 'deleteUserLeads');
                $t4 = microtime(true);
                LeadActivityLog::where('lead_id', $lead->id)->delete();
                $this->logExecutionTime($t4, $action, 'deleteLeadActivityLogs');
                $t5 = microtime(true);
                $lead->delete();
                $this->logExecutionTime($t5, $action, 'deleteLead');
                Log::info("[{$class}::{$action}] deleted", ['lead_id' => $lead->id]);
                return redirect()->back()->with('success', __('Lead successfully deleted!'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $lead->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $lead->id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $lead->id]);
    }

    public function json(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, PermissionsConstants::MNG_LD);
                $this->logExecutionTime($authStart, $action, 'authorize');
                $pipelineId = $req->input(ProjectsConstants::COL_PPL_ID);
                Log::info("[{$class}::{$action}] start", [ProjectsConstants::COL_PPL_ID => $pipelineId]);
                $stageStart = microtime(true);
                $stages = $pipelineId ? LeadStage::where(ProjectsConstants::COL_PPL_ID, $pipelineId)->pluck('name', 'id') : [];
                $this->logExecutionTime($stageStart, $action, 'pluckStages');
                Log::info("[{$class}::{$action}] ready", ['count' => is_countable($stages) ? count($stages) : 0]);
                return response()->json($stages, 200);
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'pipeline_id' => $req->input(ProjectsConstants::COL_PPL_ID), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, ProjectsConstants::COL_PPL_ID => $request->input(ProjectsConstants::COL_PPL_ID)]);
    }

    public const FL_UPL = 'fileUpload';
    public function fileUpload(Request $request, string|int $id): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $valStart = microtime(true);
                $req->validate(['file' => 'required']);
                $this->logExecutionTime($valStart, $action, 'validate');
                $size = $req->file('file')->getSize();
                $quotaStart = microtime(true);
                $limitOk = Utility::updateStorageLimit($req->user()->creatorId(), $size) == 1;
                $this->logExecutionTime($quotaStart, $action, 'updateStorageLimit');
                $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $req->file->getClientOriginalName());
                $path = "{$id}_" . md5(time()) . "_{$name}";
                $fileCreateStart = microtime(true);
                $file = LeadFile::create(['lead_id' => $id, 'file_name' => $name, 'file_path' => $path]);
                $this->logExecutionTime($fileCreateStart, $action, 'createLeadFile');
                $ret = ['is_success' => true];
                if ($limitOk) {
                    $storeStart = microtime(true);
                    $req->file->storeAs('lead_files', $path);
                    $this->logExecutionTime($storeStart, $action, 'storeFile');
                    $routeStart = microtime(true);
                    $ret['download'] = route(ViewsConstants::LD . '.file.download', [$id, $file->id]);
                    $ret['delete'] = route(ViewsConstants::LD . '.file.delete', [$id, $file->id]);
                    $this->logExecutionTime($routeStart, $action, 'buildFileRoutes');
                } else {
                    $ret['status'] = 1;
                    $ret['success_msg'] = '';
                }
                $logStart = microtime(true);
                LeadActivityLog::create([UC::COL_USER_ID => $req->user()->id, 'lead_id' => $lead->id, 'log_type' => 'Upload File', 'remark' => json_encode(['file_name' => $name])]);
                $this->logExecutionTime($logStart, $action, 'createActivityLog');
                Log::info("[{$class}::{$action}] uploaded", ['lead_id' => $lead->id, 'file_id' => $file->id, 'size' => $size, 'limit_ok' => $limitOk]);
                return response()->json($ret, 200);
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const FL_DWL = 'fileDownload';
    public function fileDownload(Request $request, string|int $id, string|int $fileId): BinaryFileResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $fileId, $action, $method, $class) {
            try {
                $findLeadStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findLeadStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $findFileStart = microtime(true);
                $file = LeadFile::findOrFail($fileId);
                $this->logExecutionTime($findFileStart, $action, 'findLeadFile');
                $pathStart = microtime(true);
                $full = storage_path("lead_files/{$file->filePath}");
                $len = filesize($full);
                $this->logExecutionTime($pathStart, $action, 'resolvePathAndSize');
                $dlStart = microtime(true);
                $resp = ResponseFacade::download($full, $file->fileName, ['Content-Length: ' . $len]);
                $this->logExecutionTime($dlStart, $action, 'buildDownloadResponse');
                Log::info("[{$class}::{$action}] download ready", ['lead_id' => $lead->id, 'file_id' => $file->id, 'bytes' => $len]);
                return $resp;
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'file_id' => $fileId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'file_id' => $fileId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'file_id' => $fileId]);
    }

    public const FL_DEL = 'fileDelete';
    public function fileDelete(Request $request, string|int $id, string|int $fileId): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $fileId, $action, $method, $class) {
            try {
                $findLeadStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findLeadStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $findFileStart = microtime(true);
                $file = LeadFile::findOrFail($fileId);
                $this->logExecutionTime($findFileStart, $action, 'findLeadFile');
                $quotaStart = microtime(true);
                Utility::changeStorageLimit($req->user()->creatorId(), "lead_files/{$file->filePath}");
                $this->logExecutionTime($quotaStart, $action, 'changeStorageLimit');
                $pathStart = microtime(true);
                $full = storage_path("lead_files/{$file->filePath}");
                $exists = File::exists($full);
                $this->logExecutionTime($pathStart, $action, 'resolvePathAndExists');
                if ($exists) {
                    $delFSStart = microtime(true);
                    File::delete($full);
                    $this->logExecutionTime($delFSStart, $action, 'deleteFileFromFS');
                }
                $delRowStart = microtime(true);
                $file->delete();
                $this->logExecutionTime($delRowStart, $action, 'deleteLeadFileRow');
                Log::info("[{$class}::{$action}] deleted", ['lead_id' => $lead->id, 'file_id' => $file->id, 'fs_deleted' => $exists]);
                return response()->json(['is_success' => true], 200);
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'file_id' => $fileId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'file_id' => $fileId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'file_id' => $fileId]);
    }

    public const NT_STR = 'noteStore';
    public function noteStore(Request $request, string|int $id): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                Log::info("[{$class}::{$action}] start", ['lead_id' => $id]);
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $lead->notes = $req->input('notes');
                $saveStart = microtime(true);
                $lead->save();
                $this->logExecutionTime($saveStart, $action, 'saveLeadNotes');
                Log::info("[{$class}::{$action}] saved", ['lead_id' => $lead->id]);
                return response()->json(['is_success' => true, 'success' => __('Note successfully saved!')], 200);
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public function labels(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $labelsStart = microtime(true);
                $labels = Label::where(ProjectsConstants::COL_PPL_ID, $lead[ProjectsConstants::COL_PPL_ID])->where(DC::COL_TABLE_CREATOR, $req->user()->creatorId())->get();
                $this->logExecutionTime($labelsStart, $action, 'fetchLabels');
                $selectedStart = microtime(true);
                $selected = $lead->labels()?->pluck('name', 'id')->toArray() ?? [];
                $this->logExecutionTime($selectedStart, $action, 'pluckSelectedLabels');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'labels_count' => $labels->count(), 'selected_count' => count($selected)]);
                return view($viewPath, compact('lead', 'labels', 'selected'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const LB_STR = 'labelStore';
    public function labelStore(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                Log::info("[{$class}::{$action}] start", ['lead_id' => $id]);
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $labels = $req->input('labels') ? implode(',', array_filter($req->input('labels'))) : '';
                $lead->labels = $labels;
                $saveStart = microtime(true);
                $lead->save();
                $this->logExecutionTime($saveStart, $action, 'saveLeadLabels');
                Log::info("[{$class}::{$action}] labels updated", ['lead_id' => $lead->id, 'labels_length' => strlen($labels)]);
                return redirect()->back()->with('success', __('Labels successfully updated!'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'labels_input_count' => is_array($req->input('labels')) ? count($req->input('labels')) : 0, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const USR_EDT = 'userEdit';
    public function userEdit(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.users';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $usersStart = microtime(true);
                $users = User::where(DC::COL_TABLE_CREATOR, $req->user()->creatorId())->whereNotIn(UC::COL_TP, [PermissionsConstants::CL, PermissionsConstants::CPN])->whereNotIn('id', function ($q) use ($lead) {
                    $q->select(UC::COL_USER_ID)->from('user_leads')->where('lead_id', $lead->id);
                })->pluck(UC::COL_NM, 'id');
                $this->logExecutionTime($usersStart, $action, 'pluckAssignableUsers');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'users_count' => count($users ?? [])]);
                return view($viewPath, compact('lead', 'users'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const USR_UPD = 'userUpdate';
    public function userUpdate(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorize');
                $user = $req->user();
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                if ($lead[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new AuthorizationException();
                $ids = array_filter($req->input('users', []));
                if ($ids) {
                    $linkStart = microtime(true);
                    foreach ($ids as $uid) UserLead::create(['lead_id' => $lead->id, UC::COL_USER_ID => $uid]);
                    $this->logExecutionTime($linkStart, $action, 'linkUsersToLead');
                    Log::info("[{$class}::{$action}] users updated", ['lead_id' => $lead->id, 'count' => count($ids)]);
                    return redirect()->back()->with('success', __('Users successfully updated!'));
                }
                Log::debug("[{$class}::{$action}] no valid users selected", ['lead_id' => $lead->id, 'input_count' => is_array($req->input('users')) ? count($req->input('users')) : 0, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return redirect()->back()->with('error', __('Please Select Valid User!'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const USR_DST = 'userDestroy';
    public function userDestroy(Request $request, string|int $id, string|int $userId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $userId, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $delStart = microtime(true);
                UserLead::where('lead_id', $lead->id)->where(UC::COL_USER_ID, $userId)->delete();
                $this->logExecutionTime($delStart, $action, 'unlinkUserFromLead');
                Log::info("[{$class}::{$action}] user unlinked", ['lead_id' => $lead->id, 'user_id' => $userId]);
                return redirect()->back()->with('success', __('User successfully deleted!'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'user_id' => $userId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'user_id' => $userId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'user_id' => $userId]);
    }

    public const PRD_EDT = 'productEdit';
    public function productEdit(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.products';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $creatorId = $req->user()->creatorId();
                $excluded = explode(',', $lead->products);
                $prodStart = microtime(true);
                $products = ProductService::where(DC::COL_TABLE_CREATOR, $creatorId)->whereNotIn('id', $excluded)->pluck('name', 'id');
                $this->logExecutionTime($prodStart, $action, 'pluckProducts');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'products' => count($products ?? []), 'excluded_count' => count(array_filter($excluded))]);
                return view($viewPath, compact('lead', 'products'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const PRD_UPD = 'productUpdate';
    public function productUpdate(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id]);
                $new = array_filter($req->input('products', []));
                if ($new) {
                    $mergeStart = microtime(true);
                    $all = array_merge(explode(',', $lead->products), $new);
                    $lead->products = implode(',', array_unique($all));
                    $lead->save();
                    $this->logExecutionTime($mergeStart, $action, 'mergeAndSaveProducts');
                    $namesStart = microtime(true);
                    $names = ProductService::whereIn('id', $new)->pluck('name')->toArray();
                    $this->logExecutionTime($namesStart, $action, 'pluckNewProductNames');
                    $logStart = microtime(true);
                    LeadActivityLog::create([UC::COL_USER_ID => $req->user()->id, 'lead_id' => $lead->id, 'log_type' => 'Add Product', 'remark' => json_encode(['title' => implode(',', $names)])]);
                    $this->logExecutionTime($logStart, $action, 'createActivityLog');
                    Log::info("[{$class}::{$action}] products updated", ['lead_id' => $lead->id, 'added_count' => count($new)]);
                    return redirect()->back()->with('success', __('Products successfully updated!'))->with('status', 'products');
                }
                Log::debug("[{$class}::{$action}] no valid products provided", ['lead_id' => $lead->id, 'input_count' => is_array($req->input('products')) ? count($req->input('products')) : 0, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return redirect()->back()->with('error', __('Please Select Valid Product!'))->with('status', 'general');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const PRD_DST = 'productDestroy';
    public function productDestroy(Request $request, string|int $id, string|int $productId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $productId, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id, 'product_id' => $productId]);
                $updateStart = microtime(true);
                $keep = array_filter(explode(',', $lead->products), fn($p) => $p != $productId);
                $lead->products = implode(',', $keep);
                $lead->save();
                $this->logExecutionTime($updateStart, $action, 'removeProductAndSave');
                Log::info("[{$class}::{$action}] product removed", ['lead_id' => $lead->id, 'product_id' => $productId]);
                return redirect()->back()->with('success', __('Products successfully deleted!'))->with('status', 'products');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'product_id' => $productId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'product_id' => $productId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'product_id' => $productId]);
    }

    public const SRC_EDT = 'sourceEdit';
    public function sourceEdit(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.sources';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $creatorId = $req->user()->creatorId();
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id, 'creator_id' => $creatorId]);
                $srcStart = microtime(true);
                $sources = Source::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
                $this->logExecutionTime($srcStart, $action, 'pluckSources');
                $selStart = microtime(true);
                $selected = $lead->sources()?->pluck('name', 'id')->toArray() ?? [];
                $this->logExecutionTime($selStart, $action, 'pluckSelectedSources');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'sources_count' => is_countable($sources) ? count($sources) : 0, 'selected_count' => count($selected)]);
                return view($viewPath, compact('lead', 'sources', 'selected'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const SRC_UPD = 'sourceUpdate';
    public function sourceUpdate(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id]);
                $arr = array_filter($req->input('sources', []));
                $lead->sources = $arr ? implode(',', $arr) : '';
                $saveStart = microtime(true);
                $lead->save();
                $this->logExecutionTime($saveStart, $action, 'saveLeadSources');
                $logStart = microtime(true);
                LeadActivityLog::create([UC::COL_USER_ID => $req->user()->id, 'lead_id' => $lead->id, 'log_type' => 'Update Sources', 'remark' => json_encode(['title' => 'Update Sources'])]);
                $this->logExecutionTime($logStart, $action, 'createActivityLog');
                Log::info("[{$class}::{$action}] sources updated", ['lead_id' => $lead->id, 'count' => count($arr)]);
                return redirect()->back()->with('success', __('Sources successfully updated!'))->with('status', 'sources');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const SRC_DST = 'sourceDestroy';
    public function sourceDestroy(Request $request, string|int $id, string|int $sourceId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $sourceId, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id, 'source_id' => $sourceId]);
                $updateStart = microtime(true);
                $keep = array_filter(explode(',', $lead->sources), fn($s) => $s != $sourceId);
                $lead->sources = implode(',', $keep);
                $lead->save();
                $this->logExecutionTime($updateStart, $action, 'removeSourceAndSave');
                Log::info("[{$class}::{$action}] source removed", ['lead_id' => $lead->id, 'source_id' => $sourceId]);
                return redirect()->back()->with('success', __('Sources successfully deleted!'))->with('status', 'sources');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'source_id' => $sourceId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'source_id' => $sourceId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'source_id' => $sourceId]);
    }

    public const DCS_CRT = 'discussionCreate';
    public function discussionCreate(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.discussions';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id]);
                return view($viewPath, compact('lead'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const DCS_STR = 'discussionStore';
    public function discussionStore(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $buildStart = microtime(true);
                $disc = new LeadDiscussion(['comment' => $req->input('comment'), 'lead_id' => $lead->id, DC::COL_TABLE_CREATOR => $req->user()->id]);
                $this->logExecutionTime($buildStart, $action, 'buildDiscussion');
                $saveStart = microtime(true);
                $disc->save();
                $this->logExecutionTime($saveStart, $action, 'saveDiscussion');
                Log::info("[{$class}::{$action}] discussion saved", ['lead_id' => $lead->id, 'discussion_id' => $disc->id]);
                return redirect()->back()->with('success', __('Message successfully added!'))->with('status', 'discussion');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public function order(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            try {
                $authStart = microtime(true);
                self::_authorize($req, 'move lead');
                $this->logExecutionTime($authStart, $action, 'authorize');
                $post = $req->all();
                Log::info("[{$class}::{$action}] start", ['lead_id' => $post['lead_id'] ?? null, 'stage_id' => $post['stage_id'] ?? null, 'order_count' => is_countable($post['order'] ?? null) ? count($post['order']) : 0]);
                $leadFetchStart = microtime(true);
                $lead = $this->lead($post['lead_id']);
                $this->logExecutionTime($leadFetchStart, $action, 'fetchLead');
                if ($lead->stageId != $post['stage_id']) {
                    $newStageStart = microtime(true);
                    $new = LeadStage::findOrFail($post['stage_id']);
                    $this->logExecutionTime($newStageStart, $action, 'findNewStage');
                    $logMoveStart = microtime(true);
                    LeadActivityLog::create([UC::COL_USER_ID => $req->user()->id, 'lead_id' => $lead->id, 'log_type' => 'Move', 'remark' => json_encode(['title' => $lead->name, 'old_status' => $lead->stage->name, 'new_status' => $new->name])]);
                    $this->logExecutionTime($logMoveStart, $action, 'createMoveActivityLog');
                    $emailStart = microtime(true);
                    Utility::sendEmailTemplate('Move Lead', $lead->users->pluck('email', 'id')->toArray(), ['lead_name' => $lead->name, 'lead_pipeline' => $lead->pipeline->name, 'lead_stage' => $lead->stage->name, 'lead_old_stage' => $lead->stage->name, 'lead_new_stage' => $new->name]);
                    $this->logExecutionTime($emailStart, $action, 'sendMoveEmail');
                }
                $updateStart = microtime(true);
                foreach ($post['order'] as $k => $item) {
                    $l = $this->lead($item);
                    $l->order = $k;
                    $l->stageId = $post['stage_id'];
                    $l->save();
                }
                $this->logExecutionTime($updateStart, $action, 'reorderAndSaveLeads');
                Log::info("[{$class}::{$action}] completed", ['stage_id' => $post['stage_id'] ?? null]);
                return response()->json(['success' => true], 200);
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return response()->json(['error' => __('Permission Denied.')], 401);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return response()->json(['error' => __('An unexpected error occurred.')], 500);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public const SHW_CV_DL = 'showConvertToDeal';
    public function showConvertToDeal(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.convert';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'convert lead');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $creatorId = $req->user()->creatorId();
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id, 'creator_id' => $creatorId, 'lead_email' => $lead->email]);
                $existStart = microtime(true);
                $exist = User::where(UC::COL_TP, PermissionsConstants::CL)->where(UC::COL_EM, $lead->email)->where(DC::COL_TABLE_CREATOR, $creatorId)->first();
                $this->logExecutionTime($existStart, $action, 'findExistingClientByEmail');
                $clientsStart = microtime(true);
                $clients = User::where(UC::COL_TP, PermissionsConstants::CL)->where(DC::COL_TABLE_CREATOR, $creatorId)->pluck(UC::COL_NM, 'id');
                $this->logExecutionTime($clientsStart, $action, 'pluckClients');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'has_existing' => (bool) $exist, 'clients_count' => is_countable($clients) ? count($clients) : 0]);
                return view($viewPath, compact('lead', 'exist', 'clients'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const CV_DL = 'convertToDeal';
    public function convertToDeal(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $tFindLead = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($tFindLead, $action, 'findLead');
                $tAuth = microtime(true);
                self::_authorizeOwner($req, $lead, 'convert lead');
                $this->logExecutionTime($tAuth, $action, 'authorizeOwner');
                $user = $req->user();
                $creatorId = $user?->creatorId();
                Log::info("[{$class}::{$action}] start", ['lead_id' => $lead->id, 'creator_id' => $creatorId, 'client_check' => $req->input('client_check')]);
                if ($req->input('client_check') === 'exist') {
                    $tValExist = microtime(true);
                    $clientId = $req->validate(['clients' => 'required'])['clients'];
                    $this->logExecutionTime($tValExist, $action, 'validateExistingClient');
                    $tFindClient = microtime(true);
                    $client = User::where(UC::COL_TP, PermissionsConstants::CL)->where(UC::COL_EM, $clientId)->where(DC::COL_TABLE_CREATOR, $creatorId)->firstOrFail();
                    $this->logExecutionTime($tFindClient, $action, 'findExistingClient');
                } else {
                    $tValNew = microtime(true);
                    $data = $req->validate(['client_name' => 'required', 'client_email' => 'required|email|unique:users,email', 'client_password' => 'required']);
                    $this->logExecutionTime($tValNew, $action, 'validateNewClient');
                    $tCreateClient = microtime(true);
                    $client = User::create([UC::COL_NM => $data['client_name'], UC::COL_EM => $data['client_email'], UC::COL_PW => Hash::make($data['client_password']), UC::COL_TP => PermissionsConstants::CL, UC::COL_LG => DC::DEFAULT_LANG, DC::COL_TABLE_CREATOR => $creatorId]);
                    $this->logExecutionTime($tCreateClient, $action, 'createClient');
                    $tAssignRole = microtime(true);
                    $client->assignRole(Role::findByName(PermissionsConstants::CL));
                    $this->logExecutionTime($tAssignRole, $action, 'assignClientRole');
                    $tEmailNew = microtime(true);
                    Utility::sendEmailTemplate('New User', [$client->id => $client->email], [UC::COL_EM => $data['client_email'], 'password' => $data['client_password']]);
                    $this->logExecutionTime($tEmailNew, $action, 'emailNewUser');
                }
                $tFindStage = microtime(true);
                $stage = Stage::where(ProjectsConstants::COL_PPL_ID, $lead[ProjectsConstants::COL_PPL_ID])->firstOrFail();
                $this->logExecutionTime($tFindStage, $action, 'findStage');
                $tCreateDeal = microtime(true);
                $deal = Deal::create(['name' => $req->input('name'), 'price' => $req->input('price', 0), ProjectsConstants::COL_PPL_ID => $lead[ProjectsConstants::COL_PPL_ID], 'stage_id' => $stage->id, 'sources' => $req->input('is_transfer', []) ? implode(',', $lead->sourcesArray) : '', 'products' => $req->input('is_transfer', []) ? implode(',', $lead->productsArray) : '', 'notes' => $req->input('is_transfer', []) ? $lead->notes : '', 'labels' => $lead->labels, 'status' => 'Active', DC::COL_TABLE_CREATOR => $lead[DC::COL_TABLE_CREATOR]]);
                $this->logExecutionTime($tCreateDeal, $action, 'createDeal');
                $tLinkClient = microtime(true);
                ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $client->id]);
                $this->logExecutionTime($tLinkClient, $action, 'linkClientDeal');
                $tAssignEmail = microtime(true);
                Utility::sendEmailTemplate('Assign Deal', [$client->id => $client->email], ['deal_name' => $deal->name, 'deal_pipeline' => Pipeline::find($lead[ProjectsConstants::COL_PPL_ID])->name, 'deal_stage' => $stage->name, 'deal_status' => $deal->status, 'deal_price' => $user?->priceFormat($deal->price)]);
                $this->logExecutionTime($tAssignEmail, $action, 'emailAssignDeal');
                $tLinkUsers = microtime(true);
                foreach (UserLead::where('lead_id', $lead->id)->pluck(UC::COL_USER_ID) as $uid) UserDeal::create([UC::COL_USER_ID => $uid, 'deal_id' => $deal->id]);
                $this->logExecutionTime($tLinkUsers, $action, 'linkUsersToDeal');
                if (in_array('discussion', $req->input('is_transfer', []))) {
                    $tTransDisc = microtime(true);
                    foreach (LeadDiscussion::where('lead_id', $lead->id)->get() as $d) DealDiscussion::create($d->only(['comment', DC::COL_TABLE_CREATOR]) + ['deal_id' => $deal->id]);
                    $this->logExecutionTime($tTransDisc, $action, 'transferDiscussions');
                }
                if (in_array('files', $req->input('is_transfer', []))) {
                    $tTransFiles = microtime(true);
                    foreach (LeadFile::where('lead_id', $lead->id)->get() as $f) {
                        copy(storage_path("lead_files/{$f->filePath}"), storage_path("deal_files/{$f->filePath}"));
                        DealFile::create($f->only(['file_name', 'file_path']) + ['deal_id' => $deal->id]);
                    }
                    $this->logExecutionTime($tTransFiles, $action, 'transferFiles');
                }
                if (in_array('calls', $req->input('is_transfer', []))) {
                    $tTransCalls = microtime(true);
                    foreach (LeadCall::where('lead_id', $lead->id)->get() as $c) DealCall::create($c->only(['subject', 'call_type', 'duration', UC::COL_USER_ID, 'description', 'call_result']) + ['deal_id' => $deal->id]);
                    $this->logExecutionTime($tTransCalls, $action, 'transferCalls');
                }
                if (in_array('emails', $req->input('is_transfer', []))) {
                    $tTransEmails = microtime(true);
                    foreach (LeadEmail::where('lead_id', $lead->id)->get() as $e) DealEmail::create($e->only(['to', 'subject', 'description']) + ['deal_id' => $deal->id]);
                    $this->logExecutionTime($tTransEmails, $action, 'transferEmails');
                }
                $tMarkConverted = microtime(true);
                $lead->isConverted = $deal->id;
                $lead->save();
                $this->logExecutionTime($tMarkConverted, $action, 'markLeadConverted');
                $notif = Utility::settingsById($creatorId);
                $arr = ['lead_user_name' => $lead->name, 'lead_name' => $lead->name, 'lead_email' => $lead->email];
                if ($notif['leadtodeal_notification'] ?? 0) {
                    $tSlack = microtime(true);
                    Utility::sendSlackMsg('lead_to_deal_conversion', $arr);
                    $this->logExecutionTime($tSlack, $action, 'slackNotify');
                }
                if ($notif['telegram_leadtodeal_notification'] ?? 0) {
                    $tTg = microtime(true);
                    Utility::sendTelegramMsg('lead_to_deal_conversion', $arr);
                    $this->logExecutionTime($tTg, $action, 'telegramNotify');
                }
                if ($hook = Utility::webhookSetting('Lead to Deal Conversion')) {
                    $tWebhook = microtime(true);
                    $hookOk = Utility::webhookCall($hook['url'], json_encode($lead), $hook['method']);
                    $this->logExecutionTime($tWebhook, $action, 'webhookCall');
                    $hookOk ?: redirect()->back()->with('error', __('Webhook call failed.'));
                }
                Log::info("[{$class}::{$action}] converted", ['lead_id' => $lead->id, 'deal_id' => $deal->id, 'client_id' => $client->id]);
                return redirect()->back()->with('success', __('Lead successfully converted'));
            } catch (ValidationException $e) {
                Log::debug("[{$class}::{$action}] validation exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return redirect()->back()->with('error', $e->getMessage());
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const CLL_CRT = 'callCreate';
    public function callCreate(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.calls';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'create lead call');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $usersStart = microtime(true);
                $users = UserLead::where('lead_id', $lead->id)->get();
                $this->logExecutionTime($usersStart, $action, 'fetchUserLeads');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'users_count' => $users->count()]);
                return view($viewPath, compact('lead', 'users'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const CLL_STR = 'callStore';
    public function callStore(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                $authStart = microtime(true);
                self::_authorizeOwner($req, $lead, 'create lead call');
                $this->logExecutionTime($authStart, $action, 'authorizeOwner');
                $valStart = microtime(true);
                $data = $req->validate(['subject' => 'required', 'call_type' => 'required', UC::COL_USER_ID => 'required']);
                $this->logExecutionTime($valStart, $action, 'validate');
                $createStart = microtime(true);
                $call = LeadCall::create(['lead_id' => $lead->id, 'subject' => $data['subject'], 'call_type' => $data['call_type'], 'duration' => $req->input('duration'), UC::COL_USER_ID => $data[UC::COL_USER_ID], 'description' => $req->input('description'), 'call_result' => $req->input('call_result')]);
                $this->logExecutionTime($createStart, $action, 'createLeadCall');
                $logStart = microtime(true);
                LeadActivityLog::create([UC::COL_USER_ID => $req->user()->id, 'lead_id' => $lead->id, 'log_type' => 'Create Lead Call', 'remark' => json_encode(['title' => $call->subject])]);
                $this->logExecutionTime($logStart, $action, 'createActivityLog');
                Log::info("[{$class}::{$action}] created", ['lead_id' => $lead->id, 'call_id' => $call->id, 'user_id' => $req->user()->id]);
                return redirect()->back()->with('success', __('Call successfully created!'))->with('status', 'calls');
            } catch (ValidationException $e) {
                Log::debug("[{$class}::{$action}] validation exception", ['lead_id' => $id, 'errors' => $e->getMessage(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return redirect()->back()->with('error', $e->getMessage())->with('status', 'calls');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'message' => $e->getMessage(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const CLL_EDT = 'callEdit';
    public function callEdit(Request $request, string|int $id, string|int $callId): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.calls';
        return $this->measureProfile($action, function () use ($req, $id, $callId, $action, $method, $class, $viewPath) {
            try {
                $t1 = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($t1, $action, 'findLead');
                $t2 = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead call');
                $this->logExecutionTime($t2, $action, 'authorizeOwner');
                $t3 = microtime(true);
                $call = LeadCall::findOrFail($callId);
                $this->logExecutionTime($t3, $action, 'findLeadCall');
                $t4 = microtime(true);
                $users = UserLead::where('lead_id', $lead->id)->get();
                $this->logExecutionTime($t4, $action, 'fetchUserLeads');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id, 'call_id' => $call->id, 'users_count' => $users->count()]);
                return view($viewPath, compact('call', 'lead', 'users'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'call_id' => $callId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'call_id' => $callId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'call_id' => $callId]);
    }

    public const CLL_UPD = 'callUpdate';
    public function callUpdate(Request $request, string|int $id, string|int $callId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $callId, $action, $method, $class) {
            try {
                $t1 = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($t1, $action, 'findLead');
                $t2 = microtime(true);
                self::_authorizeOwner($req, $lead, 'edit lead call');
                $this->logExecutionTime($t2, $action, 'authorizeOwner');
                $t3 = microtime(true);
                $data = $req->validate(['subject' => 'required', 'call_type' => 'required', UC::COL_USER_ID => 'required']);
                $this->logExecutionTime($t3, $action, 'validate');
                $t4 = microtime(true);
                $call = LeadCall::findOrFail($callId);
                $this->logExecutionTime($t4, $action, 'findLeadCall');
                $t5 = microtime(true);
                $call->update(['subject' => $data['subject'], 'call_type' => $data['call_type'], 'duration' => $req->input('duration'), UC::COL_USER_ID => $data[UC::COL_USER_ID], 'description' => $req->input('description'), 'call_result' => $req->input('call_result')]);
                $this->logExecutionTime($t5, $action, 'updateLeadCall');
                Log::info("[{$class}::{$action}] updated", ['lead_id' => $lead->id, 'call_id' => $call->id]);
                return redirect()->back()->with('success', __('Call successfully updated!'))->with('status', 'calls');
            } catch (ValidationException $e) {
                Log::debug("[{$class}::{$action}] validation exception", ['lead_id' => $id, 'call_id' => $callId, 'message' => $e->getMessage(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return redirect()->back()->with('error', $e->getMessage())->with('status', 'calls');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'call_id' => $callId, 'message' => $e->getMessage(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'call_id' => $callId, 'input_keys' => array_keys($req->all()), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'call_id' => $callId]);
    }

    public const CLL_DST = 'callDestroy';
    public function callDestroy(Request $request, string|int $id, string|int $callId): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $callId, $action, $method, $class) {
            try {
                $t1 = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($t1, $action, 'findLead');
                $t2 = microtime(true);
                self::_authorizeOwner($req, $lead, 'delete lead call');
                $this->logExecutionTime($t2, $action, 'authorizeOwner');
                $t3 = microtime(true);
                $call = LeadCall::findOrFail($callId);
                $this->logExecutionTime($t3, $action, 'findLeadCall');
                $t4 = microtime(true);
                $call->delete();
                $this->logExecutionTime($t4, $action, 'deleteLeadCall');
                Log::info("[{$class}::{$action}] deleted", ['lead_id' => $lead->id, 'call_id' => $callId]);
                return redirect()->back()->with('success', __('Call successfully deleted!'))->with('status', 'calls');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'call_id' => $callId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'call_id' => $callId, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'call_id' => $callId]);
    }

    public const EML_CRT = 'emailCreate';
    public function emailCreate(Request $request, string|int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        $viewPath = ViewsConstants::LD . '.emails';
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
            try {
                $t1 = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($t1, $action, 'findLead');
                $t2 = microtime(true);
                self::_authorizeOwner($req, $lead, 'create lead email');
                $this->logExecutionTime($t2, $action, 'authorizeOwner');
                if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                Log::info("[{$class}::{$action}] ready", ['lead_id' => $lead->id]);
                return view($viewPath, compact('lead'));
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] auth exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected exception", ['lead_id' => $id, 'route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id]);
    }

    public const EML_STR = 'emailStore';
    public function emailStore(Request $request, string|int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $req = $request;
        return $this->measureProfile($action, function () use ($req, $id, $action, $class) {
            Log::info("[{$class}::{$action}] start", ['lead_id' => $id]);
            try {
                $findStart = microtime(true);
                $lead = Lead::findOrFail($id);
                $this->logExecutionTime($findStart, $action, 'findLead');
                self::_authorizeOwner($req, $lead, 'create lead email');
                $data = $req->validate(['to' => 'required|email', 'subject' => 'required', 'description' => 'required']);
                $settingsStart = microtime(true);
                $settings = Utility::settings();
                $this->logExecutionTime($settingsStart, $action, 'loadSettings');
                $createStart = microtime(true);
                $email = LeadEmail::create(['lead_id' => $lead->id, 'to' => $data['to'], 'subject' => $data['subject'], 'description' => $data['description']]);
                $this->logExecutionTime($createStart, $action, 'createLeadEmail');
                $smtpError = null;
                try {
                    $sendStart = microtime(true);
                    Mail::to($data['to'])->send(new SendLeadEmail($email->toArray(), $settings));
                    $this->logExecutionTime($sendStart, $action, 'sendLeadEmail');
                } catch (\Exception $e) {
                    $smtpError = __('E-Mail has not been sent due to SMTP configuration');
                    Log::debug("[{$class}::{$action}] smtp send failure", ['lead_id' => $id, 'to' => $data['to'], 'subject' => $data['subject'], 'message' => $e->getMessage()]);
                }
                $activityStart = microtime(true);
                LeadActivityLog::create([UC::COL_USER_ID => $req->user()->id, 'lead_id' => $lead->id, 'log_type' => 'Create Lead Email', 'remark' => json_encode(['title' => 'Create new Lead Email'])]);
                $this->logExecutionTime($activityStart, $action, 'createActivityLog');
                return redirect()->back()->with('success', __('Email successfully created!') . ($smtpError ? '<br><span class="text-danger">' . $smtpError . '</span>' : ''))->with('status', 'emails');
            } catch (ValidationException $e) {
                Log::debug("[{$class}::{$action}] validation error context", ['lead_id' => $id, 'input_keys' => array_keys($req->all()), 'errors' => $e->validator?->errors()?->all()]);
                return redirect()->back()->with('error', $e->getMessage())->with('status', 'emails');
            } catch (AuthorizationException $e) {
                Log::debug("[{$class}::{$action}] authorization denied", ['lead_id' => $id, 'user_id' => $req->user()?->id, 'message' => $e->getMessage()]);
                return defaultPermissionDenial($req, $e, $class . '::' . $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] unexpected error context", ['lead_id' => $id, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'lead_id' => $id, 'to' => $request->input('to'), 'subject' => $request->input('subject')]);
    }

    public function lead(int|string $leadId): Lead
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        return $this->measureProfile($action, function () use ($leadId, $action, $class) {
            Log::info("[{$class}::{$action}] start", ['lead_id_param' => $leadId, 'cached_id' => self::$leadData['id'] ?? null]);
            try {
                if (self::$leadData['lead'] === null || self::$leadData['id'] !== (int)$leadId) {
                    $findStart = microtime(true);
                    $lead = Lead::findOrFail($leadId);
                    $this->logExecutionTime($findStart, $action, 'findLead');
                    self::$leadData['lead'] = $lead;
                    self::$leadData['id'] = $lead->getKey();
                    Log::info("[{$class}::{$action}] cache_refresh", ['lead_id' => self::$leadData['id']]);
                } else Log::info("[{$class}::{$action}] cache_hit", ['lead_id' => self::$leadData['id']]);
                return self::$leadData['lead'];
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] error context", ['lead_id_param' => $leadId, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                throw $e;
            }
        }, ['method' => $method, 'class' => $class, 'lead_id' => $leadId]);
    }

    protected static function _authorize(Request $request, string $permission): void
    {
        if (!$request->user()->can($permission)) {
            throw new AuthorizationException();
        }
    }

    protected static function _authorizeOwner(Request $request, Lead $lead, string $permission): void
    {
        self::_authorize($request, $permission);
        if ($lead[DC::COL_TABLE_CREATOR] !== $request->user()->creatorId()) {
            throw new AuthorizationException();
        }
    }
}
