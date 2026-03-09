@php
    try {
$lang = Utility::fetchUserLang();

        $billIdIsValid = isset($bill) && isset($bill->id);
        $billPaymentRoute = (Route::has(ViewsConstants::BIL . '.payment') && $billIdIsValid)
            ? route(ViewsConstants::BIL . '.payment', $bill->id)
            : '#';

        $billPaymentFormId = $billIdIsValid ? ('bill-payment-form-' . $bill->id) : 'bill-payment-form';

        $billPaymentGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BIL,
            'bill_payment_route_unavailable'
        ) ?? 'Bill payment route is unavailable. Please contact technical support or your domain administrator.';

        $fieldsIsArray      = is_array($fields ?? null) && count($fields ?? []) > 0;
        $fieldsIsCollection = ($fields ?? null) instanceof \Illuminate\Support\Collection && ($fields)->isNotEmpty();
        $fieldsIterable     = $fieldsIsArray || $fieldsIsCollection;
    } catch (\Throwable $e) {
        \Log::error('bills/payment — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'            => $billPaymentRoute,
    'id'             => $billPaymentFormId,
    'data-url'       => $billPaymentRoute,
    'data-guard-msg' => $billPaymentGuardMsg,
    'method'         => 'post',
    'enctype'        => 'multipart/form-data',
]) }}
    <div class="modal-body">
        <div class="row">
            @if($fieldsIterable)
                @foreach(($fieldsIsCollection ? $fields : collect($fields)) as $f)
                    @php
                        try {
                            $name     = isset($f['name']) && $f['name'] !== '' ? (string) $f['name'] : null;
                            $label    = isset($f['label']) ? (string) $f['label'] : '';
                            $type     = isset($f['type'])  ? (string) $f['type']  : 'text';
                            $value    = $f['value'] ?? null;
                            $options  = (isset($f['options']) && (is_array($f['options']) || ($f['options'] instanceof \Illuminate\Support\Collection))) ? $f['options'] : [];
                            $attrsRaw = $f['attrs'] ?? [];
                            $attrs    = is_array($attrsRaw) ? $attrsRaw : [];
                            $colClass = isset($f['colClass']) ? (string) $f['colClass'] : ViewClassNamesConstants::CM6;

                            $supportedBasic = ['text','email','number','date','password','hidden','url','tel'];
                            $fieldHtml = '';

                            if ($name !== null) {
                                if ($type === 'select') {
                                    $fieldHtml = \Collective\Html\FormFacade::select($name, $options, $value, $attrs);
                                } elseif ($type === 'textarea') {
                                    $fieldHtml = \Collective\Html\FormFacade::textarea($name, $value, $attrs);
                                } elseif (in_array($type, $supportedBasic, true)) {
                                    $fieldHtml = call_user_func([\Collective\Html\FormFacade::class, $type], $name, $value, $attrs);
                                } else {
                                    $fieldHtml = \Collective\Html\FormFacade::text($name, $value, $attrs);
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::error('bills/payment — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ $colClass }} {{ ViewClassNamesConstants::FM_G }}">
                        {{ Form::label($name ?? Str::random(6), $label !== '' ? $label : __('No label provided'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                        {!! $fieldHtml !== '' ? $fieldHtml : \Collective\Html\FormFacade::text($name ?? Str::random(6), null, $attrs) !!}
                    </div>
                @endforeach
            @else
                <div class="{{ ViewClassNamesConstants::CM12 }} {{ ViewClassNamesConstants::FM_G }}">
                    <div class="{{ VC::ALT_WRN_MB0 }}">
                        {{ __('No fields available to display.') }}
                    </div>
                </div>
            @endif

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
