@php
    use App\Config\Constants\{
        ActivitiesConstants,
        PlansConstants,
        ProjectsConstants,
        SupportsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp

{!! Form::open([
    'route'    => ViewsConstants::SPT,
    'method'   => 'post',
    'id'       => 'create_support',
    'enctype'  => 'multipart/form-data',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ route('generate', ['support']) }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label(SupportsConstants::COL_SBJ, __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text(SupportsConstants::COL_SBJ, null, ['class' => VC::FM_CT, 'required' => true]) }}
            </div>

            @if($user?->type !== 'client')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label(SupportsConstants::COL_USR, __('Support for User'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(SupportsConstants::COL_USR, $users, null, ['class' => VC::FM_CT_SL]) }}
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_PRT, __('Priority'), ['class' => VC::FM_LB]) }}
                {{ Form::select(ProjectsConstants::COL_PRT, $priority, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ActivitiesConstants::COL_TSK_STT, __('Status'), ['class' => VC::FM_LB]) }}
                {{ Form::select(ActivitiesConstants::COL_TSK_STT, $status, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_E_DT, __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date(ProjectsConstants::COL_E_DT, null, ['class' => VC::FM_CT, 'required' => true]) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label(ActivitiesConstants::COL_DESC, __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => VC::FM_CT, 'rows' => 3]) }}
            </div>
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label(SupportsConstants::COL_ATC, __('Attachment'), ['class' => VC::FM_LB]) }}
            <label for="attachment" class="{{ VC::FM_LB }}">
                <input type="file" class="{{ VC::FM_CT }}" name="attachment" id="attachment" data-filename="attachment_create">
            </label>
            <img id="image" class="mt-2" style="width:25%;" />
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}

    <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
    ar: { attachment_preview_unavailable: "تعذّر معاينة المرفق" },
    da: { attachment_preview_unavailable: "Kunne ikke forhåndsvise vedhæftning" },
    de: { attachment_preview_unavailable: "Anhang kann nicht in der Vorschau angezeigt werden" },
    en: { attachment_preview_unavailable: "Cannot preview attachment" },
    es: { attachment_preview_unavailable: "No se puede previsualizar el adjunto" },
    fr: { attachment_preview_unavailable: "Impossible d’afficher l’aperçu de la pièce jointe" },
    he: { attachment_preview_unavailable: "לא ניתן להציג תצוגה מקדימה לקובץ המצורף" },
    it: { attachment_preview_unavailable: "Impossibile visualizzare l’anteprima dell’allegato" },
    ja: { attachment_preview_unavailable: "添付ファイルをプレビューできません" },
    nl: { attachment_preview_unavailable: "Bijlage kan niet worden weergegeven" },
    pl: { attachment_preview_unavailable: "Nie można wyświetlić podglądu załącznika" },
    pt: { attachment_preview_unavailable: "Não é possível pré-visualizar o anexo" },
    "pt-br": { attachment_preview_unavailable: "Não é possível pré-visualizar o anexo" },
    ru: { attachment_preview_unavailable: "Не удаётся показать предварительный просмотр вложения" },
    tr: { attachment_preview_unavailable: "Ek önizlenemiyor" },
    zh: { attachment_preview_unavailable: "无法预览附件" }
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
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    const DATA_LISTENER_ADDED = "data-listener-added";

    const getLocalizedMsg = (el, msgKey)=>{
      let msg = errFb;
      if (el?.getAttribute("data-sv-localized")==="true" || el?.getAttribute(dataClientLocalized)==="true") {
        msg = el.getAttribute(dataGuardMsg) || errFb;
      } else {
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
          .toLowerCase().replace(/_/g,"-");
        lang = lang==="pt-br" ? lang : lang.slice(0,2);
        msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
        if (msg!==errFb) { el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true"); }
      }
      return msg;
    };

    const showFeedback = (el, key, ev="click")=>{
      const text = getLocalizedMsg(el || document.body, key);
      const hasBs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
      if (hasBs) {
        let toast = document.querySelector("#np-error-toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.id = "np-error-toast";
          toast.className = "toast align-items-center text-bg-danger border-0";
          toast.setAttribute("role","alert");
          toast.setAttribute("aria-live","assertive");
          toast.setAttribute("aria-atomic","true");
          toast.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${text}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
          document.body.appendChild(toast);
        }
        const handler = ()=> new bootstrap.Toast(toast).show();
        document.addEventListener(ev, handler, { once:true });
        const mo = new MutationObserver((_,o)=>{ if(!document.body.contains(toast)){ document.removeEventListener(ev,handler); o.disconnect(); } });
        mo.observe(document.body,{ childList:true, subtree:true });
      } else {
        const handler = ()=> alert(text);
        document.addEventListener(ev, handler, { once:true });
      }
    };

    const guardOnce = (targetEl, key, ev="click")=>{
      if (!targetEl || targetEl.getAttribute(DATA_LISTENER_ADDED)==="true") return;
      const handler = ()=> showFeedback(targetEl, key, ev);
      document.addEventListener(ev, handler, { once:true });
      targetEl.setAttribute(DATA_LISTENER_ADDED,"true");
      const mo = new MutationObserver((_,o)=>{ if(!document.body.contains(targetEl)){ document.removeEventListener(ev,handler); o.disconnect(); } });
      mo.observe(document.body,{ childList:true, subtree:true });
    };

    try {
      if (typeof $ === "undefined") { console.error("jQuery failed to load"); return; }

      const $input = $("#attachment");
      const $img = $("#image");

      if ($input.length) {
        const onChange = function(){
          const file = this?.files?.[0];
          if (!file || !$img.length) { guardOnce(this, "attachment_preview_unavailable"); return; }
          try {
            const prev = this.getAttribute("data-prev-url") ?? "";
            const url = URL.createObjectURL(file);
            $img.attr("src", url);
            if (prev) { try { URL.revokeObjectURL(prev); } catch {} }
            this.setAttribute("data-prev-url", url);
          } catch { guardOnce(this, "attachment_preview_unavailable"); }
        };
        if ($input.attr("data-np-bound")!=="true") {
          $input.on("change", onChange);
          $input.attr("data-np-bound","true");
          const el = $input.get(0);
          const mo = new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ $input.off("change", onChange); o.disconnect(); } });
          mo.observe(document.body,{ childList:true, subtree:true });
        }
      } else {
        guardOnce(document.body, "attachment_preview_unavailable");
      }
    } catch (e) {
      console.error("Initialization failed", e);
    }
  })();
</script>
