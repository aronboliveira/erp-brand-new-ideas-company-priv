@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants as VW};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Route, Str};

	$lang = Utility::fetchUserLang();

	$formId = 'store_training_type_form';

	$createBase  = VW::TNG_TP;
	$createKebab = Str::kebab($createBase);
	$createName  = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
	$createUrl   = $createName ? route($createName) : '#';
	$guardMsg    = Utility::fetchLinkMessage($lang, VW::TNG_TP, 'store_training_type_route_unavailable') ?? 'Store training type route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
	'url'                  => $createUrl,
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $createUrl,
	'data-guard-msg'       => $guardMsg,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
				{{ Form::text('name', null, ['class' => VC::FM_CT]) }}
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
	</div>
    <script defer src="{{ asset('assets/js/routes/training/types/store.js') }}"></script>
{!! Form::close() !!}

