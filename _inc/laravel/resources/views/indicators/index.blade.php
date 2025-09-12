@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, Gate};

    $user = Auth::user();
    $hasUser = (bool) $user;
    $hasUserDateFormat = $hasUser && method_exists($user, 'dateFormat');
    $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Indicator') }}
@endsection

@push(ST::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/indicators/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/indicators/index.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Indicator') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create indicator')
            @php
                $createName = VW::IND.'.create';
                $createUrl = Route::has($createName) ? route($createName) : '#';
                $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'create_indicator_route_unavailable') : null)
                    ?? __('Create Indicator route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a href="{{ $createUrl }}"
               data-size="lg"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Indicator') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ $createGuardMsg }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    @if(!$hasUser)
        <div class="alert alert-warning mb-0" role="alert">{{ __('Failed to load Indicators for the current user.') }}</div>
    @else
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            @php
                                $hasAnyActions = Gate::check('edit indicator') || Gate::check('delete indicator') || Gate::check('show indicator');
                            @endphp
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>{{ __('Designation') }}</th>
                                        <th>{{ __('Overall Rating') }}</th>
                                        <th>{{ __('Added By') }}</th>
                                        <th>{{ __('Created At') }}</th>
                                        @if($hasAnyActions)
                                            <th width="200px">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="font-style">
                                    @forelse ($indicators as $indicator)
                                        @php
                                            $branchName = isset($indicator->branches, $indicator->branches->name) && $indicator->branches->name !== ''
                                                ? $indicator->branches->name
                                                : __('Branch not available.');
                                            $departmentName = isset($indicator->departments, $indicator->departments->name) && $indicator->departments->name !== ''
                                                ? $indicator->departments->name
                                                : __('Department not available.');
                                            $designationName = isset($indicator->designations, $indicator->designations->name) && $indicator->designations->name !== ''
                                                ? $indicator->designations->name
                                                : __('Designation not available.');
                                            $addedBy = isset($indicator->user, $indicator->user->name) && $indicator->user->name !== ''
                                                ? $indicator->user->name
                                                : __('Added by not available.');

                                            $overall = 0.0;
                                            if (!empty($indicator->rating)) {
                                                $ratingArr = json_decode($indicator->rating, true);
                                                if (is_array($ratingArr) && count($ratingArr) > 0) {
                                                    $overall = array_sum($ratingArr) / count($ratingArr);
                                                }
                                            }

                                            $createdAtTxt = isset($indicator->created_at) && $indicator->created_at
                                                ? ($hasUserDateFormat ? $user->dateFormat($indicator->created_at) : __('Failed to format created date.'))
                                                : __('Created date not available.');
                                            $iid = isset($indicator->id) ? (string) $indicator->id : '';
                                        @endphp
                                        <tr>
                                            <td>{{ $branchName }}</td>
                                            <td>{{ $departmentName }}</td>
                                            <td>{{ $designationName }}</td>
                                            <td>
                                                @for($i=1; $i<=5; $i++)
                                                    @if($overall < $i)
                                                        @if(is_float($overall) && (round($overall) == $i))
                                                            <i class="text-warning fas fa-star-half-alt"></i>
                                                        @else
                                                            <i class="fas fa-star"></i>
                                                        @endif
                                                    @else
                                                        <i class="text-warning fas fa-star"></i>
                                                    @endif
                                                @endfor
                                                <span class="theme-text-color">({{ number_format($overall, 1) }})</span>
                                            </td>
                                            <td>{{ $addedBy }}</td>
                                            <td>{{ $createdAtTxt }}</td>

                                            @if($hasAnyActions)
                                                <td>
                                                    <div class="{{ VC::DFL }}">
                                                        @can('show indicator')
                                                            @php
                                                                $showName = VW::IND.'.show';
                                                                $showUrl = (Route::has($showName) && $iid !== '') ? route($showName, $iid) : '#';
                                                                $showGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'show_indicator_route_unavailable') : null)
                                                                    ?? __('View Indicator route is unavailable. Please contact technical support or your domain administrator.');
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                                <a href="{{ $showUrl }}"
                                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                                   data-url="{{ $showUrl }}"
                                                                   data-size="lg"
                                                                   data-ajax-popup="true"
                                                                   data-title="{{ __('Indicator Detail') }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('View') }}"
                                                                   data-guard-msg="{{ $showGuardMsg }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('edit indicator')
                                                            @php
                                                                $editName = VW::IND.'.edit';
                                                                $editUrl = (Route::has($editName) && $iid !== '') ? route($editName, $iid) : '#';
                                                                $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'edit_indicator_route_unavailable') : null)
                                                                    ?? __('Edit Indicator route is unavailable. Please contact technical support or your domain administrator.');
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                <a href="{{ $editUrl }}"
                                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                                   data-url="{{ $editUrl }}"
                                                                   data-size="lg"
                                                                   data-ajax-popup="true"
                                                                   data-title="{{ __('Edit Indicator') }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Edit') }}"
                                                                   data-guard-msg="{{ $editGuardMsg }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('delete indicator')
                                                            @php
                                                                $destroyName = VW::IND.'.destroy';
                                                                $destroyUrl = (Route::has($destroyName) && $iid !== '') ? route($destroyName, $iid) : '#';
                                                                $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'destroy_indicator_route_unavailable') : null)
                                                                    ?? __('Delete Indicator route is unavailable. Please contact technical support or your domain administrator.');
                                                                $delFormId = 'delete-form-'.$iid;
                                                                $confirmTitle = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                                $confirmBody = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'url'    => $destroyUrl,
                                                                    'id'     => $delFormId,
                                                                    'data-url' => $destroyUrl,
                                                                    'data-guard-msg' => $destroyGuardMsg,
                                                                    'data-sv-localized' => 'true',
                                                                ]) !!}
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT_PR }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                                       data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $hasAnyActions ? 7 : 6 }}" class="text-center">
                                                {{ __('No indicators found or the indicator data failed to load.') }}
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
    @endif
@endsection
