/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/images.js
 * @generated from original JavaScript - manual review recommended
 * @module images
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataBoundView = "data-view-images-bound";
  const dataBoundConfirm = "data-confirm-bound";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
      window.bootstrap &&
      window.bootstrap.Toast);
  const ensureToastContainer = (): void => {
    let c = qs("#np-toast-container");
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = message => {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const schedulePointerupError = message => {
    const target = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!target || target.getAttribute(dataErrGuard) === "true") {
      return;
    }
    target.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        target.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(target)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
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
    const url = el?.getAttribute?.("data-url") || "";
    const href = el
      ? el.tagName === "FORM"
        ? el.getAttribute("action") ?? ""
        : el.getAttribute("href") ?? ""
      : "";
    if (
      (!explicit || explicit === "#") &&
      (!url || url === "#") &&
      (!href || href === "#")
    ) {
      return null;
    }
    return explicit && explicit !== "#"
      ? explicit
      : url && url !== "#"
      ? url
      : href;
  };
  const initSlider = (): void => {
    try {
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      if (!$ || !window.Swiper) {
        schedulePointerupError(getMsg(document.body, "slider_unavailable"));
        return;
      }
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!$(".product-left").length) {
        return;
      }
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
      if (productSlider?.controller && productThumbs?.controller) {
        productSlider.controller.control = productThumbs;
        productThumbs.controller.control = productSlider;
      }
    } catch (_) {
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
          cb?.(res);
        },
        error: function (): void {
          schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
        },
      });
    } catch (_) {
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
          cb?.(res);
        },
        error: function (): void {
          schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
        },
      });
    } catch (_) {
      schedulePointerupError(getMsg(document.body, "ajax_unavailable"));
    }
  };
  const bindViewImages = (): void => {
    const root = document.body;
    if (root.getAttribute(dataBoundView) === "true") {
      return;
    }
    root.setAttribute(dataBoundView, "true");
    $(document).on("click.viewImages", ".view-images", function (): void {
      try {
        const explicit = "{{route('time_trackers.image.view')}}";
        const endpoint = routeFrom(this, explicit);
        if (!endpoint) {
          schedulePointerupError(getMsg(this, "img_preview_unavailable"));
          return;
        }
        const id = $(this).attr("data-id") ?? "";
        safePost(endpoint, { id: id }, function (res) {
          try {
            $(".image_sider_div").html(res ?? "");
            $("#exampleModalCenter").modal("show");
            setTimeout(function (): void {
              const total =
                $(".product-left").find(".product-slider").length | 0;
              if (total > 0) {
                initSlider();
              }
            }, 200);
          } catch (_) {
            schedulePointerupError(
              getMsg(document.body, "img_preview_unavailable")
            );
          }
        });
      } catch (_) {
        schedulePointerupError(
          getMsg(document.body, "img_preview_unavailable")
        );
      }
    });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        $(document).off("click.viewImages");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindConfirmRemove = (): void => {
    const root = document.body;
    if (root.getAttribute(dataBoundConfirm) === "true") {
      return;
    }
    root.setAttribute(dataBoundConfirm, "true");
    $(document).on(
      "click.trackRemoveStart",
      ".track-image-remove",
      function (): void {
        try {
          const rid = $(this).attr("data-pid") ?? "";
          $(".confirm_yes").addClass("image_remove").attr("image_id", rid);
          $("#cModal").modal("show");
        } catch (_) {}
      }
    );
    $(document).on(
      "click.trackRemoveConfirm",
      ".confirm_yes.image_remove",
      function (): void {
        try {
          const id = $(this).attr("image_id") ?? "";
          const explicit = "{{route('time_trackers.image.remove')}}";
          const endpoint = routeFrom(this, explicit);
          if (!endpoint) {
            schedulePointerupError(getMsg(this, "img_remove_unavailable"));
            return;
          }
          safeDelete(endpoint, { id: id }, function (res) {
            try {
              if (res?.flag) {
                $("#slide-thum-" + id).remove();
                $("#slide-" + id).remove();
                setTimeout(function (): void {
                  const total =
                    $(".product-left").find(".swiper-slide").length | 0;
                  if (total > 0) {
                    initSlider();
                  } else {
                    const msg = getMsg(document.body, "images_empty_label");
                    $(".product-left").html(
                      '<div class="no-image"><h5 class="text-muted">' +
                        (msg) +
                        "</h5></div>"
                    );
                  }
                }, 200);
              }
              $("#cModal").modal("hide");
              // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
              if (window.show_toastr) {
                window.show_toastr("error", res?.msg ?? "", "error");
              }
            } catch (_) {
              schedulePointerupError(
                getMsg(document.body, "img_remove_unavailable")
              );
            }
          });
        } catch (_) {
          schedulePointerupError(
            getMsg(document.body, "img_remove_unavailable")
          );
        }
      }
    );
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        $(document).off("click.trackRemoveStart");
        $(document).off("click.trackRemoveConfirm");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const init = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$.fn) {
      schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    bindViewImages();
    bindConfirmRemove();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
