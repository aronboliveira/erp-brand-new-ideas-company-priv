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
use App\Models\{
    FormBuilder,
    FormField,
    FormFieldResponse,
    FormResponse,
    Lead,
    LeadStage,
    Pipeline,
    User,
    UserLead,
    Utility
};
use App\Traits\ChecksLogin;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request,
    Response
};
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Log,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    private const REDIRECT_BACK = '/';

    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(): View|Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.index';

        return $this->measureProfile($action, function () use ($sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", [UsersConstants::COL_USER_ID => $user?->id]);

            if ($r = $this->_authorize(request(), PermissionsConstants::MNG_FM_BD)) return $r;

            $t = microtime(true);
            $forms = FormBuilder::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            $this->logExecutionTime($t, "$sig::fetchForms", 'completed');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            Log::info("$sig ready", ['count' => $forms->count()]);
            return view($viewPath, compact('forms'));
        });
    }

    public function create(): View|Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.create';

        return $this->measureProfile($action, function () use ($sig, $viewPath) {
            Log::info("$sig start");

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath);
        });
    }

    public function store(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($req, $sig) {
            Log::info("$sig start", ['input' => $req->all()]);

            if ($r = $this->_authorize($req, 'create form builder')) return $r;

            $t = microtime(true);
            $req->validate(['name' => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');

            $user = $req->user();

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $form = FormBuilder::create([
                    'name'        => $req->name,
                    'code'        => uniqid() . time(),
                    'is_active'   => $req->boolean('is_active'),
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                ]);
                DB::commit();
                $this->logExecutionTime($t, "$sig::createFormTransaction", 'completed');

                Log::info("$sig created", ['id' => $form->id]);
                return redirect()->route(ViewsConstants::FM_BD . '.index')
                    ->with('success', __('Form successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return redirect()->back()->with('error', __('Unexpected error.'));
            }
        });
    }

    public function show(FormBuilder $form): View|RedirectResponse|Response|JsonResponse|null
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.show';

        return $this->measureProfile($action, function () use ($form, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['id' => $form->id, UsersConstants::COL_USER_ID => $user?->id]);

            if ($r = $this->_authorize(request(), 'manage form field')) return $r;
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$sig denied", ['id' => $form->id]);
                return response()->json(['error' => __('Permission Denied.')], 401);
            }

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form'));
        });
    }

    public function edit(FormBuilder $form): View|RedirectResponse|Response|JsonResponse|null
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.edit';

        return $this->measureProfile($action, function () use ($form, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['id' => $form->id, UsersConstants::COL_USER_ID => $user?->id]);

            if ($r = $this->_authorize(request(), 'edit form builder')) return $r;
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$sig denied", ['id' => $form->id]);
                return response()->json(['error' => __('Permission Denied.')], 401);
            }

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form'));
        });
    }

    public function update(Request $req, FormBuilder $form): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($req, $form, $sig) {
            Log::info("$sig start", ['id' => $form->id, 'input' => $req->all()]);

            if ($r = $this->_authorize($req, 'edit form builder')) return $r;
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $t = microtime(true);
            $req->validate(['name' => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');

            $form->update([
                'name'           => $req->name,
                'is_active'      => $req->boolean('is_active'),
                'is_lead_active' => false,
            ]);

            Log::info("$sig updated", ['id' => $form->id]);
            return redirect()->route(ViewsConstants::FM_BD . '.index')
                ->with('success', __('Form successfully updated.'));
        });
    }

    public function destroy(FormBuilder $form): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($form, $sig) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['id' => $form->id, UsersConstants::COL_USER_ID => $user?->id]);

            if ($r = $this->_authorize(request(), 'delete form builder')) return $r;
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $t = microtime(true);
            DB::transaction(function () use ($form) {
                FormField::where('form_id', $form->id)->delete();
                FormFieldResponse::where('form_id', $form->id)->delete();
                FormResponse::where('form_id', $form->id)->delete();
                $form->delete();
            });
            $this->logExecutionTime($t, "$sig::transaction", 'completed');

            Log::info("$sig deleted", ['id' => $form->id]);
            return redirect()->route(ViewsConstants::FM_BD . '.index')
                ->with('success', __('Form successfully deleted!'));
        });
    }

    public const FD_CRT = 'fieldCreate';
    public function fieldCreate(int|string $formId): View|Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.fieldCreate';

        return $this->measureProfile($action, function () use ($formId, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['form_id' => $formId, UsersConstants::COL_USER_ID => $user?->id]);

            if ($r = $this->_authorize(request(), 'create form field')) return $r;

            $t = microtime(true);
            $form = FormBuilder::findOrFail($formId);
            $this->logExecutionTime($t, "$sig::findForm", 'completed');

            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $types = FormBuilder::$fieldTypes;

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form', 'types'));
        });
    }

    public const FD_STR = 'fieldStore';
    public function fieldStore(int|string $formId, Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($formId, $req, $sig) {
            Log::info("$sig start", ['form_id' => $formId, 'input' => $req->all()]);

            if ($r = $this->_authorize($req, 'create form field')) return $r;

            $form = FormBuilder::findOrFail($formId);
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $names = $req->input('name', []);
            $types = $req->input('type', []);

            $t = microtime(true);
            foreach ($names as $key => $val) {
                if (trim($val) === '') continue;
                FormField::create([
                    'form_id'     => $formId,
                    'name'        => $val,
                    'type'        => $types[$key] ?? null,
                    DatabaseConstants::TABLE_CREATOR => $req->user()->creatorId(),
                ]);
            }
            $this->logExecutionTime($t, "$sig::createFields", 'completed');

            return redirect()->back()->with('success', __('Field successfully created.'));
        });
    }

    public const FD_EDT = 'fieldEdit';
    public function fieldEdit(int|string $formId, int|string $fieldId): View|Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.field_edit';

        return $this->measureProfile($action, function () use ($formId, $fieldId, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['form_id' => $formId, 'field_id' => $fieldId]);

            if ($r = $this->_authorize(request(), 'edit form field')) return $r;

            $t = microtime(true);
            $form  = FormBuilder::findOrFail($formId);
            $field = FormField::findOrFail($fieldId);
            $this->logExecutionTime($t, "$sig::findFormAndField", 'completed');

            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $types = FormBuilder::$fieldTypes;

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form', 'field', 'types'));
        });
    }

    public const FD_UPD = 'fieldUpdate';
    public function fieldUpdate(int|string $formId, int|string $fieldId, Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($formId, $fieldId, $req, $sig) {
            Log::info("$sig start", ['form_id' => $formId, 'field_id' => $fieldId, 'input' => $req->all()]);

            if ($r = $this->_authorize($req, 'edit form field')) return $r;

            $t = microtime(true);
            $req->validate(['name' => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');

            $form = FormBuilder::findOrFail($formId);
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $t = microtime(true);
            FormField::findOrFail($fieldId)->update(['name' => $req->name, 'type' => $req->type]);
            $this->logExecutionTime($t, "$sig::updateField", 'completed');

            Log::info("$sig updated", ['field_id' => $fieldId]);
            return redirect()->back()->with('success', __('Form successfully updated.'));
        });
    }

    public const FD_DST = 'fieldDestroy';
    public function fieldDestroy(int|string $formId, int|string $fieldId): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($formId, $fieldId, $sig) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['form_id' => $formId, 'field_id' => $fieldId]);

            if ($r = $this->_authorize(request(), 'delete form field')) return $r;

            $form = FormBuilder::findOrFail($formId);
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $t = microtime(true);
            $exists = FormFieldResponse::where(function ($q) use ($fieldId) {
                $q->where('subject_id', $fieldId)
                    ->orWhere('name_id', $fieldId)
                    ->orWhere('email_id', $fieldId);
            })->exists();
            $this->logExecutionTime($t, "$sig::checkBindings", 'completed');

            if ($exists) {
                Log::warning("$sig bound field", ['field_id' => $fieldId]);
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Please remove this field from Convert Lead.'));
            }

            $t = microtime(true);
            FormField::destroy($fieldId);
            $this->logExecutionTime($t, "$sig::deleteField", 'completed');

            Log::info("$sig deleted", ['field_id' => $fieldId]);
            return redirect()->back()->with('success', __('Form successfully deleted.'));
        });
    }

    public const VW_RES = 'viewResponse';
    public function viewResponse(int|string $formId): View|RedirectResponse|Response|JsonResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.response';

        return $this->measureProfile($action, function () use ($formId, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['form_id' => $formId, UsersConstants::COL_USER_ID => Auth::id()]);

            if ($r = $this->_authorize(request(), 'view form response')) return $r;

            $t = microtime(true);
            $form = FormBuilder::findOrFail($formId);
            $this->logExecutionTime($t, "$sig::findForm", 'completed');

            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return response()->json(['error' => __('Permission Denied.')], 401);

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form'));
        });
    }

    public const RES_DT = 'responseDetail';
    public function responseDetail(string|int $responseId): View|RedirectResponse|Response|JsonResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.response_detail';

        return $this->measureProfile($action, function () use ($responseId, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['response_id' => $responseId, UsersConstants::COL_USER_ID => Auth::id()]);

            if ($r = $this->_authorize(request(), 'view form response')) return $r;

            $t = microtime(true);
            $resp = FormResponse::findOrFail($responseId);
            $form = FormBuilder::findOrFail($resp->form_id);
            $this->logExecutionTime($t, "$sig::findResponseAndForm", 'completed');

            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return response()->json(['error' => __('Permission Denied.')], 401);

            $data = json_decode($resp->response, true);

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('data'));
        });
    }

    public const FM_VW = 'formView';
    public function formView(string $code): View|Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.form_view';

        return $this->measureProfile($action, function () use ($code, $sig, $viewPath) {
            Log::info("$sig start", ['code' => $code]);

            $t = microtime(true);
            $form = FormBuilder::where('code', $code)->first();
            $this->logExecutionTime($t, "$sig::findByCode", 'completed');

            if (!$form)
                return redirect()->route('login')->with('error', __('Form not found please contact to admin.'));

            $fields = $form->form_field;

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form', 'fields', 'code'));
        });
    }

    public const FM_VW_STR = 'formViewStore';
    public function formViewStore(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($req, $sig) {
            Log::info("$sig start", $req->all());

            $t = microtime(true);
            $form = FormBuilder::where('code', $req->code)->firstOrFail();
            $this->logExecutionTime($t, "$sig::findForm", 'completed');

            $respArr = [];
            foreach ($req->field as $fid => $val) {
                $name = FormField::find($fid)?->name;
                if (!$name) continue;
                $respArr[$name] = $val ?: '-';
            }

            $t = microtime(true);
            FormResponse::create([
                'form_id'  => $form->id,
                'response' => json_encode($respArr),
            ]);
            $this->logExecutionTime($t, "$sig::createResponse", 'completed');

            if ($form->is_lead_active) {
                $t = microtime(true);
                DB::transaction(function () use ($req, $form) {
                    $mapping = $form->fieldResponse;

                    $email = $req->field[$mapping->email_id] ?? null;
                    if ($email && User::where('email', $email)->exists()) {
                        throw new \Exception('Email exists');
                    }

                    $stage = LeadStage::where(ProjectsConstants::COL_PPL_ID, $mapping->pipeline_id)
                        ->where(DatabaseConstants::TABLE_CREATOR, $form[DatabaseConstants::TABLE_CREATOR])
                        ->firstOrFail();

                    $lead = Lead::create([
                        'name'       => $req->field[$mapping->name_id] ?? '',
                        'email'      => $email,
                        'subject'    => $req->field[$mapping->subject_id] ?? '',
                        UsersConstants::COL_USER_ID    => $mapping->user_id,
                        ProjectsConstants::COL_PPL_ID  => $mapping->pipeline_id,
                        'stage_id'   => $stage->id,
                        DatabaseConstants::TABLE_CREATOR => $form[DatabaseConstants::TABLE_CREATOR],
                        'date'       => now()->toDateString(),
                    ]);

                    UserLead::insert(array_map(fn($uid) => [
                        UsersConstants::COL_USER_ID => $uid,
                        'lead_id'                   => $lead->id
                    ], [$form[DatabaseConstants::TABLE_CREATOR], $mapping->user_id]));
                });
                $this->logExecutionTime($t, "$sig::leadTransaction", 'completed');
            }

            return redirect()->back()->with('success', __('Data submit successfully.'));
        });
    }

    public const FD_BD = 'formFieldBind';
    public function formFieldBind(int|string $formId): View|Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::FM_BD . '.form_field';

        return $this->measureProfile($action, function () use ($formId, $sig, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['form_id' => $formId, UsersConstants::COL_USER_ID => $user?->id]);

            if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN)
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $t = microtime(true);
            $form = FormBuilder::findOrFail($formId);
            $this->logExecutionTime($t, "$sig::findForm", 'completed');

            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $types     = $form->form_field->pluck('name', 'id');
            $binding   = FormFieldResponse::firstOrNew(['form_id' => $formId]);
            $users     = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->where('type', '!=', 'client')->pluck('name', 'id');
            $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return view($viewPath, compact('form', 'types', 'binding', 'users', 'pipelines'));
        });
    }

    public const BD_STR = 'bindStore';
    public function bindStore(Request $req, int|string $formId): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($req, $formId, $sig) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info("$sig start", ['form_id' => $formId, 'input' => $req->all()]);

            if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN)
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $form = FormBuilder::findOrFail($formId);
            if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
                return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));

            $form->is_lead_active = $req->boolean('is_lead_active');
            $form->save();

            if ($form->is_lead_active) {
                $t = microtime(true);
                $req->validate([
                    'subject_id'                 => 'required',
                    'name_id'                    => 'required',
                    'email_id'                   => 'required',
                    UsersConstants::COL_USER_ID  => 'required',
                    ProjectsConstants::COL_PPL_ID => 'required',
                ]);
                $this->logExecutionTime($t, "$sig::validate", 'completed');
            }

            $t = microtime(true);
            FormFieldResponse::updateOrCreate(
                ['form_id' => $formId],
                $req->only(['subject_id', 'name_id', 'email_id', UsersConstants::COL_USER_ID, ProjectsConstants::COL_PPL_ID])
            );
            $this->logExecutionTime($t, "$sig::bindFields", 'completed');

            Log::info("$sig bound", ['form_id' => $formId]);
            return redirect()->back()->with('success', __('Setting saved successfully!'));
        });
    }

    private function _authorize(Request $req, string $perm): ?RedirectResponse
    {
        $user = $req->user();
        if (!$user?->can($perm)) {
            Log::warning('Permission denied', [
                UsersConstants::COL_USER_ID => $user?->id,
                'perm'   => $perm,
                'method' => __METHOD__
            ]);
            return defaultPermissionDenial($req, new \Exception($perm), __CLASS__ . '::' . __FUNCTION__);
        }
        Log::info('Permission granted', [
            UsersConstants::COL_USER_ID => $user?->id,
            'perm'   => $perm,
            'method' => __METHOD__
        ]);
        return null;
    }
}
