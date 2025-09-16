@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        LangsConstants,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD,
        PermissionsConstants as PERM,
        UsersConstants as UC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $hasFetchMsg = is_callable([Utility::class, 'fetchLinkMessage']);
    $hasGetFile = is_callable([Utility::class, 'getFile']);
    $hasGetAdminPay = is_callable([Utility::class, 'getAdminPaymentSetting']);
    $hasUserDate = $user && method_exists($user, 'dateFormat');
    $hasUserPrice = $user && method_exists($user, 'priceFormat');
    $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user: $user) : app()->getLocale();

    $dashUrl = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($hasFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null) ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $admin_payment_setting = $hasGetAdminPay ? Utility::getAdminPaymentSetting() : [];
    $currencySymbol = $admin_payment_setting['currency_symbol'] ?? '$';

    $isSuperAdmin = strtolower((string)($user->{UC::COL_TP} ?? '')) === strtolower((string) PERM::SA);

    $ordersList = [];
    if (is_array($orders ?? null) && count($orders ?? []) > 0) {
        $ordersList = $orders;
    } elseif (($orders ?? null) instanceof Collection && $orders->isNotEmpty()) {
        $ordersList = $orders;
    }

    $fmtDate = function ($v, $fb) use ($hasUserDate, $user) {
        if ($v && $hasUserDate) {
            try { return $user->dateFormat($v) ?? $fb; } catch (\Throwable $e) { return $fb; }
        }
        if ($v instanceof \Carbon\Carbon) {
            try { return $v->format('d M Y'); } catch (\Throwable $e) { return $fb; }
        }
        return $fb;
    };
    $fmtPrice = function ($v) use ($hasUserPrice, $user, $currencySymbol) {
        if ($hasUserPrice) {
            try { return $user->priceFormat($v) ?? __('Failed to format amount.'); } catch (\Throwable $e) { return __('Failed to format amount.'); }
        }
        if (is_numeric($v)) return $currencySymbol . number_format((float)$v, 2);
        return __('Amount not available.');
    };

    $filesBase = $hasGetFile ? Utility::getFile('uploads/order') : 'uploads/order';
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Orders') }}
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Order') }}</li>
@endsection

@section(YD::ADM_CTT)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Order Id') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Plan Name') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Payment Type') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Coupon') }}</th>
                                <th>{{ __('Invoice') }}</th>
                                @if($isSuperAdmin)
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ordersList as $order)
                                @php
                                    $oid = $order->order_id ?? __('Order ID not available.');
                                    $uname = $order->user_name ?? __('Customer name not available.');
                                    $pname = $order->plan_name ?? __('Plan name not available.');
                                    $price = $fmtPrice($order->price ?? null);
                                    $pstatus = (string)($order->payment_status ?? '');
                                    $pstatusLower = strtolower($pstatus);
                                    $ptype = (string)($order->payment_type ?? '');
                                    $ptypeLower = strtolower($ptype);
                                    $created = $fmtDate($order->created_at ?? null, __('Failed to format date.'));
                                    $couponCode = data_get($order, 'totalCouponUsed.couponDetail.code') ?? '-';

                                    $statusBadge = ['class' => 'bg-danger', 'text' => ucfirst($pstatusLower ?: __('unknown'))];
                                    if (in_array($pstatusLower, ['success', 'approved'])) $statusBadge = ['class' => 'bg-primary', 'text' => ucfirst($pstatusLower)];
                                    elseif ($pstatusLower === 'succeeded') $statusBadge = ['class' => 'bg-primary', 'text' => __('Success')];
                                    elseif ($pstatusLower === 'pending') $statusBadge = ['class' => 'bg-warning', 'text' => __('Pending')];
                                @endphp
                                <tr>
                                    <td>{{ $oid }}</td>
                                    <td>{{ $uname }}</td>
                                    <td>{{ $pname }}</td>
                                    <td>{{ $price }}</td>
                                    <td>
                                        <span class="status_badge badge {{ $statusBadge['class'] }} p-2 px-3 rounded">{{ $statusBadge['text'] }}</span>
                                    </td>
                                    <td>{{ $ptype !== '' ? $ptype : __('Payment type not available.') }}</td>
                                    <td>{{ $created }}</td>
                                    <td class="text-center">{{ $couponCode }}</td>
                                    <td class="Id">
                                        @php
                                            $receipt = (string)($order->receipt ?? '');
                                        @endphp
                                        @if($ptypeLower === 'manually')
                                            <p>{{ __('Manually plan upgraded by Super Admin') }}</p>
                                        @elseif($receipt !== '' && strtolower($receipt) === 'free coupon')
                                            <p>{{ __('Used 100 % discount coupon code.') }}</p>
                                        @elseif($ptypeLower === 'stripe' && $receipt !== '')
                                            <a href="{{ $receipt }}" target="_blank">
                                                <i class="ti ti-file-invoice"></i> {{ __('Receipt') }}
                                            </a>
                                        @elseif($ptypeLower === 'bank transfer' && $receipt !== '')
                                            <a href="{{ rtrim($filesBase, '/').'/'.$receipt }}" target="_blank">
                                                <i class="ti ti-file-invoice"></i> {{ __('Receipt') }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @if($isSuperAdmin)
                                        <td class="Action">
                                            @php
                                                $showAction = ($ptypeLower === 'bank transfer' && $pstatusLower === 'pending');
                                            @endphp
                                            @if($showAction)
                                                @php
                                                    $actionUrl = Route::has(VW::OD.'.action') ? route(VW::OD.'.action', $order->id) : '#';
                                                    $actionGuard = ($hasFetchMsg ? Utility::fetchLinkMessage($lang, VW::OD, 'payment_status_unavailable') : null) ?? __('Ordering of payment status route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                <span>
                                                    <div class="action-btn bg-warning">
                                                        <a
                                                            href="{{ $actionUrl }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $actionUrl }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $actionGuard }}"
                                                            data-size="lg"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Payment Status') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Payment Status') }}">
                                                            <i class="{{ VC::TI_CRT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                </span>
                                            @endif
                                            @php
                                                $deleteUrl = Route::has(VW::OD.'.destroy') ? route(VW::OD.'.destroy', $order->id) : '#';
                                                $deleteGuard = ($hasFetchMsg ? Utility::fetchLinkMessage($lang, VW::OD, 'delete_order_unavailable') : 'Delete order route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete order route is unavailable. Please contact technical support or your domain administrator.');
                                                $formId = 'delete-order-form-'.$order->id;
                                            @endphp
                                            <span>
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'url'    => $deleteUrl,
                                                        'id'     => $formId,
                                                        'data-url' => $deleteUrl,
                                                        'data-sv-localized' => 'true',
                                                        'data-guard-msg' => $deleteGuard
                                                    ]) !!}
                                                        <a
                                                            href="{{ $deleteUrl }}"
                                                            class="{{ VC::TRS_PARA }}"
                                                            data-url="{{ $deleteUrl }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $deleteGuard }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </span>
                                        </td>
                                        @push(ST::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/orders/action.js') }}"></script>
                                            <script defer src="{{ asset('assets/js/routes/orders/delete.js') }}"></script>
                                        @endpush
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 10 : 9 }}" class="text-center text-muted">{{ __('No orders found.') }}</td>
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
