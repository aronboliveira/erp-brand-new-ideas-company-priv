@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $employeeId = (string) data_get($payslip ?? null, 'employee_id', '');
    $routeName  = VW::PY_SLP . '.updateEmployee';
    $resolved   = Route::has($routeName) ? route($routeName, $employeeId)
               : ($employeeId !== '' ? url(trim(VW::PY_SLP, '/') . '/employee/update/' . urlencode($employeeId)) : '#');

    $guardMsg = Utility::fetchLinkMessage($lang, VW::PY_SLP, 'update_employee_route_unavailable')
            ?? 'Update employee route is unavailable. Please contact technical support or your domain administrator.';
@endphp

<div class="col-form-label">
    <div class="{{ VC::RW }} {{ VC::PX3 }}">
        <div class="{{ VC::CM4 }} {{ VC::MB3 }}">
            <h6 class="emp-title {{ VC::MB0 }}">{{ __('Employee') }}</h6>
            <h6 class="emp-title black-text">
                {{ data_get($payslip??null,'employees.employee_id')
                    ? ($user?->employeeIdFormat(data_get($payslip,'employees.employee_id')) ?? __('Failed to format employee ID'))
                    : __('No employee ID available') }}
            </h6>
        </div>

        <div class="{{ VC::CM4 }} {{ VC::MB3 }}">
            <h6 class="emp-title {{ VC::MB0 }}">{{ __('Basic Salary') }}</h6>
            <h6 class="emp-title black-text">
                {{ data_get($payslip??null,'basic_salary')!==null
                    ? ($user?->priceFormat(data_get($payslip,'basic_salary')) ?? __('Failed to format salary'))
                    : __('No basic salary available') }}
            </h6>
        </div>

        <div class="{{ VC::CM4 }} {{ VC::MB3 }}">
            <h6 class="emp-title {{ VC::MB0 }}">{{ __('Payroll Month') }}</h6>
            <h6 class="emp-title black-text">
                {{ data_get($payslip??null,'salary_month')
                    ? ($user?->dateFormat(data_get($payslip,'salary_month')) ?? __('Failed to format date'))
                    : __('No payroll month available') }}
            </h6>
        </div>

        <div class="col-lg-12 our-system">
            {!! Form::open([
                'url'                  => $resolved,
                'method'               => 'post',
                'id'                   => 'update_employee_form',
                'data-resolved-action' => $resolved,
                'data-guard-msg'       => $guardMsg,
                'data-sv-localized'    => 'true',
            ]) !!}
                {!! Form::hidden('payslip_id', data_get($payslip??null,'id',''), ['class'=> VC::FM_CT]) !!}

                <div class="{{ VC::RW }}">
                    <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                        <li class="{{ VC::NV_IT }}">
                            <a class="{{ VC::NV_LK }} active" id="pills-home-tab" data-bs-toggle="pill" href="#allowance" role="tab" aria-controls="pills-home" aria-selected="true">{{ __('Allowance') }}</a>
                        </li>
                        <li class="{{ VC::NV_IT }}">
                            <a class="{{ VC::NV_LK }}" id="pills-profile-tab" data-bs-toggle="pill" href="#commission" role="tab" aria-controls="pills-profile" aria-selected="false">{{ __('Commission') }}</a>
                        </li>
                        <li class="{{ VC::NV_IT }}">
                            <a class="{{ VC::NV_LK }}" id="pills-contact-tab" data-bs-toggle="pill" href="#loan" role="tab" aria-controls="pills-contact" aria-selected="false">{{ __('Loan') }}</a>
                        </li>
                        <li class="{{ VC::NV_IT }}">
                            <a class="{{ VC::NV_LK }}" id="pills-contact-tab" data-bs-toggle="pill" href="#deduction" role="tab" aria-controls="pills-contact" aria-selected="false">{{ __('Saturation Deduction') }}</a>
                        </li>
                        <li class="{{ VC::NV_IT }}">
                            <a class="{{ VC::NV_LK }}" id="pills-contact-tab" data-bs-toggle="pill" href="#payment" role="tab" aria-controls="pills-contact" aria-selected="false">{{ __('Other Payment') }}</a>
                        </li>
                        <li class="{{ VC::NV_IT }}">
                            <a class="{{ VC::NV_LK }}" id="pills-contact-tab" data-bs-toggle="pill" href="#overtime" role="tab" aria-controls="pills-contact" aria-selected="false">{{ __('Overtime') }}</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <div id="allowance" class="tab-pane in active">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-12">
                                    <div class="{{ VC::CD }} bg-none {{ VC::MB0 }}">
                                        <div class="{{ VC::RW }} {{ VC::PX3 }}">
                                            @php
                                                $allowances = json_decode((string) data_get($payslip??null,'allowance','[]'));
                                                $allowances = is_iterable($allowances) ? $allowances : [];
                                            @endphp
                                            @foreach($allowances as $allowance)
                                                <div class="{{ VC::CM12 }} {{ VC::FM_G }}">
                                                    {!! Form::label('title', data_get($allowance,'title',__('No allowance title available')), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('allowance[]', data_get($allowance,'amount',''), ['class' => VC::FM_CT]) !!}
                                                    {!! Form::hidden('allowance_id[]', data_get($allowance,'id',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="commission" class="tab-pane">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-12">
                                    <div class="{{ VC::CD }} bg-none {{ VC::MB0 }}">
                                        <div class="{{ VC::RW }} {{ VC::PX3 }}">
                                            @php
                                                $commissions = json_decode((string) data_get($payslip??null,'commission','[]'));
                                                $commissions = is_iterable($commissions) ? $commissions : [];
                                            @endphp
                                            @foreach($commissions as $commission)
                                                <div class="{{ VC::CM12 }} {{ VC::FM_G }}">
                                                    {!! Form::label('title', data_get($commission,'title',__('No commission title available')), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('commission[]', data_get($commission,'amount',''), ['class' => VC::FM_CT]) !!}
                                                    {!! Form::hidden('commission_id[]', data_get($commission,'id',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="loan" class="tab-pane">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-12">
                                    <div class="{{ VC::CD }} bg-none {{ VC::MB0 }}">
                                        <div class="{{ VC::RW }} {{ VC::PX3 }}">
                                            @php
                                                $loans = json_decode((string) data_get($payslip??null,'loan','[]'));
                                                $loans = is_iterable($loans) ? $loans : [];
                                            @endphp
                                            @foreach($loans as $loan)
                                                <div class="{{ VC::CM12 }} {{ VC::FM_G }}">
                                                    {!! Form::label('title', data_get($loan,'title',__('No loan title available')), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('loan[]', data_get($loan,'amount',''), ['class' => VC::FM_CT]) !!}
                                                    {!! Form::hidden('loan_id[]', data_get($loan,'id',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="deduction" class="tab-pane">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-12">
                                    <div class="{{ VC::CD }} bg-none {{ VC::MB0 }}">
                                        <div class="{{ VC::RW }} {{ VC::PX3 }}">
                                            @php
                                                $saturation_deductions = json_decode((string) data_get($payslip??null,'saturation_deduction','[]'));
                                                $saturation_deductions = is_iterable($saturation_deductions) ? $saturation_deductions : [];
                                            @endphp
                                            @foreach($saturation_deductions as $deduction)
                                                <div class="{{ VC::CM12 }} {{ VC::FM_G }}">
                                                    {!! Form::label('title', data_get($deduction,'title',__('No deduction title available')), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('saturation_deductions[]', data_get($deduction,'amount',''), ['class' => VC::FM_CT]) !!}
                                                    {!! Form::hidden('saturation_deductions_id[]', data_get($deduction,'id',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="payment" class="tab-pane">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-12">
                                    <div class="{{ VC::CD }} bg-none {{ VC::MB0 }}">
                                        <div class="{{ VC::RW }} {{ VC::PX3 }}">
                                            @php
                                                $other_payments = json_decode((string) data_get($payslip??null,'other_payment','[]'));
                                                $other_payments = is_iterable($other_payments) ? $other_payments : [];
                                            @endphp
                                            @foreach($other_payments as $payment)
                                                <div class="{{ VC::CM12 }} {{ VC::FM_G }}">
                                                    {!! Form::label('title', data_get($payment,'title',__('No other payment title available')), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('other_payment[]', data_get($payment,'amount',''), ['class' => VC::FM_CT]) !!}
                                                    {!! Form::hidden('other_payment_id[]', data_get($payment,'id',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="overtime" class="tab-pane">
                            <div class="{{ VC::RW }}">
                                <div class="col-lg-12">
                                    <div class="{{ VC::CD }} bg-none {{ VC::MB0 }}">
                                        <div class="{{ VC::RW }} {{ VC::PX3 }}">
                                            @php
                                                $overtimes = json_decode((string) data_get($payslip??null,'overtime','[]'));
                                                $overtimes = is_iterable($overtimes) ? $overtimes : [];
                                            @endphp
                                            @foreach($overtimes as $overtime)
                                                <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                                                    {!! Form::label('rate', data_get($overtime,'title',__('Overtime')).' '.__('Rate'), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('rate[]', data_get($overtime,'rate',''), ['class' => VC::FM_CT]) !!}
                                                    {!! Form::hidden('rate_id[]', data_get($overtime,'id',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                                <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                                                    {!! Form::label('hours', data_get($overtime,'title',__('Overtime')).' '.__('Hours'), ['class' => VC::FM_LB]) !!}
                                                    {!! Form::text('hours[]', data_get($overtime,'hours',''), ['class' => VC::FM_CT]) !!}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
                    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                </div>

                <script defer src="{{ asset('assets/js/routes/payslips/employees/update.js') }}"></script>
            {!! Form::close() !!}
        </div>
    </div>
</div>
