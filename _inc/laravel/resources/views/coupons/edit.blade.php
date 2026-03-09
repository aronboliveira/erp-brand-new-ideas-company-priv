@php
$lang ??= 'en';
	$generateAiRoute ??= '#';
	$generateAiBtnId ??= 'coupon-generate-ai-btn';
	$generateAiGuardMsg ??= '';
	$settings ??= [];
	$couponUpdateBaseRouteName ??= '';
	$couponUpdateKebabRouteName ??= '';
	$couponUpdateResolvedName ??= null;
	$couponIdValue ??= '';
	$couponUpdateUrl ??= '#';
	$couponUpdateFormId ??= 'coupon-update-form-x';
	$userLang ??= 'en';
	$couponUpdateGuardMessage ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$generateAiRoute = Route::has('generate')
			? (route('generate', ['coupon']) ?? '#')
			: '#';
		$generateAiGuardMsg = Utility::fetchLinkMessage(
			$lang,
			'generics',
			'coupon_generate_ai_route_unavailable'
		) ?? 'AI generate route is unavailable. Please contact technical support or your domain administrator.';
		$settings = Utility::settings() ?? [];
		$couponUpdateBaseRouteName = ViewsConstants::CPN . '.update';
		$couponUpdateKebabRouteName = Str::kebab($couponUpdateBaseRouteName);
		$couponUpdateResolvedName = Route::has($couponUpdateBaseRouteName)
			? $couponUpdateBaseRouteName
			: (Route::has($couponUpdateKebabRouteName) ? $couponUpdateKebabRouteName : null);
		$couponIdValue = (string) data_get($coupon ?? null, 'id', '');
		$couponUpdateUrl = ($couponUpdateResolvedName && $couponIdValue !== '')
			? (route($couponUpdateResolvedName, $couponIdValue) ?? '#')
			: '#';
		$couponUpdateFormId = 'coupon-update-form-' . ($couponIdValue === '' ? 'x' : $couponIdValue);
		$userLang = isset($lang) ? $lang : (Utility::fetchUserLang() ?? 'en');
		$couponUpdateGuardMessage = Utility::fetchLinkMessage($userLang, ViewsConstants::CPN, 'update_coupon_route_unavailable')
			?? 'Update coupon route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\\Error $e) {
		Log::error('Error in coupons/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\\Exception $e) {
		Log::error('Exception in coupons/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\\Throwable $e) {
		Log::error('Throwable in coupons/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($coupon) && isset($coupon->id))
    {{ Form::model($coupon, [
        'method'            => 'PUT',
        'url'               => $couponUpdateUrl,
        'id'                => $couponUpdateFormId,
        'data-url'          => $couponUpdateUrl,
        'data-guard-msg'    => $couponUpdateGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @if(!empty($settings['chat_gpt_key']))
                <div class="{{ VC::FEND }}">
                    <a
                        id="{{ $generateAiBtnId }}"
                        href="#"
                        data-url="{{ $generateAiRoute }}"
                        data-guard-msg="{{ base64_encode($generateAiGuardMsg) }}"
                        data-ajax-popup-over="true"
                        data-size="md"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                        data-bs-toggle="tooltip"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} col-md-12">
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
                <div class="{{ VC::FM_G }} col-md-12">
                    {{ Form::label('code', __('Code'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('code', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/coupons/generate.js') }}"></script>
        <script defer>
            (() => {
                try {
                    const formEl = document.getElementById('{{ $couponUpdateFormId }}');
                    if (!formEl) { return; }
                    if (formEl.getAttribute('data-listener-active') === 'true') { return; }
                    formEl.setAttribute('data-listener-active', 'true');

                    formEl.addEventListener('submit', (e) => {
                        try {
                            const action = formEl.getAttribute('action') ?? '#';
                            const url    = formEl.getAttribute('data-url') ?? action ?? '#';
                            if (url !== '#' && action !== '#') { return; }

                            e.preventDefault();

                            const msg = formEl.getAttribute('data-guard-msg')
                                ?? 'Update coupon route is unavailable. Please contact technical support or your domain administrator.';

                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);

                            formEl.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    });
                } catch (err) {}
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="{{ VC::TXT_MT }}">{{ __('No coupon found') }}</div>
@endif
