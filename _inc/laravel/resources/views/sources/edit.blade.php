@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang                         = Utility::fetchUserLang();
    $sourceUpdateBaseName         = ViewsConstants::SRC . '.update';
    $sourceUpdateKebabName        = Str::kebab($sourceUpdateBaseName);
    $sourceUpdateResolvedName     = Route::has($sourceUpdateBaseName)
        ? $sourceUpdateBaseName
        : (Route::has($sourceUpdateKebabName) ? $sourceUpdateKebabName : null);
    $sourceUpdateRouteArray       = $sourceUpdateResolvedName ? [$sourceUpdateResolvedName, $source->id] : ['#'];
    $sourceUpdateUrl              = $sourceUpdateResolvedName ? route($sourceUpdateResolvedName, $source->id) : '#';
    $sourceUpdateGuardMsg         = Utility::fetchLinkMessage($lang, ViewsConstants::SRC, 'source_update_route_unavailable') ?? 'Source update route is unavailable. Please contact technical support or your domain administrator.';
    $sourceUpdateFormId           = 'source-update-form-' . $source->id;
@endphp
{!! Form::model($source, [
    'route'          => $sourceUpdateRouteArray,
    'method'         => 'PUT',
    'id'             => $sourceUpdateFormId,
    'data-url'       => $sourceUpdateUrl,
    'data-guard-msg' => $sourceUpdateGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Source Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $sourceUpdateFormId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    const action = form.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (hasBootstrap) {
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
                } catch (err) {}
            });
        })();
    </script>
@endpush
