@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user        = Auth::user();
    $lang        = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
    $canPriceFormat = is_callable([$user,'priceFormat']);

    $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $importUrl   = Route::has(VW::PRD_SV.'.file.import') ? route(VW::PRD_SV.'.file.import') : '#';
    $importGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'import_product_services_unavailable') : 'Import Product & Services route is unavailable. Please contact technical support or your domain administrator.') ?? __('Import Product & Services route is unavailable. Please contact technical support or your domain administrator.');

    $exportUrl   = Route::has(VW::PRD_SV.'.export') ? route(VW::PRD_SV.'.export') : '#';
    $exportGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'export_product_services_unavailable') : 'Export Product & Services route is unavailable. Please contact technical support or your domain administrator.') ?? __('Export Product & Services route is unavailable. Please contact technical support or your domain administrator.');

    $createUrl   = Route::has(VW::PRD_SV.'.create') ? route(VW::PRD_SV.'.create') : '#';
    $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'create_product_service_unavailable') : 'Create Product Service route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Product Service route is unavailable. Please contact technical support or your domain administrator.');

    $items = [];
    if (is_array($productServices ?? null) && count($productServices)) {
        $items = $productServices;
    } elseif (($productServices ?? null) instanceof Collection && $productServices->isNotEmpty()) {
        $items = $productServices;
    }
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Product & Services') }}
@endsection

