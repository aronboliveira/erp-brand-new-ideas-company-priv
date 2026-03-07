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
    use Illuminate\Support\Facades\{Route, URL};
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();

    $isList = fn($v) => (is_array($v ?? null) && count($v ?? [])) || (($v ?? null) instanceof Collection && $v->isNotEmpty());

    $psTypeIsList   = $isList($payslip_type);
    $psTypeOptions  = $psTypeIsList ? (is_array($payslip_type) ? $payslip_type : $payslip_type->toArray()) : ['' => __('No payslip types available')];
    $psTypeAttrs    = ['class' => VC::FM_CT, 'required' => 'required'] + ($psTypeIsList ? [] : ['disabled'=>'disabled']);

    $alwOptIsList   = $isList($allowance_options);
    $alwOptOptions  = $alwOptIsList ? (is_array($allowance_options) ? $allowance_options : $allowance_options->toArray()) : ['' => __('No allowance options available')];
    $alwOptAttrs    = ['class' => VC::FM_CT, 'required' => 'required'] + ($alwOptIsList ? [] : ['disabled'=>'disabled']);

    $loanOptIsList  = $isList($loan_options);
    $loanOptOptions = $loanOptIsList ? (is_array($loan_options) ? $loan_options : $loan_options->toArray()) : ['' => __('No loan options available')];
    $loanOptAttrs   = ['class' => VC::FM_CT, 'required' => 'required'] + ($loanOptIsList ? [] : ['disabled'=>'disabled']);

    $dedOptIsList   = $isList($deduction_options);
    $dedOptOptions  = $dedOptIsList ? (is_array($deduction_options) ? $deduction_options : $deduction_options->toArray()) : ['' => __('No deduction options available')];
    $dedOptAttrs    = ['class' => VC::FM_CT, 'required' => 'required'] + ($dedOptIsList ? [] : ['disabled'=>'disabled']);

    $allowancesIsList         = $isList($allowances);
    $commissionsIsList        = $isList($commissions);
    $loansIsList              = $isList($loans);
    $saturationdeductionsList = $isList($saturationdeductions);
    $otherpaymentsIsList      = $isList($otherpayments);
    $overtimesIsList          = $isList($overtimes);
@endphp

@extends(EL::ADM)

