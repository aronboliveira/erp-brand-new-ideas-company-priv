<?php

namespace App\Http\Controllers;

use App\Models\TrainingType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\HttpFoundation\Response;

class TrainingTypeController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = 'trainingtype.index';

    public function index(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            if ($c = self::guard($request, 'manage training type', self::ROUTE_INDEX)) return $c;
            $user            = $request->user();
            $trainingtypes   = TrainingType::where('created_by', $user?->creatorId())->get();
            Log::info(__METHOD__ . ' fetched training types', [
                'user_id' => $user?->id,
                'count'   => $trainingtypes->count(),
            ]);
            return view('trainingtype.index', compact('trainingtypes'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function create(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            if ($c = self::guard($request, 'create training type', self::ROUTE_INDEX)) return $c;
            Log::info(__METHOD__ . ' displaying create form', [
                'user_id' => $request->user()->id,
            ]);
            return view('trainingtype.create');
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            if ($c = self::guard($request, 'create training type', self::ROUTE_INDEX)) return $c;
            Log::info(__METHOD__ . ' called', [
                'user_id' => $request->user()->id,
                'input'   => $request->only('name'),
            ]);
            $request->validate(['name' => 'required|string|max:100']);
            DB::transaction(function () use ($request) {
                $tt = TrainingType::create([
                    'name'       => $request->name,
                    'created_by' => $request->user()->creatorId(),
                ]);
                Log::info('TrainingType created', ['id' => $tt->id]);
            });
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('TrainingType successfully created.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $e->errors()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function show(TrainingType $trainingType): RedirectResponse
    {
        // ### show
        return redirect()->route(self::ROUTE_INDEX);
    }

    public function edit(Request $request, TrainingType $trainingType): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            if ($c = self::guard($request, 'edit training type', self::ROUTE_INDEX)) return $c;
            if ($trainingType->created_by !== $request->user()->creatorId()) {
                Log::warning('Unauthorized edit attempt', [
                    'user_id' => $request->user()->id, 'tt_id' => $trainingType->id,
                ]);
                return defaultPermissionDenial(
                    $request,
                    new \Illuminate\Auth\Access\AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ROUTE_INDEX)
                );
            }
            Log::info(__METHOD__ . ' displaying edit form', [
                'tt_id'   => $trainingType->id,
            ]);
            return view('trainingtype.edit', compact('trainingType'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function update(Request $request, TrainingType $trainingType): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            if ($c = self::guard($request, 'edit training type', self::ROUTE_INDEX)) return $c;
            if ($trainingType->created_by !== $request->user()->creatorId()) {
                Log::warning('Unauthorized update attempt', [
                    'user_id' => $request->user()->id, 'tt_id' => $trainingType->id,
                ]);
                return defaultPermissionDenial(
                    $request,
                    new \Illuminate\Auth\Access\AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ROUTE_INDEX)
                );
            }
            Log::info(__METHOD__ . ' called', [
                'tt_id' => $trainingType->id, 'input' => $request->only('name'),
            ]);
            $request->validate(['name' => 'required|string|max:100']);
            DB::transaction(function () use ($request, $trainingType) {
                $trainingType->update(['name' => $request->name]);
                Log::info('TrainingType updated', ['id' => $trainingType->id]);
            });
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('TrainingType successfully updated.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $e->errors()]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function destroy(Request $request, TrainingType $trainingType): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            if ($c = self::guard($request, 'delete training type', self::ROUTE_INDEX)) return $c;
            if ($trainingType->created_by !== $request->user()->creatorId()) {
                Log::warning('Unauthorized delete attempt', [
                    'user_id' => $request->user()->id, 'tt_id' => $trainingType->id,
                ]);
                return defaultPermissionDenial(
                    $request,
                    new \Illuminate\Auth\Access\AuthorizationException(),
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ROUTE_INDEX)
                );
            }
            DB::transaction(function () use ($trainingType) {
                $trainingType->delete();
                Log::info('TrainingType deleted', ['id' => $trainingType->id]);
            });
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('TrainingType successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' unexpected error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }
}
