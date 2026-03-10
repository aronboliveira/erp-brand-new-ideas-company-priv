<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Http\Controllers\Controller;
use App\Models\{Label, Pipeline};
use App\Traits\ChecksLogin;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};
use Illuminate\Auth\Access\AuthorizationException;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class LabelController extends Controller
{
    use ChecksLogin;

    private const INDEX_ROUTE = ViewsConstants::LBL . '.index';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::LBL . '.index';

        return $this->measureProfile($action, function () use ($req, $class, $action, $sig, $viewPath) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, PermissionsConstants::MNG_LB)) !== true) return $r;

            try {
                $t = microtime(true);
                $ownerId  = $u->ownerId();
                $rows     = Label::select(DatabaseConstants::TABLE_LBL . '.*', DatabaseConstants::TABLE_PIPELINES . '.name as pipeline')
                    ->join(DatabaseConstants::TABLE_PIPELINES, DatabaseConstants::TABLE_PIPELINES . '.id', '=', DatabaseConstants::TABLE_LBL . '.pipeline_id')
                    ->where(DatabaseConstants::TABLE_PIPELINES . '.' . DatabaseConstants::COL_TABLE_CREATOR, $ownerId)
                    ->where(DatabaseConstants::TABLE_LBL . '.' . DatabaseConstants::COL_TABLE_CREATOR, $ownerId)
                    ->orderBy(DatabaseConstants::TABLE_LBL . '.pipeline_id')
                    ->get();
                $this->logExecutionTime($t, $sig, 'fetchLabels');

                $t = microtime(true);
                $pipelines = [];
                foreach ($rows as $row) {
                    $pid = $row->pipeline_id;
                    if (!isset($pipelines[$pid])) {
                        $pipelines[$pid] = ['name' => $row->pipeline, 'labels' => []];
                    }
                    $pipelines[$pid]['labels'][] = $row;
                }
                $this->logExecutionTime($t, $sig, 'groupLabels');

                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');

                Log::info("$sig fetched", ['groups' => count($pipelines)]);
                return response()->view($viewPath, compact('pipelines'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

    public function create(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::LBL . '.create';

        return $this->measureProfile(function () use ($req, $action, $sig, $viewPath) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'create label')) !== true) return $r;

            $t = microtime(true);
            $ownerId  = $u->ownerId();
            $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
            $colors   = Label::$colors;
            $this->logExecutionTime($t, $sig, 'loadPipelinesColors');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, $sig, 'viewExistsCheck');

            return response()->view($viewPath, compact('pipelines', 'colors'));
        });
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($req, $class, $action, $sig) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'create label')) !== true) return $r;

            $t = microtime(true);
            $v = Validator::make($req->all(), [
                'name'        => 'required|string|max:20',
                'pipeline_id' => 'required|exists:pipelines,id',
                'color'       => 'required|string'
            ]);
            if ($v->fails()) {
                $this->logExecutionTime($t, $sig, 'validateFail');
                Log::warning("$sig validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->route(self::INDEX_ROUTE)->with('error', $v->errors()->first());
            }
            $this->logExecutionTime($t, $sig, 'validateSuccess');

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $data = $req->only(['name', 'color', 'pipeline_id']);
                $data['created_by'] = $u->ownerId();
                Label::create($data);
                DB::commit();
                $this->logExecutionTime($t, $sig, 'createLabelTx');

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Label successfully created!'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

    public function show(Label $label): RedirectResponse
    {
        // Simple redirect; profiling unnecessary
        return redirect()->route(self::INDEX_ROUTE)->with($label);
    }

    public function edit(Label $label, Request $req): Response|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::LBL . '.edit';

        return $this->measureProfile($action, function () use ($label, $req, $class, $action, $sig, $viewPath) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($r = self::guard($req, 'edit label')) !== true) return $r;
            if (!$this->isOwner($label)) {
                return defaultPermissionDenial($req, new AuthorizationException(), "$class::$action");
            }

            $t = microtime(true);
            $ownerId  = $u->ownerId();
            $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
            $colors   = Label::$colors;
            $this->logExecutionTime($t, $sig, 'loadPipelinesColors');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, $sig, 'viewExistsCheck');

            return response()->view($viewPath, compact('label', 'pipelines', 'colors'));
        });
    }

    public function update(Request $req, Label $label): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($req, $label, $class, $action, $sig) {
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if (($r = self::guard($req, 'edit label')) !== true) return $r;
            if (!$this->isOwner($label)) {
                return defaultPermissionDenial($req, new AuthorizationException(), "$class::$action");
            }

            $t = microtime(true);
            $v = Validator::make($req->all(), [
                'name'        => 'required|string|max:20',
                'pipeline_id' => 'required|exists:pipelines,id',
                'color'       => 'required|string'
            ]);
            if ($v->fails()) {
                $this->logExecutionTime($t, $sig, 'validateFail');
                Log::warning("$sig validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->route(self::INDEX_ROUTE)->with('error', $v->errors()->first());
            }
            $this->logExecutionTime($t, $sig, 'validateSuccess');

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $updates = $req->only(['name', 'color', 'pipeline_id']);
                $label->update($updates);
                DB::commit();
                $this->logExecutionTime($t, $sig, 'updateLabelTx');

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Label successfully updated!'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

    public function destroy(Label $label, Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($label, $req, $class, $action, $sig) {
            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if (($r = self::guard($req, 'delete label')) !== true) return $r;
            if (!$this->isOwner($label)) {
                return defaultPermissionDenial($req, new AuthorizationException(), "$class::$action");
            }

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $label->delete();
                DB::commit();
                $this->logExecutionTime($t, $sig, 'deleteLabelTx');

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Label successfully deleted!'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, "$class::$action");
            }
        });
    }

    private static function guard(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        $user = $req->user();
        Log::info(__METHOD__ . ' checking permission', ['user_id' => $user?->id, 'perm' => $perm]);
        if ($user?->can($perm)) {
            Log::info(__METHOD__ . ' granted', ['perm' => $perm]);
            return null;
        }
        Log::warning(__METHOD__ . ' denied', ['perm' => $perm]);
        return defaultPermissionDenial(
            $req,
            new AuthorizationException($perm),
            __CLASS__ . '::' . __FUNCTION__,
            route(self::INDEX_ROUTE)
        );
    }

    private function isOwner(Label $label): bool|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user  = $userOrRedirect;
        $owner = $label[DatabaseConstants::COL_TABLE_CREATOR] === $user?->ownerId();
        Log::info(__METHOD__ . ' ownership', ['label_id' => $label->id, 'is_owner' => $owner]);
        return $owner;
    }
}
