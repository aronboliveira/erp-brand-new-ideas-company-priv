/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/stages/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  const Q = (sel: string): HTMLElement | null => document.querySelector(sel),
    QA = (sel: string): HTMLElement[] =>
      Array.from(document.querySelectorAll(sel)),
    DEFAULT_ROUTE_MSG =
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
    DEFAULT_ORDER_ERR = "Failed to save the new order of job stages.";
  const toast = (message: string): void => {
    const text = message || DEFAULT_ROUTE_MSG,
      hasBs = !!(
        document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
        window.bootstrap
      );
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      document.body.appendChild(box);
    }
    if (hasBs) {
      const t = document.createElement("div");
      t.className = "toast";
      for (const [k, v] of Object.entries({
        role: "alert",
        "aria-live": "assertive",
        "aria-atomic": "true",
      }))
        t.setAttribute(k, v);
      const b = document.createElement("div");
      b.className = "toast-body";
      b.textContent = text;
      t.appendChild(b);
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(text);
    }
  };

  const bindLinkGuard = (el: HTMLElement | null): void => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    if (!el.getAttribute("data-listener-bound-click")) {
      el.setAttribute("data-listener-bound-click", "1");
      el.addEventListener("click", (e: Event) => {
        const href = (el.getAttribute("href") ?? "#").trim(),
          url = (el.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        toast(el.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
        el.setAttribute("data-failed-route", "true");
      });
    }
  };

  const bindFormGuard = (fm: HTMLFormElement | null): void => {
    if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    if (!fm.getAttribute("data-listener-bound-submit")) {
      fm.setAttribute("data-listener-bound-submit", "1");
      fm.addEventListener("submit", (e: Event) => {
        const action = (fm.getAttribute("action") ?? "#").trim(),
          url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        toast(fm.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
        fm.setAttribute("data-failed-route", "true");
      });
    }
  };

  const getCsrf = (): string => {
    const meta = document.querySelector(
      'meta[name="csrf-token"]',
    ) as HTMLMetaElement | null;
    if (meta?.content) return meta.content;
    // fallback: try hidden input in any form
    const input = document.querySelector(
      'input[name="_token"]',
    ) as HTMLInputElement | null;
    return input ? input.value : "";
  };

  // Lightweight HTML5 drag & drop for <li> reordering
  const enableDragSort = (list: HTMLElement | null): void => {
    if (!list) return;
    const items = Array.from(list.children) as HTMLElement[];
    items.forEach((li: HTMLElement) => {
      li.setAttribute("draggable", "true");
      li.addEventListener("dragstart", (e: DragEvent) => {
        if (e.dataTransfer) {
          e.dataTransfer.effectAllowed = "move";
          e.dataTransfer.setData(
            "text/plain",
            li.getAttribute("data-id") ?? "",
          );
        }
        li.classList.add("dragging");
      });
      li.addEventListener("dragend", () => li.classList.remove("dragging"));
    });

    if (!list.getAttribute("data-listener-bound-dragover")) {
      list.setAttribute("data-listener-bound-dragover", "1");
      list.addEventListener("dragover", (e: DragEvent) => {
        e.preventDefault();
        const dragging = list.querySelector(".dragging");
        if (!dragging) return;
        const after = getDragAfterElement(list, e.clientY);
        if (after == null) {
          list.appendChild(dragging);
        } else {
          list.insertBefore(dragging, after);
        }
      });
    }

    if (!list.getAttribute("data-listener-bound-drop")) {
      list.setAttribute("data-listener-bound-drop", "1");
      list.addEventListener("drop", (): void => {
        // on drop, attempt to persist order
        persistOrder(list);
      });
    }
  };

  const getDragAfterElement = (
    container: HTMLElement,
    y: number,
  ): HTMLElement | null => {
    const els = [
      ...container.querySelectorAll("li:not(.dragging)"),
    ] as HTMLElement[];
    return (
      els.reduce(
        (
          closest: { offset: number; element: HTMLElement | null },
          child: HTMLElement,
        ) => {
          const box = child.getBoundingClientRect(),
            offset = y - (box.top + box.height / 2);
          if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
          } else {
            return closest;
          }
        },
        { offset: Number.NEGATIVE_INFINITY, element: null },
        // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      ).element || null
    );
  };

  const persistOrder = (list: HTMLElement): void => {
    const url = (list.getAttribute("data-order-url") ?? "#").trim();
    if (url === "#") {
      toast(list.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      return;
    }
    const ids = QA("#job-stages-sortable > li")
      .map((li: HTMLElement) => li.getAttribute("data-id"))
      .filter(Boolean);
    if (ids.length === 0) return;
    const payload = { order: ids },
      csrf = getCsrf();
    // Prefer fetch; fallback to jQuery if present
    const doFetch = (): Promise<unknown> =>
      fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify(payload),
      }).then(r => (r.ok ? r.json().catch(console.error) : Promise.reject()));

    const $ = window.jQuery;
    const doAjax = (): Promise<unknown> => {
      if (typeof $ === "undefined") return Promise.reject();
      return Promise.resolve(
        $.ajax({
          url,
          method: "POST",
          headers: csrf ? { "X-CSRF-TOKEN": csrf } : {},
          data: payload,
        }),
      );
    };

    (typeof fetch === "function"
      ? doFetch().catch(doAjax)
      : doAjax().catch(doFetch)
    )
      .then((): void => {
        /* ok */
      })
      .catch((): void => {
        toast(DEFAULT_ORDER_ERR);
      });
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    // guards & tooltips
    QA("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
    QA("form[data-guard-msg], form[data-url]").forEach(fm =>
      bindFormGuard(fm as HTMLFormElement | null),
    );
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {
          console.error(`[index] Error:`, _);
        }
      });
    } catch (_) {
      console.error(`[index] Error:`, _);
    }

    // drag & drop
    enableDragSort(Q("#job-stages-sortable"));
  });
})();

export {};
