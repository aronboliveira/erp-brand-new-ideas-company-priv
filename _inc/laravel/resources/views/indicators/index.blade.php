@php
    try {
$user = Auth::user();
        $hasUser = (bool) $user;
        $hasUserDateFormat = $hasUser && method_exists($user, 'dateFormat');
        $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
    } catch (\Throwable $e) {
        \Log::error('indicators/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
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
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Indicator') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create indicator')
            @php
                try {
                    $createName = VW::IND.'.create';
                    $createUrl = Route::has($createName) ? route($createName) : '#';
                    $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'create_indicator_route_unavailable') : null)
                        ?? __('Create Indicator route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('indicators/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $createUrl }}"
               data-size="lg"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Indicator') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ base64_encode($createGuardMsg) }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    @if(!$hasUser)
        <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('Failed to load Indicators for the current user.') }}</div>
    @else
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }}">
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
                                            try {
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
                                            } catch (\Throwable $e) {
                                                \Log::error('indicators/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
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
                                                                try {
                                                                    $showName = VW::IND.'.show';
                                                                    $showUrl = (Route::has($showName) && $iid !== '') ? route($showName, $iid) : '#';
                                                                    $showGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'show_indicator_route_unavailable') : null)
                                                                        ?? __('View Indicator route is unavailable. Please contact technical support or your domain administrator.');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('indicators/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
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
                                                                   data-guard-msg="{{ base64_encode($showGuardMsg) }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('edit indicator')
                                                            @php
                                                                try {
                                                                    $editName = VW::IND.'.edit';
                                                                    $editUrl = (Route::has($editName) && $iid !== '') ? route($editName, $iid) : '#';
                                                                    $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'edit_indicator_route_unavailable') : null)
                                                                        ?? __('Edit Indicator route is unavailable. Please contact technical support or your domain administrator.');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('indicators/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
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
                                                                   data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('delete indicator')
                                                            @php
                                                                try {
                                                                    $destroyName = VW::IND.'.destroy';
                                                                    $destroyUrl = (Route::has($destroyName) && $iid !== '') ? route($destroyName, $iid) : '#';
                                                                    $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::IND, 'destroy_indicator_route_unavailable') : null)
                                                                        ?? __('Delete Indicator route is unavailable. Please contact technical support or your domain administrator.');
                                                                    $delFormId = 'delete-form-'.$iid;
                                                                    $confirmTitle = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                                    $confirmBody = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('indicators/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
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
                                            <td colspan="{{ $hasAnyActions ? 7 : 6 }}" class="{{ VC::TXCT }}">
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
