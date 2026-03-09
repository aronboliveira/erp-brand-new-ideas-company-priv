(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const QA = (s) => Array.from(document.querySelectorAll(s));
    // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
    const T = window.JOBS_I18N || {}, DEFAULT_ROUTE_MSG = T.routeUnavailable ??
        "Requested route is unavailable. Please contact technical support or your domain administrator.", COPIED = T.copySuccess ?? "Link copied to clipboard", COPY_FAIL = T.copyFail ?? "Failed to copy link";
    const toast = (message) => {
        const text = message || DEFAULT_ROUTE_MSG, hasBs = !!(document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
            window.bootstrap);
        let box = document.getElementById("toast-container");
        if (!box) {
            box = document.createElement("div");
            box.id = "toast-container";
            Object.assign(box.style, {
                position: "fixed",
                top: "1rem",
                right: "1rem",
                zIndex: "1060",
            });
            document.body.appendChild(box);
        }
        if (hasBs) {
            const t = document.createElement("div");
            t.className = "toast";
            for (const [k, v] of Object.entries({
                role: "alert",
                "aria-live": "assertive",
                "aria-atomic": "true",
            }))
                t.setAttribute(k, v);
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = text;
            t.appendChild(b);
            box.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
        }
        else {
            alert(text);
        }
    };
    const bindLinkGuard = (el) => {
        if (!el || el.getAttribute("data-listener-active") === "true")
            return;
        el.setAttribute("data-listener-active", "true");
        if (!el.getAttribute("data-listener-bound-click")) {
            el.setAttribute("data-listener-bound-click", "1");
            el.addEventListener("click", (e) => {
                const href = (el.getAttribute("href") ?? "#").trim(), url = (el.getAttribute("data-url") ?? href ?? "#").trim();
                if (url !== "#" && href !== "#")
                    return;
                e.preventDefault();
                toast(el.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
                el.setAttribute("data-failed-route", "true");
            });
        }
    };
    const bindFormGuard = (fm) => {
        if (!fm || fm.getAttribute("data-submit-guarded") === "true")
            return;
        fm.setAttribute("data-submit-guarded", "true");
        if (!fm.getAttribute("data-listener-bound-submit")) {
            fm.setAttribute("data-listener-bound-submit", "1");
            fm.addEventListener("submit", (e) => {
                const action = (fm.getAttribute("action") ?? "#").trim(), url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
                if (url !== "#" && action !== "#")
                    return;
                e.preventDefault();
                toast(fm.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
                fm.setAttribute("data-failed-route", "true");
            });
        }
    };
    const initTooltips = () => {
        try {
            QA('[data-bs-toggle="tooltip"]').forEach((el) => {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (_) {
                    console.error(`[index] Error:`, _);
                }
            });
        }
        catch (_) {
            console.error(`[index] Error:`, _);
        }
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const copyToClipboard = (text) => navigator.clipboard
        ? navigator.clipboard.writeText(text)
        : Promise.reject();
    const bindCopy = () => {
        QA("a.copy-link").forEach(a => {
            if (a.getAttribute("data-copy-bound") === "true")
                return;
            a.setAttribute("data-copy-bound", "true");
            a.addEventListener("click", (e) => {
                const href = (a.getAttribute("href") ?? "#").trim(), url = (a.getAttribute("data-url") ?? href ?? "#").trim();
                if (url === "#" || href === "#") {
                    e.preventDefault();
                    toast(a.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
                    return;
                }
                e.preventDefault();
                copyToClipboard(url)
                    .then(() => {
                    toast(COPIED);
                })
                    .catch(() => {
                    toast(COPY_FAIL);
                });
            });
        });
    };
    document.addEventListener("DOMContentLoaded", () => {
        QA("a.route-guard, a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
        QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
        bindCopy();
        initTooltips();
    });
})();
})();