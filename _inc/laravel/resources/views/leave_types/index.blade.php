@php
    try {
$user = Auth::user();
        $hasFetchUserLang    = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user:$user) : app()->getLocale();

        $leavetypesIsList = (is_array($leavetypes ?? null) && count($leavetypes ?? []) > 0)
            || (($leavetypes ?? null) instanceof Collection && $leavetypes->isNotEmpty());
    } catch (\Throwable $e) {
        \Log::error('leave_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Leave Type') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Leave Type') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create leave type')
            @php
                try {
                    $createUrl = Route::has(VW::LV_TP.'.create') ? route(VW::LV_TP.'.create') : '#';
                    $createGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV_TP, 'create_leave_type_unavailable') : null)
                        ?? __('Create leave type route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('leave_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $createUrl }}"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Leave Type') }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Days / Year') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @if ($leavetypesIsList)
                                    @foreach ($leavetypes as $leavetype)
                                        @php
                                            try {
                                                $id      = isset($leavetype->id) ? (string)$leavetype->id : '';
                                                $title   = isset($leavetype->title) && $leavetype->title !== '' ? $leavetype->title : __('(title not available)');
                                                $daysRaw = $leavetype->days ?? null;
                                                $days    = (is_numeric($daysRaw) || (is_string($daysRaw) && trim($daysRaw) !== ''))
                                                            ? $daysRaw
                                                            : __('Days per year data was not available.');
                                            } catch (\Throwable $e) {
                                                \Log::error('leave_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr>
                                            <td>{{ $title }}</td>
                                            <td>{{ $days }}</td>
                                            <td>
                                                @can('edit leave type')
                                                    @php
                                                        try {
                                                            $editUrl = ($id !== '' && Route::has(VW::LV_TP.'.edit')) ? route(VW::LV_TP.'.edit', $id) : '#';
                                                            $editGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV_TP, 'edit_leave_type_unavailable') : null)
                                                                ?? __('Edit leave type route is unavailable. Please contact technical support or your domain administrator.');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('leave_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Leave Type') }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ base64_encode($editGuard) }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('delete leave type')
                                                    @php
                                                        try {
                                                            $delUrl  = ($id !== '' && Route::has(VW::LV_TP.'.destroy')) ? route(VW::LV_TP.'.destroy', $id) : '#';
                                                            $formId  = 'delete-leavetype-form-'.($id === '' ? 'x' : $id);
                                                            $delGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV_TP, 'delete_leave_type_unavailable') : null)
                                                                ?? __('Delete leave type route is unavailable. Please contact technical support or your domain administrator.');
                                                            $confirmA = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? __('Are You Sure?');
                                                            $confirmB = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? __('This action can not be undone. Do you want to continue?');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('leave_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'method'            => 'DELETE',
                                                            'url'               => $delUrl,
                                                            'id'                => $formId,
                                                            'data-url'          => $delUrl,
                                                            'data-guard-msg'    => $delGuard,
                                                            'data-sv-localized' => 'true'
                                                        ]) !!}
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __($confirmA) }}|{{ __($confirmB) }}"
                                                               data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="{{ VC::TXCT_MT }}">
                                            {{ __('Leave types data was not available or failed to be fetched.') }}
                                        </td>
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

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/leaves/types/index.js') }}"></script>
@endpush
