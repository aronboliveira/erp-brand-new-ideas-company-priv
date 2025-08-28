@php
	use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants, ViewClassNamesConstants as VC, ViewsConstants as VW, YieldingConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Route, Str};

	$lang = Utility::fetchUserLang();

	$dashboardResolved = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
	$dashboardUrl = $dashboardResolved ? route($dashboardResolved) : '#';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Termination Type') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="breadcrumb-item">
		<a href="{{ $dashboardUrl }}" {{ $dashboardUrl === '#' ? 'aria-disabled=true' : '' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="breadcrumb-item">{{ __('Termination Type') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	<div class="{{ VC::FEND }}">
		@can('create termination type')
			@php
				$createResolved = Route::has(VW::TMN_TP . '.create') ? VW::TMN_TP . '.create' : (Route::has(Str::kebab(VW::TMN_TP . '.create')) ? Str::kebab(VW::TMN_TP . '.create') : null);
				$createUrl = $createResolved ? route($createResolved) : '#';
				$createGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN_TP, 'create_termination_type_unavailable') ?? 'Create termination type route is unavailable. Please contact technical support or your domain administrator.';
			@endphp
			<a id="terminationtype-create-link"
			   href="{{ $createUrl }}"
			   data-url="{{ $createUrl }}"
			   data-ajax-popup="true"
			   data-title="{{ __('Create New Termination Type') }}"
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
	<div class="row">
		<div class="col-3">@include('layouts.hrm_setup')</div>
		<div class="col-9">
			<div class="card">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="table datatable">
							<thead>
								<tr>
									<th>{{ __('Termination Type') }}</th>
									<th width="200px">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach(($terminationtypes ?? []) as $terminationtype)
									@php($rowId = $terminationtype?->id)
									<tr>
										<td>{{ $terminationtype?->name ?? __('No termination type available') }}</td>
										<td>
											@can('edit termination type')
												@php
													$editResolved = Route::has(VW::TMN_TP . '.edit') ? VW::TMN_TP . '.edit' : (Route::has(Str::kebab(VW::TMN_TP . '.edit')) ? Str::kebab(VW::TMN_TP . '.edit') : null);
													$editUrl = ($editResolved && $rowId) ? route($editResolved, $rowId) : '#';
													$editGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN_TP, 'edit_termination_type_unavailable') ?? 'Edit termination type route is unavailable. Please contact technical support or your domain administrator.';
													$editId = 'terminationtype-edit-link-' . $rowId;
												@endphp
												<div class="{{ VC::ACT_BTN_PRIM }}">
													<a id="{{ $editId }}"
													   href="{{ $editUrl }}"
													   data-url="{{ $editUrl }}"
													   data-ajax-popup="true"
													   data-title="{{ __('Edit Document Type') }}"
													   data-bs-toggle="tooltip"
													   title="{{ __('Edit') }}"
													   data-guard-msg="{{ $editGuardMsg }}"
													   data-sv-localized="true"
													   class="{{ VC::BT_SM_CT }}">
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
																if (a.hasAttribute('href') && a.getAttribute('href') === '#' && url !== '#') {
																	a.setAttribute('href', url);
																}

																a.addEventListener('click', (e) => {
																	try {
																		const href = a.getAttribute('href') ?? '#';
																		if (href !== '#') return;
																		e.preventDefault();

																		const msg = a.getAttribute('data-guard-msg') ?? 'Edit termination type route is unavailable. Please contact technical support or your domain administrator.';
																		let container = document.getElementById('toast-container');
																		if (!container) {
																			container = document.createElement('div');
																			container.id = 'toast-container';
																			document.body.appendChild(container);
																		}
																		const bsLink = document.querySelector('link[href*="bootstrap"]');
																		if (bsLink && typeof window.bootstrap !== 'undefined') {
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
											@can('delete termination type')
												@php
													$destroyResolved = Route::has(VW::TMN_TP . '.destroy') ? VW::TMN_TP . '.destroy' : (Route::has(Str::kebab(VW::TMN_TP . '.destroy')) ? Str::kebab(VW::TMN_TP . '.destroy') : null);
													$destroyUrl = ($destroyResolved && $rowId) ? route($destroyResolved, $rowId) : '#';
													$destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::TMN_TP, 'delete_termination_type_unavailable') ?? 'Delete termination type route is unavailable. Please contact technical support or your domain administrator.';
													$formId = 'delete-form-' . $rowId;
													$btnId = 'delete-trigger-' . $rowId;
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
														<a id="{{ $btnId }}" href="#" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
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
																if (f.hasAttribute('action') && f.getAttribute('action') === '#' && resolved !== '#') {
																	f.setAttribute('action', resolved);
																}

																t.addEventListener('click', (e) => {
																	try { f.submit(); } catch { e.preventDefault(); }
																});

																f.addEventListener('submit', (e) => {
																	try {
																		const action = f.getAttribute('action') ?? '#';
																		if (action !== '#') return;
																		e.preventDefault();

																		const msg = f.getAttribute('data-guard-msg') ?? 'Delete termination type route is unavailable. Please contact technical support or your domain administrator.';
																		let container = document.getElementById('toast-container');
																		if (!container) {
																			container = document.createElement('div');
																			container.id = 'toast-container';
																			document.body.appendChild(container);
																		}
																		const bsLink = document.querySelector('link[href*="bootstrap"]');
																		if (bsLink && typeof window.bootstrap !== 'undefined') {
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

@push(StacksConstants::ADM_SCRP_PG)
	<script defer src="{{ asset('assets/js/routes/terminations/types/create.js') }}"></script>
@endpush
