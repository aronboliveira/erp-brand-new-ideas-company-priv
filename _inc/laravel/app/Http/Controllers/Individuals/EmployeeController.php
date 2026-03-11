<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    PlansConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Exports\{CustomerExport, EmployeeExport};
use App\Imports\EmployeesImport;
use App\Models\{
    Branch,
    Department,
    Designation,
    Document,
    Employee,
    EmployeeDocument,
    ExperienceCertificate,
    JoiningLetter,
    Noc,
    Plan,
    Termination,
    User,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Crypt, File, Hash, Log, Mail, Route, Validator, View as ViewFacade};
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
//use Faker\Provider\File;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
class EmployeeController extends Controller
{
    use HasCrudConstants;


    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'employee';
    private const REDIRECT_INDEX = '/';

    public function index(Request $r)
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, PermissionsConstants::MNG_EMP, self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] fetch employees", ['creator' => $u->creatorId(), 'type' => $u[UsersConstants::COL_TP]]);
            $t = microtime(true);
            $employees = strtolower($u[UsersConstants::COL_TP]) === 'employee'
                ? Employee::where(UsersConstants::COL_USER_ID, $u->id)->get()
                : Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
            $this->logExecutionTime($t, $action, 'employeesLoaded');
            $view = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($r, new \RuntimeException('View not found'), $base . '::' . $action, route(VW::EMP . '.index')); // ! ALERT
            return ViewFacade::make($view, compact(DatabaseConstants::TABLE_EMPLOYEES));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function create(Request $r)
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'create employee', self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] preload form data", ['creator' => $u->creatorId()]);
            $t = microtime(true);
            $settings    = Utility::settings();
            $documents   = Document::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
            $branches    = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('name', 'id');
            $employees   = User::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
            $employee_id = $u->employeeIdFormat(self::nextEmployeeNumber());
            $this->logExecutionTime($t, $action, 'formDataLoaded');
            $view = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($r, new \RuntimeException('View not found'), $base . '::' . $action, route(VW::EMP . '.index')); // ! ALERT
            return ViewFacade::make($view, compact(
                DatabaseConstants::TABLE_EMPLOYEES,
                UsersConstants::COL_EMP_ID,
                DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_DESIGNS,
                DatabaseConstants::TABLE_DOCS,
                DatabaseConstants::TABLE_BRANCHES,
                DatabaseConstants::TABLE_SETTINGS
            ));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'create employee', self::REDIRECT_INDEX)) !== true) return $c;
            $rules = [
                'name' => 'required',
                'dob' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
                CompaniesConstants::COL_DEP_ID => 'required',
                'designation_id' => 'required',
            ];
            if ($c = self::v($r, $rules)) return $c;
            try {
                Log::debug("[$base::$action] plan check", ['creator' => $u->creatorId()]);
                $owner = User::find($u->creatorId());
                $plan = Plan::find($owner->plan);
                if ($plan[PlansConstants::COL_MAX_U] !== -1 && $owner->countEmployees() >= $plan[PlansConstants::COL_MAX_U])
                    return redirect()->back()->with('error', __('Employee limit reached, upgrade plan.')); // ! ALERT
                $t = microtime(true);
                $employeeUser = User::create([
                    UsersConstants::COL_NM => $r[UsersConstants::COL_NM],
                    UsersConstants::COL_EM => $r[UsersConstants::COL_EM],
                    UsersConstants::COL_PW => Hash::make($r[UsersConstants::COL_PW]),
                    UsersConstants::COL_TP => self::SINGULAR,
                    UsersConstants::COL_LG => DatabaseConstants::DEFAULT_UUID,
                    DatabaseConstants::COL_TABLE_CREATOR => $u->creatorId(),
                ]);
                $employeeUser->assignRole('Employee');
                $employee = Employee::create([
                    UsersConstants::COL_USER_ID => $employeeUser->id,
                    'name' => $r->name,
                    'dob' => $r->dob,
                    'gender' => $r->gender,
                    'phone' => $r->phone,
                    'address' => $r->address,
                    'email' => $r->email,
                    'password' => Hash::make($r->password),
                    UsersConstants::COL_EMP_ID => self::nextEmployeeNumber(),
                    CompaniesConstants::COL_BRC_ID => $r[CompaniesConstants::COL_BRC_ID],
                    CompaniesConstants::COL_DEP_ID => $r[CompaniesConstants::COL_DEP_ID],
                    'designation_id' => $r->designation_id,
                    'company_doj' => $r->company_doj,
                    DatabaseConstants::TABLE_DOCS => $r->hasFile('document') ? implode(',', array_keys($r->file('document'))) : null,
                    'account_holder_name' => $r->account_holder_name,
                    'account_number' => $r->account_number,
                    'bank_name' => $r->bank_name,
                    'bank_identifier_code' => $r->bank_identifier_code,
                    'branch_location' => $r->branch_location,
                    'tax_payer_id' => $r->tax_payer_id,
                    DatabaseConstants::COL_TABLE_CREATOR => $u->creatorId(),
                ]);
                self::syncDocs($r, $employee->employee_id);
                $settings = Utility::settingsById($u->creatorId());
                if (!empty($settings['new_user']) && (int) $settings['new_user'] === 1)
                    Utility::sendEmailTemplate('new_user', [$employeeUser->id => $employeeUser->email], ['email' => $employeeUser->email, 'password' => $r->password]);
                if (!empty($settings['slack_employee_notification']) && (int) $settings['slack_employee_notification'] === 1)
                    Utility::sendSlackMsg('new_employee', ['employee_name' => $employee->name, 'department' => optional($employee->department)->name]);
                if (!empty($settings['telegram_employee_notification']) && (int) $settings['telegram_employee_notification'] === 1)
                    Utility::sendTelegramMsg('new_employee', ['employee_name' => $employee->name, 'department' => optional($employee->department)->name]);
                if ($hook = Utility::webhookSetting('New Employee')) {
                    $payload = json_encode($employee->only(['id', UsersConstants::COL_EMP_ID, 'name', 'email', CompaniesConstants::COL_DEP_ID, 'designation_id']));
                    Utility::webhookCall($hook['url'], $payload, $hook['method']) || Log::warning('Employee webhook failed', ['url' => $hook['url']]);
                }
                $this->logExecutionTime($t, $action, 'employeeCreated');
                return redirect()->route(VW::EMP . '.index')->with('success', __('Employee successfully created.')); // ! ALERT
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('error', $e->getMessage()); // ! ALERT
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $base . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function edit(Request $r, string $encId)
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $encId, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit employee', self::REDIRECT_INDEX)) !== true) return $c;
            try {
                $id = Crypt::decrypt($encId);
                Log::debug("[$base::$action] load employee", ['id' => $id]);
                $t = microtime(true);
                $employee = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->findOrFail($id);
                $documents = Document::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
                $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend('Select Branch', '');
                $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id');
                $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('name', 'id');
                $employee_id = $u->employeeIdFormat($employee->employee_id);
                $departmentData = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                    ->where(CompaniesConstants::COL_BRC_ID, $employee[CompaniesConstants::COL_BRC_ID])
                    ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
                $this->logExecutionTime($t, $action, 'formDataLoaded');
                $view = self::SINGULAR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($r, new \RuntimeException('View not found'), $base . '::' . $action, route(VW::EMP . '.index')); // ! ALERT
                return ViewFacade::make($view, compact(
                    self::SINGULAR,
                    UsersConstants::COL_EMP_ID,
                    DatabaseConstants::TABLE_BRANCHES,
                    DatabaseConstants::TABLE_DEPARTMENTS,
                    DatabaseConstants::TABLE_DESIGNS,
                    DatabaseConstants::TABLE_DOCS,
                    'departmentData'
                ));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $base . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function update(Request $r, int $id): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $id, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit employee', self::REDIRECT_INDEX)) !== true) return $c;
            $rules = [
                UsersConstants::COL_NM => 'required',
                'dob' => 'required',
                'gender' => 'required',
                'phone' => 'required|numeric',
                'address' => 'required',
            ];
            if ($c = self::v($r, $rules)) return $c;
            try {
                Log::debug("[$base::$action] updating", ['id' => $id]);
                $t = microtime(true);
                $employee = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->findOrFail($id);
                $employee->fill($r->all())->save();
                self::syncDocs($r, $employee->employee_id);
                $employee->user()->update([UsersConstants::COL_NM => $employee->name, UsersConstants::COL_EM => $employee->email]);
                $this->logExecutionTime($t, $action, 'employeeUpdated');
                $route = $u[UsersConstants::COL_TP] === self::SINGULAR
                    ? redirect()->route(VW::EMP . '.show', Crypt::encrypt($employee->id)) // ! ALERT
                    : redirect()->route(VW::EMP . '.index'); // ! ALERT
                return $route->with('success', __('Employee successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $base . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function destroy(Request $r, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $id, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'delete employee', self::REDIRECT_INDEX)) !== true) return $c;
            try {
                Log::debug("[$base::$action] deleting", ['id' => $id]);
                $t = microtime(true);
                $employee = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->findOrFail($id);
                $employee->documents()->each(function ($doc) {
                    File::delete('uploads/document/' . $doc->document_value);
                    $doc->delete();
                });
                $employee->user()->delete();
                $employee->delete();
                $this->logExecutionTime($t, $action, 'employeeDeleted');
                return redirect()->route(VW::EMP . '.index')->with('success', __('Employee successfully deleted.')); // ! ALERT
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $base . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function show(Request $r, string $encId)
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $encId, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'view employee', self::REDIRECT_INDEX)) !== true) return $c;
            try {
                $id = Crypt::decrypt($encId);
                Log::debug("[$base::$action] load employee", ['id' => $id]);
                $t = microtime(true);
                $employee = Employee::findOrFail($id);
                $documents  = Document::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
                $branches   = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id');
                $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id');
                $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('name', 'id');
                $employee_id = $u->employeeIdFormat($employee->employee_id);
                $this->logExecutionTime($t, $action, 'detailLoaded');
                $view = self::SINGULAR . '.' . $action;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($r, new \RuntimeException('View not found'), $base . '::' . $action, route(VW::EMP . '.index')); // ! ALERT
                return ViewFacade::make($view, compact(
                    self::SINGULAR,
                    UsersConstants::COL_EMP_ID,
                    DatabaseConstants::TABLE_BRANCHES,
                    DatabaseConstants::TABLE_DEPARTMENTS,
                    DatabaseConstants::TABLE_DESIGNS,
                    DatabaseConstants::TABLE_DOCS
                ));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $base . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function json(Request $r): JsonResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r) {
            $designations = Designation::where(CompaniesConstants::COL_DEP_ID, $r[CompaniesConstants::COL_DEP_ID])->pluck('name', 'id');
            return response()->json($designations);
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public function profile(Request $r): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'manage employee profile', self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] filters", $r->only(['branch', 'department', 'designation']));
            $t = microtime(true);
            $employeesQ = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->when($r->branch, fn($q) => $q->where(CompaniesConstants::COL_BRC_ID, $r->branch))
                ->when($r->department, fn($q) => $q->where(CompaniesConstants::COL_DEP_ID, $r->department))
                ->when($r->designation, fn($q) => $q->where('designation_id', $r->designation));
            $employees = $employeesQ->get();
            $branches    = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend(__('All'), '');
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id')->prepend(__('All'), '');
            $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('name', 'id')->prepend(__('All'), '');
            $this->logExecutionTime($t, $action, 'profileDataLoaded');
            $view = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($r, new \RuntimeException('View not found'), $base . '::' . $action, route(VW::EMP . '.index')); // ! ALERT
            return ViewFacade::make($view, compact(
                DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_BRANCHES,
                DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_DESIGNS
            ));
        }, ['route' => Route::getCurrentRoute()?->getName(), 'class' => $base]);
    }

    public const PRF_SHW = 'profileShow';
    public function profileShow(string $encId): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($encId, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard(request(), 'show employee profile', self::REDIRECT_INDEX)) !== true) return $c;
            try {
                $empId = Crypt::decrypt($encId);
                Log::debug("[$base::$action] decrypt ok", ['empId' => $empId]);
                $t = microtime(true);
                $employee = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->whereKey($empId)->firstOrFail();
                $documents   = Document::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
                $branches    = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id');
                $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_DEP_NM, 'id');
                $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('name', 'id');
                $employee_id = $u->employeeIdFormat($employee->employee_id);
                $this->logExecutionTime($t, $action, 'employeeProfileLoaded');
                $view = VW::EMP . '.show';
                if (!ViewFacade::exists($view)) return defaultUndefinedException(request(), new \RuntimeException('View not found'), $base . '::' . $action, route(self::SINGULAR . '.index'));
                return ViewFacade::make($view, compact(self::SINGULAR, UsersConstants::COL_EMP_ID, DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::TABLE_DEPARTMENTS, DatabaseConstants::TABLE_DESIGNS, DatabaseConstants::TABLE_DOCS));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'encId' => $encId]);
    }

    public const LST_LGN = 'lastLogin';
    public function lastLogin(): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            Log::debug("[$base::$action] load users", ['creator' => $u->creatorId()]);
            $t = microtime(true);
            $users = User::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
            $this->logExecutionTime($t, $action, 'usersLoaded');
            $view = VW::EMP . '.' . $action;
            if (!ViewFacade::exists($view)) return defaultUndefinedException(request(), new \RuntimeException('View not found'), $base . '::' . $action, route(self::SINGULAR . '.index'));
            return ViewFacade::make($view, compact(DatabaseConstants::TABLE_USERS));
        }, ['route' => Route::getCurrentRoute()?->getName()]);
    }

    public const EMP_JSON = 'employeeJson';
    public function employeeJson(Request $r): JsonResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            Log::debug("[$base::$action] fetch by branch", ['branch' => $r->branch]);
            $list = Employee::where(CompaniesConstants::COL_BRC_ID, $r->branch)->pluck(UsersConstants::COL_NM, 'id');
            return response()->json($list);
        }, ['route' => Route::getCurrentRoute()?->getName()]);
    }

    public const GET_DPT = 'getDepartment';
    public function getDepartment(Request $r): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug("[$base::$action] list departments", [$r[CompaniesConstants::COL_BRC_ID]]);
            $departments = Department::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->when($r[CompaniesConstants::COL_BRC_ID] != 0, fn($q) => $q->where(CompaniesConstants::COL_BRC_ID, $r[CompaniesConstants::COL_BRC_ID]))
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            return response()->json($departments);
        }, ['route' => Route::getCurrentRoute()?->getName()]);
    }

    public const JNL_PDF = 'joiningLetterPdf';
    public function joiningletterPdf(int $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, fn() => self::renderLetter(self::SINGULAR . '.templates.joining_letter_pdf', JoiningLetter::class, $id), ['id' => $id]);
    }

    public const JNL_DOC = 'joiningLetterDoc';
    public function joiningletterDoc(int $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, fn() => self::renderLetter(self::SINGULAR . '.templates.joining_letter_doc', JoiningLetter::class, $id), ['id' => $id]);
    }

    public const EC_PDF = 'expCertificatePdf';
    public function expCertificatePdf(int $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($id) {
            $term = Termination::where(UsersConstants::COL_EMP_ID, $id)->first();
            if (!$term?->termination_date) return back()->with('error', __('Termination date is required.'));
            $emp = Employee::find($id);
            $duration = $emp ? now()->diffInDays($emp->company_doj ?? now()) : 0;
            return self::renderLetter(self::SINGULAR . '.templates.exp_certificate_pdf', ExperienceCertificate::class, $id, ['duration' => "$duration days", 'payroll' => optional($emp->salary_type)->name]);
        }, ['id' => $id]);
    }

    public const EC_DOC = 'expCertificateDoc';
    public function expCertificateDoc(int $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($id) {
            $term = Termination::where(UsersConstants::COL_EMP_ID, $id)->first();
            if (!$term?->termination_date) return back()->with('error', __('Termination date is required.'));
            $emp = Employee::find($id);
            $duration = $emp ? now()->diffInDays($emp->company_doj ?? now()) : 0;
            return self::renderLetter(self::SINGULAR . '.templates.exp_certificate_doc', ExperienceCertificate::class, $id, ['duration' => "$duration days", 'payroll' => optional($emp->salary_type)->name]);
        }, ['id' => $id]);
    }

    public const NOC_PDF = 'nocPdf';
    public function nocPdf(int $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, fn() => self::renderLetter(self::SINGULAR . '.templates.noc_pdf', Noc::class, $id), ['id' => $id]);
    }

    public const NOC_DOC = 'nocDoc';
    public function nocDoc(int $id): View|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, fn() => self::renderLetter(self::SINGULAR . '.templates.noc_doc', Noc::class, $id), ['id' => $id]);
    }

    public function export(): RedirectResponse|BinaryFileResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard(request(), PermissionsConstants::MNG_EMP, self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] export start", ['creator' => $u->creatorId()]);
            return Excel::download(new EmployeeExport(), self::SINGULAR . '_' . now()->format('Y_m_d_His') . '.xlsx');
        }, ['route' => Route::getCurrentRoute()?->getName()]);
    }

    public const IMP_FL = 'importFile';
    public function importFile(): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($action, $base) {
            $view = VW::EMP . '.import';
            if (!ViewFacade::exists($view)) return defaultUndefinedException(request(), new \RuntimeException('View not found'), $base . '::' . $action, route(self::SINGULAR . '.index'));
            return ViewFacade::make($view);
        }, ['route' => Route::getCurrentRoute()?->getName()]);
    }

    public function import(Request $r): RedirectResponse
    {
        $action = __FUNCTION__;
        $base = class_basename(static::class);
        return $this->measureProfile($action, function () use ($r, $action, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'create employee', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($r, ['file' => 'required|mimes:csv,txt'])) return $c;
            try {
                Log::debug("[$base::$action] import start");
                $t = microtime(true);
                $rows = (new EmployeesImport())->toArray($r->file('file'))[0];
                $errorLines = [];
                $nextEmployeeId = self::nextEmployeeNumber();
                foreach (array_slice($rows, 1) as $i => $row) {
                    if (empty($row[5])) {
                        $errorLines[] = $i + 2;
                        continue;
                    }
                    [$name, $dob, $gender, $phone, $addr, $email, $pwd,, $branch, $dept, $desg, $doj, $accName, $accNum, $bank, $bic, $brLoc, $tax] = array_pad($row, 18, null);
                    $user = User::firstOrCreate(
                        [UsersConstants::COL_EM => $email],
                        [
                            UsersConstants::COL_NM => $name,
                            UsersConstants::COL_PW => Hash::make($pwd ?: Str::random(8)),
                            UsersConstants::COL_TP => self::SINGULAR,
                            UsersConstants::COL_LG => DatabaseConstants::DEFAULT_LANG,
                            DatabaseConstants::COL_TABLE_CREATOR => $u->creatorId(),
                        ]
                    );
                    $user?->assignRole('Employee');
                    Employee::updateOrCreate(
                        [UsersConstants::COL_USER_ID => $user?->id],
                        [
                            UsersConstants::COL_EMP_ID => $nextEmployeeId++,
                            UsersConstants::COL_NM => $name,
                            'dob' => $dob,
                            'gender' => $gender,
                            'phone' => $phone,
                            'address' => $addr,
                            UsersConstants::COL_EM => $email,
                            UsersConstants::COL_PW => Hash::make($pwd),
                            CompaniesConstants::COL_BRC_ID => $branch,
                            CompaniesConstants::COL_DEP_ID => $dept,
                            'designation_id' => $desg,
                            'company_doj' => $doj,
                            'account_holder_name' => $accName,
                            'account_number' => $accNum,
                            'bank_name' => $bank,
                            'bank_identifier_code' => $bic,
                            'branch_location' => $brLoc,
                            'tax_payer_id' => $tax,
                            DatabaseConstants::COL_TABLE_CREATOR => $u->creatorId(),
                        ]
                    );
                }
                $this->logExecutionTime($t, $action, 'importProcessed');
                if ($errorLines) return back()->with('error', __('Rows with missing email: :rows', ['rows' => implode(', ', $errorLines)]));
                return back()->with('success', __('Record successfully imported'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $base . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName()]);
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }

    private static function nextEmployeeNumber(): int|string|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $latest = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
            ->latest()
            ->value(UsersConstants::COL_EMP_ID);
        return is_numeric($latest) ? (int)$latest + 1 : $latest;
    }

    /**
     * Upload any “document[]” files passed with the request
     * and keep DB synchronized (upsert on EmployeeDocument).
     */
    private static function syncDocs(Request $r, int $employeeId): void
    {
        if (!$r->hasFile('document')) return;
        foreach ($r->file('document') as $docId => $file) {
            $storedName = Utility::uploadFileGeneric(
                $file,
                'uploads/document',
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
                    '_' .
                    time()
            );
            EmployeeDocument::updateOrCreate(
                [UsersConstants::COL_EMP_ID => $employeeId, 'document_id' => $docId],
                ['document_value' => $storedName]
            );
        }
    }

    private static function buildLetterContext(Employee $e): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $s     = Utility::settings();
        $offset = strtotime($s['company_end_time']) - strtotime($s['company_start_time']);
        return [
            ActivitiesConstants::COL_TSK_DATE         => $user?->dateFormat(now()),
            'app_name'     => env('APP_NAME'),
            'employee_name' => $e->name,
            'address'      => $e->address ?? '',
            'designation'  => $e->designation->name ?? '',
            'start_date'   => $e->company_doj ?? '',
            'branch'       => $e->branch->name ?? '',
            ActivitiesConstants::COL_ST_TIME   => $s['company_start_time'] ?? '',
            ActivitiesConstants::COL_E_TIME     => $s['company_end_time']   ?? '',
            'total_hours'  => date('H:i', $offset),
        ];
    }

    private static function renderLetter(
        string $view,
        string $templateModel,
        int    $empId,
        array  $extra = []
    ): View|RedirectResponse {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $u = $userOrRedirect;
        $lang = $u->currentLanguage() ?? DatabaseConstants::DEFAULT_LANG;
        $tpl = $templateModel::where([
            'lang' => $lang,
            DatabaseConstants::COL_TABLE_CREATOR => $u->creatorId()
        ])->first();
        $emp = Employee::find($empId);
        if (!$tpl || !$emp) return redirect()->back()->with('error', __('Template or employee missing.'));
        $ctx = array_merge(self::buildLetterContext($emp), $extra);
        $tpl->content = $templateModel::replaceVariable($tpl->content, $ctx);
        return view($view, [
            strtolower(class_basename($templateModel)) => $tpl,
            DatabaseConstants::TABLE_EMPLOYEES => $emp
        ]);
    }
}
