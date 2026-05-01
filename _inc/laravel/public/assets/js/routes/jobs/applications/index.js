(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const Q = (s) => document.querySelector(s), 
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    QA = (s) => Array.from(document.querySelectorAll(s));
    const T = (m) => {
        const t = typeof m === "string"
            ? m
            : "Requested route is unavailable. Please contact technical support or your domain administrator.", hasBs = !!(document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
            window.bootstrap);
        let box = document.getElementById("toast-container");
        if (!box) {
            box = document.createElement("div");
            box.id = "toast-container";
            document.body.appendChild(box);
        }
        if (hasBs) {
            const el = document.createElement("div");
            el.className = "toast";
            for (const [k, v] of Object.entries({
                role: "alert",
                "aria-live": "assertive",
                "aria-atomic": "true",
            }))
                el.setAttribute(k, v);
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = t;
            el.appendChild(b);
            box.appendChild(el);
            bootstrap.Toast.getOrCreateInstance(el).show();
        }
        else {
            alert(t);
        }
    };
    const bindLink = (a) => {
        if (!a || a.getAttribute("data-listener-active") === "true")
            return;
        a.setAttribute("data-listener-active", "true");
        a.addEventListener("click", (e) => {
            const href = (a.getAttribute("href") ?? "#").trim(), url = (a.getAttribute("data-url") ?? href ?? "#").trim();
            if (url !== "#" && href !== "#")
                return;
            e.preventDefault();
            T(a.getAttribute("data-guard-msg") ?? "");
        });
    };
    const bindForm = (f) => {
        if (!f || f.getAttribute("data-submit-guarded") === "true")
            return;
        f.setAttribute("data-submit-guarded", "true");
        f.addEventListener("submit", (e) => {
            const action = (f.getAttribute("action") ?? "#").trim(), url = (f.getAttribute("data-url") ?? action ?? "#").trim();
            if (url !== "#" && action !== "#")
                return;
            e.preventDefault();
            T(f.getAttribute("data-guard-msg") ?? "");
        });
    };
    const tips = () => {
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
    const updateCounts = () => {
        QA(".kanban-box").forEach(box => {
            const cnt = box.querySelectorAll("> .card").length, header = box.closest(".card")?.querySelector(".card-header .count");
            if (header)
                header.textContent = String(cnt);
        });
    };
    const initDragula = () => {
        const wrap = Q(".kanban-wrapper");
        if (!wrap || typeof dragula !== "function")
            return;
        let ids = [];
        try {
            ids = JSON.parse(wrap.getAttribute("data-containers") ?? "[]");
        }
        catch (_) {
            ids = [];
        }
        const containers = ids
            .map((id) => document.getElementById(id))
            .filter((el) => el !== null);
        if (containers.length === 0)
            return;
        dragula(containers).on("drop", () => {
            updateCounts();
        });
    };
    document.addEventListener("DOMContentLoaded", () => {
        QA("a[data-guard-msg],a[data-url]").forEach(bindLink);
        QA("form[data-guard-msg],form[data-url]").forEach(bindForm);
        tips();
        updateCounts();
        initDragula();
    });
})();
})();