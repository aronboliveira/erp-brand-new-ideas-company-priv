(function () {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    if (!window.svToastOrAlert) {
      window.svToastOrAlert = function (msg) {
        scheduleError(msg || getMsg("action_unavailable"), "click");
      };
    }

    function guardByAction(formSelector, anchorSelector) {
      const forms = document.querySelectorAll(formSelector);
      Array.prototype.forEach.call(forms, function (f) {
        const act = (f.getAttribute("action") || "").trim();
        if (!act || act === "#") {
          const a = f.querySelector(anchorSelector);
          if (!a) return;
          const msg =
            a.getAttribute("data-guard-msg") || "This action is unavailable.";
          a.addEventListener("click", function (e) {
            e.preventDefault();
            window.svToastOrAlert(msg);
          });
        }
      });
    }

    function guardByHref(anchor) {
      if (!anchor) return;
      const href = (anchor.getAttribute("href") || "").trim();
      if (!href || href === "#") {
        const msg =
          anchor.getAttribute("data-guard-msg") ||
          "This action is unavailable.";
        anchor.addEventListener("click", function (e) {
          e.preventDefault();
          window.svToastOrAlert(msg);
        });
      }
    }

    function guardByDataUrl(anchor) {
      if (!anchor) return;
      const url = (anchor.getAttribute("data-url") || "").trim();
      if (!url || url === "#") {
        const msg =
          anchor.getAttribute("data-guard-msg") ||
          "This action is unavailable.";
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
      const act = (cForm.getAttribute("data-action") || "").trim();
      if (!act || act === "#") {
        const msg =
          cForm.getAttribute("data-guard-msg") || "This action is unavailable.";
        cBtn.addEventListener("click", function (e) {
          e.preventDefault();
          window.svToastOrAlert(msg);
        });
      }
    }

    guardByHref(document.querySelector("#grammarCheck"));
  } catch (_) {}
})();
