/**
 * Shared utility functions for journal entry repeater functionality.
 * Reduces code duplication between create and edit blade templates.
 * Mirror of public/assets/js/routes/journalEntries/shared/repeater-utils.js
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unsafe-argument -- jQuery ($) lacks type definitions in this project */

// eslint-disable-next-line @typescript-eslint/no-explicit-any
declare const $: any;
declare const JsSearchBox: (() => void) | undefined;
declare const changeItem: ((item: unknown) => void) | undefined;

const _emitted: Record<string, true> = {};

const devError = (tag: string, err: unknown): void => {
  if (location.hostname !== "localhost" && location.hostname !== "127.0.0.1") return;
  const msg = err instanceof Error ? err.message : String(err);
  const key = `${tag}:${msg}`;
  if (_emitted[key]) return;
  _emitted[key] = true;
  console.error(`[${tag}]`, msg);
};

interface RepeaterOptions {
  selector?: string;
  maxUploadSize?: string;
  destroyRoute?: string | null;
  isEditMode?: boolean;
}

interface JournalEntryRepeaterAPI {
  getLocalizedMessage: (el: HTMLElement | null, key: string) => string;
  handleErrorDisplay: (el: HTMLElement | null, key: string) => void;
  recalculateTotals: () => void;
  setupDebitCreditHandlers: () => void;
  initRepeater: (options?: RepeaterOptions) => void;
  DATA_LISTENER_ADDED: string;
  ERR_FB: string;
  DATA_CLIENT_LOCALIZED: string;
  DATA_GUARD_MSG: string;
}

interface JournalEntryRow {
  id?: string | number;
  credit?: number;
  debit?: number;
  product_id?: string | number;
  [key: string]: unknown;
}

declare global {
  interface Window {
    JournalEntryRepeater: JournalEntryRepeaterAPI | Record<string, never>;
  }
}

const JournalEntryRepeater: JournalEntryRepeaterAPI | Record<string, never> = ((): JournalEntryRepeaterAPI | Record<string, never> => {
  "use strict";

  const guard = window.ERPGuard,
    utils = window.ERPUtils;

  const scheduleError = guard?.scheduleError?.bind(guard),
    getMsgUtil = utils?.getMsg?.bind(utils);

  if (typeof scheduleError !== "function" || typeof getMsgUtil !== "function") return {} as Record<string, never>;

  const DATA_LISTENER_ADDED = "data-listener-added",
    ERR_FB = "# ERROR",
    DATA_CLIENT_LOCALIZED = "data-client-localized",
    DATA_GUARD_MSG = "data-guard-msg";

  const getLocalizedMessage = (el: HTMLElement | null, key: string): string => {
    return getMsgUtil(key) || el?.getAttribute(DATA_GUARD_MSG) || ERR_FB;
  };

  const handleErrorDisplay = (el: HTMLElement | null, key: string): void => {
    const message = el ? getLocalizedMessage(el, key) : ERR_FB;
    scheduleError(message, "pointerup");
  };

  const recalculateTotals = (): void => {
    try {
      let totalD = 0,
        totalC = 0;
      $(".debit").each((_: number, i: HTMLInputElement) => {
        totalD += parseFloat($(i).val()) || 0;
      });
      $(".credit").each((_: number, i: HTMLInputElement) => {
        totalC += parseFloat($(i).val()) || 0;
      });
      $(".totalDebit").html(totalD.toFixed(2));
      $(".totalCredit").html(totalC.toFixed(2));
    } catch {
      handleErrorDisplay(document.body, "calc_unavailable");
    }
  };

  const setupDebitCreditHandlers = (): void => {
    $(document).on("keyup", ".debit", function (this: HTMLElement) {
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

    $(document).on("keyup", ".credit", function (this: HTMLElement) {
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

  const initRepeater = (options: RepeaterOptions = {}): void => {
    const { selector = "body", maxUploadSize = "2048", destroyRoute = null, isEditMode = false } = options;

    if (typeof $ === "undefined") {
      devError("JournalEntryRepeater", new Error("jQuery unavailable"));
      return;
    }

    if (!$(selector + " .repeater").length) return;

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let $repeater: any;
    try {
      $repeater = $(selector + " .repeater").repeater({
        initEmpty: false,
        defaultValues: { status: 1 },
        show(this: HTMLElement) {
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
            if (typeof JsSearchBox === "function") JsSearchBox();
            if ($(".select2").length) $(".select2").select2();
          } catch {
            const el = this as HTMLElement;
            if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
              el.addEventListener("click", () => handleErrorDisplay(el, "repeater_show_unavailable"));
              el.setAttribute(DATA_LISTENER_ADDED, "true");
            }
          }
        },
        hide(this: HTMLElement, deleteElement: () => void) {
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
                    "X-CSRF-TOKEN": (document.getElementById("token") as HTMLInputElement)?.value,
                  },
                  data: { id },
                  cache: false,
                  success: () => {},
                  error: () => handleErrorDisplay($row[0], "destroy_unavailable"),
                });
              }
            }
          } catch {
            const el = this as HTMLElement;
            if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
              el.addEventListener("click", () => handleErrorDisplay(el, "repeater_hide_unavailable"));
              el.setAttribute(DATA_LISTENER_ADDED, "true");
            }
          }
        },
        ready: () => {},
        isFirstItemUndeletable: true,
      });

      const val = $(selector + " .repeater").attr("data-value") ?? "";
      if (val) {
        try {
          const list: JournalEntryRow[] = JSON.parse(val) as JournalEntryRow[];
          $repeater.setList(list);

          if (isEditMode) {
            list.forEach((item: JournalEntryRow, i: number) => {
              if ((item.credit ?? 0) > 0) {
                $(`input[name="accounts[${i}][credit]"]`).trigger("keyup");
              }
              if ((item.debit ?? 0) > 0) {
                $(`input[name="accounts[${i}][debit]"]`).trigger("keyup");
              }
            });
          } else {
            list.forEach((item: JournalEntryRow) => {
              const $row = $(`#sortable-table .id[value="${String(item.id ?? "")}"]`).parent();
              $row.find(".item").val(item.product_id);
              if (typeof changeItem === "function") changeItem($row.find(".item"));
            });
          }
        } catch {
          handleErrorDisplay(document.querySelector<HTMLElement>(".repeater"), "repeater_show_unavailable");
        }
      }
    } catch {
      handleErrorDisplay(document.querySelector<HTMLElement>(".repeater"), "repeater_show_unavailable");
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
