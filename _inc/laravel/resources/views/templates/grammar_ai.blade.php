@php
	try {
$lang = Utility::fetchUserLang();

		$formId = 'ai-grammar-form';
		$srcId = 'grammar-source';
		$outId = 'ai-description';
		$regenBtnId = 'grammar-regenerate-btn';
		$copyBtnId = 'grammar-copy-btn';

		$descPh = __('Description') ?: __('No description available');
		$regenLabel = __('Re Generate') ?: __('Re Generate');
		$copyLabel = __('Copy Text') ?: __('Copy Text');

		$copyOkMsg = Utility::fetchLinkMessage($lang, 'ai_grammar', 'copied_to_clipboard') ?? 'Text copied to clipboard.';
		$copyErrMsg = Utility::fetchLinkMessage($lang, 'ai_grammar', 'copy_failed') ?? 'Copy failed. Please try again.';
		$noSrcMsg = Utility::fetchLinkMessage($lang, 'ai_grammar', 'no_source_provided') ?? 'Please type or paste text above first.';
	} catch (\Throwable $e) {
		\Log::error('templates/grammar_ai — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp

{{ Form::open(['url' => '#', 'method' => 'post', 'id' => $formId]) }}
@csrf
<div class="{{ VC::RW }}">
    <div class="{{ VC::C12 }} {{ VC::MB3 }}" id="grammar-keywords">
        <div class="{{ VC::FM_G }}">
            <textarea class="{{ VC::FM_CT }} form-control-light mt-2" id="{{ $srcId }}" rows="3" name="description"></textarea>
        </div>
    </div>
</div>
{{ Form::close() }}

<div class="response">
    <a class="{{ VC::BT_SM_PM }} float-left" href="#!" id="{{ $regenBtnId }}" data-empty-msg="{{ $noSrcMsg }}" data-sv-localized="true">{{ $regenLabel }}</a>
    <a href="#!" id="{{ $copyBtnId }}" class="{{ VC::BT_SM_PM }} float-end"><i class="{{ VC::TI_CC_PLS }}"></i> {{ $copyLabel }}</a>
    <div class="{{ VC::FM_G }} {{ VC::MT3 }}">
        {{ Form::textarea('description', null, [
			'class' => VC::FM_CT,
			'rows' => 5,
			'placeholder' => $descPh,
			'id' => $outId,
			'data-copy-ok-msg' => $copyOkMsg,
			'data-copy-err-msg' => $copyErrMsg,
			'data-sv-localized' => 'true',
		]) }}
    </div>
</div>
<script async src="{{ asset('assets/js/routes/ai/grammar/lang/init.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/ai/grammar/regenerate.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/ai/grammar/clipboard.js') }}"></script>
<script async>
    (function() {
        const $ = window.jQuery;
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataErrGuard = "data-error-guard";
        const dataBoundInit = "data-bound-grammar-init";
        const dataBoundRegen = "data-bound-grammar-regen";
        const qs = (s, r = document) => r.querySelector(s);
        const ensureToastContainer = () => {
            let c = qs("#np-toast-container");
            if (c) {
                return c;
            }
            c = document.createElement("div");
            c.id = "np-toast-container";
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            document.body.appendChild(c);
            return c;
        };
        const showErrorNow = message => {
            const hasBootstrap =
                (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                    qs('link[href*="bootstrap"]')) &&
                window.bootstrap &&
                window.bootstrap.Toast;
            if (hasBootstrap) {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                    t = document.createElement("div");
                    t.id = "np-toast";
                    t.className = "toast";
                    t.setAttribute("role", "alert");
                    t.setAttribute("aria-live", "assertive");
                    t.setAttribute("aria-atomic", "true");
                    t.innerHTML =
                        '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                    container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) {
                    body.textContent = message ?? errFb;
                }
                try {
                    new window.bootstrap.Toast(t, {
                        autohide: true,
                        delay: 4000
                    }).show();
                } catch (_) {
                    alert(message ?? errFb);
                }
            } else {
                alert(message ?? errFb);
            }
        };
        const scheduleInteractiveError = message => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") {
                return;
            }
            host.setAttribute(dataErrGuard, "true");
            const once = () => {
                try {
                    showErrorNow(message);
                } finally {
                    host.removeAttribute(dataErrGuard);
                }
            };
            document.addEventListener("pointerup", once, {
                once: true
            });
            const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    document.removeEventListener("pointerup", once);
                    o.disconnect();
                }
            });
            mo.observe(document.documentElement, {
                childList: true,
                subtree: true
            });
        };
        const getMsg = (el, key) => {
            let msg = errFb;
            if (
                el?.getAttribute?.(dataSvLocalized) === "true" ||
                el?.getAttribute?.(dataClientLocalized) === "true"
            ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang = (
                        window.sessionStorage.getItem("erp-np-lang") ||
                        document.documentElement.lang ||
                        "en"
                    )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute?.(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
                if (el && msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
            }
            return msg;
        };
        const resolveRoute = (el, explicit) => {
            const url = el?.getAttribute?.("data-url") || "";
            const href = el ?
                el.tagName === "FORM" ?
                el.getAttribute("action") || "" :
                el.getAttribute("href") || "" :
                "";
            if (
                (!explicit || explicit === "#") &&
                (!url || url === "#") &&
                (!href || href === "#")
            ) {
                return null;
            }
            return explicit && explicit !== "#" ?
                explicit :
                url && url !== "#" ?
                url :
                href;
        };
        const initGrammarSeed = () => {
            const host = document.body;
            if (!host || host.getAttribute(dataBoundInit) === "true") {
                return;
            }
            host.setAttribute(dataBoundInit, "true");
            try {
                let summernoteValue = "";
                if ($ && $(".grammer_textarea").length > 0) {
                    summernoteValue = $(".grammer_textarea").val() ?? "";
                } else {
                    if (!$ || !$.fn) {
                        try {
                            if (
                                window.location.hostname === "localhost" ||
                                window.location.hostname === "127.0.0.1"
                            ) console.error("jQuery unavailable");
                        } catch (_) {}
                        scheduleInteractiveError(getMsg(host, "plugin_unavailable"));
                        return;
                    }
                    if ($.fn.summernote && $(".summernote-simple").length > 0) {
                        try {
                            $(".summernote-simple").summernote();
                            summernoteValue = $(".summernote-simple").summernote("code") ?? "";
                        } catch (_) {
                            summernoteValue = $(".summernote-simple").val() ?? "";
                        }
                    } else {
                        scheduleInteractiveError(getMsg(host, "plugin_unavailable"));
                    }
                }
                summernoteValue = String(summernoteValue).replace(/<(.|\n)*?>/g, "");
                const desc = $("#description");
                if (desc && desc.length) {
                    desc.text(summernoteValue ?? "");
                } else {
                    scheduleInteractiveError(getMsg(host, "grammar_init_unavailable"));
                }
            } catch (_) {
                scheduleInteractiveError(
                    getMsg(document.body, "grammar_init_unavailable")
                );
            }
            const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    o.disconnect();
                }
            });
            mo.observe(document.body, {
                childList: true,
                subtree: true
            });
        };
        const bindRegenerate = () => {
            const btn = qs("#regenerate");
            if (!btn) {
                return;
            }
            if (btn.getAttribute(dataBoundRegen) === "true") {
                return;
            }
            btn.setAttribute(dataBoundRegen, "true");
            $(document.body).on("click.grammarRegen", "#regenerate", function() {
                try {
                    const form = $("#myGrammarForm");
                    const formEl = form.get(0);
                    const explicit = "{{ route('grammar.response') }}";
                    const endpoint = resolveRoute(formEl, explicit);
                    if (!endpoint) {
                        scheduleInteractiveError(
                            getMsg(formEl || document.body, "generate_unavailable")
                        );
                        return;
                    }
                    $.ajax({
                        type: "post",
                        url: endpoint,
                        dataType: "json",
                        data: form.serialize(),
                        cache: false,
                        beforeSend: function() {
                            try {
                                $("#regenerate").empty();
                                $("#regenerate").append(
                                    '<span class="spinner-grow spinner-grow-sm" role="status"></span>'
                                );
                            } catch (_) {}
                        },
                        success: function(data) {
                            try {
                                $(".response").removeClass("d-none");
                                $("#regenerate").text("Re-Generate");
                                if (data && data.message) {
                                    if (window.show_toastr) {
                                        window.show_toastr("error", data.message, "error");
                                    }
                                    $("#commonModalOver").modal("hide");
                                } else {
                                    $("#ai-description").val(data ?? "");
                                }
                            } catch (_) {
                                scheduleInteractiveError(
                                    getMsg(document.body, "generate_unavailable")
                                );
                            }
                        },
                        error: function() {
                            scheduleInteractiveError(getMsg(document.body, "ajax_unavailable"));
                        },
                    });
                } catch (_) {
                    scheduleInteractiveError(getMsg(document.body, "generate_unavailable"));
                }
            });
            const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(btn)) {
                    $(document.body).off(".grammarRegen");
                    o.disconnect();
                }
            });
            mo.observe(document.body, {
                childList: true,
                subtree: true
            });
        };
        const exposeCopy = () => {
            if (!window.copyGrammerText) {
                window.copyGrammerText = function() {
                    try {
                        const copied = $("#ai-description").val() ?? "";
                        if ($ && $(".grammer_textarea").length > 0) {
                            $(".grammer_textarea").val(copied ?? "");
                        } else {
                            if (
                                $ &&
                                $.fn &&
                                $.fn.summernote &&
                                $(".summernote-simple").length > 0
                            ) {
                                try {
                                    $(".summernote-simple").summernote("code", copied ?? "");
                                } catch (_) {
                                    $(".summernote-simple").val(copied ?? "");
                                }
                            } else {
                                scheduleInteractiveError(
                                    getMsg(document.body, "plugin_unavailable")
                                );
                            }
                        }
                        if (window.show_toastr) {
                            window.show_toastr(
                                "success",
                                "Result text has been copied successfully",
                                "success"
                            );
                        }
                        $("#commonModalOver").modal("hide");
                    } catch (_) {
                        scheduleInteractiveError(
                            getMsg(document.body, "grammar_init_unavailable")
                        );
                    }
                };
            }
        };
        const init = () => {
            if (!$ || !$.fn) {
                try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
                } catch (_) {}
                scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
                return;
            }
            initGrammarSeed();
            bindRegenerate();
            exposeCopy();
        };
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", init, {
                once: true
            });
        } else {
            init();
        }
    })();
</script>
