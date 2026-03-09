@php
    try {
$lang          = Utility::fetchUserLang();
        $createRoute   = Route::has(VW::AWD . '.create')
            ? route(VW::AWD . '.create')
            : '#';
        $createMsg     = Utility::fetchLinkMessage(
            $lang,
            VW::AWD,
            'award_create_route_unavailable'
        ) ?? 'Award create route is unavailable. Please contact technical support or your domain administrator.';
        $createBtnId   = 'award-create-button';
    } catch (\Throwable $e) {
        \Log::error('awards/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL, __('Manage Award'))

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Award') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create award')
            <a id="{{ $createBtnId }}"
               href="{{ $createRoute }}"
               data-url="{{ $createRoute }}"
               data-guard-msg="{{ base64_encode($createMsg) }}"
               data-size="lg"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Award') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body table-border-style">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    @role('company')
                                        <th>{{ __('Employee') }}</th>
                                    @endrole
                                    <th>{{ __('Award Type') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Gift') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit award') || Gate::check('delete award'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($awards as $award)
                                    @php
                                        try {
                                            $editRoute   = Route::has(VW::AWD . '.edit')
                                                ? route(VW::AWD . '.edit', $award->id)
                                                : '#';
                                            $editMsg     = Utility::fetchLinkMessage(
                                                $lang,
                                                VW::AWD,
                                                'award_edit_route_unavailable'
                                            ) ?? 'Award edit route is unavailable. Please contact technical support or your domain administrator.';
                                            $editBtnId   = 'award-edit-' . $award->id;
                                            $destroyRoute = Route::has(VW::AWD . '.destroy')
                                                ? route(VW::AWD . '.destroy', $award->id)
                                                : '#';
                                            $destroyMsg   = Utility::fetchLinkMessage(
                                                $lang,
                                                VW::AWD,
                                                'award_destroy_route_unavailable'
                                            ) ?? 'Award destroy route is unavailable. Please contact technical support or your domain administrator.';
                                            $deleteBtnId  = 'award-delete-' . $award->id;
                                        } catch (\Throwable $e) {
                                            \Log::error('awards/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        @role('company')
                                            <td>{{ $award->employee->name ?? '' }}</td>
                                        @endrole
                                        <td>{{ $award->awardType->name ?? '' }}</td>
                                        <td>{{ auth()->user()?->dateFormat($award->date) }}</td>
                                        <td>{{ $award->gift }}</td>
                                        <td>{{ $award->description }}</td>
                                        @if(Gate::check('edit award') || Gate::check('delete award'))
                                            <td>
                                                @can('edit award')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="{{ $editBtnId }}"
                                                           href="{{ $editRoute }}"
                                                           data-url="{{ $editRoute }}"
                                                           data-guard-msg="{{ base64_encode($editMsg) }}"
                                                           data-size="lg"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Award') }}"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete award')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'url'            => $destroyRoute,
                                                            'method'         => 'DELETE',
                                                            'id'             => 'delete-form-' . $award->id,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $destroyMsg,
                                                        ]) !!}
                                                        <a id="{{ $deleteBtnId }}"
                                                           href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('delete-form-{{ $award->id }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/awards/index.js') }}"></script>
@endpush
