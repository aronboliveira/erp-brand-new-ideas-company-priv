/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/select.js
 * @generated from original JavaScript - manual review recommended
 * @module select
 */
(() => {
    const select = document.getElementById("vendor_select");
    if (!select || select.getAttribute("data-listener-active") === "true")
        return;
    select.setAttribute("data-listener-active", "true");
    const urlAttr = "data-url", guardMsgAttr = "data-guard-msg", failedAttr = "data-failed-route";
    if (!select.getAttribute("data-listener-bound-change")) {
        select.setAttribute("data-listener-bound-change", "1");
        select.addEventListener("change", async () => {
            try {
                const url = select.getAttribute(urlAttr);
                if (!url || url === "#") {
                    const msg = select.getAttribute(guardMsgAttr), bootstrapLink = document.querySelector('link[href*="bootstrap"]');
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
                    select.setAttribute(failedAttr, "true");
                    return;
                }
                const vendorId = select.value ?? "", response = await fetch(`${url}?vendor_id=${encodeURIComponent(vendorId)}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                if (!response.ok)
                    throw new Error(`Network error: ${response.status}`);
                const data = await response.json();
                document
                    .querySelectorAll("[data-vendor-field]")
                    .forEach((el) => {
                    const key = el.getAttribute("data-vendor-field") ?? "";
                    const val = data[key] ?? "";
                    if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
                        el.value = val;
                    }
                    else {
                        el.textContent = val;
                    }
                });
            }
            catch (e) {
                console.error(`[select] Error:`, e);
            }
        });
    }
})();
//# sourceMappingURL=select.js.map