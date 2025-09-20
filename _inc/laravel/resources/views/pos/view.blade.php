@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, Crypt};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();

    $canPosNum   = is_object($user) && is_callable([$user,'posNumberFormat']);
    $canDate     = is_object($user) && is_callable([$user,'dateFormat']);
    $canPrice    = is_object($user) && is_callable([$user,'priceFormat']);

    $dashBase   = 'dashboard';
    $dashUrl    = Route::has($dashBase) ? route($dashBase) : '#';
    $dashGuard  = Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $repBase     = VW::POS . '.report';
    $repKebab    = Str::kebab($repBase);
    $repResolved = Route::has($repBase) ? $repBase : (Route::has($repKebab) ? $repKebab : null);
    $repUrl      = $repResolved ? route($repResolved) : '#';
    $repGuard    = Utility::fetchLinkMessage($lang, VW::POS, 'report_route_unavailable') ?? __('Report route is unavailable. Please contact technical support or your domain administrator.');

    $pdfBase     = VW::POS . '.pdf';
    $pdfKebab    = Str::kebab($pdfBase);
    $pdfResolved = Route::has($pdfBase) ? $pdfBase : (Route::has($pdfKebab) ? $pdfKebab : null);
    $pdfUrl      = ($pdfResolved && !empty($pos?->id)) ? route($pdfResolved, Crypt::encrypt($pos->id)) : '#';
    $pdfGuard    = Utility::fetchLinkMessage($lang, VW::POS, 'pdf_route_unavailable') ?? __('PDF route is unavailable. Please contact technical support or your domain administrator.');

    $settings = Utility::settings();

    $posNumber  = $canPosNum ? ($user->posNumberFormat($pos->pos_id ?? null) ?? __('No POS number available')) : __('No POS number available');
    $issueDate  = $canDate ? ($user->dateFormat($pos->purchase_date ?? null) ?? __('No issue date available')) : __('No issue date available');

    $amountFmt   = $canPrice ? ($user->priceFormat($posPayment['amount'] ?? 0) ?? __('Could not format amount')) : __('No amount available');
    $discountFmt = $canPrice ? ($user->priceFormat($posPayment['discount'] ?? 0) ?? __('Could not format discount')) : __('No discount available');
    $totalFmt    = $canPrice ? ($user->priceFormat($posPayment['discount_amount'] ?? 0) ?? __('Could not format total')) : __('No total available');

    $shippingOn = (Utility::getValByName('shipping_display') ?? '') === 'on';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('POS Detail') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}" data-url="{{ $dashUrl }}" data-guard-msg="{{ $dashGuard }}" data-sv-localized="true" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ $repUrl }}" data-url="{{ $repUrl }}" data-guard-msg="{{ $repGuard }}" data-sv-localized="true" {{ $repUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('POS Summary') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ $posNumber }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="{{ $pdfUrl }}" target="_blank" class="{{ VC::BT_PRM }}" data-url="{{ $pdfUrl }}" data-guard-msg="{{ $pdfGuard }}" data-sv-localized="true">{{ __('Download') }}</a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="{{ VC::C12 }} {{ VC::CL6 }} {{ VC::CM6 }}">
                            <h4>{{ __('POS') }}</h4>
                        </div>
                        <div class="{{ VC::C12 }} {{ VC::CL6 }} {{ VC::CM6 }} text-end">
                            <h4 class="invoice-number">{{ $posNumber }}</h4>
                        </div>
                        <div class="{{ VC::C12 }}"><hr></div>
                    </div>

                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::C12 }} {{ VC::CL4 }} {{ VC::CM5 }}">
                            <small class="font-style">
                                <strong>{{ __('Billed To') }} :</strong><br>
                                @php
                                    $bn = data_get($customer ?? [], 'billing_name');
                                    $ba = data_get($customer ?? [], 'billing_address');
                                    $bc = data_get($customer ?? [], 'billing_city');
                                    $bs = data_get($customer ?? [], 'billing_state');
                                    $bz = data_get($customer ?? [], 'billing_zip');
                                    $bco= data_get($customer ?? [], 'billing_country');
                                    $bp = data_get($customer ?? [], 'billing_phone');
                                    $btax = data_get($customer ?? [], 'tax_number');
                                @endphp
                                @if(!empty($bn))
                                    {{ $bn }}<br>
                                    {{ $ba ?? '' }}<br>
                                    {{ $bc ? $bc : '' }}{{ $bc && $bs ? ', ' : '' }}{{ $bs ? $bs : '' }}{{ ($bc || $bs) && $bz ? ', ' : '' }}{{ $bz ?? '' }}<br>
                                    {{ $bco ?? '' }}<br>
                                    {{ $bp ?? '' }}<br>
                                    @if(($settings['vat_gst_number_switch'] ?? '') === 'on')
                                        <strong>{{ __('Tax Number') }} : </strong>{{ $btax ?? '' }}
                                    @endif
                                @else
                                    -
                                @endif
                            </small>
                        </div>

                        <div class="{{ VC::C12 }} {{ VC::CL4 }} {{ VC::CM4 }}">
                            @if($shippingOn)
                                @php
                                    $sn = data_get($customer ?? [], 'shipping_name');
                                    $sa = data_get($customer ?? [], 'shipping_address');
                                    $sc = data_get($customer ?? [], 'shipping_city');
                                    $ss = data_get($customer ?? [], 'shipping_state');
                                    $sz = data_get($customer ?? [], 'shipping_zip');
                                    $sco= data_get($customer ?? [], 'shipping_country');
                                    $sp = data_get($customer ?? [], 'shipping_phone');
                                @endphp
                                <small>
                                    <strong>{{ __('Shipped To') }} :</strong><br>
                                    @if(!empty($sn))
                                        {{ $sn }}<br>
                                        {{ $sa ?? '' }}<br>
                                        {{ $sc ? $sc : '' }}{{ $sc && $ss ? ', ' : '' }}{{ $ss ? $ss : '' }}{{ ($sc || $ss) && $sz ? ', ' : '' }}{{ $sz ?? '' }}<br>
                                        {{ $sco ?? '' }}<br>
                                        {{ $sp ?? '' }}<br>
                                    @else
                                        -
                                    @endif
                                </small>
                            @endif
                        </div>

                        <div class="{{ VC::C12 }} {{ VC::CL4 }} {{ VC::CM3 }}">
                            <div class="{{ VC::DFL_AIC_JCB }}">
                                <div class="{{ VC::ME3 }}">
                                    <small>
                                        <strong>{{ __('Issue Date') }} :</strong>
                                        {{ $issueDate }}<br><br>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="{{ VC::RW }} {{ VC::MT3 }}">
                        <div class="{{ VC::CM12 }}">
                            <div class="table-responsive {{ VC::MT3 }}">
                                <table class="{{ VC::TB }}">
                                    <thead>
                                        <tr>
                                            <th class="text-dark">#</th>
                                            <th class="text-dark">{{ __('Items') }}</th>
                                            <th class="text-dark">{{ __('Quantity') }}</th>
                                            <th class="text-dark">{{ __('Price') }}</th>
                                            <th class="text-dark">{{ __('Tax') }}</th>
                                            <th class="text-dark">{{ __('Tax Amount') }}</th>
                                            <th class="text-dark">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $taxesData = [];
                                    @endphp
                                    @forelse(($items ?? []) as $key => $item)
                                        @php
                                            $rowTaxTotal = 0;
                                            $taxes = [];
                                            if (!empty($item?->tax)) {
                                                $taxes = Utility::tax($item->tax) ?? [];
                                                foreach ($taxes as $tx) {
                                                    $tp = Utility::taxRate($tx->rate ?? 0, $item->price ?? 0, $item->quantity ?? 0);
                                                    $rowTaxTotal += $tp;
                                                    $name = $tx->name ?? 'Tax';
                                                    $taxesData[$name] = ($taxesData[$name] ?? 0) + $tp;
                                                }
                                            }
                                            $qty = $item->quantity ?? 0;
                                            $price = $item->price ?? 0;
                                            $lineTotal = ($price * $qty) + $rowTaxTotal;
                                            $priceFmt = $canPrice ? ($user->priceFormat($price) ?? '') : (string)$price;
                                            $rowTaxFmt = $canPrice ? ($user->priceFormat($rowTaxTotal) ?? '') : (string)$rowTaxTotal;
                                            $lineTotalFmt = $canPrice ? ($user->priceFormat($lineTotal) ?? '') : (string)$lineTotal;
                                        @endphp
                                        <tr>
                                            <td>{{ ($key ?? 0) + 1 }}</td>
                                            <td>{{ optional($item->product())->name ?? __('Unnamed product') }}</td>
                                            <td>{{ $qty }}</td>
                                            <td>{{ $priceFmt }}</td>
                                            <td>
                                                @if(!empty($taxes))
                                                    <table>
                                                        @foreach($taxes as $tax)
                                                            <tr>
                                                                <span class="badge bg-primary">{{ ($tax->name ?? 'Tax') . ' (' . (($tax->rate ?? 0)) . '%)' }}</span><br>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $rowTaxFmt }}</td>
                                            <td>{{ $lineTotalFmt }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted">{{ __('No items available.') }}</td>
                                        </tr>
                                    @endforelse

                                    <tr>
                                        <td><b>{{ __('Sub Total') }}</b></td>
                                        <td></td><td></td><td></td><td></td><td></td>
                                        <td>{{ $amountFmt }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>{{ __('Discount') }}</b></td>
                                        <td></td><td></td><td></td><td></td><td></td>
                                        <td>{{ $discountFmt }}</td>
                                    </tr>
                                    <tr class="pos-header">
                                        <td><b>{{ __('Total') }}</b></td>
                                        <td></td><td></td><td></td><td></td><td></td>
                                        <td>{{ $totalFmt }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/pos/lang/view.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/view.js') }}"></script>
    <script defer>
        (() => {
            const $ = window.jQuery;
            const send = (url, isDisplay) => {
            if ($ && typeof $.ajax === "function") {
                $.ajax({
                url,
                type: "GET",
                data: { is_display: !!isDisplay },
                success: () => {},
                error: () => {},
                });
                return;
            }
            try {
                const u = new URL(url, window.location.href);
                u.searchParams.set("is_display", isDisplay ? "1" : "0");
                fetch(u.toString(), { method: "GET", credentials: "same-origin" }).catch(() => {});
            } catch (_) {}
            };
            const handler = (e) => {
            try {
                const target = e.target.closest("#shipping");
                if (!target) return;
                const url = target.getAttribute("data-url") || "";
                if (!url) return;
                const isDisplay = target.checked === true;
                send(url, isDisplay);
            } catch (_) {}
            };
            const init = () => {
            if ($ && typeof $(document).on === "function") {
                $(document).on("click", "#shipping", handler);
            } else {
                document.addEventListener("click", handler);
            }
            };
            if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", init, { once: true });
            } else {
            init();
            }
        })();
    </script>
@endpush