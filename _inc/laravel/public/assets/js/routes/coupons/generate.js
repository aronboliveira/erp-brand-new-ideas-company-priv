(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/coupons/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */
(() => {
    const aiBtn = document.getElementById("coupon-generate-ai-btn");
    if (aiBtn && aiBtn.getAttribute("data-listener-active") !== "true") {
        aiBtn.setAttribute("data-listener-active", "true");
        if (!aiBtn.getAttribute("data-listener-bound-click")) {
            aiBtn.setAttribute("data-listener-bound-click", "1");
            aiBtn.addEventListener("click", event => {
                try {
                    const url = aiBtn.getAttribute("data-url");
                    if (!url || url === "#") {
                        event.preventDefault();
                        const msg = aiBtn.getAttribute("data-guard-msg") ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container = document.getElementById("toast-container");
                        if (!container) {
                            container = document.createElement("div");
                            container.id = "toast-container";
                            container.className =
                                "toast-container position-fixed top-0 end-0 p-3";
                            container.style.zIndex = "1080";
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement("div");
                            toastEl.className = "toast";
                            for (const [k, v] of Object.entries({
                                role: "alert",
                                "aria-live": "assertive",
                                "aria-atomic": "true",
                            }))
                                toastEl.setAttribute(k, v);
                            const body = document.createElement("div");
                            body.className = "toast-body";
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        }
                        else {
                            alert(msg);
                        }
                        aiBtn.setAttribute("data-failed-route", "true");
                        return;
                    }
                }
                catch (e) {
                    console.error(`[generate] Error:`, e);
                }
            });
        }
    }
    const manualRad = document.getElementById("manual_code"), autoRad = document.getElementById("auto_code"), manualDiv = document.getElementById("manual"), autoDiv = document.getElementById("auto"), generateBtn = document.getElementById("code-generate");
    if (manualRad && autoRad && manualDiv && autoDiv) {
        const toggle = () => {
            if (manualRad.checked) {
                manualDiv.classList.remove("d-none");
                autoDiv.classList.add("d-none");
            }
            else {
                autoDiv.classList.remove("d-none");
                manualDiv.classList.add("d-none");
            }
        };
        if (!manualRad.getAttribute("data-listener-bound-change")) {
            manualRad.setAttribute("data-listener-bound-change", "1");
            manualRad.addEventListener("change", toggle);
        }
        if (!autoRad.getAttribute("data-listener-bound-change")) {
            autoRad.setAttribute("data-listener-bound-change", "1");
            autoRad.addEventListener("change", toggle);
        }
        toggle();
    }
    if (generateBtn &&
        generateBtn.getAttribute("data-listener-active") !== "true") {
        generateBtn.setAttribute("data-listener-active", "true");
        if (!generateBtn.getAttribute("data-listener-bound-click")) {
            generateBtn.setAttribute("data-listener-bound-click", "1");
            generateBtn.addEventListener("click", event => {
                try {
                    event.preventDefault();
                    const input = document.getElementById("auto-code");
                    if (!input)
                        return;
                    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                    let code = "";
                    for (let i = 0; i < 8; i++) {
                        code += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    input.value = code;
                }
                catch (e) {
                    console.error(`[generate] Error:`, e);
                }
            });
        }
    }
})();
})();