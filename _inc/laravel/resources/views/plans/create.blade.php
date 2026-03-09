@php
    try {
$lang = Utility::fetchUserLang();

        $formId    = 'pln-store-form';
        $base      = VW::PLN;
        $baseKebab = Str::kebab($base);
        $routeRes  = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
        $actionUrl = $routeRes ? route($routeRes) : '#';
        $guardMsg  = Utility::fetchLinkMessage($lang, VW::PLN, 'store_route_unavailable') ?? __('Plan store route is unavailable. Please contact technical support or your domain administrator.');

        $arrDurationIsList = (is_array($arrDuration ?? null) && count($arrDuration ?? []) > 0) || (($arrDuration ?? null) instanceof Collection && $arrDuration->isNotEmpty());
        $arrDurationOpts   = $arrDurationIsList ? (is_array($arrDuration) ? $arrDuration : $arrDuration->toArray()) : ['' => __('No durations available')];
        $durationErr       = $errors->has('duration');
        $durationAttrs     = [
            'id'               => 'duration',
            'class'            => trim(VC::FM_CT . ' select' . ($durationErr ? ' is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $durationErr ? 'true' : 'false',
            'aria-describedby' => $durationErr ? 'duration-error' : null,
        ];
        if (!$arrDurationIsList) { $durationAttrs['disabled'] = 'disabled'; }

        $nameErr = $errors->has('name');
        $nameAttrs = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT . ' font-style' . ($nameErr ? ' is-invalid' : '')),
            'placeholder'      => __('Enter Plan Name'),
            'required'         => 'required',
            'aria-invalid'     => $nameErr ? 'true' : 'false',
            'aria-describedby' => $nameErr ? 'name-error' : null,
            'autocomplete'     => 'off',
        ];

        $priceErr = $errors->has('price');
        $priceAttrs = [
            'id'               => 'price',
            'class'            => trim(VC::FM_CT . ($priceErr ? ' is-invalid' : '')),
            'placeholder'      => __('Enter Plan Price'),
            'aria-invalid'     => $priceErr ? 'true' : 'false',
            'aria-describedby' => $priceErr ? 'price-error' : null,
            'step'             => '0.01',
            'inputmode'        => 'decimal',
        ];

        $qtyAttrs = fn(string $id) => [
            'id'               => $id,
            'class'            => VC::FM_CT,
            'required'         => 'required',
            'inputmode'        => 'numeric',
        ];

        $aiGuardMsg = Utility::fetchLinkMessage($lang, VW::PLN, 'generate_route_unavailable') ?? __('AI generate route for Plans is unavailable. Please contact technical support or your domain administrator.');
    } catch (\Throwable $e) {
        \Log::error('plans/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'               => $actionUrl,
    'enctype'           => 'multipart/form-data',
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        @php
 $settings = Utility::settings();
@endphp
        @if(!empty($settings['chat_gpt_key']))
            <div class="{{ VC::TX_END }}">
                <a href="#"
                class="{{ VC::BT_SM }} {{ VC::BT_PRM }} ai-btn"
                data-size="md"
                data-ajax-popup-over="true"
                data-url="{{ route('generate', ['plan']) }}"
                data-guard-msg="{{ base64_encode($aiGuardMsg) }}"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('price', __('Price'), ['class' => VC::FM_LB]) }}
                {{ Form::number('price', null, $priceAttrs) }}
                @error('price')
                    <span id="price-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('duration', __('Duration'), ['class' => VC::FM_LB]) }}
                {{ Form::select('duration', $arrDurationOpts, null, $durationAttrs) }}
                @error('duration')
                    <span id="duration-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
                @unless($arrDurationIsList)
                    <span class="{{ VC::TXT_MT }} d-block mt-1">{{ __('No durations available') }}</span>
                @endunless
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('max_users', __('Maximum Users'), ['class' => VC::FM_LB]) }}
                {{ Form::number('max_users', null, $qtyAttrs('max_users')) }}
                <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('max_customers', __('Maximum Customers'), ['class' => VC::FM_LB]) }}
                {{ Form::number('max_customers', null, $qtyAttrs('max_customers')) }}
                <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('max_vendors', __('Maximum Vendors'), ['class' => VC::FM_LB]) }}
                {{ Form::number('max_vendors', null, $qtyAttrs('max_vendors')) }}
                <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('max_clients', __('Maximum Clients'), ['class' => VC::FM_LB]) }}
                {{ Form::number('max_clients', null, $qtyAttrs('max_clients')) }}
                <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('storage_limit', __('Storage limit'), ['class' => VC::FM_LB]) }}
                <div class="input-group">
                    {{ Form::number('storage_limit', null, ['id' => 'storage_limit', 'class' => VC::FM_CT, 'required' => 'required', 'inputmode' => 'numeric']) }}
                    <span class="{{ VC::TXTS_TRP }}" id="basic-addon2">{{ __('MB') }}</span>
                </div>
                <span class="small">{{ __('Note: upload size ( In MB)') }}</span>
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['id' => 'description', 'class' => VC::FM_CT, 'rows' => 2]) }}
            </div>

            <div class="{{ VC::FM_GCB3 }}">
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" class="form-check-input" name="enable_crm" id="enable_crm">
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_crm">{{ __('CRM') }}</label>
                </div>
            </div>
            <div class="{{ VC::FM_GCB3 }}">
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" class="form-check-input" name="enable_project" id="enable_project">
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_project">{{ __('Project') }}</label>
                </div>
            </div>
            <div class="{{ VC::FM_GCB3 }}">
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" class="form-check-input" name="enable_hrm" id="enable_hrm">
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_hrm">{{ __('HRM') }}</label>
                </div>
            </div>
            <div class="{{ VC::FM_GCB3 }}">
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" class="form-check-input" name="enable_account" id="enable_account">
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_account">{{ __('Account') }}</label>
                </div>
            </div>
            <div class="{{ VC::FM_GCB3 }}">
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" class="form-check-input" name="enable_pos" id="enable_pos">
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_pos">{{ __('POS') }}</label>
                </div>
            </div>
            <div class="{{ VC::FM_GCB3 }}">
                <div class="{{ VC::FM_CHK }} form-switch">
                    <input type="checkbox" class="form-check-input" name="enable_chatgpt" id="enable_chatgpt">
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_chatgpt">{{ __('Chat GPT') }}</label>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/plans/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/ai/generateStore.js') }}"></script>
{{ Form::close() }}
