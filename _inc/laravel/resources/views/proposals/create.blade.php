@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $proposalIndexBaseName = ViewsConstants::PPS . '.index';
    $proposalIndexKebabName = Str::kebab($proposalIndexBaseName);
    $proposalIndexResolvedName = Route::has($proposalIndexBaseName) ? $proposalIndexBaseName : (Route::has($proposalIndexKebabName) ? $proposalIndexKebabName : null);
    $proposalIndexUrl = $proposalIndexResolvedName ? route($proposalIndexResolvedName) : '#';
    $proposalIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_index_route_unavailable') ?? 'Proposal index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Proposal Create')}}
@endsection
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
    <li class="breadcrumb-item">{{__('Proposal Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script async>
        window.translations={
            ar:{repeater_unavailable:'تعذّر تهيئة المكرر',sortable_unavailable:'تعذّر تهيئة الفرز بالسحب',multifile_unavailable:'تعذّر تهيئة رفع الملفات',select2_unavailable:'تعذّر تهيئة Select2',customer_unavailable:'تعذّر تحميل تفاصيل العميل',item_unavailable:'تعذّر تحميل بيانات المنتج'},
            da:{repeater_unavailable:'Kunne ikke initialisere repeater',sortable_unavailable:'Kunne ikke initialisere træk-og-slip sortering',multifile_unavailable:'Kunne ikke initialisere filupload',select2_unavailable:'Kunne ikke initialisere Select2',customer_unavailable:'Kunne ikke hente kundedetaljer',item_unavailable:'Kunne ikke hente produktdata'},
            de:{repeater_unavailable:'Wiederholer konnte nicht initialisiert werden',sortable_unavailable:'Sortieren per Drag & Drop fehlgeschlagen',multifile_unavailable:'Datei-Upload konnte nicht initialisiert werden',select2_unavailable:'Select2 konnte nicht initialisiert werden',customer_unavailable:'Kundendetails konnten nicht geladen werden',item_unavailable:'Produktdaten konnten nicht geladen werden'},
            en:{repeater_unavailable:'Cannot init repeater',sortable_unavailable:'Cannot init drag & drop sort',multifile_unavailable:'Cannot init file uploader',select2_unavailable:'Cannot init Select2',customer_unavailable:'Cannot load customer details',item_unavailable:'Cannot load product data'},
            es:{repeater_unavailable:'No se puede iniciar el repetidor',sortable_unavailable:'No se puede iniciar el orden por arrastre',multifile_unavailable:'No se puede iniciar el cargador de archivos',select2_unavailable:'No se puede iniciar Select2',customer_unavailable:'No se pueden cargar los detalles del cliente',item_unavailable:'No se pueden cargar los datos del producto'},
            fr:{repeater_unavailable:'Impossible d’initialiser le répéteur',sortable_unavailable:'Impossible d’initialiser le tri par glisser-déposer',multifile_unavailable:'Impossible d’initialiser le téléversement',select2_unavailable:'Impossible d’initialiser Select2',customer_unavailable:'Impossible de charger les détails du client',item_unavailable:'Impossible de charger les données du produit'},
            he:{repeater_unavailable:'לא ניתן לאתחל Repeater',sortable_unavailable:'לא ניתן לאתחל מיון גרירה',multifile_unavailable:'לא ניתן לאתחל העלאת קבצים',select2_unavailable:'לא ניתן לאתחל Select2',customer_unavailable:'לא ניתן לטעון פרטי לקוח',item_unavailable:'לא ניתן לטעון נתוני מוצר'},
            it:{repeater_unavailable:'Impossibile inizializzare il repeater',sortable_unavailable:'Impossibile inizializzare l’ordinamento drag & drop',multifile_unavailable:'Impossibile inizializzare il caricatore file',select2_unavailable:'Impossibile inizializzare Select2',customer_unavailable:'Impossibile caricare i dettagli del cliente',item_unavailable:'Impossibile caricare i dati del prodotto'},
            ja:{repeater_unavailable:'リピーターを初期化できません',sortable_unavailable:'ドラッグ＆ドロップ並べ替えを初期化できません',multifile_unavailable:'ファイルアップローダーを初期化できません',select2_unavailable:'Select2 を初期化できません',customer_unavailable:'顧客詳細を読み込めません',item_unavailable:'商品データを読み込めません'},
            nl:{repeater_unavailable:'Kan repeater niet initialiseren',sortable_unavailable:'Kan slepen-sorteren niet initialiseren',multifile_unavailable:'Kan bestandsuploader niet initialiseren',select2_unavailable:'Kan Select2 niet initialiseren',customer_unavailable:'Kan klantdetails niet laden',item_unavailable:'Kan productgegevens niet laden'},
            pl:{repeater_unavailable:'Nie można zainicjować repeatera',sortable_unavailable:'Nie można zainicjować sortowania „przeciągnij i upuść”',multifile_unavailable:'Nie można zainicjować przesyłania plików',select2_unavailable:'Nie można zainicjować Select2',customer_unavailable:'Nie można wczytać danych klienta',item_unavailable:'Nie można wczytać danych produktu'},
            pt:{repeater_unavailable:'Não foi possível iniciar o repetidor',sortable_unavailable:'Não foi possível iniciar a ordenação por arrastar',multifile_unavailable:'Não foi possível iniciar o envio de arquivos',select2_unavailable:'Não foi possível iniciar o Select2',customer_unavailable:'Não foi possível carregar os detalhes do cliente',item_unavailable:'Não foi possível carregar os dados do produto'},
            'pt-br':{repeater_unavailable:'Não foi possível iniciar o repetidor',sortable_unavailable:'Não foi possível iniciar a ordenação por arrastar',multifile_unavailable:'Não foi possível iniciar o envio de arquivos',select2_unavailable:'Não foi possível iniciar o Select2',customer_unavailable:'Não foi possível carregar os detalhes do cliente',item_unavailable:'Não foi possível carregar os dados do produto'},
            ru:{repeater_unavailable:'Не удалось инициализировать повторитель',sortable_unavailable:'Не удалось инициализировать сортировку перетаскиванием',multifile_unavailable:'Не удалось инициализировать загрузчик файлов',select2_unavailable:'Не удалось инициализировать Select2',customer_unavailable:'Не удалось загрузить данные клиента',item_unavailable:'Не удалось загрузить данные товара'},
            tr:{repeater_unavailable:'Tekrarlayıcı başlatılamadı',sortable_unavailable:'Sürükle-bırak sıralama başlatılamadı',multifile_unavailable:'Dosya yükleyici başlatılamadı',select2_unavailable:'Select2 başlatılamadı',customer_unavailable:'Müşteri detayları yüklenemedi',item_unavailable:'Ürün verileri yüklenemedi'},
            zh:{repeater_unavailable:'无法初始化重复器',sortable_unavailable:'无法初始化拖拽排序',multifile_unavailable:'无法初始化文件上传',select2_unavailable:'无法初始化 Select2',customer_unavailable:'无法加载客户详情',item_unavailable:'无法加载商品数据'}
        };
    </script>
    <script defer>
        (()=>{
            const ERR_FB='# ERROR';
            const DATA_CLIENT_LOCALIZED='data-client-localized';
            const DATA_GUARD_MSG='data-guard-msg';
            const DATA_LISTENER_ADDED='data-listener-added';
            const DATA_RENDERED='data-rendered';

            const getMsg=(el,key)=>{
            let msg=ERR_FB;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
                msg=el.getAttribute(DATA_GUARD_MSG)||ERR_FB;
            }else{
                let lang=(window.sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang=lang==='pt-br'?lang:lang.slice(0,2);
                const m=window.translations?.[lang]?.[key]||el?.getAttribute(DATA_GUARD_MSG)||window.translations?.en?.[key]||ERR_FB;
                if(m!==ERR_FB){ el?.setAttribute(DATA_GUARD_MSG,m); el?.setAttribute(DATA_CLIENT_LOCALIZED,'true'); }
                msg=m;
            }
            return msg;
            };

            const showToast=(text)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(text); }
            };

            const guardOnce=(el,key,ev='pointerup')=>{
            if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showToast(getMsg(el,key));
            el.addEventListener(ev,handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const routeGuard=(el)=>{
            const url=el?.getAttribute?.('data-url');
            const href=el?.getAttribute?.('action')||el?.getAttribute?.('href');
            return (!url||url==='#')&&(!href||href==='#');
            };

            const n=(v)=>{ const x=parseFloat(v); return Number.isFinite(x)?x:0; };

            const rowRoot=($el)=>$el.closest('tr').length?$el.closest('tr'):$el.closest('.repeater-item').length?$el.closest('.repeater-item'):$el.closest('.row');

            const renderBadges=(taxes)=>{
            if(!Array.isArray(taxes)||!taxes.length) return '-';
            return taxes.map(t=>`<span class="badge bg-primary mt-1 mr-2">${t.name} (${t.rate}%)</span>`).join('');
            };

            const recalcRowAndTotals=($row)=>{
            if(!$row||!$row.length) return;
            const $q=$row.find('.quantity'); const $p=$row.find('.price'); const $d=$row.find('.discount'); const $rate=$row.find('.itemTaxRate'); const $taxPrice=$row.find('.itemTaxPrice'); const $amt=$row.find('.amount');
            const qty=n($q.val()); const price=n($p.val()); const disc=n($d.val()); const totalItemPrice=(qty*price)-disc; const totalItemTaxRate=n($rate.val()); const itemTaxPrice=(totalItemTaxRate/100)*totalItemPrice;
            $taxPrice.val(itemTaxPrice.toFixed(2)); $amt.html((totalItemPrice+itemTaxPrice).toFixed(2));
            let totalItemPriceSum=0, totalItemTaxPriceSum=0, totalItemDiscountSum=0;
            const $prices=$('.price'); const $qtys=$('.quantity'); const $taxes=$('.itemTaxPrice'); const $discounts=$('.discount');
            for(let j=0;j<$prices.length;j++){ totalItemPriceSum+=n($prices[j].value)*n($qtys[j]?.value); }
            for(let j=0;j<$taxes.length;j++){ totalItemTaxPriceSum+=n($taxes[j].value); }
            for(let k=0;k<$discounts.length;k++){ totalItemDiscountSum+=n($discounts[k].value); }
            $('.subTotal').html(totalItemPriceSum.toFixed(2));
            $('.totalTax').html(totalItemTaxPriceSum.toFixed(2));
            $('.totalAmount').html((totalItemPriceSum-totalItemDiscountSum+totalItemTaxPriceSum).toFixed(2));
            $('.totalDiscount')?.html?.(totalItemDiscountSum.toFixed(2));
            };

            try{
            if(typeof $==='undefined'){ console.error('jQuery is required'); return; }

            $(()=>{

                const selector='body';
                const $repRoot=$(`${selector} .repeater`);

                if($repRoot.length){
                let $dragAndDrop=null;
                if($.fn.sortable){ $dragAndDrop=$('body .repeater tbody').sortable({ handle:'.sort-handler' }); }
                else{ guardOnce($repRoot.get(0),'sortable_unavailable','click'); }

                if($.fn.repeater){
                    const $repeater=$(`${selector} .repeater`).repeater({
                    initEmpty:false,
                    defaultValues:{ status:1 },
                    show:function(){
                        try{
                        $(this).slideDown();
                        const $files=$(this).find('input.multi');
                        if($files.length){
                            if($.fn.MultiFile){ $files.MultiFile({ max:3, accept:'png|jpg|jpeg', max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }} }); }
                            else{ guardOnce($files.get(0)||this,'multifile_unavailable','click'); }
                        }
                        const $sel=$(this).find('.select2');
                        if($sel.length){
                            if($.fn.select2){ $sel.select2(); }
                            else{ guardOnce($sel.get(0)||this,'select2_unavailable','click'); }
                        }
                        }catch{ guardOnce(this,'repeater_unavailable','click'); }
                    },
                    hide:function(deleteElement){
                        try{
                        if(window.confirm('Are you sure you want to delete this element?')){
                            $(this).slideUp(deleteElement); $(this).remove();
                            recalcRowAndTotals($repRoot.find('.repeater-item').first());
                        }
                        }catch{ guardOnce(this,'repeater_unavailable','click'); }
                    },
                    ready:function(setIndexes){
                        try{ $dragAndDrop && $dragAndDrop.on('drop', setIndexes); }catch{ /* no-op */ }
                    },
                    isFirstItemUndeletable:true
                    });

                    let value=$repRoot.attr('data-value');
                    if(typeof value!=='undefined'&&value.length){
                    try{ value=JSON.parse(value); $repeater.setList && $repeater.setList(value); }catch{ /* ignore bad JSON */ }
                    }
                }else{
                    guardOnce($repRoot.get(0),'repeater_unavailable','click');
                }
                }

                $(document).on('change','#customer',function(){
                try{
                    const el=this;
                    if(routeGuard(el)){ guardOnce(el,'customer_unavailable','pointerup'); return; }
                    const id=$(this).val(); const url=$(this).data('url');
                    $.ajax({
                    url:String(url||''),
                    type:'POST',
                    headers:{ 'X-CSRF-TOKEN': $('#token').val() },
                    data:{ id },
                    cache:false,
                    success:(data)=>{
                        if(data!=null && data!==''){ $('#customer_detail').removeClass('d-none').addClass('d-block').html(data); $('#customer-box').removeClass('d-block').addClass('d-none'); }
                        else{ $('#customer-box').removeClass('d-none').addClass('d-block'); $('#customer_detail').removeClass('d-block').addClass('d-none'); }
                    },
                    error:()=>guardOnce(el,'customer_unavailable','pointerup')
                    });
                }catch{ guardOnce(this,'customer_unavailable','pointerup'); }
                });

                $(document).on('click','#remove',function(){
                $('#customer-box').removeClass('d-none').addClass('d-block');
                $('#customer_detail').removeClass('d-block').addClass('d-none');
                });

                $(document).on('change','.item',function(){
                try{
                    const el=this;
                    if(routeGuard(el)){ guardOnce(el,'item_unavailable','pointerup'); return; }
                    const iteams_id=$(this).val(); const url=$(this).data('url'); const $el=$(this);
                    $.ajax({
                    url:String(url||''),
                    type:'POST',
                    headers:{ 'X-CSRF-TOKEN': $('#token').val() },
                    data:{ product_id: iteams_id },
                    cache:false,
                    success:(data)=>{
                        try{
                        const item=(typeof data==='string')?JSON.parse(data):data;
                        const $row=rowRoot($el);
                        $row.find('.quantity').val(1);
                        $row.find('.price').val(item?.product?.sale_price ?? 0);
                        $row.find('.pro_description').val(item?.product?.description ?? '');
                        const taxesArr=Array.isArray(item?.taxes)?item.taxes:[];
                        const totalItemTaxRate=taxesArr.reduce((s,t)=>s+n(t.rate),0);
                        const itemTaxPrice=(totalItemTaxRate/100)*(n(item?.product?.sale_price)*1);
                        $row.find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));
                        $row.find('.itemTaxRate').val(totalItemTaxRate.toFixed(2));
                        $row.find('.taxes').html(taxesArr.length?renderBadges(taxesArr):'-');
                        $row.find('.tax').val(taxesArr.map(t=>t.id));
                        $row.find('.unit').html(item?.unit ?? '');
                        $row.find('.discount').val(0);
                        recalcRowAndTotals($row);
                        }catch{ guardOnce(el,'item_unavailable','pointerup'); }
                    },
                    error:()=>guardOnce(el,'item_unavailable','pointerup')
                    });
                }catch{ guardOnce(this,'item_unavailable','pointerup'); }
                });

                $(document).on('keyup','.quantity',function(){ try{ recalcRowAndTotals(rowRoot($(this))); }catch{} });
                $(document).on('keyup change','.price',function(){ try{ recalcRowAndTotals(rowRoot($(this))); }catch{} });
                $(document).on('keyup change','.discount',function(){ try{ recalcRowAndTotals(rowRoot($(this))); }catch{} });

                const customer_id='{{$customer_id}}';
                if(n(customer_id)>0){ $('#customer').val(customer_id).trigger('change'); }

                $(document).on('click','[data-repeater-delete]',function(){ try{ $('.price').trigger('change'); $('.discount').trigger('change'); }catch{} });

            });

            }catch(e){ console.error('Initialization failed',e); }
        })();
    </script>
