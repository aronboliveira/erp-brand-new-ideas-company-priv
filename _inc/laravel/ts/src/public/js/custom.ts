/**
 * @fileoverview custom.js — ERP application helpers (jQuery-lean rewrite).
 *
 * Migração jQuery → vanilla JS. Chamadas a plugins que exigem jQuery
 * (Summernote, TagsInput, NiceScroll, SearchBox, Bootstrap 4 tooltip/modal/dropdown)
 * continuam usando $ mas são guardadas com `typeof $ !== 'undefined'`.
 *
 * @module custom
 */
// @ts-nocheck

/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-base-to-string, @typescript-eslint/no-floating-promises, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, @typescript-eslint/restrict-plus-operands, no-var, prefer-const */
/* global bootstrap, flatpickr, Swal, $, jQuery, simpleDatatables, Choices, jscolor, site_currency_symbol, site_currency_symbol_position */

// PULL REQUEST START — Alteração customizada: remoção parcial de jQuery

"use strict";

/* ------------------------------------------------------------------ */
/*  Helpers                                                           */
/* ------------------------------------------------------------------ */

const qs  = (sel, root = document) => root.querySelector(sel);
const qsa = (sel, root = document) => root.querySelectorAll(sel);
const byId = (id) => document.getElementById(id);

function csrfToken() {
  const meta = qs('meta[name="csrf-token"]');
  return meta ? meta.getAttribute("content") : "";
}

/** Delegated event helper (replaces $(document).on) */
function onDelegate(event, selector, handler) {
  document.addEventListener(event, function (e) {
    const target = e.target.closest(selector);
    if (target) handler.call(target, e);
  });
}

/** Simplified fetch wrapper (replaces $.ajax GET) */
async function ajaxGet(url, params) {
  const query = params ? "?" + new URLSearchParams(params).toString() : "";
  const resp = await fetch(url + query, {
    headers: { "X-Requested-With": "XMLHttpRequest" },
  });
  const ct = resp.headers.get("content-type") || "";
  return ct.includes("application/json") ? resp.json() : resp.text();
}

/** Simplified fetch wrapper (replaces $.ajax POST/DELETE) */
async function ajaxPost(url, body, method = "POST") {
  const token = csrfToken();
  const isFormData = body instanceof FormData;
  const headers = { "X-Requested-With": "XMLHttpRequest" };
  if (!isFormData) headers["Content-Type"] = "application/x-www-form-urlencoded";

  let payload;
  if (isFormData) {
    body.append("_token", token);
    payload = body;
  } else {
    const data = { _token: token, ...body };
    payload = new URLSearchParams(data).toString();
  }
  const resp = await fetch(url, { method, headers, body: payload });
  const ct = resp.headers.get("content-type") || "";
  return ct.includes("application/json") ? resp.json() : resp.text();
}

/* ------------------------------------------------------------------ */
/*  Session key (POS system)                                          */
/* ------------------------------------------------------------------ */
var session_key = window.location.href.split("/").pop();

/* ------------------------------------------------------------------ */
/*  DOM Ready                                                         */
/* ------------------------------------------------------------------ */
document.addEventListener("DOMContentLoaded", function () {
  // NiceScroll (jQuery plugin — manter $)
  if (typeof $ !== "undefined") {
    if (qs(".custom-scroll")) {
      $(".custom-scroll").niceScroll();
      $(".custom-scroll-horizontal").niceScroll();
    }
  }

  // DataTable init
  if (qs(".datatable") && typeof simpleDatatables !== "undefined") {
    new simpleDatatables.DataTable(".datatable");
  }

  select2();
  summernote();
  daterange();
  JsSearchBox();
});

/* ------------------------------------------------------------------ */
/*  Daterange (Flatpickr — vanilla-compatible)                        */
/* ------------------------------------------------------------------ */
function daterange() {
  const el = qs("#pc-daterangepicker-1");
  if (el && typeof flatpickr !== "undefined") {
    el.flatpickr({ mode: "range" });
  }
}

/* ------------------------------------------------------------------ */
/*  Select2 / Choices.js replacement                                  */
/* ------------------------------------------------------------------ */
function select2() {
  qsa(".select2").forEach(function (element) {
    const id = element.getAttribute("id");
    if (id && typeof Choices !== "undefined") {
      new Choices("#" + id, { removeItemButton: true });
    }
  });
}

