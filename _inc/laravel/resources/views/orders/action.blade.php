@php
    use App\Config\Constants\{
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;

    $lang        = Utility::fetchUserLang();
    $hasOrder    = !empty($order ?? null) && data_get($order, 'id');
    $path        = \App\Models\Utility::getFile('uploads/order') ?: '';
    $bankDetails = $admin_payment_setting['bank_details'] ?? null;

    $changeName  = VW::OD . '.change.status';
    $actionUrl   = ($hasOrder && Route::has($changeName)) ? route($changeName, $order->id) : '#';
    $guardMsg    = Utility::fetchLinkMessage($lang, VW::OD, 'change_status_route_unavailable')
                   ?? __('Change status route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@if($hasOrder)
    {{ Form::open([
        'url'               => $actionUrl,
        'method'            => 'POST',
        'id'                => 'order-change-status-form',
        'data-url'          => $actionUrl,
        'data-guard-msg'    => $guardMsg,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::C12 }}">
                    <table class="table modal-table">
                        <tr role="row">
                            <th>{{ __('Order Id') }}</th>
                            <td>{{ data_get($order, 'order_id', __('Not available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Plan Name') }}</th>
                            <td>{{ data_get($order, 'plan_name', __('Not available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Plan Price') }}</th>
                            <td>{{ data_get($order, 'price', __('Not available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Payment Type') }}</th>
                            <td>{{ data_get($order, 'payment_type', __('Not available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Payment Status') }}</th>
                            <td>{{ data_get($order, 'payment_status', __('Not available')) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Bank Details') }}</th>
                            <td>{!! $bankDetails ?: __('No bank details available.') !!}</td>
                        </tr>
                        @if(!empty(data_get($order, 'receipt')))
                            <tr>
                                <th>{{ __('Payment Receipt') }}</th>
                                <td>
                                    <a class="{{ VC::ACT_BTN_PRIM }} {{ VC::BT_SM }} {{ VC::AL_IT_CT }}"
                                       href="{{ trim($path, '/') . '/' . $order->receipt }}"
                                       download
                                       target="_blank"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('Download') }}">
                                        <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                    </a>
                                </td>
                            </tr>
                        @endif
                        <input type="hidden" name="order_id" value="{{ data_get($order, 'id') }}">
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="submit" name="status" value="{{ __('Approval') }}" class="btn btn-success" data-bs-dismiss="modal">
            <input type="submit" name="status" value="{{ __('Reject') }}" class="btn btn-danger">
        </div>
        <script defer src="{{ asset('assets/js/routes/orders/changeStatus.js') }}"></script>
    {{ Form::close() }}
@else
    <p>{{ __('The requested order could not be found or is unavailable.') }}</p>
@endif
