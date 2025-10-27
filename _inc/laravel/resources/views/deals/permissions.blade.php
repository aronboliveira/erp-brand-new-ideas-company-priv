@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
    $routeKey       = ViewsConstants::DL . '.client.permissions.store';
    $kebabRouteKey  = Str::kebab($routeKey);
    $hasRoute       = Route::has($routeKey);
    $hasKebab       = Route::has($kebabRouteKey);
    $storeRouteName = $hasRoute
        ? $routeKey
        : ($hasKebab ? $kebabRouteKey : null);
    $storeRouteArr  = $storeRouteName
        ? [$storeRouteName, $deal->id, $client->id]
        : ['#'];
    $storeRouteUrl  = $storeRouteName
        ? route($storeRouteName, [$deal->id, $client->id])
        : '#';
    $storeGuardMsg  = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::DL,
        'deal_client_permissions_store_route_unavailable'
    ) ?? 'Deal client permissions store route is unavailable. Please contact technical support or your domain administrator.';
    $selected ??= [];
@endphp
@if(!empty($deal) && !empty($client) && isset($client->id) && isset($deal->id))
    {!! Form::model($deal, [
        'route'          => $storeRouteArr,
        'method'         => 'PUT',
        'id'             => 'update-client-permissions-form-' . $deal->id . '-' . $client->id,
        'data-url'       => $storeRouteUrl,
        'data-guard-msg' => $storeGuardMsg
    ]) !!}
        <div class="modal-body">
            <ul class="{{ VC::LGRP }}">
                <div class="{{ VC::RW }}">
                    @if(Utility::isFilled($permissions) ?? [])
                        @foreach($permissions as $key => $permission)
                            <div class="{{ VC::CM6 }} {{ VC::PY2 }} px-2">
                                <li class="{{ VC::LG_IT }}">
                                    <div class="{{ VC::C12 }} {{ VC::CST_CTL }} {{ VC::CST_CB }} mt-2 mb-2 p-0">
                                        {{ Form::checkbox(
                                            'permissions[' . $key . ']',
                                            $permission,
                                            in_array($permission, $selected),
                                            ['class' => 'custom-control-input', 'id' => 'permissions_' . $key]
                                        ) }}
                                        {{ Form::label(
                                            'permissions_' . $key,
                                            ucfirst($permission),
                                            ['class' => VC::CST_LB . ' ml-4']
                                        ) }}
                                    </div>
                                </li>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning">{{ __('No permissions available') }}</div>
                    @endif
                </div>
            </ul>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer>
            (() => {
                const form = document.getElementById('update-client-permissions-form-{{ $deal->id }}-{{ $client->id }}');
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
                    } catch (error) {}
                });
            })();
        </script>
    {!! Form::close() !!}
@else
    @if(empty($deal) || !isset($deal->id))
        <div class="alert alert-warning">{{ __('Deal information is missing or invalid') }}</div>
    @elseif (empty($client) || !isset($client->id))
        <div class="alert alert-warning">{{ __('Client information is missing or invalid') }}</div>
    @else
        <div class="alert alert-warning">{{ __('Available data is missing or invalid') }}</div>
    @endif
@endif