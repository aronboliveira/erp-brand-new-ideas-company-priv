@php
$lang ??= 'en';
	$routeKey ??= '';
	$kebabRouteKey ??= '';
	$updateRouteName ??= null;
	$updateRouteArr ??= ['#'];
	$updateRouteUrl ??= '#';
	$updateGuardMsg ??= '';
	$formId ??= 'update-category-form';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$routeKey = ViewsConstants::PRD_SV_CAT . '.update';
		$kebabRouteKey = Str::kebab($routeKey);
		$categoryId = data_get($category ?? null, 'id');
		$updateRouteName = Route::has($routeKey) ? $routeKey : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
		$updateRouteArr = ($updateRouteName && $categoryId) ? [$updateRouteName, $categoryId] : ['#'];
		$updateRouteUrl = ($updateRouteName && $categoryId) ? (route($updateRouteName, $categoryId) ?? '#') : '#';
		$updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_SV_CAT, 'product_category_update_route_unavailable') ?? 'Product category update route is unavailable. Please contact technical support or your domain administrator.';
		$formId = 'update-category-form-' . ($categoryId ?: 'unknown');
	} catch (\Error $e) {
		Log::error('Error in product_service_categories/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in product_service_categories/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in product_service_categories/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{!! Form::model($category, [
    'route'          => $updateRouteArr,
    'method'         => 'PUT',
    'id'             => $formId,
    'data-url'       => $updateRouteUrl,
    'data-guard-msg' => $updateGuardMsg
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_G }} col-md-12">
                {{ Form::label('name', __('Category Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT . ' font-style', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} col-md-12 {{ VC::DBL }}">
                {{ Form::label('type', __('Category Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $types, null, ['class' => VC::FM_CT_SL . ' cattype', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} col-md-12 account {{ $category->type == 'product & service' ? 'd-none' : '' }}">
                {{ Form::label('chart_account_id', __('Account'), ['class' => VC::FM_LB]) }}
                <select class="{{ VC::FM_CT_SL }}" name="chart_account" id="chart_account"></select>
            </div>

            <div class="{{ VC::FM_G }} col-md-12">
                {{ Form::label('color', __('Category Color'), ['class' => VC::FM_LB]) }}
                {{ Form::text('color', null, ['class' => VC::FM_CT . ' jscolor', 'required' => 'required']) }}
                <p class="small">{{ __('For chart representation') }}</p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script async src="{{ asset('assets/js/routes/product/services/categories/lang/edit.js') }}"></script>
    <script defer>
      (() => {
        const dataListenerAdded = 'data-listener-added';
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';

        const getLocalizedMessage = (el, msgKey) => {
          let msg = errFb;
          if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') msg = el.getAttribute(dataGuardMsg) || errFb;
          else {
            let lang = (window.sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en').toLowerCase().replace(/_/g,'-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = window.translations?.[lang]?.[msgKey] || el.getAttribute(dataGuardMsg) || window.translations?.['en']?.[msgKey] || errFb;
            if (msg !== errFb) { el.setAttribute(dataGuardMsg, msg); el.setAttribute(dataClientLocalized, 'true'); }
          }
          return msg;
        };
        const handleErrorDisplay = (el, msgKey) => {
          const message = el ? getLocalizedMessage(el, msgKey) : errFb;
          const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
          if (hasBootstrap) {
            if (!document.querySelector('#error-toast')) {
              const toast = document.createElement('div');
              toast.id = 'error-toast';
              toast.className = 'toast align-items-center text-bg-danger border-0';
              toast.setAttribute('role','alert');
              toast.setAttribute('aria-live','assertive');
              toast.setAttribute('aria-atomic','true');
              toast.innerHTML = `<div class="{{ VC::DFL }}"><div class="toast-body">${message}</div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
              document.body.appendChild(toast);
            }
            new bootstrap.Toast(document.querySelector('#error-toast')).show();
          } else { alert(message); }
        };
        try {
          if (typeof $ === 'undefined') {
              if (
                  window.location.hostname === "localhost" ||
                  window.location.hostname === "127.0.0.1"
              ) console.error("jQuery unavailable");
            return;
          }
          $(document).on('click','.cattype',function() {
            try {
              const type = $(this).val() ?? '';
              const $acc = $('.account');
              if (!$acc.length) return;
              if (type !== 'product & service') $acc.removeClass('d-none').addClass('d-block');
              else $acc.addClass('d-none').removeClass('d-block');
            } catch {
              const el = this;
              if (!el.hasAttribute(dataListenerAdded)) {
                el.addEventListener('click', ()=>handleErrorDisplay(el,'account_toggle_unavailable'));
                el.setAttribute(dataListenerAdded,'true');
                const obs = new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('click',()=>handleErrorDisplay(el,'account_toggle_unavailable')); o.disconnect(); }});
                obs.observe(document.body,{childList:true,subtree:true});
              }
            }
          });
          const $type = $('#type');
          const attachPointerGuard = (el, key) => {
            if (!el || el.getAttribute(dataListenerAdded)==='true') return;
            el.addEventListener('pointerup', ()=>handleErrorDisplay(el,key), { once:true });
            el.setAttribute(dataListenerAdded,'true');
            const obs = new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('pointerup',()=>handleErrorDisplay(el,key)); o.disconnect(); }});
            obs.observe(document.body,{childList:true,subtree:true});
          };

          $(document).on('change','#type',function() {
            const el = this;
            try {
              const type = $(el).val() ?? '';
              const url = '{{ route(ViewsConstants::PRD_SV_CAT.".get_account") }}' || '';
              if (!url || url === '#') { attachPointerGuard(el,'get_account_unavailable'); return; }
              $.ajax({
                url,
                type:'POST',
                data:{ type, _token: '{{ csrf_token() }}' },
                success:data=>{
                  try {
                    const $sel = $('#chart_account');
                    if (!$sel.length) return;
                    $sel.empty();
                    if (!$sel.find('option[value=""]').length) $sel.append('<option value="">{{__(" --- Select Account ---")}}</option>');
                    $.each(data,(key,value)=>{
                      const selected = String(key) === String('{{ $category->chart_account_id }}') ? ' selected' : '';
                      $sel.append($sel.find(`option[value="${key}"]`).length ? '' : `<option value="${key}"${selected}>${value}</option>`);
                    });
                  } catch { attachPointerGuard(el,'get_account_unavailable'); }
                },
                error:()=>attachPointerGuard(el,'get_account_unavailable')
              });
            } catch { attachPointerGuard(el,'get_account_unavailable'); }
          });
          $(function(){ try { $type.trigger('change'); } catch { attachPointerGuard($type.get(0),'get_account_unavailable'); } });
        } catch(e) {
            if (
                window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1"
            ) {
                console.error("Initialization failed", e);
            }
         }
      })();
    </script>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', (e) => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                    form.setAttribute('data-failed-route', 'true');
                } catch (error) {}
            });
        })();
    </script>
{{ Form::close() }}
