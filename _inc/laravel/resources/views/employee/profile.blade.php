@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Employee Profile')}}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="row d-flex justify-content-end">
        <div class="col-xl-3 col-lg-3 col-md-4">
            {{ Collective\Html\FormFacade::open(array('route' => array('employee.profile'),'method'=>'get','id'=>'employee_profile_filter')) }}
            <div class="all-select-box">
                <div class="btn-box">
                    {{ Collective\Html\FormFacade::label('branch', __('Branch'),['class'=>'text-type']) }}
                    {{ Collective\Html\FormFacade::select('branch',$brances,isset($_GET['branch'])?$_GET['branch']:'', array('class' => 'select-box select2')) }}
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4">
            <div class="all-select-box">
                <div class="btn-box">
                    {{ Collective\Html\FormFacade::label('department', __('Department'),['class'=>'text-type']) }}
                    {{ Collective\Html\FormFacade::select('department',$departments,isset($_GET['department'])?$_GET['department']:'', array('class' => 'select-box select2')) }}
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4">
            <div class="all-select-box">
                <div class="btn-box">
                    {{ Collective\Html\FormFacade::label('designation', __('Designation'),['class'=>'text-type']) }}
                    <select class="select2 select-box select2-multiple" id="designation_id" name="designation" data-placeholder="{{ __('Select Designation ...') }}">
                        <option value="">{{__('Designation')}}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-auto text-end my-auto">
            <a href="#" class="apply-btn" onclick="document.getElementById('employee_profile_filter').submit(); return false;" data-toggle="tooltip" data-title="{{__('Apply')}}">
                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
            </a>
            <a href="{{route('employee.profile')}}" class="reset-btn" data-toggle="tooltip" data-title="{{__('Reset')}}">
                <span class="btn-inner--icon"><i class="ti ti-trash-restore-alt"></i></span>
            </a>
            {{ Collective\Html\FormFacade::close() }}
        </div>
    </div>

@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        @forelse($employees as $employee)
            <div class="col-lg-3 col-sm-6 col-md-6">
                <div class="card profile-card">
                    <div class="avatar-parent-child">
                        <img src="{{!empty($employee->user->avatar) ? asset(Storage::url('uploads/avatar')).'/'.$employee->user->avatar : asset(Storage::url('uploads/avatar')).'/avatar.png'}}" class="avatar rounded-circle avatar-xl">
                    </div>
                    <h4 class="h4 mb-0 mt-2">{{ $employee->name }}</h4>
                    <div class="sal-right-card">
                        <span class="badge badge-pill badge-blue">{{ !empty($employee->designation)?$employee->designation->name:'' }}</span>
                        <div class="Id">
                            @can('Show Employee Profile')
                                <a href="{{route('show.employee.profile',\Illuminate\Support\Facades\Crypt::encrypt($employee->id))}}">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                            @else
                                <a href="#">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center">
                    <h6>{{__('there is no employee')}}</h6>
                </div>
            </div>
        @endforelse
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar:    { employee_fetch_unavailable: 'فشل جلب المسميات الوظيفية.' },
        da:    { employee_fetch_unavailable: 'Kunne ikke hente betegnelse.' },
        de:    { employee_fetch_unavailable: 'Abrufen der Bezeichnungen fehlgeschlagen.' },
        en:    { employee_fetch_unavailable: 'Failed to fetch designations.' },
        es:    { employee_fetch_unavailable: 'Error al obtener designaciones.' },
        fr:    { employee_fetch_unavailable: 'Échec de la récupération des intitulés.' },
        it:    { employee_fetch_unavailable: 'Impossibile recuperare le mansioni.' },
        ja:    { employee_fetch_unavailable: '役職を取得できませんでした。' },
        nl:    { employee_fetch_unavailable: 'Ophalen van functies mislukt.' },
        pl:    { employee_fetch_unavailable: 'Nie udało się pobrać stanowisk.' },
        pt:    { employee_fetch_unavailable: 'Falha ao obter designações.' },
        'pt-br':{ employee_fetch_unavailable: 'Falha ao buscar cargos.' },
        ru:    { employee_fetch_unavailable: 'Не удалось получить должности.' },
        tr:    { employee_fetch_unavailable: 'Unvanlar alınamadı.' },
        zh:    { employee_fetch_unavailable: '获取职位失败。' }
        };
    </script>
    <script defer>
        (() => {
        const errKey      = 'employee_fetch_unavailable';
        const toastBoxId  = 'toast-box';
        const csrfToken   = '{{ csrf_token() }}';
        const routeUrl    = '{{ route("employee.json") }}';
        
        const lang = (() => {
            const l = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
            .toLowerCase()
            .replace(/_/g, '-');
            return l === 'pt-br' ? l : l.slice(0, 2);
        })();
        
        const tr = key =>
            window.translations?.[lang]?.[key] ||
            window.translations.en?.[key] ||
            '# ERROR';
        
        const showToast = msg => {
            const hasBootstrap = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
            if (hasBootstrap) {
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
            bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
            alert(msg);
            }
        };
        
        let pendingError = '';
        const flushError = () => {
            if (pendingError) {
            showToast(pendingError);
            pendingError = '';
            }
        };
        document.addEventListener('pointerup', flushError);
        new MutationObserver((records, obs) => {
            for (const rec of records) {
            for (const node of rec.removedNodes) {
                if (node === document.documentElement) {
                document.removeEventListener('pointerup', flushError);
                obs.disconnect();
                }
            }
            }
        }).observe(document.body, { childList: true, subtree: true });
        
        const getDesignation = deptId => {
            try {
            if (!routeUrl || routeUrl === '#') {
                pendingError = tr(errKey);
                return;
            }
            $.ajax({
                url: routeUrl,
                type: 'POST',
                data: { department_id: deptId ?? '', _token: csrfToken },
            })
                .done(data => {
                const sel = document.getElementById('designation_id');
                if (!sel) return;
                sel.innerHTML = '<option value="">{{ __("Select Designation") }}</option>';
                for (const [k, v] of Object.entries(data || {})) {
                    sel.insertAdjacentHTML('beforeend',
                    `<option value="${k}">${v}</option>`);
                }
                })
                .fail(() => {
                pendingError = tr(errKey);
                });
            } catch {
            pendingError = tr(errKey);
            }
        };
        
        document.addEventListener('DOMContentLoaded', () => {
            const dep = document.getElementById('department');
            if (dep) getDesignation(dep.value);
        });
        
        document.addEventListener('change', e => {
            if (e.target.matches('select[name="department"]')) {
            getDesignation(e.target.value);
            }
        });
        })();
    </script>
@endpush

