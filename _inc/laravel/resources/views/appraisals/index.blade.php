@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Model\Utility;
    use Illuminate\Support\Facades\{Gate, Route};
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Appraisal')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Appraisal')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/appraisals/lang/index.js') }}"></script>
    <script defer>
        (() => {
          const errFb = '# ERROR';
          const dataClientLocalized = 'data-client-localized';
          const dataGuardMsg = 'data-guard-msg';
          const langSessionKey = 'erp-np-lang';
          const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
              msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
              let lang = (window.sessionStorage.getItem(langSessionKey) ?? document.documentElement.lang ?? 'en')
                .toLowerCase()
                .replace(/_/g, '-');
              lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
              msg = window.translations?.[lang]?.[msgKey] ??
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
                const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                  .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
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
                    toast.addEventListener('click', () => body.textContent = message);
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
            ms.forEach(m => m.removedNodes.forEach(n => {
              if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
              }
            }));
          }).observe(document.body, { childList: true, subtree: true });
          document.addEventListener('DOMContentLoaded', () => {
            const empEl = document.getElementById('employee');
            if (empEl && empEl.dataset.listenerAttached !== 'true') {
              empEl.dataset.listenerAttached = 'true';
              const obs1 = new MutationObserver((ms, obs) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                  if (n === empEl) {
                    empEl.removeEventListener('change', onEmployeeChange);
                    obs.disconnect();
                  }
                }));
              });
              obs1.observe(document.body, { childList: true, subtree: true });
              empEl.addEventListener('change', onEmployeeChange);
            }
            const branchEl = document.getElementById('branch');
            if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
              branchEl.dataset.listenerAttached = 'true';
              const obs2 = new MutationObserver((ms, obs) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                  if (n === branchEl) {
                    branchEl.removeEventListener('change', onBranchChange);
                    obs.disconnect();
                  }
                }));
              });
              obs2.observe(document.body, { childList: true, subtree: true });
              branchEl.addEventListener('change', onBranchChange);
            }
            if (empEl) onEmployeeChange.call(empEl);
          });
          function onEmployeeChange() {
            loadStars(this.value);
          }
          function onBranchChange() {
            loadEmployees(this.value);
          }
          function loadStars(empId) {
            try {
              const url = '{{ route("empByStar") }}';
              if (!url || url === '#') {
                errorMessage = getLocalizedMessage('emp_by_star_route_unavailable', document.getElementById('employee') || document.body);
                return;
              }
              $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: {
                  employee: empId ?? '',
                  _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
              })
              .done(data => {
                const stEl = document.getElementById('stares');
                if (stEl) stEl.innerHTML = data.html ?? '';
              })
              .fail(() => {
                errorMessage = getLocalizedMessage('emp_by_star_unavailable', document.getElementById('employee') || document.body);
              });
            } catch {
              errorMessage = getLocalizedMessage('emp_by_star_unavailable', document.getElementById('employee') || document.body);
            }
          }
          function loadEmployees(branchId) {
            try {
              const url = '{{ route("getemployee") }}';
              if (!url || url === '#') {
                errorMessage = getLocalizedMessage('getemployee_route_unavailable', document.getElementById('branch') || document.body);
                return;
              }
              $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: {
                  branch_id: branchId ?? '',
                  _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
              })
              .done(data => {
                const empEl = document.getElementById('employee');
                if (!empEl) return;
                empEl.innerHTML = '<option value="">{{ __("Select Employee") }}</option>';
                (data.employee || []).forEach(val => {
                  const o = document.createElement('option');
                  o.value = val.id;
                  o.textContent = val.name;
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
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create appraisal')
            @php
                $createAppraisalRoute = Route::has(ViewsConstants::APR.'.create')
                    ? route(ViewsConstants::APR.'.create')
                    : Route::has(Str::kebab(ViewsConstants::APR.'.create'))
                        ? route(Str::kebab(ViewsConstants::APR.'.create'))
                        : '#';
                $createAppraisalId = 'appraisal-create-link';
                $createAppraisalMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::APR,
                    'appraisal_create_route_unavailable'
                ) ?? 'Create Appraisal route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $createAppraisalId }}"
                href="#"
                data-size="lg"
                data-url="{{ $createAppraisalRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createAppraisalMsg }}"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/appraisals/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::C12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Target Rating') }}</th>
                                    <th>{{ __('Overall Rating') }}</th>
                                    <th>{{ __('Appraisal Date') }}</th>
                                    @if(Gate::check('edit appraisal')||Gate::check('delete appraisal')||Gate::check('show appraisal'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($appraisals as $appraisal)
                                    @php
                                        $designation = $appraisal->employees->designation->id ?? 0;
                                        $targetRating = Utility::getTargetrating($designation,$competencyCount);
                                        $ratingData = json_decode($appraisal->rating,true) ?? [];
                                        $overallrating = $ratingData ? array_sum($ratingData)/count($ratingData) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $appraisal->branches->name ?? __('Failed to fetch branch name') }}</td>
                                        <td>{{ $appraisal->employees->department->name ?? __('Failed to fetch department name') }}</td>
                                        <td>{{ $appraisal->employees->designation->name ?? __('Failed to fetch designation name') }}</td>
                                        <td>{{ $appraisal->employees->name ?? __('Failed to fetch employee name') }}</td>
                                        <td>
                                            @for($i=1;$i<=5;$i++)
                                                @if($targetRating < $i)
                                                    @if(is_float($targetRating) && round($targetRating)==$i)
                                                        <i class="text-warning fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="fas fa-star"></i>
                                                    @endif
                                                @else
                                                    <i class="text-warning fas fa-star"></i>
                                                @endif
                                            @endfor
                                            <span class="theme-text-color">({{ number_format($targetRating,1) }})</span>
                                        </td>
                                        <td>
                                            @for($i=1;$i<=5;$i++)
                                                @if($overallrating < $i)
                                                    @if(is_float($overallrating) && round($overallrating)==$i)
                                                        <i class="text-warning fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="fas fa-star"></i>
                                                    @endif
                                                @else
                                                    <i class="text-warning fas fa-star"></i>
                                                @endif
                                            @endfor
                                            <span class="theme-text-color">({{ number_format($overallrating,1) }})</span>
                                        </td>
                                        <td>{{ $appraisal->appraisal_date }}</td>
                                        @if(Gate::check('edit appraisal')||Gate::check('delete appraisal')||Gate::check('show appraisal'))
                                          <td>
                                            @can('show appraisal')
                                                @php
                                                    $lang = Utility::fetchUserLang();
                                                    $showRoute = Route::has(ViewsConstants::APR.'.show')
                                                        ? route(ViewsConstants::APR.'.show', $appraisal->id)
                                                        : '#';
                                                    $showId = 'appraisal-show-' . $appraisal->id . '-link';
                                                    $showMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::APR,
                                                        'appraisal_show_route_unavailable'
                                                    ) ?? 'Appraisal show route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_INF }}">
                                                    <a
                                                        id="{{ $showId }}"
                                                        href="#"
                                                        data-url="{{ $showRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $showMsg }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Appraisal Detail') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_EYE_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('edit appraisal')
                                                @php
                                                    $editRoute = Route::has(ViewsConstants::APR.'.edit')
                                                        ? route(ViewsConstants::APR.'.edit', $appraisal->id)
                                                        : '#';
                                                    $editId = 'appraisal-edit-' . $appraisal->id . '-link';
                                                    $editMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::APR,
                                                        'appraisal_edit_route_unavailable'
                                                    ) ?? 'Appraisal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $editId }}"
                                                        href="#"
                                                        data-url="{{ $editRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Appraisal') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete appraisal')
                                                @php
                                                    $deleteRoute = Route::has(ViewsConstants::APR.'.destroy')
                                                        ? route(ViewsConstants::APR.'.destroy', $appraisal->id)
                                                        : '#';
                                                    $deleteId = 'appraisal-delete-' . $appraisal->id . '-link';
                                                    $deleteMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::APR,
                                                        'appraisal_destroy_route_unavailable'
                                                    ) ?? 'Appraisal delete route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method' => 'DELETE',
                                                        'route'  => [$deleteRoute],
                                                        'id'     => 'delete-form-'.$appraisal->id
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteId }}"
                                                            href="#"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                            data-url="{{ $deleteRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $deleteMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone.') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{$appraisal->id}}').submit();"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                          </td>
                                          @push(StacksConstants::ADM_SCR_PG)
                                              <script defer>
                                                  (() => {
                                                      const ids = [
                                                          '{{ $showId ?? '' }}',
                                                          '{{ $editId ?? '' }}',
                                                          '{{ $deleteId ?? '' }}'
                                                      ].filter(Boolean);
                                                      const flagAttr = 'data-listener-active';
                                                      ids.forEach(id => {
                                                          const el = document.getElementById(id);
                                                          if (!el || el.getAttribute(flagAttr) === 'true') return;
                                                          el.setAttribute(flagAttr, 'true');
                                                          el.addEventListener('click', event => {
                                                              try {
                                                                  const url = el.getAttribute('data-url');
                                                                  const href = el.href;
                                                                  if ((!url || url === '#') && (!href || href === '#')) {
                                                                      event.preventDefault();
                                                                      const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                      let container = document.getElementById('toast-container');
                                                                      if (!container) {
                                                                          container = document.createElement('div');
                                                                          container.id = 'toast-container';
                                                                          document.body.appendChild(container);
                                                                      }
                                                                      if (bootstrapLink && window.bootstrap) {
                                                                          const toastEl = document.createElement('div');
                                                                          toastEl.className = 'toast';
                                                                          toastEl.setAttribute('role', 'alert');
                                                                          toastEl.setAttribute('aria-live', 'assertive');
                                                                          toastEl.setAttribute('aria-atomic', 'true');
                                                                          const body = document.createElement('div');
                                                                          body.className = 'toast-body';
                                                                          body.textContent = msg;
                                                                          toastEl.appendChild(body);
                                                                          container.appendChild(toastEl);
                                                                          bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                      } else {
                                                                          alert(msg);
                                                                      }
                                                                      el.setAttribute('data-failed-route', 'true');
                                                                  }
                                                              } catch {}
                                                          });
                                                          const observer = new MutationObserver(() => {
                                                              if (!document.getElementById(id)) observer.disconnect();
                                                          });
                                                          observer.observe(document.body, { childList: true, subtree: true });
                                                      });
                                                  })();
                                              </script>
                                          @endpush
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection