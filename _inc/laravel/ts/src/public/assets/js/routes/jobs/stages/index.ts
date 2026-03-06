/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/stages/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const Q = sel => document.querySelector(sel);
  const QA = sel => Array.from(document.querySelectorAll(sel));

  const DEFAULT_ROUTE_MSG =
    "Requested route is unavailable. Please contact technical support or your domain administrator.";
  const DEFAULT_ORDER_ERR = "Failed to save the new order of job stages.";

  const toast = message => {
    const text = message || DEFAULT_ROUTE_MSG;
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
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

  const bindLinkGuard = el => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      const href = (el.getAttribute("href") ?? "#").trim();
      const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(el.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      el.setAttribute("data-failed-route", "true");
    });
  };

  const bindFormGuard = fm => {
    if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      const action = (fm.getAttribute("action") ?? "#").trim();
      const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      fm.setAttribute("data-failed-route", "true");
    });
  };

  const getCsrf = (): void => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta.content) return meta.content;
    // fallback: try hidden input in any form
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : "";
  };

  // Lightweight HTML5 drag & drop for <li> reordering
  const enableDragSort = list => {
    if (!list) return;
    const items = Array.from(list.children);
    items.forEach(li => {
      li.setAttribute("draggable", "true");
      li.addEventListener("dragstart", e => {
        e.dataTransfer.effectAllowed = "move";
        e.dataTransfer.setData("text/plain", li.getAttribute("data-id") ?? "");
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

    list.addEventListener("drop", (): void => {
      // on drop, attempt to persist order
      persistOrder(list);
    });
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
        { offset: Number.NEGATIVE_INFINITY }
      ).element || null
    );
  };

  const persistOrder = list => {
    const url = (list.getAttribute("data-order-url") ?? "#").trim();
    if (url === "#") {
      toast(list.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      return;
    }
    const ids = QA("#job-stages-sortable > li")
      .map(li => li.getAttribute("data-id"))
      .filter(Boolean);
    if (ids.length === 0) return;

    const payload = { order: ids };
    const csrf = getCsrf();

    // Prefer fetch; fallback to jQuery if present
    const doFetch = () =>
      fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify(payload),
      }).then(r => (r.ok ? r.json().catch(console.error) : Promise.reject()));

    const doAjax = (): void => {
      if (typeof $ === "undefined") return Promise.reject();
      return $.ajax({
        url,
        method: "POST",
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
        headers: csrf ? { "X-CSRF-TOKEN": csrf } : {},
        data: payload,
      });
    };

    (typeof fetch === "function"
      ? doFetch().catch(doAjax)
      : doAjax().catch(doFetch)
    )
      .then((): void => {
        /* ok */
      })
      .catch((): void => { toast(DEFAULT_ORDER_ERR); });
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    // guards & tooltips
    QA("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
    QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}

    // drag & drop
    enableDragSort(Q("#job-stages-sortable"));
  });
})();

export {};
