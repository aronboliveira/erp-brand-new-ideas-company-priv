@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
    $guardMsgPrint = Utility::fetchLinkMessage($lang, VW::POS, 'store_print_barcode_missing_route') ?? __('Action unavailable');
    $guardMsgSetting = Utility::fetchLinkMessage($lang, VW::POS, 'store_barcode_setting_missing_route') ?? __('Action unavailable');
    $isList = fn($v) => (is_array($v ?? null) && count($v ?? [])) || (($v ?? null) instanceof Collection && $v->isNotEmpty());
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('POS Product Barcode') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('POS Product Barcode') }}</li>
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_BC)
            @php
                $posPrintBase      = VW::POS.'.print';
                $posPrintKebab     = Str::kebab($posPrintBase);
                $posPrintResolved  = Route::has($posPrintBase) ? $posPrintBase : (Route::has($posPrintKebab) ? $posPrintKebab : null);
                $posPrintUrl       = $posPrintResolved ? route($posPrintResolved) : '#';
                $posPrintGuard     = Utility::fetchLinkMessage($lang, VW::POS, 'pos_print_route_unavailable') ?? 'Print POS barcode route is unavailable. Please contact technical support or your domain administrator.';
                $posSettingBase     = VW::POS.'.setting';
                $posSettingKebab    = Str::kebab($posSettingBase);
                $posSettingResolved = Route::has($posSettingBase) ? $posSettingBase : (Route::has($posSettingKebab) ? $posSettingKebab : null);
                $posSettingUrl      = $posSettingResolved ? route($posSettingResolved) : '#';
                $posSettingGuard    = Utility::fetchLinkMessage($lang, VW::POS, 'pos_setting_route_unavailable') ?? 'POS barcode setting route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a href="{{ $posPrintUrl }}"
            id="pos-print-btn"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $posPrintUrl }}"
            data-bs-toggle="tooltip"
            title="{{ __('Print Barcode') }}"
            data-guard-msg="{{ $posPrintGuard }}"
            data-sv-localized="true">
                <i class="ti ti-scan text-white"></i>
            </a>
            <a href="{{ $posSettingUrl }}"
            id="pos-setting-btn"
            data-url="{{ $posSettingUrl }}"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            data-title="{{ __('Barcode Setting') }}"
            title="{{ __('Barcode Setting') }}"
            class="{{ VC::BT_SM_PM }}"
            data-guard-msg="{{ $posSettingGuard }}"
            data-sv-localized="true">
                <i class="ti ti-settings text-white"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/pos/linkBarcodePrint.js') }}"></script>
                <script defer src="{{ asset('assets/js/routes/pos/setting.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable-barcode">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Barcode') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($isList($productServices))
                                    @foreach ($productServices as $productService)
                                        @php
                                            $psName = data_get($productService, 'name') ?: __('Data unavailable');
                                            $psSku  = data_get($productService, 'sku') ?: __('Data unavailable');
                                            $psId   = data_get($productService, 'id');
                                            $divId  = $psId ?: ('ps-'.$loop->index);
                                        @endphp
                                        <tr>
                                            <td>{{ $psName }}</td>
                                            <td>{{ $psSku }}</td>
                                            <td>
                                                <div id="{{ $divId }}" class="product_barcode product_barcode_hight_de" data-skucode="{{ $psSku }}"></div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-dark"><p>{{ __('No Product Services found') }}</p></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    {{--    <script src="{{ asset('public/js/jquery-barcode.min.js') }}"></script>--}}
    <script src="{{ asset('public/js/jquery-barcode.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/guard.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/pos/lang/barcode.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/pos/printBarcode.js') }}"></script>
    <script async>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const guardOnce = "data-guard-once";
        const guardListener = "data-guard-listener";
        const getMsg = el => {
            let msg = errFb;
            if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = "barcode_unavailable";
            msg = window.translations?.[lang]?.[msgKey] || el.getAttribute(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        const hasBootstrapCss = () => !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
        const showError = el => {
            const message = getMsg(el);
            if (hasBootstrapCss() && window.bootstrap) {
            const wrapId = "toast-wrap-guard";
            if (!document.getElementById(wrapId)) {
                const wrap = document.createElement("div");
                wrap.id = wrapId;
                wrap.className = "position-fixed top-0 end-0 p-3";
                wrap.style.zIndex = "1080";
                document.body.appendChild(wrap);
            }
            const toastId = "toast-barcode-error";
            const t = document.createElement("div");
            t.className = "toast align-items-center text-bg-danger border-0";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            t.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
            document.getElementById(wrapId).appendChild(t);
            new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            } else {
            alert(message);
            }
        };
        const safeBarcode = $el => {
            try {
            const id = $el.attr("id");
            const sku = $el.data("skucode");
            const btype = '{{ $barcode['barcodeType'] }}';
            const renderer = '{{ $barcode['barcodeFormat'] }}';
            if (!id || !sku) return;
            if (typeof jQuery === "undefined" || !jQuery.fn?.barcode) throw new Error("deps_missing");
            const settings = { output: renderer, bgColor: "#FFFFFF", color: "#000000", barWidth: "1", barHeight: "50", moduleSize: "5", posX: "10", posY: "20", addQuietZone: "1" };
            jQuery("#" + id).html("").show().barcode(String(sku), String(btype), settings);
            } catch {
            $el.attr(dataGuardMsg, getMsg($el.get(0)));
            }
        };
        const initAll = root => {
            jQuery(root || document).find(".product_barcode").each(function () { safeBarcode(jQuery(this)); });
            window.setTimeout(() => {
            try {
                if (document.querySelector(".datatable-barcode") && typeof window.simpleDatatables?.DataTable === "function") {
                new window.simpleDatatables.DataTable(".datatable-barcode");
                }
            } catch {}
            }, 1000);
        };
        const attachTrigger = () => {
            const sel = '[data-action="regen-barcodes"]';
            const host = document.querySelector(sel);
            if (!host) return;
            if (host.getAttribute(guardListener) === "true") return;
            host.setAttribute(guardListener, "true");
            host.addEventListener("click", e => {
            e.preventDefault();
            try {
                initAll(document);
                const anyErr = Array.from(document.querySelectorAll(".product_barcode")).some(n => n.getAttribute(dataGuardMsg));
                if (anyErr) showError(host);
            } catch {
                showError(host);
            }
            }, { passive: true });
        };
        const mo = new MutationObserver(muts => {
            muts.forEach(m => {
            m.addedNodes && m.addedNodes.forEach(n => {
                if (n.nodeType === 1) {
                if (n.matches?.(".product_barcode") || n.querySelector?.(".product_barcode")) initAll(n);
                if (n.matches?.('[data-action="regen-barcodes"]') || n.querySelector?.('[data-action="regen-barcodes"]')) attachTrigger();
                }
            });
            });
        });
        jQuery(() => { initAll(document); attachTrigger(); });
        mo.observe(document.documentElement, { childList: true, subtree: true });
        })();
    </script>
@endpush
