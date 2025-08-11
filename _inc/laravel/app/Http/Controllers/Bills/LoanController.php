<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Employee, Loan, LoanOption, Utility};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};

final class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware(MiddlewaresConstants::AUTH);
    }

    /** Show the form to create a loan */
    public function loanCreate(string|int $employeeId, Request $request): mixed
    {
        $action = 'loanCreate';
        Log::info(__CLASS__ . "::{$action} start", [UsersConstants::COL_EMP_ID => $employeeId]);
        if ($deny = $this->deny($request, 'create loan', $action))
            return $deny;
        try {
            $employee  = Employee::findOrFail($employeeId);
            $creatorId = $request->user()->creatorId();
            $options   = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('name', 'id');
            $types     = self::loanTypes();
            Log::info(__CLASS__ . "::{$action} ready form", [
                UsersConstants::COL_EMP_ID => $employeeId,
                'option_count' => $options->count(),
                'type_count'  => count($types),
            ]);
            return view(ViewsConstants::LN . '.create', compact('employee', 'options', 'types'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} error", [
                UsersConstants::COL_EMP_ID => $employeeId,
                'exception'   => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} error", [
                UsersConstants::COL_EMP_ID => $employeeId,
                'exception'   => $e->getMessage(),
                'stack'       => $e->getTraceAsString(),
            ]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
        }
    }

    /** Persist a new loan */
    public function store(Request $request): RedirectResponse
    {
        $action = 'store';
        Log::info(__CLASS__ . "::{$action} start", ['input' => $request->all()]);
        if ($deny = $this->deny($request, 'create loan', $action))
            return $deny;
        $request->validate([
            UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
            'loan_option' => 'required|exists:loan_options,id',
            'title'       => 'required|string|max:191',
            'amount'      => 'required|numeric|min:0.01',
            'reason'      => 'required|string',
            'type'        => 'nullable|string',
        ]);
        try {
            $loan = DB::transaction(function () use ($request, $action) {
                $data = [
                    UsersConstants::COL_EMP_ID => $request->employee_id,
                    'loan_option' => $request->loan_option,
                    'title'       => $request->title,
                    'amount'      => (float) $request->amount,
                    'reason'      => $request->reason,
                    'type'        => $request->type,
                    DatabaseConstants::TABLE_CREATOR  => $request->user()->creatorId(),
                ];
                $new = Loan::create($data);
                Log::info(__CLASS__ . "::{$action} created", [
                    'loan_id'     => $new->id,
                    UsersConstants::COL_EMP_ID => $new->employee_id,
                    'amount'      => $new->amount,
                ]);
                return $new;
            });

            return back()->with('success', __('Loan successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} failed", [
                'exception' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} failed", [
                'exception' => $e->getMessage(),
                'stack'     => $e->getTraceAsString(),
            ]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
        }
    }

    /** Show the form to edit a loan */
    public function edit(string|int $loanId, Request $request): mixed
    {
        $action = 'edit';
        Log::info(__CLASS__ . "::{$action} start", ['loan_id' => $loanId]);
        if ($deny = $this->deny($request, 'edit loan', $action))
            return $deny;
        try {
            $loan = Loan::findOrFail($loanId);
            if ($loan->created_by !== $request->user()->creatorId()) {
                Log::warning(__CLASS__ . "::{$action} forbidden", ['loan_id' => $loanId, 'user_id' => $request->user()->id]);
                return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . "::{$action}");
            }

            $options = LoanOption::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->pluck('name', 'id');
            $types  = self::loanTypes();

            Log::info(__CLASS__ . "::{$action} ready form", ['loan_id' => $loanId]);
            return view(ViewsConstants::LN . '.edit', compact('loan', 'options', 'types'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} error", [
                'loan_id' => $loanId,
                'exception' => $e->getMessage(),
            ]);
            return defaultUndefinedException($request, $e, __CLASS__ . "::{$action}");
        }
    }

    /** Persist updates to a loan */
    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $action = 'update';
        Log::info(__CLASS__ . "::{$action} start", ['loan_id' => $loan->id, 'input' => $request->all()]);

        if ($deny = $this->deny($request, 'edit loan', $action)) {
            return $deny;
        }
        if ($loan->created_by !== $request->user()->creatorId()) {
            Log::warning(__CLASS__ . "::{$action} forbidden owner-mismatch", ['loan_id' => $loan->id]);
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . "::{$action}");
        }

        $request->validate([
            'loan_option' => 'required|exists:loan_options,id',
            'title'       => 'required|string|max:191',
            'amount'      => 'required|numeric|min:0.01',
            'reason'      => 'required|string',
            'type'        => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($loan, $request, $action) {
                $old = $loan->replicate();
                $loan->update($request->only(['loan_option', 'title', 'amount', 'reason', 'type']));
                Log::info(__CLASS__ . "::{$action} updated", [
                    'loan_id'   => $loan->id,
                    'before'    => $old->toArray(),
                    'after'     => $loan->toArray(),
                ]);
            });

            return back()->with('success', __('Loan successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} failed", [
                'loan_id'   => $loan->id,
                'exception' => $e->getMessage(),
            ]);
            return defaultUndefinedException($request, $e, __CLASS__ . "::{$action}");
        }
    }

    /** Delete a loan */
    public function destroy(Request $request, Loan $loan): RedirectResponse
    {
        $action = 'destroy';
        Log::info(__CLASS__ . "::{$action} start", ['loan_id' => $loan->id]);

        if ($deny = $this->deny($request, 'delete loan', $action)) {
            return $deny;
        }
        if ($loan->created_by !== $request->user()->creatorId()) {
            Log::warning(__CLASS__ . "::{$action} forbidden owner-mismatch", ['loan_id' => $loan->id]);
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . "::{$action}");
        }

        try {
            DB::transaction(function () use ($loan, $action) {
                $loan->delete();
                Log::info(__CLASS__ . "::{$action} deleted", ['loan_id' => $loan->id]);
            });

            return back()->with('success', __('Loan successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . "::{$action} failed", [
                'loan_id'   => $loan->id,
                'exception' => $e->getMessage(),
            ]);
            return defaultUndefinedException($request, $e, __CLASS__ . "::{$action}");
        }
    }

    /** Stub for compatibility */
    public function show(): RedirectResponse
    {
        return redirect()->route(ViewsConstants::LN . '.index');
    }

    /** Centralized permission check with logging */
    private function deny(Request $request, string $permission, string $action): ?RedirectResponse
    {
        if (!$request->user()->can($permission)) {
            Log::warning(__CLASS__ . "::{$action} permission denied", [
                'user_id'   => $request->user()->id,
                'permission' => $permission,
            ]);
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException($permission),
                __CLASS__ . '::' . $action
            );
        }
        return null;
    }

    /** 
     * Fallback for loan types 
     * @return array<string,string>
     */
    private static function loanTypes(): array
    {
        return Loan::$loanTypes
            ?? Loan::$Loantypes
            ?? [];
    }
}
