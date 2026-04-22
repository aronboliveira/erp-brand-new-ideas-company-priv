/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/createSelect.js
 * @generated from original JavaScript - manual review recommended
 * @module createSelect
 */
(() => {
  const errFb = "# ERROR",
    clientLoc = "data-client-localized",
    guardMsg = "data-guard-msg",
    langKey = "erp-np-lang";
  let errorMessage = "";
  const getMsg = (key, el) => {
    let msg = errFb;
    if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(clientLoc) === "true") {
      msg = el.getAttribute(guardMsg) || errFb;
    } else {
      let lang = (window.sessionStorage.getItem(langKey) ?? document.documentElement.lang ?? "en").toLowerCase().replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg = window.translations?.[lang]?.[key] || window.translations?.en?.[key] || errFb;
      if (msg !== errFb) {
        el.setAttribute(guardMsg, msg);
        el.setAttribute(clientLoc, "true");
      }
    }
    return msg;
  };
  const showError = message => {
    try {
      const bs = document.querySelector('link[href*="bootstrap"]');
      let c = document.getElementById("toast-container");
      if (bs && window.bootstrap) {
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = message;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };
  document.addEventListener("pointerup", () => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  });
  const initSelection = () => {
    const first = document.querySelector("input[name=type]");
    if (!first) return;
    first.checked = true;
    const radios = document.querySelectorAll('input[name="type"]');
    radios.forEach(r => {
      if (r.getAttribute("data-listener-active") === "true") return;
      r.setAttribute("data-listener-active", "true");
      r.addEventListener("change", onTypeChange);
      new MutationObserver((m, o) => {
        m.forEach(mut => {
          mut.removedNodes.forEach(n => {
            if (n === r) {
              r.removeEventListener("change", onTypeChange);
              o.disconnect();
            }
          });
        });
      }).observe(document.body, { childList: true, subtree: true });
    });
    const checkedInput = document.querySelector('input[name="type"]:checked');
    if (checkedInput) onTypeChange.call(checkedInput);
  };
  const onTypeChange = function () {
    const type = this.value;
    ["employee", "customer", "vendor"].forEach(cls => {
      document.querySelectorAll(`.${cls}`).forEach(el => {
        el.classList.toggle("d-block", cls === type);
        el.classList.toggle("d-none", cls !== type);
      });
    });
  };
  const setupAjax = type => {
    const sel = document.getElementById(type);
    if (!sel || sel.getAttribute("data-listener-active") === "true") return;
    sel.setAttribute("data-listener-active", "true");
    const detail = document.getElementById(`${type}_detail`),
      box = document.getElementById(`${type}-box`);
    if (!sel.getAttribute("data-listener-bound-change")) {
      sel.setAttribute("data-listener-bound-change", "1");
      sel.addEventListener("change", () => {
        if (detail) detail.classList.replace("d-none", "d-block");
        if (box) box.classList.replace("d-block", "d-none");
        const url = sel.getAttribute("data-url");
        if (url == null || url === "") {
          errorMessage = getMsg(`${type}_fetch_failed`, sel);
          return;
        }
        const id = sel.value;
        $.ajax({
          url,
          type: "POST",
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: { id },
        })
          .done(data => {
            if (data && detail) {
              // SECURITY: Use safe HTML insertion instead of innerHTML
              safeSethtmlContent(detail, data);
            } else if (box && detail) {
              box.classList.replace("d-none", "d-block");
              detail.classList.replace("d-block", "d-none");
            }
          })
          .fail(() => {
            errorMessage = getMsg(`${type}_fetch_failed`, sel);
          });
      });
    }
    new MutationObserver((m, o) => {
      m.forEach(mut => {
        mut.removedNodes.forEach(n => {
          if (n === sel) {
            sel.removeEventListener("change", () => {});
            o.disconnect();
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  };
  document.addEventListener("DOMContentLoaded", () => {
    try {
      initSelection();
      ["employee", "customer", "vendor"].forEach(setupAjax);
    } catch {
      if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") console.error("Initialization error");
    }
  });
  // SECURITY: Safe HTML insertion helper
  function safeSethtmlContent(el, html) {
    try {
      const parser = new DOMParser(),
        doc = parser.parseFromString(html, "text/html");
      if (doc.body.innerHTML.includes("PARSER ERROR")) {
        el.textContent = html;
        return;
      }
      while (el.firstChild) {
        el.removeChild(el.firstChild);
      }
      const fragment = document.createDocumentFragment();
      for (const node of doc.body.childNodes) {
        fragment.appendChild(node.cloneNode(true));
      }
      el.appendChild(fragment);
    } catch (_e) {
      el.textContent = html;
    }
  }
})();
//# sourceMappingURL=createSelect.js.map
