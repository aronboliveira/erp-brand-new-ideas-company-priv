(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  let errorMessage = "";

  const getLocalizedMessage = (key, el) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const showError = message => {
    guard.showToast(message);
  };

  const onErrorPointerUp = () => {
    if (errorMessage) {
      showError(errorMessage);
      errorMessage = "";
    }
  };

  document.addEventListener("pointerup", onErrorPointerUp);

  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      })
    );
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", () => {
    const typeEl = document.getElementById("type");
    if (typeEl && typeEl.dataset.listenerAttached !== \"true\") {
      typeEl.dataset.listenerAttached = \"true\";
      const onTypeChange = () => {
        try {
          const url = '{{ route(\"charofAccount.subType\") }}';\n          if (!url) throw new Error(\"char_of_account_subtype_unavailable\");
          const val = typeEl.value ?? \"\";
          $.ajax({
            url,
            type: \"POST\",
            dataType: \"json\",
            data: { type: val, _token: \"{{ csrf_token() }}\" },
          })
            .done(data => {
              const sub = document.getElementById(\"sub_type\");
              if (!sub) return;
              sub.innerHTML = \"\";
              Object.entries(data).forEach(([k, v]) => {
                const o = document.createElement(\"option\");
                o.value = k;
                o.textContent = v;
                sub.appendChild(o);
              });
            })
            .fail(() => {
              errorMessage = getLocalizedMessage(\"char_of_account_subtype_unavailable\", typeEl);
            });
        } catch (e) {
          errorMessage = getLocalizedMessage(e.message, typeEl);
        }
      };
      typeEl.addEventListener(\"change\", onTypeChange);
      new MutationObserver((ms, obs) => {
        ms.forEach(m =>
          Array.from(m.removedNodes).forEach(n => {
            if (n === typeEl) {
              typeEl.removeEventListener(\"change\", onTypeChange);
              obs.disconnect();
            }
          })
        );
      }).observe(document.body, { childList: true, subtree: true });
    }

    try {
      const copyDates = () => {
        const start = document.querySelector(\".startDate\")?.value ?? \"\";
        const end = document.querySelector(\".endDate\")?.value ?? \"\";
        document
          .querySelectorAll(\".start_date\")
          .forEach(el => (el.value = start));
        document.querySelectorAll(\".end_date\").forEach(el => (el.value = end));
      };
      copyDates();
    } catch {
      errorMessage = getLocalizedMessage(\"date_callback_failed\", document.body);
    }
  });
})();
