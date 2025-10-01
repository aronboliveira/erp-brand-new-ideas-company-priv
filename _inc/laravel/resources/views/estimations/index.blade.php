@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PermissionsConstants as PC,
        StacksConstants as ST,
        UsersConstants as UC,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\{Estimation, Utility};
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $cntTotal      = data_get($cnt_estimation ?? [], 'total', __('No total available'));
    $cntThisMonth  = data_get($cnt_estimation ?? [], 'this_month', __('No monthly total available'));
    $cntThisWeek   = data_get($cnt_estimation ?? [], 'this_week', __('No weekly total available'));
    $cntLast30     = data_get($cnt_estimation ?? [], 'last_30days', __('No 30-day total available'));
@endphp
@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Estimate') }}
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="all-button-box {{ VC::R_FLX_ALC_JCE }}">
        @can('create estimation')
            @php
                $createBase     = VW::EST . '.create';
                $createKebab    = Str::kebab($createBase);
                $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
                $createUrl      = $createResolved ? route($createResolved) : '#';
                $createLinkId   = 'est-create-btn';
                $createGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'create_estimate_route_unavailable') ?? 'Create estimate route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                <a
                    id="{{ $createLinkId }}"
                    href="{{ $createUrl }}"
                    data-url="{{ $createUrl }}"
                    data-size="sm"
                    data-ajax-popup="true"
                    data-title="{{ __('Create Estimate') }}"
                    data-guard-msg="{{ $createGuardMsg }}"
                    class="{{ VC::BT_XS }} btn-white btn-icon-only width-auto"
                >
                    <i class="ti ti-plus"></i> {{ __('Create') }}
                </a>
            </div>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }} {{ VC::RW }}">
            <div class="{{ VC::CL_XL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h5 class="{{ VC::RPT_TX_GR }}">{{ __('Total Estimate') }}</h5>
                    <h5 class="{{ VC::RPT_TX_DEF }}">{{ $cntTotal }}</h5>
                </div>
            </div>
            <div class="{{ VC::CL_XL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h5 class="{{ VC::RPT_TX_GR }}">{{ __('This Month Total Estimate') }}</h5>
                    <h5 class="{{ VC::RPT_TX_DEF }}">{{ $cntThisMonth }}</h5>
                </div>
            </div>
            <div class="{{ VC::CL_XL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h5 class="{{ VC::RPT_TX_GR }}">{{ __('This Week Total Estimate') }}</h5>
                    <h5 class="{{ VC::RPT_TX_DEF }}">{{ $cntThisWeek }}</h5>
                </div>
            </div>
            <div class="{{ VC::CL_XL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h5 class="{{ VC::RPT_TX_GR }}">{{ __('Last 30 Days Total Estimate') }}</h5>
                    <h5 class="{{ VC::RPT_TX_DEF }}">{{ $cntLast30 }}</h5>
                </div>
            </div>
        </div>

        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} table-striped dataTable">
                            <thead>
                            <tr>
                                <th>{{ __('Estimate') }}</th>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Issue Date') }}</th>
                                <th>{{ __('Value') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if(($user?->{UC::COL_TP} ?? null) !== PC::CL)
                                    <th width="250px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                                @if((is_array($estimations) && count($estimations)) || ($estimations instanceof Collection && 
                                $estimations->isNotEmpty()))
                                    @php
                                        $isPriceFormatAvailable = method_exists($user, 'priceFormat');
                                        $isDateFormatAvailable  = method_exists($user, 'dateFormat');
                                        $isEsimateNumberFormatAvailable = method_exists($user, 'estimateNumberFormat');
                                        $isGetTotalAvailable   = method_exists($estimate, 'getTotal');
                                    @endphp
                                    @foreach ($estimations as $estimate)
                                        @php
                                            $estId      = data_get($estimate, 'id');
                                            $clientName = data_get($estimate, 'client.name', __('Could not find client name'));
                                            $issueDate  = $isDateFormatAvailable ? ($user?->dateFormat(data_get($estimate, 'issue_date')) ?? __('Failed to get issue date')) : (__('Failed to format date'));
                                            $totalValue = $isPriceFormatAvailable && $isGetTotalAvailable ? ($user?->priceFormat($estimate?->getTotal()) ?? __('Failed to get value')) : (__('Failed to format price'));
                                            $statusIdx  = (int) data_get($estimate, 'status', -1);
                                            $statusLbl  = data_get(Estimation::$statuses ?? [], $statusIdx, __('No status available'));
                                        @endphp
                                        <tr>
                                            <td class="Id">
                                                @can('View Estimation')
                                                    @php
                                                        $showBase     = VW::EST . '.show';
                                                        $showKebab    = Str::kebab($showBase);
                                                        $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                                                        $showUrl      = ($showResolved && $estId) ? route($showResolved, $estId) : '#';
                                                        $showGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'estimation_show_route_unavailable') ?? 'Show estimate route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <a
                                                        href="{{ $showUrl }}"
                                                        data-url="{{ $showUrl }}"
                                                        data-guard-msg="{{ $showGuardMsg }}"
                                                        class="est-show-link"
                                                    >
                                                        <i class="ti ti-file-estimate"></i>
                                                        {{ $isEsimateNumberFormatAvailable ? ($user?->estimateNumberFormat(data_get($estimate, 'estimation_id')) ?? __('Failed to get estimate number')) : (__('Failed to format estimate number')) }}
                                                    </a>
                                                @else
                                                    {{ $isEsimateNumberFormatAvailable ? ($user?->estimateNumberFormat(data_get($estimate, 'estimation_id')) ?? __('Failed to get estimate number')) : (__('Failed to format estimate number')) }}
                                                @endcan
                                            </td>
                                            <td>{{ $clientName }}</td>
                                            <td>{{ $issueDate }}</td>
                                            <td>{{ $totalValue }}</td>
                                            <td>
                                                @if($statusIdx === 0)
                                                    <span class="badge badge-pill badge-primary">{{ __($statusLbl) }}</span>
                                                @elseif($statusIdx === 1)
                                                    <span class="badge badge-pill badge-danger">{{ __($statusLbl) }}</span>
                                                @elseif($statusIdx === 2)
                                                    <span class="badge badge-pill badge-warning">{{ __($statusLbl) }}</span>
                                                @elseif($statusIdx === 3)
                                                    <span class="badge badge-pill badge-success">{{ __($statusLbl) }}</span>
                                                @elseif($statusIdx === 4)
                                                    <span class="badge badge-pill badge-info">{{ __($statusLbl) }}</span>
                                                @else
                                                    <span class="badge badge-pill badge-secondary">{{ __($statusLbl) }}</span>
                                                @endif
                                            </td>
                                            @if(($user?->{UC::COL_TP} ?? null) !== PC::CL)
                                                <td class="Action">
                                                    <span>
                                                        @can('view estimation')
                                                            @php
                                                                $showBase     = VW::EST . '.show';
                                                                $showKebab    = Str::kebab($showBase);
                                                                $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                                                                $showUrl      = ($showResolved && $estId) ? route($showResolved, $estId) : '#';
                                                                $showGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'estimation_show_route_unavailable') ?? 'Show estimate route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                href="{{ $showUrl }}"
                                                                data-url="{{ $showUrl }}"
                                                                data-guard-msg="{{ $showGuardMsg }}"
                                                                class="edit-icon bg-warning"
                                                                data-toggle="tooltip"
                                                            >
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        @endcan

                                                        @can('edit estimation')
                                                            @php
                                                                $editBase     = VW::EST . '.edit';
                                                                $editKebab    = Str::kebab($editBase);
                                                                $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                                $editUrl      = ($editResolved && $estId) ? route($editResolved, $estId) : '#';
                                                                $editGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'estimation_edit_route_unavailable') ?? 'Edit estimate route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                href="#"
                                                                data-url="{{ $editUrl }}"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Estimation') }}"
                                                                data-guard-msg="{{ $editGuardMsg }}"
                                                                class="edit-icon"
                                                                data-toggle="tooltip"
                                                                data-original-title="{{ __('Edit') }}"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan

                                                        @can('delete estimation')
                                                            @php
                                                                $destroyBase     = VW::EST . '.destroy';
                                                                $destroyKebab    = Str::kebab($destroyBase);
                                                                $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                                $destroyUrl      = ($destroyResolved && $estId) ? route($destroyResolved, $estId) : '#';
                                                                $destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'estimation_destroy_route_unavailable') ?? 'Delete estimate route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                href="#"
                                                                class="delete-icon"
                                                                data-toggle="tooltip"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-guard-msg="{{ $destroyGuardMsg }}"
                                                                data-url="{{ $destroyUrl }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{$estId}}').submit();"
                                                            >
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Collective\Html\FormFacade::open([
                                                                'method' => 'DELETE',
                                                                'url'    => $destroyUrl,
                                                                'id'     => 'delete-form-'.$estId
                                                            ]) !!}
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        @endcan
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('No estimations found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/estimations/index.js') }}"></script>
    @endpush
@endsection
