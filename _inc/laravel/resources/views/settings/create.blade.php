@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
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
                        <h4>{{ __('Create Role') }}</h4>
                    </div>
                    @php
                        $roleStoreBaseName                 = ViewsConstants::RL;
                        $roleStoreKebabName                = Str::kebab($roleStoreBaseName);
                        $roleStoreResolvedName             = Route::has($roleStoreBaseName)
                            ? $roleStoreBaseName
                            : (Route::has($roleStoreKebabName) ? $roleStoreKebabName : null);
                        $roleStoreUrl                      = $roleStoreResolvedName ? route($roleStoreResolvedName) : '#';
                        $roleStoreGuardMsg                 = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_store_route_unavailable') ?? 'Role store route is unavailable. Please contact technical support or your domain administrator.';
                        $roleStoreFormId                   = 'role-store-form';
                    @endphp
                    {!! Form::open([
                        'url'            => $roleStoreUrl,
                        'method'         => 'post',
                        'id'             => $roleStoreFormId,
                        'data-url'       => $roleStoreUrl,
                        'data-guard-msg' => $roleStoreGuardMsg
                    ]) !!}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const form = document.getElementById('{{ $roleStoreFormId }}');
                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                    form.setAttribute('data-listener-active', 'true');
                                    form.addEventListener('submit', e => {
                                        try {
                                            const url = form.getAttribute('data-url') || '#';
                                            const action = form.getAttribute('action') || '#';
                                            if (url !== '#' || action !== '#') return;
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
                        @endpush
                        <div class="card-body">
                            <div class="{{ VC::FM_G }}">
                                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                                {{ Form::text('name', old('name'), [
                                    'class' => VC::FM_CT,
                                    'placeholder' => __('Enter Role Name'),
                                ]) }}
                                @error('name')
                                    <span class="invalid-name" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="{{ VC::FM_G }}">
                                @if (!empty($permissions))
                                    <h6>{{ __('Assign Permission to Roles') }}</h6>

                                    <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}" id="dataTable-1">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Module') }}</th>
                                                <th>{{ __('Permissions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $modules = ['user', 'language', 'account'];
                                                $actions = [
                                                    'manage' => __('Manage'),
                                                    'create' => __('Create'),
                                                    'edit'   => __('Edit'),
                                                    'delete' => __('Delete'),
                                                ];
                                            @endphp

                                            @foreach ($modules as $module)
                                                <tr>
                                                    <td>{{ ucfirst($module) }}</td>
                                                    <td>
                                                        @foreach ($actions as $actionKey => $actionLabel)
                                                            @php
                                                                $needle = $actionKey . ' ' . $module;
                                                                $key = in_array($needle, (array) $permissions, true)
                                                                    ? array_search($needle, $permissions, true)
                                                                    : false;
                                                            @endphp

                                                            @if ($key !== false)
                                                                <div class="{{ VC::FM_CHK_IL }}">
                                                                    {{ Form::checkbox('permissions[]', $key, false, ['id' => 'permission'.$key]) }}
                                                                    {{ Form::label('permission'.$key, $actionLabel) }}<br>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ Form::submit(__('Create'), ['class' => VC::BT_PRM]) }}
                            @php
                                $roleIndexBaseName        = ViewsConstants::RL . '.index';
                                $roleIndexKebabName       = Str::kebab($roleIndexBaseName);
                                $roleIndexResolvedName    = Route::has($roleIndexBaseName) ? $roleIndexBaseName : (Route::has($roleIndexKebabName) ? $roleIndexKebabName : null);
                                $roleIndexUrl             = $roleIndexResolvedName ? route($roleIndexResolvedName) : '#';
                                $roleIndexGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::RL, 'role_index_route_unavailable') ?? 'Role index route is unavailable. Please contact technical support or your domain administrator.';
                                $roleIndexLinkId          = 'role-index-cancel-link';
                            @endphp
                            <a
                                id="{{ $roleIndexLinkId }}"
                                href="{{ $roleIndexUrl }}"
                                data-url="{{ $roleIndexUrl }}"
                                data-guard-msg="{{ $roleIndexGuardMsg }}"
                                class="{{ VC::BT . ' ' . VC::BT . '-danger' }}"
                            >
                                {{ __('Cancel') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const link = document.getElementById('{{ $roleIndexLinkId }}');
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
