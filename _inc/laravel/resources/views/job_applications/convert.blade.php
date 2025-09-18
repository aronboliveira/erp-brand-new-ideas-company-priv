@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Collective\Html\FormFacade as Form;

    $user = Auth::user() ?? null;
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $convertUrl = route(VW::JB.'.on.board.convert', data_get($jobOnBoard,'id'));
    $convertGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::JB, 'onboard_convert_route_unavailable') : 'Convert route is unavailable. Please contact technical support or your domain administrator.') ?? __('Convert route is unavailable. Please contact technical support or your domain administrator.');

    $designationUrl = route(VW::DSG.'.byDepartment');
    $designationGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::DSG, 'designation_by_department_unavailable') : 'Designation list route is unavailable. Please contact technical support or your domain administrator.') ?? __('Designation list route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Convert To Employee') }}
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        {!! Form::open(['url'=>$convertUrl,'method'=>'post','enctype'=>'multipart/form-data','data-url'=>$convertUrl,'data-guard-msg'=>$convertGuard]) !!}
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::CD }} card-fluid">
                <div class="card-header"><h6 class="{{ VC::MB0 }}">{{ __('Personal Detail') }}</h6></div>
                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('name', __('Name'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::text('name', data_get($jobOnBoard,'applications.name',__('No name available')), ['class'=>VC::FM_CT,'required'=>'required']) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('phone', __('Phone'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::number('phone', data_get($jobOnBoard,'applications.phone',__('Failed to get phone')), ['class'=>VC::FM_CT]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('dob', __('Date of Birth'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::date('dob', data_get($jobOnBoard,'applications.dob',''), ['class'=>VC::FM_CT.' datepicker']) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('gender', __('Gender'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                            <div class="d-flex radio-check {{ VC::MT2 }}">
                                <div class="{{ VC::FM_CHK_IL_GP_COLM6 }}">
                                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input" {{ data_get($jobOnBoard,'applications.gender')==='Male'?'checked':'' }}>
                                    <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                </div>
                                <div class="{{ VC::FM_CHK_IL_GP_COLM6 }}">
                                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input" {{ data_get($jobOnBoard,'applications.gender')==='Female'?'checked':'' }}>
                                    <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('email', __('Email'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::email('email', old('email'), ['class'=>VC::FM_CT,'required'=>'required']) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('password', __('Password'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::password('password', ['class'=>VC::FM_CT,'required'=>'required']) !!}
                        </div>
                    </div>
                    <div class="{{ VC::FM_G }}">
                        {!! Form::label('address', __('Address'), ['class'=>VC::FM_LB]) !!}<span class="text-danger pl-1">*</span>
                        {!! Form::textarea('address', old('address'), ['class'=>VC::FM_CT,'rows'=>2]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::CD }} card-fluid">
                <div class="card-header"><h6 class="{{ VC::MB0 }}">{{ __('Company Detail') }}</h6></div>
                <div class="card-body employee-detail-create-body">
                    <div class="{{ VC::RW }}">
                        @csrf
                        <div class="{{ VC::FM_GCB12 }}">
                            {!! Form::label('employee_id', __('Employee ID'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::text('employee_id', $employeesId ?? __('Failed to get employee id'), ['class'=>VC::FM_CT,'disabled'=>'disabled']) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('branch_id', __('Branch'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::select('branch_id', $branches ?? [], data_get($jobOnBoard,'applications.jobs.branch',''), ['class'=>VC::FM_CT,'required'=>'required']) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('department_id', __('Department'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::select('department_id', $departments ?? [], null, ['class'=>VC::FM_CT,'id'=>'department_id','required'=>'required','data-designation-url'=>$designationUrl,'data-guard-msg'=>$designationGuard]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB12 }}">
                            {!! Form::label('designation_id', __('Designation'), ['class'=>VC::FM_LB]) !!}
                            <select class="{{ VC::FM_CT }}" id="designation_id" name="designation_id" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}" data-url="{{ $designationUrl }}" data-guard-msg="{{ $designationGuard }}">
                                <option value="">{{ __('Select any Designation') }}</option>
                            </select>
                        </div>
                        <div class="{{ VC::FM_GCB12 }}">
                            {!! Form::label('company_doj', __('Company Date Of Joining'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::date('company_doj', data_get($jobOnBoard,'joining_date',null), ['class'=>VC::FM_CT.' datepicker','required'=>'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::CD }} card-fluid">
                <div class="card-header"><h6 class="{{ VC::MB0 }}">{{ __('Document') }}</h6></div>
                <div class="card-body employee-detail-create-body">
                    @php
                        $docs = (is_array($documents??null) && count($documents??[])) ? $documents : ((($documents??null) instanceof \Illuminate\Support\Collection && $documents->isNotEmpty()) ? $documents : []);
                    @endphp
                    @if(!empty($docs))
                        @foreach($docs as $document)
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::FM_GCB12 }}">
                                    <div class="float-left col-4">
                                        <label for="document" class="float-left pt-1 {{ VC::FM_LB }}">{{ data_get($document,'name',__('No document name available')) }} @if((int) (data_get($document,'is_required',0))===1) <span class="text-danger">*</span> @endif</label>
                                    </div>
                                    <div class="float-right col-8">
                                        <input type="hidden" name="emp_doc_id[{{ data_get($document,'id','') }}]" value="{{ data_get($document,'id','') }}">
                                        <div class="choose-file {{ VC::FM_G }}">
                                            <label for="document[{{ data_get($document,'id','') }}]">
                                                <div>{{ __('Choose File') }}</div>
                                                <input class="{{ VC::FM_CT }} @error('document') is-invalid @enderror border-0" @if((int) (data_get($document,'is_required',0))===1) required @endif name="document[{{ data_get($document,'id','') }}]" type="file" id="document[{{ data_get($document,'id','') }}]" data-filename="{{ data_get($document,'id','') . '_filename' }}">
                                            </label>
                                            <p class="{{ data_get($document,'id','') . '_filename' }}"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="{{ VC::RW }}"><div class="{{ VC::CM12 }}"><h6 class="text-center">{{ __('No documents available') }}</h6></div></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::CD }} card-fluid">
                <div class="card-header"><h6 class="{{ VC::MB0 }}">{{ __('Bank Account Detail') }}</h6></div>
                <div class="card-body employee-detail-create-body">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('account_holder_name', __('Account Holder Name'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::text('account_holder_name', old('account_holder_name'), ['class'=>VC::FM_CT]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('account_number', __('Account Number'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::number('account_number', old('account_number'), ['class'=>VC::FM_CT]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('bank_name', __('Bank Name'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::text('bank_name', old('bank_name'), ['class'=>VC::FM_CT]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::text('bank_identifier_code', old('bank_identifier_code'), ['class'=>VC::FM_CT]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('branch_location', __('Branch Location'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::text('branch_location', old('branch_location'), ['class'=>VC::FM_CT]) !!}
                        </div>
                        <div class="{{ VC::FM_GCB6 }}">
                            {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class'=>VC::FM_LB]) !!}
                            {!! Form::text('tax_payer_id', old('tax_payer_id'), ['class'=>VC::FM_CT]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }} text-end">
            {!! Form::submit(__('Create'), ['class'=>VC::BT_PRM.' radius-10px']) !!}
            {!! Form::close() !!}
        </div>
    </div>
@endsection
    
@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/jobs/boards/convert.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/boards/lang/convert.js') }}"></script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (
                    el?.getAttribute('data-sv-localized') === 'true' ||
                    el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true'
                ) {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (
                        sessionStorage.getItem('erp-np-lang') ||
                        document.documentElement.lang ||
                        'en'
                    )
                        .toLowerCase()
                        .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg =
                        window.translations?.[lang]?.[key] ||
                        el.getAttribute(DATA_GUARD_MSG) ||
                        window.translations?.['en']?.[key] ||
                        ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap =
                    document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="Close"></button>
                            </div>`;
                        document.body.appendChild(toast);
                    }
                    new bootstrap.Toast(
                        document.querySelector('#error-toast')
                    ).show();
                } else {
                    alert(message);
                }
            };

            try {
                if (typeof $ === 'undefined') {
                    console.error('jQuery is required');
                    return;
                }

                const loadDesignations = (did) => {
                    if (did == null) return;
                    const url = '{{ route("employees.json") }}' ?? '';
                    if (!url) return;
                    try {
                        $.ajax({
                            url,
                            type: 'POST',
                            data: {
                                department_id: did,
                                _token: '{{ csrf_token() }}'
                            },
                            success: (data) => {
                                try {
                                    const $sel = $('#designation_id');
                                    $sel.empty();
                                    $sel.append(
                                        `<option value="">{{ __('Select any Designation') }}</option>`
                                    );
                                    $.each(data, (key, value) => {
                                        $sel.append(
                                            `<option value="${key}">${value}</option>`
                                        );
                                    });
                                } catch {
                                    const el = document.getElementById('designation_id');
                                    handleErrorDisplay(el, 'designation_fetch_unavailable');
                                }
                            }
                        });
                    } catch {
                        const el = document.getElementById('designation_id');
                        handleErrorDisplay(el, 'designation_fetch_unavailable');
                    }
                };

                const init = () => {
                    const did = $('#department_id').val();
                    loadDesignations(did);
                };

                $(init);
                $(document).on(
                    'change',
                    'select[name=department_id]',
                    function () {
                        loadDesignations($(this).val());
                    }
                );

            } catch (e) {
                console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
