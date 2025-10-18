@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{Support, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Support')}}
@endsection
@section(YieldingConstants::ADM_PG_TTL)
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Support')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Support')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $sptIndexBase = VW::SPT.'.index';
        $sptIndexKebab = Str::kebab($sptIndexBase);
        $sptIndexResolved = Route::has($sptIndexBase) ? $sptIndexBase : (Route::has($sptIndexKebab) ? $sptIndexKebab : null);
        $sptIndexUrl = $sptIndexResolved ? route($sptIndexResolved) : '#';
        $sptCreateBase = VW::SPT.'.create';
        $sptCreateKebab = Str::kebab($sptCreateBase);
        $sptCreateResolved = Route::has($sptCreateBase) ? $sptCreateBase : (Route::has($sptCreateKebab) ? $sptCreateKebab : null);
        $sptCreateUrl = $sptCreateResolved ? route($sptCreateResolved) : '#';
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $listGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'list_support_route_unavailable') ?? 'List support route is unavailable. Please contact technical support or your domain administrator.';
        $createGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'create_support_route_unavailable') ?? 'Create support route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        <a href="{{ $sptIndexUrl }}"
           class="{{ VC::BT_SM_PM }} support-list"
           data-url="{{ $sptIndexUrl }}"
           data-guard-msg="{{ $listGuardMsg }}"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="{{ __('List View') }}">
            <i class="{{ VC::TI_LT }}"></i>
        </a>
        <a href="{{ $sptCreateUrl }}"
           data-size="lg"
           data-url="{{ $sptCreateUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Create') }}"
           data-title="{{ __('Create Support') }}"
           class="{{ VC::BT_SM_PM }} support-create"
           data-guard-msg="{{ $createGuardMsg }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/routes/supports/list.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/supports/create.js') }}" defer></script>
    @endpush
