@php
$routeKey ??= '';
	$kebabRouteKey ??= '';
	$saveRouteName ??= null;
	$saveRouteUrl ??= '#';
	$saveGuardMsg ??= '';
	try {
		$routeKey = ViewsConstants::PRD_SV_CAT;
		$kebabRouteKey = Str::kebab($routeKey);
		$saveRouteName = Route::has($routeKey)
			? $routeKey
			: (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
		$saveRouteUrl = $saveRouteName ? (route($saveRouteName) ?? '#') : '#';
		$saveGuardMsg = Utility::fetchLinkMessage(
			isset($lang) && $lang ? $lang : Utility::fetchUserLang(),
			ViewsConstants::PRD_SV_CAT,
			'product_category_create_route_unavailable'
		) ?? 'Product service category create route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in product_service_categories/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in product_service_categories/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in product_service_categories/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
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
        const RG = window.RouteGuard || {};
        const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
        const showToast = RG.showToast || (m => alert(m));

        const attachPointerGuard=(el,key)=>{
          if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
          const handler=()=>showToast(getMsg(key, el));
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
              const url='{{ route(ViewsConstants::PRD_SV_CAT.".get_account") }}';
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
