/**
 * modal-autoopen.ts — Companion to RedirectModalRoutes middleware.
 *
 * When the middleware redirects a direct browser hit on a modal
 * .create / .edit route back to the parent .index route it appends
 * query params: `?modal=create` or `?modal=edit&modal_id=42`
 *
 * This script reads those params on DOMContentLoaded, attempts to
 * locate the matching [data-ajax-popup="true"] trigger and clicks it.
 * Falls back to a direct AJAX fetch using the same pattern as custom.js.
 *
 * Mirror of public/assets/js/core/modal-autoopen.js
 * @module core/modal-autoopen
 */
const _emitted = {};
const devWarn = (tag, msg) => {
    if (location.hostname !== "localhost" &&
        location.hostname !== "127.0.0.1")
        return;
    const key = `${tag}:${msg}`;
    if (_emitted[key])
        return;
    _emitted[key] = true;
    console.warn(`[${tag}]`, msg);
};
(function () {
    "use strict";
    /* ── Entry point ─────────────────────────────────────────────────── */
    function autoOpenModal() {
        const params = new URLSearchParams(window.location.search);
        const action = params.get("modal");
        if (!action)
            return;
        const modalId = params.get("modal_id") || null;
        cleanUrl(params);
        const trigger = findTrigger(action, modalId);
        if (trigger) {
            trigger.click();
            return;
        }
        openDirectly(action, modalId);
    }
    /* ── Helpers ─────────────────────────────────────────────────────── */
    function cleanUrl(params) {
        if (!window.history?.replaceState)
            return;
        params.delete("modal");
        params.delete("modal_id");
        const qs = params.toString(), clean = window.location.pathname +
            (qs ? "?" + qs : "") +
            window.location.hash;
        window.history.replaceState(null, "", clean);
    }
    function findTrigger(action, id) {
        const triggers = document.querySelectorAll('[data-ajax-popup="true"][data-url]');
        for (const trigger of triggers) {
            const url = (trigger.getAttribute("data-url") || "").replace(/\/+$/, "");
            if (action === "create" && /\/create\/?$/.test(url))
                return trigger;
            if (action === "edit" && id) {
                const re = new RegExp("/" + escRe(String(id)) + "/edit/?$");
                if (re.test(url))
                    return trigger;
            }
        }
        return null;
    }
    function openDirectly(action, id) {
        if (typeof jQuery === "undefined" && typeof $ === "undefined")
            return;
        const jq = typeof $ !== "undefined" ? $ : jQuery;
        const basePath = window.location.pathname.replace(/\/+$/, "");
        let url;
        if (action === "create") {
            url = basePath + "/create";
        }
        else if (action === "edit" && id) {
            url = basePath + "/" + encodeURIComponent(id) + "/edit";
        }
        else {
            return;
        }
        const title = action === "create" ? "Create" : action === "edit" ? "Edit" : "";
        const $modal = jq("#commonModal");
        if (!$modal.length)
            return;
        $modal
            .find(".modal-dialog")
            .removeClass("modal-sm modal-md modal-lg modal-xl")
            .addClass("modal-lg");
        $modal.find(".modal-title").html(title);
        jq.ajax({
            url,
            data: { _modal_partial: 1 },
            success(html) {
                $modal.find(".body").html(html);
                $modal.modal("show");
                if (typeof window.taskCheckbox === "function")
                    window.taskCheckbox();
                if (typeof window.common_bind === "function")
                    window.common_bind("#commonModal");
                if (typeof window.commonLoader === "function")
                    window.commonLoader();
            },
            error(xhr) {
                devWarn("modal-autoopen", `Failed to load modal content from ${url}: ${xhr.status} ${xhr.statusText}`);
                if (typeof window.show_toastr === "function") {
                    const data = xhr.responseJSON ?? {};
                    window.show_toastr("error", data.error || "Could not load modal content.", "error");
                }
            },
        });
    }
    function escRe(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }
    /* ── Bootstrap ───────────────────────────────────────────────────── */
    const DELAY = 300;
    if (typeof jQuery !== "undefined") {
        jQuery(document).ready(() => {
            setTimeout(autoOpenModal, DELAY);
        });
    }
    else {
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(autoOpenModal, DELAY);
        });
    }
})();
//# sourceMappingURL=modal-autoopen.js.map