@endsection
@section('filter')
@endsection
@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		@forelse((($supports ?? null) instanceof Collection || is_array($supports ?? null)) ? $supports : [] as $support)
			<div class="{{ VC::CM3 }}">
				<div class="{{ VC::CD_FL }}">
					<div class="card-header">
						<div class="{{ VC::R_ALC }}">
							<div class="{{ VC::C_AT }}">
                                @php
                                    $avatarFile = data_get($support,'createdBy.avatar');
                                    $avatarSrc = !empty($avatarFile)
                                        ? Storage::url('uploads/avatar/'.$avatarFile)
                                        : Storage::url('uploads/avatar/avatar.png');
                                    $unread = (is_object($support) && method_exists($support,'replyUnread')) ? (int)($support->replyUnread() ?? 0) : 0;
                                @endphp
								<a href="#" class="{{ VC::AV_CC }}">
									<img alt="" src="{{ $avatarSrc }}">
									@if($unread > 0)
										<span class="avatar-child avatar-badge bg-success"></span>
									@endif
								</a>
							</div>
							<div class="col">
								<a href="#!" class="{{ VC::DBL }} {{ VC::H6 }} {{ VC::MB0 }}">{{ data_get($support,'createdBy.name') ?: __('No creator name available') }}</a>
								<small class="{{ VC::DBL }} {{ VC::TXT_MT }}">{{ data_get($support,'subject') ?: __('No subject available') }}</small>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="{{ VC::RW }}">
							<div class="col text-center">
								<span class="{{ VC::H6 }} {{ VC::MB0 }}">{{ data_get($support,'ticket_code') ?: __('No code available') }}</span>
								<span class="{{ VC::DBL }} {{ VC::TXSM }}">{{ __('Code') }}</span>
							</div>
							<div class="col text-center">
								@php
									$priorityBadgeClasses = [0 => VC::BG_P, 1 => 'badge-info', 2 => 'badge-warning', 3 => 'badge-danger'];
									$prio = data_get($support,'priority');
									$badge = $priorityBadgeClasses[$prio] ?? 'bg-secondary';
									$priorityMap = Support::$priority ?? [];
									$prioLabel = isset($priorityMap[$prio]) ? __($priorityMap[$prio]) : __('No priority available');
								@endphp
								<span class="{{ VC::H6 }} {{ VC::MB0 }}">
									<span class="text-capitalize {{ VC::BDG }} {{ $badge }} rounded-pill badge-sm">{{ $prioLabel }}</span>
								</span>
								<span class="{{ VC::DBL }} {{ VC::TXSM }}">{{ __('Priority') }}</span>
							</div>
							<div class="col text-center">
                                @php
                                    $attachment = data_get($support,'attachment');
                                    $attachUrl = !empty($attachment) ? Storage::url('uploads/supports/'.$attachment) : null;
                                @endphp
								<span class="{{ VC::H6 }} {{ VC::MB0 }}">
									@if(!empty($attachment))
										<a href="{{ $attachUrl }}" download class="{{ VC::BT_SM }} btn-secondary btn-icon rounded-pill" target="_blank">
											<span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
										</a>
									@else
										{{ __('No attachment found') }}
									@endif
								</span>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<div class="{{ VC::R_ALC }}">
							<div class="col-6 text-start">
								<span data-toggle="tooltip" data-title="{{ __('Created Date') }}">{{ $user?->dateFormat(data_get($support,'created_at')) ?? __('Failed to get created date') }}</span>
							</div>
							<div class="col-6 {{ VC::DFL }} {{ VC::FEND }}">
								<div class="{{ VC::ACT_BTN_WRN }}">
									@php
                                        $sptReplyBase = VW::SPT.'.reply';
                                        $sptReplyKebab = Str::kebab($sptReplyBase);
                                        $sptReplyResolved = Route::has($sptReplyBase) ? $sptReplyBase : (Route::has($sptReplyKebab) ? $sptReplyKebab : null);
                                        $supportId = data_get($support,'id','0');
                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                        $sptReplyUrl = ($sptReplyResolved && $sptEncryptedId) ? route($sptReplyResolved, $sptEncryptedId) : '#';
                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                        $sptReplyGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_support_route_unavailable') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                        $sptReplyAnchorId = 'support-reply-'.$supportId;
                                    @endphp
                                    <a id="{{ $sptReplyAnchorId }}"
                                    href="{{ $sptReplyUrl }}"
                                    data-title="{{ __('Support Reply') }}"
                                    class="{{ VC::BT_SM_CT }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reply') }}"
                                    data-original-title="{{ __('Reply') }}"
                                    data-url="{{ $sptReplyUrl }}"
                                    data-guard-msg="{{ $sptReplyGuardMsg }}"
                                    data-sv-localized="true">
                                        <i class="ti ti-corner-up-left {{ VC::TXT_WT }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const el = document.getElementById('{{ $sptReplyAnchorId }}');
                                                    if (!el) { return; }
                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                    el.setAttribute('data-listener-active', 'true');
                                                    el.addEventListener('click', (e) => {
                                                        try {
                                                            const href = el.getAttribute('href') ?? '#';
                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                            if (url !== '#' && href !== '#') { return; }
                                                            e.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
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
                                                            el.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (err) {}
                                            })();
                                        </script>
                                    @endpush
								</div>
								@if((($user?->id) ?? null) === data_get($support,'ticket_created'))
									<div class="{{ VC::ACT_BTN_PRIM }}">
										@php
                                            $sptEditBase = VW::SPT.'.edit';
                                            $sptEditKebab = Str::kebab($sptEditBase);
                                            $sptEditResolved = Route::has($sptEditBase) ? $sptEditBase : (Route::has($sptEditKebab) ? $sptEditKebab : null);
                                            $supportIdRaw = data_get($support,'id');
                                            $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                            $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                            $sptEditUrl = ($sptEditResolved && $sptEncryptedId) ? route($sptEditResolved, $sptEncryptedId) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $sptEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'edit_support_route_unavailable') ?? 'Edit support route is unavailable. Please contact technical support or your domain administrator.';
                                            $sptEditAnchorId = 'support-edit-'.($supportId ? substr(md5($supportId),0,8) : 'x');
                                        @endphp
                                        <a
                                            id="{{ $sptEditAnchorId }}"
                                            href="{{ $sptEditUrl }}"
                                            data-size="lg"
                                            data-url="{{ $sptEditUrl }}"
                                            data-ajax-popup="true"
                                            data-title="{{ __('Edit Support') }}"
                                            class="{{ VC::BT_SM_CT }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Edit') }}"
                                            data-original-title="{{ __('Edit') }}"
                                            data-guard-msg="{{ $sptEditGuardMsg }}"
                                            data-sv-localized="true"
                                        >
                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const el = document.getElementById('{{ $sptEditAnchorId }}');
                                                        if (!el) { return; }
                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                        el.setAttribute('data-listener-active', 'true');
                                                        el.addEventListener('click', (e) => {
                                                            try {
                                                                const href = el.getAttribute('href') ?? '#';
                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                if (url !== '#' && href !== '#') { return; }
                                                                e.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit support route is unavailable. Please contact technical support or your domain administrator.';
                                                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
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
                                                                el.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    } catch (err) {}
                                                })();
                                            </script>
                                        @endpush
									</div>
									<div class="{{ VC::ACT_BTN_DNG_2 }}">
										@php
                                            $sptDestroyBase = VW::SPT.'.destroy';
                                            $sptDestroyKebab = Str::kebab($sptDestroyBase);
                                            $sptDestroyResolved = Route::has($sptDestroyBase) ? $sptDestroyBase : (Route::has($sptDestroyKebab) ? $sptDestroyKebab : null);
                                            $supportIdRaw = data_get($support,'id');
                                            $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                            $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                            $sptDestroyUrl = ($sptDestroyResolved && $sptEncryptedId) ? route($sptDestroyResolved, $sptEncryptedId) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $sptDeleteGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'delete_support_route_unavailable') ?? 'Delete support route is unavailable. Please contact technical support or your domain administrator.';
                                            $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                            $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                            $formId = 'support-delete-form-'.Str::uuid();
                                            $anchorId = 'support-delete-btn-'.Str::uuid();
                                        @endphp
                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE','url' => $sptDestroyUrl,'id' => $formId]) !!}
                                            <a id="{{ $anchorId }}"
                                            href="#!"
                                            class="{{ VC::BT_SM_CT_PR }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Delete') }}"
                                            data-original-title="{{ __('Delete') }}"
                                            data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                            data-url="{{ $sptDestroyUrl }}"
                                            data-guard-msg="{{ $sptDeleteGuardMsg }}"
                                            data-sv-localized="true">
                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                            </a>
                                        {!! Collective\Html\FormFacade::close() !!}
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const el = document.getElementById('{{ $anchorId }}');
                                                        if (!el) { return; }
                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                        el.setAttribute('data-listener-active', 'true');
                                                        el.addEventListener('click', (e) => {
                                                            try {
                                                                const form = document.getElementById('{{ $formId }}');
                                                                const action = form ? (form.getAttribute('action') ?? '#') : '#';
                                                                const href = el.getAttribute('href') ?? '#';
                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                if (url !== '#' && href !== '#' && action !== '#') { return; }
                                                                e.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Delete support route is unavailable. Please contact technical support or your domain administrator.';
                                                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
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
                                                                el.setAttribute('data-failed-route', 'true');
                                                                if (form) { form.setAttribute('data-failed-route', 'true'); }
                                                            } catch (err) {}
                                                        });
                                                    } catch (err) {}
                                                })();
                                            </script>
                                        @endpush
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		@empty
			<div class="{{ VC::CM12 }}"><p class="text-center text-muted">{{ __('No supports available') }}</p></div>
		@endforelse
	</div>
@endsection

