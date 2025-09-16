@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YW::ADM_PG_TTL)
    {{__('Expense Edit')}}
@endsection
@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $expIndexBase    = VW::PRJ_EXP.'.index';
        $expIndexKebab   = Str::kebab($expIndexBase);
        $expIndexName    = Route::has($expIndexBase) ? $expIndexBase : (Route::has($expIndexKebab) ? $expIndexKebab : null);
        $expIndexUrl     = $expIndexName ? route($expIndexName) : '#';
        $expIndexGuard   = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'index_expense_route_unavailable') ?? 'Expense index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a
            id="bc-expense-index-link"
            href="{{ $expIndexUrl }}"
            data-url="{{ $expIndexUrl }}"
            data-guard-msg="{{ $expIndexGuard }}"
            data-sv-localized="true"
        >
            {{ __('Expense') }}
        </a>
    </li>
    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/expenses/editIndex.js') }}"></script>
    @endpush
    <li class="breadcrumb-item">{{__('Expense Edit')}}</li>
@endsection

@push(ST::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{asset('js/jquery-searchbox.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/expenses/lang/edit.js') }}"></script>
    <script defer>
        (() => {
          const errFb = "# ERROR";
          const dataClientLocalized = "data-client-localized";
          const dataGuardMsg = "data-guard-msg";
          const langSessionKey = "erp-np-lang";
      
          function getMsg(key, el){
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            ){
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
              ).toLowerCase().replace(/_/g,"-");
              lang = (lang === "pt-br" ? lang : lang.slice(0,2));
              msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[key] ||
                errFb;
              if (msg !== errFb){
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
            return msg;
          }
      
          function showError(message){
            try {
              const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .some(l=>/bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
              if (hasBs){
                let container = document.getElementById("bootstrap-toast-container");
                if (!container){
                  container = document.createElement("div");
                  container.id = "bootstrap-toast-container";
                  container.setAttribute("aria-live","polite");
                  container.setAttribute("aria-atomic","true");
                  document.body.appendChild(container);
                }
                let toast = container.querySelector(".toast") || (()=>{
                  const t = document.createElement("div");
                  t.className = "toast";
                  t.setAttribute("role","alert");
                  t.setAttribute("aria-live","assertive");
                  t.setAttribute("aria-atomic","true");
                  const body = document.createElement("div");
                  body.className = "toast-body";
                  t.appendChild(body);
                  container.appendChild(t);
                  return t;
                })();
                toast.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(toast).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          }
      
          try {
            const sel = "body";
            if (!document.querySelector(sel + " .repeater")) return;
      
            const $drag = $("body .repeater tbody").sortable({ handle: ".sort-handler" });
      
            const $rep = $(sel + " .repeater").repeater({
              initEmpty: true,
              defaultValues: { status: 1 },
              show() {
                $(this).slideDown();
                const files = $(this).find("input.multi");
                if (files.length){
                  files.MultiFile({
                    max: 3,
                    accept: "png|jpg|jpeg",
                    max_size: "{{ SettingsConstants::MAX_U_SIZE_DEF }}"
                  });
                }
                JsSearchBox();
                if (document.querySelector(".select2")) $(".select2").select2();
              },
              hide(deleteEl) {
                $(this).slideUp(deleteEl);
                this.remove();
                const subTotal = Array.from(document.querySelectorAll(".amount"))
                  .reduce((sum, el) => sum + parseFloat(el.textContent || "0"), 0);
                document.querySelector(".subTotal")?.textContent = subTotal.toFixed(2);
                document.querySelector(".totalAmount")?.textContent = subTotal.toFixed(2);
              },
              ready(setIdx) {
                $drag.on("drop", setIdx);
              },
              isFirstItemUndeletable: true
            });
      
            const dataVal = document.querySelector(sel + " .repeater").getAttribute("data-value");
            if (dataVal){
              JSON.parse(dataVal).forEach(item => {
                const row = document.querySelector(`#sortable-table .id[value="${item.id}"]`)?.closest("tr");
                if (row){
                  const elem = row.querySelector(".item");
                  elem.value = item.product_id;
                  changeItem($(elem));
                }
              });
            }
      
          } catch (e){
            const msg = getMsg("repeater_initialization_failed", document.body);
            showError(msg);
          }
        })();
    </script>
    <script defer>
        (() => {
          const errFb = "# ERROR";
          const dataClientLocalized = "data-client-localized";
          const dataGuardMsg = "data-guard-msg";
          const langSessionKey = "erp-np-lang";
        
          function getMsg(key, el) {
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            ) {
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
              ).toLowerCase().replace(/_/g, "-");
              lang = lang === "pt-br" ? lang : lang.slice(0, 2);
              msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
            return msg;
          }
        
          function showError(message) {
            try {
              const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
                  /bootstrap/i.test(l.href)
                ) && window.bootstrap?.Toast;
              if (hasBs) {
                let c = document.getElementById("bootstrap-toast-container");
                if (!c) {
                  c = document.createElement("div");
                  c.id = "bootstrap-toast-container";
                  c.setAttribute("aria-live", "polite");
                  c.setAttribute("aria-atomic", "true");
                  document.body.appendChild(c);
                }
                let t = c.querySelector(".toast") || (() => {
                  const t0 = document.createElement("div");
                  t0.className = "toast";
                  t0.setAttribute("role", "alert");
                  t0.setAttribute("aria-live", "assertive");
                  t0.setAttribute("aria-atomic", "true");
                  const b = document.createElement("div");
                  b.className = "toast-body";
                  t0.appendChild(b);
                  c.appendChild(t0);
                  return t0;
                })();
                t.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(t).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          }
        
          const billId = '{{ $expense->id }}';
        
          async function changeItem($el) {
            try {
              const prodId = $el.val();
              const url    = $el.data("url");
              if (!url || url === "#") {
                throw new Error("item_fetch_failed");
              }
        
              const resp = await fetch(url, {
                method: "POST",
                headers: {
                  "X-CSRF-TOKEN": $("#token").val(),
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({ product_id: prodId })
              });
              const text = await resp.text();
              const item = JSON.parse(text);
        
              const resp2 = await fetch(`{{ route(VW::PRJ_EXP.'.items') }}?bill_id=${billId}&product_id=${prodId}`, {
                headers: { "X-CSRF-TOKEN": $("#token").val() }
              });
              const billItems = JSON.parse(await resp2.text());
        
              const $row = $el.closest("tr");
              const qtyIn = $row.find(".quantity");
              const priceIn = $row.find(".price");
              const discIn = $row.find(".discount");
              const descIn = $row.find(".pro_description");
        
              if (billItems) {
                qtyIn.val(billItems.quantity);
                priceIn.val(billItems.price);
                discIn.val(billItems.discount);
                descIn.val(billItems.description);
              } else {
                qtyIn.val(1);
                priceIn.val(item.product.purchase_price);
                discIn.val(0);
                descIn.val(item.product.description);
              }
        
              // compute tax badges
              const taxes = [];
              let totalTaxRate = 0;
              item.taxes?.forEach(tax => {
                taxes.push(`<span class="badge bg-primary p-2 px-3 rounded mt-1 mr-1">
                  ${tax.name} (${tax.rate}%)
                </span>`);
                totalTaxRate += parseFloat(tax.rate);
              });
              const taxPrice = ((totalTaxRate / 100) *
                ( (billItems?.price || item.product.purchase_price) *
                  (billItems?.quantity || 1) -
                  parseFloat(discIn.val() || 0)
                )
              ).toFixed(2);
              $row.find(".itemTaxPrice").val(taxPrice);
              $row.find(".itemTaxRate").val(totalTaxRate.toFixed(2));
              $row.find(".taxes").html(taxes.join(""));
              $row.find(".tax").val(item.taxes.map(t=>t.id));
              $row.find(".unit").text(item.unit);
        
              // recalc totals
              const amounts = Array.from(document.querySelectorAll(".amount"))
                .reduce((s, el) => s + parseFloat(el.textContent||"0"), 0);
              const acct = Array.from(document.querySelectorAll(".accountamount"))
                .reduce((s, el) => {
                  const v = parseFloat(el.textContent||"0");
                  return s + (isNaN(v)?0:v);
                },0);
        
              $(".subTotal").text((amounts+acct).toFixed(2));
              $(".totalTax").text(
                Array.from(document.querySelectorAll(".itemTaxPrice"))
                  .reduce((s, el) => s + parseFloat(el.value||"0"),0)
                  .toFixed(2)
              );
              $(".totalAmount").text((amounts+acct).toFixed(2));
              $(".totalAmount").val((amounts+acct).toFixed(2));
        
            } catch (err) {
              const key = err.message || "item_fetch_failed";
              showError(getMsg(key, document.body));
            }
          }
        
          $(document).on("change", ".item", function() {
            changeItem($(this));
          });
        })();
    </script>
    <script defer>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const langSessionKey = "erp-np-lang";
        
        function getMsg(key, el) {
            let msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
            ).toLowerCase().replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        }
        
        function showError(message) {
            try {
            const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
                /bootstrap/i.test(l.href)
                ) && window.bootstrap?.Toast;
            if (hasBs) {
                let c = document.getElementById("bootstrap-toast-container");
                if (!c) {
                c = document.createElement("div");
                c.id = "bootstrap-toast-container";
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                document.body.appendChild(c);
                }
                let t = c.querySelector(".toast") || (() => {
                const t0 = document.createElement("div");
                t0.className = "toast";
                t0.setAttribute("role", "alert");
                t0.setAttribute("aria-live", "assertive");
                t0.setAttribute("aria-atomic", "true");
                const b = document.createElement("div");
                b.className = "toast-body";
                t0.appendChild(b);
                c.appendChild(t0);
                return t0;
                })();
                t.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        }
        
        const sel = "body";
        if (!$(sel).find(".repeater").length) return;
        
        try {
            const $dragAndDrop = $("body .repeater tbody").sortable({
            handle: ".sort-handler"
            });
            const $repeater = $(`${sel} .repeater`).repeater({
            initEmpty: true,
            defaultValues: { status: 1 },
            show() {
                try {
                $(this).slideDown();
                const $multi = $(this).find("input.multi");
                if ($multi.length) {
                    $multi.MultiFile({
                    max: 3,
                    accept: "png|jpg|jpeg",
                    max_size: "{{ SettingsConstants::MAX_U_SIZE_DEF }}"
                    });
                }
                JsSearchBox();
                if ($(".select2").length) {
                    $(".select2").select2();
                }
                } catch {
                showError(getMsg("repeater_initialization_failed", this));
                }
            },
            hide(deleteElement) {
                try {
                $(this).slideUp(deleteElement);
                $(this).remove();
                const subTotal = Array.from(document.querySelectorAll(".amount"))
                    .reduce((sum, el) => sum + parseFloat(el.textContent || 0), 0);
                $(".subTotal").html(subTotal.toFixed(2));
                $(".totalAmount").html(subTotal.toFixed(2));
                } catch {
                showError(getMsg("calculation_failed", this));
                }
            },
            ready(setIndexes) {
                $dragAndDrop.on("drop", setIndexes);
            },
            isFirstItemUndeletable: true
            });
        
            const dataVal = $(sel).find(".repeater").attr("data-value");
            if (dataVal) {
            const list = JSON.parse(dataVal);
            $repeater.setList(list);
            list.forEach(item => {
                const tr = $(`#sortable-table .id[value="${item.id}"]`).parent();
                tr.find(".item").val(item.product_id);
                changeItem(tr.find(".item"));
            });
            }
        } catch {
            showError(getMsg("repeater_initialization_failed", document.body));
        }
        })();
    </script>
    <script defer src="{{ asset('assets/js/routes/expenses/editRepeater.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/expenses/editSelect.js') }}"></script>
@endpush
@if(!empty($expense) && isset($expense->id))
  @php
      $formId         = 'expense-update-form';
      $updateBase     = VW::PRJ_EXP . '.update';
      $updateKebab    = Str::kebab($updateBase);
      $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
      $updateUrl      = ($updateResolved && isset($expense?->id)) ? route($updateResolved, $expense->id) : '#';
      $updateGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'update_expense_route_unavailable') ?? 'Update expense route is unavailable. Please contact technical support or your domain administrator.';

      $empBase     = VW::PRJ_EXP . '.employee';
      $empKebab    = Str::kebab($empBase);
      $empResolved = Route::has($empBase) ? $empBase : (Route::has($empKebab) ? $empKebab : null);
      $empUrl      = $empResolved ? route($empResolved) : '#';
      $empGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'employee_route_unavailable') ?? 'Employee endpoint is unavailable. Please contact technical support or your domain administrator.';

      $cusBase     = VW::PRJ_EXP . '.customer';
      $cusKebab    = Str::kebab($cusBase);
      $cusResolved = Route::has($cusBase) ? $cusBase : (Route::has($cusKebab) ? $cusKebab : null);
      $cusUrl      = $cusResolved ? route($cusResolved) : '#';
      $cusGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'customer_route_unavailable') ?? 'Customer endpoint is unavailable. Please contact technical support or your domain administrator.';

      $venBase     = VW::PRJ_EXP . '.vendor';
      $venKebab    = Str::kebab($venBase);
      $venResolved = Route::has($venBase) ? $venBase : (Route::has($venKebab) ? $venKebab : null);
      $venUrl      = $venResolved ? route($venResolved) : '#';
      $venGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'vendor_route_unavailable') ?? 'Vendor endpoint is unavailable. Please contact technical support or your domain administrator.';

      $prodBase     = VW::PRJ_EXP . '.product';
      $prodKebab    = Str::kebab($prodBase);
      $prodResolved = Route::has($prodBase) ? $prodBase : (Route::has($prodKebab) ? $prodKebab : null);
      $prodUrl      = $prodResolved ? route($prodResolved) : '#';
      $prodGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'product_route_unavailable') ?? 'Product endpoint is unavailable. Please contact technical support or your domain administrator.';

      $indexBase     = VW::PRJ_EXP . '.index';
      $indexKebab    = Str::kebab($indexBase);
      $indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
      $indexUrl      = $indexResolved ? route($indexResolved) : '#';
      $indexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_EXP, 'index_expense_route_unavailable') ?? 'Expense index route is unavailable. Please contact technical support or your domain administrator.';

      $employeesIsList = (is_array($employees ?? null) && count($employees ?? []) > 0) || (($employees ?? null) instanceof Collection && $employees->isNotEmpty());
      $customersIsList = (is_array($customers ?? null) && count($customers ?? []) > 0) || (($customers ?? null) instanceof Collection && $customers->isNotEmpty());
      $vendorsIsList   = (is_array($vendors   ?? null) && count($vendors   ?? []) > 0) || (($vendors   ?? null) instanceof Collection && $vendors->isNotEmpty());
      $categoryIsList  = (is_array($category  ?? null) && count($category  ?? []) > 0) || (($category  ?? null) instanceof Collection && $category->isNotEmpty());
      $accountsIsList  = (is_array($bank_Account ?? null) && count($bank_Account ?? []) > 0) || (($bank_Account ?? null) instanceof Collection && $bank_Account->isNotEmpty());
      $prodSvcIsList   = (is_array($product_services ?? null) && count($product_services ?? []) > 0) || (($product_services ?? null) instanceof Collection && $product_services->isNotEmpty());
      $chartAccIsList  = (is_array($chartAccounts ?? null) && count($chartAccounts ?? []) > 0) || (($chartAccounts ?? null) instanceof Collection && $chartAccounts->isNotEmpty());

      $employeeOptions = $employeesIsList ? (is_array($employees) ? $employees : $employees->toArray()) : [__('No employees available')];
      $customerOptions = $customersIsList ? (is_array($customers) ? $customers : $customers->toArray()) : [__('No customers available')];
      $vendorOptions   = $vendorsIsList   ? (is_array($vendors)   ? $vendors   : $vendors->toArray())   : [__('No vendors available')];
      $categoryOptions = $categoryIsList  ? (is_array($category)  ? $category  : $category->toArray())  : [__('No categories available')];
      $accountOptions  = $accountsIsList  ? (is_array($bank_Account) ? $bank_Account : $bank_Account->toArray()) : [__('No accounts available')];
      $prodSvcOptions  = $prodSvcIsList   ? (is_array($product_services) ? $product_services : $product_services->toArray()) : [__('No items available')];
      $chartAccOptions = $chartAccIsList  ? (is_array($chartAccounts) ? $chartAccounts : $chartAccounts->toArray()) : [__('No chart accounts available')];

      $uType = (string) data_get($expense ?? null, 'user_type', 'employee');
      $isEmployeeType = $uType === 'employee';
      $isCustomerType = $uType === 'customer';
      $isVendorType   = $uType === 'vendor';
      $payeeId        = data_get($expense ?? null, 'vendor_id');
  @endphp
  @section(YW::ADM_CTT)
      <div class="{{ VC::RW }}">
          {{ Form::model($expense, [
              'url'               => $updateUrl,
              'method'            => 'PUT',
              'id'                => $formId,
              'class'             => 'w-100',
              'data-url'          => $updateUrl,
              'data-guard-msg'    => $updateGuardMsg,
              'data-sv-localized' => 'true',
          ]) }}
              <div class="{{ VC::C12 }}">
                  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                  <div class="{{ VC::CD }}">
                      <div class="card-body">
                          <div class="{{ VC::RW }}">
                              <div class="{{ VC::CM6 }}">
                                  <div class="{{ VC::C12 }}">
                                      <div class="{{ VC::FM_CHK_IL_GP }}">
                                          <input type="radio" id="employee_radio" value="employee" name="type" class="form-check-input" {{ $isEmployeeType ? 'checked' : '' }}>
                                          <label class="form-check-label" for="employee_radio">{{ __('Employee') }}</label>
                                      </div>
                                      <div class="{{ VC::FM_CHK_IL_GP }}">
                                          <input type="radio" id="customer_radio" value="customer" name="type" class="form-check-input" {{ $isCustomerType ? 'checked' : '' }}>
                                          <label class="form-check-label" for="customer_radio">{{ __('Customer') }}</label>
                                      </div>
                                      <div class="{{ VC::FM_CHK_IL_GP }}">
                                          <input type="radio" id="vendor_radio" value="vendor" name="type" class="form-check-input" {{ $isVendorType ? 'checked' : '' }}>
                                          <label class="form-check-label" for="vendor_radio">{{ __('Vendor') }}</label>
                                      </div>
                                  </div>

                                  <div class="col employee {{ $isEmployeeType ? '' : 'd-none' }}">
                                      <div class="form-group" id="employee-box">
                                          {{ Form::label('employee_id', __('Payee'), ['class' => VC::FM_LB]) }}
                                          {{ Form::select(
                                              'employee_id',
                                              $employeeOptions,
                                              $isEmployeeType ? $payeeId : null,
                                              array_merge([
                                                  'class'          => VC::FM_CT_SL,
                                                  'id'             => 'employee',
                                                  'data-url'       => $empUrl,
                                                  'data-guard-msg' => $empGuardMsg
                                              ], $employeesIsList ? [] : ['disabled' => 'disabled'])
                                          ) }}
                                      </div>
                                      <div id="employee_detail" class="d-none"></div>
                                  </div>

                                  <div class="col customer {{ $isCustomerType ? '' : 'd-none' }}">
                                      <div class="form-group" id="customer-box">
                                          {{ Form::label('customer_id', __('Payee'), ['class' => VC::FM_LB]) }}
                                          {{ Form::select(
                                              'customer_id',
                                              $customerOptions,
                                              $isCustomerType ? $payeeId : null,
                                              array_merge([
                                                  'class'          => VC::FM_CT_SL,
                                                  'id'             => 'customer',
                                                  'data-url'       => $cusUrl,
                                                  'data-guard-msg' => $cusGuardMsg
                                              ], $customersIsList ? [] : ['disabled' => 'disabled'])
                                          ) }}
                                      </div>
                                      <div id="customer_detail" class="d-none"></div>
                                  </div>

                                  <div class="col vendor {{ $isVendorType ? '' : 'd-none' }}">
                                      <div class="form-group" id="vendor-box">
                                          {{ Form::label('vendor_id', __('Payee'), ['class' => VC::FM_LB]) }}
                                          {{ Form::select(
                                              'vendor_id',
                                              $vendorOptions,
                                              $isVendorType ? $payeeId : null,
                                              array_merge([
                                                  'class'          => VC::FM_CT_SL,
                                                  'id'             => 'vendor',
                                                  'data-url'       => $venUrl,
                                                  'data-guard-msg' => $venGuardMsg
                                              ], $vendorsIsList ? [] : ['disabled' => 'disabled'])
                                          ) }}
                                      </div>
                                      <div id="vendor_detail" class="d-none"></div>
                                  </div>
                              </div>

                              <div class="{{ VC::CM6 }}">
                                  <div class="{{ VC::RW }}">
                                      <div class="{{ VC::CM6 }}">
                                          <div class="form-group">
                                              {{ Form::label('bill_date', __('Payment Date'), ['class' => VC::FM_LB]) }}
                                              {{ Form::date('bill_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                                          </div>
                                      </div>
                                      <div class="{{ VC::CM6 }}">
                                          <div class="form-group">
                                              {{ Form::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}
                                              {{ Form::select(
                                                  'category_id',
                                                  $categoryOptions,
                                                  null,
                                                  array_merge(['class' => VC::FM_CT_SL], $categoryIsList ? [] : ['disabled' => 'disabled'])
                                              ) }}
                                          </div>
                                      </div>
                                      <div class="{{ VC::CM6 }}">
                                          <div class="form-group">
                                              {{ Form::label('account_id', __('Account'), ['class' => VC::FM_LB]) }}
                                              {{ Form::select(
                                                  'account_id',
                                                  $accountOptions,
                                                  null,
                                                  array_merge(['class' => VC::FM_CT_SL], $accountsIsList ? [] : ['disabled' => 'disabled'])
                                              ) }}
                                          </div>
                                      </div>
                                      <div class="{{ VC::CM6 }}">
                                          <div class="form-group">
                                              {{ Form::label('bill_number', __('Expense Number'), ['class' => VC::FM_LB]) }}
                                              <input type="text" class="{{ VC::FM_CT }}" value="{{ $expense_number }}" readonly>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="{{ VC::C12 }}">
                  <h5 class="d-inline-block mb-4">{{ __('Product & Services') }}</h5>
                  <div class="card repeater" data-value='{!! json_encode($items) !!}'>
                      <div class="item-section py-2">
                          <div class="{{ VC::RW }} justify-content-between align-items-center">
                              <div class="{{ VC::CM12 }} d-flex align-items-center justify-content-between justify-content-md-end">
                                  <div class="all-button-box me-2">
                                      <a href="#" data-repeater-create class="{{ VC::BT_PRM }}">
                                          <i class="ti ti-plus"></i> {{ __('Add item') }}
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
                                          <th width="20%">{{ __('Items') }}</th>
                                          <th>{{ __('Quantity') }}</th>
                                          <th>{{ __('Price') }}</th>
                                          <th>{{ __('Discount') }}</th>
                                          <th>{{ __('Tax') }} (%)</th>
                                          <th class="text-end">
                                              {{ __('Amount') }}
                                              <br><small class="text-danger font-bold">{{ __('after tax & discount') }}</small>
                                          </th>
                                          <th></th>
                                      </tr>
                                  </thead>
                                  <tbody class="ui-sortable" data-repeater-item>
                                      <tr>
                                          {{ Form::hidden('id', null, ['class' => VC::FM_CT.' id']) }}
                                          {{ Form::hidden('account_id', null, ['class' => VC::FM_CT.' account_id']) }}
                                          <td width="25%" class="form-group pt-0">
                                              {{ Form::select(
                                                  'items',
                                                  $prodSvcOptions,
                                                  null,
                                                  array_merge([
                                                      'class'          => VC::FM_CT_SL.' item',
                                                      'data-url'       => $prodUrl,
                                                      'data-guard-msg' => $prodGuardMsg
                                                  ], $prodSvcIsList ? [] : ['disabled' => 'disabled'])
                                              ) }}
                                          </td>
                                          <td>
                                              <div class="form-group price-input input-group search-form">
                                                  {{ Form::text('quantity', null, ['class' => VC::FM_CT.' quantity', 'placeholder' => __('Qty')]) }}
                                                  <span class="unit input-group-text bg-transparent"></span>
                                              </div>
                                          </td>
                                          <td>
                                              <div class="form-group price-input input-group search-form">
                                                  {{ Form::text('price', null, ['class' => VC::FM_CT.' price', 'placeholder' => __('Price')]) }}
                                                  <span class="input-group-text bg-transparent">{{ $user?->currencySymbol() }}</span>
                                              </div>
                                          </td>
                                          <td>
                                              <div class="form-group price-input input-group search-form">
                                                  {{ Form::text('discount', null, ['class' => VC::FM_CT.' discount', 'placeholder' => __('Discount')]) }}
                                                  <span class="input-group-text bg-transparent">{{ $user?->currencySymbol() }}</span>
                                              </div>
                                          </td>
                                          <td>
                                              <div class="form-group">
                                                  <div class="input-group">
                                                      <div class="taxes"></div>
                                                      {{ Form::hidden('tax', '', ['class' => VC::FM_CT.' tax']) }}
                                                      {{ Form::hidden('itemTaxPrice', '', ['class' => VC::FM_CT.' itemTaxPrice']) }}
                                                      {{ Form::hidden('itemTaxRate', '', ['class' => VC::FM_CT.' itemTaxRate']) }}
                                                  </div>
                                              </div>
                                          </td>
                                          <td class="text-end amount">0.00</td>
                                          <td>
                                              @can('delete bill product')
                                                  <a href="#" class="{{ VC::TRS_PARA }}" data-repeater-delete></a>
                                              @endcan
                                          </td>
                                      </tr>
                                      <tr>
                                          <td class="form-group">
                                              {{ Form::select(
                                                  'chart_account_id',
                                                  $chartAccOptions,
                                                  null,
                                                  array_merge(['class' => VC::FM_CT_SL.' js-searchBox'], $chartAccIsList ? [] : ['disabled' => 'disabled'])
                                              ) }}
                                          </td>
                                          <td class="form-group">
                                              <div class="input-group">
                                                  {{ Form::text('amount', null, ['class' => VC::FM_CT.' accountAmount', 'placeholder' => __('Amount')]) }}
                                                  <span class="input-group-text bg-transparent">{{ $user?->currencySymbol() }}</span>
                                              </div>
                                          </td>
                                          <td colspan="2" class="form-group">
                                              {{ Form::textarea('description', null, ['class' => VC::FM_CT.' pro_description', 'rows' => 1, 'placeholder' => __('Description')]) }}
                                          </td>
                                          <td></td>
                                          <td class="text-end accountamount">0.00</td>
                                      </tr>
                                  </tbody>
                                  <tfoot>
                                      <tr>
                                          <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                          <td><strong>{{ __('Sub Total') }} ({{ $user?->currencySymbol() }})</strong></td>
                                          <td class="text-end subTotal">0.00</td>
                                          <td></td>
                                      </tr>
                                      <tr>
                                          <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                          <td><strong>{{ __('Discount') }} ({{ $user?->currencySymbol() }})</strong></td>
                                          <td class="text-end totalDiscount">0.00</td>
                                          <td></td>
                                      </tr>
                                      <tr>
                                          <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                          <td><strong>{{ __('Tax') }} ({{ $user?->currencySymbol() }})</strong></td>
                                          <td class="text-end totalTax">0.00</td>
                                          <td></td>
                                      </tr>
                                      <tr>
                                          <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                          <td class="blue-text"><strong>{{ __('Total Amount') }} ({{ $user?->currencySymbol() }})</strong></td>
                                          <td class="blue-text text-end totalAmount">0.00</td>
                                          {{ Form::hidden('totalAmount', null, ['class' => VC::FM_CT.' totalAmount']) }}
                                          <td></td>
                                      </tr>
                                  </tfoot>
                              </table>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <a
                      href="{{ $indexUrl }}"
                      data-url="{{ $indexUrl }}"
                      data-guard-msg="{{ $indexGuardMsg }}"
                      class="btn btn-light me-3"
                      id="expense-update-cancel-link"
                  >{{ __('Cancel') }}</a>
                  <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
              </div>
          {{ Form::close() }}
      </div>

      @push(ST::ADM_SCR_PG)
          <script defer src="{{ asset('assets/js/routes/expenses/update.js') }}"></script>
      @endpush
  @endsection
@else
  <div class="alert alert-warning">{{ __('No expense found') }}</div>
@endif
