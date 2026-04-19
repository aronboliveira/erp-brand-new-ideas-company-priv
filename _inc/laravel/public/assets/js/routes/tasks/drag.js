/**
 * @fileoverview TypeScript version of public/assets/js/routes/tasks/drag.js
 * @generated from original JavaScript - manual review recommended
 * @module drag
 */
(function () {
    const $ = window.jQuery;
    if (!$) {
        console.error("jQuery not available");
        return;
    }
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-error-guard", dataBound = "data-bound-", now = "{{__('Now')}}";
    const ensureToastContainer = () => {
        let c = document.getElementById("np-toast-container");
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
        const hasBootstrap = (document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') ??
            document.querySelector('link[href*="bootstrap"]')) &&
            window.bootstrap.Toast;
        if (hasBootstrap) {
            const container = ensureToastContainer();
            let t = document.getElementById("np-toast");
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
            const body = t.querySelector(".toast-body");
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
    const scheduleInteractiveError = (message) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                host.removeAttribute(dataErrGuard);
            }
        };
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
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
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
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
                window.translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    window.translations?.en?.[msgKey] ??
                    errFb;
            if (el && msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const resolveUrl = (el, explicit) => {
        const url = el.getAttribute("data-url") ?? "", href = el
            ? el.tagName === "FORM"
                ? (el.getAttribute("action") ?? "")
                : (el.getAttribute("href") ?? "")
            : "";
        if ((!explicit || explicit === "#") &&
            (!url || url === "#") &&
            (!href || href === "#")) {
            scheduleInteractiveError(getMsg(el ?? document.body, "ajax_unavailable"));
            return null;
        }
        return explicit && explicit !== "#"
            ? explicit
            : url && url !== "#"
                ? url
                : href;
    };
    const ajaxPost = (endpoint, data, onSuccess, elForMsg, msgKey) => {
        const url = endpoint ?? "";
        if (!url) {
            scheduleInteractiveError(getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"));
            return;
        }
        $.ajax({
            url: url,
            type: "POST",
            data: data || {},
            cache: false,
            success: function (d) {
                if (typeof onSuccess === "function")
                    onSuccess(d);
            },
            error: function () {
                scheduleInteractiveError(getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"));
            },
        });
    };
    const ajaxDelete = (endpoint, onSuccess, elForMsg, msgKey) => {
        const url = endpoint ?? "";
        if (!url) {
            scheduleInteractiveError(getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"));
            return;
        }
        $.ajax({
            url: url,
            type: "DELETE",
            dataType: "JSON",
            cache: false,
            success: function (d) {
                if (typeof onSuccess === "function")
                    onSuccess(d);
            },
            error: function () {
                scheduleInteractiveError(getMsg(elForMsg ?? document.body, msgKey ?? "ajax_unavailable"));
            },
        });
    };
    const initDragula = () => {
        if (!window.dragula) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("dragula unavailable");
            }
            catch (_) {
                console.error(`[drag] Error:`, _);
            }
            scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
            return;
        }
        $('[data-plugin="dragula"]').each(function () {
            const hostEl = this;
            const $host = $(hostEl);
            const containers = $host.data("containers");
            const nodes = [];
            if (containers?.length) {
                for (let i = 0; i < containers.length; i++) {
                    const n = document.getElementById(containers[i]);
                    if (n)
                        nodes.push(n);
                }
            }
            else {
                nodes.push(hostEl);
            }
            const handleClass = $host.data("handleclass");
            const drake = handleClass
                ? window.dragula(nodes, {
                    moves: function (_el, _c, handle) {
                        return handle.classList.contains(handleClass);
                    },
                })
                : window.dragula(nodes);
            drake.on("drop", function (el, target, source) {
                try {
                    const elHtml = el, targetHtml = target, sourceHtml = source;
                    if (!targetHtml || !sourceHtml || !elHtml) {
                        scheduleInteractiveError(getMsg(document.body, "drag_unavailable"));
                        return;
                    }
                    const sort = [];
                    $("#" + targetHtml.id + " > div").each(function () {
                        const itemEl = this;
                        sort[$(itemEl).index()] = $(itemEl).attr("id");
                    });
                    const id = elHtml.id, old_stage = $("#" + sourceHtml.id).data("status"), new_stage = $("#" + targetHtml.id).data("status"), project_id = "{{$project->id}}";
                    $("#" + sourceHtml.id)
                        .parent()
                        .find(".count")
                        .text(String($("#" + sourceHtml.id + " > div").length));
                    $("#" + targetHtml.id)
                        .parent()
                        .find(".count")
                        .text(String($("#" + targetHtml.id + " > div").length));
                    const explicit = "{{route(VW::PRJ . '.tasks.update.order',[$project->id])}}", endpoint = resolveUrl(targetHtml, explicit);
                    if (!endpoint) {
                        scheduleInteractiveError(getMsg(targetHtml, "update_order_unavailable"));
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
                        success: function () { },
                        error: function () {
                            scheduleInteractiveError(getMsg(targetHtml, "update_order_unavailable"));
                        },
                    });
                }
                catch (_) {
                    scheduleInteractiveError(getMsg(document.body, "update_order_unavailable"));
                }
            });
            const mo = new MutationObserver((_m, o) => {
                if (!document.body.contains($host.get(0))) {
                    try {
                        drake.destroy();
                    }
                    catch (_) {
                        console.error(`[drag] Error:`, _);
                    }
                    o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        });
    };
    const bindOnce = (key, binder) => {
        const root = document.documentElement, attr = dataBound + key;
        if (root.getAttribute(attr) === "true")
            return;
        root.setAttribute(attr, "true");
        binder();
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(root))
                o.disconnect();
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const toggleSelectionUser = () => {
        bindOnce("add-usr", function () {
            $(document).on("click.addUsr", ".add_usr", function () {
                const btnEl = this;
                try {
                    const ids = [];
                    const $btn = $(btnEl);
                    $btn.toggleClass("selected");
                    const crr_id = $btn.attr("data-id"), t = $("#usr_txt_" + crr_id);
                    t.html(t.html() === "Add" ? "{{__('Added')}}" : "{{__('Add')}}");
                    const ic = $("#usr_icon_" + crr_id);
                    if (ic.hasClass("fa-plus")) {
                        ic.removeClass("fa-plus").addClass("fa-check");
                    }
                    else {
                        ic.removeClass("fa-check").addClass("fa-plus");
                    }
                    $(".selected").each(function () {
                        const selEl = this;
                        ids.push($(selEl).attr("data-id"));
                    });
                    $('input[name="assign_to"]').val(ids.filter((id) => id !== undefined));
                }
                catch (_) {
                    console.error(`[drag] Error:`, _);
                }
            });
        });
    };
    const deleteTask = () => {
        bindOnce("del-task", function () {
            $(document).on("click.delTask", ".del_task", function () {
                const el = this;
                const $btn = $(el), url = resolveUrl(el, $btn.attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "delete_task_unavailable"));
                    return;
                }
                ajaxDelete(url, function (data) {
                    const d = data;
                    if (d.task_id)
                        $("#" + String(d.task_id)).remove();
                    if (window.show_toastr)
                        window.show_toastr("{{__('Success')}}", "{{ __('Task Deleted Successfully!')}}", "success");
                }, el, "delete_task_unavailable");
            });
        });
    };
    const addComment = () => {
        bindOnce("comment-submit", function () {
            $(document).on("click.commentSubmit", "#comment_submit", function () {
                const el = this;
                const curr = $(el), v = String($("#form-comment textarea[name='comment']").val() ?? "").trim();
                if (!v) {
                    if (window.show_toastr)
                        window.show_toastr("{{__('Error')}}", "{{ __('Please write comment!')}}", "error");
                    return;
                }
                const form = document.getElementById("form-comment");
                if (!form)
                    return;
                const url = resolveUrl(form, String($("#form-comment").data("action") ?? ""));
                if (!url) {
                    scheduleInteractiveError(getMsg(form, "comment_add_unavailable"));
                    return;
                }
                ajaxPost(url, { comment: v }, function (data) {
                    try {
                        const d = (typeof data === "string" ? JSON.parse(data) : data), user = d.user;
                        const html = "<div class='list-group-item px-0'><div class='row align-items-center'><div class='col-auto'><a href='#' class='avatar avatar-sm rounded-circle'><img " +
                            (user?.img_avatar ? user.img_avatar : "") +
                            " alt='" +
                            (user?.name ? user.name : "") +
                            "'></a></div><div class='col ml-n2'><p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" +
                            (d.comment ?? "") +
                            "</p><small class='d-block'>" +
                            now +
                            "</small></div><div class='col-auto'><a href='#' class='delete-comment' data-url='" +
                            (d.deleteUrl ?? "") +
                            "'><i class='ti ti-trash-alt text-danger'></i></a></div></div></div>";
                        $("#comments").prepend(html);
                        $("#form-comment textarea[name='comment']").val("");
                        const sid = curr.closest(".side-modal").attr("id");
                        if (sid != null && sid !== "")
                            load_task(sid);
                        if (window.show_toastr)
                            window.show_toastr("{{__('Success')}}", "{{ __('Comment Added Successfully!')}}", "success");
                    }
                    catch (_) {
                        scheduleInteractiveError(getMsg(form, "comment_add_unavailable"));
                    }
                }, form, "comment_add_unavailable");
            });
        });
    };
    const deleteComment = () => {
        bindOnce("comment-delete", function () {
            $(document).on("click.commentDel", ".delete-comment", function () {
                const el = this;
                const btn = $(el), url = resolveUrl(el, btn.attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "comment_delete_unavailable"));
                    return;
                }
                ajaxDelete(url, function () {
                    const sid = btn.closest(".side-modal").attr("id");
                    if (sid != null && sid !== "")
                        load_task(sid);
                    if (window.show_toastr)
                        window.show_toastr("{{__('Success')}}", "{{ __('Comment Deleted Successfully!')}}", "success");
                    btn.closest(".list-group-item").remove();
                }, el, "comment_delete_unavailable");
            });
        });
    };
    const addChecklist = () => {
        bindOnce("checklist-add", function () {
            $(document).on("click.checklistAdd", "#checklist_submit", function () {
                const name = $("#form-checklist input[name=name]").val() ?? "";
                if (!name) {
                    if (window.show_toastr)
                        window.show_toastr("{{__('Error')}}", "{{ __('Please write checklist name!')}}", "error");
                    return;
                }
                const form = document.getElementById("form-checklist");
                if (!form)
                    return;
                const url = resolveUrl(form, String($("#form-checklist").data("action") ?? ""));
                if (!url) {
                    scheduleInteractiveError(getMsg(form, "checklist_add_unavailable"));
                    return;
                }
                ajaxPost(url, { name: name }, function (data) {
                    try {
                        const d = (typeof data === "string" ? JSON.parse(data) : data);
                        const html = '<div class="card border shadow-none checklist-member"><div class="px-3 py-2 row align-items-center"><div class="col-10"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="check-item-' +
                            (d.id ?? "") +
                            '" value="' +
                            (d.id ?? "") +
                            '" data-url="' +
                            (d.updateUrl ?? "") +
                            '"><label class="custom-control-label h6 text-sm" for="check-item-' +
                            (d.id ?? "") +
                            '">' +
                            (d.name ?? "") +
                            "</label></div></div><div class='col-auto card-meta d-inline-flex align-items-center ml-sm-auto'><a href='#' class='action-item delete-checklist' role='button' data-url='" +
                            (d.deleteUrl ?? "") +
                            "'><i class='ti ti-trash-alt text-danger'></i></a></div></div></div>";
                        $("#checklist").append(html);
                        $("#form-checklist input[name=name]").val("");
                        $("#form-checklist").collapse("toggle");
                        const sid = $(".side-modal").attr("id");
                        if (sid != null && sid !== "")
                            load_task(sid);
                        if (window.show_toastr)
                            window.show_toastr("{{__('Success')}}", "{{ __('Checklist Added Successfully!')}}", "success");
                    }
                    catch (_) {
                        scheduleInteractiveError(getMsg(form, "checklist_add_unavailable"));
                    }
                }, form, "checklist_add_unavailable");
            });
        });
    };
    const updateChecklist = () => {
        bindOnce("checklist-update", function () {
            $(document).on("change.checklistToggle", "#checklist input[type=checkbox]", function () {
                const el = this;
                const url = resolveUrl(el, $(el).attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "checklist_update_unavailable"));
                    return;
                }
                ajaxPost(url, {}, function () {
                    const sid = $(".side-modal").attr("id");
                    if (sid != null && sid !== "")
                        load_task(sid);
                    if (window.show_toastr)
                        window.show_toastr("{{__('Success')}}", "{{ __('Checklist Updated Successfully!')}}", "success");
                }, el, "checklist_update_unavailable");
            });
        });
    };
    const deleteChecklist = () => {
        bindOnce("checklist-delete", function () {
            $(document).on("click.checklistDel", ".delete-checklist", function () {
                const el = this;
                const btn = $(el), url = resolveUrl(el, btn.attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "checklist_delete_unavailable"));
                    return;
                }
                ajaxDelete(url, function () {
                    const sid = $(".side-modal").attr("id");
                    if (sid != null && sid !== "")
                        load_task(sid);
                    if (window.show_toastr)
                        window.show_toastr("{{__('Success')}}", "{{ __('Checklist Deleted Successfully!')}}", "success");
                    btn.closest(".checklist-member").remove();
                }, el, "checklist_delete_unavailable");
            });
        });
    };
    const favToggle = () => {
        bindOnce("favorite", function () {
            $(document).on("click.favorite", "#add_favourite", function () {
                const el = this;
                const btn = $(el), url = resolveUrl(el, btn.attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "favorite_unavailable"));
                    return;
                }
                ajaxPost(url, {}, function (data) {
                    const d = data;
                    if (d.fav === 1) {
                        $("#add_favourite").addClass("action-favorite");
                    }
                    else if (d.fav === 0) {
                        $("#add_favourite").removeClass("action-favorite");
                    }
                }, el, "favorite_unavailable");
            });
        });
    };
    const completeToggle = () => {
        bindOnce("complete", function () {
            $(document).on("change.complete", "#complete_task", function () {
                const el = this;
                const cb = $(el), url = resolveUrl(el, cb.attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "complete_unavailable"));
                    return;
                }
                ajaxPost(url, {}, function (data) {
                    const d = data;
                    if (d && typeof d.com !== "undefined")
                        $("#complete_task").prop("checked", !!d.com);
                    if (d.task && d.stage) {
                        $("#" + String(d.task)).insertBefore($("#task-list-" + String(d.stage) + " .empty-container"));
                        load_task(String(d.task));
                    }
                }, el, "complete_unavailable");
            });
        });
    };
    const progressMove = () => {
        bindOnce("progress", function () {
            $(document).on("change.progress", "#task_progress", function () {
                const el = this;
                const sel = $(el), url = resolveUrl(el, sel.attr("data-url") ?? null);
                if (!url) {
                    scheduleInteractiveError(getMsg(el, "progress_unavailable"));
                    return;
                }
                const progress = String(sel.val() ?? "");
                $("#t_percentage").html(progress);
                ajaxPost(url, { progress: progress }, function (data) {
                    const d = data;
                    if (d.task_id)
                        load_task(String(d.task_id));
                }, el, "progress_unavailable");
            });
        });
    };
    const ajaxCsrfHeader = () => {
        if (!$.ajaxSetup)
            return;
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") ?? "",
            },
        });
    };
    const load_task = (id) => {
        const base = "{{route(VW::PRJ_TSK_C.'.get','_task_id')}}".replace("_task_id", id ?? ""), url = resolveUrl(document.body, base);
        if (!url) {
            scheduleInteractiveError(getMsg(document.body, "load_task_unavailable"));
            return;
        }
        $.ajax({
            url: url,
            dataType: "html",
            cache: false,
            success: function (data) {
                if (id) {
                    const c = document.getElementById(id);
                    if (c) {
                        $("#" + id).html("");
                        $("#" + id).html(String(data ?? ""));
                    }
                }
            },
            error: function () {
                scheduleInteractiveError(getMsg(document.body, "load_task_unavailable"));
            },
        });
    };
    const init = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[drag] Error:`, _);
            }
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
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", init, { once: true })
        : init();
})();
//# sourceMappingURL=drag.js.map