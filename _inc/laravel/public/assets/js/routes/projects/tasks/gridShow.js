(function () {
  function toast(msg) {
    var m =
      msg ||
      "Requested route is unavailable. Please contact technical support or your domain administrator.";
    var hasBootstrap =
      typeof window.bootstrap !== "undefined" &&
      typeof window.bootstrap.Toast !== "undefined";
    if (hasBootstrap) {
      var box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        box.style.position = "fixed";
        box.style.zIndex = "1080";
        box.style.right = "1rem";
        box.style.bottom = "1rem";
        document.body.appendChild(box);
      }
      var t = document.createElement("div");
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      t.innerHTML = '<div class="toast-body"></div>';
      t.querySelector(".toast-body").textContent = m;
      box.appendChild(t);
      window.bootstrap.Toast.getOrCreateInstance(t, { delay: 3000 }).show();
    } else {
      alert(m);
    }
  }

  function disabledUrl(a) {
    var href = (a.getAttribute("href") || "").trim();
    var url = (a.getAttribute("data-url") || href || "").trim();
    if (!url || url === "#" || href === "#") return true;
    try {
      new URL(url, window.location.origin);
      return false;
    } catch (e) {
      return true;
    }
  }

  function guard(el) {
    if (!el || el.dataset.guardBound === "1") return;
    el.dataset.guardBound = "1";
    el.addEventListener("click", function (e) {
      if (disabledUrl(el)) {
        e.preventDefault();
        toast(el.getAttribute("data-guard-msg"));
      }
    });
    el.addEventListener("keydown", function (e) {
      if ((e.key === "Enter" || e.key === " ") && disabledUrl(el)) {
        e.preventDefault();
        toast(el.getAttribute("data-guard-msg"));
      }
    });
  }

  function bind() {
    document.querySelectorAll("a.project-task-index-link").forEach(guard);
    document.querySelectorAll(".card-progress").forEach(function (card) {
      if (card.dataset.cardBound === "1") return;
      card.dataset.cardBound = "1";
      card.addEventListener("click", function (e) {
        var target = e.target;
        if (
          target.closest(
            'a,button,input,textarea,select,[role="button"],[data-ajax-popup]'
          )
        )
          return;
        var link = card.querySelector("a.project-task-index-link");
        if (!link) return;
        if (disabledUrl(link)) {
          e.preventDefault();
          toast(link.getAttribute("data-guard-msg"));
          return;
        }
        var url = (
          link.getAttribute("data-url") ||
          link.getAttribute("href") ||
          "#"
        ).trim();
        if (url && url !== "#") window.location.assign(url);
      });
    });
    if (
      window.bootstrap &&
      document.querySelector('[data-bs-toggle="tooltip"]')
    ) {
      [].slice
        .call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .forEach(function (el) {
          window.bootstrap.Tooltip.getOrCreateInstance(el);
        });
    }
  }

  function observe() {
    if (!("MutationObserver" in window)) return;
    var mo = new MutationObserver(function (muts) {
      for (var i = 0; i < muts.length; i++) {
        if (muts[i].addedNodes && muts[i].addedNodes.length) {
          bind();
          break;
        }
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  function init() {
    bind();
    observe();
  }

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", init);
  else init();
})();
