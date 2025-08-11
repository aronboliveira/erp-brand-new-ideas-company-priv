@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Convert To Employee')}}
@endsection
@section('content')
    <div class="row">
        {{Collective\Html\FormFacade::open(array('route'=>array('job.on.board.convert',$jobOnBoard->id),'method'=>'post','enctype'=>'multipart/form-data'))}}
    </div>
    <div class="row">
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-header"><h6 class="mb-0">{{__('Personal Detail')}}</h6></div>
                <div class="card-body ">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::text('name', !empty($jobOnBoard->applications)?$jobOnBoard->applications->name:'', ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('phone', __('Phone'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::number('phone',!empty($jobOnBoard->applications)?$jobOnBoard->applications->phone:'', ['class' => 'form-control']) !!}
                        </div>

                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Collective\Html\FormFacade::date('dob', !empty($jobOnBoard->applications)?$jobOnBoard->applications->dob:'', ['class' => 'form-control datepicker']) !!}
                            </div>


                            <div class="form-group col-md-6 ">
                                {!! Collective\Html\FormFacade::label('gender', __('Gender'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                <div class="d-flex radio-check mt-2">
                                    <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                        <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input" {{(!empty($jobOnBoard->applications) && $jobOnBoard->applications->gender=='Male')?'checked':''}}>
                                        <label class="form-check-label" for="g_male">{{__('Male')}}</label>
                                    </div>
                                    <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                        <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input" {{(!empty($jobOnBoard->applications) && $jobOnBoard->applications->gender=='Female')?'checked':''}}>
                                        <label class="form-check-label" for="g_female">{{__('Female')}}</label>
                                    </div>
                                </div>
                            </div>

                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('email', __('Email'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::email('email',old('email'), ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('password', __('Password'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::password('password', ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Collective\Html\FormFacade::label('address', __('Address'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                        {!! Collective\Html\FormFacade::textarea('address',old('address'), ['class' => 'form-control','rows'=>2]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-header"><h6 class="mb-0">{{__('Company Detail')}}</h6></div>
                <div class="card-body employee-detail-create-body">
                    <div class="row">
                        @csrf
                        <div class="form-group col-md-12">
                            {!! Collective\Html\FormFacade::label('employee_id', __('Employee ID'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('employee_id', $employeesId, ['class' => 'form-control','disabled'=>'disabled']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {{ Collective\Html\FormFacade::label('branch_id', __('Branch'),['class'=>'form-label']) }}
                            {{ Collective\Html\FormFacade::select('branch_id', $branches,!empty($jobOnBoard->applications)?!empty($jobOnBoard->applications->jobs)?$jobOnBoard->applications->jobs->branch:'':'', array('class' => 'form-control','required'=>'required')) }}
                        </div>

                        <div class="form-group col-md-6">
                            {{ Collective\Html\FormFacade::label('department_id', __('Department'),['class'=>'form-label']) }}
                            {{ Collective\Html\FormFacade::select('department_id', $departments,null, array('class' => 'form-control','id'=>'department_id','required'=>'required')) }}
                        </div>

                        <div class="form-group col-md-12">
                            {{ Collective\Html\FormFacade::label('designation_id', __('Designation'),['class'=>'form-label']) }}
                            <select class=" form-control " id="designation_id" name="designation_id" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}">
                                <option value="">{{__('Select any Designation')}}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12 ">
                            {!! Collective\Html\FormFacade::label('company_doj', __('Company Date Of Joining'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::date('company_doj', $jobOnBoard->joining_date, ['class' => 'form-control datepicker','required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-header"><h6 class="mb-0">{{__('Document')}}</h6></div>
                <div class="card-body employee-detail-create-body">
                    @foreach($documents as $key=>$document)
                        <div class="row">
                            <div class="form-group col-12">
                                <div class="float-left col-4">
                                    <label for="document" class="float-left pt-1 form-label">{{ $document->name }} @if($document->is_required == 1) <span class="text-danger">*</span> @endif</label>
                                </div>
                                <div class="float-right col-8">
                                    <input type="hidden" name="emp_doc_id[{{ $document->id}}]" id="" value="{{$document->id}}">
                                    <div class="choose-file form-group">
                                        <label for="document[{{ $document->id }}]">
                                            <div>{{__('Choose File')}}</div>
                                            <input class="form-control  @error('document') is-invalid @enderror border-0" @if($document->is_required == 1) required @endif name="document[{{ $document->id}}]" type="file" id="document[{{ $document->id }}]" data-filename="{{ $document->id.'_filename'}}">
                                        </label>
                                        <p class="{{ $document->id.'_filename'}}"></p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-header"><h6 class="mb-0">{{__('Bank Account Detail')}}</h6></div>
                <div class="card-body employee-detail-create-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('account_holder_name', __('Account Holder Name'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('account_holder_name', old('account_holder_name'), ['class' => 'form-control']) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('account_number', __('Account Number'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::number('account_number', old('account_number'), ['class' => 'form-control']) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('bank_name', __('Bank Name'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('bank_name', old('bank_name'), ['class' => 'form-control']) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('bank_identifier_code', __('Bank Identifier Code'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('bank_identifier_code',old('bank_identifier_code'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('branch_location', __('Branch Location'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('branch_location',old('branch_location'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('tax_payer_id', __('Tax Payer Id'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('tax_payer_id',old('tax_payer_id'), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-end" >
            {!! Collective\Html\FormFacade::submit('Create', ['class' => 'btn btn-primary radius-10px']) !!}
            {{--            </form>--}}
            {{Collective\Html\FormFacade::close()}}
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async>
        window.translations = {
            ar:  { designation_fetch_unavailable: 'لا يمكن جلب المسميات الوظيفية' },
            da:  { designation_fetch_unavailable: 'Kan ikke hente titler' },
            de:  { designation_fetch_unavailable: 'Kann Bezeichnungen nicht abrufen' },
            en:  { designation_fetch_unavailable: 'Cannot fetch designations' },
            es:  { designation_fetch_unavailable: 'No se pueden obtener las designaciones' },
            fr:  { designation_fetch_unavailable: 'Impossible de récupérer les désignations' },
            he:  { designation_fetch_unavailable: 'לא ניתן להביא את התפקידים' },
            it:  { designation_fetch_unavailable: 'Impossibile recuperare le designazioni' },
            ja:  { designation_fetch_unavailable: '役職を取得できません' },
            nl:  { designation_fetch_unavailable: 'Kan functietitels niet ophalen' },
            pl:  { designation_fetch_unavailable: 'Nie można pobrać stanowisk' },
            pt:  { designation_fetch_unavailable: 'Não foi possível obter as designações' },
            'pt-br': { designation_fetch_unavailable: 'Não foi possível obter as designações' },
            ru:  { designation_fetch_unavailable: 'Не удалось получить должности' },
            tr:  { designation_fetch_unavailable: 'Unvanlar alınamadı' },
            zh:  { designation_fetch_unavailable: '无法获取职务' }
        };
    </script>
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
                    const url = '{{ route("employee.json") }}' ?? '';
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
