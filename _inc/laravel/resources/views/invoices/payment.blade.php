@php
    try {
$lang                        = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('invoices/payment — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if(!empty($invoice) && isset($invoice->id))
    @php
        try {
            $fields = [
                ['name'=>'date','type'=>'date','label'=>__('Date'),'cols'=>6,'attrs'=>['class'=>'form-control','required'=>'required']],
                ['name'=>'amount','type'=>'number','label'=>__('Amount'),'value'=> is_callable([$invoice, 'getDue']) ? $invoice->getDue() : [__('No due invoice available')],'cols'=>6,'attrs'=>['class'=>'form-control','required'=>'required','step'=>'0.01']],
                ['name'=>'account_id','type'=>'select','label'=>__('Account'),'options'=> Utility::isFilled($accounts) ? $accounts : [__('No account available' ?? [])],'cols'=>6,'attrs'=>['class'=>'form-control select','required'=>'required']],
                ['name'=>'reference','type'=>'text','label'=>__('Reference'),'cols'=>6,'attrs'=>['class'=>'form-control']],
                ['name'=>'description','type'=>'textarea','label'=>__('Description'),'cols'=>12,'attrs'=>['class'=>'form-control','rows'=>3]],
                ['name'=>'add_receipt','type'=>'file','label'=>__('Payment Receipt'),'cols'=>6,'attrs'=>['class'=>'form-control']],
            ];
            $paymentRouteName            = ViewsConstants::INV . '.payment';
            $paymentActionUrl            = Route::has($paymentRouteName)
                ? route($paymentRouteName, $invoice->id)
                : '#';
            $paymentFormId               = 'invoice-payment-form-' . $invoice->id;
            $paymentGuardMsg             = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::INV,
                'invoice_payment_route_unavailable'
            ) ?? 'Invoice payment route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('invoices/payment — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    {{ Form::open([
        'route'           => $paymentActionUrl,
        'method'        => 'post',
        'enctype'       => 'multipart/form-data',
        'id'            => $paymentFormId,
        'data-url'      => $paymentActionUrl,
        'data-guard-msg'=> $paymentGuardMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                @foreach($fields as $f)
                    <div class="{{ VC::FM_G }} {{ 'col-md-' . $f['cols'] }}">
                        {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                        @php
 $attrs = $f['attrs'] ?? [];
@endphp

                        @if($f['type'] === 'textarea')
                            {{ Form::textarea($f['name'], $f['value'] ?? null, $attrs) }}
                        @elseif($f['type'] === 'select')
                            {{ Form::select($f['name'], $f['options'], $f['value'] ?? null, $attrs) }}
                        @elseif($f['type'] === 'file')
                            <div class="choose-file {{ VC::FM_G }}">
                                {{ Form::file($f['name'], $attrs) }}
                                <p class="upload_file"></p>
                            </div>
                        @elseif($f['type'] === 'date')
                            {{ Form::date($f['name'], $f['value'] ?? null, $attrs) }}
                        @elseif($f['type'] === 'number')
                            {{ Form::number($f['name'], $f['value'] ?? null, $attrs) }}
                        @else
                            {{ Form::text($f['name'], $f['value'] ?? null, $attrs) }}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Add') }}</button>
        </div>
        <script defer>
            (() => {
                const form = document.getElementById('{{ $paymentFormId }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', event => {
                    try {
                        const url = form.getAttribute('data-url') ?? '#';
                        if (url !== '#') return;
                        event.preventDefault();
                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                        form.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALT_DNG }}">
        {{ __('Invoice record is not available.') }}
    </div>
@endif
