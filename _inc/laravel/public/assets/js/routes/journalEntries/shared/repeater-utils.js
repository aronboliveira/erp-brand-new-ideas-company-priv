/**
 * Shared utility functions for journal entry repeater functionality.
 * Reduces code duplication between create and edit blade templates.
 */
const JournalEntryRepeater = (() => {
  "use strict";

  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg: getMsgUtil } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsgUtil !== "function") {
    void 0;
    return {};
  }

  const DATA_LISTENER_ADDED = "data-listener-added";
  const ERR_FB = "# ERROR";
  const DATA_CLIENT_LOCALIZED = "data-client-localized";
  const DATA_GUARD_MSG = "data-guard-msg";
  const CONFIRM_DELETE_MSG = (() => {
    const l = document.documentElement?.lang || "en";
    return window.translations?.[l]?.["Are you sure you want to delete this element?"] ?? "Are you sure you want to delete this element?";
  })();

  /**
   * Get localized message from element attributes or translation object
   * @param {HTMLElement|null} el - The element to get message from
   * @param {string} key - The translation key
   * @returns {string} The localized message
   */
  const getLocalizedMessage = (el, key) => {
    return getMsgUtil(key) || el?.getAttribute(DATA_GUARD_MSG) || ERR_FB;
  };

  /**
   * Display error message using ERPGuard
   * @param {HTMLElement|null} el - The element context
   * @param {string} key - The translation key for the error message
   */
  const handleErrorDisplay = (el, key) => {
    const message = el ? getLocalizedMessage(el, key) : ERR_FB;
    scheduleError(message, "pointerup");
  };

  /**
   * Recalculate and update total debit/credit values
   */
  const recalculateTotals = () => {
    try {
      let totalD = 0,
        totalC = 0;
      $(".debit").each((_, i) => (totalD += parseFloat($(i).val()) || 0));
      $(".credit").each((_, i) => (totalC += parseFloat($(i).val()) || 0));
      $(".totalDebit").html(totalD.toFixed(2));
      $(".totalCredit").html(totalC.toFixed(2));
    } catch {
      handleErrorDisplay(document.body, "calc_unavailable");
    }
  };

  /**
   * Setup debit/credit keyup handlers
   */
  const setupDebitCreditHandlers = () => {
    $(document).on("keyup", ".debit", function () {
      try {
        const $row = $(this).closest("tr");
        $row.find(".credit").val("").prop("disabled", true);
        if (!$(this).val()) $row.find(".credit").prop("disabled", false);
        $row.find(".amount").html($(this).val());
        recalculateTotals();
      } catch {
        handleErrorDisplay(this, "calc_unavailable");
      }
    });

    $(document).on("keyup", ".credit", function () {
      try {
        const $row = $(this).closest("tr");
        $row.find(".debit").val("").prop("disabled", true);
        if (!$(this).val()) $row.find(".debit").prop("disabled", false);
        $row.find(".amount").html($(this).val());
        recalculateTotals();
      } catch {
        handleErrorDisplay(this, "calc_unavailable");
      }
    });
  };

  /**
   * Initialize repeater with common configuration
   * @param {Object} options - Configuration options
   * @param {string} options.selector - CSS selector for the repeater container
   * @param {string} [options.maxUploadSize] - Max upload size for MultiFile
   * @param {string} [options.destroyRoute] - Route for AJAX delete (edit mode only)
   * @param {boolean} [options.isEditMode] - Whether this is edit mode
   */
  const initRepeater = (options = {}) => {
    const { selector = "body", maxUploadSize = "2048", destroyRoute = null, isEditMode = false } = options;

    if (typeof $ === "undefined") {
      if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") {
        void "jQuery unavailable";
      }
      return;
    }

    if (!$(selector + " .repeater").length) return;

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
            if (typeof JsSearchBox === "function") {
              JsSearchBox();
            }
            if ($(".select2").length) {
              $(".select2").select2();
            }
          } catch {
            const el = this;
            if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
              el.addEventListener("click", () => handleErrorDisplay(el, "repeater_show_unavailable"));
              el.setAttribute(DATA_LISTENER_ADDED, "true");
            }
          }
        },
        hide(deleteElement) {
          try {
            if (confirm(CONFIRM_DELETE_MSG)) {
              const $row = $(this);
              $row.slideUp(deleteElement);
              $row.remove();
              recalculateTotals();

              // AJAX delete for edit mode
              if (isEditMode && destroyRoute) {
                const id = $row.find(".id").val();
                $.ajax({
                  url: destroyRoute,
                  type: "POST",
                  headers: { "X-CSRF-TOKEN": $("#token").val() },
                  data: { id },
                  cache: false,
                  success: () => {},
                  error: () => handleErrorDisplay($row[0], "destroy_unavailable"),
                });
              }
            }
          } catch {
            const el = this;
            if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
              el.addEventListener("click", () => handleErrorDisplay(el, "repeater_hide_unavailable"));
              el.setAttribute(DATA_LISTENER_ADDED, "true");
            }
          }
        },
        ready: () => {},
        isFirstItemUndeletable: true,
      });

      // Process initial data-value
      const val = $(selector + " .repeater").attr("data-value") ?? "";
      if (val) {
        try {
          const list = JSON.parse(val);
          $repeater.setList(list);

          if (isEditMode) {
            list.forEach((item, i) => {
              if (item.credit > 0) {
                $(`input[name="accounts[${i}][credit]"]`).trigger("keyup");
              }
              if (item.debit > 0) {
                $(`input[name="accounts[${i}][debit]"]`).trigger("keyup");
              }
            });
          } else {
            list.forEach(item => {
              const $row = $(`#sortable-table .id[value="${item.id}"]`).parent();
              $row.find(".item").val(item.product_id);
              if (typeof changeItem === "function") {
                changeItem($row.find(".item"));
              }
            });
          }
        } catch {
          handleErrorDisplay(document.querySelector(".repeater"), "repeater_show_unavailable");
        }
      }
    } catch {
      handleErrorDisplay(document.querySelector(".repeater"), "repeater_show_unavailable");
    }

    // Setup keyup handlers
    setupDebitCreditHandlers();
  };

  // Public API
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

// Export for module systems if available
if (typeof module !== "undefined" && module.exports) {
  module.exports = JournalEntryRepeater;
}
