/**
 * @fileoverview TypeScript version of public/assets/js/generic/checkMounted.js
 * @generated from original JavaScript - manual review recommended
 * @module checkMounted
 */

/* global bootstrap */
(() => {
  const checkMounted = () => {
    const isModal = !document.body || document.querySelector(".modal-body, .modal-header, .modal-content"),
      targetElement = isModal ? document.documentElement || document.querySelector("form, div") : document.body;
    if (!targetElement) return;
    const content = targetElement.innerHTML || "",
      m = content.match(/^[\s\n\t\r]*[0-9]+</);
    if (!m) return;
    const ensureCss = (href, selector) => {
      if (!document.querySelector(selector)) {
        const l = document.createElement("link");
        l.rel = "stylesheet";
        l.href = href;
        const appendTarget = document.head || targetElement;
        appendTarget.appendChild(l);
      }
    };
    const ensureJs = (src, selector) => {
      if (!document.querySelector(selector)) {
        const s = document.createElement("script");
        s.src = src;
        s.defer = true;
        const appendTarget = document.head || targetElement;
        appendTarget.appendChild(s);
      }
    };
    ensureCss("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css", 'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])');
    ensureCss("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css", 'link[rel="stylesheet"][href*="bootstrap-icons"]');
    ensureJs("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js", 'script[src*="bootstrap"][src*="bundle"]');
    if (document.getElementById("manual-recovery-overlay")) return;
    const overlay = document.createElement("div");
    overlay.id = "manual-recovery-overlay";
    if (isModal) {
      overlay.className = "position-relative w-100 d-flex align-items-center justify-content-center p-3";
      Object.assign(overlay.style, {
        background: "rgba(255,255,255,.95)",
        minHeight: "300px",
      });
    } else {
      overlay.className = "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";
      Object.assign(overlay.style, {
        zIndex: "2147483000",
        background: "rgba(0,0,0,.25)",
      });
    }
    for (const [k, v] of Object.entries({
      role: "dialog",
      "aria-modal": "true",
      "aria-labelledby": "diagTitle",
      "aria-describedby": "diagDesc",
    }))
      overlay.setAttribute(k, v);
    overlay.tabIndex = -1;
    const card = document.createElement("div");
    card.className = isModal ? "border-0" : "card shadow-lg border-0";
    Object.assign(card.style, { maxWidth: "720px", width: "100%" });
    card.setAttribute("role", "document");
    const cardBody = document.createElement("div");
    cardBody.className = isModal ? "p-3 text-center" : "card-body p-4 text-center";
    const iconWrap = document.createElement("div");
    iconWrap.className = "mb-3";
    iconWrap.innerHTML = '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>';
    const title = document.createElement("h1");
    for (const [k, v] of Object.entries({
      id: "diagTitle",
      className: isModal ? "h5 fw-bold mb-2" : "h4 fw-bold mb-2",
      textContent: "Unexpected Output Detected",
    }))
      title[k] = v;
    const desc = document.createElement("p");
    desc.id = "diagDesc";
    desc.className = "text-muted mb-3";
    desc.innerHTML = isModal ? "We found unexpected content in this modal. Please try closing and reopening it." : "We found unexpected content at the start of this page. You can safely navigate using the options below.";
    const imgWrap = document.createElement("div");
    imgWrap.className = "my-3";
    const img = document.createElement("img");
    for (const [k, v] of Object.entries({
      src: "/assets/images/406-art.webp",
      alt: "Illustration for error/406",
      className: "img-fluid rounded",
    }))
      img[k] = v;
    img.style.maxHeight = isModal ? "180px" : "240px";
    img.loading = "lazy";
    img.setAttribute("aria-hidden", "true");
    img.onerror = function () {
      const alert = document.createElement("div");
      alert.className = "alert alert-warning d-flex align-items-center justify-content-center gap-2 mt-3 mb-0";
      for (const [k, v] of Object.entries({
        role: "alert",
        "aria-live": "assertive",
        "aria-atomic": "true",
      }))
        alert.setAttribute(k, v);
      alert.innerHTML = '<i class="bi bi-image-alt" aria-hidden="true"></i><div>Illustration failed to load.</div>';
      this.replaceWith(alert);
    };
    imgWrap.appendChild(img);
    const actions = document.createElement("div");
    actions.className = "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";
    if (isModal) {
      const closeBtn = document.createElement("button");
      closeBtn.type = "button";
      closeBtn.className = "btn btn-secondary";
      closeBtn.setAttribute("data-bs-dismiss", "modal");
      closeBtn.setAttribute("aria-label", "Close this modal");
      closeBtn.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i> Close Modal';
      closeBtn.onclick = () => {
        const modalElement = overlay.closest(".modal");
        if (modalElement) {
          const modal = bootstrap.Modal.getInstance(modalElement);
          if (modal) modal.hide();
          else modalElement.querySelector('[data-bs-dismiss="modal"]')?.click();
        }
        overlay.remove();
      };
      const reloadBtn = document.createElement("button");
      reloadBtn.type = "button";
      reloadBtn.className = "btn btn-warning";
      reloadBtn.style.color = "white";
      reloadBtn.setAttribute("aria-label", "Reload this page");
      reloadBtn.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Reload Page';
      reloadBtn.onclick = () => {
        location.reload();
      };
      actions.append(closeBtn, reloadBtn);
    } else {
      const backBtn = document.createElement("button");
      backBtn.type = "button";
      backBtn.className = "btn btn-primary";
      backBtn.setAttribute("aria-label", "Go back to previous page");
      backBtn.innerHTML = '<i class="bi bi-arrow-left" aria-hidden="true"></i> Go Back';
      backBtn.onclick = () => {
        history.back();
      };
      const homeA = document.createElement("a");
      homeA.className = "btn btn-info";
      homeA.style.color = "white";
      homeA.href = "/";
      homeA.setAttribute("aria-label", "Return to the home page");
      homeA.innerHTML = '<i class="bi bi-house" aria-hidden="true"></i> Home';
      const reloadBtn = document.createElement("button");
      reloadBtn.type = "button";
      reloadBtn.style.color = "white";
      reloadBtn.className = "btn btn-warning";
      reloadBtn.setAttribute("aria-label", "Reload this page");
      reloadBtn.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Reload';
      reloadBtn.onclick = () => {
        location.reload();
      };
      actions.append(backBtn, homeA, reloadBtn);
    }
    const hint = document.createElement("p");
    hint.className = "mt-3 small text-muted";
    hint.innerHTML = isModal ? '<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> to dismiss.' : '<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> to dismiss this message.';
    cardBody.append(iconWrap, title, desc, imgWrap, actions, hint);
    for (const [p, c] of [
      [card, cardBody],
      [overlay, card],
      [targetElement, overlay],
    ])
      p.appendChild(c);
    if (!isModal) document.body.classList.add("overflow-hidden");
    setTimeout(() => {
      overlay.focus();
    }, 0);
    const onEsc = e => {
      if (e.key === "Escape") {
        document.removeEventListener("keydown", onEsc);
        overlay.remove();
        if (!isModal) document.body.classList.remove("overflow-hidden");
      }
    };
    document.addEventListener("keydown", onEsc, { once: true });
  };
  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", checkMounted) : checkMounted();
})();
//# sourceMappingURL=checkMounted.js.map
