@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Create Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection


@section('content')
<div class="row">
    <div class="">
        <div class="">
            <div class="row">
            </div>
            {{ Collective\Html\FormFacade::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
            <div class="row">
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('name', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Collective\Html\FormFacade::text('name', old('name'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee name']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('phone', __('Phone'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Collective\Html\FormFacade::text('phone', old('phone'), ['class' => 'form-control' ,'placeholder'=>'Enter employee phone']) !!}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {{ Collective\Html\FormFacade::date('dob', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off','placeholder'=>'Select Date of Birth']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Collective\Html\FormFacade::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input">
                                                <label class="form-check-label " for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input">
                                                <label class="form-check-label "
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Collective\Html\FormFacade::email('email', old('email'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee email']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('password', __('Password'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Collective\Html\FormFacade::password('password', ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee new password']) !!}
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Collective\Html\FormFacade::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Collective\Html\FormFacade::textarea('address', old('address'), ['class' => 'form-control', 'rows' => 2 ,'placeholder'=>'Enter employee address']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Company Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                @csrf
                                <div class="form-group ">
                                    {!! Collective\Html\FormFacade::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Collective\Html\FormFacade::label('branch_id', __('Select Branch*'), ['class' => 'form-label']) }}
                                    <div class="form-icon-user">
                                        {{ Collective\Html\FormFacade::select('branch_id', $branches, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Collective\Html\FormFacade::label('department_id', __('Select Department*'), ['class' => 'form-label']) }}
                                    <div class="form-icon-user">
                                        {{ Collective\Html\FormFacade::select('department_id', $departments, null, ['class' => 'form-control select2', 'id' => 'department_id', 'required' => 'required' , 'placeholder' => 'Select Department']) }}
                                    </div>
                                </div>

                                <div class="form-group ">
                                    {{ Collective\Html\FormFacade::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}

                                    <div class="form-icon-user">
                                        {{--  <div class="designation_div">
                                            <select class="form-control  designation_id" name="designation_id"
                                                id="choices-multiple" placeholder="Select Designation">
                                            </select>
                                        </div>  --}}
                                        {{ Collective\Html\FormFacade::select('designation_id', $designations, null, ['class' => 'form-control select2', 'id' => 'designation_id', 'required' => 'required' , 'placeholder' => 'Select Designation']) }}

                                    </div>
                                </div>
                                <div class="form-group  ">
                                    {!! Collective\Html\FormFacade::label('company_doj', __('Company Date Of Joining'), ['class' => '  form-label']) !!}
                                    {{ Collective\Html\FormFacade::date('company_doj', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off' ,'placeholder'=>'Select company date of joining']) }}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h6>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            @foreach ($documents as $key => $document)
                                <div class="row">
                                    <div class="form-group col-12 d-flex">
                                        <div class="float-left col-4">
                                            <label for="document"
                                                class="float-left pt-1 form-label">{{ $document->name }} @if ($document->is_required == 1)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="float-right col-8">
                                            <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                                value="{{ $document->id }}">
                                            <div class="choose-files">
                                                <label for="document[{{ $document->id }}]">
                                                    <div class=" bg-primary document "> <i
                                                            class="ti ti-upload "></i>{{ __('Choose file here') }}
                                                    </div>
                                                    <input type="file"
                                                        class="form-control file  d-none @error('document') is-invalid @enderror"
                                                        @if ($document->is_required == 1) required @endif
                                                        name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                                        data-filename="{{ $document->id . '_filename' }}" onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])">
                                                </label>
                                                <img id="{{'blah'.$key}}" src=""  width="50%" />

                                            </div>

                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::text('account_holder_name', old('account_holder_name'), ['class' => 'form-control' ,'placeholder'=>'Enter account holder name']) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::number('account_number', old('account_number'), ['class' => 'form-control' ,'placeholder'=>'Enter account number']) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::text('bank_name', old('bank_name'), ['class' => 'form-control' ,'placeholder'=>'Enter bank name']) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::text('bank_identifier_code', old('bank_identifier_code'), ['class' => 'form-control' ,'placeholder'=>'Enter bank identifier code']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::text('branch_location', old('branch_location'), ['class' => 'form-control' ,'placeholder'=>'Enter branch location']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Collective\Html\FormFacade::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                    {!! Collective\Html\FormFacade::text('tax_payer_id', old('tax_payer_id'), ['class' => 'form-control' ,'placeholder'=>'Enter tax payer id']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="float-end">
            <button type="submit" class="btn btn-primary">{{ 'Create' }}</button>
        </div>
        </form>
    </div>
</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar:    { file_name_append_failed: 'تعذر عرض اسم الملف.',        designation_fetch_failed: 'فشل جلب المناصب الوظيفية.' },
        da:    { file_name_append_failed: 'Kunne ikke vise filnavnet.',  designation_fetch_failed: 'Kunne ikke hente betegnelse.' },
        de:    { file_name_append_failed: 'Dateiname konnte nicht angezeigt werden.', designation_fetch_failed: 'Abrufen der Bezeichnungen fehlgeschlagen.' },
        en:    { file_name_append_failed: 'Unable to display file name.',            designation_fetch_failed: 'Failed to fetch designations.' },
        es:    { file_name_append_failed: 'No se pudo mostrar el nombre del archivo.', designation_fetch_failed: 'Error al obtener designaciones.' },
        fr:    { file_name_append_failed: 'Impossible d’afficher le nom du fichier.',  designation_fetch_failed: 'Échec de la récupération des intitulés.' },
        it:    { file_name_append_failed: 'Impossibile mostrare il nome del file.',    designation_fetch_failed: 'Impossibile recuperare le mansioni.' },
        ja:    { file_name_append_failed: 'ファイル名を表示できませんでした。',            designation_fetch_failed: '役職を取得できませんでした。' },
        nl:    { file_name_append_failed: 'Bestandsnaam kon niet worden weergegeven.', designation_fetch_failed: 'Ophalen van functies mislukt.' },
        pl:    { file_name_append_failed: 'Nie można wyświetlić nazwy pliku.',         designation_fetch_failed: 'Nie udało się pobrać stanowisk.' },
        pt:    { file_name_append_failed: 'Não foi possível exibir o nome do ficheiro.', designation_fetch_failed: 'Falha ao obter designações.' },
        'pt-br':{ file_name_append_failed: 'Não foi possível exibir o nome do arquivo.', designation_fetch_failed: 'Falha ao buscar cargos.' },
        ru:    { file_name_append_failed: 'Не удалось отобразить имя файла.',          designation_fetch_failed: 'Не удалось получить должности.' },
        tr:    { file_name_append_failed: 'Dosya adı gösterilemedi.',                  designation_fetch_failed: 'Unvanlar alınamadı.' },
        zh:    { file_name_append_failed: '无法显示文件名。',                             designation_fetch_failed: '获取职位失败。' }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const toastBoxId = 'toast-box';
        const csrf = '{{ csrf_token() }}';
        const lang = (() => {
            const l = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
            .toLowerCase()
            .replace(/_/g, '-');
            return l === 'pt-br' ? l : l.slice(0, 2);
        })();
        const tr = k =>
            window.translations?.[lang]?.[k] ||
            window.translations.en[k] ||
            errFb;
        
        const toast = msg => {
            const hasBs = [...document.querySelectorAll('link[rel="stylesheet"]')].some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
            if (hasBs) {
            let box = document.getElementById(toastBoxId);
            if (!box) {
                box = document.createElement('div');
                box.id = toastBoxId;
                box.setAttribute('aria-live', 'polite');
                box.setAttribute('aria-atomic', 'true');
                document.body.appendChild(box);
            }
            const t = document.createElement('div');
            t.className = 'toast';
            t.innerHTML = `<div class="toast-body">${msg}</div>`;
            box.appendChild(t);
            window.bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
            alert(msg);
            }
        };
        
        let queuedErr = '';
        const flushErr = () => {
            if (queuedErr) {
            toast(queuedErr);
            queuedErr = '';
            }
        };
        document.addEventListener('click', flushErr);
        
        document
            .querySelectorAll('input[type="file"][data-filename]')
            .forEach(input => {
            if (input.dataset.bound === '1') return;
            input.dataset.bound = '1';
        
            const changeHandler = e => {
                try {
                const name = e.target.files?.[0]?.name;
                if (!name) return;
                const target = document.querySelector('.' + input.dataset.filename);
                if (target && !target.textContent.includes(name)) target.textContent += name;
                } catch {
                queuedErr = tr('file_name_append_failed');
                }
            };
        
            input.addEventListener('change', changeHandler);
        
            new MutationObserver((m, o) => {
                m.forEach(rec =>
                rec.removedNodes.forEach(n => {
                    if (n === input) {
                    input.removeEventListener('change', changeHandler);
                    o.disconnect();
                    }
                })
                );
            }).observe(document.body, { childList: true, subtree: true });
            });
        
        const loadDesig = id => {
            try {
            $.ajax({
                url: '{{ route("employees.json") }}',
                type: 'POST',
                data: { department_id: id ?? '', _token: csrf },
                success: data => {
                const wrap = document.querySelector('.designation_div');
                if (!wrap) return;
                wrap.innerHTML =
                    `<select class="form-control designation_id" name="designation_id" id="choices-designation">
                    <option value="0">{{ __('All') }}</option>
                    </select>`;
                Object.entries(data || {}).forEach(([k, v]) =>
                    $('#choices-designation').append(`<option value="${k}">${v}</option>`)
                );
                new Choices('#choices-designation', { removeItemButton: true });
                },
                error: () => {
                queuedErr = tr('designation_fetch_failed');
                }
            });
            } catch {
            queuedErr = tr('designation_fetch_failed');
            }
        };
        
        $(document).ready(() => loadDesig($('.department_id').val()));
        $(document).on('change', 'select[name=department_id]', function () {
            loadDesig(this.value);
        });
        })();
    </script>
@endpush
