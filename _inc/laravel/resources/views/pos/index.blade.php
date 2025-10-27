@php
	use App\Config\Constants\{
		DatabaseConstants,
		SettingsConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants as VW
	};
	use App\Models\{Utility, ProductService};
	use Illuminate\Support\Facades\{Auth, Log, Route, Storage};
	use Illuminate\Support\{Collection};
	use Collective\Html\FormFacade as Form;

	$data ??= [];
	$logo ??= '';
	$company_favicon ??= '';
	$siteRtl ??= false;
	$colorSettings ??= [];
	$color ??= '';
	$faviconUrl ??= '';
	$user = Auth::user();
	$lang = Utility::fetchUserLang(user: $user);

	try {
		$data = Utility::prepareCommonViewData() ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$siteRtl = $data[SettingsConstants::RTL] ?? false;
		$colorSettings = $data[SettingsConstants::CLR_STG] ?? [];
		$color = $data[SettingsConstants::THM_CLR] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error('Error fetching view data', ['exception_class'=>get_class($e),'message'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
	} catch (\Exception $e) {
		Log::error('Exception fetching view data', ['exception_class'=>get_class($e),'message'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
	} catch (\Throwable $e) {
		Log::error('Throwable fetching view data', ['exception_class'=>get_class($e),'message'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
	}
	$data = Utility::fallbackSettings($data);
	$canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
	$msg = fn(string $ns,string $key,string $fb) => ($canFetchMsg ? (Utility::fetchLinkMessage($lang,$ns,$key) ?? null) : null) ?? __($fb);
	$lastsegment    = request()->segment(count(request()->segments()));
	$sessionCart    = session($lastsegment);
	$cartHasItems   = Utility::isFilled($sessionCart ?? []);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ?? (str_replace('_','-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ data_get($companySettings,'header_text.value',config('app.name','ERP Nova Prestech')) }} - {{ __('POS') }}</title>
        @include('fragments.std', ['meta_title'=>$meta_title ?? '', 'meta_desc'=>$meta_desc ?? '', 'meta_vp'=>'shrink-to-fit-no'])
        @include('fragments.favicon', ['faviconUrl'=>$faviconUrl])
        @include('fragments.stylesheets', ['settings'=>$colorSettings])
        <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        @if ($siteRtl == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        @if(in_array($color, ['theme-2', 'theme-3', 'theme-4']))
            <link rel="stylesheet" href="{{ asset('assets/css/routes/pos/theme-'.substr($color, -1).'.css') }}" />
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/routes/pos/theme-1.css') }}" />
        @endif
        @stack('css-page')
    </head>
    <body class="{{ $color }}">
        <div class="container-fluid {{ VC::PX3 }}">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="mt-2 pos-top-bar bg-color {{ VC::DFL }} {{ VC::JCB }} {{ VC::BG_P }}">
                        <span class="{{ VC::TXT_WT }}">{{ __('POS') }}</span>
                        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" class="{{ VC::TXT_WT }}"><i class="{{ VC::TI }} {{ VC::TI_HM }}" style="font-size:20px;"></i></a>
                    </div>
                </div>
            </div>

            <div class="mt-2 {{ VC::RW }}">
                <div class="{{ VC::CL7 ?? 'col-lg-7' }}">
                    <div class="sop-card {{ VC::CD }}">
                        <div class="{{ VC::CD }}-header p-2">
                            <div class="search-bar-left">
                                <form>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="{{ VC::INP_GP_TXT }}"><i class="{{ VC::TI }} {{ VC::TI_SRC }}"></i></span>
                                        </div>
                                        @php
                                            $rSearchProducts = Route::has('search.products') ? route('search.products') : '#';
                                            $gSearchProducts = $msg(VW::POS,'search_products_unavailable','Search is unavailable. Please contact technical support or your domain administrator.');
                                        @endphp
                                        <input id="searchproduct" type="text" class="{{ VC::FM_CT }} pr-4 rounded-right"
                                            placeholder="{{ __('Search Product') }}"
                                            data-url="{{ $rSearchProducts }}"
                                            data-guard-msg="{{ $gSearchProducts }}">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="{{ VC::CD }}-body p-2">
                            <div class="right-content">
                                <div class="button-list b-bottom catgory-pad">
                                    <div class="form-row m-0" id="categories-listing"></div>
                                </div>
                                <div class="product-body-nop">
                                    <div class="form-row" id="product-listing"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::CL5 ?? 'col-lg-5' }} ps-lg-0">
                    <div class="{{ VC::CD }} m-0">
                        <div class="{{ VC::CD }}-header p-2">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM6 }}">
                                    @if(Utility::isFilled($customers) ?? [])
                                        {{ Form::select('customer_id',$customers,'',['class'=> VC::FM_CT.' select customer_select','id'=>'customer','required'=>'required']) }}
                                    @else
                                        <select class="{{ VC::FM_CT }} select customer_select" id="customer" disabled>
                                            <option value="">{{ __('No customers found') }}</option>
                                        </select>
                                    @endif
                                    {{ Form::hidden('vc_name_hidden','',['id'=>'vc_name_hidden']) }}
                                </div>
                                <div class="{{ VC::CM6 }}">
                                    @if(Utility::isFilled($warehouses) ?? [])
                                        {{ Form::select('warehouse_id',$warehouses,'',['class'=> VC::FM_CT.' select warehouse_select','id'=>'warehouse','required'=>'required']) }}
                                    @else
                                        <select class="{{ VC::FM_CT }} select warehouse_select" id="warehouse" disabled>
                                            <option value="">{{ __('No warehouses found') }}</option>
                                        </select>
                                    @endif
                                    {{ Form::hidden('warehouse_name_hidden','',['id'=>'warehouse_name_hidden']) }}
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CD }}-body carttable cart-product-list carttable-scroll" id="carthtml">
                            @php $total = 0; @endphp
                            <div class="table-responsive">
                                <table class="{{ VC::TB }}">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-left">{{ __('Name') }}</th>
                                            <th class="text-center">{{ __('QTY') }}</th>
                                            <th>{{ __('Tax') }}</th>
                                            <th class="text-center">{{ __('Price') }}</th>
                                            <th class="text-center">{{ __('Sub Total') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody">
                                        @if($cartHasItems)
                                            @foreach($sessionCart as $id => $details)
                                                @php
                                                    $product    = ProductService::find(data_get($details,'id'));
                                                    $image_file = data_get($product,'pro_image','');
                                                    $image_url  = $image_file !== '' ? $image_file : 'avatar.png';
                                                    $subtotal   = (float) data_get($details,'subtotal',0);
                                                    $qty        = (int) data_get($details,'quantity',1);
                                                    $price      = (float) data_get($details,'price',0);
                                                    $name       = data_get($details,'name',__('No product name'));
                                                    $total     += $subtotal;
                                                    $taxes      = [];
                                                    if (!empty($product) && !empty($product->tax_id) && is_callable([Utility::class,'tax'])) {
                                                        $taxes = Utility::tax($product->tax_id) ?? [];
                                                    }
                                                @endphp
                                                <tr data-product-id="{{ $id }}" id="product-id-{{ $id }}">
                                                    <td class="cart-images">
                                                        <img alt="Image" src="{{ asset(Storage::url('uploads/pro_image/'.$image_url)) }}" class="card-image avatar rounded-circle-sale shadow hover-shadow-lg">
                                                    </td>
                                                    <td class="name">{{ $name }}</td>
                                                    <td>
                                                        <span class="quantity buttons_added">
                                                            <input type="button" value="-" class="minus">
                                                            @php
                                                                $rUpdateCart = Route::has('update-cart') ? route('update-cart') : url('update-cart');
                                                                $gUpdateCart = $msg(VW::POS,'update_cart_unavailable','Update cart is unavailable. Please contact technical support or your domain administrator.');
                                                            @endphp
                                                            <input type="number" step="1" min="1" name="quantity"
                                                                title="{{ __('Quantity') }}" class="input-number"
                                                                data-url="{{ $rUpdateCart }}" data-guard-msg="{{ $gUpdateCart }}"
                                                                data-id="{{ $id }}" size="4" value="{{ $qty }}">
                                                            <input type="button" value="+" class="plus">
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if(Utility::isFilled($taxes) ?? [])
                                                            @foreach($taxes as $tax)
                                                                <span class="{{ VC::BDG }} {{ VC::BG_P }}">{{ data_get($tax,'name',__('No name')) }} ({{ data_get($tax,'rate',0) }}%)</span><br>
                                                            @endforeach
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="price text-right">{{ $user?->priceFormat($price) }}</td>
                                                    <td class="col-sm-3 mt-2"><span class="subtotal">{{ $user?->priceFormat($subtotal) }}</span></td>
                                                    <td class="col-sm-2 mt-2">
                                                        @php
                                                            $rRemoveFromCart = Route::has('remove-from-cart') ? route('remove-from-cart') : url('remove-from-cart');
                                                            $gRemoveFromCart = $msg(VW::POS,'remove_from_cart_unavailable','Remove from cart is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <a href="#" class="{{ VC::ACT_BTN_DNG }} bs-pass-para-pos" title="{{ __('Delete') }}"
                                                            data-guard-msg="{{ $gRemoveFromCart }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang,'generics','are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang,'generics','irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $id }}" data-id="{{ $id }}">
                                                            <i class="{{ VC::TI }} {{ VC::TI_TRS_WT }} {{ VC::BT_SM }}"></i>
                                                        </a>
                                                        {{ Form::open(['method'=>'delete','url'=>$rRemoveFromCart,'id'=>'delete-form-'.$id]) }}
                                                            <input type="hidden" name="session_key" value="{{ $lastsegment }}">
                                                            <input type="hidden" name="id" value="{{ $id }}">
                                                        {{ Form::close() }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="text-center no-found">
                                                <td colspan="7">{{ __('No Data Found.!') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="total-section mt-3">
                                <div class="sub-total">
                                    <div class="{{ VC::DFL_JCB }}">
                                        <h6 class="{{ VC::MB0 }} text-dark">{{ __('Sub Total') }} :</h6>
                                        <h6 class="{{ VC::MB0 }} text-dark subtotal_price" id="displaytotal">{{ $user?->priceFormat($total) }}</h6>
                                    </div>
                                    <div class="{{ VC::RW }} {{ VC::ALC }}">
                                        <div class="{{ VC::C6 ?? 'col-6' }}">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <span class="{{ VC::TXTS_TRP }}">{{ $user?->currencySymbol() }}</span>
                                                {{ Form::number('discount',null,['class'=> VC::FM_CT.' discount','required'=>'required','placeholder'=>__('Discount')]) }}
                                                {{ Form::hidden('discount_hidden','',['id'=>'discount_hidden']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::C6 ?? 'col-6' }}">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h6>{{ __('Total') }} :</h6>
                                                <h6 class="totalamount">{{ $user?->priceFormat($total) }}</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="{{ VC::DFL_AIC_JCB }} pt-3" id="btn-pur">
                                        @php
                                            $rPosCreate = $cartHasItems && Route::has(VW::POS.'.create') ? route(VW::POS.'.create') : '#';
                                            $gPosCreate = $msg(VW::POS,'pos_create_unavailable','Checkout is unavailable. Please contact technical support or your domain administrator.');
                                        @endphp
                                        <button type="button" class="{{ VC::BT_PRM }} rounded"
                                            data-ajax-popup="true" data-size="xl" data-align="centered"
                                            data-url="{{ $rPosCreate }}" data-guard-msg="{{ $gPosCreate }}"
                                            data-title="{{ __('POS Invoice') }}"
                                            @if(!$cartHasItems) disabled="disabled" @endif>
                                            {{ __('PAY') }}
                                        </button>

                                        <div class="tab-content btn-empty text-end">
                                            <a href="#" class="{{ VC::BT }} btn-danger rounded m-0 bs-pass-para-pos"
                                                data-toggle="tooltip" data-original-title="{{ __('Empty Cart') }}"
                                                data-guard-msg="{{ $gEmptyCart }}"
                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang,'generics','are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang,'generics','irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="delete-form-emptycart">
                                                {{ __('Empty Cart') }}
                                            </a>
                                            @php
                                                $rEmptyCart = Route::has('empty-cart') ? route('empty-cart') : url('empty-cart');
                                                $gEmptyCart = $msg(VW::POS,'empty_cart_unavailable','Empty cart is unavailable. Please contact technical support or your domain administrator.');
                                            @endphp
                                            {{ Form::open(['method'=>'post','url'=>$rEmptyCart,'id'=>'delete-form-emptycart']) }}
                                                <input type="hidden" name="session_key" value="{{ $lastsegment }}" id="empty_cart">
                                            {{ Form::close() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::MD_FD }}" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="body"></div>
                </div>
            </div>
        </div>

        <div class="position-fixed top-0 end-0 p-3" style="z-index:99999">
            <div id="liveToast" class="toast text-white fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="{{ VC::DFL }}">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/dash.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/pos/lang/index.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/pos/lang/cart.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script async src="{{ asset('js/moment.min.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
        <script async src="{{ asset('js/jscolor.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/pos/index.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
        @if($message = Session::get('success'))
            <script>
                (() => {typeof show_toastr === 'function' && show_toastr('success', '{!! $message !!}');})()
            </script>
        @endif
        @if($message = Session::get('error'))
            <script>
                (() => {typeof show_toastr === 'function' && show_toastr('error', '{!! $message !!}');})()
            </script>
        @endif
        @stack('script-page')
        <script>
            $.ajaxSetup({ headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") } });
            $(() => {
                const lang = document.documentElement.getAttribute("lang") || "en";
                const tr = k => (window.translations[lang] && window.translations[lang][k]) || (window.translations.en && window.translations.en[k]) || k;
                const $customerSelect = $(".customer_select");
                const $warehouseSelect = $(".warehouse_select");
                const $discountInput = $(".discount");
                const $tbody = $("#tbody");
                const $searchInput = $("#searchproduct");
                const $categoriesWrap = $("#categories-listing");
                const $productsWrap = $("#product-listing");
                const $totalDisplay = $("#displaytotal");
                const $totalAmount = $(".totalamount");
                const $purchaseBtn = $("#btn-pur button");
                const $clearBtn = $(".btn-empty button");
                const $warehouseField = $("#warehouse");
                const session_key = $("#empty_cart").val() || "";
                $("#vc_name_hidden").val($customerSelect.val());
                $("#warehouse_name_hidden").val($warehouseSelect.val());
                $("#discount_hidden").val($discountInput.val());
                const addCommas = n => (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                const searchProducts = (url, value = "", cat_id = "0", war_id = "0") => {
                $.get(url, { search: value, cat_id, war_id, session_key }).done(html => $productsWrap.html(html));
                };
                const getProductCategories = () => {
                $.get("{{ route(VW::PRD_SV_CAT . '.categories') }}").done(html => $categoriesWrap.html(html));
                };
                const updateTotalsFromCart = items => {
                let sum = 0;
                items.forEach(({ id, subtotal }) => {
                    sum += subtotal;
                    $(`#product-id-${id} .subtotal`).text(addCommas(subtotal));
                });
                $totalDisplay.text(addCommas(sum));
                $totalAmount.text(addCommas(sum));
                };
                getProductCategories();
                if ($searchInput.length) {
                const url = $searchInput.data("url");
                const wid = $warehouseField.val() || "0";
                searchProducts(url, "", "0", wid);
                $warehouseField.on("change", () => {
                    const widNow = $warehouseField.val() || "0";
                    searchProducts(url, "", "0", widNow);
                });
                }
                $customerSelect.on("change", function () {
                $("#vc_name_hidden").val($(this).val());
                });
                $warehouseSelect.on("change", function () {
                $("#warehouse_name_hidden").val($(this).val());
                const sess = $("#empty_cart").val();
                $.post("{{route('warehouse-empty-cart')}}", { session_key: sess }).done(() => {
                    $tbody.empty().html(`<tr class="text-center no-found"><td colspan="7">${tr("no_data_found")}</td></tr>`);
                });
                });
                $(document).on("click", "#clearinput", function () {
                $(this).closest("div").find("input").val("");
                });
                $(document).on("keyup", "input#searchproduct", function () {
                const url = $(this).data("url");
                const value = this.value;
                const cat = $(".cat-active").children().data("cat-id") || "0";
                const wid = $warehouseField.val() || "0";
                searchProducts(url, value, String(cat), wid);
                });
                $(document).on("click", ".toacart", function () {
                $.get($(this).data("url"))
                    .done(data => {
                    if (data.code !== "200") return;
                    $totalDisplay.text(addCommas(data.product.subtotal));
                    $totalAmount.text(addCommas(data.product.subtotal));
                    if (data.carttotal) {
                        updateTotalsFromCart(data.carttotal);
                        $discountInput.val("");
                    }
                    $tbody.append(data.carthtml);
                    $(".no-found").addClass("d-none");
                    $(`.carttable #product-id-${data.product.id} input[name="quantity"]`).val(data.product.quantity);
                    $purchaseBtn.prop("disabled", false);
                    $clearBtn.addClass("btn-clear-cart");
                    })
                    .fail(({ responseJSON }) => {
                    show_toastr(tr("error"), responseJSON?.error || "Error", "error");
                    });
                });
                $(document).on("input", '#carthtml input[name="quantity"]', function (e) {
                e.preventDefault();
                const $input = $(this);
                const qty = Number($input.val());
                const discount = $discountInput.val();
                if (!Number.isFinite(qty)) return;
                $.ajax({
                    url: $input.data("url"),
                    method: "PATCH",
                    data: { id: $input.attr("data-id"), quantity: qty, discount, session_key }
                })
                    .done(data => {
                    if (data.code !== "200") return;
                    if (qty === 0) {
                        const $row = $input.closest(".row");
                        $row.hide(250, () => $row.remove());
                        if ($row.is(":last-child")) {
                        $purchaseBtn.prop("disabled", true);
                        $clearBtn.removeClass("btn-clear-cart");
                        }
                    }
                    updateTotalsFromCart(data.product);
                    if (typeof data.discount !== "undefined") $totalAmount.text(data.discount);
                    })
                    .fail(({ responseJSON }) => {
                    show_toastr(tr("error"), responseJSON?.error || "Error", "error");
                    });
                });
                $(document).on("click", ".remove-from-cart", function (e) {
                e.preventDefault();
                const $btn = $(this);
                if (!confirm(tr("are_you_sure"))) return;
                const $row = $btn.closest(".row");
                $row.hide(250, () => $row.parent().parent().remove());
                if ($row.is(":last-child")) {
                    $purchaseBtn.prop("disabled", true);
                    $clearBtn.removeClass("btn-clear-cart");
                }
                $.ajax({ url: $btn.data("url"), method: "DELETE", data: { id: $btn.attr("data-id") } })
                    .done(data => {
                    if (data.code !== "200") return;
                    updateTotalsFromCart(data.product);
                    show_toastr(tr("success"), data.success, "success");
                    })
                    .fail(({ responseJSON }) => {
                    show_toastr(tr("error"), responseJSON?.error || "Error", "error");
                    });
                });
                $(document).on("click", ".btn-clear-cart", function (e) {
                e.preventDefault();
                if (!confirm(tr("remove_all_items"))) return;
                $.get($(this).data("url"), { session_key })
                    .done(() => window.location.reload())
                    .fail(({ responseJSON }) => {
                    show_toastr(tr("error"), responseJSON?.error || "Error", "error");
                    });
                });
                $(document).on("click", ".btn-done-payment", function (e) {
                e.preventDefault();
                const $btn = $(this);
                $.get($btn.data("url"), {
                    vc_name: $("#vc_name_hidden").val(),
                    warehouse_name: $("#warehouse_name_hidden").val(),
                    discount: $("#discount_hidden").val()
                })
                    .always(() => $btn.remove())
                    .done(data => {
                    if (data.code === 200) show_toastr(tr("success"), data.success, "success");
                    })
                    .fail(({ responseJSON }) => {
                    show_toastr(tr("error"), responseJSON?.error || "Error", "error");
                    });
                });
                $(document).on("click", ".category-select", function () {
                const catId = String($(this).data("cat-id") || "0");
                $(".category-select").parent().removeClass("cat-active");
                $(".category-select .card-title").removeClass("text-white").addClass("text-dark");
                $(this).find(".card-title").removeClass("text-dark").addClass("text-white");
                $(this).parent().addClass("cat-active");
                const url = "{{ route('search.products') }}";
                const wid = $warehouseField.val() || "0";
                searchProducts(url, "", catId, wid);
                });
                $(document).on("keyup", ".discount", () => {
                const discount = $discountInput.val();
                $("#discount_hidden").val(discount);
                $.post("{{ route(VW::POS . '.cart.discount') }}", { discount })
                    .done(({ total }) => $totalAmount.text(total))
                    .fail(({ responseJSON }) => {
                    show_toastr(tr("error"), responseJSON?.error || "Error", "error");
                    });
                });
            });
            const site_currency_symbol_position = "{{ Utility::getValByName('site_currency_symbol_position') }}";
            const site_currency_symbol = "{{ Utility::getValByName('site_currency_symbol') }}";
        </script>
    </body>
</html>

