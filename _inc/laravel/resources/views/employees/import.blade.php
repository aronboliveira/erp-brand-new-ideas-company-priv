@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, Storage};
    use Illuminate\Support\Str;

    $lang               = Utility::fetchUserLang();
    $empImportBase      = VW::EMP.'.import';
    $empImportKebab     = Str::kebab($empImportBase);
    $empImportResolved  = Route::has($empImportBase) ? $empImportBase : (Route::has($empImportKebab) ? $empImportKebab : null);
    $empImportUrl       = $empImportResolved ? route($empImportResolved) : '#';
    $empImportFormId    = 'employee-import-form';
    $empImportGuardMsg  = Utility::fetchLinkMessage($lang, VW::EMP, 'import_employee_route_unavailable') ?? 'Import employee route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'               => $empImportUrl,
    'method'            => 'POST',
    'enctype'           => 'multipart/form-data',
    'id'                => $empImportFormId,
    'data-url'          => $empImportUrl,
    'data-guard-msg'    => $empImportGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }} mb-6">
                {{ Form::label('file', __('Download sample employee CSV file'), ['class' => VC::FM_LB]) }}
                <a href="{{ asset(Storage::url('uploads/sample')).'/sample-employee.csv' }}" class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_DWN }}"></i> {{ __('Download') }}
                </a>
            </div>
            <div class="{{ VC::CM12 }}">
                {{ Form::label('file', __('Select CSV File'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <label for="file" class="{{ VC::FM_LB }}">
                        <input type="file" class="{{ VC::FM_CT }}" name="file" id="file" data-filename="upload_file" accept=".csv,text/csv" required>
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Upload') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/employees/import.js') }}"></script>
{{ Form::close() }}
