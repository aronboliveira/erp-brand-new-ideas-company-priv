@php
    try {
$lang        = Utility::fetchUserLang();
        $formId      = 'fm-bd-form';
        $storeBase   = VW::FM_BD;
        $storeKebab  = Str::kebab($storeBase);
        $storeRes    = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl    = $storeRes ? route($storeRes) : '#';
        $storeGuard  = Utility::fetchLinkMessage($lang, VW::FM_BD, 'store_route_unavailable') ?? __('Form route is unavailable. Please contact technical support or your domain administrator.');
    } catch (\Throwable $e) {
        \Log::error('form_builders/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
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
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('name', __('Name') ?: __('Failed to get label: Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', '', ['class' => 'form-control','required'=> 'required','placeholder' => __('Enter name') ?: __('Failed to get placeholder: name')]) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                <label class="{{ VC::FM_LB }}">{{ __('Active') ?: __('Failed to get label: Active') }}</label>
                <div class="{{ VC::DFL }} radio-check">
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="on" value="1" name="is_active" class="form-check-input" checked="checked">
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="on">{{ __('On') ?: __('Failed to get label: On') }}</label>
                    </div>
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="off" value="0" name="is_active" class="form-check-input">
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="off">{{ __('Off') ?: __('Failed to get label: Off') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') ?: __('Failed to get label: Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/formBuilders/store.js') }}"></script>
{{ Form::close() }}
