@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Gate, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bug Report')}}
@endsection
@if(isset($project) && !empty($project))
    @php
        $projectIndexBaseName = ViewsConstants::PRJ . '.index';
        $projectIndexKebabName = Str::kebab($projectIndexBaseName);
        $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
        $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
        $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    @section(YieldingConstants::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a
                id="project-index-link"
                href="{{ $projectIndexUrl }}"
                data-url="{{ $projectIndexUrl }}"
                data-guard-msg="{{ $projectIndexGuardMsg }}"
            >
                {{ __('Project') }}
            </a>
        </li>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const link = document.getElementById('project-index-link');
                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                    link.setAttribute('data-listener-active', 'true');
                    link.addEventListener('click', e => {
                        try {
                            const url = link.getAttribute('data-url') || '#';
                            if (url !== '#') return;
                            e.preventDefault();
                            const msg = link.getAttribute('data-guard-msg') || '# ERROR';
                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (hasBootstrap) {
                                const toast = document.createElement('div');
                                toast.className = 'toast';
                                toast.setAttribute('role','alert');
                                toast.setAttribute('aria-live','assertive');
                                toast.setAttribute('aria-atomic','true');
                                const body = document.createElement('div');
                                body.className = 'toast-body';
                                body.textContent = msg;
                                toast.appendChild(body);
                                container.appendChild(toast);
                                bootstrap.Toast.getOrCreateInstance(toast).show();
                            } else {
                                alert(msg);
                            }
                            link.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    });
                })();
            </script>
        @endpush
        <li class="breadcrumb-item">
            @php
                $projectShowBaseName     = ViewsConstants::PRJ.'.show';
                $projectShowKebabName    = Str::kebab($projectShowBaseName);
                $projectShowResolvedName = Route::has($projectShowBaseName)
                    ? $projectShowBaseName
                    : (Route::has($projectShowKebabName) ? $projectShowKebabName : null);
                $projectId               = isset($project) && !empty($project->id) ? $project->id : null;
                $projectShowUrl          = ($projectShowResolvedName && $projectId) ? route($projectShowResolvedName, $projectId) : '#';
                $projectShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                $projectShowLinkId       = 'project-show-link';
                $projectNameText         = ($project->project_name ?? null) ? ucwords($project->project_name) : __('No name found for project');
            @endphp
            <a href="{{ $projectShowUrl }}"
            id="{{ $projectShowLinkId }}"
            data-url="{{ $projectShowUrl }}"
            data-guard-msg="{{ $projectShowGuardMsg }}">
                {{ $projectNameText }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        try {
                            const l = document.getElementById('{{ $projectShowLinkId }}');
                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                            l.setAttribute('data-listener-active', 'true');
                            l.addEventListener('click', e => {
                                try {
                                    const href = l.getAttribute('href') || '#';
                                    const url = l.getAttribute('data-url') || href || '#';
                                    if (href !== '#' || url !== '#') return;
                                    e.preventDefault();
                                    const msg = l.getAttribute('data-guard-msg') || 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (hasBootstrap) {
                                        const toast = document.createElement('div');
                                        toast.className = 'toast';
                                        toast.setAttribute('role', 'alert');
                                        toast.setAttribute('aria-live', 'assertive');
                                        toast.setAttribute('aria-atomic', 'true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toast.appendChild(body);
                                        container.appendChild(toast);
                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                    } else {
                                        alert(msg);
                                    }
                                    l.setAttribute('data-failed-route', 'true');
                                } catch (err) {}
                            });
                        } catch (error) {}
                    })();
                </script>
            @endpush
        </li>
        <li class="breadcrumb-item">{{__('Bug Report')}}</li>
    @endsection
    @push(StacksConstants::ADM_CSS)
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
    @endpush
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
        <script async>
            window.translations = {
                ar:{dragula_unavailable:"تعذّر تفعيل السحب والإفلات",kanban_move_unavailable:"تعذّر نقل العنصر",comment_add_unavailable:"تعذّر إضافة التعليق",comment_delete_unavailable:"تعذّر حذف التعليق",file_add_unavailable:"تعذّر إضافة الملف",file_delete_unavailable:"تعذّر حذف الملف"},
                da:{dragula_unavailable:"Kunne ikke aktivere træk-og-slip",kanban_move_unavailable:"Kunne ikke flytte elementet",comment_add_unavailable:"Kunne ikke tilføje kommentar",comment_delete_unavailable:"Kunne ikke slette kommentar",file_add_unavailable:"Kunne ikke tilføje fil",file_delete_unavailable:"Kunne ikke slette fil"},
                de:{dragula_unavailable:"Drag-and-Drop konnte nicht aktiviert werden",kanban_move_unavailable:"Element konnte nicht verschoben werden",comment_add_unavailable:"Kommentar konnte nicht hinzugefügt werden",comment_delete_unavailable:"Kommentar konnte nicht gelöscht werden",file_add_unavailable:"Datei konnte nicht hinzugefügt werden",file_delete_unavailable:"Datei konnte nicht gelöscht werden"},
                en:{dragula_unavailable:"Drag & drop could not be initialized",kanban_move_unavailable:"Could not move the item",comment_add_unavailable:"Could not add the comment",comment_delete_unavailable:"Could not delete the comment",file_add_unavailable:"Could not add the file",file_delete_unavailable:"Could not delete the file"},
                es:{dragula_unavailable:"No se pudo iniciar arrastrar y soltar",kanban_move_unavailable:"No se pudo mover el elemento",comment_add_unavailable:"No se pudo agregar el comentario",comment_delete_unavailable:"No se pudo eliminar el comentario",file_add_unavailable:"No se pudo agregar el archivo",file_delete_unavailable:"No se pudo eliminar el archivo"},
                fr:{dragula_unavailable:"Impossible d’activer le glisser-déposer",kanban_move_unavailable:"Impossible de déplacer l’élément",comment_add_unavailable:"Impossible d’ajouter le commentaire",comment_delete_unavailable:"Impossible de supprimer le commentaire",file_add_unavailable:"Impossible d’ajouter le fichier",file_delete_unavailable:"Impossible de supprimer le fichier"},
                he:{dragula_unavailable:"לא ניתן להפעיל גרירה ושחרור",kanban_move_unavailable:"לא ניתן להעביר את הפריט",comment_add_unavailable:"לא ניתן להוסיף את התגובה",comment_delete_unavailable:"לא ניתן למחוק את התגובה",file_add_unavailable:"לא ניתן להוסיף את הקובץ",file_delete_unavailable:"לא ניתן למחוק את הקובץ"},
                it:{dragula_unavailable:"Impossibile inizializzare il drag & drop",kanban_move_unavailable:"Impossibile spostare l’elemento",comment_add_unavailable:"Impossibile aggiungere il commento",comment_delete_unavailable:"Impossibile eliminare il commento",file_add_unavailable:"Impossibile aggiungere il file",file_delete_unavailable:"Impossibile eliminare il file"},
                ja:{dragula_unavailable:"ドラッグ＆ドロップを初期化できませんでした",kanban_move_unavailable:"項目を移動できませんでした",comment_add_unavailable:"コメントを追加できませんでした",comment_delete_unavailable:"コメントを削除できませんでした",file_add_unavailable:"ファイルを追加できませんでした",file_delete_unavailable:"ファイルを削除できませんでした"},
                nl:{dragula_unavailable:"Slepen-en-neerzetten kon niet worden gestart",kanban_move_unavailable:"Item kon niet worden verplaatst",comment_add_unavailable:"Opmerking kon niet worden toegevoegd",comment_delete_unavailable:"Opmerking kon niet worden verwijderd",file_add_unavailable:"Bestand kon niet worden toegevoegd",file_delete_unavailable:"Bestand kon niet worden verwijderd"},
                pl:{dragula_unavailable:"Nie można uruchomić przeciągania",kanban_move_unavailable:"Nie można przenieść elementu",comment_add_unavailable:"Nie można dodać komentarza",comment_delete_unavailable:"Nie można usunąć komentarza",file_add_unavailable:"Nie można dodać pliku",file_delete_unavailable:"Nie można usunąć pliku"},
                pt:{dragula_unavailable:"Não foi possível iniciar o arrastar e soltar",kanban_move_unavailable:"Não foi possível mover o item",comment_add_unavailable:"Não foi possível adicionar o comentário",comment_delete_unavailable:"Não foi possível excluir o comentário",file_add_unavailable:"Não foi possível adicionar o arquivo",file_delete_unavailable:"Não foi possível excluir o arquivo"},
                "pt-br":{dragula_unavailable:"Não foi possível iniciar o arrastar e soltar",kanban_move_unavailable:"Não foi possível mover o item",comment_add_unavailable:"Não foi possível adicionar o comentário",comment_delete_unavailable:"Não foi possível excluir o comentário",file_add_unavailable:"Não foi possível adicionar o arquivo",file_delete_unavailable:"Não foi possível excluir o arquivo"},
                ru:{dragula_unavailable:"Не удалось инициализировать перетаскивание",kanban_move_unavailable:"Не удалось переместить элемент",comment_add_unavailable:"Не удалось добавить комментарий",comment_delete_unavailable:"Не удалось удалить комментарий",file_add_unavailable:"Не удалось добавить файл",file_delete_unavailable:"Не удалось удалить файл"},
                tr:{dragula_unavailable:"Sürükle-bırak başlatılamadı",kanban_move_unavailable:"Öğe taşınamadı",comment_add_unavailable:"Yorum eklenemedi",comment_delete_unavailable:"Yorum silinemedi",file_add_unavailable:"Dosya eklenemedi",file_delete_unavailable:"Dosya silinemedi"},
                zh:{dragula_unavailable:"无法初始化拖放",kanban_move_unavailable:"无法移动条目",comment_add_unavailable:"无法添加评论",comment_delete_unavailable:"无法删除评论",file_add_unavailable:"无法添加文件",file_delete_unavailable:"无法删除文件"}
            };
        </script>
        <script defer>
            (()=>{
                const errFb="# ERROR";
                const dataClientLocalized="data-client-localized";
                const dataGuardMsg="data-guard-msg";
                const DATA_LISTENER="data-listener-added";

                const getMsg=(el,key)=>{
                let msg=errFb;
                if(el?.getAttribute("data-sv-localized")==="true"||el?.getAttribute(dataClientLocalized)==="true"){
                    msg=el.getAttribute(dataGuardMsg)||errFb;
                }else{
                    let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");
                    lang=lang==="pt-br"?lang:lang.slice(0,2);
                    msg=window.translations?.[lang]?.[key]||el?.getAttribute(dataGuardMsg)||window.translations?.en?.[key]||errFb;
                    if(msg!==errFb){ el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true"); }
                }
                return msg;
                };

                const showFeedback=(key,ev="click")=>{
                const text=getMsg(document.body,key);
                const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
                if(hasBs){
                    let toast=document.querySelector("#np-error-toast");
                    if(!toast){
                    toast=document.createElement("div");
                    toast.id="np-error-toast";
                    toast.className="toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    toast.setAttribute("role","alert");
                    toast.setAttribute("aria-live","assertive");
                    toast.setAttribute("aria-atomic","true");
                    toast.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    document.body.appendChild(toast);
                    }
                    const once=()=>new bootstrap.Toast(toast).show();
                    document.addEventListener(ev,once,{once:true});
                    const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(toast)){ document.removeEventListener(ev,once); o.disconnect(); } });
                    mo.observe(document.body,{childList:true,subtree:true});
                }else{
                    const once=()=>alert(text);
                    document.addEventListener(ev,once,{once:true});
                }
                };

                const guardRoute=(url)=>!url||url==="#"||url==="";

                try{
                if(typeof $==="undefined"){ console.error("jQuery failed to load"); return; }

                const token=$('meta[name="csrf-token"]').attr('content') ?? "";

                const initDragula=()=>{
                    if(typeof dragula!=="function"){ console.error("Dragula failed to load"); showFeedback("dragula_unavailable"); return; }
                    $('[data-plugin="dragula"]').each(function(){
                    const $root=$(this);
                    const ids=$root.data("containers");
                    const containers=ids?ids.map(id=>$("#"+id)[0]).filter(Boolean):[$root[0]];
                    const handleClass=$root.data("handleclass");
                    const onDrop=(el,target,source)=>{
                        try{
                        const order=[];
                        $("#"+target.id+" > [data-id]").each(function(i,node){ order[i]=$(node).attr("data-id"); });
                        const id=$(el).attr("id") ?? "";
                        const stage_id=$(target).attr("data-id") ?? "";
                        $("#"+source.id).parent().find(".count").text($("#"+source.id+" > div").length);
                        $("#"+target.id).parent().find(".count").text($("#"+target.id+" > div").length);
                        const url='{{route(ViewsConstants::PRJ_BUG . '.kanban.order')}}';
                        if(guardRoute(url)){ showFeedback("kanban_move_unavailable"); return; }
                        $.ajax({
                            url,
                            type:"POST",
                            data:{ bug_id:id, status_id:stage_id, order, "_token":token },
                            success:()=>{ try{ show_toastr?.('success',"Bug Moved Successfully.",'success'); }catch{} },
                            error:()=>{ showFeedback("kanban_move_unavailable"); }
                        });
                        }catch{ showFeedback("kanban_move_unavailable"); }
                    };
                    const drg=handleClass?dragula(containers,{ moves:(_el,_src,handle)=>handle.classList.contains(handleClass) }):dragula(containers);
                    drg.on("drop",onDrop);
                    });
                };

                const bindComments=()=>{
                    $(document).on("click","#form-comment button",function(){
                    const $form=$("#form-comment");
                    const url=$form.data("action");
                    const comment=$.trim($form.find("textarea[name='comment']").val()||"");
                    if(!comment){ try{ show_toastr?.('{{__("error")}}','{{ __("Please write comment!")}}','error'); }catch{} return; }
                    if(guardRoute(url)){ showFeedback("comment_add_unavailable"); return; }
                    $.ajax({
                        url,
                        type:"POST",
                        data:{ comment, "_token":token },
                        success:(data)=>{
                        const name='{{ \Auth::user()->name }}';
                        const cmt=data?.data?.comment ?? "";
                        const del=data?.data?.deleteUrl ?? "#";
                        const html=
                            `<li class="media mb-20">
                            <div class="media-body">
                                <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <h5 class="mt-0">${name}</h5>
                                    <p class="mb-0 text-xs">${cmt}</p>
                                </div>
                                <div class="comment-trash" style="float:right">
                                    <a href="#" class="btn btn-sm red btn-danger delete-comment" data-url="${del}">
                                    <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                                </div>
                            </div>
                            </li>`;
                        $("#comments").prepend(html);
                        $form.find("textarea[name='comment']").val("");
                        try{ show_toastr?.('{{__("success")}}','{{ __("Comment Added Successfully!")}}','success'); }catch{}
                        },
                        error:()=>{ showFeedback("comment_add_unavailable"); }
                    });
                    });

                    $(document).on("click",".delete-comment",function(){
                    if(!confirm('Are You Sure ?')) return;
                    const $btn=$(this);
                    const url=$btn.attr("data-url");
                    if(guardRoute(url)){ showFeedback("comment_delete_unavailable"); return; }
                    $.ajax({
                        url,
                        type:"DELETE",
                        data:{ _token:token },
                        dataType:"JSON",
                        success:()=>{ try{ show_toastr?.('{{__("Success")}}','{{ __("Comment Deleted Successfully!")}}','success'); }catch{} $btn.closest(".media").remove(); },
                        error:(xhr)=>{
                        const msg=xhr?.responseJSON?.message;
                        if(msg){ try{ show_toastr?.('{{__("Error")}}',msg,'error'); }catch{} } else { showFeedback("comment_delete_unavailable"); }
                        }
                    });
                    });
                };

                const bindFiles=()=>{
                    $(document).on("submit","#form-file",function(e){
                    e.preventDefault();
                    const $form=$(this);
                    const url=$form.data("url");
                    if(guardRoute(url)){ showFeedback("file_add_unavailable"); return; }
                    $.ajax({
                        url,
                        type:"POST",
                        data:new FormData(this),
                        dataType:"JSON",
                        contentType:false,
                        cache:false,
                        processData:false,
                        success:(data)=>{
                        try{ show_toastr?.('{{__("success")}}','{{ __("File Added Successfully!")}}','success'); }catch{}
                        $(".file_update").html("");
                        $("#file-error").html("");
                        const id=data?.id ?? "";
                        const name=data?.name ?? "";
                        const size=data?.file_size ?? "";
                        const file=data?.file ?? "";
                        const delUrl=data?.deleteUrl ?? "";
                        const dlHref=`{{asset(Storage::url('bugs'))}}/${file}`;
                        const html=
                            `<div class="col-8 mb-2 file-${id}">
                            <h5 class="mt-0 mb-1 font-weight-bold text-sm">${name}</h5>
                            <p class="m-0 text-xs">${size}</p>
                            </div>
                            <div class="col-4 mb-2 file-${id}">
                            <div class="comment-trash" style="float:right">
                                <a download href="${dlHref}" class="btn btn-sm btn-primary"><i class="ti ti-download"></i></a>
                                <a href="#" class="btn btn-sm red btn-danger delete-comment-file m-0 px-2" data-id="${id}" data-url="${delUrl}">
                                <i class="ti ti-trash"></i>
                                </a>
                            </div>
                            </div>`;
                        $("#comments-file").prepend(html);
                        },
                        error:(xhr)=>{
                        const msg=xhr?.responseJSON?.errors?.file?.[0] || xhr?.responseJSON?.message;
                        if(msg){ $("#file-error").text(msg).show(); } else { showFeedback("file_add_unavailable"); }
                        }
                    });
                    });

                    $(document).on("click",".delete-comment-file",function(){
                    if(!confirm('Are You Sure ?')) return;
                    const $btn=$(this);
                    const url=$btn.attr("data-url");
                    if(guardRoute(url)){ showFeedback("file_delete_unavailable"); return; }
                    $.ajax({
                        url,
                        type:"DELETE",
                        data:{ _token:token },
                        dataType:"JSON",
                        success:()=>{ try{ show_toastr?.('{{__("Success")}}','{{ __("File Deleted Successfully!")}}','success'); }catch{} $(`.file-${$btn.attr("data-id")}`).remove(); },
                        error:(xhr)=>{
                        const msg=xhr?.responseJSON?.message;
                        if(msg){ try{ show_toastr?.('{{__("Error")}}',msg,'error'); }catch{} } else { showFeedback("file_delete_unavailable"); }
                        }
                    });
                    });
                };

                initDragula();
                bindComments();
                bindFiles();
                }catch(e){
                console.error("Initialization failed",e);
                }
            })();
        </script>
    @endpush
    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="float-end">
            @can('manage bug report')
                @php
                    $bugListBaseName     = ViewsConstants::PRJ_TSK_BUG;
                    $bugListKebabName    = Str::kebab($bugListBaseName);
                    $bugListResolvedName = Route::has($bugListBaseName)
                        ? $bugListBaseName
                        : (Route::has($bugListKebabName) ? $bugListKebabName : null);
                    $projectId           = isset($project) && !empty($project->id) ? $project->id : null;
                    $bugListUrl          = ($bugListResolvedName && $projectId) ? route($bugListResolvedName, $projectId) : '#';
                    $bugListGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'list_bug_route_unavailable') ?? 'List bug route is unavailable. Please contact technical support or your domain administrator.';
                    $bugListLinkId       = 'bug-list-link-'.($projectId ?? 'x');
                    $bugListTitle        = __('List');
                @endphp
                <a href="{{ $bugListUrl }}"
                id="{{ $bugListLinkId }}"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $bugListUrl }}"
                data-guard-msg="{{ $bugListGuardMsg }}"
                data-bs-toggle="tooltip"
                title="{{ $bugListTitle }}">
                    <i class="{{ VC::TI_LT }}"></i>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const l = document.getElementById('{{ $bugListLinkId }}');
                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                l.setAttribute('data-listener-active', 'true');
                                l.addEventListener('click', e => {
                                    try {
                                        const href = l.getAttribute('href') || '#';
                                        const url = l.getAttribute('data-url') || href || '#';
                                        if (href !== '#' || url !== '#') return;
                                        e.preventDefault();
                                        const msg = l.getAttribute('data-guard-msg') || 'List bug route is unavailable. Please contact technical support or your domain administrator.';
                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                        let container = document.getElementById('toast-container');
                                        if (!container) {
                                            container = document.createElement('div');
                                            container.id = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (hasBootstrap) {
                                            const toast = document.createElement('div');
                                            toast.className = 'toast';
                                            toast.setAttribute('role', 'alert');
                                            toast.setAttribute('aria-live', 'assertive');
                                            toast.setAttribute('aria-atomic', 'true');
                                            const body = document.createElement('div');
                                            body.className = 'toast-body';
                                            body.textContent = msg;
                                            toast.appendChild(body);
                                            container.appendChild(toast);
                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                        } else {
                                            alert(msg);
                                        }
                                        l.setAttribute('data-failed-route', 'true');
                                    } catch (err) {}
                                });
                            } catch (error) {}
                        })();
                    </script>
                @endpush
            @endcan
            @can('create bug report')
                @php
                    $bugCreateBaseName     = ViewsConstants::PRJ_TSK_BUG.'.create';
                    $bugCreateKebabName    = Str::kebab($bugCreateBaseName);
                    $bugCreateResolvedName = Route::has($bugCreateBaseName)
                        ? $bugCreateBaseName
                        : (Route::has($bugCreateKebabName) ? $bugCreateKebabName : null);
                    $projectId             = isset($project) && !empty($project->id) ? $project->id : null;
                    $bugCreateUrl          = ($bugCreateResolvedName && $projectId) ? route($bugCreateResolvedName, $projectId) : '#';
                    $bugCreateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'create_bug_route_unavailable') ?? 'Create bug route is unavailable. Please contact technical support or your domain administrator.';
                    $bugCreateLinkId       = 'bug-create-link-'.($projectId ?? 'x');
                    $bugCreateTitle        = __('Create New Bug');
                @endphp
                <a href="{{ $bugCreateUrl }}"
                id="{{ $bugCreateLinkId }}"
                data-size="lg"
                data-url="{{ $bugCreateUrl }}"
                data-ajax-popup="true"
                data-guard-msg="{{ $bugCreateGuardMsg }}"
                data-bs-toggle="tooltip"
                data-title="{{ $bugCreateTitle }}"
                title="{{ $bugCreateTitle }}"
                class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_PLS }}"></i>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const l = document.getElementById('{{ $bugCreateLinkId }}');
                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                l.setAttribute('data-listener-active', 'true');
                                l.addEventListener('click', e => {
                                    try {
                                        const href = l.getAttribute('href') || '#';
                                        const url = l.getAttribute('data-url') || href || '#';
                                        if (href !== '#' || url !== '#') return;
                                        e.preventDefault();
                                        const msg = l.getAttribute('data-guard-msg') || 'Create bug route is unavailable. Please contact technical support or your domain administrator.';
                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                        let container = document.getElementById('toast-container');
                                        if (!container) {
                                            container = document.createElement('div');
                                            container.id = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (hasBootstrap) {
                                            const toast = document.createElement('div');
                                            toast.className = 'toast';
                                            toast.setAttribute('role', 'alert');
                                            toast.setAttribute('aria-live', 'assertive');
                                            toast.setAttribute('aria-atomic', 'true');
                                            const body = document.createElement('div');
                                            body.className = 'toast-body';
                                            body.textContent = msg;
                                            toast.appendChild(body);
                                            container.appendChild(toast);
                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                        } else {
                                            alert(msg);
                                        }
                                        l.setAttribute('data-failed-route', 'true');
                                    } catch (err) {}
                                });
                            } catch (error) {}
                        })();
                    </script>
                @endpush
            @endcan
        </div>
    @endsection
    @section(YieldingConstants::ADM_CTT)
        @php
            $json = [];
            foreach ($bug_status as $status){
                $json[] = 'task-list-'.$status->id;
            }
        @endphp
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CS12 }}">
                @php
                    $jsonData = isset($json) && (is_array($json) || is_object($json)) ? json_encode($json) : '[]';
                @endphp
                <div class="{{ VC::RW }} kanban-wrapper horizontal-scroll-cards" 
                     data-containers='{{ $jsonData }}' 
                     data-plugin="dragula">
                    @if(isset($bug_status) && is_countable($bug_status) && count($bug_status) > 0)
                        @foreach($bug_status as $status)
                            @if(isset($status) && is_object($status))
                                @php 
                                    $bugs = [];
                                    $projectId = data_get($project, 'id');
                                    if (!empty($projectId) && method_exists($status, 'bugs')) {
                                        try {
                                            $bugs = $status->bugs($projectId);
                                            $bugs = is_countable($bugs) ? $bugs : [];
                                        } catch (Exception $e) {
                                            $bugs = [];
                                        }
                                    }
                                    $statusId = data_get($status, 'id', 'status-' . uniqid());
                                    $statusTitle = data_get($status, 'title', __('Untitled Status'));
                                    $bugsCount = is_countable($bugs) ? count($bugs) : 0;
                                @endphp
                                <div class="col">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::FEND }}">
                                                <span class="{{ VC::BT_SM_PM }} btn-icon count">
                                                    {{ $bugsCount }}
                                                </span>
                                            </div>
                                            <h4 class="{{ VC::MB0 }}">{{ e($statusTitle) }}</h4>
                                        </div>
                                        <div class="card-body kanban-box" 
                                             id="task-list-{{ $statusId }}" 
                                             data-id="{{ $statusId }}">
                                            @if(!empty($bugs) && is_countable($bugs) && count($bugs) > 0)
                                                @foreach($bugs as $bug)
                                                    @if(isset($bug) && is_object($bug))
                                                        @php
                                                            $bugId = data_get($bug, 'id', 'bug-' . uniqid());
                                                            $bugTitle = data_get($bug, 'title', __('Untitled Bug'));
                                                            $bugPriority = data_get($bug, 'priority', '');
                                                            $bugStartDate = data_get($bug, 'start_date', '');
                                                            $bugDueDate = data_get($bug, 'due_date', '');
                                                            $priorityConfig = [
                                                                'low' => 'bg-success',
                                                                'medium' => 'bg-warning',
                                                                'high' => 'bg-danger'
                                                            ];
                                                            $priorityClass = data_get($priorityConfig, $bugPriority, 'bg-secondary');
                                                        @endphp
                                                        <div class="{{ VC::CD }} draggable-item" id="{{ $bugId }}">
                                                            <div class="pt-3 ps-3">
                                                                @if(!empty($bugPriority) && in_array($bugPriority, ['low', 'medium', 'high']))
                                                                    <span class="p-2 px-3 rounded badge badge-pill {{ VC::BDG }}-xs {{ $priorityClass }}">
                                                                        {{ ucfirst(e($bugPriority)) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="card-header border-0 pb-0 position-relative">
                                                                <h5>
                                                                    @if(!empty($projectId) && !empty($bugId))
                                                                        @php
                                                                            $bugShowBaseName     = ViewsConstants::PRJ_TSK_BUG.'.show';
                                                                            $bugShowKebabName    = Str::kebab($bugShowBaseName);
                                                                            $bugShowResolvedName = Route::has($bugShowBaseName)
                                                                                ? $bugShowBaseName
                                                                                : (Route::has($bugShowKebabName) ? $bugShowKebabName : null);
                                                                            $projectId           = isset($projectId) && !empty($projectId) ? $projectId : (isset($bug) && !empty($bug->project_id) ? $bug->project_id : null);
                                                                            $bugId               = isset($bugId) && !empty($bugId) ? $bugId : (isset($bug) && !empty($bug->id) ? $bug->id : null);
                                                                            $bugTitle            = e(data_get($bug ?? null, 'title', __('Untitled Bug')));
                                                                            $bugShowUrl          = ($bugShowResolvedName && $projectId && $bugId) ? route($bugShowResolvedName, [$projectId, $bugId]) : '#';
                                                                            $bugShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'show_bug_route_unavailable') ?? 'Show bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                            $bugShowLinkId       = 'bug-show-link-'.($bugId ?? 'x');
                                                                        @endphp
                                                                        <a href="{{ $bugShowUrl }}"
                                                                        id="{{ $bugShowLinkId }}"
                                                                        data-url="{{ $bugShowUrl }}"
                                                                        data-ajax-popup="true"
                                                                        data-size="lg"
                                                                        data-bs-original-title="{{ $bugTitle }}"
                                                                        data-guard-msg="{{ $bugShowGuardMsg }}">
                                                                            {{ $bugTitle }}
                                                                        </a>
                                                                        @push(StacksConstants::ADM_SCR_PG)
                                                                            <script defer>
                                                                                (() => {
                                                                                    try {
                                                                                        const l = document.getElementById('{{ $bugShowLinkId }}');
                                                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                                        l.setAttribute('data-listener-active', 'true');
                                                                                        l.addEventListener('click', e => {
                                                                                            try {
                                                                                                const href = l.getAttribute('href') || '#';
                                                                                                const url = l.getAttribute('data-url') || href || '#';
                                                                                                if (href !== '#' || url !== '#') return;
                                                                                                e.preventDefault();
                                                                                                const msg = l.getAttribute('data-guard-msg') || 'Show bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                                let container = document.getElementById('toast-container');
                                                                                                if (!container) {
                                                                                                    container = document.createElement('div');
                                                                                                    container.id = 'toast-container';
                                                                                                    document.body.appendChild(container);
                                                                                                }
                                                                                                if (hasBootstrap) {
                                                                                                    const toast = document.createElement('div');
                                                                                                    toast.className = 'toast';
                                                                                                    toast.setAttribute('role', 'alert');
                                                                                                    toast.setAttribute('aria-live', 'assertive');
                                                                                                    toast.setAttribute('aria-atomic', 'true');
                                                                                                    const body = document.createElement('div');
                                                                                                    body.className = 'toast-body';
                                                                                                    body.textContent = msg;
                                                                                                    toast.appendChild(body);
                                                                                                    container.appendChild(toast);
                                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                                } else {
                                                                                                    alert(msg);
                                                                                                }
                                                                                                l.setAttribute('data-failed-route', 'true');
                                                                                            } catch (err) {}
                                                                                        });
                                                                                    } catch (error) {}
                                                                                })();
                                                                            </script>
                                                                        @endpush
                                                                    @else
                                                                        {{ e($bugTitle) }}
                                                                    @endif
                                                                </h5>
                                                                <div class="card-header-right">
                                                                    <div class="btn-group card-option">
                                                                        <button type="button" class="btn dropdown-toggle"
                                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                                aria-expanded="false">
                                                                            <i class="{{ VC::TD_DOTV }}"></i>
                                                                        </button>
                                                                        @if(class_exists('Gate') && (Gate::check('edit bug report') || Gate::check('delete bug report')))
                                                                            <div class="{{ ViewClassNamesConstants::DRP_MN_EM }}">
                                                                                @can('edit project task')
                                                                                    @php
                                                                                        $bugEditBaseName     = ViewsConstants::PRJ_TSK_BUG.'.edit';
                                                                                        $bugEditKebabName    = Str::kebab($bugEditBaseName);
                                                                                        $bugEditResolvedName = Route::has($bugEditBaseName)
                                                                                            ? $bugEditBaseName
                                                                                            : (Route::has($bugEditKebabName) ? $bugEditKebabName : null);
                                                                                        $projectIdValue      = isset($projectId) && !empty($projectId) ? $projectId : (isset($bug) && !empty($bug->project_id) ? $bug->project_id : null);
                                                                                        $bugIdValue          = isset($bugId) && !empty($bugId) ? $bugId : (isset($bug) && !empty($bug->id) ? $bug->id : null);
                                                                                        $bugNameText         = e(data_get($bug ?? null, 'name', $bugTitle ?? __('Untitled Bug')));
                                                                                        $bugEditUrl          = ($bugEditResolvedName && $projectIdValue && $bugIdValue) ? route($bugEditResolvedName, [$projectIdValue, $bugIdValue]) : '#';
                                                                                        $bugEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'edit_bug_route_unavailable') ?? 'Edit bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        $bugEditLinkId       = 'bug-edit-link-'.($bugIdValue ?? 'x');
                                                                                        $bugEditTitle        = __('Edit ').$bugNameText;
                                                                                    @endphp
                                                                                    <a href="{{ $bugEditUrl }}"
                                                                                    id="{{ $bugEditLinkId }}"
                                                                                    data-size="lg"
                                                                                    data-url="{{ $bugEditUrl }}"
                                                                                    data-ajax-popup="true"
                                                                                    class="dropdown-item"
                                                                                    data-bs-original-title="{{ $bugEditTitle }}"
                                                                                    data-guard-msg="{{ $bugEditGuardMsg }}">
                                                                                        <i class="{{ VC::TI_PC }}"></i>
                                                                                        <span>{{ __('Edit') }}</span>
                                                                                    </a>
                                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                                        <script defer>
                                                                                            (() => {
                                                                                                try {
                                                                                                    const l = document.getElementById('{{ $bugEditLinkId }}');
                                                                                                    if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                                                    l.setAttribute('data-listener-active', 'true');
                                                                                                    l.addEventListener('click', e => {
                                                                                                        try {
                                                                                                            const href = l.getAttribute('href') || '#';
                                                                                                            const url = l.getAttribute('data-url') || href || '#';
                                                                                                            if (href !== '#' || url !== '#') return;
                                                                                                            e.preventDefault();
                                                                                                            const msg = l.getAttribute('data-guard-msg') || 'Edit bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                                            let container = document.getElementById('toast-container');
                                                                                                            if (!container) {
                                                                                                                container = document.createElement('div');
                                                                                                                container.id = 'toast-container';
                                                                                                                document.body.appendChild(container);
                                                                                                            }
                                                                                                            if (hasBootstrap) {
                                                                                                                const toast = document.createElement('div');
                                                                                                                toast.className = 'toast';
                                                                                                                toast.setAttribute('role', 'alert');
                                                                                                                toast.setAttribute('aria-live', 'assertive');
                                                                                                                toast.setAttribute('aria-atomic', 'true');
                                                                                                                const body = document.createElement('div');
                                                                                                                body.className = 'toast-body';
                                                                                                                body.textContent = msg;
                                                                                                                toast.appendChild(body);
                                                                                                                container.appendChild(toast);
                                                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                                            } else {
                                                                                                                alert(msg);
                                                                                                            }
                                                                                                            l.setAttribute('data-failed-route', 'true');
                                                                                                        } catch (err) {}
                                                                                                    });
                                                                                                } catch (error) {}
                                                                                            })();
                                                                                        </script>
                                                                                    @endpush
                                                                                @endcan
                                                                                @can('delete project task')
                                                                                    @if(class_exists(Form::class) && !empty($projectId) && !empty($bugId))
                                                                                        @php
                                                                                            $bugDestroyBaseName     = ViewsConstants::PRJ_TSK_BUG.'.destroy';
                                                                                            $bugDestroyKebabName    = Str::kebab($bugDestroyBaseName);
                                                                                            $bugDestroyResolvedName = Route::has($bugDestroyBaseName)
                                                                                                ? $bugDestroyBaseName
                                                                                                : (Route::has($bugDestroyKebabName) ? $bugDestroyKebabName : null);
                                                                                            $projectIdValue         = isset($projectId) && !empty($projectId) ? $projectId : (isset($bug) && !empty($bug->project_id) ? $bug->project_id : null);
                                                                                            $bugIdValue             = isset($bugId) && !empty($bugId) ? $bugId : (isset($bug) && !empty($bug->id) ? $bug->id : null);
                                                                                            $bugDestroyRouteArray   = ($bugDestroyResolvedName && $projectIdValue && $bugIdValue) ? [$bugDestroyResolvedName, [$projectIdValue, $bugIdValue]] : ['#'];
                                                                                            $bugDestroyUrl          = ($bugDestroyResolvedName && $projectIdValue && $bugIdValue) ? route($bugDestroyResolvedName, [$projectIdValue, $bugIdValue]) : '#';
                                                                                            $bugDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'delete_bug_route_unavailable') ?? 'Delete bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            $bugDestroyFormId       = 'bug-destroy-form-'.($bugIdValue ?? 'x');
                                                                                            $bugDestroyLinkId       = 'bug-destroy-link-'.($bugIdValue ?? 'x');
                                                                                        @endphp
                                                                                        {!! Form::open([
                                                                                            'method'         => 'DELETE',
                                                                                            'route'          => $bugDestroyRouteArray,
                                                                                            'id'             => $bugDestroyFormId,
                                                                                            'data-url'       => $bugDestroyUrl,
                                                                                            'data-guard-msg' => $bugDestroyGuardMsg
                                                                                        ]) !!}
                                                                                            @csrf
                                                                                            <a href="#!"
                                                                                            id="{{ $bugDestroyLinkId }}"
                                                                                            class="dropdown-item bs-pass-para"
                                                                                            data-form-id="{{ $bugDestroyFormId }}"
                                                                                            data-url="{{ $bugDestroyUrl }}"
                                                                                            data-guard-msg="{{ $bugDestroyGuardMsg }}">
                                                                                                <i class="{{ VC::TI_ARC }}"></i>
                                                                                                <span>{{ __('Delete') }}</span>
                                                                                            </a>
                                                                                        {!! Form::close() !!}
                                                                                        @push(StacksConstants::ADM_SCR_PG)
                                                                                            <script defer>
                                                                                                (() => {
                                                                                                    try {
                                                                                                        const l = document.getElementById('{{ $bugDestroyLinkId }}');
                                                                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                                                        l.setAttribute('data-listener-active', 'true');
                                                                                                        l.addEventListener('click', e => {
                                                                                                            try {
                                                                                                                e.preventDefault();
                                                                                                                const formId = l.getAttribute('data-form-id') || '';
                                                                                                                const f = formId ? document.getElementById(formId) : null;
                                                                                                                if (!f) return;
                                                                                                                const url = f.getAttribute('data-url') || '#';
                                                                                                                const action = f.getAttribute('action') || '#';
                                                                                                                if (url === '#' && action === '#') {
                                                                                                                    const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Delete bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                                                    let container = document.getElementById('toast-container');
                                                                                                                    if (!container) {
                                                                                                                        container = document.createElement('div');
                                                                                                                        container.id = 'toast-container';
                                                                                                                        document.body.appendChild(container);
                                                                                                                    }
                                                                                                                    if (hasBootstrap) {
                                                                                                                        const toast = document.createElement('div');
                                                                                                                        toast.className = 'toast';
                                                                                                                        toast.setAttribute('role', 'alert');
                                                                                                                        toast.setAttribute('aria-live', 'assertive');
                                                                                                                        toast.setAttribute('aria-atomic', 'true');
                                                                                                                        const body = document.createElement('div');
                                                                                                                        body.className = 'toast-body';
                                                                                                                        body.textContent = msg;
                                                                                                                        toast.appendChild(body);
                                                                                                                        container.appendChild(toast);
                                                                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                                                    } else {
                                                                                                                        alert(msg);
                                                                                                                    }
                                                                                                                    l.setAttribute('data-failed-route', 'true');
                                                                                                                    f.setAttribute('data-failed-route', 'true');
                                                                                                                    return;
                                                                                                                }
                                                                                                                f.submit();
                                                                                                            } catch (err) {}
                                                                                                        });
                                                                                                    } catch (error) {}
                                                                                                })();
                                                                                            </script>
                                                                                        @endpush
                                                                                    @endif
                                                                                @endcan
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="{{ VC::DFL_AIC_JCB }} mb-2">
                                                                    <ul class="list-inline {{ VC::MB0 }}">
                                                                        <li class="list-inline-item d-inline-flex align-items-center" 
                                                                            data-bs-toggle="tooltip" 
                                                                            title="{{ __('Start Date') }}">
                                                                            @if(isset($user) && is_object($user) && method_exists($user, 'dateFormat') && !empty($bugStartDate))
                                                                                @php
                                                                                    try {
                                                                                        $formattedStartDate = $user->dateFormat($bugStartDate);
                                                                                    } catch (Exception $e) {
                                                                                        $formattedStartDate = e($bugStartDate);
                                                                                    }
                                                                                @endphp
                                                                                {{ $formattedStartDate }}
                                                                            @elseif(!empty($bugStartDate))
                                                                                {{ e($bugStartDate) }}
                                                                            @else
                                                                                {{ __('No Start Date') }}
                                                                            @endif
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <div class="user-group">
                                                                        <span data-bs-toggle="tooltip" title="{{ __('End Date') }}">
                                                                            @if(isset($user) && is_object($user) && method_exists($user, 'dateFormat') && !empty($bugDueDate))
                                                                                @php
                                                                                    try {
                                                                                        $formattedDueDate = $user->dateFormat($bugDueDate);
                                                                                    } catch (Exception $e) {
                                                                                        $formattedDueDate = e($bugDueDate);
                                                                                    }
                                                                                @endphp
                                                                                {{ $formattedDueDate }}
                                                                            @elseif(!empty($bugDueDate))
                                                                                {{ e($bugDueDate) }}
                                                                            @else
                                                                                {{ __('No Due Date') }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::DFL_AIC_JCB }}">
                                                                    @php 
                                                                        $bugUsers = [];
                                                                        if (method_exists($bug, 'users')) {
                                                                            try {
                                                                                $bugUsers = $bug->users();
                                                                                $bugUsers = is_countable($bugUsers) || is_object($bugUsers) ? $bugUsers : [];
                                                                            } catch (Exception $e) {
                                                                                $bugUsers = [];
                                                                            }
                                                                        }
                                                                        $firstUser = null;
                                                                        if (!empty($bugUsers)) {
                                                                            if (is_array($bugUsers) && count($bugUsers) > 0) {
                                                                                $firstUser = $bugUsers[0];
                                                                            } elseif (is_object($bugUsers) && method_exists($bugUsers, 'first')) {
                                                                                $firstUser = $bugUsers->first();
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <div class="user-group">
                                                                        @if(isset($firstUser) && is_object($firstUser))
                                                                            @php
                                                                                $avatar = data_get($firstUser, 'avatar', '');
                                                                                $userName = data_get($firstUser, 'name', __('Unknown User'));
                                                                                $avatarPath = !empty($avatar) 
                                                                                    ? asset('/storage/uploads/avatar/' . $avatar)
                                                                                    : asset('/storage/uploads/avatar/avatar.png');
                                                                            @endphp
                                                                            <img src="{{ $avatarPath }}" 
                                                                                 alt="{{ e($userName) }}" 
                                                                                 data-bs-toggle="tooltip" 
                                                                                 title="{{ e($userName) }}"
                                                                                 onerror="this.src='{{ asset('/storage/uploads/avatar/avatar.png') }}'">
                                                                        @else
                                                                            <img src="{{ asset('/storage/uploads/avatar/avatar.png') }}" 
                                                                                 alt="{{ __('No User') }}" 
                                                                                 data-bs-toggle="tooltip" 
                                                                                 title="{{ __('No User Assigned') }}">
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                {{ __('No bug statuses available') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endsection
@else
    <div>{{ __('No project selected') }}</div>
@endif