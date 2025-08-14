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
{{ Form::model($contract, array('route' => array(ViewsConstants::CTC.'.update', $contract->id), 'method' => 'PUT')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan = Utility::getChatGPTSettings();
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
            {{ Form::text('subject', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('client_name', __('Client'),['class'=>'form-label']) }}
            {{ Form::select('client_name', $clients, null, ['class' => 'form-control select client_select', 'id' => 'client_select']) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('project', __('Project'), ['class' => 'form-label']) }}
            <div class="project-div">
                {{ Form::select('project', $project, null, ['class' => 'form-control  project_select', 'id' => 'project_id', 'name' => 'project_id']) }}
            </div>
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('type', __('Contract Type'),['class'=>'form-label']) }}
            {{ Form::select('type', $contractTypes,null, array('class' => 'form-control','data-toggle'=>'select','required'=>'required')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('value', __('Contract Value'),['class'=>'form-label']) }}
            {{ Form::number('value', null, array('class' => 'form-control','required'=>'required','stage'=>'0.01')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}
            {{ Form::date('start_date', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}
            {{ Form::date('end_date', null, array('class' => 'form-control','required'=>'required')) }}
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
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Form::close()}}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script async>
  window.translations = {
    ar: { choices_unavailable: "تعذّر تهيئة عناصر الاختيار", project_list_unavailable: "تعذّر تحميل قائمة المشاريع" },
    da: { choices_unavailable: "Kunne ikke initialisere multi-select", project_list_unavailable: "Kunne ikke indlæse projektliste" },
    de: { choices_unavailable: "Mehrfachauswahl konnte nicht initialisiert werden", project_list_unavailable: "Projektliste konnte nicht geladen werden" },
    en: { choices_unavailable: "Cannot initialize multi-select", project_list_unavailable: "Cannot load project list" },
    es: { choices_unavailable: "No se puede inicializar el selector múltiple", project_list_unavailable: "No se puede cargar la lista de proyectos" },
    fr: { choices_unavailable: "Impossible d’initialiser la sélection multiple", project_list_unavailable: "Impossible de charger la liste des projets" },
    he: { choices_unavailable: "לא ניתן לאתחל בחירה מרובה", project_list_unavailable: "לא ניתן לטעון את רשימת הפרויקטים" },
    it: { choices_unavailable: "Impossibile inizializzare la multiselezione", project_list_unavailable: "Impossibile caricare l’elenco progetti" },
    ja: { choices_unavailable: "マルチセレクトを初期化できません", project_list_unavailable: "プロジェクト一覧を読み込めません" },
    nl: { choices_unavailable: "Multiselect kan niet worden geïnitialiseerd", project_list_unavailable: "Projectlijst kan niet worden geladen" },
    pl: { choices_unavailable: "Nie można zainicjować pola wielokrotnego wyboru", project_list_unavailable: "Nie można wczytać listy projektów" },
    pt: { choices_unavailable: "Não foi possível iniciar o multisseleção", project_list_unavailable: "Não foi possível carregar a lista de projetos" },
    "pt-br": { choices_unavailable: "Não foi possível iniciar o multisseleção", project_list_unavailable: "Não foi possível carregar a lista de projetos" },
    ru: { choices_unavailable: "Не удалось инициализировать мультивыбор", project_list_unavailable: "Не удалось загрузить список проектов" },
    tr: { choices_unavailable: "Çoklu seçim başlatılamıyor", project_list_unavailable: "Proje listesi yüklenemiyor" },
    zh: { choices_unavailable: "无法初始化多选控件", project_list_unavailable: "无法加载项目列表" }
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
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
          .toLowerCase()
          .replace(/_/g, "-");
        lang = (lang === "pt-br") ? lang : lang.slice(0, 2);

        msg =
          window.translations?.[lang]?.[key] ||
          el?.getAttribute(dataGuardMsg) ||
          window.translations?.en?.[key] ||
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
            </div>
          `;
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
      const url  = element?.getAttribute?.("data-url");
      const href = element?.action ?? element?.href;
      return (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#");
    };

    try {
      if (typeof $ === "undefined") {
        console.error("jQuery failed to load");
        return;
      }

      const initChoices = () => {
        const $ms = $(".multi-select");
        if (!$ms.length) return;

        if (typeof window.Choices !== "function") {
          console.error("Choices failed to load");
          showFeedback(document.body, "choices_unavailable");
          return;
        }

        $ms.each((_, el) => {
          const id = el?.id;
          if (!id) return;
          if (el.getAttribute("data-choices-init") === "true") return;

          try {
            // eslint-disable-next-line no-new
            new Choices(`#${id}`, { removeItemButton: true });
            el.setAttribute("data-choices-init", "true");
          } catch {
            showFeedback(el, "choices_unavailable");
          }

          const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) o.disconnect();
          });
          mo.observe(document.body, { childList: true, subtree: true });
        });
      };

      const getParent = (bid, sourceEl) => {
        const base = `{{ url('contract/clients/select') }}`;
        const url  = `${base}/${encodeURIComponent(bid ?? "")}`;

        if (!bid || routeGuard(null, url)) {
          guardOnce(sourceEl, "project_list_unavailable");
          return;
        }

        $.ajax({
          url,
          type: "GET",
          success: (data) => {
            try {
              const $sel = $("#project_id");
              if (!$sel.length) {
                guardOnce(document.body, "project_list_unavailable");
                return;
              }

              $sel.empty();

              if (Array.isArray(data) && data.length) {
                data.forEach((it) => {
                  if (!it) return;
                  const val  = String(it.id ?? "");
                  const text = String(it.name ?? "");
                  if (val.length) $sel.append(`<option value="${val}">${text}</option>`);
                });
              }

              if (typeof window.Choices === "function" && !$sel[0].getAttribute("data-choices-init")) {
                try {
                  // eslint-disable-next-line no-new
                  new Choices("#project_id", { removeItemButton: true });
                  $sel[0].setAttribute("data-choices-init", "true");
                } catch {
                  showFeedback($sel[0], "choices_unavailable");
                }
              }

              if (!Array.isArray(data) || !data.length) {
                $sel.empty();
              }
            } catch {
              showFeedback(sourceEl, "project_list_unavailable");
            }
          },
          error: () => showFeedback(sourceEl, "project_list_unavailable")
        });
      };

      initChoices();

      $(document).on("change", ".client_select", function () {
        const client_id = $(this).val();
        getParent(client_id, this);
      });
    } catch (e) {
      console.error("Initialization failed", e);
    }
  })();
</script>
