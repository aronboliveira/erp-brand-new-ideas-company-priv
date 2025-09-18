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
    $path        = Utility::getFile('uploads/order');
@endphp
@if(!empty($invoiceBankTransfer) && isset($invoiceBankTransfer->id))
    @php
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
    @endphp
    {{ Form::open([
        'route'          => [$changeRoute],
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
                            <td>{{ (!empty($invoice) && isset($invoice->invoice_id) && method_exists($user, 'invoiceNumberFormat') && isset($invoice->invoice_id)) ? $user->invoiceNumberFormat($invoice->invoice_id) : __('Failed to format invoice number')}}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Order Id') }}</th>
                            <td>{{ !empty($invoiceBankTransfer->order_id) ? $invoiceBankTransfer->order_id : __('Failed to retrieve invoice order identificator') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Amount') }}</th>
                            <td>{{ !empty($invoiceBankTransfer->amount) ? $invoiceBankTransfer->amount : __('Failed to retrieve invoice amount') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Payment Type') }}</th>
                            <td>{{ __('Bank Transfer') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Payment Status') }}</th>
                            <td>{{ !empty($invoiceBankTransfer->status) ? $invoiceBankTransfer->status : __('Failed to retrieve invoice status') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Bank Details') }}</th>
                            <td>{!! !empty($company_payment_setting) && isset($company_payment_setting['bank_details']) ? $company_payment_setting['bank_details'] : __('Failed to retrieve bank details') !!}</td>
                        </tr>
                        @if(!empty($invoiceBankTransfer->receipt))
                            <tr>
                                <th>{{ __('Payment Receipt') }}</th>
                                <td>
                                @php
                                    $transferId      = (string) data_get($invoiceBankTransfer ?? null, 'id', 'x');
                                    $receiptName     = (string) data_get($invoiceBankTransfer ?? null, 'receipt', '');
                                    $basePath        = isset($path) ? (string) $path : '';
                                    $receiptUrl      = ($basePath !== '' && $receiptName !== '') ? ($basePath.'/'.$receiptName) : '#';
                                    $linkId          = 'ibt-download-receipt-'.$transferId;
                                    $guardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'bank_transfer_receipt_download_unavailable')
                                                        ?? 'Bank transfer receipt download is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <a
                                    id="{{ $linkId }}"
                                    href="{{ $receiptUrl }}"
                                    data-url="{{ $receiptUrl }}"
                                    data-guard-msg="{{ $guardMsg }}"
                                    data-sv-localized="true"
                                    download
                                    target="_blank"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Download') }}"
                                    class="{{ VC::ACT_BTN_PRIM }} {{ VC::ALC }}"
                                    {{ $receiptUrl === '#' ? 'aria-disabled=true' : '' }}
                                >
                                    <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                </a>
                                <script defer src="{{ asset('assets/js/routes/invoices/receipts.js') }}"></script>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <th>{{ __('Payment Receipt') }}</th>
                                <td>{{ __('No payment receipt found') }}</td>
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
    {{ Form::close() }}
@else
    <div class="{{ VC::ALC }} {{ VC::TXT_DNG }}">
        {{ __('Invoice bank transfer record is unavailable.') }}
    </div>
@endif