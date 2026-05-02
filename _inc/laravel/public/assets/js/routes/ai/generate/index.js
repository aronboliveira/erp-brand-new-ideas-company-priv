(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/generate/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

(() => {
    try {
        const f = document.getElementById("ai-template-form"), btn = document.getElementById("ai-generate-btn"), desc = document.getElementById("ai-description");
        if (!f || !btn || !desc)
            return;
        if (btn.getAttribute("data-listener-active") === "true")
            return;
        btn.setAttribute("data-listener-active", "true");
        const showNotice = (msg) => {
            try {
                if (!msg)
                    return;
                const bsLink = document.querySelector('link[href*="bootstrap"]');
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                if (bsLink && window.bootstrap.Toast) {
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
                    body.textContent = msg;
                    toast.appendChild(body);
                    container.appendChild(toast);
                    window.bootstrap.Toast.getOrCreateInstance(toast).show();
                }
                else {
                    alert(msg);
                }
            }
            catch (_) {
                alert(msg);
            }
        };
        if (!btn.getAttribute("data-listener-bound-click")) {
            btn.setAttribute("data-listener-bound-click", "1");
            btn.addEventListener("click", (e) => {
                try {
                    e.preventDefault();
                    const selected = f.querySelector('input.template_name[type="radio"]:checked');
                    if (!selected) {
                        const msg = btn.getAttribute("data-msg-select-template") ??
                            "Please select a template first.";
                        showNotice(msg);
                        return;
                    }
                    const templateId = selected.getAttribute("value") ?? "", templateName = selected.getAttribute("data-name") ?? "", language = (document.getElementById("language")
                        ?.value ?? "").toString(), tone = (f.querySelector('select[name="tone"]')?.value ?? "").toString(), creativity = (document.getElementById("ai_creativity")?.value ?? "").toString(), num = (document.getElementById("num_of_result")?.value ?? "").toString(), maxLen = (f.querySelector('input[name="result_length"]')?.value ?? "").toString(), payload = {
                        templateId,
                        templateName,
                        language,
                        tone,
                        creativity,
                        num,
                        maxLen,
                    }, evt = new CustomEvent("ai:generate", { detail: payload });
                    document.dispatchEvent(evt);
                    const hint = `[ready] ${templateName} • lang=${language} • tone=${tone} • creativity=${creativity} • results=${num} • maxLen=${maxLen}`;
                    if (!desc.value || desc.value.trim().length === 0)
                        desc.value = hint;
                }
                catch (err) {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("[assets/js/routes/aiTemplates/generate.js] Click handler error:",

                        err?.constructor?.name ?? "Error",

                        err?.message ?? "Unknown error");
                }
            });
        }
    }
    catch (error) {
        if (window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1")
            console.error("[assets/js/routes/aiTemplates/generate.js] Initialization error:",

            error?.constructor?.name ?? "Error",

            error?.message ?? "Unknown error");
    }
})();
})();