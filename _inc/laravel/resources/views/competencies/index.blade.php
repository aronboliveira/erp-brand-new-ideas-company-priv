@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants
    };

    $lang = Utility::fetchUserLang();
    $createName     = ViewsConstants::CPT . '.create';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : '#';
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPT,
        'competency_create_route_unavailable'
    ) ?? 'Competency create route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Competencies') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Competencies') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('Create Competencies')
            <a
                href="#"
                id="createCompetencyBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-listener-alias="create-competency"
                data-ajax-popup="true"
                data-title="{{ __('Create New Competencies') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body {{ VC::TB }}">
                    <div class="{{ VC::DFL }} {{ VC::JCE }}">
                        @php
                            $rows = ((is_array($competencies ?? null) && count($competencies ?? [])) || (($competencies ?? null) instanceof Collection && ($competencies)->isNotEmpty())) ? $competencies : [];
                        @endphp
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($rows as $competency)
                                    @php
                                        $cid = data_get($competency,'id');
                                        $name = data_get($competency,'name') ?: __('No competency name available');
                                        $typeName = data_get($competency,'performance.name') ?: __('No competency type available');
                                    @endphp
                                    <tr>
                                        <td>{{ $name }}</td>
                                        <td>{{ $typeName }}</td>
                                        <td class="Action">
                                            @can('edit document type')
                                                @php
                                                    $editName = ViewsConstants::CPT.'.edit';
                                                    $editRoute = (Route::has($editName) ? route($editName, $cid) : (Route::has(Str::kebab($editName)) ? route(Str::kebab($editName), $cid) : '#'));
                                                    $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPT, 'competency_edit_route_unavailable') ?: __('Failed to get competency edit route');
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="#" class="{{ VC::DFL_IL }} {{ VC::ALC }}" id="editCompetencyBtn_{{ $cid ?: 'na' }}" data-url="{{ $editRoute }}" data-guard-msg="{{ $editGuardMsg }}" data-listener-alias="edit-competency" data-ajax-popup="true" data-title="{{ __('Edit Competencies') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                </div>
                                            @endcan
                                            @can('Delete Competencies')
                                                @php
                                                    $destroyName = ViewsConstants::CPT.'.destroy';
                                                    $destroyRoute = (Route::has($destroyName) ? route($destroyName, $cid) : (Route::has(Str::kebab($destroyName)) ? route(Str::kebab($destroyName), $cid) : '#'));
                                                    $destroyGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CPT, 'competency_destroy_route_unavailable') ?: __('Failed to get competency delete route');
                                                    $deleteFormId = 'delete-form-'.($cid ?: 'na');
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method' => 'DELETE','url' => $destroyRoute,'id' => $deleteFormId,'data-url' => $destroyRoute,'data-guard-msg' => $destroyGuardMsg]) !!}
                                                        <a href="#" class="{{ VC::BT_SM_CT_PR }}" data-listener-alias="delete-competency" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            <div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}">
                                                <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                    <p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No competencies available') }}</p>
                                                </div>
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

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/competencies/index.js') }}"></script>
@endpush
