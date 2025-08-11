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
    Validator
};
use Illuminate\View\View;
use Throwable;

class FormBuilderController extends Controller
{
    private const REDIRECT_BACK = 'back';

    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(): Response|RedirectResponse
    {
        $function = __FUNCTION__;
        $method = __METHOD__;
        $cls = __CLASS__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info($cls . '.' . $function, [UsersConstants::COL_USER_ID => $user?->id]);
        if ($r = $this->_authorize(request(), PermissionsConstants::MNG_FM_BD)) return $r;
        $forms = FormBuilder::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
        Log::info($cls . '.' . $function . ' loaded', [
            'count' => $forms->count(), 'method' => $method
        ]);
        return view(ViewsConstants::FM_BD . '.' . $function, compact('forms'));
    }

    public function create(): View
    {
        Log::info(__CLASS__ . '.' . __FUNCTION__);
        return view(ViewsConstants::FM_BD . '.' . __FUNCTION__);
    }

    public function store(Request $req): RedirectResponse
    {
        Log::info(__CLASS__ . '.' . __FUNCTION__, $req->all());
        if ($r = $this->_authorize($req, 'create form builder')) return $r;
        $req->validate(['name' => 'required']);
        $user = $req->user();
        $form = FormBuilder::create([
            'name'       => $req->name,
            'code'       => uniqid() . time(),
            'is_active'  => $req->boolean('is_active'),
            DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
        ]);
        Log::info('FormBuilder created', ['id' => $form->id]);
        return redirect()->route(ViewsConstants::FM_BD . '.index')
            ->with('success', __('Form successfully created.'));
    }

