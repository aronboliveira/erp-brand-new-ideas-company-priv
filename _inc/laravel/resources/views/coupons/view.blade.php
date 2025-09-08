@php
    use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants, YieldingConstants};
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Coupon Details') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') }} : '#" >{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Coupon Details') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $list = (($userCoupons ?? null) instanceof Collection || is_array($userCoupons ?? null)) ? $userCoupons : [];
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
                                        <td colspan="2" class="text-center text-muted">{{ __('No coupon details found') }}</td>
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
