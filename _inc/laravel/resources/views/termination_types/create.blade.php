@php
    try {
$lang = Utility::fetchUserLang();
        $ttStoreBaseName     = VW::TMN_TP;
        $ttStoreKebabName    = Str::kebab($ttStoreBaseName);
        $ttStoreResolvedName = Route::has($ttStoreBaseName) ? $ttStoreBaseName : (Route::has($ttStoreKebabName) ? $ttStoreKebabName : null);
        $ttCreateAction      = $ttStoreResolvedName ? route($ttStoreResolvedName) : '#';
        $ttFormId            = 'termination-type-store-form';
        $ttGuardMsg          = Utility::fetchLinkMessage($lang, VW::TMN_TP, 'store_termination_type_route_unavailable') ?? 'Store termination type route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('termination_types/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'               => $ttCreateAction,
    'method'            => 'post',
    'id'                => $ttFormId,
    'data-url'          => $ttCreateAction,
    'data-guard-msg'    => $ttGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Termination Type Name')]) }}
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/terminations/types/store.js') }}"></script>
{{ Form::close() }}
