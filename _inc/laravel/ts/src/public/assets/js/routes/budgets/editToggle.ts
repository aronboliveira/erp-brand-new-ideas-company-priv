/**
 * @fileoverview TypeScript version of public/assets/js/routes/budgets/editToggle.js
 * @generated from original JavaScript - manual review recommended
 * @module editToggle
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-base-to-string, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, @typescript-eslint/restrict-template-expressions */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langKey = "erp-np-lang";

  function getLocalizedMessage(key, el) {
    let msg = errFb;
    if (el.getAttribute(dataClientLocalized) === "true") {
      msg = el.getAttribute(dataGuardMsg) || msg;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem(langKey) ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        msg;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  }

  function showError(message) {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const bsLink = document.querySelector('link[href*="bootstrap"]');
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (bsLink && window.bootstrap.Toast) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = message;
        toast.appendChild(body);
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  }

  let errorMessage = "";
  const onErrorPointerUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onErrorPointerUp);
  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      { m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      }); }
    );
  }).observe(document.body, { childList: true, subtree: true });

  $((): void => {
    const bindIncome = (): void => {
      $(".income_data").each((_, el) => {
        const $el = $(el);
        if ($el.data("listener-income") === true) return;
        $el.data("listener-income", true);
        const handler = e => {
          try {
            const $row = $el.closest("tr");
            let catTotal = 0;
            $row.find(".income_data").each((i, inp) => {
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
              const v = parseFloat($(inp).val()) || 0;
              catTotal += v;
            });
            $row.find(".totalIncome").text(catTotal);
            const month = $el.data("month") ?? "";
            let mTotal = 0;
            $row
              .parent()
              .find(`.${month}_income`)
              .each((i, inp) => {
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
                mTotal += parseFloat($(inp).val()) || 0;
              });
            $row.parent().find(`.${month}_total_income`).text(mTotal);
            let grand = 0;
            $row
              .parent()
              .find(".totalIncome")
              .each((i, td) => {
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
                grand += parseFloat($(td).text()) || 0;
              });
            $row.parent().find(".income").text(grand);
          } catch {
            errorMessage = getLocalizedMessage("income_calculation_failed", el);
          }
        };
        $el.on("keyup", handler);
        new MutationObserver((ms, obs) => {
          ms.forEach(m =>
            { m.removedNodes.forEach(n => {
              if (n === el) {
                $el.off("keyup", handler);
                obs.disconnect();
              }
            }); }
          );
        }).observe(document.body, { childList: true, subtree: true });
        handler();
      });
    };

    const bindExpense = (): void => {
      $(".expense_data").each((_, el) => {
        const $el = $(el);
        if ($el.data("listener-expense") === true) return;
        $el.data("listener-expense", true);
        const handler = e => {
          try {
            const $row = $el.closest("tr");
            let catTotal = 0;
            $row.find(".expense_data").each((i, inp) => {
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
              catTotal += parseFloat($(inp).val()) || 0;
            });
            $row.find(".totalExpense").text(catTotal);
            const month = $el.data("month") ?? "";
            let mTotal = 0;
            $row
              .parent()
              .find(`.${month}_expense`)
              .each((i, inp) => {
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
                mTotal += parseFloat($(inp).val()) || 0;
              });
            $row.parent().find(`.${month}_total_expense`).text(mTotal);
            let grand = 0;
            $row
              .parent()
              .find(".totalExpense")
              .each((i, td) => {
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
                grand += parseFloat($(td).text()) || 0;
              });
            $row.parent().find(".expense").text(grand);
          } catch {
            errorMessage = getLocalizedMessage(
              "expense_calculation_failed",
              el
            );
          }
        };
        $el.on("keyup", handler);
        new MutationObserver((ms, obs) => {
          ms.forEach(m =>
            { m.removedNodes.forEach(n => {
              if (n === el) {
                $el.off("keyup", handler);
                obs.disconnect();
              }
            }); }
          );
        }).observe(document.body, { childList: true, subtree: true });
        handler();
      });
    };

    const bindPeriod = (): void => {
      $(".period").each((_, el) => {
        const $el = $(el);
        if ($el.data("listener-period") === true) return;
        $el.data("listener-period", true);
        const handler = e => {
          try {
            const val = $el.val() ?? "";
            $(".budget_plan").addClass("d-none");
            $(`#${val}`).removeClass("d-none").addClass("d-block");
          } catch {
            errorMessage = getLocalizedMessage("period_toggle_failed", el);
          }
        };
        $el.on("change", handler);
        new MutationObserver((ms, obs) => {
          ms.forEach(m =>
            { m.removedNodes.forEach(n => {
              if (n === el) {
                $el.off("change", handler);
                obs.disconnect();
              }
            }); }
          );
        }).observe(document.body, { childList: true, subtree: true });
        handler();
      });
    };

    bindIncome();
    bindExpense();
    bindPeriod();
  });
})();

export {};
