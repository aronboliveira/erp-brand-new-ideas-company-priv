/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/view.js
 * @generated from original JavaScript - manual review recommended
 * @module view
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const L = "data-guard-listener";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const ERR = "# ERROR";
  const NS = ".detailGuards";
  function hasBootstrapCss() {
    try {
      return !!document.querySelector(
        'link[rel~="stylesheet"][href*="bootstrap"]'
      );
    } catch (_) {
      return false;
    }
  }
  function toast(msg) {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
      if (hasBootstrapCss() && window.bootstrap && window.bootstrap.Toast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = msg;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    } catch (_) {
      alert(msg);
    }
  }
  function getMsg(el, key) {
    try {
      let msg = ERR;
      if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true")
        msg = el.getAttribute(DGM) || ERR;
      else {
        let lang = (
          // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
          window.sessionStorage.getItem("erp-np-lang") ??
          document.documentElement.lang ?? "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[key] ||
          el.getAttribute(DGM) ||
          window.translations?.en?.[key] ||
          ERR;
        if (msg !== ERR) {
          el.setAttribute(DGM, msg);
          el.setAttribute(DCL, "true");
        }
      }
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      return msg || ERR;
    } catch (_) {
      return ERR;
    }
  }
  function bindLink(a) {
    if (!a || a.getAttribute(L) === "true") return;
    a.setAttribute(L, "true");
    const $a = window.jQuery(a);
    const handler = function (e) {
      try {
        const url = a.getAttribute("data-url");
        const href = a.href;
        if ((!url || url === "#") && (!href || href === "#")) {
          e.preventDefault();
          toast(getMsg(a, "action_unavailable"));
        }
      } catch (_) {
        e.preventDefault();
        toast(getMsg(a, "action_unavailable"));
      }
    };
    $a.on("click" + NS, handler);
    const obs = new MutationObserver(function (): void {
      if (!document.body.contains(a)) {
        try {
          $a.off("click" + NS);
        } catch (_) {}
        obs.disconnect();
      }
    });
    try {
      obs.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}
  }
  try {
    const $ = window.jQuery;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery not found for detailGuards");
      } catch (_) {}
      return;
    }
    $(function (): void {
      try {
        const links = document.querySelectorAll(
          "a[data-guard-msg], a[data-url]"
        );
        links.forEach(function (el) {
          bindLink(el);
        });
      } catch (_) {}
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to initialize detailGuards");
    } catch (__) {}
  }
})();

export {};