@endpush
@section('content')
    <div class="row">
    @php
        $proposalStoreBaseName = ViewsConstants::PPS;
        $proposalStoreKebabName = Str::kebab($proposalStoreBaseName);
        $proposalStoreResolvedName = Route::has($proposalStoreBaseName) ? $proposalStoreBaseName : (Route::has($proposalStoreKebabName) ? $proposalStoreKebabName : null);
        $proposalStoreUrl = $proposalStoreResolvedName ? route($proposalStoreResolvedName) : '#';
        $proposalStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_store_route_unavailable') ?? 'Proposal store route is unavailable. Please contact technical support or your domain administrator.';
        $proposalStoreFormId = 'proposal-store-form';
    @endphp
    {!! Form::open([
        'url'            => $proposalStoreUrl,
        'class'          => 'w-100',
        'id'             => $proposalStoreFormId,
        'data-url'       => $proposalStoreUrl,
        'data-guard-msg' => $proposalStoreGuardMsg
    ]) !!}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="customer-box">
                                {{ Form::label('customer_id', __('Customer'),['class'=>'form-label']) }}
                                @php
                                    $proposalCustomerBaseName = ViewsConstants::PPS . '.customer';
                                    $proposalCustomerKebabName = Str::kebab($proposalCustomerBaseName);
                                    $proposalCustomerResolvedName = Route::has($proposalCustomerBaseName) ? $proposalCustomerBaseName : (Route::has($proposalCustomerKebabName) ? $proposalCustomerKebabName : null);
                                    $proposalCustomerUrl = $proposalCustomerResolvedName ? route($proposalCustomerResolvedName) : '#';
                                    $proposalCustomerGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_customer_route_unavailable') ?? 'Proposal customer route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                {{ Form::select(
                                    'customer_id',
                                    $customers,
                                    null,
                                    array(
                                        'class'          => 'form-control select ',
                                        'id'             => 'customer',
                                        'data-url'       => $proposalCustomerUrl,
                                        'data-guard-msg' => $proposalCustomerGuardMsg,
                                        'required'       => 'required'
                                    )
                                ) }}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const el = document.getElementById('customer');
                                            if (!el || el.getAttribute('data-change-listener-active') === 'true') return;
                                            el.setAttribute('data-change-listener-active', 'true');
                                            el.addEventListener('change', e => {
                                                try {
                                                    const url = el.getAttribute('data-url') || '#';
                                                    if (url !== '#') return;
                                                    const msg = el.getAttribute('data-guard-msg') || '# ERROR';
                                                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bs) {
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
                                                    el.setAttribute('data-failed-route', 'true');
                                                } catch (error) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                            </div>
                            <div id="customer_detail" class="d-none">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}
                                        <div class="form-icon-user">
                                            {{Form::date('issue_date',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}
                                        {{ Form::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('proposal_number', __('Proposal Number'),['class'=>'form-label']) }}
                                        <div class="form-icon-user">
                                            <input type="text" class="form-control" value="{{$proposal_number}}" readonly>
                                        </div>
                                    </div>
                                </div>
                                    {{--                                <div class="col-md-6">--}}
                                    {{--                                    <div class="form-check custom-checkbox mt-4">--}}
                                    {{--                                        <input class="form-check-input" type="checkbox" name="discount_apply" id="discount_apply">--}}
                                    {{--                                        <label class="form-check-label " for="discount_apply">{{__('Discount Apply')}}</label>--}}
                                    {{--                                    </div>--}}
                                    {{--                                </div>--}}


                                    {{--                                <div class="col-md-6">--}}
                                    {{--                                    <div class="form-group">--}}
                                    {{--                                        {{Form::label('sku',__('SKU')) }}--}}
                                    {{--                                        {!!Form::text('sku', null,array('class' => 'form-control','required'=>'required')) !!}--}}
                                    {{--                                    </div>--}}
                                    {{--                                </div>--}}
                                @if(!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include(ViewsConstants::CST_FD . '.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class="d-inline-block mb-4">{{__('Product & Services')}}</h5>
            <div class="card repeater">
                <div class="item-section py-2">
                    <div class="{{ VC::DFL_AIC }} {{ VC::FEND }} me-2">
                        <a href="#"
                        data-repeater-create
                        class="{{ VC::BT_PRM }} mb-2"
                        data-bs-toggle="modal"
                        data-bs-target="#add-bank">
                            <i class="{{ VC::TI_PLS }}"></i> {{ __('Add item') }}
                        </a>
                    </div>
                    <div class="card-body mt-3">
                        <div class="table-responsive">
                            <table class="table mb-0" data-repeater-list="items">
                                <thead>
                                <tr>
                                    <th>{{__('Items')}}</th>
                                    <th>{{__('Quantity')}}</th>
                                    <th>{{__('Price')}} </th>
                                    <th>{{__('Discount')}}</th>
                                    <th>{{__('Tax')}} (%)</th>

                                    <th class="text-end">{{__('Amount')}} <br>
                                        <small class="text-danger font-weight-bold">{{__('after tax & discount')}}</small>
                                    </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    <td width="25%" class="form-group pt-0">
                                        @php
                                            $proposalProductBaseName = ViewsConstants::PPS . '.product';
                                            $proposalProductKebabName = Str::kebab($proposalProductBaseName);
                                            $proposalProductResolvedName = Route::has($proposalProductBaseName) ? $proposalProductBaseName : (Route::has($proposalProductKebabName) ? $proposalProductKebabName : null);
                                            $proposalProductUrl = $proposalProductResolvedName ? route($proposalProductResolvedName) : '#';
                                            $proposalProductGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_product_route_unavailable') ?? 'Proposal product route is unavailable. Please contact technical support or your domain administrator.';
                                            $proposalItemSelectId = 'proposal-item-select';
                                        @endphp
                                        {{ Form::select(
                                            'item',
                                            $product_services,
                                            null,
                                            array(
                                                'class'          => 'form-control select item',
                                                'id'             => $proposalItemSelectId,
                                                'data-url'       => $proposalProductUrl,
                                                'data-guard-msg' => $proposalProductGuardMsg
                                            )
                                        ) }}
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const el = document.getElementById('{{ $proposalItemSelectId }}');
                                                    if (!el || el.getAttribute('data-change-listener-active') === 'true') return;
                                                    el.setAttribute('data-change-listener-active', 'true');
                                                    el.addEventListener('change', e => {
                                                        try {
                                                            const url = el.getAttribute('data-url') || '#';
                                                            if (url !== '#') return;
                                                            const msg = el.getAttribute('data-guard-msg') || '# ERROR';
                                                            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bs) {
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
                                                            el.setAttribute('data-failed-route', 'true');
                                                        } catch (error) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    </td>
                                    <td>
                                        <div class="{{ VC::FM_G }} price-input input-group search-form">
                                            {{ Form::text('quantity', '', [
                                                'class' => VC::FM_CT . ' quantity',
                                                'required' => 'required',
                                                'placeholder' => __('Qty'),
                                            ]) }}
                                            <span class="unit {{ VC::TXTS_TRP }}"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="{{ VC::FM_G }} price-input input-group search-form">
                                            {{ Form::text('price', '', [
                                                'class' => VC::FM_CT . ' price',
                                                'required' => 'required',
                                                'placeholder' => __('Price'),
                                            ]) }}
                                            <span class="{{ VC::TXTS_TRP }}">{{ $user?->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="{{ VC::FM_G }} price-input input-group search-form">
                                            {{ Form::text('discount', '', [
                                                'class' => VC::FM_CT . ' discount',
                                                'required' => 'required',
                                                'placeholder' => __('Discount'),
                                            ]) }}
                                            <span class="{{ VC::TXTS_TRP }}">{{ $user?->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="{{ VC::FM_G }}">
                                            <div class="input-group">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => VC::FM_CT . ' tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => VC::FM_CT . ' itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => VC::FM_CT . ' itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end amount">
                                        0.00
                                    </td>
                                    <td>
                                        <a href="#" class="{{ VC::TI_TRS_WT }} text-danger" data-repeater-delete></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="form-group">
                                            {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'1','placeholder'=>__('Description')]) }}
                                        </div>
                                    </td>
                                    <td colspan="5"></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="border-none">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{__('Sub Total')}} ({{$user?->currencySymbol()}})</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{__('Discount')}} ({{$user?->currencySymbol()}})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{__('Tax')}} ({{$user?->currencySymbol()}})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td class="blue-text border-none"><strong>{{__('Total Amount')}} ({{$user?->currencySymbol()}})</strong></td>
                                    <td class="text-end totalAmount blue-text border-none"></td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @php
                    $proposalIndexBaseName = ViewsConstants::PPS . '.index';
                    $proposalIndexKebabName = Str::kebab($proposalIndexBaseName);
                    $proposalIndexResolvedName = Route::has($proposalIndexBaseName) ? $proposalIndexBaseName : (Route::has($proposalIndexKebabName) ? $proposalIndexKebabName : null);
                    $proposalIndexUrl = $proposalIndexResolvedName ? route($proposalIndexResolvedName) : '#';
                    $proposalIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_index_route_unavailable') ?? 'Proposal index route is unavailable. Please contact technical support or your domain administrator.';
                    $proposalIndexBtnId = 'proposal-index-cancel-btn';
                @endphp
                <input
                    type="button"
                    value="{{ __('Cancel') }}"
                    id="{{ $proposalIndexBtnId }}"
                    class="{{ VC::BT_LG }}"
                    data-url="{{ $proposalIndexUrl }}"
                    data-guard-msg="{{ $proposalIndexGuardMsg }}"
                >
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            const btn = document.getElementById('{{ $proposalIndexBtnId }}');
                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                            btn.setAttribute('data-listener-active', 'true');
                            btn.addEventListener('click', e => {
                                try {
                                    const url = btn.getAttribute('data-url') || '#';
                                    if (url === '#') {
                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
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
                                        btn.setAttribute('data-failed-route', 'true');
                                        return;
                                    }
                                    window.location.assign(url);
                                } catch (err) {}
                            });
                        })();
                    </script>
                @endpush
                <input type="submit" value="{{__('Create')}}" class="{{ VC::BT_PRM }}">
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $proposalStoreFormId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    const action = form.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
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
                    form.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        })();
    </script>
@endpush

