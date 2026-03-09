/**
 * modal-autoopen.js
 * =================
 * Companion to RedirectModalRoutes middleware.
 *
 * When the middleware redirects a direct browser hit on a modal .create / .edit
 * route back to the parent .index route it appends query params:
 *
 *   ?modal=create
 *   ?modal=edit&modal_id=42
 *
 * This script reads those params on DOMContentLoaded, first attempts to locate
 * the matching [data-ajax-popup="true"] trigger on the page and clicks it so
 * that custom.js handles everything normally (including data-title, data-size,
 * data-guard-msg, etc.).  If no trigger can be found it falls back to a direct
 * AJAX fetch using the same pattern as custom.js.
 *
 * Loaded in the admin layout AFTER jQuery and custom.js.
 */
(function () {
  "use strict";

  /* ── Entry point ─────────────────────────────────────────────────── */

  function autoOpenModal() {
    const params = new URLSearchParams(window.location.search);
    const action = params.get("modal"); // "create" | "edit"
    if (!action) return;

    const modalId = params.get("modal_id") || null;

    // Clean URL immediately so a refresh won't re-trigger
    cleanUrl(params);

    // Try clicking an existing trigger button first (preserves title, size, etc.)
    const trigger = findTrigger(action, modalId);
    if (trigger) {
      trigger.click();
      return;
    }

    // Fallback: open via AJAX directly
    openDirectly(action, modalId);
  }

  /* ── Helpers ─────────────────────────────────────────────────────── */

  /**
   * Remove modal / modal_id params from the URL bar without reloading.
   */
  function cleanUrl(params) {
    if (!window.history || !window.history.replaceState) return;
    params.delete("modal");
    params.delete("modal_id");
    const qs = params.toString();
    const clean =
      window.location.pathname + (qs ? "?" + qs : "") + window.location.hash;
    window.history.replaceState(null, "", clean);
  }

  /**
   * Scan [data-ajax-popup="true"] elements for one whose data-url matches the
   * expected create / edit pattern.
   */
  function findTrigger(action, id) {
    const triggers = document.querySelectorAll(
      '[data-ajax-popup="true"][data-url]',
    );
    for (let i = 0; i < triggers.length; i++) {
      const url = (triggers[i].getAttribute("data-url") || "").replace(
        /\/+$/,
        "",
      );
      if (action === "create" && /\/create\/?$/.test(url)) {
        return triggers[i];
      }
      if (action === "edit" && id) {
        const re = new RegExp("/" + escRe(String(id)) + "/edit/?$");
        if (re.test(url)) return triggers[i];
      }
    }
    return null;
  }

  /**
   * Build the AJAX URL and open #commonModal directly — mirrors custom.js logic.
   */
  function openDirectly(action, id) {
    if (typeof jQuery === "undefined" && typeof $ === "undefined") return;
    const jq = typeof $ !== "undefined" ? $ : jQuery;

    const basePath = window.location.pathname.replace(/\/+$/, "");
    let url;
    if (action === "create") {
      url = basePath + "/create";
    } else if (action === "edit" && id) {
      url = basePath + "/" + encodeURIComponent(id) + "/edit";
    } else {
      return;
    }

    const title =
      action === "create" ? "Create" : action === "edit" ? "Edit" : "";
    const $modal = jq("#commonModal");
    if (!$modal.length) return;

    $modal
      .find(".modal-dialog")
      .removeClass("modal-sm modal-md modal-lg modal-xl")
      .addClass("modal-lg");
    $modal.find(".modal-title").html(title);

    jq.ajax({
      url: url,
      data: { _modal_partial: 1 }, // escape-hatch so middleware won't redirect
      success: function (html) {
        $modal.find(".body").html(html);
        $modal.modal("show");
        if (typeof taskCheckbox === "function") taskCheckbox();
        if (typeof common_bind === "function") common_bind("#commonModal");
        if (typeof commonLoader === "function") commonLoader();
      },
      error: function (xhr) {
        console.warn(
          "[modal-autoopen] Failed to load modal content from",
          url,
          xhr.status,
          xhr.statusText,
        );
        if (typeof show_toastr === "function") {
          const data = xhr.responseJSON || {};
          show_toastr(
            "Error",
            data.error || "Could not load modal content.",
            "error",
          );
        }
      },
    });
  }

  /** Escape a string for safe use inside a RegExp */
  function escRe(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  /* ── Bootstrap ───────────────────────────────────────────────────── */

  const DELAY = 300; // allow DataTables, other JS to finish rendering

  if (typeof jQuery !== "undefined") {
    jQuery(document).ready(function () {
      setTimeout(autoOpenModal, DELAY);
    });
  } else {
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(autoOpenModal, DELAY);
    });
  }
})();
