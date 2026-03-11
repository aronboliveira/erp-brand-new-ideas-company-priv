@php
    use App\Config\Constants\{
        SettingsConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        StacksConstants as ST
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    if (!function_exists("resolveRoute")) {
        function resolveRoute(string $base): ?string {
        $k = Str::kebab($base);
        return Route::has($base) ? $base : (Route::has($k) ? $k : null);
        }
    }
    function safeDate($user, $v) {
        return (is_object($user) && method_exists($user,'dateFormat') && !empty($v))
            ? $user->dateFormat($v)
            : __('Failed to format date');
    }

    $user = Auth::user();
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $logoDir = Utility::getFile('uploads/logo');
    $companyLogo = Utility::getValByName(SettingsConstants::CPN_LG);
    $logoFile = !empty($companyLogo) ? $companyLogo : SettingsConstants::CPN_LG_DK_DEF;
    $logoSrc = (is_string($logoDir) && $logoDir !== '') ? ($logoDir . '/' . $logoFile) : '#';

    $printBase = VW::POS . '.printview';
    $printResolved = resolveRoute($printBase);
    $printUrl = $printResolved ? route($printResolved) : '#';
    $printGuardMsg = __(($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::POS, 'pos_printview_route_unavailable') : 'POS print view route is unavailable. Please contact technical support or your domain administrator.') ?? 'POS print view route is unavailable. Please contact technical support or your domain administrator.');

    $sales = (is_array($sales ?? null) && count($sales ?? [])) ? $sales : (($sales ?? null) instanceof Collection && $sales->isNotEmpty() ? $sales->toArray() : []);
    $details = is_array($details ?? null) ? $details : [];
@endphp

