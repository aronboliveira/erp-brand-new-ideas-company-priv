@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\{Purchase,Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $lang = Utility::fetchUserLang();
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
        window.translations = {
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
            <a href="{{ route(ViewsConstants::PRC.'.create',0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Purchase')}}</th>
                                <th> {{__('Vendor')}}</th>
                                <th> {{__('Category')}}</th>
                                <th> {{__('Purchase Date')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                    <th > {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($purchases as $purchase)

                                <tr>
                                    <td class="Id">
                                        <a href="{{ route(ViewsConstants::PRC.'.show',Crypt::encrypt($purchase->id)) }}" class="btn btn-outline-primary">{{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</a>
                                    </td>
                                    <td> {{ (!empty( $purchase->vendor)?$purchase->vendor->name:'') }} </td>
                                    <td>{{ !empty($purchase->category)?$purchase->category->name:''}}</td>
                                    <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                                    <td>
                                        @if($purchase->status == 0)
                                            <span class="purchase_status badge bg-secondary p-2 px-3 rounded">{{ __(Purchase::$statuses[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 1)
                                            <span class="purchase_status badge bg-warning p-2 px-3 rounded">{{ __(Purchase::$statuses[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 2)
                                            <span class="purchase_status badge bg-danger p-2 px-3 rounded">{{ __(Purchase::$statuses[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 3)
                                            <span class="purchase_status badge bg-info p-2 px-3 rounded">{{ __(Purchase::$statuses[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 4)
                                            <span class="purchase_status badge bg-primary p-2 px-3 rounded">{{ __(Purchase::$statuses[$purchase->status]) }}</span>
                                        @endif
                                    </td>

                                    @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                        <td class="Action">
                                            <span>
                                                @can('show purchase')
                                                    <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route(ViewsConstants::PRC.'.show', Crypt::encrypt($purchase->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                @endcan
                                                @can('edit purchase')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route(ViewsConstants::PRC.'.edit', Crypt::encrypt($purchase->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete purchase')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::PRC.'.destroy', $purchase->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$purchase->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$purchase->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

