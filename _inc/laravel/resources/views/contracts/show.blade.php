@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Contract, Plan, User, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    use Illuminate\Support\{Collection, File, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $attachments = Utility::getFile('contract_attachment');
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Contract Detail') }}
@endsection
@if(!empty($contract) && isset($contract->id))
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
        <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
        <script async src="{{ asset('asset/js/routes/contracts/lang/attachment.js') }}"></script>
        <script async>
            (function () {
                const $ = window.jQuery;
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const dataSvLocalized = "data-sv-localized";
                const dataStatusBound = "data-status-bound";
                const dataDzBound = "data-dz-bound";
                const dataCmtBound = "data-cmt-bound";
                const dataSpyBound = "data-spy-bound";
                const nsStatus = "._npStatus";
                const nsCmt = "._npCmt";
                const qs = (s, r = document) => r.querySelector(s);
                const hasBS = () =>
                    !!(
                    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                    qs('link[href*="bootstrap"]')
                    ) && !!(window.bootstrap && window.bootstrap.Toast);
                const ensureToastContainer = () => {
                    let c = qs("#np-toast-container");
                    if (c) return c;
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
                    if (hasBS()) {
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
                    const body = t.querySelector(".toast-body");
                    if (body) body.textContent = message ?? errFb;
                    try {
                        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                    } catch (_) {
                        alert(message ?? errFb);
                    }
                    } else {
                    alert(message ?? errFb);
                    }
                };
                const scheduleClickError = msg => {
                    const host = document.body;
                    if (!host || host.getAttribute("data-error-armed") === "true") return;
                    host.setAttribute("data-error-armed", "true");
                    const once = () => {
                    try {
                        showErrorNow(msg);
                    } finally {
                        host.removeAttribute("data-error-armed");
                    }
                    };
                    document.addEventListener("click", once, { once: true, capture: true });
                    const mo = new MutationObserver((m, o) => {
                    if (!document.body.contains(host)) {
                        document.removeEventListener("click", once, { capture: true });
                        o.disconnect();
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };
                const localize = (el, key) => {
                    let msg = errFb;
                    if (
                    el?.getAttribute?.(dataSvLocalized) === "true" ||
                    el?.getAttribute?.(dataClientLocalized) === "true"
                    )
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                    else {
                    let lang = (
                        window.sessionStorage.getItem("erp-np-lang") ||
                        document.documentElement.lang ||
                        "en"
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
                const verifyRouteFromEl = el => {
                    const url = el?.getAttribute?.("data-url");
                    const href = el?.getAttribute?.("href");
                    if ((!url || url === "#") && (!href || href === "#")) return "";
                    return url && url !== "#" ? url : href && href !== "#" ? href : "";
                };
                const token = () =>
                    document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ?? "";
                const ensureJq = () => {
                    if (!$ || !$.fn) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("jQuery unavailable");
                    } catch (_) {}
                    scheduleClickError(localize(document.body, "plugin_unavailable"));
                    return false;
                    }
                    return true;
                };
                const ensureDropzone = () => {
                    if (!window.Dropzone) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("Dropzone unavailable");
                    } catch (_) {}
                    scheduleClickError(localize(document.body, "plugin_unavailable"));
                    return false;
                    }
                    return true;
                };

                const bindStatus = () => {
                    if (!ensureJq()) return;
                    const host = document.body;
                    if (host.getAttribute(dataStatusBound) === "true") return;
                    host.setAttribute(dataStatusBound, "true");
                    $(document).on("click" + nsStatus, ".status", function () {
                    const el = this;
                    const status = $(el).attr("data-id") ?? "";
                    const route = verifyRouteFromEl(el);
                    if (!route) {
                        scheduleClickError(localize(el, "route_unavailable"));
                        return;
                    }
                    $.ajax({
                        url: route,
                        type: "POST",
                        data: { status: status, _token: token() },
                        success: function (data) {
                        if (typeof window.show_toastr === "function") {
                            window.show_toastr(
                            "success",
                            "Status Update Successfully!",
                            "success"
                            );
                        }
                        try {
                            window.location &&
                            window.location.reload &&
                            window.location.reload();
                        } catch (_) {}
                        },
                        error: function () {
                        scheduleClickError(localize(el, "status_update_unavailable"));
                        },
                    });
                    });
                    const mo = new MutationObserver(function () {
                    if (!$(".status").length) {
                        $(document).off("click" + nsStatus, ".status");
                        host.removeAttribute(dataStatusBound);
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };

                const bindDropzone = () => {
                    if (!ensureJq() || !ensureDropzone()) return;
                    const dzEl = qs("#dropzonewidget");
                    if (!dzEl || dzEl.getAttribute(dataDzBound) === "true") return;
                    dzEl.setAttribute(dataDzBound, "true");
                    try {
                    window.Dropzone.autoDiscover = true;
                    } catch (_) {}
                    let myDropzone;
                    try {
                    myDropzone = new window.Dropzone("#dropzonewidget", {
                        maxFiles: 20,
                        parallelUploads: 1,
                        url: "{{route(VW::CTC . '.file.upload',[$contract->id])}}",
                    });
                    } catch (_) {
                    scheduleClickError(localize(dzEl, "attachment_upload_unavailable"));
                    return;
                    }
                    myDropzone.on("success", function (file, response) {
                    const ok = !!(response && response.is_success);
                    if (ok) {
                        if (Number(response.status) === 1) {
                        if (typeof window.show_toastr === "function")
                            window.show_toastr(
                            "success",
                            response.success_msg ?? "",
                            "success"
                            );
                        } else {
                        if (typeof window.show_toastr === "function")
                            window.show_toastr(
                            "success",
                            "Attachment Create Successfully!",
                            "success"
                            );
                        dropzoneBtn(file, response);
                        }
                    } else {
                        try {
                        myDropzone.removeFile(file);
                        } catch (_) {}
                        if (typeof window.show_toastr === "function")
                        window.show_toastr(
                            "error",
                            "The attachment must be same as stoarge setting",
                            "error"
                        );
                    }
                    });
                    myDropzone.on("error", function (file, response) {
                    try {
                        myDropzone.removeFile(file);
                    } catch (_) {}
                    if (typeof window.show_toastr === "function")
                        window.show_toastr(
                        "error",
                        "The attachment must be same as stoarge setting",
                        "error"
                        );
                    });
                    myDropzone.on("sending", function (file, xhr, formData) {
                    try {
                        formData.append("_token", token());
                        formData.append("contract_id", "{{$contract->id}}");
                    } catch (_) {}
                    });
                    const dropzoneBtn = function (file, response) {
                    if (!file || !file.previewTemplate) return;
                    const tmpl = file.previewTemplate;
                    if (!tmpl.querySelector(".np-dz-actions")) {
                        const wrap = document.createElement("div");
                        wrap.className = "np-dz-actions text-center mt-10";
                        tmpl.appendChild(wrap);
                    }
                    const wrap = tmpl.querySelector(".np-dz-actions");
                    const addBtn = (cls, title, href, icon) => {
                        if (!href || href === "#") return;
                        const key = cls + "-" + href;
                        if (wrap.querySelector('[data-key="' + key + '"]')) return;
                        const a = document.createElement("a");
                        a.setAttribute("data-key", key);
                        a.href = href;
                        a.className = cls;
                        a.setAttribute("data-toggle", "tooltip");
                        a.setAttribute("data-original-title", title);
                        a.innerHTML = icon;
                        wrap.appendChild(a);
                        return a;
                    };
                    const dl = addBtn(
                        "action-btn btn-primary mx-1 mt-1 btn btn-sm d-inline-flex align-items-center",
                        "{{__('Download')}}",
                        response.download ?? "",
                        "<i class='fas fa-download'></i>"
                    );
                    const del = addBtn(
                        "action-btn btn-danger mx-1 mt-1 btn btn-sm d-inline-flex align-items-center",
                        "{{__('Delete')}}",
                        response.delete ?? "",
                        "<i class='ti ti-trash'></i>"
                    );
                    if (del && !del.getAttribute("data-del-bound")) {
                        del.setAttribute("data-del-bound", "true");
                        del.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const route = verifyRouteFromEl(this);
                        if (!route) {
                            scheduleClickError(localize(document.body, "route_unavailable"));
                            return;
                        }
                        if (window.confirm && !window.confirm("Are you sure ?")) return;
                        $.ajax({
                            url: route,
                            type: "DELETE",
                            data: { _token: token() },
                            success: function (res) {
                            try {
                                window.location &&
                                window.location.reload &&
                                window.location.reload();
                            } catch (_) {}
                            if (res && res.is_success) {
                                const p = del.closest(".dz-image-preview");
                                if (p && p.parentNode) p.parentNode.removeChild(p);
                            } else {
                                if (typeof window.show_toastr === "function")
                                window.show_toastr(
                                    "error",
                                    (res && res.error) || "",
                                    "error"
                                );
                            }
                            },
                            error: function (xhr) {
                            const r = xhr && xhr.responseJSON ? xhr.responseJSON : {};
                            if (typeof window.show_toastr === "function")
                                window.show_toastr("error", r.error || "", "error");
                            },
                        });
                        });
                    }
                    };
                    const mo = new MutationObserver(function () {
                    if (!document.body.contains(dzEl)) {
                        dzEl.removeAttribute(dataDzBound);
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };

                const bindComments = () => {
                    if (!ensureJq()) return;
                    const host = document.body;
                    if (host.getAttribute(dataCmtBound) === "true") return;
                    host.setAttribute(dataCmtBound, "true");
                    $(document).on("click" + nsCmt, "#comment_submit", function () {
                    const curr = $(this);
                    const $form = $("#form-comment");
                    const action = $form.data("action") ?? "";
                    if (!action) {
                        scheduleClickError(localize(curr[0], "route_unavailable"));
                        return;
                    }
                    const comment = $.trim(
                        $form.find('textarea[name="comment"]').val() ?? ""
                    );
                    if (!comment) {
                        if (typeof window.show_toastr === "function")
                        window.show_toastr(
                            "error",
                            "{{ __('Please write comment!') }}",
                            "error"
                        );
                        return;
                    }
                    $.ajax({
                        url: action,
                        type: "POST",
                        data: { comment: comment, _token: token() },
                        success: function (data) {
                        if (typeof window.show_toastr === "function")
                            window.show_toastr(
                            "success",
                            "Comment Create Successfully!",
                            "success"
                            );
                        setTimeout(function () {
                            try {
                            window.location &&
                                window.location.reload &&
                                window.location.reload();
                            } catch (_) {}
                        }, 500);
                        try {
                            const d = typeof data === "string" ? JSON.parse(data) : data;
                            const html =
                            "<div class='list-group-item px-0'><div class='row align-items-center'><div class='col-auto'><a href='#' class='avatar avatar-sm rounded-circle ms-2'><img src=" +
                            (d.default_img ?? "") +
                            " alt='' class='avatar-sm rounded-circle'></a></div><div class='col ml-n2'><p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" +
                            (d.comment ?? "") +
                            "</p><small class='d-block'>" +
                            (d.current_time ?? "") +
                            "</small></div><div class='action-btn bg-danger me-4'><div class='col-auto'><a href='#' class='mx-3 btn btn-sm align-items-center delete-comment' data-url='" +
                            (d.deleteUrl ?? "#") +
                            "'><i class='ti ti-trash text-white'></i></a></div></div></div></div>";
                            $("#comments").prepend(html);
                            $form.find('textarea[name="comment"]').val("");
                        } catch (_) {}
                        },
                        error: function () {
                        scheduleClickError(localize(curr[0], "comment_submit_unavailable"));
                        },
                    });
                    });
                    $(document).on("click" + nsCmt, ".delete-comment", function () {
                    const btn = $(this);
                    const route = verifyRouteFromEl(this);
                    if (!route) {
                        scheduleClickError(localize(this, "route_unavailable"));
                        return;
                    }
                    $.ajax({
                        url: route,
                        type: "DELETE",
                        dataType: "JSON",
                        data: { _token: token() },
                        success: function (data) {
                        if (typeof window.show_toastr === "function")
                            window.show_toastr(
                            "success",
                            "{{ __('Comment Deleted Successfully!') }}",
                            "success"
                            );
                        btn.closest(".list-group-item").remove();
                        },
                        error: function (xhr) {
                        const r = xhr && xhr.responseJSON ? xhr.responseJSON : {};
                        if (r.message) {
                            if (typeof window.show_toastr === "function")
                            window.show_toastr("error", r.message, "error");
                        } else {
                            scheduleClickError(localize(btn[0], "comment_delete_unavailable"));
                        }
                        },
                    });
                    });
                    const mo = new MutationObserver(function () {
                    if (!$("#comment_submit").length && !$(".delete-comment").length) {
                        $(document).off("click" + nsCmt, "#comment_submit");
                        $(document).off("click" + nsCmt, ".delete-comment");
                        host.removeAttribute(dataCmtBound);
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };

                const bindScrollSpy = () => {
                    const host = document.body;
                    if (host.getAttribute(dataSpyBound) === "true") return;
                    host.setAttribute(dataSpyBound, "true");
                    try {
                    if (window.bootstrap && window.bootstrap.ScrollSpy) {
                        new window.bootstrap.ScrollSpy(document.body, {
                        target: "#useradd-sidenav",
                        offset: 300,
                        });
                    } else {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("Bootstrap ScrollSpy unavailable");
                    }
                    } catch (_) {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("Bootstrap ScrollSpy unavailable");
                    }
                    $(document).on("click", ".list-group-item", function () {
                    const id = this.getAttribute("href") ?? "";
                    $(".list-group-item")
                        .filter(function () {
                        return (this.getAttribute("href") ?? "") === id;
                        })
                        .parent()
                        .removeClass("text-primary");
                    });
                    const mo = new MutationObserver(function () {
                    if (!$("#useradd-sidenav").length) {
                        host.removeAttribute(dataSpyBound);
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };

                const start = () => {
                    bindStatus();
                    bindDropzone();
                    bindComments();
                    bindScrollSpy();
                };
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", start, { once: true });
                } else {
                    start();
                }
            })();
        </script>
        @can('manage contract')
            <script async src="{{ asset('assets/js/routes/contracts/lang/note.js') }}"></script>
            <script defer src="{{ asset('assets/js/routes/contracts/note.js') }}">
                (function () {
                const $ = window.jQuery;
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const dataSvLocalized = "data-sv-localized";
                const dataBind = "data-contract-desc-bound";
                const dataErr = "data-contract-desc-error";
                const ns = "._npCDesc";
                const qs = (s, r = document) => r.querySelector(s);
                const hasBS = () =>
                    !!(
                    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                    qs('link[href*="bootstrap"]')
                    ) && !!(window.bootstrap && window.bootstrap.Toast);
                const ensureToastContainer = () => {
                    let c = qs("#np-toast-container");
                    if (c) return c;
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
                    if (hasBS()) {
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
                    const body = t.querySelector(".toast-body");
                    if (body) body.textContent = message ?? errFb;
                    try {
                        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                    } catch (_) {
                        alert(message ?? errFb);
                    }
                    } else {
                    alert(message ?? errFb);
                    }
                };
                const schedulePointerupError = msg => {
                    const host = document.body;
                    if (!host || host.getAttribute(dataErr) === "true") return;
                    host.setAttribute(dataErr, "true");
                    const once = () => {
                    try {
                        showErrorNow(msg);
                    } finally {
                        host.removeAttribute(dataErr);
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
                };
                const getMsg = (el, key) => {
                    let msg = errFb;
                    if (
                    el?.getAttribute?.(dataSvLocalized) === "true" ||
                    el?.getAttribute?.(dataClientLocalized) === "true"
                    )
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                    else {
                    let lang = (
                        window.sessionStorage.getItem("erp-np-lang") ||
                        document.documentElement.lang ||
                        "en"
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
                const ensureJq = () => {
                    if (!$ || !$.fn) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("jQuery unavailable");
                    } catch (_) {}
                    schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
                    return false;
                    }
                    return true;
                };
                const verifyRoute = candidate => {
                    const a = document.createElement("a");
                    a.setAttribute("data-url", candidate ?? "");
                    a.href = candidate ?? "";
                    const url = a.getAttribute("data-url");
                    const href = a.href;
                    if ((!url || url === "#") && (!href || href === "#")) return false;
                    return true;
                };
                const bind = () => {
                    if (!ensureJq()) return;
                    const host = document.body;
                    if (host.getAttribute(dataBind) === "true") return;
                    host.setAttribute(dataBind, "true");
                    $(document).on("summernote.blur" + ns, ".summernote-simple", function () {
                    const $el = $(this);
                    const value = $el.val() ?? "";
                    const url =
                        "{{route(VW::CTC.'.contract_description.store',$contract->id)}}";
                    if (!verifyRoute(url)) {
                        schedulePointerupError(getMsg(document.body, "route_unavailable"));
                        return;
                    }
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                        _token:
                            document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") ?? "",
                        contract_description: value,
                        },
                        success: function (response) {
                        try {
                            const ok = !!(response && response.is_success);
                            if (ok && typeof window.show_toastr === "function") {
                            window.show_toastr("success", response.success ?? "", "success");
                            } else if (!ok && typeof window.show_toastr === "function") {
                            window.show_toastr("error", response.error ?? "", "error");
                            } else if (!ok) {
                            schedulePointerupError(
                                getMsg(document.body, "contract_desc_save_unavailable")
                            );
                            }
                        } catch (_) {
                            schedulePointerupError(
                            getMsg(document.body, "contract_desc_save_unavailable")
                            );
                        }
                        },
                        error: function (xhr) {
                        try {
                            const r = xhr && xhr.responseJSON ? xhr.responseJSON : {};
                            const m =
                            r.error ||
                            getMsg(document.body, "contract_desc_save_unavailable");
                            document.body.setAttribute(dataGuardMsg, m);
                            schedulePointerupError(
                            getMsg(document.body, "contract_desc_save_unavailable")
                            );
                        } catch (_) {
                            schedulePointerupError(
                            getMsg(document.body, "contract_desc_save_unavailable")
                            );
                        }
                        },
                    });
                    });
                    const mo = new MutationObserver(function () {
                    if (!$(".summernote-simple").length) {
                        $(document).off("summernote.blur" + ns, ".summernote-simple");
                        host.removeAttribute(dataBind);
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", bind, { once: true });
                } else {
                    bind();
                }
                })();
            </script>
        @endcan
    @endpush
    @php
        $cid = isset($contract->id) ? $contract->id : null;
        $userType = $user?->{UsersConstants::COL_TP} ?? null;
        $isCompany = $user && $userType === PermissionsConstants::CPN;
        $isClient = $user && $userType === PermissionsConstants::CL;
        $statusText = (isset($contract->status) && $contract->status !== '') ? ucfirst($contract->status) : __('No status available');
        $statusListRaw = Contract::status();
        $statusList = Utility::isFilled($statusListRaw ?? []) ? $statusListRaw : [];
        $msgDownload = Utility::fetchLinkMessage($lang, VW::CTC, 'download_pdf_contracts_route_unavailable')
                        ?? 'Contracts PDF download route is unavailable. Please contact technical support or your domain administrator.';
        $msgPreview  = Utility::fetchLinkMessage($lang, VW::CTC, 'preview_route_unavailable')
        ?? 'Preview route is unavailable. Please contact technical support or your domain administrator.';
        $msgSign     = Utility::fetchLinkMessage($lang, VW::CTC, 'signature_contracts_route_unavailable')
                        ?? 'Signature route is unavailable. Please contact technical support or your domain administrator.';
        if ($isCompany) {
            $msgSendMail = Utility::fetchLinkMessage($lang, VW::CTC, 'send_mail_route_unavailable')
                            ?? 'Send mail route is unavailable. Please contact technical support or your domain administrator.';
            $msgCopy     = Utility::fetchLinkMessage($lang, VW::CTC, 'copy_route_unavailable')
                            ?? 'Copy route is unavailable. Please contact technical support or your domain administrator.';
        }
        if ($isClient) {
            $msgStatus   = Utility::fetchLinkMessage($lang, VW::CTC, 'status_route_unavailable')
                            ?? 'Status change route is unavailable. Please contact technical support or your domain administrator.';
        }
    @endphp
    @section(YieldingConstants::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            @php
                $contractsIndexBaseRouteName = VW::CTC.'.index';
                $contractsIndexKebabRouteName = Str::kebab($contractsIndexBaseRouteName);
                $contractsIndexResolvedRouteName = Route::has($contractsIndexBaseRouteName)
                    ? $contractsIndexBaseRouteName
                    : (Route::has($contractsIndexKebabRouteName) ? $contractsIndexKebabRouteName : null);
                $contractsIndexUrl = $contractsIndexResolvedRouteName ? route($contractsIndexResolvedRouteName) : '#';
                $contractsLangValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $contractsIndexGuardMessage = Utility::fetchLinkMessage($contractsLangValue, VW::CTC, 'index_route_unavailable')
                    ?? 'Contracts index route is unavailable. Please contact technical support or your domain administrator.';
                $contractsIndexLinkId = 'contracts-index-list-link';
            @endphp
            <a id="{{ $contractsIndexLinkId }}"
                href="{{ $contractsIndexUrl }}" data-sv-localized="true" 
                data-url="{{ $contractsIndexUrl }}"
                data-guard-msg="{{ $contractsIndexGuardMessage }}">
                {{ __('Contract') }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/contracts/index.js') }}"></script>
            @endpush
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ method_exists($user, 'contractNumberFormat') ? $user->contractNumberFormat($cid) : __('Failed to format contract number') }}</li>
    @endsection
    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="{{ VC::FEND }} {{ VC::DFL_AIC }}">
            @php
                $dlBase   = VW::CTC . '.download.pdf';
                $dlKebab  = Str::kebab($dlBase);
                $dlName   = Route::has($dlBase) ? $dlBase : (Route::has($dlKebab) ? $dlKebab : null);
                $dlUrl    = ($dlName && $encId) ? route($dlName, $encId) : '#';
                $dlId     = 'contracts-download-pdf-link-' . ($cid === '' ? 'x' : $cid);
            @endphp
            <a id="{{ $dlId }}"
            href="{{ $dlUrl }}"
            class="{{ VC::BT_SM_PM }} btn-icon m-1"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="{{ __('Download') }}"
            target="_blank"
            data-url="{{ $dlUrl }}"
            data-guard-msg="{{ $msgDownload }}"
            data-sv-localized="true">
                <i class="{{ VC::TI_DWN }}"></i>
            </a>
            @php
                $previewName = VW::CTC . '.get';
                $previewUrl  = ($cid !== '' && Route::has($previewName)) ? route($previewName, $cid) : '#';
                $previewId   = 'contracts-preview-link-' . ($cid === '' ? 'x' : $cid);
            @endphp
            <a id="{{ $previewId }}"
            href="{{ $previewUrl }}"
            target="_blank"
            class="{{ VC::BT_SM_PM }} btn-icon m-1"
            data-sv-localized="true"
            data-guard-msg="{{ $msgPreview }}">
                <i class="{{ VC::TI_EYE_WT }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('PreView') }}"></i>
            </a>
            @if($isCompany)
                @php
                    $sendName  = VW::CTC . '.send.mail';
                    $sendUrl   = ($cid !== '' && Route::has($sendName)) ? route($sendName, $cid) : '#';
                    $sendId    = 'contracts-send-mail-link-' . ($cid === '' ? 'x' : $cid);

                    $copyName  = VW::CTC . '.copy';
                    $copyUrl   = ($cid !== '' && Route::has($copyName)) ? route($copyName, $cid) : '#';
                    $copyId    = 'contracts-duplicate-link-' . ($cid === '' ? 'x' : $cid);

                    $signName  = VW::CTC . '.signature';
                    $signUrl   = ($cid !== '' && Route::has($signName)) ? route($signName, $cid) : '#';
                    $signId    = 'contracts-signature-link-' . ($cid === '' ? 'x' : $cid);
                @endphp

                <a id="{{ $sendId }}"
                href="{{ $sendUrl }}"
                class="{{ VC::BT_SM_PM }} btn-icon m-1"
                data-sv-localized="true"
                data-guard-msg="{{ $msgSendMail }}"
                data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Send Email') }}">
                    <i class="ti ti-mail {{ VC::TXT_WT }}"></i>
                </a>

                <a id="{{ $copyId }}"
                href="#"
                class="{{ VC::BT_SM_PM }} btn-icon m-1"
                data-size="lg"
                data-url="{{ $copyUrl }}"
                data-ajax-popup="true"
                data-sv-localized="true"
                data-guard-msg="{{ $msgCopy }}"
                data-bs-toggle="tooltip"
                title="{{ __('Duplicate') }}">
                    <i class="ti ti-copy {{ VC::TXT_WT }}"></i>
                </a>

                <a id="{{ $signId }}"
                href="#"
                class="{{ VC::BT_SM_PM }} btn-icon m-1"
                data-size="lg"
                data-url="{{ $signUrl }}"
                data-ajax-popup="true"
                data-sv-localized="true"
                data-guard-msg="{{ $msgSign }}"
                data-bs-toggle="tooltip"
                data-title="{{ __('Add signature') }}">
                    <i class="{{ VC::TI_PC_WT }}"></i>
                </a>
            @endif
            @if($isClient && strtolower((string) data_get($contract,'status','')) === 'accept')
                @php
                    $cSignName = VW::CTC . '.signature';
                    $cSignUrl  = ($cid !== '' && Route::has($cSignName)) ? route($cSignName, $cid) : '#';
                    $cSignId   = 'contracts-client-signature-link-' . ($cid === '' ? 'x' : $cid);
                @endphp
                <a id="{{ $cSignId }}"
                href="#"
                class="{{ VC::BT_SM_PM }} btn-icon m-1"
                data-size="lg"
                data-url="{{ $cSignUrl }}"
                data-ajax-popup="true"
                data-sv-localized="true"
                data-guard-msg="{{ $msgSign }}"
                data-bs-toggle="tooltip"
                data-title="{{ __('Add signature') }}">
                    <i class="{{ VC::TI_PC_WT }}"></i>
                </a>
            @endif
            @if($isClient)
                @php
                    $statusText = (string) (data_get($contract,'status') ? ucfirst(data_get($contract,'status')) : __('No status available'));
                    $statusListRaw = \App\Models\Contract::status();
                    $statusList = Utility::isFilled($statusListRaw ?? []) ? $statusListRaw : [];
                    $statusName = VW::CTC . '.status';
                    $statusUrl  = ($cid !== '' && Route::has($statusName)) ? route($statusName, $cid) : '#';
                @endphp
                <ul class="list-unstyled m-0">
                    <li class="{{ VC::STT_DD_IT }}">
                        <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="drp-text hide-mob text-primary">
                                {{ $statusText }}
                                <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                            </span>
                        </a>
                        <div class="{{ VC::DRP_DSH_MN }}">
                            @forelse ($statusList as $k => $label)
                                <a class="dropdown-item status"
                                data-id="{{ $k }}"
                                data-url="{{ $statusUrl }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ $msgStatus }}"
                                href="#">{{ ucfirst($label) }}</a>
                            @empty
                                <a class="dropdown-item disabled" href="#">{{ __('No status available') }}</a>
                            @endforelse
                        </div>
                    </li>
                </ul>
            @endif
        </div>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (function () {
                    try {
                        if (!window.svToastOrAlert) {
                            window.svToastOrAlert = function (msg) {
                                try {
                                    var ok = !!(window.bootstrap && window.bootstrap.Toast);
                                    if (!ok) { alert(msg); return; }
                                    var t = document.getElementById('route-guard-toast');
                                    if (!t) {
                                        t = document.createElement('div');
                                        t.id = 'route-guard-toast';
                                        t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                        t.setAttribute('role','alert');
                                        t.setAttribute('aria-live','assertive');
                                        t.setAttribute('aria-atomic','true');
                                        t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                        document.body.appendChild(t);
                                    }
                                    var body = t.querySelector('.toast-body');
                                    if (body) body.textContent = msg;
                                    new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                } catch (e) { alert(msg); }
                            };
                        }

                        function guardAnchor(a, isDataUrl) {
                            if (!a) return;
                            var msg = a.getAttribute('data-guard-msg') || 'This action is unavailable.';
                            if (isDataUrl) {
                                if ((a.getAttribute('data-url') || '#') === '#') {
                                    a.addEventListener('click', function (e) { e.preventDefault(); window.svToastOrAlert(msg); });
                                }
                            } else {
                                if ((a.getAttribute('href') || '#') === '#') {
                                    a.addEventListener('click', function (e) { e.preventDefault(); window.svToastOrAlert(msg); });
                                }
                            }
                        }

                        // Download + Preview
                        guardAnchor(document.getElementById('{{ $dlId }}'), false);
                        guardAnchor(document.getElementById('{{ $previewId }}'), false);

                        // Company actions
                        @if($isCompany)
                            guardAnchor(document.getElementById('{{ $sendId ?? '' }}'), false);
                            guardAnchor(document.getElementById('{{ $copyId ?? '' }}'), true);
                            guardAnchor(document.getElementById('{{ $signId ?? '' }}'), true);
                        @endif

                        // Client signature
                        @if($isClient && strtolower((string) data_get($contract,'status','')) === 'accept')
                            guardAnchor(document.getElementById('{{ $cSignId ?? '' }}'), true);
                        @endif

                        // Client status items
                        @if($isClient)
                            var statusItems = document.querySelectorAll('.dropdown-item.status[data-url]');
                            if (statusItems && statusItems.length) {
                                Array.prototype.forEach.call(statusItems, function (it) {
                                    if ((it.getAttribute('data-url') || '#') === '#') {
                                        it.addEventListener('click', function (e) {
                                            e.preventDefault();
                                            window.svToastOrAlert(it.getAttribute('data-guard-msg') || '{{ $msgStatus }}');
                                        });
                                    }
                                });
                            }
                        @endif
                    } catch (_) {}
                })();
            </script>
        @endpush
    @endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-3">
            @php
                $sections = [__('General'), __('Attachment'), __('Comment'), __('Notes')];
            @endphp
            <div class="{{ VC::CD_STK }}" style="top:30px">
                <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                    @foreach($sections as $index => $label)
                        <a href="#useradd-{{ $index + 1 }}" class="{{ VC::LGI_ACT_NBD }}">
                            {{ $label }}
                            <div class="{{ VC::FEND }}"><i class="{{ VC::TI_CHV_RT }}"></i></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-9">
            @php
                $authUser = $user ?? null;
                $hasPriceFormat = $authUser && method_exists($authUser,'priceFormat');
                $hasDateFormat = $authUser && method_exists($authUser,'dateFormat');
                $filesRel = $contract->files ?? [];
                $filesCount = Utility::isFilled($filesRel) ? (is_array($filesRel) ? count($filesRel) : $filesRel->count() ?? []) : 0;
                $commentsRel = $contract->comment ?? [];
                $commentsCount = Utility::isFilled($commentsRel) ? (is_array($commentsRel) ? count($commentsRel) : $commentsRel->count() ?? []) : 0;
                $notesRel = $contract->note ?? [];
                $notesCount = Utility::isFilled($notesRel) ? (is_array($notesRel) ? count($notesRel) : $notesRel->count() ?? []) : 0;
            @endphp
            <div id="useradd-1">
                <div class="row">
                    <div class="col-xl-7">
                        <div class="row">
                            <div class="col-lg-4 col-6">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body" style="min-height: 205px;">
                                        <div class="theme-avatar {{ VC::BG_P }}"><i class="ti ti-user-plus"></i></div>
                                        <h6 class="{{ VC::MB3 }} {{ VC::MT4 }}">{{ __('Attachment') }}</h6>
                                        <h3 class="{{ VC::MB0 }}">{{ $filesCount }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body" style="min-height: 205px;">
                                        <div class="theme-avatar bg-info"><i class="ti ti-click"></i></div>
                                        <h6 class="{{ VC::MB3 }} {{ VC::MT4 }}">{{ __('Comment') }}</h6>
                                        <h3 class="{{ VC::MB0 }}">{{ $commentsCount }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body" style="min-height: 205px;">
                                        <div class="theme-avatar bg-warning"><i class="ti ti-file"></i></div>
                                        <h6 class="{{ VC::MB3 }} {{ VC::MT4 }}">{{ __('Notes') }}</h6>
                                        <h3 class="{{ VC::MB0 }}">{{ $notesCount }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-5">
                        <div class="card report_card total_amount_card">
                            <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: -10px;">
                                <address class="{{ VC::MB0 }} {{ VC::TXSM }}">
                                    <dl class="row mt-4 align-items-center">
                                        <h5>{{ __('Contract Detail') }}</h5><br>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Subject') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ !empty($contract->subject) ? $contract->subject : __('No subject available') }}</dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Project') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ data_get($contract,'projects.project_name') ?: __('No project available') }}</dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Value') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ isset($contract->value) && is_numeric($contract->value) ? ($hasPriceFormat ? ($authUser?->priceFormat($contract->value) ?? __('Failed to format value')) : __('Failed to format value')) : __('No value available') }}</dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Type') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ data_get($contract,'types.name') ?: __('No type available') }}</dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Status') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ !empty($contract->status) ? $contract->status : __('No status available') }}</dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Start Date') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ !empty($contract->start_date) ? ($hasDateFormat ? ($authUser?->dateFormat($contract->start_date) ?? __('Failed to format start date')) : __('Failed to format start date')) : __('No start date available') }}</dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('End Date') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ !empty($contract->end_date) ? ($hasDateFormat ? ($authUser?->dateFormat($contract->end_date) ?? __('Failed to format end date')) : __('Failed to format end date')) : __('No end date available') }}</dd>
                                    </dl>
                                </address>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD }}">
                    <div class="card-header"><h5 class="{{ VC::MB0 }}">{{ __('Contract Description ') }}</h5></div>
                    <div class="card-body">
                        <div class="{{ VC::CM12 }}">
                            <div class="form-group {{ VC::MT3 }}">
                                <textarea class="summernote-simple">{!! !empty($contract->contract_description) ? $contract->contract_description : e(__('No contract description available')) !!}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="useradd-2">
                <div class="{{ VC::CD }}">
                    <div class="card-header"><h5 class="{{ VC::MB0 }}">{{ __('Contract Attachments') }}</h5></div>
                    <div class="card-body">
                        <div class="form-group">
                            @if($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
                                <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                            @elseif($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CL && ($contract->status ?? '') === 'accept')
                                <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                            @endif
                        </div>
                        <div class="scrollbar-inner">
                            <div class="card-wrapper p-3 lead-common-box">
                                @forelse($contract->files ?? [] as $file)
                                    @php
                                        $fileName = $file->files ?? '';
                                        $fullPath = $fileName ? storage_path('contract_attachment/' . $fileName) : '';
                                        $sizeText = ($fileName && $fullPath && file_exists($fullPath)) ? (number_format(File::size($fullPath) / 1048576, 2) . ' ' . __('MB')) : __('No file size available');
                                        $downloadBase = isset($attachments) ? $attachments : '#';
                                        $canRemove = ($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CPN && ($contract->status ?? '') === 'accept') || ($authUser && isset($file[UsersConstants::COL_USER_ID]) && $authUser->id == $file[UsersConstants::COL_USER_ID]);
                                        $delRouteName = VW::CTC.'.file.delete';
                                        $delUrl = (Route::has($delRouteName) && isset($contract->id, $file->id)) ? route($delRouteName, [$contract->id, $file->id]) : '#';
                                        $delMsg = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_file_route_unavailable') ?? 'Delete file route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="card mb-3 border shadow-none">
                                        <div class="px-3 py-3">
                                            <div class="{{ VC::R_ALC }}">
                                                <div class="col">
                                                    <h6 class="{{ VC::TXSM }} {{ VC::MB0 }}"><a href="#!">{{ $fileName ?: __('No filename available') }}</a></h6>
                                                    <p class="card-text small {{ VC::TXT_MT }}">{{ $sizeText }}</p>
                                                </div>
                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                    <a href="{{ $fileName ? ($downloadBase . '/' . $fileName) : '#' }}" class="{{ VC::BT_SM }} {{ VC::DFL_IL_VC }}" download data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                                        <span class="{{ VC::TXT_WT }}"><i class="{{ VC::TI_DWN }}"></i></span>
                                                    </a>
                                                </div>
                                                @if($canRemove)
                                                    <div class="col-auto actions">
                                                        <div class="{{ VC::ACT_BTN_DNG }}">
                                                            {!! Form::open(['method' => 'DELETE', $delUrl === '#' ? 'url' : 'route' => $delUrl === '#' ? '#' : [$delRouteName, $contract->id ?? 0, $file->id ?? 0], 'id' => 'file-del-form-'.$file->id]) !!}
                                                                <a href="#!" class="{{ VC::BT_SM_CT_PR }}" data-sv-localized="true" data-guard-msg="{{ $delMsg }}"><i class="{{ VC::TI_TRS_WT }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">{{ __('No attachments available') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="useradd-3">
                <div class="{{ VC::CD }}">
                    <div class="card-header"><h5 class="{{ VC::MB0 }}">{{ __('Comments') }}</h5></div>
                    <div class="card-body">
                        @php
                            $cmtStoreName = VW::CTC . '.comment.store';
                            $cmtStoreUrl  = (isset($contract->id) && Route::has($cmtStoreName)) ? route($cmtStoreName, [$contract->id]) : '#';
                            $cmtStoreMsg  = Utility::fetchLinkMessage($lang, VW::CTC, 'store_comment_route_unavailable') ?? 'Store comment route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        @if($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
                            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                                <div class="form-group mb-0 form-send w-100">
                                    <form method="post" class="card-comment-box" id="form-comment" data-action="{{ $cmtStoreUrl }}" data-sv-localized="true" data-guard-msg="{{ $cmtStoreMsg }}">
                                        <textarea rows="1" class="{{ VC::FM_CT }}" name="comment" data-toggle="autosize" placeholder="{{ __('Add a comment...') }}"></textarea>
                                    </form>
                                </div>
                                <button id="comment_submit" class="btn btn-send mt-2"><i class="f-16 text-primary ti ti-brand-telegram"></i></button>
                            </div>
                        @elseif($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CL && ($contract->status ?? '') === 'accept')
                            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                                <div class="form-group mb-0 form-send w-100">
                                    <form method="post" class="card-comment-box" id="form-comment" data-action="{{ $cmtStoreUrl }}" data-sv-localized="true" data-guard-msg="{{ $cmtStoreMsg }}">
                                        <textarea rows="1" class="{{ VC::FM_CT }}" name="comment" data-toggle="autosize" placeholder="{{ __('Add a comment...') }}"></textarea>
                                    </form>
                                </div>
                                <button id="comment_submit" class="btn btn-send mt-2"><i class="f-16 text-primary ti ti-brand-telegram"></i></button>
                            </div>
                        @endif
                        <div class="{{ VC::LG_FLSH }} mb-0" id="comments">
                            @forelse($commentsRel as $comment)
                                @php
                                    $commentUser = User::find($comment[UsersConstants::COL_USER_ID] ?? null);
                                    $logo = Utility::getFile('uploads/avatar/');
                                    $avatar = !empty($commentUser?->avatar) ? ($logo . '/' . $commentUser->avatar) : ($logo . '/avatar.png');
                                    $mayDelete = ($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CPN && ($contract->status ?? '') === 'accept') || ($authUser && isset($comment[UsersConstants::COL_USER_ID]) && $authUser->id == $comment[UsersConstants::COL_USER_ID]);
                                    $cDelName = VW::CTC . '.comment.destroy';
                                    $cDelUrl  = (isset($comment->id) && Route::has($cDelName)) ? route($cDelName, $comment->id) : '#';
                                    $cDelMsg  = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_comment_route_unavailable') ?? 'Delete comment route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <div class="{{ VC::LGI }}">
                                    <div class="{{ VC::R_ALC }}">
                                        <div class="col-auto">
                                            <a href="{{ $avatar }}" target="_blank"><img class="rounded-circle" width="40" height="40" src="{{ $avatar }}"></a>
                                        </div>
                                        <div class="col ml-n2">
                                            <p class="d-block h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break">{{ !empty($comment->comment) ? $comment->comment : __('No comment text available') }}</p>
                                            <small class="d-block">{{ isset($comment->created_at) ? $comment->created_at->diffForHumans() : __('No timestamp available') }}</small>
                                        </div>
                                        @if($mayDelete)
                                            <div class="col-auto actions">
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method' => 'DELETE', $cDelUrl === '#' ? 'url' : 'route' => $cDelUrl === '#' ? '#' : [$cDelName, $comment->id ?? 0], 'id' => 'comment-del-form-'.$comment->id]) !!}
                                                        <a href="#!" class="{{ VC::BT_SM_CT_PR }}" data-sv-localized="true" data-guard-msg="{{ $cDelMsg }}"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">{{ __('No comments available') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div id="useradd-4">
                @php
                    $owner = $authUser ? User::find($authUser->creatorId()) : null;
                    $plan = $owner ? Plan::getPlan($owner->plan ?? null) : null;
                    $hasAIGrammar = isset($plan->chatgpt) && (int)$plan->chatgpt === 1;
                @endphp
                <div class="{{ VC::CD }}">
                    <div class="card-header {{ VC::DFL_JCB }}">
                        <h5>{{ __('Notes') }}</h5>
                        @if($hasAIGrammar)
                            @php
                                $grammarUrl = Route::has('grammar') ? route('grammar', ['grammar']) : '#';
                                $grammarMsg = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable') ?? 'The route for grammar check is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <div class="{{ VC::DFL_JCB }}">
                                <div class="{{ VC::MB0 }}">
                                    <a href="#" data-size="md" class="{{ VC::BT_PRM }} btn-icon {{ VC::BT_SM }}" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ $grammarUrl }}" data-sv-localized="true" data-guard-msg="{{ $grammarMsg }}" data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                        <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @php
                            $nStoreName = VW::CTC . '.note.store';
                            $nStoreUrl  = (isset($contract->id) && Route::has($nStoreName)) ? route($nStoreName, $contract->id) : '#';
                            $nStoreMsg  = Utility::fetchLinkMessage($lang, VW::CTC, 'store_note_route_unavailable') ?? 'Store note route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        @if($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
                            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                                <div class="form-group mb-0 form-send w-100">
                                    {!! Form::open([$nStoreUrl === '#' ? 'url' : 'route' => $nStoreUrl === '#' ? '#' : [$nStoreName, $contract->id ?? '0'], 'id' => 'note-add-form', 'data-sv-localized' => 'true', 'data-guard-msg' => $nStoreMsg]) !!}
                                        <div class="form-group">
                                            <textarea rows="3" class="{{ VC::FM_CT }} grammar_textarea" name="notes" data-toggle="autosize" placeholder="{{ __('Add a note...') }}" required></textarea>
                                        </div>
                                        <div class="{{ VC::CM12 }} text-end mb-0">{!! Form::submit(__('Add'), ['class' => VC::BT_PRM]) !!}</div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        @elseif($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CL && ($contract->status ?? '') === 'accept')
                            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                                <div class="form-group mb-0 form-send w-100">
                                    {!! Form::open([$nStoreUrl === '#' ? 'url' : 'route' => $nStoreUrl === '#' ? '#' : [$nStoreName, $contract->id ?? '0'], 'id' => 'note-add-form', 'data-sv-localized' => 'true', 'data-guard-msg' => $nStoreMsg]) !!}
                                        <div class="form-group">
                                            <textarea rows="3" class="{{ VC::FM_CT }} grammar_textarea" name="notes" data-toggle="autosize" placeholder="{{ __('Add a note...') }}" required></textarea>
                                        </div>
                                        <div class="{{ VC::CM12 }} text-end mb-0">{!! Form::submit(__('Add'), ['class' => VC::BT_PRM]) !!}</div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        @endif
                        <div class="{{ VC::LG_FLSH }} mb-0" id="notes">
                            @forelse($notesRel as $note)
                                @php
                                    $noteUser = User::find($note[UsersConstants::COL_USER_ID] ?? null);
                                    $logo = Utility::getFile('uploads/avatar/');
                                    $avatar = !empty($noteUser?->avatar) ? ($logo . '/' . $noteUser->avatar) : ($logo . '/avatar.png');
                                    $mayDelete = ($authUser && $authUser->{UsersConstants::COL_TP} === PermissionsConstants::CPN && ($contract->status ?? '') === 'accept') || ($authUser && isset($note[UsersConstants::COL_USER_ID]) && $authUser->id == $note[UsersConstants::COL_USER_ID]);
                                    $nDelName = VW::CTC . '.note.destroy';
                                    $nDelUrl  = (isset($note->id) && Route::has($nDelName)) ? route($nDelName, $note->id) : '#';
                                    $nDelMsg  = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_note_route_unavailable') ?? 'Delete note route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <div class="{{ VC::LGI }}">
                                    <div class="{{ VC::R_ALC }}">
                                        <div class="col-auto">
                                            <a href="{{ $avatar }}" target="_blank"><img class="rounded-circle" width="40" height="40" src="{{ $avatar }}"></a>
                                        </div>
                                        <div class="col ml-n2">
                                            <p class="d-block h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break">{{ !empty($note->notes) ? $note->notes : __('No note text available') }}</p>
                                            <small class="d-block">{{ isset($note->created_at) ? $note->created_at->diffForHumans() : __('No timestamp available') }}</small>
                                        </div>
                                        @if($mayDelete)
                                            <div class="col-auto actions">
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method' => 'DELETE', $nDelUrl === '#' ? 'url' : 'route' => $nDelUrl === '#' ? '#' : [$nDelName, $note->id ?? 0], 'id' => 'note-del-form-'.$note->id]) !!}
                                                        <a href="#!" class="{{ VC::BT_SM_CT_PR }}" data-sv-localized="true" data-guard-msg="{{ $nDelMsg }}"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">{{ __('No notes available') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script defer src="{{ asset('assets/js/routes/contracts/show.js') }}"></script>
@endsection

@else
    <div class="alert alert-warning">{{ __('No contract found.') }}</div>
@endif