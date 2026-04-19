/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/viewImages.js
 * @generated from original JavaScript - manual review recommended
 * @module viewImages
 */
(() => {
    try {
        const items = document.querySelectorAll(".view-images");
        if (!items || items.length === 0)
            return;
        items.forEach(img => {
            try {
                if (img.getAttribute("data-listener-active") === "true")
                    return;
                img.setAttribute("data-listener-active", "true");
                img.addEventListener("click", async (e) => {
                    try {
                        e.preventDefault();
                        const url = img.getAttribute("data-url") ?? "#";
                        if (!url || url === "#") {
                            const msg = img.getAttribute("data-guard-msg") ??
                                "View tracker images route is unavailable. Please contact technical support or your domain administrator.";
                            let container = document.getElementById("toast-container");
                            if (!container) {
                                container = document.createElement("div");
                                container.id = "toast-container";
                                container.className =
                                    "toast-container position-fixed top-0 end-0 p-3";
                                container.style.zIndex = "1080";
                                document.body.appendChild(container);
                            }
                            const bsLink = document.querySelector('link[href*="bootstrap"]');
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
                                try {
                                    window.bootstrap.Toast.getOrCreateInstance(toast).show();
                                }
                                catch {
                                    alert(msg);
                                }
                            }
                            else {
                                alert(msg);
                            }
                            img.setAttribute("data-failed-route", "true");
                            return;
                        }
                        const modal = document.getElementById("exampleModalCenter"), content = modal ? modal.querySelector(".image_sider_div") : null;
                        if (!modal || !content) {
                            alert("Could not find images modal container");
                            return;
                        }
                        try {
                            const rsp = await fetch(url, {
                                method: "GET",
                                credentials: "same-origin",
                                headers: { "X-Requested-With": "XMLHttpRequest" },
                            });
                            if (!rsp.ok)
                                throw new Error("HTTP " + rsp.status);
                            const html = await rsp.text();
                            // SECURITY: Use textContent for any user data, or use a sanitizer for arbitrary HTML
                            safeSethtmlContent(content, html);
                            if (window.bootstrap.Modal) {
                                const m = window.bootstrap.Modal.getOrCreateInstance(modal);
                                m.show();
                            }
                        }
                        catch (xhrErr) {
                            const msg = "Failed to load tracker images. Please try again later.", bsLink = document.querySelector('link[href*="bootstrap"]');
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
                                try {
                                    window.bootstrap.Toast.getOrCreateInstance(toast).show();
                                }
                                catch {
                                    alert(msg);
                                }
                            }
                            else {
                                alert(msg);
                            }
                        }
                    }
                    catch (__err) {
                        console.error(`[viewImages] Error:`, __err);
                    }
                });
            }
            catch (__err) {
                console.error(`[viewImages] Error:`, __err);
            }
        });
        // SECURITY: Safe HTML insertion helper
         
        function safeSethtmlContent(el, html) {
            try {
                // Use DOMParser to safely parse HTML, then clone nodes to prevent scripts
                const parser = new DOMParser(), doc = parser.parseFromString(html, "text/html");
                // Check for parser errors
                if (doc.body.innerHTML.includes("PARSER ERROR")) {
                    el.textContent = html;
                    return;
                }
                // Clear element and append parsed content
                while (el.firstChild) {
                    el.removeChild(el.firstChild);
                }
                // Clone nodes to create a new tree (breaks event handlers, which is safer)
                const fragment = document.createDocumentFragment();
                for (const node of doc.body.childNodes) {
                    fragment.appendChild(node.cloneNode(true));
                }
                el.appendChild(fragment);
            }
            catch (e) {
                // Fallback to textContent if parsing fails
                el.textContent = html;
            }
        }
    }
    catch (__err) {
        console.error(`[viewImages] Error:`, __err);
    }
})();
//# sourceMappingURL=viewImages.js.map