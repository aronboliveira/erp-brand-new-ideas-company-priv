@php
    use App\Config\Constants\ViewsConstants;
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $routeKey       = ViewsConstants::PRD_SV_CAT;
    $kebabRouteKey  = Str::kebab($routeKey);
    $saveRouteName  = Route::has($routeKey)
        ? $routeKey
        : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
    $saveRouteUrl   = $saveRouteName ? route($saveRouteName) : '#';
    $saveGuardMsg   = Utility::fetchLinkMessage(
        isset($lang) && $lang ? $lang : Utility::fetchUserLang(),
        ViewsConstants::PRD,
        'product_category_index_route_unavailable'
    ) ?? 'Product category save route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{!! Form::open([
    'url'          => $saveRouteUrl,
    'id'             => 'product-category-save-form',
    'data-url'       => $saveRouteUrl,
    'data-guard-msg' => $saveGuardMsg
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_G }} col-md-12">
                {{ Form::label('name', __('Category Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} col-md-12 {{ VC::DBL }}">
                {{ Form::label('type', __('Category Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $types, null, ['class' => VC::FM_CT_SL . ' cattype', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} col-md-12 account d-none">
                {{ Form::label('chart_account_id', __('Account'), ['class' => VC::FM_LB]) }}
                <select class="{{ VC::FM_CT_SL }}" name="chart_account" id="chart_account"></select>
            </div>

            <div class="{{ VC::FM_G }} col-md-12">
                {{ Form::label('color', __('Category Color'), ['class' => VC::FM_LB]) }}
                {{ Form::text('color', '', ['class' => VC::FM_CT . ' jscolor', 'required' => 'required']) }}
                <small>{{ __('For chart representation') }}</small>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
<script async>
  window.translations = {
    ar:{account_toggle_unavailable:'تعذّر تبديل عرض الحساب',get_account_unavailable:'تعذّر جلب قائمة الحسابات'},
    da:{account_toggle_unavailable:'Kan ikke skifte konto-visning',get_account_unavailable:'Kan ikke hente kontoliste'},
    de:{account_toggle_unavailable:'Kontosichtbarkeit konnte nicht umgeschaltet werden',get_account_unavailable:'Kontoliste konnte nicht geladen werden'},
    en:{account_toggle_unavailable:'Cannot toggle account visibility',get_account_unavailable:'Cannot fetch accounts list'},
    es:{account_toggle_unavailable:'No se puede alternar la visibilidad de cuenta',get_account_unavailable:'No se pudo obtener la lista de cuentas'},
    fr:{account_toggle_unavailable:'Impossible d’afficher/masquer la section compte',get_account_unavailable:'Impossible de récupérer la liste des comptes'},
    he:{account_toggle_unavailable:'לא ניתן להחליף תצוגת חשבון',get_account_unavailable:'לא ניתן להביא את רשימת החשבונות'},
    it:{account_toggle_unavailable:'Impossibile alternare visibilità conto',get_account_unavailable:'Impossibile recuperare elenco conti'},
    ja:{account_toggle_unavailable:'アカウント表示を切り替えできません',get_account_unavailable:'口座リストを取得できません'},
    nl:{account_toggle_unavailable:'Kan accountweergave niet wisselen',get_account_unavailable:'Kan accountlijst niet ophalen'},
    pl:{account_toggle_unavailable:'Nie można przełączyć widoczności konta',get_account_unavailable:'Nie można pobrać listy kont'},
    pt:{account_toggle_unavailable:'Não foi possível alternar a visibilidade da conta',get_account_unavailable:'Não foi possível obter a lista de contas'},
    'pt-br':{account_toggle_unavailable:'Não foi possível alternar a visibilidade da conta',get_account_unavailable:'Não foi possível obter a lista de contas'},
    ru:{account_toggle_unavailable:'Не удалось переключить видимость счёта',get_account_unavailable:'Не удалось получить список счетов'},
    tr:{account_toggle_unavailable:'Hesap görünürlüğü değiştirilemiyor',get_account_unavailable:'Hesap listesi alınamıyor'},
    zh:{account_toggle_unavailable:'无法切换账户可见性',get_account_unavailable:'无法获取账户列表'}
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
        msg=window.translations?.[lang]?.[key]||el?.getAttribute?.(DATA_GUARD_MSG)||window.translations?.en?.[key]||ERR_FB;
        if(msg!==ERR_FB){ el.setAttribute(DATA_GUARD_MSG,msg); el.setAttribute(DATA_CLIENT_LOCALIZED,'true'); }
      }
      return msg;
    };

    const showToast=(text)=>{
      const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
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
      if(typeof $==='undefined'){ console.error('jQuery is required'); return; }
      $(document).on('click','.cattype',function(){
        try{
          const type=$(this).val() ?? '';
          const $acc=$('.account');
          if(!$acc.length) return;
          if(type!=='product & service'){ $acc.removeClass('d-none').addClass('d-block'); }
          else { $acc.addClass('d-none').removeClass('d-block'); }
        }catch{ attachPointerGuard(this,'account_toggle_unavailable'); }
      });
      $(document).on('change','#type',function(){
        const el=this;
        try{
          const type=$(el).val() ?? '';
          const url='{{ route(ViewsConstants::PRD_SV_CAT.".getAccount") }}';
          if(!url || url==='#'){ attachPointerGuard(el,'get_account_unavailable'); return; }
          $.ajax({
            url,
            type:'POST',
            data:{ type, _token:'{{ csrf_token() }}' },
            success:(data)=>{
              try{
                const $sel=$('#chart_account');
                if(!$sel.length) return;
                $sel.empty();
                $.each(data,(key,value)=>{
                  if(!$sel.find(`option[value="${key}"]`).length){ $sel.append(`<option value="${key}">${value}</option>`); }
                });
              }catch{ attachPointerGuard(el,'get_account_unavailable'); }
            },
            error:()=>attachPointerGuard(el,'get_account_unavailable')
          });
        }catch{ attachPointerGuard(el,'get_account_unavailable'); }
      });

    }catch(e){ console.error('Initialization failed',e); }
  })();
</script>
<script defer>
    (() => {
        const form = document.getElementById('product-category-save-form');
        if (!form || form.getAttribute('data-listener-active') === 'true') return;
        form.setAttribute('data-listener-active', 'true');
        form.addEventListener('submit', e => {
            try {
                const url = form.getAttribute('data-url') || '#';
                const action = form.getAttribute('action') || '#';
                if (url !== '#' || action !== '#') return;
                e.preventDefault();
                const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    document.body.appendChild(container);
                }
                if (bs) {
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
            } catch (error) {}
        });
    })();
</script>