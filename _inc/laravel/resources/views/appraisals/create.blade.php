@php
$lang ??= 'en';
	$storeRoute ??= '#';
	$formId ??= 'appraisal-store-form';
	$storeMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeRoute = Route::has(ViewsConstants::APR) ? (route(ViewsConstants::APR) ?? '#') : '#';
		$storeMsg = Utility::fetchLinkMessage($lang, ViewsConstants::APR, 'appraisal_store_route_unavailable')
			?? 'Appraisal store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in appraisals/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in appraisals/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in appraisals/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open(['url'=> $storeRoute,'method'=>'post']) }}
    <div class="modal-body">
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('branch',__('Branch*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <select name="branch" id="branch" required class="{{ ViewClassNamesConstants::FM_CT_SL }}">
                        <option selected disabled value="0">{{ __('Select Branch') }}</option>
                        @foreach($branches as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }} {{ ViewClassNamesConstants::MT4 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('employee',__('Employee*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <div class="employee_div">
                        <select name="employee" id="employee" required class="{{ ViewClassNamesConstants::FM_CT_SL }}"></select>
                    </div>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('appraisal_date',__('Select Month*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::month('appraisal_date','',['class'=>ViewClassNamesConstants::FM_CT,'autocomplete'=>'off','required']) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('remark',__('Remarks'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::textarea('remark',null,['class'=>ViewClassNamesConstants::FM_CT,'rows'=>3,'placeholder'=>'Enter remark']) }}
                </div>
            </div>
        </div>
        <div class="{{ ViewClassNamesConstants::RW }}" id="stares"></div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
    <script async src="{{ asset('assets/js/routes/appraisals/lang/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/appraisals/store.js') }}"></script>
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
            const empEl = document.getElementById('employee');
            if (empEl && empEl.dataset.listenerAttached !== 'true') {
              empEl.dataset.listenerAttached = 'true';
              new MutationObserver((ms, obs) => {
                for (const m of ms) {
                  for (const n of m.removedNodes) {
                    if (n === empEl) {
                      empEl.removeEventListener('change', onEmployeeChange);
                      obs.disconnect();
                    }
                  }
                }
              }).observe(document.body, { childList: true, subtree: true });
              empEl.addEventListener('change', onEmployeeChange);
            }
            const branchEl = document.getElementById('branch');
            if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
              branchEl.dataset.listenerAttached = 'true';
              new MutationObserver((ms, obs) => {
                for (const m of ms) {
                  for (const n of m.removedNodes) {
                    if (n === branchEl) {
                      branchEl.removeEventListener('change', onBranchChange);
                      obs.disconnect();
                    }
                  }
                }
              }).observe(document.body, { childList: true, subtree: true });
              branchEl.addEventListener('change', onBranchChange);
            }
          });
          function onEmployeeChange() {
            const el = this;
            try {
              const empId = el.value ?? '';
              $.ajax({
                url: '{{ route("appraisals.employees.star") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                  employee: empId,
                  _token:
                    document
                      .querySelector('meta[name="csrf-token"]')
                      ?.getAttribute('content') ?? ''
                }
              })
                .done(data => {
                  const target = document.getElementById('stares');
                  if (target) target.innerHTML = data.html ?? '';
                })
                .fail(() => {
                  errorMessage = getLocalizedMessage('emp_by_star_unavailable', el);
                });
            } catch {
              errorMessage = getLocalizedMessage('emp_by_star_unavailable', el);
            }
          }
          function onBranchChange() {
            const el = this;
            try {
              const branchId = el.value ?? '';
              $.ajax({
                url: '{{ route("getemployee") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                  branch_id: branchId,
                  _token:
                    document
                      .querySelector('meta[name="csrf-token"]')
                      ?.getAttribute('content') ?? ''
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
                  errorMessage = getLocalizedMessage('employee_fetch_unavailable', el);
                });
            } catch {
              errorMessage = getLocalizedMessage('employee_fetch_unavailable', el);
            }
          }
        })();
    </script>
{{ Form::close() }}
