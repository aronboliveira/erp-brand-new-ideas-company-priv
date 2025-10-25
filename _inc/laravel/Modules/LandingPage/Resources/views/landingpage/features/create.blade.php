@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang                 = Utility::fetchUserLang();
    $featureStoreBase     = VW::FT;
    $featureStoreKebab    = Str::kebab($featureStoreBase);
    $featureStoreResolved = Route::has($featureStoreBase) ? $featureStoreBase : (Route::has($featureStoreKebab) ? $featureStoreKebab : null);
    $featureStoreUrl      = $featureStoreResolved ? route($featureStoreResolved) : '#';
    $featureStoreFormId   = 'feature-store-form';
    $featureGuardMsg      = Utility::fetchLinkMessage($lang, VW::FT, 'feature_store_route_unavailable') ?? 'Store feature route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'               => $featureStoreUrl,
    'method'            => 'post',
    'enctype'           => 'multipart/form-data',
    'id'                => $featureStoreFormId,
    'data-url'          => $featureStoreUrl,
    'data-guard-msg'    => $featureGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        @csrf
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('feature_heading', __('Heading'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('feature_heading', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Heading'), 'autocomplete' => 'off']) }}
                </div>
            </div>

            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('feature_description', __('Description'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('feature_description', null, ['class' => VC::FM_CT.' summernote-simple', 'placeholder' => __('Enter Description')]) }}
                </div>
            </div>

            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('feature_logo', __('Logo'), ['class' => VC::FM_LB]) }}
                    <input type="file" name="feature_logo" id="feature_logo" class="{{ VC::FM_CT }}" required="required">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/features/store.js') }}"></script>
{{ Form::close() }}


{{--<script>--}}
{{--    tinymce.init({--}}
{{--      selector: '#mytextarea',--}}
{{--      menubar: '',--}}
{{--    });--}}
{{--  </script>--}}
