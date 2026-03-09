/**
 * @file checkMounted.js
 * @description Detects unexpected content (like PHP errors) at page start and shows recovery overlay
 * @version 3.0.0 — Removed ES2022 private class fields for broader browser compatibility
 */

(function () {
  "use strict";

  /* ── private constants (closure-scoped) ─────────────────────── */
  var _OVERLAY_ID     = "manual-recovery-overlay";
  var _OVERFLOW_CLASS = "overflow-hidden";
  var _BOOTSTRAP_CSS  = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css";
  var _BOOTSTRAP_ICONS = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css";
  var _BOOTSTRAP_JS   = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js";
  var _ERROR_IMAGE     = "/assets/images/406-art.webp";
  var _ERROR_PATTERN   = /^[\s\n\t\r]*[0-9]+</;
  var _DATA_INIT       = "data-recovery-init";

  /* ── WeakMap for per-instance private state ─────────────────── */
  var _priv = new WeakMap();

  function _p(self) {
    if (!_priv.has(self)) _priv.set(self, {});
    return _priv.get(self);
  }

  /**
   * Recovery overlay manager for detecting and handling malformed page content
   * @class RecoveryOverlay
   */
  function RecoveryOverlay() {
    var p = _p(this);
    p.isModal       = false;
    p.targetElement = null;
    p.overlay       = null;
    p.escHandler    = null;
  }

  Object.defineProperty(RecoveryOverlay.prototype, "isModalContext", {
    get: function () { return _p(this).isModal; }
  });

  /** Initialize and run the check */
  RecoveryOverlay.prototype.init = function () {
    if (document.body && document.body.hasAttribute(_DATA_INIT)) return;

    var p = _p(this);
    p.isModal = !document.body || !!document.querySelector(".modal-body, .modal-header, .modal-content");
    p.targetElement = p.isModal
      ? document.documentElement || document.querySelector("form, div")
      : document.body;

    if (!p.targetElement) return;

    var content = p.targetElement.innerHTML || "";
    if (!_ERROR_PATTERN.test(content)) return;

    this._ensureDependencies();

    if (document.getElementById(_OVERLAY_ID)) return;

    if (document.body) document.body.setAttribute(_DATA_INIT, "true");
    this._createOverlay();
    this._attachOverlay();
    this._setupEscapeHandler();
  };

  /** Ensure CSS/JS dependencies are loaded */
  RecoveryOverlay.prototype._ensureDependencies = function () {
    this._ensureStylesheet(
      _BOOTSTRAP_CSS,
      'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])'
    );
    this._ensureStylesheet(
      _BOOTSTRAP_ICONS,
      'link[rel="stylesheet"][href*="bootstrap-icons"]'
    );
    this._ensureScript(
      _BOOTSTRAP_JS,
      'script[src*="bootstrap"][src*="bundle"]'
    );
  };

  RecoveryOverlay.prototype._ensureStylesheet = function (href, selector) {
    if (document.querySelector(selector)) return;
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = href;
    (document.head || _p(this).targetElement).appendChild(link);
  };

  RecoveryOverlay.prototype._ensureScript = function (src, selector) {
    if (document.querySelector(selector)) return;
    var script = document.createElement("script");
    script.src = src;
    script.defer = true;
    (document.head || _p(this).targetElement).appendChild(script);
  };

  RecoveryOverlay.prototype._createOverlay = function () {
    var p = _p(this);
    p.overlay = document.createElement("div");
    p.overlay.id = _OVERLAY_ID;
    p.overlay.tabIndex = -1;
    this._applyOverlayStyles();
    this._setAccessibilityAttributes();
    var card = this._createCard();
    p.overlay.appendChild(card);
  };

  RecoveryOverlay.prototype._applyOverlayStyles = function () {
    var p = _p(this);
    if (!p.overlay) return;
    var baseClass = p.isModal
      ? "position-relative w-100 d-flex align-items-center justify-content-center p-3"
      : "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";
    if (p.overlay.getAttribute("class") !== baseClass) p.overlay.className = baseClass;
    var bgStyle = p.isModal ? "rgba(255,255,255,.95)" : "rgba(0,0,0,.25)";
    if (p.overlay.style.background !== bgStyle) p.overlay.style.background = bgStyle;
    if (!p.isModal && p.overlay.style.zIndex !== "2147483000") p.overlay.style.zIndex = "2147483000";
    if (p.isModal && p.overlay.style.minHeight !== "300px") p.overlay.style.minHeight = "300px";
  };

  RecoveryOverlay.prototype._setAccessibilityAttributes = function () {
    var p = _p(this);
    if (!p.overlay) return;
    var attrs = { role: "dialog", "aria-modal": "true", "aria-labelledby": "diagTitle", "aria-describedby": "diagDesc" };
    for (var key in attrs) {
      if (attrs.hasOwnProperty(key) && p.overlay.getAttribute(key) !== attrs[key])
        p.overlay.setAttribute(key, attrs[key]);
    }
  };

  RecoveryOverlay.prototype._createCard = function () {
    var p = _p(this);
    var card = document.createElement("div");
    card.className = p.isModal ? "border-0" : "card shadow-lg border-0";
    card.style.maxWidth = "720px";
    card.style.width = "100%";
    card.setAttribute("role", "document");
    card.appendChild(this._createCardBody());
    return card;
  };

  RecoveryOverlay.prototype._createCardBody = function () {
    var p = _p(this);
    var body = document.createElement("div");
    body.className = p.isModal ? "p-3 text-center" : "card-body p-4 text-center";
    body.appendChild(this._createIcon());
    body.appendChild(this._createTitle());
    body.appendChild(this._createDescription());
    body.appendChild(this._createImage());
    body.appendChild(this._createActions());
    body.appendChild(this._createHint());
    return body;
  };

  RecoveryOverlay.prototype._createIcon = function () {
    var wrap = document.createElement("div");
    wrap.className = "mb-3";
    wrap.innerHTML = '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>';
    return wrap;
  };

  RecoveryOverlay.prototype._createTitle = function () {
    var title = document.createElement("h1");
    title.id = "diagTitle";
    title.className = _p(this).isModal ? "h5 fw-bold mb-2" : "h4 fw-bold mb-2";
    title.textContent = "Unexpected Output Detected";
    return title;
  };

  RecoveryOverlay.prototype._createDescription = function () {
    var desc = document.createElement("p");
    desc.id = "diagDesc";
    desc.className = "text-muted mb-3";
    desc.innerHTML = _p(this).isModal
      ? "We found unexpected content in this modal. Please try closing and reopening it."
      : "We found unexpected content at the start of this page. You can safely navigate using the options below.";
    return desc;
  };

  RecoveryOverlay.prototype._createImage = function () {
    var p = _p(this);
    var wrap = document.createElement("div");
    wrap.className = "my-3";
    var img = document.createElement("img");
    img.src = _ERROR_IMAGE;
    img.alt = "Illustration for error/406";
    img.className = "img-fluid rounded";
    img.style.maxHeight = p.isModal ? "180px" : "240px";
    img.loading = "lazy";
    img.setAttribute("aria-hidden", "true");
    img.onerror = function () {
      var alert = document.createElement("div");
      alert.className = "alert alert-warning d-flex align-items-center justify-content-center gap-2 mt-3 mb-0";
      alert.setAttribute("role", "alert");
      alert.setAttribute("aria-live", "assertive");
      alert.setAttribute("aria-atomic", "true");
      alert.innerHTML = '<i class="bi bi-image-alt" aria-hidden="true"></i><div>Illustration failed to load.</div>';
      img.replaceWith(alert);
    };
    wrap.appendChild(img);
    return wrap;
  };

  RecoveryOverlay.prototype._createActions = function () {
    var actions = document.createElement("div");
    actions.className = "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";
    _p(this).isModal ? this._createModalActions(actions) : this._createPageActions(actions);
    return actions;
  };

  RecoveryOverlay.prototype._createModalActions = function (container) {
    var self = this;
    var closeBtn = this._createButton("btn btn-secondary", "Close this modal", "x-lg", "Close Modal");
    closeBtn.setAttribute("data-bs-dismiss", "modal");
    closeBtn.onclick = function () { self._closeModal(); };
    var reloadBtn = this._createButton("btn btn-warning", "Reload this page", "arrow-repeat", "Reload Page");
    reloadBtn.style.color = "white";
    reloadBtn.onclick = function () { location.reload(); };
    container.append(closeBtn, reloadBtn);
  };

  RecoveryOverlay.prototype._createPageActions = function (container) {
    var backBtn = this._createButton("btn btn-primary", "Go back to previous page", "arrow-left", "Go Back");
    backBtn.onclick = function () { history.back(); };
    var homeLink = document.createElement("a");
    homeLink.className = "btn btn-info";
    homeLink.style.color = "white";
    homeLink.href = "/";
    homeLink.setAttribute("aria-label", "Return to the home page");
    homeLink.innerHTML = '<i class="bi bi-house" aria-hidden="true"></i> Home';
    var reloadBtn = this._createButton("btn btn-warning", "Reload this page", "arrow-repeat", "Reload");
    reloadBtn.style.color = "white";
    reloadBtn.onclick = function () { location.reload(); };
    container.append(backBtn, homeLink, reloadBtn);
  };

  RecoveryOverlay.prototype._createButton = function (className, ariaLabel, iconName, text) {
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = className;
    btn.setAttribute("aria-label", ariaLabel);
    btn.innerHTML = '<i class="bi bi-' + iconName + '" aria-hidden="true"></i> ' + text;
    return btn;
  };

  RecoveryOverlay.prototype._createHint = function () {
    var hint = document.createElement("p");
    hint.className = "mt-3 small text-muted";
    var message = _p(this).isModal ? "to dismiss." : "to dismiss this message.";
    hint.innerHTML = '<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> ' + message;
    return hint;
  };

  RecoveryOverlay.prototype._attachOverlay = function () {
    var p = _p(this);
    if (!p.overlay || !p.targetElement) return;
    p.targetElement.appendChild(p.overlay);
    if (!p.isModal && document.body && !document.body.classList.contains(_OVERFLOW_CLASS))
      document.body.classList.add(_OVERFLOW_CLASS);
    setTimeout(function () { if (p.overlay) p.overlay.focus(); }, 0);
  };

  RecoveryOverlay.prototype._setupEscapeHandler = function () {
    var self = this;
    var p = _p(this);
    p.escHandler = function (e) {
      if (e.key !== "Escape") return;
      self.dismiss();
    };
    document.addEventListener("keydown", p.escHandler, { once: true });
  };

  /** Dismiss the overlay */
  RecoveryOverlay.prototype.dismiss = function () {
    var p = _p(this);
    if (p.escHandler) {
      document.removeEventListener("keydown", p.escHandler);
      p.escHandler = null;
    }
    if (p.overlay) p.overlay.remove();
    if (!p.isModal && document.body && document.body.classList.contains(_OVERFLOW_CLASS))
      document.body.classList.remove(_OVERFLOW_CLASS);
    if (document.body) document.body.removeAttribute(_DATA_INIT);
  };

  RecoveryOverlay.prototype._closeModal = function () {
    var p = _p(this);
    try {
      var modalElement = p.overlay ? p.overlay.closest(".modal") : null;
      if (!modalElement) return this.dismiss();
      var modal = window.bootstrap && window.bootstrap.Modal
        ? window.bootstrap.Modal.getInstance(modalElement) : null;
      if (modal) {
        modal.hide();
      } else {
        var dismissBtn = modalElement.querySelector('[data-bs-dismiss="modal"]');
        if (dismissBtn) dismissBtn.click();
      }
    } catch (err) {
      console.error("[RecoveryOverlay] Error closing modal:", err);
    } finally {
      this.dismiss();
    }
  };

  /* ── Bootstrap ──────────────────────────────────────────────── */
  var initRecoveryCheck = function () {
    try {
      new RecoveryOverlay().init();
    } catch (err) {
      console.error("[RecoveryOverlay] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initRecoveryCheck)
    : initRecoveryCheck();
})();
