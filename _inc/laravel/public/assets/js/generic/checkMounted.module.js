/**
 * checkMounted.module.ts — RecoveryOverlay for detecting unexpected
 * content (e.g. PHP error output) at page start.
 *
 * Mirror of public/assets/js/generic/checkMounted.module.js
 * @module generic/checkMounted.module
 */
var __classPrivateFieldGet = (this && this.__classPrivateFieldGet) || function (receiver, state, kind, f) {
    if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a getter");
    if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot read private member from an object whose class did not declare it");
    return kind === "m" ? f : kind === "a" ? f.call(receiver) : f ? f.value : state.get(receiver);
};
var __classPrivateFieldSet = (this && this.__classPrivateFieldSet) || function (receiver, state, value, kind, f) {
    if (kind === "m") throw new TypeError("Private method is not writable");
    if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a setter");
    if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot write private member to an object whose class did not declare it");
    return (kind === "a" ? f.call(receiver, value) : f ? f.value = value : state.set(receiver, value)), value;
};
const _emitted = {};
const devError = (tag, err) => {
    if (location.hostname !== "localhost" && location.hostname !== "127.0.0.1")
        return;
    const msg = err instanceof Error ? err.message : String(err);
    const key = `${tag}:${msg}`;
    if (_emitted[key])
        return;
    _emitted[key] = true;
    console.error(`[${tag}]`, msg);
};
(() => {
    "use strict";
    var _RecoveryOverlay_instances, _a, _RecoveryOverlay_OVERLAY_ID, _RecoveryOverlay_OVERFLOW_CLASS, _RecoveryOverlay_BOOTSTRAP_CSS, _RecoveryOverlay_BOOTSTRAP_ICONS, _RecoveryOverlay_BOOTSTRAP_JS, _RecoveryOverlay_ERROR_IMAGE, _RecoveryOverlay_ERROR_PATTERN, _RecoveryOverlay_DATA_INIT, _RecoveryOverlay_isModal, _RecoveryOverlay_targetElement, _RecoveryOverlay_overlay, _RecoveryOverlay_escHandler, _RecoveryOverlay_ensureDependencies, _RecoveryOverlay_ensureStylesheet, _RecoveryOverlay_ensureScript, _RecoveryOverlay_createOverlay, _RecoveryOverlay_applyOverlayStyles, _RecoveryOverlay_setAccessibilityAttributes, _RecoveryOverlay_createCard, _RecoveryOverlay_createCardBody, _RecoveryOverlay_createIcon, _RecoveryOverlay_createTitle, _RecoveryOverlay_createDescription, _RecoveryOverlay_createImage, _RecoveryOverlay_createActions, _RecoveryOverlay_createModalActions, _RecoveryOverlay_createPageActions, _RecoveryOverlay_createButton, _RecoveryOverlay_createHint, _RecoveryOverlay_attachOverlay, _RecoveryOverlay_setupEscapeHandler, _RecoveryOverlay_closeModal;
    class RecoveryOverlay {
        constructor() {
            _RecoveryOverlay_instances.add(this);
            _RecoveryOverlay_isModal.set(this, false);
            _RecoveryOverlay_targetElement.set(this, null);
            _RecoveryOverlay_overlay.set(this, null);
            _RecoveryOverlay_escHandler.set(this, null);
        }
        get isModalContext() {
            return __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f");
        }
        init() {
            if (document.body?.hasAttribute(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_DATA_INIT)))
                return;
            __classPrivateFieldSet(this, _RecoveryOverlay_isModal, !document.body || !!document.querySelector(".modal-body, .modal-header, .modal-content"), "f");
            __classPrivateFieldSet(this, _RecoveryOverlay_targetElement, __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? document.documentElement || document.querySelector("form, div") : document.body, "f");
            if (!__classPrivateFieldGet(this, _RecoveryOverlay_targetElement, "f"))
                return;
            const content = __classPrivateFieldGet(this, _RecoveryOverlay_targetElement, "f").innerHTML || "";
            if (!__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_ERROR_PATTERN).test(content))
                return;
            __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_ensureDependencies).call(this);
            if (document.getElementById(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_OVERLAY_ID)))
                return;
            document.body?.setAttribute(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_DATA_INIT), "true");
            __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createOverlay).call(this);
            __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_attachOverlay).call(this);
            __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_setupEscapeHandler).call(this);
        }
        dismiss() {
            if (__classPrivateFieldGet(this, _RecoveryOverlay_escHandler, "f")) {
                document.removeEventListener("keydown", __classPrivateFieldGet(this, _RecoveryOverlay_escHandler, "f"));
                __classPrivateFieldSet(this, _RecoveryOverlay_escHandler, null, "f");
            }
            __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f")?.remove();
            if (!__classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") && document.body.classList.contains(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_OVERFLOW_CLASS)))
                document.body.classList.remove(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_OVERFLOW_CLASS));
            document.body?.removeAttribute(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_DATA_INIT));
        }
    }
    _a = RecoveryOverlay, _RecoveryOverlay_isModal = new WeakMap(), _RecoveryOverlay_targetElement = new WeakMap(), _RecoveryOverlay_overlay = new WeakMap(), _RecoveryOverlay_escHandler = new WeakMap(), _RecoveryOverlay_instances = new WeakSet(), _RecoveryOverlay_ensureDependencies = function _RecoveryOverlay_ensureDependencies() {
        __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_ensureStylesheet).call(this, __classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_BOOTSTRAP_CSS), 'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])');
        __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_ensureStylesheet).call(this, __classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_BOOTSTRAP_ICONS), 'link[rel="stylesheet"][href*="bootstrap-icons"]');
        __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_ensureScript).call(this, __classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_BOOTSTRAP_JS), 'script[src*="bootstrap"][src*="bundle"]');
    }, _RecoveryOverlay_ensureStylesheet = function _RecoveryOverlay_ensureStylesheet(href, selector) {
        if (document.querySelector(selector))
            return;
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = href;
        (document.head || __classPrivateFieldGet(this, _RecoveryOverlay_targetElement, "f")).appendChild(link);
    }, _RecoveryOverlay_ensureScript = function _RecoveryOverlay_ensureScript(src, selector) {
        if (document.querySelector(selector))
            return;
        const script = document.createElement("script");
        script.src = src;
        script.defer = true;
        (document.head || __classPrivateFieldGet(this, _RecoveryOverlay_targetElement, "f")).appendChild(script);
    }, _RecoveryOverlay_createOverlay = function _RecoveryOverlay_createOverlay() {
        __classPrivateFieldSet(this, _RecoveryOverlay_overlay, document.createElement("div"), "f");
        __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").id = __classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_OVERLAY_ID);
        __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").tabIndex = -1;
        __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_applyOverlayStyles).call(this);
        __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_setAccessibilityAttributes).call(this);
        const card = __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createCard).call(this);
        __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").appendChild(card);
    }, _RecoveryOverlay_applyOverlayStyles = function _RecoveryOverlay_applyOverlayStyles() {
        if (!__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f"))
            return;
        const baseClass = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "position-relative w-100 d-flex align-items-center justify-content-center p-3" : "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";
        if (__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").getAttribute("class") !== baseClass)
            __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").className = baseClass;
        const bgStyle = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "rgba(255,255,255,.95)" : "rgba(0,0,0,.25)";
        if (__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").style.background !== bgStyle)
            __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").style.background = bgStyle;
        if (!__classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") && __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").style.zIndex !== "2147483000")
            __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").style.zIndex = "2147483000";
        if (__classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") && __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").style.minHeight !== "300px")
            __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").style.minHeight = "300px";
    }, _RecoveryOverlay_setAccessibilityAttributes = function _RecoveryOverlay_setAccessibilityAttributes() {
        if (!__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f"))
            return;
        const attrs = {
            role: "dialog",
            "aria-modal": "true",
            "aria-labelledby": "diagTitle",
            "aria-describedby": "diagDesc",
        };
        for (const [key, value] of Object.entries(attrs))
            if (__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").getAttribute(key) !== value)
                __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f").setAttribute(key, value);
    }, _RecoveryOverlay_createCard = function _RecoveryOverlay_createCard() {
        const card = document.createElement("div"), cardClass = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "border-0" : "card shadow-lg border-0";
        card.className = cardClass;
        card.style.maxWidth = "720px";
        card.style.width = "100%";
        card.setAttribute("role", "document");
        card.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createCardBody).call(this));
        return card;
    }, _RecoveryOverlay_createCardBody = function _RecoveryOverlay_createCardBody() {
        const body = document.createElement("div");
        body.className = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "p-3 text-center" : "card-body p-4 text-center";
        body.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createIcon).call(this));
        body.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createTitle).call(this));
        body.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createDescription).call(this));
        body.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createImage).call(this));
        body.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createActions).call(this));
        body.appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createHint).call(this));
        return body;
    }, _RecoveryOverlay_createIcon = function _RecoveryOverlay_createIcon() {
        const wrap = document.createElement("div");
        wrap.className = "mb-3";
        wrap.innerHTML = '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>';
        return wrap;
    }, _RecoveryOverlay_createTitle = function _RecoveryOverlay_createTitle() {
        const title = document.createElement("h1");
        title.id = "diagTitle";
        title.className = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "h5 fw-bold mb-2" : "h4 fw-bold mb-2";
        title.textContent = "Unexpected Output Detected";
        return title;
    }, _RecoveryOverlay_createDescription = function _RecoveryOverlay_createDescription() {
        const desc = document.createElement("p");
        desc.id = "diagDesc";
        desc.className = "text-muted mb-3";
        desc.innerHTML = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "We found unexpected content in this modal. Please try closing and reopening it." : "We found unexpected content at the start of this page. You can safely navigate using the options below.";
        return desc;
    }, _RecoveryOverlay_createImage = function _RecoveryOverlay_createImage() {
        const wrap = document.createElement("div");
        wrap.className = "my-3";
        const img = document.createElement("img");
        img.src = __classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_ERROR_IMAGE);
        img.alt = "Illustration for error/406";
        img.className = "img-fluid rounded";
        img.style.maxHeight = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "180px" : "240px";
        img.loading = "lazy";
        img.setAttribute("aria-hidden", "true");
        img.onerror = () => {
            const alert = document.createElement("div");
            alert.className = "alert alert-warning d-flex align-items-center justify-content-center gap-2 mt-3 mb-0";
            alert.setAttribute("role", "alert");
            alert.setAttribute("aria-live", "assertive");
            alert.setAttribute("aria-atomic", "true");
            alert.innerHTML = '<i class="bi bi-image-alt" aria-hidden="true"></i><div>Illustration failed to load.</div>';
            img.replaceWith(alert);
        };
        wrap.appendChild(img);
        return wrap;
    }, _RecoveryOverlay_createActions = function _RecoveryOverlay_createActions() {
        const actions = document.createElement("div");
        actions.className = "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";
        __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createModalActions).call(this, actions) : __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createPageActions).call(this, actions);
        return actions;
    }, _RecoveryOverlay_createModalActions = function _RecoveryOverlay_createModalActions(container) {
        const closeBtn = __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createButton).call(this, "btn btn-secondary", "Close this modal", "x-lg", "Close Modal");
        closeBtn.setAttribute("data-bs-dismiss", "modal");
        closeBtn.onclick = () => {
            __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_closeModal).call(this);
        };
        const reloadBtn = __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createButton).call(this, "btn btn-warning", "Reload this page", "arrow-repeat", "Reload Page");
        reloadBtn.style.color = "white";
        reloadBtn.onclick = () => {
            location.reload();
        };
        container.append(closeBtn, reloadBtn);
    }, _RecoveryOverlay_createPageActions = function _RecoveryOverlay_createPageActions(container) {
        const backBtn = __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createButton).call(this, "btn btn-primary", "Go back to previous page", "arrow-left", "Go Back");
        backBtn.onclick = () => {
            history.back();
        };
        const homeLink = document.createElement("a");
        homeLink.className = "btn btn-info";
        homeLink.style.color = "white";
        homeLink.href = "/";
        homeLink.setAttribute("aria-label", "Return to the home page");
        homeLink.innerHTML = '<i class="bi bi-house" aria-hidden="true"></i> Home';
        const reloadBtn = __classPrivateFieldGet(this, _RecoveryOverlay_instances, "m", _RecoveryOverlay_createButton).call(this, "btn btn-warning", "Reload this page", "arrow-repeat", "Reload");
        reloadBtn.style.color = "white";
        reloadBtn.onclick = () => {
            location.reload();
        };
        container.append(backBtn, homeLink, reloadBtn);
    }, _RecoveryOverlay_createButton = function _RecoveryOverlay_createButton(className, ariaLabel, iconName, text) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = className;
        btn.setAttribute("aria-label", ariaLabel);
        btn.innerHTML = `<i class="bi bi-${iconName}" aria-hidden="true"></i> ${text}`;
        return btn;
    }, _RecoveryOverlay_createHint = function _RecoveryOverlay_createHint() {
        const hint = document.createElement("p");
        hint.className = "mt-3 small text-muted";
        const message = __classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") ? "to dismiss." : "to dismiss this message.";
        hint.innerHTML = `<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> ${message}`;
        return hint;
    }, _RecoveryOverlay_attachOverlay = function _RecoveryOverlay_attachOverlay() {
        if (!__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f") || !__classPrivateFieldGet(this, _RecoveryOverlay_targetElement, "f"))
            return;
        __classPrivateFieldGet(this, _RecoveryOverlay_targetElement, "f").appendChild(__classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f"));
        if (!__classPrivateFieldGet(this, _RecoveryOverlay_isModal, "f") && !document.body.classList.contains(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_OVERFLOW_CLASS)))
            document.body.classList.add(__classPrivateFieldGet(_a, _a, "f", _RecoveryOverlay_OVERFLOW_CLASS));
        setTimeout(() => {
            __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f")?.focus();
        }, 0);
    }, _RecoveryOverlay_setupEscapeHandler = function _RecoveryOverlay_setupEscapeHandler() {
        __classPrivateFieldSet(this, _RecoveryOverlay_escHandler, (e) => {
            if (e.key !== "Escape")
                return;
            this.dismiss();
        }, "f");
        document.addEventListener("keydown", __classPrivateFieldGet(this, _RecoveryOverlay_escHandler, "f"), { once: true });
    }, _RecoveryOverlay_closeModal = function _RecoveryOverlay_closeModal() {
        try {
            const modalElement = __classPrivateFieldGet(this, _RecoveryOverlay_overlay, "f")?.closest(".modal");
            if (!modalElement)
                return this.dismiss();
            const modal = window.bootstrap?.Modal?.getInstance(modalElement);
            modal ? modal.hide() : modalElement.querySelector('[data-bs-dismiss="modal"]')?.click();
        }
        catch (err) {
            devError("RecoveryOverlay.closeModal", err);
        }
        finally {
            this.dismiss();
        }
    };
    _RecoveryOverlay_OVERLAY_ID = { value: "manual-recovery-overlay" };
    _RecoveryOverlay_OVERFLOW_CLASS = { value: "overflow-hidden" };
    _RecoveryOverlay_BOOTSTRAP_CSS = { value: "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" };
    _RecoveryOverlay_BOOTSTRAP_ICONS = { value: "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" };
    _RecoveryOverlay_BOOTSTRAP_JS = { value: "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" };
    _RecoveryOverlay_ERROR_IMAGE = { value: "/assets/images/406-art.webp" };
    _RecoveryOverlay_ERROR_PATTERN = { value: /^[\s\n\t\r]*[0-9]+</ };
    _RecoveryOverlay_DATA_INIT = { value: "data-recovery-init" };
    const initRecoveryCheck = () => {
        try {
            new RecoveryOverlay().init();
        }
        catch (err) {
            devError("RecoveryOverlay.init", err);
        }
    };
    document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", initRecoveryCheck) : initRecoveryCheck();
})();
//# sourceMappingURL=checkMounted.module.js.map