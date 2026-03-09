@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('warehouse_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Warehouse Transfer') }}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Warehouse Transfer') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create warehouse transfer')
            @php
                try {
                    $wtCreateBase  = VW::WRH_TRF . '.create';
                    $wtCreateKebab = Str::kebab($wtCreateBase);
                    $wtCreateName  = Route::has($wtCreateBase) ? $wtCreateBase : (Route::has($wtCreateKebab) ? $wtCreateKebab : null);
                    $wtCreateUrl   = $wtCreateName ? route($wtCreateName) : '#';
                    $wtCreateGuard = Utility::fetchLinkMessage($lang, VW::WRH_TRF, 'create_warehouse_transfer_unavailable') ?? 'Create warehouse transfer route is unavailable. Please contact technical support or your domain administrator.';
                    $wtCreateId    = 'warehouse-transfer-create-link';
                } catch (\Throwable $e) {
                    \Log::error('warehouse_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a id="{{ $wtCreateId }}"
               href="{{ $wtCreateUrl }}"
               data-url="{{ $wtCreateUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create Warehouse Transfer') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-guard-msg="{{ base64_encode($wtCreateGuard) }}"
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
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
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
                                        try {
                                            $trId     = (string) data_get($transfer, 'id', '');
                                            $fromName = (string) (data_get($transfer, 'fromWarehouse.name') ?? '');
                                            $toName   = (string) (data_get($transfer, 'toWarehouse.name') ?? '');
                                            $prodName = (string) (data_get($transfer, 'product.name') ?? '');
                                            $qtyVal   = data_get($transfer, 'quantity');
                                            $dateRaw  = (string) (data_get($transfer, 'date') ?? '');
                                            $dateFmt  = $dateRaw !== '' ? ($user?->dateFormat($dateRaw) ?? $dateRaw) : '';
                                        } catch (\Throwable $e) {
                                            \Log::error('warehouse_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
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
                                                        try {
                                                            $wtDelBase   = VW::WRH_TRF . '.destroy';
                                                            $wtDelKebab  = Str::kebab($wtDelBase);
                                                            $wtDelName   = Route::has($wtDelBase) ? $wtDelBase : (Route::has($wtDelKebab) ? $wtDelKebab : null);
                                                            $wtDelUrl    = ($wtDelName && $trId !== '') ? route($wtDelName, [$trId]) : '#';
                                                            $wtDelGuard  = Utility::fetchLinkMessage($lang, VW::WRH_TRF, 'delete_warehouse_transfer_unavailable') ?? 'Delete warehouse transfer route is unavailable. Please contact technical support or your domain administrator.';
                                                            $wtDelFormId = 'warehouse-transfer-delete-form-' . $trId;
                                                            $wtDelBtnId  = 'warehouse-transfer-delete-btn-' . $trId;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('warehouse_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
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
                                                                        const RG = window.RouteGuard || {};
                                                                        (RG.showToast || (m => alert(m)))(msg);
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
                                        <td colspan="6" class="{{ VC::TXCT_MT }}">{{ __('No warehouse transfers available') }}</td>
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
        const ns = "._npWarehouse";
        const dataBindGuard = "data-warehouse-bound";
        const qs = (s, r = document) => r.querySelector(s);
        const RG = window.RouteGuard || {};
        const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
        const showErrorNow = RG.showToast || (m => alert(m));
        const scheduleClickError = message => {
            const host = document.body;
            if (!host || host.getAttribute('data-warehouse-error') === "true") return;
            host.setAttribute('data-warehouse-error', "true");
            const once = () => {
            try {
                showErrorNow(message);
            } finally {
                host.removeAttribute('data-warehouse-error');
            }
            };
            document.addEventListener("click", once, { once: true });
        };
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
            products: '{{ route(VW::WRH_TRF.".get.product") }}',
            quantity: '{{ route(VW::WRH_TRF.".get.quantity") }}',
        };

        const ensureJq = () => {
            if (!$ || !$.fn) {
            try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
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
                '<label for="product" class="{{ VC::FM_LB }}">{{ __("Product") }}</label>'
            );
            }
            if (!$("#product_id").length) {
            wrap.append(
                '<select class="{{ VC::FM_CT }}" id="product_id" name="product_id"></select>'
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
