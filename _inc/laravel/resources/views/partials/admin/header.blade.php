@php
    use App\Models\{User, Utility};
    use App\Config\Constants\{
        DatabaseConstants,
        PermissionsConstants, 
        SettingsConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants
    };
    use Illuminate\Support\Facades\{Auth, File, Log};
    Log::debug('Loading admin header data...');
    $user=Auth::user();
    $profile=Utility::getFile('uploads/avatar/');
    $languages=Utility::languages();
    $lang = isset($user[UsersConstants::COL_LG])?$user[UsersConstants::COL_LG]: Utility::fetchUserLang(user:$user);
    if (empty($lang)) $lang = DatabaseConstants::DEFAULT_LANG;
    // $langName = \App\Models\Language::where('code',$lang)->first();
    // $langName =\App\Models\Language::languageData($lang);
    $langName = cache()->remember('full_language_data_' . $lang, now()->addHours(24), function () use ($lang) {
        return \App\Models\Language::languageData($lang);
    });
    $settings = Utility::settings();
    $unseenCounter = ($user instanceof User) ? App\Models\ChMessage::where('to_id', $user?->id)->where('seen', 0)->count() : 0;
    Log::debug('Loading admin header template...')
@endphp
@if (isset($settings[SettingsConstants::CST_BG]) && $settings[SettingsConstants::CST_BG] == 'on')
    <header class="{{ ViewClassNamesConstants::DSH }} transparent-bg">
@else
    <header class="{{ ViewClassNamesConstants::DSH }}">
