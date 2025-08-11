@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();

    $routeKey         = ViewsConstants::DL . '.sources.update';
    $kebabRouteKey    = Str::kebab($routeKey);
    $hasRoute         = Route::has($routeKey);
    $hasKebab         = Route::has($kebabRouteKey);
    $updateRouteName  = $hasRoute
        ? $routeKey
        : ($hasKebab ? $kebabRouteKey : null);
    $updateRouteArr   = $updateRouteName
        ? [$updateRouteName, $deal->id]
        : ['#'];
    $updateRouteUrl   = $updateRouteName
        ? route($updateRouteName, $deal->id)
        : '#';
    $updateGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::DL,
        'deal_sources_update_route_unavailable'
    ) ?? 'Deal sources update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::model($deal, [
    'route'          => $updateRouteArr,
    'method'         => 'PUT',
    'id'             => 'update-sources-form-' . $deal->id,
    'data-url'       => $updateRouteUrl,
    'data-guard-msg' => $updateGuardMsg
]) !!}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            <div class="row gutters-xs">
                @foreach ($sources as $source)
                    <div class="col-12 custom-control custom-checkbox mt-2 mb-2">
                        {{ Form::checkbox(
                            'sources[]',
                            $source->id,
                            ($selected && array_key_exists($source->id, $selected)) ? true : false,
                            ['class' => 'form-check-input', 'id' => 'sources_' . $source->id]
                        ) }}
                        {{ Form::label(
                            'sources_' . $source->id,
                            ucfirst($source->name),
                            ['class' => 'custom-control-label ml-4 text-sm font-weight-bold']
                        ) }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
</div>
{!! Form::close() !!}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('update-sources-form-{{ $deal->id }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
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
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
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
                } catch (error) {}
            });
        })();
    </script>
@endpush
