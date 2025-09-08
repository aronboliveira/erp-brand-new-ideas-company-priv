@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        StacksConstants,
        YieldingConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Payment') }}
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    @php
                        $paymentRoute = Route::has(ViewsConstants::CST . '.payment')
                            ? route(ViewsConstants::CST . '.payment')
                            : '#';
                        $paymentGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::CST,
                            'payment_route_unavailable'
                        ) ?? 'Payment route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    {{ Form::open(['url' => $paymentRoute, 'method' => 'GET', 'id' => 'frm_submit']) }}
                        <div class="{{ VC::RW }} justify-content-end mt-2">
                            <div class="{{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }}">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'text-type']) }}
                                        {{ Form::text('date', request('date'), ['class' => 'form-control datepicker-range']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }}">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('category', __('Category'), ['class' => 'text-type']) }}
                                        {{ Form::select('category', ['' => __('All')] + $category, request('category'), ['class' => 'form-control select2']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} my-auto">
                                <a
                                    id="filter-apply-btn"
                                    href="{{ $paymentRoute }}"
                                    data-url="{{ $paymentRoute }}"
                                    data-guard-msg="{{ $paymentGuardMsg }}"
                                    class="apply-btn"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Apply') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                </a>
                                <a
                                    id="filter-reset-btn"
                                    href="{{ $paymentRoute }}"
                                    data-url="{{ $paymentRoute }}"
                                    data-guard-msg="{{ $paymentGuardMsg }}"
                                    class="reset-btn"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon"><i class="ti ti-trash-restore-alt"></i></span>
                                </a>
                            </div>
                        </div>
                    {{ Form::close() }}
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/customers/payments/apply.js') }}"></script>
                        <script defer src="{{ asset('assets/js/routes/customers/payments/reset.js') }}"></script>
                    @endpush
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rows = ((is_array($payments ?? null) && count($payments)) || ($payments instanceof Collection && $payments->isNotEmpty())) ? $payments : [];
                                    $hasDateFormat = method_exists($user, 'dateFormat');
                                    $hasPriceFormat = method_exists($user, 'priceFormat');
                                @endphp
                                @if(!empty($rows))
                                    @foreach($rows as $payment)
                                        <tr>
                                            <td>{{ (!empty($payment->date)) ? ($hasDateFormat ? $user?->dateFormat($payment->date) : __('Failed to format date')) : __('No date available') }}</td>
                                            <td>{{ (isset($payment->amount) && is_numeric($payment->amount)) ? ($hasPriceFormat ? $user?->priceFormat($payment->amount) : __('Failed to format amount')) : __('No amount available') }}</td>
                                            <td>{{ (!empty($payment->category)) ? $payment->category : __('No category available') }}</td>
                                            <td>{{ (isset($payment->description) && $payment->description !== '') ? $payment->description : __('No description available') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4">
                                            <div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}">
                                                <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                    <p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No payments available') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
