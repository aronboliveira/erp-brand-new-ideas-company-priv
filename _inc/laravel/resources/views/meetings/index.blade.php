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
                            @if(Utility::isFilled($meetings) ?? [])
                                @foreach ($meetings as $meeting)
                                    <tr>
                                        <td>{{ !empty($meeting->title) ? $meeting->title : __('Meeting title was not available.') }}</td>
                                        <td>{{ method_exists($user, 'dateFormat') ? (!empty($meeting->date) ? $user->dateFormat($meeting->date) : __('Meeting date is not available.')) : __('Failed to format meeting date.') }}</td>
                                        <td>{{ method_exists($user, 'timeFormat') ? (!empty($meeting->time) ? $user->timeFormat($meeting->time) : __('Meeting time is not available.')) : __('Failed to format meeting time.') }}</td>
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
                            @else
                                <tr>
                                    <td colspan="4">
                                        <div class="text-center p-4">
                                            <h5>{{ __('No meetings available') }}</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/meetings/lang/index.js') }}"></script>
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
                select.appendChild(new Option("{{__('Select Department')}}', "));
                select.appendChild(new Option("{{__('All Department')}}', '0"));

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
                    url: '{{route(ViewsConstants::MT.".getemployee")}}',
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
                select.appendChild(new Option("{{__('Select Employee')}}", ""));
                select.appendChild(new Option("{{__('All Employee')}}", "0"));

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
