(function () {
  const listened = "data-listener-active";
  function toast(message) {
    const text = message || "Requested route is unavailable.";
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      window.bootstrap
    );
    if (hasBs) {
      let box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        document.body.appendChild(box);
      }
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
  }
  function guardLink(a) {
    if (!a || a.getAttribute(listened) === "true") return;
    a.setAttribute(listened, "true");
    a.addEventListener("click", function (e) {
      const href = (a.getAttribute("href") || "#").trim();
      const url = (a.getAttribute("data-url") || href || "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg") || "");
    });
  }
  function guardForm(f) {
    if (!f || f.getAttribute(listened) === "true") return;
    f.setAttribute(listened, "true");
    f.addEventListener("submit", function (e) {
      const action = (f.getAttribute("action") || "#").trim();
      const url = (f.getAttribute("data-url") || action || "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(f.getAttribute("data-guard-msg") || "");
    });
  }
  function hookConfirm(el) {
    if (!el || el.getAttribute("data-confirm-hooked") === "true") return;
    el.setAttribute("data-confirm-hooked", "true");
    el.addEventListener("click", function (e) {
      const txt = el.getAttribute("data-confirm");
      if (!txt) return;
      e.preventDefault();
      const parts = String(txt).split("|");
      const title = parts[0] || "";
      const body = parts[1] || "";
      const yes = el.getAttribute("data-confirm-yes");
      const hasBs = !!(
        document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
        window.bootstrap
      );
      if (hasBs) {
        let modal = document.getElementById("confirm-modal");
        if (!modal) {
          const wrap = document.createElement("div");
          wrap.innerHTML =
            '<div class="modal fade" id="confirm-modal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p></p></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"></button><button type="button" class="btn btn-primary" id="confirm-yes-btn"></button></div></div></div></div>';
          document.body.appendChild(wrap.firstChild);
        }
        modal = document.getElementById("confirm-modal");
        modal.querySelector(".modal-title").textContent = title;
        modal.querySelector(".modal-body p").textContent = body;
        modal.querySelector(".modal-footer .btn-light").textContent = "Cancel";
        modal.querySelector("#confirm-yes-btn").textContent = "OK";
        const inst = bootstrap.Modal.getOrCreateInstance(modal);
        const yesBtn = modal.querySelector("#confirm-yes-btn");
        const handler = function () {
          try {
            if (yes) {
              // SECURITY: Safe handler dispatch instead of new Function()
              window.__confirmHandlers?.[yes]?.() ||
                safeFormAction(yes, yesBtn);
            }
          } catch (_) {}
          inst.hide();
        };
        yesBtn.addEventListener("click", handler, { once: true });
        inst.show();
      } else {
        if (confirm((title ? title + "\n\n" : "") + body)) {
          try {
            if (yes) {
              // SECURITY: Safe handler dispatch instead of new Function()
              window.__confirmHandlers?.[yes]?.() ||
                safeFormAction(yes, document.body);
            }
          } catch (_) {}
        }
      }
    });
  }
  // SECURITY: Safe fallback for confirm handlers instead of new Function()
  function safeFormAction(actionStr, element) {
    if (!actionStr) return;
    if (actionStr.startsWith("#") || actionStr.startsWith(".")) {
      let form = document.querySelector(actionStr);
      if (form && form.tagName === "FORM") {
        form.submit();
      }
      return;
    }
    if (
      /^(https?:\/\/|\/)/.test(actionStr) &&
      !/^javascript:/i.test(actionStr)
    ) {
      window.location.href = actionStr;
      return;
    }
  }
  function init() {
    document
      .querySelectorAll("a[data-guard-msg],a[data-url]")
      .forEach(guardLink);
    document
      .querySelectorAll("form[data-guard-msg],form[data-url]")
      .forEach(guardForm);
    document.querySelectorAll(".bs-pass-para").forEach(hookConfirm);
    try {
      document
        .querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(function (el) {
          try {
            bootstrap.Tooltip.getOrCreateInstance(el);
          } catch (_) {}
        });
    } catch (_) {}
  }
  document.addEventListener("DOMContentLoaded", function () {
    init();
    const mo = new MutationObserver(function () {
      init();
    });
    mo.observe(document.body, { childList: true, subtree: true });
  });
})();
