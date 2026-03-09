@php
$allowanceoptions ??= [];
	$lang ??= '';
	$createRoute ??= '#';
	$createId ??= 'allowance-option-create-link';
	$createMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$createRoute = Route::has(VW::ALW_OPT.'.create')
			? (route(VW::ALW_OPT.'.create') ?? '#')
			: (Route::has(Str::kebab(VW::ALW_OPT.'.create'))
				? (route(Str::kebab(VW::ALW_OPT.'.create')) ?? '#')
				: '#');
		$createMsg = Utility::fetchLinkMessage(
			$lang,
			VW::ALW_OPT,
			'allowance_option_create_route_unavailable'
		) ?? 'Create Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in allowance_options/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in allowance_options/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in allowance_options/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(EL::ADM)
@section(YC::ADM_PG_TTL)
	{{ __('Manage Allowance Option') }}
@endsection
@section(YC::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a
			href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
			{{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}
		>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">{{ __('Allowance Option') }}</li>
@endsection
@section(YC::ADM_ACT_BTN)
	<div class="{{ VCN::FEND }}">
		@can('create allowance option')
			<a
				id="{{ $createId }}"
				href="{{ $createRoute }}"
				data-url="{{ $createRoute }}"
				data-sv-localized="true"
				data-guard-msg="{{ base64_encode($createMsg) }}"
				data-ajax-popup="true"
				data-title="{{ __('Create New Allowance Option') }}"
				data-bs-toggle="tooltip"
				title="{{ __('Create') }}"
				class="{{ VCN::BT_SM_PM }}"
			>
				<i class="{{ VCN::TI_PLS }}"></i>
			</a>
		@endcan
	</div>
@endsection
@section(YC::ADM_CTT)
	<div class="{{ VCN::RW }}">
		<div class="{{ VC::C3 }}">
			@include('layouts.hrm_setup')
		</div>
		<div class="{{ VC::C9 }}">
			<div class="{{ VCN::CD }}">
				<div class="{{ VC::CD_BD_TB_BD }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="{{ VCN::TB }} datatable">
							<thead>
								<tr>
									<th>{{ __('Allowance Option') }}</th>
									<th width="200px">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody class="font-style">
								@foreach($allowanceoptions as $option)
									@php
										$optionId ??= null;
										$optionName ??= '';
										try {
											$optionId = $option->id ?? null;
											$optionName = is_string($option->name ?? null)
												? $option->name
												: '';
										} catch (\Throwable $e) {
											Log::error('Error getting option data in allowance_options/index.blade.php', [
												'exception_class' => get_class($e),
												'message' => $e->getMessage(),
												'file' => $e->getFile(),
												'line' => $e->getLine(),
											]);
										}
@endphp
									<tr>
										<td>{{ $optionName }}</td>
										<td>
											@can('edit allowance option')
												<div class="{{ VCN::ACT_BTN_PRIM }}">
													@php
														$editRoute ??= '#';
														$editId ??= '';
														$editMsg ??= '';
														try {
															$editRoute = Route::has(VW::ALW_OPT.'.edit')
																? (route(VW::ALW_OPT.'.edit', $optionId) ?? '#')
																: (Route::has(Str::kebab(VW::ALW_OPT.'.edit'))
																	? (route(Str::kebab(VW::ALW_OPT.'.edit'), $optionId) ?? '#')
																	: '#');
															$editId = "allowance-option-edit-{$optionId}-link";
															$editMsg = Utility::fetchLinkMessage(
																$lang,
																VW::ALW_OPT,
																'allowance_option_edit_route_unavailable'
															) ?? 'Edit Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
														} catch (\Throwable $e) {
															Log::error('Error building edit route in allowance_options/index.blade.php', [
																'exception_class' => get_class($e),
																'message' => $e->getMessage(),
																'file' => $e->getFile(),
																'line' => $e->getLine(),
															]);
														}
@endphp
													<a
														id="{{ $editId }}"
														href="{{ $editRoute }}"
														data-url="{{ $editRoute }}"
														data-sv-localized="true"
														data-guard-msg="{{ base64_encode($editMsg) }}"
														data-ajax-popup="true"
														data-title="{{ __('Edit Allowance Option') }}"
														data-bs-toggle="tooltip"
														title="{{ __('Edit') }}"
														class="{{ VCN::BT_SM_MX3 }} {{ VCN::AL_IT_CT }}"
													>
														<i class="{{ VCN::TI_PC_WT }}"></i>
													</a>
												</div>
											@endcan
											@can('delete allowance option')
												@php
													$destroyRoute ??= '#';
													$deleteId ??= '';
													$deleteMsg ??= '';
													$confirmTitle ??= 'Are You Sure?';
													$confirmBody ??= 'This action can not be undone. Do you want to continue?';
													try {
														$destroyRoute = Route::has(VW::ALW_OPT.'.destroy')
															? (route(VW::ALW_OPT.'.destroy', $optionId) ?? '#')
															: (Route::has(Str::kebab(VW::ALW_OPT.'.destroy'))
																? (route(Str::kebab(VW::ALW_OPT.'.destroy'), $optionId) ?? '#')
																: '#');
														$deleteId = "allowance-option-delete-{$optionId}-link";
														$deleteMsg = Utility::fetchLinkMessage(
															$lang,
															VW::ALW_OPT,
															'allowance_option_destroy_route_unavailable'
														) ?? 'Delete Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
														$confirmTitle = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure')
															?? 'Are You Sure?';
														$confirmBody = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action')
															?? 'This action can not be undone. Do you want to continue?';
													} catch (\Throwable $e) {
														Log::error('Error building destroy route in allowance_options/index.blade.php', [
															'exception_class' => get_class($e),
															'message' => $e->getMessage(),
															'file' => $e->getFile(),
															'line' => $e->getLine(),
														]);
													}
@endphp
												<div class="{{ VCN::ACT_BTN_DNG_2 }}">
													{{ Form::open([
														'method' => 'DELETE',
														'url'    => $destroyRoute,
														'id'     => "delete-form-{$optionId}"
													]) }}
														<a
															id="{{ $deleteId }}"
															href="#"
															class="{{ VCN::BT_SM_CT_PR }}"
															data-url="{{ $destroyRoute }}"
															data-sv-localized="true"
															data-guard-msg="{{ base64_encode($deleteMsg) }}"
															data-bs-toggle="tooltip"
															title="{{ __('Delete') }}"
															data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
															data-confirm-yes="document.getElementById('delete-form-{{ $optionId }}').submit();"
														>
															<i class="{{ VCN::TI_TRS_WT }}"></i>
														</a>
													{{ Form::close() }}
												</div>
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
	<script async src="{{ asset('assets/js/routes/allowanceOptions/lang/index.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/allowanceOptions/index.js') }}"></script>
@endsection
