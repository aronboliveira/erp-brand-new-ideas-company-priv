@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>

    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('CRM')}}</li>
@endsection
@section('content')
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL4 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar {{ VC::BG_P }}">
                                    <i class="ti ti-layout-2"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Lead') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $crm_data['total_leads'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CL4 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-warning">
                                    <i class="ti ti-layout-2"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Deal') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $crm_data['total_deals'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CL4 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-notebook"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Contract') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $crm_data['total_contracts'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CL6 }}">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Lead Status') }}</h5>
                </div>
                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        @php $leadStatuses = $crm_data['lead_status'] ?? []; @endphp
                        @if(!empty($leadStatuses))
                            @foreach($leadStatuses as $status => $val)
                                @php
                                    $stage = $val['lead_stage'] ?? __('Stage not available');
                                    $perc  = is_numeric($val['lead_percentage'] ?? null) ? $val['lead_percentage'] : 0;
                                @endphp
                                <div class="{{ VC::CM6 }} {{ VC::CS6 }} mb-5">
                                    <div class="align-items-start">
                                        <div class="{{ VC::MS2 }}">
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ $stage }}</p>
                                            <h3 class="mb-0 text-primary">{{ $perc }}%</h3>
                                            <div class="progress {{ VC::MB0 }}">
                                                <div class="progress-bar {{ VC::BG_P }}" style="width: {{ $perc }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="px-3 py-2">{{ __('No lead status data available') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CL6 }}">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Deal Status') }}</h5>
                </div>
                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        @php $dealStatuses = $crm_data['deal_status'] ?? []; @endphp
                        @if(!empty($dealStatuses))
                            @foreach($dealStatuses as $status => $val)
                                @php
                                    $stage = $val['deal_stage'] ?? __('Stage not available');
                                    $perc  = is_numeric($val['deal_percentage'] ?? null) ? $val['deal_percentage'] : 0;
                                @endphp
                                <div class="{{ VC::CM6 }} {{ VC::CS6 }} mb-5">
                                    <div class="align-items-start">
                                        <div class="{{ VC::MS2 }}">
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ $stage }}</p>
                                            <h3 class="mb-0 text-primary">{{ $perc }}%</h3>
                                            <div class="progress {{ VC::MB0 }}">
                                                <div class="progress-bar {{ VC::BG_P }}" style="width: {{ $perc }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="px-3 py-2">{{ __('No deal status data available') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-12">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5 class="{{ VC::MT1 }} {{ VC::MB0 }}">{{ __('Latest Contract') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ __('Subject') }}</th>
                                    @if(($users?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CL)
                                        <th>{{ __('Client') }}</th>
                                    @endif
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Contract Type') }}</th>
                                    <th>{{ __('Contract Value') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($crm_data['latestContract'] ?? []) as $contract)
                                    <tr>
                                        <td>
                                            @php
                                                $contractShowBase = 'contracts.show';
                                                $contractShowKebab = Str::kebab($contractShowBase);
                                                $contractShowResolved = Route::has($contractShowBase) ? $contractShowBase : (Route::has($contractShowKebab) ? $contractShowKebab : null);
                                                $contractIdValue = isset($contract) && !empty($contract->id) ? $contract->id : null;
                                                $contractShowUrl = ($contractShowResolved && $contractIdValue) ? route($contractShowResolved, $contractIdValue) : '#';
                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                $contractShowGuardMsg = Utility::fetchLinkMessage($langValue, 'contracts', 'show_contract_route_unavailable') ?? 'Show contract route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a href="{{ $contractShowUrl }}"
                                            class="{{ VC::BT_OUTPM }} contract-show-link"
                                            data-url="{{ $contractShowUrl }}"
                                            data-guard-msg="{{ $contractShowGuardMsg }}"
                                            data-sv-localized="true">
                                                {{ $users?->contractNumberFormat($contract->id) ?? __('Contract number not available') }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script src="{{ asset('assets/js/routes/contracts/show.js') }}" defer></script>
                                            @endpush
                                        </td>
                                        <td>{{ $contract->subject ?? __('Subject not available') }}</td>
                                        @if(($users?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CL)
                                            <td>{{ optional($contract->clients)->name ?? __('Client not available') }}</td>
                                        @endif
                                        <td>{{ optional($contract->projects)->project_name ?? __('Project not available') }}</td>
                                        <td>{{ optional($contract->types)->name ?? __('Type not available') }}</td>
                                        <td>{{ isset($contract->value) ? $users?->priceFormat($contract->value) : __('Value not available') }}</td>
                                        <td>{{ isset($contract->start_date) ? $users?->dateFormat($contract->start_date) : __('Start date not available') }}</td>
                                        <td>{{ isset($contract->end_date) ? $users?->dateFormat($contract->end_date) : __('End date not available') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="text-center">
                                                <h6>{{ __('No latest contracts available') }}</h6>
                                            </div>
                                        </td>
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
