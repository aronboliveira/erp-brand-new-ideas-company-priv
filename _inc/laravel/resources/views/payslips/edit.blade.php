@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, URL};
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_CTT)
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{__('Employee Salary Pay Slip')}}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Home</a></div>
                    <div class="breadcrumb-item">{{__('Employee Salary Pay Slip')}}</div>
                </div>
            </div>
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between w-100">
                                <h4>{{__('Employee Salary Pay Slip')}}</h4>
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
                                <ul class="nav nav-pills mb-3" id="myTab3" role="tablist">
                                    @foreach ($tabs as $index => $tab)
                                        @php
                                            $id = $index < 2 
                                                ? $tab['id'] 
                                                : 'contact-tab' . ($index + 1);
                                        @endphp
                                        <li class="nav-item">
                                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                            id="{{ $id }}"
                                            data-toggle="tab"
                                            href="{{ $tab['href'] }}"
                                            role="tab"
                                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                            >
                                                {{ __($tab['text']) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade show active" id="salary" role="tabpanel" aria-labelledby="salary-tab3">
                                        <div class="company-setting-wrap">
                                            {{ Form::model($employee, array('route' => array(ViewsConstants::EMP.'.update', $employee->id), 'method' => 'PUT' , 'enctype' => 'multipart/form-data')) }}
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('salary_type', __('Payslip Type*')) }}
                                                        {{ Form::select('salary_type',$payslip_type,null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('salary', __('Salary')) }}
                                                        {{ Form::number('salary',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>
                                            {{Form::close()}}
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="allowance" role="tabpanel" aria-labelledby="allowance-tab3">
                                        <div class="company-setting-wrap">
                                            {{Form::open(array('url'=>ViewsConstants::ALW,'method'=>'post'))}}
                                            @csrf
                                            {{ Form::hidden('employee_id',$employee->id, array()) }}
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('allowance_option', __('Allowance Options*')) }}
                                                        {{ Form::select('allowance_option',$allowance_options,null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('title', __('Title')) }}
                                                        {{ Form::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('amount', __('Amount')) }}
                                                        {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>
                                            {{Form::close()}}
                                            <hr>
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0" id="allowance-dataTable">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Employee Name')}}</th>
                                                        <th>{{__('Allownace Option')}}</th>
                                                        <th>{{__('Title')}}</th>
                                                        <th>{{__('Amount')}}</th>
                                                        <th class="text-end" width="200px">{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($allowances as $allowance)
                                                        <tr>
                                                            <td>{{ $allowance->employee()->name }}</td>
                                                            <td>{{ $allowance->allowance_option()->name }}</td>
                                                            <td>{{ $allowance->title }}</td>
                                                            <td>{{ $allowance->amount }}</td>
                                                            <td class="text-end">
                                                                @can('edit allowance')
                                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::ALW.'/'.$allowance->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Allowance')}}" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i> <span>{{__('Edit')}}</span></a>
                                                                @endcan
                                                                @can('delete allowance')
                                                                    <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$allowance->id}}').submit();"><i class="ti ti-trash"></i> <span>{{__('Delete')}}</span></a>
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::ALW.'.destroy', $allowance->id],'id'=>'delete-form-'.$allowance->id]) !!}
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
                                    <div class="tab-pane fade" id="commission" role="tabpanel" aria-labelledby="commission-tab3">
                                        <div class="email-setting-wrap">
                                            {{Form::open(array('url'=>ViewsConstants::COM,'method'=>'post'))}}
                                            @csrf
                                            {{ Form::hidden('employee_id',$employee->id, array()) }}
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('title', __('Title')) }}
                                                        {{ Form::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('amount', __('Amount')) }}
                                                        {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>

                                            {{Form::close()}}

                                            <hr>
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0" id="commission-dataTable">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Employee Name')}}</th>
                                                        <th>{{__('Title')}}</th>
                                                        <th>{{__('Amount')}}</th>
                                                        <th class="text-end" width="200px">{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($commissions as $commission)
                                                        <tr>
                                                            <td>{{ $commission->employee()->name }}</td>
                                                            <td>{{ $commission->title }}</td>
                                                            <td>{{ $commission->amount }}</td>
                                                            <td class="text-end">
                                                                @can('edit allowance')
                                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::COM.'/'.$commission->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Allowance')}}" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i> <span>{{__('Edit')}}</span></a>
                                                                @endcan
                                                                @can('delete allowance')
                                                                    <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$commission->id}}').submit();"><i class="ti ti-trash"></i> <span>{{__('Delete')}}</span></a>
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::COM.'.destroy', $commission->id],'id'=>'delete-form-'.$commission->id]) !!}
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
                                    <div class="tab-pane fade" id="loan" role="tabpanel" aria-labelledby="loan-tab4">
                                        <div class="email-setting-wrap">
                                            {{Form::open(array('url'=>ViewsConstants::LN,'method'=>'post'))}}
                                            @csrf
                                            {{ Form::hidden('employee_id',$employee->id, array()) }}
                                            <div class="row">
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('loan_option', __('Loan Options*')) }}
                                                        {{ Form::select('loan_option',$loan_options,null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('title', __('Title')) }}
                                                        {{ Form::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('amount', __('Loan Amount')) }}
                                                        {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('start_date', __('Start Date')) }}
                                                        {{ Form::text('start_date',null, array('class' => 'form-control datepicker','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('end_date', __('End Date')) }}
                                                        {{ Form::text('end_date',null, array('class' => 'form-control datepicker','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::label('reason', __('Reason')) }}
                                                        {{ Form::textarea('reason',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>
                                            {{Form::close()}}

                                            <hr>
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0" id="loan-dataTable">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('employee')}}</th>
                                                        <th>{{__('Loan Options')}}</th>
                                                        <th>{{__('Title')}}</th>
                                                        <th>{{__('Loan Amount')}}</th>
                                                        <th>{{__('Start Date')}}</th>
                                                        <th>{{__('End Date')}}</th>
                                                        <th class="text-end" width="200px">{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($loans as $loan)
                                                        <tr>
                                                            <td>{{ $loan->employee()->name }}</td>
                                                            <td>{{ $loan->loan_option()->name }}</td>
                                                            <td>{{ $loan->title }}</td>
                                                            <td>{{ $loan->amount }}</td>
                                                            <td>{{ $loan->start_date }}</td>
                                                            <td>{{ $loan->end_date }}</td>
                                                            <td class="text-end">
                                                                @can('edit loan')
                                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::LN.'/'.$loan->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Allowance')}}" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i> <span>{{__('Edit')}}</span></a>
                                                                @endcan
                                                                @can('delete loan')
                                                                    <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$loan->id}}').submit();"><i class="ti ti-trash"></i> <span>{{__('Delete')}}</span></a>
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::LN.'.destroy', $loan->id],'id'=>'delete-form-'.$loan->id]) !!}
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
                                    <div class="tab-pane fade" id="saturation-deduction" role="tabpanel" aria-labelledby="saturation-deduction-tab3">
                                        <div class="email-setting-wrap">
                                            {{Form::open(array('url'=>ViewsConstants::STR_DD,'method'=>'post'))}}
                                            @csrf
                                            {{ Form::hidden('employee_id',$employee->id, array()) }}
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('deduction_option', __('Deduction Options*')) }}
                                                        {{ Form::select('deduction_option',$deduction_options,null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('title', __('Title')) }}
                                                        {{ Form::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('amount', __('Amount')) }}
                                                        {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>
                                            {{Form::close()}}

                                            <hr>
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0" id="saturation-deduction-dataTable">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Employee Name')}}</th>
                                                        <th>{{__('Deduction Option')}}</th>
                                                        <th>{{__('Title')}}</th>
                                                        <th>{{__('Amount')}}</th>
                                                        <th class="text-end" width="200px">{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($saturationdeductions as $saturationdeduction)
                                                        <tr>

                                                            <td>{{ $saturationdeduction->employee()->name }}</td>
                                                            <td>{{ $saturationdeduction->deduction_option()->name }}</td>
                                                            <td>{{ $saturationdeduction->title }}</td>
                                                            <td>{{ $saturationdeduction->amount }}</td>
                                                            <td class="text-end">

                                                                @can('edit saturation deduction')
                                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::STR_DD.'/'.$saturationdeduction->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Allowance')}}" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i> <span>{{__('Edit')}}</span></a>
                                                                @endcan
                                                                @can('delete saturation deduction')
                                                                    <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$saturationdeduction->id}}').submit();"><i class="ti ti-trash"></i> <span>{{__('Delete')}}</span></a>
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['saturationdeduction.destroy', $saturationdeduction->id],'id'=>'delete-form-'.$saturationdeduction->id]) !!}
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
                                    <div class="tab-pane fade" id="other-payment" role="tabpanel" aria-labelledby="other-payment-tab4">
                                        <div class="email-setting-wrap">
                                            {{Form::open(array('url'=>ViewsConstants::OT_PAY,'method'=>'post'))}}
                                            @csrf
                                            {{ Form::hidden('employee_id',$employee->id, array()) }}
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('title', __('Title')) }}
                                                        {{ Form::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('amount', __('Amount')) }}
                                                        {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>
                                            {{Form::close()}}

                                            <hr>
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0" id="other-payment-dataTable">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('employee')}}</th>
                                                        <th>{{__('Title')}}</th>
                                                        <th>{{__('Amount')}}</th>
                                                        <th class="text-end" width="200px">{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($otherpayments as $otherpayment)
                                                        <tr>
                                                            <td>{{ $otherpayment->employee()->name }}</td>
                                                            <td>{{ $otherpayment->title }}</td>
                                                            <td>{{ $otherpayment->amount }}</td>
                                                            <td class="text-end">
                                                                @can('edit other payment')
                                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::OT_PAY.'/'.$otherpayment->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Allowance')}}" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i> <span>{{__('Edit')}}</span></a>
                                                                @endcan
                                                                @can('delete other payment')
                                                                    <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$otherpayment->id}}').submit();"><i class="ti ti-trash"></i> <span>{{__('Delete')}}</span></a>
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['otherpayment.destroy', $otherpayment->id],'id'=>'delete-form-'.$otherpayment->id]) !!}
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
                                    <div class="tab-pane fade" id="overtime" role="tabpanel" aria-labelledby="overtime-tab4">
                                        <div class="email-setting-wrap">
                                            {{Form::open(array('url'=>ViewsConstants::OVT,'method'=>'post'))}}
                                            @csrf
                                            {{ Form::hidden('employee_id',$employee->id, array()) }}
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('title', __('Overtime Title*')) }}
                                                        {{ Form::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('number_of_days', __('Number of days')) }}
                                                        {{ Form::number('number_of_days',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('hours', __('Hours')) }}
                                                        {{ Form::number('hours',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('rate', __('Rate')) }}
                                                        {{ Form::number('rate',null, array('class' => 'form-control','required'=>'required')) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 text-end mt-1">
                                                    {{ Form::button('<i class="ti ti-plus"></i> '.__('Save Change'), ['type' => 'submit','class' => 'btn btn-primary']) }}
                                                </div>
                                            </div>
                                            {{Form::close()}}


                                            <hr>
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0" id="overtime-dataTable">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Employee Name')}}</th>
                                                        <th>{{__('Overtime Title')}}</th>
                                                        <th>{{__('Number of days')}}</th>
                                                        <th>{{__('Hours')}}</th>
                                                        <th>{{__('Rate')}}</th>

                                                        <th class="text-end" width="200px">{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($overtimes as $overtime)
                                                        <tr>
                                                            <td>{{ $overtime->employee()->name }}</td>
                                                            <td>{{ $overtime->title }}</td>
                                                            <td>{{ $overtime->number_of_days }}</td>
                                                            <td>{{ $overtime->hours }}</td>
                                                            <td>{{ $overtime->rate }}</td>

                                                            <td class="text-end">
                                                                @can('adit allowance')
                                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::OVT.'/'.$overtime->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Allowance')}}" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i> <span>{{__('Edit')}}</span></a>
                                                                @endcan
                                                                @can('delete allowance')
                                                                    <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$overtime->id}}').submit();"><i class="ti ti-trash"></i> <span>{{__('Delete')}}</span></a>
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['overtime.destroy', $overtime->id],'id'=>'delete-form-'.$overtime->id]) !!}
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
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
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
                    console.error("DataTables unavailable");
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
                    console.error("jQuery unavailable");
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
