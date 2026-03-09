@php
$lang ??= 'en';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in appraisals/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in appraisals/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in appraisals/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Appraisal')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Appraisal')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/appraisals/lang/index.js') }}"></script>
    <script defer>
        (() => {
            const getMsg = (key, el) => window.RouteGuard?.getMsg?.(el, key) ?? el?.getAttribute?.('data-guard-msg') ?? '# ERROR';
            const showError = msg => window.RouteGuard?.showToast?.(msg, 'error') ?? alert(msg);
            const csrfToken = () => window.RouteGuard?.csrfToken?.() ?? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let errorMessage = '';
            const onErrorPointerUp = () => { if (errorMessage) { showError(errorMessage); errorMessage = ''; } };
            document.addEventListener('pointerup', onErrorPointerUp);
            document.addEventListener('DOMContentLoaded', () => {
                const empEl = document.getElementById('employee');
                const branchEl = document.getElementById('branch');
                if (empEl && empEl.dataset.listenerAttached !== 'true') {
                    empEl.dataset.listenerAttached = 'true';
                    empEl.addEventListener('change', function() { loadStars(this.value); });
                }
                if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
                    branchEl.dataset.listenerAttached = 'true';
                    branchEl.addEventListener('change', function() { loadEmployees(this.value); });
                }
                if (empEl) loadStars(empEl.value);
            });
            function loadStars(empId) {
                try {
                    const url = '{{ route("appraisals.employees.star") }}';
                    if (!url || url === '#') { errorMessage = getMsg('emp_by_star_route_unavailable', document.getElementById('employee')); return; }
                    $.ajax({ url, type: 'POST', dataType: 'json', data: { employee: empId ?? '', _token: csrfToken() } })
                        .done(data => { const stEl = document.getElementById('stares'); if (stEl) stEl.innerHTML = data.html ?? ''; })
                        .fail(() => { errorMessage = getMsg('emp_by_star_unavailable', document.getElementById('employee')); });
                } catch { errorMessage = getMsg('emp_by_star_unavailable', document.getElementById('employee')); }
            }
            function loadEmployees(branchId) {
                try {
                    const url = '{{ route("appraisals.get.employee") }}';
                    if (!url || url === '#') { errorMessage = getMsg('getemployee_route_unavailable', document.getElementById('branch')); return; }
                    $.ajax({ url, type: 'POST', dataType: 'json', data: { branch_id: branchId ?? '', _token: csrfToken() } })
                        .done(data => {
                            const empEl = document.getElementById('employee');
                            if (!empEl) return;
                            empEl.innerHTML = '<option value="">{{ __("Select Employee") }}</option>';
                            (data.employee || []).forEach(val => { const o = document.createElement('option'); o.value = val.id; o.textContent = val.name; empEl.appendChild(o); });
                        })
                        .fail(() => { errorMessage = getMsg('employee_fetch_unavailable', document.getElementById('branch')); });
                } catch { errorMessage = getMsg('employee_fetch_unavailable', document.getElementById('branch')); }
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create appraisal')
            @php
                try {
                    $createAppraisalRoute = Route::has(ViewsConstants::APR.'.create')
                        ? route(ViewsConstants::APR.'.create')
                        : (Route::has(Str::kebab(ViewsConstants::APR.'.create'))
                            ? route(Str::kebab(ViewsConstants::APR.'.create'))
                            : '#');
                    $createAppraisalId = 'appraisal-create-link';
                    $createAppraisalMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::APR,
                        'appraisal_create_route_unavailable'
                    ) ?? 'Create Appraisal route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('appraisals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                id="{{ $createAppraisalId }}"
                href="#"
                data-size="lg"
                data-url="{{ $createAppraisalRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ base64_encode($createAppraisalMsg) }}"
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
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
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
                                        try {
                                            $designation = $appraisal->employees->designation->id ?? 0;
                                            $targetRating = Utility::getTargetrating($designation,$competencyCount);
                                            $ratingData = json_decode($appraisal->rating,true) ?? [];
                                            $overallrating = $ratingData ? array_sum($ratingData)/count($ratingData) : 0;
                                        } catch (\Throwable $e) {
                                            \Log::error('appraisals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
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
                                                    try {
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
                                                    } catch (\Throwable $e) {
                                                        \Log::error('appraisals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_INF }}">
                                                    <a
                                                        id="{{ $showId }}"
                                                        href="#"
                                                        data-url="{{ $showRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ base64_encode($showMsg) }}"
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
                                                    try {
                                                        $editRoute = Route::has(ViewsConstants::APR.'.edit')
                                                            ? route(ViewsConstants::APR.'.edit', $appraisal->id)
                                                            : '#';
                                                        $editId = 'appraisal-edit-' . $appraisal->id . '-link';
                                                        $editMsg = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::APR,
                                                            'appraisal_edit_route_unavailable'
                                                        ) ?? 'Appraisal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('appraisals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $editId }}"
                                                        href="#"
                                                        data-url="{{ $editRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ base64_encode($editMsg) }}"
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
                                                    try {
                                                        $deleteRoute = Route::has(ViewsConstants::APR.'.destroy')
                                                            ? route(ViewsConstants::APR.'.destroy', $appraisal->id)
                                                            : '#';
                                                        $deleteId = 'appraisal-delete-' . $appraisal->id . '-link';
                                                        $deleteMsg = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::APR,
                                                            'appraisal_destroy_route_unavailable'
                                                        ) ?? 'Appraisal delete route is unavailable. Please contact technical support or your domain administrator.';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('appraisals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method' => 'DELETE',
                                                        'url'    => $deleteRoute,
                                                        'id'     => 'delete-form-'.$appraisal->id
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteId }}"
                                                            href="#"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                            data-url="{{ $deleteRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ base64_encode($deleteMsg) }}"
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
                                                                  const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                                                  if ((!url || url === '#') && (!href || href === '#')) {
                                                                      event.preventDefault();
                                                                      const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                      (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
