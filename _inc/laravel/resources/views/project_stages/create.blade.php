@php
    try {
$lang = Utility::fetchUserLang();
        $storeBaseName   = VW::PRJ_STG;
        $storeKebabName  = Str::kebab($storeBaseName);
        $storeResolved   = Route::has($storeBaseName) ? $storeBaseName : (Route::has($storeKebabName) ? $storeKebabName : null);
        $storeUrl        = $storeResolved ? route($storeResolved) : '#';
        $formId          = 'create-project-stage-form';
        $formGuardMsg    = Utility::fetchLinkMessage($lang, VW::PRJ_STG, 'store_project_stage_unavailable') ?? 'Store project stage route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('project_stages/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="{{ VC::CD }} bg-none card-box">
    {!! Form::open([
        'url'            => $storeUrl,
        'method'         => 'post',
        'id'             => $formId,
        'data-guard-msg' => $formGuardMsg,
    ]) !!}
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Project Stage Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('color', __('Color'), ['class' => VC::FM_LB]) }}
                <input class="jscolor {{ VC::FM_CT }}" value="FFFFFF" name="color" id="color" required>
                <small class="small">{{ __('For chart representation') }}</small>
            </div>
            <div class="{{ VC::C12 }} text-end">
                <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
                <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            </div>
        </div>
        <script src="{{ asset('assets/js/routes/projects/stages/store.js') }}" defer></script>
    {!! Form::close() !!}
</div>
