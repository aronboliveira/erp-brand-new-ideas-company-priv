@php
	use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants, ViewClassNamesConstants as VC, ViewsConstants as VW, YieldingConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Gate, Facades\Route, Str};

	$lang = Utility::fetchUserLang();

	$dashResolved = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
	$dashUrl = $dashResolved ? route($dashResolved) : '#';
	$dashGuard = Utility::fetchLinkMessage($lang, VW::TNR, 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Trainer') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="breadcrumb-item">
		@php
			$dashId = 'dashboard-link';
		@endphp
		<a id="{{ $dashId }}"
		   href="{{ $dashUrl }}"
		   data-url="{{ $dashUrl }}"
		   data-guard-msg="{{ $dashGuard }}"
		   data-sv-localized="true"
		   {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="breadcrumb-item">{{ __('Trainer') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	<div class="float-end">
		@can('create trainer')
			@php
				$createBase = VW::TNR . '.create';
				$createResolved = Route::has($createBase) ? $createBase : (Route::has(Str::kebab($createBase)) ? Str::kebab($createBase) : null);
				$createUrl = $createResolved ? route($createResolved) : '#';
				$createGuard = Utility::fetchLinkMessage($lang, VW::TNR, 'create_trainer_route_unavailable') ?? 'Create trainer route is unavailable. Please contact technical support or your domain administrator.';
				$createId = 'trainer-create-link';
			@endphp
			<a id="{{ $createId }}"
			   href="{{ $createUrl }}"
			   data-size="lg"
			   data-url="{{ $createUrl }}"
			   data-ajax-popup="true"
			   data-bs-toggle="tooltip"
			   title="{{ __('Create') }}"
			   data-title="{{ __('Create New Trainer') }}"
			   data-guard-msg="{{ $createGuard }}"
			   data-sv-localized="true"
			   class="{{ VC::BT_SM_PM }}">
				<i class="{{ VC::TI_PLS }}"></i>
			</a>
		@endcan
	</div>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="table datatable">
							<thead>
								<tr>
									<th>{{ __('Branch') }}</th>
									<th>{{ __('Full Name') }}</th>
									<th>{{ __('Contact') }}</th>
									<th>{{ __('Email') }}</th>
									@if(Gate::check('edit trainer') || Gate::check('delete trainer') || Gate::check('show trainer'))
										<th width="200px">{{ __('Action') }}</th>
									@endif
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach(($trainers ?? []) as $trainer)
									@php
										$tid = data_get($trainer, 'id', '');
										$branchName = data_get($trainer, 'branches.name');
										$branchName = isset($branchName) && $branchName !== '' ? $branchName : __('No branch available');
										$fullName = trim((data_get($trainer, 'firstname', __('No first name available')) ?? '') . ' ' . (data_get($trainer, 'lastname', __('No last name available')) ?? __('No last name available')));
										$fullName = $fullName !== '' ? $fullName : __('No name available');
										$contact = data_get($trainer, 'contact');
										$contact = isset($contact) && $contact !== '' ? $contact : __('No contact available');
										$email = data_get($trainer, 'email');
										$email = isset($email) && $email !== '' ? $email : __('No email available');
									@endphp
									<tr>
										<td>{{ $branchName }}</td>
										<td>{{ $fullName }}</td>
										<td>{{ $contact }}</td>
										<td>{{ $email }}</td>
										@if(Gate::check('edit trainer') || Gate::check('delete trainer') || Gate::check('show trainer'))
											<td class="d-flex">
												@can('show trainer')
													@php
														$showBase = VW::TNR . '.show';
														$showResolved = Route::has($showBase) ? $showBase : (Route::has(Str::kebab($showBase)) ? Str::kebab($showBase) : null);
														$showUrl = ($showResolved && $tid) ? route($showResolved, $tid) : '#';
														$showGuard = Utility::fetchLinkMessage($lang, VW::TNR, 'view_trainer_route_unavailable') ?? 'View trainer route is unavailable. Please contact technical support or your domain administrator.';
														$showId = 'trainer-show-link-' . $tid;
													@endphp
													<div class="{{ VC::ACT_BTN_INF }}">
														<a id="{{ $showId }}"
														   href="{{ $showUrl }}"
														   data-url="{{ $showUrl }}"
														   data-size="lg"
														   data-ajax-popup="true"
														   data-title="{{ __('Trainer Detail') }}"
														   class="{{ VC::BT_SM_CT }}"
														   data-bs-toggle="tooltip"
														   title="{{ __('View') }}"
														   data-guard-msg="{{ $showGuard }}"
														   data-sv-localized="true">
															<i class="{{ VC::TI_EYE_WT }}"></i>
														</a>
													</div>
													@push(StacksConstants::ADM_SCR_PG)
														<script>
															(() => {
																try {
																	const a = document.getElementById('{{ $showId }}');
																	if (!a) return;
																	if (a.getAttribute('data-listener-active') === 'true') return;
																	a.setAttribute('data-listener-active', 'true');
																	const url = a.getAttribute('data-url') ?? '#';
																	if (a.hasAttribute('href') && (a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
																	a.addEventListener('click', (e) => {
																		try {
																			const href = a.getAttribute('href') ?? '#';
																			if (href && href !== '#') return;
																			e.preventDefault();
																			const msg = a.getAttribute('data-guard-msg') ?? 'View trainer route is unavailable. Please contact technical support or your domain administrator.';
																			let container = document.getElementById('toast-container');
																			if (!container) { container = document.createElement('div'); container.id = 'toast-container'; document.body.appendChild(container); }
																			const bsLink = document.querySelector('link[href*="bootstrap"]');
																			if (bsLink && typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast) {
																				const toast = document.createElement('div'); toast.className = 'toast'; toast.setAttribute('role','alert'); toast.setAttribute('aria-live','assertive'); toast.setAttribute('aria-atomic','true');
																				const body = document.createElement('div'); body.className = 'toast-body'; body.textContent = msg;
																				toast.appendChild(body); container.appendChild(toast);
																				try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
																			} else { alert(msg); }
																			a.setAttribute('data-failed-route', 'true');
																		} catch {}
																	});
																} catch {}
															})();
														</script>
													@endpush
												@endcan
												@can('edit trainer')
													@php
														$editBase = VW::TNR . '.edit';
														$editResolved = Route::has($editBase) ? $editBase : (Route::has(Str::kebab($editBase)) ? Str::kebab($editBase) : null);
														$editUrl = ($editResolved && $tid) ? route($editResolved, $tid) : '#';
														$editGuard = Utility::fetchLinkMessage($lang, VW::TNR, 'edit_trainer_route_unavailable') ?? 'Edit trainer route is unavailable. Please contact technical support or your domain administrator.';
														$editId = 'trainer-edit-link-' . $tid;
													@endphp
													<div class="{{ VC::ACT_BTN_PRIM }}">
														<a id="{{ $editId }}"
														   href="{{ $editUrl }}"
														   data-url="{{ $editUrl }}"
														   data-size="lg"
														   data-ajax-popup="true"
														   data-title="{{ __('Edit Trainer') }}"
														   class="{{ VC::BT_SM_CT }}"
														   data-bs-toggle="tooltip"
														   title="{{ __('Edit') }}"
														   data-guard-msg="{{ $editGuard }}"
														   data-sv-localized="true">
															<i class="{{ VC::TI_PC_WT }}"></i>
														</a>
													</div>
													@push(StacksConstants::ADM_SCR_PG)
														<script>
															(() => {
																try {
																	const a = document.getElementById('{{ $editId }}');
																	if (!a) return;
																	if (a.getAttribute('data-listener-active') === 'true') return;
																	a.setAttribute('data-listener-active', 'true');
																	const url = a.getAttribute('data-url') ?? '#';
																	if (a.hasAttribute('href') && (a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
																	a.addEventListener('click', (e) => {
																		try {
																			const href = a.getAttribute('href') ?? '#';
																			if (href && href !== '#') return;
																			e.preventDefault();
																			const msg = a.getAttribute('data-guard-msg') ?? 'Edit trainer route is unavailable. Please contact technical support or your domain administrator.';
																			let container = document.getElementById('toast-container');
																			if (!container) { container = document.createElement('div'); container.id = 'toast-container'; document.body.appendChild(container); }
																			const bsLink = document.querySelector('link[href*="bootstrap"]');
																			if (bsLink && typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast) {
																				const toast = document.createElement('div'); toast.className = 'toast'; toast.setAttribute('role','alert'); toast.setAttribute('aria-live','assertive'); toast.setAttribute('aria-atomic','true');
																				const body = document.createElement('div'); body.className = 'toast-body'; body.textContent = msg;
																				toast.appendChild(body); container.appendChild(toast);
																				try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
																			} else { alert(msg); }
																			a.setAttribute('data-failed-route', 'true');
																		} catch {}
																	});
																} catch {}
															})();
														</script>
													@endpush
												@endcan
												@can('delete trainer')
													@php
														$destroyBase = VW::TNR . '.destroy';
														$destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has(Str::kebab($destroyBase)) ? Str::kebab($destroyBase) : null);
														$destroyUrl = ($destroyResolved && $tid) ? route($destroyResolved, $tid) : '#';
														$destroyGuard = Utility::fetchLinkMessage($lang, VW::TNR, 'delete_trainer_route_unavailable') ?? 'Delete trainer route is unavailable. Please contact technical support or your domain administrator.';
														$formId = 'delete-form-' . $tid;
													@endphp
													<div class="{{ VC::ACT_BTN_DNG_2 }}">
														{!! Form::open([
															'method'               => 'DELETE',
															'url'                  => $destroyUrl,
															'id'                   => $formId,
															'data-resolved-action' => $destroyUrl,
															'data-guard-msg'       => $destroyGuard,
															'data-sv-localized'    => 'true',
														]) !!}
															<a href="#"
															   class="{{ VC::BT_SM_CT_PR }}"
															   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
															   data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
															   data-bs-toggle="tooltip"
															   title="{{ __('Delete') }}">
																<i class="{{ VC::TI_TRS_WT }}"></i>
															</a>
														{!! Form::close() !!}
													</div>
													@push(StacksConstants::ADM_SCR_PG)
														<script>
															(() => {
																try {
																	const f = document.getElementById('{{ $formId }}');
																	if (!f) return;
																	if (f.getAttribute('data-listener-active') === 'true') return;
																	f.setAttribute('data-listener-active', 'true');
																	const resolved = f.getAttribute('data-resolved-action') || '#';
																	if (f.hasAttribute('action') && (f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') f.setAttribute('action', resolved);
																	f.addEventListener('submit', (e) => {
																		try {
																			const action = f.getAttribute('action') || '#';
																			if (action && action !== '#') return;
																			e.preventDefault();
																			const msg = f.getAttribute('data-guard-msg') || 'Delete trainer route is unavailable. Please contact technical support or your domain administrator.';
																			let container = document.getElementById('toast-container');
																			if (!container) { container = document.createElement('div'); container.id = 'toast-container'; document.body.appendChild(container); }
																			const bsLink = document.querySelector('link[href*="bootstrap"]');
																			if (bsLink && typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast) {
																				const toast = document.createElement('div'); toast.className = 'toast'; toast.setAttribute('role','alert'); toast.setAttribute('aria-live','assertive'); toast.setAttribute('aria-atomic','true');
																				const body = document.createElement('div'); body.className = 'toast-body'; body.textContent = msg;
																				toast.appendChild(body); container.appendChild(toast);
																				try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
																			} else { alert(msg); }
																			f.setAttribute('data-failed-route', 'true');
																		} catch {}
																	});
																} catch {}
															})();
														</script>
													@endpush
												@endcan
											</td>
										@endif
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
	@can('create trainer')
		<script defer src="{{ asset('assets/js/routes/trainers/create.js') }}"></script>
	@endcan
@endpush
