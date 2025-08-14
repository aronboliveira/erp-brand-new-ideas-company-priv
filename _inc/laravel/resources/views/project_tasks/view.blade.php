@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\{User, Utility};
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
<div class="modal-body task-id" id="{{ $task->id }}">
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <h5>{{ __('Task Detail') }}</h5>
            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start">
                        <div class="{{ VC::MS2 }}">
                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Estimated Hours') }}</p>
                            <h3 class="{{ VC::MB0 }} text-success">{{ !empty($task->estimated_hrs) ? number_format($task->estimated_hrs) : '-' }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                    <div class="d-flex align-items-start">
                        <div class="{{ VC::MS2 }}">
                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Milestone') }}</p>
                            <h3 class="{{ VC::MB0 }} text-primary">{{ !empty($task->milestone) ? $task->milestone->title : '-' }}</h3>
                        </div>
                    </div>
                </div>
                @if($allow_progress == 'false')
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="{{ VC::MS2 }}">
                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Task Progress') }}</p>
                                <h3 class="{{ VC::MB0 }} text-danger"><b id="t_percentage">{{ $task->progress }}</b>%</h3>
                                <div class="{{ VC::PG }} {{ VC::MB0 }}">
                                    <div id="progress-result" class="tab-pane tab-example-result fade show active" role="tabpanel" aria-labelledby="progress-result-tab">
                                        <input type="range" class="task_progress custom-range" value="{{ $task->progress }}" id="task_progress" name="progress" data-url="{{ route('change.progress',[$task->project_id,$task->id]) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                <div class="col">
                    <p class="{{ VC::TXSM }} {{ VC::TXT_MT }} mb-2">{{ !empty($task->description) ? $task->description : '-' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Checklist') }}</h5>
                </div>
                <div class="col-6">
                    <div class="{{ VC::FEND }}">
                        <a data-bs-toggle="collapse" href="#form-checklist" role="button" aria-expanded="false" aria-controls="form-checklist" data-bs-toggle="tooltip" title="{{ __('Add item') }}" class="{{ VC::BT_SM_PM }}">
                            <i class="{{ VC::TI_PLS }}"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="checklist" id="checklist">
                <form id="form-checklist" class="{{ VC::CLP }} pb-2" data-action="{{ route('checklist.store',[$task->project_id,$task->id]) }}">
                    <div class="{{ VC::CD_NSD }}">
                        <div class="{{ VC::R_ALC_SMPD }}">
                            @csrf
                            <div class="col-10">
                                <input type="text" name="name" required class="{{ VC::FM_CT }}" placeholder="{{ __('Checklist Name') }}"/>
                            </div>
                            <div class="{{ VC::CL_MT_VC }}">
                                <button class="{{ VC::BT_SM_PM }}" type="button" id="checklist_submit" data-bs-toggle="tooltip" title="{{ __('Create') }}">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                @foreach($task->checklist as $checklist)
                    <div class="{{ VC::CD_NSD }} checklist-member">
                        <div class="{{ VC::R_ALC_SMPD }}">
                            <div class="col">
                                <div class="{{ VC::FM_CHK_IL }}">
                                    <input type="checkbox" class="form-check-input" id="check-item-{{ $checklist->id }}" @if($checklist->status) checked @endif data-url="{{ route('checklist.update',[$task->project_id,$checklist->id]) }}">
                                    <label class="form-check-label {{ VC::H6 }} {{ VC::TXSM }}" for="check-item-{{ $checklist->id }}">{{ $checklist->name }}</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                    <a href="#" class="{{ VC::BT_SM_CT }} delete-checklist" data-url="{{ route('checklist.destroy',[$task->project_id,$checklist->id]) }}">
                                        <i data-bs-toggle="tooltip" title="{{ __('Delete') }}" class="{{ VC::TI_TRS_WT }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Attachments') }}</h5>
                </div>
                <div class="col-6">
                    <div class="{{ VC::FEND }}">
                        <a data-bs-toggle="collapse" href="#add_file" role="button" aria-expanded="false" aria-controls="add_file" data-bs-toggle="tooltip" title="{{ __('Add attachment') }}" class="{{ VC::BT_SM_PM }}">
                            <i class="{{ VC::TI_PLS }}"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="attachments" id="attachments">
                <form id="add_file" class="{{ VC::CLP }} pb-2">
                    <div class="{{ VC::CD_NSD }}">
                        <div class="{{ VC::R_ALC_SMPD }}">
                            @csrf
                            <div class="col-10">
                                <input type="file" name="task_attachment" id="task_attachment" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" required class="{{ VC::FM_CT }}"/>
                            </div>
                            <div class="{{ VC::CL_MT_VC }}">
                                <button class="{{ VC::BT_SM_PM }}" type="button" id="file_attachment_submit" data-action="{{ route('comment.store.file',[$task->project_id,$task->id]) }}" data-bs-toggle="tooltip" title="{{ __('Create') }}">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                            <img id="blah" src="" class="img_preview" />
                        </div>
                    </div>
                </form>

                <div id="comments-file">
                    @foreach($task->taskFiles as $file)
                        <div class="{{ VC::CD }} {{ VC::MB3 }} {{ VC::BD }} {{ VC::SNN }} task-file">
                            <div class="px-3 py-3">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col ml-n2">
                                        <h6 class="{{ VC::TXSM }} {{ VC::MB0 }}">
                                            <a href="#">{{ $file->name }}</a>
                                        </h6>
                                        <p class="card-text small {{ VC::TXT_MT }}">{{ $file->file_size }}</p>
                                    </div>
                                    <div class="col-auto actions">
                                        <div class="action-btn bg-secondary {{ VC::MS2 }}">
                                            <a href="{{ asset(Storage::url('tasks/'.$file->file)) }}" download class="{{ VC::BT_SM_CT }}" role="button">
                                                <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                            </a>
                                        </div>
                                        @auth('web')
                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                <a href="#" class="{{ VC::BT_SM_CT }} delete-comment-file" data-url="{{ route('comment.destroy.file',[$task->project_id,$task->id,$file->id]) }}">
                                                    <i data-bs-toggle="tooltip" title="{{ __('Delete') }}" class="{{ VC::TI_TRS_WT }}"></i>
                                                </a>
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Activity') }}</h5>
                </div>
            </div>

            <div class="activity" id="activity">
                @foreach($task->activityLog() as $activity)
                    @php $activityUser = User::find($activity->user_id); @endphp
                    <div class="{{ VC::LGI }} px-0">
                        <div class="{{ VC::R_ALC }}">
                            <div class="col-auto">
                                <a href="#" class="avatar avatar-sm {{ VC::MS2 }}">
                                    <img data-toggle="tooltip" data-original-title="{{ !empty($activityUser) ? $activityUser->name : '' }}"
                                         @if($activityUser->avatar)
                                             src="{{ asset('/storage/uploads/avatar/'.$activityUser?->avatar) }}"
                                         @else
                                             src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                                         @endif
                                         title="{{ $activityUser?->name }}" class="wid-40 rounded-circle ml-3">
                                </a>
                            </div>
                            <div class="col ml-n2">
                                <span class="text-dark {{ VC::TXSM }}">{{ __($activity->log_type) }}</span>
                                <a class="{{ VC::DBL }} {{ VC::H6 }} {{ VC::TXSM }} font-weight-light {{ VC::MB0 }}">{!! $activity->getRemark() !!}</a>
                                <small class="{{ VC::DBL }}">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::RW }}">
                <div class="col-6">
                    <h5 class="{{ VC::MB3 }}">{{ __('Comments') }}</h5>
                </div>
                @php $plan = Utility::getChatGPTSettings(); @endphp
                @if($plan?->{PlansConstants::COL_GPT} == 1)
                    <div class="col-6 text-end">
                        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm {{ VC::MB3 }} me-2" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['grammar']) }}" data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                            <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                        </a>
                    </div>
                @endif
            </div>

            @if(empty($task->comments))
                <hr/>
            @endif

            <div class="activity" id="comments">
                @foreach($task->comments as $comment)
                    @php $taskUser = User::find($comment->user_id); @endphp
                    <div class="{{ VC::LGI }} px-0 {{ VC::MB1 }}">
                        <div class="{{ VC::R_ALC }}">
                            <div class="col-auto">
                                <a href="#" class="{{ VC::AV_CC_SM }} {{ VC::MS2 }}">
                                    <img data-original-title="{{ !empty($taskUser) ? $taskUser->name : '' }}"
                                         @if($taskUser->avatar)
                                             src="{{ asset('/storage/uploads/avatar/'.$taskUser?->avatar) }}"
                                         @else
                                             src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                                         @endif
                                         title="{{ $comment->user->name }}" class="wid-40 rounded-circle ml-3">
                                </a>
                            </div>
                            <div class="col ml-n2">
                                <p class="{{ VC::DBL }} {{ VC::H6 }} {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break">{{ $comment->comment }}</p>
                                <small class="{{ VC::DBL }}">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-auto">
                                <div class="action-btn bg-danger me-2">
                                    <a href="#" class="{{ VC::BT_SM_CT }} delete-comment" data-url="{{ route('comment.destroy',[$task->project_id,$task->id,$comment->id]) }}">
                                        <i data-bs-toggle="tooltip" title="{{ __('Delete') }}" class="{{ VC::TI_TRS_WT }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer">
            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                <div class="{{ VC::AV }} me-3">
                    <img data-original-title="{{ !empty($user) ? $user?->name : '' }}"
                         @if($user?->avatar)
                             src="{{ asset('/storage/uploads/avatar/'.$user->avatar) }}"
                         @else
                             src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                         @endif
                         title="{{ $user?->name }}" class="wid-40 rounded-circle ml-3">
                </div>
                <div class="{{ VC::FM_G }} {{ VC::MB0 }} form-send w-100">
                    <form method="post" class="card-comment-box" id="form-comment" data-action="{{ route('task.comment.store',[$task->project_id,$task->id]) }}">
                        <textarea rows="1" class="{{ VC::FM_CT }} grammer_textarea" name="comment" data-toggle="autosize" placeholder="{{ __('Add a comment...') }}"></textarea>
                    </form>
                </div>
                <button id="comment_submit" class="btn btn-send"><i class="text-primary ti ti-brand-telegram"></i></button>
            </div>
        </div>
    </div>
</div>
@push('script-page')
    <script async>
        window.translations = {
            ar:{color_picker_unavailable:'تعذّر تهيئة منتقي الألوان',color_change_unavailable:'تعذّر تحديث لون أولوية المهمة'},
            da:{color_picker_unavailable:'Kunne ikke initialisere farvevælger',color_change_unavailable:'Kunne ikke opdatere opgavens prioritesfarve'},
            de:{color_picker_unavailable:'Farbauswahl konnte nicht initialisiert werden',color_change_unavailable:'Aktualisierung der Aufgabenprioritätsfarbe fehlgeschlagen'},
            en:{color_picker_unavailable:'Cannot initialize color picker',color_change_unavailable:'Cannot update task priority color'},
            es:{color_picker_unavailable:'No se puede inicializar el selector de color',color_change_unavailable:'No se puede actualizar el color de prioridad de la tarea'},
            fr:{color_picker_unavailable:'Impossible d’initialiser le sélecteur de couleur',color_change_unavailable:'Impossible de mettre à jour la couleur de priorité de la tâche'},
            he:{color_picker_unavailable:'לא ניתן לאתחל בוחר צבע',color_change_unavailable:'לא ניתן לעדכן את צבע עדיפות המשימה'},
            it:{color_picker_unavailable:'Impossibile inizializzare il selettore colore',color_change_unavailable:'Impossibile aggiornare il colore di priorità del task'},
            ja:{color_picker_unavailable:'カラーピッカーを初期化できません',color_change_unavailable:'タスクの優先色を更新できません'},
            nl:{color_picker_unavailable:'Kan kleurkiezer niet initialiseren',color_change_unavailable:'Kan prioriteitskleur van taak niet bijwerken'},
            pl:{color_picker_unavailable:'Nie można zainicjować selektora kolorów',color_change_unavailable:'Nie można zaktualizować koloru priorytetu zadania'},
            pt:{color_picker_unavailable:'Não foi possível iniciar o seletor de cores',color_change_unavailable:'Não foi possível atualizar a cor de prioridade da tarefa'},
            'pt-br':{color_picker_unavailable:'Não foi possível iniciar o seletor de cores',color_change_unavailable:'Não foi possível atualizar a cor de prioridade da tarefa'},
            ru:{color_picker_unavailable:'Не удалось инициализировать выбор цвета',color_change_unavailable:'Не удалось обновить цвет приоритета задачи'},
            tr:{color_picker_unavailable:'Renk seçici başlatılamıyor',color_change_unavailable:'Görev öncelik rengi güncellenemedi'},
            zh:{color_picker_unavailable:'无法初始化取色器',color_change_unavailable:'无法更新任务优先级颜色'}
        };
    </script>
    <script defer>
        (()=>{
            const ERR_FB='# ERROR';
            const DATA_CLIENT_LOCALIZED='data-client-localized';
            const DATA_GUARD_MSG='data-guard-msg';
            const DATA_LISTENER_ADDED='data-listener-added';

            const getLocalizedMessage=(el,key)=>{
            let msg=ERR_FB;
            if(el?.getAttribute('data-sv-localized')==='true' || el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
                msg=el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
            }else{
                let lang=(sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g,'-');
                lang = (lang === 'pt-br') ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[key]
                || el?.getAttribute?.(DATA_GUARD_MSG)
                || window.translations?.en?.[key]
                || ERR_FB;
                if(msg !== ERR_FB){ el?.setAttribute?.(DATA_GUARD_MSG,msg); el?.setAttribute?.(DATA_CLIENT_LOCALIZED,'true'); }
            }
            return msg;
            };

            const showError=(el,key)=>{
            const text=getLocalizedMessage(el||document.body,key);
            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const n=document.createElement('div');
                n.id='error-toast';
                n.className='toast align-items-center text-bg-danger border-0';
                n.setAttribute('role','alert'); n.setAttribute('aria-live','assertive'); n.setAttribute('aria-atomic','true');
                n.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(n);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{
                alert(text);
            }
            };

            const attachPointerGuard=(el,key)=>{
            if(!el || el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showError(el,key);
            el.addEventListener('pointerup',handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('pointerup',handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            try{
            if(typeof $==='undefined' || !$.fn?.colorPick){
                console.error('Color picker dependency failed to load');
                return;
            }

            $(()=>{
                try{
                $('.colorPickSelector').colorPick({
                    onColorSelected: function(){
                    try{
                        const jqEl = this.element;
                        const rawEl = jqEl?.get?.(0) || jqEl?.[0] || document.body;
                        const taskId = jqEl?.closest('.side-modal')?.attr('id') ?? '';
                        const color = this.color ?? '';
                        if(!taskId || !color) return;
                        jqEl.css({ backgroundColor: color });
                        const endpoint='{{ route('update.task.priority.color') }}' ?? '#';
                        const urlAttr = rawEl?.getAttribute?.('data-url') || '';
                        const hrefAttr = endpoint;
                        if((!urlAttr || urlAttr==='#') && (!hrefAttr || hrefAttr==='#')){
                        attachPointerGuard(rawEl,'color_change_unavailable');
                        return;
                        }
                        $.ajax({
                        url: hrefAttr,
                        method: 'PATCH',
                        data: { task_id: taskId, color },
                        success: (data)=>{
                            try{
                            $('.task-list-items')?.find('#'+taskId)?.attr('style', 'border-left:2px solid '+color+' !important');
                            }catch{ /* noop */ }
                        },
                        error: ()=>attachPointerGuard(rawEl,'color_change_unavailable')
                        });
                    }catch{
                        const el = this?.element?.get?.(0) || this?.element?.[0] || document.body;
                        attachPointerGuard(el,'color_change_unavailable');
                    }
                    }
                });
                }catch{
                const el=document.querySelector('.colorPickSelector') || document.body;
                attachPointerGuard(el,'color_picker_unavailable');
                }
            });

            }catch(e){
            console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