    public function show(FormBuilder $form): Response|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '.show', ['id' => $form->id, UsersConstants::COL_USER_ID => $user?->id]);
        if ($r = $this->_authorize(request(), 'manage form field')) return $r;
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            Log::warning(__CLASS__ . '.show denied', ['id' => $form->id]);
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        return view(ViewsConstants::FM_BD . '.' . __FUNCTION__, compact('form'));
    }

    public function edit(FormBuilder $form): Response|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '.edit', ['id' => $form->id, UsersConstants::COL_USER_ID => $user?->id]);
        if ($r = $this->_authorize(request(), 'edit form builder')) return $r;
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            Log::warning(__CLASS__ . '.edit denied', ['id' => $form->id]);
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        return view(ViewsConstants::FM_BD . '.' . __FUNCTION__, compact('form'));
    }

    public function update(Request $req, FormBuilder $form): RedirectResponse
    {
        Log::info(__CLASS__ . '.update', ['id' => $form->id, 'input' => $req->all()]);
        if ($r = $this->_authorize($req, 'edit form builder')) return $r;
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $req->validate(['name' => 'required']);
        $form->update([
            'name'          => $req->name,
            'is_active'     => $req->boolean('is_active'),
            'is_lead_active' => false,
        ]);
        Log::info('FormBuilder updated', ['id' => $form->id]);
        return redirect()->route(ViewsConstants::FM_BD . '.index')
            ->with('success', __('Form successfully updated.'));
    }

    public function destroy(FormBuilder $form): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '.destroy', ['id' => $form->id, UsersConstants::COL_USER_ID => $user?->id]);
        if ($r = $this->_authorize(request(), 'delete form builder')) return $r;
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        DB::transaction(function () use ($form) {
            FormField::where('form_id', $form->id)->delete();
            FormFieldResponse::where('form_id', $form->id)->delete();
            FormResponse::where('form_id', $form->id)->delete();
            $form->delete();
        });
        Log::info('FormBuilder and related cleaned up', ['id' => $form->id]);
        return redirect()->route(ViewsConstants::FM_BD . '.index')
            ->with('success', __('Form successfully deleted!'));
    }

    public function fieldCreate(int $formId): Response|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('fieldCreate', ['form_id' => $formId, UsersConstants::COL_USER_ID => $user?->id]);
        if ($r = $this->_authorize(request(), 'create form field')) return $r;
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $types = FormBuilder::$fieldTypes;
        return view(ViewsConstants::FM_BD . '.' . __FUNCTION__, compact('form', 'types'));
    }

    public function fieldStore(int $formId, Request $req): RedirectResponse
    {
        Log::info('fieldStore', ['form_id' => $formId, 'input' => $req->all()]);
        if ($r = $this->_authorize($req, 'create form field')) return $r;
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $names = $req->input('name', []);
        $types = $req->input('type', []);
        foreach ($names as $key => $val) {
            if (trim($val) === '') continue;
            FormField::create([
                'form_id'   => $formId,
                'name'      => $val,
                'type'      => $types[$key] ?? null,
                DatabaseConstants::TABLE_CREATOR => $req->user()->creatorId(),
            ]);
            Log::info('FormField created', ['form_id' => $formId, 'field' => $val]);
        }
        return redirect()->back()
            ->with('success', __('Field successfully created.'));
    }

    public function fieldEdit(int $formId, int $fieldId): Response|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('fieldEdit', ['form_id' => $formId, 'field_id' => $fieldId]);
        if ($r = $this->_authorize(request(), 'edit form field')) return $r;
        $form = FormBuilder::findOrFail($formId);
        $field = FormField::findOrFail($fieldId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $types = FormBuilder::$fieldTypes;
        return view(ViewsConstants::FM_BD . '.field_edit', compact('form', 'field', 'types'));
    }

    public function fieldUpdate(int $formId, int $fieldId, Request $req): RedirectResponse
    {
        Log::info('fieldUpdate', ['form_id' => $formId, 'field_id' => $fieldId, 'input' => $req->all()]);
        if ($r = $this->_authorize($req, 'edit form field')) return $r;
        $req->validate(['name' => 'required']);
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        FormField::findOrFail($fieldId)
            ->update(['name' => $req->name, 'type' => $req->type]);
        Log::info('FormField updated', ['field_id' => $fieldId]);
        return redirect()->back()
            ->with('success', __('Form successfully updated.'));
    }

    public function fieldDestroy(int $formId, int $fieldId): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('fieldDestroy', ['form_id' => $formId, 'field_id' => $fieldId]);
        if ($r = $this->_authorize(request(), 'delete form field')) return $r;
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $exists = FormFieldResponse::where(function ($q) use ($fieldId) {
            $q->where('subject_id', $fieldId)
                ->orWhere('name_id', $fieldId)
                ->orWhere('email_id', $fieldId);
        })->exists();
        if ($exists) {
            Log::warning('Cannot delete field; bound in responses', ['field_id' => $fieldId]);
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Please remove this field from Convert Lead.'));
        }
        FormField::destroy($fieldId);
        Log::info('FormField deleted', ['field_id' => $fieldId]);
        return redirect()->back()
            ->with('success', __('Form successfully deleted.'));
    }

    public function viewResponse(int $formId): Response|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('viewResponse', ['form_id' => $formId, UsersConstants::COL_USER_ID => Auth::id()]);
        if ($r = $this->_authorize(request(), 'view form response')) return $r;
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return response()->json(['error' => __('Permission Denied.')], 401);
        return view(ViewsConstants::FM_BD . '.response', compact('form'));
    }

    public function responseDetail(int $responseId): Response|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('responseDetail', ['response_id' => $responseId, UsersConstants::COL_USER_ID => Auth::id()]);
        if ($r = $this->_authorize(request(), 'view form response')) return $r;
        $resp = FormResponse::findOrFail($responseId);
        $form = FormBuilder::findOrFail($resp->form_id);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return response()->json(['error' => __('Permission Denied.')], 401);
        $data = json_decode($resp->response, true);
        return view(ViewsConstants::FM_BD . '.response_detail', compact('data'));
    }

    public function formView(string $code): Response|RedirectResponse
    {
        Log::info('formView', ['code' => $code]);
        $form = FormBuilder::where('code', $code)->first();
        if (!$form)
            return redirect()->route('login')
                ->with('error', __('Form not found please contact to admin.'));
        $fields = $form->form_field;
        return view(ViewsConstants::FM_BD . '.form_view', compact('form', 'fields', 'code'));
    }

    public function formViewStore(Request $req): RedirectResponse
    {
        Log::info('formViewStore', $req->all());
        $form = FormBuilder::where('code', $req->code)->firstOrFail();
        $respArr = [];
        foreach ($req->field as $fid => $val) {
            $name = FormField::find($fid)->name;
            $respArr[$name] = $val ?: '-';
        }
        FormResponse::create([
            'form_id'  => $form->id,
            'response' => json_encode($respArr),
        ]);
        Log::info('FormResponse saved', ['form_id' => $form->id]);
        if ($form->is_lead_active) {
            DB::transaction(function () use ($req, $form) {
                // validate unique email
                $mapping = $form->fieldResponse;
                $email  = $req->field[$mapping->email_id] ?? null;
                if (User::where('email', $email)->exists())
                    throw new \Exception('Email exists');
                $stage = LeadStage::where(ProjectsConstants::COL_PPL_ID, $mapping->pipeline_id)
                    ->where(DatabaseConstants::TABLE_CREATOR, $form[DatabaseConstants::TABLE_CREATOR])->firstOrFail();
                $lead = Lead::create([
                    'name' => $req->field[$mapping->name_id] ?? '',
                    'email' => $email,
                    'subject' => $req->field[$mapping->subject_id] ?? '',
                    UsersConstants::COL_USER_ID => $mapping->user_id,
                    ProjectsConstants::COL_PPL_ID => $mapping->pipeline_id,
                    'stage_id' => $stage->id,
                    DatabaseConstants::TABLE_CREATOR => $form[DatabaseConstants::TABLE_CREATOR],
                    'date' => now()->toDateString(),
                ]);
                UserLead::insert(array_map(fn ($uid) => [
                    UsersConstants::COL_USER_ID => $uid, 'lead_id' => $lead->id
                ], [$form[DatabaseConstants::TABLE_CREATOR], $mapping->user_id]));
                Log::info('Lead created from form', ['lead_id' => $lead->id]);
            });
        }
        return redirect()->back()
            ->with('success', __('Data submit successfully.'));
    }

    public function formFieldBind(int $formId): Response|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('formFieldBind', ['form_id' => $formId, UsersConstants::COL_USER_ID => $user?->id]);
        if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN)
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $types    = $form->form_field->pluck('name', 'id');
        $binding  = FormFieldResponse::firstOrNew(['form_id' => $formId]);
        $users    = User::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->where('type', '!=', 'client')->pluck('name', 'id');
        $pipelines = Pipeline::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
        return view(ViewsConstants::FM_BD . '.form_field', compact(
            'form',
            'types',
            'binding',
            'users',
            'pipelines'
        ));
    }

    public function bindStore(Request $req, int $formId): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info('bindStore', ['form_id' => $formId, 'input' => $req->all()]);
        if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CPN)
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $form = FormBuilder::findOrFail($formId);
        if ($form[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        $form->is_lead_active = $req->boolean('is_lead_active');
        $form->save();
        if ($form->is_lead_active)
            $req->validate([
                'subject_id'  => 'required',
                'name_id'     => 'required',
                'email_id'    => 'required',
                UsersConstants::COL_USER_ID     => 'required',
                ProjectsConstants::COL_PPL_ID => 'required',
            ]);
        FormFieldResponse::updateOrCreate(
            ['form_id' => $formId],
            $req->only(['subject_id', 'name_id', 'email_id', UsersConstants::COL_USER_ID, ProjectsConstants::COL_PPL_ID])
        );
        Log::info('FormFieldResponse bound', ['form_id' => $formId]);
        return redirect()->back()
            ->with('success', __('Setting saved successfully!'));
    }

    private function _authorize(Request $req, string $perm): ?RedirectResponse
    {
        $user = $req->user();
        if (!$user?->can($perm)) {
            Log::warning('Permission denied', [
                UsersConstants::COL_USER_ID => $user?->id, 'perm' => $perm, 'method' => __METHOD__
            ]);
            return defaultPermissionDenial($req, new \Exception($perm), __CLASS__ . '::' . __FUNCTION__);
        }
        Log::info('Permission granted', [
            UsersConstants::COL_USER_ID => $user?->id, 'perm' => $perm, 'method' => __METHOD__
        ]);
        return null;
    }
}
