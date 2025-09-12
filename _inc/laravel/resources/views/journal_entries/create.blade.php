@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ExtendingLayoutsConstants as EL,
        PlansConstants,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\{Plan, User, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $formId     = 'jrn-et-store-form';
    $storeBase  = VW::JRN_ET . '.store';
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'store_route_unavailable') ?? __('Journal entry store route is unavailable. Please contact technical support or your domain administrator.');

    $planUser   = $user && method_exists($user, 'creatorId') ? User::find($user->creatorId()) : null;
    $plan       = Plan::getPlan($planUser?->plan);
    $aiEnabled  = (int) data_get($plan, PlansConstants::COL_GPT, 0) === 1;

    $aiContextId = (string) ($user?->creatorId() ?? $user?->id ?? '0');
    $genBase     = 'generate';
    $genKebab    = Str::kebab($genBase);
    $genRes      = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
    $genUrl      = $genRes ? route($genRes, [$aiContextId]) : '#';
    $genGuard    = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'generate_route_unavailable') ?? __('Generate content route is unavailable. Please contact technical support or your domain administrator.');

    $indexBase   = VW::JRN_ET . '.index';
    $indexKebab  = Str::kebab($indexBase);
    $indexRes    = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
    $indexUrl    = $indexRes ? route($indexRes) : '#';
    $indexGuard  = Utility::fetchLinkMessage($lang, VW::JRN_ET, 'index_route_unavailable') ?? __('Journal entries index route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Journal Entry Create') }}
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
    @if($aiEnabled)
        <div class="{{ VC::FEND }}">
            <a  href="{{ $genUrl }}"
                data-size="md"
                data-ajax-popup-over="true"
                data-url="{{ $genUrl }}"
                data-guard-msg="{{ $genGuard }}"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}"
                class="ai-btn btn btn-icon {{ VC::BT_SM_PM }}">
                <i class="{{ VC::FAS_RB }}"></i>
                <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif
@endsection

@section(YW::ADM_CTT)
    {{ Form::open([
        'url'               => $storeUrl,
        'class'             => 'w-100',
        'id'                => $formId,
        'data-url'          => $storeUrl,
        'data-guard-msg'    => $storeGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="{{ VC::RW }} {{ VC::MT4 }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::P4 }}">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CLM4 }}">
                                <div class="form-group">
                                    {{ Form::label('journal_number', __('Journal Number'), ['class' => VC::FM_LB]) }}
                                    <input type="text" class="{{ VC::FM_CT }}" value="{{ $user?->journalNumberFormat($journalId) ?? __('Journal number unavailable') }}" readonly>
                                </div>
                            </div>
                            <div class="{{ VC::CLM4 }}">
                                <div class="form-group">
                                    {{ Form::label('date', __('Transaction Date'), ['class' => VC::FM_LB]) }}
                                    {{ Form::date('date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="{{ VC::CLM4 }}">
                                <div class="form-group">
                                    {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
                                    {{ Form::text('reference', '', ['class' => VC::FM_CT]) }}
                                </div>
                            </div>
                            <div class="{{ VC::CLM6 }} {{ VC::CM6 }}">
                                <div class="form-group">
                                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                                    {{ Form::textarea('description', '', ['class' => VC::FM_CT, 'rows' => 2]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="card repeater">
                    <div class="item-section {{ VC::PY2 }} {{ VC::PX3 }}">
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <a href="#" data-repeater-create class="{{ VC::BT_PRM }} {{ VC::ME3 }}" data-toggle="modal" data-target="#add-bank">
                                <i class="{{ VC::TI_PLS }}"></i> {{ __('Add Accounts') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} {{ VC::MB0 }}" data-repeater-list="accounts" id="sortable-table">
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
                                        <td width="25%" class="form-group pt-0">
                                            {{ Form::select('account', $accounts, '', ['class' => VC::FM_CT . ' js-searchBox', 'required' => 'required']) }}
                                        </td>
                                        <td>
                                            <div class="form-group price-input">
                                                {{ Form::text('debit', '', ['class' => VC::FM_CT . ' debit', 'required' => 'required', 'placeholder' => __('Debit')]) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group price-input">
                                                {{ Form::text('credit', '', ['class' => VC::FM_CT . ' credit', 'required' => 'required', 'placeholder' => __('Credit')]) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                {{ Form::text('description', '', ['class' => VC::FM_CT, 'placeholder' => __('Description')]) }}
                                            </div>
                                        </td>
                                        <td class="text-end amount">0.00</td>
                                        <td>
                                            <a href="#" class="{{ VC::TI_TRS_ALT }}" data-repeater-delete></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td class="text-end">
                                            <strong>{{ __('Total Credit') }} ({{ $user?->currencySymbol() ?? __('Currency unavailable') }})</strong>
                                        </td>
                                        <td class="text-end totalCredit">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td class="text-end">
                                            <strong>{{ __('Total Debit') }} ({{ $user?->currencySymbol() ?? __('Currency unavailable') }})</strong>
                                        </td>
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
                    class="{{ VC::BT_LG }} cancel-link"
                    data-href="{{ $indexUrl }}"
                    data-guard-msg="{{ $indexGuard }}">
                {{ __('Cancel') }}
            </input>
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}
@endsection


@push(ST::ADM_SCR_PG)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/journalEntries/lang/create.js') }}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/cancel.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/journalEntries/generateStore.js') }}"></script>
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
            console.error("jQuery is required");
            return;
            }

            const selector = "body";
            if ($(selector + " .repeater").length) {
            let $repeater;
            try {
                $repeater = $(`${selector} .repeater`).repeater({
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
                        $(this).slideUp(deleteElement);
                        $(this).remove();
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

                const val = $(`${selector} .repeater`).attr("data-value");
                if (val) {
                try {
                    const list = JSON.parse(val);
                    $repeater.setList(list);
                    list.forEach(item => {
                    const $row = $(
                        `#sortable-table .id[value="${item.id}"]`
                    ).parent();
                    $row.find(".item").val(item.product_id);
                    changeItem($row.find(".item"));
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
            let totalD = 0,
                totalC = 0;
            $(".debit").each((_, i) => (totalD += parseFloat($(i).val()) || 0));
            $(".credit").each((_, i) => (totalC += parseFloat($(i).val()) || 0));
            $(".totalDebit").html(totalD.toFixed(2));
            $(".totalCredit").html(totalC.toFixed(2));
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
            console.error("Initialization failed", e);
        }
        })();
    </script>
@endpush
