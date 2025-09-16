@php
    use App.Config.Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId    = 'ppl-store-form';
    $base      = VW::PPL;
    $baseKebab = Str::kebab($base);
    $routeRes  = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
    $actionUrl = $routeRes ? route($routeRes) : '#';
    $guardMsg  = Utility::fetchLinkMessage($lang, VW::PPL, 'store_route_unavailable') ?? __('Pipeline store route is unavailable. Please contact technical support or your domain administrator.');

    $nameErr = $errors->has('name');
    $nameAttrs = [
        'id'               => 'name',
        'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $nameErr ? 'true' : 'false',
        'aria-describedby' => $nameErr ? 'name-error' : null,
        'autocomplete'     => 'off',
    ];
@endphp

{{ Form::open([
    'url'               => $actionUrl,
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Pipeline Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/pipelines/store.js') }}"></script>
{{ Form::close() }}
