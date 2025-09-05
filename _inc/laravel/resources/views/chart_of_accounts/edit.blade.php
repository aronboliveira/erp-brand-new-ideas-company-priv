@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use Collective\Html\FormFacade as Form;
    $lang         = Utility::fetchUserLang();
    $routeName    = ViewsConstants::COA . '.update';
    $updateRoute  = Route::has($routeName)
        ? route($routeName, $chartOfAccount->id)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName), $chartOfAccount->id)
            : '#');
    $formId       = 'chart_of_accounts_update_form_' . $chartOfAccount->id;
    $guardMsg     = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COA,
        'chart_of_account_update_route_unavailable'
    ) ?? 'Chart of Account update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($chartOfAccount, [
    'route'          => $updateRoute,
    'method'         => 'PUT',
    'id'             => $formId,
    'data-url'       => $updateRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        @php $plan = Utility::getChatGPTSettings(); @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }}">
                <a
                    href="#"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-url="{{ route('generate',['chart of account']) }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('code', __('Code'), ['class' => VC::FM_LB]) }}
                {{ Form::number('code', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('is_enabled', __('Is Enabled'), ['class' => VC::FM_LB]) }}
                <div class="form-check form-switch">
                    <input
                        type="checkbox"
                        name="is_enabled"
                        id="is_enabled"
                        class="form-check-input"
                        {{ $chartOfAccount->is_enabled ? 'checked' : '' }}
                    >
                    <label for="is_enabled" class="form-check-label"></label>
                </div>
            </div>

            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 2]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Update') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
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