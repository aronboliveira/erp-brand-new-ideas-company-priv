<?php

namespace App\Http\Controllers\Shapes;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MiddlewaresConstants as MWC,
    ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Document;
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
final class DocumentController extends Controller
{
    use ChecksLogin;

    private const INDEX_ROUTE = VW::DOC . '.index';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function __construct()
    {
        $this->middleware([MWC::AUTH, MWC::XSS]);
    }

    public function index(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $cls      = static::class;
        $sig      = "$cls::$action";
        $viewPath = VW::DOC . '.' . $action;

        return $this->measureProfile($action, function () use ($req, $sig, $viewPath) {
            Log::info("$sig start", ['user' => Auth::id()]);

            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($c = self::guard($req, 'manage document type')) !== true) return $c;

            try {
                $t = microtime(true);
                $docs = Document::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($t, "$sig::fetchDocuments", 'completed');
                Log::info("$sig fetched", ['count' => $docs->count()]);

                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

                return response()->view($viewPath, ['documents' => $docs]);
            } catch (\Throwable $e) {
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $sig);
            }
        });
    }

    public function create(Request $req): Response|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $cls      = static::class;
        $sig      = "$cls::$action";
        $viewPath = VW::DOC . '.' . $action;

        return $this->measureProfile($action, function () use ($req, $sig, $viewPath) {
            Log::info("$sig start", ['user' => Auth::id()]);

            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if (($c = self::guard($req, 'create document type')) !== true) return $c;

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return response()->view($viewPath);
        });
    }

    public function store(Request $req): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $sig) {
            Log::info("$sig start", ['user' => Auth::id()]);

            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($req, 'create document type')) !== true) return $c;

            $t = microtime(true);
            $v = Validator::make($req->all(), ['name' => 'required|string|max:20']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');
            if ($v->fails()) {
                Log::warning("$sig validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $data = $req->only(['name', DC::COL_IR]);
                $data[DC::COL_TABLE_CREATOR] = $user?->creatorId();
                Document::create($data);
                DB::commit();
                $this->logExecutionTime($t, "$sig::transaction", 'completed');
                Log::info("$sig created");

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Document type successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $sig);
            }
        });
    }

    public function show(Document $document): RedirectResponse
    {
        // No view to render; no need for profiling wrapper.
        return redirect()->route(self::INDEX_ROUTE)->with($document);
    }

    public function edit(Document $document, Request $req): Response|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $cls      = static::class;
        $sig      = "$cls::$action";
        $viewPath = VW::DOC . '.edit';

        return $this->measureProfile($action, function () use ($req, $document, $sig, $viewPath) {
            Log::info("$sig start", ['id' => $document->id]);

            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if (($c = self::guard($req, 'edit document type')) !== true) return $c;
            if (!$this->isOwner($document)) {
                return defaultPermissionDenial($req, new AuthorizationException, $sig, route(self::INDEX_ROUTE));
            }

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return response()->view($viewPath, compact('document'));
        });
    }

    public function update(Request $req, Document $document): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $document, $sig) {
            Log::info("$sig start", ['id' => $document->id]);

            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if (($c = self::guard($req, 'edit document type')) !== true) return $c;
            if (!$this->isOwner($document)) {
                return defaultPermissionDenial($req, new AuthorizationException, $sig, route(self::INDEX_ROUTE));
            }

            $t = microtime(true);
            $v = Validator::make($req->all(), ['name' => 'required|string|max:20']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');
            if ($v->fails()) {
                Log::warning("$sig validation failed", ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $updates = $req->only(['name', 'is_required']);
                $document->update($updates);
                DB::commit();
                $this->logExecutionTime($t, "$sig::transaction", 'completed');
                Log::info("$sig updated");

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Document type successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $sig);
            }
        });
    }

    public function destroy(Document $document, Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $document, $sig) {
            Log::info("$sig start", ['id' => $document->id]);

            if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
            if (($c = self::guard($req, 'delete document type')) !== true) return $c;
            if (!$this->isOwner($document)) {
                return defaultPermissionDenial($req, new AuthorizationException, $sig, route(self::INDEX_ROUTE));
            }

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $document->delete();
                DB::commit();
                $this->logExecutionTime($t, "$sig::transaction", 'completed');
                Log::info("$sig deleted");

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Document type successfully deleted.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $sig);
            }
        });
    }

    private static function guard(Request $req, string $perm): bool|RedirectResponse|JsonResponse
    {
        $user = $req->user();
        Log::info(__METHOD__ . ' checking', ['user_id' => $user?->id, 'perm' => $perm]);
        if ($user?->can($perm)) return true;
        if (strtolower((string)($user?->type ?? '')) === \App\Config\Constants\PermissionsConstants::SA) {
            Log::notice(__METHOD__ . ' SA bypass', ['user_id' => $user?->id, 'perm' => $perm]);
            return true;
        }

        Log::warning(__METHOD__ . ' denied', ['user_id' => $user?->id, 'perm' => $perm]);
        return defaultPermissionDenial(
            $req,
            new AuthorizationException($perm),
            __CLASS__ . '::' . __FUNCTION__,
            route(self::INDEX_ROUTE)
        );
    }

    private function isOwner(Document $doc): bool|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user  = $userOrRedirect;
        $owner = $doc->created_by === $user?->creatorId();
        Log::info(__METHOD__ . ' check', ['doc_id' => $doc->id, 'is_owner' => $owner]);
        return $owner;
    }
}
