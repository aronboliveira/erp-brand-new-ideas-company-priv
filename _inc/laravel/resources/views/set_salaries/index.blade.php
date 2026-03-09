@php
  try {
$user = auth()->user();
      $lang = Utility::fetchUserLang($user);
  } catch (\Throwable $e) {
      \Log::error('set_salaries/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
  }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
  {{ __('Manage Employee Salary') }}
@endsection

@section(YieldingConstants::ADM_BDC)
  <li class="{{ VC::BCI }}">
    <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
       {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
      {{ __('Dashboard') }}
    </a>
  </li>
  <li class="{{ VC::BCI }}">{{ __('Employee Salary') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
  <div class="{{ VC::RW }}">
    <div class="{{ VC::CXL12 }}">
      <div class="{{ VC::CD }}">
        <div class="{{ VC::CD_BD_TB_BD }}">
          <div class="{{ VC::TB_RSP }}">
            <table class="{{ VC::TB }} datatable">
              <thead>
                <tr>
                  <th>{{ __('Employee Id') }}</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Payroll Type') }}</th>
                  <th>{{ __('Salary') }}</th>
                  <th>{{ __('Net Salary') }}</th>
                  <th width="200px">{{ __('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse((($employees ?? null) instanceof Collection || is_array($employees ?? null)) ? $employees : [] as $emp)
                  @php
                      try {
                          $id = data_get($emp,'id','0');
                          $showBase = ViewsConstants::S_SLR.'.show';
                          $showKebab = Str::kebab($showBase);
                          $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                          $showUrl = ($showResolved && $id) ? route($showResolved, $id) : '#';
                          $langLocal = $lang ?? Utility::fetchUserLang();
                          $showMsg = Utility::fetchLinkMessage($langLocal, ViewsConstants::S_SLR, 'salary_show_route_unavailable') ?? 'Salary show route is unavailable. Please contact technical support or your domain administrator.';
                          $viewBtnId = 'salary-view-'.$id;
                          $setBtnId = 'salary-set-'.$id;
                      } catch (\Throwable $e) {
                          \Log::error('set_salaries/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                      }
@endphp
                  <tr>
                      <td class="Id">
                          <a id="{{ $viewBtnId }}" href="{{ $showUrl }}" class="{{ VC::BT_OUTPM }}" data-url="{{ $showUrl }}" data-guard-msg="{{ base64_encode($showMsg) }}" data-sv-localized="true" data-bs-toggle="tooltip" title="{{ __('View') }}">
                              {{ $user?->employeeIdFormat(data_get($emp,'employee_id')) ?? __('No employee ID available') }}
                          </a>
                      </td>
                      <td>{{ data_get($emp,'name') ?: __('No name available') }}</td>
                      <td>{{ data_get($emp,'salary_type.name') ?: __('No payroll type available') }}</td>
                      <td>{{ $user?->priceFormat((float)(data_get($emp,'salary') ?? 0)) ?? __('Failed to get salary') }}</td>
                      <td>{{ (is_object($emp) && method_exists($emp,'getNetSalary') && !is_null($emp->getNetSalary())) ? ($user?->priceFormat((float)$emp->getNetSalary()) ?? __('Failed to format net salary')) : __('No net salary available') }}</td>
                      <td class="{{ VC::JCE }}">
                          <div class="{{ VC::ACT_BTN }} bg-success ms-2">
                              <a id="{{ $setBtnId }}" href="{{ $showUrl }}" class="{{ VC::BT_SM_CT }}" data-url="{{ $showUrl }}" data-guard-msg="{{ base64_encode($showMsg) }}" data-sv-localized="true" data-bs-toggle="tooltip" title="{{ __('Set Salary') }}">
                                  <i class="{{ VC::TI_EYE_WT }}"></i>
                              </a>
                          </div>
                      </td>
                  </tr>
                  @push(StacksConstants::ADM_SCR_PG)
                      <script defer>
                          (() => {
                              try {
                                  const attach = (id) => {
                                      try {
                                          const el = document.getElementById(id);
                                          if (!el) { return; }
                                          if (el.getAttribute('data-listener-active') === 'true') { return; }
                                          el.setAttribute('data-listener-active', 'true');
                                          el.addEventListener('click', (e) => {
                                              try {
                                                  const href = el.getAttribute('href') ?? '#';
                                                  const url = el.getAttribute('data-url') ?? href ?? '#';
                                                  if (url !== '#' && href !== '#') { return; }
                                                  e.preventDefault();
                                                  const msg = el.getAttribute('data-guard-msg') ?? 'Salary show route is unavailable. Please contact technical support or your domain administrator.';
                                                  (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                  el.setAttribute('data-failed-route', 'true');
                                              } catch (err) {}
                                          });
                                      } catch (err) {}
                                  };
                                  attach('{{ $viewBtnId }}');
                                  attach('{{ $setBtnId }}');
                              } catch (err) {}
                          })();
                      </script>
                  @endpush
                @empty
                  <tr>
                    <td colspan="6" class="{{ VC::TXCT_MT }}">{{ __('No employees available') }}</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/setSalaries/index.js') }}"></script>
@endpush
