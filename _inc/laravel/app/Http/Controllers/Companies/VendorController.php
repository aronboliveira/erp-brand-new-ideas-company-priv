<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants
};
use App\Exports\VendorExport;
use App\Imports\VendorImport;
use App\Models\{
    CustomField,
    Plan,
    Role,
    Transaction,
    User,
    Utility,
    Vendor
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Cache, Crypt, DB, Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
class VendorController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'vendor';
    private const ROUTE_INDEX = 'vendors.index';

    public function dashboard(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                if (($c = self::guard($request, PermissionsConstants::MNG_VD, self::ROUTE_INDEX)) !== true) return $c;
                Log::debug("[$base::$action] start", ['user' => $request->user()->id]);
                $t = microtime(true);
                $data['billChartData'] = $request->user()->billChartData();
                $this->logExecutionTime($t, $action, 'billChartData');
                $view = self::SINGULAR . '.' . __FUNCTION__;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX));
                return ViewFacade::make($view, $data);
            } catch (\Throwable $e) {
                Log::error("[$base::$action] error", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                if (($c = self::guard($request, PermissionsConstants::MNG_VD, self::ROUTE_INDEX)) !== true) return $c;
                $t = microtime(true);
                $vendors = Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())->get();
                $this->logExecutionTime($t, $action, 'loadVendors');
                Log::info("[$base::$action] vendors loaded", ['count' => $vendors->count()]);
                $view = self::SINGULAR . '.index';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX));
                return ViewFacade::make($view, compact('vendors'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] error", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($request, 'create vendor', self::ROUTE_INDEX)) !== true) return $c;
            $t = microtime(true);
            $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                ->where('module', 'vendor')
                ->get();
            $this->logExecutionTime($t, $action, 'loadCustomFields');
            $view = self::SINGULAR . '.create';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX));
            return ViewFacade::make($view, compact('customFields'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                if (($c = self::guard($request, 'create vendor', self::ROUTE_INDEX)) !== true) return $c;
                Log::debug("[$base::$action] start", ['input' => $request->all()]);
                $rules = [
                    'name'    => 'required',
                    'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                    'email'   => 'required|email|unique:vendors,email,NULL,id,created_by,' . $request->user()->creatorId()
                ];
                $data = Validator::make($request->all(), $rules)->validate();
                $user = $request->user();
                $creator = User::find($user?->creatorId());
                $totalVendor = $user?->countVendors();
                $plan = Plan::find($creator->plan);
                $maxVendors = $plan->max_vendors;
                if ($totalVendor >= $maxVendors && $maxVendors != -1) {
                    return back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
                DB::transaction(function () use ($data, $request, $user, $base, $action) {
                    $vendor = new Vendor();
                    $vendor->vendor_id = $this->vendorNumber();
                    $vendor->name = $data['name'];
                    $vendor->contact = $data['contact'];
                    $vendor->email = $data['email'];
                    $vendor->tax_number = $request->tax_number;
                    $vendor[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                    foreach (['billing', 'shipping'] as $zone) {
                        foreach (['name', 'country', 'state', 'city', 'phone', 'zip', 'address'] as $field) {
                            $key = "{$zone}_{$field}";
                            $vendor->{$key} = $request->{$key} ?? '';
                        }
                    }
                    $vendor->lang = Utility::settingsById($user?->creatorId())[SettingsConstants::DEF_LNG] ?? '';
                    $vendor->save();
                    CustomField::saveData($vendor, $request->customField);
                    $vendor->assignRole(Role::where('name', 'vendor')->firstOrFail());
                    Log::info("[$base::$action] vendor created", ['id' => $vendor->id]);
                    $notify = [
                        'user_name' => $user?->name,
                        'vendor_name' => $vendor->name,
                        'vendor_email' => $vendor->email
                    ];
                    if (Utility::settingsById($user?->creatorId())['twilio_vendor_notification'] ?? false) {
                        Utility::sendTwilioMsg($vendor->contact, 'new_vendor', $notify);
                    }
                });
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Vendor successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] error", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, string $ids): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $ids, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                $t = microtime(true);
                $id = Crypt::decrypt($ids);
                $vendor = Vendor::findOrFail($id);
                $this->logExecutionTime($t, $action, 'loadVendor');
                $view = self::SINGULAR . '.show';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX));
                return ViewFacade::make($view, compact('vendor'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] decrypt/load error", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $request, Vendor $vendor): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $vendor, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($request, 'edit vendor', self::ROUTE_INDEX)) !== true) return $c;
            $user = $request->user();
            if ($vendor[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $base . '::' . $action, route(self::ROUTE_INDEX));
            }
            $vendor->customField = CustomField::getData($vendor, 'vendor');
            $t = microtime(true);
            $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->where('module', 'vendor')
                ->get();
            $this->logExecutionTime($t, $action, 'loadCustomFields');
            $view = self::SINGULAR . '.edit';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX));
            return ViewFacade::make($view, compact('vendor', 'customFields'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $vendor->id]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $vendor, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                if (($c = self::guard($request, 'edit vendor', self::ROUTE_INDEX)) !== true) return $c;
                $user = $request->user();
                if ($vendor[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new \Exception('owner');
                $rules = [
                    'name'    => 'required',
                    'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
                ];
                $data = Validator::make($request->all(), $rules)->validate();
                DB::transaction(function () use ($vendor, $request, $data, $base, $action) {
                    $vendor->name = $data['name'];
                    $vendor->contact = $data['contact'];
                    $vendor->tax_number = $request->tax_number;
                    foreach (['billing', 'shipping'] as $zone) {
                        foreach (['name', 'country', 'state', 'city', 'phone', 'zip', 'address'] as $field) {
                            $key = "{$zone}_{$field}";
                            $vendor->{$key} = $request->{$key} ?? $vendor->{$key};
                        }
                    }
                    $vendor->save();
                    CustomField::saveData($vendor, $request->customField);
                    Log::info("[$base::$action] vendor updated", ['id' => $vendor->id]);
                });
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Vendor successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] error", ['exception' => $e->getMessage()]);
                return $e->getMessage() === 'owner'
                    ? defaultPermissionDenial($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX))
                    : defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $vendor->id]);
    }

    public function destroy(Request $request, Vendor $vendor): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $vendor, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                if (($c = self::guard($request, 'delete vendor', self::ROUTE_INDEX)) !== true) return $c;
                $user = $request->user();
                if ($vendor[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) throw new \Exception('owner');
                DB::transaction(fn() => $vendor->delete());
                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Vendor successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] error", ['exception' => $e->getMessage()]);
                return $e->getMessage() === 'owner'
                    ? defaultPermissionDenial($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX))
                    : defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'vendor_id' => $vendor->id]);
    }

    public const VND_LGO = 'vendorLogout';
    public function vendorLogout(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request) {
            auth()->guard('vendor')->logout();
            $request->session()->invalidate();
            return redirect()->route(self::SINGULAR . '.login'); // ! ALERT
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function payment(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'manage vendor payment', self::ROUTE_INDEX)) !== true) return $c; // ! ALERT
            Log::debug("[$base::$action] start", ['user_id' => $user?->id]);
            $category = ['Bill' => 'Bill', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];
            $t = microtime(true);
            $payments = Transaction::where('user_id', $user?->id)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->where('user_type', 'Vendor')
                ->where('type', 'Payment')
                ->when($request->date, function ($q) use ($request) {
                    [$start, $end] = explode(' - ', $request->date);
                    $q->whereBetween('date', [$start, $end]);
                })
                ->when($request->category, fn($q) => $q->where('category', $request->category))
                ->get();
            $this->logExecutionTime($t, $action, 'paymentsQuery');
            $view = self::SINGULAR . '.payment';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
            return ViewFacade::make($view, compact('payments', 'category'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function transaction(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($request, 'manage vendor transaction', self::ROUTE_INDEX)) !== true) return $c; // ! ALERT
            Log::debug("[$base::$action] start", ['user_id' => auth()->id()]);
            $category = ['Bill' => 'Bill', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];
            $t = microtime(true);
            $transactions = Transaction::where('user_id', auth()->id())
                ->where('user_type', 'Vendor')
                ->when($request->date, function ($q) use ($request) {
                    [$start, $end] = explode(' - ', $request->date);
                    $q->whereBetween('date', [$start, $end]);
                })
                ->when($request->category, fn($q) => $q->where('category', $request->category))
                ->get();
            $this->logExecutionTime($t, $action, 'transactionsQuery');
            $view = self::SINGULAR . '.transaction';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
            return ViewFacade::make($view, compact('transactions', 'category'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function profile(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug("[$base::$action] start", ['user_id' => $user?->id]);
            $t = microtime(true);
            $user->customField = CustomField::getData($user, 'vendor');
            $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->where('module', 'vendor')
                ->get();
            $this->logExecutionTime($t, $action, 'loadCustomFields');
            $view = self::SINGULAR . '.profile';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
            return ViewFacade::make($view, compact('user', 'customFields'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const EDT_PRF = 'editProfile';
    public function editProfile(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = auth()->user();
            $vendor = Vendor::findOrFail($user?->id);
            $data = $request->validate([
                'name' => 'required|max:120',
                'contact' => 'required',
                'email' => 'required|email|unique:users,email,' . $user?->id
            ]);
            if ($file = $request->file('profile')) {
                $info = pathinfo($file->getClientOriginalName());
                $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $info['filename'] ?? 'file');
                $ext  = preg_replace('/[^A-Za-z0-9]/', '', $info['extension'] ?? '');
                $fileName = "{$name}_" . time() . ".{$ext}";
                $dir = storage_path('uploads/avatar/');
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                if ($user?->avatar && file_exists($dir . $user?->avatar)) unlink($dir . $user?->avatar);
                $file->storeAs('uploads/avatar/', $fileName);
                $vendor->avatar = $fileName;
            }
            $vendor->fill($data)->save();
            CustomField::saveData($vendor, $request->customField);
            return redirect()->back()->with('success', __('Profile successfully updated.'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => class_basename(static::class)]);
    }

    public const EDT_BIL = 'editBilling';
    public function editBilling(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = auth()->user();
            $vendor = Vendor::findOrFail($user?->id);
            $data = $request->validate([
                'billing_name' => 'required',
                'billing_country' => 'required',
                'billing_state' => 'required',
                'billing_city' => 'required',
                'billing_phone' => 'required',
                'billing_zip' => 'required',
                'billing_address' => 'required'
            ]);
            $vendor->fill($data)->save();
            return redirect()->back()->with('success', __('Profile successfully updated.'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => class_basename(static::class)]);
    }

    public const EDT_SHP = 'editShipping';
    public function editShipping(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            $user = auth()->user();
            $vendor = Vendor::findOrFail($user?->id);
            $data = $request->validate([
                'shipping_name' => 'required',
                'shipping_country' => 'required',
                'shipping_state' => 'required',
                'shipping_city' => 'required',
                'shipping_phone' => 'required',
                'shipping_zip' => 'required',
                'shipping_address' => 'required'
            ]);
            $vendor->fill($data)->save();
            return redirect()->back()->with('success', __('Profile successfully updated.'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => class_basename(static::class)]);
    }

    public const CHG_LNG = 'changeLanguage';
    public function changeLanguage(Request $request, string $lang): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $lang) {
            Log::debug('ChangeLanguage invoked', ['user_id' => $request->user()?->id, 'lang' => $lang]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $user->lang = $lang;
            $user?->save();
            return redirect()->back()->with('success', __('Language successfully changed.'));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => class_basename(static::class)]);
    }

    public function export(Request $request)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                $user = $request->user();
                $filters = $request->only(['start', 'end']);
                Log::debug("[$base::$action] start", ['user_id' => $user?->id, 'filters' => $filters]);
                $filename = 'vendor_' . now()->format('Ymd_His') . '.xlsx';
                $t = microtime(true);
                $download = Excel::download(new VendorExport($filters), $filename);
                $this->logExecutionTime($t, $action, 'excelDownload');
                return $download;
            } catch (\Throwable $e) {
                Log::error("[$base::$action] failed", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const IMP_F = 'importFile';
    public function importFile(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            try {
                if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
                Log::debug("[$base::$action] form display", ['user_id' => $request->user()?->id]);
                $dryRun = false;
                $view = self::SINGULAR . '.import';
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
                return ViewFacade::make($view, compact('dryRun'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] failed", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function import(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($request, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            try {
                $data = Validator::make($request->all(), ['file' => 'required|file|mimes:csv,txt'])->validate();
                $uploaded = $data['file'];
                Log::debug("[$base::$action] upload received", [
                    'user_id' => $request->user()->id,
                    'original_name' => $uploaded->getClientOriginalName(),
                    'size' => $uploaded->getSize(),
                ]);
                $rows = (new VendorImport())->toArray($uploaded)[0] ?? [];
                $total = max(0, count($rows) - 1);
                $errors = [];
                DB::beginTransaction();
                try {
                    $user = $request->user();
                    foreach (array_slice($rows, 1) as $idx => $row) {
                        if (count($row) < 19) {
                            $errors[] = "row {$idx} has insufficient columns";
                            continue;
                        }
                        $vendor = Vendor::firstOrNew(['email' => $row[2]]);
                        if (!$vendor->exists) $vendor->vendor_id = $this->vendorNumber();
                        [
                            $vendor->vendor_id,
                            $vendor->name,
                            $vendor->email,
                            $vendor->contact,
                            $vendor->avatar,
                            $vendor->billing_name,
                            $vendor->billing_country,
                            $vendor->billing_state,
                            $vendor->billing_city,
                            $vendor->billing_phone,
                            $vendor->billing_zip,
                            $vendor->billing_address,
                            $vendor->shipping_name,
                            $vendor->shipping_country,
                            $vendor->shipping_state,
                            $vendor->shipping_city,
                            $vendor->shipping_phone,
                            $vendor->shipping_zip,
                            $vendor->shipping_address,
                        ] = $row;
                        $vendor[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                        if (!$vendor->save()) $errors[] = 'failed to save row ' . $idx;
                    }
                    if (empty($errors)) {
                        DB::commit();
                        $status = 'success';
                        $msg = __('Record successfully imported');
                    } else {
                        DB::rollBack();
                        $status = 'error';
                        $msg = count($errors) . ' ' . __('records failed out of') . ' ' . $total;
                        session()->flash('errorArray', $errors);
                    }
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
                Log::debug("[$base::$action] completed", ['status' => $status, 'msg' => $msg]);
                return redirect()->back()->with($status, $msg);
            } catch (\Throwable $e) {
                Log::error("[$base::$action] error", ['exception' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $base . '::' . $action, route(self::ROUTE_INDEX)); // ! ALERT
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    private function vendorNumber(): int|string|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $latest = Vendor::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->latest()->first();
            if ($latest && is_numeric($latest->vendor_id)) return (int) $latest->vendor_id + 1;
            return $latest ? (string) $latest->vendor_id : -1;
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }
}
