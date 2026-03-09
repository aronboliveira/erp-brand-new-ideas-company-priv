@php
$employees ??= [];
	$awardtypes ??= [];
	$lang ??= '';
	$plan ??= null;
	$awardStoreRoute ??= '#';
	$generateRoute ??= '#';
	$formId ??= 'award-store-form';
	$linkId ??= 'award-generate-link';
	$storeMsg ??= '';
	$generateMsg ??= '';
	$fields ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$plan = Utility::getChatGPTSettings();
		$awardStoreRoute = Route::has(VW::AWD . '.store')
			? (route(VW::AWD . '.store') ?? '#')
			: '#';
		$generateRoute = Route::has('generate')
			? (route('generate', [VW::AWD]) ?? '#')
			: '#';
		$storeMsg = Utility::fetchLinkMessage(
			$lang,
			VW::AWD,
			'award_store_route_unavailable'
		) ?? 'Award store route is unavailable. Please contact technical support or your domain administrator.';
		$generateMsg = Utility::fetchLinkMessage(
			$lang,
			VW::AWD,
			'award_generate_route_unavailable'
		) ?? 'Award generate route is unavailable. Please contact technical support or your domain administrator.';
		$fields = [
			[
				'name'     => 'employee_id',
				'type'     => 'select',
				'label'    => __('Employee'),
				'options'  => is_array($employees) ? $employees : (method_exists($employees, 'toArray') ? $employees->toArray() : []),
				'colClass' => 'col-md-6 col-lg-6',
				'attrs'    => ['required' => 'required'],
			],
			[
				'name'     => 'award_type',
				'type'     => 'select',
				'label'    => __('Award Type'),
				'options'  => is_array($awardtypes) ? $awardtypes : (method_exists($awardtypes, 'toArray') ? $awardtypes->toArray() : []),
				'colClass' => 'col-md-6 col-lg-6',
				'attrs'    => ['required' => 'required'],
			],
			[
				'name'     => 'date',
				'type'     => 'date',
				'label'    => __('Date'),
				'colClass' => 'col-md-6 col-lg-6',
				'attrs'    => [],
			],
			[
				'name'     => 'gift',
				'type'     => 'text',
				'label'    => __('Gift'),
				'colClass' => 'col-md-6 col-lg-6',
				'attrs'    => ['placeholder' => __('Enter Gift')],
			],
			[
				'name'     => 'description',
				'type'     => 'textarea',
				'label'    => __('Description'),
				'colClass' => 'col-md-12',
				'attrs'    => ['placeholder' => __('Enter Description')],
			],
		];
	} catch (\Error $e) {
		Log::error('Error in awards/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in awards/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in awards/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open([
	'url'            => $awardStoreRoute,
	'method'         => 'post',
	'id'             => $formId,
	'data-url'       => $awardStoreRoute,
	'data-guard-msg' => $storeMsg,
]) }}
	<div class="{{ VC::RW }}">
		@if(!empty($plan) && ($plan?->{PLC::COL_GPT} ?? 0) == 1)
			<div class="{{ VC::FEND }} {{ VC::MB3 }}">
				<a id="{{ $linkId }}"
				   href="{{ $generateRoute }}"
				   class="{{ VC::BT_SM_PM }} {{ VC::DFL_IL }} btn-icon"
				   data-ajax-popup-over="true"
				   data-size="md"
				   data-url="{{ $generateRoute }}"
				   data-guard-msg="{{ base64_encode($generateMsg) }}"
				   title="{{ __('Generate content with AI') }}">
					<i class="{{ VC::FAS_RB }}"></i> {{ __('Generate with AI') }}
				</a>
			</div>
		@endif
		@foreach($fields as $f)
			@php
				$fName ??= '';
				$fType ??= 'text';
				$fLabel ??= '';
				$fOptions ??= [];
				$fColClass ??= '';
				$fAttrs ??= [];
				try {
					$fName = is_string($f['name'] ?? null) ? $f['name'] : '';
					$fType = is_string($f['type'] ?? null) ? $f['type'] : 'text';
					$fLabel = is_string($f['label'] ?? null) ? $f['label'] : '';
					$fOptions = is_array($f['options'] ?? null) ? $f['options'] : [];
					$fColClass = is_string($f['colClass'] ?? null) ? $f['colClass'] : '';
					$fAttrs = is_array($f['attrs'] ?? null) ? $f['attrs'] : [];
				} catch (\Throwable $e) {
					Log::error('Error processing field in awards/create.blade.php', [
						'exception_class' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
@endphp
			<div class="{{ VC::FM_G }} {{ $fColClass }}">
				{{ Form::label($fName, $fLabel, ['class' => VC::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
				@php
					try {
					    $attrs = ['class' => VC::FM_CT, 'required' => 'required'];
					    if (!empty($fAttrs)) {
					    	$attrs = array_merge($attrs, $fAttrs);
					    }
					} catch (\Throwable $e) {
					    \Log::error('awards/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
					}
@endphp
				@if($fType === 'select')
					{{ Form::select($fName, $fOptions, null, $attrs + ['placeholder' => '']) }}
				@elseif($fType === 'textarea')
					{{ Form::textarea($fName, null, $attrs) }}
				@else
					{{ Form::{$fType}($fName, null, $attrs) }}
				@endif
			</div>
		@endforeach
	</div>
	<div class="{{ VC::CD_POS }}">
		<button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
		<button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
	</div>
	<script defer src="{{ asset('assets/js/routes/awards/store.js') }}"></script>
{{ Form::close() }}
