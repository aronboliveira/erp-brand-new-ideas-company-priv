(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_LISTENER_ADDED = "data-listener-added";

  const getMsg = (el, msgKey) => {
    let msg = errFb;
    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el?.setAttribute(dataGuardMsg, msg);
        el?.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showFeedback = (el, key, ev = "pointerup") => {
    const text = getMsg(el || document.body, key);
    const hasBs =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap?.Toast;
    if (hasBs) {
      let toast = document.querySelector("#np-error-toast");
      if (!toast) {
        toast = document.createElement("div");
        toast.id = "np-error-toast";
        toast.className = "toast align-items-center text-bg-danger border-0";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        toast.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${text}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
        document.body.appendChild(toast);
      }
      const handler = () => new bootstrap.Toast(toast).show();
      document.addEventListener(ev, handler, { once: true });
      const mo = new MutationObserver((_, o) => {
        if (!document.body.contains(toast)) {
          document.removeEventListener(ev, handler);
          o.disconnect();
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    } else {
      const handler = () => alert(text);
      document.addEventListener(ev, handler, { once: true });
    }
  };

  const guardOnce = (el, key, ev = "pointerup") => {
    if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
    const handler = () => showFeedback(el, key, ev);
    el.addEventListener(ev, handler, { once: true });
    el.setAttribute(DATA_LISTENER_ADDED, "true");
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(el)) {
        el.removeEventListener(ev, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const routeGuard = (element, alt) => {
    const url = element?.getAttribute?.("data-url");
    const href = element?.action ?? element?.href;
    return (
      (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#")
    );
  };

  try {
    if (typeof $ === "undefined") {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery failed to load");
      return;
    }

    const initChoices = () => {
      if (!$(".multi-select").length) return;
      if (typeof window.Choices !== "function") {
        showFeedback(document.body, "choices_unavailable");
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Choices library failed to load");
        return;
      }
      $(".multi-select").each((_, element) => {
        const id = element?.id;
        if (!id) return;
        if (element.getAttribute("data-choices-init") === "true") return;
        try {
           
          new Choices(`#${id}`, { removeItemButton: true });
          element.setAttribute("data-choices-init", "true");
        } catch {
          showFeedback(element, "choices_unavailable");
        }
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(element)) {
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      });
    };

    const onClientChange = e => {
      const clientId = $(e.currentTarget).val() ?? "";
      getParent(clientId, e.currentTarget);
    };

    const getParent = (bid, targetEl) => {
      const base = `{{ url('contracts/clients/select') }}`;
      const url = `${base}/${encodeURIComponent(bid ?? "")}`;
      if (!bid || routeGuard(null, url)) {
        guardOnce(targetEl, "project_list_unavailable");
        return;
      }
      $.ajax({
        url,
        type: "GET",
        success: data => {
          try {
            const $select = $("#project_id");
            if (!$select.length) {
              guardOnce(document.body, "project_list_unavailable");
              return;
            }
            $select.empty();
            if (Array.isArray(data) && data.length) {
              data.forEach(item => {
                if (!item) return;
                const val = item.id ?? "";
                const text = item.name ?? "";
                if (String(val).length)
                  $select.append(
                    `<option value="${String(val)}">${String(text)}</option>`
                  );
              });
            }
            if (
              typeof window.Choices === "function" &&
              !$select[0].getAttribute("data-choices-init")
            ) {
              try {
                 
                new Choices("#project_id", { removeItemButton: true });
                $select[0].setAttribute("data-choices-init", "true");
              } catch {
                showFeedback($select[0], "choices_unavailable");
              }
            }
          } catch {
            showFeedback(targetEl, "project_list_unavailable");
          }
        },
        error: () => showFeedback(targetEl, "project_list_unavailable"),
      });
    };

    initChoices();
    $(document).on("change", ".client_select", onClientChange);
  } catch (e) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Initialization failed", e);
  }
})();
