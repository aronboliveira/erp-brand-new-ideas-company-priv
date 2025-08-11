<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, MiddlewaresConstants, ViewsConstants};
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Traits\ChecksLogin;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Illuminate\Auth\Access\AuthorizationException;

final class DocumentController extends Controller
{
    use ChecksLogin;

    private const INDEX_ROUTE = ViewsConstants::DOC . '.index';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(Request $req): Response|RedirectResponse|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($r = self::guard($req, 'manage document type')) return $r;
        try {
            $docs = Document::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            Log::info(__METHOD__ . ' fetched', ['count' => $docs->count()]);
            return response()->view(ViewsConstants::DOC . '.' . __FUNCTION__, compact('docs'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $req): Response|RedirectResponse|JsonResponse
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::guard($req, 'create document type')) return $r;
        return response()->view(ViewsConstants::DOC . '.' . __FUNCTION__);
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if ($r = self::guard($req, 'create document type')) return $r;
        $v = Validator::make($req->all(), ['name' => 'required|string|max:20']);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $data = $req->only(['name', 'is_required']);
            $data[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();
            Log::info(__METHOD__ . ' creating', ['data' => $data]);
            Document::create($data);
            DB::commit();
            Log::info(__METHOD__ . ' created');
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Document type successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(Document $document): RedirectResponse
    {
        return redirect()->route(self::INDEX_ROUTE);
    }

    public function edit(Document $document, Request $req): Response|RedirectResponse|JsonResponse
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::guard($req, 'edit document type')) return $r;
        if (!$this->isOwner($document)) {
            return defaultPermissionDenial($req, new AuthorizationException, __CLASS__ . '::' . __FUNCTION__);
        }
        return response()->view(ViewsConstants::DOC . '.edit', compact('document'));
    }

    public function update(Request $req, Document $document): RedirectResponse|JsonResponse|null
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::guard($req, 'edit document type')) return $r;
        if (!$this->isOwner($document)) {
            return defaultPermissionDenial($req, new AuthorizationException, __CLASS__ . '::' . __FUNCTION__);
        }
        $v = Validator::make($req->all(), ['name' => 'required|string|max:20']);
        if ($v->fails()) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }
        DB::beginTransaction();
        try {
            $updates = $req->only(['name', 'is_required']);
            Log::info(__METHOD__ . ' updating', ['id' => $document->id, 'updates' => $updates]);
            $document->update($updates);
            DB::commit();
            Log::info(__METHOD__ . ' updated');
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Document type successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Document $document, Request $req): RedirectResponse
    {
        if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
        if ($r = self::guard($req, 'delete document type')) return $r;
        if (!$this->isOwner($document)) {
            return defaultPermissionDenial($req, new AuthorizationException, __CLASS__ . '::' . __FUNCTION__);
        }
        DB::beginTransaction();
        try {
            Log::info(__METHOD__ . ' deleting', ['id' => $document->id]);
            $document->delete();
            DB::commit();
            Log::info(__METHOD__ . ' deleted');
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Document type successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__METHOD__ . ' failed', ['err' => $e->getMessage()]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    private static function guard(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        $user = $req->user();
        Log::info(__METHOD__ . ' checking', ['user_id' => $user?->id, 'perm' => $perm]);
        if ($user?->can($perm)) return null;
        Log::warning(__METHOD__ . ' denied', ['user_id' => $user?->id, 'perm' => $perm]);
        return defaultPermissionDenial(
            $req,
            new AuthorizationException($perm),
            __CLASS__ . '::' . __FUNCTION__,
            route(self::INDEX_ROUTE)
        );
    }

    private function isOwner(Document $doc): bool
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $owner = $doc->created_by === $user?->creatorId();
        Log::info(__METHOD__ . ' check', ['doc_id' => $doc->id, 'is_owner' => $owner]);
        return $owner;
    }
}
