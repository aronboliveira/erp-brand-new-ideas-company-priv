(function () {
  try {
    if (!window.svToastOrAlert) {
      window.svToastOrAlert = function (msg) {
        try {
          var ok = !!(window.bootstrap && window.bootstrap.Toast);
          if (!ok) {
            alert(msg);
            return;
          }
          var t = document.getElementById("route-guard-toast");
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
          var body = t.querySelector(".toast-body");
          if (body) body.textContent = msg;
          new window.bootstrap.Toast(t, { delay: 4000 }).show();
        } catch (e) {
          alert(msg);
        }
      };
    }

    function guardByAction(formSelector, anchorSelector) {
      var forms = document.querySelectorAll(formSelector);
      Array.prototype.forEach.call(forms, function (f) {
        var act = (f.getAttribute("action") || "").trim();
        if (!act || act === "#") {
          var a = f.querySelector(anchorSelector);
          if (!a) return;
          var msg =
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
      var href = (anchor.getAttribute("href") || "").trim();
      if (!href || href === "#") {
        var msg =
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
      var url = (anchor.getAttribute("data-url") || "").trim();
      if (!url || url === "#") {
        var msg =
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

    var cForm = document.getElementById("form-comment");
    var cBtn = document.getElementById("comment_submit");
    if (cForm && cBtn) {
      var act = (cForm.getAttribute("data-action") || "").trim();
      if (!act || act === "#") {
        var msg =
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
