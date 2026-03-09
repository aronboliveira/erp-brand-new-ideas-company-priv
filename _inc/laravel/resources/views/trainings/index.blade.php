@php
$user ??= null;
	$lang ??= 'en';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in trainings/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in trainings/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in trainings/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Training') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">{{ __('Training') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	@php
		try {
		    $createBase = VW::TNG . '.create';
		    $createKebab = Str::kebab($createBase);
		    $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
		    $createUrl = $createResolved ? route($createResolved) : '#';
		    $createGuard = Utility::fetchLinkMessage($lang, VW::TNG, 'create_training_route_unavailable') ?? 'Create training route is unavailable. Please contact technical support or your domain administrator.';
		} catch (\Throwable $e) {
		    \Log::error('trainings/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
@endphp
	<div class="{{ VC::FEND }}">
		@can('create training')
			<a href="#"
			   id="training-create-link"
			   data-size="lg"
			   data-url="{{ $createUrl }}"
			   data-ajax-popup="true"
			   data-bs-toggle="tooltip"
			   title="{{ __('Create') }}"
			   data-title="{{ __('Create New Training') }}"
			   data-guard-msg="{{ base64_encode($createGuard) }}"
			   data-sv-localized="true"
			   class="{{ ViewClassNamesConstants::BT_SM_PM }}">
				<i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
			</a>
		@endcan
	</div>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="row">
		<div class="{{ VC::CM12 }}">
			<div class="card">
				<div class="{{ VC::CD_BD_TB_BD }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="table datatable">
							<thead>
								<tr>
									<th>{{ __('Branch') }}</th>
									<th>{{ __('Training Type') }}</th>
									<th>{{ __('Status') }}</th>
									<th>{{ __('Employee') }}</th>
									<th>{{ __('Trainer') }}</th>
									<th>{{ __('Training Duration') }}</th>
									<th>{{ __('Cost') }}</th>
									<th width="200px">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach ($trainings as $training)
									@php
										try {
										    $showBase = VW::TNG . '.show';
										    $showKebab = Str::kebab($showBase);
										    $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
										    $showUrl = $showResolved ? route($showResolved, [Crypt::encrypt($training->id)]) : '#';
										    $showGuard = Utility::fetchLinkMessage($lang, VW::TNG, 'show_training_route_unavailable') ?? 'Show training route is unavailable. Please contact technical support or your domain administrator.';
										    $showId = 'training-show-link-' . $training->id;
										} catch (\Throwable $e) {
										    \Log::error('trainings/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
										}
@endphp
									<tr>
										<td>{{ !empty($training->branches) ? $training->branches->name : '' }}</td>
										<td>{{ !empty($training->types) ? $training->types->name : '' }}</td>
										<td>
											@if($training->status == 0)
												<span class="status_badge badge bg-warning p-2 {{ VC::PX3 }} rounded">{{ __($status[$training->status]) }}</span>
											@elseif($training->status == 1)
												<span class="status_badge badge {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded">{{ __($status[$training->status]) }}</span>
											@elseif($training->status == 2)
												<span class="status_badge badge bg-success p-2 {{ VC::PX3 }} rounded">{{ __($status[$training->status]) }}</span>
											@elseif($training->status == 3)
												<span class="status_badge badge bg-info p-2 {{ VC::PX3 }} rounded">{{ __($status[$training->status]) }}</span>
											@endif
										</td>
										<td>{{ !empty($training->employees) ? $training->employees->name : '' }}</td>
										<td>{{ !empty($training->trainers) ? $training->trainers->firstname : '' }}</td>
										<td>{{ $user?->dateFormat($training->start_date) . ' to ' . $user?->dateFormat($training->end_date) }}</td>
										<td>{{ $user?->priceFormat($training->training_cost) }}</td>
										<td>
<div class="{{ ViewClassNamesConstants::ACT_BTN_INF }}">
										<a href="{{ $showUrl }}"
										   id="{{ $showId }}"
										   class="{{ ViewClassNamesConstants::BT_SM_CT }}"
										   data-bs-toggle="tooltip"
										   title="{{ __('View') }}"
										   data-original-title="{{ __('View Detail') }}"
										   data-guard-msg="{{ base64_encode($showGuard) }}"
										   data-sv-localized="true">
											<i class="{{ ViewClassNamesConstants::TI_EYE_WT }}"></i>
												</a>
											</div>

											@can('edit training')
												@php
													try {
													    $editBase = VW::TNG . '.edit';
													    $editKebab = Str::kebab($editBase);
													    $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
													    $editUrl = $editResolved ? route($editResolved, [$training->id]) : '#';
													    $editGuard = Utility::fetchLinkMessage($lang, VW::TNG, 'edit_training_route_unavailable') ?? 'Edit training route is unavailable. Please contact technical support or your domain administrator.';
													    $editId = 'training-edit-link-' . $training->id;
													} catch (\Throwable $e) {
													    \Log::error('trainings/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
													}
@endphp
												<div class="{{ VC::ACT_BTN_PRIM }}">
													<a href="#"
													   id="{{ $editId }}"
													   data-url="{{ $editUrl }}"
													   data-size="lg"
													   data-ajax-popup="true"
													   data-title="{{ __('Edit Training') }}"
													   class="{{ VC::BT_SM_CT }}"
													   data-bs-toggle="tooltip"
													   title="{{ __('Edit') }}"
													   data-original-title="{{ __('Edit') }}"
													   data-guard-msg="{{ base64_encode($editGuard) }}"
													   data-sv-localized="true">
														<i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
													</a>
												</div>
												@push(StacksConstants::ADM_SCR_PG)
													<script defer>
														(() => {
															try {
																const a = document.getElementById('{{ $editId }}');
																if (!a || a.getAttribute('data-listener-active') === 'true') return;
																a.setAttribute('data-listener-active', 'true');
																const url = a.getAttribute('data-url') || '#';
																if (a.hasAttribute('href') && a.getAttribute('href') === '#' && url !== '#') { a.setAttribute('href', url); }
																a.addEventListener('click', e => {
																	try {
																		const href = a.getAttribute('href') || '#';
																		if (href !== '#') return;
																		e.preventDefault();
																		const msg = a.getAttribute('data-guard-msg') || 'Edit training route is unavailable. Please contact technical support or your domain administrator.';
																		let c = document.getElementById('toast-container');
																		if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
																		const bs = document.querySelector('link[href*="bootstrap"]');
																		if (bs && typeof window.bootstrap !== 'undefined') {
																			const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
																			const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
																			t.appendChild(b); c.appendChild(t); window.bootstrap.Toast.getOrCreateInstance(t).show();
																		} else { alert(msg); }
																		a.setAttribute('data-failed-route','true');
																	} catch (_) {}
																});
															} catch (_) {}
														})();
													</script>
												@endpush
											@endcan

											@can('delete training')
												@php
													try {
													    $delBase = VW::TNG . '.destroy';
													    $delKebab = Str::kebab($delBase);
													    $delResolved = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
													    $delUrl = $delResolved ? route($delResolved, [$training->id]) : '#';
													    $delGuard = Utility::fetchLinkMessage($lang, VW::TNG, 'delete_training_route_unavailable') ?? 'Delete training route is unavailable. Please contact technical support or your domain administrator.';
													    $delFormId = 'delete-form-' . $training->id;
													    $delLinkId = 'training-delete-link-' . $training->id;
													} catch (\Throwable $e) {
													    \Log::error('trainings/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
													}
@endphp
												<div class="{{ VC::ACT_BTN_DNG_2 }}">
													{!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $delFormId]) !!}
														<a href="#"
														   id="{{ $delLinkId }}"
														   class="{{ VC::BT_SM_CT_PR }}"
																data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
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
													<script defer>
														(() => {
															try {
																const a = document.getElementById('{{ $delLinkId }}');
																const f = document.getElementById('{{ $delFormId }}');
																if (!a || !f || a.getAttribute('data-listener-active') === 'true') return;
																a.setAttribute('data-listener-active', 'true');
																a.addEventListener('click', e => {
																	try {
																		const action = f.getAttribute('action') || '#';
																		if (action !== '#') return;
																		e.preventDefault();
																		const msg = a.getAttribute('data-guard-msg') || 'Delete training route is unavailable. Please contact technical support or your domain administrator.';
																		let c = document.getElementById('toast-container');
																		if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
																		const bs = document.querySelector('link[href*="bootstrap"]');
																		if (bs && typeof window.bootstrap !== 'undefined') {
																			const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
																			const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
																			t.appendChild(b); c.appendChild(t); window.bootstrap.Toast.getOrCreateInstance(t).show();
																		} else { alert(msg); }
																		a.setAttribute('data-failed-route','true');
																	} catch (_) {}
																});
															} catch (_) {}
														})();
													</script>
												@endpush
											@endcan

											@push(StacksConstants::ADM_SCR_PG)
												<script defer>
													(() => {
														try {
															const a = document.getElementById('{{ $showId }}');
															if (!a || a.getAttribute('data-listener-active') === 'true') return;
															a.setAttribute('data-listener-active', 'true');
															a.addEventListener('click', e => {
																try {
																	const href = a.getAttribute('href') || '#';
																	if (href !== '#') return;
																	e.preventDefault();
																	const msg = a.getAttribute('data-guard-msg') || 'Show training route is unavailable. Please contact technical support or your domain administrator.';
																	let c = document.getElementById('toast-container');
																	if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
																	const bs = document.querySelector('link[href*="bootstrap"]');
																	if (bs && typeof window.bootstrap !== 'undefined') {
																		const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
																		const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
																		t.appendChild(b); c.appendChild(t); window.bootstrap.Toast.getOrCreateInstance(t).show();
																	} else { alert(msg); }
																	a.setAttribute('data-failed-route','true');
																} catch (_) {}
															});
														} catch (_) {}
													})();
												</script>
											@endpush
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

@push(StacksConstants::ADM_SCR_PG)
	<script defer src="{{ asset('assets/js/routes/trainings/create.js') }}"></script>
@endpush