@endif
        <div class="header-wrapper">
            <div class="me-auto dash-mob-drp">
                <ul class="list-unstyled">
                    <li class="{{ ViewClassNamesConstants::DSH_H }} mob-hamburger">
                        <a href="#" class="{{ ViewClassNamesConstants::DSH_HD_LK }}" id="mobile-collapse">
                            <div class="hamburger hamburger--arrowturn">
                                <div class="hamburger-box">
                                    <div class="hamburger-inner"></div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="{{ ViewClassNamesConstants::DRP_DSH }} drp-company">
                        @if ($user instanceof User) 
                            <a class="{{ ViewClassNamesConstants::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="theme-avatar" style="transform: scale(1.1);">
                                    <img
                                        src="{{ asset( ($user?->avatar && File::exists($user->avatar)) ? $user->avatar : 'assets/images/user/defaults/fictional_tech_lead.webp') }}"
                                        alt="User Avatar"
                                        data-reload-attempt="0"
                                        height="40px"
                                        width="40px"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="high"
                                        style="border-radius: 40%;"
                                    >
                                </span>
                                <span class="hide-mob ms-2">{{__('Hi, ')}}{{$user?->name }}!</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                            </a>
                        @endif
                        @php
                            $profileRoute = Route::has('profile')
                                ? route('profile')
                                : '#';
                            $profileLinkId = 'profile-link';
                            $profileMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::USR,
                                'profile_route_unavailable'
                            ) ?? 'Profile route is unavailable. Please contact technical support or your domain administrator.';
                        
                            $logoutRoute = Route::has('logout')
                                ? route('logout')
                                : '#';
                            $logoutLinkId = 'logout-link';
                            $logoutMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::USR,
                                'logout_route_unavailable'
                            ) ?? 'Logout route is unavailable. Please contact technical support or your domain administrator.';
                            $guardIds = [$profileLinkId, $logoutLinkId];
                        @endphp
                        <div class="{{ ViewClassNamesConstants::DRP_DSH_MN }}">
                            <a
                                id="{{ $profileLinkId }}"
                                href="{{ $profileRoute }}"
                                class="dropdown-item"
                                data-url="{{ $profileRoute }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ $profileMsg }}"
                            >
                                <i class="ti ti-user text-dark"></i>
                                <span>{{ __('Profile') }}</span>
                            </a>
                            <a
                                id="{{ $logoutLinkId }}"
                                href="{{ $logoutRoute }}"
                                onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"
                                class="dropdown-item"
                                data-url="{{ $logoutRoute }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ $logoutMsg }}"
                            >
                                <i class="ti ti-power text-dark"></i>
                                <span>{{ __('Logout') }}</span>
                            </a>
                            <form id="frm-logout" action="{{ $logoutRoute }}" method="POST" class="d-none">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="ms-auto" style="margin-right: 1rem;">
                <ul class="list-unstyled">
                    @if($user instanceof User && $user[UsersConstants::COL_TP] != PermissionsConstants::CL 
                        && $user[UsersConstants::COL_TP] != PermissionsConstants::SA )
                        <li class="{{ ViewClassNamesConstants::DRP_DSH }} drp-notification">
                            <a class="{{ ViewClassNamesConstants::DSH_NO_ARROW }}" href="{{ url('chats') }}" aria-haspopup="false"
                            aria-expanded="false" style="transform: translateY(-2px);">
                                <i class="ti ti-brand-hipchat"></i>
                                <span class="bg-danger dash-h-badge message-toggle-msg  message-counter custom_messanger_counter beep"> 
                                    {{ $unseenCounter }}
                                    <span class="sr-only"></span>
                                </span>
                            </a>
                        </li>
                    @endif
                    <li class="{{ ViewClassNamesConstants::LNG_DD_IT }}">
                        <a
                            class="{{ ViewClassNamesConstants::DRP_NO_ARROW }}"
                            data-bs-toggle="dropdown"
                            href="#"
                            role="button"
                            aria-haspopup="false"
                            aria-expanded="false"
                        >
                            <i class="ti ti-world nocolor"></i>
                            <span class="drp-text hide-mob">{{ucfirst($langName->full_name)}}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        @php
                            $changeIds = [];
                            foreach ($languages as $code => $language) {
                                $route = Route::has('languages.change')
                                    ? route('languages.change', $code)
                                    : '#';
                                $id = "language-change-{$code}-link";
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::LNG,
                                    'language_change_route_unavailable'
                                ) ?? 'Language change route is unavailable. Please contact technical support or your domain administrator.';
                                $changeIds[] = $id;
                                $changeLinks[] = compact('code', 'language', 'route', 'id', 'message');
                            }
                            if ($user instanceof User && $user[UsersConstants::COL_TP] == PermissionsConstants::SA) {
                                $createRoute = Route::has('languages.create')
                                    ? route('languages.create')
                                    : '#';
                                $createId = 'language-create-link';
                                $createMsg = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::LNG,
                                    'language_create_route_unavailable'
                                ) ?? 'Create Language route is unavailable. Please contact technical support or your domain administrator.';
                                $changeIds[] = $createId;
                                $createLink = compact('createRoute', 'createId', 'createMsg');
                        
                                $manageRoute = Route::has('languages.manage')
                                    ? route('languages.manage', [isset($lang) ? $lang : 'english'])
                                    : '#';
                                $manageId = 'language-manage-link';
                                $manageMsg = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::LNG,
                                    'language_manage_route_unavailable'
                                ) ?? 'Manage Language route is unavailable. Please contact technical support or your domain administrator.';
                                $changeIds[] = $manageId;
                                $manageLink = compact('manageRoute', 'manageId', 'manageMsg');
                            }
                        @endphp
                        <div class="{{ ViewClassNamesConstants::DRP_MN_DSH_END }}">
                            @foreach ($changeLinks as $link)
                                <a
                                    id="{{ $link['id'] }}"
                                    href="{{ $link['route'] }}"
                                    class="dropdown-item {{ $lang === $link['code'] ? 'text-primary' : '' }}"
                                    data-url="{{ $link['route'] }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $link['message'] }}"
                                >
                                    <span>{{ ucfirst($link['language']) }}</span>
                                    <div class="float-end">
                                        <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
                                    </div>
                                </a>
                            @endforeach
                        
                            @if (!empty($createLink))
                                <a
                                    id="{{ $createLink['createId'] }}"
                                    href="{{ $createLink['createRoute'] }}"
                                    class="dropdown-item text-primary"
                                    data-ajax-popup="true"
                                    data-title="{{ __('Create New Language') }}"
                                    data-url="{{ $createLink['createRoute'] }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $createLink['createMsg'] }}"
                                >
                                    {{ __('Create Language') }}
                                </a>
                            @endif
                        
                            @if (!empty($manageLink))
                                <a
                                    id="{{ $manageLink['manageId'] }}"
                                    href="{{ $manageLink['manageRoute'] }}"
                                    class="dropdown-item text-primary"
                                    data-url="{{ $manageLink['manageRoute'] }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $manageLink['manageMsg'] }}"
                                >
                                    {{ __('Manage Language') }}
                                </a>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <script defer>
            (() => {
                const ids = {!! json_encode($guardIds) !!};
                const flagAttr = 'data-listener-active';
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el || el.getAttribute(flagAttr) === 'true') return;
                    el.setAttribute(flagAttr, 'true');
                    el.addEventListener('click', event => {
                        try {
                            const url = el.getAttribute('data-url');
                            const href = el.href;
                            if ((!url || url === '#') && (!href || href === '#')) {
                                event.preventDefault();
                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl = document.createElement('div');
                                    toastEl.className = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                el.setAttribute('data-failed-route', 'true');
                            }
                        } catch {}
                    });
                    const observer = new MutationObserver(() => {
                        if (!document.getElementById(id)) observer.disconnect();
                    });
                    observer.observe(document.body, { childList: true, subtree: true });
                });
            })();
        </script>
        <script defer>
            (() => {
                const ids = {!! json_encode($changeIds) !!};
                const flagAttr = 'data-listener-active';
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el || el.getAttribute(flagAttr) === 'true') return;
                    el.setAttribute(flagAttr, 'true');
                    el.addEventListener('click', event => {
                        try {
                            const url = el.getAttribute('data-url');
                            const href = el.href;
                            if ((!url || url === '#') && (!href || href === '#')) {
                                event.preventDefault();
                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl = document.createElement('div');
                                    toastEl.className = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                el.setAttribute('data-failed-route', 'true');
                            }
                        } catch {}
                    });
                    const observer = new MutationObserver(() => {
                        if (!document.getElementById(id)) observer.disconnect();
                    });
                    observer.observe(document.body, { childList: true, subtree: true });
                });
            })();
        </script>
        <script defer>
            document.querySelectorAll('.theme-avatar img').forEach(img => {
                if (img.hasAttribute('data-reloading-active')) return
                img.setAttribute('data-reloading-active', 'true')
                const fallbacks = [
                'public/assets/images/user/defaults/fictional_tech_lead.webp',
                'assets/images/user/defaults/fictional_tech_lead.png',
                '/public/uploads/avatar/avatar.png',
                '/public/avatar/avatar.png',
                '/public/avatar.png',
                '/public/Modules/landingpage/images/user/avatar.png',
                '/public/Modules/landingpage/images/avatar.png',
                '/public/assets/imgs/avatar.png',
                '/public/assets/avatar.png',
                '/public/storage/avatar/avatar.png',
                '/public/storage/avatar.png'
                ]
                img.addEventListener('error', function() {
                let attempt = parseInt(this.getAttribute('data-reload-attempt') || '0', 10)
                if (!Number.isFinite(attempt) || attempt < 0) attempt = 0
                if (attempt === 0) {
                    this.setAttribute(
                    'data-original-opacity',
                    getComputedStyle(this).opacity || '1'
                    )
                    this.style.transition =
                    (this.style.transition || '') + 'opacity 0.25s ease-in-out'
                    this.style.opacity = '0'
                }
                if (attempt >= fallbacks.length) {
                    this.style.opacity =
                    this.getAttribute('data-original-opacity') || '1'
                    this.removeAttribute('data-reload-attempt')
                    this.removeAttribute('data-original-opacity')
                } else {
                    this.setAttribute(
                    'data-reload-attempt',
                    String(attempt + 1)
                    )
                    this.src = window.location.origin + fallbacks[attempt]
                }
                })
                img.addEventListener('load', function() {
                this.style.opacity =
                    this.getAttribute('data-original-opacity') || '1'
                this.removeAttribute('data-reload-attempt')
                this.removeAttribute('data-original-opacity')
                })
            })
        </script>
    </header>
