@php
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

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
            {{ Form::label('salary_type', __('Payslip Type'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
            {{ Form::select('salary_type', $payslip_type, null, ['required' => 'required', 'class' => VC::FM_CT_SL]) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('salary', __('Salary'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
            {{ Form::number('salary', null, ['required' => 'required', 'class' => VC::FM_CT]) }}
        </div>
    </div>
    <div class="{{ VC::CD_POS }}">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Save Change') }}</button>
    </div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (form && form.getAttribute('data-listener-active') !== 'true') {
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', event => {
                    try {
                        const action = form.getAttribute('action');
                        const url    = form.getAttribute('data-url');
                        if ((action && action !== '#') || (url && url !== '#')) return;
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
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        form.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            }
        })();
    </script>
@endpush
