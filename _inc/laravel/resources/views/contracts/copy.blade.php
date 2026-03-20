
@php
    try {
$lang = Utility::fetchUserLang();
        $routeName   = VW::CTC.'.copy.store';
        $formId      = 'copy_contract';
        $actionHref  = Route::has($routeName) ? route($routeName, $contract->id) : '#';
        $guardMsg    = Utility::fetchLinkMessage($lang, VW::CTC, 'copy_route_unavailable')
                        ?? __('Copy contract route is unavailable. Please contact technical support or your domain administrator.');
        $formParams = [
            'method'            => 'POST',
            'id'                => $formId,
            'data-sv-localized' => 'true',
            'data-guard-msg'    => $guardMsg,
            'data-action-href'  => $actionHref,
        ];
        if (Route::has($routeName))
            $formParams['route'] = [$routeName, $contract->id];
        else
            $formParams['url'] = '#';
    } catch (\Throwable $e) {
        \Log::error('contracts/copy — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if(!empty($contract) && isset($contract->id))
    {{ Form::model($contract, $formParams) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('client', __('Client'), ['class' => VC::FM_LB]) }}
                    @if(!empty($clients) && ((is_array($clients) && count($clients)) || ($clients instanceof Collection && $clients->isNotEmpty())))
                        {{ Form::select('client', $clients, null, ['class' => VC::FM_CT_SL . ' select client_select', 'id' => 'client_select']) }}
                    @else
                        {{ Form::select('client', [__('No clients available')], null, ['class' => VC::FM_CT_SL . ' select client_select', 'id' => 'client_select']) }}
                    @endif
                </div>

                <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                    {{ Form::label('project', __('Project'), ['class' => VC::FM_LB]) }}
                    <div class="project-div">
                        @if(!empty($project) && ((is_array($project) && count($project)) || ($project instanceof Collection && $project->isNotEmpty())))
                            {{ Form::select('project', $project, null, ['class' => VC::FM_CT_SL . ' select project_select', 'id' => 'project_id', 'name' => 'project_id[]']) }}
                        @else
                            {{ Form::select('project', [__('No projects available')], null, ['class' => VC::FM_CT_SL . ' select project_select', 'id' => 'project_id', 'name' => 'project_id[]']) }}
                        @endif
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('type', __('Contract Type'), ['class' => VC::FM_LB]) }}
                    @if(!empty($contractTypes) && ((is_array($contractTypes) && count($contractTypes)) || ($contractTypes instanceof Collection && $contractTypes->isNotEmpty())))
                        {{ Form::select('type', $contractTypes, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    @else
                        {{ Form::select('type', [__('No contract types available')], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    @endif
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('value', __('Contract Value'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('value', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('start_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('end_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 3]) }}
                </div>
            </div>
        </div>

        <div class="modal-footer pr-0">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
            {{ Form::submit(__('Copy'), ['class' => 'btn btn-primary']) }}
        </div>

        <script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
        <script async src="{{ asset('assets/js/routes/contracts/lang/copy.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/contracts/lang/payment.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/contracts/copy.js') }}"></script>
        <script defer>
        (function () {
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-pw-error";
            const dataBindGuard = "data-pw-bound";
            const qs = (s, r = document) => r.querySelector(s);
            const hasBS = () => !!(qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]')) && !!(window.bootstrap && window.bootstrap.Toast);
            const ensureToastContainer = () => { let c = qs("#np-toast-container"); if (c) return c; c = document.createElement("div"); c.id = "np-toast-container"; c.setAttribute("aria-live", "polite"); c.setAttribute("aria-atomic", "true"); c.style.position = "fixed"; c.style.top = "1rem"; c.style.right = "1rem"; document.body.appendChild(c); return c; };
            const showErrorNow = (message) => { if (hasBS()) { const container = ensureToastContainer(); let t = qs("#np-toast", container); if (!t) { t = document.createElement("div"); t.id = "np-toast"; t.className = "toast"; t.setAttribute("role", "alert"); t.setAttribute("aria-live", "assertive"); t.setAttribute("aria-atomic", "true"); t.innerHTML = '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>'; container.appendChild(t); } const body = t.querySelector(".toast-body"); if (body) body.textContent = message ?? errFb; try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); } } else { alert(message ?? errFb); } };
            const schedulePointerupError = (msg) => { const host = document.body; if (!host || host.getAttribute(dataErrGuard) === "true") return; host.setAttribute(dataErrGuard, "true"); const once = () => { try { showErrorNow(msg); } finally { host.removeAttribute(dataErrGuard); } }; document.addEventListener("pointerup", once, { once: true }); const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener("pointerup", once); o.disconnect(); } }); mo.observe(document.documentElement, { childList: true, subtree: true }); };
            const localize = (el, key) => { const err = errFb; const dataClient = dataClientLocalized; const dataGuard = dataGuardMsg; if (el?.getAttribute?.("data-sv-localized") === "true" || el?.getAttribute?.(dataClient) === "true") { return el.getAttribute(dataGuard) || err; } let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-"); lang = lang === "pt-br" ? lang : lang.slice(0, 2); const msgKey = key; let msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute?.(dataGuard) || window.translations?.en?.[msgKey] || err; if (msg !== err && el) { el.setAttribute(dataGuard, msg); el.setAttribute(dataClient, "true"); } return msg; };
            const verifyRoute = (candidate) => { const a = document.createElement("a"); a.setAttribute("data-url", candidate ?? ""); a.href = candidate ?? ""; const url = a.getAttribute("data-url"); const href = a.href; if ((!url || url === "#") && (!href || href === "#")) return false; return true; };
            const init = () => {
            try {
                const host = document.body;
                if (!host || host.getAttribute(dataBindGuard) === "true") return;
                host.setAttribute(dataBindGuard, "true");
                const containerId = "payment-form-container";
                const ctn = qs("#" + containerId);
                if (!ctn) { schedulePointerupError(localize(document.body, "payment_init_unavailable")); return; }
                const BrickCtor = window.Brick;
                if (typeof BrickCtor !== "function") {
                    try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) {
                        console.error("Brick library unavailable");
                    }
                } catch (_) {}
                schedulePointerupError(localize(document.body, "plugin_unavailable"));
                return;
                }
                const action = '{{route(ViewsConstants::PLN.".pay.with.paymentwall",[$data["plan_id"],$data["coupon"]])}}' ?? "";
                if (!verifyRoute(action)) { schedulePointerupError(localize(document.body, "route_unavailable")); return; }
                const brick = new BrickCtor({
                public_key: '{{ $admin_payment_setting['paymentwall_public_key'] }}' ?? "",
                amount: '{{$plan->price }}' ?? "",
                currency: '{{App\Models\Utility::getValByName('site_currency')}}' ?? "",
                container: containerId,
                action: action,
                form: {
                    merchant: 'Paymentwall',
                    product: '{{$plan->name}}' ?? "",
                    pay_button: 'Pay',
                    show_zip: true,
                    show_cardholder: true
                }
                });
                const toErr = '{{route("error.plan.show",1)}}';
                const toOk = '{{route("error.plan.show",2)}}';
                const go = (target) => { if (!verifyRoute(target)) { schedulePointerupError(localize(document.body, "payment_redirect_unavailable")); return; } window.location.href = target; };
                brick.showPaymentForm(function (data) { try { const f = Number((data && data.flag) ?? 0); go(f === 1 ? toErr : toOk); } catch (_) { schedulePointerupError(localize(document.body, "payment_redirect_unavailable")); } }, function (errors) { try { const f = Number((errors && errors.flag) ?? 0); go(f === 1 ? toErr : toOk); } catch (_) { schedulePointerupError(localize(document.body, "payment_redirect_unavailable")); } });
                const mo = new MutationObserver(function () { if (!document.body.contains(ctn)) { host.removeAttribute(dataBindGuard); } });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            } catch (_) { schedulePointerupError(localize(document.body, "payment_init_unavailable")); }
            };
            if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init, { once: true }); else init();
        })();
        </script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALT_DNG }} {{ VC::DBL }}">
        {{ __('Contract data is not available. Please try again later.') }}
    </div>
@endif
