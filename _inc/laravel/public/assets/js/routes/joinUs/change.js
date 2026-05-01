/**
 * @fileoverview TypeScript version of public/assets/js/routes/joinUs/change.js
 * @generated from original JavaScript - manual review recommended
 * @module change
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
    const lang = (document.documentElement.getAttribute("lang") ?? "en").toLowerCase();
    const dict = (window.translations &&
        (window.translations[lang] || window.translations[lang.split("-")[0]])) ||
        window.translations?.en ||
        {};
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const tr = (k) => dict[k] || k;
    const showToastOrAlert = (msg) => {
        try {
            const hasBootstrapToast = !!window.bootstrap.Toast;
            if (hasBootstrapToast) {
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    Object.assign(container.style, {
                        position: "fixed",
                        top: "1rem",
                        right: "1rem",
                        zIndex: "1080",
                    });
                    document.body.appendChild(container);
                }
                const toastEl = document.createElement("div");
                toastEl.className = "toast align-items-center text-bg-danger border-0";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toastEl.setAttribute(k, v);
                toastEl.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
                container.appendChild(toastEl);
                const toast = new window.bootstrap.Toast(toastEl, { delay: 3500 });
                toast.show();
                setTimeout(() => {
                    toastEl.remove();
                }, 4000);
            }
            else {
                alert(msg);
            }
        }
        catch {
            alert(msg);
        }
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const ensure = (selector) => {
        const el = document.querySelector(selector);
        if (!el)
            throw new Error(`${tr("element_unavailable")} (${selector})`);
        return el;
    };
    const init = () => {
        if (!window.jQuery)
            throw new Error(tr("jquery_unavailable"));
        const $ = window.jQuery;
        const $radios = $("input[name='client_check']");
        const $existWrap = $(".exist_client");
        const $newWrap = $(".new_client");
        const $name = $("#client_name");
        const $email = $("#client_email");
        const $password = $("#client_password");
        const safeToggle = (mode) => {
            const exist = mode === "exist";
            if ($existWrap.length)
                $existWrap.toggleClass("d-none", !exist);
            if ($newWrap.length)
                $newWrap.toggleClass("d-none", exist);
            if ($name.length)
                exist
                    ? $name.removeAttr("required")
                    : $name.attr("required", "required");
            if ($email.length)
                exist
                    ? $email.removeAttr("required")
                    : $email.attr("required", "required");
            if ($password.length)
                exist
                    ? $password.removeAttr("required")
                    : $password.attr("required", "required");
        };
        const current = String($radios.filter(":checked").val() ?? "new").toLowerCase();
        safeToggle(current);
        $(document).on("click", "input[name='client_check']", function () {
            try {
                // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                const mode = String($(this).val() ?? "new").toLowerCase();
                safeToggle(mode);
            }
            catch (e) {
                showToastOrAlert((e instanceof Error ? e.message : null) || tr("request_failed"));
            }
        });
    };
    const start = () => {
        try {
            ensure("body");
            init();
        }
        catch (e) {
            showToastOrAlert((e instanceof Error ? e.message : null) || tr("init_failed"));
        }
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", start, { once: true })
        : start();
})();
//# sourceMappingURL=change.js.map