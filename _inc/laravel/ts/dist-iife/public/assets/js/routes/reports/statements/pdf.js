(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/statements/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
    const $ = window.jQuery;
    const qs = (s, r = document) => r.querySelector(s);
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-error-guard", dataFilterGuard = "data-filter-guard", dataNavGuard = "data-nav-guard";
    const ensureToastContainer = () => {
        const id = "np-toast-container", existing = qs("#" + id);
        if (existing)
            return existing;
        const c = document.createElement("div");
        c.id = id;
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')) &&
            window.bootstrap.Toast;
        if (hasBootstrap) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    t.setAttribute(k, v);
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = qs(".toast-body", t);
            if (body)
                body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, {
                    autohide: true,
                    delay: 4000,
                }).show();
            }
            catch (_) {
                alert(message ?? errFb);
            }
        }
        else {
            alert(message ?? errFb);
        }
    };
    const scheduleInteractiveError = (message) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                host.removeAttribute(dataErrGuard);
            }
        };
        document.addEventListener("click", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("click", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const getMsg = (el, key) => {
        let msg = errFb;
        if (el.getAttribute(dataSvLocalized) === "true" ||
            el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const saveAsPDF = () => {
        const area = document.getElementById("printableArea");
        if (!area) {
            scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
            return;
        }
        const name = String($("#filename").val() ?? "").trim(), opt = {
            margin: 0.3,
            filename: name,
            image: { type: "jpeg", quality: 1 },
            html2canvas: { scale: 4, dpi: 72, letterRendering: true },
            jsPDF: { unit: "in", format: "A2" },
        };
        try {
            if (typeof window.html2pdf !== "function") {
                try {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("html2pdf unavailable");
                }
                catch (_) {
                    console.error(`[pdf] Error:`, _);
                }
                scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
                return;
            }
            // eslint-disable-next-line @typescript-eslint/no-unsafe-call
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
            window.html2pdf().set(opt).from(area).save();
        }
        catch (_) {
            scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
        }
    };
    const bindSave = () => {
        window.saveAsPDF = saveAsPDF;
    };
    const bindFilterToggle = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[pdf] Error:`, _);
            }
            scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
            return;
        }
        const btn = document.getElementById("filter"), panel = document.getElementById("show_filter");
        if (!btn || btn.getAttribute(dataFilterGuard) === "true")
            return;
        btn.setAttribute(dataFilterGuard, "true");
        const handler = function () {
            try {
                if (panel) {
                    $("#show_filter").toggle();
                }
                else {
                    scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
                }
            }
            catch (_) {
                scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
            }
        };
        $(btn).on("click", handler);
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(btn)) {
                $(btn).off("click", handler);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const syncDates = () => {
        if (!$)
            return;
        try {
            const s = String($(".startDate").val() ?? ""), e = String($(".endDate").val() ?? "");
            $(".start_date").val(s);
            $(".end_date").val(e);
        }
        catch (_) {
            scheduleInteractiveError(getMsg(document.body, "date_sync_unavailable"));
        }
    };
    const initReportTab = () => {
        if (!$)
            return;
        const setReport = (href) => {
            if (!href) {
                scheduleInteractiveError(getMsg(document.body, "report_unavailable"));
                return;
            }
            $(".report").val(href);
        };
        const initial = $(".nav-item .active").attr("href") ??
            $("ul.nav-pills > li > a.active").attr("href") ??
            "";
        if (initial !== "")
            setReport(initial);
        document
            .querySelectorAll("ul.nav-pills > li > a")
            .forEach((el) => {
            if (el.getAttribute(dataNavGuard) === "true")
                return;
            el.setAttribute(dataNavGuard, "true");
            const h = function () {
                setReport($(this).attr("href"));
            };
            $(el).on("click", h);
            const mo = new MutationObserver((_m, o) => {
                if (!document.body.contains(el)) {
                    $(el).off("click", h);
                    o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        });
    };
    const initDataTable = () => {
        if (!$)
            return;
        const table = document.getElementById("report-dataTable");
        if (!table)
            return;
        if (!$.fn.DataTable) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("DataTables unavailable");
            }
            catch (_) {
                console.error(`[pdf] Error:`, _);
            }
            scheduleInteractiveError(getMsg(table, "plugin_unavailable"));
            return;
        }
        if ($.fn.DataTable.isDataTable(table))
            return;
        try {
            const name = String($("#filename").val() ?? "").trim();
            $(table).DataTable({
                dom: "lBfrtip",
                buttons: [
                    { extend: "excel", title: name },
                    { extend: "pdf", title: name },
                    { extend: "csv", title: name },
                ],
            });
        }
        catch (_) {
            scheduleInteractiveError(getMsg(table, "plugin_unavailable"));
        }
    };
    const init = () => {
        bindSave();
        bindFilterToggle();
        syncDates();
        initReportTab();
        initDataTable();
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();