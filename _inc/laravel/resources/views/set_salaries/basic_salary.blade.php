@php
    try {
$lang                 = Utility::fetchUserLang();
        $salaryUpdateRoute    = Route::has(ViewsConstants::EMP . '.salary.update')
            ? route(ViewsConstants::EMP . '.salary.update', $employee->id)
            : (Route::has(Str::kebab(ViewsConstants::EMP . '.salary.update'))
                ? route(Str::kebab(ViewsConstants::EMP . '.salary.update'), $employee->id)
                : '#');
        $formId               = 'employee-salary-update-form';
        $updateMsg            = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::EMP,
            'salary_update_route_unavailable'
        ) ?? 'Salary update route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('set_salaries/basic_salary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::model($employee, [
    'url'            => $salaryUpdateRoute,
    'method'         => 'POST',
    'id'             => $formId,
    'data-url'       => $salaryUpdateRoute,
    'data-guard-msg' => $updateMsg,
]) }}
    <div class="{{ VC::RW }}">
        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('salary_type', __('Payslip Type'), ['class' => VC::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
            {{ Form::select('salary_type', $payslip_type, null, ['required' => 'required', 'class' => VC::FM_CT_SL]) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('salary', __('Salary'), ['class' => VC::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
            {{ Form::number('salary', null, ['required' => 'required', 'class' => VC::FM_CT]) }}
        </div>
    </div>
    <div class="{{ VC::CD_POS }}">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Change') }}</button>
    </div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/setSalaries/update.js') }}"></script>
@endpush
