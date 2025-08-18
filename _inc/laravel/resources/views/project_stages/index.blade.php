@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, URL};
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Project Stages')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Project Stage')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/jscolor.js') }}"></script>
    <script async src="{{ asset('assets/libs/jquery-ui/jquery-ui.js') }}"></script>
    <script async>
        window.translations = {
            ar:{project_stage_order_unavailable:'فشل تحديث ترتيب مراحل المشروع'},
            da:{project_stage_order_unavailable:'Opdatering af projektfaserækkefølge mislykkedes'},
            de:{project_stage_order_unavailable:'Aktualisieren der Projektphasenreihenfolge fehlgeschlagen'},
            en:{project_stage_order_unavailable:'Failed to update project stages order'},
            es:{project_stage_order_unavailable:'Error al actualizar el orden de las etapas del proyecto'},
            fr:{project_stage_order_unavailable:'Échec de la mise à jour de l’ordre des étapes du projet'},
            he:{project_stage_order_unavailable:'עדכון סדר שלבי הפרויקט נכשל'},
            it:{project_stage_order_unavailable:'Aggiornamento ordine fasi progetto non riuscito'},
            ja:{project_stage_order_unavailable:'プロジェクト段階の順序を更新できませんでした'},
            nl:{project_stage_order_unavailable:'Bijwerken van volgorde projectfasen mislukt'},
            pl:{project_stage_order_unavailable:'Nie udało się zaktualizować kolejności etapów projektu'},
            pt:{project_stage_order_unavailable:'Falha ao atualizar a ordem das etapas do projeto'},
            'pt-br':{project_stage_order_unavailable:'Falha ao atualizar a ordem das etapas do projeto'},
            ru:{project_stage_order_unavailable:'Не удалось обновить порядок этапов проекта'},
            tr:{project_stage_order_unavailable:'Proje aşamaları sırası güncellenemedi'},
            zh:{project_stage_order_unavailable:'无法更新项目阶段顺序'}
        };
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

            const showToast=(text)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(text); }
            };

            const attachPointerGuard=(el,key)=>{
            if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showToast(getLocalizedMessage(el,key));
            el.addEventListener('pointerup',handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('pointerup',handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            try{
            if(typeof $==='undefined' || !$.fn.sortable){ console.error('jQuery UI sortable is required'); return; }

            $('.sortable').each(function(){
                const listEl=this;
                try{
                const $list=$(listEl);
                $list.sortable();
                $list.disableSelection();
                $list.on('sortstop',function(){
                    try{
                    const order=[];
                    $(this).find('li').each((idx,li)=>{ order[idx]=$(li).attr('data-id')??''; });
                    const url="{{ route(ViewsConstants::PRJ_STG.'.order') }}";
                    if(!url || url==='#'){ attachPointerGuard(listEl,'project_stage_order_unavailable'); return; }
                    $.ajax({
                        url,
                        type:'POST',
                        data:{ order, _token:$('meta[name="csrf-token"]').attr('content') },
                        success:()=>{},
                        error:()=>attachPointerGuard(listEl,'project_stage_order_unavailable')
                    });
                    }catch{ attachPointerGuard(listEl,'project_stage_order_unavailable'); }
                });
                }catch{ attachPointerGuard(listEl,'project_stage_order_unavailable'); }
            });
            }catch(e){ console.error('Initialization failed',e); }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    @can('create project stage')
        @php
            $projectStageCreateBaseName     = ViewsConstants::PRJ_STG.'.create';
            $projectStageCreateKebabName    = Str::kebab($projectStageCreateBaseName);
            $projectStageCreateResolvedName = Route::has($projectStageCreateBaseName)
                ? $projectStageCreateBaseName
                : (Route::has($projectStageCreateKebabName) ? $projectStageCreateKebabName : null);
            $projectStageCreateUrl          = $projectStageCreateResolvedName ? route($projectStageCreateResolvedName) : '#';
            $projectStageCreateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_STG, 'create_project_stage_route_unavailable') ?? 'Create project stage route is unavailable. Please contact technical support or your domain administrator.';
            $projectStageCreateLinkId       = 'project-stage-create-link';
            $projectStageCreateTitle        = __('Create Project Stage');
            $projectStageCreateLabel        = __('Create');
        @endphp
        <div class="float-end">
            <a href="{{ $projectStageCreateUrl }}"
            id="{{ $projectStageCreateLinkId }}"
            class="btn btn-xs btn-white btn-icon-only width-auto"
            data-ajax-popup="true"
            data-url="{{ $projectStageCreateUrl }}"
            data-guard-msg="{{ $projectStageCreateGuardMsg }}"
            data-title="{{ $projectStageCreateTitle }}">
                <i class="ti ti-plus"></i> {{ $projectStageCreateLabel }}
            </a>
        </div>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    try {
                        const l = document.getElementById('{{ $projectStageCreateLinkId }}');
                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                        l.setAttribute('data-listener-active', 'true');
                        l.addEventListener('click', (e) => {
                            try {
                                const href = l.getAttribute('href') || '#';
                                const url  = l.getAttribute('data-url') || href || '#';
                                if (href !== '#' || url !== '#') return;
                                e.preventDefault();
                                const msg = l.getAttribute('data-guard-msg') || 'Create project stage route is unavailable. Please contact technical support or your domain administrator.';
                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
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
    @endcan
@endsection
@section(YieldingConstants::ADM_CTT)
    @php
        $stagesSafe = (isset($projectstages) && (is_array($projectstages) || $projectstages instanceof Collection)) ? $projectstages : [];
    @endphp
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="alert alert-info note-constant {{ VC::TXS }}">
                <p class="{{ VC::MT4 }}">
                    <strong>{{ __('Note') }} : </strong>
                    <b>{{ __('The system will consider last stage as a completed | done task for getting the progress for the project.') }}</b>
                </p>
            </div>
        </div>
        <div class="col-md-12">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <ul class="{{ VC::LGRP }} sortable">
                        @forelse ($stagesSafe as $projectstage)
                            @php
                                $valid = isset($projectstage) && !empty($projectstage) && (is_array($projectstage) || is_object($projectstage));
                                $pid = $valid ? data_get($projectstage,'id') : null;
                                $nameText = $valid ? (data_get($projectstage,'name') ?? __('No stage name available')) : __('No stage available');
                                $createdText = $valid ? (data_get($projectstage,'created_at') ?? __('No created date available')) : __('No created date available');
                            @endphp
                            <li class="{{ VC::LGI }}" data-id="{{ $pid ?? '' }}">
                                <div class="{{ VC::RW }}">
                                    <div class="col-6 {{ VC::TXS }} text-dark">{{ $nameText }}</div>
                                    <div class="col-4 {{ VC::TXS }} text-dark">{{ $createdText }}</div>
                                    <div class="col-2">
                                        @can('edit project stage')
                                            <a href="#"
                                            data-url="{{ $pid ? URL::to(ViewsConstants::PRJ_STG.'/'.$pid.'/edit') : '#' }}"
                                            data-ajax-popup="true"
                                            data-title="{{ __('Edit Project Stages') }}"
                                            class="edit-icon">
                                                <i class="{{ VC::TI_PC }}"></i>
                                            </a>
                                        @endcan
                                        @can('delete project stage')
                                            @if($pid)
                                                <a href="#"
                                                class="delete-icon"
                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang ?? app()->getLocale(), 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang ?? app()->getLocale(), 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{$pid}}').submit();">
                                                    <i class="{{ VC::TI_TRS }}"></i>
                                                </a>
                                                {!! Form::open(['method' => 'DELETE', 'route' => [ViewsConstants::PRJ_STG.'.destroy', $pid],'id'=>'delete-form-'.$pid]) !!}
                                                {!! Form::close() !!}
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="{{ VC::LGI }}">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::C12 }} {{ VC::TXS }} text-dark">{{ __('No project stages available') }}</div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
