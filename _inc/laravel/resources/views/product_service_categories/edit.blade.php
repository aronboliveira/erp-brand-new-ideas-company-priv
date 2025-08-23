@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Config\Constants\StacksConstants;
    use App\Models\Utility;
    $lang = Utility::fetchUserLang();
    $routeKey        = ViewsConstants::PRD_SV_CAT . '.update';
    $kebabRouteKey   = Str::kebab($routeKey);
    $updateRouteName = Route::has($routeKey) ? $routeKey : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
    $updateRouteArr  = $updateRouteName ? [$updateRouteName, $category->id] : ['#'];
    $updateRouteUrl  = $updateRouteName ? route($updateRouteName, $category->id) : '#';
    $updateGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_SV_CAT, 'product_category_update_route_unavailable') ?? 'Product category update route is unavailable. Please contact technical support or your domain administrator.';
    $formId          = 'update-category-form-' . $category->id;
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
{{ Form::close() }}
    <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
    ar:{account_toggle_unavailable:'تعذر تبديل عرض الحساب',get_account_unavailable:'تعذر جلب قائمة الحسابات'},
    da:{account_toggle_unavailable:'Kunne ikke skifte konto-visibility',get_account_unavailable:'Kan ikke hente kontoliste'},
    de:{account_toggle_unavailable:'Kontosichtbarkeit konnte nicht umgeschaltet werden',get_account_unavailable:'Kontenliste konnte nicht geladen werden'},
    en:{account_toggle_unavailable:'Cannot toggle account visibility',get_account_unavailable:'Cannot fetch accounts list'},
    es:{account_toggle_unavailable:'No se puede alternar la visibilidad de cuenta',get_account_unavailable:'No se puede obtener la lista de cuentas'},
    fr:{account_toggle_unavailable:'Impossible d’afficher/masquer la section compte',get_account_unavailable:'Impossible de récupérer la liste des comptes'},
    he:{account_toggle_unavailable:'לא ניתן להחליף תצוגת חשבון',get_account_unavailable:'לא ניתן להביא את רשימת החשבונות'},
    it:{account_toggle_unavailable:'Impossibile alternare visibilità conto',get_account_unavailable:'Impossibile recuperare elenco conti'},
    ja:{account_toggle_unavailable:'アカウント表示を切り替えできません',get_account_unavailable:'口座リストを取得できません'},
    nl:{account_toggle_unavailable:'Kan zichtbaarheid van account niet wisselen',get_account_unavailable:'Kan accountlijst niet ophalen'},
    pl:{account_toggle_unavailable:'Nie można przełączyć widoczności konta',get_account_unavailable:'Nie można pobrać listy kont'},
    pt:{account_toggle_unavailable:'Não foi possível alternar a visibilidade da conta',get_account_unavailable:'Não foi possível obter a lista de contas'},
    'pt-br':{account_toggle_unavailable:'Não foi possível alternar a visibilidade da conta',get_account_unavailable:'Não foi possível obter a lista de contas'},
    ru:{account_toggle_unavailable:'Не удалось переключить видимость счета',get_account_unavailable:'Не удалось получить список счетов'},
    tr:{account_toggle_unavailable:'Hesap görünürlüğü değiştirilemiyor',get_account_unavailable:'Hesap listesi alınamıyor'},
    zh:{account_toggle_unavailable:'无法切换账户可见性',get_account_unavailable:'无法获取账户列表'}
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
          toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
          document.body.appendChild(toast);
        }
        new bootstrap.Toast(document.querySelector('#error-toast')).show();
      } else { alert(message); }
    };
    try {
      if (typeof $ === 'undefined') { console.error('jQuery is required'); return; }
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
          const url = '{{ route(ViewsConstants::PRD_SV_CAT.".getAccount") }}' || '';
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
    } catch(e) { console.error('Initialization failed', e); }
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
                form.setAttribute('data-failed-route', 'true');
            } catch (error) {}
        });
    })();
</script>