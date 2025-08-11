@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{__('Employee')}}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{route('home')}}">{{__('Dashboard')}}</a></div>
                    <div class="breadcrumb-item">{{__('Employee')}}</div>
                </div>
            </div>
            <form method="post" action="{{route('employee.store')}}" enctype="multipart/form-data">

                @csrf
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6 ">
                            <div class="card">
                                <div class="card-header"><h4>{{__('Personal Detail')}}</h4></div>
                                <div class="card-body">

                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('name', 'Name') !!}<span class="text-danger pl-1">*</span>
                                        {!! Collective\Html\FormFacade::text('name', null, ['class' => 'form-control','required' => 'required']) !!}

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Collective\Html\FormFacade::label('dob', 'Date of Birth') !!}
                                                {!! Collective\Html\FormFacade::text('dob', null, ['class' => 'form-control datepicker']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Collective\Html\FormFacade::label('gender', 'Gender') !!}<span class="text-danger pl-1">*</span>
                                                <br>
                                                {{ Collective\Html\FormFacade::radio('gender', 'Male' , true,['class' => 'mt-2']) }}{{ __('Male') }} &nbsp&nbsp&nbsp
                                                {{ Collective\Html\FormFacade::radio('gender', 'Female' , false) }}{{ __('Female') }}
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('phone', 'Phone') !!}<span class="text-danger pl-1">*</span>
                                        {!! Collective\Html\FormFacade::number('phone',null, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('address', 'Address') !!}
                                        {!! Collective\Html\FormFacade::textarea('address',null, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('email', 'Email') !!}<span class="text-danger pl-1">*</span>
                                        {!! Collective\Html\FormFacade::email('email',null, ['class' => 'form-control','required' => 'required']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('password', 'Password') !!}<span class="text-danger pl-1">*</span>
                                        {!! Collective\Html\FormFacade::text('password',null, ['class' => 'form-control','required' => 'required']) !!}
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="card">
                                <div class="card-header"><h4>{{__('Company Detail')}}</h4></div>
                                <div class="card-body">

                                    @csrf
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('employee_id', 'Employee ID') !!}
                                        {!! Collective\Html\FormFacade::text('employee_id', \Illuminate\Support\Facades\Auth::user()->employeeIdFormat(1), ['class' => 'form-control','disabled'=>'disabled']) !!}
                                    </div>

                                    <div class="form-group">
                                        {{ Collective\Html\FormFacade::label('branch_id', __('Branch')) }}
                                        {{ Collective\Html\FormFacade::select('branch_id', $branches,null, array('class' => 'form-control select2','required'=>'required')) }}
                                    </div>

                                    <div class="form-group">
                                        {{ Collective\Html\FormFacade::label('department_id', __('Department')) }}
                                        {{ Collective\Html\FormFacade::select('department_id', $departments,null, array('class' => 'form-control select2','id'=>'department_id','required'=>'required')) }}
                                    </div>

                                    <div class="form-group">
                                        {{ Collective\Html\FormFacade::label('designation_id', __('Designation')) }}
                                        <select class="select2 form-control select2-multiple" id="designation_id" name="designation_id" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}">
                                            <option value="">{{__('Select any Designation')}}</option>

                                        </select>
                                    </div>

                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('company_doj', 'Company Date Of Joining') !!}
                                        {!! Collective\Html\FormFacade::text('company_doj', null, ['class' => 'form-control datepicker','required' => 'required']) !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 ">
                            <div class="card">
                                <div class="card-header"><h4>{{__('Document')}}</h4></div>
                                <div class="card-body">
                                    @foreach($documents as $key=>$document)

                                        <div class="row">
                                            <div class="form-group col-10">
                                                <div class="float-left">
                                                    <label for="document" class="float-left pt-1">{{ $document->name }} @if($document->is_required == 1) <span class="text-danger">*</span> @endif</label>
                                                </div>
                                                <div class="float-right">
                                                    <input class="form-control float-right @error('document') is-invalid @enderror border-0" @if($document->is_required == 1) required @endif name="document[{{ $document->id}}]" type="file" id="document[{{ $document->id }}]" accept="image/*">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="card">
                                <div class="card-header"><h4>{{__('Bank Account Detail')}}</h4></div>
                                <div class="card-body">

                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('account_holder_name', 'Account Holder Name') !!}
                                        {!! Collective\Html\FormFacade::text('account_holder_name', null, ['class' => 'form-control']) !!}

                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('account_number', 'Account Number') !!}
                                        {!! Collective\Html\FormFacade::text('account_number', null, ['class' => 'form-control']) !!}

                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('bank_name', 'Bank Name') !!}
                                        {!! Collective\Html\FormFacade::text('bank_name', null, ['class' => 'form-control']) !!}

                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('bank_identifier_code', 'Bank Identifier Code') !!}
                                        {!! Collective\Html\FormFacade::text('bank_identifier_code',null, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('branch_location', 'Branch Location') !!}
                                        {!! Collective\Html\FormFacade::text('branch_location',null, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('tax_payer_id', 'Tax Payer Id') !!}
                                        {!! Collective\Html\FormFacade::text('tax_payer_id',null, ['class' => 'form-control']) !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {!! Collective\Html\FormFacade::submit('save', ['class' => 'btn btn-primary btn-lg float-right']) !!}
            {!! Collective\Html\FormFacade::close() !!}
        </section>
    </div>

@endsection

@push(StacksConstants::ADM_SCR_PG)

    <script>

        $(document).ready(function () {
            var d_id = $('#department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function () {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        $(function getDesignation(did) {
            $.ajax({
                url: '{{route('employee.json')}}',
                type: 'POST',
                data: {
                    "department_id": did, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#designation_id').empty();
                    $('#designation_id').append('<option value="">Select any Designation</option>');
                    $.each(data, function (key, value) {
                        $('#designation_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        });
    </script>
@endpush
