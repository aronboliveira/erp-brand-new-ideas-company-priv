<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\DocumentUpload;
use App\Models\{Role, Utility};
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    DB,
    Log,
    View as ViewFacade
};
use Illuminate\View\View;
use App\Traits\{
    ChecksLogin,
    ChecksPermissions
};

final class DocumentUploadController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::DOC_UP . '.index';

    public function index(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::DOC_UP . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::MNG_DOC, self::REDIRECT_INDEX)) !== true) return $redirect;

            $user = $userOrRedirect;
            $creatorId = $user?->creatorId();
            Log::info("$action loading list", [UsersConstants::COL_USER_ID => $user?->id]);

            $documents = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN
                ? DocumentUpload::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->get()
                : DocumentUpload::whereIn('role', [$user?->roles->first()->id, 0])
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                ->get();

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('documents'));
        });
    }

    public function create(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::DOC_UP . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create document', self::REDIRECT_INDEX)) !== true) return $redirect;

            $user = $userOrRedirect;
            $roles = Role::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id')->prepend('All', '0');

            Log::info("$action preparing form", [UsersConstants::COL_USER_ID => $user?->id]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('roles'));
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'create document', self::REDIRECT_INDEX)) !== true) return $redirect;

            $user = $userOrRedirect;

            DB::beginTransaction();
            try {
                $data = $request->only(['name', 'role', 'description']);
                $data[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();

                if ($file = $request->file('document')) {
                    $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                    $fileName = time() . '_' . $safe;
                    $upload = Utility::uploadFile($request, 'document', $fileName, 'uploads/documentUpload', []);
                    if (!$upload['flag']) {
                        Log::error("$action upload failed", ['msg' => $upload['msg']]);
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $data['document'] = $fileName;
                }

                $doc = DocumentUpload::create($data);

                DB::commit();
                Log::info("$action success", ['document_id' => $doc->id]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Document successfully uploaded.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$action error", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action error", [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, string|int $id): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::DOC_UP . '.edit';

        return $this->measureProfile($action, function () use ($request, $id, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit document', self::REDIRECT_INDEX)) !== true) return $redirect;

            $user = $userOrRedirect;
            $doc = DocumentUpload::findOrFail($id);
            if ($doc->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

            $roles = Role::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->pluck('name', 'id')->prepend('All', '0');

            Log::info("$action loading", ['document_id' => $id, UsersConstants::COL_USER_ID => $user?->id]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('roles', 'doc'));
        });
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'edit document', self::REDIRECT_INDEX)) !== true) return $redirect;

            $user = $userOrRedirect;
            $doc = DocumentUpload::findOrFail($id);
            if ($doc->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

            DB::beginTransaction();
            try {
                $data = $request->only(['name', 'role', 'description']);

                if ($file = $request->file('document')) {
                    if ($doc->document) {
                        $deleteResult = Utility::deleteFile('uploads/documentUpload/' . $doc->document);
                        if (empty($deleteResult['flag'])) {
                            Log::warning("$action old-file deletion failed", [
                                'document' => $doc->document,
                                'msg' => $deleteResult['msg'] ?? 'unknown'
                            ]);
                        }
                    }
                    $safe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                    $fileName = time() . '_' . $safe;
                    $upload = Utility::uploadFile($request, 'document', $fileName, 'uploads/documentUpload', []);
                    if (!$upload['flag']) {
                        Log::error("$action upload failed", ['msg' => $upload['msg']]);
                        return redirect()->back()->with('error', __($upload['msg']));
                    }
                    $data['document'] = $fileName;
                }

                $doc->update($data);

                DB::commit();
                Log::info("$action success", ['document_id' => $id]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Document successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$action error", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action error", [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'delete document', self::REDIRECT_INDEX)) !== true) return $redirect;

            $user = $userOrRedirect;
            $doc = DocumentUpload::findOrFail($id);
            if ($doc->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

            DB::beginTransaction();
            try {
                if ($doc->document) {
                    $deleteResult = Utility::deleteFile('uploads/documentUpload/' . $doc->document);
                    if (empty($deleteResult['flag'])) {
                        Log::warning("$action file deletion failed", [
                            'document' => $doc->document,
                            'msg' => $deleteResult['msg'] ?? 'unknown'
                        ]);
                    }
                }
                $doc->delete();

                DB::commit();
                Log::info("$action deleted", ['document_id' => $id]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Document successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action error", ['error' => $e->getMessage()]);
                Log::error("$action error", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }
}
