@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PlansConstants,
        StacksConstants,
        UsersConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Plan-Request')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Plan Request')}}</li>
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Plan Request')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table header " width="100%">
                            <tbody>
                            @if($plan_requests->count() > 0)
                                @foreach($plan_requests as $prequest)
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Plan Name')}}</th>
                                    <th>{{__('Total Users')}}</th>
                                    <th>{{__('Total Customers')}}</th>
                                    <th>{{__('Total Vendors')}}</th>
                                    <th>{{__('Total Clients')}}</th>
                                    <th>{{__('Duration')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                                </thead>
                                    <tr>
                                        <td>
                                            <div class="font-style">{{ $prequest->user[UsersConstants::COL_NM] }}</div>
                                        </td>
                                        @php
                                            use App\Config\Constants\PlansConstants;
                                            $planCols = [
                                                [ 'key' => PlansConstants::COL_NM,      'class' => 'font-style' ],
                                                [ 'key' => PlansConstants::COL_MAX_U,   'class' => ''           ],
                                                [ 'key' => PlansConstants::COL_MAX_CR,  'class' => ''           ],
                                                [ 'key' => PlansConstants::COL_MAX_V,   'class' => ''           ],
                                                [ 'key' => PlansConstants::COL_MAX_CL,  'class' => ''           ],
                                            ];
                                            $durationLabels = [
                                                'year'  => __('Yearly'),
                                                'month' => __('Monthly'),
                                            ];
                                            $durationLabel = $durationLabels[$prequest[PlansConstants::COL_DUR]] ?? __('Lifetime');
                                        @endphp
                                        @foreach($planCols as $col)
                                            <td>
                                                <div class="{{ $col['class'] }}">
                                                    {{ $prequest->plan->{ $col['key'] } }}
                                                </div>
                                            </td>
                                        @endforeach
                                        <td>
                                            <div class="font-style">{{ $durationLabel }}</div>
                                        </td>
                                        <td>{{ Utility::getDateFormated($prequest->created_at,true) }}</td>
                                        <td>
                                            <div>
                                                <a href="{{route('response.request',[$prequest->id,1])}}" class="btn btn-success btn-sm">
                                                    <i class="ti ti-check"></i>
                                                </a>
                                                <a href="{{route('response.request',[$prequest->id,0])}}" class="btn btn-danger btn-sm">
                                                <i class="ti ti-x"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <th scope="col" colspan="7"><h6 class="text-center">{{__('No Manually Plan Request Found.')}}</h6></th>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
