@php
    try {
$lang = Utility::fetchUserLang();
        $branchCreateRoute = Route::has(ViewsConstants::BRC . '.create')
            ? route(ViewsConstants::BRC . '.create')
            : '#';
        $branchCreateBtnId = 'branch-create-btn';
        $branchCreateMsg   = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BRC,
            'branch_create_route_unavailable'
        ) ?? 'Branch create route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('branches/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Branch') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Branch') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create branch')
            <a
                id="{{ $branchCreateBtnId }}"
                href="#"
                data-url="{{ $branchCreateRoute }}"
                data-guard-msg="{{ base64_encode($branchCreateMsg) }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Branch') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@if(!empty($branches) && ((is_array($branches) && $branches->count()) || ($branches instanceof Collection && $branches->isNotEmpty())))
    @section(YieldingConstants::ADM_CTT)
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C3 }}">
                @include('layouts.hrm_setup')
            </div>
            <div class="{{ VC::C9 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD }}-body table-border-style">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Branch') }}</th>
                                        <th width="200px">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="font-style">
                                    @foreach ($branches as $branch)
                                        @php
                                            try {
                                                $branchEditRoute    = Route::has(ViewsConstants::BRC . '.edit')
                                                    ? route(ViewsConstants::BRC . '.edit', $branch->id)
                                                    : '#';
                                                $branchEditBtnId    = 'branch-edit-btn-' . $branch->id;
                                                $branchEditMsg      = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BRC,
                                                    'branch_edit_route_unavailable'
                                                ) ?? 'Branch edit route is unavailable. Please contact technical support or your domain administrator.';

                                                $branchDestroyRoute = Route::has(ViewsConstants::BRC . '.destroy')
                                                    ? route(ViewsConstants::BRC . '.destroy', $branch->id)
                                                    : '#';
                                                $branchDestroyBtnId = 'branch-delete-btn-' . $branch->id;
                                                $branchDestroyFormId= 'delete-form-' . $branch->id;
                                                $branchDestroyMsg   = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BRC,
                                                    'branch_destroy_route_unavailable'
                                                ) ?? 'Branch destroy route is unavailable. Please contact technical support or your domain administrator.';
                                            } catch (\Throwable $e) {
                                                \Log::error('branches/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr>
                                            <td>{{ $branch->name }}</td>
                                            <td class="Action {{ VC::TX_END }}">
                                                @can('edit branch')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            id="{{ $branchEditBtnId }}"
                                                            href="#"
                                                            data-url="{{ $branchEditRoute }}"
                                                            data-guard-msg="{{ base64_encode($branchEditMsg) }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Branch') }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete branch')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'url'            => $branchDestroyRoute,
                                                            'method'         => 'DELETE',
                                                            'id'             => $branchDestroyFormId,
                                                            'data-url'       => $branchDestroyRoute,
                                                            'data-guard-msg' => $branchDestroyMsg,
                                                        ]) !!}
                                                            <a
                                                                id="{{ $branchDestroyBtnId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('{{ $branchDestroyFormId }}').submit();"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
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
        <script defer>
            window.RouteGuard?.guardMultiple?.(
                '{{ $branchCreateBtnId }}',
                @foreach ($branches as $branch)
                    'branch-edit-btn-{{ $branch->id ?? '' }}',
                    'branch-delete-btn-{{ $branch->id ?? '' }}',
                @endforeach
            );
        </script>
    @endpush
@else
    <div class="{{ VC::TXCT }}">
        <h5>{{ __('No branches found') }}</h5>
        <p class="{{ VC::TXT_MT }}">{{ __('Please create a new branch to get started.') }}</p>
    </div>
@endif
