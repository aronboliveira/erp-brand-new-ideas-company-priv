@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{ProjectTask,Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection,Str};
    $lang = Utility::fetchUserLang();
    $projectIndexBaseName = ViewsConstants::PRJ . '.index';
    $projectIndexKebabName = Str::kebab($projectIndexBaseName);
    $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
    $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
    $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ucwords($project->project_name).__("'s Tasks")}}
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dragula.min.css')}}" id="main-style-link">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
   <script defer src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:{dragula_unavailable:'تعذّر تهيئة السحب والإفلات',task_order_unavailable:'تعذّر تحديث ترتيب المهام',assign_toggle_unavailable:'تعذّر تبديل المكلّفين',task_delete_unavailable:'تعذّر حذف المهمة',comment_add_unavailable:'تعذّر إضافة التعليق',comment_delete_unavailable:'تعذّر حذف التعليق',checklist_add_unavailable:'تعذّر إضافة عنصر القائمة',checklist_update_unavailable:'تعذّر تحديث عنصر القائمة',checklist_delete_unavailable:'تعذّر حذف عنصر القائمة',file_add_unavailable:'تعذّر إضافة الملف',file_delete_unavailable:'تعذّر حذف الملف',favorite_toggle_unavailable:'تعذّر تمييز كمفضلة',task_complete_unavailable:'تعذّر تغيير حالة الإكمال',task_progress_unavailable:'تعذّر تحديث التقدم',task_load_unavailable:'تعذّر تحميل تفاصيل المهمة'},
            da:{dragula_unavailable:'Kunne ikke initialisere træk-og-slip',task_order_unavailable:'Kunne ikke opdatere opgaveorden',assign_toggle_unavailable:'Kunne ikke skifte tildelinger',task_delete_unavailable:'Kunne ikke slette opgave',comment_add_unavailable:'Kunne ikke tilføje kommentar',comment_delete_unavailable:'Kunne ikke slette kommentar',checklist_add_unavailable:'Kunne ikke tilføje tjeklistepunkt',checklist_update_unavailable:'Kunne ikke opdatere tjeklistepunkt',checklist_delete_unavailable:'Kunne ikke slette tjeklistepunkt',file_add_unavailable:'Kunne ikke tilføje fil',file_delete_unavailable:'Kunne ikke slette fil',favorite_toggle_unavailable:'Kunne ikke ændre favorit',task_complete_unavailable:'Kunne ikke ændre fuldført-status',task_progress_unavailable:'Kunne ikke opdatere fremgang',task_load_unavailable:'Kunne ikke indlæse opgave'},
            de:{dragula_unavailable:'Drag&Drop konnte nicht initialisiert werden',task_order_unavailable:'Aufgabenreihenfolge konnte nicht aktualisiert werden',assign_toggle_unavailable:'Zuweisungen konnten nicht umgeschaltet werden',task_delete_unavailable:'Aufgabe konnte nicht gelöscht werden',comment_add_unavailable:'Kommentar konnte nicht hinzugefügt werden',comment_delete_unavailable:'Kommentar konnte nicht gelöscht werden',checklist_add_unavailable:'Checklistenpunkt konnte nicht hinzugefügt werden',checklist_update_unavailable:'Checklistenpunkt konnte nicht aktualisiert werden',checklist_delete_unavailable:'Checklistenpunkt konnte nicht gelöscht werden',file_add_unavailable:'Datei konnte nicht hinzugefügt werden',file_delete_unavailable:'Datei konnte nicht gelöscht werden',favorite_toggle_unavailable:'Favorit konnte nicht umgeschaltet werden',task_complete_unavailable:'Abschlussstatus konnte nicht geändert werden',task_progress_unavailable:'Fortschritt konnte nicht aktualisiert werden',task_load_unavailable:'Aufgabe konnte nicht geladen werden'},
            en:{dragula_unavailable:'Cannot initialize drag & drop',task_order_unavailable:'Failed to update task order',assign_toggle_unavailable:'Cannot toggle assignees',task_delete_unavailable:'Cannot delete task',comment_add_unavailable:'Cannot add comment',comment_delete_unavailable:'Cannot delete comment',checklist_add_unavailable:'Cannot add checklist item',checklist_update_unavailable:'Cannot update checklist item',checklist_delete_unavailable:'Cannot delete checklist item',file_add_unavailable:'Cannot add file',file_delete_unavailable:'Cannot delete file',favorite_toggle_unavailable:'Cannot toggle favorite',task_complete_unavailable:'Cannot change completion state',task_progress_unavailable:'Cannot update progress',task_load_unavailable:'Cannot load task details'},
            es:{dragula_unavailable:'No se puede inicializar arrastrar y soltar',task_order_unavailable:'No se pudo actualizar el orden de tareas',assign_toggle_unavailable:'No se pueden alternar asignados',task_delete_unavailable:'No se puede eliminar la tarea',comment_add_unavailable:'No se puede agregar el comentario',comment_delete_unavailable:'No se puede eliminar el comentario',checklist_add_unavailable:'No se puede agregar elemento de lista',checklist_update_unavailable:'No se puede actualizar elemento de lista',checklist_delete_unavailable:'No se puede eliminar elemento de lista',file_add_unavailable:'No se puede agregar el archivo',file_delete_unavailable:'No se puede eliminar el archivo',favorite_toggle_unavailable:'No se puede alternar favorito',task_complete_unavailable:'No se puede cambiar el estado de finalización',task_progress_unavailable:'No se puede actualizar el progreso',task_load_unavailable:'No se pueden cargar los detalles de la tarea'},
            fr:{dragula_unavailable:'Impossible d’initialiser le glisser-déposer',task_order_unavailable:'Échec de la mise à jour de l’ordre des tâches',assign_toggle_unavailable:'Impossible d’alterner les assignés',task_delete_unavailable:'Impossible de supprimer la tâche',comment_add_unavailable:'Impossible d’ajouter le commentaire',comment_delete_unavailable:'Impossible de supprimer le commentaire',checklist_add_unavailable:'Impossible d’ajouter un élément',checklist_update_unavailable:'Impossible de mettre à jour l’élément',checklist_delete_unavailable:'Impossible de supprimer l’élément',file_add_unavailable:'Impossible d’ajouter le fichier',file_delete_unavailable:'Impossible de supprimer le fichier',favorite_toggle_unavailable:'Impossible de basculer le favori',task_complete_unavailable:'Impossible de changer l’état d’achèvement',task_progress_unavailable:'Impossible de mettre à jour la progression',task_load_unavailable:'Impossible de charger la tâche'},
            he:{dragula_unavailable:'לא ניתן לאתחל גרירה ושחרור',task_order_unavailable:'עדכון סדר המשימות נכשל',assign_toggle_unavailable:'לא ניתן להחליף מוקצים',task_delete_unavailable:'לא ניתן למחוק משימה',comment_add_unavailable:'לא ניתן להוסיף תגובה',comment_delete_unavailable:'לא ניתן למחוק תגובה',checklist_add_unavailable:'לא ניתן להוסיף פריט רשימה',checklist_update_unavailable:'לא ניתן לעדכן פריט רשימה',checklist_delete_unavailable:'לא ניתן למחוק פריט רשימה',file_add_unavailable:'לא ניתן להוסיף קובץ',file_delete_unavailable:'לא ניתן למחוק קובץ',favorite_toggle_unavailable:'לא ניתן לשנות מועדף',task_complete_unavailable:'לא ניתן לשנות מצב השלמה',task_progress_unavailable:'לא ניתן לעדכן התקדמות',task_load_unavailable:'לא ניתן לטעון משימה'},
            it:{dragula_unavailable:'Impossibile inizializzare drag & drop',task_order_unavailable:'Aggiornamento ordine attività non riuscito',assign_toggle_unavailable:'Impossibile cambiare assegnatari',task_delete_unavailable:'Impossibile eliminare attività',comment_add_unavailable:'Impossibile aggiungere commento',comment_delete_unavailable:'Impossibile eliminare commento',checklist_add_unavailable:'Impossibile aggiungere elemento',checklist_update_unavailable:'Impossibile aggiornare elemento',checklist_delete_unavailable:'Impossibile eliminare elemento',file_add_unavailable:'Impossibile aggiungere file',file_delete_unavailable:'Impossibile eliminare file',favorite_toggle_unavailable:'Impossibile cambiare preferito',task_complete_unavailable:'Impossibile cambiare stato completamento',task_progress_unavailable:'Impossibile aggiornare avanzamento',task_load_unavailable:'Impossibile caricare dettagli attività'},
            ja:{dragula_unavailable:'ドラッグ＆ドロップを初期化できません',task_order_unavailable:'タスク順序を更新できませんでした',assign_toggle_unavailable:'担当者を切り替えできません',task_delete_unavailable:'タスクを削除できません',comment_add_unavailable:'コメントを追加できません',comment_delete_unavailable:'コメントを削除できません',checklist_add_unavailable:'チェックリスト項目を追加できません',checklist_update_unavailable:'チェックリスト項目を更新できません',checklist_delete_unavailable:'チェックリスト項目を削除できません',file_add_unavailable:'ファイルを追加できません',file_delete_unavailable:'ファイルを削除できません',favorite_toggle_unavailable:'お気に入りを切り替えできません',task_complete_unavailable:'完了状態を変更できません',task_progress_unavailable:'進捗を更新できません',task_load_unavailable:'タスク詳細を読み込めません'},
            nl:{dragula_unavailable:'Slepen en neerzetten kan niet worden gestart',task_order_unavailable:'Volgorde taken bijwerken mislukt',assign_toggle_unavailable:'Toewijzingen kunnen niet worden gewisseld',task_delete_unavailable:'Taak kan niet worden verwijderd',comment_add_unavailable:'Opmerking kan niet worden toegevoegd',comment_delete_unavailable:'Opmerking kan niet worden verwijderd',checklist_add_unavailable:'Checklistitem kan niet worden toegevoegd',checklist_update_unavailable:'Checklistitem kan niet worden bijgewerkt',checklist_delete_unavailable:'Checklistitem kan niet worden verwijderd',file_add_unavailable:'Bestand kan niet worden toegevoegd',file_delete_unavailable:'Bestand kan niet worden verwijderd',favorite_toggle_unavailable:'Favoriet kan niet worden gewijzigd',task_complete_unavailable:'Voltooid-status kan niet worden gewijzigd',task_progress_unavailable:'Voortgang kan niet worden bijgewerkt',task_load_unavailable:'Taak kan niet worden geladen'},
            pl:{dragula_unavailable:'Nie można zainicjować przeciągania',task_order_unavailable:'Nie udało się zaktualizować kolejności zadań',assign_toggle_unavailable:'Nie można przełączyć przypisań',task_delete_unavailable:'Nie można usunąć zadania',comment_add_unavailable:'Nie można dodać komentarza',comment_delete_unavailable:'Nie można usunąć komentarza',checklist_add_unavailable:'Nie można dodać elementu listy',checklist_update_unavailable:'Nie można zaktualizować elementu listy',checklist_delete_unavailable:'Nie można usunąć elementu listy',file_add_unavailable:'Nie można dodać pliku',file_delete_unavailable:'Nie można usunąć pliku',favorite_toggle_unavailable:'Nie można zmienić ulubionych',task_complete_unavailable:'Nie można zmienić stanu ukończenia',task_progress_unavailable:'Nie można zaktualizować postępu',task_load_unavailable:'Nie można wczytać zadania'},
            pt:{dragula_unavailable:'Não foi possível iniciar arrastar & soltar',task_order_unavailable:'Falha ao atualizar ordem das tarefas',assign_toggle_unavailable:'Não foi possível alternar responsáveis',task_delete_unavailable:'Não foi possível excluir tarefa',comment_add_unavailable:'Não foi possível adicionar comentário',comment_delete_unavailable:'Não foi possível excluir comentário',checklist_add_unavailable:'Não foi possível adicionar item',checklist_update_unavailable:'Não foi possível atualizar item',checklist_delete_unavailable:'Não foi possível excluir item',file_add_unavailable:'Não foi possível adicionar arquivo',file_delete_unavailable:'Não foi possível excluir arquivo',favorite_toggle_unavailable:'Não foi possível alternar favorito',task_complete_unavailable:'Não foi possível alterar conclusão',task_progress_unavailable:'Não foi possível atualizar progresso',task_load_unavailable:'Não foi possível carregar tarefa'},
            'pt-br':{dragula_unavailable:'Não foi possível iniciar arrastar & soltar',task_order_unavailable:'Falha ao atualizar ordem das tarefas',assign_toggle_unavailable:'Não foi possível alternar responsáveis',task_delete_unavailable:'Não foi possível excluir tarefa',comment_add_unavailable:'Não foi possível adicionar comentário',comment_delete_unavailable:'Não foi possível excluir comentário',checklist_add_unavailable:'Não foi possível adicionar item',checklist_update_unavailable:'Não foi possível atualizar item',checklist_delete_unavailable:'Não foi possível excluir item',file_add_unavailable:'Não foi possível adicionar arquivo',file_delete_unavailable:'Não foi possível excluir arquivo',favorite_toggle_unavailable:'Não foi possível alternar favorito',task_complete_unavailable:'Não foi possível alterar conclusão',task_progress_unavailable:'Não foi possível atualizar progresso',task_load_unavailable:'Não foi possível carregar tarefa'},
            ru:{dragula_unavailable:'Не удалось инициализировать перетаскивание',task_order_unavailable:'Не удалось обновить порядок задач',assign_toggle_unavailable:'Не удалось переключить исполнителей',task_delete_unavailable:'Не удалось удалить задачу',comment_add_unavailable:'Не удалось добавить комментарий',comment_delete_unavailable:'Не удалось удалить комментарий',checklist_add_unavailable:'Не удалось добавить элемент чек-листа',checklist_update_unavailable:'Не удалось обновить элемент чек-листа',checklist_delete_unavailable:'Не удалось удалить элемент чек-листа',file_add_unavailable:'Не удалось добавить файл',file_delete_unavailable:'Не удалось удалить файл',favorite_toggle_unavailable:'Не удалось переключить избранное',task_complete_unavailable:'Не удалось изменить состояние выполнения',task_progress_unavailable:'Не удалось обновить прогресс',task_load_unavailable:'Не удалось загрузить задачу'},
            tr:{dragula_unavailable:'Sürükle-bırak başlatılamıyor',task_order_unavailable:'Görev sırası güncellenemedi',assign_toggle_unavailable:'Atananlar değiştirilemiyor',task_delete_unavailable:'Görev silinemiyor',comment_add_unavailable:'Yorum eklenemiyor',comment_delete_unavailable:'Yorum silinemiyor',checklist_add_unavailable:'Kontrol listesi öğesi eklenemiyor',checklist_update_unavailable:'Kontrol listesi öğesi güncellenemiyor',checklist_delete_unavailable:'Kontrol listesi öğesi silinemiyor',file_add_unavailable:'Dosya eklenemiyor',file_delete_unavailable:'Dosya silinemiyor',favorite_toggle_unavailable:'Favori değiştirilemiyor',task_complete_unavailable:'Tamamlama durumu değiştirilemiyor',task_progress_unavailable:'İlerleme güncellenemiyor',task_load_unavailable:'Görev yüklenemiyor'},
            zh:{dragula_unavailable:'无法初始化拖放',task_order_unavailable:'更新任务顺序失败',assign_toggle_unavailable:'无法切换指派人',task_delete_unavailable:'无法删除任务',comment_add_unavailable:'无法添加评论',comment_delete_unavailable:'无法删除评论',checklist_add_unavailable:'无法添加清单项',checklist_update_unavailable:'无法更新清单项',checklist_delete_unavailable:'无法删除清单项',file_add_unavailable:'无法添加文件',file_delete_unavailable:'无法删除文件',favorite_toggle_unavailable:'无法切换收藏',task_complete_unavailable:'无法更改完成状态',task_progress_unavailable:'无法更新进度',task_load_unavailable:'无法加载任务详情'}
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED='data-listener-added';
            const ERR_FB='# ERROR';
            const DATA_CLIENT_LOCALIZED='data-client-localized';
            const DATA_GUARD_MSG='data-guard-msg';

            const getLocalizedMessage=(el,key)=>{
            let msg=ERR_FB;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
                msg=el.getAttribute(DATA_GUARD_MSG)||ERR_FB;
            }else{
                let lang=(sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang=lang==='pt-br'?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[key]||window.translations?.en?.[key]||ERR_FB;
                if(msg!==ERR_FB){ el?.setAttribute(DATA_GUARD_MSG,msg); el?.setAttribute(DATA_CLIENT_LOCALIZED,'true'); }
            }
            return msg;
            };

            const showToast=(msg)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(msg); }
            };

            const attachPointerGuard=(el,key)=>{
            if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showToast(getLocalizedMessage(el,key));
            el.addEventListener('pointerup',handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('pointerup',handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const safeAjax=(opts, guardEl, key)=>{
            try{ $.ajax(opts); }catch{ attachPointerGuard(guardEl,key); }
            };

            try{
                if(typeof $==="undefined"){ 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");     
                    return; 
                }

            // Dragula board
            try{
                $('[data-plugin="dragula"]').each(function(){
                const root=this;
                const ids=$(root).data('containers')||[];
                const containers=ids.length?ids.map(id=>document.getElementById(id)).filter(Boolean):[root];
                const handleClass=$(root).data('handleclass');
                if(typeof dragula==='undefined'){ 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) {
                        console.error("Dragula unavailable", e);
                    }
                    attachPointerGuard(root,'dragula_unavailable'); return; }
                const drake=dragula(containers, handleClass?{ moves:(_el,_src,handle)=>handle.classList.contains(handleClass) }:undefined);
                drake.on('drop',(el,target,source)=>{
                    try{
                    const sort=[];
                    $('#'+target.id+' > div').each(function(){ sort[$(this).index()]=$(this).attr('id'); });
                    const id=el?.id??'';
                    const old_stage=$('#'+source.id).data('status');
                    const new_stage=$('#'+target.id).data('status');
                    const project_id='{{ $project->id }}';
                    $('#'+source.id).parent().find('.count').text($('#'+source.id+' > div').length);
                    $('#'+target.id).parent().find('.count').text($('#'+target.id+' > div').length);
                    safeAjax({
                        url:'{{ route(VW::PRJ . ".tasks.update.order",[$project->id]) }}',
                        type:'PATCH',
                        data:{ id, sort, new_stage, old_stage, project_id, _token:'{{ csrf_token() }}' }
                    }, target, 'task_order_unavailable');
                    }catch{ attachPointerGuard(target,'task_order_unavailable'); }
                });
                });
            }catch{ attachPointerGuard(document.body,'dragula_unavailable'); }

            // Assign users
            $(document).on('click','.add_usr',function(){
                try{
                const ids=[];
                $(this).toggleClass('selected');
                const crr_id=$(this).attr('data-id');
                const $txt=$('#usr_txt_'+crr_id);
                $txt.html($txt.html()==='Add'?'{{ __("Added") }}':'{{ __("Add") }}');
                const $icon=$('#usr_icon_'+crr_id);
                if($icon.hasClass('ti-plus')){ $icon.removeClass('ti-plus').addClass('ti-check'); }
                else { $icon.removeClass('ti-check').addClass('ti-plus'); }
                $('.selected').each(function(){ ids.push($(this).attr('data-id')); });
                $('input[name="assign_to"]').val(ids);
                }catch{ attachPointerGuard(this,'assign_toggle_unavailable'); }
            });

            // Delete task
            $(document).on('click','.del_task',function(){
                const btn=this;
                safeAjax({
                url:$(btn).attr('data-url')||'',
                type:'DELETE',
                dataType:'JSON',
                data:{ _token:'{{ csrf_token() }}' },
                success:(data)=>{ if(data?.task_id){ $('#'+data.task_id).remove(); show_toastr('{{ __("success") }}','{{ __("Task Deleted Successfully!") }}'); } }
                }, btn,'task_delete_unavailable');
            });

            // Add comment
            $(document).on('click','#comment_submit',function(){
                const el=this;
                try{
                const comment=$.trim($("#form-comment textarea[name='comment']").val()||'');
                if(!comment){ show_toastr('error','{{ __("Please write comment!") }}'); return; }
                safeAjax({
                    url:$("#form-comment").data('action')||'',
                    type:'POST',
                    data:{ comment, _token:'{{ csrf_token() }}' },
                    success:(json)=>{
                    let data;
                    try{ data=typeof json==='string'?JSON.parse(json):json; }catch{ attachPointerGuard(el,'comment_add_unavailable'); return; }
                    const html =
                        "<div class='list-group-item px-0'>"+
                        " <div class='row align-items-center'>"+
                        "  <div class='col-auto'><a href='#' class='avatar avatar-sm rounded-circle ms-2'>"+
                        "   <img src="+(data.default_img||'')+" alt='' class='avatar-sm rounded-circle'></a></div>"+
                        "  <div class='col ml-n2'>"+
                        "   <p class='d-block h6 text-sm font-weight-light mb-0 text-break'>"+(data.comment||'')+"</p>"+
                        "   <small class='d-block'>"+(data.current_time||'')+"</small></div>"+
                        "  <div class='action-btn bg-danger me-4'><div class='col-auto'>"+
                        "   <a href='#' class='mx-3 btn btn-sm align-items-center delete-comment' data-url='"+(data.deleteUrl||'#')+"'>"+
                        "    <i class='ti ti-trash text-white'></i></a></div></div></div></div>";
                    $("#comments").prepend(html);
                    $("#form-comment textarea[name='comment']").val('');
                    load_task($(el).closest('.task-id').attr('id'));
                    show_toastr('{{ __("success") }}','{{ __("Comment Added Successfully!") }}');
                    },
                    error:()=>attachPointerGuard(el,'comment_add_unavailable')
                }, el,'comment_add_unavailable');
                }catch{ attachPointerGuard(el,'comment_add_unavailable'); }
            });

            // Delete comment
            $(document).on('click','.delete-comment',function(){
                const btn=this;
                safeAjax({
                url:$(btn).attr('data-url')||'',
                type:'DELETE',
                dataType:'JSON',
                data:{ _token:'{{ csrf_token() }}' },
                success:()=>{ load_task($(btn).closest('.task-id').attr('id')); show_toastr('{{ __("success") }}','{{ __("Comment Deleted Successfully!") }}'); $(btn).closest('.list-group-item').remove(); },
                error:(xhr)=>{ const m=xhr?.responseJSON?.message; show_toastr('error', m||'{{ __("Some Thing Is Wrong!") }}'); }
                }, btn,'comment_delete_unavailable');
            });

            // Add checklist item
            $(document).on('click','#checklist_submit',function(){
                const el=this;
                try{
                const name=$("#form-checklist input[name=name]").val()||'';
                if(!name){ show_toastr('error','{{ __("Please write checklist name!") }}'); return; }
                safeAjax({
                    url:$("#form-checklist").data('action')||'',
                    type:'POST',
                    data:{ name, _token:'{{ csrf_token() }}' },
                    success:(json)=>{
                    let data;
                    try{ data=typeof json==='string'?JSON.parse(json):json; }catch{ attachPointerGuard(el,'checklist_add_unavailable'); return; }
                    load_task($('.task-id').attr('id'));
                    show_toastr('{{ __("success") }}','{{ __("Checklist Added Successfully!") }}');
                    const html =
                        '<div class="card border shadow-none checklist-member">'+
                        ' <div class="px-3 py-2 row align-items-center">'+
                        '  <div class="col">'+
                        '   <div class="form-check form-check-inline">'+
                        '    <input type="checkbox" class="form-check-input" id="check-item-'+data.id+'" value="'+data.id+'" data-url="'+data.updateUrl+'">'+
                        '    <label class="form-check-label h6 text-sm" for="check-item-'+data.id+'">'+data.name+'</label>'+
                        '   </div></div>'+
                        '  <div class="col-auto"><div class="action-btn bg-danger ms-2">'+
                        '   <a href="#" class="mx-3 btn btn-sm align-items-center delete-checklist" data-url="'+data.deleteUrl+'">'+
                        '    <i class="ti ti-trash text-white"></i></a></div></div></div></div>';
                    $("#checklist").append(html);
                    $("#form-checklist input[name=name]").val('');
                    $("#form-checklist").collapse('toggle');
                    },
                    error:(xhr)=>{ show_toastr('error', xhr?.responseJSON?.message||'{{ __("Some Thing Is Wrong!") }}'); }
                }, el,'checklist_add_unavailable');
                }catch{ attachPointerGuard(el,'checklist_add_unavailable'); }
            });

            // Toggle checklist item
            $(document).on('change','#checklist input[type=checkbox]',function(){
                const el=this;
                safeAjax({
                url:$(el).attr('data-url')||'',
                type:'POST',
                dataType:'JSON',
                data:{ _token:'{{ csrf_token() }}' },
                success:()=>{ load_task($('.task-id').attr('id')); show_toastr('{{ __("Success") }}','{{ __("Checklist Updated Successfully!") }}','success'); },
                error:(xhr)=>{ show_toastr('error', xhr?.responseJSON?.message||'{{ __("Some Thing Is Wrong!") }}'); attachPointerGuard(el,'checklist_update_unavailable'); }
                }, el,'checklist_update_unavailable');
            });

            // Delete checklist item
            $(document).on('click','.delete-checklist',function(){
                const btn=this;
                safeAjax({
                url:$(btn).attr('data-url')||'',
                type:'DELETE',
                dataType:'JSON',
                data:{ _token:'{{ csrf_token() }}' },
                success:()=>{ load_task($('.task-id').attr('id')); show_toastr('{{ __("success") }}','{{ __("Checklist Deleted Successfully!") }}'); $(btn).closest('.checklist-member').remove(); },
                error:(xhr)=>{ show_toastr('error', xhr?.responseJSON?.message||'{{ __("Some Thing Is Wrong!") }}'); attachPointerGuard(btn,'checklist_delete_unavailable'); }
                }, btn,'checklist_delete_unavailable');
            });

            // Upload attachment
            $(document).on('click','#file_attachment_submit',function(){
                const btn=this;
                try{
                const file=$("#task_attachment").prop('files')?.[0];
                if(!file){ show_toastr('error','{{ __("Please select file!") }}'); return; }
                const fd=new FormData();
                fd.append('file',file);
                fd.append('_token','{{ csrf_token() }}');
                safeAjax({
                    url:$("#file_attachment_submit").data('action')||'',
                    type:'POST',
                    data:fd,
                    cache:false,
                    processData:false,
                    contentType:false,
                    success:(json)=>{
                    $("#task_attachment").val(''); $('.attachment_text').html('{{ __("Choose a file…") }}');
                    const data=typeof json==='string'?JSON.parse(json):json;
                    load_task(data.task_id);
                    show_toastr('{{ __("success") }}','{{ __("File Added Successfully!") }}');
                    const delLink = (data.deleteUrl||'').length
                        ? ' <div class="action-btn bg-danger "><a href="#" class="action-item delete-comment-file" data-url="'+data.deleteUrl+'"><i class="ti ti-trash text-white"></i></a></div>'
                        : '';
                    const html =
                        '<div class="card mb-3 border shadow-none task-file"><div class="px-3 py-3"><div class="row align-items-center">'+
                        ' <div class="col ml-n2"><h6 class="text-sm mb-0"><a href="#">'+data.name+'</a></h6><p class="card-text small text-muted">'+data.file_size+'</p></div>'+
                        ' <div class="col-auto"><div class="action-btn bg-secondary "><a href="{{ asset(Storage::url('tasks')) }}/'+data.file+'" download class="action-item"><i class="ti ti-download text-white"></i></a></div></div>'+
                        delLink+
                        ' </div></div></div>';
                    $("#comments-file").prepend(html);
                    },
                    error:(xhr)=>{ const r=xhr?.responseJSON; show_toastr('error', r?.errors?.file?.[0]||'{{ __("Some Thing Is Wrong!") }}'); if(r?.errors?.file?.[0]){ $('#file-error').text(r.errors.file[0]).show(); } }
                }, btn,'file_add_unavailable');
                }catch{ attachPointerGuard(btn,'file_add_unavailable'); }
            });

            // Delete attachment
            $(document).on('click','.delete-comment-file',function(){
                const btn=this;
                safeAjax({
                url:$(btn).attr('data-url')||'',
                type:'DELETE',
                dataType:'JSON',
                data:{ _token:'{{ csrf_token() }}' },
                success:()=>{ load_task($(btn).closest('.task-id').attr('id')); show_toastr('{{ __("success") }}','{{ __("File Deleted Successfully!") }}'); $(btn).closest('.task-file').remove(); },
                error:(xhr)=>{ show_toastr('error', xhr?.responseJSON?.message||'{{ __("Some Thing Is Wrong!") }}'); attachPointerGuard(btn,'file_delete_unavailable'); }
                }, btn,'file_delete_unavailable');
            });

            // Favorite toggle
            $(document).on('click','#add_favourite',function(){
                const el=this;
                safeAjax({
                url:$(el).attr('data-url')||'',
                type:'POST',
                data:{ _token:'{{ csrf_token() }}' },
                success:(data)=>{ if(data?.fav===1){ $('#add_favourite').addClass('action-favorite'); } else if(data?.fav===0){ $('#add_favourite').removeClass('action-favorite'); } },
                error:()=>attachPointerGuard(el,'favorite_toggle_unavailable')
                }, el,'favorite_toggle_unavailable');
            });

            // Complete toggle
            $(document).on('change','#complete_task',function(){
                const el=this;
                safeAjax({
                url:$(el).attr('data-url')||'',
                type:'POST',
                data:{ _token:'{{ csrf_token() }}' },
                success:(data)=>{
                    if(data?.com===1){ $("#complete_task").prop('checked',true); }
                    else if(data?.com===0){ $("#complete_task").prop('checked',false); }
                    if(data?.task && data?.stage){ $('#'+data.task).insertBefore($('#task-list-'+data.stage+' .empty-container')); load_task(data.task); }
                },
                error:()=>attachPointerGuard(el,'task_complete_unavailable')
                }, el,'task_complete_unavailable');
            });

            // Progress change
            $(document).on('change','#task_progress',function(){
                const el=this;
                const progress=$(el).val() ?? 0;
                $('#t_percentage').html(progress);
                safeAjax({
                url:$(el).attr('data-url')||'',
                type:'POST',
                data:{ progress, _token:'{{ csrf_token() }}' },
                success:(data)=>{ if(data?.task_id) load_task(data.task_id); },
                error:()=>attachPointerGuard(el,'task_progress_unavailable')
                }, el,'task_progress_unavailable');
            });

            // Load task details
            window.load_task=(id)=>{
                const target=document.getElementById(id);
                safeAjax({
                url:"{{ route(ViewsConstants::PRJ_TSK_C.'.get','_task_id') }}".replace('_task_id', id||''),
                dataType:'html',
                data:{ _token:'{{ csrf_token() }}' },
                success:(html)=>{ if(target){ $('#'+id).html(html); } }
                }, target||document.body,'task_load_unavailable');
            };

            }catch(e){ 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) {
                    console.error("Initialization failed", e);
                }
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
    <li class="breadcrumb-item">{{__('Task')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
@endsection
@section(YieldingConstants::ADM_CTT)
    @php
        $containersJson = json_encode(is_array($stageClass ?? null) ? $stageClass : []);
        $stagesSafe = (isset($stages) && (is_array($stages) || $stages instanceof Collection)) ? $stages : [];
        $projectIdSafe = data_get($project ?? null, 'id');
    @endphp
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::RW }} kanban-wrapper horizontal-scroll-cards" data-containers='{{ $containersJson }}' data-plugin="dragula">
                @foreach($stagesSafe as $stage)
                    @php
                        $stageId = data_get($stage,'id');
                        $stageName = data_get($stage,'name') ?? __('No stage name available');
                        $stageTasks = is_iterable(data_get($stage,'tasks')) ? data_get($stage,'tasks') : [];
                        $createUrl = ($projectIdSafe && $stageId) ? route(ViewsConstants::PRJ_TSK_C.'.create',[$projectIdSafe,$stageId]) : '#';
                    @endphp
                    <div class="col">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                @can('create project task')
                                    <div class="{{ VC::FEND }}">
                                        <a href="#" data-size="lg" data-url="{{ $createUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Add Task in ') . $stageName }}" class="{{ VC::BT_SM_PM }}">
                                            <i class="{{ VC::TI_PLS }}"></i>
                                        </a>
                                    </div>
                                @endcan
                                <h4 class="{{ VC::MB0 }}">{{ $stageName }}</h4>
                            </div>
                            <div class="card-body kanban-box" id="task-list-{{ $stageId ?? 'x' }}" data-status="{{ $stageId ?? '' }}">
                                @foreach($stageTasks as $taskDetail)
                                    @php
                                        $taskId = data_get($taskDetail,'id');
                                        $priorityIdx = (int)(data_get($taskDetail,'priority') ?? -1);
                                        $prioColors = is_array(ProjectTask::$priority_color ?? null) ? ProjectTask::$priority_color : [];
                                        $prioLabels = is_array(ProjectTask::$priority ?? null) ? ProjectTask::$priority : [];
                                        $prioBg = $prioColors[$priorityIdx] ?? 'secondary';
                                        $prioLabel = __($prioLabels[$priorityIdx] ?? __('Unknown'));
                                        $taskName = data_get($taskDetail,'name') ?? __('No task name available');
                                        $destroyRouteParams = ($projectIdSafe && $taskId) ? [ViewsConstants::PRJ_TSK_C.'.destroy', [$projectIdSafe,$taskId]] : null;
                                        $progressArr = (method_exists($taskDetail,'taskProgress')) ? (array)$taskDetail->taskProgress($taskDetail) : [];
                                        $pctStr = (string)($progressArr['percentage'] ?? '0%');
                                        $pctNum = (float)str_replace('%','',$pctStr);
                                        $pctColor = (string)($progressArr['color'] ?? 'secondary');
                                        $endDate = data_get($taskDetail,'end_date');
                                        $endDateText = (!empty($endDate) && $endDate !== '0000-00-00') ? (Utility::getDateFormated($endDate) ?? '') : '';
                                        $isOverdue = $endDateText && (strtotime((string)$endDate) < time());
                                        $filesRel = data_get($taskDetail,'taskFiles');
                                        $filesCount = is_countable($filesRel) ? count($filesRel) : ((is_object($filesRel) && method_exists($filesRel,'count')) ? $filesRel->count() : 0);
                                        $commentsRel = data_get($taskDetail,'comments');
                                        $commentsCount = is_countable($commentsRel) ? count($commentsRel) : ((is_object($commentsRel) && method_exists($commentsRel,'count')) ? $commentsRel->count() : 0);
                                        $checklistTotal = method_exists($taskDetail,'countTaskChecklist') ? (int)$taskDetail->countTaskChecklist() : 0;
                                        $usersRel = method_exists($taskDetail,'users') ? $taskDetail->users() : collect();
                                        $usersIter = Utility::isFilled($usersRel) ? $usersRel : [];
                                    @endphp
                                    <div class="{{ VC::CD }} draggable-item" id="{{ $taskId ?? 'x' }}">
                                        <div class="pt-3 ps-3">
                                            <div class="{{ VC::BDG_XS }} p-2 {{ VC::PX3 }} rounded bg-{{ $prioBg }}">{{ $prioLabel }}</div>
                                        </div>
                                        <div class="card-header border-0 pb-0 position-relative">
                                            <h5>
                                                @php
                                                    $taskShowBaseName     = ViewsConstants::PRJ_TSK_C.'.show';
                                                    $taskShowKebabName    = Str::kebab($taskShowBaseName);
                                                    $taskShowResolvedName = Route::has($taskShowBaseName)
                                                        ? $taskShowBaseName
                                                        : (Route::has($taskShowKebabName) ? $taskShowKebabName : null);
                                                    $projectIdValue       = isset($projectIdSafe) && !empty($projectIdSafe) ? $projectIdSafe : null;
                                                    $taskIdValue          = isset($taskId) && !empty($taskId) ? $taskId : null;
                                                    $taskNameText         = e($taskName ?? __('Untitled Task'));
                                                    $taskShowUrl          = ($taskShowResolvedName && $projectIdValue && $taskIdValue) ? route($taskShowResolvedName, [$projectIdValue, $taskIdValue]) : '#';
                                                    $taskShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'show_task_route_unavailable') ?? 'Show task route is unavailable. Please contact technical support or your domain administrator.';
                                                    $taskShowLinkId       = 'task-show-link-'.($taskIdValue ?? 'x');
                                                @endphp
                                                <a href="{{ $taskShowUrl }}"
                                                id="{{ $taskShowLinkId }}"
                                                data-url="{{ $taskShowUrl }}"
                                                data-ajax-popup="true"
                                                data-size="lg"
                                                data-guard-msg="{{ $taskShowGuardMsg }}"
                                                data-bs-original-title="{{ $taskNameText }}">
                                                    {{ $taskNameText }}
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const l = document.getElementById('{{ $taskShowLinkId }}');
                                                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                l.setAttribute('data-listener-active', 'true');
                                                                l.addEventListener('click', e => {
                                                                    try {
                                                                        const href = l.getAttribute('href') || '#';
                                                                        const url  = l.getAttribute('data-url') || href || '#';
                                                                        if (href !== '#' || url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = l.getAttribute('data-guard-msg') || 'Show task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                        l.setAttribute('data-failed-route', 'true');
                                                                    } catch (err) {}
                                                                });
                                                            } catch (error) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </h5>
                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="{{ VC::BT }} dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="{{ VC::TD_DOTV }}"></i>
                                                    </button>
                                                    <div class="{{ VC::DRP_MN_EM }}">
                                                        @can('view project task')
                                                            @php
                                                                $taskShowDropdownLinkId = isset($taskShowLinkId) ? ($taskShowLinkId.'-dd') : ('task-show-dd-link-'.($taskIdValue ?? 'x'));
                                                                $taskShowDropdownTitle  = __('View');
                                                            @endphp
                                                            <a href="{{ $taskShowUrl }}"
                                                            id="{{ $taskShowDropdownLinkId }}"
                                                            data-size="md"
                                                            data-url="{{ $taskShowUrl }}"
                                                            data-ajax-popup="true"
                                                            class="dropdown-item"
                                                            data-guard-msg="{{ $taskShowGuardMsg }}"
                                                            data-bs-original-title="{{ $taskShowDropdownTitle }}">
                                                                <i class="ti ti-bookmark"></i><span>{{ $taskShowDropdownTitle }}</span>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $taskShowDropdownLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', e => {
                                                                                try {
                                                                                    const href = l.getAttribute('href') || '#';
                                                                                    const url  = l.getAttribute('data-url') || href || '#';
                                                                                    if (href !== '#' || url !== '#') return;
                                                                                    e.preventDefault();
                                                                                    const msg = l.getAttribute('data-guard-msg') || 'Show task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                        @can('edit project task')
                                                            @php
                                                                $taskEditBaseName     = ViewsConstants::PRJ_TSK_C.'.edit';
                                                                $taskEditKebabName    = Str::kebab($taskEditBaseName);
                                                                $taskEditResolvedName = Route::has($taskEditBaseName)
                                                                    ? $taskEditBaseName
                                                                    : (Route::has($taskEditKebabName) ? $taskEditKebabName : null);
                                                                $projectIdVal         = isset($projectIdValue) ? $projectIdValue : (isset($projectIdSafe) && !empty($projectIdSafe) ? $projectIdSafe : null);
                                                                $taskIdVal            = isset($taskIdValue) ? $taskIdValue : (isset($taskId) && !empty($taskId) ? $taskId : null);
                                                                $taskNameTxt          = isset($taskNameText) ? $taskNameText : e($taskName ?? __('Untitled Task'));
                                                                $taskEditUrl          = ($taskEditResolvedName && $projectIdVal && $taskIdVal) ? route($taskEditResolvedName, [$projectIdVal, $taskIdVal]) : '#';
                                                                $taskEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'edit_task_route_unavailable') ?? 'Edit task route is unavailable. Please contact technical support or your domain administrator.';
                                                                $taskEditLinkId       = 'task-edit-link-'.($taskIdVal ?? 'x');
                                                            @endphp
                                                            <a href="{{ $taskEditUrl }}"
                                                            id="{{ $taskEditLinkId }}"
                                                            data-size="lg"
                                                            data-url="{{ $taskEditUrl }}"
                                                            data-ajax-popup="true"
                                                            class="dropdown-item"
                                                            data-guard-msg="{{ $taskEditGuardMsg }}"
                                                            data-bs-original-title="{{ __('Edit ').$taskNameTxt }}">
                                                                <i class="{{ VC::TI_PC }}"></i><span>{{ __('Edit') }}</span>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $taskEditLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', e => {
                                                                                try {
                                                                                    const href = l.getAttribute('href') || '#';
                                                                                    const url  = l.getAttribute('data-url') || href || '#';
                                                                                    if (href !== '#' || url !== '#') return;
                                                                                    e.preventDefault();
                                                                                    const msg = l.getAttribute('data-guard-msg') || 'Edit task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                            @if($destroyRouteParams)
                                                                @php
                                                                    $taskDestroyBaseName     = ViewsConstants::PRJ_TSK_C.'.destroy';
                                                                    $taskDestroyKebabName    = Str::kebab($taskDestroyBaseName);
                                                                    $taskDestroyResolvedName = Route::has($taskDestroyBaseName)
                                                                        ? $taskDestroyBaseName
                                                                        : (Route::has($taskDestroyKebabName) ? $taskDestroyKebabName : null);
                                                                    $projectIdVal            = isset($projectIdValue) ? $projectIdValue : (isset($projectIdSafe) && !empty($projectIdSafe) ? $projectIdSafe : null);
                                                                    $taskIdVal               = isset($taskIdValue) ? $taskIdValue : (isset($taskId) && !empty($taskId) ? $taskId : null);
                                                                    $taskDestroyRouteArray   = ($taskDestroyResolvedName && $projectIdVal && $taskIdVal) ? [$taskDestroyResolvedName, [$projectIdVal, $taskIdVal]] : ['#'];
                                                                    $taskDestroyUrl          = ($taskDestroyResolvedName && $projectIdVal && $taskIdVal) ? route($taskDestroyResolvedName, [$projectIdVal, $taskIdVal]) : '#';
                                                                    $taskDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'delete_task_route_unavailable') ?? 'Delete task route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $taskDestroyFormId       = 'task-destroy-form-'.($taskIdVal ?? 'x');
                                                                    $taskDestroyLinkId       = 'task-destroy-link-'.($taskIdVal ?? 'x');
                                                                @endphp
                                                                {!! Collective\Html\FormFacade::open([
                                                                    'method'         => 'DELETE',
                                                                    'route'          => $taskDestroyRouteArray,
                                                                    'id'             => $taskDestroyFormId,
                                                                    'data-url'       => $taskDestroyUrl,
                                                                    'data-guard-msg' => $taskDestroyGuardMsg
                                                                ]) !!}
                                                                    @csrf
                                                                    <a href="#!"
                                                                    id="{{ $taskDestroyLinkId }}"
                                                                    class="dropdown-item bs-pass-para"
                                                                    data-form-id="{{ $taskDestroyFormId }}"
                                                                    data-url="{{ $taskDestroyUrl }}"
                                                                    data-guard-msg="{{ $taskDestroyGuardMsg }}">
                                                                        <i class="{{ VC::TI_ARC }}"></i><span>{{ __('Delete') }}</span>
                                                                    </a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const l = document.getElementById('{{ $taskDestroyLinkId }}');
                                                                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                                l.setAttribute('data-listener-active', 'true');
                                                                                l.addEventListener('click', e => {
                                                                                    try {
                                                                                        e.preventDefault();
                                                                                        const formId = l.getAttribute('data-form-id') || '';
                                                                                        const f = formId ? document.getElementById(formId) : null;
                                                                                        if (!f) return;
                                                                                        const url = f.getAttribute('data-url') || l.getAttribute('data-url') || '#';
                                                                                        const action = f.getAttribute('action') || '#';
                                                                                        if (url === '#' && action === '#') {
                                                                                            const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Delete task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="{{ VC::DFL_AIC_JCB }} mb-2">
                                                <ul class="list-inline {{ VC::MB0 }}">
                                                    <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Files') }}">
                                                        <i class="f-16 text-primary {{ VC::TI_FL }}"></i> {{ $filesCount }}
                                                    </li>
                                                    <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Task Progress') }}">
                                                        @if($pctNum > 0)
                                                            <span class="text-md">{{ $pctStr }}</span>
                                                        @endif
                                                    </li>
                                                </ul>
                                                <div class="user-group">
                                                    @if($endDateText)
                                                        <span data-bs-toggle="tooltip" title="{{ __('End Date') }}" @if($isOverdue) class="text-danger" @endif>{{ $endDateText }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <ul class="list-inline {{ VC::MB0 }}">
                                                    <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Comments') }}">
                                                        <i class="f-16 text-primary ti ti-message"></i> {{ $commentsCount }}
                                                    </li>
                                                    <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Task Checklist') }}">
                                                        <i class="f-16 text-primary {{ VC::TI_LT }}"></i>{{ $checklistTotal }}
                                                    </li>
                                                </ul>
                                                <div class="user-group">
                                                    @foreach($usersIter as $u)
                                                        @php
                                                            $uAvatar = data_get($u,'avatar');
                                                            $uSrc = !empty($uAvatar) ? asset('/storage/uploads/avatar/'.$uAvatar) : asset('/storage/uploads/avatar/avatar.png');
                                                            $uName = data_get($u,'name') ?? '';
                                                        @endphp
                                                        <img src="{{ $uSrc }}" alt="image" data-bs-toggle="tooltip" title="{{ $uName }}">
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

