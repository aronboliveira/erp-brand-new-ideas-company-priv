@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Gate, Route};
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Meeting')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Meeting')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create meeting')
            @php
                $calendarUrl   = Route::has(ViewsConstants::MT.'.calendar')
                    ? route(ViewsConstants::MT.'.calendar')
                    : '#';
                $createUrl     = Route::has(ViewsConstants::MT.'.create')
                    ? route(ViewsConstants::MT.'.create')
                    : '#';
                $calendarClass = 'calendar-meeting-link';
                $createClass   = 'create-meeting-link';
            @endphp
            <a
                href="{{ $calendarUrl }}"
                id="{{ $calendarClass }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $calendarClass }}"
                data-url="{{ $calendarUrl }}"
                data-guard-msg="{{ __('Calendar view route is unavailable. Please contact technical support or your domain administrator.') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Calendar View') }}"
                data-original-title="{{ __('Calendar View') }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_CLD }}"></i>
            </a>
            <a
                href="{{ $createUrl }}"
                id="{{ $createClass }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $createClass }}"
                data-url="{{ $createUrl }}"
                data-guard-msg="{{ __('Create meeting route is unavailable. Please contact technical support or your domain administrator.') }}"
                data-size="lg"
                data-ajax-popup="true"
                data-title="{{ __('Create New Meeting') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-original-title="{{ __('Create') }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Meeting title')}}</th>
                                <th>{{__('Meeting Date')}}</th>
                                <th>{{__('Meeting Time')}}</th>
                                @if(Gate::check('edit meeting') || Gate::check('delete meeting'))
                                    <th width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($meetings as $meeting)
                                <tr>
                                    <td>{{ $meeting->title }}</td>
                                    <td>{{ $user?->dateFormat($meeting->date) }}</td>
                                    <td>{{ $user?->timeFormat($meeting->time) }}</td>
                                    @if(Gate::check('edit meeting') || Gate::check('delete meeting'))
                                        <td>
                                            @can('edit meeting')
                                                @php
                                                    $editUrl    = Route::has(ViewsConstants::MT.'.edit')
                                                        ? route(ViewsConstants::MT.'.edit', $meeting->id)
                                                        : '#';
                                                    $editClass  = 'edit-meeting-link';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="{{ $editUrl }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT }} {{ $editClass }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-guard-msg="{{ __('Edit meeting route is unavailable. Please contact technical support or your domain administrator.') }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Meeting') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-original-title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete meeting')
                                                @php
                                                    $deleteUrl   = Route::has(ViewsConstants::MT.'.destroy')
                                                        ? route(ViewsConstants::MT.'.destroy', $meeting->id)
                                                        : '#';
                                                    $deleteClass = 'delete-meeting-link';
                                                    $formId      = 'delete-meeting-form-'.$meeting->id;
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method' => 'DELETE',
                                                        'url'    => $deleteUrl,
                                                        'id'     => $formId
                                                    ]) !!}
                                                        <a
                                                            href="{{ $deleteUrl }}"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }} {{ $deleteClass }}"
                                                            data-url="{{ $deleteUrl }}"
                                                            data-guard-msg="{{ __('Delete meeting route is unavailable. Please contact technical support or your domain administrator.') }}"
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
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
            dept_init_fail: "فشل تهيئة قائمة الأقسام",
            dept_load_fail: "فشل تحميل الأقسام",
            emp_load_fail: "فشل تحميل الموظفين",
            choices_fail: "فشل تحميل مكتبة الاختيارات",
            edit_meeting_route_unavailable: "تحرير مسار الاجتماع غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            calendar_view_route_unavailable: "عرض التقويم غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            create_meeting_route_unavailable:
            "إنشاء مسار اجتماع جديد غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال الخاص بك.",
            delete_meeting_route_unavailable: "حذف مسار الاجتماع غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
        },
        da: {
            dept_init_fail: "Kunne ikke initialisere afdelingsliste",
            dept_load_fail: "Kunne ikke indlæse afdelinger",
            emp_load_fail: "Kunne ikke indlæse medarbejdere",
            choices_fail: "Kunne ikke indlæse valgbibliotek",
            edit_meeting_route_unavailable:
            "Redigeringsruten til mødet er ikke tilgængelig. Kontakt venligst teknisk support eller din domæneadministrator.",
            calendar_view_route_unavailable:
            "Kalendervisningsrute er ikke tilgængelig. Kontakt venligst teknisk support eller din domæneadministrator.",
            create_meeting_route_unavailable:
            "Opret ny møderute er ikke tilgængelig. Kontakt venligst teknisk support eller din domæneadministrator.",
            delete_meeting_route_unavailable:
            "Slet møderute er ikke tilgængelig. Kontakt venligst teknisk support eller din domæneadministrator.",
        },
        de: {
            dept_init_fail: "Abteilungsliste konnte nicht initialisiert werden",
            dept_load_fail: "Abteilungen konnten nicht geladen werden",
            emp_load_fail: "Mitarbeiter konnten nicht geladen werden",
            choices_fail: "Auswahlbibliothek konnte nicht geladen werden",
            edit_meeting_route_unavailable:
            "Die Bearbeitung der Meeting-Route ist nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domänenadministrator.",
            calendar_view_route_unavailable:
            "Kalenderansicht-Route ist nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domänenadministrator.",
            create_meeting_route_unavailable:
            "Erstellen einer neuen Meeting-Route ist nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domänenadministrator.",
            delete_meeting_route_unavailable:
            "Löschen der Meeting-Route ist nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domänenadministrator.",
        },
        en: {
            dept_init_fail: "Failed to initialize department list",
            dept_load_fail: "Failed to load departments",
            emp_load_fail: "Failed to load employees",
            choices_fail: "Failed to load choices library",
            edit_meeting_route_unavailable:
            "Edit meeting route is unavailable. Please contact technical support or your domain administrator.",
            calendar_view_route_unavailable:
            "Calendar view route is unavailable. Please contact technical support or your domain administrator.",
            create_meeting_route_unavailable:
            "Create new meeting route is unavailable. Please contact technical support or your domain administrator.",
            delete_meeting_route_unavailable:
            "Delete meeting route is unavailable. Please contact technical support or your domain administrator.",
        },
        es: {
            dept_init_fail: "Error al inicializar lista de departamentos",
            dept_load_fail: "Error al cargar departamentos",
            emp_load_fail: "Error al cargar empleados",
            choices_fail: "Error al cargar biblioteca de opciones",
            edit_meeting_route_unavailable:
            "No es posible editar la ruta de la reunión. Por favor, póngase en contacto con el soporte técnico o con el administrador de dominio.",
            calendar_view_route_unavailable:
            "La ruta de la vista de calendario no está disponible. Por favor, póngase en contacto con el soporte técnico o con el administrador de dominio.",
            create_meeting_route_unavailable:
            "La creación de una nueva ruta de reunión no está disponible. Por favor, póngase en contacto con soporte técnico o con el administrador de dominio.",
            delete_meeting_route_unavailable:
            "La eliminación de la ruta de la reunión no está disponible. Por favor, póngase en contacto con el soporte técnico o con el administrador de dominio.",
        },
        fr: {
            dept_init_fail: "Échec de l'initialisation de la liste des départements",
            dept_load_fail: "Échec du chargement des départements",
            emp_load_fail: "Échec du chargement des employés",
            choices_fail: "Échec du chargement de la bibliothèque de choix",
            edit_meeting_route_unavailable:
            "La modification de l'itinéraire de réunion n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            calendar_view_route_unavailable:
            "La route de la vue calendrier n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            create_meeting_route_unavailable:
            "La création d'un nouvel itinéraire de réunion n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            delete_meeting_route_unavailable:
            "La suppression de l'itinéraire de réunion n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
        },
        he: {
            dept_init_fail: "נכשל באתחול רשימת המחלקות",
            dept_load_fail: "נכשל בטעינת המחלקות",
            emp_load_fail: "נכשל בטעינת העובדים",
            choices_fail: "נכשל בטעינת ספריית הבחירות",
            edit_meeting_route_unavailable: "עריכת מסלול הפגישה אינה זמינה. אנא פנה לתמיכה הטכנית או למנהל התחום שלך.",
            calendar_view_route_unavailable: "נתיב תצוגת היומן אינו זמין. אנא פנה לתמיכה הטכנית או למנהל הדומיין שלך.",
            create_meeting_route_unavailable: "יצירת מסלול פגישה חדש אינה זמינה. אנא פנה לתמיכה הטכנית או למנהל התחום שלך.",
            delete_meeting_route_unavailable: "מחיקת מסלול הפגישה אינה זמינה. אנא פנה לתמיכה הטכנית או למנהל הדומיין שלך.",
        },
        it: {
            dept_init_fail: "Impossibile inizializzare l'elenco dei dipartimenti",
            dept_load_fail: "Impossibile caricare i dipartimenti",
            emp_load_fail: "Impossibile caricare i dipendenti",
            choices_fail: "Impossibile caricare la libreria delle scelte",
            edit_meeting_route_unavailable:
            "La modifica del percorso della riunione non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore di dominio.",
            calendar_view_route_unavailable:
            "Il percorso della vista calendario non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore di dominio.",
            create_meeting_route_unavailable:
            "La creazione di un nuovo percorso riunione non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore di dominio.",
            delete_meeting_route_unavailable:
            "La cancellazione del percorso della riunione non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore di dominio.",
        },
        ja: {
            dept_init_fail: "部門リストの初期化に失敗しました",
            dept_load_fail: "部門の読み込みに失敗しました",
            emp_load_fail: "従業員の読み込みに失敗しました",
            choices_fail: "選択ライブラリの読み込みに失敗しました",
            edit_meeting_route_unavailable:
            "会議ルートの編集は利用できません。技術サポートまたはドメイン管理者にお問い合わせください。",
            calendar_view_route_unavailable:
            "カレンダー表示ルートは利用できません。技術サポートまたはドメイン管理者にお問い合わせください。",
            create_meeting_route_unavailable:
            "新しい会議ルートの作成は利用できません。技術サポートまたはドメイン管理者にお問い合わせください。",
            delete_meeting_route_unavailable:
            "会議ルートの削除は利用できません。技術サポートまたはドメイン管理者にお問い合わせください。",
        },
        nl: {
            dept_init_fail: "Initialiseren afdelingslijst mislukt",
            dept_load_fail: "Laden afdelingen mislukt",
            emp_load_fail: "Laden werknemers mislukt",
            choices_fail: "Laden keuzebibliotheek mislukt",
            edit_meeting_route_unavailable:
            "Het bewerken van de vergaderroute is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
            calendar_view_route_unavailable:
            "Route voor kalenderweergave is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
            create_meeting_route_unavailable:
            "Het aanmaken van een nieuwe vergaderroute is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
            delete_meeting_route_unavailable:
            "Het verwijderen van de vergaderroute is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
        },
        pl: {
            dept_init_fail: "Nie udało się zainicjować listy działów",
            dept_load_fail: "Nie udało się załadować działów",
            emp_load_fail: "Nie udało się załadować pracowników",
            choices_fail: "Nie udało się załadować biblioteki wyboru",
            edit_meeting_route_unavailable:
            "Edycja trasy spotkania jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            calendar_view_route_unavailable:
            "Trasa widoku kalendarza jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            create_meeting_route_unavailable:
            "Tworzenie nowej trasy spotkania jest niedostępne. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            delete_meeting_route_unavailable:
            "Usuwanie trasy spotkania jest niedostępne. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
        },
        pt: {
            dept_init_fail: "Falha ao inicializar lista de departamentos",
            dept_load_fail: "Falha ao carregar departamentos",
            emp_load_fail: "Falha ao carregar funcionários",
            choices_fail: "Falha ao carregar biblioteca de escolhas",
            edit_meeting_route_unavailable:
            "A edição da rota da reunião não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador do domínio.",
            calendar_view_route_unavailable:
            "A rota de exibição do calendário não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
            create_meeting_route_unavailable:
            "A criação de uma nova rota de reunião não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
            delete_meeting_route_unavailable:
            "A exclusão da rota da reunião não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador do domínio.",
        },
        "pt-br": {
            dept_init_fail: "Falha ao inicializar lista de departamentos",
            dept_load_fail: "Falha ao carregar departamentos",
            emp_load_fail: "Falha ao carregar funcionários",
            choices_fail: "Falha ao carregar biblioteca de escolhas",
            edit_meeting_route_unavailable:
            "A edição da rota da reunião não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador do domínio.",
            calendar_view_route_unavailable:
            "A rota de exibição do calendário não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
            create_meeting_route_unavailable:
            "A criação de uma nova rota de reunião não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
            delete_meeting_route_unavailable:
            "A exclusão da rota da reunião não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
        },
        ru: {
            dept_init_fail: "Не удалось инициализировать список отделов",
            dept_load_fail: "Не удалось загрузить отделы",
            emp_load_fail: "Не удалось загрузить сотрудников",
            choices_fail: "Не удалось загрузить библиотеку выбора",
            edit_meeting_route_unavailable:
            "Изменение маршрута встречи недоступно. Пожалуйста, свяжитесь с технической поддержкой или администратором домена.",
            calendar_view_route_unavailable:
            "Маршрут просмотра календаря недоступен. Пожалуйста, свяжитесь с технической поддержкой или администратором домена.",
            create_meeting_route_unavailable:
            "Создание нового маршрута встречи недоступно. Пожалуйста, свяжитесь с технической поддержкой или администратором домена.",
            delete_meeting_route_unavailable:
            "Удаление маршрута встречи недоступно. Пожалуйста, свяжитесь с технической поддержкой или администратором домена.",
        },
        tr: {
            dept_init_fail: "Departman listesi başlatılamadı",
            dept_load_fail: "Departmanlar yüklenemedi",
            emp_load_fail: "Çalışanlar yüklenemedi",
            choices_fail: "Seçim kütüphanesi yüklenemedi",
            edit_meeting_route_unavailable:
            "Toplantı rotasını düzenleme kullanılamıyor. Lütfen teknik destek veya alan yöneticisi ile iletişime geçin.",
            calendar_view_route_unavailable:
            "Takvim görüntüleme rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            create_meeting_route_unavailable:
            "Yeni toplantı rotası oluşturma kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            delete_meeting_route_unavailable:
            "Toplantı rotasını silme kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
        },
        zh: {
            dept_init_fail: "无法初始化部门列表",
            dept_load_fail: "无法加载部门",
            emp_load_fail: "无法加载员工",
            choices_fail: "无法加载选择库",
            edit_meeting_route_unavailable: "无法编辑会议路由。请联系技术支持或您的域管理员。",
            calendar_view_route_unavailable: "日历视图路由不可用。请联系技术支持或您的域管理员。",
            create_meeting_route_unavailable: "无法创建新的会议路由。请联系技术支持或您的域管理员。",
            delete_meeting_route_unavailable: "删除会议路由不可用。请联系技术支持或您的域管理员。",
        },
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
    @can('create meeting')
        <script defer src="{{ asset('assets/js/routes/meetings/calendar.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/meetings/create.js') }}"></script>
    @endcan
    @can('delete meeting')
        <script defer src="{{ asset('assets/js/routes/meetings/delete.js') }}"></script>
    @endcan
    <script defer>
        (() => {
            const BS_LINK = 'link[href*="bootstrap"]';
            let toastContainer = null;
            const getToastContainer = () => {
                if (!toastContainer) {
                toastContainer =
                    document.querySelector(".toast-container") ||
                    document.createElement("div");
                toastContainer.className =
                    "toast-container position-fixed bottom-0 end-0 p-3";
                toastContainer.style.zIndex = "1080";
                if (!toastContainer.isConnected) document.body.append(toastContainer);
                }
                return toastContainer;
            };
            const showError = key => {
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                let msg = errFb;
                if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true")
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                else {
                    let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                    .toLowerCase()
                    .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    const msgKey = key;
                    msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.["en"]?.[msgKey] ||
                    errFb;
                    if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                    }
                }
                const hasBootstrap =
                document.querySelector(BS_LINK) && window.bootstrap?.Toast;
                if (hasBootstrap) {
                const container = getToastContainer();
                const toast = document.createElement("div");
                toast.className = "toast align-items-center text-bg-danger border-0";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                container.append(toast);
                new bootstrap.Toast(toast, { autohide: true, delay: 5000 }).show();
                } else {
                alert(msg);
                }
            };
            const initChoices = (selector, options = {}) => {
                try {
                if (typeof Choices === "undefined") {
                    showError("choices_fail");
                    return null;
                }

                const element = document.querySelector(selector);
                if (!element) return null;

                if (element.choices) element.choices.destroy();

                return new Choices(element, {
                    removeItemButton: true,
                    ...options,
                });
                } catch {
                showError("choices_fail");
                return null;
                }
            };

            const getDepartment = async bid => {
                if (!bid) {
                const deptDiv = document.getElementById("department_div");
                if (deptDiv) deptDiv.innerHTML = "";
                return;
                }

                try {
                const data =
                    (await $.ajax({
                    url: '{{route(ViewsConstants::MT.'.getdepartment')}}',
                    type: "POST",
                    data: { branch_id: bid, _token: "{{ csrf_token() }}" },
                    })) ?? {};

                const deptDiv = document.getElementById("department_div");
                if (!deptDiv) return;

                deptDiv.innerHTML =
                    '<select class="form-control" id="department_id" name="department_id[]" multiple></select>';

                const select = document.getElementById("department_id");
                if (!select) return;

                select.innerHTML = "";
                select.appendChild(new Option('{{__('Select Department')}}', ''));
                select.appendChild(new Option('{{__('All Department')}}', '0'));

                Object.entries(data).forEach(([key, value]) => {
                    select.appendChild(new Option(value, key));
                });

                initChoices("#department_id");
                } catch {
                showError("dept_load_fail");
                }
            };

            const getEmployee = async did => {
                if (!did?.length) {
                const empDiv = document.getElementById("employee_div");
                if (empDiv) empDiv.innerHTML = "";
                return;
                }

                try {
                const data =
                    (await $.ajax({
                    url: '{{route(ViewsConstants::MT.'.getemployee')}}',
                    type: "POST",
                    data: { department_id: did, _token: "{{ csrf_token() }}" },
                    })) ?? {};

                const empDiv = document.getElementById("employee_div");
                if (!empDiv) return;

                empDiv.innerHTML =
                    '<select class="form-control" id="employee_id" name="employee_id[]" multiple></select>';

                const select = document.getElementById("employee_id");
                if (!select) return;

                select.innerHTML = "";
                select.appendChild(new Option('{{__('Select Employee')}}', ''));
                select.appendChild(new Option('{{__('All Employee')}}', '0'));

                Object.entries(data).forEach(([key, value]) => {
                    select.appendChild(new Option(value, key));
                });

                initChoices("#employee_id");
                } catch {
                showError("emp_load_fail");
                }
            };

            const init = () => {
                try {
                const branchId = $("#branch_id").val() ?? "";
                if (!branchId) return;
                getDepartment(branchId);
                } catch {
                showError("dept_init_fail");
                }
            };

            const cleanupChoices = () => {
                ["#department_id", "#employee_id"].forEach(selector => {
                const element = document.querySelector(selector);
                if (element?.choices) {
                    element.choices.destroy();
                    element.choices = null;
                }
                });
            };

            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                mutation.removedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                    if (node.matches("#department_div, #employee_div")) {
                        cleanupChoices();
                    }
                    }
                });
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });

            const setupListeners = () => {
                $(document)
                .off("change", "select[name=branch_id]")
                .on("change", "select[name=branch_id]", ({ target }) => {
                    cleanupChoices();
                    const branchId = $(target).val() ?? "";
                    if (!branchId) return;
                    getDepartment(branchId);
                });

                $(document)
                .off("change", "#department_id")
                .on("change", "#department_id", ({ target }) => {
                    cleanupChoices();
                    const dept = $(target).val() ?? [];
                    if (!dept?.length) return;
                    getEmployee(dept);
                });
            };

            $(document).ready(() => {
                init();
                setupListeners();
            });
        })();
    </script>
@endpush
