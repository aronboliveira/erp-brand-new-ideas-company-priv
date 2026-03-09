(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/loans/options/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    const DEFAULT_MSG = "Requested route is unavailable. Please contact technical support or your domain administrator.";
    const toast = (message) => {
        const text = message || DEFAULT_MSG, hasBs = !!document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
            !!window.bootstrap;
        let box = document.getElementById("toast-container");
        if (!box) {
            box = document.createElement("div");
            box.id = "toast-container";
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
                try {
                    const href = (el.getAttribute("href") ?? "#").trim(), url = (el.getAttribute("data-url") ?? href ?? "#").trim();
                    if (url !== "#" && href !== "#")
                        return;
                    e.preventDefault();
                    toast(el.getAttribute("data-guard-msg") || DEFAULT_MSG);
                    el.setAttribute("data-failed-route", "true");
                }
                catch (_) {
                    console.error(`[index] Error:`, _);
                }
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
                try {
                    const action = (fm.getAttribute("action") ?? "#").trim(), url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
                    if (url !== "#" && action !== "#")
                        return;
                    e.preventDefault();
                    toast(fm.getAttribute("data-guard-msg") || DEFAULT_MSG);
                    fm.setAttribute("data-failed-route", "true");
                }
                catch (_) {
                    console.error(`[index] Error:`, _);
                }
            });
        }
    };
    document
        .querySelectorAll("a[data-guard-msg], a[data-url]")
        .forEach(el => bindLinkGuard(el));
    document
        .querySelectorAll("form[data-guard-msg], form[data-url]")
        .forEach(bindFormGuard);
    try {
        document
            .querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach((el) => {
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
})();
})();