<?php

namespace App\Http\Controllers\Individuals;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    PlansConstants,
    SettingsConstants as SC,
    UsersConstants as UC,
    ViewsConstants as VW
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
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    Cache,
    Crypt,
    Log,
    Validator,
    View as ViewFacade
};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\DefinesResourceActions;
class CustomerController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin, ChecksPermissions;
    /** Cache TTL in seconds — 2 minutes */
    private const CACHE_TTL = 120;

    private const PERM_MANAGE = PMC::MNG_CST;
    private const PERM_CREATE = 'create customer';
    private const PERM_EDIT  = 'edit customer';
    private const PERM_DELETE = 'delete customer';
    private const PERM_PAYMENT = 'manage customer payment';
    private const REDIRECT_INDEX = VW::JB . '.index';

    public function dashboard(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@dashboard';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            try {
                $t = microtime(true);
                $data['invoiceChartData'] = $user->invoiceChartData();
                $this->logExecutionTime($t, $action . '::invoiceChartData', 'completed');

                $view = VW::CST . '.dashboard';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, $data);
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
    }

    public function index(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@index';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $resp = self::_authorize($req, self::PERM_MANAGE);
            $this->logExecutionTime($t, $action . '::authorize', $resp ? 'redirect' : 'ok');
            if ($resp) return $resp;

            try {
                $t = microtime(true);
                $creatorId = $user->creatorId();
                $customers = Cache::remember("cust.list.{$creatorId}", self::CACHE_TTL, fn() => Customer::where(DC::COL_TABLE_CREATOR, $creatorId)->get());
                $this->logExecutionTime($t, $action . '::query', 'completed');

                $view = VW::CST . '.index';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, compact(DC::TABLE_CUSTOMERS));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    public function create(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@create';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $resp = self::_authorize($req, self::PERM_CREATE);
            $this->logExecutionTime($t, $action . '::authorize', $resp ? 'redirect' : 'ok');
            if ($resp) return $resp;

            try {
                $t = microtime(true);
                $customFields = CustomField::where(DC::COL_TABLE_CREATOR, $user->creatorId())
                    ->where('module', 'customer')->get();
                $this->logExecutionTime($t, $action . '::loadCustomFields', 'completed');

                $view = VW::CST . '.create';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, compact('customFields'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    public function store(Request $req): RedirectResponse
    {
        $action = 'CustomerController@store';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            $u = $user ?? $this->_checkLogin(); // get actual user when not redirect

            $t = microtime(true);
            $resp = self::_authorize($req, self::PERM_CREATE);
            $this->logExecutionTime($t, $action . '::authorize', $resp ? 'redirect' : 'ok');
            if ($resp) return $resp;

            $t = microtime(true);
            $v = Validator::make($req->all(), [
                'name'    => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email'   => [
                    'required',
                    Rule::unique(DC::TABLE_CUSTOMERS)
                        ->where(fn($q) => $q->where(DC::COL_TABLE_CREATOR, $u->creatorId()))
                ]
            ]);
            $this->logExecutionTime($t, $action . '::validate', $v->fails() ? 'failed' : 'completed');
            if ($v->fails()) {
                return redirect()->route(VW::CST . '.index')
                    ->with('error', $v->errors()->first());
            }

            try {
                $t = microtime(true);
                $plan  = Plan::find($u->creatorId());            // keeping original logic
                $count = $u->countCustomers();
                $this->logExecutionTime($t, $action . '::planAndCount', 'completed');

                if ($plan->max_customers !== -1 && $count >= $plan->max_customers) {
                    return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }

                $t = microtime(true);
                $payload  = self::buildCustomerPayload($req, $u->creatorId());
                $customer = Customer::create($payload);
                CustomField::saveData($customer, $req->input('customField', []));
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->route(VW::CST . '.index')
                    ->with('success', __('Customer successfully created.'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    public function show(Request $req, string $ids): RedirectResponse|View
    {
        $action = 'CustomerController@show';
        return $this->measureProfile($action, function () use ($req, $ids, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            try {
                $t = microtime(true);
                $id       = Crypt::decrypt($ids);
                $customer = Customer::findOrFail($id);
                $this->logExecutionTime($t, $action . '::loadCustomer', 'completed');

                $view = VW::CST . '.show';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, compact('customer'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action, route(VW::CST . '.index'));
            }
        }, ['customer_id_encrypted' => $ids]);
    }

    public function edit(Request $req, Customer $customer): RedirectResponse|View
    {
        $action = 'CustomerController@edit';
        return $this->measureProfile($action, function () use ($req, $customer, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $resp = self::_authorize($req, self::PERM_EDIT);
            $this->logExecutionTime($t, $action . '::authorize', $resp ? 'redirect' : 'ok');
            if ($resp) return $resp;

            try {
                $t = microtime(true);
                $customer->customField = CustomField::getData($customer, 'customer');
                $customFields = CustomField::where(DC::COL_TABLE_CREATOR, $user->creatorId())
                    ->where('module', 'customer')->get();
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');

                $view = VW::CST . '.edit';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, compact('customer', 'customFields'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['customer_id' => $customer->id, 'uri' => $req->getRequestUri()]);
    }

    public function update(Request $req, Customer $customer): RedirectResponse
    {
        $action = 'CustomerController@update';
        return $this->measureProfile($action, function () use ($req, $customer, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $resp = self::_authorize($req, self::PERM_EDIT);
            $this->logExecutionTime($t, $action . '::authorize', $resp ? 'redirect' : 'ok');
            if ($resp) return $resp;

            $t = microtime(true);
            $v = Validator::make($req->all(), [
                'name'    => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
            ]);
            $this->logExecutionTime($t, $action . '::validate', $v->fails() ? 'failed' : 'completed');
            if ($v->fails()) {
                return redirect()->route(VW::CST . '.index')->with('error', $v->errors()->first());
            }

            try {
                $t = microtime(true);
                $customer->update(self::buildCustomerPayload($req, $user->creatorId()));
                CustomField::saveData($customer, $req->input('customField', []));
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->route(VW::CST . '.index')
                    ->with('success', __('Customer successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['customer_id' => $customer->id, 'uri' => $req->getRequestUri()]);
    }

    public function destroy(Request $req, Customer $customer): RedirectResponse
    {
        $action = 'CustomerController@destroy';
        return $this->measureProfile($action, function () use ($req, $customer, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $resp = self::_authorize($req, self::PERM_DELETE);
            $this->logExecutionTime($t, $action . '::authorize', $resp ? 'redirect' : 'ok');
            if ($resp) return $resp;

            try {
                $t = microtime(true);
                $customer->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return redirect()->route(VW::CST . '.index')
                    ->with('success', __('Customer successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['customer_id' => $customer->id, 'uri' => $req->getRequestUri()]);
    }

    public const CST_LGO = 'customerLogout';
    public function customerLogout(Request $req): RedirectResponse
    {
        $action = 'CustomerController@customerLogout';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                $t = microtime(true);
                Auth::guard('customer')->logout();
                $req->session()->invalidate();
                $this->logExecutionTime($t, $action . '::logout', 'completed');

                return redirect()->route(VW::CST . '.login');
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    public function payment(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@payment';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            $t = microtime(true);
            $guard = self::guard($req, self::PERM_PAYMENT, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            try {
                $category = ['Invoice' => 'Invoice', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];

                $t = microtime(true);
                $query = Transaction::where(UC::COL_USER_ID, $u->id)
                    ->where('user_type', 'Customer')->where('type', 'Payment');

                if ($req->filled('date')) {
                    [$from, $to] = explode(' - ', $req->input('date'));
                    $query->whereBetween('date', [$from, $to]);
                }
                if ($req->filled('category')) {
                    $query->where('category', $req->input('category'));
                }
                $payments = $query->get();
                $this->logExecutionTime($t, $action . '::query', 'completed');

                $view = VW::CST . '.payment';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, compact('payments', 'category'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action, route(VW::CST . '.index'));
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    public function transaction(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@transaction';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            $t = microtime(true);
            $guard = self::guard($req, self::PERM_PAYMENT, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            try {
                $category = ['Invoice' => 'Invoice', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];

                $t = microtime(true);
                $query = Transaction::where(UC::COL_USER_ID, $u->id)
                    ->where('user_type', 'Customer');

                if ($req->filled('date')) {
                    [$from, $to] = explode(' - ', $req->input('date'));
                    $query->whereBetween('date', [$from, $to]);
                }
                if ($req->filled('category')) {
                    $query->where('category', $req->input('category'));
                }
                $transactions = $query->get();
                $this->logExecutionTime($t, $action . '::query', 'completed');

                $view = VW::CST . '.transaction';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view, compact('transactions', 'category'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action, route(VW::CST . '.index'));
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * Show customer profile.
     */
    public function profile(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@profile';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
                $userDetail = $u;
                $userDetail->customField = CustomField::getData($u, 'customer');
                $customFields = CustomField::where(DC::COL_TABLE_CREATOR, $u->creatorId())
                    ->where('module', 'customer')
                    ->get();
                $this->logExecutionTime($t, $action . '::loadProfileData', 'completed');

                $view = VW::CST . '.profile';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");

                return view($view, compact('userDetail', 'customFields'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
    }

    /**
     * Edit basic profile fields.
     */
    public function editProfile(Request $req): RedirectResponse
    {
        $action = 'CustomerController@editProfile';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
                $user = Customer::findOrFail($u->id);

                $v = Validator::make($req->all(), [
                    'name'    => 'required|max:120',
                    'contact' => 'required',
                    'email'   => "required|email|unique:users,email,{$u->id}"
                ]);
                $this->logExecutionTime($t, $action . '::validate', $v->fails() ? 'failed' : 'ok');
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());

                // avatar upload
                if ($req->hasFile('profile')) {
                    $t = microtime(true);
                    $file = $req->file('profile');
                    $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                        . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $dir = storage_path('uploads/avatar/');
                    if (!is_dir($dir)) mkdir($dir, 0777, true);
                    if ($user->avatar && file_exists($dir . $user->avatar)) @unlink($dir . $user->avatar);
                    // store on local disk under storage/app/uploads/avatar
                    $file->storeAs('uploads/avatar/', $name);
                    $user->avatar = $name;
                    $this->logExecutionTime($t, $action . '::storeAvatar', 'completed');
                }

                $t = microtime(true);
                $user->fill($req->only(UC::COL_NM, UC::COL_EM, 'contact'))->save();
                CustomField::saveData($user, $req->input('customField', []));
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->back()->with('success', 'Profile successfully updated.');
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * Edit billing address section.
     */
    public const EDT_BIL = 'editBilling';
    public function editBilling(Request $req): RedirectResponse
    {
        $action = 'CustomerController@editBilling';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
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
                $this->logExecutionTime($t, $action . '::validate', $v->fails() ? 'failed' : 'ok');
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());

                $t = microtime(true);
                $user->fill($req->only([
                    'billing_name',
                    'billing_country',
                    'billing_state',
                    'billing_city',
                    'billing_phone',
                    'billing_zip',
                    'billing_address'
                ]))->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->back()->with('success', 'Profile successfully updated.');
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * Edit shipping address section.
     */
    public const EDT_SHP = 'editShipping';
    public function editShipping(Request $req): RedirectResponse
    {
        $action = 'CustomerController@editShipping';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
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
                $this->logExecutionTime($t, $action . '::validate', $v->fails() ? 'failed' : 'ok');
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());

                $t = microtime(true);
                $user->fill($req->only([
                    'shipping_name',
                    'shipping_country',
                    'shipping_state',
                    'shipping_city',
                    'shipping_phone',
                    'shipping_zip',
                    'shipping_address'
                ]))->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->back()->with('success', 'Profile successfully updated.');
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * Change user language.
     */
    public const CHG_LNG = 'changeLanguage';
    public function changeLanguage(Request $req, string $lang): RedirectResponse
    {
        $action = 'CustomerController@changeLanguage';
        return $this->measureProfile($action, function () use ($req, $lang, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
                $u->lang = $lang;
                $u->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return redirect()->back()->with('success', __('Language Change Successfully!'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['lang' => $lang, 'uri' => $req->getRequestUri()]);
    }

    /**
     * Export customers to Excel.
     */
    public function export(Request $req): mixed
    {
        $action = 'CustomerController@export';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
                $fn = 'customer_' . now()->format('Y-m-d_His') . '.xlsx';
                // prevent any stray output before XLSX stream
                if (function_exists('ob_get_level')) {
                    while (ob_get_level() > 0) @ob_end_clean();
                }
                $this->logExecutionTime($t, $action . '::prepare', 'completed');

                return Excel::download(new CustomerExport(), $fn);
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * Show import page.
     */
    public const IMP_F = 'importFile';
    public function importFile(Request $req): RedirectResponse|View
    {
        $action = 'CustomerController@importFile';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $view = VW::CST . '.import';
                if (!ViewFacade::exists($view)) abort(404, "View [$view] not found");
                return view($view);
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * Handle CSV import.
     */
    public function import(Request $req): RedirectResponse
    {
        $action = 'CustomerController@import';
        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;

            try {
                $t = microtime(true);
                $v = Validator::make($req->all(), ['file' => 'required|mimes:csv,txt']);
                $this->logExecutionTime($t, $action . '::validate', $v->fails() ? 'failed' : 'ok');
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());

                $t = microtime(true);
                $rows = (new CustomerImport())->toArray($req->file('file'))[0] ?? [];
                $this->logExecutionTime($t, $action . '::parseCsv', 'completed');

                $errors = [];
                $total  = max(count($rows) - 1, 0);

                $tLoop = microtime(true);
                for ($i = 1; $i < count($rows); $i++) {
                    $cols = $rows[$i];

                    $cust = Customer::firstOrNew(['email' => $cols[2] ?? null]);
                    $cust->customer_id = $cust->exists ? $cust->customer_id : self::customerNumber();

                    foreach (
                        [
                            'customer_id',
                            'name',
                            'email',
                            'contact',
                            'billing_name',
                            'billing_country',
                            'billing_state',
                            'billing_city',
                            'billing_phone',
                            'billing_zip',
                            'billing_address',
                            'shipping_name',
                            'shipping_country',
                            'shipping_state',
                            'shipping_city',
                            'shipping_phone',
                            'shipping_zip',
                            'shipping_address'
                        ] as $j => $field
                    ) {
                        if (array_key_exists($j, $cols) && $cols[$j] !== null) {
                            $cust->$field = $cols[$j];
                        }
                    }

                    $cust->is_active  = 1;
                    $cust->created_by = $u->creatorId();

                    if (!$cust->save()) $errors[] = $cols;
                }
                $this->logExecutionTime($tLoop, $action . '::loopRows', 'completed');

                if (!empty($errors)) {
                    session()->put('errorArray', array_map(fn($r) => implode(',', $r), $errors));
                    $msg = count($errors) . ' ' . __('records failed out of') . ' ' . $total;
                    return redirect()->back()->with('error', $msg);
                }

                return redirect()->back()->with('success', __('Record successfully imported'));
            } catch (\Throwable $e) {
                Log::error("$action failed: " . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri()]);
    }

    /**
     * AJAX search for customers.
     */
    public const SRC_CTM = 'searchCustomers';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function searchCustomers(Request $req): JsonResponse|RedirectResponse
    {
        $action = 'CustomerController@searchCustomers';

        return $this->measureProfile($action, function () use ($req, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $t = microtime(true);
            if ($resp = self::_authorize($req, self::PERM_MANAGE)) {
                $this->logExecutionTime($t, $action . '::authorize', 'redirect');
                return $resp;
            }
            $this->logExecutionTime($t, $action . '::authorize', 'ok');

            try {
                $term = trim((string) $req->input('search', ''));
                if ($req->ajax() && $term !== '') {
                    $t = microtime(true);
                    $qb = Customer::select(
                        'id as value',
                        UC::COL_NM . ' as label',
                        UC::COL_EM
                    )
                        ->where(UC::COL_IA, 1)
                        ->where(DC::COL_TABLE_CREATOR, $u->creatorId())
                        ->where(function ($qr) use ($term) {
                            $qr->where(UC::COL_NM, 'like', "%{$term}%")
                                ->orWhere(UC::COL_EM, 'like', "%{$term}%");
                        });
                    $this->logExecutionTime($t, $action . '::buildQuery', 'ok');
                    $t = microtime(true);
                    $list = $qb->get();
                    $this->logExecutionTime($t, $action . '::exec', 'rows: ' . $list->count());
                    return response()->json($list);
                }

                return response()->json([]);
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($req, $e, $action);
            }
        }, ['uri' => $req->getRequestUri(), 'ip' => $req->ip(), 'q' => $req->input('search')]);
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
            'name',
            'contact',
            'email',
            'tax_number',
            'billing_name',
            'billing_country',
            'billing_state',
            'billing_city',
            'billing_phone',
            'billing_zip',
            'billing_address',
            'shipping_name',
            'shipping_country',
            'shipping_state',
            'shipping_city',
            'shipping_phone',
            'shipping_zip',
            'shipping_address'
        ];
        $data = [
            'customer_id' => self::nextCustomerId($creator),
            DC::COL_TABLE_CREATOR  => $creator,
            'lang'        => Utility::settingsById($creator)[SC::DEF_LNG] ?? ''
        ];
        foreach ($fields as $f) $data[$f] = $req->input($f, '');
        return $data;
    }

    private static function nextCustomerId(int|string $creator): int|string // ! CHANGED
    {
        $last = Customer::where(DC::COL_TABLE_CREATOR, $creator)->latest()->first();
        if (!$last) return 1;
        $cid = $last->customer_id;
        return is_numeric($cid)
            ? ((int)$cid + 1)
            : (string) Str::uuid();
    }

    private static function customerNumber(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        try {
            $creator = $user?->creatorId();
            $latest = Customer::where(DC::COL_TABLE_CREATOR, $creator)->latest()->first();
            if (!$latest) return 0;
            return is_numeric($latest->customer_id) ? $latest->customer_id + 1 : $latest->customer_id;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return 1;
        }
    }
}
