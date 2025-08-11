@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Edit Employee')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('employee.index')}}">{{__('Employee')}}</a></li>
    <li class="breadcrumb-item">{{$employeesId}}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            {{ Collective\Html\FormFacade::model($employee, array('route' => array('employee.update', $employee->id), 'method' => 'PUT' , 'enctype' => 'multipart/form-data')) }}
            @csrf
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 ">
            <div class="card emp_details">
                <div class="card-header"><h6 class="mb-0">{{__('Personal Detail')}}</h6></div>
                <div class="card-body employee-detail-edit-body">

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::text('name', null, ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('phone', __('Phone'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::number('phone',null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-6">

                            {!! Collective\Html\FormFacade::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Collective\Html\FormFacade::date('dob', null, ['class' => 'form-control']) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('gender', __('Gender'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            <div class="d-flex radio-check mt-2">
                                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input" {{($employee->gender == 'Male')?'checked':''}}>
                                    <label class="form-check-label" for="g_male">{{__('Male')}}</label>
                                </div>
                                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input" {{($employee->gender == 'Female')?'checked':''}}>
                                    <label class="form-check-label" for="g_female">{{__('Female')}}</label>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Collective\Html\FormFacade::label('address', __('Address'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                        {!! Collective\Html\FormFacade::textarea('address',null, ['class' => 'form-control','rows'=>2]) !!}
                    </div>
                    @if(\Auth::user()->type=='employee')
                        {!! Collective\Html\FormFacade::submit('Update', ['class' => 'btn-create btn-xs badge-blue radius-10px float-right']) !!}
                    @endif
                </div>
            </div>
        </div>
        @if(\Auth::user()->type!='Employee')
            <div class="col-md-6 ">
                <div class="card emp_details">
                    <div class="card-header"><h6 class="mb-0">{{__('Company Detail')}}</h6></div>
                    <div class="card-body employee-detail-edit-body">
                        <div class="row">
                            @csrf
                            <div class="form-group col-md-12">
                                {!! Collective\Html\FormFacade::label('employee_id', __('Employee ID'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::text('employee_id',$employeesId, ['class' => 'form-control','disabled'=>'disabled']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {{ Collective\Html\FormFacade::label('branch_id', __('Branch'),['class'=>'form-label']) }}
                                {{ Collective\Html\FormFacade::select('branch_id', $branches,null, array('class' => 'form-control select','required'=>'required','id' => 'branch_id')) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Collective\Html\FormFacade::label('department_id', __('Department'),['class'=>'form-label']) }}
                                {{ Collective\Html\FormFacade::select('department_id', $departments,null, array('class' => 'form-control select','required'=>'required','id' => 'department_id')) }}
                                {{-- <select class=" select form-control " id="department_id" name="department_id"  >
                                    @foreach($departmentData as $key=>$val )
                                        <option value="{{$key}}" {{$key==$employee->department_id?'selected':''}}>{{$val}}</option>
                                    @endforeach
                                </select> --}}

                            </div>
                            <div class="form-group col-md-6">
                                {{ Collective\Html\FormFacade::label('designation_id', __('Designation'),['class'=>'form-label']) }}
                                <select class="select form-control " id="designation_id" name="designation_id" ></select>

                            </div>
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('company_doj', 'Company Date Of Joining',['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::date('company_doj', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-6 ">
                <div class="employee-detail-wrap ">
                    <div class="card emp_details">
                        <div class="card-header"><h6 class="mb-0">{{__('Company Detail')}}</h6></div>
                        <div class="card-body employee-detail-edit-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Branch')}}</strong>
                                        <span>{{!empty($employee->branch)?$employee->branch->name:''}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong>{{__('Department')}}</strong>
                                        <span>{{!empty($employee->department)?$employee->department->name:''}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong>{{__('Designation')}}</strong>
                                        <span>{{!empty($employee->designation)?$employee->designation->name:''}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Date Of Joining')}}</strong>
                                        <span>{{\Auth::user()->dateFormat($employee->company_doj)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @if(\Auth::user()->type!='Employee')
        <div class="row">
            <div class="col-md-6 ">
                <div class="card emp_details">
                    <div class="card-header"><h6 class="mb-0">{{__('Document')}}</h6></div>
                    <div class="card-body employee-detail-edit-body">
                        @php
                            $employeedoc = $employee->documents()->pluck('document_value',__('document_id'));
                        @endphp

                        @foreach($documents as $key=>$document)
                            <div class="row">
                                <div class="form-group col-12">
                                    <div class="float-left col-4">
                                        <label for="document" class="float-left pt-1 form-label">{{ $document->name }} @if($document->is_required == 1) <span class="text-danger">*</span> @endif</label>
                                    </div>
                                    <div class="float-right col-4">
                                        <input type="hidden" name="emp_doc_id[{{ $document->id}}]" id="" value="{{$document->id}}">
                                        <div class="choose-file form-group">
                                            <label for="document[{{ $document->id }}]">
                                                <input class="form-control @if(!empty($employeedoc[$document->id])) float-left @endif @error('document') is-invalid @enderror border-0" @if($document->is_required == 1 && empty($employeedoc[$document->id]) ) required @endif name="document[{{ $document->id}}]"  onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])" type="file"  data-filename="{{ $document->id.'_filename'}}">
                                            </label>
                                            <p class="{{ $document->id.'_filename'}}"></p>

                                            @php
                                                $logo=\App\Models\Utility::getFile('uploads/document/');
                                            @endphp

{{--                                            <img id="{{'blah'.$key}}" src=""  width="25%" />--}}
                                            <img id="{{'blah'.$key}}" src="{{ (isset($employeedoc[$document->id]) && !empty($employeedoc[$document->id])?$logo.'/'.$employeedoc[$document->id]:'') }}"  width="25%" />

                                        </div>


{{--                                        @if(!empty($employeedoc[$document->id]))--}}
{{--                                            <br> <span class="text-xs"><a href="{{ (!empty($employeedoc[$document->id])?asset(Storage::url('uploads/document')).'/'.$employeedoc[$document->id]:'') }}" target="_blank">{{ (!empty($employeedoc[$document->id])?$employeedoc[$document->id]:'') }}</a>--}}
{{--                                                    </span>--}}
{{--                                        @endif--}}
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card emp_details">
                    <div class="card-header"><h6 class="mb-0">{{__('Bank Account Detail')}}</h6></div>
                    <div class="card-body employee-detail-edit-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('account_holder_name', __('Account Holder Name'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::text('account_holder_name', null, ['class' => 'form-control']) !!}

                            </div>
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('account_number', __('Account Number'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::number('account_number', null, ['class' => 'form-control']) !!}

                            </div>
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('bank_name', __('Bank Name'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::text('bank_name', null, ['class' => 'form-control']) !!}

                            </div>
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('bank_identifier_code', __('Bank Identifier Code'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::text('bank_identifier_code',null, ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('branch_location', __('Branch Location'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::text('branch_location',null, ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Collective\Html\FormFacade::label('tax_payer_id', __('Tax Payer Id'),['class'=>'form-label']) !!}
                                {!! Collective\Html\FormFacade::text('tax_payer_id',null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-6 ">
                <div class="employee-detail-wrap">
                    <div class="card emp_details">
                        <div class="card-header"><h6 class="mb-0">{{__('Document Detail')}}</h6></div>
                        <div class="card-body employee-detail-edit-body">
                            <div class="row">
                                @php
                                    $employeedoc = $employee->documents()->pluck('document_value',__('document_id'));
                                @endphp
                                @foreach($documents as $key=>$document)
                                    <div class="col-md-12">
                                        <div class="info">
                                            <strong>{{$document->name }}</strong>
                                            <span><a href="{{ (!empty($employeedoc[$document->id])?asset(Storage::url('uploads/document')).'/'.$employeedoc[$document->id]:'') }}" target="_blank">{{ (!empty($employeedoc[$document->id])?$employeedoc[$document->id]:'') }}</a></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ">
                <div class="employee-detail-wrap">
                    <div class="card emp_details">
                        <div class="card-header"><h6 class="mb-0">{{__('Bank Account Detail')}}</h6></div>
                        <div class="card-body employee-detail-edit-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Account Holder Name')}}</strong>
                                        <span>{{$employee->account_holder_name}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong>{{__('Account Number')}}</strong>
                                        <span>{{$employee->account_number}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong>{{__('Bank Name')}}</strong>
                                        <span>{{$employee->bank_name}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Bank Identifier Code')}}</strong>
                                        <span>{{$employee->bank_identifier_code}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Branch Location')}}</strong>
                                        <span>{{$employee->branch_location}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Tax Payer Id')}}</strong>
                                        <span>{{$employee->tax_payer_id}}</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(\Auth::user()->type != 'employee')
        <div class="row">
            <div class="col-12">
                <input type="submit" value="{{__('Update')}}" class="btn btn-primary float-end">
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            {!! Collective\Html\FormFacade::close() !!}
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar:    { file_name_append_failed:'تعذر عرض اسم الملف.',        designation_fetch_failed:'فشل جلب المناصب الوظيفية.' },
        da:    { file_name_append_failed:'Kunne ikke vise filnavnet.',  designation_fetch_failed:'Kunne ikke hente betegnelse.' },
        de:    { file_name_append_failed:'Dateiname konnte nicht angezeigt werden.', designation_fetch_failed:'Abrufen der Bezeichnungen fehlgeschlagen.' },
        en:    { file_name_append_failed:'Unable to display file name.',            designation_fetch_failed:'Failed to fetch designations.' },
        es:    { file_name_append_failed:'No se pudo mostrar el nombre del archivo.', designation_fetch_failed:'Error al obtener designaciones.' },
        fr:    { file_name_append_failed:'Impossible d’afficher le nom du fichier.',  designation_fetch_failed:'Échec de la récupération des intitulés.' },
        it:    { file_name_append_failed:'Impossibile mostrare il nome del file.',    designation_fetch_failed:'Impossibile recuperare le mansioni.' },
        ja:    { file_name_append_failed:'ファイル名を表示できませんでした。',            designation_fetch_failed:'役職を取得できませんでした。' },
        nl:    { file_name_append_failed:'Bestandsnaam kon niet worden weergegeven.', designation_fetch_failed:'Ophalen van functies mislukt.' },
        pl:    { file_name_append_failed:'Nie można wyświetlić nazwy pliku.',         designation_fetch_failed:'Nie udało się pobrać stanowisk.' },
        pt:    { file_name_append_failed:'Não foi possível exibir o nome do ficheiro.', designation_fetch_failed:'Falha ao obter designações.' },
        'pt-br':{ file_name_append_failed:'Não foi possível exibir o nome do arquivo.', designation_fetch_failed:'Falha ao buscar cargos.' },
        ru:    { file_name_append_failed:'Не удалось отобразить имя файла.',          designation_fetch_failed:'Не удалось получить должности.' },
        tr:    { file_name_append_failed:'Dosya adı gösterilemedi.',                  designation_fetch_failed:'Unvanlar alınamadı.' },
        zh:    { file_name_append_failed:'无法显示文件名。',                             designation_fetch_failed:'获取职位失败。' }
        };
    </script>

    <script defer>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langKey = 'erp-np-lang';
        const toastContainerId = 'toast-box';
        const csrfToken = '{{ csrf_token() }}';
        
        const getMsg = (key, el) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true'
            || el.getAttribute(dataClientLocalized) === 'true') {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (sessionStorage.getItem(langKey)
                || document.documentElement.lang
                || 'en')
                .toLowerCase()
                .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = window.translations?.[lang]?.[key]
                || el.getAttribute(dataGuardMsg)
                || window.translations?.en?.[key]
                || errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
            }
            }
            return msg;
        };
        
        const showToast = message => {
            const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .some(l => /bootstrap/i.test(l.href))
            && window.bootstrap?.Toast;
            if (hasBs) {
            let container = document.getElementById(toastContainerId);
            if (!container) {
                container = document.createElement('div');
                container.id = toastContainerId;
                container.setAttribute('aria-live', 'polite');
                container.setAttribute('aria-atomic', 'true');
                document.body.appendChild(container);
            }
            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.innerHTML = `<div class="toast-body">${message}</div>`;
            container.appendChild(toastEl);
            bootstrap.Toast.getOrCreateInstance(toastEl).show();
            } else {
            alert(message);
            }
        };
        
        let queuedError = '';
        const flushError = () => {
            if (queuedError) {
            showToast(queuedError);
            queuedError = '';
            }
        };
        document.addEventListener('click', flushError);
        new MutationObserver((records, obs) => {
            for (const r of records) {
            for (const n of r.removedNodes) {
                if (n === document.documentElement) {
                document.removeEventListener('click', flushError);
                obs.disconnect();
                }
            }
            }
        }).observe(document.body, { childList: true, subtree: true });
        
        document.querySelectorAll('input[type="file"][data-filename]')
            .forEach(input => {
            if (input.dataset.listenerAttached === 'true') return;
            input.dataset.listenerAttached = 'true';
        
            const onChange = e => {
                try {
                const name = e.target.files?.[0]?.name;
                if (!name) return;
                const target = document.querySelector('.' + input.dataset.filename);
                if (target && !target.textContent.includes(name)) {
                    target.textContent += name;
                }
                } catch {
                queuedError = getMsg('file_name_append_failed', input);
                }
            };
            input.addEventListener('change', onChange);
        
            new MutationObserver((recs, obs) => {
                for (const r of recs) {
                for (const n of r.removedNodes) {
                    if (n === input) {
                    input.removeEventListener('change', onChange);
                    obs.disconnect();
                    }
                }
                }
            }).observe(document.body, { childList: true, subtree: true });
            });
        
        const getDepartment = branchId => {
            try {
            $.ajax({
                url: '{{ route("employee.getdepartment") }}',
                type: 'POST',
                data: { branch_id: branchId ?? '', _token: csrfToken },
                success: data => {
                try {
                    const sel = document.getElementById('department_id');
                    if (!sel) return;
                    sel.innerHTML = '<option value="" disabled>{{ __("Select any Department") }}</option>';
                    for (const [k, v] of Object.entries(data || {})) {
                    sel.insertAdjacentHTML('beforeend', `<option value="${k}">${v}</option>`);
                    }
                    sel.value = '';
                } catch {
                    queuedError = getMsg('department_fetch_failed', document.getElementById('branch_id'));
                }
                },
                error: () => {
                queuedError = getMsg('department_fetch_failed', document.getElementById('branch_id'));
                }
            });
            } catch {
            queuedError = getMsg('department_fetch_failed', document.getElementById('branch_id'));
            }
        };
        
        document.addEventListener('change', e => {
            if (e.target && e.target.matches('#branch_id')) {
            getDepartment(e.target.value);
            }
        });
        
        const getDesignation = deptId => {
            try {
            $.ajax({
                url: '{{ route("employee.json") }}',
                type: 'POST',
                data: { department_id: deptId ?? '', _token: csrfToken },
                success: data => {
                try {
                    const wrap = document.querySelector('.designation_div');
                    if (!wrap) return;
                    wrap.innerHTML = `
                    <select class="form-control designation_id" name="designation_id" id="choices-designation">
                        <option value="">{{ __("Select any Designation") }}</option>
                    </select>`;
                    for (const [k, v] of Object.entries(data || {})) {
                    document.getElementById('choices-designation')
                            .insertAdjacentHTML('beforeend',
                                `<option value="${k}"${k==='{{ $employee->designation_id }}'?' selected':''}>${v}</option>`);
                    }
                    new Choices('#choices-designation', { removeItemButton: true });
                } catch {
                    queuedError = getMsg('designation_fetch_failed', document.getElementById('department_id'));
                }
                },
                error: () => {
                queuedError = getMsg('designation_fetch_failed', document.getElementById('department_id'));
                }
            });
            } catch {
            queuedError = getMsg('designation_fetch_failed', document.getElementById('department_id'));
            }
        };
        
        document.addEventListener('DOMContentLoaded', () => {
            const dep = document.getElementById('department_id');
            if (dep) getDesignation(dep.value);
        });
        
        document.addEventListener('change', e => {
            if (e.target && e.target.matches('select[name=department_id]')) {
            getDesignation(e.target.value);
            }
        });
        })();
    </script>
@endpush
