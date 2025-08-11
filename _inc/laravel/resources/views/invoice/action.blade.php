@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    $user        = Auth::user();
    $lang        = Utility::fetchUserLang(user:$user);
    $routeName   = ViewsConstants::INV . '.change.status';
    $changeRoute = Route::has($routeName)
        ? route($routeName, $invoiceBankTransfer->id)
        : '#';
    $formId      = 'changeStatusForm_' . $invoiceBankTransfer->id;
    $guardMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'change_status_route_unavailable'
    ) ?? 'Change status route is unavailable. Please contact technical support or your domain administrator.';
    $path        = Utility::getFile('uploads/order');
@endphp

{{ Form::open([
    'route'          => [ViewsConstants::INV . '.change.status', $invoiceBankTransfer->id],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $changeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
<div class="modal-body">
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <table class="{{ VC::TB }} modal-table">
                <tr>
                    <th>{{ __('Invoice Number') }}</th>
                    <td>{{ $user?->invoiceNumberFormat($invoice->invoice_id) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Order Id') }}</th>
                    <td>{{ $invoiceBankTransfer->order_id }}</td>
                </tr>
                <tr>
                    <th>{{ __('Amount') }}</th>
                    <td>{{ $invoiceBankTransfer->amount }}</td>
                </tr>
                <tr>
                    <th>{{ __('Payment Type') }}</th>
                    <td>{{ __('Bank Transfer') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Payment Status') }}</th>
                    <td>{{ $invoiceBankTransfer->status }}</td>
                </tr>
                <tr>
                    <th>{{ __('Bank Details') }}</th>
                    <td>{!! $company_payment_setting['bank_details'] !!}</td>
                </tr>
                @if(!empty($invoiceBankTransfer->receipt))
                    <tr>
                        <th>{{ __('Payment Receipt') }}</th>
                        <td>
                            <a href="{{ $path . '/' . $invoiceBankTransfer->receipt }}"
                               download
                               target="_blank"
                               data-bs-toggle="tooltip"
                               title="{{ __('Download') }}"
                               class="{{ VC::ACT_BTN_PRIM }} {{ VC::ALC }}">
                                <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                            </a>
                        </td>
                    </tr>
                @endif
                <input type="hidden" name="order_id" value="{{ $invoiceBankTransfer->id }}">
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input
        type="submit"
        name="status"
        value="{{ __('Approval') }}"
        class="{{ VC::BT }} btn-success"
    >
    <input
        type="submit"
        name="status"
        value="{{ __('Reject') }}"
        class="{{ VC::BT }} btn-danger"
    >
</div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', event => {
                try {
                    const url = form.getAttribute('data-url') ?? '#';
                    if (url !== '#') return;
                    event.preventDefault();
                    const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
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
