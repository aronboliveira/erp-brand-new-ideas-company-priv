@php
$lang ??= 'en';
	$promotionId ??= null;
	$updateBaseName ??= '';
	$updateKebabName ??= '';
	$updateResolvedName ??= null;
	$updateParams ??= ['#'];
	$updateUrl ??= '#';
	$formId ??= 'edit_promotion';
	$formGuardMsg ??= '';
	$plan ??= null;
	$aiGenBase ??= 'generate';
	$aiGenKebab ??= '';
	$aiGenResolved ??= null;
	$aiGenParams ??= ['promotion'];
	$aiGenUrl ??= '#';
	$aiLinkId ??= 'promotion-ai-generate-link';
	$aiGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$promotionId = isset($promotion) && !empty(data_get($promotion, 'id')) ? data_get($promotion, 'id') : null;
		$updateBaseName = VW::PRM . '.update';
		$updateKebabName = Str::kebab($updateBaseName);
		$updateResolvedName = Route::has($updateBaseName) ? $updateBaseName : (Route::has($updateKebabName) ? $updateKebabName : null);
		$updateParams = $promotionId ? [$promotionId] : ['#'];
		$updateUrl = ($updateResolvedName && $promotionId) ? (route($updateResolvedName, $updateParams) ?? '#') : '#';
		$formGuardMsg = Utility::fetchLinkMessage($lang, VW::PRM, 'update_promotion_unavailable')
			?? 'Update promotion route is unavailable. Please contact technical support or your domain administrator.';
		$plan = Utility::getChatGPTSettings();
		$aiGenKebab = Str::kebab($aiGenBase);
		$aiGenResolved = Route::has($aiGenBase) ? $aiGenBase : (Route::has($aiGenKebab) ? $aiGenKebab : null);
		$aiGenUrl = $aiGenResolved ? (route($aiGenResolved, $aiGenParams) ?? '#') : '#';
		$aiGuardMsg = Utility::fetchLinkMessage($lang, VW::PRM, 'generate_promotion_unavailable')
			?? 'Generate promotion content route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in promotions/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in promotions/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in promotions/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{!! Form::model($promotion, [
    'url'    => $updateUrl,
    'method' => 'PUT',
    'id'     => $formId,
    'data-guard-msg' => $formGuardMsg,
]) !!}
    <div class="modal-body">
        @if ($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::TX_END }}">
                <a href="{{ $aiGenUrl }}"
                   id="{{ $aiLinkId }}"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $aiGenUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ base64_encode($aiGuardMsg) }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('designation_id', __('Designation'), ['class' => VC::FM_LB]) }}
                {{ Form::select('designation_id', $designations, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('promotion_title', __('Promotion Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('promotion_title', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('promotion_date', __('Promotion Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('promotion_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
@push(StacksConstants::ADM_SCR_PG)
    <script>
        (() => {
            try {
                const formEl = document.getElementById('{{ $formId }}');
                if (formEl && !(formEl.hasAttribute('data-submit-listener') && formEl.getAttribute('data-submit-listener') === 'true')) {
                    formEl.setAttribute('data-submit-listener', 'true');
                    formEl.addEventListener('submit', function (e) {
                        try {
                            const action = formEl.getAttribute('action') || '#';
                            if (action !== '#') return;
                            e.preventDefault();
                            const msg = formEl.getAttribute('data-guard-msg') || 'Update promotion route is unavailable. Please contact technical support or your domain administrator.';
                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                            formEl.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    }, { passive: false });
                }

                const ai = document.getElementById('{{ $aiLinkId }}');
                if (ai && !(ai.hasAttribute('data-ai-listener') && ai.getAttribute('data-ai-listener') === 'true')) {
                    ai.setAttribute('data-ai-listener', 'true');
                    ai.addEventListener('click', function (e) {
                        try {
                            const href = ai.getAttribute('href') || '#';
                            const url = ai.getAttribute('data-url') || href || '#';
                            if (href !== '#' || url !== '#') return;
                            e.preventDefault();
                            const msg = ai.getAttribute('data-guard-msg') || 'Generate promotion content route is unavailable. Please contact technical support or your domain administrator.';
                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                            ai.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    }, { passive: false });
                }
            } catch (error) {}
        })();
    </script>
@endpush
