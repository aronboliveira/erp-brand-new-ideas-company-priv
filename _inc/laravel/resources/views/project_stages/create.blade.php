@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $storeBaseName   = VW::PRJ_STG;
    $storeKebabName  = Str::kebab($storeBaseName);
    $storeResolved   = Route::has($storeBaseName) ? $storeBaseName : (Route::has($storeKebabName) ? $storeKebabName : null);
    $storeUrl        = $storeResolved ? route($storeResolved) : '#';
    $formId          = 'create-project-stage-form';
    $formGuardMsg    = Utility::fetchLinkMessage($lang, VW::PRJ_STG, 'store_project_stage_unavailable') ?? 'Store project stage route is unavailable. Please contact technical support or your domain administrator.';
@endphp
<div class="{{ VC::CD }} bg-none card-box">
    {!! Form::open([
        'url'            => $storeUrl,
        'method'         => 'post',
        'id'             => $formId,
        'data-guard-msg' => $formGuardMsg,
    ]) !!}
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Project Stage Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('color', __('Color'), ['class' => VC::FM_LB]) }}
                <input class="jscolor {{ VC::FM_CT }}" value="FFFFFF" name="color" id="color" required>
                <small class="small">{{ __('For chart representation') }}</small>
            </div>
            <div class="{{ VC::C12 }} text-end">
                <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
                <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            </div>
        </div>
    {!! Form::close() !!}
</div>
@push(StacksConstants::ADM_SCRP_PG)
    <script>
        (() => {
            try {
                const f = document.getElementById('{{ $formId }}');
                if (!f) return;
                const flag = 'data-submit-listener';
                if (f.hasAttribute(flag) && f.getAttribute(flag) === 'true') return;
                f.setAttribute(flag, 'true');
                f.addEventListener('submit', function (e) {
                    try {
                        const action = f.getAttribute('action') || '#';
                        if (action !== '#') return;
                        e.preventDefault();
                        const msg = f.getAttribute('data-guard-msg') || 'Create project stage route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast);
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            container.className = 'position-fixed top-0 end-0 p-3';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role', 'alert');
                            toast.setAttribute('aria-live', 'assertive');
                            toast.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            container.appendChild(toast);
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (_) {} });
                            inst.show();
                        } else {
                            alert(msg);
                        }
                        f.setAttribute('data-failed-route', 'true');
                    } catch (_) {}
                }, { passive: false });
            } catch (_) {}
        })();
    </script>
@endpush
