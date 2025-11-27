@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId    = 'prd-sv-store-form';
    $base      = VW::PRD_SV;
    $baseKebab = Str::kebab($base);
    $routeRes  = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
    $actionUrl = $routeRes ? route($routeRes) : '#';
    $guardMsg  = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'store_route_unavailable') ?? __('Product Service store route is unavailable. Please contact technical support or your domain administrator.');

    $aiBase    = 'generate';
    $aiResolved= Route::has($aiBase) ? $aiBase : (Route::has(Str::kebab($aiBase)) ? Str::kebab($aiBase) : null);
    $aiUrl     = $aiResolved ? route($aiResolved, [VW::PRD_SV]) : '#';
    $aiGuard   = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'generate_route_unavailable') ?? __('AI generate route is unavailable for Product Services. Please contact technical support or your domain administrator.');

    $incomeIsList  = (is_array($incomeChartAccounts ?? null) && count($incomeChartAccounts ?? []) > 0) || (($incomeChartAccounts ?? null) instanceof Collection && $incomeChartAccounts->isNotEmpty());
    $incomeOptions = $incomeIsList ? (is_array($incomeChartAccounts) ? $incomeChartAccounts : $incomeChartAccounts->toArray()) : ['' => __('No income accounts available')];

    $expenseIsList  = (is_array($expenseChartAccounts ?? null) && count($expenseChartAccounts ?? []) > 0) || (($expenseChartAccounts ?? null) instanceof Collection && $expenseChartAccounts->isNotEmpty());
    $expenseOptions = $expenseIsList ? (is_array($expenseChartAccounts) ? $expenseChartAccounts : $expenseChartAccounts->toArray()) : ['' => __('No expense accounts available')];

    $taxIsList  = (is_array($tax ?? null) && count($tax ?? []) > 0) || (($tax ?? null) instanceof Collection && $tax->isNotEmpty());
    $taxOptions = $taxIsList ? (is_array($tax) ? $tax : $tax->toArray()) : ['' => __('No taxes available')];

    $catIsList  = (is_array($category ?? null) && count($category ?? []) > 0) || (($category ?? null) instanceof Collection && $category->isNotEmpty());
    $catOptions = $catIsList ? (is_array($category) ? $category : $category->toArray()) : ['' => __('No categories available')];

    $unitIsList  = (is_array($unit ?? null) && count($unit ?? []) > 0) || (($unit ?? null) instanceof Collection && $unit->isNotEmpty());
    $unitOptions = $unitIsList ? (is_array($unit) ? $unit : $unit->toArray()) : ['' => __('No units available')];

    $catIndexBase   = VW::PRD_SV_CAT . '.index';
    $catIndexKebab  = Str::kebab($catIndexBase);
    $catIndexRes    = Route::has($catIndexBase) ? $catIndexBase : (Route::has($catIndexKebab) ? $catIndexKebab : null);
    $catIndexUrl    = $catIndexRes ? route($catIndexRes) : '#';

    $nameErr = $errors->has('name');
    $skuErr  = $errors->has('sku');

    $nameAttrs = [
        'id'               => 'name',
        'class'            => trim(VC::FM_CT . ($nameErr ? ' is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $nameErr ? 'true' : 'false',
        'aria-describedby' => $nameErr ? 'name-error' : null,
        'autocomplete'     => 'off',
    ];
    $skuAttrs = [
        'id'               => 'sku',
        'class'            => trim(VC::FM_CT . ($skuErr ? ' is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $skuErr ? 'true' : 'false',
        'aria-describedby' => $skuErr ? 'sku-error' : null,
        'autocomplete'     => 'off',
    ];

    $saleAttrs = ['id' => 'sale_price', 'class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01', 'inputmode' => 'decimal'];
    $purchaseAttrs = ['id' => 'purchase_price', 'class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01', 'inputmode' => 'decimal'];

    $incomeAttrs = ['id' => 'sale_chart_account_id', 'class' => trim(VC::FM_CT_SL . ' select' . ($incomeIsList ? '' : ' is-invalid')), 'required' => 'required'] + ($incomeIsList ? [] : ['disabled' => 'disabled']);
    $expenseAttrs = ['id' => 'expense_chart_account_id', 'class' => trim(VC::FM_CT_SL . ' select' . ($expenseIsList ? '' : ' is-invalid')), 'required' => 'required'] + ($expenseIsList ? [] : ['disabled' => 'disabled']);

    $taxAttrs = ['id' => 'choices-multiple1', 'class' => 'form-control select2', 'multiple' => 'multiple'] + ($taxIsList ? [] : ['disabled' => 'disabled']);
    $catAttrs = ['id' => 'category_id', 'class' => trim(VC::FM_CT_SL . ' select' . ($catIsList ? '' : ' is-invalid')), 'required' => 'required'] + ($catIsList ? [] : ['disabled' => 'disabled']);
    $unitAttrs = ['id' => 'unit_id', 'class' => trim(VC::FM_CT_SL . ' select' . ($unitIsList ? '' : ' is-invalid')), 'required' => 'required'] + ($unitIsList ? [] : ['disabled' => 'disabled']);
@endphp

{{ Form::open([
    'url'               => $actionUrl,
    'enctype'           => 'multipart/form-data',
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        @php $plan = Utility::getChatGPTSettings(); @endphp
        @if($plan?->{\App\Config\Constants\PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   class="btn btn-primary btn-icon btn-sm ai-btn"
                   data-size="md"
                   data-ajax-popup-over="true"
                   data-url="{{ $aiUrl }}"
                   data-guard-msg="{{ $aiGuard }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, $nameAttrs) }}
                @error('name')<span id="name-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>@enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('sku', __('SKU'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::text('sku', null, $skuAttrs) }}
                @error('sku')<span id="sku-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>@enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('sale_price', __('Sale Price'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::number('sale_price', null, $saleAttrs) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('sale_chart_account_id', __('Income Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('sale_chart_account_id', $incomeOptions, null, $incomeAttrs) }}
                @unless($incomeIsList)<span class="d-block {{ VC::TXT_MT }}">{{ __('No income accounts available') }}</span>@endunless
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('purchase_price', __('Purchase Price'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::number('purchase_price', null, $purchaseAttrs) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('expense_chart_account_id', __('Expense Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('expense_chart_account_id', $expenseOptions, null, $expenseAttrs) }}
                @unless($expenseIsList)<span class="d-block {{ VC::TXT_MT }}">{{ __('No expense accounts available') }}</span>@endunless
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('tax_id', __('Tax'), ['class' => VC::FM_LB]) }}
                {{ Form::select('tax_id[]', $taxOptions, null, $taxAttrs) }}
                @unless($taxIsList)<span class="d-block {{ VC::TXT_MT }}">{{ __('No taxes available') }}</span>@endunless
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::select('category_id', $catOptions, null, $catAttrs) }}
                <div class="text-xs">
                    {{ __('Please add constant category.') }}
                    <a href="{{ $catIndexUrl }}" {{ $catIndexRes ? '' : 'aria-disabled=true' }}><b>{{ __('Add Category') }}</b></a>
                </div>
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('unit_id', __('Unit'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::select('unit_id', $unitOptions, null, $unitAttrs) }}
                @unless($unitIsList)<span class="d-block {{ VC::TXT_MT }}">{{ __('No units available') }}</span>@endunless
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('pro_image', __('Product Image'), ['class' => VC::FM_LB]) }}
                <div class="choose-file">
                    <input type="file" class="{{ VC::FM_CT }}" name="pro_image" id="pro_image" data-preview-target="pro_image_preview">
                    <img id="pro_image_preview" class="mt-3" style="width:25%;"/>
                </div>
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                <label class="{{ VC::FM_LB }} d-block">{{ __('Type') }}</label>
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CM6 }}">
                        <div class="{{ VC::FM_CHK_IL }}">
                            <input type="radio" class="form-check-input type" id="type-product" name="type" value="product" checked="checked">
                            <label class="custom-control-label form-label" for="type-product">{{ __('Product') }}</label>
                        </div>
                    </div>
                    <div class="{{ VC::CM6 }}">
                        <div class="{{ VC::FM_CHK_IL }}">
                            <input type="radio" class="form-check-input type" id="type-service" name="type" value="service">
                            <label class="custom-control-label form-label" for="type-service">{{ __('Service') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::FM_GCB6 }} quantity">
                {{ Form::label('quantity', __('Quantity'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::text('quantity', null, ['id' => 'quantity', 'class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 2]) }}
            </div>

            @php $customFieldsIsList = ($customFields ?? null) instanceof Collection && $customFields->isNotEmpty(); @endphp
            @if($customFieldsIsList)
                <div class="{{ VC::CLM6 }}">
                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                        @include(VW::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script async src="{{ asset('assets/js/routes/products/services/lang/create.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/products/services/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/products/services/toggleType.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/products/services/preview.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/products/services/inputPreview.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/products/services/generateStore.js') }}"></script>
{{ Form::close() }}