/* ------------------------------------------------------------------ */
/*  Toast notification                                                */
/* ------------------------------------------------------------------ */
function show_toastr(type, message) {
  const toast = byId("liveToast");
  if (!toast) return;
  new bootstrap.Toast(toast).show();
  toast.classList.remove("bg-primary", "bg-danger");
  toast.classList.add(type === "success" ? "bg-primary" : "bg-danger");
  const body = qs(".toast-body", toast);
  if (body) body.textContent = message;
}

/* ------------------------------------------------------------------ */
/*  Ajax popup modals                                                 */
/* ------------------------------------------------------------------ */
onDelegate("click", '[data-ajax-popup="true"]', function (e) {
  e.preventDefault();
  const el = this;
  const data = {};
  const title = el.dataset.title || el.dataset.bsOriginalTitle || el.dataset.originalTitle || "";
  const size = el.dataset.size || "md";
  const url = el.dataset.url;

  qsa(".modal-dialog").forEach(function (d) { d.classList.remove("modal-xl"); });

  const modal = byId("commonModal");
  if (!modal) return;
  const titleEl = qs(".modal-title", modal);
  const dialogEl = qs(".modal-dialog", modal);
  if (titleEl) titleEl.textContent = title;
  if (dialogEl) dialogEl.classList.add("modal-" + size);

  const vcHidden = byId("vc_name_hidden");
  if (vcHidden) data.vc_name = vcHidden.value;
  const whHidden = byId("warehouse_name_hidden");
  if (whHidden) data.warehouse_name = whHidden.value;
  const discHidden = byId("discount_hidden");
  if (discHidden) data.discount = discHidden.value;

  ajaxGet(url, Object.keys(data).length ? data : undefined)
    .then(function (html) {
      const body = qs(".body", modal);
      if (body) body.innerHTML = html;
      if (typeof $ !== "undefined") $(modal).modal("show");
      else new bootstrap.Modal(modal).show();
      taskCheckbox();
      common_bind();
      commonLoader();
    })
    .catch(function (err) {
      show_toastr("Error", err.message || "Request failed");
    });
});

/* ------------------------------------------------------------------ */
/*  AI module overlay modal                                           */
/* ------------------------------------------------------------------ */
onDelegate("click", '[data-ajax-popup-over="true"]', function (e) {
  e.preventDefault();
  const el = this;
  const validateSel = el.getAttribute("data-validate");
  let id = "";
  if (validateSel) {
    const valEl = qs(validateSel);
    if (valEl) id = valEl.value;
  }
  const title = el.dataset.title || "";
  const size = el.dataset.size || "md";
  const url = el.dataset.url;

  const modal = byId("commonModalOver");
  if (!modal) return;
  const dialogEl = qs(".modal-dialog", modal);
  if (dialogEl) {
    dialogEl.classList.remove("modal-lg");
    dialogEl.classList.add("modal-" + size);
  }
  const titleEl = qs(".modal-title", modal);
  if (titleEl) titleEl.textContent = title;

  ajaxGet(url, id ? { id: id } : undefined)
    .then(function (html) {
      const body = qs(".modal-body", modal);
      if (body) body.innerHTML = html;
      if (typeof $ !== "undefined") $(modal).modal("show");
      else new bootstrap.Modal(modal).show();
      taskCheckbox();
    })
    .catch(function (err) {
      show_toastr("Error", err.message || "Request failed");
    });
});

/* ------------------------------------------------------------------ */
/*  Google Calendar event click                                       */
/* ------------------------------------------------------------------ */
onDelegate("click", ".local_calendar .fc-daygrid-event, .fc-timegrid-event", function (e) {
  e.preventDefault();
  const el = this;
  const titleEl = qs(".fc-event-title");
  const title = (titleEl ? titleEl.textContent : "") || el.dataset.bsOriginalTitle || "";
  const url = el.getAttribute("href");

  const modal = byId("commonModal");
  if (!modal || !url) return;
  const modalTitle = qs(".modal-title", modal);
  const dialogEl = qs(".modal-dialog", modal);
  if (modalTitle) modalTitle.textContent = title;
  if (dialogEl) dialogEl.classList.add("modal-md");

  ajaxGet(url)
    .then(function (html) {
      const body = qs(".body", modal);
      if (body) body.innerHTML = html;
      if (typeof $ !== "undefined") $(modal).modal("show");
      else new bootstrap.Modal(modal).show();
      common_bind();
    })
    .catch(function (err) {
      show_toastr("Error", err.message || "Request failed");
    });
});

