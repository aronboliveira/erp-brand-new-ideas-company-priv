@php
	use App\Config\Constants\{
		ExtendingLayoutsConstants,
		StacksConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants as VW,
		YieldingConstants
	};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Auth, Facades\Gate, Facades\Route, Str};

	$user = Auth::user();
	$lang = Utility::fetchUserLang(user: $user);

	$dashResolved = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
	$dashUrl = $dashResolved ? route($dashResolved) : '#';
	$dashGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Termination') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="breadcrumb-item">
		<a id="dashboard-link"
		   href="{{ $dashUrl }}"
		   data-url="{{ $dashUrl }}"
		   data-guard-msg="{{ $dashGuardMsg }}"
		   data-sv-localized="true"
		   {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="breadcrumb-item">{{ __('Termination') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	<div class="{{ VC::FEND }}">
		@can('create termination')
			@php
				$createBase = VW::TMN . '.create';
				$createResolved = Route::has($createBase) ? $createBase : (Route::has(Str::kebab($createBase)) ? Str::kebab($createBase) : null);
				$createUrl = $createResolved ? route($createResolved) : '#';
				$createGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN, 'create_termination_unavailable') ?? 'Create termination route is unavailable. Please contact technical support or your domain administrator.';
			@endphp
			<a id="termination-create-link"
			   href="{{ $createUrl }}"
			   data-url="{{ $createUrl }}"
			   data-size="lg"
			   data-ajax-popup="true"
			   data-title="{{ __('Create New Termination') }}"
			   data-bs-toggle="tooltip"
			   title="{{ __('Create') }}"
			   data-guard-msg="{{ $createGuardMsg }}"
			   data-sv-localized="true"
			   class="{{ VC::BT_SM_PM }}">
				<i class="{{ VC::TI_PLS }}"></i>
			</a>
		@endcan
	</div>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="{{ VC::CM12 }}">
			<div class="card">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="table datatable">
							<thead>
								<tr>
									@role('company')
										<th>{{ __('Employee Name') }}</th>
									@endrole
									<th>{{ __('Termination Type') }}</th>
									<th>{{ __('Notice Date') }}</th>
									<th>{{ __('Termination Date') }}</th>
									<th>{{ __('Description') }}</th>
									@if(Gate::check('edit termination') || Gate::check('delete termination'))
										<th>{{ __('Action') }}</th>
									@endif
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach(($terminations ?? []) as $termination)
									@php
										$rowId = data_get($termination, 'id', '');
										$empName = data_get($termination, 'employee.name');
										$empName = isset($empName) && $empName !== '' ? $empName : __('No employee available');
										$tpName = data_get($termination, 'terminationType.name');
										$tpName = isset($tpName) && $tpName !== '' ? $tpName : __('No termination type available');
										$descBase = VW::TMN . '.description';
										$descResolved = Route::has($descBase) ? $descBase : (Route::has(Str::kebab($descBase)) ? Str::kebab($descBase) : null);
										$descUrl = ($descResolved && $rowId) ? route($descResolved, $rowId) : '#';
										$descGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN, 'description_unavailable') ?? 'Description route is unavailable. Please contact technical support or your domain administrator.';
										$descId = 'termination-desc-link-' . $rowId;
									@endphp
									<tr>
										@role('company')
											<td>{{ $empName }}</td>
										@endrole

										<td>{{ $tpName }}</td>
										<td>{{ $user?->dateFormat($termination?->notice_date) ?? __('No date available') }}</td>
										<td>{{ $user?->dateFormat($termination?->termination_date) ?? __('No date available') }}</td>
										<td>
											<a id="{{ $descId }}"
											   href="{{ $descUrl }}"
											   class="action-item"
											   data-url="{{ $descUrl }}"
											   data-ajax-popup="true"
											   data-bs-toggle="tooltip"
											   title="{{ __('Description') }}"
											   data-title="{{ __('Description') }}"
											   data-guard-msg="{{ $descGuardMsg }}"
											   data-sv-localized="true">
												<i class="fa fa-comment text-dark"></i>
											</a>
										</td>

										@if(Gate::check('edit termination') || Gate::check('delete termination'))
											<td>
												@can('edit termination')
													@php
														$editBase = VW::TMN . '.edit';
														$editResolved = Route::has($editBase) ? $editBase : (Route::has(Str::kebab($editBase)) ? Str::kebab($editBase) : null);
														$editUrl = ($editResolved && $rowId) ? route($editResolved, $rowId) : '#';
														$editGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN, 'edit_termination_unavailable') ?? 'Edit termination route is unavailable. Please contact technical support or your domain administrator.';
														$editId = 'termination-edit-link-' . $rowId;
													@endphp
													<div class="{{ VC::ACT_BTN_PRIM }}">
														<a id="{{ $editId }}"
														   href="{{ $editUrl }}"
														   class="{{ VC::BT_SM_CT }}"
														   data-url="{{ $editUrl }}"
														   data-size="lg"
														   data-ajax-popup="true"
														   data-title="{{ __('Edit Termination') }}"
														   data-bs-toggle="tooltip"
														   title="{{ __('Edit') }}"
														   data-guard-msg="{{ $editGuardMsg }}"
														   data-sv-localized="true">
															<i class="{{ VC::TI_PC_WT }}"></i>
														</a>
													</div>
													@push(StacksConstants::ADM_SCRP_PG)
														<script>
															(() => {
																try {
																	const a = document.getElementById('{{ $editId }}');
																	if (!a) return;
																	if (a.getAttribute('data-listener-active') === 'true') return;
																	a.setAttribute('data-listener-active', 'true');

																	const url = a.getAttribute('data-url') ?? '#';
																	if (a.hasAttribute('href') && (a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') {
																		a.setAttribute('href', url);
																	}

																	a.addEventListener('click', (e) => {
																		try {
																			const href = a.getAttribute('href') ?? '#';
																			if (href && href !== '#') return;
																			e.preventDefault();

																			const msg = a.getAttribute('data-guard-msg') ?? 'Edit termination route is unavailable. Please contact technical support or your domain administrator.';
																			let container = document.getElementById('toast-container');
																			if (!container) {
																				container = document.createElement('div');
																				container.id = 'toast-container';
																				document.body.appendChild(container);
																			}
																			const bsLink = document.querySelector('link[href*="bootstrap"]');
																			if (bsLink && typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast) {
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
																				try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
																			} else {
																				alert(msg);
																			}
																			a.setAttribute('data-failed-route', 'true');
																		} catch {}
																	});
																} catch {}
															})();
														</script>
													@endpush
												@endcan
												@can('delete termination')
													@php
														$destroyBase = VW::TMN . '.destroy';
														$destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has(Str::kebab($destroyBase)) ? Str::kebab($destroyBase) : null);
														$destroyUrl = ($destroyResolved && $rowId) ? route($destroyResolved, $rowId) : '#';
														$destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN, 'delete_termination_unavailable') ?? 'Delete termination route is unavailable. Please contact technical support or your domain administrator.';
														$formId = 'delete-form-' . $rowId;
														$btnId  = 'delete-trigger-' . $rowId;
														$confirmMsg = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') . '|' . __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
													@endphp
													<div class="{{ VC::ACT_BTN_DNG_2 }}">
														{!! Form::open([
															'method'               => 'DELETE',
															'url'                  => $destroyUrl,
															'id'                   => $formId,
															'data-resolved-action' => $destroyUrl,
															'data-guard-msg'       => $destroyGuardMsg,
															'data-sv-localized'    => 'true',
														]) !!}
															<a id="{{ $btnId }}"
															   href="#"
															   class="{{ VC::BT_SM_CT_PR }}"
															   data-bs-toggle="tooltip"
															   title="{{ __('Delete') }}"
															   data-confirm-delete="{{ $confirmMsg }}"
															   data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
																<i class="{{ VC::TI_TRS_WT }}"></i>
															</a>
														{!! Form::close() !!}
													</div>
													@push(StacksConstants::ADM_SCRP_PG)
														<script>
															(() => {
																try {
																	const f = document.getElementById('{{ $formId }}');
																	const t = document.getElementById('{{ $btnId }}');
																	if (!f || !t) return;
																	if (f.getAttribute('data-listener-active') === 'true') return;
																	f.setAttribute('data-listener-active', 'true');

																	const resolved = f.getAttribute('data-resolved-action') ?? '#';
																	if (f.hasAttribute('action') && (f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
																		f.setAttribute('action', resolved);
																	}

																	t.addEventListener('click', (e) => {
																		try {
																			/* defer to bs-pass-para confirmation; nothing here */
																		} catch {}
																	});

																	f.addEventListener('submit', (e) => {
																		try {
																			const action = f.getAttribute('action') ?? '#';
																			if (action && action !== '#') return;
																			e.preventDefault();

																			const msg = f.getAttribute('data-guard-msg') ?? 'Delete termination route is unavailable. Please contact technical support or your domain administrator.';
																			let container = document.getElementById('toast-container');
																			if (!container) {
																				container = document.createElement('div');
																				container.id = 'toast-container';
																				document.body.appendChild(container);
																			}
																			const bsLink = document.querySelector('link[href*="bootstrap"]');
																			if (bsLink && typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast) {
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
																				try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
																			} else {
																				alert(msg);
																			}
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
									@push(StacksConstants::ADM_SCRP_PG)
										<script>
											(() => {
												try {
													const a = document.getElementById('{{ $descId }}');
													if (!a) return;
													if (a.getAttribute('data-listener-active') === 'true') return;
													a.setAttribute('data-listener-active', 'true');

													const url = a.getAttribute('data-url') ?? '#';
													if (a.hasAttribute('href') && (a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') {
														a.setAttribute('href', url);
													}

													a.addEventListener('click', (e) => {
														try {
															const href = a.getAttribute('href') ?? '#';
															if (href && href !== '#') return;
															e.preventDefault();

															const msg = a.getAttribute('data-guard-msg') ?? 'Description route is unavailable. Please contact technical support or your domain administrator.';
															let container = document.getElementById('toast-container');
															if (!container) {
																container = document.createElement('div');
																container.id = 'toast-container';
																document.body.appendChild(container);
															}
															const bsLink = document.querySelector('link[href*="bootstrap"]');
															if (bsLink && typeof window.bootstrap !== 'undefined' && window.bootstrap?.Toast) {
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
																try { window.bootstrap.Toast.getOrCreateInstance(toast).show(); } catch { alert(msg); }
															} else {
																alert(msg);
															}
															a.setAttribute('data-failed-route', 'true');
														} catch {}
													});
												} catch {}
											})();
										</script>
									@endpush
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push(StacksConstants::ADM_SCRP_PG)
	@can('create termination')
		<script defer src="{{ asset('assets/js/routes/terminations/create.js') }}"></script>
	@endcan
@endpush
