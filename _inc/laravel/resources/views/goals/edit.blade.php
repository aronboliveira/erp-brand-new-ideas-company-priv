@php
$lang ??= 'en';
	$hasGoal ??= false;
	$typesIsList ??= false;
	$typeOptions ??= [];
	$updateUrl ??= '#';
	$updateGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasGoal = !empty($goal ?? null) && data_get($goal, 'id');
		$typesIsList = (is_array($types ?? null) && count($types ?? []) > 0) || (($types ?? null) instanceof Collection && $types->isNotEmpty());
		$typeOptions = $typesIsList ? (is_array($types) ? $types : $types->toArray()) : [__('No types available')];
		$updateGuardMsg = Utility::fetchLinkMessage($lang, VW::GL, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in goals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in goals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in goals/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@can('edit goal')
    @php
        $updateBase ??= '';
        $updateKebab ??= '';
        $updateResolved ??= null;
        try {
            $updateBase = VW::GL . '.update';
            $updateKebab = Str::kebab($updateBase);
            $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
            $updateUrl = ($updateResolved && $hasGoal) ? (route($updateResolved, $goal->id) ?? '#') : '#';
        } catch (\Error $e) {
            Log::error('Error in goals/edit.blade.php @can edit @php block', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        } catch (\Exception $e) {
            Log::error('Exception in goals/edit.blade.php @can edit @php block', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Throwable in goals/edit.blade.php @can edit @php block', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
@endphp
@endcan

@if(!$hasGoal)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested goal was not found or is unavailable.') }}</div>
@else
    {{ Form::model($goal, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'goal-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required', 'placeholder' => __('Enter name or leave empty if unknown')]) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                    {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter amount')]) }}
                </div>
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                    {{ Form::select(
                        'type',
                        $typeOptions,
                        null,
                        array_merge(['class' => VC::FM_CT.' select', 'required' => 'required'], $typesIsList ? [] : ['disabled' => 'disabled'])
                    ) }}
                    @unless($typesIsList)
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No types available.') }}</div>
                    @endunless
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('from', __('From'), ['class' => 'form-label']) }}
                    {{ Form::date('from', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('to', __('To'), ['class' => 'form-label']) }}
                    {{ Form::date('to', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB12 }}">
                    @php
 $isDisplay = (int) data_get($goal ?? [], 'is_display', 0) === 1;
@endphp
                    <input class="form-check-input" type="checkbox" name="is_display" id="is_display" {{ $isDisplay ? 'checked' : '' }}>
                    <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="is_display">{{ __('Display On Dashboard') }}</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/goals/edit.js') }}"></script>
    {{ Form::close() }}
@endif
