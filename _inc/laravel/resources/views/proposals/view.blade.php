@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Proposal,Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $settings = Utility::settings();
    $proposalIndexBaseName = VW::PPS . '.index';
    $proposalIndexKebabName = Str::kebab($proposalIndexBaseName);
    $proposalIndexResolvedName = Route::has($proposalIndexBaseName) ? $proposalIndexBaseName : (Route::has($proposalIndexKebabName) ? $proposalIndexKebabName : null);
    $proposalIndexUrl = $proposalIndexResolvedName ? route($proposalIndexResolvedName) : '#';
    $proposalIndexGuardMsg = Utility::fetchLinkMessage($lang, VW::PPS, 'proposal_index_route_unavailable') ?? 'Proposal index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Proposal Detail')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:{status_unavailable:"تعذّر تحديث الحالة",copy_unavailable:"تعذّر نسخ الرابط",copy_success:"تم نسخ الرابط إلى الحافظة"},
            da:{status_unavailable:"Kunne ikke opdatere status",copy_unavailable:"Kunne ikke kopiere linket",copy_success:"Link kopieret til udklipsholder"},
            de:{status_unavailable:"Status konnte nicht aktualisiert werden",copy_unavailable:"Link konnte nicht kopiert werden",copy_success:"Link in die Zwischenablage kopiert"},
            en:{status_unavailable:"Could not update the status",copy_unavailable:"Could not copy the link",copy_success:"Link copied to clipboard"},
            es:{status_unavailable:"No se pudo actualizar el estado",copy_unavailable:"No se pudo copiar el enlace",copy_success:"Enlace copiado al portapapeles"},
            fr:{status_unavailable:"Impossible de mettre à jour le statut",copy_unavailable:"Impossible de copier le lien",copy_success:"Lien copié dans le presse-papiers"},
            he:{status_unavailable:"לא ניתן לעדכן את הסטטוס",copy_unavailable:"לא ניתן להעתיק את הקישור",copy_success:"הקישור הועתק ללוח"},
            it:{status_unavailable:"Impossibile aggiornare lo stato",copy_unavailable:"Impossibile copiare il link",copy_success:"Link copiato negli appunti"},
            ja:{status_unavailable:"ステータスを更新できませんでした",copy_unavailable:"リンクをコピーできませんでした",copy_success:"リンクをクリップボードにコピーしました"},
            nl:{status_unavailable:"Status kon niet worden bijgewerkt",copy_unavailable:"Link kon niet worden gekopieerd",copy_success:"Link gekopieerd naar klembord"},
            pl:{status_unavailable:"Nie udało się zaktualizować statusu",copy_unavailable:"Nie można skopiować linku",copy_success:"Link skopiowano do schowka"},
            pt:{status_unavailable:"Não foi possível atualizar o status",copy_unavailable:"Não foi possível copiar o link",copy_success:"Link copiado para a área de transferência"},
            "pt-br":{status_unavailable:"Não foi possível atualizar o status",copy_unavailable:"Não foi possível copiar o link",copy_success:"Link copiado para a área de transferência"},
            ru:{status_unavailable:"Не удалось обновить статус",copy_unavailable:"Не удалось скопировать ссылку",copy_success:"Ссылка скопирована в буфер обмена"},
            tr:{status_unavailable:"Durum güncellenemedi",copy_unavailable:"Bağlantı kopyalanamadı",copy_success:"Bağlantı panoya kopyalandı"},
            zh:{status_unavailable:"无法更新状态",copy_unavailable:"无法复制链接",copy_success:"链接已复制到剪贴板"}
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
            const DATA_BOUND="data-np-bound";

            const getMsg=(el,msgKey)=>{
            let msg=errFb;
            if(el.getAttribute("data-sv-localized")==="true"||el.getAttribute(dataClientLocalized)==="true"){
                msg=el.getAttribute(dataGuardMsg)||errFb;
            }else{
                let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");
                lang=lang==="pt-br"?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[msgKey]||el.getAttribute(dataGuardMsg)||window.translations?.en?.[msgKey]||errFb;
                if(msg!==errFb){ el.setAttribute(dataGuardMsg,msg); el.setAttribute(dataClientLocalized,"true"); }
            }
            return msg;
            };

            const planToastOn=(key,target,evType="pointerup")=>{
            const t=target; if(!t) return;
            if(t.getAttribute(DATA_BOUND)==="true") return;
            const handler=()=>{
                const text=getMsg(document.body,key);
                const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
                if(hasBs){
                let toast=document.querySelector("#np-error-toast");
                if(!toast){
                    toast=document.createElement("div");
                    toast.id="np-error-toast";
                    toast.className="toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    toast.setAttribute("role","alert"); toast.setAttribute("aria-live","assertive"); toast.setAttribute("aria-atomic","true");
                    toast.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    document.body.appendChild(toast);
                }
                new bootstrap.Toast(toast).show();
                }else{
                alert(text);
                }
            };
            t.addEventListener(evType,handler,{once:true});
            t.setAttribute(DATA_BOUND,"true");
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(t)){ t.removeEventListener(evType,handler); o.disconnect(); } });
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const routeInvalid=(el)=>{
            const url=el?.getAttribute?.("data-url");
            const href=el?.href ?? el?.getAttribute?.("href");
            return ((!url||url==="#")&&(!href||href==="#"));
            };

            try{
            if(typeof $==="undefined"){ 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");     
                return; 
            }
            $(document).on("change",".status_change",function(){
                const el=this;
                const status=el?.value ?? "";
                const base=$(el).data("url") ?? el.getAttribute("data-url") ?? "";
                if(routeInvalid(el)){ planToastOn("status_unavailable",el,"pointerup"); return; }
                const url=`${base}${base.includes("?")?"&":"?"}status=${encodeURIComponent(status)}`;
                $.ajax({ url, type:"GET", cache:false }).fail(()=>{ planToastOn("status_unavailable",el,"pointerup"); });
            });

            $(document).on("click",".cp_link",function(e){
                e.preventDefault();
                const el=this;
                const value=$(el).attr("data-link") ?? "";
                if(!value){ planToastOn("copy_unavailable",el,"click"); return; }
                const done=(ok)=>{
                const msg=getMsg(document.body, ok?"copy_success":"copy_unavailable");
                try{ show_toastr?.( ok?"success":"error", msg, ok?"success":"error" ); }catch{}
                };
                if(navigator?.clipboard?.writeText){
                navigator.clipboard.writeText(value).then(()=>done(true)).catch(()=>done(false));
                }else{
                const temp=document.createElement("input");
                temp.style.position="fixed"; temp.style.opacity="0"; temp.value=value;
                document.body.appendChild(temp); temp.select();
                const ok=document.execCommand("copy"); document.body.removeChild(temp);
                done(!!ok);
                }
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
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a
            id="proposal-index-link"
            href="{{ $proposalIndexUrl }}"
            data-url="{{ $proposalIndexUrl }}"
            data-guard-msg="{{ $proposalIndexGuardMsg }}"
        >
            {{ __('Proposal') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Proposal Details')}}</li>
@endsection
@section('content')
    @can('send proposal')
        @php
            $proposalValid = isset($proposal) && !empty($proposal) && (is_array($proposal) || is_object($proposal));
            $pid = $proposalValid ? data_get($proposal,'id') : null;
            $statusVal = $proposalValid ? data_get($proposal,'status') : null;
            $issueDate = $proposalValid ? data_get($proposal,'issue_date') : null;
            $sendDate = $proposalValid ? data_get($proposal,'send_date') : null;
            $issueDateText = ($user && method_exists($user,'dateFormat') && $issueDate) ? ($user->dateFormat($issueDate) ?? __('No issue date available')) : __('No issue date available');
            $sendDateText = ($user && method_exists($user,'dateFormat') && $sendDate) ? ($user->dateFormat($sendDate) ?? __('No send date available')) : __('No send date available');
            $badgeByStatus = [0=>'bg-primary',1=>'bg-info',2=>'bg-success',3=>'bg-warning',4=>'bg-danger'];
            $statusLabels = (isset(Proposal::$statuses) && is_array(Proposal::$statuses)) ? Proposal::$statuses : [];
            $validSt = isset($statusVal) && is_numeric($statusVal) && $statusVal >= 0 && $statusVal <= 4;
            $statusBadgeClasses = $validSt ? ($badgeByStatus[(int)$statusVal] ?? 'bg-secondary') : 'bg-secondary';
            $statusText = $validSt ? __($statusLabels[(int)$statusVal] ?? __('Unknown status')) : __('Unknown status');
            $statusOptions = (isset($status) && (is_array($status) || $status instanceof \Illuminate\Support\Collection)) ? (array) $status : [];
        @endphp
        @if($proposalValid && ((int)($statusVal ?? -1) !== 4))
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }} {{ VC::MB3 }}">
                        <div class="card-body">
                            <div class="row timeline-wrapper">
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots"></span>
                                        <i class="{{ VC::TI_PLS }} text-primary"></i>
                                    </div>
                                    <h6 class="text-primary {{ VC::MY3 }}">{{ __('Create Proposal') }}</h6>
                                    <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}"><i class="ti ti-clock {{ VC::MR2 }}"></i>{{ __('Created on ') }}{{ $issueDateText }}</p>
                                    @can('edit proposal')
                                        <a href="{{ $pid ? route(VW::PPS.'.edit', Crypt::encrypt($pid)) : '#' }}" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}"><i class="{{ VC::TI_PC }} {{ VC::MR2 }}"></i>{{ __('Edit') }}</a>
                                    @endcan
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots"></span>
                                        <i class="ti ti-mail text-warning"></i>
                                    </div>
                                    <h6 class="text-warning {{ VC::MY3 }}">{{ __('Send Proposal') }}</h6>
                                    <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}">
                                        @if($validSt && (int)$statusVal !== 0)
                                            <i class="ti ti-clock {{ VC::MR2 }}"></i>{{ __('Sent on') }} {{ $sendDateText }}
                                        @else
                                            @can('send proposal')
                                                <small>{{ __('Status') }} : {{ __('Not Sent') }}</small>
                                            @endcan
                                        @endif
                                    </p>
                                    @if($validSt && (int)$statusVal === 0)
                                        @can('send proposal')
                                            <a href="{{ $pid ? route(VW::PPS.'.sent', $pid) : '#' }}" class="{{ VC::BT_SM }} btn-warning" data-bs-toggle="tooltip" data-original-title="{{ __('Mark Sent') }}"><i class="ti ti-send {{ VC::MR2 }}"></i>{{ __('Send') }}</a>
                                        @endcan
                                    @endif
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots"></span>
                                        <i class="ti ti-report-money text-info"></i>
                                    </div>
                                    <h6 class="text-info {{ VC::MY3 }}">{{ __('Proposal Status') }}</h6>
                                    <small>
                                        <span class="badge {{ $statusBadgeClasses }} p-2 px-3 rounded">{{ $statusText }}</span>
                                    </small>
                                    <br>
                                    <div class="float-right {{ VC::MT3 }} col-md-3 {{ VC::FEND }} ml-5" data-bs-toggle="tooltip" data-original-title="{{ __('Click to change status') }}">
                                        @if(!empty($statusOptions))
                                            <select class="{{ VC::FM_CT }} status_change select2" name="status" data-url="{{ $pid ? route(VW::PPS.'.status.change', $pid) : '#' }}">
                                                @foreach($statusOptions as $k => $val)
                                                    <option value="{{ $k }}" {{ ($validSt && (int)$statusVal === (int)$k) ? 'selected' : '' }}>{{ $val }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <small>{{ __('No statuses available') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endcan
    @php
        $isCompanyUser = (data_get($user ?? null, UsersConstants::COL_TP) === PermissionsConstants::CPN);
        $proposalValid = isset($proposal) && !empty($proposal) && (is_array($proposal) || is_object($proposal));
        $pid = $proposalValid ? data_get($proposal,'id') : null;
        $statusVal = $proposalValid ? data_get($proposal,'status') : null;
        $notZeroStatus = isset($statusVal) && is_numeric($statusVal) && (int)$statusVal !== 0;
        $proposalPdfBaseName     = ViewsConstants::PPS.'.pdf';
        $proposalPdfKebabName    = Str::kebab($proposalPdfBaseName);
        $proposalPdfResolvedName = Route::has($proposalPdfBaseName)
            ? $proposalPdfBaseName
            : (Route::has($proposalPdfKebabName) ? $proposalPdfKebabName : null);
        $proposalIdValue         = isset($pid) && !empty($pid) ? $pid : null;
        $encryptedProposalId     = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
        $proposalPdfUrl          = ($proposalPdfResolvedName && $encryptedProposalId) ? route($proposalPdfResolvedName, $encryptedProposalId) : '#';
        $proposalPdfGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'download_proposal_pdf_route_unavailable') ?? 'Download proposal pdf route is unavailable. Please contact technical support or your domain administrator.';
        $proposalPdfLinkId       = 'proposal-pdf-download-link-'.($proposalIdValue ?? 'x');
    @endphp
    @if($isCompanyUser)
        @if($proposalValid && $notZeroStatus)
            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} {{ VC::JCB }} justify-content-md-end">
                    <div class="all-button-box mx-2">
                        @php
                            $proposalResendBaseName     = ViewsConstants::PPS.'.resent';
                            $proposalResendKebabName    = Str::kebab($proposalResendBaseName);
                            $proposalResendResolvedName = Route::has($proposalResendBaseName)
                                ? $proposalResendBaseName
                                : (Route::has($proposalResendKebabName) ? $proposalResendKebabName : null);
                            $proposalIdValue            = isset($pid) && !empty($pid) ? $pid : null;
                            $proposalResendUrl          = ($proposalResendResolvedName && $proposalIdValue) ? route($proposalResendResolvedName, $proposalIdValue) : '#';
                            $proposalResendGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'resend_proposal_route_unavailable') ?? 'Resend proposal route is unavailable. Please contact technical support or your domain administrator.';
                            $proposalResendLinkId       = 'proposal-resend-link-'.($proposalIdValue ?? 'x');
                        @endphp
                        <a href="{{ $proposalResendUrl }}"
                        id="{{ $proposalResendLinkId }}"
                        class="{{ VC::BT_PRM }}"
                        data-url="{{ $proposalResendUrl }}"
                        data-guard-msg="{{ $proposalResendGuardMsg }}">
                            {{ __('Resend Proposal') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    try {
                                        const l = document.getElementById('{{ $proposalResendLinkId }}');
                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                        l.setAttribute('data-listener-active', 'true');
                                        l.addEventListener('click', (e) => {
                                            try {
                                                const href = l.getAttribute('href') || '#';
                                                const url  = l.getAttribute('data-url') || href || '#';
                                                if (href !== '#' || url !== '#') return;
                                                e.preventDefault();
                                                const msg = l.getAttribute('data-guard-msg') || 'Resend proposal route is unavailable. Please contact technical support or your domain administrator.';
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
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
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
                                            } catch (err) {}
                                        });
                                    } catch (error) {}
                                })();
                            </script>
                        @endpush
                    </div>
                    <div class="all-button-box">
                        <a href="{{ $proposalPdfUrl }}"
                        id="{{ $proposalPdfLinkId }}"
                        class="{{ VC::BT_PRM }}"
                        target="_blank"
                        data-url="{{ $proposalPdfUrl }}"
                        data-guard-msg="{{ $proposalPdfGuardMsg }}">
                            {{ __('Download') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
            <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} {{ VC::JCB }} justify-content-md-end">
                <div class="all-button-box">
                    <a href="{{ $proposalPdfUrl }}"
                        id="{{ $proposalPdfLinkId }}" class="{{ VC::BT_XS }} btn-white btn-icon-only width-auto" target="_blank"
                        data-url="{{ $proposalPdfUrl }}"
                        data-guard-msg="{{ $proposalPdfGuardMsg }}"
                        >{{ __('Download') }}</a>
                </div>
            </div>
        </div>
    @endif
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                try {
                    const l = document.getElementById('{{ $proposalPdfLinkId }}');
                    if (!l || l.getAttribute('data-listener-active') === 'true') return;
                    l.setAttribute('data-listener-active', 'true');
                    l.addEventListener('click', e => {
                        try {
                            const href = l.getAttribute('href') || '#';
                            const url  = l.getAttribute('data-url') || href || '#';
                            if (href !== '#' || url !== '#') return;
                            e.preventDefault();
                            const msg = l.getAttribute('data-guard-msg') || 'Download proposal pdf route is unavailable. Please contact technical support or your domain administrator.';
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
                                toast.setAttribute('role','alert');
                                toast.setAttribute('aria-live','assertive');
                                toast.setAttribute('aria-atomic','true');
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
                        } catch (err) {}
                    });
                } catch (error) {}
            })();
        </script>
    @endpush
    @php
        $proposalValid = isset($proposal) && !empty($proposal) && (is_array($proposal) || is_object($proposal));
        $userValid = isset($user) && is_object($user);
        $fmtDate = function($v) use($userValid,$user){ return $v ? (($userValid && method_exists($user,'dateFormat')) ? ($user->dateFormat($v) ?? null) : null) : null; };
        $fmtPrice = function($v) use($userValid,$user){ return ($userValid && method_exists($user,'priceFormat')) ? ($user->priceFormat($v) ?? number_format((float)$v,2)) : number_format((float)$v,2); };
        $pid = $proposalValid ? data_get($proposal,'id') : null;
        $proposalNum = $proposalValid ? (($userValid && method_exists($user,'proposalNumberFormat')) ? ($user->proposalNumberFormat(data_get($proposal,'proposal_id')) ?? null) : null) : null;
        $proposalNum = $proposalNum ?? __('Failed to get proposal number');
        $issueDateText = $fmtDate($proposalValid ? data_get($proposal,'issue_date') : null) ?? __('No issue date available');
        $statusVal = $proposalValid ? data_get($proposal,'status') : null;
        $statusMap = [0=>'bg-primary',1=>'bg-info',2=>'bg-success',3=>'bg-warning',4=>'bg-danger'];
        $statusLabels = (isset(\App\Models\Proposal::$statuses) && is_array(\App\Models\Proposal::$statuses)) ? \App\Models\Proposal::$statuses : [];
        $stValid = isset($statusVal) && is_numeric($statusVal) && (int)$statusVal>=0 && (int)$statusVal<=4;
        $statusClass = $stValid ? ($statusMap[(int)$statusVal] ?? 'bg-secondary') : 'bg-secondary';
        $statusText = $stValid ? __($statusLabels[(int)$statusVal] ?? __('Unknown status')) : __('Unknown status');
        $customerValid = isset($customer) && (is_array($customer) || is_object($customer));
        $customFieldsSafe = (isset($customFields) && (is_array($customFields) || $customFields instanceof \Illuminate\Support\Collection)) ? $customFields : [];
        $itemsSafe = (isset($items) && (is_array($items) || $items instanceof \Illuminate\Support\Collection)) ? $items : [];
        $totalQuantity = 0; $totalRate = 0; $grandTaxTotal = 0; $totalDiscount = 0; $taxesData = [];
        $vatOn = (isset($settings) && is_array($settings) && (data_get($settings,'vat_gst_number_switch') === 'on'));
        $footerTitle = (isset($settings) && is_array($settings) ? (data_get($settings,'footer_title') ?? __('No footer title available')) : __('No footer title available'));
        $footerNotes = (isset($settings) && is_array($settings) ? (data_get($settings,'footer_notes') ?? __('No footer notes available')) : __('No footer notes available'));
    @endphp
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="{{ VC::CLM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                    <h4>{{ __('Proposal') }}</h4>
                                </div>
                                <div class="{{ VC::CLM6 }} {{ VC::CS12 }} {{ VC::C12 }} text-end">
                                    <h4 class="invoice-number">{{ $proposalNum }}</h4>
                                </div>
                                <div class="{{ VC::C12 }}">
                                    <hr>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="col text-end">
                                    <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}">
                                        <div class="me-4">
                                            <small>
                                                <strong>{{ __('Issue Date') }} :</strong><br>
                                                {{ $issueDateText }}<br><br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="col">
                                    <small class="font-style">
                                        <strong>{{ __('Billed To') }} :</strong><br>
                                        @if($customerValid && !empty(data_get($customer,'billing_name')))
                                            {{ data_get($customer,'billing_name') ?? __('No billing name available') }}<br>
                                            {{ data_get($customer,'billing_address') ?? __('No billing address available') }}<br>
                                            {{ data_get($customer,'billing_city') ?? __('No billing city available') }}<br>
                                            {{ data_get($customer,'billing_state') ?? __('No billing state available') }}<br>
                                            {{ data_get($customer,'billing_zip') ?? __('No billing zip available') }}<br>
                                            {{ data_get($customer,'billing_country') ?? __('No billing country available') }}<br>
                                            {{ data_get($customer,'billing_phone') ?? '' }}<br>
                                            @if($vatOn)
                                                <strong>{{ __('Tax Number ') }} : </strong>{{ data_get($customer,'tax_number') ?? '' }}
                                            @endif
                                        @else
                                            <div>{{ __('No valid billing information available') }}</div>
                                        @endif
                                    </small>
                                </div>
                                @if(Utility::getValByName('shipping_display')=='on')
                                    <div class="col">
                                        <small>
                                            <strong>{{ __('Shipped To') }} :</strong><br>
                                            @if($customerValid && !empty(data_get($customer,'shipping_name')))
                                                {{ data_get($customer,'shipping_name') ?? __('No shipping name available') }}<br>
                                                {{ data_get($customer,'shipping_address') ?? __('No shipping address available') }}<br>
                                                {{ data_get($customer,'shipping_city') ?? __('No shipping city available') }}<br>
                                                {{ data_get($customer,'shipping_state') ?? __('No shipping state available') }}<br>
                                                {{ data_get($customer,'shipping_zip') ?? __('No shipping zip available') }}<br>
                                                {{ data_get($customer,'shipping_country') ?? __('No shipping country available') }}<br>
                                                {{ data_get($customer,'shipping_phone') ?? __('No shipping phone available') }}<br>
                                            @else
                                                <div>{{ __('No valid shipping information available') }}</div>
                                            @endif
                                        </small>
                                    </div>
                                @endif
                                <div class="col">
                                    <div class="{{ VC::FEND }} {{ VC::MT3 }}">
                                        @if($pid)
                                            @php
                                                $proposalLinkCopyBaseName     = ViewsConstants::PPS.'.link.copy';
                                                $proposalLinkCopyKebabName    = Str::kebab($proposalLinkCopyBaseName);
                                                $proposalLinkCopyResolvedName = Route::has($proposalLinkCopyBaseName)
                                                    ? $proposalLinkCopyBaseName
                                                    : (Route::has($proposalLinkCopyKebabName) ? $proposalLinkCopyKebabName : null);
                                                $proposalIdValue              = isset($pid) && !empty($pid) ? $pid : null;
                                                $encryptedProposalId          = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                $proposalLinkCopyUrl          = ($proposalLinkCopyResolvedName && $encryptedProposalId) ? route($proposalLinkCopyResolvedName, $encryptedProposalId) : '#';
                                                $proposalLinkCopyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'copy_proposal_link_route_unavailable') ?? 'Copy proposal link route is unavailable. Please contact technical support or your domain administrator.';
                                                $proposalLinkCopyQrId         = 'proposal-link-copy-qr-'.($proposalIdValue ?? 'x');
                                            @endphp
                                            <div id="{{ $proposalLinkCopyQrId }}"
                                                class="{{ VC::DBL }}"
                                                data-url="{{ $proposalLinkCopyUrl }}"
                                                data-guard-msg="{{ $proposalLinkCopyGuardMsg }}">
                                                {!! (new \Milon\Barcode\DNS2D)->getBarcodeHTML($proposalLinkCopyUrl, 'QRCODE', 2, 2) !!}
                                            </div>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const c = document.getElementById('{{ $proposalLinkCopyQrId }}');
                                                            if (!c || c.getAttribute('data-listener-active') === 'true') return;
                                                            c.setAttribute('data-listener-active', 'true');
                                                            c.addEventListener('click', e => {
                                                                try {
                                                                    const url = c.getAttribute('data-url') || '#';
                                                                    if (url !== '#') return;
                                                                    e.preventDefault();
                                                                    const msg = c.getAttribute('data-guard-msg') || 'Copy proposal link route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                    c.setAttribute('data-failed-route', 'true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (error) {}
                                                    })();
                                                </script>
                                            @endpush
                                        @else
                                            <small>{{ __('Could not generate QR code') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }} {{ VC::MT3 }}">
                                <div class="col">
                                    <small>
                                        <strong>{{ __('Status') }}:</strong><br>
                                        <span class="badge {{ $statusClass }} p-2 px-3 rounded">{{ $statusText }}</span>
                                    </small>
                                </div>
                            </div>
                            @if(!empty($customFieldsSafe) && !empty(data_get($proposal,'customField')))
                                @foreach($customFieldsSafe as $field)
                                    @php
                                        $fid = data_get($field,'id');
                                        $fname = data_get($field,'name') ?? __('No field name available');
                                        $fval = data_get($proposal,'customField.'.$fid) ?? '-';
                                    @endphp
                                    <div class="col text-end">
                                        <small>
                                            <strong>{{ $fname }} :</strong><br>
                                            {{ $fval }}<br><br>
                                        </small>
                                    </div>
                                @endforeach
                            @endif
                            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                <div class="{{ VC::CM12 }}">
                                    <div class="font-weight-bold">{{ __('Product Summary') }}</div>
                                    <small>{{ __('All items here cannot be deleted.') }}</small>
                                    <div class="table-responsive mt-2">
                                        <table class="{{ VC::TB }} {{ VC::MB0 }} invoice-body">
                                            <thead>
                                                <tr>
                                                    <th class="text-dark" data-width="40">#</th>
                                                    <th class="text-dark">{{ __('Product') }}</th>
                                                    <th class="text-dark">{{ __('Quantity') }}</th>
                                                    <th class="text-dark">{{ __('Rate') }}</th>
                                                    <th class="text-dark">{{ __('Discount') }}</th>
                                                    <th class="text-dark">{{ __('Tax') }}</th>
                                                    <th class="text-dark">{{ __('Description') }}</th>
                                                    <th class="text-end text-dark" width="12%">{{ __('Price') }}<br><small class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small></th>
                                                </tr>
                                            </thead>
                                            @foreach($itemsSafe as $key => $item)
                                                @php
                                                    $qty = (float)(data_get($item,'quantity') ?? 0);
                                                    $price = (float)(data_get($item,'price') ?? 0);
                                                    $disc = (float)(data_get($item,'discount') ?? 0);
                                                    $totalQuantity += $qty;
                                                    $totalRate += $price;
                                                    $totalDiscount += $disc;
                                                    $rowTaxes = [];
                                                    $rowTaxTotal = 0;
                                                    $taxRef = data_get($item,'tax');
                                                    if(!empty($taxRef)){
                                                        $taxList = Utility::tax($taxRef);
                                                        foreach($taxList as $tx){
                                                            $txName = data_get($tx,'name') ?? __('Unknown tax');
                                                            $txRate = (float)(data_get($tx,'rate') ?? 0);
                                                            $txPrice = Utility::taxRate($txRate,$price,$qty,$disc);
                                                            $rowTaxes[] = ['name'=>$txName,'rate'=>$txRate,'amount'=>$txPrice];
                                                            $rowTaxTotal += $txPrice;
                                                            $taxesData[$txName] = ($taxesData[$txName] ?? 0) + $txPrice;
                                                        }
                                                    }
                                                    $grandTaxTotal += $rowTaxTotal;
                                                    $prodName = data_get($item,'product.name') ?? __('No product name available');
                                                    $unitId = data_get($item,'product.unit_id');
                                                    $unitModel = $unitId ? \App\Models\ProductServiceUnit::find($unitId) : null;
                                                    $unitName = $unitModel->name ?? __('unit');
                                                    $desc = data_get($item,'description') ?? '-';
                                                    $rowTotal = ($price * $qty - $disc) + $rowTaxTotal;
                                                @endphp
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>{{ $prodName }}</td>
                                                    <td>{{ $qty . ' (' . $unitName . ')' }}</td>
                                                    <td>{{ $fmtPrice($price) }}</td>
                                                    <td>{{ $fmtPrice($disc) }}</td>
                                                    <td>
                                                        @if(!empty($rowTaxes))
                                                            <table>
                                                                @foreach($rowTaxes as $tx)
                                                                    <tr>
                                                                        <td>{{ $tx['name'].' ('.$tx['rate'].'%)' }}</td>
                                                                        <td>{{ $fmtPrice($tx['amount']) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ $desc }}</td>
                                                    <td class="text-end">{{ $fmtPrice($rowTotal) }}</td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td><b>{{ __('Total') }}</b></td>
                                                    <td><b>{{ $totalQuantity }}</b></td>
                                                    <td><b>{{ $fmtPrice($totalRate) }}</b></td>
                                                    <td><b>{{ $fmtPrice($totalDiscount) }}</b></td>
                                                    <td><b>{{ $fmtPrice($grandTaxTotal) }}</b></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                    <td class="text-end">{{ $fmtPrice($proposalValid && method_exists($proposal,'getSubTotal') ? $proposal->getSubTotal() : 0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                    <td class="text-end">{{ $fmtPrice($proposalValid && method_exists($proposal,'getTotalDiscount') ? $proposal->getTotalDiscount() : 0) }}</td>
                                                </tr>
                                                @if(!empty($taxesData))
                                                    @foreach($taxesData as $taxName => $taxPrice)
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ $taxName }}</b></td>
                                                            <td class="text-end">{{ $fmtPrice($taxPrice) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                    <td class="blue-text text-end">{{ $fmtPrice($proposalValid && method_exists($proposal,'getTotal') ? $proposal->getTotal() : 0) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="invoice-footer">
                                            <b>{{ $footerTitle }}</b><br>
                                            {!! $footerNotes !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
