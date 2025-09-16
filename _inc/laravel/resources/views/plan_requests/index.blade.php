@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PlansConstants as PL,
        StacksConstants as ST,
        UsersConstants as UC,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $items = [];
    if (is_array($plan_requests ?? null) && count($plan_requests)) {
        $items = $plan_requests;
    } elseif (($plan_requests ?? null) instanceof Collection && $plan_requests->isNotEmpty()) {
        $items = $plan_requests;
    }
@endphp
@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Plan-Request') }}
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
    <li class="breadcrumb-item">{{ __('Plan Request') }}</li>
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Plan Request') }}</h5>
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} header datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Plan Name') }}</th>
                                    <th>{{ __('Total Users') }}</th>
                                    <th>{{ __('Total Customers') }}</th>
                                    <th>{{ __('Total Vendors') }}</th>
                                    <th>{{ __('Total Clients') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $prequest)
                                    @php
                                        $uidName   = data_get($prequest, 'user.'.UC::COL_NM, '-');
                                        $planName  = data_get($prequest, 'plan.'.PL::COL_NM, __('No name available'));
                                        $maxUsers  = data_get($prequest, 'plan.'.PL::COL_MAX_U, __('Failed to get the number of max users'));
                                        $maxCust   = data_get($prequest, 'plan.'.PL::COL_MAX_CR, __('Failed to get the number of max customers'));
                                        $maxVend   = data_get($prequest, 'plan.'.PL::COL_MAX_V, __('Failed to get the number of max vendors'));
                                        $maxClient = data_get($prequest, 'plan.'.PL::COL_MAX_CL, __('Failed to get the number of max clients'));

                                        $durRaw    = data_get($prequest, PL::COL_DUR, null);
                                        $duration  = $durRaw === 'year' ? __('Yearly') : ($durRaw === 'month' ? __('Monthly') : __('Lifetime'));
                                        $dateStr   = \App\Models\Utility::getDateFormated($prequest->created_at, true);

                                        $approveUrl = Route::has(VW::PLN_RQ.'.request.response') ? route(VW::PLN_RQ.'.request.response', [$prequest->id, 1]) : '#';
                                        $rejectUrl  = Route::has(VW::PLN_RQ.'.request.response') ? route(VW::PLN_RQ.'.request.response', [$prequest->id, 0]) : '#';

                                        $approveGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ, 'approve_plan_request_unavailable') : 'Approve Plan Request route is unavailable. Please contact technical support or your domain administrator.') ?? __('Approve Plan Request route is unavailable. Please contact technical support or your domain administrator.');
                                        $rejectGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ, 'reject_plan_request_unavailable')  : 'Reject Plan Request route is unavailable. Please contact technical support or your domain administrator.')  ?? __('Reject Plan Request route is unavailable. Please contact technical support or your domain administrator.');
                                    @endphp
                                    <tr>
                                        <td><div class="font-style">{{ $uidName }}</div></td>
                                        <td><div class="font-style">{{ $planName }}</div></td>
                                        <td>{{ $maxUsers }}</td>
                                        <td>{{ $maxCust }}</td>
                                        <td>{{ $maxVend }}</td>
                                        <td>{{ $maxClient }}</td>
                                        <td><div class="font-style">{{ $duration }}</div></td>
                                        <td>{{ $dateStr }}</td>
                                        <td>
                                            <div class="{{ VC::DFL_IL_VC }}">
                                                <a href="{{ $approveUrl }}"
                                                   class="{{ VC::BT_SM }} btn-success {{ VC::MX3 }} {{ VC::AL_IT_CT }}"
                                                   data-url="{{ $approveUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $approveGuard }}"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Approve') }}">
                                                    <i class="ti ti-check {{ VC::TXT_WT }}"></i>
                                                </a>
                                                <a href="{{ $rejectUrl }}"
                                                   class="{{ VC::BT_SM }} btn-danger {{ VC::MX3 }} {{ VC::AL_IT_CT }}"
                                                   data-url="{{ $rejectUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $rejectGuard }}"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Reject') }}">
                                                    <i class="ti ti-x {{ VC::TXT_WT }}"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">{{ __('No Manually Plan Request Found.') }}</td>
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

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/plans/requests/index.js') }}"></script>
@endpush
