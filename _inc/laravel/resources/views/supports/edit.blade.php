@php
$user = Auth::user();
	$lang = Utility::fetchUserLang(user: $user);

	$support ??= null;
	$users ??= [];
	$priority ??= [];
	$status ??= [];

	$formId = 'edit-support-form';
	$supportId = data_get($support, 'id') ?? null;

	$updateBase = ViewsConstants::SPT . '.update';
	$updateResolved = null;
	$updateUrl = '#';
	$updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SPT, 'update_support_route_unavailable') ?? 'Update support route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has(Str::kebab($updateBase)) ? Str::kebab($updateBase) : null);
	} catch (\Error $e) {
		Log::error('Blade supports/edit: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade supports/edit: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade supports/edit: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade supports/edit: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$updateUrl = ($updateResolved && !empty($supportId)) ? route($updateResolved, $supportId) : '#';
	} catch (\Error $e) {
		Log::error('Blade supports/edit: route URL generation error: ' . $e->getMessage());
		$updateUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade supports/edit: invalid argument while generating URL: ' . $e->getMessage());
		$updateUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade supports/edit: general exception while generating URL: ' . $e->getMessage());
		$updateUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade supports/edit: throwable while generating URL: ' . $e->getMessage());
		$updateUrl = '#';
	}

	$plan = null;
	try {
		$plan = Utility::getChatGPTSettings();
	} catch (\Error $e) {
		Log::error('Blade supports/edit: error fetching ChatGPT settings: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade supports/edit: exception fetching ChatGPT settings: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade supports/edit: throwable fetching ChatGPT settings: ' . $e->getMessage());
	}

	$generateId = 'support-generate-ai';
	$generateBase = 'generate';
	$generateResolved = null;
	$generateUrl = '#';
	$generateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::SPT, 'generate_support_route_unavailable') ?? 'Generate support content route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$generateResolved = Route::has($generateBase) ? $generateBase : (Route::has(Str::kebab($generateBase)) ? Str::kebab($generateBase) : null);
	} catch (\Error $e) {
		Log::error('Blade supports/edit: route name resolution error for generate: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade supports/edit: invalid argument while resolving generate route: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade supports/edit: general exception while resolving generate route: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade supports/edit: throwable while resolving generate route: ' . $e->getMessage());
	}

	try {
		$generateUrl = $generateResolved ? route($generateResolved, ['support']) : '#';
	} catch (\Error $e) {
		Log::error('Blade supports/edit: URL generation error for generate: ' . $e->getMessage());
		$generateUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade supports/edit: invalid argument while generating generate URL: ' . $e->getMessage());
		$generateUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade supports/edit: general exception while generating generate URL: ' . $e->getMessage());
		$generateUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade supports/edit: throwable while generating generate URL: ' . $e->getMessage());
		$generateUrl = '#';
	}

	$attachmentUrl = null;
	try {
		$attachment = data_get($support, 'attachment');
		$attachmentUrl = $attachment ? asset(Storage::url('uploads/supports')) . '/' . $attachment : null;
	} catch (\Error $e) {
		Log::error('Blade supports/edit: error building attachment URL: ' . $e->getMessage());
		$attachmentUrl = null;
	} catch (\Exception $e) {
		Log::error('Blade supports/edit: exception building attachment URL: ' . $e->getMessage());
		$attachmentUrl = null;
	} catch (\Throwable $e) {
		Log::error('Blade supports/edit: throwable building attachment URL: ' . $e->getMessage());
		$attachmentUrl = null;
	}

	$subjectLabel = __('Subject') ?: __('No subject label available');
	$supportForUserLabel = __('Support for User') ?: __('No user label available');
	$priorityLabel = __('Priority') ?: __('No priority label available');
	$statusLabel = __('Status') ?: __('No status label available');
	$endDateLabel = __('End Date') ?: __('No date label available');
	$descLabel = __('Description') ?: __('No description label available');
	$attachLabel = __('Attachment') ?: __('No attachment label available');
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$updateLabel = __('Update') ?: __('Failed to get update label');
	$genContentLabel = __('Generate content with AI') ?: __('Failed to get AI label');
@endphp

{{ Form::model($support, [
	'url'                  => $updateUrl,
	'method'               => 'PUT',
	'id'                   => $formId,
	'enctype'              => 'multipart/form-data',
	'data-resolved-action' => $updateUrl,
	'data-guard-msg'       => $updateGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="modal-body">
		@if(data_get($plan, PlansConstants::COL_GPT) == 1)
			<div class="{{ VC::TX_END }}">
				<a id="{{ $generateId }}"
				   href="{{ $generateUrl }}"
				   data-size="md"
				   class="{{ VC::BT_SM_PM }} btn-icon"
				   data-ajax-popup-over="true"
				   data-url="{{ $generateUrl }}"
				   data-bs-placement="top"
				   data-title="{{ $genContentLabel }}"
				   data-guard-msg="{{ base64_encode($generateGuardMsg) }}"
				   data-sv-localized="true">
					<i class="{{ VC::FAS_RB }}"></i>
					<span>{{ $genContentLabel }}</span>
				</a>
			</div>
		@endif

		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label(SupportsConstants::COL_SBJ, $subjectLabel, ['class' => VC::FM_LB]) }}
				{{ Form::text(SupportsConstants::COL_SBJ, null, ['class' => VC::FM_CT, 'required' => true]) }}
			</div>

			@if(data_get($user, 'type') !== 'client')
				<div class="{{ VC::FM_GCB6 }}">
					{{ Form::label(SupportsConstants::COL_USR, $supportForUserLabel, ['class' => VC::FM_LB]) }}
					{{ Form::select(SupportsConstants::COL_USR, $users, null, ['class' => VC::FM_CT_SL]) }}
				</div>
			@endif

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label(ProjectsConstants::COL_PRT, $priorityLabel, ['class' => VC::FM_LB]) }}
				{{ Form::select(ProjectsConstants::COL_PRT, $priority, null, ['class' => VC::FM_CT_SL]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label(ActivitiesConstants::COL_TSK_STT, $statusLabel, ['class' => VC::FM_LB]) }}
				{{ Form::select(ActivitiesConstants::COL_TSK_STT, $status, null, ['class' => VC::FM_CT_SL]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label(ProjectsConstants::COL_E_DT, $endDateLabel, ['class' => VC::FM_LB]) }}
				{{ Form::date(ProjectsConstants::COL_E_DT, null, ['class' => VC::FM_CT, 'required' => true]) }}
			</div>
		</div>

		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label(ActivitiesConstants::COL_DESC, $descLabel, ['class' => VC::FM_LB]) }}
				{{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => VC::FM_CT, 'rows' => 3]) }}
			</div>
		</div>

		<div class="{{ VC::FM_GCB6 }}">
			{{ Form::label(SupportsConstants::COL_ATC, $attachLabel, ['class' => VC::FM_LB]) }}
			<label for="attachment" class="{{ VC::FM_LB }}">
				<input type="file" class="{{ VC::FM_CT }}" name="attachment" id="attachment" data-filename="attachment_create">
			</label>
			@if(!empty($attachmentUrl))
				<img id="image" class="{{ VC::MT2 }}" src="{{ $attachmentUrl }}" style="width:25%;" />
			@endif
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ $updateLabel }}" class="{{ VC::BT_PRM }}">
	</div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
	<script async src="{{ asset('assets/js/routes/supports/lang/attachmentEdit.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/supports/attachmentEdit.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/supports/edit.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/supports/generate.js') }}"></script>
@endpush
