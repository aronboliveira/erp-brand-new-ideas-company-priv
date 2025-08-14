@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        PlansConstants,
        StacksConstants,
        UsersConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Plan,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, URL};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@if($user && isset($lead) && !empty($lead))
    @php
        $leadName = !empty($lead->name) ? $lead->name : __('No given name.');
    @endphp
    @section(YieldingConstants::ADM_PG_TTL)
        {{$leadName}}
    @endsection
    @push(StacksConstants::ADM_CSS)
        <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
    @endpush
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
        <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
        <script async>
        window.translations = {
            ar: { lead_upload_unavailable: "تعذّر رفع الملف الآن", lead_delete_unavailable: "تعذّر حذف الملف الآن", lead_notes_unavailable: "تعذّر حفظ الملاحظات الآن", dropzone_unavailable: "عنصر الرفع غير متاح" },
            da: { lead_upload_unavailable: "Kan ikke uploade fil lige nu", lead_delete_unavailable: "Kan ikke slette fil lige nu", lead_notes_unavailable: "Kan ikke gemme noter lige nu", dropzone_unavailable: "Upload-widget ikke tilgængelig" },
            de: { lead_upload_unavailable: "Datei kann derzeit nicht hochgeladen werden", lead_delete_unavailable: "Datei kann derzeit nicht gelöscht werden", lead_notes_unavailable: "Notizen können derzeit nicht gespeichert werden", dropzone_unavailable: "Upload-Widget nicht verfügbar" },
            en: { lead_upload_unavailable: "Cannot upload file right now", lead_delete_unavailable: "Cannot delete file right now", lead_notes_unavailable: "Cannot save notes right now", dropzone_unavailable: "Upload widget unavailable" },
            es: { lead_upload_unavailable: "No se puede subir el archivo ahora", lead_delete_unavailable: "No se puede eliminar el archivo ahora", lead_notes_unavailable: "No se pueden guardar las notas ahora", dropzone_unavailable: "Carga no disponible" },
            fr: { lead_upload_unavailable: "Impossible de téléverser le fichier maintenant", lead_delete_unavailable: "Impossible de supprimer le fichier maintenant", lead_notes_unavailable: "Impossible d’enregistrer les notes maintenant", dropzone_unavailable: "Widget d’envoi indisponible" },
            he: { lead_upload_unavailable: "לא ניתן להעלות קובץ כעת", lead_delete_unavailable: "לא ניתן למחוק קובץ כעת", lead_notes_unavailable: "לא ניתן לשמור הערות כעת", dropzone_unavailable: "רכיב העלאה לא זמין" },
            it: { lead_upload_unavailable: "Impossibile caricare il file ora", lead_delete_unavailable: "Impossibile eliminare il file ora", lead_notes_unavailable: "Impossibile salvare le note ora", dropzone_unavailable: "Widget di upload non disponibile" },
            ja: { lead_upload_unavailable: "現在ファイルをアップロードできません", lead_delete_unavailable: "現在ファイルを削除できません", lead_notes_unavailable: "現在メモを保存できません", dropzone_unavailable: "アップロードウィジェットが利用できません" },
            nl: { lead_upload_unavailable: "Bestand kan nu niet worden geüpload", lead_delete_unavailable: "Bestand kan nu niet worden verwijderd", lead_notes_unavailable: "Notities kunnen nu niet worden opgeslagen", dropzone_unavailable: "Uploadwidget niet beschikbaar" },
            pl: { lead_upload_unavailable: "Nie można teraz przesłać pliku", lead_delete_unavailable: "Nie można teraz usunąć pliku", lead_notes_unavailable: "Nie można teraz zapisać notatek", dropzone_unavailable: "Widget przesyłania niedostępny" },
            pt: { lead_upload_unavailable: "Não é possível enviar o arquivo agora", lead_delete_unavailable: "Não é possível excluir o arquivo agora", lead_notes_unavailable: "Não é possível salvar as notas agora", dropzone_unavailable: "Widget de upload indisponível" },
            "pt-br": { lead_upload_unavailable: "Não é possível enviar o arquivo agora", lead_delete_unavailable: "Não é possível excluir o arquivo agora", lead_notes_unavailable: "Não é possível salvar as notas agora", dropzone_unavailable: "Widget de upload indisponível" },
            ru: { lead_upload_unavailable: "Не удаётся загрузить файл сейчас", lead_delete_unavailable: "Не удаётся удалить файл сейчас", lead_notes_unavailable: "Не удаётся сохранить заметки сейчас", dropzone_unavailable: "Виджет загрузки недоступен" },
            tr: { lead_upload_unavailable: "Şu anda dosya yüklenemiyor", lead_delete_unavailable: "Şu anda dosya silinemiyor", lead_notes_unavailable: "Şu anda notlar kaydedilemiyor", dropzone_unavailable: "Yükleme bileşeni kullanılamıyor" },
            zh: { lead_upload_unavailable: "当前无法上传文件", lead_delete_unavailable: "当前无法删除文件", lead_notes_unavailable: "当前无法保存备注", dropzone_unavailable: "上传组件不可用" }
        };
        </script>
        <script defer>
        (()=>{
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const DATA_LISTENER_ADDED = "data-listener-added";

            const getMsg = (el, msgKey) => {
            let msg = errFb;
            if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
                msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                .toLowerCase()
                .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg =
                window.translations?.[lang]?.[msgKey] ||
                el?.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[msgKey] ||
                errFb;
                if (msg !== errFb) {
                el?.setAttribute(dataGuardMsg, msg);
                el?.setAttribute(dataClientLocalized, "true");
                }
            }
            return msg;
            };

            const showFeedback = (el, key, ev = "pointerup") => {
            const text = getMsg(el || document.body, key);
            const hasBs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (hasBs) {
                let toast = document.querySelector("#np-error-toast");
                if (!toast) {
                toast = document.createElement("div");
                toast.id = "np-error-toast";
                toast.className = "toast align-items-center text-bg-danger border-0";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${text}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
                document.body.appendChild(toast);
                }
                const handler = () => new bootstrap.Toast(toast).show();
                document.addEventListener(ev, handler, { once: true });
                const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(toast)) {
                    document.removeEventListener(ev, handler);
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            } else {
                const handler = () => alert(text);
                document.addEventListener(ev, handler, { once: true });
            }
            };

            const guardOnce = (el, key, ev = "pointerup") => {
            if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
            const handler = () => showFeedback(el, key, ev);
            el.addEventListener(ev, handler, { once: true });
            el.setAttribute(DATA_LISTENER_ADDED, "true");
            const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(el)) {
                el.removeEventListener(ev, handler);
                o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            };

            const routeGuard = (element, alt) => {
            const url  = element?.getAttribute?.("data-url");
            const href = element?.action ?? element?.href;
            return (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#");
            };

            try {
            if (typeof $ === "undefined") { console.error("jQuery failed to load"); return; }

            const leadId = {{$lead->id ?? 'null'}};
            const uploadUrl = "{{ route(ViewsConstants::LD.'.file.upload', $lead->id) }}";
            const saveNotesUrl = "{{ route(ViewsConstants::LD.'.note.store', $lead->id) }}";

            if (window.bootstrap?.ScrollSpy && document.querySelector("#lead-sidenav")) {
                if (!document.body.getAttribute("data-np-scrollspy-lead")) {
                new bootstrap.ScrollSpy(document.body, { target: "#lead-sidenav", offset: 300 });
                document.body.setAttribute("data-np-scrollspy-lead", "true");
                }
            }

            if (!window.Dropzone) {
                console.error("Dropzone failed to load");
                guardOnce(document.body, "dropzone_unavailable");
            } else {
                try { window.Dropzone.autoDiscover = false; } catch {}

                const dzSelector = "#dropzonewidget";
                const dzEl = document.querySelector(dzSelector);

                if (dzEl && !routeGuard(null, uploadUrl)) {
                const dz = new Dropzone(dzSelector, {
                    maxFiles: 20,
                    parallelUploads: 1,
                    filename: false,
                    url: uploadUrl,
                    success(file, response) {
                    const ok = response && (response.is_success ?? false);
                    if (ok) {
                        if (String(response.status ?? "") === "1" && typeof window.show_toastr === "function") {
                        window.show_toastr("success", response.success_msg ?? "", "success");
                        }
                        dropzoneBtn(file, response);
                    } else {
                        this.removeFile(file);
                        guardOnce(dzEl, "lead_upload_unavailable");
                    }
                    },
                    error(file, response) {
                    this.removeFile(file);
                    const hasErr = response && response.error;
                    if (!hasErr) {
                        guardOnce(dzEl, "lead_upload_unavailable");
                    } else {
                        guardOnce(dzEl, "lead_upload_unavailable");
                    }
                    }
                });

                dz.on("sending", (file, xhr, formData) => {
                    formData.append("_token", $('meta[name="csrf-token"]').attr('content') ?? "");
                    formData.append("lead_id", leadId ?? "");
                });

                const dropzoneBtn = (file, response) => {
                    const ensureOnce = (tpl, selector, node) => {
                    if (!tpl.querySelector(selector)) tpl.appendChild(node);
                    };

                    const download = document.createElement("a");
                    download.href = response?.download ?? "#";
                    download.className = "badge bg-info mx-1";
                    download.setAttribute("data-toggle", "tooltip");
                    download.setAttribute("data-original-title", "{{ __('Download') }}");
                    download.innerHTML = "<i class='ti ti-download'></i>";

                    const del = document.createElement("a");
                    del.href = response?.delete ?? "#";
                    del.className = "badge bg-danger mx-1";
                    del.setAttribute("data-toggle", "tooltip");
                    del.setAttribute("data-original-title", "{{ __('Delete') }}");
                    del.innerHTML = "<i class='ti ti-trash'></i>";

                    const onDelete = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!confirm("Are you sure ?")) return;
                    if (routeGuard(del, del.href)) { guardOnce(del, "lead_delete_unavailable"); return; }
                    $.ajax({
                        url: del.href,
                        type: "DELETE",
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: (res) => {
                        if (res && res.is_success) {
                            $(del).closest(".dz-image-preview").remove();
                        } else {
                            guardOnce(del, "lead_delete_unavailable");
                        }
                        },
                        error: (res) => {
                        const r = res?.responseJSON;
                        if (!(r && r.is_success)) guardOnce(del, "lead_delete_unavailable");
                        }
                    });
                    };

                    del.addEventListener("click", onDelete, { once: true });
                    const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(del)) { del.removeEventListener("click", onDelete); o.disconnect(); }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });

                    const container = document.createElement("div");
                    ensureOnce(file.previewTemplate, "a.badge.bg-info", download);
                    @if($user[UsersConstants::COL_TP] != PermissionsConstants::CL)
                    @can('edit lead')
                    ensureOnce(file.previewTemplate, "a.badge.bg-danger", del);
                    @endcan
                    @endif
                    file.previewTemplate.appendChild(container);
                };

                @foreach($lead->files as $file)
                @if (file_exists(storage_path('lead_files/'.$file->file_path)))
                (function(){
                    const mock = { name: "{{$file->file_name}}", size: {{ \File::size(storage_path('lead_files/'.$file->file_path)) }} };
                    dz.emit("addedfile", mock);
                    dz.emit("thumbnail", mock, "{{ asset(Storage::url('lead_files/'.$file->file_path)) }}");
                    dz.emit("complete", mock);
                    const resp = {
                    download: "{{ route(ViewsConstants::LD.'.file.download',[$lead->id,$file->id]) }}",
                    delete:   "{{ route(ViewsConstants::LD.'.file.delete',[$lead->id,$file->id]) }}"
                    };
                    // reuse helper inside closure
                    const addBtns = (file, response) => {
                    const download = document.createElement("a");
                    download.href = response?.download ?? "#";
                    download.className = "badge bg-info mx-1";
                    download.innerHTML = "<i class='ti ti-download'></i>";
                    const del = document.createElement("a");
                    del.href = response?.delete ?? "#";
                    del.className = "badge bg-danger mx-1";
                    del.innerHTML = "<i class='ti ti-trash'></i>";
                    const onDelete = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!confirm("Are you sure ?")) return;
                        if (routeGuard(del, del.href)) { guardOnce(del, "lead_delete_unavailable"); return; }
                        $.ajax({
                        url: del.href,
                        type: "DELETE",
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: (res) => { if (res?.is_success) $(del).closest(".dz-image-preview").remove(); else guardOnce(del, "lead_delete_unavailable"); },
                        error:   () => guardOnce(del, "lead_delete_unavailable")
                        });
                    };
                    del.addEventListener("click", onDelete, { once: true });
                    file.previewTemplate.appendChild(download);
                    @if($user[UsersConstants::COL_TP] != PermissionsConstants::CL)
                    @can('edit lead')
                    file.previewTemplate.appendChild(del);
                    @endcan
                    @endif
                    };
                    addBtns(mock, resp);
                })();
                @endif
                @endforeach
                } else {
                guardOnce(document.body, "dropzone_unavailable");
                }
            }

            @can('edit lead')
            if ($(".summernote-simple").length) {
                $(".summernote-simple").on("summernote.blur", function () {
                const notesVal = $(this).val() ?? "";
                if (routeGuard(null, saveNotesUrl)) { guardOnce(this, "lead_notes_unavailable"); return; }
                $.ajax({
                    url: saveNotesUrl,
                    type: "POST",
                    data: { _token: $('meta[name="csrf-token"]').attr('content'), notes: notesVal },
                    success: (res) => { if (!(res && res.is_success)) guardOnce(this, "lead_notes_unavailable"); },
                    error:   ()  => guardOnce(this, "lead_notes_unavailable")
                });
                });
            }
            @else
            if ($(".summernote-simple").length && $.fn.summernote) {
                $(".summernote-simple").summernote("disable");
            }
            @endcan
            } catch (e) {
            console.error("Initialization failed", e);
            }
        })();
        </script>
    @endpush
    @section(YieldingConstants::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item"><a href="{{route(ViewsConstants::LD.'.index')}}">{{__('Lead')}}</a></li>
        <li class="breadcrumb-item"> {{$leadName}}</li>
    @endsection
    @section(YieldingConstants::ADM_ACT_BTN)
        @if(!empty($lead->id))
            <div class="float-end">
                @can('convert lead to deal')
                    @if(!empty($deal))
                        <a href="@can('View Deal') @if($deal->is_active && !empty($dela->id)) {{route(ViewsConstants::DL.'.show',$deal->id)}} @else # @endif @else # @endcan" data-size="lg" data-bs-toggle="tooltip" title=" {{__('Already Converted To Deal')}}" class="btn btn-sm btn-primary">
                            <i class="ti ti-exchange"></i>
                        </a>
                    @else
                        <a href="#" data-size="lg" data-url="{{ URL::to(ViewsConstants::LD.'/'.$lead->id.'/show_convert') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Convert ['.$lead->subject.'] To Deal')}}" class="btn btn-sm btn-primary">
                            <i class="ti ti-exchange"></i>
                        </a>
                    @endif
                @endcan
                <a href="#" data-url="{{ URL::to(ViewsConstants::LD.'/'.$lead->id.'/labels') }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('Label')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-bookmark"></i>
                </a>
                <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::LD.'.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
            </div>
        @else
            <p>{{__('No lead id found.')}}</p>
        @endif
    @endsection
    @section(YieldingConstants::ADM_CTT)
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xl-3">
                        @php
                            $userType = $user[UsersConstants::COL_TP] ?? null;
                            $navItems = [
                                ['id' => 'general',         'label' => __('General')],
                                ['id' => 'users_products',  'label' => __('Users').' | '.__('Products')],
                                ['id' => 'sources_emails',  'label' => __('Sources').' | '.__('Emails')],
                                ['id' => 'discussion_note', 'label' => __('Discussion').' | '.__('Notes')],
                                ['id' => 'files',           'label' => __('Files')],
                                ['id' => 'calls',           'label' => __('Calls')],
                                ['id' => 'activity',        'label' => __('Activity')],
                            ];
                        @endphp
                        @if($userType != PermissionsConstants::CL)
                            <div class="{{ VC::CD_STK }}" style="top:30px">
                                <div class="{{ VC::LG_FLSH }}" id="lead-sidenav">
                                    @foreach($navItems as $item)
                                        <a href="#{{ $item['id'] }}"
                                        class="{{ VC::LGI_ACT_NBD }}">
                                            {{ $item['label'] }}
                                            <div class="float-end">
                                                <i class="{{ VC::TI_CHV_RT }}"></i>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-xl-9">
                        <?php
                        $products = $lead->products();
                        $sources = $lead->sources();
                        $calls = $lead->calls;
                        $emails = $lead->emails;
                        ?>
                        <div id="general" class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 col-sm-4">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-primary">
                                                <i class="ti ti-mail"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Email')}}</p>
                                                <h5 class="mb-0 text-primary">{{!empty($lead->email)?$lead->email:''}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-warning">
                                                <i class="ti ti-phone"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Phone')}}</p>
                                                <h5 class="mb-0 text-warning">{{!empty($lead->phone)?$lead->phone:''}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-info">
                                                <i class="ti ti-test-pipe"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Pipeline')}}</p>
                                                <h5 class="mb-0 text-info">{{$lead->pipeline->name}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-4">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-primary">
                                                <i class="ti ti-server"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Stage')}}</p>
                                                <h5 class="mb-0 text-primary">{{$lead->stage->name}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-4">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-warning">
                                                <i class="ti ti-calendar"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Created')}}</p>
                                                <h5 class="mb-0 text-warning">{{$user?->dateFormat($lead->created_at)}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-4">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-info">
                                                <i class="ti ti-chart-bar"></i>
                                            </div>
                                            <div class="ms-2">
                                                <h3 class="mb-0 text-info">{{$precentage}}%</h3>
                                                <div class="progress mb-0">
                                                    <div class="progress-bar bg-info" style="width: {{$precentage}}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-auto mb-3 mb-sm-0">
                                                <small class="text-muted">{{__('Product')}}</small>
                                                <h3 class="m-0">{{count($products)}}</h3>
                                            </div>
                                            <div class="col-auto">
                                                <div class="theme-avatar bg-info">
                                                    <i class="ti ti-shopping-cart"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-auto mb-3 mb-sm-0">
                                                <small class="text-muted">{{__('Source')}}</small>
                                                <h3 class="m-0">{{count($sources)}}</h3>
                                            </div>
                                            <div class="col-auto">
                                                <div class="theme-avatar bg-primary">
                                                    <i class="ti ti-social"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-auto mb-3 mb-sm-0">
                                                <small class="text-muted">{{__('Files')}}</small>
                                                <h3 class="m-0">{{count($lead->files)}}</h3>
                                            </div>
                                            <div class="col-auto">
                                                <div class="theme-avatar bg-warning">
                                                    <i class="ti ti-file"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div id="users_products">
                            <div class="row">
                                <div class="col-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>{{__('Users')}}</h5>
                                                <div class="float-end">
                                                    <a  data-size="md" data-url="{{ route(ViewsConstants::LD.'.users.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add User')}}" class="btn btn-sm btn-primary ">
                                                        <i class="ti ti-plus text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Name')}}</th>
                                                        <th>{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($lead->users as $user)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div>
                                                                        <img @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="wid-30 rounded-circle me-3" alt="avatar image">
                                                                    </div>
                                                                    <p class="mb-0">{{$user->name}}</p>
                                                                </div>
                                                            </td>
                                                            @can('edit lead')
                                                                <td>
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.users.destroy', $lead->id,$user->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                </td>
                                                            @endcan
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>{{__('Products')}}</h5>
                                                <div class="float-end">
                                                    <a data-size="md" data-url="{{ route(ViewsConstants::LD.'.products.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Product')}}" class="btn btn-sm btn-primary">
                                                        <i class="ti ti-plus text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Name')}}</th>
                                                        <th>{{__('Price')}}</th>
                                                        <th>{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($lead->products() as $product)
                                                        <tr>
                                                            <td>
                                                                {{$product->name}}
                                                            </td>
                                                            <td>
                                                                {{$user?->priceFormat($product->sale_price)}}
                                                            </td>
                                                            @can('edit lead')
                                                                <td>
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.products.destroy', $lead->id,$product->id]]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                </td>
                                                            @endcan
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="sources_emails">
                            <div class="row">
                                <div class="col-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>{{__('Sources')}}</h5>
                                                <div class="float-end">
                                                <a data-size="md" data-url="{{ route(ViewsConstants::LD.'.sources.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Source')}}" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-plus text-white"></i>
                                                </a>
                                            </div>
                                            </div>

                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th>{{__('Name')}}</th>
                                                        <th>{{__('Action')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($sources as $source)
                                                        <tr>
                                                            <td>{{$source->name}} </td>
                                                            @can('edit lead')
                                                                <td>
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.sources.destroy', $lead->id,$source->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                </td>
                                                            @endcan
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>{{__('Emails')}}</h5>
                                                @can('create lead email')
                                                    <div class="float-end">
                                                        <a data-size="md" data-url="{{ route(ViewsConstants::LD.'.emails.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Email')}}" class="btn btn-sm btn-primary">
                                                            <i class="ti ti-plus text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                            </div>

                                        </div>
                                        <div class="card-body">
                                            <div class="{{ VC::LG_FLSH_MT2 }}">
                                                @if(!$emails->isEmpty())
                                                    @foreach($emails as $email)
                                                        <li class="list-group-item px-0">
                                                            <div class="d-block d-sm-flex align-items-start">
                                                                <img src="{{asset('/storage/uploads/avatar/avatar.png')}}"
                                                                    class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                                <div class="w-100">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="mb-3 mb-sm-0">
                                                                            <h6 class="mb-0">{{$email->subject}}</h6>
                                                                            <span class="text-muted text-sm">{{$email->to}}</span>
                                                                        </div>
                                                                        <div class="form-check form-switch form-switch-right mb-2">
                                                                            {{$email->created_at->diffForHumans()}}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="text-center">
                                                        {{__(' No Emails Available.!')}}
                                                    </li>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="discussion_note">
                            <div class="row">
                                <div class="col-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>{{__('Discussion')}}</h5>
                                                <div class="float-end">
                                                    <a data-size="lg" data-url="{{ route(ViewsConstants::LD.'.discussions.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Message')}}" class="btn btn-sm btn-primary">
                                                        <i class="ti ti-plus text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="{{ VC::LG_FLSH_MT2 }}">
                                                @if(!$lead->discussions->isEmpty())
                                                    @foreach($lead->discussions as $discussion)
                                                        <li class="list-group-item px-0">
                                                            <div class="d-block d-sm-flex align-items-start">
                                                                <img src="@if($discussion->user->avatar) {{asset('/storage/uploads/avatar/'.$discussion->user->avatar)}} @else {{asset('/storage/uploads/avatar/avatar.png')}} @endif"
                                                                    class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                                <div class="w-100">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="mb-3 mb-sm-0">
                                                                            <h6 class="mb-0"> {{$discussion->comment}}</h6>
                                                                            <span class="text-muted text-sm">{{$discussion->user->name}}</span>
                                                                        </div>
                                                                        <div class="form-check form-switch form-switch-right mb-2">
                                                                            {{$discussion->created_at->diffForHumans()}}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="text-center">
                                                        {{__(' No Data Available.!')}}
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>{{__('Notes')}}</h5>
                                                @php
                                                    $plan = Plan::getPlan($user->plan);
                                                @endphp
                                                @if($plan?->{PlansConstants::COL_GPT} == 1)
                                                    <div class="float-end">
                                                        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['grammar']) }}"
                                                        data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                                            <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span>
                                                        </a>
                                                        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['lead']) }}"
                                                        data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                                            <i class="{{ VC::FAS_RB }}"></i> <span>{{__('Generate with AI')}}</span>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <textarea class="summernote-simple " name="note">{!! $lead->notes !!}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="files" class="card">
                            <div class="card-header">
                                <h5>{{__('Files')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                            </div>
                        </div>
                        <div id="calls" class="card">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5>{{__('Calls')}}</h5>

                                    <div class="float-end">
                                    <a data-size="lg" data-url="{{ route(ViewsConstants::LD.'.calls.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Call')}}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-plus text-white"></i>
                                    </a>
                                </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                        <tr>
                                            <th width="">{{__('Subject')}}</th>
                                            <th>{{__('Call Type')}}</th>
                                            <th>{{__('Duration')}}</th>
                                            <th>{{__('User')}}</th>
                                            <th>{{__('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($calls as $call)
                                            <tr>
                                                <td>{{ $call->subject }}</td>
                                                <td>{{ ucfirst($call->call_type) }}</td>
                                                <td>{{ $call->duration }}</td>
                                                <td>{{ isset($call->getLeadCallUser) ? $call->getLeadCallUser->name : '-' }}</td>
                                                <td>
                                                    @can('edit lead call')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to(ViewsConstants::LD.'/'.$lead->id.'/call/'.$call->id.'/edit') }}" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Role Edit')}}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete lead call')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.calls.destroy', $lead->id,$call->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="activity" class="card">
                            <div class="card-header">
                                <h5>{{__('Activity')}}</h5>
                            </div>
                            <div class="card-body ">
                                <div class="row leads-scroll" >
                                    <ul class="{{ VC::LG_FLSH_W }}">
                                        @if(!$lead->activities->isEmpty())
                                            @foreach($lead->activities as $activity)
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avatar bg-primary">
                                                                    <i class="ti {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getLeadRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            No activity found yet.
                                        @endif
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    <div class="alert alert-danger">{{__('Lead not found')}}</div>
@endif