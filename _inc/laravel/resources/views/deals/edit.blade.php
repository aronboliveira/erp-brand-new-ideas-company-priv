@php
$lang ??= 'en';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in deals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in deals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in deals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($deal) && isset($deal->id))
    @php
		$routeKey ??= '';
		$kebabRouteKey ??= '';
		$updateRouteName ??= null;
		$updateRouteArray ??= ['#'];
		$updateRouteUrl ??= '#';
		$updateGuardMsg ??= '';
		try {
			$routeKey = ViewsConstants::DL . '.update';
			$kebabRouteKey = Str::kebab($routeKey);
			$updateRouteName = Route::has($routeKey)
				? $routeKey
				: (Route::has($kebabRouteKey)
					? $kebabRouteKey
					: null);
			$updateRouteArray = $updateRouteName
				? [$updateRouteName, $deal->id]
				: ['#'];
			$updateRouteUrl = $updateRouteName
				? (route($updateRouteName, $deal->id) ?? '#')
				: '#';
			$updateGuardMsg = Utility::fetchLinkMessage(
				$lang,
				ViewsConstants::DL,
				'deal_update_route_unavailable'
			) ?? 'Update route is unavailable. Please contact technical support or your domain administrator.';
		} catch (\Error $e) {
			Log::error('Error in deals/edit.blade.php route @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Exception $e) {
			Log::error('Exception in deals/edit.blade.php route @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Throwable $e) {
			Log::error('Throwable in deals/edit.blade.php route @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp
    {!! Form::model($deal, [
        'route'          => $updateRouteArray,
        'method'         => 'PUT',
        'id'             => 'update-deal-form-' . $deal->id,
        'data-url'       => $updateRouteUrl,
        'data-guard-msg' => $updateGuardMsg
    ]) !!}
    <div class="modal-body">
        @php
            $plan = Utility::getChatGPTSettings();
@endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
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
                        \Log::error('deals/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('name', __('Deal Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('phone', __('Phone'), ['class' => VC::FM_LB]) }}
                {{ Form::text('phone', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('price', __('Price'), ['class' => VC::FM_LB]) }}
                {{ Form::number('price', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
                {{ Form::select('pipeline_id', Utility::isFilled($pipelines) ? $pipelines : [__('No pipelines available')], null, ['class' => VC::FM_CT, 'required' => 'required'] ?? []) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('stage_id', __('Stage'), ['class' => VC::FM_LB]) }}
                {{ Form::select('stage_id', ['' => __('Select Stage')], null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('sources', __('Sources'), ['class' => VC::FM_LB]) }}
                {{ Form::select('sources[]', Utility::isFilled($sources) ? $sources : [__('No sources available')], null, ['class' => VC::FM_CT . ' select2', 'multiple' => '', 'id' => 'choices-multiple3', 'required' => 'required'] ?? []) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('products', __('Products'), ['class' => VC::FM_LB]) }}
                {{ Form::select('products[]', Utility::isFilled($products) ? $products : [__('No products available')], null, ['class' => VC::FM_CT . ' select2', 'multiple' => '', 'id' => 'choices-multiple4', 'required' => 'required'] ?? []) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('notes', __('Notes'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('notes', null, ['class' => 'summernote-simple']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>
    <script async src="{{ asset('assets/js/routes/deals/lang/edit.js') }}"></script>
    <script defer>
        (() => {
            const ERR_FB = '# ERROR';
            const DS_CLIENT = 'data-client-localized';
            const DS_GUARD  = 'data-guard-msg';
            const LANG_KEY  = 'erp-np-lang';
            let errorMessage = '';

            const getMsg = (key, el) => {
                let msg = ERR_FB;
                if (el.getAttribute(DS_CLIENT) === 'true') {
                msg = el.getAttribute(DS_GUARD) || msg;
                } else {
                let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                    .toLowerCase().replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                msg = translations?.[lang]?.[key]
                    ?? el.getAttribute(DS_GUARD)
                    ?? translations?.['en']?.[key]
                    ?? msg;
                if (msg !== ERR_FB) {
                    el.setAttribute(DS_GUARD, msg);
                    el.setAttribute(DS_CLIENT, 'true');
                }
                }
                return msg;
            };

            const showError = message => {
                (window.RouteGuard?.showToast || (m => alert(m)))(message);
            };

            const onUp = () => {
                if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onUp);
            new MutationObserver((m, obs) => {
                m.forEach(mut => Array.from(mut.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                    document.removeEventListener('pointerup', onUp);
                    obs.disconnect();
                }
                }));
            }).observe(document.body,{ childList:true, subtree:true });

            document.addEventListener('DOMContentLoaded', () => {
                const stageId = '{{ $deal->stage_id }}';
                const pipelineSelect = document.querySelector('#commonModal select[name=pipeline_id]');
                const stageSelect    = document.getElementById('stage_id');

                if (pipelineSelect) {
                pipelineSelect.addEventListener('change', () => {
                    const pid = pipelineSelect.value ?? '';
                    fetch('{{ route("stages.json") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ pipeline_id: pid })
                    })
                    .then(res => res.json())
                    .then(data => {
                    if (!stageSelect) return;
                    stageSelect.innerHTML = '';
                    const opt0 = document.createElement('option');
                    opt0.value = '';
                    opt0.selected = true;
                    opt0.textContent = '{{ __("Select Stage") }}';
                    stageSelect.appendChild(opt0);
                    Object.entries(data).forEach(([key, val]) => {
                        const o = document.createElement('option');
                        o.value = key;
                        o.textContent = val;
                        if (key == stageId) o.selected = true;
                        stageSelect.appendChild(o);
                    });
                    $(stageSelect).select2({ placeholder: "{{ __('Select Stage') }}" });
                    })
                    .catch(() => {
                    errorMessage = getMsg('stages_fetch_unavailable', pipelineSelect);
                    });
                });
                pipelineSelect.dispatchEvent(new Event('change'));
                }
            });
        })();
    </script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALT_WRN }}">
        {{ __('Failed to fetch deal data.') }}
    </div>
@endif