/* ------------------------------------------------------------------ */
/*  Form helpers                                                      */
/* ------------------------------------------------------------------ */
function arrayToJson(form) {
  const el = typeof form === "string" ? qs(form) : form;
  if (!el) return {};
  const formData = new FormData(el);
  const indexed_array = {};
  formData.forEach(function (value, key) { indexed_array[key] = value; });
  return indexed_array;
}

function common_bind() {
  select2();
}

/* ------------------------------------------------------------------ */
/*  Task checkbox progress bar                                        */
/* ------------------------------------------------------------------ */
function taskCheckbox() {
  const checkList = byId("check-list");
  if (!checkList) return;

  const boxes = checkList.querySelectorAll('input[type="checkbox"]');
  const checked = checkList.querySelectorAll('input[type="checkbox"]:checked');
  const count = boxes.length;
  let percentage = count > 0 ? parseInt(String((checked.length / count) * 100), 10) : 0;
  if (isNaN(percentage)) percentage = 0;

  qsa(".custom-label").forEach(function (el) { el.textContent = percentage + "%"; });

  const prog = byId("taskProgress");
  if (!prog) return;
  prog.style.width = percentage + "%";
  prog.classList.remove("bg-warning", "bg-primary", "bg-success", "bg-danger");

  if (percentage <= 15) prog.classList.add("bg-danger");
  else if (percentage <= 33) prog.classList.add("bg-warning");
  else if (percentage <= 70) prog.classList.add("bg-primary");
  else prog.classList.add("bg-success");
}

/* ------------------------------------------------------------------ */
/*  commonLoader — inits que rodam após conteúdo dinâmico              */
/* ------------------------------------------------------------------ */
function commonLoader() {
  // Tooltip, TagsInput — jQuery plugins
  if (typeof $ !== "undefined") {
    $('[data-toggle="tooltip"]').tooltip();
    if (qs('[data-toggle="tags"]'))
      $('[data-toggle="tags"]').tagsinput({ tagClass: "badge badge-primary" });

    // Scrollbar plugin
    const scrollbar = $(".scrollbar-inner");
    if (scrollbar.length) scrollbar.scrollbar().scrollLock();
  }

  // Custom file input
  qsa(".custom-input-file").forEach(function (input) {
    input.addEventListener("change", function (e) {
      const label = input.nextElementSibling;
      if (!label || label.tagName !== "LABEL") return;
      let name;
      if (input.files && input.files.length > 1) {
        name = (input.getAttribute("data-multiple-caption") || "").replace("{count}", String(input.files.length));
      } else if (e.target.value) {
        name = e.target.value.split("\\").pop();
      }
      const span = label.querySelector("span");
      if (name) {
        if (span) span.textContent = name;
        else label.textContent = name;
      }
    });
    input.addEventListener("focus", function () { input.classList.add("has-focus"); });
    input.addEventListener("blur", function () { input.classList.remove("has-focus"); });
  });

  if (qs(".jscolor") && typeof jscolor !== "undefined")
    jscolor.installByClassName("jscolor");

  summernote();
}

// Generic file-name display
onDelegate("change", "input[type=file]", function () {
  const fileclass = this.getAttribute("data-filename");
  const finalname = this.value.split("\\").pop();
  if (fileclass) {
    qsa("." + fileclass).forEach(function (el) { el.textContent = finalname; });
  }
});

/* ------------------------------------------------------------------ */
/*  Summernote (jQuery plugin — manter $)                             */
/* ------------------------------------------------------------------ */
function summernote() {
  if (typeof $ === "undefined") return;

  const opts = {
    dialogsInBody: true,
    minHeight: 200,
    maxHeight: 300,
    toolbar: [
      ["style", ["style"]],
      ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
      ["fontname", ["fontname"]],
      ["color", ["color"]],
      ["para", ["ul", "ol", "paragraph"]],
    ],
  };

  if (qs(".summernote-simple")) {
    $(".summernote-simple").summernote(opts);
    $(".dropdown-toggle").dropdown();
  }
  if (qs(".summernote-simple-2"))
    $(".summernote-simple-2").summernote(opts);
}

/* ------------------------------------------------------------------ */
/*  SweetAlert confirm dialogs                                        */
/* ------------------------------------------------------------------ */
onDelegate("click", ".bs-pass-para", function (e) {
  e.preventDefault();
  const form = this.closest("form");
  if (!form) return;
  Swal.mixin({
    customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" },
    buttonsStyling: false,
  }).fire({
    title: "Are you sure?",
    text: "This action can not be undone. Do you want to continue?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    reverseButtons: true,
  }).then(function (result) {
    if (result.isConfirmed) form.submit();
  });
});

