@php
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC
        StacksConstants
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $fields = [
        ['name'=>'date','type'=>'date','label'=>__('Date'),'cols'=>6,'attrs'=>['class'=>'form-control','required'=>'required']],
        ['name'=>'amount','type'=>'number','label'=>__('Amount'),'value'=>$invoice->getDue(),'cols'=>6,'attrs'=>['class'=>'form-control','required'=>'required','step'=>'0.01']],
        ['name'=>'account_id','type'=>'select','label'=>__('Account'),'options'=>$accounts,'cols'=>6,'attrs'=>['class'=>'form-control select','required'=>'required']],
        ['name'=>'reference','type'=>'text','label'=>__('Reference'),'cols'=>6,'attrs'=>['class'=>'form-control']],
        ['name'=>'description','type'=>'textarea','label'=>__('Description'),'cols'=>12,'attrs'=>['class'=>'form-control','rows'=>3]],
        ['name'=>'add_receipt','type'=>'file','label'=>__('Payment Receipt'),'cols'=>6,'attrs'=>['class'=>'form-control']],
    ];
    $lang                        = Utility::fetchUserLang();
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
                    @php $attrs = $f['attrs'] ?? []; @endphp

                    @if($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], $f['value'] ?? null, $attrs) }}
                    @elseif($f['type'] === 'select')
                        {{ Form::select($f['name'], $f['options'], $f['value'] ?? null, $attrs) }}
                    @elseif($f['type'] === 'file')
                        <div class="choose-file {{ VC::FM_G }}">
                            {{ Form::file($f['name'], $attrs) }}
                            <p class="upload_file"></p>
                        </div>
                    @else
                        {{ Form::{ $f['type'] }($f['name'], $f['value'] ?? null, $attrs) }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Add') }}</button>
    </div>
{{ Form::close() }}
@push(StacksConstants::ADM_SCR_PG)
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

                const msg           = form.getAttribute('data-guard-msg') || '# ERROR';
                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                let container       = document.getElementById('toast-container');
                if (!container) {
                    container       = document.createElement('div');
                    container.id    = 'toast-container';
                    document.body.appendChild(container);
                }
                if (bootstrapLink && window.bootstrap) {
                    const toastEl      = document.createElement('div');
                    toastEl.className  = 'toast';
                    toastEl.setAttribute('role', 'alert');
                    toastEl.setAttribute('aria-live', 'assertive');
                    toastEl.setAttribute('aria-atomic', 'true');
                    const body         = document.createElement('div');
                    body.className     = 'toast-body';
                    body.textContent   = msg;
                    toastEl.appendChild(body);
                    container.appendChild(toastEl);
                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                } else {
                    alert(msg);
                }
                form.setAttribute('data-failed-route', 'true');
            } catch (e) {}
        });
    })();
    </script>
@endpush


