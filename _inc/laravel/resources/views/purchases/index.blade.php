@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Purchase,Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Purchase')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Purchase')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
          (() => { 
            if (!window.translations) {
            window.translations = {};
            }
            const t = {
                        ar:{ copy_unavailable:"تعذّر نسخ الرابط إلى الحافظة", copy_success:"تم نسخ الرابط إلى الحافظة" },
                        da:{ copy_unavailable:"Kunne ikke kopiere linket til udklipsholderen", copy_success:"Link kopieret til udklipsholder" },
                        de:{ copy_unavailable:"Link konnte nicht in die Zwischenablage kopiert werden", copy_success:"Link in die Zwischenablage kopiert" },
                        en:{ copy_unavailable:"Could not copy the link to clipboard", copy_success:"Link copied to clipboard" },
                        es:{ copy_unavailable:"No se pudo copiar el enlace al portapapeles", copy_success:"Enlace copiado al portapapeles" },
                        fr:{ copy_unavailable:"Impossible de copier le lien dans le presse-papiers", copy_success:"Lien copié dans le presse-papiers" },
                        he:{ copy_unavailable:"לא ניתן להעתיק את הקישור ללוח", copy_success:"הקישור הועתק ללוח" },
                        it:{ copy_unavailable:"Impossibile copiare il link negli appunti", copy_success:"Link copiato negli appunti" },
                        ja:{ copy_unavailable:"リンクをクリップボードにコピーできませんでした", copy_success:"リンクをクリップボードにコピーしました" },
                        nl:{ copy_unavailable:"Link kon niet naar het klembord worden gekopieerd", copy_success:"Link gekopieerd naar klembord" },
                        pl:{ copy_unavailable:"Nie można skopiować linku do schowka", copy_success:"Link skopiowano do schowka" },
                        pt:{ copy_unavailable:"Não foi possível copiar o link para a área de transferência", copy_success:"Link copiado para a área de transferência" },
                        "pt-br":{ copy_unavailable:"Não foi possível copiar o link para a área de transferência", copy_success:"Link copiado para a área de transferência" },
                        ru:{ copy_unavailable:"Не удалось скопировать ссылку в буфер обмена", copy_success:"Ссылка скопирована в буфер обмена" },
                        tr:{ copy_unavailable:"Bağlantı panoya kopyalanamadı", copy_success:"Bağlantı panoya kopyalandı" },
                        zh:{ copy_unavailable:"无法将链接复制到剪贴板", copy_success:"链接已复制到剪贴板" }
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
            const DATA_BOUND="data-copy-bound";

            const getMsg=(el,msgKey)=>{
            let msg=errFb;
            if(el?.getAttribute("data-sv-localized")==="true"||el?.getAttribute(dataClientLocalized)==="true"){
                msg=el.getAttribute(dataGuardMsg)||errFb;
            }else{
                let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");
                lang=lang==="pt-br"?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[msgKey]||el?.getAttribute(dataGuardMsg)||window.translations?.en?.[msgKey]||errFb;
                if(msg!==errFb){ el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true"); }
            }
            return msg;
            };

            const showErrorNow=(msgKey)=>{
            const text=getMsg(document.body,msgKey);
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
                new bootstrap.Toast(toast).show();
            }else{
                alert(text);
            }
            };

            const routeInvalid=(el)=>{
            const url=el?.getAttribute?.("data-url");
            const href=el?.getAttribute?.("href");
            return ((!url||url==="#")&&(!href||href==="#"));
            };

            try{
            if(typeof $==="undefined"){ console.error("jQuery failed to load"); return; }

            const SUCCESS_KEY="copy_success";

            if(document.body.getAttribute(DATA_BOUND)==="true") return;
            $(document).on("click",".copy_link",function(e){
                e.preventDefault();
                const el=this;
                if(routeInvalid(el)){ showErrorNow("copy_unavailable"); return; }

                const value = el.getAttribute("href") ?? "";
                const onSuccess=()=>{
                const msg=getMsg(document.body,SUCCESS_KEY);
                try{ show_toastr?.("success",msg,"success"); }catch{}
                };
                const onFail=()=>showErrorNow("copy_unavailable");

                if(navigator?.clipboard?.writeText){
                navigator.clipboard.writeText(value).then(onSuccess).catch(onFail);
                }else{
                try{
                    const ta=document.createElement("textarea");
                    ta.style.position="fixed";
                    ta.style.opacity="0";
                    ta.value=value;
                    document.body.appendChild(ta);
                    ta.select();
                    const ok=document.execCommand("copy");
                    document.body.removeChild(ta);
                    ok?onSuccess():onFail();
                }catch{ onFail(); }
                }
            });
            document.body.setAttribute(DATA_BOUND,"true");

            const mo=new MutationObserver((_,o)=>{
                if(!document.body.contains(document.body)){ $(document).off("click",".copy_link"); o.disconnect(); }
            });
            mo.observe(document.body,{childList:true,subtree:true});
            }catch(e){
            console.error("Initialization failed",e);
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        {{--        <a href="{{ route('bills.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">--}}
        {{--            <i class="ti ti-file-export"></i>--}}
        {{--        </a>--}}
        @can('create purchase')
            @php
                $purchaseCreateBase   = ViewsConstants::PRC.'.create';
                $purchaseCreateKebab  = Str::kebab($purchaseCreateBase);
                $purchaseCreateRes    = Route::has($purchaseCreateBase) ? $purchaseCreateBase : (Route::has($purchaseCreateKebab) ? $purchaseCreateKebab : null);
                $purchaseCreateParams = [0];
                $purchaseCreateUrl    = $purchaseCreateRes ? route($purchaseCreateRes, $purchaseCreateParams) : '#';
                $purchaseCreateMsg    = Utility::fetchLinkMessage($lang, ViewsConstants::PRC, 'create_purchase_route_unavailable') ?? 'Create purchase route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a href="{{ $purchaseCreateUrl }}"
            class="{{ VC::BT_PRM }} create-purchase"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            data-url="{{ $purchaseCreateUrl }}"
            data-guard-msg="{{ $purchaseCreateMsg }}"
            data-sv-localized="true">
                <i class="ti ti-plus"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/purchases/create.js') }}" defer></script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    @php
        $purchasesSafe = (isset($purchases) && (is_array($purchases) || $purchases instanceof \Illuminate\Support\Collection)) ? $purchases : [];
        $fmtDate = function($v,$fb) use($user){ return ($v && $user && method_exists($user,'dateFormat')) ? ($user->dateFormat($v) ?? $fb) : $fb; };
        $statusClass = fn($s) => match((int)$s){0=>'bg-secondary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-primary',default=>'bg-secondary'};
    @endphp
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th class="text-dark">{{ __('Purchase') }}</th>
                                    <th class="text-dark">{{ __('Vendor') }}</th>
                                    <th class="text-dark">{{ __('Category') }}</th>
                                    <th class="text-dark">{{ __('Purchase Date') }}</th>
                                    <th class="text-dark">{{ __('Status') }}</th>
                                    @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                        <th class="text-dark">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script src="{{ asset('assets/js/routes/purchases/show.js') }}" defer></script>
                                    @can('edit purchase')
                                        <script src="{{ asset('assets/js/routes/purchases/edit.js') }}" defer></script>
                                    @endcan
                                    @can('delete purchase')
                                        <script src="{{ asset('assets/js/routes/purchases/delete.js') }}" defer></script>
                                    @endcan
                                @endpush
                                @forelse ($purchasesSafe as $purchase)
                                    @php
                                        $pid = data_get($purchase,'id');
                                        $pnum = $user?->purchaseNumberFormat(data_get($purchase,'purchase_id')) ?? __('Could not find purchase number');
                                        $vname = data_get($purchase,'vendor.name') ?? __('No vendor name available');
                                        $cname = data_get($purchase,'category.name') ?? __('No category name available');
                                        $pdate = $fmtDate(data_get($purchase,'purchase_date'), __('No purchase date available'));
                                        $stIdx = data_get($purchase,'status');
                                        $stText = __(\App\Models\Purchase::$statuses[$stIdx] ?? __('Unknown status'));
                                    @endphp
                                    <tr>
                                        @php
                                            $pidVal                  = isset($pid) ? $pid : null;
                                            $encId                   = $pidVal ? Crypt::encrypt($pidVal) : null;
                                            $purchaseShowBase        = VW::PRC.'.show';
                                            $purchaseShowKebab       = Str::kebab($purchaseShowBase);
                                            $purchaseShowResolved    = Route::has($purchaseShowBase) ? $purchaseShowBase : (Route::has($purchaseShowKebab) ? $purchaseShowKebab : null);
                                            $purchaseShowParams      = $encId ? [$encId] : ['#'];
                                            $purchaseShowUrl         = ($purchaseShowResolved && $encId) ? route($purchaseShowResolved, $purchaseShowParams) : '#';
                                            $purchaseShowGuardMsg    = Utility::fetchLinkMessage($lang, VW::PRC, 'show_purchase_route_unavailable') ?? 'Show purchase route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <td class="Id">
                                            <a href="{{ $purchaseShowUrl }}" class="{{ VC::BT_OUTPM }} purchase-show" data-url="{{ $purchaseShowUrl }}" data-guard-msg="{{ $purchaseShowGuardMsg }}" data-sv-localized="true">{{ $pnum }}</a>
                                        </td>
                                        <td>{{ $vname }}</td>
                                        <td>{{ $cname }}</td>
                                        <td>{{ $pdate }}</td>
                                        <td><span class="purchase_status {{ VC::BDG }} {{ $statusClass($stIdx) }} p-2 {{ VC::PX3 }} rounded">{{ $stText }}</span></td>
                                        @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                            <td class="Action">
                                                <span>
                                                    @can('show purchase')
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a href="{{ $purchaseShowUrl }}" class="{{ VC::BT_SM_CT }} purchase-show" data-bs-toggle="tooltip" title="{{ __('Show') }}" data-original-title="{{ __('Detail') }}" data-url="{{ $purchaseShowUrl }}" data-guard-msg="{{ $purchaseShowGuardMsg }}" data-sv-localized="true"><i class="{{ VC::TI_EYE_WT }}"></i></a>
                                                        </div>
                                                    @endcan
                                                    @can('edit purchase')
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            @php
                                                                $pidVal                = isset($pid) ? $pid : null;
                                                                $encId                 = $pidVal ? Crypt::encrypt($pidVal) : null;
                                                                $purchaseEditBase      = VW::PRC.'.edit';
                                                                $purchaseEditKebab     = Str::kebab($purchaseEditBase);
                                                                $purchaseEditResolved  = Route::has($purchaseEditBase) ? $purchaseEditBase : (Route::has($purchaseEditKebab) ? $purchaseEditKebab : null);
                                                                $purchaseEditParams    = $encId ? [$encId] : ['#'];
                                                                $purchaseEditUrl       = ($purchaseEditResolved && $encId) ? route($purchaseEditResolved, $purchaseEditParams) : '#';
                                                                $purchaseEditGuardMsg  = Utility::fetchLinkMessage($lang, VW::PRC, 'edit_purchase_route_unavailable') ?? 'Edit purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a href="{{ $purchaseEditUrl }}"
                                                            class="{{ VC::BT_SM_CT }} edit-purchase"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-original-title="{{ __('Edit') }}"
                                                            data-url="{{ $purchaseEditUrl }}"
                                                            data-guard-msg="{{ $purchaseEditGuardMsg }}"
                                                            data-sv-localized="true">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete purchase')
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            @php
                                                                $pidVal                 = isset($pid) ? $pid : null;
                                                                $deleteFormId           = 'delete-form-'.($pidVal ?? 'x');
                                                                $destroyBase            = VW::PRC.'.destroy';
                                                                $destroyKebab           = Str::kebab($destroyBase);
                                                                $destroyResolved        = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                                $destroyParams          = $pidVal ? [$pidVal] : ['#'];
                                                                $purchaseDestroyUrl     = ($destroyResolved && $pidVal) ? route($destroyResolved, $destroyParams) : '#';
                                                                $purchaseDestroyGuardMsg= Utility::fetchLinkMessage($lang, VW::PRC, 'destroy_purchase_unavailable') ?? 'Destroy purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                                $confirmTitle           = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                                $confirmBody            = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                            @endphp
                                                            {!! Collective\Html\FormFacade::open([
                                                                'method'              => 'DELETE',
                                                                'url'                 => $purchaseDestroyUrl,
                                                                'class'               => 'delete-form-btn',
                                                                'id'                  => $deleteFormId,
                                                                'data-url'            => $purchaseDestroyUrl,
                                                                'data-guard-msg'      => $purchaseDestroyGuardMsg,
                                                                'data-sv-localized'   => 'true',
                                                            ]) !!}
                                                                <a href="{{ $purchaseDestroyUrl }}"
                                                                class="{{ VC::BT_SM_CT_PR }} delete-purchase"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-url="{{ $purchaseDestroyUrl }}"
                                                                data-guard-msg="{{ $purchaseDestroyGuardMsg }}"
                                                                data-sv-localized="true"
                                                                data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                                data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

