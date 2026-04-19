/**
 * Shared utility functions for journal entry repeater functionality.
 * Reduces code duplication between create and edit blade templates.
 * Mirror of public/assets/js/routes/journalEntries/shared/repeater-utils.js
 */

const _emitted = {};
const devError = (tag, err) => {
    if (location.hostname !== "localhost" && location.hostname !== "127.0.0.1")
        return;
    const msg = err instanceof Error ? err.message : String(err);
    const key = `${tag}:${msg}`;
    if (_emitted[key])
        return;
    _emitted[key] = true;
    console.error(`[${tag}]`, msg);
};
const JournalEntryRepeater = (() => {
    "use strict";
    const guard = window.ERPGuard, utils = window.ERPUtils;
    const scheduleError = guard?.scheduleError?.bind(guard), getMsgUtil = utils?.getMsg?.bind(utils);
    if (typeof scheduleError !== "function" || typeof getMsgUtil !== "function")
        return {};
    const DATA_LISTENER_ADDED = "data-listener-added", ERR_FB = "# ERROR", DATA_CLIENT_LOCALIZED = "data-client-localized", DATA_GUARD_MSG = "data-guard-msg";
    const getLocalizedMessage = (el, key) => {
        return getMsgUtil(key) || el?.getAttribute(DATA_GUARD_MSG) || ERR_FB;
    };
    const handleErrorDisplay = (el, key) => {
        const message = el ? getLocalizedMessage(el, key) : ERR_FB;
        scheduleError(message, "pointerup");
    };
    const recalculateTotals = () => {
        try {
            let totalD = 0, totalC = 0;
            $(".debit").each((_, i) => {
                totalD += parseFloat($(i).val()) || 0;
            });
            $(".credit").each((_, i) => {
                totalC += parseFloat($(i).val()) || 0;
            });
            $(".totalDebit").html(totalD.toFixed(2));
            $(".totalCredit").html(totalC.toFixed(2));
        }
        catch {
            handleErrorDisplay(document.body, "calc_unavailable");
        }
    };
    const setupDebitCreditHandlers = () => {
        $(document).on("keyup", ".debit", function () {
            try {
                const $row = $(this).closest("tr");
                $row.find(".credit").val("").prop("disabled", true);
                if (!$(this).val())
                    $row.find(".credit").prop("disabled", false);
                $row.find(".amount").html($(this).val());
                recalculateTotals();
            }
            catch {
                handleErrorDisplay(this, "calc_unavailable");
            }
        });
        $(document).on("keyup", ".credit", function () {
            try {
                const $row = $(this).closest("tr");
                $row.find(".debit").val("").prop("disabled", true);
                if (!$(this).val())
                    $row.find(".debit").prop("disabled", false);
                $row.find(".amount").html($(this).val());
                recalculateTotals();
            }
            catch {
                handleErrorDisplay(this, "calc_unavailable");
            }
        });
    };
    const initRepeater = (options = {}) => {
        const { selector = "body", maxUploadSize = "2048", destroyRoute = null, isEditMode = false } = options;
        if (typeof $ === "undefined") {
            devError("JournalEntryRepeater", new Error("jQuery unavailable"));
            return;
        }
        if (!$(selector + " .repeater").length)
            return;
        let $repeater;
        try {
            $repeater = $(selector + " .repeater").repeater({
                initEmpty: false,
                defaultValues: { status: 1 },
                show() {
                    try {
                        $(this).slideDown();
                        const $multi = $(this).find("input.multi");
                        if ($multi.length) {
                            $multi.MultiFile({
                                max: 3,
                                accept: "png|jpg|jpeg",
                                max_size: maxUploadSize,
                            });
                        }
                        if (typeof JsSearchBox === "function")
                            JsSearchBox();
                        if ($(".select2").length)
                            $(".select2").select2();
                    }
                    catch {
                        const el = this;
                        if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                            el.addEventListener("click", () => handleErrorDisplay(el, "repeater_show_unavailable"));
                            el.setAttribute(DATA_LISTENER_ADDED, "true");
                        }
                    }
                },
                hide(deleteElement) {
                    try {
                        if (confirm("Are you sure you want to delete this element?")) {
                            const $row = $(this);
                            $row.slideUp(deleteElement);
                            $row.remove();
                            recalculateTotals();
                            if (isEditMode && destroyRoute) {
                                const id = $row.find(".id").val();
                                $.ajax({
                                    url: destroyRoute,
                                    type: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": document.getElementById("token")?.value,
                                    },
                                    data: { id },
                                    cache: false,
                                    success: () => { },
                                    error: () => handleErrorDisplay($row[0], "destroy_unavailable"),
                                });
                            }
                        }
                    }
                    catch {
                        const el = this;
                        if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                            el.addEventListener("click", () => handleErrorDisplay(el, "repeater_hide_unavailable"));
                            el.setAttribute(DATA_LISTENER_ADDED, "true");
                        }
                    }
                },
                ready: () => { },
                isFirstItemUndeletable: true,
            });
            const val = $(selector + " .repeater").attr("data-value") ?? "";
            if (val) {
                try {
                    const list = JSON.parse(val);
                    $repeater.setList(list);
                    if (isEditMode) {
                        list.forEach((item, i) => {
                            if ((item.credit ?? 0) > 0) {
                                $(`input[name="accounts[${i}][credit]"]`).trigger("keyup");
                            }
                            if ((item.debit ?? 0) > 0) {
                                $(`input[name="accounts[${i}][debit]"]`).trigger("keyup");
                            }
                        });
                    }
                    else {
                        list.forEach((item) => {
                            const $row = $(`#sortable-table .id[value="${String(item.id ?? "")}"]`).parent();
                            $row.find(".item").val(item.product_id);
                            if (typeof changeItem === "function")
                                changeItem($row.find(".item"));
                        });
                    }
                }
                catch {
                    handleErrorDisplay(document.querySelector(".repeater"), "repeater_show_unavailable");
                }
            }
        }
        catch {
            handleErrorDisplay(document.querySelector(".repeater"), "repeater_show_unavailable");
        }
        setupDebitCreditHandlers();
    };
    return {
        getLocalizedMessage,
        handleErrorDisplay,
        recalculateTotals,
        setupDebitCreditHandlers,
        initRepeater,
        DATA_LISTENER_ADDED,
        ERR_FB,
        DATA_CLIENT_LOCALIZED,
        DATA_GUARD_MSG,
    };
})();
window.JournalEntryRepeater = JournalEntryRepeater;
export { JournalEntryRepeater };
//# sourceMappingURL=repeater-utils.js.map