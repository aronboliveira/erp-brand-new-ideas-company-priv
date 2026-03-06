/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/barcodeSetting.js
 * @generated from original JavaScript - manual review recommended
 * @module barcodeSetting
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const L = "data-guard-listener";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const ERR = "# ERROR";
  const map = new WeakMap();
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
        const dict = window.translations || {};
        msg =
          dict?.[lang]?.[key] ||
          el.getAttribute(DGM) ||
          dict?.en?.[key] ||
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
  function bindForm($f) {
    const f = $f.get(0);
    if (!f || f.getAttribute(L) === "true") return;
    f.setAttribute(L, "true");
    const handler = function (e) {
      try {
        const url = f.getAttribute("data-url");
        const href = f.action;
        if ((!url || url === "#") && (!href || href === "#")) {
          e.preventDefault();
          toast(getMsg(f, "action_unavailable"));
        }
      } catch (_) {
        e.preventDefault();
        toast(getMsg(f, "action_unavailable"));
      }
    };
    $f.on("submit.formGuard", handler);
    map.set(f, handler);
  }
  function unbindForm(f) {
    try {
      if (!f) return;
      const $f = window.jQuery(f);
      $f.off("submit.formGuard");
      f.removeAttribute(L);
      map.delete(f);
    } catch (_) {}
  }
  function observeRemoval(f) {
    try {
      const obs = new MutationObserver(function (): void {
        if (!document.body.contains(f)) {
          unbindForm(f);
          obs.disconnect();
        }
      });
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
          console.error("jQuery not found for formGuard");
      } catch (_) {}
      return;
    }
    $(function (): void {
      try {
        const $forms = $("form[data-guard-msg], form[data-url]");
        $forms.each(function (): void {
          const $f = $(this);
          bindForm($f);
          observeRemoval($f.get(0));
        });
      } catch (_) {}
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to initialize formGuard");
    } catch (__) {}
  }
  (function (): void {
    const L = "data-listener-active";
    const NS = ".barcodeSetting";
    function bindSelect($s) {
      const el = $s.get(0);
      if (!el || el.getAttribute(L) === "true") return;
      el.setAttribute(L, "true");
      if (!el.value && el.options?.length) el.selectedIndex = 0;
      $s.on("change" + NS, function (): void {
        try {
          const v = $s.val();
          if (v == null) return;
          el.setAttribute("data-has-selection", String(v !== ""));
        } catch (_) {}
      });
    }
    function unbindSelect(el) {
      try {
        if (!el) return;
        const $s = window.jQuery(el);
        $s.off("change" + NS);
        el.removeAttribute(L);
      } catch (_) {}
    }
    function observeRemoval(nodeList) {
      try {
        const obs = new MutationObserver(function (): void {
          nodeList.forEach(function (el) {
            if (!document.body.contains(el)) {
              unbindSelect(el);
            }
          });
        });
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
            console.error("jQuery not found for barcodeSetting");
        } catch (_) {}
        return;
      }
      $(function (): void {
        try {
          const form = document.getElementById("pos-barcode-setting-form");
          if (!form) return;
          const selects = form.querySelectorAll('select[data-toggle="select"]');
          const nodes = [];
          selects.forEach(function (s) {
            const $s = $(s);
            bindSelect($s);
            nodes.push(s);
          });
          observeRemoval(nodes);
        } catch (_) {}
      });
    } catch (_) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Failed to initialize barcodeSetting");
      } catch (__) {}
    }
  })();
})();

export {};
