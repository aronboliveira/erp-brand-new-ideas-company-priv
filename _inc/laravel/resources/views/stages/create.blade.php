@php
    try {
$lang = Utility::fetchUserLang();
        $formId = 'store-stage-form';
        $stageCreateBaseName  = ViewsConstants::STG;
        $stageCreateKebabName = Str::kebab($stageCreateBaseName);
        $stageCreateResolved  = Route::has($stageCreateBaseName)
            ? $stageCreateBaseName
            : (Route::has($stageCreateKebabName) ? $stageCreateKebabName : null);
        $stageCreateActionUrl = $stageCreateResolved ? route($stageCreateResolved) : '#';
        $stageCreateGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::STG, 'store_stage_route_unavailable') ?? 'Store stage route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('stages/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'                => $stageCreateActionUrl,
    'method'             => 'post',
    'id'                 => $formId,
    'data-resolved-action' => $stageCreateActionUrl,
    'data-guard-msg'     => $stageCreateGuardMsg,
    'data-sv-localized'  => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Stage Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
                {{ Form::select('pipeline_id', $pipelines, null, ['class' => VC::FM_CT_SL . ' select2', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
<script defer src="{{ asset('js/routes/stages/store.js') }}"></script>
