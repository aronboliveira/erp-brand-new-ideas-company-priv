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
	use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
	use Illuminate\Support\Str;

	$user = Auth::user();
	$lang = Utility::fetchUserLang(user: $user);

	$trainingTypeText   = data_get($training, 'types.name') ?? __('No training type available');
	$trainerNameText    = data_get($training, 'trainers.firstname') ?? __('No trainer available');
	$trainingCostText   = isset($training->training_cost) ? ($user?->priceFormat($training->training_cost) ?? __('Failed to format training cost')) : __('Failed to get training cost');
	$startDateText      = data_get($training, 'start_date') ? ($user?->dateFormat($training->start_date) ?? __('Failed to format start date')) : __('Failed to get start date');
	$endDateText        = data_get($training, 'end_date') ? ($user?->dateFormat($training->end_date) ?? __('Failed to format end date')) : __('Failed to get end date');
	$createdAtText      = data_get($training, 'created_at') ? ($user?->dateFormat($training->created_at) ?? __('Failed to format date')) : __('Failed to get date');
	$employeeId         = data_get($training, 'employees.id');
	$employeeNameText   = data_get($training, 'employees.name') ?? __('No employee available');
	$employeeDesigText  = data_get($training, 'employees.designation.name') ?? '';
	$avatarFile         = data_get($training, 'employees.user.avatar');
	$avatarBase         = asset(Storage::url('uploads/avatar'));
	$avatarUrl          = $avatarFile ? ($avatarBase . '/' . $avatarFile) : ($avatarBase . '/avatar.png');

	$dashBase           = 'dashboard';
	$dashResolved       = Route::has($dashBase) ? $dashBase : null;
	$dashUrl            = $dashResolved ? route($dashResolved) : '#';
	$dashGuard          = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';

	$indexBase          = VW::TNG . '.index';
	$indexKebab         = Str::kebab($indexBase);
	$indexResolved      = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
	$indexUrl           = $indexResolved ? route($indexResolved) : '#';
	$indexGuard         = Utility::fetchLinkMessage($lang, VW::TNG, 'index_training_route_unavailable') ?? 'Training index route is unavailable. Please contact technical support or your domain administrator.';

	$empShowBase        = VW::EMP . '.show';
	$empShowKebab       = Str::kebab($empShowBase);
	$empShowResolved    = Route::has($empShowBase) ? $empShowBase : (Route::has($empShowKebab) ? $empShowKebab : null);
	$empShowUrl         = ($empShowResolved && $employeeId) ? route($empShowResolved, [Crypt::encrypt($employeeId)]) : '#';
	$empShowGuard       = Utility::fetchLinkMessage($lang, VW::EMP, 'show_employee_route_unavailable') ?? 'Show employee route is unavailable. Please contact technical support or your domain administrator.';

	$statusBase         = VW::TNG . '.status';
	$statusKebab        = Str::kebab($statusBase);
	$statusResolved     = Route::has($statusBase) ? $statusBase : (Route::has($statusKebab) ? $statusKebab : null);
	$statusUrl          = ($statusResolved && data_get($training, 'id')) ? route($statusResolved, [$training->id]) : '#';
	$statusGuard        = Utility::fetchLinkMessage($lang, VW::TNG, 'update_training_status_route_unavailable') ?? 'Update training status route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Training Details') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="breadcrumb-item">
		<a href="{{ $dashUrl }}"
		   id="dashboard-link"
		   {{ $dashResolved ? '' : 'aria-disabled=true' }}
		   data-url="{{ $dashUrl }}"
		   data-guard-msg="{{ $dashGuard }}"
		   data-sv-localized="true">
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="breadcrumb-item">
		<a href="{{ $indexUrl }}"
		   id="training-index-link"
		   data-url="{{ $indexUrl }}"
		   data-guard-msg="{{ $indexGuard }}"
		   data-sv-localized="true">
			{{ __('Training') }}
		</a>
	</li>
	<li class="breadcrumb-item">{{ __('Training Details') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="{{ VC::TB }}">
							<tbody>
								<tr>
									<td>{{ __('Training Type') }}</td>
									<td class="text-end">{{ $trainingTypeText }}</td>
								</tr>
								<tr>
									<td>{{ __('Trainer') }}</td>
									<td class="text-end">{{ $trainerNameText }}</td>
								</tr>
								<tr>
									<td>{{ __('Training Cost') }}</td>
									<td class="text-end">{{ $trainingCostText }}</td>
								</tr>
								<tr>
									<td>{{ __('Start Date') }}</td>
									<td class="text-end">{{ $startDateText }}</td>
								</tr>
								<tr>
									<td>{{ __('End Date') }}</td>
									<td class="text-end">{{ $endDateText }}</td>
								</tr>
								<tr>
									<td>{{ __('Date') }}</td>
									<td class="text-end">{{ $createdAtText }}</td>
								</tr>
							</tbody>
						</table>
						<div class="text-sm mt-4 p-2">
							{{ data_get($training, 'description') ?? __('No description available') }}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body table-border-style">
					<div class="row">
						<div class="col-md-12">
							<h6>{{ __('Training Employee') }}</h6>
							<hr>
							<div class="media-list" id="all_employees_list">
								<ul class="list-group list-group-flush">
									<li class="list-group-item" style="border:0;">
										<div class="{{ VC::MD_AIC }}">
											<img src="{{ $avatarUrl }}" class="user-image-hr-prj ui-w-30 {{ VC::AV_CC }}" width="50" height="50" alt="{{ $employeeNameText }}">
											<div class="media-body px-2 text-sm">
												<a href="{{ $empShowUrl }}"
												   id="employee-show-link"
												   class="text-dark"
												   data-url="{{ $empShowUrl }}"
												   data-guard-msg="{{ $empShowGuard }}"
												   data-sv-localized="true">
													{{ $employeeNameText }}
												</a>
												<br>
												{{ $employeeDesigText }}
											</div>
										</div>
									</li>
                                </ul>
							</div>

							{!! Form::open([
								'url'                  => $statusUrl,
								'method'               => 'post',
								'id'                   => 'training-status-form',
								'data-resolved-action' => $statusUrl,
								'data-guard-msg'       => $statusGuard,
								'data-sv-localized'    => 'true',
							]) !!}
								<h6>{{ __('Update Status') }}</h6>
								<hr>
								<div class="row col-md-12">
									<div class="col-md-6">
										<input type="hidden" name="id" value="{{ data_get($training, 'id', '') }}">
										<div class="{{ VC::FM_G }}">
											{{ Form::label('performance', __('Performance'), ['class' => VC::FM_LB . ' text-dark']) }}
											{{ Form::select('performance', $performance ?? [], null, ['class' => VC::FM_CT_SL]) }}
										</div>
									</div>
									<div class="col-md-6">
										<div class="{{ VC::FM_G }}">
											{{ Form::label('status', __('Status'), ['class' => VC::FM_LB . ' text-dark']) }}
											{{ Form::select('status', $status ?? [], null, ['class' => VC::FM_CT_SL]) }}
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="{{ VC::FM_G }}">
										{{ Form::label('remarks', __('Remarks'), ['class' => VC::FM_LB . ' text-dark']) }}
										{{ Form::textarea('remarks', null, ['class' => VC::FM_CT, 'rows' => 3, 'placeholder' => __('Remarks')]) }}
									</div>
								</div>
								<div class="{{ VC::FM_GCB12 }} text-end">
									<input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
								</div>
							{!! Form::close() !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
	<script defer src="{{ asset('assets/js/routes/trainings/index.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/trainings/status.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/employees/showTraining.js') }}"></script>
@endpush
