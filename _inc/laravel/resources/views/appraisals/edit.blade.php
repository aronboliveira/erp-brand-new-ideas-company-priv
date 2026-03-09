@php
$lang ??= 'en';
	$updateRoute ??= '#';
	$formId ??= 'appraisal-update-form';
	$updateMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$updateRoute = (Route::has(VW::APR . '.update') && isset($appraisal?->id))
			? (route(VW::APR . '.update', $appraisal->id) ?? '#')
			: '#';
		$updateMsg = Utility::fetchLinkMessage($lang, VW::APR, 'appraisal_update_route_unavailable')
			?? 'Appraisal update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in appraisals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in appraisals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in appraisals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!empty($appraisal) && isset($appraisal?->id))
  {{ Form::model($appraisal, [
      'route'             => [$updateRoute],
      'method'            => 'PUT',
      'id'                => $formId,
      'data-url'          => $updateRoute,
      'data-sv-localized' => 'true',
      'data-guard-msg'    => $updateMsg,
  ]) }}
      <div class="modal-body">
          <div class="{{ VC::RW }}">
              <div class="{{ VC::C12 }}">
                  <div class="{{ VC::FM_G }}">
                      {{ Form::label('branch',__('Branch*'),['class'=>VC::FM_LB]) }}
                      <select name="branch" id="branch" required class="{{ VC::FM_CT_SL }}">
                          @foreach($branches as $value)
                              <option value="{{ $value->id }}" @if($appraisal->branch==$value->id) selected @endif>{{ $value->name }}</option>
                          @endforeach
                      </select>
                  </div>
              </div>
              <div class="{{ VC::CM6 }}">
                  <div class="{{ VC::FM_G }}">
                      {{ Form::label('employees',__('Employee*'),['class'=>VC::FM_LB]) }}
                      <div class="employee_div">
                          <select name="employee" id="employee" required class="{{ VC::FM_CT_SL }}"></select>
                      </div>
                  </div>
              </div>
              <div class="{{ VC::CM6 }}">
                  <div class="{{ VC::FM_G }}">
                      {{ Form::label('appraisal_date',__('Select Month*'),['class'=>VC::FM_LB]) }}
                      {{ Form::text('appraisal_date',null,['class'=>VC::FM_CT_SL.' d_filter','required']) }}
                  </div>
              </div>
              <div class="{{ VC::C12 }}">
                  <div class="{{ VC::FM_G }}">
                      {{ Form::label('remark',__('Remarks'),['class'=>VC::FM_LB]) }}
                      {{ Form::textarea('remark',null,['class'=>VC::FM_CT,'rows'=>3]) }}
                  </div>
              </div>
          </div>
          <div class="{{ VC::RW }}" id="stares"></div>
      </div>
      <div class="modal-footer">
          <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
          <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
      </div>
      <script async src="{{ asset('assets/js/routes/appraisals/lang/edit.js') }}"></script>
      <script defer src="{{ asset('assets/js/routes/appraisals/edit.js') }}"></script>
      <script defer>
          (() => {
            const errFb = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg = 'data-guard-msg';
            const langSessionKey = 'erp-np-lang';

            const getLocalizedMessage = (msgKey, el) => {
              let msg = errFb;
              if (
                el.getAttribute('data-sv-localized') === 'true' ||
                el.getAttribute(dataClientLocalized) === 'true'
              ) {
                msg = el.getAttribute(dataGuardMsg) ?? errFb;
              } else {
                let lang = (
                  window.sessionStorage.getItem(langSessionKey) ??
                  document.documentElement.lang ??
                  'en'
                )
                  .toLowerCase()
                  .replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                msg =
                  window.translations?.[lang]?.[msgKey] ??
                  el.getAttribute(dataGuardMsg) ??
                  window.translations?.['en']?.[msgKey] ??
                  errFb;
                if (msg !== errFb) {
                  el.setAttribute(dataGuardMsg, msg);
                  el.setAttribute(dataClientLocalized, 'true');
                }
              }
              return msg;
            };

            const showError = message => {
              try {
                let container = document.querySelector('#bootstrap-toast-container');
                if (!container) {
                  const hasBs =
                    Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                      .some(l => /bootstrap/i.test(l.href)) &&
                    window.bootstrap?.Toast;
                  if (hasBs) {
                    container = document.createElement('div');
                    container.id = 'bootstrap-toast-container';
                    container.setAttribute('aria-live', 'polite');
                    container.setAttribute('aria-atomic', 'true');
                    document.body.appendChild(container);
                  }
                }
                if (container && window.bootstrap.Toast) {
                  let toast = container.querySelector('.toast');
                  if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    toast.appendChild(body);
                    container.appendChild(toast);
                    if (toast.getAttribute('data-click-listener') !== 'true') {
                      toast.addEventListener('click', () => (body.textContent = message));
                      toast.setAttribute('data-click-listener', 'true');
                    }
                  }
                  toast.querySelector('.toast-body').textContent = message;
                  new bootstrap.Toast(toast).show();
                } else {
                  alert(message);
                }
              } catch {
                alert(message);
              }
            };

            let errorMessage = '';
            const onErrorPointerUp = () => {
              if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
              }
            };
            document.addEventListener('pointerup', onErrorPointerUp);
            new MutationObserver((ms, obs) => {
              for (const m of ms) {
                for (const n of m.removedNodes) {
                  if (n === document.documentElement) {
                    document.removeEventListener('pointerup', onErrorPointerUp);
                    obs.disconnect();
                  }
                }
              }
            }).observe(document.body, { childList: true, subtree: true });

            document.addEventListener('DOMContentLoaded', () => {
              const branchEl = document.getElementById('branch');
              const empEl    = document.getElementById('employee');
              const stEl     = document.getElementById('stares');
              const branchIds   = '{{ $appraisal->branch }}';
              const employeeId  = '{{ $appraisal->employee }}';
              const appraisalId = '{{ $appraisal->id }}';

              if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
                branchEl.dataset.listenerAttached = 'true';
                branchEl.addEventListener('change', onBranchChange);
                new MutationObserver((ms, obs) => {
                  ms.forEach(m => m.removedNodes.forEach(n => {
                    if (n === branchEl) {
                      branchEl.removeEventListener('change', onBranchChange);
                      obs.disconnect();
                    }
                  }));
                }).observe(document.body, { childList: true, subtree: true });
              }

              if (empEl && empEl.dataset.listenerAttached !== 'true') {
                empEl.dataset.listenerAttached = 'true';
                empEl.addEventListener('change', onEmployeeChange);
                new MutationObserver((ms, obs) => {
                  ms.forEach(m => m.removedNodes.forEach(n => {
                    if (n === empEl) {
                      empEl.removeEventListener('change', onEmployeeChange);
                      obs.disconnect();
                    }
                  }));
                }).observe(document.body, { childList: true, subtree: true });
              }

              // initial load
              onBranchChange.call({ value: branchIds });
              loadStars(employeeId, appraisalId);
            });

            function onBranchChange() {
              const branchId = this.value ?? '';
              try {
                const url = '{{ route(VW::APR.".get.employee") }}';
                if (!url) throw 0;
                $.ajax({
                  url,
                  type: 'POST',
                  dataType: 'json',
                  data: {
                    branch_id: branchId,
                    _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                  }
                })
                .done(data => {
                  const empEl = document.getElementById('employee');
                  if (!empEl) return;
                  empEl.innerHTML = '<option value="">{{ __("Select Employee") }}</option>';
                  (data.employee || []).forEach(({ id, name }) => {
                    const o = document.createElement('option');
                    o.value = id;
                    o.textContent = name;
                    empEl.appendChild(o);
                  });
                })
                .fail(() => {
                  errorMessage = getLocalizedMessage('employee_fetch_unavailable', document.getElementById('branch') || document.body);
                });
              } catch {
                errorMessage = getLocalizedMessage('employee_fetch_unavailable', document.getElementById('branch') || document.body);
              }
            }

            function onEmployeeChange() {
              const empId = this.value ?? '';
              loadStars(empId);
            }

            function loadStars(empId, appId = null) {
              try {
                const routeName = appId ? '{{ route(VW::APR . '.' . VW::EMP . ".star1") }}' : '{{ route(VW::APR . '.' . VW::EMP . ".star") }}';
                const data = { employee: empId, _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '' };
                if (appId) data.appraisal = appId;
                $.ajax({
                  url: routeName,
                  type: 'POST',
                  dataType: 'json',
                  data
                })
                .done(resp => {
                  const stEl = document.getElementById('stares');
                  if (stEl) stEl.innerHTML = resp.html ?? '';
                })
                .fail(() => {
                  errorMessage = getLocalizedMessage(appId ? 'emp_by_star1_unavailable' : 'emp_by_star_unavailable', document.getElementById('employee') || document.body);
                });
              } catch {
                errorMessage = getLocalizedMessage(appId ? 'emp_by_star1_unavailable' : 'emp_by_star_unavailable', document.getElementById('employee') || document.body);
              }
            }
          })();
      </script>
  {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ C::ALERT }} {{ C::ALERT_DANGER }}">
                    <h4 class="{{ VC::TX_DNG }}">{{ __('No Appraisal found') }}</h4>
                    <p>{{ __('The appraisal data is invalid or not found. Please refresh the page and try again.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
