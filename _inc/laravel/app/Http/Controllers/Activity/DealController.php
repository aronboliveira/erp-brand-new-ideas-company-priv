<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  ActivitiesConstants,
  DatabaseConstants as DC,
  PermissionsConstants as PC,
  ProjectsConstants,
  UsersConstants as UC,
  ViewsConstants as VW
};
use App\Mail\SendDealEmail;
use App\Models\{
  ActivityLog,
  ClientDeal,
  ClientPermission,
  CustomField,
  Deal,
  DealCall,
  DealDiscussion,
  DealEmail,
  DealFile,
  DealTask,
  Label,
  Pipeline,
  ProductService,
  Source,
  Stage,
  User,
  UserDeal,
  Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{
  Request,
  Response,
  JsonResponse,
  RedirectResponse
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
  Auth,
  DB,
  File,
  Mail,
  Log,
  Route,
  Validator,
  View as ViewFacade
};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class DealController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

  use ChecksLogin, ChecksPermissions;

  private const ROUTE_INDEX = DC::TABLE_DEALS . '.index';
  private static ?Deal $dealCache = null;

  public function index(Request $req): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      if (($r = self::guard($req, PC::MNG_DL, self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      try {
        $userStart = microtime(true);
        $user = $req->user();
        $this->logExecutionTime($userStart, $action, 'fetchUser');
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $user?->id, UC::COL_TP => $user[UC::COL_TP], 'method' => $method]);
        $pipeStart = microtime(true);
        $pipeline = $this->getDefaultPipeline($user);
        $this->logExecutionTime($pipeStart, $action, 'getDefaultPipeline');
        $listStart = microtime(true);
        $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $user?->ownerId())->pluck('name', 'id');
        $this->logExecutionTime($listStart, $action, 'loadPipelines');
        $idsStart = microtime(true);
        $ids = $user[UC::COL_TP] === PC::CL ? $user?->clientDeals->pluck('id') : $user?->deals->pluck('id');
        $this->logExecutionTime($idsStart, $action, 'collectDealIds');
        $dealsStart = microtime(true);
        $deals = Deal::whereIn('id', $ids)->where('pipeline_id', $pipeline->id)->get();
        $this->logExecutionTime($dealsStart, $action, 'loadDeals');
        $cntDeal = ['total' => Deal::getDealSummary($deals)];
        Log::debug("[{$class}::{$action}]", ['cntDeal' => $cntDeal]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view(VW::DL . '.' . $action, compact(DC::TABLE_PIPELINES, Str::singular(DC::TABLE_PIPELINES), 'cntDeal'));
        $this->logExecutionTime($renderStart, $action, 'renderIndex');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const DL_LST = 'dealList';
  public function dealList(Request $req): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = VW::DL . '.list';
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      try {
        if (($r = self::guard($req, PC::MNG_DL, self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $userStart = microtime(true);
        $user = $req->user();
        $this->logExecutionTime($userStart, $action, 'fetchUser');
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $user?->id, 'method' => $method]);
        $pipeStart = microtime(true);
        $pipeline = $this->getDefaultPipeline($user);
        $this->logExecutionTime($pipeStart, $action, 'getDefaultPipeline');
        $listStart = microtime(true);
        $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $user?->ownerId())->pluck('name', 'id');
        $this->logExecutionTime($listStart, $action, 'loadPipelines');
        $idsStart = microtime(true);
        $ids = $user[UC::COL_TP] === PC::CL ? $user?->clientDeals->pluck('id') : $user?->deals->pluck('id');
        $this->logExecutionTime($idsStart, $action, 'collectDealIds');
        $sumStart = microtime(true);
        $cntDeal = ['total' => Deal::getDealSummary(Deal::whereIn('id', $ids)->where('pipeline_id', $pipeline->id)->get())];
        $this->logExecutionTime($sumStart, $action, 'computeDealSummary');
        Log::debug("[{$class}::{$action}]", ['cntDeal' => $cntDeal]);
        $ordStart = microtime(true);
        if ($user[UC::COL_TP] === PC::CL) {
          $ordered = Deal::join('client_deals', 'client_deals.deal_id', '=', 'deals.id')
            ->where('client_deals.client_id', $user?->id);
        } else {
          $ordered = Deal::join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
            ->where('user_deals.user_id', $user?->id);
        }
        $deals = $ordered->where(DC::TABLE_DEALS . '.pipeline_id', $pipeline->id)->orderBy(DC::TABLE_DEALS . '.order')->get();
        $this->logExecutionTime($ordStart, $action, 'loadOrderedDeals');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view(VW::DL . '.list', compact(DC::TABLE_PIPELINES, Str::singular(DC::TABLE_PIPELINES), 'deals', 'cntDeal'));
        $this->logExecutionTime($renderStart, $action, 'renderDealList');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $req): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
      if (($r = self::guard($req, 'create deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      try {
        $userStart = microtime(true);
        $user = $req->user();
        $this->logExecutionTime($userStart, $action, 'fetchUser');
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $user?->id, 'method' => $method]);
        $ownerStart = microtime(true);
        $ownerId = $user?->ownerId();
        $this->logExecutionTime($ownerStart, $action, 'getOwnerId');
        $listStart = microtime(true);
        $clients = User::where(DC::COL_TABLE_CREATOR, $ownerId)->where(UC::COL_TP, PC::CL)->pluck('name', 'id');
        $customFields = CustomField::where('module', 'deal')->get();
        $this->logExecutionTime($listStart, $action, 'loadClientsAndCustomFields');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view(VW::DL . '.' . $action, compact(DC::TABLE_CLIENTS, 'customFields'));
        $this->logExecutionTime($renderStart, $action, 'renderCreate');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $req): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      if (($r = self::guard($req, 'create deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      Log::info("[{$class}::{$action}] input", ['input' => $req->all(), 'method' => $method]);
      $valStart = microtime(true);
      $v = Validator::make($req->all(), ['name' => 'required']);
      if ($v->fails()) {
        Log::warning("[{$class}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
        Log::debug("[{$class}::{$action}] validation debug", ['first_error' => $v->errors()->first()]);
        return redirect()->back()->with('error', $v->errors()->first());
      }
      $this->logExecutionTime($valStart, $action, 'validateStore');
      $user = $req->user();
      $pipeStart = microtime(true);
      $pipeline = $this->getDefaultPipeline($user);
      $this->logExecutionTime($pipeStart, $action, 'getDefaultPipeline');
      $stageStart = microtime(true);
      $stage = Stage::where('pipeline_id', $pipeline->id)->first();
      $this->logExecutionTime($stageStart, $action, 'findFirstStage');
      if (!$stage) {
        Log::error("[{$class}::{$action}] missing stage", ['pipeline_id' => $pipeline->id]);
        return redirect()->back()->with('error', __('Please Create Stage for This Pipeline.'));
      }
      try {
        $txnStart = microtime(true);
        DB::transaction(function () use ($req, $user, $pipeline, $stage, $action, $class) {
          $createStart = microtime(true);
          $deal = Deal::create([
            'name' => $req->name,
            'phone' => $req->phone,
            'price' => $req->price ?: 0,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'status' => 'Active',
            DC::COL_TABLE_CREATOR => $user?->ownerId(),
          ]);
          $this->logExecutionTime($createStart, $action, 'createDeal');
          Log::info("[{$class}::{$action}] deal created", ['deal_id' => $deal->id]);
          $cids = array_filter((array)$req->clients);
          $cliStart = microtime(true);
          foreach ($cids as $cid) ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $cid]);
          $this->logExecutionTime($cliStart, $action, 'assignClients');
          Log::info("[{$class}::{$action}] clients assigned", ['deal_id' => $deal->id, DC::TABLE_CLIENTS => $cids]);
          $uids = $user[UC::COL_TP] === PC::CPN ? [$user?->id] : [$user?->id, $user?->ownerId()];
          $userStart = microtime(true);
          for ($i = 0, $n = count($uids); $i < $n; $i++) UserDeal::create([UC::COL_USER_ID => $uids[$i], 'deal_id' => $deal->id]);
          $this->logExecutionTime($userStart, $action, 'assignUsers');
          Log::info("[{$class}::{$action}] users assigned", ['deal_id' => $deal->id, DC::TABLE_USERS => $uids]);
          $cfStart = microtime(true);
          CustomField::saveData($deal, $req->customField ?? []);
          $this->logExecutionTime($cfStart, $action, 'saveCustomFields');
        });
        $this->logExecutionTime($txnStart, $action, 'storeTransaction');
        return redirect()->route(self::ROUTE_INDEX)->with('success', __('Deal successfully created!'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] transaction failed", ['err' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function edit(Request $req, Deal $deal): View|JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.edit';
    return $this->measureProfile($action, function () use ($req, $deal, $action, $method, $class, $viewPath) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      if ($deal->created_by !== $user?->ownerId()) {
        Log::warning("[{$class}::{$action}] unauthorized", [UC::COL_USER_ID => $user?->id, 'deal_id' => $deal->id]);
        return defaultPermissionDenial($req, new AuthorizationException, '', '');
      }
      try {
        Log::info("[{$class}::{$action}] start", ['deal_id' => $deal->id, 'method' => $method]);
        $ownerStart = microtime(true);
        $ownerId = $user?->ownerId();
        $this->logExecutionTime($ownerStart, $action, 'getOwnerId');
        $loadStart = microtime(true);
        $pipelines = Pipeline::where(DC::COL_TABLE_CREATOR, $ownerId)->pluck('name', 'id');
        $sources = Source::where(DC::COL_TABLE_CREATOR, $ownerId)->pluck('name', 'id');
        $products = ProductService::where(DC::COL_TABLE_CREATOR, $ownerId)->pluck('name', 'id');
        $customFields = CustomField::where('module', 'deal')->get();
        $this->logExecutionTime($loadStart, $action, 'loadEditLists');
        $prepStart = microtime(true);
        $deal->sources = explode(',', $deal->sources);
        $deal->products = explode(',', $deal->products);
        $deal->customField = CustomField::getData($deal, 'deal');
        $this->logExecutionTime($prepStart, $action, 'prepareDealExtras');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', DC::TABLE_PIPELINES, 'sources', DC::TABLE_PRODUCTS, 'customFields'));
        $this->logExecutionTime($renderStart, $action, 'renderEdit');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $deal->id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $deal->id]);
  }

  public function update(Request $req, Deal $deal): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $deal, $action, $method, $class) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      if ($deal->created_by !== $user?->ownerId()) {
        Log::warning("[{$class}::{$action}] unauthorized", [UC::COL_USER_ID => $user?->id, 'deal_id' => $deal->id]);
        return defaultPermissionDenial($req, new AuthorizationException, '', '');
      }
      $valStart = microtime(true);
      $v = Validator::make($req->all(), ['name' => 'required|max:20', 'pipeline_id' => 'required']);
      if ($v->fails()) {
        Log::warning("[{$class}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
        Log::debug("[{$class}::{$action}] validation debug", ['first_error' => $v->errors()->first(), 'input_keys' => array_keys($req->all())]);
        return redirect()->back()->with('error', $v->errors()->first());
      }
      $this->logExecutionTime($valStart, $action, 'validateUpdate');
      DB::beginTransaction();
      $txnStart = microtime(true);
      try {
        $prepStart = microtime(true);
        $data = [
          'name' => $req->name,
          'phone' => $req->phone,
          'price' => $req->price ?: 0,
          'pipeline_id' => $req->pipeline_id,
          'stage_id' => $req->stage_id,
          'sources' => implode(',', array_filter($req->sources ?? [])),
          DC::TABLE_PRODUCTS => implode(',', array_filter($req->products ?? [])),
          'notes' => $req->notes,
        ];
        $this->logExecutionTime($prepStart, $action, 'prepareUpdatePayload');
        $updStart = microtime(true);
        $deal->update($data);
        $this->logExecutionTime($updStart, $action, 'persistDealUpdate');
        $cfStart = microtime(true);
        CustomField::saveData($deal, $req->customField ?? []);
        $this->logExecutionTime($cfStart, $action, 'saveCustomFields');
        DB::commit();
        $this->logExecutionTime($txnStart, $action, 'updateTransaction');
        Log::info("[{$class}::{$action}] updated", ['deal_id' => $deal->id]);
        return redirect()->back()->with('success', __('Deal successfully updated!'));
      } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $deal->id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $deal->id]);
  }

  public function destroy(Request $req, Deal $deal): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $deal, $action, $method, $class) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      if (($r = $this->guard($req, 'delete deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      if ($deal->created_by !== $user?->ownerId()) {
        Log::warning("[{$class}::{$action}] unauthorized", [UC::COL_USER_ID => $user?->id, 'deal_id' => $deal->id]);
        return defaultPermissionDenial($req, new AuthorizationException, '', '');
      }
      DB::beginTransaction();
      $txnStart = microtime(true);
      try {
        $disStart = microtime(true);
        DealDiscussion::where('deal_id', $deal->id)->delete();
        $this->logExecutionTime($disStart, $action, 'deleteDiscussions');
        $fileStart = microtime(true);
        DealFile::where('deal_id', $deal->id)->delete();
        $this->logExecutionTime($fileStart, $action, 'deleteFiles');
        $cliStart = microtime(true);
        ClientDeal::where('deal_id', $deal->id)->delete();
        $this->logExecutionTime($cliStart, $action, 'deleteClientLinks');
        $userStart = microtime(true);
        UserDeal::where('deal_id', $deal->id)->delete();
        $this->logExecutionTime($userStart, $action, 'deleteUserLinks');
        $taskStart = microtime(true);
        DealTask::where('deal_id', $deal->id)->delete();
        $this->logExecutionTime($taskStart, $action, 'deleteTasks');
        $logStart = microtime(true);
        ActivityLog::where('deal_id', $deal->id)->delete();
        $this->logExecutionTime($logStart, $action, 'deleteActivityLogs');
        $rowStart = microtime(true);
        $deal->delete();
        $this->logExecutionTime($rowStart, $action, 'deleteDealRow');
        DB::commit();
        $this->logExecutionTime($txnStart, $action, 'destroyTransaction');
        Log::info("[{$class}::{$action}] deleted", ['deal_id' => $deal->id]);
        return redirect()->route(self::ROUTE_INDEX)->with('success', __('Deal successfully deleted!'));
      } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $deal->id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $deal->id]);
  }

  public function order(Request $req): JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      if (($r = $this->guard($req, 'move deal', '')) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
      Log::info("[{$class}::{$action}] start", ['method' => $method, 'input_keys' => array_keys($req->all()), UC::COL_USER_ID => $user?->id]);
      $valStart = microtime(true);
      $v = Validator::make($req->all(), ['deal_id' => 'required', 'stage_id' => 'required', 'order' => 'required|array']);
      if ($v->fails()) return response()->json(['error' => $v->errors()->first()], 400);
      $this->logExecutionTime($valStart, $action, 'validateOrderPayload');
      DB::beginTransaction();
      $txnStart = microtime(true);
      try {
        $loadStart = microtime(true);
        $deal = Deal::findOrFail($req->deal_id);
        $clients = ClientDeal::where('deal_id', $deal->id)->pluck('client_id')->toArray();
        $dealUsers = $deal->users->pluck('id')->toArray();
        $usrs = User::whereIn('id', array_merge($dealUsers, $clients))->pluck('email', 'id')->toArray();
        $this->logExecutionTime($loadStart, $action, 'loadDealAndParticipants');
        if ($deal->stage_id !== $req->stage_id) {
          $stageStart = microtime(true);
          $newStage = Stage::findOrFail($req->stage_id);
          ActivityLog::create([UC::COL_USER_ID => $user?->id, 'deal_id' => $deal->id, 'log_type' => 'Move', 'remark' => json_encode(['title' => $deal->name, 'oldStatus' => $deal->stage->name, 'newStatus' => $newStage->name])]);
          Utility::sendEmailTemplate('Move Deal', $usrs, ['deal_name' => $deal->name, 'deal_pipeline' => $deal->pipeline->name, 'deal_stage' => $deal->stage->name, 'deal_status' => $deal->status, 'deal_price' => $user?->priceFormat($deal->price), 'deal_oldStage' => $deal->stage->name, 'deal_newStage' => $newStage->name]);
          $this->logExecutionTime($stageStart, $action, 'handleStageMove');
          Log::info("[{$class}::{$action}] moved", ['deal_id' => $deal->id, 'new_stage' => $newStage->id]);
        }
        $orderStart = microtime(true);
        foreach ($req->order as $position => $item) {
          $d = Deal::findOrFail($item);
          $d->order = $position;
          $d->stage_id = $req->stage_id;
          $d->save();
        }
        $this->logExecutionTime($orderStart, $action, 'reorderDealsLoop');
        DB::commit();
        $this->logExecutionTime($txnStart, $action, 'orderTransaction');
        return response()->json(['success' => true]);
      } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $req->deal_id, 'stage_id' => $req->stage_id, 'order_count' => is_array($req->order) ? count($req->order) : null]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('An error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function labels(Request $req, int|string $id): View|JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.labels';
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $user?->id, 'deal_id' => $id, 'method' => $method]);
        $loadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($loadStart, $action, 'loadDeal');
        if ($deal->created_by !== $user?->ownerId()) {
          Log::warning("[{$class}::{$action}] unauthorized", [UC::COL_USER_ID => $user?->id, 'deal_id' => $id]);
          return response()->json(['error' => __('Permission Denied.')], 401);
        }
        $listStart = microtime(true);
        $labels = Label::where('pipeline_id', $deal->pipeline_id)->where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
        $selected = $deal->labels()->pluck('id')->toArray();
        $this->logExecutionTime($listStart, $action, 'loadLabelsAndSelected');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', 'labels', 'selected'));
        $this->logExecutionTime($renderStart, $action, 'renderLabels');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const LB_STR = 'labelStore';
  public function labelStore(Request $req, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
      if (($r = self::guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        $loadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($loadStart, $action, 'loadDeal');
        if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
        DB::beginTransaction();
        $persistStart = microtime(true);
        $deal->labels = $req->labels ? implode(',', $req->labels) : null;
        $deal->save();
        $this->logExecutionTime($persistStart, $action, 'persistLabels');
        DB::commit();
        Log::info("[{$class}::{$action}] labels updated", ['deal_id' => $deal->id, UC::COL_USER_ID => $user?->id]);
        return redirect()->back()->with('success', __('Labels successfully updated!'));
      } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const USR_EDT = 'userEdit';
  public function userEdit(Request $req, int|string $id): View|JsonResponse|RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.users';
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $viewPath) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $user?->id, 'deal_id' => $id, 'method' => $method]);
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
        $usersStart = microtime(true);
        $users = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->where(UC::COL_TP, '!=', PC::CL)->whereNotIn('id', function ($q) use ($id) {
          $q->select(UC::COL_USER_ID)->from('user_deals')->where('deal_id', $id);
        })->get()->filter(fn($u) => $u->can(PC::MNG_DL))->pluck('name', 'id')->prepend(__('Select Users'), '');
        $this->logExecutionTime($usersStart, $action, 'loadAssignableUsers');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', DC::TABLE_USERS));
        $this->logExecutionTime($renderStart, $action, 'renderUserEdit');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const USR_UPD = 'userUpdate';
  public function userUpdate(Request $req, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class) {
      if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
        $uids = array_filter((array)$req->users);
        $emailLoadStart = microtime(true);
        $emails = User::whereIn('id', $uids)->pluck('email', 'id')->toArray();
        $this->logExecutionTime($emailLoadStart, $action, 'loadEmails');
        DB::beginTransaction();
        $assignStart = microtime(true);
        foreach ($uids as $uid) UserDeal::create(['deal_id' => $deal->id, UC::COL_USER_ID => $uid]);
        $this->logExecutionTime($assignStart, $action, 'assignUsers');
        if ($emails) {
          $emailSendStart = microtime(true);
          $dArr = ['deal_name' => $deal->name, 'deal_pipeline' => $deal->pipeline->name, 'deal_stage' => $deal->stage->name, 'deal_status' => $deal->status, 'deal_price' => $user?->priceFormat($deal->price)];
          $resp = Utility::sendEmailTemplate('Assign Deal', $emails, $dArr);
          $this->logExecutionTime($emailSendStart, $action, 'sendAssignEmails');
          Log::info("[{$class}::{$action}] users assigned", ['deal_id' => $deal->id, DC::TABLE_USERS => $uids]);
        }
        DB::commit();
        return redirect()->back()->with('success', __('Users successfully updated!') . (!empty($resp['error']) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
      } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id, 'method' => $method]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const USR_DST = 'userDestroy';
  public function userDestroy(Request $req, int|string $id, int|string $userId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($req, $id, $userId, $action, $method, $class) {
      if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;
      try {
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
        $delStart = microtime(true);
        UserDeal::where([['deal_id', $id], [UC::COL_USER_ID, $userId]])->delete();
        $this->logExecutionTime($delStart, $action, 'deleteUserLink');
        Log::info("[{$class}::{$action}] user removed", ['deal_id' => $id, UC::COL_USER_ID => $userId, 'method' => $method]);
        return redirect()->back()->with('success', __('User successfully deleted!'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id, UC::COL_USER_ID => $userId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'user_id' => $userId]);
  }

  public const CL_EDT = 'clientEdit';
  public function clientEdit(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.clients';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
      try {
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) {
          $this->logExecutionTime($authStart, $action, 'authorizeOwner');
          return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
        }
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $listStart = microtime(true);
        $exclude = ClientDeal::where('deal_id', $id)->pluck('client_id');
        $clients = User::where(DC::COL_TABLE_CREATOR, $request->user()->ownerId())->where(UC::COL_TP, PC::CL)->whereNotIn('id', $exclude)->pluck('name', 'id');
        $this->logExecutionTime($listStart, $action, 'loadEligibleClients');
        Log::info("[{$class}::{$action}] fetched clients", ['count' => $clients->count()]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', DC::TABLE_CLIENTS));
        $this->logExecutionTime($renderStart, $action, 'renderClients');
        return $response;
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const CL_UPD = 'clientUpdate';
  public function clientUpdate(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}]", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $clients = array_filter($request->clients ?? []);
        $txnStart = microtime(true);
        DB::transaction(function () use ($deal, $clients, $action) {
          $assignStart = microtime(true);
          foreach ($clients as $cid) ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $cid]);
          $this->logExecutionTime($assignStart, $action, 'assignClientsLoop');
        });
        $this->logExecutionTime($txnStart, $action, 'clientUpdateTransaction');
        Log::info("[{$class}::{$action}] assigned clients", ['deal_id' => $deal->id, DC::TABLE_CLIENTS => $clients]);
        return $clients ? redirect()->back()->with('success', __('Clients successfully updated!'))->with('status', DC::TABLE_CLIENTS) : redirect()->back()->with('error', __('Please Select Valid Clients!'))->with('status', DC::TABLE_CLIENTS);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const CL_DST = 'clientDestroy';
  public function clientDestroy(Request $request, int|string $id, int|string $clientId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $clientId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'client_id' => $clientId, 'method' => $method]);
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $delStart = microtime(true);
        ClientDeal::where('deal_id', $deal->id)->where('client_id', $clientId)->delete();
        $this->logExecutionTime($delStart, $action, 'deleteClientLink');
        Log::info("[{$class}::{$action}] removed client", ['deal_id' => $deal->id, 'client_id' => $clientId]);
        return redirect()->back()->with('success', __('Client successfully deleted!'))->with('status', DC::TABLE_CLIENTS);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'client_id' => $clientId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'client_id' => $clientId]);
  }

  public const PRD_EDT = 'productEdit';
  public function productEdit(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.products';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
      try {
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) {
          $this->logExecutionTime($authStart, $action, 'authorizeOwner');
          return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
        }
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $listStart = microtime(true);
        $excluded = explode(',', $deal->products);
        $products = ProductService::where(DC::COL_TABLE_CREATOR, $request->user()->ownerId())->whereNotIn('id', $excluded)->pluck('name', 'id');
        $this->logExecutionTime($listStart, $action, 'loadEligibleProducts');
        Log::info("[{$class}::{$action}] fetched products", ['count' => $products->count()]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', DC::TABLE_PRODUCTS));
        $this->logExecutionTime($renderStart, $action, 'renderProducts');
        return $response;
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const PRD_UPD = 'productUpdate';
  public function productUpdate(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $new = array_filter($request->products ?? []);
        if (!$new) return redirect()->back()->with('error', __('Please Select Valid Product!'))->with('status', 'general');
        $mergeStart = microtime(true);
        $old = explode(',', $deal->products);
        $deal->products = implode(',', array_merge($old, $new));
        $deal->save();
        $this->logExecutionTime($mergeStart, $action, 'mergeAndSaveProducts');
        $namesLoadStart = microtime(true);
        $names = ProductService::whereIn('id', $new)->pluck('name')->implode(',');
        $this->logExecutionTime($namesLoadStart, $action, 'loadProductNames');
        $activityStart = microtime(true);
        ActivityLog::create([UC::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Add Product', 'remark' => json_encode(['title' => $names])]);
        $this->logExecutionTime($activityStart, $action, 'createActivityLog');
        Log::info("[{$class}::{$action}] products added", ['deal_id' => $deal->id, DC::TABLE_PRODUCTS => $new]);
        return redirect()->back()->with('success', __('Products successfully updated!'))->with('status', DC::TABLE_PRODUCTS);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const PRD_DST = 'productDestroy';
  public function productDestroy(Request $request, int|string $id, int|string $productId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $productId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'product_id' => $productId, 'method' => $method]);
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $updateStart = microtime(true);
        $list = array_filter(explode(',', $deal->products), fn($p) => $p != $productId);
        $deal->products = implode(',', $list);
        $deal->save();
        $this->logExecutionTime($updateStart, $action, 'removeProductAndSave');
        Log::info("[{$class}::{$action}] removed product", ['deal_id' => $deal->id, 'product_id' => $productId]);
        return redirect()->back()->with('success', __('Products successfully deleted!'))->with('status', DC::TABLE_PRODUCTS);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'product_id' => $productId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'product_id' => $productId]);
  }

  public const FL_UPL = 'fileUpload';
  public function fileUpload(Request $request, int|string $id): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) throw new AuthorizationException;
        $valStart = microtime(true);
        $request->validate(['file' => 'required|file']);
        $this->logExecutionTime($valStart, $action, 'validateUpload');
        $size = $request->file('file')->getSize();
        $limitStart = microtime(true);
        $lim = Utility::updateStorageLimit($request->user()->creatorId(), $size);
        $this->logExecutionTime($limitStart, $action, 'updateStorageLimit');
        $orig = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $request->file('file')->getClientOriginalName());
        $path = "{$id}_" . md5(time()) . "_{$orig}";
        $createStart = microtime(true);
        $file = DealFile::create(['deal_id' => $id, 'file_name' => $orig, 'file_path' => $path]);
        $this->logExecutionTime($createStart, $action, 'createDealFileRow');
        if ($lim === 1) {
          $storeStart = microtime(true);
          $request->file->storeAs('deal_files', $path);
          $this->logExecutionTime($storeStart, $action, 'storeUploadedFile');
        }
        $logStart = microtime(true);
        ActivityLog::create([UC::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Upload File', 'remark' => json_encode(['file_name' => $orig])]);
        $this->logExecutionTime($logStart, $action, 'createActivityLog');
        $routeStart = microtime(true);
        $download = route(DC::TABLE_DEALS . '.file.download', [$id, $file->id]);
        $delete = route(DC::TABLE_DEALS . '.file.delete', [$id, $file->id]);
        $this->logExecutionTime($routeStart, $action, 'buildResponseRoutes');
        Log::info("[{$class}::{$action}] file stored", ['file_id' => $file->id, 'limit' => $lim]);
        return response()->json(['is_success' => true, 'download' => $download, 'delete' => $delete], 200);
      } catch (AuthorizationException $e) {
        return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const FL_DWN = 'fileDownload';
  public function fileDownload(Request $request, int|string $id, int|string $fileId): BinaryFileResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $fileId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'file_id' => $fileId, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $fileStart = microtime(true);
        $file = DealFile::findOrFail($fileId);
        $this->logExecutionTime($fileStart, $action, 'loadDealFile');
        $pathStart = microtime(true);
        $full = storage_path("deal_files/{$file->file_path}");
        $this->logExecutionTime($pathStart, $action, 'resolveFilePath');
        if (!file_exists($full)) {
          Log::warning("[{$class}::{$action}] missing file on disk", ['resolved_path' => $full, 'file_id' => $fileId]);
          return redirect()->back()->with('error', __('File does not exist.'));
        }
        $downloadStart = microtime(true);
        $response = Response::download($full, $file->file_name, ['Content-Length:' . filesize($full)]);
        $this->logExecutionTime($downloadStart, $action, 'streamDownload');
        return $response;
      } catch (AuthorizationException $e) {
        return redirect()->back()->with('error', __('Permission Denied.'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'file_id' => $fileId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return redirect()->back()->with('error', __('File does not exist.'));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'file_id' => $fileId]);
  }

  public const FL_DEL = 'fileDelete';
  public function fileDelete(Request $request, int|string $id, int|string $fileId): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $fileId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'file_id' => $fileId, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) throw new AuthorizationException;
        $fileStart = microtime(true);
        $file = DealFile::findOrFail($fileId);
        $this->logExecutionTime($fileStart, $action, 'loadDealFile');
        $quotaStart = microtime(true);
        Utility::changeStorageLimit($request->user()->creatorId(), "deal_files/{$file->file_path}");
        $this->logExecutionTime($quotaStart, $action, 'changeStorageLimit');
        $pathStart = microtime(true);
        $full = storage_path("deal_files/{$file->file_path}");
        $this->logExecutionTime($pathStart, $action, 'resolveFilePath');
        if (File::exists($full)) {
          $delStart = microtime(true);
          File::delete($full);
          $this->logExecutionTime($delStart, $action, 'deleteFileFromDisk');
        } else Log::notice("[{$class}::{$action}] file missing on disk", ['resolved_path' => $full, 'file_id' => $fileId]);
        $dbDelStart = microtime(true);
        $file->delete();
        $this->logExecutionTime($dbDelStart, $action, 'deleteFileRow');
        Log::info("[{$class}::{$action}] deleted file", ['file_id' => $fileId]);
        return response()->json(['is_success' => true], 200);
      } catch (AuthorizationException $e) {
        return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'file_id' => $fileId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'file_id' => $fileId]);
  }

  public const NT_STR = 'noteStore';
  public function noteStore(Request $request, int|string $id): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) throw new AuthorizationException;
        $updStart = microtime(true);
        $deal->update(['notes' => $request->notes]);
        $this->logExecutionTime($updStart, $action, 'updateNotes');
        Log::info("[{$class}::{$action}] saved note", ['deal_id' => $id]);
        return response()->json(['is_success' => true, 'success' => __('Note successfully saved!')], 200);
      } catch (AuthorizationException $e) {
        return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const TSK_CRT = 'taskCreate';
  public function taskCreate(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.tasks';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'create task', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, ['deal' => $deal, 'priorities' => DealTask::$priorities, 'status' => DealTask::$status]);
        $this->logExecutionTime($renderStart, $action, 'renderTaskCreate');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('Permission Denied.')], 401);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const TSK_STR = 'taskStore';
  public function taskStore(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method, 'input_keys' => array_keys($request->all())]);
        $dealLoadStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealLoadStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'create task', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $valStart = microtime(true);
        $v = Validator::make($request->all(), ['name' => 'required', 'date' => 'required', 'time' => 'required', ProjectsConstants::COL_PRT => 'required', 'status' => 'required']);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());
        $this->logExecutionTime($valStart, $action, 'validateTaskPayload');
        $txnStart = microtime(true);
        DB::transaction(function () use ($request, $deal, $action) {
          $createStart = microtime(true);
          $task = DealTask::create(['deal_id' => $deal->id, 'name' => $request->name, 'date' => $request->date, 'time' => date('H:i:s', strtotime("{$request->date} {$request->time}")), ProjectsConstants::COL_PRT => $request[ProjectsConstants::COL_PRT], 'status' => $request->status]);
          $this->logExecutionTime($createStart, $action, 'createTaskRow');
          $logStart = microtime(true);
          ActivityLog::create([UC::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Create Task', 'remark' => json_encode(['title' => $task->name])]);
          $this->logExecutionTime($logStart, $action, 'createActivityLog');
        });
        $this->logExecutionTime($txnStart, $action, 'taskStoreTransaction');
        return redirect()->back()->with('success', __('Task successfully created!'))->with('status', 'tasks');
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return redirect()->back()->with('error', $e instanceof \InvalidArgumentException ? $e->getMessage() : __('Permission Denied.'))->with('status', 'tasks');
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const TSK_SHW = 'taskShow';
  public function taskShow(Request $request, int|string $id, int|string $taskId): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.tasksShow';
    return $this->measureProfile($action, function () use ($request, $id, $taskId, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'view task', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $taskStart = microtime(true);
        $task = DealTask::findOrFail($taskId);
        $this->logExecutionTime($taskStart, $action, 'loadTask');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', 'task'));
        $this->logExecutionTime($renderStart, $action, 'renderTaskShow');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'task_id' => $taskId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('Permission Denied.')], 401);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'task_id' => $taskId]);
  }

  public const TSK_EDT = 'taskEdit';
  public function taskEdit(Request $request, int|string $id, int|string $taskId): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.tasks';
    return $this->measureProfile($action, function () use ($request, $id, $taskId, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit task', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $taskStart = microtime(true);
        $task = DealTask::findOrFail($taskId);
        $this->logExecutionTime($taskStart, $action, 'loadTask');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, ['deal' => $deal, 'task' => $task, 'priorities' => DealTask::$priorities, 'status' => DealTask::$status]);
        $this->logExecutionTime($renderStart, $action, 'renderTaskEdit');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'task_id' => $taskId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('Permission Denied.')], 401);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'task_id' => $taskId]);
  }

  public const TSK_UPD = 'taskUpdate';
  public function taskUpdate(Request $request, int|string $id, int|string $taskId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $taskId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId, 'method' => $method, 'input_keys' => array_keys($request->all())]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit task', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $valStart = microtime(true);
        $v = Validator::make($request->all(), ['name' => 'required', 'date' => 'required', 'time' => 'required', ProjectsConstants::COL_PRT => 'required', 'status' => 'required']);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());
        $this->logExecutionTime($valStart, $action, 'validateTaskUpdate');
        $updStart = microtime(true);
        DealTask::findOrFail($taskId)->update(['name' => $request->name, 'date' => $request->date, 'time' => date('H:i:s', strtotime("{$request->date} {$request->time}")), ProjectsConstants::COL_PRT => $request[ProjectsConstants::COL_PRT], 'status' => $request->status]);
        $this->logExecutionTime($updStart, $action, 'persistTaskUpdate');
        return redirect()->back()->with('success', __('Task successfully updated!'))->with('status', 'tasks');
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id, 'task_id' => $taskId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return redirect()->back()->with('error', $e instanceof \InvalidArgumentException ? $e->getMessage() : __('Permission Denied.'))->with('status', 'tasks');
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'task_id' => $taskId]);
  }

  public const TSK_UPD_STT = 'taskUpdateStatus';
  public function taskUpdateStatus(Request $request, int|string $id, int|string $taskId): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $taskId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId, 'method' => $method, 'input_keys' => array_keys($request->all())]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'edit task', self::ROUTE_INDEX)) instanceof RedirectResponse) throw new AuthorizationException;
        $valStart = microtime(true);
        $v = Validator::make($request->all(), ['status' => 'required']);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());
        $this->logExecutionTime($valStart, $action, 'validateStatusPayload');
        $taskStart = microtime(true);
        $task = DealTask::findOrFail($taskId);
        $this->logExecutionTime($taskStart, $action, 'loadTask');
        $saveStart = microtime(true);
        $task->status = $request->status ? 0 : 1;
        $task->save();
        $this->logExecutionTime($saveStart, $action, 'persistTaskStatus');
        return response()->json(['is_success' => true, 'status' => $task->status, 'status_label' => __(DealTask::$status[$task->status])], 200);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id, 'task_id' => $taskId, 'method' => $method]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
        return response()->json(['is_success' => false, 'error' => $e instanceof \InvalidArgumentException ? $e->getMessage() : __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'task_id' => $taskId]);
  }

  public const TSK_DST = 'taskDestroy';
  public function taskDestroy(Request $request, int|string $id, int|string $taskId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $taskId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId, 'method' => $method]);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (($r = self::guard($request, 'delete task', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $delStart = microtime(true);
        DealTask::findOrFail($taskId)->delete();
        $this->logExecutionTime($delStart, $action, 'deleteTaskRow');
        return redirect()->back()->with('success', __('Task successfully deleted!'))->with('status', 'tasks');
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $id, 'task_id' => $taskId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'task_id' => $taskId]);
  }

  public const SRC_EDT = 'sourceEdit';
  public function sourceEdit(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.sources';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) {
          $this->logExecutionTime($authStart, $action, 'authorizeOwner');
          return response()->json(['error' => __('Permission Denied.')], 401);
        }
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $listStart = microtime(true);
        $sources = Source::where(DC::COL_TABLE_CREATOR, $request->user()->ownerId())->pluck('name', 'id');
        $selected = $deal->sources()?->pluck('id')->toArray() ?: [];
        $this->logExecutionTime($listStart, $action, 'loadSources');
        Log::info("[{$class}::{$action}] fetched sources", ['count' => count($sources)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', 'sources', 'selected'));
        $this->logExecutionTime($renderStart, $action, 'renderSources');
        return $response;
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const SRC_UPD = 'sourceUpdate';
  public function sourceUpdate(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}]", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'input' => $request->all(), 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $prepStart = microtime(true);
        $list = array_filter($request->sources ?? []);
        $this->logExecutionTime($prepStart, $action, 'prepareSourcesList');
        $txnStart = microtime(true);
        DB::transaction(fn() => $deal->update(['sources' => implode(',', $list)]));
        $this->logExecutionTime($txnStart, $action, 'persistSourcesTransaction');
        $actStart = microtime(true);
        ActivityLog::create([UC::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Update Sources', 'remark' => json_encode(['title' => 'Update Sources'])]);
        $this->logExecutionTime($actStart, $action, 'createActivityLog');
        Log::info("[{$class}::{$action}] updated sources", ['deal_id' => $deal->id, 'sources' => $list]);
        return redirect()->back()->with('success', __('Sources successfully updated!'))->with('status', 'sources');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const SRC_DST = 'sourceDestroy';
  public function sourceDestroy(Request $request, int|string $id, int|string $sourceId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $sourceId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'source_id' => $sourceId, 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $authStart = microtime(true);
        if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwner');
        $updStart = microtime(true);
        $remaining = array_filter(explode(',', $deal->sources), fn($s) => $s != (string)$sourceId);
        $deal->update(['sources' => implode(',', $remaining)]);
        $this->logExecutionTime($updStart, $action, 'removeSourcePersist');
        Log::info("[{$class}::{$action}] removed source", ['deal_id' => $deal->id, 'source_id' => $sourceId]);
        return redirect()->back()->with('success', __('Sources successfully deleted!'))->with('status', 'sources');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'source_id' => $sourceId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'source_id' => $sourceId]);
  }

  public function permission(Request $request, int|string $id, int|string $clientId): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.permissions';
    return $this->measureProfile($action, function () use ($request, $id, $clientId, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'client_id' => $clientId, 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $clientStart = microtime(true);
        $client = User::findOrFail($clientId);
        $this->logExecutionTime($clientStart, $action, 'loadClient');
        $permStart = microtime(true);
        $perm = $client->clientPermission($deal->id);
        $selected = $perm ? explode(',', $perm->permissions) : [];
        $permissions = Deal::$permissions;
        $this->logExecutionTime($permStart, $action, 'loadPermissions');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', PC::CL, 'selected', DC::TABLE_PERMISSIONS));
        $this->logExecutionTime($renderStart, $action, 'renderPermissionView');
        return $response;
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'client_id' => $clientId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'client_id' => $clientId]);
  }

  public const PRM_STR = 'permissionStore';
  public function permissionStore(Request $request, int|string $id, int|string $clientId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $clientId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}]", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'client_id' => $clientId, 'input' => $request->all(), 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $clientStart = microtime(true);
        $client = User::findOrFail($clientId);
        $this->logExecutionTime($clientStart, $action, 'loadClient');
        $permStart = microtime(true);
        $perm = $client->clientPermission($deal->id);
        $this->logExecutionTime($permStart, $action, 'loadClientPermission');
        $prepStart = microtime(true);
        $list = array_filter($request->permissions ?? []);
        $this->logExecutionTime($prepStart, $action, 'preparePermissionsList');
        $txnStart = microtime(true);
        DB::transaction(function () use ($perm, $list, $clientId, $deal, $action) {
          $persistStart = microtime(true);
          $perm ? $perm->update([DC::TABLE_PERMISSIONS => implode(',', $list)]) : ($list && ClientPermission::create(['client_id' => $clientId, 'deal_id' => $deal->id, DC::TABLE_PERMISSIONS => implode(',', $list)]));
          $this->logExecutionTime($persistStart, $action, 'persistPermissions');
        });
        $this->logExecutionTime($txnStart, $action, 'permissionStoreTransaction');
        Log::info("[{$class}::{$action}] updated permissions", ['deal_id' => $deal->id, 'client_id' => $clientId, DC::TABLE_PERMISSIONS => $list]);
        return redirect()->back()->with('success', __(ucfirst(DC::TABLE_PERMISSIONS) . ' successfully updated!'))->with('status', DC::TABLE_CLIENTS);
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'client_id' => $clientId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'client_id' => $clientId]);
  }

  public const USR_JSON = 'jsonUser';
  public function jsonUser(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", ['deal_id' => $request->input('deal_id'), 'method' => $method, 'input_keys' => array_keys($request->all())]);
        $id = $request->input('deal_id');
        if (!$id) return response()->json([], 200);
        $loadStart = microtime(true);
        $users = Deal::findOrFail($id)->users->pluck('name', 'id');
        $this->logExecutionTime($loadStart, $action, 'loadDealUsers');
        return response()->json($users, 200);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['message' => $e->getMessage(), 'deal_id' => $request->input('deal_id')]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json([], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const PPL_CHG = 'changePipeline';
  public function changePipeline(Request $request): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $action, $method, $class) {
      try {
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $user = $request->user();
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $user?->id, 'input_keys' => array_keys($request->all()), 'method' => $method]);
        $valStart = microtime(true);
        $v = Validator::make($request->all(), ['defaultPipelineId' => 'required|integer|min:1']);
        if ($v->fails()) {
          Log::warning("[{$class}::{$action}] validation failed", ['errors' => $v->errors()->all()]);
          return redirect()->back()->with('error', $v->errors()->first());
        }
        $this->logExecutionTime($valStart, $action, 'validatePayload');
        $updStart = microtime(true);
        $user->update(['default_pipeline' => $request->input('defaultPipelineId')]);
        $this->logExecutionTime($updStart, $action, 'persistDefaultPipeline');
        Log::info("[{$class}::{$action}] updated", [UC::COL_USER_ID => $user?->id, 'default_pipeline' => $user?->default_pipeline]);
        return redirect()->back();
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage()]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public const DSC_CRT = 'discussionCreate';
  public function discussionCreate(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.discussions';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal'));
        $this->logExecutionTime($renderStart, $action, 'renderDiscussionCreate');
        return $response;
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const DSC_STR = 'discussionStore';
  public function discussionStore(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method, 'input_keys' => array_keys($request->all())]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $createStart = microtime(true);
        $disc = DealDiscussion::create(['deal_id' => $deal->id, 'comment' => $request->input('comment'), DC::COL_TABLE_CREATOR => $request->user()->id]);
        $this->logExecutionTime($createStart, $action, 'createDiscussionRow');
        Log::info("[{$class}::{$action}] added discussion", ['discussion_id' => $disc->id]);
        return redirect()->back()->with('success', __('Message successfully added!'))->with('status', 'discussion');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const STT_CHG = 'changeStatus';
  public function changeStatus(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'status' => $request->input('dealStatus'), 'method' => $method]);
        if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $updStart = microtime(true);
        $deal->update(['status' => $request->input('dealStatus')]);
        $this->logExecutionTime($updStart, $action, 'persistStatus');
        return redirect()->back();
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'status' => $request->input('dealStatus')]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const CAL_CRT = 'callCreate';
  public function callCreate(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.calls';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        if (($r = self::guard($request, 'create deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $usersStart = microtime(true);
        $users = UserDeal::where('deal_id', $id)->pluck(UC::COL_USER_ID);
        $this->logExecutionTime($usersStart, $action, 'loadDealUsers');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', DC::TABLE_USERS));
        $this->logExecutionTime($renderStart, $action, 'renderCallCreate');
        return $response;
      } catch (AuthorizationException $e) {
        return response()->json(['error' => __('Permission Denied.')], 401);
      # PULL REQUEST START
      } catch (ModelNotFoundException $e) {
        // Retorna 404 quando o deal não é encontrado
        Log::warning("[{$class}::{$action}] deal não encontrado", ['deal_id' => $id]);
        return response()->json(['error' => __('Deal not found.')], 404);
      # PULL REQUEST END
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('An unexpected error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const CAL_STR = 'callStore';
  public function callStore(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method, 'input' => $request->all()]);
        if (($r = self::guard($request, 'create deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $valStart = microtime(true);
        try {
          $v = $request->validate(['subject' => 'required', 'callType' => 'required', UC::COL_USER_ID => 'required']);
        } catch (ValidationException $e) {
          $this->logExecutionTime($valStart, $action, 'validatePayload');
          Log::warning("[{$class}::{$action}] validation failed", ['errors' => $e->errors()]);
          Log::debug("[{$class}::{$action}] debug validation", ['message' => $e->getMessage(), 'method' => $method]);
          return redirect()->back()->with('error', $e->getMessage())->with('status', 'calls');
        }
        $this->logExecutionTime($valStart, $action, 'validatePayload');
        $createStart = microtime(true);
        $call = DealCall::create(['deal_id' => $deal->id, 'subject' => $v['subject'], 'call_type' => $v['callType'], 'duration' => $request->input('duration'), UC::COL_USER_ID => $v[UC::COL_USER_ID], 'description' => $request->input('description'), 'call_result' => $request->input('callResult')]);
        $this->logExecutionTime($createStart, $action, 'createDealCall');
        $actStart = microtime(true);
        ActivityLog::create([UC::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Create Deal Call', 'remark' => json_encode(['title' => 'Create new Deal Call'])]);
        $this->logExecutionTime($actStart, $action, 'createActivityLog');
        Log::info("[{$class}::{$action}] created call", ['call_id' => $call->id]);
        return redirect()->back()->with('success', __('Call successfully created!'))->with('status', 'calls');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method, 'input_keys' => array_keys($request->all())]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const CAL_EDT = 'callEdit';
  public function callEdit(Request $request, int|string $id, int|string $callId): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.calls';
    return $this->measureProfile($action, function () use ($request, $id, $callId, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'call_id' => $callId, 'method' => $method]);
        if (($r = self::guard($request, 'edit deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $callStart = microtime(true);
        $call = DealCall::findOrFail($callId);
        $this->logExecutionTime($callStart, $action, 'loadCall');
        $usersStart = microtime(true);
        $users = UserDeal::where('deal_id', $id)->pluck(UC::COL_USER_ID);
        $this->logExecutionTime($usersStart, $action, 'loadDealUsers');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', 'call', DC::TABLE_USERS));
        $this->logExecutionTime($renderStart, $action, 'renderCallEdit');
        return $response;
      } catch (AuthorizationException $e) {
        return response()->json(['error' => __('Permission Denied.')], 401);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'call_id' => $callId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('An unexpected error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'call_id' => $callId]);
  }

  public const CAL_UPD = 'callUpdate';
  public function callUpdate(Request $request, int|string $id, int|string $callId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $callId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'call_id' => $callId, 'method' => $method, 'input' => $request->all()]);
        if (($r = self::guard($request, 'edit deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $valStart = microtime(true);
        try {
          $v = $request->validate(['subject' => 'required', 'callType' => 'required', UC::COL_USER_ID => 'required']);
        } catch (ValidationException $e) {
          $this->logExecutionTime($valStart, $action, 'validatePayload');
          Log::warning("[{$class}::{$action}] validation failed", ['errors' => $e->errors()]);
          Log::debug("[{$class}::{$action}] debug validation", ['message' => $e->getMessage(), 'method' => $method]);
          return redirect()->back()->with('error', $e->getMessage())->with('status', 'calls');
        }
        $this->logExecutionTime($valStart, $action, 'validatePayload');
        $updStart = microtime(true);
        DealCall::findOrFail($callId)->update(['subject' => $v['subject'], 'call_type' => $v['callType'], 'duration' => $request->input('duration'), UC::COL_USER_ID => $v[UC::COL_USER_ID], 'description' => $request->input('description'), 'call_result' => $request->input('callResult')]);
        $this->logExecutionTime($updStart, $action, 'updateDealCall');
        Log::info("[{$class}::{$action}] updated call", ['call_id' => $callId]);
        return redirect()->back()->with('success', __('Call successfully updated!'))->with('status', 'calls');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'call_id' => $callId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'call_id' => $callId]);
  }

  public const CAL_DST = 'callDestroy';
  public function callDestroy(Request $request, int|string $id, int|string $callId): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $callId, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'call_id' => $callId, 'method' => $method]);
        if (($r = self::guard($request, 'delete deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $findStart = microtime(true);
        $call = DealCall::findOrFail($callId);
        $this->logExecutionTime($findStart, $action, 'loadDealCall');
        $delStart = microtime(true);
        $call->delete();
        $this->logExecutionTime($delStart, $action, 'deleteDealCall');
        Log::info("[{$class}::{$action}] deleted call", ['call_id' => $callId]);
        return redirect()->back()->with('success', __('Call successfully deleted!'))->with('status', 'calls');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'call_id' => $callId]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id, 'call_id' => $callId]);
  }

  public const EML_CRT = 'emailCreate';
  public function emailCreate(Request $request, int|string $id): View|JsonResponse|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = DC::TABLE_DEALS . '.emails';
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class, $viewPath) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method]);
        if (($r = self::guard($request, 'create deal email', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal'));
        $this->logExecutionTime($renderStart, $action, 'renderEmailCreate');
        return $response;
      } catch (AuthorizationException $e) {
        return response()->json(['error' => __('Permission Denied.')], 401);
      # PULL REQUEST START
      } catch (ModelNotFoundException $e) {
        // Retorna 404 quando o deal não é encontrado
        Log::warning("[{$class}::{$action}] deal não encontrado", ['deal_id' => $id]);
        return response()->json(['error' => __('Deal not found.')], 404);
      # PULL REQUEST END
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return response()->json(['error' => __('An unexpected error occurred.')], 500);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public const EML_STR = 'emailStore';
  public function emailStore(Request $request, int|string $id): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($request, $id, $action, $method, $class) {
      try {
        Log::info("[{$class}::{$action}] start", [UC::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'method' => $method, 'input' => $request->all()]);
        if (($r = self::guard($request, 'create deal email', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        $dealStart = microtime(true);
        $deal = Deal::findOrFail($id);
        $this->logExecutionTime($dealStart, $action, 'loadDeal');
        $valStart = microtime(true);
        try {
          $v = $request->validate(['to' => 'required|email', 'subject' => 'required', 'description' => 'required']);
        } catch (ValidationException $e) {
          $this->logExecutionTime($valStart, $action, 'validatePayload');
          Log::warning("[{$class}::{$action}] validation failed", ['errors' => $e->errors()]);
          Log::debug("[{$class}::{$action}] debug validation", ['message' => $e->getMessage(), 'method' => $method]);
          return redirect()->back()->with('error', $e->getMessage())->with('status', 'emails');
        }
        $this->logExecutionTime($valStart, $action, 'validatePayload');
        $createStart = microtime(true);
        DealEmail::create(['deal_id' => $deal->id, 'to' => $v['to'], 'subject' => $v['subject'], 'description' => $v['description']]);
        $this->logExecutionTime($createStart, $action, 'createDealEmail');
        $emailData = ['deal_name' => $deal->name, 'to' => $v['to'], 'subject' => $v['subject'], 'description' => $v['description']];
        $settings = Utility::settings();
        $mailStart = microtime(true);
        try {
          Mail::to($v['to'])->send(new SendDealEmail($emailData, $settings));
        } catch (\Throwable $e) {
          Log::warning("[{$class}::{$action}] SMTP failed", ['message' => $e->getMessage()]);
          Log::debug("[{$class}::{$action}] debug smtp", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
          $smtpError = __('E-Mail has not been sent due to SMTP configuration');
        }
        $this->logExecutionTime($mailStart, $action, 'sendDealEmail');
        $actStart = microtime(true);
        ActivityLog::create([UC::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Create Deal Email', 'remark' => json_encode(['title' => 'Create new Deal Email'])]);
        $this->logExecutionTime($actStart, $action, 'createActivityLog');
        return redirect()->back()->with('success', __('Email successfully created!') . ($smtpError ?? ''))->with('status', 'emails');
      } catch (AuthorizationException $e) {
        return defaultPermissionDenial($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  public function show(Request $request, Deal $deal): View|RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = VW::DL . '.show';
    return $this->measureProfile($action, function () use ($request, $deal, $action, $method, $class, $viewPath) {
      Log::info("[{$class}::{$action}]", [UC::COL_USER_ID => Auth::id(), 'deal_id' => $deal->id, 'method' => $method]);
      if (($resp = self::guard($request, 'view deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $resp;
      try {
        $activeStart = microtime(true);
        if (!$deal->is_active) {
          $this->logExecutionTime($activeStart, $action, 'inactiveDealCheck');
          Log::warning("[{$class}::{$action}] inactive", ['deal_id' => $deal->id]);
          return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $tasksStart = microtime(true);
        $calendarTasks = [];
        if ($request->user()->can('view task')) foreach ($deal->tasks as $task) $calendarTasks[] = ['title' => $task->name, 'start' => $task->date, 'url' => route(DC::TABLE_DEALS . '.tasks.show', [$deal->id, $task->id]), 'className' => $task->status ? 'bg-primary border-primary' : 'bg-warning border-warning'];
        $this->logExecutionTime($tasksStart, $action, 'buildCalendarTasks');
        $customStart = microtime(true);
        $permission = [];
        $customFields = CustomField::where('module', 'deal')->get();
        $deal->customField = CustomField::getData($deal, 'deal')->toArray();
        $this->logExecutionTime($customStart, $action, 'loadCustomFields');
        Log::info("[{$class}::{$action}] rendering view", ['deal_id' => $deal->id, 'tasks_count' => count($calendarTasks)]);
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        $renderStart = microtime(true);
        $response = view($viewPath, compact('deal', 'customFields', 'calendarTasks', 'permission'));
        $this->logExecutionTime($renderStart, $action, 'renderShow');
        return $response;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['error' => $e->getMessage(), 'deal_id' => $deal->id]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'method' => $method]);
        return defaultUndefinedException($request, $e, $class . '::' . $action, route(self::ROUTE_INDEX));
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'deal_id' => $deal->id]);
  }

  public function deal(int|string $id): ?Deal
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    return $this->measureProfile($action, function () use ($id, $action, $method, $class) {
      Log::info("[{$class}::{$action}]", ['deal_id' => $id, 'method' => $method]);
      try {
        $cacheStart = microtime(true);
        $hit = self::$dealCache && self::$dealCache->id === $id;
        $this->logExecutionTime($cacheStart, $action, 'checkCache');
        if ($hit) {
          Log::info("[{$class}::{$action}] cache hit", ['deal_id' => $id]);
          return self::$dealCache;
        }
        $loadStart = microtime(true);
        self::$dealCache = Deal::find($id);
        $this->logExecutionTime($loadStart, $action, 'loadDeal');
        Log::info("[{$class}::{$action}] loaded", ['deal_id' => $id, 'found' => (bool) self::$dealCache]);
        return self::$dealCache;
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] failed", ['err' => $e->getMessage(), 'deal_id' => $id, 'method' => $method]);
        Log::debug("[{$class}::{$action}] debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()]);
        return null;
      }
    }, ['method' => $method, 'class' => $class, 'deal_id' => $id]);
  }

  protected function authorizeOwner(Request $req, Deal $deal, string $perm): ?RedirectResponse
  {
    $r = self::guard($req, $perm, self::ROUTE_INDEX);
    if ($r instanceof RedirectResponse) return $r;
    if ($deal->created_by !== $req->user()->ownerId()) {
      return defaultPermissionDenial(
        $req,
        new AuthorizationException($perm),
        __METHOD__,
        route(self::ROUTE_INDEX)
      );
    }
    return null;
  }

  /**
   * @throws \RuntimeException
   */
  private function getDefaultPipeline(User $user): Pipeline
  {
    Log::debug(__METHOD__, [
      UC::COL_USER_ID => $user?->id,
      'default_pipeline' => $user?->default_pipeline ?? '#NULL'
    ]);
    try {
      $isSa = $user->{UC::COL_TP} === PC::SA;
      $creatorId = $isSa ? $user->id : DC::DEFAULT_UUID;
      $baseQuery = fn() => Pipeline::where(DC::COL_TABLE_CREATOR, $creatorId);
      $pipeline = null;
      if ($user->default_pipeline)
        $pipeline = $baseQuery()->where('id', $user->default_pipeline)->first();
      if (!$pipeline)
        $pipeline = $baseQuery()->first();
      if (!$pipeline)
        throw new \RuntimeException("No pipeline found for user {$user->id}");
      Log::debug(__METHOD__ . ' resolved', ['pipeline_id' => $pipeline->id]);
      return $pipeline;
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' error', ['err' => $e->getMessage()]);
      throw $e;
    }
  }
}
