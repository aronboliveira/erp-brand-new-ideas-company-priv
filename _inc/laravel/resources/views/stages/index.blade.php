@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Deal Stages')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/stages/lang/reorder.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/stages/reorder.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Deal Stage')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
	<div class="{{ VC::FEND }}">
		<a href="#" data-size="md" data-url="{{ route(VW::STG.'.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create Deal Stage') }}" class="{{ VC::BT_SM_PM }}">
			<i class="{{ VC::TI_PLS }}"></i>
		</a>
	</div>
@endsection
@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="col-3">
			@include('layouts.crm_setup')
		</div>
		<div class="col-9">
			<div class="{{ VC::RW }} justify-content-center">
				<div class="p-3 {{ VC::CD }}">
					<ul class="{{ VC::NAV_PL }} nav-fill" id="pills-tab" role="tablist">
						@php
							$i = 0;
						@endphp
						@forelse((($pipelines ?? null) instanceof Collection || is_array($pipelines ?? null)) ? $pipelines : [] as $key => $pipeline)
							<li class="{{ VC::NV_IT }}" role="presentation">
								<button class="{{ VC::NV_LK }} @if($i==0) active @endif" id="pills-user-tab-1" data-bs-toggle="pill" data-bs-target="#tab{{ $key }}" type="button">
									{{ data_get($pipeline,'name') ?: __('No pipeline name available') }}
								</button>
							</li>
							@php
								$i++;
							@endphp
						@empty
							<li class="{{ VC::NV_IT }}"><button class="{{ VC::NV_LK }}" type="button">{{ __('No pipelines available') }}</button></li>
						@endforelse
					</ul>
				</div>
				<div class="{{ VC::CD }}">
					<div class="card-body">
						<div class="tab-content" id="pills-tabContent">
							@php
								$i = 0;
							@endphp
							@forelse((($pipelines ?? null) instanceof Collection || is_array($pipelines ?? null)) ? $pipelines : [] as $key => $pipeline)
								@php
									$stages = data_get($pipeline,'stages',[]);
									$stagesCount = is_countable($stages) ? count($stages) : 0;
								@endphp
								<div class="tab-pane fade show @if($i==0) active @endif" id="tab{{ $key }}" role="tabpanel" aria-labelledby="pills-user-tab-1">
									<ul class="list-unstyled {{ VC::LGRP }} sortable stage">
										@forelse($stages as $stage)
											<li class="{{ VC::DFL_AIC_JCB_IT }}" data-id="{{ data_get($stage,'id','0') }}">
												<h6 class="{{ VC::MB0 }}">
													<i class="{{ VC::TI_AR_M3 }}" data-feather="move"></i>
													<span>{{ data_get($stage,'name') ?: __('No stage name available') }}</span>
												</h6>
												<span class="{{ VC::FEND }}">
													@can('edit lead stage')
														<div class="{{ VC::ACT_BTN_INF }}">
															@php
                                                                $stgEditBase = ViewsConstants::STG.'.edit';
                                                                $stgEditKebab = Str::kebab($stgEditBase);
                                                                $stgEditResolved = Route::has($stgEditBase) ? $stgEditBase : (Route::has($stgEditKebab) ? $stgEditKebab : null);
                                                                $stageIdValue = data_get($stage,'id','0');
                                                                $stgEncryptedId = $stageIdValue ? Crypt::encrypt($stageIdValue) : null;
                                                                $stgEditUrl = ($stgEditResolved && $stgEncryptedId) ? route($stgEditResolved, $stgEncryptedId) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $stgEditGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::STG, 'edit_stage_route_unavailable') ?? 'Edit stage route is unavailable. Please contact technical support or your domain administrator.';
                                                                $stgEditAnchorId = 'stage-edit-btn-'.$stageIdValue;
                                                            @endphp
                                                            <a
                                                                id="{{ $stgEditAnchorId }}"
                                                                href="{{ $stgEditUrl }}"
                                                                class="{{ VC::BT_SM_FL_CT }}"
                                                                data-url="{{ $stgEditUrl }}"
                                                                data-ajax-popup="true"
                                                                data-size="md"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                                data-title="{{ __('Edit Lead Stages') }}"
                                                                data-guard-msg="{{ $stgEditGuardMsg }}"
                                                                data-sv-localized="true"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const el = document.getElementById('{{ $stgEditAnchorId }}');
                                                                            if (!el) { return; }
                                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                            el.setAttribute('data-listener-active', 'true');
                                                                            el.addEventListener('click', (e) => {
                                                                                try {
                                                                                    const href = el.getAttribute('href') ?? '#';
                                                                                    const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                    if (url !== '#' && href !== '#') { return; }
                                                                                    e.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? 'Edit stage route is unavailable. Please contact technical support or your domain administrator.';
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
													@endcan
													@if($stagesCount > 0)
														@can('delete lead stage')
															<div class="{{ VC::ACT_BTN_DNG_2 }}">
																@php
                                                                    $stgDestroyBase = ViewsConstants::STG.'.destroy';
                                                                    $stgDestroyKebab = Str::kebab($stgDestroyBase);
                                                                    $stgDestroyResolved = Route::has($stgDestroyBase) ? $stgDestroyBase : (Route::has($stgDestroyKebab) ? $stgDestroyKebab : null);
                                                                    $stageIdValue = data_get($stage,'id','0');
                                                                    $stgEncryptedId = $stageIdValue ? Crypt::encrypt($stageIdValue) : null;
                                                                    $stgDestroyUrl = ($stgDestroyResolved && $stgEncryptedId) ? route($stgDestroyResolved, $stgEncryptedId) : '#';
                                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                    $stgDeleteGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::STG, 'delete_stage_route_unavailable') ?? 'Delete stage route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                                    $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                    $stgFormId = 'stage-delete-form-'.$stageIdValue;
                                                                    $stgAnchorId = 'stage-delete-btn-'.$stageIdValue;
                                                                @endphp
                                                                {!! Form::open(['method' => 'DELETE', 'url' => $stgDestroyUrl, 'id' => $stgFormId]) !!}
                                                                    <a
                                                                        id="{{ $stgAnchorId }}"
                                                                        href="#"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                                                        data-confirm-yes="document.getElementById('{{ $stgFormId }}').submit();"
                                                                        data-url="{{ $stgDestroyUrl }}"
                                                                        data-guard-msg="{{ $stgDeleteGuardMsg }}"
                                                                        data-sv-localized="true"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const el = document.getElementById('{{ $stgAnchorId }}');
                                                                                if (!el) { return; }
                                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                el.setAttribute('data-listener-active', 'true');
                                                                                el.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const form = document.getElementById('{{ $stgFormId }}');
                                                                                        const action = form ? (form.getAttribute('action') ?? '#') : '#';
                                                                                        const href = el.getAttribute('href') ?? '#';
                                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                        if (url !== '#' && href !== '#' && action !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Delete stage route is unavailable. Please contact technical support or your domain administrator.';
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
														@endcan
													@endif
												</span>
											</li>
										@empty
											<li><span class="text-muted">{{ __('No stages available') }}</span></li>
										@endforelse
									</ul>
								</div>
								@php
									$i++;
								@endphp
							@empty
								<div class="tab-pane fade show active"><span class="text-muted">{{ __('No pipelines available') }}</span></div>
							@endforelse
						</div>
						<p class="{{ VC::MT4 }}"><strong>{{ __('Note') }} : </strong><b>{{ __('You can easily change order of deal stage using drag & drop.') }}</b></p>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
