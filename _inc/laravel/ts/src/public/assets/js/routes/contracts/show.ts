/**
 * @fileoverview TypeScript version of public/assets/js/routes/contracts/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */

/* global bootstrap */
(function (): void {
  try {
    if (!window.svToastOrAlert) {
      window.svToastOrAlert = function (msg: string) {
        try {
          const ok = !!window.bootstrap?.Toast;
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

    function guardByAction(formSelector: string, anchorSelector: string) {
      const forms = document.querySelectorAll(formSelector);
      Array.prototype.forEach.call(forms, function (f: Element) {
        const act = (f.getAttribute("action") ?? "").trim();
        if (!act || act === "#") {
          const a = f.querySelector(anchorSelector);
          if (!a) return;
          const msg =
            a.getAttribute("data-guard-msg") ?? "This action is unavailable.";
          a.addEventListener("click", function (e: Event) {
            e.preventDefault();
            window.svToastOrAlert!(msg);
          });
        }
      });
    }

    function guardByHref(anchor: HTMLElement) {
      if (!anchor) return;
      const href = (anchor.getAttribute("href") ?? "").trim();
      if (!href || href === "#") {
        const msg =
          anchor.getAttribute("data-guard-msg") ??
          "This action is unavailable.";
        anchor.addEventListener("click", function (e: Event) {
          e.preventDefault();
          window.svToastOrAlert!(msg);
        });
      }
    }

    function guardByDataUrl(anchor: HTMLElement) {
      if (!anchor) return;
      const url = (anchor.getAttribute("data-url") ?? "").trim();
      if (!url || url === "#") {
        const msg =
          anchor.getAttribute("data-guard-msg") ??
          "This action is unavailable.";
        anchor.addEventListener("click", function (e: Event) {
          e.preventDefault();
          window.svToastOrAlert!(msg);
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
      if (!act || act === "#") {
        const msg =
          cForm.getAttribute("data-guard-msg") ?? "This action is unavailable.";
        cBtn.addEventListener("click", function (e: Event) {
          e.preventDefault();
          window.svToastOrAlert!(msg);
        });
      }
    }

    const grammarEl = document.querySelector<HTMLElement>("#grammarCheck");
    if (grammarEl) guardByHref(grammarEl);
  } catch (_) {}
})();

export {};