@if (!empty($sales) && count($sales) > 0)
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::RW }} mt-2">
                <div class="col-6">
                    <img src="{{ $logoSrc }}" width="120" alt="{{ __('Company logo') }}">
                </div>
            </div>
            <div id="printableArea">
                <div class="{{ VC::RW }} {{ VC::MT3 }}">
                    <div class="col-6">
                        <h1 class="invoice-id {{ VC::H6 }}">{{ data_get($details, 'pos_id', __('No POS identifier available')) }}</h1>
                        <div class="date"><b>{{ __('Date') }}: </b>{{ safeDate($user, data_get($details, 'date')) }}</div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="text-dark"><b>{{ __('Warehouse Name') }}: </b>{!! data_get($details, 'warehouse.details') ?: e(__('No warehouse details available')) !!}</div>
                    </div>
                </div>
                <div class="{{ VC::RW }} mt-2">
                    <div class="col contacts d-flex justify-content-between pb-4">
                        <div class="invoice-to">
                            <div class="text-dark {{ VC::H6 }}"><b>{{ __('Billed To :') }}</b></div>
                            {!! data_get($details, 'customer.details') ?: e(__('No customer billing details available')) !!}
                        </div>
                        @if(!empty(data_get($details, 'customer.shippdetails')))
                            <div class="invoice-to">
                                <div class="text-dark {{ VC::H6 }}"><b>{{ __('Shipped To :') }}</b></div>
                                {!! data_get($details, 'customer.shippdetails') ?: e(__('No shipping details available')) !!}
                            </div>
                        @endif
                        <div class="company-details">
                            <div class="text-dark {{ VC::H6 }}"><b>{{ __('From:') }}</b></div>
                            {!! data_get($details, 'user.details') ?: e(__('No sender details available')) !!}
                        </div>
                    </div>
                </div>
                <div class="{{ VC::RW }}">
                    <table class="{{ VC::TB }}">
                        <thead>
                        <tr>
                            <th class="text-left">{{ __('Items') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th class="text-right">{{ __('Price') }}</th>
                            <th class="text-right">{{ __('Tax') }}</th>
                            <th class="text-right">{{ __('Tax Amount') }}</th>
                            <th class="text-right">{{ __('Total') }}</th>
                        </tr>
                        </thead>
                        <tbody class="font-style">
                        @foreach (data_get($sales, 'data', []) as $value)
                            <tr>
                                <td class="cart-summary-table text-left">{{ data_get($value, 'name', __('No item name available')) }}</td>
                                <td class="cart-summary-table">{{ data_get($value, 'quantity', __('N/A')) }}</td>
                                <td class="text-right cart-summary-table">{{ data_get($value, 'price', __('N/A')) }}</td>
                                <td class="text-right cart-summary-table">{!! data_get($value, 'product_tax') ?: e(__('No tax available')) !!}</td>
                                <td class="text-right cart-summary-table">{{ data_get($value, 'tax_amount', __('N/A')) }}</td>
                                <td class="text-right cart-summary-table">{{ data_get($value, 'subtotal', __('N/A')) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td>{{ __('Sub Total') }}</td>
                            <td></td><td></td><td></td><td></td>
                            <td class="text-right">{{ data_get($sales, 'sub_total', __('No subtotal available')) }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Discount') }}</td>
                            <td></td><td></td><td></td><td></td>
                            <td class="text-right">{{ data_get($sales, 'discount', __('No discount available')) }}</td>
                        </tr>
                        <tr class="pos-header">
                            <td>{{ __('Total') }}</td>
                            <td></td><td></td><td></td><td></td>
                            <td class="text-right">{{ data_get($sales, 'total', __('No total available')) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if (data_get($details, 'pay') === 'show')
                <button
                    class="{{ VC::BT }} btn-success rounded mt-2 {{ VC::FEND }} payment-done-btn"
                    data-url="{{ $printUrl }}"
                    data-ajax-popup="true"
                    data-size="sm"
                    data-bs-toggle="tooltip"
                    data-title="{{ __('POS Invoice') }}"
                    data-guard-msg="{{ $printGuardMsg }}"
                    data-sv-localized="true"
                    title="{{ __('Cash Payment') }}"
                >{{ __('Cash Payment') }}</button>
            @endif
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/pos/lang/show.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/pos/lang/printShow.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/show.js') }}"></script>
    <script defer>
      (() => {
        const $ = window.jQuery;
        let filename = "";
        const initFilename = () => {
          try {
            const el = $("#filename");
            if (el && el.length) filename = el.val() || "";
          } catch (_) {}
        };
        const saveAsPDF = () => {
          try {
            const el = document.getElementById("printableArea");
            if (!el) return;
            const hasHtml2Pdf = typeof window.html2pdf !== "undefined";
            if (!hasHtml2Pdf) return;
            const opt = {
              margin: 0.3,
              filename: filename || "",
              image: { type: "jpeg", quality: 1 },
              html2canvas: { scale: 4, dpi: 72, letterRendering: true },
              jsPDF: { unit: "in", format: "A2" },
            };
            const api = typeof html2pdf === "function" ? html2pdf() : html2pdf;
            const p = api.set(opt).from(el).save();
            if (p && typeof p.catch === "function") {
              p.catch(() => {});
            }
          } catch (_) {}
        };
        const bindPayment = () => {
          if (!$ || typeof $.ajax !== "function") return;
          $(document).on("click", ".payment-done-btn", (e) => {
            try {
              e.preventDefault();
              const btn = e.currentTarget;
              const $btn = $(btn);
              const vc = $("#vc_name_hidden").val() || "";
              const wh = $("#warehouse_name_hidden").val() || "";
              const dc = $("#discount_hidden").val() || "";
              $.ajax({
                url: "{{ route(VW::POS.'.data.store') }}",
                method: "GET",
                data: { vc_name: vc, warehouse_name: wh, discount: dc },
                beforeSend: () => {
                  try { $btn.remove(); } catch (_) {}
                },
                success: (data) => {
                  try {
                    if (data && Number(data.code) === 200) {
                      $("#carthtml").load(`${document.URL} #carthtml`);
                      if (typeof window.show_toastr === "function") {
                        window.show_toastr("success", data.success, "success");
                      }
                    }
                  } catch (_) {}
                },
                error: (xhr) => {
                  try {
                    const resp = (xhr && xhr.responseJSON) || {};
                    if (typeof window.show_toastr === "function") {
                      window.show_toastr("{{ __('Error') }}", resp.error || "", "error");
                    }
                  } catch (_) {}
                },
              });
            } catch (_) {}
          });
        };
        const ready = () => {
          initFilename();
          bindPayment();
          window.saveAsPDF = saveAsPDF;
        };
        if ($ && typeof $.fn.ready === "function") {
          $(document).ready(ready);
        } else if (document.readyState === "loading") {
          document.addEventListener("DOMContentLoaded", ready, { once: true });
        } else {
          ready();
        }
      })();
    </script>
@else
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::RW }}">
                <div class="col-12 text-center">
                    <h4 class="text-dark">{{ __('No data found for sales') }}</h4>
                </div>
            </div>
        </div>
    </div>
@endif


{{--                <div class="col-6 text-end">--}}
{{--                    <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()"><span class="ti ti-download"></span></a>--}}
{{--                </div>--}}
{{--                            @dd($value)--}}
{{--                <a href="#" class="btn btn-success btn-done-payment rounded mt-2 float-right"--}}
{{--                   data-url="{{ route('pos.data.store') }}">{{ __('Cash Payment') }}</a>--}}