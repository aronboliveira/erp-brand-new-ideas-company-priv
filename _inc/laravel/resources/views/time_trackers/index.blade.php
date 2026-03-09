@php
	try {
$lang = Utility::fetchUserLang();

		$dashResolved = Route::has('dashboard') ? 'dashboard' : (Route::has(Str::kebab('dashboard')) ? Str::kebab('dashboard') : null);
		$dashUrl = $dashResolved ? route($dashResolved) : '#';
		$dashGuardMsg = Utility::fetchLinkMessage($lang, VW::TMT, 'dashboard_route_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';

		$viewBase = VW::TMT . '.images.index';
		$viewResolvedName = Route::has($viewBase) ? $viewBase : (Route::has(Str::kebab($viewBase)) ? Str::kebab($viewBase) : null);

		$destroyBase = VW::TMT . '.destroy';
		$destroyResolvedName = Route::has($destroyBase) ? $destroyBase : (Route::has(Str::kebab($destroyBase)) ? Str::kebab($destroyBase) : null);
	} catch (\Throwable $e) {
		\Log::error('time_trackers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Tracker') }}
@endsection

@section(YieldingConstants::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a id="dashboard-link"
		   href="{{ $dashUrl }}"
		   data-url="{{ $dashUrl }}"
		   data-guard-msg="{{ base64_encode($dashGuardMsg) }}"
		   data-sv-localized="true"
		   {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">{{ __('Tracker') }}</li>
@endsection

@push(StacksConstants::ADM_CSS)
	<link rel="stylesheet" href="{{ url('css/swiper.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/routes/timeTrackers/index.css') }}">
@endpush

@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="{{ VC::C12 }}">
			<div class="card">
				<div class="{{ VC::CD_BD_TB_BD }} {{ VC::MT2 }}">
					<div class="{{ VC::TB_RSP }}">
						<table class="table datatable">
							<thead>
								<tr>
									<th>{{ __('Title') }}</th>
									<th>{{ __('Task') }}</th>
									<th>{{ __('Project') }}</th>
									<th>{{ __('Start Time') }}</th>
									<th>{{ __('End Time') }}</th>
									<th>{{ __('Total Time') }}</th>
									<th>{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach(($trackers ?? []) as $tracker)
									@php
										try {
										    $total_name = \App\Models\Utility::secondToTime($tracker->total_time ?? 0) ?? __('No total available');

										    $viewUrl = ($viewResolvedName && ($tracker->id ?? null)) ? route($viewResolvedName, [$tracker->id]) : '#';
										    $viewGuardMsg = Utility::fetchLinkMessage($lang, VW::TMT, 'route_view_tracker_images_unavailable') ?? 'View tracker images route is unavailable. Please contact technical support or your domain administrator.';

										    $destroyUrl = ($destroyResolvedName && ($tracker->id ?? null)) ? route($destroyResolvedName, [$tracker->id]) : '#';
										    $destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::TMT, 'route_delete_tracker_unavailable') ?? 'Delete tracker route is unavailable. Please contact technical support or your domain administrator.';
										    $formId = 'delete-form-' . ($tracker->id ?? 'unknown');
										    $imgId = 'track-images-' . ($tracker->id ?? 'unknown');
										} catch (\Throwable $e) {
										    \Log::error('time_trackers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
										}
@endphp
									<tr>
										<td>{{ $tracker->name ?? __('No title available') }}</td>
										<td>{{ $tracker->project_task ?? __('No task available') }}</td>
										<td>{{ $tracker->project_name ?? __('No project available') }}</td>
										<td>{{ isset($tracker->start_time) ? date('H:i:s', strtotime($tracker->start_time)) : __('No start time') }}</td>
										<td>{{ isset($tracker->end_time) ? date('H:i:s', strtotime($tracker->end_time)) : __('No end time') }}</td>
										<td>{{ $total_name }}</td>
										<td>
											<img alt="Image placeholder"
												 src="{{ asset('assets/images/gallery.png') }}"
												 class="{{ VC::AV_CC_SM }} view-images"
												 data-bs-toggle="tooltip"
												 title="{{ __('View Screenshot images') }}"
												 data-original-title="{{ __('View Screenshot images') }}"
												 style="height:25px;width:24px;margin-right:10px;cursor:pointer;"
												 data-id="{{ $tracker->id ?? '' }}"
												 id="{{ $imgId }}"
												 data-url="{{ $viewUrl }}"
												 data-guard-msg="{{ base64_encode($viewGuardMsg) }}"
												 data-sv-localized="true">

											<div class="{{ VC::ACT_BTN_DNG_2 }}">
												{!! Form::open([
													'method'               => 'DELETE',
													'url'                  => $destroyUrl,
													'id'                   => $formId,
													'data-resolved-action' => $destroyUrl,
													'data-guard-msg'       => $destroyGuardMsg,
													'data-sv-localized'    => 'true',
												]) !!}
													<a href="#"
													   class="{{ VC::BT_SM_CT_PR }}"
													   data-bs-toggle="tooltip"
													   title="{{ __('Delete') }}"
													   data-original-title="{{ __('Delete') }}"
													   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }} | {{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
													   data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
														<i class="{{ VC::TI_TRS_WT }}"></i>
													</a>
												{!! Form::close() !!}
											</div>
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

	<div class="{{ VC::MD_FD }}" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="{{ VC::MDL_DLG }} modal-dialog-centered modal-lg ss_modale" role="document">
			<div class="{{ VC::MDL_CTT }} image_sider_div"></div>
		</div>
	</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
  <script src="{{url('js/swiper.min.js')}}"></script>
  <script async src="{{ asset('assets/js/routes/timeTrackers/lang/images.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/timeTrackers/viewImages.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/timeTrackers/destroy.js') }}"></script>
  <script defer src="{{ asset('assets/js/routes/timeTrackers/images.js') }}"></script>
@endpush
