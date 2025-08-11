<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\{
    Models\BugStatus,
    Traits\ChecksLogin
};
use Illuminate\{
    Auth\Access\AuthorizationException,
    Support\Facades\Log,
    Validation\ValidationException,
    View\View
};
use Illuminate\Database\{
    Eloquent\ModelNotFoundException,
    QueryException
};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BugStatusController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $request): View|JsonResponse
    {
        try {
            if ($denial = self::deny($request, PermissionsConstants::MNG_BUG_STT))
                return $denial;
            $creatorId = $request->user()?->creatorId();
            $bugStatuses = BugStatus::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->orderBy(ActivitiesConstants::COL_OD)
                ->get();
            return view(ViewsConstants::BUG_STT . '.' . __FUNCTION__, [
                'bug_statuses' => $bugStatuses,
            ]);
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@index authorization failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_FORBIDDEN, 'Forbidden');
        } catch (Throwable $e) {
            Log::critical('BugStatusController@index failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to load bug statuses');
        }
    }

    public function create(Request $request): View|JsonResponse
    {
        try {
            if ($denial = self::deny($request, 'create bug status'))
                return $denial;
            return view(ViewsConstants::BUG_STT . '.' . __FUNCTION__);
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@create authorization failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_FORBIDDEN, 'Forbidden');
        } catch (Throwable $e) {
            Log::critical('BugStatusController@create failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to show creation form');
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        try {
            if ($denial = self::deny($request, 'create bug status'))
                return $denial;
            $data = $request->validate([
                ActivitiesConstants::COL_TT => 'required|string|max:20',
            ]);
            $creatorId = $request->user()->creatorId();
            $maxOrder = BugStatus::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->max(ActivitiesConstants::COL_OD);
            BugStatus::create([
                ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                ActivitiesConstants::COL_OD      => ($maxOrder ?? -1) + 1,
                DatabaseConstants::TABLE_CREATOR => $creatorId,
            ]);
            return redirect()
                ->route(ViewsConstants::BUG_STT . '.index')
                ->with('success', __('Bug status successfully created.'));
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@store authorization failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_FORBIDDEN, 'Forbidden');
        } catch (ValidationException $e) {
            Log::warning('BugStatusController@store validation failed', ['errors' => $e->errors()]);
            return $this->errorResponse(
                $request,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->validator->errors()->first(),
                $e->errors()
            );
        } catch (QueryException $e) {
            Log::error('BugStatusController@store database error', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Database error');
        } catch (Throwable $e) {
            Log::critical('BugStatusController@store failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to create bug status');
        }
    }

    public function edit(Request $request, int|string $id): View|JsonResponse
    {
        try {
            if ($denial = self::deny($request, 'edit bug status'))
                return $denial;
            $bugStatus = BugStatus::findOrFail($id);
            if (!self::isOwner($bugStatus))
                throw new AuthorizationException('You do not own this resource');
            return view(ViewsConstants::BUG_STT . '.' . __FUNCTION__, [
                'bug_status' => $bugStatus,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning('BugStatusController@edit not found', ['id' => $id]);
            return $this->errorResponse($request, Response::HTTP_NOT_FOUND, 'Bug status not found');
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@edit authorization failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_FORBIDDEN, 'Forbidden');
        } catch (Throwable $e) {
            Log::critical('BugStatusController@edit failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to load edit form');
        }
    }

    public function update(Request $request, int|string $id): RedirectResponse|JsonResponse
    {
        try {
            if ($denial = self::deny($request, 'edit bug status'))
                return $denial;
            $bugStatus = BugStatus::findOrFail($id);
            if (!self::isOwner($bugStatus))
                throw new AuthorizationException('You do not own this resource');
            $data = $request->validate([
                ActivitiesConstants::COL_TT => 'required|string|max:20',
            ]);
            $bugStatus->update([
                ActivitiesConstants::COL_TT => $data[ActivitiesConstants::COL_TT],
            ]);
            return redirect()
                ->route(ViewsConstants::BUG_STT . '.index')
                ->with('success', __('Bug status successfully updated.'));
        } catch (ModelNotFoundException $e) {
            Log::warning('BugStatusController@update not found', ['id' => $id]);
            return $this->errorResponse($request, Response::HTTP_NOT_FOUND, 'Bug status not found');
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@update authorization failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_FORBIDDEN, 'Forbidden');
        } catch (ValidationException $e) {
            Log::warning('BugStatusController@update validation failed', ['errors' => $e->errors()]);
            return $this->errorResponse(
                $request,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->validator->errors()->first(),
                $e->errors()
            );
        } catch (Throwable $e) {
            Log::critical('BugStatusController@update failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to update bug status');
        }
    }

    public function destroy(Request $request, int|string $id): RedirectResponse|JsonResponse
    {
        try {
            if ($denial = self::deny($request, 'delete bug status'))
                return $denial;
            $bugStatus = BugStatus::findOrFail($id);
            if (!self::isOwner($bugStatus))
                throw new AuthorizationException('You do not own this resource');
            $bugStatus->delete();
            return redirect()
                ->route(ViewsConstants::BUG_STT . '.index')
                ->with('success', __('Bug status successfully deleted.'));
        } catch (ModelNotFoundException $e) {
            Log::warning('BugStatusController@destroy not found', ['id' => $id]);
            return $this->errorResponse($request, Response::HTTP_NOT_FOUND, 'Bug status not found');
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@destroy authorization failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_FORBIDDEN, 'Forbidden');
        } catch (Throwable $e) {
            Log::critical('BugStatusController@destroy failed', ['exception' => $e]);
            return $this->errorResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to delete bug status');
        }
    }

    public function order(Request $request): Response|JsonResponse
    {
        try {
            if ($denial = self::deny($request, 'edit bug status'))
                return $denial;
            $positions = $request->input(ActivitiesConstants::COL_OD, []);
            foreach ($positions as $index => $id)
                BugStatus::where('id', $id)
                    ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                    ->update([ActivitiesConstants::COL_OD => $index]);
            return response()->noContent(Response::HTTP_OK);
        } catch (AuthorizationException $e) {
            Log::warning('BugStatusController@order authorization failed', ['exception' => $e]);
            return response()->json(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            Log::critical('BugStatusController@order failed', ['exception' => $e]);
            return response()->json(['error' => 'Unable to reorder statuses'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(): RedirectResponse
    {
        return redirect()->route(ViewsConstants::BUG_STT . '.index');
    }

    private static function deny(Request $request, string $permission): RedirectResponse|JsonResponse|null
    {
        if ($request->user()->can($permission))
            return null;
        return defaultPermissionDenial(
            $request,
            new AuthorizationException($permission),
            __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
        );
    }

    private static function isOwner(BugStatus $status): bool
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
            return false;
        /** @var \App\Models\User $user */
        $user = $userOrRedirect;
        return $status[DatabaseConstants::TABLE_CREATOR] === $user?->creatorId();
    }

    /**
     * Build either a JSON error or a RedirectResponse + flash
     *
     * @param  Request      $request
     * @param  int          $statusCode
     * @param  string       $message
     * @param  array|null   $errors
     * @return JsonResponse|RedirectResponse
     */
    private function errorResponse(
        Request $request,
        int     $statusCode,
        string  $message,
        array   $errors = null
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            $payload = ['message' => $message];
            if ($errors)
                $payload['errors'] = $errors;
            return response()->json($payload, $statusCode);
        }
        return redirect()->back()
            ->withErrors($errors ?? ['error' => $message])
            ->withInput();
    }
}
