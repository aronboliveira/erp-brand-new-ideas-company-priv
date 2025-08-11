@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        LangsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Cookie, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Leave Type')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Leave Type')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create leave type')
            @php
                $createUrl   = Route::has(ViewsConstants::LV_TP.'.create')
                    ? route(ViewsConstants::LV_TP.'.create')
                    : '#';
                $createClass = 'create-leavetype-link';
            @endphp
            <a
                href="{{ $createUrl }}"
                id="{{ $createClass }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $createClass }}"
                data-url="{{ $createUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Leave Type') }}"
                data-url="{{ $createUrl }}"
                data-sv-localized="true"
                data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::LV_TP, 'create_leave_type_unavailable') ?? '# ERROR' ) }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-original-title="{{ __('Create') }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
            <script defer src="{{ asset('assets/js/routes/leaveTypes/create.js') }}"></script>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Leave Type')}}</th>
                                <th>{{__('Days / Year')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($leavetypes as $leavetype)
                                <tr>
                                    <td>{{ $leavetype->title }}</td>
                                    <td>{{ $leavetype->days}}</td>
                                    <td>
                                        @can('edit leave type')
                                            @php
                                                $editUrl    = Route::has(ViewsConstants::LV_TP.'.edit')
                                                    ? route(ViewsConstants::LV_TP.'.edit', $leavetype->id)
                                                    : '#';
                                                $editClass  = 'edit-leavetype-link';
                                            @endphp
                                            <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                <a
                                                    href="{{ $editUrl }}"
                                                    id="{{ $editClass }}"
                                                    class="{{ ViewClassNamesConstants::BT_SM_CT }} {{ $editClass }}"
                                                    data-url="{{ $editUrl }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::LV_TP, 'edit_leave_type_unavailable') ?? '# ERROR' ) }}"
                                                    data-ajax-popup="true"
                                                    data-title="{{ __('Edit Leave Type') }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Edit') }}"
                                                    data-original-title="{{ __('Edit') }}"
                                                >
                                                    <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                </a>
                                            </div>
                                            <script defer src="{{ asset('assets/js/routes/leaveTypes/edit.js') }}"></script>
                                        @endcan
                                    
                                        @can('delete leave type')
                                            @php
                                                $deleteUrl   = Route::has(ViewsConstants::LV_TP.'.destroy')
                                                    ? route(ViewsConstants::LV_TP.'.destroy', $leavetype->id)
                                                    : '#';
                                                $deleteClass = 'delete-leavetype-link';
                                                $formId      = 'delete-leavetype-form-'.$leavetype->id;
                                            @endphp
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Collective\Html\FormFacade::open([
                                                    'method' => 'DELETE',
                                                    'url'    => $deleteUrl,
                                                    'id'     => $formId
                                                ]) !!}/
                                                    <a
                                                        href="{{ $deleteUrl }}"
                                                        id="{{ $deleteClass }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT_PR }} {{ $deleteClass }}"
                                                        data-url="{{ $deleteUrl }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ __( Utility::fetchLinkMessage($lang, ViewsConstants::LV_TP, 'delete_leave_type_unavailable') ?? '# ERROR' ) }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-original-title="{{ __('Delete') }}"
                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                    </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                            </div>
                                            <script defer src="{{ asset('assets/js/routes/leaveTypes/delete.js') }}"></script>
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
