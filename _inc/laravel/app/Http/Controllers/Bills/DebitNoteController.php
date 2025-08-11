<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Bill, DebitNote, Utility};
use App\Traits\ChecksLogin;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Auth\Access\AuthorizationException;

final class DebitNoteController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    public function index(Request $req): Response|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [UsersConstants::COL_USER_ID => $req->user()->id]);
        if ($r = self::_deny($req, PermissionsConstants::MNG_DBT)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) {
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' not logged', []);
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $bills = Bill::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' bills fetched', [
                UsersConstants::COL_USER_ID   => $user?->id,
                'bill_count' => $bills->count()
            ]);
            return view(ViewsConstants::DBT_NT . '.' . __FUNCTION__, compact('bills'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                UsersConstants::COL_USER_ID => $req->user()->id,
                'error'  => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(string|int $billId, Request $req): Response|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'billId' => $billId
        ]);
        if ($r = self::_deny($req, 'create debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        try {
            $bill = self::_bill($billId);
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' ready', ['bill_id' => $bill->id]);
            return view(ViewsConstants::DBT_NT . '.create', ['billDue' => $bill, 'bill_id' => $billId]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'billId' => $billId, 'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $req, string|int $billId): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $req->user()->id,
            'billId' => $billId,
            'input'  => $req->only(['amount', 'date', 'description'])
        ]);
        if ($r = self::_deny($req, 'create debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        $req->validate([
            'amount'      => 'required|numeric',
            'date'        => 'required|date',
            'description' => 'nullable|string'
        ]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' not logged', []);
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        try {
            return DB::transaction(function () use ($req, $billId, $user) {
                $bill = self::_bill($billId);
                $due = $bill->getDue();
                if ($req->amount > $due) {
                    Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' amount exceeds due', [
                        UsersConstants::COL_USER_ID => $user?->id, 'billId' => $billId, 'amount' => $req->amount, 'due' => $due
                    ]);
                    return back()->with('error', 'Maximum ' . $user?->priceFormat($due) . ' credit limit of this bill.');
                }
                $note = DebitNote::create([
                    'bill'        => $billId,
                    'vendor'      => $bill->vendor_id,
                    'date'        => $req->date,
                    'amount'      => $req->amount,
                    'description' => $req->description,
                    DatabaseConstants::TABLE_CREATOR  => $user?->creatorId()
                ]);
                Utility::updateUserBalance('vendor', $bill->vendor_id, $req->amount, 'credit');
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' created', [
                    'noteId' => $note->id, 'billId' => $billId, UsersConstants::COL_USER_ID => $user?->id, 'amount' => $req->amount
                ]);
                return back()->with('success', 'Debit Note successfully created.');
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'billId' => $billId, 'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(string|int $billId, string|int $noteId, Request $req): Response|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $req->user()->id, 'billId' => $billId, 'noteId' => $noteId
        ]);
        if ($r = self::_deny($req, 'edit debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        try {
            $note = DebitNote::findOrFail($noteId);
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' ready', ['noteId' => $note->id]);
            return view(ViewsConstants::DBT_NT . '.' . __FUNCTION__, compact('note'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'noteId' => $noteId, 'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $req, string|int $billId, string|int $noteId): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $req->user()->id, 'billId' => $billId, 'noteId' => $noteId,
            'input' => $req->only(['amount', 'date', 'description'])
        ]);
        if ($r = self::_deny($req, 'edit debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        $req->validate([
            'amount'      => 'required|numeric',
            'date'        => 'required|date',
            'description' => 'nullable|string'
        ]);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' not logged', []);
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        try {
            return DB::transaction(function () use ($req, $billId, $noteId, $user) {
                $bill = self::_bill($billId);
                $note = DebitNote::findOrFail($noteId);
                Utility::updateUserBalance('vendor', $bill->vendor_id, $note->amount, 'debit');
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' rolled back old amount', [
                    'noteId' => $noteId, 'oldAmt' => $note->amount
                ]);
                $due = $bill->getDue();
                if ($req->amount > $due) {
                    Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' amount exceeds due', [
                        UsersConstants::COL_USER_ID => $user?->id, 'billId' => $billId, 'amount' => $req->amount, 'due' => $due
                    ]);
                    return back()->with('error', 'Maximum ' . $user?->priceFormat($due) . ' credit limit of this bill.');
                }
                $note->update($req->only(['date', 'amount', 'description']));
                Utility::updateUserBalance('vendor', $bill->vendor_id, $note->amount, 'credit');
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' updated', [
                    'noteId' => $noteId, 'newAmt' => $note->amount
                ]);
                return back()->with('success', 'Debit Note successfully updated.');
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'noteId' => $noteId, 'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(string|int $billId, string|int $noteId, Request $req): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            UsersConstants::COL_USER_ID => $req->user()->id, 'billId' => $billId, 'noteId' => $noteId
        ]);
        if ($r = self::_deny($req, 'delete debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        try {
            return DB::transaction(function () use ($billId, $noteId) {
                $note = DebitNote::findOrFail($noteId);
                self::_bill($billId);
                $note->delete();
                Utility::updateUserBalance('vendor', $note->vendor, $note->amount, 'debit');
                Log::info(__CLASS__ . '::' . __FUNCTION__ . ' deleted', [
                    'noteId' => $noteId, 'billId' => $billId, 'amount' => $note->amount
                ]);
                return back()->with('success', 'Debit Note successfully deleted.');
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'noteId' => $noteId, 'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function customCreate(Request $req): Response|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [UsersConstants::COL_USER_ID => $req->user()->id]);
        if ($r = self::_deny($req, 'create debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) {
                return $userOrRedirect;
            }
            $user = $userOrRedirect;
            $bills = Bill::where([
                [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()],
                ['type', 'Bill']
            ])->pluck('bill_id', 'id');
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' bills fetched', [
                UsersConstants::COL_USER_ID => $user?->id, 'count' => $bills->count()
            ]);
            return view(ViewsConstants::DBT_NT . '.custom_create', compact('bills'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                UsersConstants::COL_USER_ID => $req->user()->id, 'error' => $e->getMessage()
            ]);
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function customStore(Request $req): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['input' => $req->all()]);
        if ($r = self::_deny($req, 'create debit note')) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' unauthorized', [UsersConstants::COL_USER_ID => $req->user()->id]);
            return $r;
        }
        $req->validate([
            'bill'   => 'required',
            'amount' => 'required|numeric',
            'date'   => 'required|date'
        ]);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' delegating to store', ['bill' => $req->input('bill')]);
        return $this->store($req, $req->input('bill'));
    }

    public function getBill(Request $req): JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['bill_id' => $req->bill_id]);
        try {
            $due = self::_bill($req->input('bill_id'))->getDue();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' success', ['due' => $due]);
            return response()->json(['due' => $due]);
        } catch (\Throwable $e) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not found', [
                'bill_id' => $req->bill_id, 'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Bill not found'], 404);
        }
    }

    private static function _deny(Request $req, string $perm): RedirectResponse|JsonResponse|null
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' called', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'permission' => $perm,
            'route'      => $req->path()
        ]);
        if (!$req->user()->can($perm)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [
                UsersConstants::COL_USER_ID    => $req->user()->id,
                'permission' => $perm
            ]);
            return defaultPermissionDenial(
                $req,
                new AuthorizationException($perm),
                __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
            );
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' granted', [
            UsersConstants::COL_USER_ID    => $req->user()->id,
            'permission' => $perm
        ]);
        return null;
    }

    private static function _bill(string|int $id): Bill
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetching bill', ['bill_id' => $id]);
        $bill = Bill::findOrFail($id);
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', ['bill_id' => $bill->id]);
        return $bill;
    }
}
