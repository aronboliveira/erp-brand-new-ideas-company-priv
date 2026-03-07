/**
 * @fileoverview TypeScript version of public/assets/js/routes/tasks/drag.js
 * @generated from original JavaScript - manual review recommended
 * @module drag
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

interface DragulaInstance {
  on(event: string, callback: (...args: unknown[]) => void): DragulaInstance;
  destroy(): void;
}

type DragulaStatic = (
  containers: Element[],
  options?: Record<string, unknown>,
) => DragulaInstance;

type TranslationsDict = Record<string, Record<string, string>>;

declare global {
  interface Window {
    translations?: TranslationsDict;
    dragula?: DragulaStatic;
  }
}

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery;
  if (!$) {
    console.error("jQuery not available");
    return;
  }
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataBound = "data-bound-";
  const now = "{{__('Now')}}";
  const ensureToastContainer = (): HTMLElement => {
    let c = document.getElementById("np-toast-container");
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
  const showErrorNow = (message: string): void=> {
    const hasBootstrap =
      (document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') ??
        document.querySelector('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = document.getElementById("np-toast");
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
      const body = t.querySelector(".toast-body");
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
  const scheduleInteractiveError = (message: string): void=> {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) ?? errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ??
        el.getAttribute(dataGuardMsg) ??
        window.translations?.en?.[msgKey] ??
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    return msg;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const resolveUrl = (el: HTMLElement, explicit: string | null) => {
    const url = el.getAttribute("data-url") ?? "";
    const href = el
      ? el.tagName === "FORM"
        ? (el.getAttribute("action") ?? "")
        : (el.getAttribute("href") ?? "")
      : "";
    if (
      (!explicit || explicit === "#") &&
      (!url || url === "#") &&
      (!href || href === "#")
    ) {
      scheduleInteractiveError(getMsg(el ?? document.body, "ajax_unavailable"));
      return null;
    }
    return explicit && explicit !== "#"
      ? explicit
      : url && url !== "#"
        ? url
        : href;
  };
  const ajaxPost = (
    endpoint: string | null,
    data: unknown,
    onSuccess: ((d: unknown) => void) | null,
    elForMsg: HTMLElement | null,
    msgKey: string | null,
  ): void=> {
    const url = endpoint ?? "";
    if (!url) {
      scheduleInteractiveError(
        getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"),
      );
      return;
    }
    $.ajax({
      url: url,
      type: "POST",
      data: data || {},
      cache: false,
      success: function (d: unknown) {
        if (typeof onSuccess === "function") {
          onSuccess(d);
        }
      },
      error: function (): void {
        scheduleInteractiveError(
          getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"),
        );
      },
    });
  };
  const ajaxDelete = (
    endpoint: string | null,
    onSuccess: ((d: unknown) => void) | null,
    elForMsg: HTMLElement | null,
    msgKey: string | null,
  ): void=> {
    const url = endpoint ?? "";
    if (!url) {
      scheduleInteractiveError(
        getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"),
      );
      return;
    }
    $.ajax({
      url: url,
      type: "DELETE",
      dataType: "JSON",
      cache: false,
      success: function (d: unknown) {
        if (typeof onSuccess === "function") {
          onSuccess(d);
        }
      },
      error: function (): void {
        scheduleInteractiveError(
          getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"),
        );
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      },
    });
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const initDragula = () => {
    if (!window.dragula) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("dragula unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    $('[data-plugin="dragula"]').each(function () {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
      const $host = $(this);
      const containers = $host.data("containers") as string[] | undefined;
      const nodes: Element[] = [];
      if (containers?.length) {
        // eslint-disable-next-line @typescript-eslint/prefer-for-of
        for (let i = 0; i < containers.length; i++) {
          const n = document.getElementById(containers[i]);
          if (n) {
            nodes.push(n);
          }
        }
      } else {
        nodes.push(this as Element);
      }
      const handleClass = $host.data("handleclass") as string | undefined;
      const drake = handleClass
        ? window.dragula!(nodes, {
            moves: function (el: HTMLElement, _c: Element, handle: Element) {
              return handle.classList.contains(handleClass);
            },
          })
        : window.dragula!(nodes);
      drake.on(
        "drop",
        function (el: HTMLElement, target: HTMLElement, source: HTMLElement) {
          try {
            if (!target || !source || !el) {
              scheduleInteractiveError(
                getMsg(document.body, "drag_unavailable"),
              );
              return;
            }
            const sort: (string | undefined)[] = [];
            $("#" + target.id + " > div").each(function (): void {
              // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
              sort[$(this).index()] = $(this).attr("id");
            });
            const id = el.id;
            const old_stage = $("#" + source.id).data("status");
            const new_stage = $("#" + target.id).data("status");
            const project_id = "{{$project->id}}";
            $("#" + source.id)
              .parent()
              .find(".count")
              .text(String($("#" + source.id + " > div").length));
            $("#" + target.id)
              .parent()
              .find(".count")
              .text(String($("#" + target.id + " > div").length));
            const explicit =
              "{{route(VW::PRJ . '.tasks.update.order',[$project->id])}}";
            const endpoint = resolveUrl(target, explicit);
            if (!endpoint) {
              scheduleInteractiveError(
                getMsg(target, "update_order_unavailable"),
              );
              return;
            }
            $.ajax({
              url: endpoint,
              type: "PATCH",
              data: {
                id: id,
                sort: sort,
                new_stage: new_stage,
                old_stage: old_stage,
                project_id: project_id,
              },
              cache: false,
              success: function (): void {},
              error: function (): void {
                scheduleInteractiveError(
                  getMsg(target, "update_order_unavailable"),
                );
              },
            });
          } catch (_) {
            scheduleInteractiveError(
              getMsg(document.body, "update_order_unavailable"),
            );
          }
        },
      );
      const mo = new MutationObserver((m, o) => {
        if (!document.body.contains($host.get(0))) {
          try {
            drake.destroy();
          } catch (_) {}
          o.disconnect();
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    });
  };
  const bindOnce = (key: string, binder: () => void): void=> {
    const root = document.documentElement;
    const attr = dataBound + key;
    if (root.getAttribute(attr) === "true") {
      return;
    }
    root.setAttribute(attr, "true");
    binder();
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const toggleSelectionUser = (): void => {
    bindOnce("add-usr", function (): void {
      $(document).on("click.addUsr", ".add_usr", function (): void {
        try {
          const ids: (string | undefined)[] = [];
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          const $btn = $(this);
          $btn.toggleClass("selected");
          const crr_id = $btn.attr("data-id");
          const t = $("#usr_txt_" + crr_id);
          t.html(t.html() === "Add" ? "{{__('Added')}}" : "{{__('Add')}}");
          const ic = $("#usr_icon_" + crr_id);
          if (ic.hasClass("fa-plus")) {
            ic.removeClass("fa-plus").addClass("fa-check");
          } else {
            ic.removeClass("fa-check").addClass("fa-plus");
          }
          $(".selected").each(function (): void {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            ids.push($(this).attr("data-id"));
          });
          $('input[name="assign_to"]').val(
            ids.filter((id): id is string => id !== undefined),
          );
        } catch (_) {}
      });
    });
  };
  const deleteTask = (): void => {
    bindOnce("del-task", function (): void {
      $(document).on("click.delTask", ".del_task", function (): void {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const $btn = $(this);
        const el = this as HTMLElement;
        const url = resolveUrl(el, $btn.attr("data-url") ?? null);
        if (!url) {
          scheduleInteractiveError(getMsg(el, "delete_task_unavailable"));
          return;
        }
        ajaxDelete(
          url,
          function (data: unknown) {
            const d = data as Record<string, unknown>;
            if (d.task_id) {
              $("#" + String(d.task_id)).remove();
            }
            if (window.show_toastr) {
              window.show_toastr(
                "{{__('Success')}}",
                "{{ __('Task Deleted Successfully!')}}",
                "success",
              );
            }
          },
          el,
          "delete_task_unavailable",
        );
      });
    });
  };
  const addComment = (): void => {
    bindOnce("comment-submit", function (): void {
      $(document).on(
        "click.commentSubmit",
        "#comment_submit",
        function (): void {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          const curr = $(this);
          const v = String(
            $("#form-comment textarea[name='comment']").val() ?? "",
          ).trim();
          if (!v) {
            if (window.show_toastr) {
              window.show_toastr(
                "{{__('Error')}}",
                "{{ __('Please write comment!')}}",
                "error",
              );
            }
            return;
          }
          const form = document.getElementById("form-comment");
          if (!form) return;
          const url = resolveUrl(
            form,
            String($("#form-comment").data("action") ?? ""),
          );
          if (!url) {
            scheduleInteractiveError(getMsg(form, "comment_add_unavailable"));
            return;
          }
          ajaxPost(
            url,
            { comment: v },
            function (data: unknown) {
              try {
                const d = (
                  typeof data === "string" ? JSON.parse(data) : data
                ) as Record<string, unknown>;
                const user = d.user as Record<string, string> | undefined;
                const html =
                  "<div class='list-group-item px-0'><div class='row align-items-center'><div class='col-auto'><a href='#' class='avatar avatar-sm rounded-circle'><img " +
                  (user?.img_avatar ? user.img_avatar : "") +
                  " alt='" +
                  (user?.name ? user.name : "") +
                  "'></a></div><div class='col ml-n2'><p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.comment ?? "") +
                  "</p><small class='d-block'>" +
                  now +
                  "</small></div><div class='col-auto'><a href='#' class='delete-comment' data-url='" +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.deleteUrl ?? "") +
                  "'><i class='ti ti-trash-alt text-danger'></i></a></div></div></div>";
                $("#comments").prepend(html);
                $("#form-comment textarea[name='comment']").val("");
                const sid = curr.closest(".side-modal").attr("id");
                if (sid != null && sid !== "") {
                  load_task(sid);
                }
                if (window.show_toastr) {
                  window.show_toastr(
                    "{{__('Success')}}",
                    "{{ __('Comment Added Successfully!')}}",
                    "success",
                  );
                }
              } catch (_) {
                scheduleInteractiveError(
                  getMsg(form, "comment_add_unavailable"),
                );
              }
            },
            form,
            "comment_add_unavailable",
          );
        },
      );
    });
  };
  const deleteComment = (): void => {
    bindOnce("comment-delete", function (): void {
      $(document).on("click.commentDel", ".delete-comment", function (): void {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const btn = $(this);
        const el = this as HTMLElement;
        const url = resolveUrl(el, btn.attr("data-url") ?? null);
        if (!url) {
          scheduleInteractiveError(getMsg(el, "comment_delete_unavailable"));
          return;
        }
        ajaxDelete(
          url,
          function (): void {
            const sid = btn.closest(".side-modal").attr("id");
            if (sid != null && sid !== "") {
              load_task(sid);
            }
            if (window.show_toastr) {
              window.show_toastr(
                "{{__('Success')}}",
                "{{ __('Comment Deleted Successfully!')}}",
                "success",
              );
            }
            btn.closest(".list-group-item").remove();
          },
          el,
          "comment_delete_unavailable",
        );
      });
    });
  };
  const addChecklist = (): void => {
    bindOnce("checklist-add", function (): void {
      $(document).on(
        "click.checklistAdd",
        "#checklist_submit",
        function (): void {
          const name = $("#form-checklist input[name=name]").val() ?? "";
          if (!name) {
            if (window.show_toastr) {
              window.show_toastr(
                "{{__('Error')}}",
                "{{ __('Please write checklist name!')}}",
                "error",
              );
            }
            return;
          }
          const form = document.getElementById("form-checklist");
          if (!form) return;
          const url = resolveUrl(
            form,
            String($("#form-checklist").data("action") ?? ""),
          );
          if (!url) {
            scheduleInteractiveError(getMsg(form, "checklist_add_unavailable"));
            return;
          }
          ajaxPost(
            url,
            { name: name },
            function (data: unknown) {
              try {
                const d = (
                  typeof data === "string" ? JSON.parse(data) : data
                ) as Record<string, unknown>;
                const html =
                  '<div class="card border shadow-none checklist-member"><div class="px-3 py-2 row align-items-center"><div class="col-10"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="check-item-' +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.id ?? "") +
                  '" value="' +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.id ?? "") +
                  '" data-url="' +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.updateUrl ?? "") +
                  '"><label class="custom-control-label h6 text-sm" for="check-item-' +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.id ?? "") +
                  '">' +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.name ?? "") +
                  "</label></div></div><div class='col-auto card-meta d-inline-flex align-items-center ml-sm-auto'><a href='#' class='action-item delete-checklist' role='button' data-url='" +
                  // eslint-disable-next-line @typescript-eslint/no-base-to-string
                  // eslint-disable-next-line @typescript-eslint/restrict-plus-operands, @typescript-eslint/no-base-to-string
                  (d.deleteUrl ?? "") +
                  "'><i class='ti ti-trash-alt text-danger'></i></a></div></div></div>";
                $("#checklist").append(html);
                $("#form-checklist input[name=name]").val("");
                (
                  $("#form-checklist") as JQuery<HTMLElement> & {
                    collapse: (action: string) => void;
                  }
                ).collapse("toggle");
                const sid = $(".side-modal").attr("id");
                if (sid != null && sid !== "") {
                  load_task(sid);
                }
                if (window.show_toastr) {
                  window.show_toastr(
                    "{{__('Success')}}",
                    "{{ __('Checklist Added Successfully!')}}",
                    "success",
                  );
                }
              } catch (_) {
                scheduleInteractiveError(
                  getMsg(form, "checklist_add_unavailable"),
                );
              }
            },
            form,
            "checklist_add_unavailable",
          );
        },
      );
    });
  };
  const updateChecklist = (): void => {
    bindOnce("checklist-update", function (): void {
      $(document).on(
        "change.checklistToggle",
        "#checklist input[type=checkbox]",
        function (): void {
          const el = this as HTMLElement;
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          const url = resolveUrl(el, $(this).attr("data-url") ?? null);
          if (!url) {
            scheduleInteractiveError(
              getMsg(el, "checklist_update_unavailable"),
            );
            return;
          }
          ajaxPost(
            url,
            {},
            function (): void {
              const sid = $(".side-modal").attr("id");
              if (sid != null && sid !== "") {
                load_task(sid);
              }
              if (window.show_toastr) {
                window.show_toastr(
                  "{{__('Success')}}",
                  "{{ __('Checklist Updated Successfully!')}}",
                  "success",
                );
              }
            },
            el,
            "checklist_update_unavailable",
          );
        },
      );
    });
  };
  const deleteChecklist = (): void => {
    bindOnce("checklist-delete", function (): void {
      $(document).on(
        "click.checklistDel",
        ".delete-checklist",
        function (): void {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          const btn = $(this);
          const el = this as HTMLElement;
          const url = resolveUrl(el, btn.attr("data-url") ?? null);
          if (!url) {
            scheduleInteractiveError(
              getMsg(el, "checklist_delete_unavailable"),
            );
            return;
          }
          ajaxDelete(
            url,
            function (): void {
              const sid = $(".side-modal").attr("id");
              if (sid != null && sid !== "") {
                load_task(sid);
              }
              if (window.show_toastr) {
                window.show_toastr(
                  "{{__('Success')}}",
                  "{{ __('Checklist Deleted Successfully!')}}",
                  "success",
                );
              }
              btn.closest(".checklist-member").remove();
            },
            el,
            "checklist_delete_unavailable",
          );
        },
      );
    });
  };
  const favToggle = (): void => {
    bindOnce("favorite", function (): void {
      $(document).on("click.favorite", "#add_favourite", function (): void {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const btn = $(this);
        const el = this as HTMLElement;
        const url = resolveUrl(el, btn.attr("data-url") ?? null);
        if (!url) {
          scheduleInteractiveError(getMsg(el, "favorite_unavailable"));
          return;
        }
        ajaxPost(
          url,
          {},
          function (data: unknown) {
            const d = data as Record<string, unknown>;
            if (d.fav === 1) {
              $("#add_favourite").addClass("action-favorite");
            } else if (d.fav === 0) {
              $("#add_favourite").removeClass("action-favorite");
            }
          },
          el,
          "favorite_unavailable",
        );
      });
    });
  };
  const completeToggle = (): void => {
    bindOnce("complete", function (): void {
      $(document).on("change.complete", "#complete_task", function (): void {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const cb = $(this);
        const el = this as HTMLElement;
        const url = resolveUrl(el, cb.attr("data-url") ?? null);
        if (!url) {
          scheduleInteractiveError(getMsg(el, "complete_unavailable"));
          return;
        }
        ajaxPost(
          url,
          {},
          function (data: unknown) {
            const d = data as Record<string, unknown>;
            if (d && typeof d.com !== "undefined") {
              $("#complete_task").prop("checked", !!d.com);
            }
            if (d.task && d.stage) {
              $("#" + String(d.task)).insertBefore(
                $("#task-list-" + String(d.stage) + " .empty-container"),
              );
              load_task(String(d.task));
            }
          },
          el,
          "complete_unavailable",
        );
      });
    });
  };
  const progressMove = (): void => {
    bindOnce("progress", function (): void {
      $(document).on("change.progress", "#task_progress", function (): void {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        const sel = $(this);
        const el = this as HTMLElement;
        const url = resolveUrl(el, sel.attr("data-url") ?? null);
        if (!url) {
          scheduleInteractiveError(getMsg(el, "progress_unavailable"));
          return;
        }
        const progress = String(sel.val() ?? "");
        $("#t_percentage").html(progress);
        ajaxPost(
          url,
          { progress: progress },
          function (data: unknown) {
            const d = data as Record<string, unknown>;
            if (d.task_id) {
              load_task(String(d.task_id));
            }
          },
          el,
          "progress_unavailable",
        );
      });
    });
  };
  const ajaxCsrfHeader = (): void => {
    if (!($ as unknown as { ajaxSetup?: unknown }).ajaxSetup) {
      return;
    }
    (
      $ as unknown as { ajaxSetup: (options: Record<string, unknown>) => void }
    ).ajaxSetup({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") ?? "",
      },
    });
  };
  const load_task = (id: string): void=> {
    const base = "{{route(VW::PRJ_TSK_C.'.get','_task_id')}}".replace(
      "_task_id",
      id ?? "",
    );
    const url = resolveUrl(document.body, base);
    if (!url) {
      scheduleInteractiveError(getMsg(document.body, "load_task_unavailable"));
      return;
    }
    $.ajax({
      url: url,
      dataType: "html",
      cache: false,
      success: function (data: unknown) {
        if (id) {
          const c = document.getElementById(id);
          if (c) {
            $("#" + id).html("");
            $("#" + id).html(String(data ?? ""));
          }
        }
      },
      error: function (): void {
        scheduleInteractiveError(
          getMsg(document.body, "load_task_unavailable"),
        );
      },
    });
  };
  const init = (): void => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    ajaxCsrfHeader();
    initDragula();
    toggleSelectionUser();
    deleteTask();
    addComment();
    deleteComment();
    addChecklist();
    updateChecklist();
    deleteChecklist();
    favToggle();
    completeToggle();
    progressMove();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
