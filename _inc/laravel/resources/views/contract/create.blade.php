@php
    use App\Config\Constants\{
        PlansConstants, 
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
@endphp
{{ Form::open(array('url' => ViewsConstants::CTC)) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= Utility::getChatGPTSettings();
    @endphp
    @if($plan?->{PlansConstants::COL_GPT} == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['contract']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="{{ VC::FAS_RB }}"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="{{ VC::FM_GCB12 }}">
            {{ Form::label('subject', __('Subject'),['class'=>'form-label']) }}
            {{ Form::text('subject', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('client_name', __('Client'),['class'=>'form-label']) }}
            {{--            {{ Form::select('client_name', $clients,null, array('class' => 'form-control','data-toggle="select"','required'=>'required')) }}--}}
            {{ Form::select('client_name', $clients, null, ['class' => 'form-control select client_select', 'id' => 'client_select']) }}

        </div>
        <div class="{{ VC::FM_GCB6 }}" >
            {{ Form::label('projects', __('Projects'),['class'=>'form-label'])}}
            <select class="form-control select project_select" id="project_id" name="project_id" >
                <option value="">{{__('Select Project')}}</option>
            </select>
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('type', __('Contract Type'),['class'=>'form-label']) }}
            {{ Form::select('type', $contractTypes,null, array('class' => 'form-control','data-toggle="select"','required'=>'required')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('value', __('Contract Value'),['class'=>'form-label']) }}
            {{ Form::number('value', '', array('class' => 'form-control','required'=>'required','stage'=>'0.01')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}
            {{ Form::date('start_date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}
            {{ Form::date('end_date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
    <div class="row">
        <div class="{{ VC::FM_GCB12 }}">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']) !!}
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Form::close()}}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script async>
  window.translations = {
    ar: { choices_unavailable: "تعذّر تهيئة عناصر الاختيار", project_list_unavailable: "تعذّر تحميل المشاريع" },
    da: { choices_unavailable: "Kunne ikke initialisere valgfelter", project_list_unavailable: "Kunne ikke indlæse projekter" },
    de: { choices_unavailable: "Auswahlfelder konnten nicht initialisiert werden", project_list_unavailable: "Projekte konnten nicht geladen werden" },
    en: { choices_unavailable: "Cannot initialize multi-select", project_list_unavailable: "Cannot load projects" },
    es: { choices_unavailable: "No se puede inicializar el selector múltiple", project_list_unavailable: "No se pueden cargar los proyectos" },
    fr: { choices_unavailable: "Impossible d’initialiser la sélection multiple", project_list_unavailable: "Impossible de charger les projets" },
    he: { choices_unavailable: "לא ניתן לאתחל בחירה מרובה", project_list_unavailable: "לא ניתן לטעון פרויקטים" },
    it: { choices_unavailable: "Impossibile inizializzare la multiselezione", project_list_unavailable: "Impossibile caricare i progetti" },
    ja: { choices_unavailable: "マルチセレクトを初期化できません", project_list_unavailable: "プロジェクトを読み込めません" },
    nl: { choices_unavailable: "Kan multiselect niet initialiseren", project_list_unavailable: "Kan projecten niet laden" },
    pl: { choices_unavailable: "Nie można zainicjować pola wielokrotnego wyboru", project_list_unavailable: "Nie można wczytać projektów" },
    pt: { choices_unavailable: "Não foi possível iniciar o multisseleção", project_list_unavailable: "Não foi possível carregar os projetos" },
    "pt-br": { choices_unavailable: "Não foi possível iniciar o multisseleção", project_list_unavailable: "Não foi possível carregar os projetos" },
    ru: { choices_unavailable: "Не удалось инициализировать мультивыбор", project_list_unavailable: "Не удалось загрузить проекты" },
    tr: { choices_unavailable: "Çoklu seçim başlatılamadı", project_list_unavailable: "Projeler yüklenemedi" },
    zh: { choices_unavailable: "无法初始化多选控件", project_list_unavailable: "无法加载项目" }
  };
</script>
<script defer>
  (()=>{
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    const DATA_LISTENER_ADDED = "data-listener-added";

    const getMsg = (el, msgKey) => {
      let msg = errFb;
      if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
        msg = el.getAttribute(dataGuardMsg) || errFb;
      } else {
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[msgKey] ||
          el?.getAttribute(dataGuardMsg) ||
          window.translations?.en?.[msgKey] ||
          errFb;
        if (msg !== errFb) {
          el?.setAttribute(dataGuardMsg, msg);
          el?.setAttribute(dataClientLocalized, "true");
        }
      }
      return msg;
    };

    const showFeedback = (el, key, ev = "pointerup") => {
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

    const guardOnce = (el, key, ev = "pointerup") => {
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

      const initChoices = () => {
        if (!$(".multi-select").length) return;
        if (typeof window.Choices !== "function") {
          showFeedback(document.body, "choices_unavailable");
          console.error("Choices library failed to load");
          return;
        }
        $(".multi-select").each((_, element) => {
          const id = element?.id;
          if (!id) return;
          if (element.getAttribute("data-choices-init") === "true") return;
          try {
            // eslint-disable-next-line no-new
            new Choices(`#${id}`, { removeItemButton: true });
            element.setAttribute("data-choices-init", "true");
          } catch {
            showFeedback(element, "choices_unavailable");
          }
          const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(element)) {
              o.disconnect();
            }
          });
          mo.observe(document.body, { childList: true, subtree: true });
        });
      };

      const onClientChange = (e) => {
        const clientId = $(e.currentTarget).val() ?? "";
        getParent(clientId, e.currentTarget);
      };

      const getParent = (bid, targetEl) => {
        const base = `{{ url('contract/clients/select') }}`;
        const url = `${base}/${encodeURIComponent(bid ?? "")}`;
        if (!bid || routeGuard(null, url)) {
          guardOnce(targetEl, "project_list_unavailable");
          return;
        }
        $.ajax({
          url,
          type: "GET",
          success: (data) => {
            try {
              const $select = $("#project_id");
              if (!$select.length) {
                guardOnce(document.body, "project_list_unavailable");
                return;
              }
              $select.empty();
              if (Array.isArray(data) && data.length) {
                data.forEach((item) => {
                  if (!item) return;
                  const val = item.id ?? "";
                  const text = item.name ?? "";
                  if (String(val).length) $select.append(`<option value="${String(val)}">${String(text)}</option>`);
                });
              }
              if (typeof window.Choices === "function" && !$select[0].getAttribute("data-choices-init")) {
                try {
                  // eslint-disable-next-line no-new
                  new Choices("#project_id", { removeItemButton: true });
                  $select[0].setAttribute("data-choices-init", "true");
                } catch {
                  showFeedback($select[0], "choices_unavailable");
                }
              }
            } catch {
              showFeedback(targetEl, "project_list_unavailable");
            }
          },
          error: () => showFeedback(targetEl, "project_list_unavailable")
        });
      };

      initChoices();
      $(document).on("change", ".client_select", onClientChange);
    } catch (e) {
      console.error("Initialization failed", e);
    }
  })();
</script>
