/**
 * @fileoverview TypeScript version of public/assets/js/routes/contracts/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, no-inner-declarations */

/* global bootstrap */
(function (): void {
  try {
    if (!window.svToastOrAlert) {
      window.svToastOrAlert = function (msg) {
        try {
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
          const ok = !!(window.bootstrap && window.bootstrap.Toast);
          // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
          if (!ok) {
            alert(msg);
            return;
          }
          let t = document.getElementById("route-guard-toast");
          if (!t) {
            t = document.createElement("div");
            t.id = "route-guard-toast";
            t.className =
              "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            t.innerHTML =
              '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            document.body.appendChild(t);
          }
          const body = t.querySelector(".toast-body");
          if (body) body.textContent = msg;
          new window.bootstrap.Toast(t, { delay: 4000 }).show();
        } catch (e) {
          alert(msg);
        }
      };
    }

    function guardByAction(formSelector, anchorSelector) {
      const forms = document.querySelectorAll(formSelector);
      Array.prototype.forEach.call(forms, function (f) {
        const act = (f.getAttribute("action") ?? "").trim();
        if (!act || act === "#") {
          const a = f.querySelector(anchorSelector);
          if (!a) return;
          const msg =
            a.getAttribute("data-guard-msg") ?? "This action is unavailable.";
          a.addEventListener("click", function (e) {
            e.preventDefault();
            window.svToastOrAlert(msg);
          });
        }
      });
    }

    function guardByHref(anchor) {
      if (!anchor) return;
      const href = (anchor.getAttribute("href") ?? "").trim();
      if (!href || href === "#") {
        const msg =
          anchor.getAttribute("data-guard-msg") ?? "This action is unavailable.";
        anchor.addEventListener("click", function (e) {
          e.preventDefault();
          window.svToastOrAlert(msg);
        });
      }
    }

    function guardByDataUrl(anchor) {
      if (!anchor) return;
      const url = (anchor.getAttribute("data-url") ?? "").trim();
      if (!url || url === "#") {
        const msg =
          anchor.getAttribute("data-guard-msg") ?? "This action is unavailable.";
        anchor.addEventListener("click", function (e) {
          e.preventDefault();
          window.svToastOrAlert(msg);
        });
      }
    }

    guardByAction('form[id^="file-del-form-"]', "a");
    guardByAction('form[id^="comment-del-form-"]', "a");
    guardByAction('form[id^="note-del-form-"]', "a");

    const cForm = document.getElementById("form-comment");
    const cBtn = document.getElementById("comment_submit");
    if (cForm && cBtn) {
      const act = (cForm.getAttribute("data-action") ?? "").trim();
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!act || act === "#") {
        const msg =
          cForm.getAttribute("data-guard-msg") ?? "This action is unavailable.";
        cBtn.addEventListener("click", function (e) {
          e.preventDefault();
          window.svToastOrAlert(msg);
        });
      }
    }

    guardByHref(document.querySelector<HTMLElement>("#grammarCheck"));
  } catch (_) {}
})();

export {};
