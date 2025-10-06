@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants as EL,
        PlansConstants,
        SettingsConstants,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\{Plan,User,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);

    $hasJournal = !empty($journalEntry ?? null) && data_get($journalEntry, 'id');

    $updateBase     = VW::JRN_ET . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasJournal) ? route($updateResolved, $journalEntry->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');

    $indexBase     = VW::JRN_ET . '.index';
    $indexKebab    = Str::kebab($indexBase);
    $indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
    $indexUrl      = $indexResolved ? route($indexResolved) : '#';
    $indexGuard    = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'index_route_unavailable') ?? __('Index route is unavailable. Please contact technical support or your domain administrator.');

    $genBase       = 'generate';
    $genResolved   = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
    $genUrl        = $genResolved ? route($genResolved, ['journal entry']) : '#';
    $genGuard      = Utility::fetchLinkMessage($lang, 'ai', 'generate_route_unavailable') ?? __('AI generation route is unavailable. Please contact technical support or your domain administrator.');

    $journalNumber = (method_exists($user, 'journalNumberFormat') ? ($user?->journalNumberFormat($journalEntry->journal_id) ?: __('No journal number found.')) : __('No journal number found.'));
    $currencySym   = (method_exists($user, 'currencySymbol') ? ($user?->currencySymbol() ?: __('No currency could be found.')) : __('No currency could be found.'));
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Journal Entry Edit') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Double Entry') }}</li>
    <li class="breadcrumb-item">{{ __('Journal Entry') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @if($user && method_exists($user, 'creatorId'))
        @php
            $planUser = User::find($user->creatorId());
            $plan = Plan::getPlan($planUser?->plan ?? DatabaseConstants::DEFAULT_PLAN);
        @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }}">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ $genUrl }}"
                   data-guard-msg="{{ $genGuard }}"
                   data-sv-localized="true"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
    @endif
@endsection