@section(YW::ADM_CTT)
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __('Employee Salary Pay Slip') }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">{{ __('Home') }}</a></div>
                    <div class="breadcrumb-item">{{ __('Employee Salary Pay Slip') }}</div>
                </div>
            </div>

            @csrf
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <div class="{{ VC::DFL_JCB }} w-100">
                                <h4>{{ __('Employee Salary Pay Slip') }}</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="setting-tab">
                                @php
                                    $tabs = [
                                        ['id' => 'home-tab3', 'href' => '#salary', 'text' => 'Salary'],
                                        ['id' => 'profile-tab3', 'href' => '#allowance', 'text' => 'Allowance'],
                                        ['href' => '#commission', 'text' => 'Commission'],
                                        ['href' => '#loan', 'text' => 'Loan'],
                                        ['href' => '#saturation-deduction', 'text' => 'Saturation Deduction'],
                                        ['href' => '#other-payment', 'text' => 'Other Payment'],
                                        ['href' => '#overtime', 'text' => 'Overtime'],
                                    ];
                                @endphp

                                <ul class="{{ VC::NAV_PL_Y3 }}" id="myTab3" role="tablist">
                                    @foreach ($tabs as $index => $tab)
                                        @php $id = $index < 2 ? $tab['id'] : 'contact-tab' . ($index + 1); @endphp
                                        <li class="{{ VC::NV_IT }}">
                                            <a class="{{ VC::NV_LK }} {{ $index === 0 ? 'active' : '' }}"
                                               id="{{ $id }}"
                                               data-toggle="tab"
                                               href="{{ $tab['href'] }}"
                                               role="tab"
                                               aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                                {{ __($tab['text']) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade show active" id="salary" role="tabpanel" aria-labelledby="salary-tab3">
                                        <div class="company-setting-wrap">
                                            @if(!empty($employee) && isset($employee->id))
                                                @php
                                                    $empIdStr             = (string) data_get($employee ?? null, 'id', '');

                                                    $empUpdateBase        = VW::EMP.'.update';
                                                    $empUpdateKebab       = Str::kebab($empUpdateBase);
                                                    $empUpdateResolved    = Route::has($empUpdateBase) ? $empUpdateBase : (Route::has($empUpdateKebab) ? $empUpdateKebab : null);
                                                    $empUpdateUrl         = ($empUpdateResolved && $empIdStr !== '') ? route($empUpdateResolved, $empIdStr) : '#';

                                                    $empUpdateFormId      = 'employee-update-form-'.($empIdStr !== '' ? $empIdStr : 'x');
                                                    $empUpdateGuardMsg    = Utility::fetchLinkMessage($lang, VW::EMP, 'update_employee_route_unavailable')
                                                                            ?? 'Update employee route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                {{ Form::model($employee, [
                                                    'url'               => $empUpdateUrl,
                                                    'method'            => 'PUT',
                                                    'enctype'           => 'multipart/form-data',
                                                    'id'                => $empUpdateFormId,
                                                    'data-url'          => $empUpdateUrl,
                                                    'data-guard-msg'    => $empUpdateGuardMsg,
                                                    'data-sv-localized' => 'true',
                                                ]) }}
                                                    <div class="{{ VC::RW }}">
                                                        <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                            <div class="{{ VC::FM_G }}">
                                                                {{ Form::label('salary_type', __('Payslip Type*'), ['class'=>VC::FM_LB]) }}
                                                                {{ Form::select('salary_type', $psTypeOptions, null, $psTypeAttrs) }}
                                                            </div>
                                                        </div>
                                                        <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                            <div class="{{ VC::FM_G }}">
                                                                {{ Form::label('salary', __('Salary'), ['class'=>VC::FM_LB]) }}
                                                                {{ Form::number('salary', null, ['class'=>VC::FM_CT, 'required'=>'required']) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="{{ VC::RW }}">
                                                        <div class="{{ VC::C12 }} text-end mt-1">
                                                            {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                        </div>
                                                    </div>
                                                    <script defer src="{{ asset('assets/js/routes/employees/update.js') }}"></script>
                                                {{ Form::close() }}
                                                <div class="tab-pane fade" id="allowance" role="tabpanel" aria-labelledby="allowance-tab3">
                                                    <div class="company-setting-wrap">
                                                        @php
                                                            $alwStoreBase         = VW::ALW;
                                                            $alwStoreKebab        = Str::kebab($alwStoreBase);
                                                            $alwStoreResolved     = Route::has($alwStoreBase) ? $alwStoreBase : (Route::has($alwStoreKebab) ? $alwStoreKebab : null);
                                                            $alwStoreUrl          = $alwStoreResolved ? route($alwStoreResolved) : '#';

                                                            $alwFormId            = 'allowance-store-form-'.((string)($employee->id ?? 'x'));
                                                            $alwGuardMsg          = Utility::fetchLinkMessage($lang, VW::ALW, 'allowance_store_route_unavailable') ?? 'Store allowance route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        {{ Form::open([
                                                            'url'               => $alwStoreUrl,
                                                            'method'            => 'POST',
                                                            'id'                => $alwFormId,
                                                            'data-url'          => $alwStoreUrl,
                                                            'data-guard-msg'    => $alwGuardMsg,
                                                            'data-sv-localized' => 'true',
                                                        ]) }}
                                                            @csrf
                                                            {{ Form::hidden('employee_id', $employee->id) }}

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('allowance_option', __('Allowance Options*'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::select('allowance_option', $alwOptOptions, null, $alwOptAttrs) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('title', __('Title'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('title', null, ['class'=>VC::FM_CT, 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('amount', __('Amount'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('amount', null, ['class'=>VC::FM_CT, 'required'=>'required', 'step'=>'any']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }} text-end mt-1">
                                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                                </div>
                                                            </div>

                                                            <script defer src="{{ asset('assets/js/routes/payslips/allowanceStore.js') }}"></script>
                                                        {{ Form::close() }}
                                                        <hr>
                                                        <div class="table-responsive">
                                                            <table class="{{ VC::TB }} table-striped mb-0" id="allowance-dataTable">
                                                                <thead>
                                                                <tr>
                                                                    <th>{{ __('Employee Name') }}</th>
                                                                    <th>{{ __('Allowance Option') }}</th>
                                                                    <th>{{ __('Title') }}</th>
                                                                    <th>{{ __('Amount') }}</th>
                                                                    <th class="text-end" width="200px">{{ __('Action') }}</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @if($allowancesIsList)
                                                                    @foreach ($allowances as $allowance)
                                                                        <tr>
                                                                            <td>{{ data_get($allowance, 'employee.name', __('Employee Name unavailable')) }}</td>
                                                                            <td>{{ data_get($allowance, 'allowance_option.name', __('Option unavailable')) }}</td>
                                                                            <td>{{ data_get($allowance, 'title', __('Title unavailable')) }}</td>
                                                                            <td>{{ data_get($allowance, 'amount', __('Amount unavailable')) }}</td>
                                                                            @php
                                                                                $alwIdStr          = (string) data_get($allowance ?? null, 'id', '');
                                                                            @endphp

                                                                            <td class="text-end">
                                                                                @can('edit allowance')
                                                                                    @php
                                                                                        $editBase          = VW::ALW.'.edit';
                                                                                        $editKebab         = Str::kebab($editBase);
                                                                                        $editResolved      = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                                                        $editUrl           = ($editResolved && $alwIdStr !== '') ? route($editResolved, $alwIdStr) : '#';
                                                                                        $editGuardMsg      = Utility::fetchLinkMessage($lang, VW::ALW, 'allowance_edit_route_unavailable') ?? 'Edit allowance route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        $editLinkId        = 'allowance-edit-btn-'.($alwIdStr !== '' ? $alwIdStr : 'x');
                                                                                    @endphp
                                                                                    <a
                                                                                        id="{{ $editLinkId }}"
                                                                                        href="{{ $editUrl }}"
                                                                                        data-url="{{ $editUrl }}"
                                                                                        data-size="lg"
                                                                                        data-ajax-popup="true"
                                                                                        data-title="{{ __('Edit Allowance') }}"
                                                                                        data-guard-msg="{{ $editGuardMsg }}"
                                                                                        data-sv-localized="true"
                                                                                        class="btn btn-outline-primary btn-sm mr-1"
                                                                                        data-bs-toggle="tooltip"
                                                                                        title="{{ __('Edit') }}"
                                                                                        {{ $editUrl === '#' ? 'aria-disabled=true' : '' }}
                                                                                    >
                                                                                        <i class="{{ VC::TI_PC_WT }}"></i> <span>{{ __('Edit') }}</span>
                                                                                    </a>
                                                                                @endcan

                                                                                @can('delete allowance')
                                                                                    @php
                                                                                        $destroyBase       = VW::ALW.'.destroy';
                                                                                        $destroyKebab      = Str::kebab($destroyBase);
                                                                                        $destroyResolved   = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                                                        $destroyUrl        = ($destroyResolved && $alwIdStr !== '') ? route($destroyResolved, $alwIdStr) : '#';
                                                                                        $formId            = 'allowance-delete-form-'.($alwIdStr !== '' ? $alwIdStr : 'x');
                                                                                        $deleteLinkId      = 'allowance-delete-link-'.($alwIdStr !== '' ? $alwIdStr : 'x');
                                                                                        $areYouSure        = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                                                        $irreversible      = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                                                        $destroyGuardMsg   = Utility::fetchLinkMessage($lang, VW::ALW, 'allowance_destroy_route_unavailable') ?? 'Delete allowance route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    @endphp
                                                                                    <a
                                                                                        id="{{ $deleteLinkId }}"
                                                                                        href="#"
                                                                                        class="btn btn-outline-danger btn-sm"
                                                                                        data-bs-toggle="tooltip"
                                                                                        title="{{ __('Delete') }}"
                                                                                        data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                                                        data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                                                        data-url="{{ $destroyUrl }}"
                                                                                        data-guard-msg="{{ $destroyGuardMsg }}"
                                                                                        data-sv-localized="true"
                                                                                    >
                                                                                        <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                                                    </a>

                                                                                    {{ Form::open([
                                                                                        'method'            => 'DELETE',
                                                                                        'url'               => $destroyUrl,
                                                                                        'id'                => $formId,
                                                                                        'data-url'          => $destroyUrl,
                                                                                        'data-guard-msg'    => $destroyGuardMsg,
                                                                                        'data-sv-localized' => 'true',
                                                                                    ]) }}
                                                                                    {{ Form::close() }}
                                                                                @endcan
                                                                            </td>

                                                                            @push(ST::ADM_SCR_PG)
                                                                                <script defer src="{{ asset('assets/js/routes/payslips/allowanceEdit.js') }}"></script>
                                                                                <script defer src="{{ asset('assets/js/routes/payslips/allowancesDestroy.js') }}"></script>
                                                                            @endpush

                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <tr>
                                                                        <td colspan="5" class="text-center text-muted">{{ __('No allowances found') }}</td>
                                                                    </tr>
                                                                @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $comStoreBase    = VW::COM;
                                                    $comStoreKebab   = Str::kebab($comStoreBase);
                                                    $comStoreName    = Route::has($comStoreBase) ? $comStoreBase : (Route::has($comStoreKebab) ? $comStoreKebab : null);
                                                    $comStoreUrl     = $comStoreName ? route($comStoreName) : '#';
                                                    $comStoreFormId  = 'commission-store-form-'.(string)($employee->id ?? 'x');
                                                    $comStoreGuard   = Utility::fetchLinkMessage($lang, VW::COM, 'commission_store_route_unavailable') ?? 'Store commission route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="tab-pane fade" id="commission" role="tabpanel" aria-labelledby="commission-tab3">
                                                    <div class="email-setting-wrap">
                                                        {{ Form::open([
                                                            'url'               => $comStoreUrl,
                                                            'method'            => 'POST',
                                                            'id'                => $comStoreFormId,
                                                            'data-url'          => $comStoreUrl,
                                                            'data-guard-msg'    => $comStoreGuard,
                                                            'data-sv-localized' => 'true',
                                                        ]) }}
                                                            @csrf
                                                            {{ Form::hidden('employee_id', $employee->id) }}
                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('title', __('Title'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('title', null, ['class'=>VC::FM_CT, 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('amount', __('Amount'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('amount', null, ['class'=>VC::FM_CT, 'required'=>'required', 'step'=>'any']) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }} text-end mt-1">
                                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                                </div>
                                                            </div>
                                                        {{ Form::close() }}

                                                        <hr>

                                                        <div class="table-responsive">
                                                            <table class="{{ VC::TB }} table-striped mb-0" id="commission-dataTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('Employee Name') }}</th>
                                                                        <th>{{ __('Title') }}</th>
                                                                        <th>{{ __('Amount') }}</th>
                                                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @if($commissionsIsList)
                                                                        @foreach ($commissions as $commission)
                                                                            <tr>
                                                                                <td>{{ data_get($commission, 'employee.name', __('Employee Name unavailable')) }}</td>
                                                                                <td>{{ data_get($commission, 'title', __('Title unavailable')) }}</td>
                                                                                <td>{{ data_get($commission, 'amount', __('Amount unavailable')) }}</td>
                                                                                <td class="text-end">
                                                                                    @can('edit commission')
                                                                                        @php
                                                                                            $comEditBase   = VW::COM.'.edit';
                                                                                            $comEditKebab  = Str::kebab($comEditBase);
                                                                                            $comEditName   = Route::has($comEditBase) ? $comEditBase : (Route::has($comEditKebab) ? $comEditKebab : null);
                                                                                            $comIdStr      = (string) data_get($commission,'id','');
                                                                                            $comEditUrl    = ($comEditName && $comIdStr !== '') ? route($comEditName, $comIdStr) : '#';
                                                                                            $comEditGuard  = Utility::fetchLinkMessage($lang, VW::COM, 'commission_edit_route_unavailable') ?? 'Edit commission route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            $editLinkId    = 'commission-edit-link-'.$comIdStr;
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $editLinkId }}"
                                                                                            href="{{ $comEditUrl }}"
                                                                                            data-url="{{ $comEditUrl }}"
                                                                                            data-size="lg"
                                                                                            data-ajax-popup="true"
                                                                                            data-title="{{ __('Edit Allowance') }}"
                                                                                            data-guard-msg="{{ $comEditGuard }}"
                                                                                            data-sv-localized="true"
                                                                                            class="btn btn-outline-primary btn-sm mr-1"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Edit') }}"
                                                                                            {{ $comEditUrl === '#' ? 'aria-disabled=true' : '' }}
                                                                                        >
                                                                                            <i class="{{ VC::TI_PC_WT }}"></i> <span>{{ __('Edit') }}</span>
                                                                                        </a>
                                                                                    @endcan

                                                                                    @can('delete comission')
                                                                                        @php
                                                                                            $comDestroyBase   = VW::COM.'.destroy';
                                                                                            $comDestroyKebab  = Str::kebab($comDestroyBase);
                                                                                            $comDestroyName   = Route::has($comDestroyBase) ? $comDestroyBase : (Route::has($comDestroyKebab) ? $comDestroyKebab : null);
                                                                                            $comIdStr         = (string) data_get($commission,'id','');
                                                                                            $comDestroyUrl    = ($comDestroyName && $comIdStr !== '') ? route($comDestroyName, $comIdStr) : '#';
                                                                                            $formId           = 'commission-delete-form-'.$comIdStr;
                                                                                            $deleteLinkId     = 'commission-delete-link-'.$comIdStr;
                                                                                            $areYouSure       = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                                                            $irreversible     = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                                                            $comDestroyGuard  = Utility::fetchLinkMessage($lang, VW::COM, 'commission_destroy_route_unavailable') ?? 'Delete commission route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $deleteLinkId }}"
                                                                                            href="#"
                                                                                            class="btn btn-outline-danger btn-sm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Delete') }}"
                                                                                            data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                                                            data-url="{{ $comDestroyUrl }}"
                                                                                            data-guard-msg="{{ $comDestroyGuard }}"
                                                                                            data-sv-localized="true"
                                                                                        >
                                                                                            <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                                                        </a>
                                                                                        {{ Form::open([
                                                                                            'method'            => 'DELETE',
                                                                                            'url'               => $comDestroyUrl,
                                                                                            'id'                => $formId,
                                                                                            'data-url'          => $comDestroyUrl,
                                                                                            'data-guard-msg'    => $comDestroyGuard,
                                                                                            'data-sv-localized' => 'true',
                                                                                        ]) }}
                                                                                        {{ Form::close() }}
                                                                                    @endcan
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        <tr>
                                                                            <td colspan="4" class="text-center text-muted">{{ __('No commissions found') }}</td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/commissionStore.js') }}"></script>
                                                    @can('edit commission')
                                                        <script defer src="{{ asset('assets/js/routes/commissionEdit.js') }}"></script>
                                                    @endcan
                                                    @can('delete commission')
                                                        <script defer src="{{ asset('assets/js/routes/commissionDestroy.js') }}"></script>
                                                    @endcan
                                                @endpush
                                                @php
                                                    $loanStoreBase     = VW::LN;
                                                    $loanStoreKebab    = Str::kebab($loanStoreBase);
                                                    $loanStoreName     = Route::has($loanStoreBase) ? $loanStoreBase : (Route::has($loanStoreKebab) ? $loanStoreKebab : null);
                                                    $loanStoreUrl      = $loanStoreName ? route($loanStoreName) : '#';
                                                    $loanStoreFormId   = 'loan-store-form-'.(string)($employee->id ?? 'x');
                                                    $loanStoreGuard    = Utility::fetchLinkMessage($lang, VW::LN, 'loan_store_route_unavailable') ?? 'Store loan route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="tab-pane fade" id="loan" role="tabpanel" aria-labelledby="loan-tab4">
                                                    <div class="email-setting-wrap">
                                                        {{ Form::open([
                                                            'url'               => $loanStoreUrl,
                                                            'method'            => 'POST',
                                                            'id'                => $loanStoreFormId,
                                                            'data-url'          => $loanStoreUrl,
                                                            'data-guard-msg'    => $loanStoreGuard,
                                                            'data-sv-localized' => 'true',
                                                        ]) }}
                                                            @csrf
                                                            {{ Form::hidden('employee_id', $employee->id) }}

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM4 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('loan_option', __('Loan Options*'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::select('loan_option', $loanOptOptions, null, $loanOptAttrs) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM4 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('title', __('Title'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('title', null, ['class'=>VC::FM_CT, 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM4 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('amount', __('Loan Amount'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('amount', null, ['class'=>VC::FM_CT, 'required'=>'required', 'step'=>'any']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('start_date', __('Start Date'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('start_date', null, ['class'=>VC::FM_CT.' datepicker', 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('end_date', __('End Date'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('end_date', null, ['class'=>VC::FM_CT.' datepicker', 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('reason', __('Reason'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::textarea('reason', null, ['class'=>VC::FM_CT, 'required'=>'required']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }} text-end mt-1">
                                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                                </div>
                                                            </div>

                                                            <script defer src="{{ asset('assets/js/routes/loans/store.js') }}"></script>
                                                        {{ Form::close() }}

                                                        <hr>

                                                        <div class="table-responsive">
                                                            <table class="{{ VC::TB }} table-striped mb-0" id="loan-dataTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('Employee') }}</th>
                                                                        <th>{{ __('Loan Options') }}</th>
                                                                        <th>{{ __('Title') }}</th>
                                                                        <th>{{ __('Loan Amount') }}</th>
                                                                        <th>{{ __('Start Date') }}</th>
                                                                        <th>{{ __('End Date') }}</th>
                                                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @if($loansIsList)
                                                                        @foreach ($loans as $loan)
                                                                            <tr>
                                                                                <td>{{ data_get($loan, 'employee.name', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($loan, 'loan_option.name', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($loan, 'title', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($loan, 'amount', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($loan, 'start_date', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($loan, 'end_date', __('Data unavailable')) }}</td>
                                                                                <td class="text-end">
                                                                                    @can('edit loan')
                                                                                        @php
                                                                                            $loanEditBase   = VW::LN.'.edit';
                                                                                            $loanEditKebab  = Str::kebab($loanEditBase);
                                                                                            $loanEditName   = Route::has($loanEditBase) ? $loanEditBase : (Route::has($loanEditKebab) ? $loanEditKebab : null);
                                                                                            $loanIdStr      = (string) data_get($loan,'id','');
                                                                                            $loanEditUrl    = ($loanEditName && $loanIdStr !== '') ? route($loanEditName, $loanIdStr) : '#';
                                                                                            $loanEditGuard  = Utility::fetchLinkMessage($lang, VW::LN, 'loan_edit_route_unavailable') ?? 'Edit loan route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            $loanEditLinkId = 'loan-edit-link-'.$loanIdStr;
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $loanEditLinkId }}"
                                                                                            href="{{ $loanEditUrl }}"
                                                                                            data-url="{{ $loanEditUrl }}"
                                                                                            data-size="lg"
                                                                                            data-ajax-popup="true"
                                                                                            data-title="{{ __('Edit Allowance') }}"
                                                                                            data-guard-msg="{{ $loanEditGuard }}"
                                                                                            data-sv-localized="true"
                                                                                            class="btn btn-outline-primary btn-sm mr-1"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Edit') }}"
                                                                                            {{ $loanEditUrl === '#' ? 'aria-disabled=true' : '' }}
                                                                                        >
                                                                                            <i class="{{ VC::TI_PC_WT }}"></i> <span>{{ __('Edit') }}</span>
                                                                                        </a>
                                                                                    @endcan

                                                                                    @can('delete loan')
                                                                                        @php
                                                                                            $loanDestroyBase   = VW::LN.'.destroy';
                                                                                            $loanDestroyKebab  = Str::kebab($loanDestroyBase);
                                                                                            $loanDestroyName   = Route::has($loanDestroyBase) ? $loanDestroyBase : (Route::has($loanDestroyKebab) ? $loanDestroyKebab : null);
                                                                                            $loanIdStr         = (string) data_get($loan,'id','');
                                                                                            $loanDestroyUrl    = ($loanDestroyName && $loanIdStr !== '') ? route($loanDestroyName, $loanIdStr) : '#';
                                                                                            $loanFormId        = 'loan-delete-form-'.$loanIdStr;
                                                                        $areYouSure = Utility::fetchLinkMessage($lang,'generics','are_you_sure') ?? 'Are You Sure?';
                                                                        $irreversible = Utility::fetchLinkMessage($lang,'generics','irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                                                            $loanDestroyGuard  = Utility::fetchLinkMessage($lang, VW::LN, 'loan_destroy_route_unavailable') ?? 'Delete loan route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        @endphp
                                                                                        <a
                                                                                            id="loan-delete-link-{{ $loanIdStr }}"
                                                                                            href="#"
                                                                                            class="btn btn-outline-danger btn-sm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Delete') }}"
                                                                                            data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                                                            data-confirm-yes="document.getElementById('{{ $loanFormId }}').submit();"
                                                                                            data-url="{{ $loanDestroyUrl }}"
                                                                                            data-guard-msg="{{ $loanDestroyGuard }}"
                                                                                            data-sv-localized="true"
                                                                                        >
                                                                                            <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                                                        </a>
                                                                                        {{ Form::open([
                                                                                            'method'            => 'DELETE',
                                                                                            'url'               => $loanDestroyUrl,
                                                                                            'id'                => $loanFormId,
                                                                                            'data-url'          => $loanDestroyUrl,
                                                                                            'data-guard-msg'    => $loanDestroyGuard,
                                                                                            'data-sv-localized' => 'true',
                                                                                        ]) }}
                                                                                        {{ Form::close() }}
                                                                                    @endcan
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        <tr>
                                                                            <td colspan="7" class="text-center text-muted">{{ __('No loans found') }}</td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/payslips/loanStore.js') }}"></script>
                                                    @can('edit loan')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/loanEdit.js') }}"></script>
                                                    @endcan
                                                    @can('delete loan')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/loanDestroy.js') }}"></script>
                                                    @endcan
                                                @endpush
                                                @php
                                                    $satStoreBase      = VW::STR_DD;
                                                    $satStoreKebab     = Str::kebab($satStoreBase);
                                                    $satStoreResolved  = Route::has($satStoreBase) ? $satStoreBase : (Route::has($satStoreKebab) ? $satStoreKebab : null);
                                                    $satStoreUrl       = $satStoreResolved ? route($satStoreResolved) : '#';
                                                    $satFormId         = 'saturation-deduction-store-form-'.((string)($employee->id ?? 'x'));
                                                    $satGuardStore     = Utility::fetchLinkMessage($lang, VW::STR_DD, 'saturation_deduction_store_route_unavailable') ?? 'Store saturation deduction route is unavailable. Please contact technical support or your domain administrator.';

                                                    $othStoreBase      = VW::OT_PAY;
                                                    $othStoreKebab     = Str::kebab($othStoreBase);
                                                    $othStoreResolved  = Route::has($othStoreBase) ? $othStoreBase : (Route::has($othStoreKebab) ? $othStoreKebab : null);
                                                    $othStoreUrl       = $othStoreResolved ? route($othStoreResolved) : '#';
                                                    $othFormId         = 'other-payment-store-form-'.((string)($employee->id ?? 'x'));
                                                    $othGuardStore     = Utility::fetchLinkMessage($lang, VW::OT_PAY, 'other_payment_store_route_unavailable') ?? 'Store other payment route is unavailable. Please contact technical support or your domain administrator.';

                                                    $ovtStoreBase      = VW::OVT;
                                                    $ovtStoreKebab     = Str::kebab($ovtStoreBase);
                                                    $ovtStoreResolved  = Route::has($ovtStoreBase) ? $ovtStoreBase : (Route::has($ovtStoreKebab) ? $ovtStoreKebab : null);
                                                    $ovtStoreUrl       = $ovtStoreResolved ? route($ovtStoreResolved) : '#';
                                                    $ovtFormId         = 'overtime-store-form-'.((string)($employee->id ?? 'x'));
                                                    $ovtGuardStore     = Utility::fetchLinkMessage($lang, VW::OVT, 'overtime_store_route_unavailable') ?? 'Store overtime route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="tab-pane fade" id="saturation-deduction" role="tabpanel" aria-labelledby="saturation-deduction-tab3">
                                                    <div class="email-setting-wrap">
                                                        {{ Form::open([
                                                            'url'               => $satStoreUrl,
                                                            'method'            => 'POST',
                                                            'id'                => $satFormId,
                                                            'data-url'          => $satStoreUrl,
                                                            'data-guard-msg'    => $satGuardStore,
                                                            'data-sv-localized' => 'true',
                                                        ]) }}
                                                            @csrf
                                                            {{ Form::hidden('employee_id', $employee->id) }}

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('deduction_option', __('Deduction Options*'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::select('deduction_option', $dedOptOptions, null, $dedOptAttrs) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('title', __('Title'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('title', null, ['class'=>VC::FM_CT, 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('amount', __('Amount'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('amount', null, ['class'=>VC::FM_CT, 'required'=>'required', 'step'=>'any']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }} text-end mt-1">
                                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                                </div>
                                                            </div>
                                                        {{ Form::close() }}
                                                        <hr>
                                                        <div class="table-responsive">
                                                            <table class="{{ VC::TB }} table-striped mb-0" id="saturation-deduction-dataTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('Employee Name') }}</th>
                                                                        <th>{{ __('Deduction Option') }}</th>
                                                                        <th>{{ __('Title') }}</th>
                                                                        <th>{{ __('Amount') }}</th>
                                                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @if($saturationdeductionsList)
                                                                        @foreach ($saturationdeductions as $saturationdeduction)
                                                                            @php
                                                                                $satIdStr = (string) data_get($saturationdeduction, 'id', '');
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ data_get($saturationdeduction, 'employee.name', __('Employee Name unavailable')) }}</td>
                                                                                <td>{{ data_get($saturationdeduction, 'deduction_option.name', __('Deduction Option unavailable')) }}</td>
                                                                                <td>{{ data_get($saturationdeduction, 'title', __('Title unavailable')) }}</td>
                                                                                <td>{{ data_get($saturationdeduction, 'amount', __('Amount unavailable')) }}</td>
                                                                                <td class="text-end">
                                                                                    @can('edit saturation deduction')
                                                                                        @php
                                                                                            $satEditBase   = VW::STR_DD.'.edit';
                                                                                            $satEditKebab  = Str::kebab($satEditBase);
                                                                                            $satEditName   = Route::has($satEditBase) ? $satEditBase : (Route::has($satEditKebab) ? $satEditKebab : null);
                                                                                            $satEditUrl    = ($satEditName && $satIdStr !== '') ? route($satEditName, $satIdStr) : '#';
                                                                                            $satEditGuard  = Utility::fetchLinkMessage($lang, VW::STR_DD, 'saturation_deduction_edit_route_unavailable') ?? 'Edit saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            $satEditLinkId = 'saturation-deduction-edit-link-'.$satIdStr;
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $satEditLinkId }}"
                                                                                            href="{{ $satEditUrl }}"
                                                                                            data-url="{{ $satEditUrl }}"
                                                                                            data-size="lg"
                                                                                            data-ajax-popup="true"
                                                                                            data-title="{{ __('Edit Allowance') }}"
                                                                                            data-guard-msg="{{ $satEditGuard }}"
                                                                                            data-sv-localized="true"
                                                                                            class="btn btn-outline-primary btn-sm mr-1"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Edit') }}"
                                                                                            {{ $satEditUrl === '#' ? 'aria-disabled=true' : '' }}
                                                                                        >
                                                                                            <i class="{{ VC::TI_PC_WT }}"></i> <span>{{ __('Edit') }}</span>
                                                                                        </a>
                                                                                    @endcan

                                                                                    @can('delete saturation deduction')
                                                                                        @php
                                                                                            $satDestroyBase   = VW::STR_DD.'.destroy';
                                                                                            $satDestroyKebab  = Str::kebab($satDestroyBase);
                                                                                            $satDestroyName   = Route::has($satDestroyBase) ? $satDestroyBase : (Route::has($satDestroyKebab) ? $satDestroyKebab : null);
                                                                                            $satDestroyUrl    = ($satDestroyName && $satIdStr !== '') ? route($satDestroyName, $satIdStr) : '#';
                                                                                            $satFormDelId     = 'saturation-deduction-delete-form-'.$satIdStr;
                                                                                            $satDelLinkId     = 'saturation-deduction-delete-link-'.$satIdStr;
                                                                                            $areYouSure       = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                                                            $irreversible     = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                                                            $satDestroyGuard  = Utility::fetchLinkMessage($lang, VW::STR_DD, 'saturation_deduction_destroy_route_unavailable') ?? 'Delete saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $satDelLinkId }}"
                                                                                            href="#"
                                                                                            class="btn btn-outline-danger btn-sm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Delete') }}"
                                                                                            data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                                                            data-confirm-yes="document.getElementById('{{ $satFormDelId }}').submit();"
                                                                                            data-url="{{ $satDestroyUrl }}"
                                                                                            data-guard-msg="{{ $satDestroyGuard }}"
                                                                                            data-sv-localized="true"
                                                                                        >
                                                                                            <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                                                        </a>
                                                                                        {{ Form::open([
                                                                                            'method'            => 'DELETE',
                                                                                            'url'               => $satDestroyUrl,
                                                                                            'id'                => $satFormDelId,
                                                                                            'data-url'          => $satDestroyUrl,
                                                                                            'data-guard-msg'    => $satDestroyGuard,
                                                                                            'data-sv-localized' => 'true',
                                                                                        ]) }}
                                                                                        {{ Form::close() }}
                                                                                    @endcan
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        <tr>
                                                                            <td colspan="5" class="text-center text-muted">{{ __('No saturation deductions found') }}</td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="other-payment" role="tabpanel" aria-labelledby="other-payment-tab4">
                                                    <div class="email-setting-wrap">
                                                        {{ Form::open([
                                                            'url'               => $othStoreUrl,
                                                            'method'            => 'POST',
                                                            'id'                => $othFormId,
                                                            'data-url'          => $othStoreUrl,
                                                            'data-guard-msg'    => $othGuardStore,
                                                            'data-sv-localized' => 'true',
                                                        ]) }}
                                                            @csrf
                                                            {{ Form::hidden('employee_id', $employee->id) }}

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('title', __('Title'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('title', null, ['class'=>VC::FM_CT, 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('amount', __('Amount'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('amount', null, ['class'=>VC::FM_CT, 'required'=>'required', 'step'=>'any']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }} text-end mt-1">
                                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                                </div>
                                                            </div>

                                                            <script defer src="{{ asset('assets/js/routes/payslips/otherPayments/store.js') }}"></script>
                                                        {{ Form::close() }}

                                                        <hr>

                                                        <div class="table-responsive">
                                                            <table class="{{ VC::TB }} table-striped mb-0" id="other-payment-dataTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('Employee') }}</th>
                                                                        <th>{{ __('Title') }}</th>
                                                                        <th>{{ __('Amount') }}</th>
                                                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @if($otherpaymentsIsList)
                                                                        @foreach ($otherpayments as $otherpayment)
                                                                            @php $othIdStr = (string) data_get($otherpayment, 'id', ''); @endphp
                                                                            <tr>
                                                                                <td>{{ data_get($otherpayment, 'employee.name', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($otherpayment, 'title', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($otherpayment, 'amount', __('Data unavailable')) }}</td>
                                                                                <td class="text-end">
                                                                                    @can('edit other payment')
                                                                                        @php
                                                                                            $othEditBase   = VW::OT_PAY.'.edit';
                                                                                            $othEditKebab  = Str::kebab($othEditBase);
                                                                                            $othEditName   = Route::has($othEditBase) ? $othEditBase : (Route::has($othEditKebab) ? $othEditKebab : null);
                                                                                            $othEditUrl    = ($othEditName && $othIdStr !== '') ? route($othEditName, $othIdStr) : '#';
                                                                                            $othEditGuard  = Utility::fetchLinkMessage($lang, VW::OT_PAY, 'other_payment_edit_route_unavailable') ?? 'Edit other payment route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            $othEditLinkId = 'other-payment-edit-link-'.$othIdStr;
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $othEditLinkId }}"
                                                                                            href="{{ $othEditUrl }}"
                                                                                            data-url="{{ $othEditUrl }}"
                                                                                            data-size="lg"
                                                                                            data-ajax-popup="true"
                                                                                            data-title="{{ __('Edit Allowance') }}"
                                                                                            data-guard-msg="{{ $othEditGuard }}"
                                                                                            data-sv-localized="true"
                                                                                            class="btn btn-outline-primary btn-sm mr-1"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Edit') }}"
                                                                                            {{ $othEditUrl === '#' ? 'aria-disabled=true' : '' }}
                                                                                        >
                                                                                            <i class="{{ VC::TI_PC_WT }}"></i> <span>{{ __('Edit') }}</span>
                                                                                        </a>
                                                                                    @endcan

                                                                                    @can('delete other payment')
                                                                                        @php
                                                                                            $othDestroyBase   = VW::OT_PAY.'.destroy';
                                                                                            $othDestroyKebab  = Str::kebab($othDestroyBase);
                                                                                            $othDestroyName   = Route::has($othDestroyBase) ? $othDestroyBase : (Route::has($othDestroyKebab) ? $othDestroyKebab : null);
                                                                                            $othDestroyUrl    = ($othDestroyName && $othIdStr !== '') ? route($othDestroyName, $othIdStr) : '#';
                                                                                            $othFormDelId     = 'other-payment-delete-form-'.$othIdStr;
                                                                                            $othDelLinkId     = 'other-payment-delete-link-'.$othIdStr;
                                                                                            $areYouSure       = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                                                            $irreversible     = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                                                            $othDestroyGuard  = Utility::fetchLinkMessage($lang, VW::OT_PAY, 'other_payment_destroy_route_unavailable') ?? 'Delete other payment route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $othDelLinkId }}"
                                                                                            href="#"
                                                                                            class="btn btn-outline-danger btn-sm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Delete') }}"
                                                                                            data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                                                            data-confirm-yes="document.getElementById('{{ $othFormDelId }}').submit();"
                                                                                            data-url="{{ $othDestroyUrl }}"
                                                                                            data-guard-msg="{{ $othDestroyGuard }}"
                                                                                            data-sv-localized="true"
                                                                                        >
                                                                                            <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                                                        </a>
                                                                                        {{ Form::open([
                                                                                            'method'            => 'DELETE',
                                                                                            'url'               => $othDestroyUrl,
                                                                                            'id'                => $othFormDelId,
                                                                                            'data-url'          => $othDestroyUrl,
                                                                                            'data-guard-msg'    => $othDestroyGuard,
                                                                                            'data-sv-localized' => 'true',
                                                                                        ]) }}
                                                                                        {{ Form::close() }}
                                                                                    @endcan
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        <tr>
                                                                            <td colspan="4" class="text-center text-muted">{{ __('No other payments found') }}</td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="overtime" role="tabpanel" aria-labelledby="overtime-tab4">
                                                    <div class="email-setting-wrap">
                                                        {{ Form::open([
                                                            'url'               => $ovtStoreUrl,
                                                            'method'            => 'POST',
                                                            'id'                => $ovtFormId,
                                                            'data-url'          => $ovtStoreUrl,
                                                            'data-guard-msg'    => $ovtGuardStore,
                                                            'data-sv-localized' => 'true',
                                                        ]) }}
                                                            @csrf
                                                            {{ Form::hidden('employee_id', $employee->id) }}

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('title', __('Overtime Title*'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::text('title', null, ['class'=>VC::FM_CT, 'required'=>'required', 'autocomplete'=>'off']) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('number_of_days', __('Number of days'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('number_of_days', null, ['class'=>VC::FM_CT, 'required'=>'required']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('hours', __('Hours'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('hours', null, ['class'=>VC::FM_CT, 'required'=>'required']) }}
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM6 }} {{ VC::C12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        {{ Form::label('rate', __('Rate'), ['class'=>VC::FM_LB]) }}
                                                                        {{ Form::number('rate', null, ['class'=>VC::FM_CT, 'required'=>'required', 'step'=>'any']) }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::RW }}">
                                                                <div class="{{ VC::C12 }} text-end mt-1">
                                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => VC::BT_PRM]) }}
                                                                </div>
                                                            </div>

                                                            <script defer src="{{ asset('assets/js/routes/payslips/overtimes/store.js') }}"></script>
                                                        {{ Form::close() }}

                                                        <hr>

                                                        <div class="table-responsive">
                                                            <table class="{{ VC::TB }} table-striped mb-0" id="overtime-dataTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('Employee Name') }}</th>
                                                                        <th>{{ __('Overtime Title') }}</th>
                                                                        <th>{{ __('Number of days') }}</th>
                                                                        <th>{{ __('Hours') }}</th>
                                                                        <th>{{ __('Rate') }}</th>
                                                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @if($overtimesIsList)
                                                                        @foreach ($overtimes as $overtime)
                                                                            @php $ovtIdStr = (string) data_get($overtime, 'id', ''); @endphp
                                                                            <tr>
                                                                                <td>{{ data_get($overtime, 'employee.name', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($overtime, 'title', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($overtime, 'number_of_days', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($overtime, 'hours', __('Data unavailable')) }}</td>
                                                                                <td>{{ data_get($overtime, 'rate', __('Data unavailable')) }}</td>
                                                                                <td class="text-end">
                                                                                    @can('edit allowance')
                                                                                        @php
                                                                                            $ovtEditBase   = VW::OVT.'.edit';
                                                                                            $ovtEditKebab  = Str::kebab($ovtEditBase);
                                                                                            $ovtEditName   = Route::has($ovtEditBase) ? $ovtEditBase : (Route::has($ovtEditKebab) ? $ovtEditKebab : null);
                                                                                            $ovtEditUrl    = ($ovtEditName && $ovtIdStr !== '') ? route($ovtEditName, $ovtIdStr) : '#';
                                                                                            $ovtEditGuard  = Utility::fetchLinkMessage($lang, VW::OVT, 'overtime_edit_route_unavailable') ?? 'Edit overtime route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            $ovtEditLinkId = 'overtime-edit-link-'.$ovtIdStr;
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $ovtEditLinkId }}"
                                                                                            href="{{ $ovtEditUrl }}"
                                                                                            data-url="{{ $ovtEditUrl }}"
                                                                                            data-size="lg"
                                                                                            data-ajax-popup="true"
                                                                                            data-title="{{ __('Edit Allowance') }}"
                                                                                            data-guard-msg="{{ $ovtEditGuard }}"
                                                                                            data-sv-localized="true"
                                                                                            class="btn btn-outline-primary btn-sm mr-1"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Edit') }}"
                                                                                            {{ $ovtEditUrl === '#' ? 'aria-disabled=true' : '' }}
                                                                                        >
                                                                                            <i class="{{ VC::TI_PC_WT }}"></i> <span>{{ __('Edit') }}</span>
                                                                                        </a>
                                                                                    @endcan

                                                                                    @can('delete allowance')
                                                                                        @php
                                                                                            $ovtDestroyBase   = VW::OVT.'.destroy';
                                                                                            $ovtDestroyKebab  = Str::kebab($ovtDestroyBase);
                                                                                            $ovtDestroyName   = Route::has($ovtDestroyBase) ? $ovtDestroyBase : (Route::has($ovtDestroyKebab) ? $ovtDestroyKebab : null);
                                                                                            $ovtDestroyUrl    = ($ovtDestroyName && $ovtIdStr !== '') ? route($ovtDestroyName, $ovtIdStr) : '#';
                                                                                            $ovtFormDelId     = 'overtime-delete-form-'.$ovtIdStr;
                                                                                            $ovtDelLinkId     = 'overtime-delete-link-'.$ovtIdStr;
                                                                                            $areYouSure       = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                                                            $irreversible     = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                                                            $ovtDestroyGuard  = Utility::fetchLinkMessage($lang, VW::OVT, 'overtime_destroy_route_unavailable') ?? 'Delete overtime route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        @endphp
                                                                                        <a
                                                                                            id="{{ $ovtDelLinkId }}"
                                                                                            href="#"
                                                                                            class="btn btn-outline-danger btn-sm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ __('Delete') }}"
                                                                                            data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                                                            data-confirm-yes="document.getElementById('{{ $ovtFormDelId }}').submit();"
                                                                                            data-url="{{ $ovtDestroyUrl }}"
                                                                                            data-guard-msg="{{ $ovtDestroyGuard }}"
                                                                                            data-sv-localized="true"
                                                                                        >
                                                                                            <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                                                        </a>
                                                                                        {{ Form::open([
                                                                                            'method'            => 'DELETE',
                                                                                            'url'               => $ovtDestroyUrl,
                                                                                            'id'                => $ovtFormDelId,
                                                                                            'data-url'          => $ovtDestroyUrl,
                                                                                            'data-guard-msg'    => $ovtDestroyGuard,
                                                                                            'data-sv-localized' => 'true',
                                                                                        ]) }}
                                                                                        {{ Form::close() }}
                                                                                    @endcan
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        <tr>
                                                                            <td colspan="6" class="text-center text-muted">{{ __('No overtimes found') }}</td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/payslips/saturationDeductionStore.js') }}"></script>
                                                    @can('edit saturation deduction')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/saturationDeductionsEdit.js') }}"></script>
                                                    @endcan
                                                    @can('delete saturation deduction')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/saturationDeductionsDestroy.js') }}"></script>
                                                    @endcan
                                                    <script defer src="{{ asset('assets/js/routes/payslips/otherPaymentStore.js') }}"></script>
                                                    @can('edit other payment')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/otherPaymentEdit.js') }}"></script>
                                                    @endcan
                                                    @can('delete other payment')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/otherPaymentDestroy.js') }}"></script>
                                                    @endcan
                                                    <script defer src="{{ asset('assets/js/routes/payslips/overtimeStore.js') }}"></script>
                                                    @can('edit overtime')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/overtimeEdit.js') }}"></script>
                                                    @endcan
                                                    @can('delete overtime')
                                                        <script defer src="{{ asset('assets/js/routes/payslips/overtimeDestroy.js') }}"></script>
                                                    @endcan
                                                @endpush
                                            @else
                                                <div class="text-mute">{{ __('No employee data available.') }}</div>
                                            @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection


@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/payslips/lang/edit.js') }}"></script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrArmed = "data-err-armed";
            const dataBound = "data-bound";
            const qs = (s, r = document) => r.querySelector(s);
            const getMsg = (el, key) => {
                let msg = errFb;
                if (
                el.getAttribute(dataSvLocalized) === "true" ||
                el.getAttribute(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg =
                    window.translations?.[lang]?.[key] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[key] ||
                    errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };
            const hasBS = () =>
                !!qs('link[href*="bootstrap"]') &&
                !!(window.bootstrap && window.bootstrap.Toast);
            const ensureToastContainer = () => {
                let c = qs("#np-toast-container");
                if (c) return c;
                c = document.createElement("div");
                c.id = "np-toast-container";
                c.style.position = "fixed";
                c.style.top = "1rem";
                c.style.right = "1rem";
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                document.body.appendChild(c);
                return c;
            };
            const showToast = message => {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = t.querySelector(".toast-body");
                if (body) body.textContent = message ?? errFb;
                try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                } catch (_) {
                alert(message ?? errFb);
                }
            };
            const scheduleClickError = (key, host) => {
                const el = host || document.body;
                if (!el || el.getAttribute(dataErrArmed) === "true") return;
                el.setAttribute(dataErrArmed, "true");
                const once = () => {
                try {
                    const m = getMsg(el, key);
                    hasBS() ? showToast(m) : alert(m);
                } finally {
                    el.removeAttribute(dataErrArmed);
                }
                };
                document.addEventListener("click", once, { once: true });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(el)) {
                    document.removeEventListener("click", once);
                    o.disconnect();
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            const initDataTables = () => {
                if (!($ && $.fn && ($.fn.DataTable || $.fn.dataTable))) {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("DataTables unavailable");
                } catch (_) {}
                scheduleClickError("datatable_unavailable");
                return;
                }
                const ids = [
                "#allowance-dataTable",
                "#commission-dataTable",
                "#loan-dataTable",
                "#saturation-deduction-dataTable",
                "#other-payment-dataTable",
                "#overtime-dataTable",
                ];
                ids.forEach(function (sel) {
                const el = qs(sel);
                if (!el) return;
                if ($.fn.DataTable.isDataTable(el)) return;
                try {
                    $(sel).DataTable({ columnDefs: [{ sortable: false, targets: [1] }] });
                } catch (_) {
                    scheduleClickError("datatable_unavailable", el);
                }
                });
            };
            const getDesignation = did => {
                const url = '{{route(ViewsConstants::EMP.".json")}}';
                const target = qs("#designation_id") || qs('select[name="designation_id"]');
                if (!url || url === "#") {
                scheduleClickError("designation_unavailable", target || document.body);
                return;
                }
                try {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: { department_id: did, _token: "{{ csrf_token() }}" },
                    success: function (data) {
                    try {
                        const sel =
                        qs("#designation_id") || qs('select[name="designation_id"]');
                        if (!sel) {
                        scheduleClickError("element_unavailable");
                        return;
                        }
                        const current = "{{ $employee->designation_id }}";
                        if (sel.tagName === "SELECT") {
                        while (sel.firstChild) {
                            sel.removeChild(sel.firstChild);
                        }
                        const opt0 = document.createElement("option");
                        opt0.value = "";
                        opt0.textContent = "Select any Designation";
                        sel.appendChild(opt0);
                        $.each(data || {}, function (key, value) {
                            const opt = document.createElement("option");
                            opt.value = key;
                            opt.textContent = value;
                            if (String(key) === String(current)) opt.selected = true;
                            sel.appendChild(opt);
                        });
                        }
                    } catch (_) {
                        scheduleClickError(
                        "designation_unavailable",
                        target || document.body
                        );
                    }
                    },
                    error: function () {
                    scheduleClickError(
                        "designation_unavailable",
                        target || document.body
                    );
                    },
                });
                } catch (_) {
                scheduleClickError("request_failed");
                }
            };
            const bindDeptChange = () => {
                const doc = document.documentElement;
                if (doc.getAttribute(dataBound) === "true") return;
                doc.setAttribute(dataBound, "true");
                const handler = function () {
                const department_id = $(this).val?.();
                getDesignation(department_id);
                };
                $(document).on("change", 'select[name="department_id"]', handler);
                const mo = new MutationObserver(function () {
                const exists = qs('select[name="department_id"]');
                if (!exists) {
                    $(document).off("change", 'select[name="department_id"]', handler);
                    doc.removeAttribute(dataBound);
                    mo.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            };
            const start = () => {
                if (!$ || !$.ajax) {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                } catch (_) {}
                scheduleClickError("request_failed");
                return;
                }
                initDataTables();
                const d_id = $("#department_id").val?.();
                if (d_id != null) getDesignation(d_id);
                bindDeptChange();
            };
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", start, { once: true });
            } else {
                start();
            }
        })();
    </script>
@endpush
