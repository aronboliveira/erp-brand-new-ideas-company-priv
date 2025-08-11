@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as C,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\EmployeeAttendanceController as EAC;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bulk Attendance')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar: {
            present_all_toggle_failed: 'فشل تبديل جميع خانات الاختيار.',
            present_toggle_failed: 'فشل تبديل خانة الحضور.'
        },
        da: {
            present_all_toggle_failed: 'Kunne ikke slå alle afkrydsningsfelter til/fra.',
            present_toggle_failed: 'Kunne ikke skifte tilstedeværelsesfelt.'
        },
        de: {
            present_all_toggle_failed: 'Konnte nicht alle Kontrollkästchen umschalten.',
            present_toggle_failed: 'Konnte das Anwesenheitskontrollkästchen nicht umschalten.'
        },
        en: {
            present_all_toggle_failed: 'Failed to toggle all checkboxes.',
            present_toggle_failed: 'Failed to toggle attendance checkbox.'
        },
        es: {
            present_all_toggle_failed: 'Error al alternar todas las casillas.',
            present_toggle_failed: 'Error al alternar la casilla de asistencia.'
        },
        fr: {
            present_all_toggle_failed: 'Échec du basculement de toutes les cases.',
            present_toggle_failed: 'Échec du basculement de la case de présence.'
        },
        he: {
            present_all_toggle_failed: 'לא ניתן להחליף את כל תיבות הסימון.',
            present_toggle_failed: 'לא ניתן להחליף את תיבת הסימון של נוכחות.'
        },
        it: {
            present_all_toggle_failed: 'Impossibile attivare/disattivare tutte le caselle.',
            present_toggle_failed: 'Impossibile attivare/disattivare la casella di presenza.'
        },
        ja: {
            present_all_toggle_failed: 'すべてのチェックボックスの切り替えに失敗しました。',
            present_toggle_failed: '出席チェックボックスの切り替えに失敗しました。'
        },
        nl: {
            present_all_toggle_failed: 'Kan niet alle selectievakjes wisselen.',
            present_toggle_failed: 'Kan selectievakje voor aanwezigheid niet wisselen.'
        },
        pl: {
            present_all_toggle_failed: 'Nie udało się przełączyć wszystkich pól wyboru.',
            present_toggle_failed: 'Nie udało się przełączyć pola wyboru obecności.'
        },
        pt: {
            present_all_toggle_failed: 'Falha ao alternar todas as caixas de seleção.',
            present_toggle_failed: 'Falha ao alternar a caixa de presença.'
        },
        'pt-br': {
            present_all_toggle_failed: 'Falha ao alternar todas as caixas de seleção.',
            present_toggle_failed: 'Falha ao alternar a caixa de presença.'
        },
        ru: {
            present_all_toggle_failed: 'Не удалось переключить все флажки.',
            present_toggle_failed: 'Не удалось переключить флажок присутствия.'
        },
        tr: {
            present_all_toggle_failed: 'Tüm onay kutuları değiştirilemedi.',
            present_toggle_failed: 'Yoklama onay kutusu değiştirilemedi.'
        },
        zh: {
            present_all_toggle_failed: '无法切换所有复选框。',
            present_toggle_failed: '无法切换出席复选框。'
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
                if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
                msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                let lang = (window.sessionStorage.getItem(langSessionKey) ?? document.documentElement.lang ?? 'en')
                    .toLowerCase().replace(/_/g, '-');
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
                let container = document.querySelector('#bootstrap-toast-container');
                if (!container) {
                    const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                    .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
                    if (hasBs) {
                    container = document.createElement('div');
                    container.id = 'bootstrap-toast-container';
                    container.setAttribute('aria-live', 'polite');
                    container.setAttribute('aria-atomic', 'true');
                    document.body.appendChild(container);
                    }
                }
                if (container && window.bootstrap.Toast) {
                    let toast = container.querySelector('.toast');
                    if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    toast.appendChild(body);
                    container.appendChild(toast);
                    if (toast.getAttribute('data-click-listener') !== 'true') {
                        toast.addEventListener('click', () => body.textContent = message);
                        toast.setAttribute('data-click-listener', 'true');
                    }
                    }
                    toast.querySelector('.toast-body').textContent = message;
                    new bootstrap.Toast(toast).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            };
            const presentAllEl = document.getElementById('present_all');
            if (presentAllEl && presentAllEl.dataset.listenerAttached !== 'true') {
                presentAllEl.dataset.listenerAttached = 'true';
                const obsAll = new MutationObserver((ms, obs) => {
                ms.forEach(m => [...m.removedNodes].forEach(n => {
                    if (n === presentAllEl) {
                    presentAllEl.removeEventListener('click', onPresentAllClick);
                    obs.disconnect();
                    }
                }));
                });
                obsAll.observe(document.body, { childList: true, subtree: true });
                presentAllEl.addEventListener('click', onPresentAllClick);
            }
            function onPresentAllClick() {
                try {
                const checked = presentAllEl.checked ?? false;
                document.querySelectorAll('.present').forEach(el => {
                    if (el instanceof HTMLInputElement) el.checked = checked;
                });
                document.querySelectorAll('.present_check_in').forEach(el => {
                    el.classList.toggle('d-none', !checked);
                    el.classList.toggle('d-block', checked);
                });
                } catch {
                showError(getLocalizedMessage('present_all_toggle_failed', presentAllEl));
                }
            }
            document.querySelectorAll('.present').forEach(el => {
                if (el.dataset.listenerAttached === 'true') return;
                el.dataset.listenerAttached = 'true';
                const obsPres = new MutationObserver((ms, obs) => {
                ms.forEach(m => [...m.removedNodes].forEach(n => {
                    if (n === el) {
                    el.removeEventListener('click', onPresentClick);
                    obs.disconnect();
                    }
                }));
                });
                obsPres.observe(document.body, { childList: true, subtree: true });
                el.addEventListener('click', onPresentClick);
            });
            function onPresentClick(event) {
                try {
                const el = event.currentTarget;
                const container = el.parentElement?.parentElement?.parentElement?.parentElement;
                const checkInEl = container?.querySelector('.present_check_in');
                if (!checkInEl) return;
                if (el.checked) {
                    checkInEl.classList.remove('d-none');
                    checkInEl.classList.add('d-block');
                } else {
                    checkInEl.classList.remove('d-block');
                    checkInEl.classList.add('d-none');
                }
                } catch {
                showError(getLocalizedMessage('present_toggle_failed', event.currentTarget));
                }
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Attendance')}}</li>
@endsection
{{--@section('action-btn')--}}
{{--    <div class="float-end">--}}
{{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
{{--    </div>--}}
{{--@endsection--}}
@section(YieldingConstants::ADM_CTT)
    <div class="{{ C::RW }}">
        <div class="col-sm-12">
            <div id="multiCollapseExample1">
                <div class="{{ C::CD }}">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open([
                            'route' => [ViewsConstants::EMP_ATD.'.'.EAC::BK_ATD],
                            'method'=> 'get',
                            'id'    => 'bulkattendance_filter'
                        ]) }}
                        <div class="{{ C::DFL }} {{ C::ALC }} {{ C::JCE }}">
                            <div class="col-xl-10">
                                <div class="{{ C::RW }}">
                                    <div class="{{ C::CLMS3 }}">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="{{ C::CLMS3 }}">
                                        {{ Collective\Html\FormFacade::label('date', __('Date'), ['class'=>C::FM_LB]) }}
                                        {{ Collective\Html\FormFacade::date('date', request('date',''), ['class'=>C::FM_CT]) }}
                                    </div>
                                    <div class="{{ C::CLMS3 }}">
                                        {{ Collective\Html\FormFacade::label('branch', __('Branch'), ['class'=>C::FM_LB]) }}
                                        {{ Collective\Html\FormFacade::select('branch', $branch, request('branch',''), ['class'=>C::FM_CT.' select','required']) }}
                                    </div>
                                    <div class="{{ C::CLMS3 }}">
                                        {{ Collective\Html\FormFacade::label('department', __('Department'), ['class'=>C::FM_LB]) }}
                                        {{ Collective\Html\FormFacade::select('department', $department, request('department',''), ['class'=>C::FM_CT.' select','required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ C::C_AT }} {{ C::FEND }} {{ C::MS2 }} {{ C::MT4 }}">
                                <a href="#" class="{{ C::BT_SM_PM }}" onclick="document.getElementById('bulkattendance_filter').submit();return false;" title="{{__('Apply')}}">
                                    <i class="{{ C::TI_SRC }}"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{ Collective\Html\FormFacade::close() }}
                </div>
            </div>
        </div>
    </div>
   <div class="{{ C::RW }}">
        <div class="col-xl-12">
            <div class="{{ C::CD }}">
                <div class="card-header {{ C::CD_MT }}">
                    {{ Collective\Html\FormFacade::open([
                        'route'  => [ViewsConstants::EMP_ATD.'.'.EAC::BK_ATD],
                        'method' => 'post'
                    ]) }}
                    <div class="table-responsive">
                        <table class="{{ C::TB_AL }}" id="pc-dt-simple">
                            <thead>
                            <tr>
                                <th width="10%">{{ __('Employee Id') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>
                                    <div class="form-group my-auto">
                                        <div class="custom-control ">
                                            <input class="form-check-input" type="checkbox" name="present_all"
                                                   id="present_all" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="present_all">
                                                {{ __('Attendance') }}</label>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    @php
                                        $attendance = $employee->presentStatus($employee->id, request('date', date('Y-m-d')));
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="hidden" name="employee_id[]" value="{{ $employee->id }}">
                                            <a href="{{ route('employees.show', encrypt($employee->id)) }}"
                                               class="btn btn-outline-primary">
                                               {{ Auth::user()->employeeIdFormat($employee->employee_id) }}
                                            </a>
                                        </td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->branch->name ?? '' }}</td>
                                        <td>{{ $employee->department->name ?? '' }}</td>
                                        <td>
                                            <div class="{{ C::RW }}">
                                                <div class="{{ C::CM3 }}">
                                                    <div class="{{ C::CST_CTL }} {{ C::CST_CB }}">
                                                        <input type="checkbox"
                                                               class="form-check-input present"
                                                               name="present-{{ $employee->id }}"
                                                               id="present{{ $employee->id }}"
                                                               {{ optional($attendance)->status=='Present'?'checked':'' }}>
                                                        <label class="{{ C::CST_LB }}" for="present{{ $employee->id }}"></label>
                                                    </div>
                                                </div>
                                                <div class="col-md-8 {{ $attendance?'':'d-none' }}">
                                                    <div class="{{ C::RW }}">
                                                        <label class="{{ C::CM3 }} {{ C::FM_LB }}">{{ __('In') }}</label>
                                                        <div class="{{ C::CM4 }}">
                                                            <input type="time"
                                                                   class="{{ C::FM_CT }}"
                                                                   name="in-{{ $employee->id }}"
                                                                   value="{{ $attendance->clock_in!='00:00:00'?$attendance->clock_in:Utility::getValByName('company_start_time') }}">
                                                        </div>
                                                        <label class="{{ C::CM2 }} {{ C::FM_LB }}">{{ __('Out') }}</label>
                                                        <div class="{{ C::CM4 }}">
                                                            <input type="time"
                                                                   class="{{ C::FM_CT }}"
                                                                   name="out-{{ $employee->id }}"
                                                                   value="{{ $attendance->clock_out!='00:00:00'?$attendance->clock_out:Utility::getValByName('company_end_time') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="{{ C::FEND }} pt-4">
                        <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">
                        <input type="hidden" name="branch" value="{{ request('branch','') }}">
                        <input type="hidden" name="department" value="{{ request('department','') }}">
                        {{ Collective\Html\FormFacade::submit(__('Update'), ['class'=>C::BT_SM_PM]) }}
                    </div>
                    {{ Collective\Html\FormFacade::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    {{--    <script>--}}
    {{--        $(document).ready(function () {--}}
    {{--            $('.daterangepicker').daterangepicker({--}}
    {{--                format: 'yyyy-mm-dd',--}}
    {{--                locale: {format: 'YYYY-MM-DD'},--}}
    {{--            });--}}
    {{--        });--}}
    {{--    </script>--}}
@endpush
