/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/reports/detail.js
 * @generated from original JavaScript - manual review recommended
 * @module detail
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
(function (): void {
  function toast(msg) {
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (window.bootstrap.Toast) {
      const box =
        document.getElementById("toast-container") ??
        document.body.appendChild(
          Object.assign(document.createElement("div"), {
            id: "toast-container",
          })
        );
      const t = document.createElement("div");
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.innerHTML = '<div class="toast-body"></div>';
      t.querySelector(".toast-body").textContent =
        msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator.";
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(
        msg ?? "Requested route is unavailable. Please contact technical support or your domain administrator."
      );
    }
  }

  function guardClick(a) {
    if (!a || a.getAttribute("data-guard-bound") === "1") return;
    a.setAttribute("data-guard-bound", "1");
    a.addEventListener("click", function (e) {
      const href = (a.getAttribute("href") ?? "#").trim();
      const url = (a.getAttribute("data-url") || href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg"));
    });
  }

  function init() {
    const ids = ["#project-report-index-link"];

    ids.forEach(function (sel) {
      const el = document.querySelector(sel);
      if (el) guardClick(el);
    });

    document
      .querySelectorAll('a[id^="project-report-export-link-"]')
      .forEach(guardClick);
    document
      .querySelectorAll('a[id^="project-task-show-link-"]')
      .forEach(guardClick);
  }

  document.addEventListener("DOMContentLoaded", init);
})();

export {};
