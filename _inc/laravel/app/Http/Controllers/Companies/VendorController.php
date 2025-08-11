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
use Illuminate\Support\Facades\{Crypt, DB, Log, Validator};
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'vendor';
    private const ROUTE_INDEX = self::SINGULAR . '.index';

    public function dashboard(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $c = self::guard($request, PermissionsConstants::MNG_VD, self::ROUTE_INDEX);
            if ($c) return $c;
            Log::info(__METHOD__ . ' start', ['user' => $request->user()->id]);
            $data['billChartData'] = $request->user()->billChartData();
            return view(self::SINGULAR . '.' . __FUNCTION__, $data);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function index(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $c = self::guard($request, PermissionsConstants::MNG_VD, self::ROUTE_INDEX);
            if ($c) return $c;
            $vendors = Vendor::where([DatabaseConstants::TABLE_CREATOR], $request->user()->creatorId())->get();
            Log::info(__METHOD__ . ' loaded vendors', ['count' => $vendors->count()]);
            return view(self::SINGULAR . '.index', compact('vendors'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function create(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($request, 'create vendor', self::ROUTE_INDEX)) return $c;
        $customFields = CustomField::where([DatabaseConstants::TABLE_CREATOR], $request->user()->creatorId())
            ->where('module', 'vendor')->get();
        return view(self::SINGULAR . '.create', compact('customFields'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $c = self::guard($request, 'create vendor', self::ROUTE_INDEX);
            if ($c) return $c;
            Log::info(__METHOD__ . ' start', ['input' => $request->all()]);
            $rules = [
                'name'    => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email'   => 'required|email|unique:vendors,email,NULL,id,created_by,' . $request->user()->creatorId()
            ];
            $data = Validator::make($request->all(), $rules)->validate();
            $user       = $request->user();
            $creator    = User::find($user?->creatorId());
            $totalVendor = $user?->countVendors();
            $plan       = Plan::find($creator->plan);
            $maxVendors = $plan->max_vendors;
            if ($totalVendor >= $maxVendors && $maxVendors != -1) {
                return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
            }
            DB::transaction(function () use ($data, $request, $user) {
                $vendor = new Vendor();
                $vendor->vendor_id      = $this->vendorNumber();
                $vendor->name           = $data['name'];
                $vendor->contact        = $data['contact'];
                $vendor->email          = $data['email'];
                $vendor->tax_number     = $request->tax_number;
                $vendor[DatabaseConstants::TABLE_CREATOR]     = $user?->creatorId();
                foreach (['billing', 'shipping'] as $zone) {
                    foreach (['name', 'country', 'state', 'city', 'phone', 'zip', 'address'] as $field) {
                        $key = "{$zone}_{$field}";
                        $vendor->{$key} = $request->{$key} ?? '';
                    }
                }
                $vendor->lang = Utility::settings($user?->creatorId())[SettingsConstants::DEF_LNG] ?? '';
                $vendor->save();
                CustomField::saveData($vendor, $request->customField);
                $vendor->assignRole(Role::where('name', 'vendor')->firstOrFail());
                Log::info(__METHOD__ . ' created vendor', ['id' => $vendor->id]);
                $notify = [
                    'user_name'   => $user?->name,
                    'vendor_name' => $vendor->name,
                    'vendor_email' => $vendor->email
                ];
                if (Utility::settings($user?->creatorId())['twilio_vendor_notification'] ?? false) {
                    Utility::sendTwilioMsg($vendor->contact, 'new_vendor', $notify);
                }
            });
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Vendor successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function show(Request $request, string $ids): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $id = Crypt::decrypt($ids);
            $vendor = Vendor::findOrFail($id);
            return view(self::SINGULAR . '.show', compact('vendor'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' decrypt error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function edit(Request $request, Vendor $vendor): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($request, 'edit vendor', self::ROUTE_INDEX)) return $c;
        $user = $request->user();
        if ($vendor[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
        $vendor->customField = CustomField::getData($vendor, 'vendor');
        $customFields = CustomField::where([DatabaseConstants::TABLE_CREATOR], $user?->creatorId())
            ->where('module', 'vendor')->get();
        return view(self::SINGULAR . '.' . __FUNCTION__, compact('vendor', 'customFields'));
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $c = self::guard($request, 'edit vendor', self::ROUTE_INDEX);
            if ($c) return $c;
            $user = $request->user();
            if ($vendor[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                throw new \Exception('owner');
            }
            $rules = [
                'name'    => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
            ];
            $data = Validator::make($request->all(), $rules)->validate();
            DB::transaction(function () use ($vendor, $request, $data) {
                $vendor->name       = $data['name'];
                $vendor->contact    = $data['contact'];
                $vendor->tax_number = $request->tax_number;
                foreach (['billing', 'shipping'] as $zone) {
                    foreach (['name', 'country', 'state', 'city', 'phone', 'zip', 'address'] as $field) {
                        $key = "{$zone}_{$field}";
                        $vendor->{$key} = $request->{$key} ?? $vendor->{$key};
                    }
                }
                $vendor->save();
                CustomField::saveData($vendor, $request->customField);
                Log::info(__METHOD__ . ' updated vendor', ['id' => $vendor->id]);
            });
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Vendor successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return $e->getMessage() === 'owner'
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX))
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    public function destroy(Request $request, Vendor $vendor): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $c = self::guard($request, 'delete vendor', self::ROUTE_INDEX);
            if ($c) return $c;
            $user = $request->user();
            if ($vendor[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                throw new \Exception('owner');
            }
            DB::transaction(fn () => $vendor->delete());
            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Vendor successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return $e->getMessage() === 'owner'
                ? defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX))
                : defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    public function vendorLogout(Request $request): RedirectResponse
    {
        auth()->guard('vendor')->logout();
        $request->session()->invalidate();
        return redirect()->route(self::SINGULAR . '.login');
    }

    public function payment(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard($request, 'manage vendor payment', self::ROUTE_INDEX)) return $c;
        $category = ['Bill' => 'Bill', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];
        $payments = Transaction::where([
            ['user_id', '=', $user?->id()],
            [[DatabaseConstants::TABLE_CREATOR], '=', $user?->creatorId()],
            ['user_type', '=', 'Vendor'],
            ['type', '=', 'Payment']
        ])
            ->when($request->date, function ($q) use ($request) {
                [$start, $end] = explode(' - ', $request->date);
                $q->whereBetween('date', [$start, $end]);
            })
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->get();
        return view(self::SINGULAR . '.payment', compact('payments', 'category'));
    }

    public function transaction(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($request, 'manage vendor transaction', self::ROUTE_INDEX)) return $c;
        $category = ['Bill' => 'Bill', 'Deposit' => 'Deposit', 'Sales' => 'Sales'];
        $transactions = Transaction::where([
            ['user_id', '=', auth()->id()],
            ['user_type', '=', 'Vendor']
        ])
            ->when($request->date, function ($q) use ($request) {
                [$start, $end] = explode(' - ', $request->date);
                $q->whereBetween('date', [$start, $end]);
            })
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->get();
        return view(self::SINGULAR . '.transaction', compact('transactions', 'category'));
    }

    public function profile(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $user->customField = CustomField::getData($user, 'vendor');
        $customFields = CustomField::where([DatabaseConstants::TABLE_CREATOR], $user?->creatorId())
            ->where('module', 'vendor')->get();
        return view(self::SINGULAR . '.profile', compact('user', 'customFields'));
    }

    public function editProfile(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user   = auth()->user();
        $vendor = Vendor::findOrFail($user?->id);
        $data   = $request->validate([
            'name'    => 'required|max:120',
            'contact' => 'required',
            'email'   => 'required|email|unique:users,email,' . $user?->id
        ]);
        if ($file = $request->file('profile')) {
            [$name, $ext] = explode('.', $file->getClientOriginalName(), 2);
            $fileName  = "{$name}_" . time() . ".{$ext}";
            $dir       = storage_path('uploads/avatar/');
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            if ($user?->avatar && file_exists($dir . $user?->avatar)) unlink($dir . $user?->avatar);
            $file->storeAs('uploads/avatar/', $fileName);
            $vendor->avatar = $fileName;
        }
        $vendor->fill($data)->save();
        CustomField::saveData($vendor, $request->customField);
        return redirect()->back()->with('success', __('Profile successfully updated.'));
    }

    public function editBilling(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user  = auth()->user();
        $vendor = Vendor::findOrFail($user?->id);
        $data  = $request->validate([
            'billing_name'    => 'required',
            'billing_country' => 'required',
            'billing_state'   => 'required',
            'billing_city'    => 'required',
            'billing_phone'   => 'required',
            'billing_zip'     => 'required',
            'billing_address' => 'required'
        ]);
        $vendor->fill($data)->save();
        return redirect()->back()->with('success', __('Profile successfully updated.'));
    }

    public function editShipping(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user  = auth()->user();
        $vendor = Vendor::findOrFail($user?->id);
        $data  = $request->validate([
            'shipping_name'    => 'required',
            'shipping_country' => 'required',
            'shipping_state'   => 'required',
            'shipping_city'    => 'required',
            'shipping_phone'   => 'required',
            'shipping_zip'     => 'required',
            'shipping_address' => 'required'
        ]);
        $vendor->fill($data)->save();
        return redirect()->back()->with('success', __('Profile successfully updated.'));
    }

    public function changeLanguage(Request $request, string $lang): RedirectResponse
    {
        Log::info('Change of Languages called by request with user id' . $request->user()?->id);
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $user->lang = $lang;
        $user?->save();
        return redirect()->back()->with('success', __('Language successfully changed.'));
    }

    public function export(Request $request)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $user  = $request->user();
            $filters = $request->only(['start', 'end']); // example usage
            Log::info(__METHOD__ . ' invoked', [
                'by_user_id' => $user?->id,
                'filters'    => $filters,
            ]);
            $filename = 'vendor_' . now()->format('Ymd_His') . '.xlsx';
            Log::info(__METHOD__ . ' generating file', ['filename' => $filename]);
            $export = new VendorExport($filters);
            $download = Excel::download($export, $filename);
            Log::info(__METHOD__ . ' download ready', ['filename' => $filename]);
            return $download;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function importFile(Request $request): \Illuminate\View\View|RedirectResponse
    {
        try {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::info('Change of Languages called by request with user id' . $request->user()?->id);
            Log::info(__METHOD__ . ' form display', [
                'by_user_id' => $user?->id,
            ]);
            return view(self::SINGULAR . '.import', compact('dryRun'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function import(Request $request): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        try {
            $data = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt',
            ])->validate();
            /** @var \Illuminate\Http\UploadedFile $uploaded */
            $uploaded = $data['file'];
            Log::info(__METHOD__ . ' upload received', [
                'by_user_id' => $request->user()->id,
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
                    if (!$vendor->exists) {
                        $vendor->vendor_id = $this->vendorNumber();
                    }
                    // mass‐assign by index
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
                    $vendor[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();
                    if (!$vendor->save()) {
                        $errors[] = 'failed to save row ' . $idx;
                    }
                }
                if (empty($errors)) {
                    DB::commit();
                    $status = 'success';
                    $msg   = __('Record successfully imported');
                } else {
                    DB::rollBack();
                    $status = 'error';
                    $msg   = count($errors)
                        . ' ' . __('records failed out of') . ' ' . $total;
                    session()->flash('errorArray', $errors);
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            Log::info(__METHOD__ . ' completed', ['status' => $status, 'msg' => $msg]);
            return redirect()->back()->with($status, $msg);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    private function vendorNumber(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $latest = Vendor::where([DatabaseConstants::TABLE_CREATOR], $user?->creatorId())
            ->latest()->first();
        return $latest ? $latest->vendor_id + 1 : 1;
    }
}
