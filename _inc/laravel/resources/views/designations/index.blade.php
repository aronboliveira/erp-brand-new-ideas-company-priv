@php
    use App\Config\Constants\{ExtendingLayoutsConstants as EL, StacksConstants as ST, ViewsConstants as VW, ViewClassNamesConstants as VC, YieldingConstants as YW};
    use App\Models\{Department, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp
@extends(EL::ADM)
@section(YW::ADM_PG_TTL)
    {{ __('Manage Designation') }}
@endsection
@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Designation') }}</li>
@endsection
@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create designation')
            @php
                $dsgCreateBase = VW::DSG.'.create';
                $dsgCreateKeb  = Str::kebab($dsgCreateBase);
                $dsgCreateName = Route::has($dsgCreateBase) ? $dsgCreateBase : (Route::has($dsgCreateKeb) ? $dsgCreateKeb : null);
                $dsgCreateUrl  = $dsgCreateName ? route($dsgCreateName) : '#';
                $dsgCreateMsg  = Utility::fetchLinkMessage($lang, VW::DSG, 'create_designation_route_unavailable') ?? 'Create designation route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a id="designation-create-btn" href="{{ $dsgCreateUrl }}" data-url="{{ $dsgCreateUrl }}" data-guard-msg="{{ $dsgCreateMsg }}" data-sv-localized="true" data-ajax-popup="true" data-title="{{ __('Create New Designation') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-3">@include('layouts.hrm_setup')</div>
        <div class="col-9">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @if((is_array($designations) && count($designations)) || (($designations ?? null) instanceof Collection && $designations->isNotEmpty()))
                                    @foreach ($designations as $designation)
                                        @php
                                            $dep = Department::where('id', $designation->department_id)->first();
                                            $depName = !empty($dep) && isset($dep->name) ? $dep->name : __('No name available for department');
                                            $dsgName = !empty($designation->name) ? $designation->name : __('No name available for designation');
                                            $did = (string) ($designation->id ?? '');
                                        @endphp
                                        <tr>
                                            <td>{{ $depName }}</td>
                                            <td>{{ $dsgName }}</td>
                                            <td class="Action">
                                                <span>
                                                    @can('edit designation')
                                                        @php
                                                            $dsgEditBase = VW::DSG.'.edit';
                                                            $dsgEditKeb  = Str::kebab($dsgEditBase);
                                                            $dsgEditName = Route::has($dsgEditBase) ? $dsgEditBase : (Route::has($dsgEditKeb) ? $dsgEditKeb : null);
                                                            $dsgEditUrl  = ($dsgEditName && $did !== '') ? route($dsgEditName, [$did]) : '#';
                                                            $dsgEditMsg  = Utility::fetchLinkMessage($lang, VW::DSG, 'edit_designation_route_unavailable') ?? 'Edit designation route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a id="designation-edit-btn-{{ $did }}" href="{{ $dsgEditUrl }}" data-url="{{ $dsgEditUrl }}" data-guard-msg="{{ $dsgEditMsg }}" data-sv-localized="true" data-ajax-popup="true" data-title="{{ __('Edit Designation') }}" class="{{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete designation')
                                                        @php
                                                            $dsgDestroyBase = VW::DSG.'.destroy';
                                                            $dsgDestroyKeb  = Str::kebab($dsgDestroyBase);
                                                            $dsgDestroyName = Route::has($dsgDestroyBase) ? $dsgDestroyBase : (Route::has($dsgDestroyKeb) ? $dsgDestroyKeb : null);
                                                            $dsgDestroyUrl  = ($dsgDestroyName && $did !== '') ? route($dsgDestroyName, [$did]) : '#';
                                                            $dsgDestroyMsg  = Utility::fetchLinkMessage($lang, VW::DSG, 'destroy_designation_route_unavailable') ?? 'Delete designation route is unavailable. Please contact technical support or your domain administrator.';
                                                            $confirmTitle   = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                            $confirmBody    = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {{ Form::open(['method' => 'DELETE', 'url' => $dsgDestroyUrl, 'id' => 'delete-form-'.$did]) }}
                                                                <a id="delete-designation-btn-{{ $did }}" href="{{ $dsgDestroyUrl }}" data-url="{{ $dsgDestroyUrl }}" data-guard-msg="{{ $dsgDestroyMsg }}" data-sv-localized="true" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}" data-confirm-yes="document.getElementById('delete-form-{{ $did }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {{ Form::close() }}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="3">{{ __('No designations found.') }}</td>
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
    @can('create designation')
        <script defer src="{{ asset('assets/js/routes/designations/create.js') }}"></script>
    @endcan
    @can('edit designation')
        <script defer src="{{ asset('assets/js/routes/designations/edit.js') }}"></script>
    @endcan
    @can('delete designation')
        <script defer src="{{ asset('assets/js/routes/designations/destroy.js') }}"></script>
    @endcan
@endpush
