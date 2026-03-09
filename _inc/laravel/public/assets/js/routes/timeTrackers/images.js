/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;

  const dataBoundView = "data-view-images-bound";
  const dataBoundConfirm = "data-confirm-bound";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') &&
      window.bootstrap &&
      window.bootstrap.Toast);
  const routeFrom = (el, explicit) => {
    const url = el?.getAttribute?.("data-url") || "";
    const href = el
      ? el.tagName === "FORM"
        ? el.getAttribute("action") || ""
        : el.getAttribute("href") || ""
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
  const initSlider = () => {
    try {
      if (!$ || !window.Swiper) {
        guard.scheduleInteractiveError(guard.getMsg("slider_unavailable"));
        return;
      }
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
      guard.scheduleInteractiveError(guard.getMsg("slider_unavailable"));
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
          cb && cb(res);
        },
        error: function () {
          guard.scheduleInteractiveError(guard.getMsg("ajax_unavailable"));
        },
      });
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("ajax_unavailable"));
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
          cb && cb(res);
        },
        error: function () {
          guard.scheduleInteractiveError(guard.getMsg("ajax_unavailable"));
        },
      });
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("ajax_unavailable"));
    }
  };
  const bindViewImages = () => {
    const root = document.body;
    if (root.getAttribute(dataBoundView) === "true") {
      return;
    }
    root.setAttribute(dataBoundView, "true");
    $(document).on("click.viewImages", ".view-images", function () {
      try {
        const explicit = "{{route('time_trackers.image.view')}}";
        const endpoint = routeFrom(this, explicit);
        if (!endpoint) {
          guard.scheduleInteractiveError(guard.getMsg("img_preview_unavailable"));
          return;
        }
        const id = $(this).attr("data-id") ?? "";
        safePost(endpoint, { id: id }, function (res) {
          try {
            $(".image_sider_div").html(res ?? "");
            $("#exampleModalCenter").modal("show");
            setTimeout(function () {
              const total =
                $(".product-left").find(".product-slider").length | 0;
              if (total > 0) {
                initSlider();
              }
            }, 200);
          } catch (_) {
            guard.scheduleInteractiveError(
              guard.getMsg("img_preview_unavailable")
            );
          }
        });
      } catch (_) {
        guard.scheduleInteractiveError(
          guard.getMsg("img_preview_unavailable")
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
  const bindConfirmRemove = () => {
    const root = document.body;
    if (root.getAttribute(dataBoundConfirm) === "true") {
      return;
    }
    root.setAttribute(dataBoundConfirm, "true");
    $(document).on(
      "click.trackRemoveStart",
      ".track-image-remove",
      function () {
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
      function () {
        try {
          const id = $(this).attr("image_id") ?? "";
          const explicit = "{{route('time_trackers.image.remove')}}";
          const endpoint = routeFrom(this, explicit);
          if (!endpoint) {
            guard.scheduleInteractiveError(guard.getMsg("img_remove_unavailable"));
            return;
          }
          safeDelete(endpoint, { id: id }, function (res) {
            try {
              if (res?.flag) {
                $("#slide-thum-" + id).remove();
                $("#slide-" + id).remove();
                setTimeout(function () {
                  const total =
                    $(".product-left").find(".swiper-slide").length | 0;
                  if (total > 0) {
                    initSlider();
                  } else {
                    const msg = guard.getMsg("images_empty_label");
                    $(".product-left").html(
                      '<div class="no-image"><h5 class="text-muted">' +
                        (msg || "—") +
                        "</h5></div>"
                    );
                  }
                }, 200);
              }
              $("#cModal").modal("hide");
              if (window.show_toastr) {
                window.show_toastr("error", res?.msg ?? "", "error");
              }
            } catch (_) {
              guard.scheduleInteractiveError(
                guard.getMsg("img_remove_unavailable")
              );
            }
          });
        } catch (_) {
          guard.scheduleInteractiveError(
            guard.getMsg("img_remove_unavailable")
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
  const init = () => {
    if (!$ || !$.fn) {
      guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
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
