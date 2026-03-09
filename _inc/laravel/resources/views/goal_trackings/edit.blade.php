@php
$lang ??= 'en';
	$hasGoal ??= false;
	$branchesIsList ??= false;
	$goalTypesIsList ??= false;
	$statusIsList ??= false;
	$branchOptions ??= [];
	$goalTypeOptions ??= [];
	$statusOptions ??= [];
	$rating ??= 0;
	$progressVal ??= 0;
	$plan ??= null;
	$aiAllowed ??= false;
	$genBase ??= 'generate';
	$genKebab ??= '';
	$genResolved ??= null;
	$genUrl ??= '#';
	$genGuardMsg ??= '';
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuardMsg ??= '';
	$starLabels ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasGoal = !empty($goalTracking ?? null) && data_get($goalTracking, 'id');
		$branchesIsList = (is_array($branches ?? null) && count($branches ?? []) > 0) || (($branches ?? null) instanceof Collection && $branches->isNotEmpty());
		$goalTypesIsList = (is_array($goalTypes ?? null) && count($goalTypes ?? []) > 0) || (($goalTypes ?? null) instanceof Collection && $goalTypes->isNotEmpty());
		$statusIsList = (is_array($status ?? null) && count($status ?? []) > 0) || (($status ?? null) instanceof Collection && $status->isNotEmpty());
		$branchOptions = $branchesIsList ? (is_array($branches) ? $branches : $branches->toArray()) : [__('No branches available')];
		$goalTypeOptions = $goalTypesIsList ? (is_array($goalTypes) ? $goalTypes : $goalTypes->toArray()) : [__('No goal types available')];
		$statusOptions = $statusIsList ? (is_array($status) ? $status : $status->toArray()) : [__('No statuses available')];
		$rating = (int) data_get($goalTracking ?? [], 'rating', 0);
		$progressVal = (int) data_get($goalTracking ?? [], 'progress', 0);
		$plan = Utility::getChatGPTSettings();
		$aiAllowed = (data_get($plan, PL::COL_GPT, 0) == 1);
		$genBase = 'generate';
		$genKebab = Str::kebab($genBase);
		$genResolved = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
		$genUrl = $genResolved ? (route($genResolved, ['goal tracking']) ?? '#') : '#';
		$genGuardMsg = Utility::fetchLinkMessage($lang, VW::GL_TRC, 'ai_generate_route_unavailable') ?? __('Generate content route for goal trackings is unavailable. Please contact technical support or your domain administrator.');
		$updateBase = VW::GL_TRC . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasGoal) ? (route($updateResolved, $goalTracking->id) ?? '#') : '#';
		$updateGuardMsg = Utility::fetchLinkMessage($lang, VW::GL_TRC, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
		$starLabels = [
			5 => __('Excellent – 5 stars'),
			4 => __('Very good – 4 stars'),
			3 => __('Good – 3 stars'),
			2 => __('Fair – 2 stars'),
			1 => __('Poor – 1 star'),
		];
	} catch (\Error $e) {
		Log::error('Error in goal_trackings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in goal_trackings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in goal_trackings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!$hasGoal)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested goal tracking entry was not found or is unavailable.') }}</div>
@else
    {{ Form::model($goalTracking, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'goal-tracking-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @if($aiAllowed)
                <div class="{{ VC::TX_END }}">
                    <a
                        id="goal-ai-generate-btn"
                        href="{{ $genUrl }}"
                        data-size="md"
                        class="{{ VC::BT_PRM }} btn-icon btn-sm"
                        data-ajax-popup-over="true"
                        data-url="{{ $genUrl }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                        data-guard-msg="{{ base64_encode($genGuardMsg) }}"
                        data-sv-localized="true"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif

            <div class="row">
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                        {{ Form::select(
                            'branch',
                            $branchOptions,
                            null,
                            array_merge(['class' => VC::FM_CT.' select', 'required' => 'required'], $branchesIsList ? [] : ['disabled' => 'disabled'])
                        ) }}
                        @unless($branchesIsList)
                            <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No branches available.') }}</div>
                        @endunless
                    </div>
                </div>

                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('goal_type', __('GoalTypes'), ['class' => 'form-label']) }}
                        {{ Form::select(
                            'goal_type',
                            $goalTypeOptions,
                            null,
                            array_merge(['class' => VC::FM_CT.' select', 'required' => 'required'], $goalTypesIsList ? [] : ['disabled' => 'disabled'])
                        ) }}
                        @unless($goalTypesIsList)
                            <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No goal types available.') }}</div>
                        @endunless
                    </div>
                </div>

                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                        {{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
                    </div>
                </div>

                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                        {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
                    </div>
                </div>

                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
                        {{ Form::text('subject', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter a subject or leave empty if unknown')]) }}
                    </div>
                </div>

                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('target_achievement', __('Target Achievement'), ['class' => 'form-label']) }}
                        {{ Form::text('target_achievement', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter a target achievement')]) }}
                    </div>
                </div>

                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                        {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter a description')]) }}
                    </div>
                </div>

                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                        {{ Form::select(
                            'status',
                            $statusOptions,
                            null,
                            array_merge(['class' => VC::FM_CT.' select'], $statusIsList ? [] : ['disabled' => 'disabled'])
                        ) }}
                        @unless($statusIsList)
                            <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No statuses available.') }}</div>
                        @endunless
                    </div>
                </div>

                <div class="{{ VC::CM12 }}">
                    <fieldset id="demo1" class="rating">
                        @foreach([5,4,3,2,1] as $value)
                            <input class="stars" type="radio" id="rating-{{ $value }}" name="rating" value="{{ $value }}" {{ $rating === $value ? 'checked' : '' }}>
                            <label class="full" for="rating-{{ $value }}" title="{{ $starLabels[$value] }}"></label>
                        @endforeach
                    </fieldset>
                </div>

                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        <input
                            type="range"
                            class="slider {{ VC::W100 }} {{ VC::MB0 }}"
                            name="progress"
                            id="goal-progress-range"
                            value="{{ $progressVal }}"
                            min="1"
                            max="100"
                            aria-label="{{ __('Progress') }}"
                        >
                        <output name="progressOutputName" id="goal-progress-output">{{ $progressVal }}</output>
                        %
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/goals/trackings/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/goals/trackings/generateEdit.js') }}"></script>
    {{ Form::close() }}
@endif
