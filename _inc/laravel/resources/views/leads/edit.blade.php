@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PlansConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
{{ Form::model($lead, array('route' => array(ViewsConstants::LD.'.update', $lead->id), 'method' => 'PUT')) }}
    <div class="modal-body">
        {{-- start for ai module--}}
        @php
            $plan= Utility::getChatGPTSettings();
        @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
        <div class="text-end">
            <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['lead']) }}"
            data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                <i class="{{ VC::FAS_RB }}"></i> <span>{{__('Generate with AI')}}</span>
            </a>
        </div>
        @endif
        {{-- end for ai module--}}
        <div class="row">
            <div class="col-6 form-group">
                {{ Form::label('subject', __('Subject'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('subject', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('user_id', __('User'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('user_id', $users,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('email', __('Email'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::email('email', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('phone', __('Phone'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('phone', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('stage_id', __('Stage'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('stage_id', [''=>__('Select Stage')],null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
            <div class="col-12 form-group">
                {{ Form::label('sources', __('Sources'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('sources[]', $sources,null, array('class' => 'form-control select2','id'=>'choices-multiple1','multiple'=>'','required'=>'required')) }}
            </div>
            <div class="col-12 form-group">
                {{ Form::label('products', __('Products'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('products[]', $products,null, array('class' => 'form-control select2','id'=>'choices-multiple2','multiple'=>'','required'=>'required')) }}
            </div>
            <div class="col-12 form-group">
                {{ Form::label('notes', __('Notes'),['class'=>'form-label']) }}
                {{ Form::textarea('notes',null, array('class' => 'summernote-simple')) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{Form::close()}}
<script async>
  window.translations = {
    ar: { pipeline_stages_unavailable: "تعذّر تحميل المراحل" },
    da: { pipeline_stages_unavailable: "Kunne ikke indlæse faser" },
    de: { pipeline_stages_unavailable: "Phasen konnten nicht geladen werden" },
    en: { pipeline_stages_unavailable: "Cannot load stages" },
    es: { pipeline_stages_unavailable: "No se pueden cargar las etapas" },
    fr: { pipeline_stages_unavailable: "Impossible de charger les étapes" },
    he: { pipeline_stages_unavailable: "לא ניתן לטעון שלבים" },
    it: { pipeline_stages_unavailable: "Impossibile caricare le fasi" },
    ja: { pipeline_stages_unavailable: "ステージを読み込めません" },
    nl: { pipeline_stages_unavailable: "Fases kunnen niet worden geladen" },
    pl: { pipeline_stages_unavailable: "Nie można wczytać etapów" },
    pt: { pipeline_stages_unavailable: "Não foi possível carregar as etapas" },
    "pt-br": { pipeline_stages_unavailable: "Não foi possível carregar as etapas" },
    ru: { pipeline_stages_unavailable: "Не удалось загрузить этапы" },
    tr: { pipeline_stages_unavailable: "Aşamalar yüklenemiyor" },
    zh: { pipeline_stages_unavailable: "无法加载阶段" }
  };
</script>
<script defer>
  (()=>{
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    const DATA_LISTENER_ADDED = "data-listener-added";

    const getMsg = (el, key) => {
      let msg = errFb;
      if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
        msg = el.getAttribute(dataGuardMsg) || errFb;
      } else {
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg = window.translations?.[lang]?.[key] || el?.getAttribute(dataGuardMsg) || window.translations?.en?.[key] || errFb;
        if (msg !== errFb) {
          el?.setAttribute(dataGuardMsg, msg);
          el?.setAttribute(dataClientLocalized, "true");
        }
      }
      return msg;
    };

    const showFeedback = (el, key, ev = "click") => {
      const text = getMsg(el || document.body, key);
      const hasBs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
      if (hasBs) {
        let toast = document.querySelector("#np-error-toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.id = "np-error-toast";
          toast.className = "toast align-items-center text-bg-danger border-0";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          toast.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${text}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
          document.body.appendChild(toast);
        }
        const handler = () => new bootstrap.Toast(toast).show();
        document.addEventListener(ev, handler, { once: true });
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(toast)) {
            document.removeEventListener(ev, handler);
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      } else {
        const handler = () => alert(text);
        document.addEventListener(ev, handler, { once: true });
      }
    };

    const guardOnce = (el, key, ev = "click") => {
      if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
      const handler = () => showFeedback(el, key, ev);
      el.addEventListener(ev, handler, { once: true });
      el.setAttribute(DATA_LISTENER_ADDED, "true");
      const mo = new MutationObserver((_, o) => {
        if (!document.body.contains(el)) {
          el.removeEventListener(ev, handler);
          o.disconnect();
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    };

    const routeGuard = (element, alt) => {
      const url = element?.getAttribute?.("data-url");
      const href = element?.action ?? element?.href;
      return (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#");
    };

    try {
      if (typeof $ === "undefined") {
        console.error("jQuery failed to load");
        return;
      }

      const stageId = '{{$lead->stage_id}}';
      const leadsUrl = '{{ route('leads.json') }}';

      const fillStages = (data) => {
        const $sel = $("#stage_id");
        if (!$sel.length) return;
        $sel.empty();
        try {
          const obj = typeof data === "string" ? JSON.parse(data) : data;
          const keys = obj && typeof obj === "object" ? Object.keys(obj) : [];
          if (keys.length > 0) {
            $.each(obj, (key, label) => {
              const selected = String(key) === String(stageId) ? ' selected' : '';
              $sel.append(`<option value="${key}"${selected}>${label ?? ""}</option>`);
            });
          }
          $sel.val(stageId);
          if ($.fn.select2) {
            $sel.select2({ placeholder: "{{ __('Select Stage') }}" });
          }
        } catch {
          guardOnce(document.body, "pipeline_stages_unavailable");
        }
      };

      const getStages = (pipelineId, sourceEl) => {
        if (!pipelineId || routeGuard(null, leadsUrl)) {
          guardOnce(sourceEl || document.body, "pipeline_stages_unavailable");
          return;
        }
        $.ajax({
          url: leadsUrl,
          type: "POST",
          data: { pipeline_id: pipelineId, _token: $('meta[name="csrf-token"]').attr('content') },
          success: fillStages,
          error: () => guardOnce(sourceEl || document.body, "pipeline_stages_unavailable")
        });
      };

      $(()=>{
        const pipeline_id = $('[name=pipeline_id]').val() ?? "";
        getStages(pipeline_id, document.body);
      });

      $(document).on("change", "#commonModal select[name='pipeline_id']", function () {
        const currVal = $(this).val() ?? "";
        getStages(currVal, this);
      });
    } catch (e) {
      console.error("Initialization failed", e);
    }
  })();
</script>