@push(ST::ADM_SCR_PG)
@endpush

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Product & Services') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="{{ $importUrl }}"
           data-size="md"
           data-bs-toggle="tooltip"
           title="{{ __('Import') }}"
           data-url="{{ $importUrl }}"
           data-ajax-popup="true"
           data-title="{{ __('Import product CSV file') }}"
           data-sv-localized="true"
           data-guard-msg="{{ $importGuard }}"
           class="{{ VC::BT_SM_PM }}">
            <i class="{{ VC::TI_IMP }}"></i>
        </a>

        <a href="{{ $exportUrl }}"
           data-bs-toggle="tooltip"
           title="{{ __('Export') }}"
           data-url="{{ $exportUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $exportGuard }}"
           class="{{ VC::BT_SM_PM }}">
            <i class="{{ VC::TI_EXP }}"></i>
        </a>

        <a href="{{ $createUrl }}"
           data-size="lg"
           data-url="{{ $createUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Create New Product') }}"
           data-title="{{ __('Create New Product') }}"
           data-sv-localized="true"
           data-guard-msg="{{ $createGuard }}"
           class="{{ VC::BT_SM_PM }}">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    @php
                        $psIndexBase             = VW::PRD_SV.'.index';
                        $psIndexKebab            = Str::kebab($psIndexBase);
                        $psIndexResolved         = Route::has($psIndexBase) ? $psIndexBase : (Route::has($psIndexKebab) ? $psIndexKebab : null);
                        $psIndexUrl              = $psIndexResolved ? route($psIndexResolved) : '#';
                        $psFormId                = 'product-service-filter-form';
                        $psIndexGuardMsg         = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'product_services_index_route_unavailable') ?? 'Product & Service index route is unavailable. Please contact technical support or your domain administrator.';
                        $categoryIsList          = (is_array($category ?? null) && count($category ?? []) > 0) || (($category ?? null) instanceof Collection && $category->isNotEmpty());
                        $categoryOptions         = $categoryIsList ? (is_array($category) ? $category : $category->toArray()) : [];
                        $selectedCategory        = request('category');
                        $applyBtnId              = 'product-service-apply-btn';
                        $resetLinkId             = 'product-service-reset-link';
                    @endphp
                    {{ Form::open([
                        'url'               => $psIndexUrl,
                        'method'            => 'GET',
                        'id'                => $psFormId,
                        'data-url'          => $psIndexUrl,
                        'data-guard-msg'    => $psIndexGuardMsg,
                        'data-sv-localized' => 'true',
                    ]) }}
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('category', __('Category'), ['class'=> VC::FM_LB]) }}
                                    {{ Form::select('category', $categoryOptions, $selectedCategory, array_merge([
                                        'class'     => VC::FM_CT_SL,
                                        'id'        => 'choices-multiple',
                                        'required'  => 'required',
                                        'placeholder' => __('Select Category'),
                                    ], $categoryIsList ? [] : ['disabled' => 'disabled'])) }}
                                </div>
                            </div>
                            <div class="{{ VC::C_AT_FEND }}">
                                <a
                                    id="{{ $applyBtnId }}"
                                    href="{{ $psIndexUrl }}"
                                    data-url="{{ $psIndexUrl }}"
                                    data-guard-msg="{{ $psIndexGuardMsg }}"
                                    data-sv-localized="true"
                                    class="{{ VC::BT_SM_PM }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('apply') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                </a>
                                <a
                                    id="{{ $resetLinkId }}"
                                    href="{{ $psIndexUrl }}"
                                    data-url="{{ $psIndexUrl }}"
                                    data-guard-msg="{{ $psIndexGuardMsg }}"
                                    data-sv-localized="true"
                                    class="{{ VC::BT_SM_DG }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                </a>
                            </div>
                        </div>
                        <script defer src="{{ asset('assets/js/routes/products/services/index.js') }}"></script>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Sku') }}</th>
                                    <th>{{ __('Sale Price') }}</th>
                                    <th>{{ __('Purchase Price') }}</th>
                                    <th>{{ __('Tax') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $productService)
                                    @php
                                        $psId      = data_get($productService,'id');
                                        $name      = data_get($productService,'name', __('(no name)'));
                                        $sku       = data_get($productService,'sku', __('(no sku)'));
                                        $sale      = data_get($productService,'sale_price');
                                        $purchase  = data_get($productService,'purchase_price');
                                        $typeRaw   = strtolower((string) data_get($productService,'type',''));
                                        $typeTxt   = $typeRaw ? ucwords($typeRaw) : __('Unknown');
                                        $categoryN = data_get($productService,'category.name', __('(no category)'));
                                        $unitN     = data_get($productService,'unit.name', __('(no unit)'));
                                        $qty       = $typeRaw === 'product' ? (data_get($productService,'quantity') ?? 0) : '-';
                                        $detailUrl   = Route::has(VW::PRD_SV.'.detail') ? route(VW::PRD_SV.'.detail', $psId) : '#';
                                        $detailGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'warehouse_details_unavailable') : 'Warehouse Details route is unavailable. Please contact technical support or your domain administrator.') ?? __('Warehouse Details route is unavailable. Please contact technical support or your domain administrator.');
                                        $taxHtml = '-';
                                        $taxId   = data_get($productService,'tax_id');
                                        if (!empty($taxId)) {
                                            $taxes = \App\Models\Utility::tax($taxId);
                                            $titems = [];
                                            if (is_array($taxes) && count($taxes)) {
                                                $titems = $taxes;
                                            } elseif ($taxes instanceof Collection && $taxes->isNotEmpty()) {
                                                $titems = $taxes;
                                            }
                                            if (!empty($titems)) {
                                                $parts = [];
                                                foreach ($titems as $tx) {
                                                    $tn = data_get($tx,'name');
                                                    $tr = data_get($tx,'rate');
                                                    if ($tn !== null && $tr !== null) {
                                                        $parts[] = e($tn).' ('.e($tr).'%)';
                                                    }
                                                }
                                                $taxHtml = !empty($parts) ? implode('<br>', $parts) : '-';
                                            }
                                        }
                                    @endphp
                                    <tr class="font-style">
                                        <td>{{ $name }}</td>
                                        <td>{{ $sku }}</td>
                                        <td>{{ $canPriceFormat ? $user?->priceFormat($sale) : __('Failed to format sale price') }}</td>
                                        <td>{{ $canPriceFormat ? $user?->priceFormat($purchase) : __('Failed to format purchase price') }}</td>
                                        <td>{!! $taxHtml !!}</td>
                                        <td>{{ $categoryN }}</td>
                                        <td>{{ $unitN }}</td>
                                        <td>{{ $qty }}</td>
                                        <td>{{ $typeTxt }}</td>
                                        <td class="Action">
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                <a href="{{ $detailUrl }}"
                                                   class="{{ VC::BT_SM_CT }}"
                                                   data-url="{{ $detailUrl }}"
                                                   data-ajax-popup="true"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Warehouse Details') }}"
                                                   data-title="{{ __('Warehouse Details') }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $detailGuard }}">
                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                </a>
                                            </div>

                                            @can('edit product & service')
                                            @php
                                                $editUrl   = Route::has(VW::PRD_SV.'.edit') ? route(VW::PRD_SV.'.edit', $psId) : '#';
                                                $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'edit_product_service_unavailable') : 'Edit Product Service route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Product Service route is unavailable. Please contact technical support or your domain administrator.');
                                            @endphp
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    <a href="{{ $editUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-size="lg"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}"
                                                       data-title="{{ __('Edit Product') }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ $editGuard }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('delete product & service')
                                                @php
                                                    $delUrl    = Route::has(VW::PRD_SV.'.destroy') ? route(VW::PRD_SV.'.destroy', $psId) : '#';
                                                    $delGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'delete_product_service_unavailable') : 'Delete Product Service route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Product Service route is unavailable. Please contact technical support or your domain administrator.');
                                                    $formId    = 'delete-form-'.$psId;
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $formId, 'data-url'=>$delUrl, 'data-sv-localized'=>'true', 'data-guard-msg'=>$delGuard]) !!}
                                                        <a href="{{ $delUrl }}"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-url="{{ $delUrl }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ $delGuard }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">{{ __('No products or services found.') }}</td>
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

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/products/services/index.js') }}"></script>
@endpush
