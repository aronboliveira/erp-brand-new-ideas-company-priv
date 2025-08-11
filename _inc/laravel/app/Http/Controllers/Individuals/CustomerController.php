<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    PlansConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Models\{
    Customer,
    CustomField,
    Plan,
    Transaction,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    Crypt,
    Log,
    Validator
};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const PERM_MANAGE = PermissionsConstants::MNG_CST;
    private const PERM_CREATE = 'create customer';
    private const PERM_EDIT  = 'edit customer';
    private const PERM_DELETE = 'delete customer';
    private const PERM_PAYMENT = 'manage customer payment';
    private const REDIRECT_INDEX = ViewsConstants::JB . '.index';

    public function dashboard(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        try {
            $data['invoiceChartData'] = $userOrRedirect->invoiceChartData();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, $data);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function index(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($resp = self::_authorize($req, self::PERM_MANAGE)) return $resp;
        try {
            $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())->get();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_CUSTOMERS));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($resp = self::_authorize($req, self::PERM_CREATE)) return $resp;
        try {
            $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())
                ->where('module', 'customer')->get();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact('customFields'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function store(Request $req): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($resp = self::_authorize($req, self::PERM_CREATE)) return $resp;
        $v = Validator::make($req->all(), [
            'name'    => 'required',
            'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'email'   => [
                'required',
                Rule::unique(DatabaseConstants::TABLE_CUSTOMERS)
                    ->where(fn ($q) => $q->where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId()))
            ]
        ]);
        if ($v->fails()) return redirect()->route(ViewsConstants::CST . '.index')
            ->with('error', $v->errors()->first());
        try {
            $plan    = Plan::find($userOrRedirect->creatorId());
            $count   = $userOrRedirect->countCustomers();
            if ($plan->max_customers !== -1 && $count >= $plan->max_customers)
                return redirect()->back()
                    ->with('error', __('Your user limit is over, Please upgrade plan.'));
            $payload = self::buildCustomerPayload($req, $userOrRedirect->creatorId());
            $customer = Customer::create($payload);
            CustomField::saveData($customer, $req->input('customField', []));
            return redirect()->route(ViewsConstants::CST . '.index')
                ->with('success', __('Customer successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Request $req, string $ids): RedirectResponse|\Illuminate\View\View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        try {
            $id      = Crypt::decrypt($ids);
            $customer = Customer::findOrFail($id);
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact('customer'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::CST . '.index')
            );
        }
    }

    public function edit(Request $req, Customer $customer): RedirectResponse|\Illuminate\View\View
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($resp = self::_authorize($req, self::PERM_EDIT)) return $resp;
        try {
            $customer->customField = CustomField::getData($customer, 'customer');
            $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $userOrRedirect->creatorId())
                ->where('module', 'customer')->get();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact('customer', 'customFields'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $req, Customer $customer): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($resp = self::_authorize($req, self::PERM_EDIT)) return $resp;
        $v = Validator::make($req->all(), [
            'name'    => 'required',
            'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
        ]);
        if ($v->fails()) return redirect()->route(ViewsConstants::CST . '.index')
            ->with('error', $v->errors()->first());
        try {
            $customer->update(
                self::buildCustomerPayload($req, $userOrRedirect->creatorId())
            );
            CustomField::saveData($customer, $req->input('customField', []));
            return redirect()->route(ViewsConstants::CST . '.index')
                ->with('success', __('Customer successfully updated.'));
        } catch (\Throwable $e) {
            Log::error('CustomerController::update failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $req, Customer $customer): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
            return $userOrRedirect;
        if ($resp = self::_authorize($req, self::PERM_DELETE)) return $resp;
        try {
            $customer->delete();
            return redirect()->route(ViewsConstants::CST . '.index')
                ->with('success', __('Customer successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function customerLogout(Request $req): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            Auth::guard('customer')->logout();
            $req->session()->invalidate();
            return redirect()->route(ViewsConstants::CST . '.login');
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * View customer payments.
     */
    public function payment(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_PAYMENT, self::REDIRECT_INDEX)) return $c;
        try {
            $category = ['Invoice' => 'Invoice', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];
            $query   = Transaction::where(UsersConstants::COL_USER_ID, $u->id)
                ->where('user_type', 'Customer')->where('type', 'Payment');
            if ($req->filled('date')) {
                [$from, $to] = explode(' - ', $req->input('date'));
                $query->whereBetween('date', [$from, $to]);
            }
            if ($req->filled('category'))
                $query->where('category', $req->input('category'));
            $payments = $query->get();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact('payments', 'category'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::CST . '.index')
            );
        }
    }

    /**
     * View all customer transactions.
     */
    public function transaction(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_PAYMENT, self::REDIRECT_INDEX)) return $c;
        try {
            $category    = ['Invoice' => 'Invoice', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];
            $query       = Transaction::where(UsersConstants::COL_USER_ID, $u->id)->where('user_type', 'Customer');
            if ($req->filled('date')) {
                [$from, $to] = explode(' - ', $req->input('date'));
                $query->whereBetween('date', [$from, $to]);
            }
            if ($req->filled('category'))
                $query->where('category', $req->input('category'));
            $transactions = $query->get();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact('transactions', 'category'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::CST . '.index')
            );
        }
    }

    /**
     * Show customer profile.
     */
    public function profile(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $userDetail             = $u;
            $userDetail->customField = CustomField::getData($u, 'customer');
            $customFields           = CustomField::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->where('module', 'customer')->get();
            return view(ViewsConstants::CST . '.' . __FUNCTION__, compact('userDetail', 'customFields'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . 'failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Edit basic profile fields.
     */
    public function editProfile(Request $req): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $user      = Customer::findOrFail($u->id);
            $v = Validator::make($req->all(), [
                'name'    => 'required|max:120',
                'contact' => 'required',
                'email'   => "required|email|unique:users,email,{$u->id}"
            ]);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            if ($req->hasFile('profile')) {
                $file   = $req->file('profile');
                $name   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                    . '_' . time() . '.' . $file->getClientOriginalExtension();
                $dir    = storage_path('uploads/avatar/');
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                if (file_exists($dir . $u->avatar)) unlink($dir . $u->avatar);
                $file->storeAs('uploads/avatar/', $name);
                $user->avatar = $name;
            }
            $user?->fill($req->only(UsersConstants::COL_NM, UsersConstants::COL_EM, 'contact'))->save();
            CustomField::saveData($user, $req->input('customField', []));
            return redirect()->back()->with('success', 'Profile successfully updated.');
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Edit billing address section.
     */
    public function editBilling(Request $req): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $user = Customer::findOrFail($u->id);
            $v = Validator::make($req->all(), [
                'billing_name'    => 'required',
                'billing_country' => 'required',
                'billing_state'   => 'required',
                'billing_city'    => 'required',
                'billing_phone'   => 'required',
                'billing_zip'     => 'required',
                'billing_address' => 'required'
            ]);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            $user?->fill($req->only([
                'billing_name', 'billing_country', 'billing_state',
                'billing_city', 'billing_phone', 'billing_zip', 'billing_address'
            ]))->save();
            return redirect()->back()->with('success', 'Profile successfully updated.');
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Edit shipping address section.
     */
    public function editShipping(Request $req): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $user = Customer::findOrFail($u->id);
            $v = Validator::make($req->all(), [
                'shipping_name'    => 'required',
                'shipping_country' => 'required',
                'shipping_state'   => 'required',
                'shipping_city'    => 'required',
                'shipping_phone'   => 'required',
                'shipping_zip'     => 'required',
                'shipping_address' => 'required'
            ]);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            $user?->fill($req->only([
                'shipping_name', 'shipping_country', 'shipping_state',
                'shipping_city', 'shipping_phone', 'shipping_zip', 'shipping_address'
            ]))->save();
            return redirect()->back()->with('success', 'Profile successfully updated.');
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Change user language.
     */
    public function changeLanguage(Request $req, string $lang): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $u->lang = $lang;
            $u->save();
            return redirect()->back()->with('success', __('Language Change Successfully!'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Export customers to Excel.
     */
    public function export(Request $req)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $fn  = 'customer_' . now()->format('Y-m-d_His') . '.xlsx';
            ob_end_clean();
            return Excel::download(new CustomerExport(), $fn);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Show import page.
     */
    public function importFile(Request $req): RedirectResponse|\Illuminate\View\View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            return view(ViewsConstants::CST . '.import');
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Handle CSV import.
     */
    public function import(Request $req): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $v = Validator::make($req->all(), ['file' => 'required|mimes:csv,txt']);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            $rows     = (new CustomerImport())->toArray($req->file('file'))[0] ?? [];
            $errors   = [];
            $total    = max(count($rows) - 1, 0);
            for ($i = 1; $i < count($rows); $i++) {
                $cols = $rows[$i];
                $cust = Customer::firstOrNew(['email' => $cols[2]]);
                $cust->customer_id = $cust->exists
                    ? $cust->customer_id
                    : self::customerNumber();
                foreach ([
                    'customer_id', 'name', 'email', 'contact', 'billing_name', 'billing_country',
                    'billing_state', 'billing_city', 'billing_phone', 'billing_zip',
                    'billing_address', 'shipping_name', 'shipping_country', 'shipping_state',
                    'shipping_city', 'shipping_phone', 'shipping_zip', 'shipping_address'
                ] as $j => $field) {
                    $cust->$field = $cols[$j] ?? $cust->$field;
                }
                $cust->is_active = 1;
                $cust->created_by = $u->creatorId();
                if (!$cust->save()) $errors[] = $cols;
            }
            if ($errors) {
                session()->put('errorArray', array_map(fn ($r) => implode(',', $r), $errors));
                $msg = count($errors) . ' ' . __('records failed out of') . ' ' . $total;
                return redirect()->back()->with('error', $msg);
            }
            return redirect()->back()->with('success', __('Record successfully imported'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * AJAX search for customers.
     */
    public const SRC_CTM = 'searchCustomers';
    public function searchCustomers(Request $req)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::_authorize($req, self::PERM_MANAGE)) return $c;
        try {
            if ($req->ajax() && $q = $req->input('search')) {
                $list = Customer::select(
                    'id as value',
                    UsersConstants::COL_NM . ' as label',
                    UsersConstants::COL_EM
                )
                    ->where(UsersConstants::COL_IA, 1)
                    ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                    ->where(fn ($qr) => $qr->where('name', 'like', "%{$q}%")
                        ->orWhere(UsersConstants::COL_EM, 'like', "%{$q}%"))
                    ->get();
                return response()->json($list);
            }
            return response()->json([]);
        } catch (\Throwable $e) {
            Log::error('CustomerController::searchCustomers failed: ' . $e->getMessage());
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    private static function _authorize(Request $req, string $perm): ?RedirectResponse
    {
        return $req->user()->can($perm)
            ? null
            : defaultPermissionDenial(
                $req,
                new AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__
            );
    }

    private static function buildCustomerPayload(Request $req, int|string $creator): array
    {
        $fields = [
            'name', 'contact', 'email', 'tax_number',
            'billing_name', 'billing_country', 'billing_state',
            'billing_city', 'billing_phone', 'billing_zip',
            'billing_address', 'shipping_name', 'shipping_country',
            'shipping_state', 'shipping_city', 'shipping_phone',
            'shipping_zip', 'shipping_address'
        ];
        $data = [
            'customer_id' => self::nextCustomerId($creator),
            DatabaseConstants::TABLE_CREATOR  => $creator,
            'lang'        => Utility::settingsById($creator)[SettingsConstants::DEF_LNG] ?? ''
        ];
        foreach ($fields as $f) $data[$f] = $req->input($f, '');
        return $data;
    }

    private static function nextCustomerId(int|string $creator): int|string // ! CHANGED
    {
        $last = Customer::where(DatabaseConstants::TABLE_CREATOR, $creator)->latest()->first();
        if (!$last) return 1;
        $cid = $last->customer_id;
        return is_numeric($cid)
            ? ((int)$cid + 1)
            : (string) Str::uuid();
    }

    private static function customerNumber(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        try {
            $creator = $user?->creatorId();
            $latest = Customer::where(DatabaseConstants::TABLE_CREATOR, $creator)->latest()->first();
            return $latest ? $latest->customer_id + 1 : 1;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return 1;
        }
    }
}
