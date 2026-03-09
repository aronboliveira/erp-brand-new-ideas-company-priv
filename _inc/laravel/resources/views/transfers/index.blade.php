@php
$user ??= null;
	$lang ??= 'en';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in transfers/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in transfers/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in transfers/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Transfer') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">{{ __('Transfer') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	@php
		try {
		    $createBase    = VW::TRF . '.create';
		    $createKebab   = Str::kebab($createBase);
		    $createResolved= Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
		    $createUrl     = $createResolved ? route($createResolved) : '#';
		    $createGuard   = Utility::fetchLinkMessage($lang, VW::TRF, 'create_transfer_route_unavailable') ?? 'Create transfer route is unavailable. Please contact technical support or your domain administrator.';
		    $createId      = 'transfer-create-link';
		} catch (\Throwable $e) {
		    \Log::error('transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
@endphp
	<div class="{{ VC::FEND }}">
		@can('create transfer')
			<a id="{{ $createId }}"
			   href="{{ $createUrl }}"
			   data-size="lg"
			   data-url="{{ $createUrl }}"
			   data-ajax-popup="true"
			   data-bs-toggle="tooltip"
			   title="{{ __('Create') }}"
			   data-title="{{ __('Create New Transfer') }}"
			   data-guard-msg="{{ base64_encode($createGuard) }}"
			   data-sv-localized="true"
			   class="{{ VC::BT_SM_PM }}">
				<i class="{{ VC::TI_PLS }}"></i>
			</a>
		@endcan
	</div>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="row">
		<div class="{{ VC::CXL12 }}">
			<div class="card">
				<div class="{{ VC::CD_BD_TB_BD }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="table datatable">
							<thead>
								<tr>
									@role('company')
										<th>{{ __('Employee Name') }}</th>
									@endrole
									<th>{{ __('Branch') }}</th>
									<th>{{ __('Department') }}</th>
									<th>{{ __('Transfer Date') }}</th>
									<th>{{ __('Description') }}</th>
									@if(Gate::check('edit transfer') || Gate::check('delete transfer'))
										<th width="200px">{{ __('Action') }}</th>
									@endif
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach ($transfers as $transfer)
									<tr>
										@role('company')
											<td>{{ !empty($transfer->employee) ? $transfer->employee->name : '' }}</td>
										@endrole
										<td>{{ !empty($transfer->branch) ? $transfer->branch->name : '' }}</td>
										<td>{{ !empty($transfer->department) ? $transfer->department->name : '' }}</td>
										<td>{{ $user?->dateFormat($transfer->transfer_date) }}</td>
										<td>{{ $transfer->description }}</td>
										@if(Gate::check('edit transfer') || Gate::check('delete transfer'))
											<td>
												@can('edit transfer')
													@php
														try {
														    $editBase     = VW::TRF . '.edit';
														    $editKebab    = Str::kebab($editBase);
														    $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
														    $editUrl      = $editResolved ? route($editResolved, [$transfer->id]) : '#';
														    $editGuard    = Utility::fetchLinkMessage($lang, VW::TRF, 'edit_transfer_route_unavailable') ?? 'Edit transfer route is unavailable. Please contact technical support or your domain administrator.';
														    $editId       = 'transfer-edit-link-' . $transfer->id;
														} catch (\Throwable $e) {
														    \Log::error('transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
														}
@endphp
													<div class="{{ VC::ACT_BTN_PRIM }}">
														<a href="{{ $editUrl }}"
														   id="{{ $editId }}"
														   data-url="{{ $editUrl }}"
														   data-size="lg"
														   data-ajax-popup="true"
														   data-title="{{ __('Edit Transfer') }}"
														   class="{{ VC::BT_SM_CT }}"
														   data-bs-toggle="tooltip"
														   title="{{ __('Edit') }}"
														   data-original-title="{{ __('Edit') }}"
														   data-guard-msg="{{ base64_encode($editGuard) }}"
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
																	const url = a.getAttribute('data-url') || '#';
																	if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
																	a.addEventListener('click', (e) => {
																		try {
																			const href = a.getAttribute('href') || '#';
																			if (href !== '#') return;
																			e.preventDefault();
																			const msg = a.getAttribute('data-guard-msg') || 'Edit transfer route is unavailable. Please contact technical support or your domain administrator.';
																			(window.RouteGuard?.showToast || (m => alert(m)))(msg);
																			a.setAttribute('data-failed-route','true');
																		} catch {}
																	});
																} catch {}
															})();
														</script>
													@endpush
												@endcan

												@can('delete transfer')
													@php
														try {
														    $delBase      = VW::TRF . '.destroy';
														    $delKebab     = Str::kebab($delBase);
														    $delResolved  = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
														    $delUrl       = $delResolved ? route($delResolved, [$transfer->id]) : '#';
														    $delGuard     = Utility::fetchLinkMessage($lang, VW::TRF, 'delete_transfer_route_unavailable') ?? 'Delete transfer route is unavailable. Please contact technical support or your domain administrator.';
														    $delFormId    = 'delete-form-' . $transfer->id;
														    $delLinkId    = 'transfer-delete-link-' . $transfer->id;
														} catch (\Throwable $e) {
														    \Log::error('transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
														}
@endphp
													<div class="{{ VC::ACT_BTN_DNG_2 }}">
														{!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $delFormId]) !!}
															<a href="#"
															   id="{{ $delLinkId }}"
															   class="{{ VC::BT_SM_CT_PR }}"
															   data-confirm="{{ __('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?') }}"
															   data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();"
															   data-bs-toggle="tooltip"
															   title="{{ __('Delete') }}"
															   data-original-title="{{ __('Delete') }}"
															   data-guard-msg="{{ base64_encode($delGuard) }}"
															   data-sv-localized="true">
																<i class="{{ VC::TI_TRS_WT }}"></i>
															</a>
														{!! Collective\Html\FormFacade::close() !!}
													</div>
													@push(StacksConstants::ADM_SCR_PG)
														<script>
															(() => {
																try {
																	const a = document.getElementById('{{ $delLinkId }}');
																	const f = document.getElementById('{{ $delFormId }}');
																	if (!a || !f) return;
																	if (a.getAttribute('data-listener-active') === 'true') return;
																	a.setAttribute('data-listener-active', 'true');
																	a.addEventListener('click', (e) => {
																		try {
																			const action = f.getAttribute('action') || '#';
																			if (action !== '#') return;
																			e.preventDefault();
																			const msg = a.getAttribute('data-guard-msg') || 'Delete transfer route is unavailable. Please contact technical support or your domain administrator.';
																			(window.RouteGuard?.showToast || (m => alert(m)))(msg);
																			a.setAttribute('data-failed-route','true');
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
	@can('create transfer')
		<script defer src="{{ asset('assets/js/routes/transfers/create.js') }}"></script>
	@endcan
@endpush
