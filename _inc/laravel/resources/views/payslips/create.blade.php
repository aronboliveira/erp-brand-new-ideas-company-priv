@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $storeBase  = VW::EMP . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeName  = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeName ? route($storeName) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::EMP, 'store_employee_route_unavailable') ?? 'Store employee route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_CTT)
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __('Employee') }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></div>
                    <div class="breadcrumb-item">{{ __('Employee') }}</div>
                </div>
            </div>
            {!! Form::open([
                'url'                   => $storeUrl,
                'method'                => 'post',
                'enctype'               => 'multipart/form-data',
                'id'                    => 'create_employee',
                'data-resolved-action'  => $storeUrl,
                'data-guard-msg'        => $storeGuard,
                'data-sv-localized'     => 'true',
            ]) !!}
                @csrf
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h4>{{ __('Personal Detail') }}</h4></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        {!! Form::label('name', __('Name')) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('name', old('name'), ['class' => 'form-control', 'required' => 'required']) !!}
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('dob', __('Date of Birth')) !!}
                                                {!! Form::text('dob', old('dob'), ['class' => 'form-control datepicker']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('gender', __('Gender')) !!}<span class="text-danger pl-1">*</span>
                                                <div class="mt-2">
                                                    {{ Form::radio('gender', 'Male', true, ['class' => 'mt-2', 'id' => 'gender_male']) }} <label for="gender_male" class="me-3">{{ __('Male') }}</label>
                                                    {{ Form::radio('gender', 'Female', false, ['id' => 'gender_female']) }} <label for="gender_female">{{ __('Female') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('phone', __('Phone')) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::number('phone', old('phone'), ['class' => 'form-control', 'required' => 'required']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('address', __('Address')) !!}
                                        {!! Form::textarea('address', old('address'), ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('email', __('Email')) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', old('email'), ['class' => 'form-control', 'required' => 'required']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('password', __('Password')) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::password('password', ['class' => 'form-control', 'required' => 'required']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h4>{{ __('Company Detail') }}</h4></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        {!! Form::label('employee_id', __('Employee ID')) !!}
                                        {!! Form::text('employee_id', $user?->employeeIdFormat(1) ?? '', ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('branch_id', __('Branch')) }}
                                        {{ Form::select('branch_id', is_array($branches ?? null) ? $branches : (($branches ?? null) instanceof Collection ? $branches->toArray() : []), old('branch_id'), ['class' => 'form-control select2', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('department_id', __('Department')) }}
                                        {{ Form::select('department_id', is_array($departments ?? null) ? $departments : (($departments ?? null) instanceof Collection ? $departments->toArray() : []), old('department_id'), ['class' => 'form-control select2', 'id' => 'department_id', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('designation_id', __('Designation')) }}
                                        <select class="select2 form-control select2-multiple" id="designation_id" name="designation_id" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}">
                                            <option value="">{{ __('Select any Designation') }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('company_doj', __('Company Date Of Joining')) !!}
                                        {!! Form::text('company_doj', old('company_doj'), ['class' => 'form-control datepicker', 'required' => 'required']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h4>{{ __('Document') }}</h4></div>
                                <div class="card-body">
                                    @forelse(((($documents ?? null) instanceof Collection) || is_array($documents ?? null)) ? $documents : [] as $key => $document)
                                        @php
                                            $docId   = (string) (data_get($document, 'id', ''));
                                            $docName = (string) (data_get($document, 'name') ?: __('Unnamed Document'));
                                            $isReq   = (int) (data_get($document, 'is_required', 0)) === 1;
                                        @endphp
                                        <div class="row">
                                            <div class="form-group col-10">
                                                <div class="float-left">
                                                    <label for="document_{{ $docId }}" class="float-left pt-1">{{ $docName }} @if($isReq) <span class="text-danger">*</span> @endif</label>
                                                </div>
                                                <div class="float-right">
                                                    <input class="form-control float-right border-0" @if($isReq) required @endif name="document[{{ $docId }}]" type="file" id="document_{{ $docId }}" accept="image/*">
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted">{{ __('No documents available') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h4>{{ __('Bank Account Detail') }}</h4></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        {!! Form::label('account_holder_name', __('Account Holder Name')) !!}
                                        {!! Form::text('account_holder_name', old('account_holder_name'), ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('account_number', __('Account Number')) !!}
                                        {!! Form::text('account_number', old('account_number'), ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('bank_name', __('Bank Name')) !!}
                                        {!! Form::text('bank_name', old('bank_name'), ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('bank_identifier_code', __('Bank Identifier Code')) !!}
                                        {!! Form::text('bank_identifier_code', old('bank_identifier_code'), ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('branch_location', __('Branch Location')) !!}
                                        {!! Form::text('branch_location', old('branch_location'), ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('tax_payer_id', __('Tax Payer Id')) !!}
                                        {!! Form::text('tax_payer_id', old('tax_payer_id'), ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::submit(__('Save'), ['class' => 'btn btn-primary btn-lg float-right']) !!}
            {!! Form::close() !!}
        </section>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/payslips/lang/storeEnv.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/payslips/storeEnv.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/payslips/store.js') }}"></script>
@endpush
