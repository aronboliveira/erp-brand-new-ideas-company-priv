(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/images.js
 * @generated from original JavaScript - manual review recommended
 * @module images
 */



(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-error-guard", dataBoundView = "data-view-images-bound", dataBoundConfirm = "data-confirm-bound";
    const qs = (s, r = document) => r.querySelector(s);

    const hasBootstrap = () => qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        (qs('link[href*="bootstrap"]') && window.bootstrap.Toast);
    const ensureToastContainer = () => {
        let c = qs("#np-toast-container");
        if (c)
            return c;
        c = document.createElement("div");
        c.id = "np-toast-container";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        if (hasBootstrap()) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    t.setAttribute(k, v);
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = qs(".toast-body", t);
            if (body)
                body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            }
            catch (_) {
                alert(message ?? errFb);
            }
        }
        else {
            alert(message ?? errFb);
        }
    };
    const schedulePointerupError = (message) => {
        const target = document.body;
        if (!target || target.getAttribute(dataErrGuard) === "true")
            return;
        target.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                target.removeAttribute(dataErrGuard);
            }
        };
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(target)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };

    const getMsg = (el, key) => {
        let msg = errFb;
        if (el.getAttribute(dataSvLocalized) === "true" ||
            el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const routeFrom = (el, explicit) => {
        const url = el.getAttribute("data-url") || "", href = el
            ? el.tagName === "FORM"
                ? (el.getAttribute("action") ?? "")
                : (el.getAttribute("href") ?? "")
            : "";
        if ((!explicit || explicit === "#") &&
            (!url || url === "#") &&
            (!href || href === "#"))
            return null;
        return explicit && explicit !== "#"
            ? explicit
            : url && url !== "#"
                ? url
                : href;
    };
    const initSlider = () => {
        try {
            if (!$ || !window.Swiper) {
                schedulePointerupError(getMsg(document.body, "slider_unavailable"));
                return;
            }
            if (!$(".product-left").length)
                return;
            const productSlider = new window.Swiper(".product-slider", {
                spaceBetween: 0,
                centeredSlides: false,
                loop: false,
                direction: "horizontal",
                loopedSlides: 5,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                resizeObserver: true,
            });
            const productThumbs = new window.Swiper(".product-thumbs", {
                spaceBetween: 0,
                centeredSlides: true,
                loop: false,
                slideToClickedSlide: true,
                direction: "horizontal",
                slidesPerView: 7,
                loopedSlides: 5,
            });
            if (productSlider.controller && productThumbs.controller) {
                productSlider.controller.control = productThumbs;
                productThumbs.controller.control = productSlider;
            }
        }
        catch (_) {
            schedulePointerupError(getMsg(document.body, "slider_unavailable"));
        }
    };
    const safePost = (url, data, cb) => {
        try {
            if (typeof window.postAjax === "function") {
                window.postAjax(url, data, cb);
                return;
            }
            $.ajax({
                url: url,
                method: "POST",
                data: data,
                cache: false,
                success: function (res) {
                    cb(res);
                },
                error: function () {
                    schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
                },
            });
        }
        catch (_) {
            schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
        }
    };
    const safeDelete = (url, data, cb) => {
        try {
            if (typeof window.deleteAjax === "function") {
                window.deleteAjax(url, data, cb);
                return;
            }
            $.ajax({
                url: url,
                method: "DELETE",
                data: data,
                cache: false,
                success: function (res) {
                    cb(res);
                },
                error: function () {
                    schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
                },
            });
        }
        catch (_) {
            schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
        }
    };
    const bindViewImages = () => {
        const root = document.body;
        if (root.getAttribute(dataBoundView) === "true")
            return;
        root.setAttribute(dataBoundView, "true");
        $(document).on("click.viewImages", ".view-images", function () {
            const el = this;
            try {
                const explicit = "{{route('time_trackers.image.view')}}", endpoint = routeFrom(el, explicit);
                if (!endpoint) {
                    schedulePointerupError(getMsg(el, "img_preview_unavailable"));
                    return;
                }
                const id = $(el).attr("data-id") ?? "";
                safePost(endpoint, { id: id }, function (res) {
                    try {
                        $(".image_sider_div").html(String(res ?? ""));
                        $("#exampleModalCenter").modal("show");
                        setTimeout(function () {
                            const total = $(".product-left").find(".product-slider").length | 0;
                            if (total > 0)
                                initSlider();
                        }, 200);
                    }
                    catch (_) {
                        schedulePointerupError(getMsg(document.body, "img_preview_unavailable"));
                    }
                });
            }
            catch (_) {
                schedulePointerupError(getMsg(document.body, "img_preview_unavailable"));
            }
        });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(root)) {
                $(document).off("click.viewImages");
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const bindConfirmRemove = () => {
        const root = document.body;
        if (root.getAttribute(dataBoundConfirm) === "true")
            return;
        root.setAttribute(dataBoundConfirm, "true");
        $(document).on("click.trackRemoveStart", ".track-image-remove", function () {
            const el = this;
            try {
                const rid = $(el).attr("data-pid") ?? "";
                $(".confirm_yes").addClass("image_remove").attr("image_id", rid);
                $("#cModal").modal("show");
            }
            catch (_) {
                console.error(`[images] Error:`, _);
            }
        });
        $(document).on("click.trackRemoveConfirm", ".confirm_yes.image_remove", function () {
            const el = this;
            try {
                const id = $(el).attr("image_id") ?? "", endpoint = routeFrom(el, "{{route('time_trackers.image.remove')}}");
                if (!endpoint) {
                    schedulePointerupError(getMsg(el, "img_remove_unavailable"));
                    return;
                }
                safeDelete(endpoint, { id: id }, function (res) {
                    try {
                        const response = res;
                        if (response?.flag) {
                            $("#slide-thum-" + id).remove();
                            $("#slide-" + id).remove();
                            setTimeout(function () {
                                const total = $(".product-left").find(".swiper-slide").length | 0;
                                if (total > 0) {
                                    initSlider();
                                }
                                else {
                                    const msg = getMsg(document.body, "images_empty_label");
                                    $(".product-left").html('<div class="no-image"><h5 class="text-muted">' +
                                        msg +
                                        "</h5></div>");
                                }
                            }, 200);
                        }
                        $("#cModal").modal("hide");
                        if (window.show_toastr)
                            window.show_toastr("error", response?.msg ?? "", "error");
                    }
                    catch (_) {
                        schedulePointerupError(getMsg(document.body, "img_remove_unavailable"));
                    }
                });
            }
            catch (_) {
                schedulePointerupError(getMsg(document.body, "img_remove_unavailable"));
            }
        });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(root)) {
                $(document).off("click.trackRemoveStart");
                $(document).off("click.trackRemoveConfirm");
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const init = () => {
        if (!$.fn) {
            schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
            return;
        }
        bindViewImages();
        bindConfirmRemove();
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
})();