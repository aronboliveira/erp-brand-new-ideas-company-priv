@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
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
    <li class="breadcrumb-item"><a href="{{route(ViewsConstans::POS.'.barcode')}}">{{__('POS Product Barcode')}}</a></li>
    <li class="breadcrumb-item">{{__('POS Barcode Print')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async>
        window.translations = {
            ar:{pos_fetch_unavailable:"تعذّر جلب المنتجات من المستودع",copy_unavailable:"تعذّر نسخ الرابط",copy_success:"تم نسخ الرابط إلى الحافظة",pdf_unavailable:"تعذّر إنشاء ملف PDF"},
            da:{pos_fetch_unavailable:"Kunne ikke hente produkter fra lageret",copy_unavailable:"Kunne ikke kopiere linket",copy_success:"Link kopieret til udklipsholder",pdf_unavailable:"Kunne ikke generere PDF"},
            de:{pos_fetch_unavailable:"Produkte konnten nicht aus dem Lager abgerufen werden",copy_unavailable:"Link konnte nicht kopiert werden",copy_success:"Link in die Zwischenablage kopiert",pdf_unavailable:"PDF konnte nicht erstellt werden"},
            en:{pos_fetch_unavailable:"Could not fetch products for this warehouse",copy_unavailable:"Could not copy the link",copy_success:"Link copied to clipboard",pdf_unavailable:"Could not generate the PDF"},
            es:{pos_fetch_unavailable:"No se pudieron obtener productos del almacén",copy_unavailable:"No se pudo copiar el enlace",copy_success:"Enlace copiado al portapapeles",pdf_unavailable:"No se pudo generar el PDF"},
            fr:{pos_fetch_unavailable:"Impossible de récupérer les produits de l’entrepôt",copy_unavailable:"Impossible de copier le lien",copy_success:"Lien copié dans le presse-papiers",pdf_unavailable:"Impossible de générer le PDF"},
            he:{pos_fetch_unavailable:"לא ניתן לאחזר מוצרים מהמחסן",copy_unavailable:"לא ניתן להעתיק את הקישור",copy_success:"הקישור הועתק ללוח",pdf_unavailable:"לא ניתן ליצור קובץ PDF"},
            it:{pos_fetch_unavailable:"Impossibile recuperare i prodotti dal magazzino",copy_unavailable:"Impossibile copiare il link",copy_success:"Link copiato negli appunti",pdf_unavailable:"Impossibile generare il PDF"},
            ja:{pos_fetch_unavailable:"倉庫の商品を取得できませんでした",copy_unavailable:"リンクをコピーできませんでした",copy_success:"リンクをクリップボードにコピーしました",pdf_unavailable:"PDF を生成できませんでした"},
            nl:{pos_fetch_unavailable:"Producten konden niet uit het magazijn worden opgehaald",copy_unavailable:"Link kon niet worden gekopieerd",copy_success:"Link gekopieerd naar klembord",pdf_unavailable:"PDF kon niet worden gegenereerd"},
            pl:{pos_fetch_unavailable:"Nie udało się pobrać produktów z magazynu",copy_unavailable:"Nie można skopiować linku",copy_success:"Link skopiowano do schowka",pdf_unavailable:"Nie udało się wygenerować PDF"},
            pt:{pos_fetch_unavailable:"Não foi possível buscar os produtos do armazém",copy_unavailable:"Não foi possível copiar o link",copy_success:"Link copiado para a área de transferência",pdf_unavailable:"Não foi possível gerar o PDF"},
            "pt-br":{pos_fetch_unavailable:"Não foi possível buscar os produtos do armazém",copy_unavailable:"Não foi possível copiar o link",copy_success:"Link copiado para a área de transferência",pdf_unavailable:"Não foi possível gerar o PDF"},
            ru:{pos_fetch_unavailable:"Не удалось получить товары со склада",copy_unavailable:"Не удалось скопировать ссылку",copy_success:"Ссылка скопирована в буфер обмена",pdf_unavailable:"Не удалось создать PDF"},
            tr:{pos_fetch_unavailable:"Depodan ürünler alınamadı",copy_unavailable:"Bağlantı kopyalanamadı",copy_success:"Bağlantı panoya kopyalandı",pdf_unavailable:"PDF oluşturulamadı"},
            zh:{pos_fetch_unavailable:"无法从仓库获取产品",copy_unavailable:"无法复制链接",copy_success:"链接已复制到剪贴板",pdf_unavailable:"无法生成 PDF"}
        };
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
            const POS_URL='{{route('pos.getproduct')}}';

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
    <div class="float-end">
        <a href="{{ route(ViewsConstans::POS.'.barcode') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Back')}}">
            <i class="ti ti-arrow-left text-white"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{Collective\Html\FormFacade::open(array('route'=>ViewsConstans::POS.'.receipt','method'=>'post'))}}
                        <div class="row" id="printableArea">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{Collective\Html\FormFacade::label('warehouse_id',__('Warehouse'),['class'=>'form-label'])}}
                                    {{ Collective\Html\FormFacade::select('warehouse_id', $warehouses,'', array('class' => 'form-control select','id'=>'warehouse_id','required'=>'required')) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" id="product_div">
                                    {{Collective\Html\FormFacade::label('product_id',__('Product'),['class'=>'form-label'])}}
                                    <select class="form-control select" name="product_id[]" id="product_id" required >
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Collective\Html\FormFacade::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                {{ Collective\Html\FormFacade::text('quantity',null, array('class' => 'form-control','required'=>'required')) }}
                            </div>
                        </div>
                        <div class="col-md-6 pt-4">
                            <button class="btn btn-sm btn-primary btn-icon" type="submit">{{__('Print')}}</button>
                        </div>
                    {{Collective\Html\FormFacade::close()}}
                </div>
            </div>
        </div>
    </div>
@endsection

