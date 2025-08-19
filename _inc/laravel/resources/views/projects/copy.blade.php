@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
    use Collective\Html\FormFacade as Form;
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang                                 = Utility::fetchUserLang();
    $projectCopyStoreBaseName             = ViewsConstants::PRJ.'.copy.store';
    $projectCopyStoreKebabName            = Str::kebab($projectCopyStoreBaseName);
    $projectCopyStoreResolvedName         = Route::has($projectCopyStoreBaseName) ? $projectCopyStoreBaseName : (Route::has($projectCopyStoreKebabName) ? $projectCopyStoreKebabName : null);
    $projectCopyStoreRouteArray           = $projectCopyStoreResolvedName ? [$projectCopyStoreResolvedName, $project->id] : ['#'];
    $projectCopyStoreUrl                  = $projectCopyStoreResolvedName ? route($projectCopyStoreResolvedName, $project->id) : '#';
    $projectCopyStoreGuardMsg             = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_copy_store_route_unavailable') ?? 'Project copy store route is unavailable. Please contact technical support or your domain administrator.';
    $projectCopyStoreFormId               = 'project-copy-store-form-' . $project->id;
@endphp
{!! Form::model($project, [
    'route'          => $projectCopyStoreRouteArray,
    'method'         => 'POST',
    'id'             => $projectCopyStoreFormId,
    'data-url'       => $projectCopyStoreUrl,
    'data-guard-msg' => $projectCopyStoreGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('project','all','', ['class' => 'form-check-input', 'id' => 'all']) }}
                        {{ Form::label('all', __('All'), ['class' => 'form-check-label']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('task[]','task','', ['class' => 'form-check-input checkbox', 'id' => 'task']) }}
                        {{ Form::label('task', __('Task'), ['class' => 'form-check-label']) }}
                    </div>
                </div>
                <div class="{{ VC::RW }} mx-4">
                    <div class="col-4 {{ VC::FM_G }}">
                        <div class="{{ VC::FM_CHK }}">
                            {{ Form::checkbox('task[]','sub_task','', ['class' => 'form-check-input checkbox task', 'id' => 'sub_task']) }}
                            {{ Form::label('sub_task', __('Sub Task'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                    <div class="col-4 {{ VC::FM_G }}">
                        <div class="{{ VC::FM_CHK }}">
                            {{ Form::checkbox('task[]','task_comment','', ['class' => 'form-check-input checkbox task', 'id' => 'task_comment']) }}
                            {{ Form::label('task_comment', __('Comment'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                    <div class="col-4 {{ VC::FM_G }}">
                        <div class="{{ VC::FM_CHK }}">
                            {{ Form::checkbox('task[]','task_files','', ['class' => 'form-check-input checkbox task', 'id' => 'task_files']) }}
                            {{ Form::label('task_files', __('Files'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                </div>
            <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('bug[]','bug','', ['class' => 'form-check-input checkbox', 'id' => 'bug']) }}
                        {{ Form::label('bug', __('Bug'), ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="{{ VC::RW }} mx-4">
                    <div class="col-6 {{ VC::FM_G }}">
                        <div class="{{ VC::FM_CHK }}">
                            {{ Form::checkbox('bug[]','bug_comment','', ['class' => 'form-check-input checkbox bug', 'id' => 'bug_comment']) }}
                            {{ Form::label('bug_comment', __('Comment'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                    <div class="col-6 {{ VC::FM_G }}">
                        <div class="{{ VC::FM_CHK }}">
                            {{ Form::checkbox('bug[]','bug_files','', ['class' => 'form-check-input checkbox bug', 'id' => 'bug_files']) }}
                            {{ Form::label('bug_files', __('Files'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                </div>

                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('user[]','user','', ['class' => 'form-check-input checkbox', 'id' => 'user']) }}
                        {{ Form::label('user', __('Team Member'), ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('client[]','client','', ['class' => 'form-check-input checkbox', 'id' => 'client']) }}
                        {{ Form::label('client', __('Client'), ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('milestone[]','milestone','', ['class' => 'form-check-input checkbox', 'id' => 'milestone']) }}
                        {{ Form::label('milestone', __('Milestone'), ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('project_file[]','project_file','', ['class' => 'form-check-input checkbox', 'id' => 'project_file']) }}
                        {{ Form::label('project_file', __('Project File'), ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="{{ VC::FM_G }} m-2">
                    <div class="{{ VC::FM_CHK }}">
                        {{ Form::checkbox('activity[]','activity','', ['class' => 'form-check-input checkbox', 'id' => 'activity']) }}
                        {{ Form::label('activity', __('Activity'), ['class' => 'form-check-label']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Copy') }}</button>
    </div>
{{ Form::close() }}
<script async>
    window.translations = {
    ar:{bulk_check_unavailable:'تعذّر تحديد/إلغاء تحديد الكل',cascade_task_unavailable:'تعذّر تفعيل تبعية مهام',cascade_bug_unavailable:'تعذّر تفعيل تبعية أخطاء'},
    da:{bulk_check_unavailable:'Kunne ikke markere/afmarkere alle',cascade_task_unavailable:'Kunne ikke aktivere afhængige opgaver',cascade_bug_unavailable:'Kunne ikke aktivere afhængige fejl'},
    de:{bulk_check_unavailable:'Alle markieren/aufheben fehlgeschlagen',cascade_task_unavailable:'Abhängige Aufgaben konnten nicht aktiviert werden',cascade_bug_unavailable:'Abhängige Bugs konnten nicht aktiviert werden'},
    en:{bulk_check_unavailable:'Cannot toggle select all',cascade_task_unavailable:'Cannot apply task dependencies',cascade_bug_unavailable:'Cannot apply bug dependencies'},
    es:{bulk_check_unavailable:'No se puede seleccionar/deseleccionar todo',cascade_task_unavailable:'No se pueden aplicar dependencias de tareas',cascade_bug_unavailable:'No se pueden aplicar dependencias de bugs'},
    fr:{bulk_check_unavailable:'Impossible de (dé)sélectionner tout',cascade_task_unavailable:'Impossible d’appliquer les dépendances des tâches',cascade_bug_unavailable:'Impossible d’appliquer les dépendances des anomalies'},
    he:{bulk_check_unavailable:'לא ניתן לבחור/לבטל הכול',cascade_task_unavailable:'לא ניתן להחיל תלות משימות',cascade_bug_unavailable:'לא ניתן להחיל תלות באגים'},
    it:{bulk_check_unavailable:'Impossibile (de)selezionare tutto',cascade_task_unavailable:'Impossibile applicare dipendenze attività',cascade_bug_unavailable:'Impossibile applicare dipendenze bug'},
    ja:{bulk_check_unavailable:'すべての切替に失敗しました',cascade_task_unavailable:'タスクの依存設定を適用できません',cascade_bug_unavailable:'不具合の依存設定を適用できません'},
    nl:{bulk_check_unavailable:'Alles (de)selecteren mislukt',cascade_task_unavailable:'Taakafhankelijkheden toepassen mislukt',cascade_bug_unavailable:'Bugafhankelijkheden toepassen mislukt'},
    pl:{bulk_check_unavailable:'Nie można zaznaczyć/odznaczyć wszystkich',cascade_task_unavailable:'Nie można zastosować zależności zadań',cascade_bug_unavailable:'Nie można zastosować zależności błędów'},
    pt:{bulk_check_unavailable:'Não foi possível (des)selecionar tudo',cascade_task_unavailable:'Não foi possível aplicar dependências de tarefas',cascade_bug_unavailable:'Não foi possível aplicar dependências de bugs'},
    'pt-br':{bulk_check_unavailable:'Não foi possível (des)selecionar tudo',cascade_task_unavailable:'Não foi possível aplicar dependências de tarefas',cascade_bug_unavailable:'Não foi possível aplicar dependências de bugs'},
    ru:{bulk_check_unavailable:'Не удалось выбрать/снять выбор со всех',cascade_task_unavailable:'Не удалось применить зависимости задач',cascade_bug_unavailable:'Не удалось применить зависимости багов'},
    tr:{bulk_check_unavailable:'Tümünü seç/açıklayı kapat yapılamadı',cascade_task_unavailable:'Görev bağımlılıkları uygulanamadı',cascade_bug_unavailable:'Hata bağımlılıkları uygulanamadı'},
    zh:{bulk_check_unavailable:'无法切换全选',cascade_task_unavailable:'无法应用任务依赖',cascade_bug_unavailable:'无法应用缺陷依赖'}
    };
</script>
<script defer>
    (()=>{
    const DATA_LISTENER_ADDED='data-listener-added';
    const ERR_FB='# ERROR';
    const DATA_CLIENT_LOCALIZED='data-client-localized';
    const DATA_GUARD_MSG='data-guard-msg';

    const getLocalizedMessage=(el,key)=>{
        let msg=ERR_FB;
        if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
        msg=el.getAttribute(DATA_GUARD_MSG)||ERR_FB;
        }else{
        let lang=(window.sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
        lang=lang==='pt-br'?lang:lang.slice(0,2);
        msg=window.translations?.[lang]?.[key]||el?.getAttribute(DATA_GUARD_MSG)||window.translations?.en?.[key]||ERR_FB;
        if(msg!==ERR_FB){ el?.setAttribute(DATA_GUARD_MSG,msg); el?.setAttribute(DATA_CLIENT_LOCALIZED,'true'); }
        }
        return msg;
    };

    const handleErrorDisplay=(el,key)=>{
        const message=getLocalizedMessage(el||document.body,key);
        const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
        if(hasBootstrap){
        if(!document.querySelector('#error-toast')){
            const t=document.createElement('div');
            t.id='error-toast';
            t.className='toast align-items-center text-bg-danger border-0';
            t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
            t.innerHTML=`<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
            document.body.appendChild(t);
        }
        new bootstrap.Toast(document.querySelector('#error-toast')).show();
        }else{ alert(message); }
    };

    const attachGuardOnce=(el,key)=>{
        if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
        const clickHandler=()=>handleErrorDisplay(el,key);
        el.addEventListener('click',clickHandler,{once:true});
        el.setAttribute(DATA_LISTENER_ADDED,'true');
        const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('click',clickHandler); o.disconnect(); }});
        mo.observe(document.body,{childList:true,subtree:true});
    };

    try{
        if(typeof $==='undefined'){ console.error('jQuery is required'); return; }

        $(document).on('click','#all',function(){
        try{
            const isChecked=!!this?.checked;
            const $boxes=$('.checkbox');
            if($boxes.length){ $boxes.prop('checked',isChecked); }
        }catch{ attachGuardOnce(this,'bulk_check_unavailable'); }
        });

        $(document).on('click','#sub_task, #task_comment, #task_files',function(){
        try{ $('#task').prop('checked',true); }catch{ attachGuardOnce(this,'cascade_task_unavailable'); }
        });
        $(document).on('click','#bug_comment, #bug_files',function(){
        try{ $('#bug').prop('checked',true); }catch{ attachGuardOnce(this,'cascade_bug_unavailable'); }
        });

        $(document).on('click','#task',function(){
        try{ $('.task').prop('checked',false); }catch{ attachGuardOnce(this,'cascade_task_unavailable'); }
        });
        $(document).on('click','#bug',function(){
        try{ $('.bug').prop('checked',false); }catch{ attachGuardOnce(this,'cascade_bug_unavailable'); }
        });

    }catch(e){ console.error('Initialization failed',e); }
    })();
</script>
<script defer>
    (() => {
        const form = document.getElementById('{{ $projectCopyStoreFormId }}');
        if (!form || form.getAttribute('data-listener-active') === 'true') return;
        form.setAttribute('data-listener-active', 'true');
        form.addEventListener('submit', e => {
            try {
                const url = form.getAttribute('data-url') || '#';
                const action = form.getAttribute('action') || '#';
                if (url !== '#' || action !== '#') return;
                e.preventDefault();
                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
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
                form.setAttribute('data-failed-route', 'true');
            } catch (err) {}
        });
    })();
</script>