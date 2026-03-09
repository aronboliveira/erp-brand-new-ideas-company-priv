(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/chartOfAccounts/date.js
 * @generated from original JavaScript - manual review recommended
 * @module date
 */
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
    const errFb = "# ERROR", clientFlag = "data-client-localized", guardMsgKey = "data-guard-msg", langKey = "erp-np-lang";
    let errorMessage = "";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const getLocalizedMessage = (key, el) => {
        let msg = errFb;
        if (el.getAttribute(clientFlag) === "true") {
            msg = el.getAttribute(guardMsgKey) || msg;
        }
        else {
            let lang = (sessionStorage.getItem(langKey) ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                    el.getAttribute(guardMsgKey) ||
                    window.translations?.en?.[key] ||
                    msg;
            if (msg !== errFb) {
                el.setAttribute(guardMsgKey, msg);
                el.setAttribute(clientFlag, "true");
            }
        }
        return msg;
    };
    const showError = (message) => {
        try {
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "toast-container position-fixed top-0 end-0 p-3";
                container.style.zIndex = "1080";
                document.body.appendChild(container);
            }
            const bs = document.querySelector('link[href*="bootstrap"]') &&
                window.bootstrap.Toast;
            if (bs) {
                const toast = document.createElement("div");
                toast.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toast.setAttribute(k, v);
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
            }
            else {
                alert(message);
            }
        }
        catch {
            alert(message);
        }
    };
    const onErrorPointerUp = () => {
        if (errorMessage !== "") {
            showError(errorMessage);
            errorMessage = "";
        }
    };
    document.addEventListener("pointerup", onErrorPointerUp);
    new MutationObserver((muts, obs) => {
        muts.forEach(m => {
            Array.from(m.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                    document.removeEventListener("pointerup", onErrorPointerUp);
                    obs.disconnect();
                }
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
    document.addEventListener("DOMContentLoaded", () => {
        const typeEl = document.getElementById("type");
        if (!typeEl)
            return;
        if (typeEl.dataset.listenerAttached !== "true") {
            typeEl.dataset.listenerAttached = "true";
            const onTypeChange = () => {
                try {
                    const url = '{{ route("charofAccount.subType") }}';
                    if (url === "")
                        throw new Error("char_of_account_subtype_unavailable");
                    const val = typeEl.value ?? "";
                    $.ajax({
                        url,
                        type: "POST",
                        dataType: "json",
                        data: { type: val, _token: "{{ csrf_token() }}" },
                    })
                        .done((data) => {
                        const sub = document.getElementById("sub_type");
                        if (!sub)
                            return;
                        sub.innerHTML = "";
                        Object.entries(data).forEach(([k, v]) => {
                            const o = document.createElement("option");
                            o.value = k;
                            o.textContent = v;
                            sub.appendChild(o);
                        });
                    })
                        .fail(() => {
                        throw new Error("char_of_account_subtype_unavailable");
                    });
                }
                catch (e) {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
                    errorMessage = getLocalizedMessage(e.message, typeEl);
                }
            };
            if (!typeEl.getAttribute("data-listener-bound-change")) {
                typeEl.setAttribute("data-listener-bound-change", "1");
                typeEl.addEventListener("change", onTypeChange);
            }
            new MutationObserver((ms, obs) => {
                ms.forEach(m => {
                    Array.from(m.removedNodes).forEach(n => {
                        if (n === typeEl) {
                            typeEl.removeEventListener("change", onTypeChange);
                            obs.disconnect();
                        }
                    });
                });
            }).observe(document.body, { childList: true, subtree: true });
        }
        try {
            const copyDates = () => {
                const start = document.querySelector(".startDate")?.value ?? "";
                const end = document.querySelector(".endDate")?.value ?? "";
                document
                    .querySelectorAll(".start_date")
                    .forEach((el) => {
                    el.value = start;
                });
                document
                    .querySelectorAll(".end_date")
                    .forEach((el) => {
                    el.value = end;
                });
            };
            copyDates();
        }
        catch {
            errorMessage = getLocalizedMessage("date_callback_failed", document.body);
        }
    });
})();
})();