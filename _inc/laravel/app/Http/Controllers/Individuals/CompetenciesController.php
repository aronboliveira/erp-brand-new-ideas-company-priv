<?php

namespace App\Http\Controllers;

use App\Models\{Competencies, PerformanceType};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Validator;

class CompetenciesController extends Controller
{
    private const PERM_MANAGE = 'Manage Competencies';
    private const PERM_CREATE = 'Create Competencies';
    private const PERM_EDIT  = 'Edit Competencies';
    private const PERM_DELETE = 'Delete Competencies';

    /** @return \Illuminate\View\View|RedirectResponse */
    public function index(Request $req)
    {
        if (!self::auth($req, self::PERM_MANAGE)) return redirect()->back();
        $competencies = Competencies::where(
            'created_by',
            $req->user()->creatorId()
        )->get();
        return view('competencies.index', compact('competencies'));
    }

    /** @return \Illuminate\View\View|RedirectResponse */
    public function create(Request $req)
    {
        if (!self::auth($req, self::PERM_CREATE)) return redirect()->back();
        $performanceTypes = PerformanceType::where(
            'created_by',
            $req->user()->creatorId()
        )->pluck('name', 'id')->prepend('Select Type', '');
        return view('competencies.create', compact('performanceTypes'));
    }

    /** @return RedirectResponse|null */
    public function store(Request $req)
    {
        if (!self::auth($req, self::PERM_CREATE)) return redirect()->back();
        if ($resp = self::validateInput($req, [
            'name' => 'required',
            'type' => 'required',
        ])) return $resp;
        try {
            Competencies::create([
                'name'       => $req->name,
                'type'       => $req->type,
                'created_by' => $req->user()->creatorId(),
            ]);
            return redirect()->route('competencies.index')
                ->with('success', __('Competencies successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    /** @return \Illuminate\View\View|RedirectResponse */
    public function show(Competencies $competency)
    {
        return redirect()->route('competencies.index');
    }

    /** @return \Illuminate\View\View|RedirectResponse */
    public function edit(Request $req, Competencies $competency)
    {
        if (!self::auth($req, self::PERM_EDIT)) return redirect()->back();
        if ($competency->created_by !== $req->user()->creatorId())
            return defaultPermissionDenial(
                $req,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
        $performanceTypes = PerformanceType::where(
            'created_by',
            $req->user()->creatorId()
        )->pluck('name', 'id')->prepend('Select Type', '');
        return view('competencies.edit', compact('competency', 'performanceTypes'));
    }

    /** @return RedirectResponse|null */
    public function update(Request $req, Competencies $competency)
    {
        if (!self::auth($req, self::PERM_EDIT)) return redirect()->back();
        if ($competency->created_by !== $req->user()->creatorId())
            return defaultPermissionDenial(
                $req,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
        if ($resp = self::validateInput($req, [
            'name' => 'required',
            'type' => 'required',
        ])) return $resp;
        try {
            $competency->update([
                'name' => $req->name,
                'type' => $req->type,
            ]);
            return redirect()->route('competencies.index')
                ->with('success', __('Competencies successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    /** @return RedirectResponse|null */
    public function destroy(Request $req, Competencies $competency)
    {
        if (!self::auth($req, self::PERM_DELETE)) return redirect()->back();
        if ($competency->created_by !== $req->user()->creatorId())
            return defaultPermissionDenial(
                $req,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
        try {
            $competency->delete();
            return redirect()->route('competencies.index')
                ->with('success', __('Competencies successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    private static function auth(Request $req, string $perm): bool
    {
        return $req->user()->can($perm) ?: !defaultPermissionDenial(
            $req,
            new AuthorizationException(),
            __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
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
