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
    Request,
    JsonResponse
};
use Illuminate\Support\Facades\{
    DB,
    Log,
};
use App\Traits\{
    ChecksLogin,
    ChecksPermissions
};

final class DocumentUploadController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::DOC_UP . '.index';

    public function index(Request $request): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($redirect = self::guard($request, PermissionsConstants::MNG_DOC, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $creatorId = $user?->creatorId();
        Log::info("$action loading list", [UsersConstants::COL_USER_ID => $user?->id]);
        $documents = $user[UsersConstants::COL_TP] === PermissionsConstants::CPN
            ? DocumentUpload::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get()
            : DocumentUpload::whereIn('role', [
                $user?->roles->first()->id,
                0
            ])->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
        return response()->view(ViewsConstants::DOC_UP . '.' . __FUNCTION__, compact('documents'));
    }

    public function create(Request $request): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($redirect = self::guard($request, 'create document', self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $roles = Role::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck('name', 'id')->prepend('All', '0');
        Log::info("$action preparing form", [UsersConstants::COL_USER_ID => $user?->id]);
        return response()->view(ViewsConstants::DOC_UP . '.' . __FUNCTION__, compact('roles'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($redirect = self::guard($request, 'create document', self::REDIRECT_INDEX)) !== true)
            return $redirect;

        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($resp = $this->validateInput($request, [
            'name' => 'required|string',
            'document' => 'nullable|file|max:' . SettingsConstants::MAX_U_SIZE_DEF
        ], $action)) return $resp;

        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'role', 'description']);
            $data[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();

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
            Log::error("$action error", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action error", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function edit(Request $request, string|int $id): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($redirect = self::guard($request, 'edit document', self::REDIRECT_INDEX)) !== true)
            return $redirect;

        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $doc = DocumentUpload::findOrFail($id);
        if ($doc->created_by !== $user?->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

        $roles = Role::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck('name', 'id')->prepend('All', '0');

        Log::info("$action loading", ['document_id' => $id, UsersConstants::COL_USER_ID => $user?->id]);

        return response()->view(ViewsConstants::DOC_UP . '.edit', compact('roles', 'doc'));
    }

    public function update(Request $request, string|int $id): RedirectResponse|JsonResponse
    {
        $action = __METHOD__;
        if (($redirect = self::guard($request, 'edit document', self::REDIRECT_INDEX)) !== true)
            return $redirect;

        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $doc = DocumentUpload::findOrFail($id);
        if ($doc->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_INDEX)
            );

        if ($resp = $this->validateInput($request, [
            'name'     => 'required|string',
            'document' => 'nullable|file|max:' . SettingsConstants::MAX_U_SIZE_DEF
        ], $action)) return $resp;

        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'role', 'description']);

            if ($file = $request->file('document')) {
                if ($doc->document) {
                    $deleteResult = Utility::deleteFile(
                        'uploads/documentUpload/' . $doc->document
                    );
                    if (empty($deleteResult['flag'])) {
                        Log::warning("$action old-file deletion failed", [
                            'document' => $doc->document,
                            'msg'      => $deleteResult['msg'] ?? 'unknown'
                        ]);
                    }
                }
                $safe    = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $fileName = time() . '_' . $safe;
                $upload  = Utility::uploadFile(
                    $request,
                    'document',
                    $fileName,
                    'uploads/documentUpload',
                    []
                );
                if (!$upload['flag']) {
                    Log::error("$action upload failed", ['msg' => $upload['msg']]);
                    return redirect()
                        ->back()
                        ->with('error', __($upload['msg']));
                }
                $data['document'] = $fileName;
            }

            $doc->update($data);

            DB::commit();
            Log::info("$action success", ['document_id' => $id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Document successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action error", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $action = __METHOD__;
        if (($redirect = self::guard($request, 'delete document', self::REDIRECT_INDEX)) !== true)
            return $redirect;

        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $doc = DocumentUpload::findOrFail($id);
        if ($doc->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                $action,
                route(self::REDIRECT_INDEX)
            );

        DB::beginTransaction();
        try {
            if ($doc->document) {
                $deleteResult = Utility::deleteFile(
                    'uploads/documentUpload/' . $doc->document
                );
                if (empty($deleteResult['flag'])) {
                    Log::warning("$action file deletion failed", [
                        'document' => $doc->document,
                        'msg'      => $deleteResult['msg'] ?? 'unknown'
                    ]);
                }
            }
            $doc->delete();

            DB::commit();
            Log::info("$action deleted", ['document_id' => $id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Document successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action error", [
                'error' => $e->getMessage(),
            ]);
            Log::error("$action error", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
