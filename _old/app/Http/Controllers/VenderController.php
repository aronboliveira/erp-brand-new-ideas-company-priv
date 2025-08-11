<?php

namespace App\Http\Controllers;

use App\Exports\VendorExport;
use App\Imports\VendorImport;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\Vendor;
use Auth;
use App\Models\User;
use App\Models\Plan;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class VendorController extends Controller
{

    public function dashboard()
    {
        $data['billChartData'] = \Auth::user()->billChartData();

        return view('vendor.dashboard', $data);
    }

    public function index()
    {
        if(\Auth::user()->can('manage vendor'))
        {
            $vendors = Vendor::where('created_by', \Auth::user()->creatorId())->get();

            return view('vendor.index', compact('vendors'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create vendor'))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();

            return view('vendor.create', compact('customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create vendor'))
        {
            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    Rule::unique('vendors')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('vendor.index')->with('error', $messages->first());
            }
                $objVendor    = \Auth::user();
                $creator      = User::find($objVendor->creatorId());
                $total_vendor = $objVendor->countVendors();
                $plan         = Plan::find($creator->plan);
                $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
                if($total_vendor < $plan->max_vendors || $plan->max_vendors == -1)
                {
                    $vendor                   = new Vendor();
                    $vendor->vendor_id        = $this->vendorNumber();
                    $vendor->name             = $request->name;
                    $vendor->contact          = $request->contact;
                    $vendor->email            = $request->email;
                    $vendor->tax_number      =$request->tax_number;
                    $vendor->created_by       = \Auth::user()->creatorId();
                    $vendor->billing_name     = $request->billing_name;
                    $vendor->billing_country  = $request->billing_country;
                    $vendor->billing_state    = $request->billing_state;
                    $vendor->billing_city     = $request->billing_city;
                    $vendor->billing_phone    = $request->billing_phone;
                    $vendor->billing_zip      = $request->billing_zip;
                    $vendor->billing_address  = $request->billing_address;
                    $vendor->shipping_name    = $request->shipping_name;
                    $vendor->shipping_country = $request->shipping_country;
                    $vendor->shipping_state   = $request->shipping_state;
                    $vendor->shipping_city    = $request->shipping_city;
                    $vendor->shipping_phone   = $request->shipping_phone;
                    $vendor->shipping_zip     = $request->shipping_zip;
                    $vendor->shipping_address = $request->shipping_address;
                    $vendor->lang             = !empty($default_language) ? $default_language->value : '';
                    $vendor->save();
                    CustomField::saveData($vendor, $request->customField);
                }
                else
                {
                    return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
                $role_r = Role::where('name', '=', 'vendor')->firstOrFail();
                $vendor->assignRole($role_r); //Assigning role to user
                $vendor->type     = 'Vendor';


            //For Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $vendorNotificationArr = [
                'user_name' => \Auth::user()->name,
                'vendor_name' => $vendor->name,
                'vendor_email' => $vendor->email,
            ];

            //Twilio Notification
            if(isset($setting['twilio_vendor_notification']) && $setting['twilio_vendor_notification'] ==1)
            {
                Utility::send_twilio_msg($request->contact,'new_vendor', $vendorNotificationArr);
            }

            return redirect()->route('vendor.index')->with('success', __('Vendor successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        try {
            $id       = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Vendor Not Found.'));
        }

        $id     = \Crypt::decrypt($ids);
        $vendor = Vendor::find($id);

        return view('vendor.show', compact('vendor'));
    }


    public function edit($id)
    {
        if(\Auth::user()->can('edit vendor'))
        {
            $vendor              = Vendor::find($id);
            $vendor->customField = CustomField::getData($vendor, 'vendor');
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();

            return view('vendor.edit', compact('vendor', 'customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Vendor $vendor)
    {
        if(\Auth::user()->can('edit vendor'))
        {

            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            ];


            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('vendor.index')->with('error', $messages->first());
            }

            $vendor->name             = $request->name;
            $vendor->contact          = $request->contact;
            $vendor->tax_number      = $request->tax_number;
            $vendor->created_by       = \Auth::user()->creatorId();
            $vendor->billing_name     = $request->billing_name;
            $vendor->billing_country  = $request->billing_country;
            $vendor->billing_state    = $request->billing_state;
            $vendor->billing_city     = $request->billing_city;
            $vendor->billing_phone    = $request->billing_phone;
            $vendor->billing_zip      = $request->billing_zip;
            $vendor->billing_address  = $request->billing_address;
            $vendor->shipping_name    = $request->shipping_name;
            $vendor->shipping_country = $request->shipping_country;
            $vendor->shipping_state   = $request->shipping_state;
            $vendor->shipping_city    = $request->shipping_city;
            $vendor->shipping_phone   = $request->shipping_phone;
            $vendor->shipping_zip     = $request->shipping_zip;
            $vendor->shipping_address = $request->shipping_address;
            $vendor->save();
            CustomField::saveData($vendor, $request->customField);

            return redirect()->route('vendor.index')->with('success', __('Vendor successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Vendor $vendor)
    {
        if(\Auth::user()->can('delete vendor'))
        {
            if($vendor->created_by == \Auth::user()->creatorId())
            {
                $vendor->delete();

                return redirect()->route('vendor.index')->with('success', __('Vendor successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function vendorNumber()
    {
        $latest = Vendor::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->vendor_id + 1;
    }

    public function vendorLogout(Request $request)
    {
        \Auth::guard('vendor')->logout();

        $request->session()->invalidate();

        return redirect()->route('vendor.login');
    }

    public function payment(Request $request)
    {

        if(\Auth::user()->can('manage vendor payment'))
        {
            $category = [
                'Bill' => 'Bill',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->where('user_type', 'Vendor')->where('type', 'Payment');
            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $payments = $query->get();

            return view('vendor.payment', compact('payments', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction(Request $request)
    {

        if(\Auth::user()->can('manage vendor transaction'))
        {

            $category = [
                'Bill' => 'Bill',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'Vendor');

            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $transactions = $query->get();

            return view('vendor.transaction', compact('transactions', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profile()
    {
        $userDetail              = \Auth::user();
        $userDetail->customField = CustomField::getData($userDetail, 'vendor');
        $customFields            = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();

        return view('vendor.profile', compact('userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {

        $userDetail = \Auth::user();
        $user       = Vendor::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'name' => 'required|max:120',
                        'contact' => 'required',
                        'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                    ]
        );
        if($request->hasFile('profile'))
        {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $dir        = storage_path('uploads/avatar/');
            $image_path = $dir . $userDetail['avatar'];

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }

            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        if(!empty($request->profile))
        {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']    = $request['name'];
        $user['email']   = $request['email'];
        $user['contact'] = $request['contact'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function editBilling(Request $request)
    {

        $userDetail = \Auth::user();
        $user       = Vendor::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'billing_name' => 'required',
                        'billing_country' => 'required',
                        'billing_state' => 'required',
                        'billing_city' => 'required',
                        'billing_phone' => 'required',
                        'billing_zip' => 'required',
                        'billing_address' => 'required',
                    ]
        );
        $input = $request->all();
        $user->fill($input)->save();

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function editShipping(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = Vendor::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'shipping_name' => 'required',
                        'shipping_country' => 'required',
                        'shipping_state' => 'required',
                        'shipping_city' => 'required',
                        'shipping_phone' => 'required',
                        'shipping_zip' => 'required',
                        'shipping_address' => 'required',
                    ]
        );
        $input = $request->all();
        $user->fill($input)->save();

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function changeLanquage($lang)
    {


        $user       = Auth::user();
        $user->lang = $lang;
        $user->save();

        return redirect()->back()->with('success', __('Language successfully change.'));

    }

    public function export()
    {
        $name = 'vendor_' . date('Y-m-d i:h:s');
        $data = Excel::download(new VendorExport(), $name . '.xlsx');

        return $data;
    }

    public function importFile()
    {
        return view('vendor.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $vendors = (new VendorImport())->toArray(request()->file('file'))[0];

        $totalCustomer = count($vendors) - 1;
        $errorArray    = [];
        for($i = 1; $i <= count($vendors) - 1; $i++)
        {
            $vendor = $vendors[$i];

            $vendorByEmail = Vendor::where('email', $vendor[2])->first();

            if(!empty($vendorByEmail))
            {
                $vendorData = $vendorByEmail;
            }
            else
            {
                $vendorData            = new Vendor();
                $vendorData->vendor_id = $this->vendorNumber();
            }

            $vendorData->vendor_id          =$vendor[0];
            $vendorData->name               = $vendor[1];
            $vendorData->email              = $vendor[2];
            $vendorData->contact            = $vendor[3];
            $vendorData->avatar             = $vendor[4];
            $vendorData->billing_name       = $vendor[5];
            $vendorData->billing_country    = $vendor[6];
            $vendorData->billing_state      = $vendor[7];
            $vendorData->billing_city       = $vendor[8];
            $vendorData->billing_phone      = $vendor[9];
            $vendorData->billing_zip        = $vendor[10];
            $vendorData->billing_address    = $vendor[11];
            $vendorData->shipping_name      = $vendor[12];
            $vendorData->shipping_country   = $vendor[13];
            $vendorData->shipping_state     = $vendor[14];
            $vendorData->shipping_city      = $vendor[15];
            $vendorData->shipping_phone     = $vendor[16];
            $vendorData->shipping_zip       = $vendor[17];
            $vendorData->shipping_address   = $vendor[18];
            $vendorData->created_by         = \Auth::user()->creatorId();

            if(empty($vendorData))
            {
                $errorArray[] = $vendorData;
            }
            else
            {
                $vendorData->save();
            }
        }

        $errorRecord = [];
        if(empty($errorArray))
        {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        }
        else
        {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


            foreach($errorArray as $errorData)
            {

                $errorRecord[] = implode(',', $errorData);

            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
}
