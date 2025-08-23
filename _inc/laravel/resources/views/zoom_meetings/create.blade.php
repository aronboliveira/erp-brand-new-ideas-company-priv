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

{!! Form::open([
    'route'  => [ViewsConstants::ZMM . '.store'],
    'method' => 'post',
    'id'     => 'store-user',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ route('generate', ['zoom meeting']) }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Meeting Title'), 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('project_id', __('Project'), ['class' => VC::FM_LB]) }}
                {{ Form::select('project_id', $projects, null, ['class' => VC::FM_CT_SL . ' project_select', 'id' => 'project_select', 'data-toggle' => 'select']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('user_id', __('Users'), ['class' => VC::FM_LB]) }}
                <div id="user_div">
                    <select class="{{ VC::FM_CT_SL }} employee_select" id="user_id" name="user_id[]">
                        <option value="">{{ __('Select User') }}</option>
                    </select>
                </div>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('start_date', __('Start Date / Time'), ['class' => VC::FM_LB]) }}
                {{ Form::input('datetime-local', 'start_date', null, ['class' => VC::FM_CT . ' date', 'placeholder' => __('Select Date/Time'), 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('duration', __('Duration'), ['class' => VC::FM_LB]) }}
                {{ Form::number('duration', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Duration'), 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('password', __('Password ( Optional )'), ['class' => VC::FM_LB]) }}
                {{ Form::password('password', ['class' => VC::FM_CT, 'placeholder' => __('Enter Password')]) }}
            </div>

            @if(isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                    <div class="form-switch">
                        <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                        <label class="form-check-label" for="switch-shadow"></label>
                    </div>
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                <div class="form-switch form-switch-right">
                    <input class="form-check-input" type="checkbox" name="client_id" id="client_id" checked>
                    <label class="form-check-label" for="client_id">{{ __('Invite Client For Zoom Meeting') }}</label>
                </div>
            </div>
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
    ar: { zoom_users_unavailable: "تعذّر تحميل مستخدمي المشروع" },
    da: { zoom_users_unavailable: "Kunne ikke hente projektbrugere" },
    de: { zoom_users_unavailable: "Projektbenutzer konnten nicht geladen werden" },
    en: { zoom_users_unavailable: "Could not load project users" },
    es: { zoom_users_unavailable: "No se pudieron cargar los usuarios del proyecto" },
    fr: { zoom_users_unavailable: "Impossible de charger les utilisateurs du projet" },
    he: { zoom_users_unavailable: "לא ניתן לטעון משתמשי פרויקט" },
    it: { zoom_users_unavailable: "Impossibile caricare gli utenti del progetto" },
    ja: { zoom_users_unavailable: "プロジェクトのユーザーを読み込めませんでした" },
    nl: { zoom_users_unavailable: "Projectgebruikers laden is mislukt" },
    pl: { zoom_users_unavailable: "Nie udało się wczytać użytkowników projektu" },
    pt: { zoom_users_unavailable: "Não foi possível carregar os utilizadores do projeto" },
    "pt-br": { zoom_users_unavailable: "Não foi possível carregar os usuários do projeto" },
    ru: { zoom_users_unavailable: "Не удалось загрузить пользователей проекта" },
    tr: { zoom_users_unavailable: "Proje kullanıcıları yüklenemedi" },
    zh: { zoom_users_unavailable: "无法加载项目用户" }
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
    const errFb="# ERROR";
    const dataClientLocalized="data-client-localized";
    const dataGuardMsg="data-guard-msg";
    const DATA_LISTENER="data-listener-added";

    const getMsg=(el,key)=>{
      let msg=errFb;
      if(el?.getAttribute("data-sv-localized")==="true"||el?.getAttribute(dataClientLocalized)==="true"){
        msg=el.getAttribute(dataGuardMsg)||errFb;
      }else{
        let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");
        lang=lang==="pt-br"?lang:lang.slice(0,2);
        msg=window.translations?.[lang]?.[key]||el?.getAttribute(dataGuardMsg)||window.translations?.en?.[key]||errFb;
        if(msg!==errFb){ el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true"); }
      }
      return msg;
    };

    const showFeedback=(el,key,ev="click")=>{
      const text=getMsg(el||document.body,key);
      const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
      if(hasBs){
        let toast=document.querySelector("#np-error-toast");
        if(!toast){
          toast=document.createElement("div");
          toast.id="np-error-toast";
          toast.className="toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
          toast.setAttribute("role","alert");
          toast.setAttribute("aria-live","assertive");
          toast.setAttribute("aria-atomic","true");
          toast.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
          document.body.appendChild(toast);
        }
        const handler=()=>new bootstrap.Toast(toast).show();
        document.addEventListener(ev,handler,{once:true});
        const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(toast)){ document.removeEventListener(ev,handler); o.disconnect(); } });
        mo.observe(document.body,{childList:true,subtree:true});
      }else{
        const handler=()=>alert(text);
        document.addEventListener(ev,handler,{once:true});
      }
    };

    const guardOnce=(el,key,ev="click")=>{
      if(!el||el.getAttribute(DATA_LISTENER)==="true") return;
      const cb=()=>showFeedback(el,key,ev);
      document.addEventListener(ev,cb,{once:true});
      el.setAttribute(DATA_LISTENER,"true");
      const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ document.removeEventListener(ev,cb); o.disconnect(); } });
      mo.observe(document.body,{childList:true,subtree:true});
    };

    const routeGuard=(url,el)=>{
      const bad=!url||url==="#";
      if(bad) guardOnce(el,"zoom_users_unavailable","click");
      return bad;
    };

    try{
      if(typeof $==="undefined"){ console.error("jQuery failed to load"); return; }
      const BASE="{{ url('zoom-meeting/projects/select') }}";
      const userDiv=$("#user_div");
      const SELECT_ID="user_id";
      const SELECT_HTML=`<select class="form-control" id="${SELECT_ID}" name="user_id[]" multiple></select>`;

      const buildOrReuseSelect=()=>{
        let $sel=$("#"+SELECT_ID);
        if(!$sel.length){
          if(!userDiv.children("#"+SELECT_ID).length) userDiv.append(SELECT_HTML);
          $sel=$("#"+SELECT_ID);
        }else{
          $sel.empty();
        }
        return $sel;
      };

      const choicesKey="_npChoicesInstance";
      const ensureChoices=(sel)=>{
        if(typeof window.Choices!=="function"){ console.error("Choices failed to load"); return null; }
        if(sel[0][choicesKey]){ try{ sel[0][choicesKey].destroy(); }catch{} sel[0][choicesKey]=null; }
        const inst=new Choices(sel[0],{ removeItemButton:true });
        sel[0][choicesKey]=inst;
        const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(sel[0])){ try{ inst.destroy(); }catch{} o.disconnect(); } });
        mo.observe(document.body,{childList:true,subtree:true});
        return inst;
      };

      const fetchUsers=(projectId)=>{
        const pid=projectId ?? "";
        const url=`${BASE}/${encodeURIComponent(pid)}`;
        if(routeGuard(url,document.body)) return;
        $.ajax({
          url,
          type:"GET",
          success:data=>{
            const list=Array.isArray(data)?data:[];
            const $sel=buildOrReuseSelect();
            const frag=document.createDocumentFragment();
            for(const it of list){
              const id=String(it?.id ?? "");
              const name=String(it?.name ?? "");
              if(!id) continue;
              const opt=document.createElement("option");
              opt.value=id;
              opt.textContent=name;
              frag.appendChild(opt);
            }
            $sel.append(frag);
            ensureChoices($sel);
            if(list.length===0) $sel.empty();
          },
          error:()=>guardOnce(document.body,"zoom_users_unavailable","click")
        });
      };

      if(document.body.getAttribute("data-zoom-users-bound")!=="true"){
        $(document).on("change",".project_select",function(){
          const projectId=$(this).val() ?? "";
          fetchUsers(projectId);
        });
        document.body.setAttribute("data-zoom-users-bound","true");
      }
    }catch(e){
      console.error("Initialization failed",e);
    }
  })();
</script>






