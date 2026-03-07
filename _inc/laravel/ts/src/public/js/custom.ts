/**
 * @fileoverview TypeScript version of public/js/custom.js
 * @generated from original JavaScript - manual review recommended
 * @module custom
 */

// Type declarations for external libraries and jQuery plugins
interface BootstrapType {
  Toast: new (el: Element | null) => { show(): void };
}

interface SimpleDatatablesType {
  DataTable: new (selector: string) => unknown;
}

interface SwalType {
  mixin(options: Record<string, unknown>): {
    fire(options: Record<string, unknown>): Promise<SwalResult>;
  };
  DismissReason: { cancel: unknown };
}

interface SwalResult {
  isConfirmed?: boolean;
  dismiss?: "cancel" | "close" | "backdrop" | "esc" | "timer" | undefined;
}

type ChoicesConstructor = new (
  selector: string,
  options: Record<string, unknown>,
) => unknown;

interface JscolorType {
  installByClassName(className: string): void;
}

// Cast globals from window
const _bootstrap = (window as unknown as { bootstrap: BootstrapType })
  .bootstrap;
const _simpleDatatables = (
  window as unknown as { simpleDatatables: SimpleDatatablesType }
).simpleDatatables;
const _Swal = (window as unknown as { Swal: SwalType }).Swal;
const _Choices = (window as unknown as { Choices: ChoicesConstructor }).Choices;
const _jscolor = (window as unknown as { jscolor: JscolorType }).jscolor;
const _site_currency_symbol = (
  window as unknown as { site_currency_symbol: string }
).site_currency_symbol;
const _site_currency_symbol_position = (
  window as unknown as { site_currency_symbol_position: string }
).site_currency_symbol_position;

// Extend String prototype
// eslint-disable-next-line @typescript-eslint/no-unused-vars
interface String {
  getDecimals(): number;
}

// Extend jQuery with plugins
// eslint-disable-next-line @typescript-eslint/no-unused-vars
interface JQuery<TElement = HTMLElement> {
  niceScroll(): this;
  summernote(options?: Record<string, unknown>): this;
  tagsinput(options?: Record<string, unknown>): this;
  scrollbar(): { scrollLock(): void };
  searchBox(options?: Record<string, unknown>): this;
  modal(action: string): this;
  tooltip(): this;
  dropdown(): this;
  next(selector?: string): this;
}

// Extend HTMLElement with flatpickr
interface HTMLElement {
  flatpickr(options?: Record<string, unknown>): void;
}

/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

("use strict");
// for pos system
const _session_key = window.location.href.split("/").pop() ?? "";
//

