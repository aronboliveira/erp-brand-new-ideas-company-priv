@php
    try {
$lang              = Utility::fetchUserLang();
        $generateAiRoute   = Route::has('generate')
            ? route('generate', ['coupon'])
            : '#';
        $generateAiBtnId   = 'coupon-generate-ai-btn';
        $generateAiGuardMsg = Utility::fetchLinkMessage(
            $lang,
            'generics',
            'coupon_generate_ai_route_unavailable'
        ) ?? 'AI generate route is unavailable. Please contact technical support or your domain administrator.';
        $couponStoreBaseRouteName    = ViewsConstants::CPN;
        $couponStoreKebabRouteName   = Str::kebab($couponStoreBaseRouteName);
        $couponStoreResolvedName     = Route::has($couponStoreBaseRouteName)
            ? $couponStoreBaseRouteName
            : (Route::has($couponStoreKebabRouteName) ? $couponStoreKebabRouteName : null);
        $couponStoreUrl              = $couponStoreResolvedName ? route($couponStoreResolvedName) : '#';

        $couponCreateFormId          = 'coupon-store-form';
        $userLang                    = isset($lang) ? $lang : Utility::fetchUserLang();
        $couponCreateGuardMessage    = Utility::fetchLinkMessage($userLang, ViewsConstants::CPN, 'store_coupon_route_unavailable')
            ?? 'Store coupon route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('coupons/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{!! Form::open([
    'method'            => 'POST',
    'url'               => $couponStoreUrl,
    'id'                => $couponCreateFormId,
    'data-url'          => $couponStoreUrl,
    'data-guard-msg'    => $couponCreateGuardMessage,
    'data-sv-localized' => 'true',
]) !!}
    <div class="modal-body">
        {{-- start for ai module --}}
        @php
            $settings = Utility::settings();
@endphp
        @if(!empty($settings['chat_gpt_key']))
            <div class="{{ VC::FEND }}">
                <a
                    id="{{ $generateAiBtnId }}"
                    href="#"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-url="{{ $generateAiRoute }}"
                    data-guard-msg="{{ base64_encode($generateAiGuardMsg) }}"
                    data-ajax-popup-over="true"
                    data-size="md"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                    data-bs-toggle="tooltip"
                    title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        {{-- end for ai module --}}

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT . ' font-style', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('discount', __('Discount'), ['class' => VC::FM_LB]) }}
                {{ Form::number('discount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                <span class="small">{{ __('Note: Discount in Percentage') }}</span>
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('limit', __('Limit'), ['class' => VC::FM_LB]) }}
                {{ Form::number('limit', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('code', __('Code'), ['class' => VC::FM_LB]) }}
                <div class="{{ VC::DFL }} radio-check">
                    <div class="{{ VC::FM_CHK_IL_GP_COLM6 }}">
                        <input type="radio" id="manual_code" value="manual" name="icon-input" class="form-check-input code" checked>
                        <label class="{{ VC::CST_LB }}" for="manual_code">{{ __('Manual') }}</label>
                    </div>
                    <div class="{{ VC::FM_CHK_IL_GP_COLM6 }}">
                        <input type="radio" id="auto_code" value="auto" name="icon-input" class="form-check-input code">
                        <label class="{{ VC::CST_LB }}" for="auto_code">{{ __('Auto Generate') }}</label>
                    </div>
                </div>
            </div>

            <div id="manual" class="{{ VC::FM_G }} {{ VC::C12 }}">
                <input class="{{ VC::FM_CT }} font-uppercase" name="manualCode" type="text">
            </div>

            <div id="auto" class="{{ VC::FM_G }} {{ VC::C12 }} d-none">
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CM10 }}">
                        <input class="{{ VC::FM_CT }}" name="autoCode" type="text" id="auto-code">
                    </div>
                    <div class="{{ VC::CM2 }} mt-2">
                        <a href="#" class="{{ VC::BT_PM }}" id="code-generate">
                            <i class="ti ti-history"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/coupons/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/coupons/generate.js') }}"></script>
{!! Form::close() !!}