// POS system delete button
onDelegate("click", ".bs-pass-para-pos", function (e) {
  e.preventDefault();
  const confirmId = this.dataset.confirmYes;
  Swal.mixin({
    customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" },
    buttonsStyling: false,
  }).fire({
    title: "Are you sure?",
    text: "This action can not be undone. Do you want to continue?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    reverseButtons: true,
  }).then(function (result) {
    if (result.isConfirmed && confirmId) {
      const form = byId(confirmId);
      if (form) form.submit();
    }
  });
});

/* ------------------------------------------------------------------ */
/*  AJAX helpers (global — usados por Blade inline scripts)           */
/* ------------------------------------------------------------------ */
function postAjax(url, data, cb) {
  ajaxPost(url, data, "POST").then(cb).catch(function (err) {
    console.error("[postAjax]", err);
  });
}

function deleteAjax(url, data, cb) {
  ajaxPost(url, data, "DELETE").then(cb).catch(function (err) {
    console.error("[deleteAjax]", err);
  });
}

/* ------------------------------------------------------------------ */
/*  Currency formatting                                               */
/* ------------------------------------------------------------------ */
function addCommas(num) {
  const number = parseFloat(num).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
  const pre = typeof site_currency_symbol_position !== "undefined" && site_currency_symbol_position === "pre" ? site_currency_symbol : "";
  const post = typeof site_currency_symbol_position !== "undefined" && site_currency_symbol_position === "post" ? site_currency_symbol : "";
  return pre + number + post;
}

/* ------------------------------------------------------------------ */
/*  Quantity plus/minus (POS)                                         */
/* ------------------------------------------------------------------ */
if (!String.prototype.getDecimals) {
  String.prototype.getDecimals = function () {
    const b = ("" + this).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
    return b ? Math.max(0, (b[1] ? b[1].length : 0) - (b[2] ? +b[2] : 0)) : 0;
  };
}

function wcqib_refresh_quantity_increments() {
  qsa("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").forEach(function (el) {
    el.classList.add("buttons_added");
    const minus = document.createElement("input");
    minus.type = "button"; minus.value = "-"; minus.className = "minus";
    const plus = document.createElement("input");
    plus.type = "button"; plus.value = "+"; plus.className = "plus";
    el.insertBefore(minus, el.firstChild);
    el.appendChild(plus);
  });
}

document.addEventListener("DOMContentLoaded", wcqib_refresh_quantity_increments);

onDelegate("click", ".plus, .minus", function () {
  const container = this.closest(".quantity");
  if (!container) return;
  const input = container.querySelector('input[name="quantity"], input[name="quantity[]"]');
  if (!input) return;

  let val = parseFloat(input.value) || 0;
  const max = parseFloat(input.getAttribute("max"));
  const min = parseFloat(input.getAttribute("min")) || 0;
  let step = input.getAttribute("step");
  if (!step || step === "any" || isNaN(parseFloat(step))) step = "1";
  const stepVal = parseFloat(step);

  if (this.classList.contains("plus")) {
    if (!isNaN(max) && val >= max) input.value = String(max);
    else input.value = (val + stepVal).toFixed(step.getDecimals());
  } else {
    if (!isNaN(min) && val <= min) input.value = String(min);
    else if (val > 0) input.value = (val - stepVal).toFixed(step.getDecimals());
  }
  input.dispatchEvent(new Event("change", { bubbles: true }));
});

// Quantity input — allow only numbers
onDelegate("keydown", 'input[name="quantity"], input[name="quantity[]"]', function (e) {
  const allow = [46, 8, 9, 27, 13, 190];
  if (allow.includes(e.keyCode)) return;
  if (e.keyCode === 65 && e.ctrlKey) return;
  if (e.keyCode >= 35 && e.keyCode <= 39) return;
  if ((e.shiftKey || e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105))
    e.preventDefault();
});

/* ------------------------------------------------------------------ */
/*  SearchBox (jQuery plugin — manter $)                              */
/* ------------------------------------------------------------------ */
function JsSearchBox() {
  if (typeof $ === "undefined" || !qs(".js-searchBox")) return;
  $(".js-searchBox").each(function () {
    if ($(this).parent().find(".formTextbox").length === 0)
      $(this).searchBox({ elementWidth: "250" });
  });
}

// PULL REQUEST END — Alteração customizada: remoção parcial de jQuery
