@php
    try {
$lang = Utility::fetchUserLang();

        $formId     = 'lv-tp-store-form';
        $storeBase  = VW::LV_TP;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::LV_TP, 'store_route_unavailable') ?? __('Leave type store route is unavailable. Please contact technical support or your domain administrator.');

        $titleErr = $errors->has('title');
        $titleAttrs = [
            'id'               => 'title',
            'class'            => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
            'placeholder'      => __('Enter Leave Type Name'),
            'required'         => 'required',
            'aria-invalid'     => $titleErr ? 'true' : 'false',
            'aria-describedby' => $titleErr ? 'title-error' : null,
            'autocomplete'     => 'off',
        ];

        $daysErr = $errors->has('days');
        $daysAttrs = [
            'id'               => 'days',
            'class'            => trim(VC::FM_CT . ' ' . ($daysErr ? 'is-invalid' : '')),
            'placeholder'      => __('Enter Days / Year'),
            'required'         => 'required',
            'aria-invalid'     => $daysErr ? 'true' : 'false',
            'aria-describedby' => $daysErr ? 'days-error' : null,
            'min'              => '0',
            'step'             => '1',
            'inputmode'        => 'numeric',
        ];
    } catch (\Throwable $e) {
        \Log::error('leave_types/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="form-group {{ VC::C12 }}">
                {{ Form::label('title', __('Leave Type'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="form-group {{ VC::C12 }}">
                {{ Form::label('days', __('Days Per Year'), ['class' => VC::FM_LB]) }}
                {{ Form::number('days', null, $daysAttrs) }}
                @error('days')
                    <span id="days-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/leaves/types/store.js') }}"></script>
{{ Form::close() }}
