@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants
    };

    $lang                  = Utility::fetchUserLang();
    $billPaymentRoute      = Route::has(ViewsConstants::BIL . '.payment')
        ? route(ViewsConstants::BIL . '.payment', $bill->id)
        : '#';
    $billPaymentFormId     = 'bill-payment-form-' . $bill->id;
    $billPaymentGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BIL,
        'bill_payment_route_unavailable'
    ) ?? 'Bill payment route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{{ Form::open([
    'route'            => $billPaymentRoute,
    'id'             => $billPaymentFormId,
    'data-url'       => $billPaymentRoute,
    'data-guard-msg' => $billPaymentGuardMsg,
    'method'         => 'post',
    'enctype'        => 'multipart/form-data',
]) }}
    <div class="modal-body">
        <div class="row">
            @foreach($fields as $f)
                <div class="{{ $f['colClass'] }} {{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => ViewClassNamesConstants::FM_LB]) }}
                    @if($f['type'] === 'select')
                        {{ Form::select($f['name'], $f['options'], $f['value'] ?? null, $f['attrs']) }}
                    @elseif($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], $f['value'] ?? null, $f['attrs']) }}
                    @else
                        {{ Form::{ $f['type'] }($f['name'], $f['value'] ?? null, $f['attrs']) }}
                    @endif
                </div>
            @endforeach

            <div class="{{ ViewClassNamesConstants::CM6 }} {{ ViewClassNamesConstants::FM_G }}">
                {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                <div class="choose-file">
                    <label for="add_receipt">
                        <input
                            type="file"
                            name="add_receipt"
                            id="add_receipt"
                            class="{{ ViewClassNamesConstants::FM_CT }}"
                        >
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
            {{ __('Cancel') }}
        </button>
        <button type="submit" class="{{ ViewClassNamesConstants::BT_PRM }}">
            {{ __('Add') }}
        </button>
    </div>
{{ Form::close() }}
