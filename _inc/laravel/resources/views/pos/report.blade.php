@php
    try {
$user = Auth::user();
        $canDateFormat = is_object($user) && is_callable([$user, 'dateFormat']);
        $canPriceFormat = is_object($user) && is_callable([$user, 'priceFormat']);
        $canPosNumFormat = is_object($user) && is_callable([$user, 'posNumberFormat']);
        $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user: $user) : app()->getLocale();
        $canFetchMsg = is_callable([Utility::class, 'fetchLinkMessage']);

        $dashBase = 'dashboard';
        $dashUrl = Route::has($dashBase) ? route($dashBase) : '#';
        $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $posShowBase = VW::POS . '.show';
        $posShowKebab = Str::kebab($posShowBase);
        $posShowResolved = Route::has($posShowBase) ? $posShowBase : (Route::has($posShowKebab) ? $posShowKebab : null);
        $posShowGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::POS, 'pos_show_route_unavailable') : 'POS show route is unavailable. Please contact technical support or your domain administrator.') ?? __('POS show route is unavailable. Please contact technical support or your domain administrator.');
    } catch (\Throwable $e) {
        \Log::error('pos/report — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('POS Summary') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a
            href="{{ $dashUrl }}"
            data-url="{{ $dashUrl }}"
            data-guard-msg="{{ base64_encode($dashGuard) }}"
            data-sv-localized="true"
        >
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('POS Summary') }}</li>
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush

@section(YieldingConstants::ADM_CTT)
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                <tr>
                                    <th>{{ __('POS ID') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Warehouse') }}</th>
                                    <th>{{ __('Sub Total') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Total') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse(($posPayments ?? []) as $posPayment)
                                    @php
                                        try {
                                            $posId = $posPayment->id ?? null;
                                            $encId = $posId ? Crypt::encrypt($posId) : null;
                                            $showUrl = ($posShowResolved && $encId) ? route($posShowResolved, $encId) : '#';

                                            $createdAt = $posPayment->created_at ?? null;
                                            $dateTxt = $createdAt ? ($canDateFormat ? $user->dateFormat($createdAt) : (string) $createdAt) : __('Failed to get date');

                                            $isWalkIn = (int)($posPayment->customer_id ?? 0) === 0;
                                            $custName = $isWalkIn ? __('Walk-in Customer') : (data_get($posPayment, 'customer.name') ?: __('No customer available'));

                                            $whName = data_get($posPayment, 'warehouse.name') ?: __('No warehouse available');

                                            $pp = $posPayment->posPayment ?? null;
                                            $sub = (isset($pp->amount) && is_numeric($pp->amount)) ? (float) $pp->amount : 0.0;
                                            $disc = (isset($pp->discount) && is_numeric($pp->discount)) ? (float) $pp->discount : 0.0;
                                            $total = (isset($pp->discount_amount) && is_numeric($pp->discount_amount)) ? (float) $pp->discount_amount : 0.0;

                                            $fmt = function ($n) use ($canPriceFormat, $user) { return $canPriceFormat ? $user->priceFormat($n) : number_format((float) $n, 2); };

                                            $posNumTxt = $posId ? ($canPosNumFormat ? $user->posNumberFormat($posId) : (string) $posId) : __('Could not find POS ID');
                                        } catch (\Throwable $e) {
                                            \Log::error('pos/report — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        <td class="Id">
                                            <a
                                                href="{{ $showUrl }}"
                                                data-url="{{ $showUrl }}"
                                                data-guard-msg="{{ base64_encode($posShowGuard) }}"
                                                data-sv-localized="true"
                                                class="{{ VC::BT_OUTPM }}"
                                            >
                                                {{ $posNumTxt }}
                                            </a>
                                        </td>
                                        <td>{{ $dateTxt }}</td>
                                        <td>{{ $custName }}</td>
                                        <td>{{ $whName }}</td>
                                        <td>{{ $fmt($sub) }}</td>
                                        <td>{{ $fmt($disc) }}</td>
                                        <td>{{ $fmt($total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="{{ VC::TXCT_DK }}">
                                            <p>{{ __('No Data Found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/pos/lang/report.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/report.js') }}"></script>
@endpush
