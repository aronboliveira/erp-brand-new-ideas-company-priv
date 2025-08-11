<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\Source;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{DB, Log, Validator};

class SourceController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware(
            [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
            ]
        );
    }

    private const REDIRECT_INDEX = ViewsConstants::SRC . '.index';

    // List all sources.
    public function index(Request $request): RedirectResponse|\Illuminate\View\View|null
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_SRC, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $creatorId = $request->user()->creatorId();
            $sources  = Source::where('created_by', $creatorId)->get();
            Log::info(__METHOD__, ['user_id' => $request->user()->id, 'count' => $sources->count()]);
            return view(ViewsConstants::SRC . '.index', compact('sources'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    // Show the create form.
    public function create(Request $request): RedirectResponse|string
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create source', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        Log::info(__METHOD__, ['user_id' => $request->user()->id]);
        return view(ViewsConstants::SRC . '.create');
    }

    public function show(Request $request, Source $source): RedirectResponse|string
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            $redirect = self::guard(
                $request,
                'view source',
                self::REDIRECT_INDEX
            )
        ) return $redirect;
        if ($source[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        Log::info(__METHOD__, [
            'source_id' => $source->id,
            'user_id'   => $request->user()->id
        ]);
        return view(ViewsConstants::SRC . '.show', compact('source'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'create source', self::REDIRECT_INDEX)) !== true)
            return $redirect;

        $rules = ['name' => 'required|string|max:20'];
        $v    = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', $v->errors()->toArray());
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('error', $v->errors()->first());
        }

        DB::beginTransaction();
        try {
            $source = Source::create([
                'name'       => $request->input('name'),
                'created_by' => $request->user()->creatorId(),
            ]);

            Log::info(__METHOD__ . ' success', ['source_id' => $source->id]);
            DB::commit();

            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Source successfully created!'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    // Show the edit form.
    public function edit(Request $request, Source $source): RedirectResponse|string
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'edit source', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($source[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );

        Log::info(__METHOD__, ['source_id' => $source->id]);

        return view(ViewsConstants::SRC . '.edit', compact('source'));
    }

    public function update(Request $request, Source $source): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'edit source', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($source[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );

        $rules = ['name' => 'required|string|max:20'];
        $v    = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', $v->errors()->toArray());
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('error', $v->errors()->first());
        }

        DB::beginTransaction();
        try {
            $source->update(['name' => $request->input('name')]);
            Log::info(__METHOD__ . ' success', ['source_id' => $source->id]);
            DB::commit();

            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Source successfully updated!'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, Source $source): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        if (($redirect = self::guard($request, 'delete source', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($source[DatabaseConstants::TABLE_CREATOR] !== $request->user()->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );

        DB::beginTransaction();
        try {
            $source->delete();
            Log::info(__METHOD__ . ' deleted', ['source_id' => $source->id]);
            DB::commit();

            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Source successfully deleted!'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error', ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
