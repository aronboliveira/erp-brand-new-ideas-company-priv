/**
 * @fileoverview TypeScript version of public/assets/js/routes/chartOfAccounts/date.js
 * @generated from original JavaScript - manual review recommended
 * @module date
 */


// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const errFb = "# ERROR";
  const clientFlag = "data-client-localized";
  const guardMsgKey = "data-guard-msg";
  const langKey = "erp-np-lang";
  let errorMessage = "";
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getLocalizedMessage = (key: string, el: HTMLElement) => {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsgKey) || msg;
    } else {
      let lang = (
        sessionStorage.getItem(langKey) ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(guardMsgKey) ||
        window.translations?.en?.[key] ||
        msg;
      if (msg !== errFb) {
        el.setAttribute(guardMsgKey, msg);
        el.setAttribute(clientFlag, "true");
      }
    }
    return msg;
  };

  const showError = (message: string): void=> {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const bs =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (bs) {
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
  };

  const onErrorPointerUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onErrorPointerUp);
  new MutationObserver((muts, obs) => {
    muts.forEach(m => {
      Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", (): void => {
    const typeEl = document.getElementById("type") as HTMLSelectElement | null;
    if (!typeEl) return;
    if (typeEl.dataset.listenerAttached !== "true") {
      typeEl.dataset.listenerAttached = "true";
      const onTypeChange = (): void => {
        try {
          const url = '{{ route("charofAccount.subType") }}' as string;
          if (url === "")
            throw new Error("char_of_account_subtype_unavailable");
          const val = typeEl.value ?? "";
          $.ajax({
            url,
            type: "POST",
            dataType: "json",
            data: { type: val, _token: "{{ csrf_token() }}" },
          })
            .done((data: unknown) => {
              const sub = document.getElementById("sub_type");
              if (!sub) return;
              sub.innerHTML = "";
              Object.entries(data as Record<string, string>).forEach(
                ([k, v]) => {
                  const o = document.createElement("option");
                  o.value = k;
                  o.textContent = v;
                  sub.appendChild(o);
                },
              );
            })
            .fail((): void => {
              throw new Error("char_of_account_subtype_unavailable");
            });
        } catch (e) {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
          errorMessage = getLocalizedMessage(e.message, typeEl);
        }
      };
      typeEl.addEventListener("change", onTypeChange);
      new MutationObserver((ms, obs) => {
        ms.forEach(m => {
          Array.from(m.removedNodes).forEach(n => {
            if (n === typeEl) {
              typeEl.removeEventListener("change", onTypeChange);
              obs.disconnect();
            }
          });
        });
      }).observe(document.body, { childList: true, subtree: true });
    }

    try {
      const copyDates = (): void => {
        const start =
          document.querySelector<HTMLInputElement>(".startDate")?.value ?? "";
        const end =
          document.querySelector<HTMLInputElement>(".endDate")?.value ?? "";
        document
          .querySelectorAll<HTMLInputElement>(".start_date")
          .forEach((el): void => {
            el.value = start;
          });
        document
          .querySelectorAll<HTMLInputElement>(".end_date")
          .forEach((el): void => {
            el.value = end;
          });
      };
      copyDates();
    } catch {
      errorMessage = getLocalizedMessage("date_callback_failed", document.body);
    }
  });
})();

export {};
