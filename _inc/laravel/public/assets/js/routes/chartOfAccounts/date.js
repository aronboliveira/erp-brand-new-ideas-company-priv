(() => {
  const errFb = "# ERROR";
  const clientFlag = "data-client-localized";
  const guardMsgKey = "data-guard-msg";
  const langKey = "erp-np-lang";
  let errorMessage = "";

  const getLocalizedMessage = (key, el) => {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsgKey) || msg;
    } else {
      let lang = (
        sessionStorage.getItem(langKey) ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        translations?.[lang]?.[key] ||
        el.getAttribute(guardMsgKey) ||
        translations?.["en"]?.[key] ||
        msg;
      if (msg !== errFb) {
        el.setAttribute(guardMsgKey, msg);
        el.setAttribute(clientFlag, "true");
      }
    }
    return msg;
  };

  const showError = message => {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        document.body.appendChild(container);
      }
      const bs =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap?.Toast;
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
    if (typeEl && typeEl.dataset.listenerAttached !== "true") {
      typeEl.dataset.listenerAttached = "true";
      const onTypeChange = () => {
        try {
          const url = '{{ route("charofAccount.subType") }}';
          if (!url) throw new Error("char_of_account_subtype_unavailable");
          const val = typeEl.value ?? "";
          $.ajax({
            url,
            type: "POST",
            dataType: "json",
            data: { type: val, _token: "{{ csrf_token() }}" },
          })
            .done(data => {
              const sub = document.getElementById("sub_type");
              if (!sub) return;
              sub.innerHTML = "";
              Object.entries(data).forEach(([k, v]) => {
                const o = document.createElement("option");
                o.value = k;
                o.textContent = v;
                sub.appendChild(o);
              });
            })
            .fail(() => {
              throw new Error("char_of_account_subtype_unavailable");
            });
        } catch (e) {
          errorMessage = getLocalizedMessage(e.message, typeEl);
        }
      };
      typeEl.addEventListener("change", onTypeChange);
      new MutationObserver((ms, obs) => {
        ms.forEach(m =>
          Array.from(m.removedNodes).forEach(n => {
            if (n === typeEl) {
              typeEl.removeEventListener("change", onTypeChange);
              obs.disconnect();
            }
          })
        );
      }).observe(document.body, { childList: true, subtree: true });
    }

    try {
      const copyDates = () => {
        const start = document.querySelector(".startDate")?.value ?? "";
        const end = document.querySelector(".endDate")?.value ?? "";
        document
          .querySelectorAll(".start_date")
          .forEach(el => (el.value = start));
        document.querySelectorAll(".end_date").forEach(el => (el.value = end));
      };
      copyDates();
    } catch {
      errorMessage = getLocalizedMessage("date_callback_failed", document.body);
    }
  });
})();
