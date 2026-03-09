@php
    try {
$currency = $admin_payment_setting['currency'] ?? '$';

        $quotaFields = [
            PlansConstants::COL_MAX_U  => __('Users'),
            PlansConstants::COL_MAX_CR => __('Customers'),
            PlansConstants::COL_MAX_V  => __('Vendors'),
        ];
    } catch (\Throwable $e) {
        \Log::error('users/plan — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="modal-body">
  <div class="{{ VC::CD }}">
    <div class="{{ VC::CD_BD_TB_BD }}">
      <div class="{{ VC::TB_RSP }}">
        <table class="{{ VC::TB }} datatable">
          @forelse((($plans ?? null) instanceof Collection || is_array($plans ?? null)) ? $plans : [] as $plan)
            <tr>
              <td>
                <h6 class="{{ VC::MB0 }}">
                  {{ data_get($plan, PlansConstants::COL_NM) ?: __('No plan name available') }}
                  ({{ is_numeric(data_get($plan, PlansConstants::COL_PC)) ? $currency.number_format((float) data_get($plan, PlansConstants::COL_PC, 0), 0) : __('No price available') }})
                  / {{ data_get($plan, PlansConstants::COL_DUR) ?: __('No duration available') }}
                </h6>
              </td>
              @foreach ($quotaFields as $field => $label)
                <td>
                  {{ $label }} :
                  {{
                    (data_get($plan,$field) === null || data_get($plan,$field) === '')
                      ? __('No '.$label.' available')
                      : ((int) data_get($plan,$field) === -1 ? __('Unlimited') : data_get($plan,$field))
                  }}
                </td>
              @endforeach
              <td class="{{ VC::AL_IT_CT }}">
                @if ((string)($user?->{UsersConstants::COL_PL} ?? '') === (string) data_get($plan,'id',''))
                  <span class="{{ VC::BT_SM }} {{ VC::BT_PRM }} my-auto">
                    <i class="ti ti-check"></i>
                  </span>
                @else
                  @php
                      try {
                          $plnActiveBase = ViewsConstants::PLN.'.active';
                          $plnActiveKebab = Str::kebab($plnActiveBase);
                          $userIdValue = (string) ($user?->id ?? '');
                          $planIdValue = (string) data_get($plan,'id','');
                          $plnActiveResolved = Route::has($plnActiveBase) ? $plnActiveBase : (Route::has($plnActiveKebab) ? $plnActiveKebab : null);
                          $plnActiveUrl = ($plnActiveResolved && $userIdValue !== '' && $planIdValue !== '') ? route($plnActiveResolved, [$userIdValue, $planIdValue]) : '#';
                          $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                          $plnActiveGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::PLN, 'activate_plan_route_unavailable') ?? 'Activate plan route is unavailable. Please contact technical support or your domain administrator.';
                          $plnActiveAnchorId = 'plan-activate-'.($userIdValue === '' ? 'x' : $userIdValue).'-'.($planIdValue === '' ? 'y' : $planIdValue);
                      } catch (\Throwable $e) {
                          \Log::error('users/plan — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                      }
@endphp
                  <a href="{{ $plnActiveUrl }}"
                    id="{{ $plnActiveAnchorId }}"
                    class="{{ VC::BT_SM }} btn-warning my-auto"
                    title="{{ __('Click to Upgrade Plan') }}"
                    data-url="{{ $plnActiveUrl }}"
                    data-guard-msg="{{ base64_encode($plnActiveGuardMsg) }}"
                    data-sv-localized="true"
                    data-bs-toggle="tooltip">
                      <i class="ti ti-shopping-cart-plus"></i>
                  </a>
                  @push(StacksConstants::ADM_SCR_PG)
                      <script defer>
                          (() => {
                              try {
                                  const el = document.getElementById('{{ $plnActiveAnchorId }}');
                                  if (!el) { return; }
                                  if (el.getAttribute('data-listener-active') === 'true') { return; }
                                  el.setAttribute('data-listener-active','true');
                                  el.addEventListener('click',(e) => {
                                      try {
                                          const href = el.getAttribute('href') ?? '#';
                                          const url = el.getAttribute('data-url') ?? href ?? '#';
                                          if (url !== '#' && href !== '#') { return; }
                                          e.preventDefault();
                                          const msg = el.getAttribute('data-guard-msg') ?? 'Activate plan route is unavailable. Please contact technical support or your domain administrator.';
                                          (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                          el.setAttribute('data-failed-route','true');
                                      } catch (err) {}
                                  });
                              } catch (err) {}
                          })();
                      </script>
                  @endpush
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ 2 + count($quotaFields) }}" class="{{ VC::TXCT_MT }}">{{ __('No plans available') }}</td>
            </tr>
          @endforelse
        </table>
      </div>
    </div>
  </div>
</div>
