@php
	try {
$lang = Utility::fetchUserLang();

		$dashBase   = 'dashboard';
		$dashKebab  = Str::kebab($dashBase);
		$dashName   = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
		$dashUrl    = $dashName ? route($dashName) : '#';
		$dashGuard  = Utility::fetchLinkMessage($lang, VW::TNG_TP ?? 'training_types', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
		$dashId     = 'dashboard-link';
	} catch (\Throwable $e) {
		\Log::error('training_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Training Type') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a id="{{ $dashId }}"
		   href="{{ $dashUrl }}"
		   data-url="{{ $dashUrl }}"
		   data-guard-msg="{{ base64_encode($dashGuard) }}"
		   data-sv-localized="true"
		   {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">{{ __('Training Type') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
	<div class="{{ VC::FEND }}">
		@can('create training type')
			@php
				try {
				    $createBase   = VW::TNG_TP . '.create';
				    $createKebab  = Str::kebab($createBase);
				    $createName   = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
				    $createUrl    = $createName ? route($createName) : '#';
				    $createGuard  = Utility::fetchLinkMessage($lang, VW::TNG_TP, 'create_training_type_unavailable') ?? 'Create training type route is unavailable. Please contact technical support or your domain administrator.';
				    $createId     = 'training-type-create-link';
				} catch (\Throwable $e) {
				    \Log::error('training_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
				}
@endphp
			<a id="{{ $createId }}"
			   href="{{ $createUrl }}"
			   data-url="{{ $createUrl }}"
			   data-ajax-popup="true"
			   data-title="{{ __('Create New Training Type') }}"
			   data-bs-toggle="tooltip"
			   title="{{ __('Create') }}"
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
		<div class="{{ VC::C3 }}">
			@include('layouts.hrm_setup')
		</div>
		<div class="{{ VC::C9 }}">
			<div class="card">
				<div class="{{ VC::CD_BD_TB_BD }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="table datatable">
							<thead>
								<tr>
									<th>{{ __('Training Type') }}</th>
									<th width="200px">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach (($trainingtypes ?? []) as $trainingtype)
									@php
										try {
										    $ttId = data_get($trainingtype, 'id', '');
										    $name = data_get($trainingtype, 'name');
										    $name = isset($name) && $name !== '' ? $name : __('No name available');
										} catch (\Throwable $e) {
										    \Log::error('training_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
										}
@endphp
									<tr>
										<td>{{ $name }}</td>
										<td class="{{ VC::DFL }}">
											@can('edit training type')
												@php
													try {
													    $editBase   = VW::TNG_TP . '.edit';
													    $editKebab  = Str::kebab($editBase);
													    $editName   = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
													    $editUrl    = ($editName && $ttId) ? route($editName, [$ttId]) : '#';
													    $editGuard  = Utility::fetchLinkMessage($lang, VW::TNG_TP, 'edit_training_type_unavailable') ?? 'Edit training type route is unavailable. Please contact technical support or your domain administrator.';
													    $editId     = 'training-type-edit-link-' . $ttId;
													} catch (\Throwable $e) {
													    \Log::error('training_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
													}
@endphp
												<div class="{{ VC::ACT_BTN_PRIM }}">
													<a id="{{ $editId }}"
													   href="{{ $editUrl }}"
													   data-url="{{ $editUrl }}"
													   data-ajax-popup="true"
													   data-title="{{ __('Edit Training Type') }}"
													   class="{{ VC::BT_SM_CT }}"
													   data-bs-toggle="tooltip"
													   title="{{ __('Edit') }}"
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
																const url = a.getAttribute('data-url') ?? '#';
																if (a.hasAttribute('href') && (a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') {
																	a.setAttribute('href', url);
																}
																a.addEventListener('click', (e) => {
																	try {
																		const href = a.getAttribute('href') ?? '#';
																		if (href && href !== '#') return;
																		e.preventDefault();
																		const msg = a.getAttribute('data-guard-msg') ?? 'Edit training type route is unavailable. Please contact technical support or your domain administrator.';

																		(window.RouteGuard?.showToast || (m => alert(m)))(msg);
																		a.setAttribute('data-failed-route', 'true');
																	} catch {}
																});
															} catch {}
														})();
													</script>
												@endpush
											@endcan

											@can('delete training type')
												@php
													try {
													    $delBase    = VW::TNG_TP . '.destroy';
													    $delKebab   = Str::kebab($delBase);
													    $delName    = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
													    $delUrl     = ($delName && $ttId) ? route($delName, [$ttId]) : '#';
													    $delGuard   = Utility::fetchLinkMessage($lang, VW::TNG_TP, 'delete_training_type_unavailable') ?? 'Delete training type route is unavailable. Please contact technical support or your domain administrator.';
													    $delFormId  = 'delete-form-' . $ttId;
													} catch (\Throwable $e) {
													    \Log::error('training_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
													}
@endphp
												<div class="{{ VC::ACT_BTN_DNG_2 }}">
													{!! Form::open([
														'method'               => 'DELETE',
														'url'                  => $delUrl,
														'id'                   => $delFormId,
														'data-resolved-action' => $delUrl,
														'data-guard-msg'       => $delGuard,
														'data-sv-localized'    => 'true',
													]) !!}
														<a href="#"
														   class="{{ VC::BT_SM_CT_PR }}"
														   data-bs-toggle="tooltip"
														   title="{{ __('Delete') }}"
														   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
														   data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
															<i class="{{ VC::TI_TRS_WT }}"></i>
														</a>
													{!! Form::close() !!}
												</div>
												@push(StacksConstants::ADM_SCR_PG)
													<script>
														(() => {
															try {
																const f = document.getElementById('{{ $delFormId }}');
																if (!f) return;
																if (f.getAttribute('data-listener-active') === 'true') return;
																f.setAttribute('data-listener-active', 'true');

																const resolved = f.getAttribute('data-resolved-action') || '#';
																if (f.hasAttribute('action') && (f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
																	f.setAttribute('action', resolved);
																}

																f.addEventListener('submit', (e) => {
																	try {
																		const action = f.getAttribute('action') || '#';
																		if (action && action !== '#') return;
																		e.preventDefault();

																		const msg = f.getAttribute('data-guard-msg') || 'Delete training type route is unavailable. Please contact technical support or your domain administrator.';

																		(window.RouteGuard?.showToast || (m => alert(m)))(msg);
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

@push(StacksConstants::ADM_SCR_PG)
	@can('create training type')
		<script defer src="{{ asset('assets/js/routes/trainings/types/create.js') }}"></script>
	@endcan
@endpush

    {{--                                        @can('edit training type')--}}
    {{--                                            <a href="#" data-url="{{ route('trainingtype.edit',$trainingtype->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Training Type')}}" class="edit-icon"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>--}}
    {{--                                        @endcan--}}
