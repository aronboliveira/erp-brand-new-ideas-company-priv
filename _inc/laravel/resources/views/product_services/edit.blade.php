@php
    use App\Config\Constants\{
        DatabaseConstants,
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang        = Utility::fetchUserLang();
    $plan        = Utility::getChatGPTSettings();

    $hasModel    = !empty($productService ?? null) && data_get($productService, 'id');

    $updateBase     = VW::PRD_SV . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $productService->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'update_route_unavailable')
                        ?? __('Update Product Service route is unavailable. Please contact technical support or your domain administrator.');

    $genUrl   = route('generate', [VW::PRD_SV]);
    $genGuard = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'generate_route_unavailable')
                ?? __('Generate content route for Product Service is unavailable. Please contact technical support or your domain administrator.');

    $fields = [
        ['name'=>'name',                   'type'=>'text',     'label'=>__('Name'),             'required'=>true],
        ['name'=>'sku',                    'type'=>'text',     'label'=>__('SKU'),              'required'=>true],
        ['name'=>'sale_price',             'type'=>'number',   'label'=>__('Sale Price'),       'required'=>true,'step'=>'0.01'],
        ['name'=>'sale_chartaccount_id',   'type'=>'select',   'label'=>__('Income Account'),   'options'=>$incomeChartAccounts ?? [],'required'=>true],
        ['name'=>'purchase_price',         'type'=>'number',   'label'=>__('Purchase Price'),   'required'=>true,'step'=>'0.01'],
        ['name'=>'expense_chartaccount_id','type'=>'select',   'label'=>__('Expense Account'),  'options'=>$expenseChartAccounts ?? [],'required'=>true],
        ['name'=>'tax_id',                 'type'=>'select2',  'label'=>__('Tax'),              'options'=>$tax ?? [],'multiple'=>true],
        ['name'=>'category_id',            'type'=>'select',   'label'=>__('Category'),         'options'=>$category ?? [],'required'=>true],
        ['name'=>'unit_id',                'type'=>'select',   'label'=>__('Unit'),             'options'=>$unit ?? [],'required'=>true],
        ['name'=>'description',            'type'=>'textarea','label'=>__('Description'),      'rows'=>2],
    ];
@endphp

@if($hasModel)
    {{ Form::model($productService, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'enctype'           => 'multipart/form-data',
        'id'                => 'productService-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="text-end mb-3">
                    <a href="#"
                       class="{{ VC::BT_SM_PM }} btn-icon"
                       data-ajax-popup-over="true"
                       data-size="md"
                       data-url="{{ $genUrl }}"
                       data-guard-msg="{{ $genGuard }}"
                       data-sv-localized="true"
                       data-bs-placement="top"
                       data-title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i> {{ __('Generate with AI') }}
                    </a>
                </div>
            @endif

            <div class="row">
                @foreach($fields as $f)
                    @php
                        $attrs = ['class'=> VC::FM_CT];
                        if(!empty($f['required'])) $attrs['required']='required';
                        if(!empty($f['step']))     $attrs['step']=$f['step'];
                        $wrapClass = VC::FM_GCB6;
                    @endphp
                    <div class="{{ $wrapClass }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=> VC::FM_LB]) }}
                        @if(!empty($f['required']))<span class="text-danger">*</span>@endif

                        @if($f['type']=='text' || $f['type']=='number')
                            {{ $f['type']=='text' ? Form::text($f['name'], null, $attrs) : Form::number($f['name'], null, $attrs) }}
                        @elseif($f['type']=='select')
                            {{ Form::select($f['name'], $f['options'], null, ['class'=>VC::FM_CT_SL] + $attrs) }}
                        @elseif($f['type']=='select2')
                            {{ Form::select($f['name'] . (!empty($f['multiple']) ? '[]' : ''), $f['options'], null, $attrs + ['class'=>VC::FM_CT . ' select2','id'=>'choices-multiple1'] + (!empty($f['multiple']) ? ['multiple'=>'multiple'] : [])) }}
                        @elseif($f['type']=='textarea')
                            {{ Form::textarea($f['name'], null, $attrs + ['rows'=>$f['rows'] ?? 2]) }}
                        @endif
                    </div>
                @endforeach

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('pro_image', __('Product Image'), ['class'=> VC::FM_LB]) }}
                    <div class="choose-file">
                        <label for="pro_image" class="{{ VC::FM_LB }}">
                            <input
                                type="file"
                                name="pro_image"
                                id="pro_image"
                                class="{{ VC::FM_CT }}"
                                data-filename="pro_image_create"
                            >
                            <img
                                id="image"
                                class="mt-3"
                                width="100"
                                src="{{ $productService->pro_image
                                    ? asset(Storage::url('uploads/pro_image/'.$productService->pro_image))
                                    : asset(Storage::url('uploads/pro_image/user-2_1654779769.jpg')) }}"
                            />
                        </label>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    <label class="d-block {{ VC::FM_LB }}">{{ __('Type') }}</label>
                    <div class="row">
                        @foreach(['product','service'] as $type)
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::FM_CHK_IL }}">
                                    <input
                                        class="form-check-input type"
                                        type="radio"
                                        name="type"
                                        id="type_{{ $type }}"
                                        value="{{ $type }}"
                                        {{ $productService->type == $type ? 'checked' : '' }}
                                    >
                                    <label class="{{ VC::FM_LB }}" for="type_{{ $type }}">{{ ucfirst($type) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }} quantity {{ $productService->type=='service' ? 'd-none' : '' }}">
                    {{ Form::label('quantity', __('Quantity'), ['class'=> VC::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Form::text('quantity', null, [ 'class'=> VC::FM_CT, 'required'=>'required' ]) }}
                </div>

                @if(!empty($customFields) && $customFields instanceof Collection && $customFields->isNotEmpty())
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                            @include(VW::CST_FD . '.form_builder')
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script async src="{{ asset('assets/js/routes/products/services/lang/create.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/products/services/update.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/products/services/generateEdit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/products/services/editPreview.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No Product Service could be found.') }}</div>
@endif


