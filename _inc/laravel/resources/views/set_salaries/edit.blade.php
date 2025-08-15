@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, URL};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
        $salaryUpdateRoute  = Route::has(ViewsConstants::EMP . '.salary.update')
        ? route(ViewsConstants::EMP . '.salary.update', $employee->id)
        : (Route::has(Str::kebab(ViewsConstants::EMP . '.salary.update'))
            ? route(Str::kebab(ViewsConstants::EMP . '.salary.update'), $employee->id)
            : '#');
    $salaryFormId             = 'salary-update-form';
    $updateMsg          = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::EMP,
        'salary_update_route_unavailable'
    ) ?? 'Salary update route is unavailable. Please contact technical support or your domain administrator.';
    $createRoute = Route::has(ViewsConstants::ALW . '.store')
        ? route(ViewsConstants::ALW . '.store')
        : '#';
    $allowanceFormId      = 'allowance-create-form';
    $createMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW,
        'allowance_store_route_unavailable'
    ) ?? 'Allowance store route is unavailable. Please contact technical support or your domain administrator.';
    $storeRoute = Route::has(ViewsConstants::COM . '.store')
        ? route(ViewsConstants::COM . '.store')
        : '#';
    $comFormId     = 'commission-store-form';
    $storeMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COM,
        'commission_store_route_unavailable'
    ) ?? 'Commission store route is unavailable. Please contact technical support or your domain administrator.';
    $loanStoreRoute     = Route::has(ViewsConstants::LN . '.store')
        ? route(ViewsConstants::LN . '.store')
        : '#';
    $loanFormId         = 'loan-store-form';
    $loanStoreMsg       = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::LN,
        'loan_store_route_unavailable'
    ) ?? 'Loan store route is unavailable. Please contact technical support or your domain administrator.';
    $sdStoreRoute       = Route::has(ViewsConstants::STR_DD . '.store')
        ? route(ViewsConstants::STR_DD . '.store')
        : (Route::has(Str::kebab(ViewsConstants::STR_DD . '.store'))
            ? route(Str::kebab(ViewsConstants::STR_DD . '.store'))
            : '#');
    $sdFormId           = 'saturation-deduction-store-form';
    $sdStoreMsg         = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::STR_DD,
        'saturation_deduction_store_route_unavailable'
    ) ?? 'Saturation deduction store route is unavailable. Please contact technical support or your domain administrator.';
    $otherPayStoreRoute = Route::has(ViewsConstants::OT_PAY . '.store')
        ? route(ViewsConstants::OT_PAY . '.store')
        : (Route::has(Str::kebab(ViewsConstants::OT_PAY . '.store'))
            ? route(Str::kebab(ViewsConstants::OT_PAY . '.store'))
            : '#');
    $otherPayFormId     = 'other-payment-store-form';
    $otherPayStoreMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::OT_PAY,
        'other_payment_store_route_unavailable'
    ) ?? 'Other payment store route is unavailable. Please contact technical support or your domain administrator.';
    $overtimeStoreRoute    = Route::has(ViewsConstants::OVT . '.store')
        ? route(ViewsConstants::OVT . '.store')
        : '#';
    $overtimeFormId        = 'overtime-store-form';
    $overtimeStoreMsg      = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::OVT,
        'overtime_store_route_unavailable'
    ) ?? 'Overtime store route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Employee  Salary List')}}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <section class="nav-tabs">
                <div class="col-lg-12 our-system">
                    <div class="row">
                        @php
                            $tabs = [
                                'salary',
                                'allowance',
                                'commission',
                                'loan',
                                'saturation-deduction',
                                'other-payment',
                                'overtime',
                            ];
                        @endphp
                        <ul class="nav nav-tabs my-4">
                            @foreach ($tabs as $tab)
                                <li>
                                    <a data-toggle="tab" href="#{{ $tab }}" class="{{ $loop->first ? 'active' : '' }}">
                                        {{ __(ucwords(str_replace('-', ' ', $tab))) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div id="salary" class="tab-pane in active">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::model($employee, [
                                    'url'            => $salaryUpdateRoute,
                                    'method'         => 'POST',
                                    'id'             => $salaryFormId,
                                    'data-url'       => $salaryUpdateRoute,
                                    'data-guard-msg' => $updateMsg,
                                ]) }}
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::C12 }} {{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label(ViewsConstants::S_SLR, __('Payslip Type'), ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                            {{ Form::select('salary_type', $payslip_type, null, ['required'=>'required','class'=>VC::FM_CT_SL.' select2']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::C12 }} {{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('salary', __('Salary'), ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                            {{ Form::number('salary', null, ['required'=>'required','class'=>VC::FM_CT]) }}
                                        </div>
                                    </div>
                                </div>
                                @can('create set salary')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Change') }}</button>
                                        </div>
                                    </div>
                                @endcan
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="allowance" class="tab-pane">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::open([
                                    'url'            => $createRoute,
                                    'method'         => 'post',
                                    'id'             => $allowanceFormId,
                                    'data-url'       => $createRoute,
                                    'data-guard-msg' => $createMsg,
                                ]) }}
                                @csrf
                                {{ Form::hidden('employee_id', $employee->id) }}
                                <div class="{{ VC::RW }}">
                                    @foreach([
                                        ['field'=>'allowance_option','type'=>'select','label'=>__('Allowance Options'),'options'=>$allowance_options],
                                        ['field'=>'title','type'=>'text','label'=>__('Title')],
                                        ['field'=>'amount','type'=>'number','label'=>__('Amount'),'attrs'=>['step'=>'0.01']],
                                    ] as $f)
                                        <div class="{{ VC::C12 }} {{ VC::CM4 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['field'], $f['label'], ['class'=>VC::FM_LB]) }}@if($f['field']=='allowance_option')<span class="text-danger">*</span>@endif
                                                @php
                                                    $attrs = array_merge(
                                                        ['class'=>VC::FM_CT_SL.' select2','required'=>'required'],
                                                        $f['attrs'] ?? []
                                                    );
                                                @endphp
                                                @if($f['type']==='select')
                                                    {{ Form::select($f['field'], $f['options'], null, $attrs) }}
                                                @else
                                                    {{ Form::{$f['type']}($f['field'], null, $attrs) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @can('create allowance')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Change') }}</button>
                                        </div>
                                    </div>
                                @endcan
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
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach($allowances as $allowance)
                                                @php
                                                    $editRoute    = Route::has(ViewsConstants::ALW . '.edit')
                                                        ? route(ViewsConstants::ALW . '.edit', $allowance->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::ALW . '.edit'))
                                                            ? route(Str::kebab(ViewsConstants::ALW . '.edit'), $allowance->id)
                                                            : '#');
                                                    $editBtnId    = 'allowance-edit-' . $allowance->id;
                                                    $editMsg      = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::ALW,
                                                        'allowance_edit_route_unavailable'
                                                    ) ?? 'Allowance edit route is unavailable. Please contact technical support or your domain administrator.';

                                                    $destroyRoute = Route::has(ViewsConstants::ALW . '.destroy')
                                                        ? route(ViewsConstants::ALW . '.destroy', $allowance->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::ALW . '.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::ALW . '.destroy'), $allowance->id)
                                                            : '#');
                                                    $deleteBtnId  = 'allowance-delete-' . $allowance->id;
                                                    $deleteFormId = 'del-allow-' . $allowance->id;
                                                    $destroyMsg   = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::ALW,
                                                        'allowance_destroy_route_unavailable'
                                                    ) ?? 'Allowance destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <tr>
                                                    <td>{{ $allowance->employee()->name }}</td>
                                                    <td>{{ $allowance->allowance_option()->name }}</td>
                                                    <td>{{ $allowance->title }}</td>
                                                    <td>{{ $user?->priceFormat($allowance->amount) }}</td>
                                                    <td>
                                                        @can('edit allowance')
                                                            <a id="{{ $editBtnId }}"
                                                            href="{{ $editRoute }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Allowance') }}"
                                                            class="{{ VC::BT_SM_CT }}{{ VC::MS2 }}"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete allowance')
                                                            <a id="{{ $deleteBtnId }}"
                                                            href="#"
                                                            data-url="{{ $destroyRoute }}"
                                                            data-guard-msg="{{ $destroyMsg }}"
                                                            class="{{ VC::BT_SM_CT_DSB }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                            title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::open([
                                                                'url'            => $destroyRoute,
                                                                'method'         => 'DELETE',
                                                                'id'             => $deleteFormId,
                                                                'data-url'       => $destroyRoute,
                                                                'data-guard-msg' => $destroyMsg,
                                                            ]) !!}
                                                            {!! Form::close() !!}
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
                    <div id="commission" class="tab-pane">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::open([
                                    'url'            => $storeRoute,
                                    'method'         => 'post',
                                    'id'             => $comFormId,
                                    'data-url'       => $storeRoute,
                                    'data-guard-msg' => $storeMsg,
                                ]) }}
                                @csrf
                                {{ Form::hidden('employee_id', $employee->id) }}
                                <div class="{{ VC::RW }}">
                                    @foreach([
                                        ['field'=>'title','type'=>'text','label'=>__('Title')],
                                        ['field'=>'amount','type'=>'number','label'=>__('Amount'),'attrs'=>['step'=>'0.01']],
                                    ] as $f)
                                        <div class="{{ VC::C12 }} {{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['field'], $f['label'], ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                                @php
                                                    $attrs = array_merge(
                                                        ['class'=>VC::FM_CT,'required'=>'required'],
                                                        $f['attrs'] ?? []
                                                    );
                                                @endphp
                                                {{ Form::{$f['type']}($f['field'], null, $attrs) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @can('create commission')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                @endcan
                                {{ Form::close() }}

                                <hr>

                                <div class="table-responsive">
                                    <table class="{{ VC::TB }} table-striped mb-0" id="commission-dataTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach($commissions as $commission)
                                                @php
                                                    $editRoute    = Route::has(ViewsConstants::COM . '.edit')
                                                        ? route(ViewsConstants::COM . '.edit', $commission->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::COM . '.edit'))
                                                            ? route(Str::kebab(ViewsConstants::COM . '.edit'), $commission->id)
                                                            : '#');
                                                    $editBtnId    = 'commission-edit-' . $commission->id;
                                                    $editMsg      = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::COM,
                                                        'commission_edit_route_unavailable'
                                                    ) ?? 'Commission edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    $destroyRoute = Route::has(ViewsConstants::COM . '.destroy')
                                                        ? route(ViewsConstants::COM . '.destroy', $commission->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::COM . '.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::COM . '.destroy'), $commission->id)
                                                            : '#');
                                                    $deleteBtnId  = 'commission-delete-' . $commission->id;
                                                    $deleteFormId = 'commission-delete-form-' . $commission->id;
                                                    $destroyMsg   = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::COM,
                                                        'commission_destroy_route_unavailable'
                                                    ) ?? 'Commission destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <tr>
                                                    <td>{{ $commission->employee()->name }}</td>
                                                    <td>{{ $commission->title }}</td>
                                                    <td>{{ $user?->priceFormat($commission->amount) }}</td>
                                                    <td class="{{ VC::JCE }}">
                                                        @can('edit commission')
                                                            <a id="{{ $editBtnId }}"
                                                            href="{{ $editRoute }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Commission') }}"
                                                            class="{{ VC::BT_SM_CT }}{{ VC::MS2 }}"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete commission')
                                                            <a id="{{ $deleteBtnId }}"
                                                            href="#"
                                                            data-url="{{ $destroyRoute }}"
                                                            data-guard-msg="{{ $destroyMsg }}"
                                                            class="{{ VC::BT_SM_CT_DSB }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                            title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::open([
                                                                'url'            => $destroyRoute,
                                                                'method'         => 'DELETE',
                                                                'id'             => $deleteFormId,
                                                                'data-url'       => $destroyRoute,
                                                                'data-guard-msg' => $destroyMsg,
                                                            ]) !!}
                                                            {!! Form::close() !!}
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
                    <div id="loan" class="tab-pane">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::open([
                                    'url'            => $loanStoreRoute,
                                    'method'         => 'post',
                                    'id'             => $loanFormId,
                                    'data-url'       => $loanStoreRoute,
                                    'data-guard-msg' => $loanStoreMsg,
                                ]) }}
                                @csrf
                                {{ Form::hidden('employee_id', $employee->id) }}
                                <div class="{{ VC::RW }}">
                                    @foreach ([
                                        ['field'=>'loan_option','label'=>__('Loan Options'),'type'=>'select','options'=>$loan_options],
                                        ['field'=>'title','label'=>__('Title'),'type'=>'text'],
                                        ['field'=>'amount','label'=>__('Loan Amount'),'type'=>'number','attrs'=>['step'=>'0.01']],
                                        ['field'=>'start_date','label'=>__('Start Date'),'type'=>'text','attrs'=>['class'=>'form-control datepicker']],
                                        ['field'=>'end_date','label'=>__('End Date'),'type'=>'text','attrs'=>['class'=>'form-control datepicker']],
                                        ['field'=>'reason','label'=>__('Reason'),'type'=>'textarea','attrs'=>['rows'=>1]],
                                    ] as $f)
                                        <div class="{{ VC::C12 }} {{ VC::CM4 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['field'], $f['label'], ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                                @php
                                                    $baseAttrs = ['class'=>VC::FM_CT,'required'=>'required'];
                                                    $attrs     = array_merge($baseAttrs, $f['attrs'] ?? []);
                                                @endphp
                                                @if($f['type'] === 'select')
                                                    {{ Form::select($f['field'], $f['options'], null, $attrs) }}
                                                @elseif($f['type'] === 'textarea')
                                                    {{ Form::textarea($f['field'], null, $attrs) }}
                                                @else
                                                    {{ Form::{$f['type']}($f['field'], null, $attrs) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @can('create loan')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                @endcan
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
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach($loans as $loan)
                                                @php
                                                    $loanEditRoute   = Route::has(ViewsConstants::LN . '.edit')
                                                        ? route(ViewsConstants::LN . '.edit', $loan->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::LN . '.edit'))
                                                            ? route(Str::kebab(ViewsConstants::LN . '.edit'), $loan->id)
                                                            : '#');
                                                    $loanEditBtnId   = 'loan-edit-' . $loan->id;
                                                    $loanEditMsg     = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::LN,
                                                        'loan_edit_route_unavailable'
                                                    ) ?? 'Loan edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    $loanDestroyRoute = Route::has(ViewsConstants::LN . '.destroy')
                                                        ? route(ViewsConstants::LN . '.destroy', $loan->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::LN . '.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::LN . '.destroy'), $loan->id)
                                                            : '#');
                                                    $loanDeleteBtnId = 'loan-delete-' . $loan->id;
                                                    $loanDeleteFormId = 'del-loan-' . $loan->id;
                                                    $loanDestroyMsg  = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::LN,
                                                        'loan_destroy_route_unavailable'
                                                    ) ?? 'Loan destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <tr>
                                                    <td>{{ $loan->employee()->name }}</td>
                                                    <td>{{ $loan->loan_option()->name }}</td>
                                                    <td>{{ $loan->title }}</td>
                                                    <td>{{ $user?->priceFormat($loan->amount) }}</td>
                                                    <td>{{ $user?->dateFormat($loan->start_date) }}</td>
                                                    <td>{{ $user?->dateFormat($loan->end_date) }}</td>
                                                    <td class="{{ VC::JCE }}">
                                                        @can('edit loan')
                                                            <a id="{{ $loanEditBtnId }}"
                                                            href="{{ $loanEditRoute }}"
                                                            data-url="{{ $loanEditRoute }}"
                                                            data-guard-msg="{{ $loanEditMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Loan') }}"
                                                            class="{{ VC::BT_SM_CT }} {{ VC::MS2 }}"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete loan')
                                                            <a id="{{ $loanDeleteBtnId }}"
                                                            href="#"
                                                            data-url="{{ $loanDestroyRoute }}"
                                                            data-guard-msg="{{ $loanDestroyMsg }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $loanDeleteFormId }}').submit();"
                                                            title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {{ Form::open([
                                                                'route'         => [ViewsConstants::LN . '.destroy', $loan->id],
                                                                'method'        => 'DELETE',
                                                                'id'            => $loanDeleteFormId,
                                                                'data-url'      => $loanDestroyRoute,
                                                                'data-guard-msg'=> $loanDestroyMsg,
                                                            ]) }}
                                                            {{ Form::close() }}
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
                    <div id="saturation-deduction" class="tab-pane">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::open([
                                    'url'            => $sdStoreRoute,
                                    'method'         => 'post',
                                    'id'             => $sdFormId,
                                    'data-url'       => $sdStoreRoute,
                                    'data-guard-msg' => $sdStoreMsg,
                                ]) }}
                                @csrf
                                {{ Form::hidden('employee_id', $employee->id) }}

                                <div class="{{ VC::RW }}">
                                    @foreach ([
                                        ['field'=>'deduction_option','label'=>__('Deduction Options'),'type'=>'select','options'=>$deduction_options,'col'=>'col-md-4'],
                                        ['field'=>'title','label'=>__('Title'),'type'=>'text','col'=>'col-md-4'],
                                        ['field'=>'amount','label'=>__('Amount'),'type'=>'number','col'=>'col-md-4','attrs'=>['step'=>'0.01']],
                                    ] as $f)
                                        <div class="{{ VC::C12 }} {{ $f['col'] }}">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['field'], $f['label'], ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                                @php
                                                    $base = ['class'=>VC::FM_CT,'required'=>'required'];
                                                    $attrs = array_merge($base, $f['attrs'] ?? []);
                                                @endphp
                                                @if($f['type'] === 'select')
                                                    {{ Form::select($f['field'], $f['options'], null, $attrs) }}
                                                @elseif($f['type'] === 'number')
                                                    {{ Form::number($f['field'], null, $attrs) }}
                                                @else
                                                    {{ Form::text($f['field'], null, $attrs) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @can('create saturation deduction')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                @endcan

                                {{ Form::close() }}

                                <hr>

                                <div class="table-responsive">
                                    <table class="{{ VC::TB }} table-striped mb-0" id="saturation-deduction-dataTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Deduction Option') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach($saturationdeductions as $sd)
                                                @php
                                                    $sdEditRoute     = Route::has(ViewsConstants::STR_DD . '.edit')
                                                        ? route(ViewsConstants::STR_DD . '.edit', $sd->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::STR_DD . '.edit'))
                                                            ? route(Str::kebab(ViewsConstants::STR_DD . '.edit'), $sd->id)
                                                            : '#');
                                                    $sdEditBtnId     = 'saturation-deduction-edit-' . $sd->id;
                                                    $sdEditMsg       = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::STR_DD,
                                                        'saturation_deduction_edit_route_unavailable'
                                                    ) ?? 'Saturation deduction edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    $sdDestroyRoute  = Route::has(ViewsConstants::STR_DD . '.destroy')
                                                        ? route(ViewsConstants::STR_DD . '.destroy', $sd->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::STR_DD . '.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::STR_DD . '.destroy'), $sd->id)
                                                            : '#');
                                                    $sdDeleteBtnId   = 'saturation-deduction-delete-' . $sd->id;
                                                    $sdDeleteFormId  = 'del-sd-' . $sd->id;
                                                    $sdDestroyMsg    = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::STR_DD,
                                                        'saturation_deduction_destroy_route_unavailable'
                                                    ) ?? 'Saturation deduction destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <tr>
                                                    <td>{{ $sd->employee()->name }}</td>
                                                    <td>{{ $sd->deduction_option()->name }}</td>
                                                    <td>{{ $sd->title }}</td>
                                                    <td>{{ $user->priceFormat($sd->amount) }}</td>
                                                    <td class="{{ VC::JCE }}">
                                                        @can('edit saturation deduction')
                                                            <a id="{{ $sdEditBtnId }}"
                                                            href="{{ $sdEditRoute }}"
                                                            data-url="{{ $sdEditRoute }}"
                                                            data-guard-msg="{{ $sdEditMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Saturation Deduction') }}"
                                                            class="{{ VC::BT_SM_CT }} {{ VC::MS2 }}"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete saturation deduction')
                                                            <a id="{{ $sdDeleteBtnId }}"
                                                            href="#"
                                                            data-url="{{ $sdDestroyRoute }}"
                                                            data-guard-msg="{{ $sdDestroyMsg }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $sdDeleteFormId }}').submit();"
                                                            title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::open([
                                                                'url'            => $sdDestroyRoute,
                                                                'method'         => 'DELETE',
                                                                'id'             => $sdDeleteFormId,
                                                                'data-url'       => $sdDestroyRoute,
                                                                'data-guard-msg' => $sdDestroyMsg,
                                                            ]) !!}
                                                            {!! Form::close() !!}
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
                    <div id="other-payment" class="tab-pane">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::open([
                                    'url'            => $otherPayStoreRoute,
                                    'method'         => 'post',
                                    'id'             => $otherPayFormId,
                                    'data-url'       => $otherPayStoreRoute,
                                    'data-guard-msg' => $otherPayStoreMsg,
                                ]) }}
                                @csrf
                                {{ Form::hidden('employee_id', $employee->id) }}

                                <div class="{{ VC::RW }}">
                                    @foreach ([
                                        ['field'=>'title','label'=>__('Title')],
                                        ['field'=>'amount','label'=>__('Amount'),'type'=>'number','attrs'=>['step'=>'0.01']],
                                    ] as $f)
                                        <div class="{{ VC::C12 }} col-md-6">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['field'], $f['label'], ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                                @php
                                                    $base  = ['class'=>VC::FM_CT,'required'=>'required'];
                                                    $attrs = array_merge($base, $f['attrs'] ?? []);
                                                @endphp
                                                @if(($f['type'] ?? '') === 'number')
                                                    {{ Form::number($f['field'], null, $attrs) }}
                                                @else
                                                    {{ Form::text($f['field'], null, $attrs) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @can('create other payment')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                @endcan
                                {{ Form::close() }}

                                <hr>

                                <div class="table-responsive">
                                    <table class="{{ VC::TB }} table-striped mb-0" id="other-payment-dataTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach($otherpayments as $op)
                                                @php
                                                    $editRoute      = Route::has(ViewsConstants::OT_PAY . '.edit')
                                                        ? route(ViewsConstants::OT_PAY . '.edit', $op->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::OT_PAY . '.edit'))
                                                            ? route(Str::kebab(ViewsConstants::OT_PAY . '.edit'), $op->id)
                                                            : '#');
                                                    $editBtnId      = 'other-payment-edit-' . $op->id;
                                                    $editMsg        = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::OT_PAY,
                                                        'other_payment_edit_route_unavailable'
                                                    ) ?? 'Other payment edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    $destroyRoute   = Route::has(ViewsConstants::OT_PAY . '.destroy')
                                                        ? route(ViewsConstants::OT_PAY . '.destroy', $op->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::OT_PAY . '.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::OT_PAY . '.destroy'), $op->id)
                                                            : '#');
                                                    $deleteBtnId    = 'other-payment-delete-' . $op->id;
                                                    $deleteFormId   = 'del-op-' . $op->id;
                                                    $destroyMsg     = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::OT_PAY,
                                                        'other_payment_destroy_route_unavailable'
                                                    ) ?? 'Other payment destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <tr>
                                                    <td>{{ $op->employee()->name }}</td>
                                                    <td>{{ $op->title }}</td>
                                                    <td>{{ $user->priceFormat($op->amount) }}</td>
                                                    <td class="{{ VC::JCE }}">
                                                        @can('edit other payment')
                                                            <a id="{{ $editBtnId }}"
                                                            href="{{ $editRoute }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ $editMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Other Payment') }}"
                                                            class="{{ VC::BT_SM_CT }} {{ VC::MS2 }}"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete other payment')
                                                            <a id="{{ $deleteBtnId }}"
                                                            href="#"
                                                            data-url="{{ $destroyRoute }}"
                                                            data-guard-msg="{{ $destroyMsg }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                            title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::open([
                                                                'route'            => [ViewsConstants::OT_PAY . '.destroy', $op->id],
                                                                'method'           => 'DELETE',
                                                                'id'               => $deleteFormId,
                                                                'data-url'         => $destroyRoute,
                                                                'data-guard-msg'   => $destroyMsg,
                                                            ]) !!}
                                                            {!! Form::close() !!}
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
                    <div id="overtime" class="tab-pane">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::card_body ?? 'card-body' }}">
                                {{ Form::open([
                                    'url'            => $overtimeStoreRoute,
                                    'method'         => 'post',
                                    'id'             => $overtimeFormId,
                                    'data-url'       => $overtimeStoreRoute,
                                    'data-guard-msg' => $overtimeStoreMsg,
                                ]) }}
                                @csrf
                                {{ Form::hidden('employee_id', $employee->id) }}

                                <div class="{{ VC::RW }}">
                                    @foreach ([
                                        ['field'=>'title','label'=>__('Overtime Title')],
                                        ['field'=>'number_of_days','label'=>__('Number of days'),'type'=>'number','attrs'=>['step'=>'0.01']],
                                        ['field'=>'hours','label'=>__('Hours'),'type'=>'number','attrs'=>['step'=>'0.01']],
                                        ['field'=>'rate','label'=>__('Rate'),'type'=>'number','attrs'=>['step'=>'0.01']],
                                    ] as $f)
                                        <div class="{{ VC::C12 }} col-md-6">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['field'], $f['label'], ['class'=>VC::FM_LB]) }}<span class="text-danger">*</span>
                                                @php
                                                    $base  = ['class'=>VC::FM_CT,'required'=>'required'];
                                                    $attrs = array_merge($base, $f['attrs'] ?? []);
                                                @endphp
                                                {{ Form::{$f['type'] ?? 'text'}($f['field'], null, $attrs) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @can('create overtime')
                                    <div class="{{ VC::RW }} mt-1">
                                        <div class="{{ VC::C12 }} {{ VC::JCE }}">
                                            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                @endcan

                                {{ Form::close() }}

                                <hr>

                                <div class="table-responsive">
                                    <table class="{{ VC::TB }} table-striped mb-0" id="overtime-dataTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Overtime Title') }}</th>
                                                <th>{{ __('Number of days') }}</th>
                                                <th>{{ __('Hours') }}</th>
                                                <th>{{ __('Rate') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach($overtimes as $ot)
                                                @php
                                                    $overtimeEditRoute    = Route::has(ViewsConstants::OVT . '.edit')
                                                        ? route(ViewsConstants::OVT . '.edit', $ot->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::OVT . '.edit'))
                                                            ? route(Str::kebab(ViewsConstants::OVT . '.edit'), $ot->id)
                                                            : '#');
                                                    $overtimeEditBtnId    = 'overtime-edit-' . $ot->id;
                                                    $overtimeEditMsg      = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::OVT,
                                                        'overtime_edit_route_unavailable'
                                                    ) ?? 'Overtime edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    $overtimeDestroyRoute = Route::has(ViewsConstants::OVT . '.destroy')
                                                        ? route(ViewsConstants::OVT . '.destroy', $ot->id)
                                                        : (Route::has(Str::kebab(ViewsConstants::OVT . '.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::OVT . '.destroy'), $ot->id)
                                                            : '#');
                                                    $overtimeDeleteBtnId  = 'overtime-delete-' . $ot->id;
                                                    $overtimeDeleteFormId = 'overtime-delete-form-' . $ot->id;
                                                    $overtimeDestroyMsg   = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::OVT,
                                                        'overtime_destroy_route_unavailable'
                                                    ) ?? 'Overtime destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <tr>
                                                    <td>{{ $ot->employee()->name }}</td>
                                                    <td>{{ $ot->title }}</td>
                                                    <td>{{ $ot->number_of_days }}</td>
                                                    <td>{{ $ot->hours }}</td>
                                                    <td>{{ $user->priceFormat($ot->rate) }}</td>
                                                    <td class="{{ VC::JCE }}">
                                                        @can('edit overtime')
                                                            <a id="{{ $overtimeEditBtnId }}"
                                                            href="{{ $overtimeEditRoute }}"
                                                            data-url="{{ $overtimeEditRoute }}"
                                                            data-guard-msg="{{ $overtimeEditMsg }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ __('Edit Overtime') }}"
                                                            class="{{ VC::BT_SM_CT }} {{ VC::MS2 }}"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete overtime')
                                                            <a id="{{ $overtimeDeleteBtnId }}"
                                                            href="#"
                                                            data-url="{{ $overtimeDestroyRoute }}"
                                                            data-guard-msg="{{ $overtimeDestroyMsg }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $overtimeDeleteFormId }}').submit();"
                                                            title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::open([
                                                                'route'            => [ViewsConstants::OVT . '.destroy', $ot->id],
                                                                'method'           => 'DELETE',
                                                                'id'               => $overtimeDeleteFormId,
                                                                'data-url'         => $overtimeDestroyRoute,
                                                                'data-guard-msg'   => $overtimeDestroyMsg,
                                                            ]) !!}
                                                            {!! Form::close() !!}
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
            </section>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar: {
            select_any_designation: 'اختر أي مسمى وظيفي'
        },
        da: {
            select_any_designation: 'Vælg en titel'
        },
        de: {
            select_any_designation: 'Wählen Sie eine Bezeichnung'
        },
        en: {
            select_any_designation: 'Select any Designation'
        },
        es: {
            select_any_designation: 'Seleccione cualquier designación'
        },
        fr: {
            select_any_designation: 'Sélectionnez une désignation'
        },
        he: {
            select_any_designation: 'בחר כל תואר'
        },
        it: {
            select_any_designation: 'Seleziona una qualifica'
        },
        ja: {
            select_any_designation: '任意の役職を選択'
        },
        nl: {
            select_any_designation: 'Selecteer een functie'
        },
        pl: {
            select_any_designation: 'Wybierz dowolne stanowisko'
        },
        pt: {
            select_any_designation: 'Selecione qualquer designação'
        },
        'pt-br': {
            select_any_designation: 'Selecione qualquer designação'
        },
        ru: {
            select_any_designation: 'Выберите любое назначение'
        },
        tr: {
            select_any_designation: 'Herhangi bir görev seçin'
        },
        zh: {
            select_any_designation: '选择任意职称'
        }
        };
    </script>
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';

        const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (
            el.getAttribute('data-sv-localized') === 'true' ||
            el.getAttribute(dataClientLocalized) === 'true'
            ) {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ??
                document.documentElement.lang ??
                'en'
            )
                .toLowerCase()
                .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ??
                el.getAttribute(dataGuardMsg) ??
                window.translations?.['en']?.[msgKey] ??
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
            }
            }
            return msg;
        };

        const showError = message => {
            try {
            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            if (bootstrapLink && window.bootstrap?.Toast) {
                const toastEl = document.createElement('div');
                toastEl.className = 'toast';
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message;
                toastEl.appendChild(body);
                container.appendChild(toastEl);
                bootstrap.Toast.getOrCreateInstance(toastEl).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };

        let errorMessage = '';
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((ms, obs) => {
            for (const m of ms) {
            for (const n of m.removedNodes) {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
                }
            }
            }
        }).observe(document.body, { childList: true, subtree: true });

        document.addEventListener('DOMContentLoaded', () => {
            // Department → Designation
            const deptSelect = document.getElementById('department_id');
            if (deptSelect) {
            if (deptSelect.dataset.listenerActive !== 'true') {
                deptSelect.dataset.listenerActive = 'true';
                deptSelect.addEventListener('change', onDeptChange);
                new MutationObserver((ms, obs) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                    if (n === deptSelect) {
                    deptSelect.removeEventListener('change', onDeptChange);
                    obs.disconnect();
                    }
                }));
                }).observe(document.body, { childList: true, subtree: true });
            }
            onDeptChange.call(deptSelect);
            }

            ['allowance-dataTable','commission-dataTable','loan-dataTable','saturation-deduction-dataTable','other-payment-dataTable','overtime-dataTable']
            .forEach(id => {
                try {
                const tbl = document.getElementById(id);
                if (!tbl) return;
                $(tbl).dataTable({ columnDefs: [{ sortable: false, targets: [1] }] });
                } catch {
                console.log(`DataTable init failed: ${id}`);
                }
            });
        });

        function onDeptChange() {
            loadDesignations(this.value ?? '');
        }

        async function loadDesignations(deptId) {
            try {
            const url = '{{ route(ViewsConstants::EMP.'.json') }}';
            if (!url || url === '#') throw new Error();
            const data = await $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: {
                department_id: deptId,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
            });
            const desEl = document.getElementById('designation_id');
            if (!desEl) return;
            desEl.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = window.translations.en.select_any_designation;
            desEl.appendChild(placeholder);
            Object.entries(data).forEach(([key, val]) => {
                if (desEl.querySelector(`option[value="${key}"]`)) return;
                const opt = document.createElement('option');
                opt.value = key;
                if (key === '{{ $employee->designation_id }}') opt.selected = true;
                opt.textContent = val;
                desEl.appendChild(opt);
            });
            } catch {
            errorMessage = getLocalizedMessage('designation_fetch_failed', document.getElementById('department_id') || document.body);
            }
        }
        })();
    </script>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $salaryFormId }}');
            if (form && form.getAttribute('data-listener-active') !== 'true') {
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', event => {
                    try {
                        const action = form.getAttribute('action');
                        const url    = form.getAttribute('data-url');
                        if ((action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        form.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            }
        })();
    </script>
    <script defer>
        (() => {
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };

            attachGuard(document.getElementById('{{ $allowanceFormId }}'), 'submit');

            document.querySelectorAll('[id^="allowance-edit-"]').forEach(el => {
                attachGuard(el, 'click');
            });

            document.querySelectorAll('[id^="allowance-delete-"]').forEach(el => {
                attachGuard(el, 'click');
            });
        })();
    </script>
    <script defer>
        (() => {
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };

            attachGuard(document.getElementById('{{ $comFormId }}'), 'submit');
            document.querySelectorAll('[id^="commission-edit-"]').forEach(el => attachGuard(el, 'click'));
            document.querySelectorAll('[id^="commission-delete-"]').forEach(el => attachGuard(el, 'click'));
        })();
    </script>
    <script defer>
        (() => {
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const action = el.tagName === 'FORM' ? el.getAttribute('action') : null;
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };

            attachGuard(document.getElementById('{{ $loanFormId }}'), 'submit');
            document.querySelectorAll('[id^="loan-edit-"]').forEach(el => attachGuard(el, 'click'));
            document.querySelectorAll('[id^="loan-delete-"]').forEach(el => attachGuard(el, 'click'));
        })();
    </script>
    <script defer>
        (() => {
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href   = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const action = el.tagName === 'FORM' ? el.getAttribute('action') : null;
                        const url    = el.getAttribute('data-url');
                        if ((href && href !== '#') || (action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };

            attachGuard(document.getElementById('{{ $sdFormId }}'), 'submit');
            document.querySelectorAll('[id^="saturation-deduction-edit-"]').forEach(el => attachGuard(el, 'click'));
            document.querySelectorAll('[id^="saturation-deduction-delete-"]').forEach(el => attachGuard(el, 'click'));
        })();
    </script>
    <script defer>
        (() => {
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href   = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const action = el.tagName === 'FORM' ? el.getAttribute('action') : null;
                        const url    = el.getAttribute('data-url');
                        if ((href && href !== '#') || (action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };

            attachGuard(document.getElementById('{{ $otherPayFormId }}'), 'submit');
            document.querySelectorAll('[id^="other-payment-edit-"]').forEach(el => attachGuard(el, 'click'));
            document.querySelectorAll('[id^="other-payment-delete-"]').forEach(el => attachGuard(el, 'click'));
        })();
    </script>
    <script defer>
        (() => {
            const attachGuard = (el, eventType) => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(eventType, event => {
                    try {
                        const href   = el.tagName === 'A' ? el.getAttribute('href') : null;
                        const action = el.tagName === 'FORM' ? el.getAttribute('action') : null;
                        const url    = el.getAttribute('data-url');
                        if ((href && href !== '#') || (action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };

            attachGuard(document.getElementById('{{ $overtimeFormId }}'), 'submit');
            document.querySelectorAll('[id^="overtime-edit-"]').forEach(el => attachGuard(el, 'click'));
            document.querySelectorAll('[id^="overtime-delete-"]').forEach(el => attachGuard(el, 'click'));
        })();
    </script>
@endpush
