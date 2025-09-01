@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Gate, Route};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Warehouse Transfer') }}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Warehouse Transfer') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create warehouse transfer')
            @php
                $wtCreateBase  = VW::WRH_TRF . '.create';
                $wtCreateKebab = Str::kebab($wtCreateBase);
                $wtCreateName  = Route::has($wtCreateBase) ? $wtCreateBase : (Route::has($wtCreateKebab) ? $wtCreateKebab : null);
                $wtCreateUrl   = $wtCreateName ? route($wtCreateName) : '#';
                $wtCreateGuard = Utility::fetchLinkMessage($lang, VW::WRH_TRF, 'create_warehouse_transfer_unavailable') ?? 'Create warehouse transfer route is unavailable. Please contact technical support or your domain administrator.';
                $wtCreateId    = 'warehouse-transfer-create-link';
            @endphp
            <a id="{{ $wtCreateId }}"
               href="{{ $wtCreateUrl }}"
               data-url="{{ $wtCreateUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create Warehouse Transfer') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-guard-msg="{{ $wtCreateGuard }}"
               data-sv-localized="true"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/warehouses/transfers/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-xl-12">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('From Warehouse') }}</th>
                                    <th>{{ __('To Warehouse') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse((($warehouse_transfers ?? null) instanceof \Illuminate\Support\Collection || is_array($warehouse_transfers ?? null)) ? $warehouse_transfers : [] as $transfer)
                                    @php
                                        $trId     = (string) data_get($transfer, 'id', '');
                                        $fromName = (string) (data_get($transfer, 'fromWarehouse.name') ?? '');
                                        $toName   = (string) (data_get($transfer, 'toWarehouse.name') ?? '');
                                        $prodName = (string) (data_get($transfer, 'product.name') ?? '');
                                        $qtyVal   = data_get($transfer, 'quantity');
                                        $dateRaw  = (string) (data_get($transfer, 'date') ?? '');
                                        $dateFmt  = $dateRaw !== '' ? ($user?->dateFormat($dateRaw) ?? $dateRaw) : '';
                                    @endphp
                                    <tr class="font-style">
                                        <td>{{ $fromName !== '' ? $fromName : __('No from warehouse available') }}</td>
                                        <td>{{ $toName !== '' ? $toName : __('No to warehouse available') }}</td>
                                        <td>{{ $prodName !== '' ? $prodName : __('No product available') }}</td>
                                        <td>{{ isset($qtyVal) ? $qtyVal : __('No quantity available') }}</td>
                                        <td>{{ $dateFmt !== '' ? $dateFmt : __('No date available') }}</td>
                                        @if(Gate::check('edit warehouse') || Gate::check('delete warehouse'))
                                            <td class="Action">
                                                @can('delete warehouse')
                                                    @php
                                                        $wtDelBase   = VW::WRH_TRF . '.destroy';
                                                        $wtDelKebab  = Str::kebab($wtDelBase);
                                                        $wtDelName   = Route::has($wtDelBase) ? $wtDelBase : (Route::has($wtDelKebab) ? $wtDelKebab : null);
                                                        $wtDelUrl    = ($wtDelName && $trId !== '') ? route($wtDelName, [$trId]) : '#';
                                                        $wtDelGuard  = Utility::fetchLinkMessage($lang, VW::WRH_TRF, 'delete_warehouse_transfer_unavailable') ?? 'Delete warehouse transfer route is unavailable. Please contact technical support or your domain administrator.';
                                                        $wtDelFormId = 'warehouse-transfer-delete-form-' . $trId;
                                                        $wtDelBtnId  = 'warehouse-transfer-delete-btn-' . $trId;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method'               => 'DELETE',
                                                            'url'                  => $wtDelUrl,
                                                            'id'                   => $wtDelFormId,
                                                            'data-resolved-action' => $wtDelUrl,
                                                            'data-guard-msg'       => $wtDelGuard,
                                                            'data-sv-localized'    => 'true',
                                                        ]) !!}
                                                            <a id="{{ $wtDelBtnId }}"
                                                               href="#"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __( Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?' ) }}|{{ __( Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?' ) }}"
                                                               data-confirm-yes="document.getElementById('{{ $wtDelFormId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const f = document.getElementById(@json($wtDelFormId));
                                                                    const t = document.getElementById(@json($wtDelBtnId));
                                                                    if (!f || !t || f.getAttribute('data-listener-active') === 'true') return;
                                                                    f.setAttribute('data-listener-active', 'true');

                                                                    const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                    if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
                                                                        f.setAttribute('action', resolved);
                                                                    }

                                                                    f.addEventListener('submit', (e) => {
                                                                        const action = f.getAttribute('action') || '#';
                                                                        if (action && action !== '#') return;
                                                                        e.preventDefault();

                                                                        const msg = f.getAttribute('data-guard-msg') || 'Delete warehouse transfer route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                                        if (ok) {
                                                                            const toast = document.createElement('div');
                                                                            toast.className = 'toast';
                                                                            toast.setAttribute('role','alert');
                                                                            toast.setAttribute('aria-live','assertive');
                                                                            toast.setAttribute('aria-atomic','true');
                                                                            const body = document.createElement('div');
                                                                            body.className = 'toast-body';
                                                                            body.textContent = msg;
                                                                            toast.appendChild(body);
                                                                            c.appendChild(toast);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        f.setAttribute('data-failed-route', 'true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ __('No warehouse transfers available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/warehouses/transfers/lang/quantity.js') }}"></script>
    <script async>
        (function () {
        const $ = window.jQuery;
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataErrGuard = "data-warehouse-error";
        const dataBindGuard = "data-warehouse-bound";
        const ns = "._npWarehouse";
        const qs = (s, r = document) => r.querySelector(s);
        const hasBootstrap = () =>
            !!(
            qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')
            ) && !!(window.bootstrap && window.bootstrap.Toast);
        const ensureToastContainer = () => {
            let c = qs("#np-toast-container");
            if (c) return c;
            c = document.createElement("div");
            c.id = "np-toast-container";
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            document.body.appendChild(c);
            return c;
        };
        const showErrorNow = message => {
            if (hasBootstrap()) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML =
                '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = qs(".toast-body", t);
            if (body) body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            } catch (_) {
                alert(message ?? errFb);
            }
            } else {
            alert(message ?? errFb);
            }
        };
        const scheduleClickError = message => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") return;
            host.setAttribute(dataErrGuard, "true");
            const once = () => {
            try {
                showErrorNow(message);
            } finally {
                host.removeAttribute(dataErrGuard);
            }
            };
            document.addEventListener("click", once, { once: true });
            const mo = new MutationObserver((m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("click", once);
                o.disconnect();
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
        };
        const getMsg = (el, key) => {
            let msg = errFb;
            if (
            el?.getAttribute?.(dataSvLocalized) === "true" ||
            el?.getAttribute?.(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el?.getAttribute?.(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        const csrf = () => {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m?.getAttribute("content") || "";
        };
        const isBadUrl = u => !u || u === "#";
        const urls = {
            products: '{{ route(VW::WRH_TRF.".getproduct") }}',
            quantity: '{{ route(VW::WRH_TRF.".getquantity") }}',
        };

        const ensureJq = () => {
            if (!$ || !$.fn) {
            try {
                console.error("jQuery unavailable");
            } catch (_) {}
            scheduleClickError(getMsg(document.body, "plugin_unavailable"));
            return false;
            }
            return true;
        };
        const buildProductControls = () => {
            const wrap = $("#product_div");
            if (!wrap.length) return;
            if (!wrap.find('label[for="product"]').length) {
            wrap.append(
                '<label for="product" class="form-label">{{ __("Product") }}</label>'
            );
            }
            if (!$("#product_id").length) {
            wrap.append(
                '<select class="form-control" id="product_id" name="product_id"></select>'
            );
            }
        };
        const populateSelect = ($sel, entries, placeholder) => {
            if (!$sel || !$sel.length) return;
            $sel.empty();
            if (placeholder) $sel.append(`<option value="">${placeholder}</option>`);
            $.each(entries, function (key, value) {
            $sel.append(`<option value="${key}">${value}</option>`);
            });
        };
        const getProduct = wid => {
            if (!ensureJq()) return;
            if (isBadUrl(urls.products)) {
            scheduleClickError(
                getMsg(document.body, "warehouse_products_unavailable")
            );
            return;
            }
            $.ajax({
            url: urls.products,
            type: "POST",
            data: { warehouse_id: wid ?? "", _token: csrf() },
            success: function (data) {
                try {
                buildProductControls();
                const $product = $("#product_id");
                populateSelect($product, {}, '{{ __("Select Product") }}');
                if (data?.ware_products) {
                    populateSelect($product, data.ware_products);
                }
                const $to = $('select[name="to_warehouse"]');
                if ($to.length && data?.to_warehouses) {
                    $to.empty();
                    $.each(data.to_warehouses, function (key, value) {
                    $to.append(`<option value="${key}">${value}</option>`);
                    });
                }
                } catch (_) {
                scheduleClickError(
                    getMsg(document.body, "warehouse_products_unavailable")
                );
                }
            },
            });
        };
        const getQuantity = (pid, wid) => {
            if (!ensureJq()) return;
            if (isBadUrl(urls.quantity)) {
            scheduleClickError(
                getMsg(document.body, "warehouse_quantity_unavailable")
            );
            return;
            }
            $.ajax({
            url: urls.quantity,
            type: "POST",
            data: { product_id: pid ?? "", warehouse_id: wid ?? "", _token: csrf() },
            success: function (data) {
                try {
                $("#quantity").val((data ?? "").toString());
                } catch (_) {
                scheduleClickError(
                    getMsg(document.body, "warehouse_quantity_unavailable")
                );
                }
            },
            });
        };
        const bind = () => {
            if (!ensureJq()) return;
            const host = document.body;
            if (host.getAttribute(dataBindGuard) === "true") return;
            host.setAttribute(dataBindGuard, "true");
            $(document).on("change" + ns, 'select[name="from_warehouse"]', function () {
            const v = $(this).val();
            getProduct(v);
            });
            $(document).on("change" + ns, "#product_id", function () {
            const pid = $(this).val();
            const wid = $("#warehouse_id").val();
            getQuantity(pid, wid);
            });
            const startWid = $("#warehouse_id").val();
            if (startWid != null) getProduct(startWid);
            const mo = new MutationObserver(function () {
            if (
                !$('select[name="from_warehouse"]').length &&
                !$("#product_id").length
            ) {
                $(document).off("change" + ns);
                host.removeAttribute(dataBindGuard);
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
        };
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", bind, { once: true });
        } else {
            bind();
        }
        })();
    </script>
@endpush
