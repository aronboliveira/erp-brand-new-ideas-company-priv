@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Support, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Support')}}
@endsection
@section('title')
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
    <li class="breadcrumb-item">{{__('Support')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $sptGridBase = VW::SPT.'.grid';
        $sptGridKebab = Str::kebab($sptGridBase);
        $sptGridResolved = Route::has($sptGridBase) ? $sptGridBase : (Route::has($sptGridKebab) ? $sptGridKebab : null);
        $sptGridUrl = $sptGridResolved ? route($sptGridResolved) : '#';
        $sptCreateBase = VW::SPT.'.create';
        $sptCreateKebab = Str::kebab($sptCreateBase);
        $sptCreateResolved = Route::has($sptCreateBase) ? $sptCreateBase : (Route::has($sptCreateKebab) ? $sptCreateKebab : null);
        $sptCreateUrl = $sptCreateResolved ? route($sptCreateResolved) : '#';
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $gridGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'grid_support_route_unavailable') ?? 'Grid support route is unavailable. Please contact technical support or your domain administrator.';
        $createGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'create_support_route_unavailable') ?? 'Create support route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        <a href="{{ $sptGridUrl }}"
           class="{{ VC::BT_SM_PM }} support-grid"
           data-url="{{ $sptGridUrl }}"
           data-guard-msg="{{ $gridGuardMsg }}"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="{{ __('Grid View') }}">
            <i class="ti ti-layout-grid {{ VC::TXT_WT }}"></i>
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
        <script src="{{ asset('assets/js/routes/supports/index/grid.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/supports/create.js') }}" defer></script>
    @endpush
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="{{ VC::CL3 }} {{ VC::CM6 }}">
			<div class="{{ VC::CD }}">
				<div class="card-body">
					<div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="{{ VC::DFL_AIC }}">
								<div class="theme-avatar {{ VC::BG_P }}"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small><h6 class="m-0">{{ __('Ticket') }}</h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0">{{ (int)($countTicket ?? 0) }}</h3></div>
					</div>
				</div>
			</div>
		</div>
		<div class="{{ VC::CL3 }} {{ VC::CM6 }}">
			<div class="{{ VC::CD }}">
				<div class="card-body">
					<div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="{{ VC::DFL_AIC }}">
								<div class="theme-avatar bg-info"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="{{ VC::TXT_MT }}">{{ __('Open') }}</small><h6 class="m-0">{{ __('Ticket') }}</h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0">{{ (int)($countOpenTicket ?? 0) }}</h3></div>
					</div>
				</div>
			</div>
		</div>
		<div class="{{ VC::CL3 }} {{ VC::CM6 }}">
			<div class="{{ VC::CD }}">
				<div class="card-body">
					<div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="{{ VC::DFL_AIC }}">
								<div class="theme-avatar bg-warning"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="{{ VC::TXT_MT }}">{{ __('On Hold') }}</small><h6 class="m-0">{{ __('Ticket') }}</h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0">{{ (int)($countonholdTicket ?? 0) }}</h3></div>
					</div>
				</div>
			</div>
		</div>
		<div class="{{ VC::CL3 }} {{ VC::CM6 }}">
			<div class="{{ VC::CD }}">
				<div class="card-body">
					<div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="{{ VC::DFL_AIC }}">
								<div class="theme-avatar bg-danger"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="{{ VC::TXT_MT }}">{{ __('Close') }}</small><h6 class="m-0">{{ __('Ticket') }}</h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0">{{ (int)($countCloseTicket ?? 0) }}</h3></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="{{ VC::RW }}">
		<div class="{{ VC::CM12 }}">
			<div class="{{ VC::CD }}">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="{{ VC::TB }} datatable">
							<thead>
								<tr>
									<th scope="col">{{ __('Created By') }}</th>
									<th scope="col">{{ __('Ticket') }}</th>
									<th scope="col">{{ __('Code') }}</th>
									<th scope="col">{{ __('Attachment') }}</th>
									<th scope="col">{{ __('Assign User') }}</th>
									<th scope="col">{{ __('Status') }}</th>
									<th scope="col">{{ __('Created At') }}</th>
									<th scope="col">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody class="list">
								@php
									$supportpath = \App\Models\Utility::getFile('uploads/supports') ?? '';
								@endphp
								@forelse((($supports ?? null) instanceof \Illuminate\Support\Collection || is_array($supports ?? null)) ? $supports : [] as $support)
									<tr>
										<td scope="row">
											<div class="{{ VC::MD_AIC }}">
												<div>
													<div class="avatar-parent-child">
														@php
															$avatar = data_get($support,'createdBy.avatar');
															$avatarSrc = !empty($avatar) ? asset(Storage::url('uploads/avatar')).'/'.$avatar : asset(Storage::url('uploads/avatar')).'/avatar.png';
															$unread = (is_object($support) && method_exists($support,'replyUnread')) ? (int)($support->replyUnread() ?? 0) : 0;
														@endphp
														<img alt="" class="{{ VC::AV_CC_SM }} me-1" src="{{ $avatarSrc }}">
														@if($unread > 0)
															<span class="avatar-child avatar-badge bg-success"></span>
														@endif
													</div>
												</div>
												<div class="media-body">{{ data_get($support,'createdBy.name') ?: __('No creator name available') }}</div>
											</div>
										</td>
										<td scope="row">
											<div class="{{ VC::MD_AIC }}">
												<div class="media-body">
													@php
                                                        $sptReplyBase = VW::SPT.'.reply';
                                                        $sptReplyKebab = Str::kebab($sptReplyBase);
                                                        $sptReplyResolved = Route::has($sptReplyBase) ? $sptReplyBase : (Route::has($sptReplyKebab) ? $sptReplyKebab : null);
                                                        $supportIdRaw = data_get($support,'id');
                                                        $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                                        $sptReplyUrl = ($sptReplyResolved && $sptEncryptedId) ? route($sptReplyResolved, $sptEncryptedId) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $sptReplyGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_support_route_unavailable') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                                        $sptReplyAnchorId = 'support-reply-'.Str::uuid();
                                                    @endphp
                                                    <a id="{{ $sptReplyAnchorId }}"
                                                    href="{{ $sptReplyUrl }}"
                                                    class="name {{ VC::H6 }} {{ VC::MB0 }} {{ VC::TXSM }}"
                                                    data-url="{{ $sptReplyUrl }}"
                                                    data-guard-msg="{{ $sptReplyGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Reply') }}">
                                                        {{ data_get($support,'subject') ?: __('No subject available') }}
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
                                                    <br/>
													@php
														$priorityBadgeClasses = [0 => VC::BG_P, 1 => 'bg-info', 2 => 'bg-warning', 3 => 'bg-danger'];
														$prio = data_get($support,'priority');
														$prioClass = $priorityBadgeClasses[$prio] ?? 'bg-secondary';
														$priorityMap = Support::$priority ?? [];
														$prioLabel = isset($priorityMap[$prio]) ? __($priorityMap[$prio]) : __('No priority available');
													@endphp
													<span data-toggle="tooltip" data-title="{{ __('Priority') }}" class="text-capitalize badge {{ $prioClass }} p-2 px-3 rounded">{{ $prioLabel }}</span>
												</div>
											</div>
										</td>
										<td>{{ data_get($support,'ticket_code') ?: __('No code available') }}</td>
										<td>
											@php
												$attachment = data_get($support,'attachment');
												$fileUrl = !empty($attachment) && !empty($supportpath) ? $supportpath.'/'.$attachment : '';
											@endphp
											@if(!empty($fileUrl))
												<a class="{{ VC::ACT_BTN_PRIM }} {{ VC::BT_SM_CT }}" href="{{ $fileUrl }}" download data-bs-toggle="tooltip" title="{{ __('Download') }}" target="_blank">
													<i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
												</a>
												<a href="{{ $fileUrl }}" class="action-btn bg-secondary ms-2 {{ VC::BT_SM_CT }}">
													<span class="btn-inner--icon"><i class="ti ti-crosshair {{ VC::TXT_WT }}"></i></span>
												</a>
											@else
												-
											@endif
										</td>
										<td>{{ data_get($support,'assignUser.name') ?: __('No user name found') }}</td>
										<td>
											@php
												$status = (string) data_get($support,'status','');
												$statusMap = Support::$status ?? [];
												$statusLabel = isset($statusMap[$status]) ? __($statusMap[$status]) : __('No status available');
												$statusClass = $status === 'Open' ? 'bg-success' : ($status === 'Close' ? 'bg-danger' : ($status === 'On Hold' ? 'bg-warning' : 'bg-secondary'));
											@endphp
											<span class="status_badge text-capitalize badge {{ $statusClass }} p-2 px-3 rounded">{{ $statusLabel }}</span>
										</td>
										<td>{{ $user?->dateFormat(data_get($support,'created_at')) ?? __('Failed to get created date') }}</td>
										<td class="Action">
											<span>
												<div class="{{ VC::ACT_BTN_WRN }} me-2">
													@php
                                                        $sptReplyBase = VW::SPT.'.reply';
                                                        $sptReplyKebab = Str::kebab($sptReplyBase);
                                                        $sptReplyResolved = Route::has($sptReplyBase) ? $sptReplyBase : (Route::has($sptReplyKebab) ? $sptReplyKebab : null);
                                                        $supportIdRaw = data_get($support,'id');
                                                        $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                                        $sptReplyUrl = ($sptReplyResolved && $sptEncryptedId) ? route($sptReplyResolved, $sptEncryptedId) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $sptReplyGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_support_route_unavailable') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                                        $sptReplyAnchorId = 'support-reply-'.($supportId ? substr(md5($supportId),0,8) : 'x');
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
												@if((($user?->type) ?? '') === 'company' || (($user?->id) ?? null) === data_get($support,'ticket_created'))
													<div class="{{ VC::ACT_BTN_PRIM }} me-2">
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
                                                                                const href = el.getAttribute('href') ?? '#!';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#!' && action !== '#') { return; }
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
											</span>
										</td>
									</tr>
								@empty
									<tr><td colspan="8" class="text-center text-muted">{{ __('No supports available') }}</td></tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
