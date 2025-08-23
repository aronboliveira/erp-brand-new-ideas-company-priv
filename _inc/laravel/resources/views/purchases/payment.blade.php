@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};
    use Collective\Html\FormFacade as Form;

    $lang        = Utility::fetchUserLang();
    $purchaseId  = isset($purchase) && !empty(data_get($purchase, 'id')) ? data_get($purchase, 'id') : null;

    $payBase     = VW::PRC . '.payment';
    $payKebab    = Str::kebab($payBase);
    $payResolved = Route::has($payBase) ? $payBase : (Route::has($payKebab) ? $payKebab : null);

    $formId      = 'purchase-payment-form';
    $guardMsg    = Utility::fetchLinkMessage($lang, VW::PRC, 'payment_purchase_unavailable') ?? 'Purchase payment route is unavailable. Please contact technical support or your domain administrator.';

    $formOpen = [
        'method'         => 'post',
        'enctype'        => 'multipart/form-data',
        'id'             => $formId,
        'data-guard-msg' => $guardMsg,
    ];
    if ($payResolved && $purchaseId) {
        $formOpen['route'] = [$payResolved, $purchaseId];
    } else {
        $formOpen['url'] = '#';
    }
@endphp

{!! Form::open($formOpen) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('date', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
                {{ Form::number('amount', $purchase->getDue(), ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('account_id', __('Account'), ['class' => VC::FM_LB]) }}
                {{ Form::select('account_id', $accounts, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
                {{ Form::text('reference', '', ['class' => VC::FM_CT]) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', '', ['class' => VC::FM_CT, 'rows' => 3]) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => VC::FM_LB]) }}
                <div class="choose-file">
                    <label for="image" class="{{ VC::FM_LB }}">
                        <input type="file" name="add_receipt" id="image" class="{{ VC::FM_CT }}">
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Add') }}" class="{{ VC::BT_PRM }}">
        </div>
    </div>
{!! Form::close() !!}

@push(ST::ADM_SCRP_PG)
    <script defer src="{{ asset('assets/js/routes/purchases/payment.js') }}"></script>
@endpush
