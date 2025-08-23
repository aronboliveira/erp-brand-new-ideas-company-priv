@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Crypt,Route};
    use Illuminate\Support\Str;
    $settings = Utility::settings();
    $logo = Utility::getFile();
    $languages = Utility::languages();
    $lang = Utility::fetchUserLang();
    $company_logo = Utility::getValByName(SettingsConstants::CPN_LG);
@endphp
@extends(ExtendingLayoutsConstants::AUTH)
@section(YieldingConstants::AUTH_PG_TTL)
    {{__('Copy Link')}}
@endsection
@section(YieldingConstants::AUTH_TB)
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="">
        <h2 class="h3">{{__('Password required')}}</h2>
        <h6>{{ __('This document is password-protected. Please enter a password.') }}</h6>
    </div>
    @php
        $projectLinkBaseName     = ViewsConstants::PRJ.'.link';
        $projectLinkKebabName    = Str::kebab($projectLinkBaseName);
        $projectLinkResolvedName = Route::has($projectLinkBaseName)
            ? $projectLinkBaseName
            : (Route::has($projectLinkKebabName) ? $projectLinkKebabName : null);
        $projectIdValue          = isset($id) && !empty($id) ? $id : null;
        $encryptedProjectId      = $projectIdValue ? Crypt::encrypt($projectIdValue) : null;
        $projectLinkRouteArray   = ($projectLinkResolvedName && $encryptedProjectId) ? [$projectLinkResolvedName, $encryptedProjectId] : ['#'];
        $projectLinkUrl          = ($projectLinkResolvedName && $encryptedProjectId) ? route($projectLinkResolvedName, $encryptedProjectId) : '#';
        $projectLinkGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'link_project_route_unavailable') ?? 'Link project route is unavailable. Please contact technical support or your domain administrator.';
        $projectLinkFormId       = 'project-link-form';
    @endphp
    {!! Collective\Html\FormFacade::open([
        'route'          => $projectLinkRouteArray,
        'method'         => 'post',
        'accept-charset' => 'UTF-8',
        'id'             => $projectLinkFormId,
        'data-url'       => $projectLinkUrl,
        'data-guard-msg' => $projectLinkGuardMsg
    ]) !!}
        @csrf
        <div class="">
            <div class="form-group ">
                <label class="form-control-label mt-2 mb-2">{{__('Password')}}</label>
                <div class="input-group input-group-merge">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ !empty($message) ? $message : __('Something went wrong.') }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn-login btn btn-primary btn-block mt-2" >{{__('Save')}}</button>
            </div>
        </div>
    {{Collective\Html\FormFacade::close()}}
@endsection
@push(StacksConstants::AUTH_CST_SCR)
    <script defer>
        (() => {
            try {
                const f = document.getElementById('{{ $projectLinkFormId }}');
                if (!f || f.getAttribute('data-listener-active') === 'true') return;
                f.setAttribute('data-listener-active', 'true');
                f.addEventListener('submit', e => {
                    try {
                        const url = f.getAttribute('data-url') || '#';
                        const action = f.getAttribute('action') || '#';
                        if (url !== '#' || action !== '#') return;
                        e.preventDefault();
                        const msg = f.getAttribute('data-guard-msg') || 'Link project route is unavailable. Please contact technical support or your domain administrator.';
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
                        f.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                });
            } catch (error) {}
        })();
    </script>
@endpush


