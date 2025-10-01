@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\{Estimation, Utility};
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $settings = (is_array($settings ?? null)) ? $settings : [];
@endphp
@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __("Estimation Detail") }}
@endsection
@if(!empty($estimation) && isset($estimation->id))
    @php
        $cli      = $client ?? data_get($estimation ?? null, 'client');
        $hasPrice = method_exists($user, 'priceFormat');
        $hasDate  = method_exists($user, 'dateFormat');
        $hasNum   = method_exists($user, 'estimateNumberFormat');

        $estId    = data_get($estimation ?? null, 'id');

        $statusIdx = (int) data_get($estimation ?? null, 'status', -1);
        $statusLbl = data_get(Estimation::$statuses ?? [], $statusIdx, __('No status available'));
    @endphp
    @section(YW::ADM_ACT_BTN)
        <div class="all-button-box {{ VC::R_FLX_ALC_JCE }}">
            @can('Edit Estimation')
                @php
                    $editBase     = VW::EST . '.edit';
                    $editKebab    = Str::kebab($editBase);
                    $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                    $editUrl      = ($editResolved && $estId) ? route($editResolved, $estId) : '#';
                    $editGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'estimation_edit_route_unavailable') ?? 'Edit estimate route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                    <a
                        href="#"
                        data-url="{{ $editUrl }}"
                        data-ajax-popup="true"
                        data-title="{{ __('Edit Estimation') }}"
                        data-guard-msg="{{ $editGuardMsg }}"
                        class="{{ VC::BT_XS }} btn-white btn-icon-only width-auto"
                    >
                        <i class="{{ VC::TI_PC_WT }}"></i> {{ __('Edit') }}
                    </a>
                </div>
            @endcan
            @can('View Estimation')
                @php
                    $printBase     = VW::EST . '.get';
                    $printKebab    = Str::kebab($printBase);
                    $printResolved = Route::has($printBase) ? $printBase : (Route::has($printKebab) ? $printKebab : null);
                    $printUrl      = ($printResolved && $estId) ? route($printResolved, $estId) : '#';
                    $printGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'print_estimate_route_unavailable') ?? 'Print estimate route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                    <a
                        href="{{ $printUrl }}"
                        data-url="{{ $printUrl }}"
                        class="{{ VC::BT_XS }} btn-white btn-icon-only bg-warning width-auto"
                        title="{{ __('Print Estimation') }}"
                        target="_blank"
                        data-guard-msg="{{ $printGuardMsg }}"
                    >
                        <span><i class="fa fa-print"></i> {{ __('Print') }}</span>
                    </a>
                </div>
            @endcan
        </div>
    @endsection
    @section(YW::ADM_CTT)
        <div class="{{ VC::CD }}">
            <div class="invoice-title">
                {{ $hasNum ? ($user?->estimateNumberFormat(data_get($estimation, 'estimation_id')) ?? __('Failed to get estimate number')) : __('Failed to format estimate number') }}
            </div>
            <div class="invoice-detail pb-2">
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CM6 }} {{ VC::CS6 }}">
                        <div class="address-detail">
                            <strong>{{ __('From') }} :</strong>
                            {{ data_get($settings, 'company_name', __('No company name available')) }}<br>
                            {{ data_get($settings, 'company_address', __('No address available')) }}<br>
                            @php
                                $city    = data_get($settings, 'company_city', '');
                                $state   = data_get($settings, 'company_state', '');
                                $zipcode = data_get($settings, 'company_zipcode', '');
                                $country = data_get($settings, 'company_country', '');
                                $line2   = trim(implode(' ', array_filter([$city, $state])));
                                $zip     = $zipcode ? "-{$zipcode}" : '';
                            @endphp
                            {{ $line2 !== '' ? $line2 : __('No city/state available') }}{{ $zip }}<br>
                            {{ $country !== '' ? $country : __('No country available') }}
                        </div>
                    </div>
                    <div class="{{ VC::CM6 }} {{ VC::CS6 }}">
                        @if($cli)
                            <div class="address-detail text-end float-right">
                                <strong>{{ __('To') }} :</strong>
                                {{ data_get($cli, 'name', __('No client name available')) }} <br>
                                {{ data_get($cli, 'email', __('No client email available')) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="status-section">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM3 }} {{ VC::CS6 }} {{ VC::C6 }}">
                            <div class="text-status">
                                <strong>{{ __('Status') }} :</strong>
                                @if($statusIdx === 0)
                                    <span class="badge badge-pill badge-primary">{{ __($statusLbl) }}</span>
                                @elseif($statusIdx === 1)
                                    <span class="badge badge-pill badge-danger">{{ __($statusLbl) }}</span>
                                @elseif($statusIdx === 2)
                                    <span class="badge badge-pill badge-warning">{{ __($statusLbl) }}</span>
                                @elseif($statusIdx === 3)
                                    <span class="badge badge-pill badge-success">{{ __($statusLbl) }}</span>
                                @elseif($statusIdx === 4)
                                    <span class="badge badge-pill badge-info">{{ __($statusLbl) }}</span>
                                @else
                                    <span class="badge badge-pill badge-secondary">{{ __($statusLbl) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="{{ VC::CM9 }} {{ VC::CS9 }} {{ VC::C9 }}">
                            <div class="text-status text-end">
                                {{ __('Issue Date') }}:
                                <strong>{{ $hasDate ? ($user?->dateFormat(data_get($estimation, 'issue_date')) ?? __('Failed to get issue date')) : __('Failed to format date') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CM12 }}">
                        <div class="justify-content-between align-items-center d-flex">
                            <h4 class="h4 font-weight-400 float-left">{{ __('Order Summary') }}</h4>
                            @can('Estimation Add Product')
                                @php
                                    $addBase     = VW::EST . '.products.add';
                                    $addKebab    = Str::kebab($addBase);
                                    $addResolved = Route::has($addBase) ? $addBase : (Route::has($addKebab) ? $addKebab : null);
                                    $addUrl      = ($addResolved && $estId) ? route($addResolved, $estId) : '#';
                                    $addGuardMsg = Utility::fetchLinkMessage($lang, VW::EST, 'add_product_route_unavailable') ?? 'Add product route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <a
                                    href="#"
                                    class="{{ VC::BT_SM }} btn-white float-right add-small"
                                    data-url="{{ $addUrl }}"
                                    data-ajax-popup="true"
                                    data-title="{{ __('Add Product') }}"
                                    data-guard-msg="{{ $addGuardMsg }}"
                                >
                                    <i class="ti ti-plus"></i> {{ __('Add Product') }}
                                </a>
                            @endcan
                        </div>
                        <div class="{{ VC::CD }}">
                            <div class="table-responsive order-table">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Action') }}</th>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Item') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th class="text-end">{{ __('Totals') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="list">
                                    @php
                                        $products = data_get($estimation ?? null, 'getProducts');
                                        $hasList  = (is_array($products) && count($products)) || ($products instanceof Collection && $products->isNotEmpty());
                                        $i = 0;
                                    @endphp
                                    @if($hasList)
                                        @foreach(($products instanceof Collection ? $products : collect($products)) as $product)
                                            @php
                                                $pvtId    = data_get($product, 'pivot.id');
                                                $pName    = data_get($product, 'name', __('No product name'));
                                                $pPrice   = (float) data_get($product, 'pivot.price', 0);
                                                $pQty     = (float) data_get($product, 'pivot.quantity', 0);
                                                $rowTotal = $pPrice * $pQty;
                                            @endphp
                                            <tr>
                                                <td class="Action">
                                                    <span>
                                                        @can('Estimation Edit Product')
                                                            @php
                                                                $editPBase     = VW::EST . '.products.edit';
                                                                $editPKebab    = Str::kebab($editPBase);
                                                                $editPResolved = Route::has($editPBase) ? $editPBase : (Route::has($editPKebab) ? $editPKebab : null);
                                                                $editPUrl      = ($editPResolved && $estId && $pvtId) ? route($editPResolved, [$estId, $pvtId]) : '#';
                                                                $editPGuard    = Utility::fetchLinkMessage($lang, VW::EST, 'edit_product_route_unavailable') ?? 'Edit product route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                href="#"
                                                                class="edit-icon"
                                                                data-url="{{ $editPUrl }}"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Estimation Product') }}"
                                                                data-toggle="tooltip"
                                                                data-original-title="{{ __('Edit') }}"
                                                                data-guard-msg="{{ $editPGuard }}"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        @endcan
                                                        @can('Estimation Delete Product')
                                                            @php
                                                                $delPBase     = VW::EST . '.products.delete';
                                                                $delPKebab    = Str::kebab($delPBase);
                                                                $delPResolved = Route::has($delPBase) ? $delPBase : (Route::has($delPKebab) ? $delPKebab : null);
                                                                $delPUrl      = ($delPResolved && $estId && $pvtId) ? route($delPResolved, [$estId, $pvtId]) : '#';
                                                                $delPGuard    = Utility::fetchLinkMessage($lang, VW::EST, 'delete_product_route_unavailable') ?? 'Delete product route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                href="#"
                                                                class="delete-icon"
                                                                data-toggle="tooltip"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-guard-msg="{{ $delPGuard }}"
                                                                data-url="{{ $delPUrl }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{$pvtId}}').submit();"
                                                            >
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Collective\Html\FormFacade::open([
                                                                'method' => 'DELETE',
                                                                'url'    => $delPUrl,
                                                                'id'     => 'delete-form-'.$pvtId
                                                            ]) !!}
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        @endcan
                                                    </span>
                                                </td>
                                                <td class="invoice-order">{{ ++$i }}</td>
                                                <td class="small-order">{{ $pName }}</td>
                                                <td class="small-order">{{ $hasPrice ? ($user?->priceFormat($pPrice) ?? __('Failed to get value')) : __('Failed to format price') }}</td>
                                                <td class="small-order">{{ $pQty }}</td>
                                                <td class="invoice-order text-end">{{ $hasPrice ? ($user?->priceFormat($rowTotal) ?? __('Failed to get value')) : __('Failed to format price') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">{{ __('No products found.') }}</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>
                @php
                    $subTotal = method_exists($estimation ?? null, 'getSubTotal') ? ($estimation->getSubTotal() ?? 0) : 0;
                    $taxVal   = method_exists($estimation ?? null, 'getTax') ? ($estimation->getTax() ?? 0) : 0;
                    $disc     = (float) data_get($estimation ?? null, 'discount', 0);
                    $taxName  = data_get($estimation ?? null, 'tax.name', __('No tax name'));
                    $taxRate  = data_get($estimation ?? null, 'tax.rate', __('N/A'));
                    $total    = $subTotal - $disc + $taxVal;
                @endphp
                <div class="{{ VC::RW }} text-end">
                    <div class="{{ VC::CM3 }}">
                        <div class="text-status"><strong>{{ __('Subtotal') }} :</strong> {{ $hasPrice ? ($user?->priceFormat($subTotal) ?? __('Failed to get value')) : __('Failed to format price') }}</div>
                    </div>
                    <div class="{{ VC::CM3 }}">
                        <div class="text-status"><strong>{{ __('Discount') }} :</strong> {{ $hasPrice ? ($user?->priceFormat($disc) ?? __('Failed to get value')) : __('Failed to format price') }}</div>
                    </div>
                    <div class="{{ VC::CM3 }}">
                        <div class="text-status"><strong>{{ $taxName }} ({{ $taxRate }} %) :</strong> {{ $hasPrice ? ($user?->priceFormat($taxVal) ?? __('Failed to get value')) : __('Failed to format price') }}</div>
                    </div>
                    <div class="{{ VC::CM3 }}">
                        <div class="text-status"><strong>{{ __('Total') }} :</strong> {{ $hasPrice ? ($user?->priceFormat($total) ?? __('Failed to get value')) : __('Failed to format price') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @push(ST::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/estimations/show.js') }}"></script>
        @endpush
    @endsection
@else
    <div class="alert alert-warning">{{ __('No data available for estimation') }}</div>
@endif
