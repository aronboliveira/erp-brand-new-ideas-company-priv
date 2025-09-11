@php
    use App\Config\Constants\{ViewClassNamesConstants as VC, ViewsConstants as VW};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\Str;

    $lang        = Utility::fetchUserLang();
    $formId      = 'fm-bd-form';
    $storeBase   = VW::FM_BD;
    $storeKebab  = Str::kebab($storeBase);
    $storeRes    = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl    = $storeRes ? route($storeRes) : '#';
    $storeGuard  = Utility::fetchLinkMessage($lang, VW::FM_BD, 'store_route_unavailable') ?? __('Form route is unavailable. Please contact technical support or your domain administrator.');
@endphp
{{ Form::open([
    'url'               => $storeUrl,
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-12 form-group">
                {{ Form::label('name', __('Name') ?: __('Failed to get label: Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', '', ['class' => 'form-control','required'=> 'required','placeholder' => __('Enter name') ?: __('Failed to get placeholder: name')]) }}
            </div>
            <div class="col-12 form-group">
                <label class="form-label">{{ __('Active') ?: __('Failed to get label: Active') }}</label>
                <div class="d-flex radio-check">
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="on" value="1" name="is_active" class="form-check-input" checked="checked">
                        <label class="custom-control-label form-label" for="on">{{ __('On') ?: __('Failed to get label: On') }}</label>
                    </div>
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="off" value="0" name="is_active" class="form-check-input">
                        <label class="custom-control-label form-label" for="off">{{ __('Off') ?: __('Failed to get label: Off') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') ?: __('Failed to get label: Create') }}" class="btn btn-primary">
    </div>
    <script defer src="{{ asset('assets/js/routes/formBuilders/store.js') }}"></script>
{{ Form::close() }}