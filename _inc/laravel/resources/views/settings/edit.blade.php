@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route,Str};
    $lang = Utility::fetchUserLang();
    $modules = ['user','language','account'];
    $actions = [
        'manage' => __('Manage'),
        'create' => __('Create'),
        'edit'   => __('Edit'),
        'delete' => __('Delete'),
    ];
    $rolePermissions = (array)($role->permission ?? []);
@endphp

@extends('layouts.main')
@section('content')
<section class="section">
    <div class="section-header">
        <h1 class="d-inline">{{ __('Role') }}</h1>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h4>{{ __('Update Role') }}</h4>
                </div>
                @php
                    $roleUpdateBaseName           = ViewsConstants::RL . '.update';
                    $roleUpdateKebabName          = Str::kebab($roleUpdateBaseName);
                    $roleUpdateResolvedName       = Route::has($roleUpdateBaseName)
                        ? $roleUpdateBaseName
                        : (Route::has($roleUpdateKebabName) ? $roleUpdateKebabName : null);
                    $roleUpdateRouteArray         = $roleUpdateResolvedName ? [$roleUpdateResolvedName, $role->id] : ['#'];
                    $roleUpdateUrl                = $roleUpdateResolvedName ? route($roleUpdateResolvedName, $role->id) : '#';
                    $roleUpdateGuardMsg           = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_update_route_unavailable') ?? 'Role update route is unavailable. Please contact technical support or your domain administrator.';
                    $roleUpdateFormId             = 'role-update-form-' . $role->id;
                @endphp
                {!! Form::model($role, [
                    'route'          => $roleUpdateRouteArray,
                    'method'         => 'PUT',
                    'id'             => $roleUpdateFormId,
                    'data-url'       => $roleUpdateUrl,
                    'data-guard-msg' => $roleUpdateGuardMsg
                ]) !!}
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const form = document.getElementById('{{ $roleUpdateFormId }}');
                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                form.setAttribute('data-listener-active', 'true');
                                form.addEventListener('submit', (e) => {
                                    try {
                                        const dataUrl = form.getAttribute('data-url') || '#';
                                        if (dataUrl !== '#') return;
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
                                    } catch (err) {}
                                });
                            })();
                        </script>
                    @endpush
                    <div class="card-body">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                            {{ Form::text('name', null, ['class' => VC::FM_CT]) }}
                            @error('name')
                                <span class="invalid-name" role="alert">
                                    <strong class="text-danger">{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6>{{ __('Assign Permission to Roles') }}</h6>

                                <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Module') }}</th>
                                            <th>{{ __('Permissions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($modules as $module)
                                        <tr>
                                            <td>{{ ucfirst($module) }}</td>
                                            <td>
                                                @foreach($actions as $actionKey => $actionLabel)
                                                    @php
                                                        $needle = $actionKey.' '.$module;
                                                        $permId = array_search($needle, (array)$permissions, true);
                                                    @endphp
                                                    @if($permId !== false)
                                                        <div class="{{ VC::FM_CHK_IL }}">
                                                            {{ Form::checkbox(
                                                                'permissions[]',
                                                                $permId,
                                                                in_array($permId, $rolePermissions, true),
                                                                ['id' => 'permission'.$permId]
                                                            ) }}
                                                            {{ Form::label('permission'.$permId, $actionLabel) }}<br>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @error('permissions')
                                <span class="invalid-permissions" role="alert">
                                    <strong class="text-danger">{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        @php
                            $roleIndexRouteBase        = ViewsConstants::RL . '.index';
                            $roleIndexRouteKebab       = Str::kebab($roleIndexRouteBase);
                            $roleIndexResolvedName     = Route::has($roleIndexRouteBase)
                                ? $roleIndexRouteBase
                                : (Route::has($roleIndexRouteKebab) ? $roleIndexRouteKebab : null);
                            $roleIndexHref             = $roleIndexResolvedName ? route($roleIndexResolvedName) : '#';
                            $roleIndexGuardMsg         = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_index_route_unavailable')
                                ?? 'Role index route is unavailable. Please contact technical support or your domain administrator.';
                            $roleIndexCancelLinkId     = 'role-index-cancel-link';
                        @endphp
                        <a
                            id="{{ $roleIndexCancelLinkId }}"
                            href="{{ $roleIndexHref }}"
                            data-url="{{ $roleIndexHref }}"
                            data-guard-msg="{{ $roleIndexGuardMsg }}"
                            class="{{ VC::BT . ' ' . VC::BT . '-danger' }}"
                        >
                            {{ __('Cancel') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const link = document.getElementById('{{ $roleIndexCancelLinkId }}');
                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                    link.setAttribute('data-listener-active', 'true');
                                    link.addEventListener('click', e => {
                                        try {
                                            const url = link.getAttribute('data-url') || '#';
                                            if (url !== '#') return;
                                            e.preventDefault();
                                            const msg = link.getAttribute('data-guard-msg') || '# ERROR';
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
                                            link.setAttribute('data-failed-route', 'true');
                                        } catch (err) {}
                                    });
                                })();
                            </script>
                        @endpush
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</section>
@endsection
<script></script>
