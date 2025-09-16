@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
        ViewsConstants as VW
    };
    use App\Models\{Branch, Designation, Department, Employee, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Create Employee') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    @php
        $homeBase       = 'home';
        $homeKebab      = Str::kebab($homeBase);
        $homeResolved   = Route::has($homeBase) ? $homeBase : (Route::has($homeKebab) ? $homeKebab : null);
        $homeUrl        = $homeResolved ? route($homeResolved) : '#';
        $homeGuardMsg   = Utility::fetchLinkMessage($lang, 'generics', 'home_route_unavailable') ?? 'Home route is unavailable. Please contact technical support or your domain administrator.';
        $empIndexBase   = VW::EMP.'.index';
        $empIndexKebab  = Str::kebab($empIndexBase);
        $empIndexName   = Route::has($empIndexBase) ? $empIndexBase : (Route::has($empIndexKebab) ? $empIndexKebab : null);
        $empIndexUrl    = $empIndexName ? route($empIndexName) : '#';
        $empIndexGuard  = Utility::fetchLinkMessage($lang, VW::EMP, 'index_employee_route_unavailable') ?? 'Employee index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a id="bc-home-link"
           href="{{ $homeUrl }}"
           data-url="{{ $homeUrl }}"
           data-guard-msg="{{ $homeGuardMsg }}"
           data-sv-localized="true">
            {{ __('Home') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a id="bc-employee-index-link"
           href="{{ $empIndexUrl }}"
           data-url="{{ $empIndexUrl }}"
           data-guard-msg="{{ $empIndexGuard }}"
           data-sv-localized="true">
            {{ __('Employee') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/employees/home.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/employees/createIndex.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div>
            <div>
                <div class="{{ VC::RW }}"></div>
                @php
                    $empStoreBase     = VW::EMP;
                    $empStoreKebab    = Str::kebab($empStoreBase);
                    $empStoreResolved = Route::has($empStoreBase) ? $empStoreBase : (Route::has($empStoreKebab) ? $empStoreKebab : null);
                    $empStoreUrl      = $empStoreResolved ? route($empStoreResolved) : '#';
                    $empStoreFormId   = 'employee-store-form';
                    $empStoreGuardMsg = Utility::fetchLinkMessage($lang, VW::EMP, 'store_employee_route_unavailable') ?? 'Store employee route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                {{ Form::open([
                    'url'               => $empStoreUrl,
                    'method'            => 'POST',
                    'enctype'           => 'multipart/form-data',
                    'id'                => $empStoreFormId,
                    'data-url'          => $empStoreUrl,
                    'data-guard-msg'    => $empStoreGuardMsg,
                    'data-sv-localized' => 'true',
                ]) }}
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::CD }} em-card">
                                <div class="card-header">
                                    <h5>{{ __('Personal Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('name', __('Name'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                            {!! Form::text('name', old('name'), ['class' => VC::FM_CT, 'required' => 'required', 'placeholder' => 'Enter employee name']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('phone', __('Phone'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                            {!! Form::text('phone', old('phone'), ['class' => VC::FM_CT, 'placeholder' => 'Enter employee phone']) !!}
                                        </div>

                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {!! Form::label('dob', __('Date of Birth'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                                {{ Form::date('dob', null, ['class' => VC::FM_CT, 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select Date of Birth']) }}
                                            </div>
                                        </div>

                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {!! Form::label('gender', __('Gender'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                                <div class="{{ VC::DFL }} radio-check">
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input">
                                                        <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                        <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input">
                                                        <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                        <input type="radio" id="g_nb" value="Non-Binary" name="gender" class="form-check-input">
                                                        <label class="form-check-label" for="g_nb">{{ __('Non-Binary') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('email', __('Email'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                            {!! Form::email('email', old('email'), ['class' => VC::FM_CT, 'required' => 'required', 'placeholder' => 'Enter employee email']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('password', __('Password'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                            {!! Form::password('password', ['class' => VC::FM_CT, 'required' => 'required', 'placeholder' => 'Enter employee new password']) !!}
                                        </div>
                                    </div>

                                    <div class="{{ VC::FM_G }}">
                                        {!! Form::label('address', __('Address'), ['class' => VC::FM_LB]) !!}<span class="text-danger ps-1">*</span>
                                        {!! Form::textarea('address', old('address'), ['class' => VC::FM_CT, 'rows' => 2, 'placeholder' => 'Enter employee address']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::CD }} em-card">
                                <div class="card-header">
                                    <h5>{{ __('Company Detail') }}</h5>
                                </div>
                                <div class="card-body employee-detail-create-body">
                                    <div class="{{ VC::RW }}">
                                        @csrf

                                        <div class="{{ VC::FM_G }}">
                                            {!! Form::label('employee_id', __('Employee ID'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::text('employee_id', !empty($employeesId) ? $employeesId : (!empty($employee) && isset($employee) ? $employee->id : __('Employee id failed to be written. Please contact your system administrator or technical support.')), ['class' => VC::FM_CT, 'disabled' => 'disabled']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('branch_id', __('Select Branch*'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                {{ Form::select('branch_id', Branch::pluck('name', 'id')->toArray(), null, ['class' => VC::FM_CT . ' select2', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
                                            </div>
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('department_id', __('Select Department*'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                {{ Form::select('department_id', Department::pluck('name', 'id')->toArray(), null, ['class' => VC::FM_CT . ' select2', 'id' => 'department_id', 'required' => 'required', 'placeholder' => 'Select Department']) }}
                                            </div>
                                        </div>

                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label('designation_id', __('Select Designation'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                {{ Form::select('designation_id', Designation::pluck('name', 'id')->toArray(), null, ['class' => VC::FM_CT . ' select2', 'id' => 'designation_id', 'required' => 'required', 'placeholder' => 'Select Designation']) }}
                                            </div>
                                        </div>

                                        <div class="{{ VC::FM_G }}">
                                            {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => VC::FM_LB]) !!}
                                            {{ Form::date('company_doj', null, ['class' => VC::FM_CT, 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select company date of joining']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php
                        $docsIsArray      = is_array($documents ?? null) && count($documents ?? []) > 0;
                        $docsIsCollection = ($documents ?? null) instanceof Collection && ($documents->isNotEmpty());
                        $docs             = ($docsIsArray || $docsIsCollection) ? $documents : [];
                    @endphp
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::CD }} em-card">
                                <div class="card-header">
                                    <h5>{{ __('Document') }}</h5>
                                </div>
                                <div class="card-body employee-detail-create-body">
                                    @forelse ($docs as $key => $document)
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::FM_G }} {{ VC::C12 }} {{ VC::DFL }}">
                                                <div class="{{ VC::CL4 }}">
                                                    <label class="{{ VC::FM_LB }} pt-1">
                                                        {{ $document->name ?? __('No name available for document') }}
                                                        @if (!empty($document->is_required) && (int)$document->is_required === 1)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                </div>
                                                <div class="{{ VC::CL8 }}">
                                                    <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">
                                                    <div class="choose-files">
                                                        <label for="document[{{ $document->id }}]">
                                                            <div class="{{ VC::BG_P }} document">
                                                                <i class="ti ti-upload"></i> {{ __('Choose file here') }}
                                                            </div>
                                                            <input
                                                                type="file"
                                                                class="{{ VC::FM_CT }} file d-none @error('document') is-invalid @enderror"
                                                                @if (!empty($document->is_required) && (int)$document->is_required === 1) required @endif
                                                                name="document[{{ $document->id }}]"
                                                                id="document[{{ $document->id }}]"
                                                                data-filename="{{ $document->id . '_filename' }}"
                                                                onchange="document.getElementById('{{ 'blah' . $key }}').src = window.URL.createObjectURL(this.files[0])"
                                                            >
                                                        </label>
                                                        <img id="{{ 'blah' . $key }}" src="" width="50%"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-3">{{ __('No documents found for this form.') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::CD }} em-card">
                                <div class="card-header">
                                    <h5>{{ __('Bank Account Detail') }}</h5>
                                </div>
                                <div class="card-body employee-detail-create-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::text('account_holder_name', old('account_holder_name'), ['class' => VC::FM_CT, 'placeholder' => 'Enter account holder name']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('account_number', __('Account Number'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::number('account_number', old('account_number'), ['class' => VC::FM_CT, 'placeholder' => 'Enter account number']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('bank_name', __('Bank Name'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::text('bank_name', old('bank_name'), ['class' => VC::FM_CT, 'placeholder' => 'Enter bank name']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::text('bank_identifier_code', old('bank_identifier_code'), ['class' => VC::FM_CT, 'placeholder' => 'Enter bank identifier code']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('branch_location', __('Branch Location'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::text('branch_location', old('branch_location'), ['class' => VC::FM_CT, 'placeholder' => 'Enter branch location']) !!}
                                        </div>

                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::text('tax_payer_id', old('tax_payer_id'), ['class' => VC::FM_CT, 'placeholder' => 'Enter tax payer id']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::FEND }}">
                        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
                    </div>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/employees/store.js') }}"></script>
                    @endpush
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/employees/lang/create.js') }}"></script>
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
