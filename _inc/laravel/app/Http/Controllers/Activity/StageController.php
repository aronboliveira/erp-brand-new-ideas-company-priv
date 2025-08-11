<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{Deal, Pipeline, Stage};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator};
use Illuminate\View\View;

class StageController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_ROUTE = ViewsConstants::STG . '.index';

    public function __construct()
    {
        $this->middleware(
            [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
            ]
        );
    }

    public function index(Request $request)
    {
        $function = __FUNCTION__;
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id]);
        if ($denial = $this->guard($request, PermissionsConstants::MNG_ST, self::REDIRECT_ROUTE))
            return $denial;
        $ownerId = $user?->ownerId();
        $stages = Stage::select(DatabaseConstants::TABLE_STAGES . '.*', DatabaseConstants::TABLE_PIPELINES . '.name as pipeline')
            ->join(
                DatabaseConstants::TABLE_PIPELINES,
                DatabaseConstants::TABLE_PIPELINES . '.id',
                '=',
                DatabaseConstants::TABLE_STAGES . '.pipeline_id'
            )
            ->where(
                DatabaseConstants::TABLE_PIPELINES . '.' . DatabaseConstants::TABLE_CREATOR,
                $ownerId
            )
            ->where(
                DatabaseConstants::TABLE_STAGES . '.' . DatabaseConstants::TABLE_CREATOR,
                $ownerId
            )
            ->orderBy(DatabaseConstants::TABLE_STAGES . '.pipeline_id')
            ->orderBy(DatabaseConstants::TABLE_STAGES . '.order')
            ->get();
        Log::info('Fetched stages', ['count' => $stages->count()]);
        $pipelines = [];
        foreach ($stages as $stage) {
            $pid = $stage->pipeline_id;
            if (!isset($pipelines[$pid]))
                $pipelines[$pid] = ['name' => $stage->pipeline, 'stages' => []];
            $pipelines[$pid]['stages'][] = $stage;
        }
        return view(ViewsConstants::STG . '.' . $function, compact('pipelines'));
    }

    public function create(Request $request)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id]);
        if ($denial = $this->guard($request, 'create stage', self::REDIRECT_ROUTE))
            return $denial;

        $ownerId  = $user?->ownerId();
        $pipelines = Pipeline::where('created_by', $ownerId)
            ->pluck('name', 'id');
        return view(ViewsConstants::STG . '.create', compact('pipelines'));
    }

    public function show(Request $request, Stage $stage): RedirectResponse|View
    {
        $action = __METHOD__;
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse) return $user;
        Log::info("$action start", ['user_id' => $user?->id, 'stageId' => $stage->id]);
        if ($denial = $this->guard($request, PermissionsConstants::MNG_ST, self::REDIRECT_ROUTE)) {
            Log::warning("$action permission denied", ['user_id' => $user?->id]);
            return $denial;
        }
        if ($stage->created_by !== $user?->ownerId()) {
            Log::warning("$action ownership denied", ['user_id' => $user?->id, 'stageId' => $stage->id]);
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_ROUTE),
                false
            );
        }
        return redirect()->route(self::REDIRECT_ROUTE);
    }

    public function store(Request $request): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'input' => $request->all()]);
        if ($denial = $this->guard($request, 'create stage', self::REDIRECT_ROUTE))
            return $denial;

        $v = Validator::make($request->all(), [
            'name'        => 'required|max:20',
            'pipeline_id' => 'required|exists:pipelines,id',
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in store', ['errors' => $v->errors()->all()]);
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('error', $v->errors()->first());
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $stage = Stage::create([
                    'name'        => $request->name,
                    'pipeline_id' => $request->pipeline_id,
                    'created_by'  => $user?->ownerId(),
                ]);
                Log::info('Stage created', ['stage' => $stage->id]);
            });
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Deal Stage successfully created!'));
        } catch (\Throwable $e) {
            Log::error('Store transaction failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $request, Stage $stage)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'stage' => $stage->id]);
        if ($denial = $this->guard($request, 'edit stage', self::REDIRECT_ROUTE))
            return $denial;
        if ($stage->created_by !== $user?->ownerId())
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE), false);

        $pipelines = Pipeline::where('created_by', $user?->ownerId())->pluck('name', 'id');
        return view(ViewsConstants::STG . '.edit', compact('stage', 'pipelines'));
    }

    public function update(Request $request, Stage $stage): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'stage' => $stage->id, 'input' => $request->all()]);
        if ($denial = $this->guard($request, 'edit stage', self::REDIRECT_ROUTE))
            return $denial;
        if ($stage->created_by !== $user?->ownerId())
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE), false);

        $v = Validator::make($request->all(), [
            'name'        => 'required|max:20',
            'pipeline_id' => 'required|exists:pipelines,id',
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in update', ['errors' => $v->errors()->all()]);
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('error', $v->errors()->first());
        }

        try {
            DB::transaction(function () use ($request, $stage) {
                $old = $stage->only('name', 'pipeline_id');
                $stage->update($request->only('name', 'pipeline_id'));
                Log::info('Stage updated', ['stage' => $stage->id, 'old' => $old, 'new' => $stage->only('name', 'pipeline_id')]);
            });
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Deal Stage successfully updated!'));
        } catch (\Throwable $e) {
            Log::error('Update transaction failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $request, Stage $stage): RedirectResponse
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'stage' => $stage->id]);
        if ($denial = $this->guard($request, 'delete stage', self::REDIRECT_ROUTE))
            return $denial;
        if ($stage->created_by !== $user?->ownerId())
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_ROUTE), false);

        $count = Deal::where('stage_id', $stage->id)
            ->where('created_by', $stage->created_by)->count();
        if ($count > 0) {
            Log::warning('Cannot delete stage with deals', ['stage' => $stage->id, 'deals' => $count]);
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('error', __('There are some deals on stage, please remove it first!'));
        }

        try {
            DB::transaction(function () use ($stage) {
                $stage->delete();
                Log::info('Stage deleted', ['stage' => $stage->id]);
            });
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Deal Stage successfully deleted!'));
        } catch (\Throwable $e) {
            Log::error('Destroy transaction failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function order(Request $request)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['order' => $request->order]);
        DB::transaction(function () use ($request) {
            foreach ($request->input('order', []) as $position => $id) {
                Stage::where('id', $id)->update(['order' => $position]);
            }
        });
        return response()->json(['status' => 'ok']);
    }

    public function json(Request $request)
    {
        if (($user = $this->requireLogin($request)) instanceof RedirectResponse)
            return $user;
        $builder = Stage::query();
        if ($pid = $request->pipeline_id) {
            $builder->where('pipeline_id', $pid);
        }
        $list = $builder->get()->pluck('name', 'id');
        return response()->json($list);
    }

    private function requireLogin(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse)
            return $u;
        return $u;
    }
}
