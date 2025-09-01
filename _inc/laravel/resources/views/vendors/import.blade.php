@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str, Facades\Storage};

    $lang = Utility::fetchUserLang();

    $importBase  = VW::VND . '.import';
    $importKebab = Str::kebab($importBase);
    $importName  = Route::has($importBase) ? $importBase : (Route::has($importKebab) ? $importKebab : null);
    $importUrl   = $importName ? route($importName) : '#';
    $importGuard = Utility::fetchLinkMessage($lang, VW::VND, 'import_vendor_route_unavailable')
        ?? 'Import vendor route is unavailable. Please contact technical support or your domain administrator.';
    $formId = 'vendor-import-form';
@endphp

{!! Form::open([
    'url'                  => $importUrl,
    'method'               => 'post',
    'id'                   => $formId,
    'enctype'              => 'multipart/form-data',
    'data-resolved-action' => $importUrl,
    'data-guard-msg'       => $importGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }} mb-6">
                {{ Form::label('file', __('Download sample vendor CSV file'), ['class' => VC::FM_LB]) }}
                <a href="{{ asset(Storage::url('uploads/sample')).'/sample-vendor.csv' }}"
                   class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_DWN }}"></i> {{ __('Download') }}
                </a>
            </div>

            <div class="{{ VC::CM12 }}">
                {{ Form::label('file', __('Select CSV File'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <label for="file" class="{{ VC::FM_LB }}">
                        <div>{{ __('Choose file here') }}</div>
                        <input type="file"
                               class="{{ VC::FM_CT }}"
                               name="file"
                               id="file"
                               data-filename="upload_file"
                               required>
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Upload') }}" class="btn btn-primary">
    </div>

    <script defer src="{{ asset('assets/js/routes/vendors/import.js') }}"></script>
{!! Form::close() !!}
