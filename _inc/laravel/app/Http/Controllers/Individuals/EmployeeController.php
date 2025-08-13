<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    PlansConstants,
    UsersConstants,
    ViewsConstants
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
use Illuminate\Support\Facades\{Auth, Crypt, File, Hash, Log, Mail, Validator};
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
//use Faker\Provider\File;

class EmployeeController extends Controller
{

    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'employee';
    private const REDIRECT_INDEX = '/';

    public function index(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, PermissionsConstants::MNG_EMP, self::REDIRECT_INDEX)) return $c;
        $employees = strtolower($u[UsersConstants::COL_TP]) === 'employee'
            ? Employee::where(UsersConstants::COL_USER_ID, $u->id)->get()
            : Employee::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
        return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_EMPLOYEES));
    }

    public function create(Request $r)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'create employee', self::REDIRECT_INDEX)) return $c;
        $settings    = Utility::settings();
        $documents   = Document::where(
            DatabaseConstants::TABLE_CREATOR,
            $u->creatorId()
        )->get();
        $branches    = Branch::where(
            DatabaseConstants::TABLE_CREATOR,
            $u->creatorId()
        )->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $departments = Department::where(
            DatabaseConstants::TABLE_CREATOR,
            $u->creatorId()
        )->pluck(CompaniesConstants::COL_DEP_NM, 'id');
        $designations = Designation::where(
            DatabaseConstants::TABLE_CREATOR,
            $u->creatorId()
        )->pluck('name', 'id');
        $employees   = User::where(
            DatabaseConstants::TABLE_CREATOR,
            $u->creatorId()
        )->get();
        $employeesId = $u->employeeIdFormat(self::nextEmployeeNumber());
        return view(
            self::SINGULAR . '.' . __FUNCTION__,
            compact(
                DatabaseConstants::TABLE_EMPLOYEES,
                UsersConstants::COL_EMP_ID,
                DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_DESIGNS,
                DatabaseConstants::TABLE_DOCS,
                DatabaseConstants::TABLE_BRANCHES,
                DatabaseConstants::TABLE_SETTINGS
            )
        );
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'create employee', self::REDIRECT_INDEX)) return $c;
        $rules = [
            'name'           => 'required',
            'dob'            => 'required',
            'phone'          => 'required',
            'address'        => 'required',
            'email'          => 'required|unique:users',
            'password'       => 'required',
            CompaniesConstants::COL_DEP_ID  => 'required',
            'designation_id' => 'required',
        ];
        if ($c = self::v($r, $rules)) return $c;
        try {
            $owner = User::find($u->creatorId());
            $plan = Plan::find($owner->plan);
            if (
                $plan[PlansConstants::COL_MAX_U] !== -1 &&
                $owner->countEmployees() >= $plan[PlansConstants::COL_MAX_U]
            )
                return redirect()
                    ->back()
                    ->with('error', __('Employee limit reached, upgrade plan.'));
            $employeeUser = User::create([
                UsersConstants::COL_NM       => $r[UsersConstants::COL_NM],
                UsersConstants::COL_EM      => $r[UsersConstants::COL_EM],
                UsersConstants::COL_PW   => Hash::make($r[UsersConstants::COL_PW]),
                UsersConstants::COL_TP       => self::SINGULAR,
                UsersConstants::COL_LG       => DatabaseConstants::DEFAULT_UUID,
                DatabaseConstants::TABLE_CREATOR => $u->creatorId(),
            ]);
            $employeeUser->assignRole('Employee');
            $employee = Employee::create([
                UsersConstants::COL_USER_ID              => $employeeUser->id,
                'name'                 => $r->name,
                'dob'                  => $r->dob,
                'gender'               => $r->gender,
                'phone'                => $r->phone,
                'address'              => $r->address,
                'email'                => $r->email,
                'password'             => Hash::make($r->password),
                UsersConstants::COL_EMP_ID          => self::nextEmployeeNumber(),
                CompaniesConstants::COL_BRC_ID            => $r[CompaniesConstants::COL_BRC_ID],
                CompaniesConstants::COL_DEP_ID        => $r[CompaniesConstants::COL_DEP_ID],
                'designation_id'       => $r->designation_id,
                'company_doj'          => $r->company_doj,
                DatabaseConstants::TABLE_DOCS            => $r->hasFile('document')
                    ? implode(',', array_keys($r->file('document')))
                    : null,
                'account_holder_name'  => $r->account_holder_name,
                'account_number'       => $r->account_number,
                'bank_name'            => $r->bank_name,
                'bank_identifier_code' => $r->bank_identifier_code,
                'branch_location'      => $r->branch_location,
                'tax_payer_id'         => $r->tax_payer_id,
                DatabaseConstants::TABLE_CREATOR           => $u->creatorId(),
            ]);

            self::syncDocs($r, $employee->employee_id);

            $settings = Utility::settings($u->creatorId());

            if (!empty($settings['new_user']) && (int) $settings['new_user'] === 1) {
                $mailVars = ['email' => $employeeUser->email, 'password' => $r->password];
                Utility::sendEmailTemplate('new_user', [$employeeUser->id => $employeeUser->email], $mailVars);
            }
            if (
                !empty($settings['slack_employee_notification']) &&
                (int) $settings['slack_employee_notification'] === 1
            )
                Utility::sendSlackMsg('new_employee', [
                    'employee_name' => $employee->name,
                    'department'    => optional($employee->department)->name,
                ]);
            if (
                !empty($settings['telegram_employee_notification']) &&
                (int) $settings['telegram_employee_notification'] === 1
            )
                Utility::sendTelegramMsg('new_employee', [
                    'employee_name' => $employee->name,
                    'department'    => optional($employee->department)->name,
                ]);
            $hook = Utility::webhookSetting('New Employee');
            if (!empty($hook)) {
                $payload = json_encode($employee->only([
                    'id',
                    UsersConstants::COL_EMP_ID,
                    'name',
                    'email',
                    CompaniesConstants::COL_DEP_ID,
                    'designation_id',
                ]));
                if (!Utility::webhookCall($hook['url'], $payload, $hook['method']))
                    Log::warning('Employee webhook failed', ['url' => $hook['url']]);
            }
            return redirect()
                ->route(self::SINGULAR . '.index')
                ->with('success', __('Employee successfully created.'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $r, string $encId)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'edit employee', self::REDIRECT_INDEX)) return $c;
        try {
            $id       = Crypt::decrypt($encId);
            $employee = Employee::findOrFail($id);
            $documents = Document::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
            $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->pluck(CompaniesConstants::COL_BRC_NM, 'id')
                ->prepend('Select Branch', '');
            $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $designations = Designation::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->pluck('name', 'id');
            $employeesId = $u->employeeIdFormat($employee->employee_id);
            $departmentData = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->where(CompaniesConstants::COL_BRC_ID, $employee[CompaniesConstants::COL_BRC_ID])
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            return view(
                self::SINGULAR . '.' . __FUNCTION__,
                compact(
                    self::SINGULAR,
                    UsersConstants::COL_EMP_ID,
                    DatabaseConstants::TABLE_BRANCHES,
                    DatabaseConstants::TABLE_DEPARTMENTS,
                    DatabaseConstants::TABLE_DESIGNS,
                    DatabaseConstants::TABLE_DOCS,
                    'departmentData'
                )
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $r, int $id): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'edit employee', self::REDIRECT_INDEX)) return $c;
        $rules = [
            UsersConstants::COL_NM    => 'required',
            'dob'     => 'required',
            'gender'  => 'required',
            'phone'   => 'required|numeric',
            'address' => 'required',
        ];
        if ($c = self::v($r, $rules)) return $c;
        try {
            $employee = Employee::findOrFail($id);
            $employee->fill($r->all())->save();
            self::syncDocs($r, $employee->employee_id);
            $employee->user()->update([
                UsersConstants::COL_NM  => $employee->name,
                UsersConstants::COL_EM => $employee->email,
            ]);
            $route = $u[UsersConstants::COL_TP] === self::SINGULAR
                ? redirect()->route(self::SINGULAR . '.show', Crypt::encrypt($employee->id))
                : redirect()->route(self::SINGULAR . '.index');
            return $route->with('success', __('Employee successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(Request $r, int $id): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'delete employee', self::REDIRECT_INDEX)) return $c;
        try {
            $employee = Employee::findOrFail($id);
            // purge docs from disk & DB
            $employee->documents()->each(function ($doc) {
                File::delete('uploads/document/' . $doc->document_value);
                $doc->delete();
            });
            // nuke linked User then Employee
            $employee->user()->delete();
            $employee->delete();
            return redirect()
                ->route(self::SINGULAR . '.index')
                ->with('success', __('Employee successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(Request $r, string $encId)
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'view employee', self::REDIRECT_INDEX)) return $c;
        try {
            $id         = Crypt::decrypt($encId);
            $employee   = Employee::findOrFail($id);
            $documents  = Document::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
            $branches   = Branch::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
            $designations = Designation::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->pluck('name', 'id');
            $employeesId = $u->employeeIdFormat($employee->employee_id);
            return view(
                self::SINGULAR . '.' . __FUNCTION__,
                compact(
                    self::SINGULAR,
                    UsersConstants::COL_EMP_ID,
                    DatabaseConstants::TABLE_BRANCHES,
                    DatabaseConstants::TABLE_DEPARTMENTS,
                    DatabaseConstants::TABLE_DESIGNS,
                    DatabaseConstants::TABLE_DOCS
                )
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function json(Request $r): JsonResponse
    {
        $designations = Designation::where(CompaniesConstants::COL_DEP_ID, $r[CompaniesConstants::COL_DEP_ID])
            ->pluck('name', 'id');
        return response()->json($designations);
    }

    /**
     * GET /employee/profile
     */
    public function profile(Request $r): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'manage employee profile', self::REDIRECT_INDEX)) return $c;
        $employeesQ = Employee::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->when($r->branch,      fn($q) => $q->where(CompaniesConstants::COL_BRC_ID,      $r->branch))
            ->when($r->department,  fn($q) => $q->where(CompaniesConstants::COL_DEP_ID,  $r->department))
            ->when($r->designation, fn($q) => $q->where('designation_id', $r->designation));
        $employees = $employeesQ->get();
        $branches    = Branch::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend(__('All'), '');
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id')->prepend(__('All'), '');
        $designations = Designation::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck('name', 'id')->prepend(__('All'), '');
        return view(
            self::SINGULAR . '.' . __FUNCTION__,
            compact(
                DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_BRANCHES,
                DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_DESIGNS
            )
        );
    }

    /**
     * GET /employee/profile/{encId}
     */
    public function profileShow(string $encId): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard(request(), 'show employee profile', self::REDIRECT_INDEX)) return $c;
        try {
            $empId = Crypt::decrypt($encId);
            $employee = Employee::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->findOrFail($empId);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }
        $documents   = Document::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->get();
        $branches    = Branch::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck(CompaniesConstants::COL_DEP_NM, 'id');
        $designations = Designation::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck('name', 'id');
        $employeesId = $u->employeeIdFormat($employee->employee_id);
        return view(self::SINGULAR . '.show', compact(self::SINGULAR, UsersConstants::COL_EMP_ID, DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::TABLE_DEPARTMENTS, DatabaseConstants::TABLE_DESIGNS, DatabaseConstants::TABLE_DOCS));
    }

    /**
     * GET /employee/last‑login
     */
    public function lastLogin(): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $users = User::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
        return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_USERS));
    }

    public function employeeJson(Request $r): JsonResponse
    {
        return response()->json(
            Employee::where(CompaniesConstants::COL_BRC_ID, $r->branch)
                ->pluck(UsersConstants::COL_NM, 'id')
        );
    }

    public function getDepartment(Request $r): JsonResponse|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $departments = Department::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->when($r[CompaniesConstants::COL_BRC_ID] != 0, fn($q) => $q->where(CompaniesConstants::COL_BRC_ID, $r[CompaniesConstants::COL_BRC_ID]))
            ->pluck('name', 'id');

        return response()->json($departments);
    }

    public const JNL_PDF = 'joiningLetterPdf';
    public function joiningletterPdf(int $id): View|RedirectResponse
    {
        return self::renderLetter(
            self::SINGULAR . '.templates.joining_letter_pdf',
            JoiningLetter::class,
            $id
        );
    }

    public const JNL_DOC = 'joiningLetterDoc';
    public function joiningletterDoc(int $id): View|RedirectResponse
    {
        return self::renderLetter(
            self::SINGULAR . '.templates.joining_letter_doc',
            JoiningLetter::class,
            $id
        );
    }

    public const EC_PDF = 'expCertificatePdf';
    public function expCertificatePdf(int $id): View|RedirectResponse
    {
        $term = Termination::where(UsersConstants::COL_EMP_ID, $id)->first();
        if (!$term?->termination_date)
            return back()->with('error', __('Termination date is required.'));
        $emp = Employee::find($id);
        $duration = $emp
            ? now()->diffInDays($emp->company_doj ?? now())
            : 0;
        return self::renderLetter(
            self::SINGULAR . '.templates.exp_certificate_pdf',
            ExperienceCertificate::class,
            $id,
            ['duration' => "$duration days", 'payroll' => optional($emp->salary_type)->name]
        );
    }

    public const EC_DOC = 'expCertificateDoc';
    public function expCertificateDoc(int $id): View|RedirectResponse
    {
        $term = Termination::where(UsersConstants::COL_EMP_ID, $id)->first();
        if (!$term?->termination_date)
            return back()->with('error', __('Termination date is required.'));
        $emp = Employee::find($id);
        $duration = $emp
            ? now()->diffInDays($emp->company_doj ?? now())
            : 0;
        return self::renderLetter(
            self::SINGULAR . '.templates.exp_certificate_doc',
            ExperienceCertificate::class,
            $id,
            ['duration' => "$duration days", 'payroll' => optional($emp->salary_type)->name]
        );
    }

    public const NOC_PDF = 'nocPdf';
    public function nocPdf(int $id): View|RedirectResponse
    {
        return self::renderLetter(
            self::SINGULAR . '.templates.noc_pdf',
            Noc::class,
            $id
        );
    }

    public const NOC_DOC = 'nocDoc';
    public function nocDoc(int $id): View|RedirectResponse
    {
        return self::renderLetter(
            self::SINGULAR . '.templates.noc_doc',
            Noc::class,
            $id
        );
    }

    public function export(): RedirectResponse|BinaryFileResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard(request(), PermissionsConstants::MNG_EMP, self::REDIRECT_INDEX)) return $c;
        try {
            return Excel::download(new EmployeeExport(), self::SINGULAR . '_' .
                now()->format('Y_m_d_His') . '.xlsx');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public const IMP_FL = 'importFile';
    public function importFile(): View
    {
        return view(self::SINGULAR . '.import');
    }

    /**
     * POST /employee/import
     */
    public function import(Request $r): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($r, 'create employee', self::REDIRECT_INDEX)) return $c;
        if ($c = self::v($r, ['file' => 'required|mimes:csv,txt'])) return $c;
        try {
            $rows         = (new EmployeesImport())->toArray($r->file('file'))[0];
            $errorLines   = [];
            $nextEmployeeId = self::nextEmployeeNumber();
            foreach (array_slice($rows, 1) as $i => $row) {
                if (empty($row[5])) {
                    $errorLines[] = $i + 2;
                    continue;
                } // +2 => human row
                [
                    $name,
                    $dob,
                    $gender,
                    $phone,
                    $addr,
                    $email,
                    $pwd,,
                    $branch,
                    $dept,
                    $desg,
                    $doj,
                    $accName,
                    $accNum,
                    $bank,
                    $bic,
                    $brLoc,
                    $tax
                ] = array_pad($row, 18, null);

                $user = User::firstOrCreate(
                    [UsersConstants::COL_EM => $email],
                    [
                        UsersConstants::COL_NM       => $name,
                        UsersConstants::COL_PW   => Hash::make($pwd ?: Str::random(8)),
                        UsersConstants::COL_TP       => self::SINGULAR,
                        UsersConstants::COL_LG     => DatabaseConstants::DEFAULT_LANG,
                        DatabaseConstants::TABLE_CREATOR => $u->creatorId(),
                    ]
                );
                $user?->assignRole('Employee');
                Employee::updateOrCreate(
                    [UsersConstants::COL_USER_ID => $user?->id],
                    [
                        UsersConstants::COL_EMP_ID          => $nextEmployeeId++,
                        UsersConstants::COL_NM                 => $name,
                        'dob'                  => $dob,
                        'gender'               => $gender,
                        'phone'                => $phone,
                        'address'              => $addr,
                        UsersConstants::COL_EM                => $email,
                        UsersConstants::COL_PW             => Hash::make($pwd),
                        CompaniesConstants::COL_BRC_ID            => $branch,
                        CompaniesConstants::COL_DEP_ID        => $dept,
                        'designation_id'       => $desg,
                        'company_doj'          => $doj,
                        'account_holder_name'  => $accName,
                        'account_number'       => $accNum,
                        'bank_name'            => $bank,
                        'bank_identifier_code' => $bic,
                        'branch_location'      => $brLoc,
                        'tax_payer_id'         => $tax,
                        DatabaseConstants::TABLE_CREATOR           => $u->creatorId(),
                    ]
                );
            }

            if ($errorLines)
                return back()->with(
                    'error',
                    __('Rows with missing email: :rows', ['rows' => implode(', ', $errorLines)])
                );

            return back()->with('success', __('Record successfully imported'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }

    private static function nextEmployeeNumber(): int|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $latest = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->latest()
            ->value(UsersConstants::COL_EMP_ID);
        return ((int) $latest) + 1;
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
            DatabaseConstants::TABLE_CREATOR => $u->creatorId()
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
