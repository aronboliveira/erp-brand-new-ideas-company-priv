@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Attendance List')}}
@endsection
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
    <div class="row">
        <div class="col-sm-12">
            @if (session('status'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session('status') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    @php
                        $indexRoute    = Route::has(ViewsConstants::EMP_ATD.'.index') ? [ViewsConstants::EMP_ATD.'.index'] : ['#'];
                        $indexUrl      = Route::has(ViewsConstants::EMP_ATD.'.index') ? route(ViewsConstants::EMP_ATD.'.index') : '#';
                        $formId        = 'employeeAttendance_filter';
                        $resetClass    = 'reset-employee-attendance-link';
                        $importRoute   = Route::has(ViewsConstants::ATD.'.file.import') ? route(ViewsConstants::ATD.'.file.import') : '#';
                        $importClass   = 'import-attendance-link';
                    @endphp
                    <div class="card-body">
                        {!! Collective\Html\FormFacade::open([
                            'route'          => $indexRoute,
                            'method'         => 'get',
                            'id'             => $formId,
                            'data-url' => $indexUrl,
                            'data-sv-localized' => 'true',
                            'data-guard-msg' => Utility::fetchLinkMessage($lang,ViewsConstants::EMP_ATD,'index_attendance_unavailable') ?? 'Employee attendance index route is unavailable. Please contact technical support or your domain administrator.'
                        ]) !!}
                            <div class="{{ ViewClassNamesConstants::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label">{{ __('Type') }}</label><br>
                                            <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                                <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{ isset($_GET['type']) && $_GET['type']=='monthly' ? 'checked' : 'checked' }}>
                                                <label class="form-check-label" for="monthly">{{ __('Monthly') }}</label>
                                            </div>
                                            <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                                <input type="radio" id="daily" value="daily" name="type" class="form-check-input" {{ isset($_GET['type']) && $_GET['type']=='daily' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="daily">{{ __('Daily') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                            <div class="btn-box">
                                                {{ Collective\Html\FormFacade::label('month', __('Month'), ['class'=>'form-label']) }}
                                                {{ Collective\Html\FormFacade::month('month', isset($_GET['month'])?$_GET['month']:date('Y-m'), ['class'=>'month-btn form-control month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                            <div class="btn-box">
                                                {{ Collective\Html\FormFacade::label('date', __('Date'), ['class'=>'form-label']) }}
                                                {{ Collective\Html\FormFacade::date('date', isset($_GET['date'])?$_GET['date']:'', ['class'=>'form-control month-btn']) }}
                                            </div>
                                        </div>
                                        @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                            <div class="{{ ViewClassNamesConstants::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Collective\Html\FormFacade::label('branch', __('Branch'), ['class'=>'form-label']) }}
                                                    {{ Collective\Html\FormFacade::select('branch', $branch, isset($_GET['branch'])?$_GET['branch']:'', ['class'=>'form-control select']) }}
                                                </div>
                                            </div>
                                            <div class="{{ ViewClassNamesConstants::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Collective\Html\FormFacade::label('department', __('Department'), ['class'=>'form-label']) }}
                                                    {{ Collective\Html\FormFacade::select('department', $department, isset($_GET['department'])?$_GET['department']:'', ['class'=>'form-control select']) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto mt-4">
                                    <div class="row">
                                        <div class="col-auto">
                                            <a href="#" class="{{ ViewClassNamesConstants::BT_SM_PM }}" onclick="document.getElementById('{{ $formId }}').submit();return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $indexUrl }}" id="{{ $resetClass }}" class="{{ ViewClassNamesConstants::BT_SM_DG }} {{ $resetClass }}" data-url="{{ $indexUrl }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::EMP_ATD,'index_attendance_unavailable') ?? 'Employee attendance index route is unavailable. Please contact technical support or your domain administrator.' }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_TRS_OFF }}"></i></span>
                                            </a>
                                            <a href="#" id="{{ $importClass }}" class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $importClass }}" data-url="{{ $importRoute }}" 
                                            data-sv-localized="true"
                                            data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::ATD,'csv_attendance_unavailable') ?? 'Import employee CSV file route is unavailable. Please contact technical support or your domain administrator.' }}" data-size="md" data-ajax-popup="true" data-title="{{ __('Import employee CSV file') }}" data-url="{{ $importRoute }}" data-bs-toggle="tooltip" title="{{ __('Import') }}" data-original-title="{{ __('Import') }}">
                                                <i class="{{ ViewClassNamesConstants::TI_IMP }}"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {!! Collective\Html\FormFacade::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Date')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Clock In')}}</th>
                                <th>{{__('Clock Out')}}</th>
                                <th>{{__('Late')}}</th>
                                <th>{{__('Early Leaving')}}</th>
                                <th>{{__('Overtime')}}</th>
                                @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($EmployeeAttendance as $attendance)
                                <tr>
                                    <td>{{!empty($attendance->employee)?$attendance->employee->name:'' }}</td>
                                    <td>{{ $user?->dateFormat($attendance->date) }}</td>
                                    <td>{{ $attendance->status }}</td>
                                    <td>{{ ($attendance->clock_in !='00:00:00') ?$user?->timeFormat( $attendance->clock_in):'00:00' }} </td>
                                    <td>{{ ($attendance->clock_out !='00:00:00') ?$user?->timeFormat( $attendance->clock_out):'00:00' }}</td>
                                    <td>{{ $attendance->late }}</td>
                                    <td>{{ $attendance->early_leaving }}</td>
                                    <td>{{ $attendance->overtime }}</td>
                                    @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                        <td class="">
                                            @can('edit attendance')
                                                @php
                                                    $editRouteName = ViewsConstants::EMP_ATD . '.edit';
                                                    $editUrl = Route::has($editRouteName) ? route($editRouteName, $attendance->id) : '#';
                                                    $editClass = 'edit-attendance-link-' . $attendance->id;
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="{{ $editUrl }}"
                                                        id="{{ $editClass }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT }} {{ $editClass }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ Utility::fetchLinkMessage($lang, ViewsConstants::EMP_ATD, 'edit_attendance_unavailable') ?? 'Edit attendance route is unavailable. Please contact technical support or your domain administrator.' }}"
                                                        data-ajax-popup="true"
                                                        data-size="lg"
                                                        data-title="{{ __('Edit Attendance') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-original-title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete attendance')
                                                @php
                                                    $deleteRouteName = ViewsConstants::EMP_ATD . '.destroy';
                                                    $deleteRoute = Route::has($deleteRouteName) ? [$deleteRouteName, $attendance->id] : ['#'];
                                                    $deleteUrl = Route::has($deleteRouteName) ? route($deleteRouteName, $attendance->id) : '#';
                                                    $deleteClass = 'delete-attendance-link-' . $attendance->id;
                                                    $formId = 'delete-form-' . $attendance->id;
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open(['method'=>'DELETE','route'=>$deleteRoute,'id'=>$formId]) !!}
                                                        <a
                                                            href="{{ $deleteUrl }}"
                                                            id="{{ $deleteClass }}"
                                                            class="{{ ViewClassNamesConstants::TRS_PARA }} {{ $deleteClass }}"
                                                            data-url="{{ $deleteUrl }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ Utility::fetchLinkMessage($lang, ViewsConstants::EMP_ATD, 'delete_attendance_unavailable') ?? 'Delete attendance route is unavailable. Please contact technical support or your domain administrator.' }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>         
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
            ar: {
                date_picker_unavailable: "منتقي التاريخ غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال."
            },
            da: {
                date_picker_unavailable: "Datovælger er ikke tilgængelig. Kontakt venligst teknisk support eller din domæneadministrator."
            },
            de: {
                date_picker_unavailable: "Datumswähler ist nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domänenadministrator."
            },
            en: {
                date_picker_unavailable: "Date picker is unavailable. Please contact technical support or your domain administrator."
            },
            es: {
                date_picker_unavailable: "Selector de fecha no está disponible. Por favor, póngase en contacto con el soporte técnico o con el administrador de dominio."
            },
            fr: {
                date_picker_unavailable: "Le sélecteur de date n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine."
            },
            he: {
                date_picker_unavailable: "בוחר התאריכים אינו זמין. אנא פנה לתמיכה הטכנית או למנהל הדומיין שלך."
            },
            it: {
                date_picker_unavailable: "Il selettore di date non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore di dominio."
            },
            ja: {
                date_picker_unavailable: "日付ピッカーは利用できません。技術サポートまたはドメイン管理者にお問い合わせください。"
            },
            nl: {
                date_picker_unavailable: "Datumkiezer is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder."
            },
            pl: {
                date_picker_unavailable: "Wybór daty jest niedostępny. Skontaktuj się z pomocą techniczną lub administratorem domeny."
            },
            pt: {
                date_picker_unavailable: "Seletor de data não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio."
            },
            "pt-br": {
                date_picker_unavailable: "Seletor de data não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio."
            },
            ru: {
                date_picker_unavailable: "Выбор даты недоступен. Пожалуйста, свяжитесь с технической поддержкой или администратором домена."
            },
            tr: {
                date_picker_unavailable: "Tarih seçici kullanılamıyor. Lütfen teknik destek veya alan yöneticisi ile iletişime geçin."
            },
            zh: {
                date_picker_unavailable: "日期选择器不可用。请联系技术支持或您的域管理员。"
            }
        };
    </script>
    <script defer src="{{ asset('assets/js/routes/attendances/page.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/employeeAttendance/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/attendance/import.js') }}"></script>
    @can('edit attendance')
        <script defer src="{{ asset('assets/js/routes/attendances/edit.js') }}"></script>
    @endcan
    @can('delete attendance')
        <script defer src="{{ asset('assets/js/routes/attendances/delete.js') }}"></script>
    @endcan
    <script defer>
        (() => {
            const BS_LINK = 'link[href*="bootstrap"]';
            const toastContainer = (() => {
                const c = document.createElement('div');
                c.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.append(c);
                return c;
            })();

            const showError = key => {
                const errFb = '# ERROR';
                let lang = (window.sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                .toLowerCase()
                .replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                const msg = window.translations?.[lang]?.[key]
                || window.translations?.['en']?.[key]
                || errFb;

                const existing = toastContainer.querySelector(`.toast[data-error-key="${key}"]`);
                if (existing) return;

                if (document.querySelector(BS_LINK) && window.bootstrap?.Toast) {
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-bg-danger border-0';
                toast.dataset.errorKey = key;
                toast.setAttribute('role','alert');
                toast.setAttribute('aria-live','assertive');
                toast.setAttribute('aria-atomic','true');
                toast.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                toastContainer.append(toast);
                new window.bootstrap.Toast(toast).show();
                } else {
                alert(msg);
                }
            };

            try {
                const pickers = document.querySelectorAll('.daterangepicker');
                pickers.forEach(el => {
                if (el.dataset.dpListener) return;
                el.dataset.dpListener = 'true';
                el.addEventListener('click', () => {
                    try {
                    if (typeof $ !== 'function') {
                        console.error('jQuery not loaded');
                        showError('date_picker_unavailable');
                        return;
                    }
                    if (typeof $.fn.daterangepicker !== 'function') {
                        console.error('daterangepicker plugin unavailable');
                        showError('date_picker_unavailable');
                        return;
                    }
                    $(el).daterangepicker({
                        format: 'yyyy-mm-dd',
                        locale: { format: 'YYYY-MM-DD' }
                    });
                    } catch (err) {
                    console.error('Error initializing date picker on click:', err);
                    showError('date_picker_unavailable');
                    }
                });
                });
            } catch (err) {
                console.error('Error binding datepicker listeners:', err);
            }
        })();
    </script>
@endpush
