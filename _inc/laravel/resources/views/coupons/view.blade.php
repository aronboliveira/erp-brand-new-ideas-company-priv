@php
    try {
} catch (\Throwable $e) {
        \Log::error('coupons/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Coupon Details') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') }} : '#" >{{ __('Dashboard') }}</a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Coupon Details') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="{{ VC::C12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $list = Utility::isFilled($userCoupons ?? []) ? $userCoupons : [];
@endphp
                                @forelse ($list as $userCoupon)
                                    @php
                                        $userName = data_get($userCoupon, 'userDetail.name') ?: __('No user name available');
                                        $created  = $userCoupon->created_at ?? null;
@endphp
                                    <tr class="font-style">
                                        <td>{{ $userName }}</td>
                                        <td>{{ $created ?: __('No date available') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="{{ VC::TXCT_MT }}">{{ __('No coupon details found') }}</td>
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
