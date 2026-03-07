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
        ViewsConstants::PRD_SV_CAT,
        'product_category_create_route_unavailable'
    ) ?? 'Product service category create route is unavailable. Please contact technical support or your domain administrator.';
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
    <script async src="{{ asset('assets/js/routes/product/services/categories/lang/create.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/product/services/categories/store.js') }}"></script>
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
              n.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
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
          if(typeof $==='undefined'){ 
            if (
                window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1"
            ) console.error("jQuery unavailable");            
            return;
          }
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
{{ Form::close() }}