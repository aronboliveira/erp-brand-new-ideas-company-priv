@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
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
    {{__('Proposal Edit')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:{repeater_unavailable:'تعذّر تهيئة المكرر',sortable_unavailable:'تعذّر تهيئة الفرز بالسحب',multifile_unavailable:'تعذّر تهيئة رافع الملفات',select2_unavailable:'تعذّر تهيئة Select2',customer_unavailable:'تعذّر تحميل تفاصيل العميل',item_unavailable:'تعذّر تحميل بيانات المنتج',delete_unavailable:'تعذّر حذف العنصر'},
            da:{repeater_unavailable:'Kunne ikke initialisere repeater',sortable_unavailable:'Kunne ikke initialisere træk-sortering',multifile_unavailable:'Kunne ikke initialisere filupload',select2_unavailable:'Kunne ikke initialisere Select2',customer_unavailable:'Kunne ikke hente kundedetaljer',item_unavailable:'Kunne ikke hente produktdata',delete_unavailable:'Kunne ikke slette element'},
            de:{repeater_unavailable:'Wiederholer konnte nicht initialisiert werden',sortable_unavailable:'Sortieren per Drag-and-drop fehlgeschlagen',multifile_unavailable:'Dateiupload konnte nicht initialisiert werden',select2_unavailable:'Select2 konnte nicht initialisiert werden',customer_unavailable:'Kundendetails konnten nicht geladen werden',item_unavailable:'Produktdaten konnten nicht geladen werden',delete_unavailable:'Element konnte nicht gelöscht werden'},
            en:{repeater_unavailable:'Cannot init repeater',sortable_unavailable:'Cannot init drag & drop sort',multifile_unavailable:'Cannot init file uploader',select2_unavailable:'Cannot init Select2',customer_unavailable:'Cannot load customer details',item_unavailable:'Cannot load product data',delete_unavailable:'Cannot delete item'},
            es:{repeater_unavailable:'No se puede iniciar el repetidor',sortable_unavailable:'No se puede iniciar el orden por arrastre',multifile_unavailable:'No se puede iniciar el cargador de archivos',select2_unavailable:'No se puede iniciar Select2',customer_unavailable:'No se pueden cargar los datos del cliente',item_unavailable:'No se pueden cargar los datos del producto',delete_unavailable:'No se puede eliminar el elemento'},
            fr:{repeater_unavailable:'Impossible d’initialiser le répéteur',sortable_unavailable:'Impossible d’initialiser le tri par glisser-déposer',multifile_unavailable:'Impossible d’initialiser le téléverseur',select2_unavailable:'Impossible d’initialiser Select2',customer_unavailable:'Impossible de charger les informations client',item_unavailable:'Impossible de charger les données produit',delete_unavailable:'Impossible de supprimer l’élément'},
            he:{repeater_unavailable:'לא ניתן לאתחל Repeater',sortable_unavailable:'לא ניתן לאתחל מיון בגרירה',multifile_unavailable:'לא ניתן לאתחל העלאת קבצים',select2_unavailable:'לא ניתן לאתחל Select2',customer_unavailable:'לא ניתן לטעון פרטי לקוח',item_unavailable:'לא ניתן לטעון נתוני מוצר',delete_unavailable:'לא ניתן למחוק פריט'},
            it:{repeater_unavailable:'Impossibile inizializzare il repeater',sortable_unavailable:'Impossibile inizializzare l’ordinamento drag & drop',multifile_unavailable:'Impossibile inizializzare il caricatore file',select2_unavailable:'Impossibile inizializzare Select2',customer_unavailable:'Impossibile caricare i dettagli cliente',item_unavailable:'Impossibile caricare i dati prodotto',delete_unavailable:'Impossibile eliminare l’elemento'},
            ja:{repeater_unavailable:'リピーターを初期化できません',sortable_unavailable:'ドラッグ並べ替えを初期化できません',multifile_unavailable:'ファイルアップローダーを初期化できません',select2_unavailable:'Select2 を初期化できません',customer_unavailable:'顧客詳細を読み込めません',item_unavailable:'商品データを読み込めません',delete_unavailable:'項目を削除できません'},
            nl:{repeater_unavailable:'Kan repeater niet initialiseren',sortable_unavailable:'Kan slepen-sorteren niet initialiseren',multifile_unavailable:'Kan bestandsuploader niet initialiseren',select2_unavailable:'Kan Select2 niet initialiseren',customer_unavailable:'Kan klantgegevens niet laden',item_unavailable:'Kan productgegevens niet laden',delete_unavailable:'Kan item niet verwijderen'},
            pl:{repeater_unavailable:'Nie można zainicjować repeatera',sortable_unavailable:'Nie można zainicjować sortowania przeciągnij‑i‑upuść',multifile_unavailable:'Nie można zainicjować wysyłania plików',select2_unavailable:'Nie można zainicjować Select2',customer_unavailable:'Nie można wczytać danych klienta',item_unavailable:'Nie można wczytać danych produktu',delete_unavailable:'Nie można usunąć elementu'},
            pt:{repeater_unavailable:'Não foi possível iniciar o repetidor',sortable_unavailable:'Não foi possível iniciar ordenação por arrastar',multifile_unavailable:'Não foi possível iniciar o envio de arquivos',select2_unavailable:'Não foi possível iniciar o Select2',customer_unavailable:'Não foi possível carregar os detalhes do cliente',item_unavailable:'Não foi possível carregar os dados do produto',delete_unavailable:'Não foi possível excluir o item'},
            'pt-br':{repeater_unavailable:'Não foi possível iniciar o repetidor',sortable_unavailable:'Não foi possível iniciar a ordenação por arrastar',multifile_unavailable:'Não foi possível iniciar o envio de arquivos',select2_unavailable:'Não foi possível iniciar o Select2',customer_unavailable:'Não foi possível carregar os detalhes do cliente',item_unavailable:'Não foi possível carregar os dados do produto',delete_unavailable:'Não foi possível excluir o item'},
            ru:{repeater_unavailable:'Не удалось инициализировать повторитель',sortable_unavailable:'Не удалось инициализировать сортировку перетаскиванием',multifile_unavailable:'Не удалось инициализировать загрузчик файлов',select2_unavailable:'Не удалось инициализировать Select2',customer_unavailable:'Не удалось загрузить данные клиента',item_unavailable:'Не удалось загрузить данные товара',delete_unavailable:'Не удалось удалить элемент'},
            tr:{repeater_unavailable:'Tekrarlayıcı başlatılamadı',sortable_unavailable:'Sürükle‑bırak sıralama başlatılamadı',multifile_unavailable:'Dosya yükleyici başlatılamadı',select2_unavailable:'Select2 başlatılamadı',customer_unavailable:'Müşteri ayrıntıları yüklenemedi',item_unavailable:'Ürün verileri yüklenemedi',delete_unavailable:'Öğe silinemedi'},
            zh:{repeater_unavailable:'无法初始化重复器',sortable_unavailable:'无法初始化拖拽排序',multifile_unavailable:'无法初始化文件上传',select2_unavailable:'无法初始化 Select2',customer_unavailable:'无法加载客户详情',item_unavailable:'无法加载商品数据',delete_unavailable:'无法删除项目'}
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
            const DATA_LISTENER_ADDED="data-listener-added";
            const DATA_RENDERED="data-rendered";
            const DATA_BOUND="data-proposal-bound";

            const getLocalized=(el,msgKey)=>{
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

            const showToast=(text)=>{
            const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBs){
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

            const guardError=(el,key)=>showToast(getLocalized(el||document.body,key));

            const routeGuard=(el)=>{
            const url=el?.getAttribute?.('data-url');
            const href=el?.getAttribute?.('action')||el?.getAttribute?.('href');
            return (!url||url==='#') && (!href||href==='#');
            };

            const n=(v)=>{ const x=parseFloat(v); return Number.isFinite(x)?x:0; };

            const $rowOf=(jq)=>jq.closest('tr').length?jq.closest('tr'):jq.closest('.repeater-item').length?jq.closest('.repeater-item'):jq.closest('.row');

            const recalcRowAndTotals=($row)=>{
            if(!$row||!$row.length) return;
            const $q=$row.find('.quantity'), $p=$row.find('.price'), $d=$row.find('.discount'), $rate=$row.find('.itemTaxRate'), $taxPrice=$row.find('.itemTaxPrice'), $amt=$row.find('.amount');
            const qty=n($q.val()), price=n($p.val()), disc=n($d.val());
            const totalItemPrice=(qty*price)-disc;
            const totalItemTaxRate=n($rate.val());
            const itemTaxPrice=(totalItemTaxRate/100)*totalItemPrice;
            $taxPrice.val(itemTaxPrice.toFixed(2));
            $amt.html((totalItemPrice+itemTaxPrice).toFixed(2));
            let totalPrice=0,totalTax=0,totalDisc=0;
            const $prices=$('.price'), $qtys=$('.quantity'), $taxes=$('.itemTaxPrice'), $discounts=$('.discount');
            for(let j=0;j<$prices.length;j++){ totalPrice+=n($prices[j].value)*n($qtys[j]?.value); }
            for(let j=0;j<$taxes.length;j++){ totalTax+=n($taxes[j].value); }
            for(let k=0;k<$discounts.length;k++){ totalDisc+=n($discounts[k].value); }
            $('.subTotal').html(totalPrice.toFixed(2));
            $('.totalTax').html(totalTax.toFixed(2));
            $('.totalAmount').html((totalPrice-totalDisc+totalTax).toFixed(2));
            $('.totalDiscount')?.html?.(totalDisc.toFixed(2));
            };

            try{
            if(typeof $==='undefined'){ console.error('jQuery is required'); return; }
            const body=document.body;
            if(body.getAttribute(DATA_BOUND)==='true') return;
            body.setAttribute(DATA_BOUND,'true');

            const selector='body';
            const $repRoot=$(`${selector} .repeater`);

            if($repRoot.length){
                let $dragAndDrop=null;
                try{
                if($.fn.sortable){ $dragAndDrop=$('body .repeater tbody').sortable({ handle:'.sort-handler' }); }
                else{ guardError($repRoot.get(0),'sortable_unavailable'); }
                }catch{ guardError($repRoot.get(0),'sortable_unavailable'); }

                if($.fn.repeater){
                const $repeater=$(`${selector} .repeater`).repeater({
                    initEmpty:true,
                    defaultValues:{ status:1 },
                    show:function(){
                    try{
                        $(this).slideDown();
                        const $files=$(this).find('input.multi');
                        if($files.length){
                        if($.fn.MultiFile){ $files.MultiFile({ max:3, accept:'png|jpg|jpeg', max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }} }); }
                        else{ guardError($files.get(0)||this,'multifile_unavailable'); }
                        }
                        const $sel=$(this).find('.select2');
                        if($sel.length){
                        if($.fn.select2){ $sel.select2(); }
                        else{ guardError($sel.get(0)||this,'select2_unavailable'); }
                        }
                    }catch{ guardError(this,'repeater_unavailable'); }
                    },
                    hide:function(deleteElement){
                    try{
                        $(this).slideUp(deleteElement);
                        $(this).remove();
                        recalcRowAndTotals($repRoot.find('.repeater-item').first());
                    }catch{ guardError(this,'repeater_unavailable'); }
                    },
                    ready:function(setIndexes){
                    try{ $dragAndDrop && $dragAndDrop.on('drop', setIndexes); }catch{}
                    },
                    isFirstItemUndeletable:true
                });

                let value=$repRoot.attr('data-value');
                if(typeof value!=='undefined'&&value.length){
                    try{
                    value=JSON.parse(value);
                    $repeater.setList && $repeater.setList(value);
                    for(let i=0;i<value.length;i++){
                        const tr=$(`#sortable-table .id[value="${value[i].id}"]`).parent();
                        tr.find('.item').val(value[i].product_id);
                        changeItem(tr.find('.item'));
                    }
                    }catch{}
                }
                }else{
                guardError($repRoot.get(0),'repeater_unavailable');
                }
            }

            $(document).on('change','#customer',function(){
                const el=this;
                try{
                if(routeGuard(el)){ guardError(el,'customer_unavailable'); return; }
                const id=$(this).val(), url=$(this).data('url');
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
                    error:()=>guardError(el,'customer_unavailable')
                });
                }catch{ guardError(el,'customer_unavailable'); }
            });

            $(document).on('click','#remove',function(){
                $('#customer-box').removeClass('d-none').addClass('d-block');
                $('#customer_detail').removeClass('d-block').addClass('d-none');
            });

            const proposal_id='{{$proposal->id}}';

            const changeItem=(element)=>{
                const $el=$(element);
                try{
                if(routeGuard($el.get(0))){ guardError($el.get(0),'item_unavailable'); return; }
                const items_id=$el.val(), url=$el.data('url');
                $.ajax({
                    url:String(url||''),
                    type:'POST',
                    headers:{ 'X-CSRF-TOKEN': $('#token').val() },
                    data:{ product_id: items_id },
                    cache:false,
                    success:(raw)=>{
                    try{
                        const item=(typeof raw==='string')?JSON.parse(raw):raw;
                        $.ajax({
                        url:'{{route(ViewsConstants::PPS . '.items')}}',
                        type:'GET',
                        headers:{ 'X-CSRF-TOKEN': $('#token').val() },
                        data:{ proposal_id, product_id: items_id },
                        cache:false,
                        success:(res)=>{
                            try{
                            const proposalItems=(typeof res==='string')?JSON.parse(res):res;
                            const $row=$rowOf($el);
                            let amount=0;
                            if(proposalItems!=null){
                                amount=(n(proposalItems.price)*n(proposalItems.quantity));
                                $row.find('.quantity').val(proposalItems.quantity);
                                $row.find('.price').val(proposalItems.price);
                                $row.find('.discount').val(proposalItems.discount);
                                $row.find('.pro_description').val(proposalItems.description);
                            }else{
                                $row.find('.quantity').val(1);
                                $row.find('.price').val(item?.product?.sale_price ?? 0);
                                $row.find('.discount').val(0);
                                $row.find('.pro_description').val(item?.product?.description ?? '');
                            }
                            let taxesHTML='', taxIds=[];
                            let totalItemTaxRate=0;
                            (item?.taxes||[]).forEach(t=>{ taxesHTML+=`<span class="badge bg-primary p-2 px-3 rounded mt-1 mr-1">${t.name} (${t.rate}%)</span>`; taxIds.push(t.id); totalItemTaxRate+=n(t.rate); });
                            const discount=n($row.find('.discount').val());
                            const basePrice=proposalItems!=null ? (n(proposalItems.price)*n(proposalItems.quantity)) : (n(item?.product?.sale_price)*1);
                            const itemTaxPrice=(totalItemTaxRate/100)*(basePrice-discount);
                            $row.find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));
                            $row.find('.itemTaxRate').val(totalItemTaxRate.toFixed(2));
                            $row.find('.taxes').html(taxesHTML||'-');
                            $row.find('.tax').val(taxIds);
                            $row.find('.unit').html(item?.unit ?? '');
                            if(proposalItems!=null){ $row.find('.amount').html((amount+itemTaxPrice-discount).toFixed(2)); } else { $row.find('.amount').html((n(item?.totalAmount)+itemTaxPrice).toFixed(2)); }
                            recalcRowAndTotals($row);
                            }catch{ guardError($el.get(0),'item_unavailable'); }
                        },
                        error:()=>guardError($el.get(0),'item_unavailable')
                        });
                    }catch{ guardError($el.get(0),'item_unavailable'); }
                    },
                    error:()=>guardError($el.get(0),'item_unavailable')
                });
                }catch{ guardError($el.get(0),'item_unavailable'); }
            };

            $(document).on('change','.item',function(){ changeItem(this); });

            $(document).on('keyup','.quantity',function(){ try{ recalcRowAndTotals($rowOf($(this))); }catch{} });

            $(document).on('keyup change','.price',function(){ try{ recalcRowAndTotals($rowOf($(this))); }catch{} });

            $(document).on('keyup change','.discount',function(){ try{ recalcRowAndTotals($rowOf($(this))); }catch{} });

            $(document).on('click','[data-repeater-create]',function(){
                try{ $('.item :selected').each(function(){ const id=$(this).val(); $(`.item option[value="${id}"]`).prop('disabled',true); }); }catch{}
            });

            $(document).on('click','[data-repeater-delete]',function(){
                const btn=this;
                try{
                if(window.confirm('Are you sure you want to delete this element?')){
                    const $el=$(btn).parent().parent();
                    const id=$el.find('.id').val();
                    $.ajax({
                    url:'{{route(ViewsConstants::PPS . '.product.destroy')}}',
                    type:'POST',
                    headers:{ 'X-CSRF-TOKEN': $('#token').val() },
                    data:{ id },
                    cache:false,
                    complete:()=>{ $('.price').trigger('change'); $('.discount').trigger('change'); }
                    });
                }
                }catch{ guardError(btn,'delete_unavailable'); }
            });

            }catch(e){ console.error('Initialization failed',e); }
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
    <li class="breadcrumb-item">{{__('Proposal Edit')}}</li>
