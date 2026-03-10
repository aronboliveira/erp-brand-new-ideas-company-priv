<?php

namespace App\Http\Controllers\Ssr;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    SettingsConstants as SC,
    UsersConstants as UC,
    ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{DocumentUpload, Role, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class DocumentUploadController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = VW::DOC_UP . '.index';
    private const UPLOAD_PATH = 'uploads/documentUpload';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    private function validateOwnership(
        Request $request,
        DocumentUpload $doc,
        mixed $user,
        string $action
    ): ?RedirectResponse {
        $file = __FILE__;
        $creatorId = $user?->creatorId() ?? null;
        $docCreator = $doc->created_by ?? null;
        if (empty($creatorId) || $docCreator !== $creatorId) {
            Log::warning("{$action} ownership validation failed", [
                'file' => $file,
                'class' => __CLASS__,
                'document_id' => $doc->id ?? null,
                'doc_creator' => $docCreator,
                'user_creator' => $creatorId
            ]);
            return defaultPermissionDenial(
                $request,
                new \Exception('Owner mismatch'),
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
        return null;
    }

    private function handleFileUpload(
        Request $request,
        string $action,
        ?string $existingFile = null
    ): array {
        $file = __FILE__;
        $result = ['success' => false, 'fileName' => null, 'error' => null];
        $uploadedFile = $request->file('document');
        if (empty($uploadedFile) || !is_object($uploadedFile)) {
            return $result;
        }
        try {
            if (!empty($existingFile) && is_string($existingFile)) {
                $deleteResult = Utility::deleteFile(self::UPLOAD_PATH . '/' . $existingFile);
                if (empty($deleteResult) || !is_array($deleteResult) || empty($deleteResult['flag'])) {
                    $msg = is_array($deleteResult) ? ($deleteResult['msg'] ?? 'unknown') : 'unknown';
                    Log::warning("{$action} old-file deletion failed", [
                        'file' => $file,
                        'class' => __CLASS__,
                        'document' => $existingFile,
                        'msg' => $msg
                    ]);
                }
            }
            $originalName = $uploadedFile->getClientOriginalName();
            $safe = is_string($originalName)
                ? preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName)
                : 'unnamed';
            $fileName = time() . '_' . $safe;
            $upload = Utility::uploadFile($request, 'document', $fileName, self::UPLOAD_PATH, []);
            if (empty($upload) || !is_array($upload) || empty($upload['flag'])) {
                $msg = is_array($upload) ? ($upload['msg'] ?? 'Upload failed') : 'Upload failed';
                Log::error("{$action} upload failed", [
                    'file' => $file,
                    'class' => __CLASS__,
                    'msg' => $msg
                ]);
                $result['error'] = $msg;
                return $result;
            }
            $result['success'] = true;
            $result['fileName'] = $fileName;
        } catch (\Throwable $e) {
            Log::error("{$action} file upload exception", [
                'file' => $file,
                'class' => __CLASS__,
                'error_class' => get_class($e),
                'message' => $e->getMessage()
            ]);
            $result['error'] = $e->getMessage();
        }
        return $result;
    }

    private function deleteDocumentFile(string $action, ?string $fileName): void
    {
        $file = __FILE__;
        if (empty($fileName) || !is_string($fileName)) {
            return;
        }
        try {
            $deleteResult = Utility::deleteFile(self::UPLOAD_PATH . '/' . $fileName);
            if (empty($deleteResult) || !is_array($deleteResult) || empty($deleteResult['flag'])) {
                $msg = is_array($deleteResult) ? ($deleteResult['msg'] ?? 'unknown') : 'unknown';
                Log::warning("{$action} file deletion failed", [
                    'file' => $file,
                    'class' => __CLASS__,
                    'document' => $fileName,
                    'msg' => $msg
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("{$action} file deletion exception", [
                'file' => $file,
                'class' => __CLASS__,
                'error_class' => get_class($e),
                'message' => $e->getMessage()
            ]);
        }
    }

    public function index(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::DOC_UP . '.' . $fn;
        return $this->measureProfile($action, function () use (
            $request,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, PMC::MNG_DOC, self::REDIRECT_INDEX);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $creatorId = $user?->creatorId() ?? null;
                Log::info("{$action} loading list", [
                    UC::COL_USER_ID => $user?->id ?? null
                ]);
                $userType = $user[UC::COL_TP] ?? null;
                $documents = collect();
                if ($userType === PMC::CPN) {
                    $documents = DocumentUpload::where(DC::COL_TABLE_CREATOR, $creatorId)->get();
                } else {
                    $roles = $user?->roles ?? null;
                    $firstRole = null;
                    if (!empty($roles) && is_object($roles) && method_exists($roles, 'first')) {
                        $firstRole = $roles->first();
                    }
                    $roleId = $firstRole?->id ?? 0;
                    $documents = DocumentUpload::whereIn('role', [$roleId, 0])
                        ->where(DC::COL_TABLE_CREATOR, $creatorId)
                        ->get();
                }
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return defaultUndefinedException(
                        $request,
                        new \RuntimeException("View '{$view}' not found"),
                        $action,
                        route(self::REDIRECT_INDEX)
                    );
                }
                return view($view, compact('documents'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::DOC_UP . '.' . $fn;
        return $this->measureProfile($action, function () use (
            $request,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'create document', self::REDIRECT_INDEX);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $creatorId = $user?->creatorId() ?? null;
                $roles = Role::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->pluck('name', 'id')
                    ->prepend('All', '0');
                Log::info("{$action} preparing form", [
                    UC::COL_USER_ID => $user?->id ?? null
                ]);
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return defaultUndefinedException(
                        $request,
                        new \RuntimeException("View '{$view}' not found"),
                        $action,
                        route(self::REDIRECT_INDEX)
                    );
                }
                return view($view, compact('roles'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use (
            $request,
            $action,
            $file,
            $cls,
            $fn
        ) {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse)
                return $userOrRedirect;
            $guard = self::guard($request, 'create document', self::REDIRECT_INDEX);
            if ($guard !== true)
                return $guard;
            $user = $userOrRedirect;
            DB::beginTransaction();
            try {
                $data = $request->only(['name', 'role', 'description']);
                if (!is_array($data)) {
                    $data = [];
                }
                $data[DC::COL_TABLE_CREATOR] = $user?->creatorId() ?? null;
                $uploadResult = $this->handleFileUpload($request, $action);
                if (!empty($request->file('document'))) {
                    if (!$uploadResult['success']) {
                        DB::rollBack();
                        $errMsg = $uploadResult['error'] ?? 'Upload failed';
                        return redirect()->back()->with('error', __($errMsg));
                    }
                    $data['document'] = $uploadResult['fileName'];
                }
                $doc = DocumentUpload::create($data);
                DB::commit();
                Log::info("{$action} success", [
                    'document_id' => $doc->id ?? null
                ]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Document successfully uploaded.'));
            } catch (\InvalidArgumentException $iae) {
                DB::rollBack();
                Log::error("{$action} InvalidArgumentException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($iae),
                    'message' => $iae->getMessage()
                ]);
                return defaultUndefinedException($request, $iae, $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("{$action} error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                Log::channel(SC::ERR_TRACE)->debug("{$action} error trace", [
                    'error' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, string|int $id): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        $view = VW::DOC_UP . '.edit';
        return $this->measureProfile($action, function () use (
            $request,
            $id,
            $view,
            $action,
            $file,
            $cls,
            $fn
        ) {
            try {
                $userOrRedirect = self::_checkLogin();
                if ($userOrRedirect instanceof RedirectResponse)
                    return $userOrRedirect;
                $guard = self::guard($request, 'edit document', self::REDIRECT_INDEX);
                if ($guard !== true)
                    return $guard;
                $user = $userOrRedirect;
                $doc = DocumentUpload::findOrFail($id);
                $ownershipError = $this->validateOwnership($request, $doc, $user, $action);
                if ($ownershipError !== null)
                    return $ownershipError;
                $creatorId = $user?->creatorId() ?? null;
                $roles = Role::where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->pluck('name', 'id')
                    ->prepend('All', '0');
                Log::info("{$action} loading", [
                    'document_id' => $id,
                    UC::COL_USER_ID => $user?->id ?? null
                ]);
                if (!ViewFacade::exists($view)) {
                    Log::error("{$action} View not found", [
                        'file' => $file,
                        'class' => $cls,
                        'view' => $view
                    ]);
                    return defaultUndefinedException(
                        $request,
                        new \RuntimeException("View '{$view}' not found"),
                        $action,
                        route(self::REDIRECT_INDEX)
                    );
                }
                return view($view, compact('roles', 'doc'));
            } catch (ModelNotFoundException $mnf) {
                Log::warning("{$action} Document not found", [
                    'file' => $file,
                    'class' => $cls,
                    'document_id' => $id,
                    'error_class' => get_class($mnf)
                ]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('error', __('Document not found.'));
            } catch (\RuntimeException $re) {
                Log::error("{$action} RuntimeException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($re),
                    'message' => $re->getMessage()
                ]);
                return defaultUndefinedException($request, $re, $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error("{$action} Unexpected error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use (
            $request,
            $id,
            $action,
            $file,
            $cls,
            $fn
        ) {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse)
                return $userOrRedirect;
            $guard = self::guard($request, 'edit document', self::REDIRECT_INDEX);
            if ($guard !== true)
                return $guard;
            $user = $userOrRedirect;
            $doc = null;
            try {
                $doc = DocumentUpload::findOrFail($id);
            } catch (ModelNotFoundException $mnf) {
                Log::warning("{$action} Document not found", [
                    'file' => $file,
                    'class' => $cls,
                    'document_id' => $id,
                    'error_class' => get_class($mnf)
                ]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('error', __('Document not found.'));
            }
            $ownershipError = $this->validateOwnership($request, $doc, $user, $action);
            if ($ownershipError !== null)
                return $ownershipError;
            DB::beginTransaction();
            try {
                $data = $request->only(['name', 'role', 'description']);
                if (!is_array($data)) {
                    $data = [];
                }
                if (!empty($request->file('document'))) {
                    $existingFile = $doc->document ?? null;
                    $existingFile = is_string($existingFile) ? $existingFile : null;
                    $uploadResult = $this->handleFileUpload($request, $action, $existingFile);
                    if (!$uploadResult['success']) {
                        DB::rollBack();
                        $errMsg = $uploadResult['error'] ?? 'Upload failed';
                        return redirect()->back()->with('error', __($errMsg));
                    }
                    $data['document'] = $uploadResult['fileName'];
                }
                $doc->update($data);
                DB::commit();
                Log::info("{$action} success", ['document_id' => $id]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Document successfully updated.'));
            } catch (\InvalidArgumentException $iae) {
                DB::rollBack();
                Log::error("{$action} InvalidArgumentException", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($iae),
                    'message' => $iae->getMessage()
                ]);
                return defaultUndefinedException($request, $iae, $action, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("{$action} error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                Log::channel(SC::ERR_TRACE)->debug("{$action} error trace", [
                    'error' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "{$cls}::{$fn}";
        $file = __FILE__;
        return $this->measureProfile($action, function () use (
            $request,
            $id,
            $action,
            $file,
            $cls,
            $fn
        ) {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse)
                return $userOrRedirect;
            $guard = self::guard($request, 'delete document', self::REDIRECT_INDEX);
            if ($guard !== true)
                return $guard;
            $user = $userOrRedirect;
            $doc = null;
            try {
                $doc = DocumentUpload::findOrFail($id);
            } catch (ModelNotFoundException $mnf) {
                Log::warning("{$action} Document not found", [
                    'file' => $file,
                    'class' => $cls,
                    'document_id' => $id,
                    'error_class' => get_class($mnf)
                ]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('error', __('Document not found.'));
            }
            $ownershipError = $this->validateOwnership($request, $doc, $user, $action);
            if ($ownershipError !== null)
                return $ownershipError;
            DB::beginTransaction();
            try {
                $docFile = $doc->document ?? null;
                $this->deleteDocumentFile($action, is_string($docFile) ? $docFile : null);
                $doc->delete();
                DB::commit();
                Log::info("{$action} deleted", ['document_id' => $id]);
                return redirect()->route(self::REDIRECT_INDEX)
                    ->with('success', __('Document successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("{$action} error", [
                    'file' => $file,
                    'class' => $cls,
                    'error_class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
                Log::channel(SC::ERR_TRACE)->debug("{$action} error trace", [
                    'error' => $e->getMessage()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    /**
     * Show a single document upload.
     */
    public function show(Request $request, DocumentUpload $document_upload): View|RedirectResponse
    {
        $action = __CLASS__ . '::' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $document_upload, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($g = self::guard($request, PMC::MNG_DOC)) !== true) return $g;
            if ($ownCheck = $this->validateOwnership($request, $document_upload, $user, $action)) return $ownCheck;
            return redirect()->route(self::REDIRECT_INDEX);
        });
    }
}
