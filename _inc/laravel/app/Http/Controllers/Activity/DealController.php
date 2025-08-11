<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  ActivitiesConstants,
  DatabaseConstants,
  PermissionsConstants,
  ProjectsConstants,
  UsersConstants
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
  Validator
};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DealController extends Controller
{
  use ChecksLogin, ChecksPermissions;

  private const ROUTE_INDEX = DatabaseConstants::TABLE_DEALS . '.index';
  private static ?Deal $dealCache = null;

  public function index(Request $req): View|RedirectResponse
  {
    if (($r = self::guard($req, PermissionsConstants::MNG_DL, self::ROUTE_INDEX)) instanceof RedirectResponse)
      return $r;
    try {
      $user = $req->user();
      Log::info(__METHOD__, [
        UsersConstants::COL_USER_ID => $user?->id,
        UsersConstants::COL_TP => $user[UsersConstants::COL_TP]
      ]);
      $pipeline = $this->getDefaultPipeline($user);
      $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $user?->ownerId())->pluck('name', 'id');
      $ids      = $user[UsersConstants::COL_TP] === PermissionsConstants::CL
        ? $user?->clientDeals->pluck('id')
        : $user?->deals->pluck('id');
      $deals    = Deal::whereIn('id', $ids)
        ->where('pipeline_id', $pipeline->id)->get();
      $cntDeal  = ['total' => Deal::getDealSummary($deals)];
      Log::debug(__METHOD__, ['cntDeal' => $cntDeal]);
      return view(DatabaseConstants::TABLE_DEALS . '.' . __FUNCTION__, compact(
        DatabaseConstants::TABLE_PIPELINES,
        Str::singular(DatabaseConstants::TABLE_PIPELINES),
        'cntDeal'
      ));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function dealList(Request $req): View|RedirectResponse
  {
    if (($r = self::guard($req, PermissionsConstants::MNG_DL, self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    try {
      $user     = $req->user();
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $user?->id]);
      $pipeline = $this->getDefaultPipeline($user);
      $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $user?->ownerId())->pluck('name', 'id');
      $ids      = $user[UsersConstants::COL_TP] === PermissionsConstants::CL
        ? $user?->clientDeals->pluck('id')
        : $user?->deals->pluck('id');
      $cntDeal  = ['total' => Deal::getDealSummary(
        Deal::whereIn('id', $ids)->where('pipeline_id', $pipeline->id)->get()
      )];
      Log::debug(__METHOD__, ['cntDeal' => $cntDeal]);
      $ordered = $user[UsersConstants::COL_TP] === PermissionsConstants::CL
        ? Deal::join('client_deals', PermissionsConstants::CL . DatabaseConstants::TABLE_DEALS .
          '_' . 'deal_id', '=', DatabaseConstants::TABLE_DEALS . '.id')
        ->where(PermissionsConstants::CL . DatabaseConstants::TABLE_DEALS .
          '_' . 'client_id', $user?->id)
        : Deal::join('user_deals',  'user' . DatabaseConstants::TABLE_DEALS . '_' .
          'deal_id', '=', DatabaseConstants::TABLE_DEALS . '.id')
        ->where('user' . DatabaseConstants::TABLE_DEALS . '_' . '.user_id', $user?->id);
      $deals  = $ordered->where(DatabaseConstants::TABLE_DEALS . '.pipeline_id', $pipeline->id)
        ->orderBy(DatabaseConstants::TABLE_DEALS . '.order')->get();
      return view(DatabaseConstants::TABLE_DEALS . '.list', compact(
        DatabaseConstants::TABLE_PIPELINES,
        Str::singular(DatabaseConstants::TABLE_PIPELINES),
        'deals',
        'cntDeal'
      ));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function create(Request $req): View|RedirectResponse
  {
    if (($r = self::guard($req, 'create deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    try {
      $ownerId    = $req->user()->ownerId();
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $req->user()->id]);
      $clients    = User::where(DatabaseConstants::TABLE_CREATOR, $ownerId)
        ->where(UsersConstants::COL_TP, PermissionsConstants::CL)->pluck('name', 'id');
      $customFields = CustomField::where('module', 'deal')->get();
      return view(DatabaseConstants::TABLE_DEALS . '.' . __FUNCTION__, compact(
        DatabaseConstants::TABLE_CLIENTS,
        'customFields'
      ));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function store(Request $req): RedirectResponse
  {
    if (($r = self::guard($req, 'create deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    Log::info(__METHOD__, ['input' => $req->all()]);
    $v = Validator::make($req->all(), ['name' => 'required']);
    if ($v->fails()) {
      Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
      return redirect()->back()->with('error', $v->errors()->first());
    }
    $user    = $req->user();
    $pipeline = $this->getDefaultPipeline($user);
    $stage   = Stage::where('pipeline_id', $pipeline->id)->first();
    if (!$stage) {
      Log::error(__METHOD__ . ' missing stage', ['pipeline_id' => $pipeline->id]);
      return redirect()->back()->with('error', __('Please Create Stage for This Pipeline.'));
    }
    DB::beginTransaction();
    try {
      $deal = Deal::create([
        'name' => $req->name, 'phone' => $req->phone,
        'price' => $req->price ?: 0, 'pipeline_id' => $pipeline->id,
        'stage_id' => $stage->id, 'status' => 'Active',
        DatabaseConstants::TABLE_CREATOR => $user?->ownerId(),
      ]);
      Log::info(__METHOD__ . ' created', ['deal_id' => $deal->id]);
      // assign clients
      $cids = array_filter((array)$req->clients);
      foreach ($cids as $cid) ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $cid]);
      Log::info(__METHOD__ . ' clients assigned', [
        'deal_id' => $deal->id,
        DatabaseConstants::TABLE_CLIENTS => $cids
      ]);
      // assign users
      $uids = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN ? [$user?->id] : [$user?->id, $user?->ownerId()];
      foreach ($uids as $uid) UserDeal::create([UsersConstants::COL_USER_ID => $uid, 'deal_id' => $deal->id]);
      Log::info(__METHOD__ . ' users assigned', ['deal_id' => $deal->id, DatabaseConstants::TABLE_USERS => $uids]);
      CustomField::saveData($deal, $req->customField ?? []);
      DB::commit();
      return redirect()->route(self::ROUTE_INDEX)->with('success', __('Deal successfully created!'));
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error(__METHOD__ . ' transaction failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function edit(Request $req, Deal $deal): View|JsonResponse|RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    if ($deal->created_by !== $user?->ownerId()) {
      Log::warning(__METHOD__ . ' unauthorized', [UsersConstants::COL_USER_ID => $user?->id, 'deal_id' => $deal->id]);
      return defaultPermissionDenial($req, new AuthorizationException, '', '');
    }
    try {
      Log::info(__METHOD__ . ' start', ['deal_id' => $deal->id]);
      $ownerId    = $user?->ownerId();
      $pipelines  = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $ownerId)->pluck('name', 'id');
      $sources    = Source::where(DatabaseConstants::TABLE_CREATOR, $ownerId)->pluck('name', 'id');
      $products   = ProductService::where(DatabaseConstants::TABLE_CREATOR, $ownerId)->pluck('name', 'id');
      $customFields = CustomField::where('module', 'deal')->get();
      $deal->sources    = explode(',', $deal->sources);
      $deal->products   = explode(',', $deal->products);
      $deal->customField = CustomField::getData($deal, 'deal');
      return view(DatabaseConstants::TABLE_DEALS . '.edit', compact(
        'deal',
        DatabaseConstants::TABLE_PIPELINES,
        'sources',
        DatabaseConstants::TABLE_PRODUCTS,
        'customFields'
      ));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function update(Request $req, Deal $deal): RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    if ($deal->created_by !== $user?->ownerId()) {
      Log::warning(__METHOD__ . ' unauthorized', [UsersConstants::COL_USER_ID => $user?->id, 'deal_id' => $deal->id]);
      return defaultPermissionDenial($req, new AuthorizationException, '', '');
    }
    $v = Validator::make($req->all(), ['name' => 'required|max:20', 'pipeline_id' => 'required']);
    if ($v->fails()) {
      Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
      return redirect()->back()->with('error', $v->errors()->first());
    }
    DB::beginTransaction();
    try {
      $data = [
        'name' => $req->name,
        'phone' => $req->phone,
        'price' => $req->price ?: 0,
        'pipeline_id' => $req->pipeline_id,
        'stage_id' => $req->stage_id,
        'sources' => implode(',', array_filter($req->sources ?? [])),

        DatabaseConstants::TABLE_PRODUCTS => implode(',', array_filter($req->products ?? [])),
        'notes' => $req->notes,
      ];
      $deal->update($data);
      CustomField::saveData($deal, $req->customField ?? []);
      DB::commit();
      Log::info(__METHOD__ . ' updated', ['deal_id' => $deal->id]);
      return redirect()->back()->with('success', __('Deal successfully updated!'));
    } catch (\Throwable $e) {
      DB::rollBack();
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function destroy(Request $req, Deal $deal): RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    if (($r = $this->guard($req, 'delete deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    if ($deal->created_by !== $user?->ownerId()) {
      Log::warning(__METHOD__ . ' unauthorized', [UsersConstants::COL_USER_ID => $user?->id, 'deal_id' => $deal->id]);
      return defaultPermissionDenial($req, new AuthorizationException, '', '');
    }
    DB::beginTransaction();
    try {
      DealDiscussion::where('deal_id', $deal->id)->delete();
      DealFile::where('deal_id', $deal->id)->delete();
      ClientDeal::where('deal_id', $deal->id)->delete();
      UserDeal::where('deal_id', $deal->id)->delete();
      DealTask::where('deal_id', $deal->id)->delete();
      ActivityLog::where('deal_id', $deal->id)->delete();
      $deal->delete();
      DB::commit();
      Log::info(__METHOD__ . ' deleted', ['deal_id' => $deal->id]);
      return redirect()->route(self::ROUTE_INDEX)->with('success', __('Deal successfully deleted!'));
    } catch (\Throwable $e) {
      DB::rollBack();
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function order(Request $req): JsonResponse
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    if (($r = $this->guard($req, 'move deal', '')) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
    $v = Validator::make($req->all(), ['deal_id' => 'required', 'stage_id' => 'required', 'order' => 'required|array']);
    if ($v->fails()) return response()->json(['error' => $v->errors()->first()], 400);
    DB::beginTransaction();
    try {
      $deal = Deal::findOrFail($req->deal_id);
      $clients = ClientDeal::where('deal_id', $deal->id)->pluck('client_id')->toArray();
      $dealUsers = $deal->users->pluck('id')->toArray();
      $usrs = User::whereIn('id', array_merge($dealUsers, $clients))->pluck('email', 'id')->toArray();
      if ($deal->stage_id !== $req->stage_id) {
        $newStage = Stage::findOrFail($req->stage_id);
        ActivityLog::create([
          UsersConstants::COL_USER_ID => $user?->id, 'deal_id' => $deal->id, 'log_type' => 'Move',
          'remark' => json_encode([
            'title' => $deal->name,
            'oldStatus' => $deal->stage->name,
            'newStatus' => $newStage->name,
          ]),
        ]);
        Utility::sendEmailTemplate('Move Deal', $usrs, [
          'deal_name' => $deal->name,
          'deal_pipeline' => $deal->pipeline->name,
          'deal_stage' => $deal->stage->name,
          'deal_status' => $deal->status,
          'deal_price' => $user?->priceFormat($deal->price),
          'deal_oldStage' => $deal->stage->name,
          'deal_newStage' => $newStage->name,
        ]);
        Log::info(__METHOD__ . ' moved', ['deal_id' => $deal->id, 'new_stage' => $newStage->id]);
      }
      foreach ($req->order as $position => $item) {
        $d = Deal::findOrFail($item);
        $d->order = $position;
        $d->stage_id = $req->stage_id;
        $d->save();
      }
      DB::commit();
      return response()->json(['success' => true]);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('An error occurred.')], 500);
    }
  }

  public function labels(Request $req, int $id): View|JsonResponse|RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    try {
      $deal = Deal::findOrFail($id);
      if ($deal->created_by !== $user?->ownerId()) {
        Log::warning(__METHOD__ . ' unauthorized', [UsersConstants::COL_USER_ID => $user?->id, 'deal_id' => $id]);
        return response()->json(['error' => __('Permission Denied.')], 401);
      }
      $labels  = Label::where('pipeline_id', $deal->pipeline_id)
        ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
      $selected = $deal->labels()->pluck('id')->toArray();
      return view(DatabaseConstants::TABLE_DEALS . '.labels', compact('deal', 'labels', 'selected'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function labelStore(Request $req, int $id): RedirectResponse
  {
    if (($r = self::guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      $deal = Deal::findOrFail($id);
      if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
      DB::beginTransaction();
      $deal->labels = $req->labels ? implode(',', $req->labels) : null;
      $deal->save();
      DB::commit();
      Log::info(__METHOD__ . ' labels updated', ['deal_id' => $deal->id]);
      return redirect()->back()->with('success', __('Labels successfully updated!'));
    } catch (\Throwable $e) {
      DB::rollBack();
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function userEdit(Request $req, int $id): View|JsonResponse|RedirectResponse|null
  {
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    if (($r = $this->guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    try {
      $deal = Deal::findOrFail($id);
      if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
      $users = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->where(UsersConstants::COL_TP, '!=', PermissionsConstants::CL)
        ->whereNotIn('id', function ($q) use ($id) {
          $q->select(UsersConstants::COL_USER_ID)->from('user_deals')->where('deal_id', $id);
        })->get()->filter(fn ($u) => $u->can(PermissionsConstants::MNG_DL))->pluck('name', 'id')->prepend(__('Select Users'), '');
      return view(DatabaseConstants::TABLE_DEALS . '.users', compact('deal', DatabaseConstants::TABLE_USERS));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function userUpdate(Request $req, int $id): RedirectResponse
  {
    if (($r = self::guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      $deal = Deal::findOrFail($id);
      if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
      $uids = array_filter((array)$req->users);
      $emails = User::whereIn('id', $uids)->pluck('email', 'id')->toArray();
      DB::beginTransaction();
      foreach ($uids as $uid) UserDeal::create(['deal_id' => $deal->id, UsersConstants::COL_USER_ID => $uid]);
      if ($emails) {
        $dArr = [
          'deal_name' => $deal->name,
          'deal_pipeline' => $deal->pipeline->name,
          'deal_stage' => $deal->stage->name,
          'deal_status' => $deal->status,
          'deal_price' => $user?->priceFormat($deal->price),
        ];
        $resp = Utility::sendEmailTemplate('Assign Deal', $emails, $dArr);
        Log::info(__METHOD__ . ' users assigned', ['deal_id' => $deal->id, DatabaseConstants::TABLE_USERS => $uids]);
      }
      DB::commit();
      return redirect()->back()->with('success', __('Users successfully updated!')
        . (!empty($resp['error']) ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : ''));
    } catch (\Throwable $e) {
      DB::rollBack();
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function userDestroy(Request $req, int $id, int $userId): RedirectResponse
  {
    if (($r = self::guard($req, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
    if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
    $user = $userOrRedirect;
    try {
      $deal = Deal::findOrFail($id);
      if ($deal->created_by !== $user?->ownerId()) throw new AuthorizationException;
      UserDeal::where([['deal_id', $id], [UsersConstants::COL_USER_ID, $userId]])->delete();
      Log::info(__METHOD__ . ' user removed', ['deal_id' => $id, UsersConstants::COL_USER_ID => $userId]);
      return redirect()->back()->with('success', __('User successfully deleted!'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
    }
  }

  public function clientEdit(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
      $exclude = ClientDeal::where('deal_id', $id)->pluck('client_id');
      $clients = User::where(DatabaseConstants::TABLE_CREATOR, $request->user()->ownerId())
        ->where(UsersConstants::COL_TP, PermissionsConstants::CL)
        ->whereNotIn('id', $exclude)
        ->pluck('name', 'id');
      Log::info(__METHOD__ . ' fetched clients', ['count' => $clients->count()]);
      return view(DatabaseConstants::TABLE_DEALS . '.clients', compact(
        'deal',
        DatabaseConstants::TABLE_CLIENTS
      ));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function clientUpdate(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse)
        return $r;
      $clients = array_filter($request->clients ?? []);
      DB::transaction(function () use ($deal, $clients) {
        foreach ($clients as $cid) ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $cid]);
      });
      Log::info(__METHOD__ . ' assigned clients', [
        'deal_id' => $deal->id,
        DatabaseConstants::TABLE_CLIENTS => $clients
      ]);
      return $clients
        ? redirect()->back()->with('success', __('Clients successfully updated!'))->with(
          'status',
          DatabaseConstants::TABLE_CLIENTS
        )
        : redirect()->back()->with('error', __('Please Select Valid Clients!'))->with(
          'status',
          DatabaseConstants::TABLE_CLIENTS
        );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function clientDestroy(Request $request, int $id, int $clientId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'client_id' => $clientId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse)
        return $r;
      ClientDeal::where('deal_id', $deal->id)->where('client_id', $clientId)->delete();
      Log::info(__METHOD__ . ' removed client', ['deal_id' => $deal->id, 'client_id' => $clientId]);
      return redirect()->back()->with('success', __('Client successfully deleted!'))->with(
        'status',
        DatabaseConstants::TABLE_CLIENTS
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function productEdit(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], Response::HTTP_UNAUTHORIZED);
      $excluded = explode(',', $deal->products);
      $products = ProductService::where(DatabaseConstants::TABLE_CREATOR, $request->user()->ownerId())
        ->whereNotIn('id', $excluded)
        ->pluck('name', 'id');
      Log::info(__METHOD__ . ' fetched products', ['count' => $products->count()]);
      return view(DatabaseConstants::TABLE_DEALS . '.products', compact(
        'deal',
        DatabaseConstants::TABLE_PRODUCTS
      ));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function productUpdate(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse)
        return $r;
      $new = array_filter($request->products ?? []);
      if (!$new) {
        return redirect()->back()->with('error', __('Please Select Valid Product!'))->with('status', 'general');
      }
      $old = explode(',', $deal->products);
      $deal->products = implode(',', array_merge($old, $new));
      $deal->save();
      $names = ProductService::whereIn('id', $new)->pluck('name')->implode(',');
      ActivityLog::create([
        UsersConstants::COL_USER_ID => $request->user()->id,
        'deal_id' => $deal->id,
        'log_type' => 'Add Product',
        'remark'   => json_encode(['title' => $names]),
      ]);
      Log::info(__METHOD__ . ' added products', [
        'deal_id' => $deal->id,
        DatabaseConstants::TABLE_PRODUCTS => $new
      ]);
      return redirect()->back()->with('success', __('Products successfully updated!'))->with(
        'status',
        DatabaseConstants::TABLE_PRODUCTS
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function productDestroy(Request $request, int $id, int $productId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'product_id' => $productId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse)
        return $r;
      $list = array_filter(explode(',', $deal->products), fn ($p) => $p != $productId);
      $deal->products = implode(',', $list);
      $deal->save();
      Log::info(__METHOD__ . ' removed product', ['deal_id' => $deal->id, 'product_id' => $productId]);
      return redirect()->back()->with('success', __('Products successfully deleted!'))->with(
        'status',
        DatabaseConstants::TABLE_PRODUCTS
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function fileUpload(Request $request, int $id): JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        throw new AuthorizationException;
      $request->validate(['file' => 'required|file']);
      $size = $request->file('file')->getSize();
      $lim = Utility::updateStorageLimit($request->user()->creatorId(), $size);
      $orig = $request->file('file')->getClientOriginalName();
      $path = "{$id}_" . md5(time()) . "_{$orig}";
      $file = DealFile::create([
        'deal_id'   => $id,
        'file_name' => $orig,
        'file_path' => $path,
      ]);
      if ($lim === 1) $request->file->storeAs('deal_files', $path);
      ActivityLog::create([
        UsersConstants::COL_USER_ID  => $request->user()->id,
        'deal_id'  => $deal->id,
        'log_type' => 'Upload File',
        'remark'   => json_encode(['file_name' => $orig]),
      ]);
      Log::info(__METHOD__ . ' file stored', ['file_id' => $file->id, 'limit' => $lim]);
      return response()->json([
        'is_success' => true,
        'download' => route(DatabaseConstants::TABLE_DEALS . '.file.download', [$id, $file->id]),
        'delete' => route(DatabaseConstants::TABLE_DEALS . '.file.delete', [$id, $file->id])
      ], 200);
    } catch (AuthorizationException $e) {
      return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
    }
  }

  public function fileDownload(Request $request, int $id, int $fileId): BinaryFileResponse|RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'file_id' => $fileId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      $file = DealFile::findOrFail($fileId);
      $full = storage_path("deal_files/{$file->file_path}");
      return Response::download($full, $file->file_name, ['Content-Length:' . filesize($full)]);
    } catch (AuthorizationException $e) {
      return redirect()->back()->with('error', __('Permission Denied.'));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return redirect()->back()->with('error', __('File does not exist.'));
    }
  }

  public function fileDelete(Request $request, int $id, int $fileId): JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'file_id' => $fileId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        throw new AuthorizationException;
      $file = DealFile::findOrFail($fileId);
      Utility::changeStorageLimit($request->user()->creatorId(), "deal_files/{$file->file_path}");
      $full = storage_path("deal_files/{$file->file_path}");
      File::exists($full) && File::delete($full);
      $file->delete();
      Log::info(__METHOD__ . ' deleted file', ['file_id' => $fileId]);
      return response()->json(['is_success' => true], 200);
    } catch (AuthorizationException $e) {
      return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['is_success' => false, 'error' => __('An unexpected error occurred.')], 500);
    }
  }

  public function noteStore(Request $request, int $id): JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        throw new AuthorizationException;
      $deal->update(['notes' => $request->notes]);
      Log::info(__METHOD__ . ' saved note', ['deal_id' => $id]);
      return response()->json(['is_success' => true, 'success' => __('Note successfully saved!')], 200);
    } catch (AuthorizationException $e) {
      return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
    }
  }

  public function taskCreate(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'create task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], 401);
      return view(DatabaseConstants::TABLE_DEALS . '.tasks', [
        'deal' => $deal,
        'priorities' => DealTask::$priorities,
        'status' => DealTask::$status
      ]);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('Permission Denied.')], 401);
    }
  }

  public function taskStore(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'create task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      $v = Validator::make($request->all(), [
        'name' => 'required', 'date' => 'required', 'time' => 'required',
        ProjectsConstants::COL_PRT => 'required', 'status' => 'required'
      ]);
      if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());
      DB::transaction(function () use ($request, $deal) {
        $task = DealTask::create([
          'deal_id' => $deal->id,
          'name' => $request->name,
          'date' => $request->date,
          'time' => date('H:i:s', strtotime("{$request->date} {$request->time}")),
          ProjectsConstants::COL_PRT => $request[ProjectsConstants::COL_PRT],
          'status' => $request->status
        ]);
        ActivityLog::create([
          UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id,
          'log_type' => 'Create Task', 'remark' => json_encode(['title' => $task->name])
        ]);
      });
      return redirect()->back()->with('success', __('Task successfully created!'))->with('status', 'tasks');
    } catch (\Throwable $e) {
      return redirect()->back()
        ->with('error', $e instanceof \InvalidArgumentException
          ? $e->getMessage()
          : __('Permission Denied.'))
        ->with('status', 'tasks');
    }
  }

  public function taskShow(Request $request, int $id, int $taskId): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'view task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], 401);
      $task = DealTask::findOrFail($taskId);
      return view(DatabaseConstants::TABLE_DEALS . '.tasksShow', compact('deal', 'task'));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('Permission Denied.')], 401);
    }
  }

  public function taskEdit(Request $request, int $id, int $taskId): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], 401);
      $task = DealTask::findOrFail($taskId);
      return view(DatabaseConstants::TABLE_DEALS . '.tasks', [
        'deal' => $deal, 'task' => $task,
        'priorities' => DealTask::$priorities,
        'status' => DealTask::$status
      ]);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('Permission Denied.')], 401);
    }
  }

  public function taskUpdate(Request $request, int $id, int $taskId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      $v = Validator::make($request->all(), [
        'name' => 'required', 'date' => 'required', 'time' => 'required',
        ProjectsConstants::COL_PRT => 'required', 'status' => 'required'
      ]);
      if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());
      DealTask::findOrFail($taskId)->update([
        'name' => $request->name,
        'date' => $request->date,
        'time' => date('H:i:s', strtotime("{$request->date} {$request->time}")),
        ProjectsConstants::COL_PRT => $request[ProjectsConstants::COL_PRT],
        'status' => $request->status
      ]);
      return redirect()->back()->with('success', __('Task successfully updated!'))->with('status', 'tasks');
    } catch (\Throwable $e) {
      return redirect()->back()
        ->with('error', $e instanceof \InvalidArgumentException
          ? $e->getMessage()
          : __('Permission Denied.'))
        ->with('status', 'tasks');
    }
  }

  public function taskUpdateStatus(Request $request, int $id, int $taskId): JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'edit task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        throw new AuthorizationException;
      $v = Validator::make($request->all(), ['status' => 'required']);
      if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());
      $task = DealTask::findOrFail($taskId);
      $task->status = $request->status ? 0 : 1;
      $task->save();
      return response()->json([
        'is_success' => true,
        'status' => $task->status,
        'status_label' => __(DealTask::$status[$task->status])
      ], 200);
    } catch (\Throwable $e) {
      return response()->json([
        'is_success' => false,
        'error' => $e instanceof \InvalidArgumentException
          ? $e->getMessage()
          : __('Permission Denied.')
      ], Response::HTTP_UNAUTHORIZED);
    }
  }

  public function taskDestroy(Request $request, int $id, int $taskId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'task_id' => $taskId]);
      $deal = Deal::findOrFail($id);
      if (($r = self::guard($request, 'delete task', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return $r;
      DealTask::findOrFail($taskId)->delete();
      return redirect()->back()->with('success', __('Task successfully deleted!'))->with('status', 'tasks');
    } catch (\Throwable $e) {
      return redirect()->back()
        ->with('error', __('Permission Denied.'))
        ->with('status', 'tasks');
    }
  }

  public function sourceEdit(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
      $deal = Deal::findOrFail($id);
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
      $sources = Source::where(DatabaseConstants::TABLE_CREATOR, $request->user()->ownerId())->pluck('name', 'id');
      $selected = $deal->sources()?->pluck('id')->toArray() ?: [];
      Log::info(__METHOD__ . ' fetched sources', ['count' => count($sources)]);
      return view(DatabaseConstants::TABLE_DEALS . '.sources', compact('deal', 'sources', 'selected'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function sourceUpdate(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'input' => $request->all()]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
      $list = array_filter($request->sources ?? []);
      DB::transaction(fn () => $deal->update(['sources' => implode(',', $list)]));
      ActivityLog::create([UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id, 'log_type' => 'Update Sources', 'remark' => json_encode(['title' => 'Update Sources'])]);
      Log::info(__METHOD__ . ' updated sources', ['deal_id' => $deal->id, 'sources' => $list]);
      return redirect()->back()->with('success', __('Sources successfully updated!'))->with('status', 'sources');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function sourceDestroy(Request $request, int $id, int $sourceId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'source_id' => $sourceId]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      if (($r = $this->authorizeOwner($request, $deal, 'edit deal')) instanceof RedirectResponse) return $r;
      $remaining = array_filter(explode(',', $deal->sources), fn ($s) => $s != (string)$sourceId);
      $deal->update(['sources' => implode(',', $remaining)]);
      Log::info(__METHOD__ . ' removed source', ['deal_id' => $deal->id, 'source_id' => $sourceId]);
      return redirect()->back()->with('success', __('Sources successfully deleted!'))->with('status', 'sources');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function permission(Request $request, int $id, int $clientId): View|RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'client_id' => $clientId]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $client = User::findOrFail($clientId);
      $perm = $client->clientPermission($deal->id);
      $selected = $perm ? explode(',', $perm->permissions) : [];
      $permissions = Deal::$permissions;
      return view(DatabaseConstants::TABLE_DEALS . '.permissions', compact(
        'deal',
        PermissionsConstants::CL,
        'selected',
        DatabaseConstants::TABLE_PERMISSIONS
      ));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function permissionStore(Request $request, int $id, int $clientId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'client_id' => $clientId, 'input' => $request->all()]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $client = User::findOrFail($clientId);
      $perm = $client->clientPermission($deal->id);
      $list = array_filter($request->permissions ?? []);
      DB::transaction(function () use ($perm, $list, $clientId, $deal) {
        $perm
          ? $perm->update([DatabaseConstants::TABLE_PERMISSIONS => implode(',', $list)])
          : ($list && ClientPermission::create([
            'client_id' => $clientId, 'deal_id' => $deal->id,
            DatabaseConstants::TABLE_PERMISSIONS => implode(',', $list)
          ]));
      });
      Log::info(__METHOD__ . ' updated permissions', [
        'deal_id' => $deal->id, 'client_id' => $clientId,
        DatabaseConstants::TABLE_PERMISSIONS => $list
      ]);
      return redirect()->back()->with('success', __(ucfirst(DatabaseConstants::TABLE_PERMISSIONS) . ' successfully updated!'))->with(
        'status',
        DatabaseConstants::TABLE_CLIENTS
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function jsonUser(Request $request): JsonResponse
  {
    try {
      if (!($id = $request->input('deal_id'))) return response()->json([], 200);
      $users = Deal::findOrFail($id)->users->pluck('name', 'id');
      return response()->json($users, 200);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json([], 500);
    }
  }

  public function changePipeline(Request $request): RedirectResponse
  {
    try {
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $user = $request->user();
      $user->update(['default_pipeline' > $request->input('defaultPipelineId')]);
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $user?->id, 'default_pipeline' => $user?->default_pipeline]);
      return redirect()->back();
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function discussionCreate(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse)
        return response()->json(['error' => __('Permission Denied.')], 401);
      $deal = Deal::findOrFail($id);
      return view(DatabaseConstants::TABLE_DEALS . '.discussions', compact('deal'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function discussionStore(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'input' => $request->all()]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $disc = DealDiscussion::create([
        'deal_id' => $deal->id,
        'comment' => $request->input('comment'),
        DatabaseConstants::TABLE_CREATOR => $request->user()->id
      ]);
      Log::info(__METHOD__ . ' added discussion', ['discussion_id' => $disc->id]);
      return redirect()->back()->with('success', __('Message successfully added!'))->with('status', 'discussion');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function changeStatus(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'status' => $request->input('dealStatus')]);
      if (($r = self::guard($request, 'edit deal', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $deal->update(['status' => $request->input('dealStatus')]);
      return redirect()->back();
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function callCreate(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      if (($r = self::guard($request, 'create deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
      $deal = Deal::findOrFail($id);
      $users = UserDeal::where('deal_id', $id)->pluck(UsersConstants::COL_USER_ID);
      return view(DatabaseConstants::TABLE_DEALS . '.calls', compact('deal', DatabaseConstants::TABLE_USERS));
    } catch (AuthorizationException $e) {
      return response()->json(['error' => __('Permission Denied.')], 401);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('An unexpected error occurred.')], 500);
    }
  }

  public function callStore(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'input' => $request->all()]);
      if (($r = self::guard($request, 'create deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $v = $request->validate(['subject' => 'required', 'callType' => 'required', UsersConstants::COL_USER_ID => 'required']);
      $call = DealCall::create([
        'deal_id' => $deal->id,
        'subject' => $v['subject'],
        'call_type' => $v['callType'],
        'duration' => $request->input('duration'),
        UsersConstants::COL_USER_ID => $v[UsersConstants::COL_USER_ID],
        'description' => $request->input('description'),
        'call_result' => $request->input('callResult')
      ]);
      ActivityLog::create([
        UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id,
        'log_type' => 'Create Deal Call', 'remark' => json_encode(['title' => 'Create new Deal Call'])
      ]);
      Log::info(__METHOD__ . ' created call', ['call_id' => $call->id]);
      return redirect()->back()->with('success', __('Call successfully created!'))->with('status', 'calls');
    } catch (ValidationException $e) {
      return redirect()->back()->with('error', $e->getMessage())->with('status', 'calls');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function callEdit(Request $request, int $id, int $callId): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'call_id' => $callId]);
      if (($r = self::guard(
        $request,
        'edit deal call',
        self::ROUTE_INDEX
      )) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
      $deal = Deal::findOrFail($id);
      $call = DealCall::findOrFail($callId);
      $users = UserDeal::where('deal_id', $id)->pluck(UsersConstants::COL_USER_ID);
      return view(DatabaseConstants::TABLE_DEALS . '.calls', compact('deal', 'call', DatabaseConstants::TABLE_USERS));
    } catch (AuthorizationException $e) {
      return response()->json(['error' => __('Permission Denied.')], 401);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('An unexpected error occurred.')], 500);
    }
  }

  public function callUpdate(Request $request, int $id, int $callId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'call_id' => $callId, 'input' => $request->all()]);
      if (($r = self::guard($request, 'edit deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $v = $request->validate(['subject' => 'required', 'callType' => 'required', UsersConstants::COL_USER_ID => 'required']);
      DealCall::findOrFail($callId)->update([
        'subject' => $v['subject'],
        'call_type' => $v['callType'],
        'duration' => $request->input('duration'),
        UsersConstants::COL_USER_ID => $v[UsersConstants::COL_USER_ID],
        'description' => $request->input('description'),
        'call_result' => $request->input('callResult')
      ]);
      Log::info(__METHOD__ . ' updated call', ['call_id' => $callId]);
      return redirect()->back()->with('success', __('Call successfully updated!'))->with('status', 'calls');
    } catch (ValidationException $e) {
      return redirect()->back()->with('error', $e->getMessage())->with('status', 'calls');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function callDestroy(Request $request, int $id, int $callId): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'call_id' => $callId]);
      if (($r = self::guard($request, 'delete deal call', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      DealCall::findOrFail($callId)->delete();
      Log::info(__METHOD__ . ' deleted call', ['call_id' => $callId]);
      return redirect()->back()->with('success', __('Call successfully deleted!'))->with('status', 'calls');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function emailCreate(Request $request, int $id): View|JsonResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id]);
      if (($r = self::guard($request, 'create deal email', self::ROUTE_INDEX)) instanceof RedirectResponse) return response()->json(['error' => __('Permission Denied.')], 401);
      $deal = Deal::findOrFail($id);
      return view(DatabaseConstants::TABLE_DEALS . '.emails', compact('deal'));
    } catch (AuthorizationException $e) {
      return response()->json(['error' => __('Permission Denied.')], 401);
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return response()->json(['error' => __('An unexpected error occurred.')], 500);
    }
  }

  public function emailStore(Request $request, int $id): RedirectResponse
  {
    try {
      Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $id, 'input' => $request->all()]);
      if (($r = self::guard($request, 'create deal email', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
      $deal = Deal::findOrFail($id);
      $v = $request->validate(['to' => 'required|email', 'subject' => 'required', 'description' => 'required']);
      DealEmail::create([
        'deal_id' => $deal->id, 'to' => $v['to'], 'subject' => $v['subject'],
        'description' => $v['description']
      ]);
      $emailData = [
        'deal_name' => $deal->name, 'to' => $v['to'], 'subject' => $v['subject'],
        'description' => $v['description']
      ];
      $settings = Utility::settings();
      try {
        Mail::to($v['to'])->send(new SendDealEmail($emailData, $settings));
      } catch (\Throwable $e) {
        Log::warning(__METHOD__ . " SMTP failed: {$e->getMessage()}");
        $smtpError = __('E-Mail has not been sent due to SMTP configuration');
      }
      ActivityLog::create([
        UsersConstants::COL_USER_ID => $request->user()->id, 'deal_id' => $deal->id,
        'log_type' => 'Create Deal Email', 'remark' => json_encode(['title' => 'Create new Deal Email'])
      ]);
      return redirect()->back()->with(
        'success',
        __('Email successfully created!') . ($smtpError ?? '')
      )->with('status', 'emails');
    } catch (ValidationException $e) {
      return redirect()->back()->with('error', $e->getMessage())->with('status', 'emails');
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __METHOD__, route(self::ROUTE_INDEX));
    }
  }

  public function show(Request $request, Deal $deal): View|RedirectResponse
  {
    Log::info(__METHOD__, [UsersConstants::COL_USER_ID => Auth::id(), 'deal_id' => $deal->id]);
    if (
      ($resp = self::guard($request, 'view deal', self::ROUTE_INDEX))
      instanceof RedirectResponse
    ) return $resp;

    try {
      if (!$deal->is_active) {
        Log::warning(__METHOD__ . ' inactive', ['deal_id' => $deal->id]);
        return redirect()
          ->back()
          ->with('error', __('Permission Denied.'));
      }

      $calendarTasks = [];
      if ($request->user()->can('view task')) {
        foreach ($deal->tasks as $task) {
          $calendarTasks[] = [
            'title'     => $task->name,
            'start'     => $task->date,
            'url'       => route(DatabaseConstants::TABLE_DEALS . '.tasks.show', [$deal->id, $task->id]),
            'className' => $task->status
              ? 'bg-primary border-primary'
              : 'bg-warning border-warning',
          ];
        }
      }

      $permission  = [];
      $customFields = CustomField::where('module', 'deal')->get();
      $deal->customField = CustomField::getData($deal, 'deal')->toArray();

      Log::info(__METHOD__ . ' rendering view', [
        'deal_id'     => $deal->id,
        'tasks_count' => count($calendarTasks),
      ]);

      return view(DatabaseConstants::TABLE_DEALS . '.show', compact(
        'deal',
        'customFields',
        'calendarTasks',
        'permission'
      ));
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__,
        route(self::ROUTE_INDEX)
      );
    }
  }

  public function deal(int $id): ?Deal
  {
    Log::info(__METHOD__, ['deal_id' => $id]);
    if (!self::$dealCache || self::$dealCache->id !== $id) {
      self::$dealCache = Deal::find($id);
      Log::info(__METHOD__ . ' loaded', ['deal_id' => $id]);
    }
    return self::$dealCache;
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
    Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $user?->id, 'default_pipeline' => $user?->default_pipeline]);
    try {
      $query = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $user?->ownerId());
      $pipeline = $user?->default_pipeline
        ? ($query->find($user?->default_pipeline) ?? $query->first())
        : $query->first();
      if (!$pipeline)
        throw new \RuntimeException('No pipeline found for user ' . $user?->id);
      Log::info(__METHOD__ . ' resolved', ['pipeline_id' => $pipeline->id]);
      return $pipeline;
    } catch (\Throwable $e) {
      Log::error(__METHOD__ . ' error', ['err' => $e->getMessage()]);
      throw $e;
    }
  }
}
