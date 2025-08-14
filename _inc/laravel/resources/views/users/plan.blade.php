@php
    use App\Config\Constants\{PlansConstants, ViewsConstants};
    $currency = $admin_payment_setting['currency'] ?? '$';
    $quotaFields = [
        PlansConstants::COL_MAX_U  => __('Users'),
        PlansConstants::COL_MAX_CR => __('Customers'),
        PlansConstants::COL_MAX_V  => __('Vendors'),
    ];
@endphp
<div class="modal-body">
  <div class="card">
    <div class="card-body table-border-style">
      <div class="table-responsive">
        <table class="table datatable">
          @foreach($plans as $plan)
            <tr>
              <td>
                <h6>{{ $plan->{PlansConstants::COL_NM} }} ({{ $currency }}{{ intval($plan->{PlansConstants::COL_PC}) }}) / {{ $plan->{PlansConstants::COL_DUR} }}</h6>
              </td>
              @foreach($quotaFields as $field => $label)
                <td>{{ $label }} : {{ $plan->{$field} === -1 ? __('Unlimited') : $plan->{$field} }}</td>
              @endforeach
              <td>
                @if($user->plan =$plan->id)
                  <span class="btn btn-sm btn-primary my-auto"><i class="ti ti-check"></i></span>
                @else
                  <a href="{{ route(ViewsConstants::PLN.'.active', [$user?->id, $plan->id]) }}" class="btn btn-sm btn-warning my-auto" title="{{ __('Click to Upgrade Plan') }}">
                    <i class="ti ti-shopping-cart-plus"></i>
                  </a>
                @endif
              </td>
            </tr>
          @endforeach
        </table>
      </div>
    </div>
  </div>
</div>
