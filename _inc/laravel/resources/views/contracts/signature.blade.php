@php
    use App\Config\Constants\{
        PermissionsConstants,
        UsersConstants,
        ViewsConstants as VW,
    };
    use Illuminate\Support\Facades\Auth;
    $user = Auth::user();
@endphp
@if(!empty($contract) && isset($contract->id))
    <form id='form_pad' method="post" enctype="multipart/form-data">
        @method('POST')
        <div class="modal-body" id="">
            <div class="row">
            @csrf
                <input type="hidden" name="contract_id" value="{{$contract->id}}">
                <div class="form-control" >
                    <canvas id="signature-pad" class="signature-pad" height=200 ></canvas>
                    <input type="hidden" @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN) name="company_signature" @elseif($user?->{UsersConstants::COL_TP} === PermissionsConstants::CL ) name="client_signature" @endif id="SignupImage1">
                </div>
                <div class="mt-1">
                <button type="button" class="btn-sm btn-danger" id="clearSig">{{__('Clear')}}</button>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary btn-light" data-bs-dismiss="modal">
            <input type="button" id="addSig" value="{{__('Sign')}}" class="btn btn-primary ms-2">
        </div>
    </form>
    <script src="{{asset('assets/js/plugins/signature_pad/signature_pad.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/contracts/lang/signature.js') }}"></script>
    <script async>
        (function () {
        const $ = window.jQuery;
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataSigBound = "data-sig-bound";
        const dataErrArmed = "data-sig-error-armed";
        const qs = (s, r = document) => r.querySelector(s);
        const hasBS = () =>
            !!(
            qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')
            ) && !!(window.bootstrap && window.bootstrap.Toast);
        const ensureToastContainer = () => {
            let c = qs("#np-toast-container");
            if (c) return c;
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
            if (hasBS()) {
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
                '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = t.querySelector(".toast-body");
            if (body) body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            } catch (_) {
                alert(message ?? errFb);
            }
            } else {
            alert(message ?? errFb);
            }
        };
        const schedulePointerupError = msg => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrArmed) === "true") return;
            host.setAttribute(dataErrArmed, "true");
            const once = () => {
            try {
                showErrorNow(msg);
            } finally {
                host.removeAttribute(dataErrArmed);
            }
            };
            document.addEventListener("pointerup", once, { once: true });
            const mo = new MutationObserver((m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
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
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        const ensureJq = () => {
            if (!$ || !$.fn) {
            try {
                console.error("jQuery unavailable");
            } catch (_) {}
            schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
            return false;
            }
            return true;
        };
        const ensureSigPad = () => {
            if (!window.SignaturePad) {
            try {
                console.error("SignaturePad unavailable");
            } catch (_) {}
            schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
            return false;
            }
            return true;
        };
        const verifyRoute = candidate => {
            const a = document.createElement("a");
            a.href = candidate ?? "";
            const url = candidate ?? "";
            const href = a.href;
            if ((!url || url === "#") && (!href || href === "#")) return false;
            return true;
        };
        const bind = () => {
            if (!ensureJq() || !ensureSigPad()) return;
            const host = document.body;
            if (host.getAttribute(dataSigBound) === "true") return;
            host.setAttribute(dataSigBound, "true");
            const canvas = qs(".signature-pad");
            const clearBtn = qs("#clearSig");
            const saveBtn = qs("#addSig");
            if (!canvas || !clearBtn || !saveBtn) {
            schedulePointerupError(
                getMsg(document.body, "signature_elements_unavailable")
            );
            return;
            }
            let sigPad;
            try {
            sigPad = new window.SignaturePad(canvas);
            } catch (_) {
            schedulePointerupError(getMsg(document.body, "signature_unavailable"));
            return;
            }
            if (clearBtn.getAttribute("data-bound") !== "true") {
            clearBtn.setAttribute("data-bound", "true");
            clearBtn.addEventListener("click", function () {
                try {
                sigPad?.clear?.();
                } catch (_) {}
            });
            }
            if (saveBtn.getAttribute("data-bound") !== "true") {
            saveBtn.setAttribute("data-bound", "true");
            saveBtn.addEventListener("click", function () {
                try {
                const data = sigPad?.toDataURL?.("image/png") ?? "";
                let form = document.querySelector("form");
                if (!form) {
                    schedulePointerupError(
                    getMsg(document.body, "signature_save_unavailable")
                    );
                    return;
                }
                let img = qs("#SignupImage1");
                if (!img) {
                    img = document.createElement("input");
                    img.type = "hidden";
                    img.id = "SignupImage1";
                    img.name = "signature_image";
                    form.appendChild(img);
                }
                img.value = data;
                const url = '{{route(VW::CTC.".signature.store")}}';
                if (!verifyRoute(url)) {
                    schedulePointerupError(getMsg(document.body, "route_unavailable"));
                    return;
                }
                $.ajax({
                    url: url,
                    type: "POST",
                    data: $(form).serialize(),
                    success: function (resp) {
                    try {
                        if (typeof window.toastrs === "function") {
                        window.toastrs(
                            "success",
                            resp?.message ?? "success",
                            "success"
                        );
                        }
                    } catch (_) {}
                    try {
                        const m = document.getElementById("exampleModal");
                        if (m && typeof $(m).modal === "function") {
                        $(m).modal("hide");
                        }
                    } catch (_) {}
                    try {
                        window.location &&
                        window.location.reload &&
                        window.location.reload();
                    } catch (_) {}
                    },
                    error: function () {
                    schedulePointerupError(
                        getMsg(document.body, "signature_save_unavailable")
                    );
                    },
                });
                } catch (_) {
                schedulePointerupError(
                    getMsg(document.body, "signature_save_unavailable")
                );
                }
            });
            }
            const mo = new MutationObserver(function () {
            if (
                !document.body.contains(canvas) ||
                !document.body.contains(clearBtn) ||
                !document.body.contains(saveBtn)
            ) {
                try {
                clearBtn?.removeEventListener?.("click", () => {});
                } catch (_) {}
                try {
                saveBtn?.removeEventListener?.("click", () => {});
                } catch (_) {}
                host.removeAttribute(dataSigBound);
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
        };
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", bind, { once: true });
        } else {
            bind();
        }
        })();
    </script>
@else
    <div class="modal-body" id="">
        <div class="row">
            {{__('Contract data is not available')}}
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Close')}}" class="btn btn-secondary btn-light" data-bs-dismiss="modal">
    </div>
@endif