@php
$user ??= null;
	$lang ??= 'en';
	$dlStoreBaseRouteName ??= '';
	$dlStoreKebabRouteName ??= '';
	$dlStoreResolvedName ??= null;
	$dlStoreUrl ??= '#';
	$dlStoreFormId ??= 'deal-store-form';
	$userLang ??= 'en';
	$dlStoreGuardMessage ??= '';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
		$dlStoreBaseRouteName = ViewsConstants::DL;
		$dlStoreKebabRouteName = Str::kebab($dlStoreBaseRouteName);
		$dlStoreResolvedName = Route::has($dlStoreBaseRouteName)
			? $dlStoreBaseRouteName
			: (Route::has($dlStoreKebabRouteName) ? $dlStoreKebabRouteName : null);
		$dlStoreUrl = $dlStoreResolvedName ? (route($dlStoreResolvedName) ?? '#') : '#';
		$userLang = isset($lang) ? $lang : Utility::fetchUserLang();
		$dlStoreGuardMessage = Utility::fetchLinkMessage($userLang, ViewsConstants::DL, 'store_deal_route_unavailable')
			?? 'Store Deal route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in deals/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in deals/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in deals/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open([
    'method'            => 'POST',
    'url'               => $dlStoreUrl,
    'id'                => $dlStoreFormId,
    'data-url'          => $dlStoreUrl,
    'data-guard-msg'    => $dlStoreGuardMessage,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        @php
 $plan = Utility::getChatGPTSettings();
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1 && !empty($deal) && isset($deal->id))
            <div class="{{ VC::TX_END }}">
                @php
                    try {
                        $generateRoute = Route::has('generate')
                            ? route('generate', ['deal' => $deal->id])
                            : '#';
                        $generateGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::DL,
                            'generate_route_unavailable'
                        ) ?? 'Generate content for deals with AI route is unavailable. Please contact technical support or your domain administrator.';
                    } catch (\Throwable $e) {
                        \Log::error('deals/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a
                    id="generate-ai-btn-{{ $deal->id }}"
                    href="{{ $generateRoute }}"
                    data-url="{{ $generateRoute }}"
                    data-guard-msg="{{ base64_encode($generateGuardMsg) }}"
                    data-size="md"
                    class="{{ VC::BT_PRM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
                <script defer>
                    (() => {
                        const btn = document.getElementById('generate-ai-btn-{{ $deal->id }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active','true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') ?? '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                btn.setAttribute('data-failed-route','true');
                            } catch {}
                        });
                    })();
                </script>
            </div>
        @endif
        <div class="{{ VC::RW }}">
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('name', __('Deal Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('phone', __('Phone'), ['class' => VC::FM_LB]) }}
                {{ Form::text('phone', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('price', __('Price'), ['class' => VC::FM_LB]) }}
                {{ Form::number('price', 0, ['class' => VC::FM_CT, 'min' => 0]) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('clients', __('Clients'), ['class' => VC::FM_LB]) }}
                {{ Form::select('clients', Utility::isFilled($clients) ? $clients : [__('No clients available.' ?? [])], null, [
                    'class'    => VC::FM_CT . ' select2',
                    'multiple' => '',
                    'id'       => 'choices-multiple1',
                    'required' => 'required'
                ]) }}
                @if(Utility::isFilled($clients) && strtolower($user?->{UsersConstants::COL_TP} ?? '') == 'owner')
                    @php
                        try {
                            $clientsIndexRoute = Route::has(ViewsConstants::CLT.'.index')
                                ? route(ViewsConstants::CLT.'.index')
                                : '#';
                            $clientsIndexGuardMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::DL,
                                'clients_index_route_unavailable'
                            ) ?? 'Clients index route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('deals/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::TXT_MT }} {{ VC::TXSM }}">
                        {{ __('Please create new clients') }} <a
                            id="clients-index-link"
                            href="{{ $clientsIndexRoute }}"
                            data-url="{{ $clientsIndexRoute }}"
                            data-guard-msg="{{ base64_encode($clientsIndexGuardMsg) }}"
                        >{{ __('here') }}</a>.
                    </div>
                    <script defer src="{{ asset('assets/js/routes/deals/storeIndex.js') }}"></script>
                @endif
            </div>
        </div>
    </div>
    <div class="{{ VC::DFL }} modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/deals/storeCreate.js') }}"></script>
{{ Form::close() }}
