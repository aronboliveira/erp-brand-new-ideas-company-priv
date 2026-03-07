/**
 * @fileoverview TypeScript version of public/assets/js/routes/contracts/editList.js
 * @generated from original JavaScript - manual review recommended
 * @module editList
 */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_LISTENER_ADDED = "data-listener-added";

  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;

    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);

      msg =
        window.translations?.[lang]?.[key] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;

      if (msg !== errFb) {
        el?.setAttribute(dataGuardMsg, msg);
        el?.setAttribute(dataClientLocalized, "true");
      }
    }

    return msg;
  };

  const showFeedback = (
    el: HTMLElement,
    key: string,
    ev: string = "pointerup",
  ) => {
    const text = getMsg(el ?? document.body, key);
    const hasBs =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap.Toast;

    if (hasBs) {
      let toast = document.querySelector<HTMLElement>("#np-error-toast");

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
            </div>
          `;
        document.body.appendChild(toast);
      }

      const handler = (): void => {
        new bootstrap.Toast(toast).show();
      };
      document.addEventListener(ev, handler, { once: true });

      const mo = new MutationObserver((_, o) => {
        if (!document.body.contains(toast)) {
          document.removeEventListener(ev, handler);
          o.disconnect();
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    } else {
      const handler = (): void => {
        alert(text);
      };
      document.addEventListener(ev, handler, { once: true });
    }
  };

  const guardOnce = (
    el: HTMLElement,
    key: string,
    ev: string = "pointerup",
  ) => {
    if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;

    const handler = (): void => {
      showFeedback(el, key, ev);
    };
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

  const routeGuard = (
    element: HTMLElement | null,
    alt: string | null | undefined,
  ) => {
    const url = element?.getAttribute?.("data-url");
    const href =
      element?.getAttribute?.("action") ?? element?.getAttribute?.("href");
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

    const initChoices = (): void => {
      const $ms = $(".multi-select");
      if (!$ms.length) return;

      if (typeof window.Choices !== "function") {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Choices failed to load");
        showFeedback(document.body, "choices_unavailable");
        return;
      }

      $ms.each((_, el: HTMLElement) => {
        const id = el.id;
        if (id === "") return;
        if (el.getAttribute("data-choices-init") === "true") return;

        try {
          new Choices(`#${id}`, { removeItemButton: true });
          el.setAttribute("data-choices-init", "true");
        } catch {
          showFeedback(el, "choices_unavailable");
        }

        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(el)) o.disconnect();
        });
        mo.observe(document.body, { childList: true, subtree: true });
      });
    };

    const getParent = (bid: string, sourceEl: HTMLElement) => {
      const base = `{{ url('contracts/clients/select') }}`;
      const url = `${base}/${encodeURIComponent(bid ?? "")}`;

      if (!bid || routeGuard(null, url)) {
        guardOnce(sourceEl, "project_list_unavailable");
        return;
      }

      $.ajax({
        url,
        type: "GET",
        success: (data: unknown) => {
          try {
            const $sel = $("#project_id");
            if (!$sel.length) {
              guardOnce(document.body, "project_list_unavailable");
              return;
            }

            $sel.empty();

            if (Array.isArray(data) && data.length > 0) {
              data.forEach(it => {
                if (!it) return;
                const val = String(it.id ?? "");
                const text = String(it.name ?? "");
                if (val.length !== 0)
                  $sel.append(`<option value="${val}">${text}</option>`);
              });
            }

            if (
              typeof window.Choices === "function" &&
              !$sel[0].getAttribute("data-choices-init")
            ) {
              try {
                new Choices("#project_id", { removeItemButton: true });
                $sel[0].setAttribute("data-choices-init", "true");
              } catch {
                showFeedback($sel[0], "choices_unavailable");
              }
            }

            if (!Array.isArray(data) || data.length === 0) {
              $sel.empty();
            }
          } catch {
            showFeedback(sourceEl, "project_list_unavailable");
          }
        },
        error: (): void => {
          showFeedback(sourceEl, "project_list_unavailable");
        },
      });
    };

    initChoices();

    $(document).on("change", ".client_select", function (): void {
      const client_id = String($(this).val() ?? "");
      getParent(client_id, this as unknown as HTMLElement);
    });
  } catch (e) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Initialization failed", e);
  }
})();

export {};
