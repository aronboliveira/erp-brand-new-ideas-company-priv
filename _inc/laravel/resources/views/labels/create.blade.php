@php
    try {
$lang = Utility::fetchUserLang();

        $formId     = 'lbl-store-form';
        $storeBase  = VW::LBL;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::LBL, 'store_route_unavailable') ?? __('Label store route is unavailable. Please contact technical support or your domain administrator.');

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

        $nameErr   = $errors->has('name');
        $nameAttrs = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $nameErr ? 'true' : 'false',
            'aria-describedby' => $nameErr ? 'name-error' : null,
            'autocomplete'     => 'off',
        ];

        $colorsIsList = (is_array($colors ?? null) && count($colors ?? []) > 0) || (($colors ?? null) instanceof Collection && $colors->isNotEmpty());
        $colorErr     = $errors->has('color');
    } catch (\Throwable $e) {
        \Log::error('labels/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
            <div class="form-group {{ VC::C12 }}">
                {{ Form::label('name', __('Label Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="form-group {{ VC::C12 }}">
                {{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
                {{ Form::select('pipeline_id', $pipelineOptions, null, $pipelineAttrs) }}
                @error('pipeline_id')
                    <span id="pipeline_id-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="form-group {{ VC::C12 }}">
                {{ Form::label('color', __('Color'), ['class' => VC::FM_LB]) }}
                @if($colorsIsList)
                    <div class="row gutters-xs">
                        @foreach($colors as $idx => $color)
                            @php
                                $cid = 'color-' . $idx;
                                $val = (string) $color;
@endphp
                            <div class="{{ VC::C_AT }}">
                                <label class="colorinput" for="{{ $cid }}">
                                    <input id="{{ $cid }}" name="color" type="radio" value="{{ $val }}" class="colorinput-input" {{ $loop->first ? 'checked' : '' }}>
                                    <span class="colorinput-color bg-{{ $val }}"></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('No colors available.') }}</div>
                @endif
                @error('color')
                    <span class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/labels/store.js') }}"></script>
{{ Form::close() }}
