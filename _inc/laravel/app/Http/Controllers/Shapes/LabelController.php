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
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Illuminate\Auth\Access\AuthorizationException;

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
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($r = self::guard($req, PermissionsConstants::MNG_LB)) return $r;
        try {
            $ownerId  = $u->ownerId();
            $rows     = Label::select(DatabaseConstants::TABLE_LBL . '.*', DatabaseConstants::TABLE_PIPELINES . '.name as pipeline')
                ->join(DatabaseConstants::TABLE_PIPELINES, DatabaseConstants::TABLE_PIPELINES . '.id', '=', DatabaseConstants::TABLE_LBL . '.pipeline_id')
                ->where(DatabaseConstants::TABLE_PIPELINES . '.' . DatabaseConstants::TABLE_CREATOR, $ownerId)
                ->where(DatabaseConstants::TABLE_LBL . '.' . DatabaseConstants::TABLE_CREATOR, $ownerId)
                ->orderBy(DatabaseConstants::TABLE_LBL . '.pipeline_id')
                ->get();
            $pipelines = [];
            foreach ($rows as $row) {
                $pid = $row->pipeline_id;
                if (!isset($pipelines[$pid])) {
                    $pipelines[$pid] = ['name' => $row->pipeline, 'labels' => []];
                }
                $pipelines[$pid]['labels'][] = $row;
            }
            Log::info(__METHOD__ . ' fetched', ['groups' => count($pipelines)]);
            return response()->view(ViewsConstants::LBL . '.index', compact('pipelines'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $req): Response|RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($r = self::guard($req, 'create label')) return $r;
        $ownerId  = $u->ownerId();
        $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
        $colors   = Label::$colors;
        return response()->view(ViewsConstants::LBL . '.create', compact('pipelines', 'colors'));
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($r = self::guard($req, 'create label')) return $r;
        $v = Validator::make($req->all(), [
            'name'        => 'required|string|max:20',
            'pipeline_id' => 'required|exists:pipelines,id',
            'color'       => 'required|string'
        ]);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
            return redirect()->route(self::INDEX_ROUTE)->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $data = $req->only(['name', 'color', 'pipeline_id']);
            $data['created_by'] = $u->ownerId();
            Log::info(__METHOD__ . ' creating', ['data' => $data]);
            Label::create($data);
            DB::commit();
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Label successfully created!'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(Label $label): RedirectResponse
    {
        return redirect()->route(self::INDEX_ROUTE);
    }

    public function edit(Label $label, Request $req): Response|RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($r = self::guard($req, 'edit label')) return $r;
        if (!$this->isOwner($label)) {
            return defaultPermissionDenial($req, new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }
        $ownerId  = $u->ownerId();
        $pipelines = Pipeline::where('created_by', $ownerId)->pluck('name', 'id');
        $colors   = Label::$colors;
        return response()->view(ViewsConstants::LBL . '.edit', compact('label', 'pipelines', 'colors'));
    }

    public function update(Request $req, Label $label): RedirectResponse|JsonResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::guard($req, 'edit label')) return $r;
        if (!$this->isOwner($label)) {
            return defaultPermissionDenial($req, new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }
        $v = Validator::make($req->all(), [
            'name'        => 'required|string|max:20',
            'pipeline_id' => 'required|exists:pipelines,id',
            'color'       => 'required|string'
        ]);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
            return redirect()->route(self::INDEX_ROUTE)->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $updates = $req->only(['name', 'color', 'pipeline_id']);
            Log::info(__METHOD__ . ' updating', ['id' => $label->id, 'updates' => $updates]);
            $label->update($updates);
            DB::commit();
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Label successfully updated!'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Label $label, Request $req): RedirectResponse
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::guard($req, 'delete label')) return $r;
        if (!$this->isOwner($label)) {
            return defaultPermissionDenial($req, new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }
        DB::beginTransaction();
        try {
            Log::info(__METHOD__ . ' deleting', ['id' => $label->id]);
            $label->delete();
            DB::commit();
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Label successfully deleted!'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
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

    private function isOwner(Label $label): bool
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $owner = $label[DatabaseConstants::TABLE_CREATOR] === $user?->ownerId();
        Log::info(__METHOD__ . ' ownership', ['label_id' => $label->id, 'is_owner' => $owner]);
        return $owner;
    }
}
