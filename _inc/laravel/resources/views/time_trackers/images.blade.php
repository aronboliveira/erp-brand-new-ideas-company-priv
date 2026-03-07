@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants as VW};
	use App\Models\Utility;
	use Illuminate\Support\{Facades\Route, Facades\Storage, Str};

	$lang = Utility::fetchUserLang();
@endphp

<div class="modal-header pb-2 pt-2">
	<h5 class="modal-title" id="exampleModalLongTitle">
		{{ $tracker->project_task ?? __('Failed to get project task for tracker') }}
		<small>( {{ $tracker->total ?? __('No total available') }}, {{ isset($tracker->start_time) ? date('d M', strtotime($tracker->start_time)) : __('No date available') }} )</small>
	</h5>
	<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
</div>

<div class="modal-body p-1">
	<div class="row">
		<div class="col-lg-12 product-left mb-5 mb-lg-0">
			@if(($images->count() ?? 0) > 0)
				<div class="swiper-container product-slider mb-2 pb-2" style="border-bottom:solid 2px #f2f3f5">
					<div class="swiper-wrapper">
						@foreach ($images as $image)
							@php
								$delBase = VW::TMT . '.images.destroy';
								$delKebab = Str::kebab($delBase);
								$deleteResolved = Route::has($delBase) ? $delBase : (Route::has($delKebab) ? $delKebab : null);
								$deleteUrl = $deleteResolved ? route($deleteResolved, [$image->id]) : '#';
								$delGuard = Utility::fetchLinkMessage($lang, VW::TMT, 'route_delete_tracker_image_unavailable') ?? 'Delete tracker image route is unavailable. Please contact technical support or your domain administrator.';
								$linkId = 'tracker-image-delete-link-' . $image->id;
							@endphp
							<div class="swiper-slide" id="slide-{{ $image->id }}">
								<img src="{{ asset(Storage::url($image->img_path)) }}" alt="..." class="img-fluid">
								<div class="time_in_slider">
									{{ isset($image->time) ? date('H:i:s, d M ', strtotime($image->time)) : __('No time available') }} |
									<a href="{{ $deleteUrl }}"
									   id="{{ $linkId }}"
									   class="{{ VC::BT_SM_CT_PR }} {{ VC::ACT_BTN_DNG_2 }}"
									   data-url="{{ $deleteUrl }}"
									   data-guard-msg="{{ $delGuard }}"
									   data-sv-localized="true"
									   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
									   data-confirm-yes="removeImage({{ $image->id }})">
										<i class="{{ VC::TI_TRS_WT }}"></i>
									</a>
								</div>
							</div>

							@push(StacksConstants::ADM_SCR_PG)
								<script defer>
									(() => {
										try {
											const a = document.getElementById('{{ $linkId }}');
											if (!a) return;
											if (a.getAttribute('data-listener-active') === 'true') return;
											a.setAttribute('data-listener-active', 'true');

											const href = a.getAttribute('href') || '#';
											const url = a.getAttribute('data-url') || href || '#';
											if (a.hasAttribute('href') && (href === '#' || !href) && url !== '#') {
												a.setAttribute('href', url);
											}

											a.addEventListener('click', (e) => {
												try {
													const h = a.getAttribute('href') || '#';
													if (h && h !== '#') return;
													e.preventDefault();

													const msgAttr = a.getAttribute('data-guard-msg') || '';
													const msg = msgAttr.trim().length ? msgAttr : 'Delete tracker image route is unavailable. Please contact technical support or your domain administrator.';

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

														try {
															window.bootstrap.Toast.getOrCreateInstance(toast).show();
														} catch {
															alert(msg);
														}
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
					</div>
					<div class="swiper-button-next"></div>
					<div class="swiper-button-prev"></div>
				</div>

				<div class="swiper-container product-thumbs">
					<div class="swiper-wrapper">
						@foreach ($images as $image)
							<div class="swiper-slide" id="slide-thum-{{ $image->id }}">
								<img src="{{ asset(Storage::url($image->img_path)) }}" alt="..." class="img-fluid">
							</div>
						@endforeach
					</div>
				</div>
			@else
				<div class="no-image">
					<h5 class="text-muted">{{ __('Images Not Available.') }}</h5>
				</div>
			@endif
		</div>
	</div>
    <script async src="{{ asset('assets/js/routes/timeTrackers/lang/confirm.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/timeTrackers/confirm.js') }}"></script>
</div>
