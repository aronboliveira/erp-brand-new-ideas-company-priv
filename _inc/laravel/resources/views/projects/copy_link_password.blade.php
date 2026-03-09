@php
    try {
$settings = Utility::settings();
        $logo = Utility::getFile();
        $languages = Utility::languages();
        $lang = Utility::fetchUserLang();
        $company_logo = Utility::getValByName(SettingsConstants::CPN_LG);
    } catch (\Throwable $e) {
        \Log::error('projects/copy_link_password — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
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
        try {
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
        } catch (\Throwable $e) {
            \Log::error('projects/copy_link_password — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
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
            <div class="{{ VC::FM_G }}">
                <label class="{{ VC::FM_CT_LB }} {{ VC::MT2 }} {{ VC::MB2 }}">{{__('Password')}}</label>
                <div class="input-group input-group-merge">
                    <input id="password" type="password" class="{{ VC::FM_CT }} @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ !empty($message) ? $message : __('Something went wrong.') }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="{{ VC::D_GR }}">
                <button type="submit" class="btn-login {{ VC::BT_PRM_BLK_MT2 }}" >{{__('Save')}}</button>
            </div>
        </div>
    {{Collective\Html\FormFacade::close()}}
@endsection
@push(StacksConstants::AUTH_CST_SCR)
    <script defer>
        if (typeof window.CopyLinkPasswordHandler === 'undefined') {
            window.CopyLinkPasswordHandler = {
                init() {
                    const f = document.getElementById('{{ $projectLinkFormId }}');
                    if (f) this.attachFormGuard(f);
                },
                attachFormGuard(f) {
                    f.addEventListener('submit', e => {
                        try {
                            const url = f.getAttribute('data-url') || '#';
                            const action = f.getAttribute('action') || '#';
                            if (url !== '#' && action !== '#') return;
                            e.preventDefault();
                            const msg = f.getAttribute('data-guard-msg') || 'Link project route is unavailable. Please contact technical support or your domain administrator.';
                            this.showToast(msg);
                        } catch (err) {}
                    });
                },
                showToast(msg) {
                    const RG = window.RouteGuard || {};
                    (RG.showToast || (m => alert(m)))(msg);
                }
            };
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.CopyLinkPasswordHandler.init()) : window.CopyLinkPasswordHandler.init();
        }
    </script>
@endpush