@endsection
@section('content')
    <div class="row">
        @php
            $proposalUpdateBaseName        = ViewsConstants::PPS . '.update';
            $proposalUpdateKebabName       = Str::kebab($proposalUpdateBaseName);
            $proposalUpdateResolvedName    = Route::has($proposalUpdateBaseName) ? $proposalUpdateBaseName : (Route::has($proposalUpdateKebabName) ? $proposalUpdateKebabName : null);
            $proposalUpdateRouteArray      = $proposalUpdateResolvedName ? [$proposalUpdateResolvedName, $proposal->id] : ['#'];
            $proposalUpdateUrl             = $proposalUpdateResolvedName ? route($proposalUpdateResolvedName, $proposal->id) : '#';
            $proposalUpdateGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_update_route_unavailable') ?? 'Proposal update route is unavailable. Please contact technical support or your domain administrator.';
            $proposalUpdateFormId          = 'proposal-update-form-' . $proposal->id;
        @endphp
        {!! Form::model($proposal, [
            'route'          => $proposalUpdateRouteArray,
            'method'         => 'PUT',
            'class'          => 'w-100',
            'id'             => $proposalUpdateFormId,
            'data-url'       => $proposalUpdateUrl,
            'data-guard-msg' => $proposalUpdateGuardMsg
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
                                            {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}
                                            {{ Form::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}
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
                                        {{--                                        <input class="form-check-input" type="checkbox" name="discount_apply" id="discount_apply" {{$proposal->discount_apply==1?'checked':''}}>--}}
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
                                                @include(ViewsConstants::CST_FD . '.form_builder')
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
                <div class="card repeater" data-value='{!! json_encode($proposal->items) !!}'>
                    <div class="item-section {{ VC::PY2 }}">
                        <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                            <div class="col-md-12 {{ VC::DFL_AIC }} {{ VC::JCB }} justify-content-md-end">
                                <div class="all-button-box me-2">
                                    <a href="#"
                                    data-repeater-create
                                    class="{{ VC::BT_PRM }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#add-bank">
                                        <i class="{{ VC::TI_PLS }}"></i> {{ __('Add Item') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                                <thead>
                                    <tr>
                                        <th>{{__('Items')}}</th>
                                        <th>{{__('Quantity')}}</th>
                                        <th>{{__('Price')}} </th>
                                        <th>{{__('Discount')}}</th>
                                        <th>{{__('Tax')}} (%)</th>

                                        <th class="text-end">{{__('Amount')}} <br><small class="text-danger font-weight-bold">{{__('after tax & discount')}}</small></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="ui-sortable" data-repeater-item>
                                    <tr>
                                        {{ Form::hidden('id',null, array('class' => 'form-control id')) }}
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
                                                {{ Form::text('quantity', null, [
                                                    'class' => VC::FM_CT . ' quantity',
                                                    'required' => 'required',
                                                    'placeholder' => __('Qty'),
                                                ]) }}
                                                <span class="unit {{ VC::TXTS_TRP }}"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="{{ VC::FM_G }} price-input input-group search-form">
                                                {{ Form::text('price', null, [
                                                    'class' => VC::FM_CT . ' price',
                                                    'required' => 'required',
                                                    'placeholder' => __('Price'),
                                                ]) }}
                                                <span class="{{ VC::TXTS_TRP }}">{{ $user?->currencySymbol() }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="{{ VC::FM_G }} price-input input-group search-form">
                                                {{ Form::text('discount', null, [
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
                                        <td class="text-end amount">0.00</td>
                                        <td>
                                            @can('delete proposal product')
                                                <a href="#" class="{{ VC::TRS_M2 }}" data-repeater-delete></a>
                                            @endcan
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="form-group">
                                                {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                            </div>
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                <tr>
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
                                    <td class="text-end totalAmount blue-text border-none">0.00</td>
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
                <input type="submit" value="{{__('Update')}}" class="{{ VC::BT_PRM }}">
            </div>
        {{ Form::close() }}
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const link = document.getElementById('proposal-index-link');
            if (!link || link.getAttribute('data-listener-active') === 'true') return;
            link.setAttribute('data-listener-active', 'true');
            link.addEventListener('click', e => {
                try {
                    const url = link.getAttribute('data-url') || '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = link.getAttribute('data-guard-msg') || '# ERROR';
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
                    link.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        })();
    </script>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $proposalUpdateFormId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
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
                    form.setAttribute('data-failed-route', 'true');
                } catch (error) {}
            });
        })();
    </script>
@endpush