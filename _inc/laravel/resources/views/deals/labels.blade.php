@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $namespace      = ViewsConstants::DL;
    $routeName      = "{$namespace}.labels.store";
    $storeRoute     = route($routeName, $deal->id);
    $storeGuardMsg  = Utility::fetchLinkMessage(
        $lang,
        $namespace,
        'labels_store_route_unavailable'
    ) ?? 'Labels store route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{{ Form::open([
    'route'           => [$routeName, $deal->id],
    'method'          => 'POST',
    'id'              => 'labels-store-form',
    'data-url'        => $storeRoute,
    'data-guard-msg'  => $storeGuardMsg
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                <div class="{{ VC::RW }} gutters-xs">
                    @foreach ($labels as $label)
                        <div class="{{ VC::C12 }} {{ VC::CST_CTL }} {{ VC::CST_CB }} mt-2 mb-2">
                            {{ Form::checkbox(
                                'labels[]',
                                $label->id,
                                array_key_exists($label->id, $selected),
                                ['class' => 'form-check-input', 'id' => 'labels_'.$label->id]
                            ) }}
                            {{ Form::label(
                                'labels_'.$label->id,
                                ucfirst($label->name),
                                [
                                    'class' => VC::CST_LB
                                        .' ml-4'
                                        .' '.VC::TXT_WT
                                        .' px-3'
                                        .' '.VC::PY2
                                        .' rounded'
                                        .' '.VC::BDG
                                        .' bg-'.$label->color
                                ]
                            ) }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
@push(StacksConstants::ADM_SCRP_PG)
    <script defer>
        (() => {
            const form = document.getElementById('labels-store-form');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const action = form.getAttribute('action') ?? form.getAttribute('data-url') ?? '#';
                    if (action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
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
                    form.setAttribute('data-failed-route', 'true');
                } catch {}
            });
        })();
    </script>
@endpush