@section(YW::ADM_CTT)
    @if($hasJournal)
        {{ Form::model($journalEntry, [
            'url'               => $updateUrl,
            'method'            => 'PUT',
            'class'             => 'w-100',
            'id'                => 'journalEntry-edit-form',
            'data-url'          => $updateUrl,
            'data-guard-msg'    => $updateGuard,
            'data-sv-localized' => 'true'
        ]) }}
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                <div class="col-xl-12">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CLM4 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('journal_number', __('Journal Number'), ['class'=> VC::FM_LB]) }}
                                        <div class="form-icon-user">
                                            <input type="text" class="{{ VC::FM_CT }}" value="{{ $journalNumber }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CLM4 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('date', __('Transaction Date'), ['class'=> VC::FM_LB]) }}
                                        <div class="form-icon-user">
                                            {{ Form::date('date', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CLM4 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Form::label('reference', __('Reference'), ['class'=> VC::FM_LB]) }}
                                        <div class="form-icon-user">
                                            {{ Form::text('reference', null, ['class' => VC::FM_CT]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-md-8">
                                    <div class="{{ VC::FM_GCB12 }}">
                                        {{ Form::label('description', __('Description'), ['class'=> VC::FM_LB]) }}
                                        {{ Form::textarea('description', null, ['class' => VC::FM_CT,'rows'=>'2']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="col-12">
                    <div class="{{ VC::CD }} repeater" data-value='{!! json_encode($journalEntry->accounts) !!}'>
                        <div class="item-section {{ VC::PY2 }} py-4">
                            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                                <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }} justify-content-md-end">
                                    <div class="all-button-box">
                                        <a href="#" data-repeater-create="" class="{{ VC::BT_PRM }} me-4" data-toggle="modal" data-target="#add-bank">
                                            <i class="{{ VC::TI_PLS }}"></i> {{ __('Add Account') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table mb-0" data-repeater-list="accounts" id="sortable-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Account') }}</th>
                                            <th>{{ __('Debit') }}</th>
                                            <th>{{ __('Credit') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th class="text-end">{{ __('Amount') }}</th>
                                            <th width="2%"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="ui-sortable" data-repeater-item>
                                        <tr>
                                            {{ Form::hidden('id', null, ['class' => 'form-control id']) }}
                                            <td width="25%" class="{{ VC::FM_G }} pt-0">
                                                {{ Form::select('account', $accounts, '', ['class' => VC::FM_CT . ' js-searchBox','required'=>'required']) }}
                                            </td>
                                            <td>
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::text('debit','', ['class' => VC::FM_CT . ' debit','required'=>'required','placeholder'=>__('Debit')]) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::text('credit','', ['class' => VC::FM_CT . ' credit','required'=>'required','placeholder'=>__('Credit')]) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::text('description', null, ['class' => VC::FM_CT,'placeholder'=>__('Description')]) }}
                                                </div>
                                            </td>
                                            <td class="text-end amount">0.00</td>
                                            <td>
                                                <a href="#" class="ti ti-trash {{ VC::TXT_WT }} text-danger" data-repeater-delete></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td></td>
                                            <td class="text-end"><strong>{{ __('Total Credit') }} ({{ $currencySym }})</strong></td>
                                            <td class="text-end totalCredit">0.00</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td class="text-end"><strong>{{ __('Total Debit') }} ({{ $currencySym }})</strong></td>
                                            <td class="text-end totalDebit">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <input type="button"
                       value="{{ __('Cancel') }}"
                       class="{{ VC::BT_LG }}"
                       data-index-url="{{ $indexUrl }}"
                       data-guard-msg="{{ $indexGuard }}"
                       onclick="location.href='{{ $indexUrl }}';">
                <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
            </div>
        {{ Form::close() }}
    @else
        <p>{{ __('No journal entry found.') }}</p>
    @endif
@endsection

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/edit.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/generateEdit.js') }}"></script>
    <script defer src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/journalEntries/lang/edit.js') }}"></script>
    <script defer>
        (() => {
        const DATA_LISTENER_ADDED = "data-listener-added";
        const ERR_FB = "# ERROR";
        const DATA_CLIENT_LOCALIZED = "data-client-localized";
        const DATA_GUARD_MSG = "data-guard-msg";

        const getLocalizedMessage = (el, key) => {
            let msg = ERR_FB;
            if (
            el?.getAttribute("data-sv-localized") === "true" ||
            el?.getAttribute(DATA_CLIENT_LOCALIZED) === "true"
            ) {
            msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
            } else {
            let lang = (
                sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                window.translations?.["en"]?.[key] ||
                ERR_FB;
            if (msg !== ERR_FB) {
                el.setAttribute(DATA_GUARD_MSG, msg);
                el.setAttribute(DATA_CLIENT_LOCALIZED, "true");
            }
            }
            return msg;
        };

        const handleErrorDisplay = (el, key) => {
            const message = el ? getLocalizedMessage(el, key) : ERR_FB;
            const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap?.Toast;
            if (hasBootstrap) {
            if (!document.querySelector("#error-toast")) {
                const toast = document.createElement("div");
                toast.id = "error-toast";
                toast.className = "toast align-items-center text-bg-danger border-0";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
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
            new bootstrap.Toast(document.querySelector("#error-toast")).show();
            } else {
            alert(message);
            }
        };

        try {
            if (typeof $ === "undefined") {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
                return;
            }

            const selector = "body";
            if ($(selector + " .repeater").length) {
            let $repeater;
            try {
                $repeater = $(selector + " .repeater").repeater({
                initEmpty: false,
                defaultValues: { status: 1 },
                show() {
                    try {
                    $(this).slideDown();
                    const $multi = $(this).find("input.multi");
                    if ($multi.length) {
                        $multi.MultiFile({
                        max: 3,
                        accept: "png|jpg|jpeg",
                        max_size: "{{ SettingsConstants::MAX_U_SIZE_DEF }}",
                        });
                    }
                    if (typeof JsSearchBox === "function") {
                        JsSearchBox();
                    }
                    if ($(".select2").length) {
                        $(".select2").select2();
                    }
                    } catch {
                    const el = this;
                    if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                        el.addEventListener("click", () =>
                        handleErrorDisplay(el, "repeater_show_unavailable")
                        );
                        el.setAttribute(DATA_LISTENER_ADDED, "true");
                    }
                    }
                },
                hide(deleteElement) {
                    try {
                    if (confirm("Are you sure you want to delete this element?")) {
                        const $row = $(this);
                        $row.slideUp(deleteElement);
                        $row.remove();
                        let totalD = 0,
                        totalC = 0;
                        $(".debit").each(
                        (_, i) => (totalD += parseFloat($(i).val()) || 0)
                        );
                        $(".credit").each(
                        (_, i) => (totalC += parseFloat($(i).val()) || 0)
                        );
                        $(".totalDebit").html(totalD.toFixed(2));
                        $(".totalCredit").html(totalC.toFixed(2));
                        const id = $row.find(".id").val();
                        $.ajax({
                        url: '{{ route(VW::JRN.".account.destroy") }}',
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("#token").val() },
                        data: { id },
                        cache: false,
                        success: () => {},
                        error: () =>
                            handleErrorDisplay($row[0], "destroy_unavailable"),
                        });
                    }
                    } catch {
                    const el = this;
                    if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                        el.addEventListener("click", () =>
                        handleErrorDisplay(el, "repeater_hide_unavailable")
                        );
                        el.setAttribute(DATA_LISTENER_ADDED, "true");
                    }
                    }
                },
                ready: () => {},
                isFirstItemUndeletable: true,
                });

                const val = $(selector + " .repeater").attr("data-value") ?? "";
                if (val) {
                try {
                    const list = JSON.parse(val);
                    $repeater.setList(list);
                    list.forEach((item, i) => {
                    if (item.credit > 0) {
                        $(`input[name="accounts[${i}][credit]"]`).trigger("keyup");
                    }
                    if (item.debit > 0) {
                        $(`input[name="accounts[${i}][debit]"]`).trigger("keyup");
                    }
                    });
                } catch {
                    handleErrorDisplay(
                    document.querySelector(".repeater"),
                    "repeater_show_unavailable"
                    );
                }
                }
            } catch {
                handleErrorDisplay(
                document.querySelector(".repeater"),
                "repeater_show_unavailable"
                );
            }
            }

            const recalc = () => {
            try {
                let totalD = 0,
                totalC = 0;
                $(".debit").each((_, i) => (totalD += parseFloat($(i).val()) || 0));
                $(".credit").each((_, i) => (totalC += parseFloat($(i).val()) || 0));
                $(".totalDebit").html(totalD.toFixed(2));
                $(".totalCredit").html(totalC.toFixed(2));
            } catch {
                handleErrorDisplay(document.body, "calc_unavailable");
            }
            };

            $(document).on("keyup", ".debit", function () {
            try {
                const $row = $(this).closest("tr");
                $row.find(".credit").val("").prop("disabled", true);
                if (!$(this).val()) $row.find(".credit").prop("disabled", false);
                $row.find(".amount").html($(this).val());
                recalc();
            } catch {
                handleErrorDisplay(this, "calc_unavailable");
            }
            });

            $(document).on("keyup", ".credit", function () {
            try {
                const $row = $(this).closest("tr");
                $row.find(".debit").val("").prop("disabled", true);
                if (!$(this).val()) $row.find(".debit").prop("disabled", false);
                $row.find(".amount").html($(this).val());
                recalc();
            } catch {
                handleErrorDisplay(this, "calc_unavailable");
            }
            });
        } catch (e) {
            if (
                window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1"
            ) console.error("jQuery unavailable");
        }
        })();
    </script>
@endpush
