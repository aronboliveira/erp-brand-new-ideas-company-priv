@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants as VW};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Route, Str};

	$lang = Utility::fetchUserLang();

	$formId = 'update_trainer_form';
	$branches = $branches ?? [];
	$trainerId = data_get($trainer ?? null, 'id', '');

	$updateBase   = VW::TNR . '.update';
	$updateKebab  = Str::kebab($updateBase);
	$updateName   = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
	$updateAction = ($updateName && $trainerId) ? route($updateName, [$trainerId]) : '#';
	$updateGuard  = Utility::fetchLinkMessage($lang, VW::TNR, 'update_trainer_route_unavailable') ?? 'Update trainer route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::model($trainer, [
	'url'                  => $updateAction,
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $updateAction,
	'data-guard-msg'       => $updateGuard,
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
		<input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
	</div>
    <script defer src="{{ asset('assets/js/routes/trainers/update.js') }}"></script>
{!! Form::close() !!}