$(function (): void {
  try {
    const SEL_TASK_PROGRESS = "#taskProgress",
      SEL_COMMON_MODAL = "#commonModal",
      SEL_COMMON_MODAL_OVER = "#commonModalOver",
      SEL_LIVE_TOAST = "#liveToast",
      SEL_CSRF_META = 'meta[name="csrf-token"]',
      SWAL_BTN_CLASSES = Object.freeze({
        confirmButton: "btn btn-success",
        cancelButton: "btn btn-danger",
      }),
      SWAL_CONFIRM_OPTS = Object.freeze({
        title: "Are you sure?",
        text: "This action can not be undone. Do you want to continue?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        reverseButtons: true,
      });
    if ($(".custom-scroll").length) {
      $(".custom-scroll").niceScroll();
      $(".custom-scroll-horizontal").niceScroll();
    }

    // loadConfirm();

    if ($(".datatable").length > 0) {
      new _simpleDatatables.DataTable(".datatable");
    }

    const daterange = (): void => {
      if ($("#pc-daterangepicker-1").length > 0) {
        document
          .querySelector<HTMLElement>("#pc-daterangepicker-1")
          ?.flatpickr({
            mode: "range",
          });
      }
    };

    const select2 = (): void => {
      if ($(".select2").length > 0) {
        $(".select2").each(function (this: HTMLElement, _index: number): void {
          const id = $(this).attr("id");
          new _Choices(`#${id!}`, { removeItemButton: true });
        });
      }
    };

    select2();
    daterange();

    const show_toastr = (type: string, message: string): void => {
      new _bootstrap.Toast(document.getElementById("liveToast")).show();
      const $toast = $(SEL_LIVE_TOAST);
      $toast.addClass(type == "success" ? "bg-primary" : "bg-danger");
      $toast.find(".toast-body").html(message);
    };

    $(document).on(
      "click",
      'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
      function (this: HTMLElement): void {
        const data: Record<string, unknown> = {};
        const title =
          ($(this).data("title") as string | undefined) ??
          ($(this).data("bs-original-title") as string | undefined) ??
          ($(this).data("original-title") as string | undefined);

        $(".modal-dialog").removeClass("modal-xl");
        const size =
          String($(this).data("size") ?? "") === ""
            ? "md"
            : String($(this).data("size"));

        const url = $(this).data("url") as string;
        const $cm = $(SEL_COMMON_MODAL);
        $cm.find(".modal-title").html(title ?? "");
        $cm.find(".modal-dialog").addClass(`modal-${size}`);

        if ($("#vc_name_hidden").length > 0) {
          data.vc_name = $("#vc_name_hidden").val();
        }
        if ($("#warehouse_name_hidden").length > 0) {
          data.warehouse_name = $("#warehouse_name_hidden").val();
        }
        if ($("#discount_hidden").length > 0) {
          data.discount = $("#discount_hidden").val();
        }
        $.ajax({
          url: url,
          data: data,
          success: function (response: string): void {
            $cm.find(".body").html(response);
            $cm.modal("show");
            // daterange_set();
            taskCheckbox();
            common_bind();
            commonLoader();
          },
          error: function (xhr: { responseJSON?: { error?: string } }): void {
            const errorData = xhr.responseJSON;
            show_toastr("Error", errorData?.error ?? "Unknown error");
          },
        });
      },
    );

    const _arrayToJson = (form: HTMLFormElement): Record<string, unknown> => {
      const data = $(form).serializeArray();
      const indexed_array: Record<string, unknown> = {};

      for (const item of data) {
        indexed_array[item.name] = item.value;
      }

      return indexed_array;
    };

    const common_bind = (): void => {
      select2();
    };

    const taskCheckbox = (): void => {
      let checked = 0;
      let count = 0;
      let percentage = 0;

      count = $("#check-list input[type=checkbox]").length;
      checked = $("#check-list input[type=checkbox]:checked").length;
      percentage = parseInt(String((checked / count) * 100), 10);
      if (isNaN(percentage)) {
        percentage = 0;
      }
      $(".custom-label").text(`${percentage}%`);
      const $tp = $(SEL_TASK_PROGRESS);
      $tp.css("width", `${percentage}%`);
      $tp.removeClass("bg-warning bg-primary bg-success bg-danger");
      if (percentage <= 15) {
        $tp.addClass("bg-danger");
      } else if (percentage > 15 && percentage <= 33) {
        $tp.addClass("bg-warning");
      } else if (percentage > 33 && percentage <= 70) {
        $tp.addClass("bg-primary");
      } else {
        $tp.addClass("bg-success");
      }
    };

    const commonLoader = (): void => {
      $('[data-toggle="tooltip"]').tooltip();
      if ($('[data-toggle="tags"]').length > 0) {
        $('[data-toggle="tags"]').tagsinput({
          tagClass: "badge badge-primary",
        });
      }

      // $(function (): void {
      //
      //     let dtToday = new Date();
      //
      //     let month = dtToday.getMonth() + 1;
      //     let day = dtToday.getDate();
      //     let year = dtToday.getFullYear();
      //     if(month < 10)
      //         month = '0' + month.toString();
      //     if(day < 10)
      //         day = '0' + day.toString();
      //
      //     let maxDate = year + '-' + month + '-' + day;
      //
      //     $("input[type='date']").attr('max', maxDate);
      // });

      const e = $(".scrollbar-inner");
      if (e.length) {
        e.scrollbar().scrollLock();
      }

      const e1 = $(".custom-input-file");
      if (e1.length) {
        e1.each(function (this: HTMLElement): void {
          const $el = $(this);
          $el.on("change", function (this: HTMLElement, t: JQueryEventObject) {
            const inputEl = this as HTMLInputElement;
            let n: string | undefined;
            const $label = $el.next("label");
            const i = $label.html();
            if (inputEl.files && inputEl.files.length > 1) {
              n = (inputEl.getAttribute("data-multiple-caption") ?? "").replace(
                "{count}",
                String(inputEl.files.length),
              );
            } else if ((t.target as HTMLInputElement).value) {
              n =
                (t.target as HTMLInputElement).value.split("\\").pop() ??
                undefined;
            }
            if (n) {
              $label.find("span").html(n);
            } else {
              $label.html(i);
            }
          });
          $el
            .on("focus", function (): void {
              $el.addClass("has-focus");
            })
            .on("blur", function (): void {
              $el.removeClass("has-focus");
            });
        });
      }

      // let e2 = $('[data-toggle="autosize"]');
      // e2.length && autosize(e2);

      if ($(".jscolor").length) {
        _jscolor.installByClassName("jscolor");
      }
      summernote();
      // for Choose file
      $(document).on(
        "change",
        "input[type=file]",
        function (this: HTMLElement): void {
          const fileclass = $(this).attr("data-filename") ?? "";
          const rawVal = $(this).val();
          const finalname =
            typeof rawVal === "string" ? (rawVal.split("\\").pop() ?? "") : "";
          $(`.${fileclass}`).html(finalname);
        },
      );
    };

    const summernote = (): void => {
      if ($(".summernote-simple").length) {
        $(".summernote-simple").summernote({
          dialogsInBody: !0,
          minHeight: 200,
          maxHeight: 300,
          toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
          ],
        });
        $(".dropdown-toggle").dropdown();
      }

      if ($(".summernote-simple-2").length) {
        $(".summernote-simple-2").summernote({
          dialogsInBody: !0,
          minHeight: 200,
          maxHeight: 300,
          toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
          ],
        });
      }
    };

    summernote();

    $(document).on(
      "click",
      ".bs-pass-para",
      function (this: HTMLElement): void {
        const form = $(this).closest("form");
        const swalWithBootstrapButtons = _Swal.mixin({
          customClass: SWAL_BTN_CLASSES,
          buttonsStyling: false,
        });
        void swalWithBootstrapButtons
          .fire(SWAL_CONFIRM_OPTS)
          .then((result: SwalResult) => {
            if (result.isConfirmed) {
              (form[0] as HTMLFormElement | undefined)?.submit();
            } else if (result.dismiss === _Swal.DismissReason.cancel) {
              // cancelled
            }
          });
      },
    );

    //only pos system delete button
    $(document).on(
      "click",
      ".bs-pass-para-pos",
      function (this: HTMLElement): void {
        const self = this;
        const swalWithBootstrapButtons = _Swal.mixin({
          customClass: SWAL_BTN_CLASSES,
          buttonsStyling: false,
        });
        void swalWithBootstrapButtons
          .fire(SWAL_CONFIRM_OPTS)
          .then((result: SwalResult) => {
            if (result.isConfirmed) {
              const formId = $(self).data("confirm-yes") as string;
              (
                document.getElementById(formId) as HTMLFormElement | null
              )?.submit();
            } else if (result.dismiss === _Swal.DismissReason.cancel) {
              // cancelled
            }
          });
      },
    );

    const _postAjax = (
      url: string,
      data: Record<string, unknown>,
      cb: (response: unknown) => void,
    ): void => {
      const token = $(SEL_CSRF_META).attr("content");
      const jdata: Record<string, unknown> = { _token: token };

      for (const k in data) {
        jdata[k] = data[k];
      }

      $.ajax({
        type: "POST",
        url: url,
        data: jdata,
        success: function (response: unknown): void {
          cb(response);
        },
      });
    };

    //end only pos system delete button

    const _deleteAjax = (
      url: string,
      data: Record<string, unknown>,
      cb: (response: unknown) => void,
    ): void => {
      const token = $(SEL_CSRF_META).attr("content");
      const jdata: Record<string, unknown> = { _token: token };

      for (const k in data) {
        jdata[k] = data[k];
      }

      $.ajax({
        type: "DELETE",
        url: url,
        data: jdata,
        success: function (response: unknown): void {
          cb(response);
        },
      });
    };

    // Google calendar
    $(document).on(
      "click",
      ".local_calendar .fc-daygrid-event, .fc-timegrid-event",
      function (this: HTMLElement, e: Event) {
        // if (!$(this).hasClass('project')) {
        e.preventDefault();
        const _event = $(this);
        const title1 = $(".fc-event-title").html();
        const title2 = $(this).data("bs-original-title") as string | undefined;
        const title = title1 || title2;
        // let size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
        const size = "md";
        const url = $(this).attr("href");
        const $cm = $(SEL_COMMON_MODAL);
        $cm.find(".modal-title").html(title ?? "");
        $cm.find(".modal-dialog").addClass(`modal-${size}`);
        $.ajax({
          url: url,
          success: function (response: string): void {
            $cm.find(".body").html(response);
            $cm.modal("show");
            common_bind();
          },
          error: function (xhr: { responseJSON?: { error?: string } }): void {
            const errData = xhr.responseJSON;
            show_toastr("Error", errData?.error ?? "Unknown error");
          },
        });
        // }
      },
    );

    //date value 4

    // $(function (): void {
    //
    //     let dtToday = new Date();
    //
    //     let month = dtToday.getMonth() + 1;
    //     let day = dtToday.getDate();
    //     let year = dtToday.getFullYear();
    //     if(month < 10)
    //         month = '0' + month.toString();
    //     if(day < 10)
    //         day = '0' + day.toString();
    //
    //     let maxDate = year + '-' + month + '-' + day;
    //
    //     $("input[type='date']").attr('max', maxDate);
    // });

    const _addCommas = (num: number): string => {
      const number = parseFloat(String(num))
        .toFixed(2)
        .replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
      return (
        (_site_currency_symbol_position == "pre" ? _site_currency_symbol : "") +
        number +
        (_site_currency_symbol_position == "post" ? _site_currency_symbol : "")
      );
    };

    // PLUS MINUS QUANTITY JS
    const wcqib_refresh_quantity_increments = (): void => {
      jQuery(
        "div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)",
      ).each(function (_a: number, b: HTMLElement) {
        const c = jQuery(b);
        (c.addClass("buttons_added"),
          c
            .children()
            .first()
            .before('<input type="button" value="-" class="minus" />'),
          c
            .children()
            .last()
            .after('<input type="button" value="+" class="plus" />'));
      });
    };

    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!String.prototype.getDecimals) {
      String.prototype.getDecimals = function (): number {
        const a = this as string;
        const b = `${a}`.match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
        return b
          ? Math.max(0, (b[1] ? b[1].length : 0) - (b[2] ? +b[2] : 0))
          : 0;
      };
    }

    jQuery(document).ready(function (): void {
      wcqib_refresh_quantity_increments();
    });

    jQuery(document).on("updated_wc_div", function (): void {
      wcqib_refresh_quantity_increments();
    });

    jQuery(document).on(
      "click",
      ".plus, .minus",
      function (this: HTMLElement): void {
        const a = jQuery(this)
          .closest(".quantity")
          .find('input[name="quantity"], input[name="quantity[]"]');
        let b = parseFloat(String(a.val() ?? "0"));
        let c = parseFloat(String(a.attr("max") ?? ""));
        let d = parseFloat(String(a.attr("min") ?? "0"));
        let e = String(a.attr("step") ?? "1");
        if (!b || isNaN(b)) {
          b = 0;
        }
        if (isNaN(c)) {
          c = Infinity;
        }
        if (!d || isNaN(d)) {
          d = 0;
        }
        if (e === "any" || e === "" || isNaN(parseFloat(e))) {
          e = "1";
        }
        if (jQuery(this).is(".plus")) {
          if (c !== Infinity && b >= c) {
            a.val(c);
          } else {
            a.val((b + parseFloat(e)).toFixed(e.getDecimals()));
          }
        } else {
          if (d && b <= d) {
            a.val(d);
          } else if (b > 0) {
            a.val((b - parseFloat(e)).toFixed(e.getDecimals()));
          }
        }
        a.trigger("change");
      },
    );

    $(document).on(
      "keydown",
      'input[name="quantity"], input[name="quantity[]"]',
      function (evt) {
        const e = evt as unknown as KeyboardEvent;
        // Allow: backspace, delete, tab, escape, enter and .
        if (
          [46, 8, 9, 27, 13, 190].indexOf(e.keyCode) !== -1 ||
          // Allow: Ctrl+A
          (e.keyCode == 65 && e.ctrlKey === true) ||
          // Allow: home, end, left, right
          (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
          // let it happen, don't do anything
          return;
        }
        // Ensure that it is a number and stop the keypress
        if (
          (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
          (e.keyCode < 96 || e.keyCode > 105)
        ) {
          e.preventDefault();
        }
      },
    );

    //for ai module
    $(document).on(
      "click",
      'a[data-ajax-popup-over="true"], button[data-ajax-popup-over="true"], div[data-ajax-popup-over="true"]',
      function (this: HTMLElement): void {
        const validate = $(this).attr("data-validate");
        let id = "";
        if (validate != null && validate !== "") {
          id = String($(validate).val() ?? "");
        }
        const title_over = $(this).data("title") as string;
        $(SEL_COMMON_MODAL_OVER + " .modal-dialog").removeClass("modal-lg");
        const size_over =
          String($(this).data("size") ?? "") === ""
            ? "md"
            : String($(this).data("size"));

        const url = $(this).data("url") as string;
        const $cmo = $(SEL_COMMON_MODAL_OVER);
        $cmo.find(".modal-title").html(title_over);
        $cmo.find(".modal-dialog").addClass(`modal-${size_over}`);
        $.ajax({
          url: `${url}?id=${id}`,
          success: function (response: string): void {
            $cmo.find(".modal-body").html(response);
            $cmo.modal("show");
            taskCheckbox();
          },
          error: function (xhr: { responseJSON?: { error?: string } }): void {
            const errData = xhr.responseJSON;
            show_toastr("Error", errData?.error ?? "Unknown error");
          },
        });
      },
    );

    //start input serach box
    const JsSearchBox = (): void => {
      if ($(".js-searchBox").length) {
        $(".js-searchBox").each(function (
          this: HTMLElement,
          _index: number,
        ): void {
          if ($(this).parent().find(".formTextbox").length === 0) {
            $(this).searchBox({ elementWidth: "250" });
          }
        });
      }
    };

    $(document).ready(function (): void {
      JsSearchBox();
    });
  } catch (__moduleErr) {
    console.error("[custom] failed to initialise:", __moduleErr);
  }
});

//end input serach box
