@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = 
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Transaction')}}
@endsection
@section('content')
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    @php
                        $transactionRoute = Route::has(ViewsConstants::CST . '.transaction')
                            ? route(ViewsConstants::CST . '.transaction')
                            : '#';
                        $transactionGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::CST,
                            'customers_transaction_route_unavailable'
                        ) ?? 'Customer transaction route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    {{ Form::open(['url' => $transactionRoute, 'method' => 'GET', 'id' => 'frm_submit']) }}
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
                                        {{ Form::select('category', ['' => 'All'] + $category, request('category'), ['class' => 'form-control select2']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} my-auto">
                                <a
                                    id="transaction-apply-btn"
                                    href="#"
                                    class="apply-btn"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Apply') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                </a>
                                <a
                                    id="transaction-reset-btn"
                                    href="{{ $transactionRoute }}"
                                    data-url="{{ $transactionRoute }}"
                                    data-guard-msg="{{ $transactionGuardMsg }}"
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
                        <script defer src="{{ asset('assets/js/routes/customers/transactions/apply.js') }}"></script>
                        <script defer src="{{ asset('assets/js/routes/customers/transactions/reset.js') }}"></script>
                    @endpush
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $txns = ((is_array($transactions ?? null) && count($transactions ?? [])) || (($transactions ?? null) instanceof Collection && $transactions->isNotEmpty())) ? $transactions : [];
                                    $isDateFormatAvailable  = method_exists($user,'dateFormat');
                                    $isPriceFormatAvailable = method_exists($user,'priceFormat');
                                @endphp
                                @if(!empty($txns))
                                    @foreach($txns as $transaction)
                                        @php
                                            $date        = $transaction->date ?? null;
                                            $amount      = $transaction->amount ?? null;
                                            $bankName    = data_get($transaction,'bankAccount.bank_name');
                                            $holderName  = data_get($transaction,'bankAccount.holder_name');
                                            $bankLabel   = trim(($bankName ?: '').' '.($holderName ?: ''));
                                            $type        = $transaction->type ?? null;
                                            $category    = $transaction->category ?? null;
                                            $description = $transaction->description ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $date ? ($isDateFormatAvailable ? $user?->dateFormat($date) : __('Failed to format date')) : __('No transaction date available') }}</td>
                                            <td>{{ is_numeric($amount) ? ($isPriceFormatAvailable ? $user?->priceFormat($amount) : __('Failed to format amount')) : __('No amount available') }}</td>
                                            <td>{{ $bankLabel !== '' ? $bankLabel : __('No bank account available') }}</td>
                                            <td>{{ $type ?: __('No type available') }}</td>
                                            <td>{{ $category ?: __('No category available') }}</td>
                                            <td>{{ $description ?: __('No description available') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">
                                            <div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}">
                                                <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                    <p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No transactions available') }}</p>
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
