@php
$lang ??= 'en';
	$plan ??= null;
	$awardId ??= null;
	$updateRoute ??= '#';
	$generateRoute ??= '#';
	$formId ??= 'award-update-form';
	$linkId ??= 'award-generate-link';
	$updateMsg ??= '';
	$generateMsg ??= '';
	$fields ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$plan = Utility::getChatGPTSettings();
		$awardId = data_get($award ?? null, 'id');
		$updateRoute = ($awardId && Route::has(ViewsConstants::AWD . '.update'))
			? (route(ViewsConstants::AWD . '.update', $awardId) ?? '#')
			: '#';
		$generateRoute = Route::has('generate')
			? (route('generate', [ViewsConstants::AWD]) ?? '#')
			: '#';
		$updateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::AWD,
			'award_update_route_unavailable'
		) ?? 'Award update route is unavailable. Please contact technical support or your domain administrator.';
		$generateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::AWD,
			'award_generate_route_unavailable'
		) ?? 'Award generate route is unavailable. Please contact technical support or your domain administrator.';
		$fields = [
			[
				'name' => 'employee_id',
				'type' => 'select',
				'label' => __('Employee'),
				'options' => $employees ?? [],
				'colClass' => 'col-md-6 col-lg-6',
				'attrs' => ['required' => 'required'],
			],
			[
				'name' => 'award_type',
				'type' => 'select',
				'label' => __('Award Type'),
				'options' => $awardtypes ?? [],
				'colClass' => 'col-md-6 col-lg-6',
				'attrs' => ['required' => 'required'],
			],
			[
				'name' => 'date',
				'type' => 'date',
				'label' => __('Date'),
				'colClass' => 'col-md-6 col-lg-6',
				'attrs' => [],
			],
			[
				'name' => 'gift',
				'type' => 'text',
				'label' => __('Gift'),
				'colClass' => 'col-md-6 col-lg-6',
				'attrs' => ['placeholder' => __('Enter Gift')],
			],
			[
				'name' => 'description',
				'type' => 'textarea',
				'label' => __('Description'),
				'colClass' => 'col-md-12',
				'attrs' => ['placeholder' => __('Enter Description')],
			],
		];
	} catch (\Error $e) {
		Log::error('Error in awards/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in awards/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in awards/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($award) && isset($award?->id))
    {{ Form::model($award, [
        'url'            => $updateRoute,
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $updateMsg,
    ]) }}
        <div class="{{ VC::RW }}">
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="{{ VC::FEND }} {{ VC::MB3 }}">
                    <a id="{{ $linkId }}"
                    href="{{ $generateRoute }}"
                    class="{{ VC::BT_SM_PM }} btn-icon"
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
                <div class="{{ VC::FM_G }} {{ $f['colClass'] }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    @php
                        try {
                            $attrs = ['class' => VC::FM_CT, 'required' => 'required'];
                            if (! empty($f['attrs']))
                                $attrs = array_merge($attrs, $f['attrs']);
                        } catch (\Throwable $e) {
                            \Log::error('awards/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    @if($f['type'] === 'select')
                        {{ Form::select($f['name'], $f['options'], null, $attrs + ['placeholder' => '']) }}
                    @elseif($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], null, $attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                    @endif
                </div>
            @endforeach
        </div>
        <div class="{{ VC::CD_POS }}">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer src="{{ asset('assets/js/routes/awards/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <p class="{{ VC::TXT_MT }}">{{ __('No award found.') }}</p>
        </div>
    </div>
@endif
