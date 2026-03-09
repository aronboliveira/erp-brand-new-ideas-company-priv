@php
$lang ??= 'en';
	$storeBase ??= '';
	$storeResolved ??= null;
	$storeUrl ??= '#';
	$storeGuard ??= '';
	$genResolved ??= null;
	$genUrl ??= '#';
	$genGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeBase = VW::TRV;
		$storeResolved = Route::has($storeBase) ? $storeBase : (Route::has(Str::kebab($storeBase)) ? Str::kebab($storeBase) : null);
		$storeUrl = $storeResolved ? (route($storeResolved) ?? url(VW::TRV)) : url(VW::TRV);
		$storeGuard = Utility::fetchLinkMessage($lang, VW::TRV, 'store_travel_route_unavailable')
			?? 'Store travel route is unavailable. Please contact technical support or your domain administrator.';
		$genResolved = Route::has('generate') ? 'generate' : (Route::has(Str::kebab('generate')) ? Str::kebab('generate') : null);
		$genUrl = $genResolved ? (route($genResolved, ['travel']) ?? '#') : '#';
		$genGuard = Utility::fetchLinkMessage($lang, VW::TRV, 'generate_route_unavailable')
			?? 'Generate content route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in travels/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in travels/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in travels/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::open([
    'url'  => $storeUrl,
    'method' => 'post',
    'id'     => 'create_travel',
    'data-guard-msg' => $storeGuard,
    'data-sv-localized' => 'true',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::TX_END }}">
                <a href="{{ $genUrl }}"
                   id="travel-generate-link"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ $genUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}"
                   data-guard-msg="{{ base64_encode($genGuard) }}"
                   data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('purpose_of_visit', __('Purpose of Trip'), ['class' => VC::FM_LB]) }}
                {{ Form::text('purpose_of_visit', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('place_of_visit', __('Country'), ['class' => VC::FM_LB]) }}
                {{ Form::text('place_of_visit', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Store') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/travels/store.js') }}"></script>
    @if($plan?->{PlansConstants::COL_GPT} == 1)
        <script defer src="{{ asset('assets/js/routes/travels/generate.js') }}"></script>
    @endif
{!! Form::close() !!}
