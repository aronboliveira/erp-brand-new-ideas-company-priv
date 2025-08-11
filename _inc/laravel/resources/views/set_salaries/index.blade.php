@php
  use App\Config\Constants\{
    ExtendingLayoutsConstants,
    YieldingConstants,
    ViewsConstants,
    ViewClassNamesConstants as VC,
    StacksConstants
  };
  use App\Models\Utility;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;

  $user = auth()->user();
  $lang = Utility::fetchUserLang();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
  {{ __('Manage Employee Salary') }}
@endsection

@section(YieldingConstants::ADM_BDC)
  <li class="breadcrumb-item">
    <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
       {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
      {{ __('Dashboard') }}
    </a>
  </li>
  <li class="breadcrumb-item">{{ __('Employee Salary') }}</li>
@endsection

@section('content')
  <div class="{{ VC::RW }}">
    <div class="col-xl-12">
      <div class="{{ VC::CD }}">
        <div class="card-body table-border-style">
          <div class="table-responsive">
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
                @foreach($employees as $emp)
                  @php
                    $showRoute = Route::has(ViewsConstants::S_SLR . '.show')
                      ? route(ViewsConstants::S_SLR . '.show', $emp->id)
                      : (Route::has(Str::kebab(ViewsConstants::S_SLR . '.show'))
                          ? route(Str::kebab(ViewsConstants::S_SLR . '.show'), $emp->id)
                          : '#');
                    $showMsg   = Utility::fetchLinkMessage(
                      $lang,
                      ViewsConstants::S_SLR,
                      'salary_show_route_unavailable'
                    ) ?? 'Salary show route is unavailable. Please contact technical support or your domain administrator.';
                    $viewBtnId = 'salary-view-' . $emp->id;
                    $setBtnId  = 'salary-set-'  . $emp->id;
                  @endphp
                  <tr>
                    <td class="Id">
                      <a id="{{ $viewBtnId }}"
                         href="{{ $showRoute }}"
                         class="{{ VC::BT_OUTPM }}"
                         data-url="{{ $showRoute }}"
                         data-guard-msg="{{ $showMsg }}"
                         data-bs-toggle="tooltip"
                         title="{{ __('View') }}">
                        {{ $user->employeeIdFormat($emp->employee_id) }}
                      </a>
                    </td>
                    <td>{{ $emp->name }}</td>
                    <td>{{ $emp->salary_type->name ?? '-' }}</td>
                    <td>{{ $user->priceFormat($emp->salary) }}</td>
                    <td>{{ optional($emp->getNetSalary(), fn($n) => $user->priceFormat($n)) }}</td>
                    <td class="{{ VC::JCE }}">
                      <div class="{{ VC::ACT_BTN }} bg-success ms-2">
                        <a id="{{ $setBtnId }}"
                           href="{{ $showRoute }}"
                           class="{{ VC::BT_SM_CT }}"
                           data-url="{{ $showRoute }}"
                           data-guard-msg="{{ $showMsg }}"
                           data-bs-toggle="tooltip"
                           title="{{ __('Set Salary') }}">
                          <i class="{{ VC::TI_EYE_WT }}"></i>
                        </a>
                      </div>
                    </td>
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

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const attachGuard = (el) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener('click', event => {
                    try {
                        const href = el.getAttribute('href');
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
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
                    } catch (e) {}
                });
            };

            document.querySelectorAll('[id^="salary-view-"]').forEach(el => attachGuard(el));
            document.querySelectorAll('[id^="salary-set-"]').forEach(el => attachGuard(el));
        })();
    </script>
@endpush
