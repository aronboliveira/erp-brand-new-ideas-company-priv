@php
    try {
$lang = Utility::fetchUserLang();

        $formId     = 'ld-stg-store-form';
        $storeBase  = VW::LD_STG;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::LD_STG, 'store_route_unavailable') ?? __('Lead stage store route is unavailable. Please contact technical support or your domain administrator.');

        $nameErr   = $errors->has('name');
        $nameAttrs = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $nameErr ? 'true' : 'false',
            'aria-describedby' => $nameErr ? 'name-error' : null,
            'autocomplete'     => 'off',
        ];

        $pipelinesIsList = (is_array($pipelines ?? null) && count($pipelines ?? []) > 0) || (($pipelines ?? null) instanceof Collection && $pipelines->isNotEmpty());
        $pipelineOptions = $pipelinesIsList ? (is_array($pipelines) ? $pipelines : $pipelines->toArray()) : ['' => __('No pipelines available')];
        $pipelineErr     = $errors->has('pipeline_id');
        $pipelineAttrs   = [
            'id'               => 'pipeline_id',
            'class'            => trim(VC::FM_CT_SL . ' select2 ' . ($pipelineErr ? 'is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $pipelineErr ? 'true' : 'false',
            'aria-describedby' => $pipelineErr ? 'pipeline_id-error' : null,
        ];
        if (!$pipelinesIsList) { $pipelineAttrs['disabled'] = 'disabled'; }
    } catch (\Throwable $e) {
        \Log::error('lead_stages/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Lead Stage Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
                {{ Form::select('pipeline_id', $pipelineOptions, null, $pipelineAttrs) }}
                @error('pipeline_id')
                    <span id="pipeline_id-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/leads/stages/store.js') }}"></script>
{{ Form::close() }}
