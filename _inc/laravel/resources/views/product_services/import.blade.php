@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, Storage};
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId    = 'prd-sv-import-form';
    $base      = VW::PRD_SV . '.import';
    $baseKebab = Str::kebab($base);
    $routeRes  = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
    $actionUrl = $routeRes ? route($routeRes) : '#';
    $guardMsg  = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'store_import_route_unavailable') ?? __('Product CSV import route is unavailable. Please contact technical support or your domain administrator.');

    $sampleDir = Storage::url('uploads/sample');
    $sampleUrl = $sampleDir ? asset($sampleDir) . '/sample-product.csv' : '#';
@endphp

{{ Collective\Html\FormFacade::open([
    'url'               => $actionUrl,
    'method'            => 'post',
    'enctype'           => 'multipart/form-data',
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }} {{ VC::MB3 }}">
                {{ Collective\Html\FormFacade::label('file', __('Download sample product CSV file'), ['class' => VC::FM_LB]) }}
                <a href="{{ $sampleUrl }}" class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_DWN }}"></i> {{ __('Download') }}
                </a>
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Collective\Html\FormFacade::label('file', __('Select CSV File'), ['class' => VC::FM_LB]) }}
                <div class="choose-file {{ VC::FM_G }}">
                    <input type="file" class="{{ VC::FM_CT }}" name="file" id="file" data-filename="upload_file" required>
                    <p class="upload_file {{ VC::MB0 }}"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Upload') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/products/services/import.js') }}"></script>
{{ Collective\Html\FormFacade::close() }}
