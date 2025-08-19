@php
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Project;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Crypt,Gate,Route};
@endphp
@if(isset($project) && is_object($project))
    @php
        $projectPassword = '';
        $rawPassword = data_get($project, 'password', '');
        if (!empty($rawPassword)) {
            try {
                $projectPassword = base64_decode($rawPassword);
                if (!$projectPassword) $projectPassword = '';
            } catch (Exception $e) {
                $projectPassword = '';
            }
        }
        $projectId = data_get($project, 'id');
        $encryptedProjectId = '';
        if (!empty($projectId)) {
            try {
                $encryptedProjectId = Crypt::encrypt($projectId);
            } catch (Exception $e) {
                $encryptedProjectId = '';
            }
        }
        $modules = [
            'basic_details' => __('Basic details'),
            'member' => __('Member'),
            'task' => __('Task'),
            'milestone' => __('Milestone'),
            'attachment' => __('Attachment'),
            'bug_report' => __('Bug Report'),
            'timesheet' => __('Timesheet'),
            'tracker_details' => __('Tracker details'),
            'expense' => __('Expense'),
            'activity' => __('Activity')
        ];
    @endphp
    <div class="modal-body">
        <div class="table-responsive">
            @if(isset($projectID) && !empty($projectID))
                @php
                    $projectCopyLinkBaseName     = ViewsConstants::PRJ.'.copy.link';
                    $projectCopyLinkKebabName    = Str::kebab($projectCopyLinkBaseName);
                    $projectCopyLinkResolvedName = Route::has($projectCopyLinkBaseName)
                        ? $projectCopyLinkBaseName
                        : (Route::has($projectCopyLinkKebabName) ? $projectCopyLinkKebabName : null);
                    $projectIdValue              = isset($projectID) && !empty($projectID) ? $projectID : null;
                    $projectCopyLinkRouteArray   = ($projectCopyLinkResolvedName && $projectIdValue) ? [$projectCopyLinkResolvedName, $projectIdValue] : ['#'];
                    $projectCopyLinkUrl          = ($projectCopyLinkResolvedName && $projectIdValue) ? route($projectCopyLinkResolvedName, $projectIdValue) : '#';
                    $projectCopyLinkGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'copy_project_link_route_unavailable') ?? 'Copy project link route is unavailable. Please contact technical support or your domain administrator.';
                    $projectCopyLinkFormId       = 'project-copy-link-form';
                @endphp
                {!! Form::open([
                    'route'          => $projectCopyLinkRouteArray,
                    'method'         => 'post',
                    'accept-charset' => 'UTF-8',
                    'id'             => $projectCopyLinkFormId,
                    'data-url'       => $projectCopyLinkUrl,
                    'data-guard-msg' => $projectCopyLinkGuardMsg
                ]) !!}
                @csrf
            @endif
            <table class="{{ VC::TB }} {{ VC::MB0 }}">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Module') }}</th>
                        <th class="text-right">{{ __('On/Off') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $moduleKey => $moduleLabel)
                        @php
                            $isChecked = false;
                            if (isset($result) && is_object($result)) {
                                $moduleValue = data_get($result, $moduleKey, '');
                                $isChecked = ($moduleValue === 'on');
                            }
                            $inputId = 'copy_link_' . str_replace('_', '', $moduleKey);
                        @endphp
                        <tr>
                            <td>{{ $moduleLabel }}</td>
                            <td class="action text-right">
                                <div class="{{ VC::FM_CHK }} form-switch">
                                    <input type="checkbox"
                                           name="{{ e($moduleKey) }}"
                                           class="form-check-input"
                                           id="{{ $inputId }}"
                                           value="on"
                                           {{ $isChecked ? 'checked="checked"' : '' }}>
                                    <label class="custom-control-label" for="{{ $inputId }}"></label>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>{{ __('Password Protected') }}</td>
                        <td class="action text-right">
                            @php
                                $isPasswordProtected = false;
                                if (isset($result) && is_object($result)) {
                                    $passwordProtectedValue = data_get($result, 'password_protected', '');
                                    $isPasswordProtected = ($passwordProtectedValue === 'on');
                                }
                            @endphp
                            <div class="{{ VC::FM_CHK }} form-switch">
                                <input type="checkbox"
                                       name="password_protected"
                                       class="form-check-input password_protect"
                                       id="password_protected"
                                       value="on"
                                       {{ $isPasswordProtected ? 'checked="checked"' : '' }}>
                                <label class="custom-control-label" for="password_protected"></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="passwords">
                        <td>
                            <div class="action input-group input-group-merge text-left">
                                <input type="password"
                                       value="{{ e($projectPassword) }}"
                                       class="{{ VC::FM_CT }} @error('password') is-invalid @enderror"
                                       name="password"
                                       autocomplete="new-password"
                                       id="password"
                                       placeholder="{{ __('Enter Your Password') }}">
                                <div class="input-group-append">
                                    <span class="{{ VC::TXTS_TRP }} py-3">
                                        <a href="#" data-toggle="password-text" data-target="#password">
                                            <i class="fas fa-eye-slash" id="togglePassword"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="modal-footer">
                @can('share project')
                    @if(!empty($encryptedProjectId))
                        @php
                            $projectCopyLinkBaseName     = ViewsConstants::PRJ.'.link';
                            $projectCopyLinkKebabName    = Str::kebab($projectCopyLinkBaseName);
                            $projectCopyLinkResolvedName = Route::has($projectCopyLinkBaseName)
                                ? $projectCopyLinkBaseName
                                : (Route::has($projectCopyLinkKebabName) ? $projectCopyLinkKebabName : null);
                            $projectIdValue              = isset($projectId) && !empty($projectId) ? $projectId : null;
                            $encryptedProjectId          = $projectIdValue ? Crypt::encrypt($projectIdValue) : null;
                            $projectCopyLinkUrl          = ($projectCopyLinkResolvedName && $encryptedProjectId) ? route($projectCopyLinkResolvedName, $encryptedProjectId) : '#';
                            $projectCopyLinkGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'link_project_route_unavailable') ?? 'Link project route is unavailable. Please contact technical support or your domain administrator.';
                            $projectCopyLinkSuccessMsg   = __('Project link copied to clipboard.');
                            $projectCopyLinkId           = 'project-copy-link-'.($projectIdValue ?? 'x');
                        @endphp
                        <a href="{{ $projectCopyLinkUrl }}"
                        id="{{ $projectCopyLinkId }}"
                        class="{{ VC::BT_PRM }}"
                        data-url="{{ $projectCopyLinkUrl }}"
                        data-guard-msg="{{ $projectCopyLinkGuardMsg }}"
                        data-success-msg="{{ $projectCopyLinkSuccessMsg }}">
                            {{ __('Copy Project') }}
                        </a>
                        <script defer>
                            (() => {
                                try {
                                    const l = document.getElementById('{{ $projectCopyLinkId }}');
                                    if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                    l.setAttribute('data-listener-active', 'true');
                                    l.addEventListener('click', async (e) => {
                                        try {
                                            e.preventDefault();
                                            const href = l.getAttribute('href') || '#';
                                            const url = l.getAttribute('data-url') || href || '#';
                                            if (href === '#' && url === '#') {
                                                const msg = l.getAttribute('data-guard-msg') || 'Copy project link route is unavailable. Please contact technical support or your domain administrator.';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
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
                                                l.setAttribute('data-failed-route', 'true');
                                                return;
                                            }
                                            const successMsg = l.getAttribute('data-success-msg') || 'Project link copied to clipboard.';
                                            let copied = false;
                                            try {
                                                if (navigator?.clipboard?.writeText) {
                                                    await navigator.clipboard.writeText(url);
                                                    copied = true;
                                                }
                                            } catch (_) {}
                                            if (!copied) {
                                                try {
                                                    const ta = document.createElement('textarea');
                                                    ta.value = url;
                                                    ta.setAttribute('readonly', '');
                                                    ta.style.position = 'absolute';
                                                    ta.style.left = '-9999px';
                                                    document.body.appendChild(ta);
                                                    ta.select();
                                                    document.execCommand('copy');
                                                    document.body.removeChild(ta);
                                                    copied = true;
                                                } catch (_) {}
                                            }
                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                            if (copied) {
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role', 'alert');
                                                    toast.setAttribute('aria-live', 'assertive');
                                                    toast.setAttribute('aria-atomic', 'true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = successMsg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(successMsg);
                                                }
                                            }
                                        } catch (err) {}
                                    });
                                } catch (error) {}
                            })();
                        </script>
                    @endif
                @endcan
                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
            </div>
            @if(isset($projectID) && $projectID)
                {{ Form::close() }}
            @endif
        </div>
    </div>
    <script async>
        window.translations = {
            ar: { password_controls_unavailable: "تعذّر تفعيل عناصر كلمة المرور" },
            da: { password_controls_unavailable: "Kunne ikke aktivere adgangskodekontroller" },
            de: { password_controls_unavailable: "Passwortsteuerungen konnten nicht aktiviert werden" },
            en: { password_controls_unavailable: "Password controls could not be initialized" },
            es: { password_controls_unavailable: "No se pudieron inicializar los controles de contraseña" },
            fr: { password_controls_unavailable: "Impossible d’initialiser les contrôles de mot de passe" },
            he: { password_controls_unavailable: "לא ניתן לאתחל פקדי סיסמה" },
            it: { password_controls_unavailable: "Impossibile inizializzare i controlli password" },
            ja: { password_controls_unavailable: "パスワード制御を初期化できませんでした" },
            nl: { password_controls_unavailable: "Wachtwoordbesturingselementen konden niet initialiseren" },
            pl: { password_controls_unavailable: "Nie udało się zainicjować kontrolek hasła" },
            pt: { password_controls_unavailable: "Não foi possível inicializar os controlos de palavra-passe" },
            "pt-br": { password_controls_unavailable: "Não foi possível inicializar os controles de senha" },
            ru: { password_controls_unavailable: "Не удалось инициализировать элементы управления паролем" },
            tr: { password_controls_unavailable: "Parola denetimleri başlatılamadı" },
            zh: { password_controls_unavailable: "无法初始化密码控件" }
        };
    </script>
    <script defer>
        (()=>{
            const errFb="# ERROR";
            const dataClientLocalized="data-client-localized";
            const dataGuardMsg="data-guard-msg";
            const BOUND="data-np-bound";

            const getMsg=(el,msgKey)=>{
            let msg=errFb;
            if (el?.getAttribute("data-sv-localized")==="true" || el?.getAttribute(dataClientLocalized)==="true") {
                msg=el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");
                lang = lang==="pt-br" ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
                if (msg!==errFb) { el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true"); }
            }
            return msg;
            };

            const toastOnUserAction=(key,ev="click")=>{
            const text=getMsg(document.body,key);
            const hasBs=document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (hasBs) {
                let toast=document.querySelector("#np-error-toast");
                if (!toast) {
                toast=document.createElement("div");
                toast.id="np-error-toast";
                toast.className="toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                toast.setAttribute("role","alert");
                toast.setAttribute("aria-live","assertive");
                toast.setAttribute("aria-atomic","true");
                toast.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(toast);
                }
                const once=()=>new bootstrap.Toast(toast).show();
                document.addEventListener(ev,once,{once:true});
                const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(toast)){ document.removeEventListener(ev,once); o.disconnect(); } });
                mo.observe(document.body,{childList:true,subtree:true});
            } else {
                const once=()=>alert(text);
                document.addEventListener(ev,once,{once:true});
            }
            };

            try {
            if (typeof $==="undefined") { console.error("jQuery failed to load"); return; }

            const $chk = $("#password_protected");
            const $fallbackChk = $(".password_protect");
            const $pwFields = $(".passwords");
            const $toggle = $("#togglePassword");
            const $pw = $("#password");

            const updateVisibility=()=>{
                const isChecked = ($chk.length ? $chk.is(":checked") : false) || ($fallbackChk.length ? $fallbackChk.is(":checked") : false);
                if ($pwFields.length) { $pwFields.toggle(isChecked); }
            };

            const onRequiredToggle=function(){
                const checked = $(this).is(":checked");
                if (!$pwFields.length) return;
                if (checked) {
                $pwFields.removeClass("password_protect");
                $pwFields.attr("required", true);
                } else {
                $pwFields.addClass("password_protect");
                $pwFields.val(null);
                $pwFields.removeAttr("required");
                }
            };

            const bindWithCleanup=(jqEl,evt,handler)=>{
                if (!jqEl?.length) return;
                const el=jqEl.get(0);
                if (el.getAttribute(BOUND)==="true") return;
                jqEl.on(evt,handler);
                el.setAttribute(BOUND,"true");
                const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ jqEl.off(evt,handler); o.disconnect(); } });
                mo.observe(document.body,{childList:true,subtree:true});
            };

            $(function(){
                if (!$pwFields.length || (!$chk.length && !$fallbackChk.length)) {
                toastOnUserAction("password_controls_unavailable","click");
                return;
                }
                updateVisibility();
                if ($chk.length) bindWithCleanup($chk,"change",onRequiredToggle);
                bindWithCleanup($(document),"change",function(e){
                if (e.target && e.target.id==="password_protected") updateVisibility();
                });
            });

            if ($toggle.length && $pw.length) {
                bindWithCleanup($toggle,"click",function(){
                try{
                    const nextType = ($pw.attr("type")??"password")==="password" ? "text" : "password";
                    $pw.attr("type",nextType);
                    $(this).toggleClass("fa-eye").toggleClass("fa-eye-slash");
                }catch{ toastOnUserAction("password_controls_unavailable","click"); }
                });
            } else {
                toastOnUserAction("password_controls_unavailable","click");
            }
            } catch (e) {
            console.error("Initialization failed", e);
            }
        })();
    </script>
    <script defer>
        (() => {
            try {
                const f = document.getElementById('{{ $projectCopyLinkFormId }}');
                if (!f || f.getAttribute('data-listener-active') === 'true') return;
                f.setAttribute('data-listener-active', 'true');
                f.addEventListener('submit', e => {
                    try {
                        const url = f.getAttribute('data-url') || '#';
                        const action = f.getAttribute('action') || '#';
                        if (url !== '#' || action !== '#') return;
                        e.preventDefault();
                        const msg = f.getAttribute('data-guard-msg') || 'Copy project link route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
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
                        f.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                });
            } catch (error) {}
        })();
    </script>
@else
    <div class="modal-body">
        <div class="alert alert-danger text-center">
            {{ __('Project data is not available') }}
        </div>
    </div>
@endif