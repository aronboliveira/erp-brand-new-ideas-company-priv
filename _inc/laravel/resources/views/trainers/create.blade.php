@php
$lang ??= 'en';
	$formId ??= 'store_trainer_form';
	$branches ??= [];
	$trainerCreateBase ??= '';
	$trainerCreateKebab ??= '';
	$trainerCreateName ??= null;
	$trainerCreateAction ??= '#';
	$trainerCreateGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$branches = $branches ?? [];
		$trainerCreateBase = VW::TNR;
		$trainerCreateKebab = Str::kebab($trainerCreateBase);
		$trainerCreateName = Route::has($trainerCreateBase)
			? $trainerCreateBase
			: (Route::has($trainerCreateKebab) ? $trainerCreateKebab : null);
		$trainerCreateAction = $trainerCreateName ? (route($trainerCreateName) ?? '#') : '#';
		$trainerCreateGuard = Utility::fetchLinkMessage($lang, VW::TNR, 'store_trainer_route_unavailable')
			?? 'Store trainer route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in trainers/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in trainers/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in trainers/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::open([
	'url'                  => $trainerCreateAction,
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $trainerCreateAction,
	'data-guard-msg'       => $trainerCreateGuard,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
				{{ Form::select('branch', $branches, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('firstname', __('First Name'), ['class' => VC::FM_LB]) }}
				{{ Form::text('firstname', null, ['class' => VC::FM_CT, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('lastname', __('Last Name'), ['class' => VC::FM_LB]) }}
				{{ Form::text('lastname', null, ['class' => VC::FM_CT, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('contact', __('Contact'), ['class' => VC::FM_LB]) }}
				{{ Form::text('contact', null, ['class' => VC::FM_CT, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('email', __('Email'), ['class' => VC::FM_LB]) }}
				{{ Form::text('email', null, ['class' => VC::FM_CT, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('expertise', __('Expertise'), ['class' => VC::FM_LB]) }}
				{{ Form::textarea('expertise', null, ['class' => VC::FM_CT, 'placeholder' => __('Expertise')]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('address', __('Address'), ['class' => VC::FM_LB]) }}
				{{ Form::textarea('address', null, ['class' => VC::FM_CT, 'placeholder' => __('Address')]) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
	</div>
    <script defer src="{{ asset('assets/js/routes/trainers/store.js') }}"></script>
{!! Form::close() !!}
