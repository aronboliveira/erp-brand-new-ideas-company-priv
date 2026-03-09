@php
    try {
$lang = Utility::fetchUserLang();
        $routeKey       = ViewsConstants::DL . '.clients.update';
        $kebabRouteKey  = Str::kebab($routeKey);
        $hasRoute       = Route::has($routeKey);
        $hasKebab       = Route::has($kebabRouteKey);
        $updateRouteName = $hasRoute
            ? $routeKey
            : ($hasKebab ? $kebabRouteKey : null);
        $updateRouteUrl = $updateRouteName
            ? route($updateRouteName, $deal->id)
            : '#';
        $updateGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::DL,
            'clients_update_route_unavailable'
        ) ?? 'Update deals with clients route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('deals/clients — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{!! Form::model($deal, [
    'route'  => $updateRouteName
        ? [$updateRouteName, $deal->id]
        : $updateRouteUrl,
    'method' => 'PUT',
    'id'     => 'update-clients-form-' . $deal->id,
    'data-url'       => $updateRouteUrl,
    'data-guard-msg' => $updateGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('clients', __('Clients'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'clients[]',
                        Utility::isFilled($clients) ? $clients : [__('No clients available.')],
                        false,
                        [
                            'class'    => VC::FM_CT . ' select2',
                            'id'       => 'choices-multiple1',
                            'multiple' => '',
                            'required' => 'required'
                        ]
                    ) }}
            </div>
        </div>
    </div>
    <div class="{{ VC::DFL }} modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            {{ __('Cancel') }}
        </button>
        <button type="submit" class="{{ VC::BT_PRM }}">
            {{ __('Create') }}
        </button>
    </div>
    <script defer>
        (() => {
            const form = document.getElementById('update-clients-form-{{ $deal->id }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                    form.setAttribute('data-failed-route', 'true');
                } catch (error) {}
            });
        })();
    </script>
{{ Form::close() }}
