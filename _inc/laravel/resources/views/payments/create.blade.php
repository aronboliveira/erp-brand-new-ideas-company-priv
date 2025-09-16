@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId     = 'pay-store-form';
    $storeBase  = VW::PAY;
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::PAY, 'store_route_unavailable') ?? __('Payment store route is unavailable. Please contact technical support or your domain administrator.');

    $vendorsIsList = (is_array($vendors ?? null) && count($vendors ?? []) > 0) || (($vendors ?? null) instanceof Collection && $vendors->isNotEmpty());
    $vendorOptions = $vendorsIsList ? (is_array($vendors) ? $vendors : $vendors->toArray()) : ['' => __('No vendors available')];
    $vendorErr     = $errors->has('vendor_id');
    $vendorAttrs   = [
        'id'               => 'vendor_id',
        'class'            => trim(VC::FM_CT_SL.' select '.($vendorErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $vendorErr ? 'true' : 'false',
        'aria-describedby' => $vendorErr ? 'vendor_id-error' : null,
    ];
    if (!$vendorsIsList) { $vendorAttrs['disabled'] = 'disabled'; }

    $dateErr = $errors->has('date');
    $dateAttrs = [
        'id'               => 'date',
        'class'            => trim(VC::FM_CT.' '.($dateErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $dateErr ? 'true' : 'false',
        'aria-describedby' => $dateErr ? 'date-error' : null,
    ];

    $amountErr = $errors->has('amount');
    $amountAttrs = [
        'id'               => 'amount',
        'class'            => trim(VC::FM_CT.' '.($amountErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $amountErr ? 'true' : 'false',
        'aria-describedby' => $amountErr ? 'amount-error' : null,
        'step'             => '0.01',
        'inputmode'        => 'decimal',
    ];

    $categoriesIsList = (is_array($categories ?? null) && count($categories ?? []) > 0) || (($categories ?? null) instanceof Collection && $categories->isNotEmpty());
    $categoryOptions  = $categoriesIsList ? (is_array($categories) ? $categories : $categories->toArray()) : ['' => __('No categories available')];
    $categoryErr      = $errors->has('category_id');
    $categoryAttrs    = [
        'id'               => 'category_id',
        'class'            => trim(VC::FM_CT_SL.' select '.($categoryErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $categoryErr ? 'true' : 'false',
        'aria-describedby' => $categoryErr ? 'category_id-error' : null,
    ];
    if (!$categoriesIsList) { $categoryAttrs['disabled'] = 'disabled'; }

    $accountsIsList = (is_array($accounts ?? null) && count($accounts ?? []) > 0) || (($accounts ?? null) instanceof Collection && $accounts->isNotEmpty());
    $accountOptions = $accountsIsList ? (is_array($accounts) ? $accounts : $accounts->toArray()) : ['' => __('No accounts available')];
    $accountErr     = $errors->has('account_id');
    $accountAttrs   = [
        'id'               => 'account_id',
        'class'            => trim(VC::FM_CT_SL.' select '.($accountErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $accountErr ? 'true' : 'false',
        'aria-describedby' => $accountErr ? 'account_id-error' : null,
    ];
    if (!$accountsIsList) { $accountAttrs['disabled'] = 'disabled'; }

    $referenceAttrs = ['id' => 'reference', 'class' => VC::FM_CT, 'autocomplete' => 'off'];

    $receiptAttrs = [
        'id'                 => 'add_receipt',
        'class'              => VC::FM_CT,
        'data-preview-target'=> '#pay-receipt-preview',
    ];

    $descAttrs = ['id' => 'description', 'class' => VC::FM_CT, 'rows' => 3];
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'enctype'           => 'multipart/form-data',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('vendor_id', __('Vendor'), ['class' => VC::FM_LB]) }}
                {{ Form::select('vendor_id', $vendorOptions, null, $vendorAttrs) }}
                @error('vendor_id')
                    <span id="vendor_id-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('date', null, $dateAttrs) }}
                @error('date')
                    <span id="date-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
                {{ Form::number('amount', null, $amountAttrs) }}
                @error('amount')
                    <span id="amount-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}
                {{ Form::select('category_id', $categoryOptions, null, $categoryAttrs) }}
                @error('category_id')
                    <span id="category_id-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('account_id', __('Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('account_id', $accountOptions, null, $accountAttrs) }}
                @error('account_id')
                    <span id="account_id-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
                {{ Form::text('reference', null, $referenceAttrs) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => VC::FM_LB]) }}
                {{ Form::file('add_receipt', $receiptAttrs) }}
                <img id="pay-receipt-preview" class="mt-2" style="width:25%;" alt="{{ __('Receipt preview') }}">
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, $descAttrs) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/payments/store.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/payments/storeFilePreview.js') }}"></script>
{{ Form::close() }}