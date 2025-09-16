@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();

    $formId     = 'ln-store-form';
    $storeBase  = VW::LN;
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::LN, 'store_route_unavailable') ?? __('Loan store route is unavailable. Please contact technical support or your domain administrator.');

    $employeeId = (string) data_get($employee ?? null, 'id', '');

    $titleErr = $errors->has('title');
    $titleAttrs = [
        'id'               => 'title',
        'class'            => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $titleErr ? 'true' : 'false',
        'aria-describedby' => $titleErr ? 'title-error' : null,
        'autocomplete'     => 'off',
    ];

    $loanOptsIsList = (is_array($loan_options ?? null) && count($loan_options ?? []) > 0) || (($loan_options ?? null) instanceof Collection && $loan_options->isNotEmpty());
    $loanOpts       = $loanOptsIsList ? (is_array($loan_options) ? $loan_options : $loan_options->toArray()) : ['' => __('No loan options available')];
    $loanOptErr     = $errors->has('loan_option');
    $loanOptAttrs   = [
        'id'               => 'loan_option',
        'class'            => trim(VC::FM_CT_SL . ' select ' . ($loanOptErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $loanOptErr ? 'true' : 'false',
        'aria-describedby' => $loanOptErr ? 'loan_option-error' : null,
    ];
    if (!$loanOptsIsList) { $loanOptAttrs['disabled'] = 'disabled'; }

    $loanTypesIsList = (is_array($loan ?? null) && count($loan ?? []) > 0) || (($loan ?? null) instanceof Collection && $loan->isNotEmpty());
    $loanTypes       = $loanTypesIsList ? (is_array($loan) ? $loan : $loan->toArray()) : ['' => __('No loan types available')];
    $typeErr         = $errors->has('type');
    $typeAttrs       = [
        'id'               => 'type',
        'class'            => trim(VC::FM_CT_SL . ' select amount_type ' . ($typeErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $typeErr ? 'true' : 'false',
        'aria-describedby' => $typeErr ? 'type-error' : null,
    ];
    if (!$loanTypesIsList) { $typeAttrs['disabled'] = 'disabled'; }

    $amountErr = $errors->has('amount');
    $amountAttrs = [
        'id'               => 'amount',
        'class'            => trim(VC::FM_CT . ' ' . ($amountErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $amountErr ? 'true' : 'false',
        'aria-describedby' => $amountErr ? 'amount-error' : null,
        'step'             => '0.01',
        'inputmode'        => 'decimal',
    ];

    $reasonErr = $errors->has('reason');
    $reasonAttrs = [
        'id'               => 'reason',
        'class'            => trim(VC::FM_CT . ' ' . ($reasonErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'rows'             => 3,
        'aria-invalid'     => $reasonErr ? 'true' : 'false',
        'aria-describedby' => $reasonErr ? 'reason-error' : null,
    ];
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    {{ Form::hidden('employee_id', $employeeId, ['id' => 'employee_id']) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('loan_option', __('Loan Options'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                {{ Form::select('loan_option', $loanOpts, null, $loanOptAttrs) }}
                @error('loan_option')
                    <span id="loan_option-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $loanTypes, null, $typeAttrs) }}
                @error('type')
                    <span id="type-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('amount', __('Loan Amount'), ['class' => 'form-label amount_label']) }}
                {{ Form::number('amount', null, $amountAttrs) }}
                @error('amount')
                    <span id="amount-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('reason', __('Reason'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('reason', null, $reasonAttrs) }}
                @error('reason')
                    <span id="reason-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/loans/store.js') }}"></script>
{{ Form::close() }}
