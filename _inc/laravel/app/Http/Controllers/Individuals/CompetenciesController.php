<?php

namespace App\Http\Controllers\Individuals;

use App\Http\Controllers\Abstracts\Controller;

use App\Models\{Competencies, PerformanceType};
use Illuminate\Auth\Access\AuthorizationException;
use App\Config\Constants\{DatabaseConstants as DC, ViewsConstants as VW};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewFacade;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class CompetenciesController extends Controller
{
    private const PERM_MANAGE = 'Manage Competencies';
    private const PERM_CREATE = 'Create Competencies';
    private const PERM_EDIT   = 'Edit Competencies';
    private const PERM_DELETE = 'Delete Competencies';

    /** @return \Illuminate\View\View|RedirectResponse */
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function index(Request $req)
    {
        $action = 'CompetenciesController@index';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($auth = self::authOrDeny($req, self::PERM_MANAGE, $action)) !== true) return $auth;

            $t = microtime(true);
            $competencies = Competencies::where(
                DC::COL_TABLE_CREATOR,
                $req->user()->creatorId()
            )->get();
            $this->logExecutionTime($t, $action . '::query', 'completed');

            $view = VW::CPT . '.index';
            if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");

            return view($view, compact('competencies'));
        }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
    }

    /** @return \Illuminate\View\View|RedirectResponse */
    public function create(Request $req)
    {
        $action = 'CompetenciesController@create';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($auth = self::authOrDeny($req, self::PERM_CREATE, $action)) !== true) return $auth;

            $t = microtime(true);
            $performanceTypes = PerformanceType::where(
                DC::COL_TABLE_CREATOR,
                $req->user()->creatorId()
            )->pluck('name', 'id')->prepend('Select Type', '');
            $this->logExecutionTime($t, $action . '::loadPerformanceTypes', 'completed');

            $view = VW::CPT . '.create';
            if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");

            return view($view, compact('performanceTypes'));
        }, ['uri' => $req->getRequestUri()]);
    }

    /** @return RedirectResponse|null */
    public function store(Request $req)
    {
        $action = 'CompetenciesController@store';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($auth = self::authOrDeny($req, self::PERM_CREATE, $action)) !== true) return $auth;

            $t = microtime(true);
            $resp = self::validateInput($req, ['name' => 'required', 'type' => 'required']);
            $this->logExecutionTime($t, $action . '::validate', $resp ? 'failed' : 'completed');
            if ($resp) return $resp;

            try {
                $t = microtime(true);
                Competencies::create([
                    'name'       => $req->name,
                    'type'       => $req->type,
                    DC::COL_TABLE_CREATOR => $req->user()->creatorId(),
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->route(VW::CPT . '.index')
                    ->with('success', __('Competencies successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /** @return \Illuminate\View\View|RedirectResponse */
    public function show(Competencies $competency)
    {
        $action = 'CompetenciesController@show';
        // your original just redirected — keep it, but still measure
        return $this->measureProfile($action, function () {
            return redirect()->route(VW::CPT . '.index');
        });
    }

    /** @return \Illuminate\View\View|RedirectResponse */
    public function edit(Request $req, Competencies $competency)
    {
        $action = 'CompetenciesController@edit';
        return $this->measureProfile($action, function () use ($req, $competency, $action) {
            if (($auth = self::authOrDeny($req, self::PERM_EDIT, $action)) !== true) return $auth;

            $t = microtime(true);
            $ownerOk = ($competency->created_by === $req->user()->creatorId());
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($req, new AuthorizationException(), $action);
            }

            $t = microtime(true);
            $performanceTypes = PerformanceType::where(
                DC::COL_TABLE_CREATOR,
                $req->user()->creatorId()
            )->pluck('name', 'id')->prepend('Select Type', '');
            $this->logExecutionTime($t, $action . '::loadPerformanceTypes', 'completed');

            $view = VW::CPT . '.edit';
            if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");

            return view($view, compact('competency', 'performanceTypes'));
        }, ['competency_id' => $competency->id, 'uri' => $req->getRequestUri()]);
    }

    /** @return RedirectResponse|null */
    public function update(Request $req, Competencies $competency)
    {
        $action = 'CompetenciesController@update';
        return $this->measureProfile($action, function () use ($req, $competency, $action) {
            if (($auth = self::authOrDeny($req, self::PERM_EDIT, $action)) !== true) return $auth;

            $t = microtime(true);
            $ownerOk = ($competency->created_by === $req->user()->creatorId());
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($req, new AuthorizationException(), $action);
            }

            $t = microtime(true);
            $resp = self::validateInput($req, ['name' => 'required', 'type' => 'required']);
            $this->logExecutionTime($t, $action . '::validate', $resp ? 'failed' : 'completed');
            if ($resp) return $resp;

            try {
                $t = microtime(true);
                $competency->update([
                    'name' => $req->name,
                    'type' => $req->type,
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->route(VW::CPT . '.index')
                    ->with('success', __('Competencies successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['competency_id' => $competency->id, 'uri' => $req->getRequestUri()]);
    }

    /** @return RedirectResponse|null */
    public function destroy(Request $req, Competencies $competency)
    {
        $action = 'CompetenciesController@destroy';
        return $this->measureProfile($action, function () use ($req, $competency, $action) {
            if (($auth = self::authOrDeny($req, self::PERM_DELETE, $action)) !== true) return $auth;

            $t = microtime(true);
            $ownerOk = ($competency->created_by === $req->user()->creatorId());
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($req, new AuthorizationException(), $action);
            }

            try {
                $t = microtime(true);
                $competency->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return redirect()->route(VW::CPT . '.index')
                    ->with('success', __('Competencies successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['competency_id' => $competency->id, 'uri' => $req->getRequestUri()]);
    }

    /**
     * Permission helper that returns true if authorized,
     * or a RedirectResponse from defaultPermissionDenial when not.
     */
    private static function authOrDeny(Request $req, string $perm, string $action): bool|RedirectResponse
    {
        if ($req->user()->can($perm)) return true;

        return defaultPermissionDenial(
            $req,
            new AuthorizationException(),
            $action
        );
    }

    private static function validateInput(Request $req, array $rules): ?RedirectResponse
    {
        $v = Validator::make($req->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }
}
