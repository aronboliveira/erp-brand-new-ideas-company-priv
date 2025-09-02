@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('POS Barcode Print')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $posProductBarcodeBaseName     = ViewsConstants::POS.'.barcode';
        $posProductBarcodeKebabName    = Str::kebab($posProductBarcodeBaseName);
        $posProductBarcodeResolvedName = Route::has($posProductBarcodeBaseName)
            ? $posProductBarcodeBaseName
            : (Route::has($posProductBarcodeKebabName) ? $posProductBarcodeKebabName : null);
        $posProductBarcodeUrl          = $posProductBarcodeResolvedName ? route($posProductBarcodeResolvedName) : '#';
        $posProductBarcodeGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::POS, 'pos_product_barcode_route_unavailable') ?? 'Access POS product barcode route is unavailable. Please contact technical support or your domain administrator.';
        $posProductBarcodeLinkId       = 'pos-product-barcode-link';
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $posProductBarcodeUrl }}"
        id="{{ $posProductBarcodeLinkId }}"
            data-url="{{ $posProductBarcodeUrl }}"
            data-guard-msg="{{ $posProductBarcodeGuardMsg }}">
            {{ __('POS Product Barcode') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/pos/productBarcode.js') }}"></script>
    @endpush
    <li class="breadcrumb-item">{{__('POS Barcode Print')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/pos/lang/fetch.js') }}">
    </script>
    <script defer>
        (()=>{
            const errFb="# ERROR";
            const dataClientLocalized="data-client-localized";
            const dataGuardMsg="data-guard-msg";
            const DATA_BOUND="data-np-bound";

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

            const toastOnInteraction=(key,evTarget,evType="click")=>{
            const once=()=>{
                const text=getMsg(document.body,key);
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
            if(!evTarget) return;
            const attr=evTarget.getAttribute(DATA_BOUND);
            if(attr==="true") return;
            evTarget.addEventListener(evType,once,{once:true});
            evTarget.setAttribute(DATA_BOUND,"true");
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(evTarget)){ evTarget.removeEventListener(evType,once); o.disconnect(); } });
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const routeInvalid=(url)=>{
            return (!url||url==="#"||url==="");
            };

            try{
            if(typeof $==="undefined"){ console.error("jQuery failed to load"); return; }

            const csrf=$('meta[name="csrf-token"]').attr('content') ?? "";
            const POS_URL='{{route("pos.getproduct")}}';

            const ensureSelect=($wrap)=>{
                if(!$wrap?.length) return null;
                let $sel=$("#product_id");
                if(!$sel.length){
                const id="product_id";
                if(!$wrap.find("label[for='product_id']").length){
                    $wrap.append('<label for="product_id" class="form-label">{{__('Product')}}</label>');
                }
                $wrap.append('<select class="form-label" id="product_id" name="product_id[]" multiple></select>');
                $sel=$("#product_id");
                }
                return $sel;
            };

            const applyChoices=(selector)=>{
                if(typeof Choices!=="function"){ console.error("Choices failed to load"); return null; }
                const el=document.querySelector(selector);
                if(!el) return null;
                if(el.getAttribute("data-choices-initialized")==="true") return null;
                const inst=new Choices(selector,{ removeItemButton:true });
                el.setAttribute("data-choices-initialized","true");
                const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ try{ inst.destroy?.(); }catch{} o.disconnect(); } });
                mo.observe(document.body,{childList:true,subtree:true});
                return inst;
            };

            const getProduct=(warehouseId)=>{
                if(!warehouseId){ $("#product_id").empty(); return; }
                if(routeInvalid(POS_URL)){
                const trg=document.querySelector('select[name="warehouse_id"]');
                toastOnInteraction("pos_fetch_unavailable",trg,"click");
                return;
                }
                $.ajax({
                url:POS_URL,
                type:"POST",
                data:{ warehouse_id:warehouseId, _token:csrf },
                success:(data)=>{
                    const $wrap=$("#product_div");
                    const $sel=ensureSelect($wrap);
                    if(!$sel) return;
                    $sel.empty();
                    $sel.append('<option value="">{{__('Select Product')}}</option>');
                    if(data&&typeof data==="object"){
                    $.each(data,(key,value)=>{ $sel.append(`<option value="${key}">${value}</option>`); });
                    }
                    applyChoices("#product_id");
                },
                error:()=>{
                    const trg=document.querySelector('select[name="warehouse_id"]');
                    toastOnInteraction("pos_fetch_unavailable",trg,"click");
                }
                });
            };

            $(function(){
                const initialId = $('#warehouse_id').val() ?? "";
                if(initialId) getProduct(initialId);
            });

            $(document).on("change","select[name=warehouse_id]",function(){
                const wid=$(this).val() ?? "";
                getProduct(wid);
            });

            window.copyToClipboard=(element)=>{
                try{
                const value = element?.id ?? "";
                if(!value){ throw new Error("no-value"); }
                const done=(ok)=>{
                    const key= ok ? "copy_success" : "copy_unavailable";
                    const msg=getMsg(document.body,key);
                    try{ show_toastr?.( ok ? "success":"error", msg, ok?"success":"error" ); }catch{}
                };
                if(navigator?.clipboard?.writeText){
                    navigator.clipboard.writeText(value).then(()=>done(true)).catch(()=>done(false));
                }else{
                    const ta=document.createElement("textarea");
                    ta.style.position="fixed";
                    ta.style.opacity="0";
                    ta.value=value;
                    document.body.appendChild(ta);
                    ta.select();
                    const ok=document.execCommand("copy");
                    document.body.removeChild(ta);
                    done(!!ok);
                }
                }catch{
                const key="copy_unavailable";
                const trg=document.body;
                toastOnInteraction(key,trg,"click");
                }
            };

            window.saveAsPDF=()=>{
                try{
                const filename = $('#filesname').val() ?? "report";
                const el=document.getElementById("printableArea");
                if(!el||typeof html2pdf==="undefined"){ throw new Error("pdf-lib-missing"); }
                const opt={ margin:0.3, filename, image:{type:"jpeg",quality:1}, html2canvas:{scale:4,dpi:72,letterRendering:true}, jsPDF:{unit:"in",format:"A2"} };
                html2pdf().set(opt).from(el).save();
                }catch{
                const trg=document.querySelector("[data-action='save-pdf']")||document.body;
                toastOnInteraction("pdf_unavailable",trg,"click");
                }
            };
            }catch(e){
            console.error("Initialization failed",e);
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $posBarcodeRouteBase = VW::POS.'.barcode';
        $posBarcodeRouteKebab = Str::kebab($posBarcodeRouteBase);
        $posBarcodeRouteResolved = Route::has($posBarcodeRouteBase) ? $posBarcodeRouteBase : (Route::has($posBarcodeRouteKebab) ? $posBarcodeRouteKebab : null);
        $posBarcodeUrl = $posBarcodeRouteResolved ? route($posBarcodeRouteResolved) : '#';
        $posBarcodeUserLang = isset($lang) ? $lang : Utility::fetchUserLang();
        $posBarcodeGuardMsg = Utility::fetchLinkMessage($posBarcodeUserLang, VW::POS, 'barcode_pos_route_unavailable') ?? 'POS barcode route is unavailable. Please contact technical support or your domain administrator.';
        $posBarcodeBackLinkId = 'pos-barcode-back-link';
    @endphp
    <a href="{{ $posBarcodeUrl }}"
    id="{{ $posBarcodeBackLinkId }}"
    class="{{ VC::BT_SM_PM }}"
    data-url="{{ $posBarcodeUrl }}"
    data-guard-msg="{{ $posBarcodeGuardMsg }}"
    data-sv-localized="true"
    data-bs-toggle="tooltip"
    title="{{ __('Back') }}">
        <i class="ti ti-arrow-left text-white"></i>
    </a>
    @push(StacksConstants::ADM_SCRP_PG)
        <script defer src="{{ asset('assets/js/routes/pos/barcode.js') }}"></script>
    @endpush
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} {{ VC::MT3 }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    @php
                        $posReceiptBaseName     = ViewsConstants::POS.'.receipt';
                        $posReceiptKebabName    = Str::kebab($posReceiptBaseName);
                        $posReceiptResolvedName = Route::has($posReceiptBaseName)
                            ? $posReceiptBaseName
                            : (Route::has($posReceiptKebabName) ? $posReceiptKebabName : null);
                        $posReceiptRouteArray   = $posReceiptResolvedName ? [$posReceiptResolvedName] : ['#'];
                        $posReceiptUrl          = $posReceiptResolvedName ? route($posReceiptResolvedName) : '#';
                        $posReceiptGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::POS, 'create_pos_receipt_route_unavailable') ?? 'Create pos receipt route is unavailable. Please contact technical support or your domain administrator.';
                        $posReceiptFormId       = 'pos-receipt-form';
                    @endphp
                    {!! Form::open([
                        'route'          => $posReceiptRouteArray,
                        'method'         => 'post',
                        'accept-charset' => 'UTF-8',
                        'id'             => $posReceiptFormId,
                        'data-url'       => $posReceiptUrl,
                        'data-guard-msg' => $posReceiptGuardMsg
                    ]) !!}
                        @csrf
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/pos/receipt.js') }}"></script>
                        @endpush
                        <div class="{{ VC::RW }}" id="printableArea">
                            <div class="col-md-4">
                                <div class="{{ VC::FM_G }}">
                                    {{ Form::label('warehouse_id', __('Warehouse'), ['class' => VC::FM_LB]) }}
                                    {{ Form::select('warehouse_id', $warehouses, '', ['class' => VC::FM_CT_SL, 'id' => 'warehouse_id', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="{{ VC::FM_G }}" id="product_div">
                                    {{ Form::label('product_id', __('Product'), ['class' => VC::FM_LB]) }}
                                    <select class="{{ VC::FM_CT_SL }}" name="product_id[]" id="product_id" required></select>
                                </div>
                            </div>
                            <div class="{{ VC::FM_G }} col-md-4">
                                {{ Form::label('quantity', __('Quantity'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                                {{ Form::text('quantity', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="{{ VC::CM6 }} pt-4">
                            <button class="{{ VC::BT_SM_PM }} btn-icon" type="submit">
                                {{ __('Print') }}
                            </button>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection


