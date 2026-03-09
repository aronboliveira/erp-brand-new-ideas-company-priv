/**
 * @file Job Stages Index Route Guard
 * @description Guards job stages routes, handles drag-drop reordering, and initializes tooltips using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const Q = sel => document.querySelector(sel);
  const QA = sel => Array.from(document.querySelectorAll(sel));
  const DEFAULT_ORDER_ERR = "Failed to save the new order of job stages.";

  const initTooltips = () => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          window.bootstrap?.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  };

  const getCsrf = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : "";
  };

  const enableDragSort = list => {
    if (!list) return;
    const items = Array.from(list.children);
    items.forEach(li => {
      li.setAttribute("draggable", "true");
      li.addEventListener("dragstart", e => {
        e.dataTransfer.effectAllowed = "move";
        e.dataTransfer.setData("text/plain", li.getAttribute("data-id") || "");
        li.classList.add("dragging");
      });
      li.addEventListener("dragend", () => li.classList.remove("dragging"));
    });

    list.addEventListener("dragover", e => {
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

    list.addEventListener("drop", () => persistOrder(list));
  };

  const getDragAfterElement = (container, y) => {
    const els = [...container.querySelectorAll("li:not(.dragging)")];
    return (
      els.reduce(
        (closest, child) => {
          const box = child.getBoundingClientRect();
          const offset = y - (box.top + box.height / 2);
          if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
          } else {
            return closest;
          }
        },
        { offset: Number.NEGATIVE_INFINITY },
      ).element || null
    );
  };

  const persistOrder = list => {
    const url = (list.getAttribute("data-order-url") || "#").trim();
    if (url === "#") {
      guard.showToast(
        list.getAttribute("data-guard-msg") || "Route unavailable",
      );
      return;
    }
    const ids = QA("#job-stages-sortable > li")
      .map(li => li.getAttribute("data-id"))
      .filter(Boolean);
    if (!ids.length) return;

    const payload = { order: ids };
    const csrf = getCsrf();

    const doFetch = () =>
      fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify(payload),
      }).then(r => (r.ok ? r.json() : Promise.reject()));

    const doAjax = () => {
      if (typeof $ === "undefined") return Promise.reject();
      return $.ajax({
        url,
        method: "POST",
        headers: csrf ? { "X-CSRF-TOKEN": csrf } : {},
        data: payload,
      });
    };

    (typeof fetch === "function"
      ? doFetch().catch(doAjax)
      : doAjax().catch(doFetch)
    )
      .then(() => {})
      .catch(() => guard.showToast(DEFAULT_ORDER_ERR, "error"));
  };

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a[data-guard-msg], a[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg], form[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    initTooltips();
    enableDragSort(Q("#job-stages-sortable"));
  });
})();
