@php
$lang ??= 'en';
	$hasModel ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$generateResolved ??= null;
	$generateUrl ??= '#';
	$generateGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasModel = !empty($plan ?? null) && data_get($plan, 'id');
		$updateBase = VW::PLN . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasModel) ? (route($updateResolved, data_get($plan ?? null, 'id')) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::PLN, 'update_route_unavailable') ?? __('Update Plan route is unavailable. Please contact technical support or your domain administrator.');
		$generateResolved = Route::has('generate') ? 'generate' : null;
		$generateUrl = $generateResolved ? (route('generate', ['plan']) ?? '#') : '#';
		$generateGuard = Utility::fetchLinkMessage($lang, VW::PLN, 'generate_route_unavailable') ?? __('Generate content route for Plan is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in plans/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in plans/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in plans/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if ($hasModel)
    {{ Form::model($plan, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'enctype'           => 'multipart/form-data',
        'id'                => 'plan-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @php($settings = Utility::settings())
            @if(!empty($settings['chat_gpt_key']))
                <div class="{{ VC::TX_END }}">
                    <a href="#"
                       id="generate-plan-btn"
                       data-size="md"
                       class="{{ VC::BT_SM_PM }} btn-icon"
                       data-ajax-popup-over="true"
                       data-url="{{ $generateUrl }}"
                       data-bs-placement="top"
                       data-title="{{ __('Generate content with AI') }}"
                       data-guard-msg="{{ base64_encode($generateGuard) }}"
                       data-sv-localized="true">
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif

            <div class="row">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('name', __('Name'), ['class'=> VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class'=> VC::FM_CT . ' font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required']) }}
                </div>

                @if(($plan->price ?? 0) > 0)
                    <div class="{{ VC::FM_GCB6 }}">
                        {{ Form::label('price', __('Price'), ['class'=> VC::FM_LB]) }}
                        {{ Form::number('price', null, ['class'=> VC::FM_CT,'placeholder'=>__('Enter Plan Price'),'required'=>'required']) }}
                    </div>
                @endif

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('duration', __('Duration'), ['class'=> VC::FM_LB]) }}
                    {!! Form::select('duration', $arrDuration ?? [], null, ['class' => VC::FM_CT_SL,'required'=>'required']) !!}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('max_users', __('Maximum Users'), ['class'=> VC::FM_LB]) }}
                    {{ Form::number('max_users', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                    <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('max_customers', __('Maximum Customers'), ['class'=> VC::FM_LB]) }}
                    {{ Form::number('max_customers', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                    <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('max_vendors', __('Maximum Vendors'), ['class'=> VC::FM_LB]) }}
                    {{ Form::number('max_vendors', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                    <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('max_clients', __('Maximum Clients'), ['class'=> VC::FM_LB]) }}
                    {{ Form::number('max_clients', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                    <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('storage_limit', __('Storage limit'), ['class'=> VC::FM_LB]) }}
                    <div class="input-group">
                        {{ Form::number('storage_limit', null, ['class'=> VC::FM_CT,'required'=>'required']) }}
                        <div class="input-group-append">
                            <span class="{{ VC::INP_GP_TXT }}" id="basic-addon2">{{ __('MB') }}</span>
                        </div>
                    </div>
                    <span class="small">{{ __('Note: upload size ( In MB)') }}</span>
                </div>

                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('description', __('Description'), ['class'=> VC::FM_LB]) }}
                    {!! Form::textarea('description', null, ['class'=> VC::FM_CT,'rows'=>'2']) !!}
                </div>

                <div class="{{ VC::FM_GCB3 }}">
                    <div class="{{ VC::FM_CHK }} form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_crm" id="enable_crm" {{ ($plan['crm'] ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_crm">{{ __('CRM') }}</label>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB3 }}">
                    <div class="{{ VC::FM_CHK }} form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_project" id="enable_project" {{ ($plan['project'] ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_project">{{ __('Project') }}</label>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB3 }}">
                    <div class="{{ VC::FM_CHK }} form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_hrm" id="enable_hrm" {{ ($plan['hrm'] ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_hrm">{{ __('HRM') }}</label>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB3 }}">
                    <div class="{{ VC::FM_CHK }} form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_account" id="enable_account" {{ ($plan['account'] ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_account">{{ __('Account') }}</label>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB3 }}">
                    <div class="{{ VC::FM_CHK }} form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_pos" id="enable_pos" {{ ($plan['pos'] ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_pos">{{ __('POS') }}</label>
                    </div>
                </div>

                <div class="{{ VC::FM_GCB3 }}">
                    <div class="{{ VC::FM_CHK }} form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_chatgpt" id="enable_chatgpt" {{ ($plan['chatgpt'] ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="enable_chatgpt">{{ __('Chat GPT') }}</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}

    <script defer src="{{ asset('assets/js/routes/plans/update.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/plans/generateEdit.js') }}"></script>
@else
    <div>{{ __('No plan could be found.') }}</div>
@endif
