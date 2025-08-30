@php
	use App\Config\Constants\{PlansConstants, StacksConstants, ViewClassNamesConstants as VC, ViewsConstants as VW};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Route, Str};

	$lang = Utility::fetchUserLang();

	$formId = 'create_training';

	$storeBase = VW::TNG;
	$storeKebab = Str::kebab($storeBase);
	$storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
	$storeActionUrl = $storeResolved ? route($storeResolved) : '#';
	$storeGuardMsg = Utility::fetchLinkMessage($lang, VW::TNG, 'store_training_route_unavailable') ?? 'Store training route is unavailable. Please contact technical support or your domain administrator.';

	$genBase = 'generate';
	$genResolved = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
	$genUrl = $genResolved ? route($genResolved, ['training']) : '#';
	$genGuardMsg = Utility::fetchLinkMessage($lang, VW::TNG, 'store_training_generate_unavailable') ?? 'Store training generate route is unavailable. Please contact technical support or your domain administrator.';
	$genId = 'training-generate-link';
@endphp

{!! Form::open([
	'url'                  => $storeActionUrl,
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $storeActionUrl,
	'data-guard-msg'       => $storeGuardMsg,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		@php($plan = Utility::getChatGPTSettings())
		@if($plan?->{PlansConstants::COL_GPT} == 1)
			<div class="text-end">
				<a href="{{ $genUrl }}"
				   id="{{ $genId }}"
				   data-size="md"
				   class="{{ VC::BT_SM_PM }} btn-icon"
				   data-ajax-popup-over="true"
				   data-url="{{ $genUrl }}"
				   data-bs-placement="top"
				   data-title="{{ __('Generate content with AI') }}"
				   data-guard-msg="{{ $genGuardMsg }}"
				   data-sv-localized="true">
					<i class="{{ VC::FAS_RB }}"></i>
					<span>{{ __('Generate with AI') }}</span>
				</a>
			</div>
            <script defer src="{{ asset('assets/js/routes/trainings/generate.js') }}"></script>
		@endif

		<div class="row">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
				{{ Form::select('branch', $branches ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('trainer_option', __('Trainer Option'), ['class' => VC::FM_LB]) }}
				{{ Form::select('trainer_option', $options ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('training_type', __('Training Type'), ['class' => VC::FM_LB]) }}
				{{ Form::select('training_type', $trainingTypes ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('trainer', __('Trainer'), ['class' => VC::FM_LB]) }}
				{{ Form::select('trainer', $trainers ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('training_cost', __('Training Cost'), ['class' => VC::FM_LB]) }}
				{{ Form::number('training_cost', null, ['class' => VC::FM_CT, 'step' => '0.01', 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('employee', __('Employee'), ['class' => VC::FM_LB]) }}
				{{ Form::select('employee', $employees ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
				{{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Description')]) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
	</div>
    <script defer src="{{ asset('assets/js/routes/trainings/store.js') }}"></script>
{!! Form::close() !!}

