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
                    @push(StacksConstants::ADM_SCRP_PG)
                        <script defer>
                            (() => {
                                const applyBtn = document.getElementById('transaction-apply-btn');
                                if (!applyBtn || applyBtn.getAttribute('data-listener-active') === 'true') return;
                                applyBtn.setAttribute('data-listener-active', 'true');
                                applyBtn.addEventListener('click', e => {
                                    try {
                                        e.preventDefault();
                                        document.getElementById('frm_submit').submit();
                                    } catch {}
                                });
                            })();
                        </script>
                        <script defer>
                            (() => {
                                const resetBtn = document.getElementById('transaction-reset-btn');
                                if (!resetBtn || resetBtn.getAttribute('data-listener-active') === 'true') return;
                                resetBtn.setAttribute('data-listener-active', 'true');
                                resetBtn.addEventListener('click', e => {
                                    try {
                                        const url = resetBtn.getAttribute('data-url') ?? '#';
                                        if (url !== '#') return;
                                        e.preventDefault();
                                        const msg = resetBtn.getAttribute('data-guard-msg') ?? '# ERROR';
                                        const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                        let container = document.getElementById('toast-container');
                                        if (!container) {
                                            container = document.createElement('div');
                                            container.id = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (bs) {
                                            const toast = document.createElement('div');
                                            toast.className = 'toast';
                                            toast.setAttribute('role','alert');
                                            toast.setAttribute('aria-live','assertive');
                                            toast.setAttribute('aria-atomic','true');
                                            const body = document.createElement('div');
                                            body.className = 'toast-body';
                                            body.textContent = msg;
                                            toast.appendChild(body);
                                            container.appendChild(toast);
                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                        } else {
                                            alert(msg);
                                        }
                                        resetBtn.setAttribute('data-failed-route','true');
                                    } catch {}
                                });
                            })();
                        </script>
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
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $user?->dateFormat($transaction->date) }}</td>
                                        <td>{{ $user?->priceFormat($transaction->amount) }}</td>
                                        <td>{{ optional($transaction->bankAccount())->bank_name . ' ' . optional($transaction->bankAccount())->holder_name }}</td>
                                        <td>{{ $transaction->type }}</td>
                                        <td>{{ $transaction->category }}</td>
                                        <td>{{ $transaction->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
