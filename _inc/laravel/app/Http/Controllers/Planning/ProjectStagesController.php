<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, UsersConstants, ViewsConstants};
use App\Models\{ProjectStages, Task};
use App\Traits\ChecksPermissions;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{DB, Log, Validator};
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class ProjectStagesController extends Controller
{
    use ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::PRJ_STG . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        if (($r = self::guard($request, 'manage project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        try {
            $user      = $request->user();
            Log::info(__METHOD__, [UsersConstants::COL_USER_ID => $user?->id]);
            $stages    = ProjectStages::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->orderBy('order')
                ->get();
            return view(ViewsConstants::PRJ_STG . '.' . __FUNCTION__, ['projectStages' => $stages]);
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (($r = self::guard($request, 'create project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        return view(ViewsConstants::PRJ_STG . '.' . __FUNCTION__);
    }

    public function store(Request $request): RedirectResponse
    {
        if (($r = self::guard($request, 'create project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        Log::info(__METHOD__, ['input' => $request->all()]);
        $v = Validator::make($request->all(), ['name' => 'required|string|max:20']);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
            return redirect()->route(self::ROUTE_INDEX)
                ->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $user   = $request->user();
            $last   = ProjectStages::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->orderByDesc('order')
                ->first();
            $stage  = ProjectStages::create([
                'name'       => $request->name,
                'color'      => '#' . $request->input('color', '000000'),
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                'order'      => $last ? $last->order + 1 : 0,
            ]);
            DB::commit();
            Log::info(__METHOD__ . ' created', ['stage_id' => $stage->id]);
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Project stage successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function edit(Request $request, int $id): View|JsonResponse|RedirectResponse
    {
        if (($r = self::guard($request, 'edit project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        try {
            $stage = ProjectStages::findOrFail($id);
            if ($stage[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId()) {
                return defaultPermissionDenial(
                    $request,
                    new \Illuminate\Auth\Access\AuthorizationException('edit project stage'),
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ROUTE_INDEX)
                );
            }
            return view(ViewsConstants::PRJ_STG . '.' . __FUNCTION__, ['projectStage' => $stage]);
        } catch (\Throwable $e) {
            return $e instanceof \Illuminate\Auth\Access\AuthorizationException
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__)
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        if (($r = self::guard($request, 'edit project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        Log::info(__METHOD__, ['id' => $id, 'input' => $request->all()]);
        $v = Validator::make($request->all(), ['name' => 'required|string|max:20']);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
            return redirect()->route(self::ROUTE_INDEX)
                ->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $user = $request->user();
            $stage = ProjectStages::findOrFail($id);
            if ($stage[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                throw new \Illuminate\Auth\Access\AuthorizationException('edit project stage');
            $stage->update([
                'name'  => $request->name,
                'color' => '#' . $request->input('color', '000000'),
            ]);
            DB::commit();
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Project stage successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return $e instanceof \Illuminate\Auth\Access\AuthorizationException
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX))
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        if (($r = self::guard($request, 'delete project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) return $r;
        try {
            $user = $request->user();
            $stage = ProjectStages::findOrFail($id);
            if ($stage[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                throw new \Illuminate\Auth\Access\AuthorizationException('delete project stage');
            }
            $used = Task::where('stage', $stage->id)->exists();
            if ($used) {
                return redirect()->route(self::ROUTE_INDEX)
                    ->with('error', __('Project task already assigned to this stage; please move them first.'));
            }
            $stage->delete();
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Project stage successfully deleted.'));
        } catch (\Throwable $e) {
            return $e instanceof \Illuminate\Auth\Access\AuthorizationException
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX))
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    public function order(Request $request): JsonResponse
    {
        if (($r = self::guard($request, 'move project stage', self::ROUTE_INDEX)) instanceof RedirectResponse) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
        Log::info(__METHOD__, ['order' => $request->input('order')]);
        DB::beginTransaction();
        try {
            foreach ($request->input('order', []) as $idx => $id) {
                ProjectStages::where('id', $id)->update(['order' => $idx]);
            }
            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return response()->json(['error' => __('An unexpected error occurred.')], 500);
        }
    }
}
