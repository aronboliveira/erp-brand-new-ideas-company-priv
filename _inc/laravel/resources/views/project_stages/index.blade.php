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
    <script async src="{{ asset('assets/js/routes/projects/stages/lang/index.js') }}"></script>
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
            if(typeof $==='undefined' || !$.fn.sortable){ 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery Sortable unavailable");
                return;
             }

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
            <script defer src="{{ asset('assets/js/routes/projects/stages/create.js') }}"></script>
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
                                            @php
                                                $projectStageEditBaseName     = ViewsConstants::PRJ_STG.'.edit';
                                                $projectStageEditKebabName    = Str::kebab($projectStageEditBaseName);
                                                $projectStageEditResolvedName = Route::has($projectStageEditBaseName)
                                                    ? $projectStageEditBaseName
                                                    : (Route::has($projectStageEditKebabName) ? $projectStageEditKebabName : null);
                                                $projectIdValue               = isset($pid) && !empty($pid) ? $pid : null;
                                                $projectStageEditUrl          = ($projectStageEditResolvedName && $projectIdValue) ? route($projectStageEditResolvedName, $projectIdValue) : '#';
                                                $projectStageEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_STG, 'edit_project_stage_route_unavailable') ?? 'Edit project stage route is unavailable. Please contact technical support or your domain administrator.';
                                                $projectStageEditLinkId       = 'project-stage-edit-link-'.($projectIdValue ?? 'x');
                                                $projectStageEditTitle        = __('Edit Project Stages');
                                            @endphp
                                            <a href="{{ $projectStageEditUrl }}"
                                            id="{{ $projectStageEditLinkId }}"
                                            data-url="{{ $projectStageEditUrl }}"
                                            data-ajax-popup="true"
                                            data-guard-msg="{{ $projectStageEditGuardMsg }}"
                                            data-title="{{ $projectStageEditTitle }}"
                                            class="edit-icon">
                                                <i class="{{ VC::TI_PC }}"></i>
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const l = document.getElementById('{{ $projectStageEditLinkId }}');
                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                            l.setAttribute('data-listener-active', 'true');
                                                            l.addEventListener('click', e => {
                                                                try {
                                                                    const href = l.getAttribute('href') || '#';
                                                                    const url  = l.getAttribute('data-url') || href || '#';
                                                                    if (href !== '#' || url !== '#') return;
                                                                    e.preventDefault();
                                                                    const msg = l.getAttribute('data-guard-msg') || 'Edit project stage route is unavailable. Please contact technical support or your domain administrator.';
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
                                        @endcan
                                        @can('delete project stage')
                                            @if($pid)
                                                @php
                                                    $projectStageDestroyBaseName     = ViewsConstants::PRJ_STG.'.destroy';
                                                    $projectStageDestroyKebabName    = Str::kebab($projectStageDestroyBaseName);
                                                    $projectStageDestroyResolvedName = Route::has($projectStageDestroyBaseName)
                                                        ? $projectStageDestroyBaseName
                                                        : (Route::has($projectStageDestroyKebabName) ? $projectStageDestroyKebabName : null);
                                                    $projectIdValue                  = isset($pid) && !empty($pid) ? $pid : null;
                                                    $projectStageDestroyRouteArray   = ($projectStageDestroyResolvedName && $projectIdValue) ? [$projectStageDestroyResolvedName, $projectIdValue] : ['#'];
                                                    $projectStageDestroyUrl          = ($projectStageDestroyResolvedName && $projectIdValue) ? route($projectStageDestroyResolvedName, $projectIdValue) : '#';
                                                    $projectStageDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_STG, 'delete_project_stage_route_unavailable') ?? 'Delete project stage route is unavailable. Please contact technical support or your domain administrator.';
                                                    $projectStageDeleteFormId        = 'delete-form-'.($projectIdValue ?? 'x');
                                                    $projectStageDeleteLinkId        = 'project-stage-delete-link-'.($projectIdValue ?? 'x');
                                                    $confirmTitle                    = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                    $confirmBody                     = __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                    $confirmCombined                 = $confirmTitle.'|'.$confirmBody;
                                                @endphp
                                                <a href="#"
                                                id="{{ $projectStageDeleteLinkId }}"
                                                class="delete-icon"
                                                data-confirm="{{ $confirmCombined }}"
                                                data-confirm-yes="document.getElementById('{{ $projectStageDeleteFormId }}').submit();"
                                                data-form-id="{{ $projectStageDeleteFormId }}"
                                                data-guard-msg="{{ $projectStageDestroyGuardMsg }}">
                                                    <i class="{{ VC::TI_TRS }}"></i>
                                                </a>
                                                {!! Form::open([
                                                    'method'         => 'DELETE',
                                                    'route'          => $projectStageDestroyRouteArray,
                                                    'id'             => $projectStageDeleteFormId,
                                                    'data-url'       => $projectStageDestroyUrl,
                                                    'data-guard-msg' => $projectStageDestroyGuardMsg
                                                ]) !!}
                                                {!! Form::close() !!}
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const a = document.getElementById('{{ $projectStageDeleteLinkId }}');
                                                                const f = document.getElementById('{{ $projectStageDeleteFormId }}');
                                                                if (!a || !f || a.getAttribute('data-listener-active') === 'true') return;
                                                                a.setAttribute('data-listener-active', 'true');
                                                                a.addEventListener('click', e => {
                                                                    try {
                                                                        e.preventDefault();
                                                                        const url = f.getAttribute('data-url') || '#';
                                                                        const action = f.getAttribute('action') || '#';
                                                                        if (url === '#' && action === '#') {
                                                                            const msg = a.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Delete project stage route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                            a.setAttribute('data-failed-route', 'true');
                                                                            f.setAttribute('data-failed-route', 'true');
                                                                            return;
                                                                        }
                                                                        const combined = a.getAttribute('data-confirm') || '';
                                                                        const parts = combined.split('|');
                                                                        const finalMsg = parts.length > 1 ? parts[0] + '\n\n' + parts.slice(1).join(' ') : combined;
                                                                        if (window.confirm(finalMsg)) {
                                                                            const yes = a.getAttribute('data-confirm-yes') || '';
                                                                            if (yes) {
                                                                                try { eval(yes); } catch (_) { f.submit(); }
                                                                            } else {
                                                                                f.submit();
                                                                            }
                                                                        }
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
