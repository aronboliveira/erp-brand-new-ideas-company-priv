@php
    use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants, YieldingConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, PermissionsConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Projects') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    @php
        $dashboardBaseName = 'dashboard';
        $dashboardKebabName = Str::kebab($dashboardBaseName);
        $dashboardResolvedName = Route::has($dashboardBaseName) ? $dashboardBaseName : (Route::has($dashboardKebabName) ? $dashboardKebabName : null);
        $dashboardUrl = $dashboardResolvedName ? route($dashboardResolvedName) : '#';
        $dashboardLinkId = 'dashboard-breadcrumb-link';
        $dashboardGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $dashboardUrl }}" id="{{ $dashboardLinkId }}" data-url="{{ $dashboardUrl }}" data-guard-msg="{{ $dashboardGuardMsg }}" {{ $dashboardUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Projects') }}</li>
    @push(StacksConstants::ADM_SCR_PG)
        <script>
            (() => {
                try {
                    const el = document.getElementById('{{ $dashboardLinkId }}');
                    if (!el) return;
                    if (el.hasAttribute('data-breadcrumb-listener') && el.getAttribute('data-breadcrumb-listener') === 'true') return;
                    el.setAttribute('data-breadcrumb-listener', 'true');
                    el.addEventListener('click', function(e) {
                        try {
                            const href = el.getAttribute('href') || '#';
                            const url = el.getAttribute('data-url') || href || '#';
                            if (href !== '#' || url !== '#') return;
                            e.preventDefault();
                            const msg = el.getAttribute('data-guard-msg') || 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                container.className = 'position-fixed top-0 end-0 p-3';
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
                                const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (err) {} });
                                inst.show();
                            } else {
                                alert(msg);
                            }
                            el.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    }, { passive: false });
                } catch (error) {}
            })();
        </script>
    @endpush
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $viewVal = isset($view) && !empty($view) ? $view : null;
        $projectsIndexBase = VW::PRJ . '.index';
        $projectsIndexKebab = Str::kebab($projectsIndexBase);
        $projectsIndexResolved = Route::has($projectsIndexBase) ? $projectsIndexBase : (Route::has($projectsIndexKebab) ? $projectsIndexKebab : null);
        $projectsIndexUrl = $projectsIndexResolved ? route($projectsIndexResolved) : '#';
        $projectsIndexLinkId = 'projects-index-toggle-link';
        $projectsIndexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'project_index_route_unavailable') ?? 'Index project route is unavailable. Please contact technical support or your domain administrator.';
        $listRouteNameA = VW::PRJ . '.list';
        $listRouteNameB = 'projects.list';
        $listCandidates = [$listRouteNameA, Str::kebab($listRouteNameA), $listRouteNameB, Str::kebab($listRouteNameB)];
        $projectsListResolved = null;
        foreach ($listCandidates as $c) { if (Route::has($c)) { $projectsListResolved = $c; break; } }
        $listParam = 'list';
        $projectsListParams = [$listParam];
        $projectsListUrl = $projectsListResolved ? route($projectsListResolved, $projectsListParams) : '#';
        $projectsListLinkId = 'projects-list-toggle-link';
        $projectsListGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'list_project_route_unavailable') ?? 'List project route is unavailable. Please contact technical support or your domain administrator.';
        $projectsCreateBase = VW::PRJ . '.create';
        $projectsCreateKebab = Str::kebab($projectsCreateBase);
        $projectsCreateResolved = Route::has($projectsCreateBase) ? $projectsCreateBase : (Route::has($projectsCreateKebab) ? $projectsCreateKebab : null);
        $projectsCreateUrl = $projectsCreateResolved ? route($projectsCreateResolved) : '#';
        $projectsCreateLinkId = 'projects-create-link';
        $projectsCreateGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'create_project_route_unavailable') ?? 'Create project route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        @if($viewVal === 'grid')
            <a href="{{ $projectsListUrl }}" id="{{ $projectsListLinkId }}" data-url="{{ $projectsListUrl }}" data-bs-toggle="tooltip" title="{{ __('List View') }}" class="{{ VC::BT_SM_PM }}" data-guard-msg="{{ $projectsListGuardMsg }}">
                <i class="{{ VC::TI_LT }}"></i>
            </a>
        @else
            <a href="{{ $projectsIndexUrl }}" id="{{ $projectsIndexLinkId }}" data-url="{{ $projectsIndexUrl }}" data-bs-toggle="tooltip" title="{{ __('Grid View') }}" class="{{ VC::BT_SM_PM }}" data-guard-msg="{{ $projectsIndexGuardMsg }}">
                <i class="ti ti-layout-grid"></i>
            </a>
        @endif
        <a href="#" class="{{ VC::BT_SM_PM }} action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-filter"></i>
        </a>
        <div class="dropdown-menu dropdown-steady" id="project_sort">
            <a class="dropdown-item active" href="#" data-val="created_at-desc">
                <i class="ti ti-sort-descending"></i>{{ __('Newest') }}
            </a>
            <a class="dropdown-item" href="#" data-val="created_at-asc">
                <i class="ti ti-sort-ascending"></i>{{ __('Oldest') }}
            </a>
            <a class="dropdown-item" href="#" data-val="project_name-desc">
                <i class="ti ti-sort-descending-letters"></i>{{ __('From Z-A') }}
            </a>
            <a class="dropdown-item" href="#" data-val="project_name-asc">
                <i class="ti ti-sort-ascending-letters"></i>{{ __('From A-Z') }}
            </a>
        </div>
        <a href="#" class="{{ VC::BT_SM_PM }} action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="btn-inner--icon">{{ __('Status') }}</span>
        </a>
        <div class="dropdown-menu project-filter-actions dropdown-steady" id="project_status">
            <a class="dropdown-item filter-action filter-show-all pl-4 active" href="#">{{ __('Show All') }}</a>
            @foreach(\App\Models\Project::$project_status as $key => $val)
                <a class="dropdown-item filter-action pl-4" href="#" data-val="{{ $key }}">{{ __($val) }}</a>
            @endforeach
        </div>
        @can(PermissionsConstants::MNG_PRJ)
            <a href="{{ $projectsCreateUrl }}" id="{{ $projectsCreateLinkId }}" data-url="{{ $projectsCreateUrl }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create New Project') }}" data-title="{{ __('Create Project') }}" class="{{ VC::BT_SM_PM }}" data-guard-msg="{{ $projectsCreateGuardMsg }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script>
            (() => {
                try {
                    const wireGuard = (id, flag) => {
                        try {
                            const el = document.getElementById(id);
                            if (!el) return;
                            if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') return;
                            el.setAttribute(flag, 'true');
                            el.addEventListener('click', function(e) {
                                try {
                                    const href = el.getAttribute('href') || '#';
                                    const url = el.getAttribute('data-url') || href || '#';
                                    if (href !== '#' || url !== '#') return;
                                    e.preventDefault();
                                    const msg = el.getAttribute('data-guard-msg') || 'Requested route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        container.className = 'position-fixed top-0 end-0 p-3';
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
                                        const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                        toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (err) {} });
                                        inst.show();
                                    } else {
                                        alert(msg);
                                    }
                                    el.setAttribute('data-failed-route', 'true');
                                } catch (err) {}
                            }, { passive: false });
                        } catch (err) {}
                    };
                    wireGuard('{{ $projectsListLinkId }}', 'data-list-listener');
                    wireGuard('{{ $projectsIndexLinkId }}', 'data-index-listener');
                    wireGuard('{{ $projectsCreateLinkId }}', 'data-create-listener');
                } catch (error) {}
            })();
        </script>
    @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row min-750" id="project_view"></div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:{project_filter_unavailable:'تعذّر تطبيق عوامل التصفية',project_sort_unavailable:'تعذّر تغيير الفرز',project_search_unavailable:'تعذّر تنفيذ البحث',project_invite_unavailable:'تعذّر دعوة المستخدم'},
            da:{project_filter_unavailable:'Kunne ikke anvende filtre',project_sort_unavailable:'Kunne ikke ændre sortering',project_search_unavailable:'Kunne ikke udføre søgning',project_invite_unavailable:'Kunne ikke invitere bruger'},
            de:{project_filter_unavailable:'Filter konnten nicht angewendet werden',project_sort_unavailable:'Sortierung konnte nicht geändert werden',project_search_unavailable:'Suche konnte nicht ausgeführt werden',project_invite_unavailable:'Benutzer konnte nicht eingeladen werden'},
            en:{project_filter_unavailable:'Cannot apply filters',project_sort_unavailable:'Cannot change sorting',project_search_unavailable:'Cannot perform search',project_invite_unavailable:'Cannot invite user'},
            es:{project_filter_unavailable:'No se pueden aplicar filtros',project_sort_unavailable:'No se puede cambiar el orden',project_search_unavailable:'No se puede realizar la búsqueda',project_invite_unavailable:'No se puede invitar al usuario'},
            fr:{project_filter_unavailable:'Impossible d’appliquer les filtres',project_sort_unavailable:'Impossible de modifier le tri',project_search_unavailable:'Impossible d’effectuer la recherche',project_invite_unavailable:'Impossible d’inviter l’utilisateur'},
            he:{project_filter_unavailable:'לא ניתן להחיל מסננים',project_sort_unavailable:'לא ניתן לשנות מיון',project_search_unavailable:'לא ניתן לבצע חיפוש',project_invite_unavailable:'לא ניתן להזמין משתמש'},
            it:{project_filter_unavailable:'Impossibile applicare i filtri',project_sort_unavailable:'Impossibile cambiare l’ordinamento',project_search_unavailable:'Impossibile eseguire la ricerca',project_invite_unavailable:'Impossibile invitare l’utente'},
            ja:{project_filter_unavailable:'フィルターを適用できません',project_sort_unavailable:'並び替えを変更できません',project_search_unavailable:'検索を実行できません',project_invite_unavailable:'ユーザーを招待できません'},
            nl:{project_filter_unavailable:'Kan filters niet toepassen',project_sort_unavailable:'Kan sortering niet wijzigen',project_search_unavailable:'Kan zoeken niet uitvoeren',project_invite_unavailable:'Kan gebruiker niet uitnodigen'},
            pl:{project_filter_unavailable:'Nie można zastosować filtrów',project_sort_unavailable:'Nie można zmienić sortowania',project_search_unavailable:'Nie można wykonać wyszukiwania',project_invite_unavailable:'Nie można zaprosić użytkownika'},
            pt:{project_filter_unavailable:'Não foi possível aplicar filtros',project_sort_unavailable:'Não foi possível alterar a ordenação',project_search_unavailable:'Não foi possível realizar a pesquisa',project_invite_unavailable:'Não foi possível convidar o usuário'},
            'pt-br':{project_filter_unavailable:'Não foi possível aplicar filtros',project_sort_unavailable:'Não foi possível alterar a ordenação',project_search_unavailable:'Não foi possível realizar a pesquisa',project_invite_unavailable:'Não foi possível convidar o usuário'},
            ru:{project_filter_unavailable:'Не удалось применить фильтры',project_sort_unavailable:'Не удалось изменить сортировку',project_search_unavailable:'Не удалось выполнить поиск',project_invite_unavailable:'Не удалось пригласить пользователя'},
            tr:{project_filter_unavailable:'Filtreler uygulanamadı',project_sort_unavailable:'Sıralama değiştirilemedi',project_search_unavailable:'Arama gerçekleştirilemedi',project_invite_unavailable:'Kullanıcı davet edilemedi'},
            zh:{project_filter_unavailable:'无法应用筛选',project_sort_unavailable:'无法更改排序',project_search_unavailable:'无法执行搜索',project_invite_unavailable:'无法邀请用户'}
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
        (()=>{
            const ERR_FB='# ERROR';
            const DATA_CLIENT_LOCALIZED='data-client-localized';
            const DATA_GUARD_MSG='data-guard-msg';
            const DATA_LISTENER_ADDED='data-listener-added';
            let currentRequest=null;

            const getMsg=(el,key)=>{
            let msg=ERR_FB;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
                msg=el.getAttribute(DATA_GUARD_MSG)||ERR_FB;
            }else{
                let lang=(sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang=lang==='pt-br'?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[key]||el?.getAttribute(DATA_GUARD_MSG)||window.translations?.en?.[key]||ERR_FB;
                if(msg!==ERR_FB){ el?.setAttribute(DATA_GUARD_MSG,msg); el?.setAttribute(DATA_CLIENT_LOCALIZED,'true'); }
            }
            return msg;
            };

            const showError=(text)=>{
            const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBs){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(text); }
            };

            const guardOnce=(el,key,ev='pointerup')=>{
            if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showError(getMsg(el,key));
            el.addEventListener(ev,handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const ajaxFilterProjectView=(project_sort,keyword='',status=[])=>{
            const mainEle=$('#project_view');
            const view='{{$view}}';
            const data={ view, sort:project_sort, keyword, status };
            try{
                const endpoint='{{ route('filter.project.view') }}';
                currentRequest=$.ajax({
                url:endpoint,
                data,
                beforeSend:()=>{ if(currentRequest!==null){ try{ currentRequest.abort(); }catch{} } },
                success:(res)=>{ try{ mainEle.html(res?.html??res); $('[id^=fire-modal]').remove(); if(typeof loadConfirm==='function') loadConfirm(); }catch{ guardOnce(mainEle.get(0),'project_filter_unavailable'); } },
                error:()=>guardOnce(mainEle.get(0),'project_filter_unavailable')
                });
            }catch{ guardOnce(mainEle.get(0),'project_filter_unavailable'); }
            };

            try{
            if(typeof $==="undefined"){ 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");     
                return; 
            }

            $(()=>{
                let sort='created_at-desc';
                let status=[];

                ajaxFilterProjectView('created_at-desc');

                $('.project-filter-actions').on('click','.filter-action',function(){
                try{
                    if($(this).hasClass('filter-show-all')){ $('.filter-action').removeClass('active'); $(this).addClass('active'); }
                    else{ $('.filter-show-all').removeClass('active'); $(this).toggleClass('active').blur(); }
                    const filterArray=[]; $('div.project-filter-actions').find('.active').each(function(){ filterArray.push($(this).attr('data-val')); });
                    status=filterArray;
                    ajaxFilterProjectView(sort,$('#project_keyword').val()??'',status);
                }catch{ guardOnce(this,'project_filter_unavailable','click'); }
                });

                $('#project_sort').on('click','a',function(){
                try{
                    sort=$(this).attr('data-val')||sort;
                    ajaxFilterProjectView(sort,$('#project_keyword').val()??'',status);
                    $('#project_sort a').removeClass('active'); $(this).addClass('active');
                }catch{ guardOnce(this,'project_sort_unavailable','click'); }
                });

                $(document).on('keyup','#project_keyword',function(){
                try{ ajaxFilterProjectView(sort,$(this).val()??'',status); }catch{ /* guard on pointerup to avoid noisy alerts while typing */ }
                });
                const searchEl=document.getElementById('project_keyword');
                if(searchEl) guardOnce(searchEl,'project_search_unavailable','pointerup');

                $(document).on('click','.invite_usr',function(){
                const el=this;
                try{
                    const project_id=$('#project_id').val();
                    const user_id=$(el).attr('data-id');
                    const url='{{ route(VW::PRJ . ".invite.user.member") }}';
                    $.ajax({
                    url,
                    method:'POST',
                    dataType:'json',
                    data:{ project_id, user_id, _token:'{{ csrf_token() }}' },
                    success:(data)=>{
                        if(String(data?.code)==='200'){ show_toastr(data.status,data.success,'success'); setTimeout(()=>location.reload(),5000); }
                        else if(String(data?.code)==='404'){ show_toastr(data.status,data.errors,'error'); }
                    },
                    error:()=>guardOnce(el,'project_invite_unavailable','click')
                    });
                }catch{ guardOnce(el,'project_invite_unavailable','click'); }
                });
            });

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
