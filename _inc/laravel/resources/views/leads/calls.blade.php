@php
    try {
$lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();

        $leadOk = isset($lead) && !empty($lead);
        $callOk = isset($call) && !empty($call);

        $formId = 'ld-call-form';
        $aiLinkId = 'ld-ai-link';

        $storeBase = VW::LD . '.calls.store';
        $storeKebab = Str::kebab($storeBase);
        $storeName = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);

        $updateBase = VW::LD . '.calls.update';
        $updateKebab = Str::kebab($updateBase);
        $updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);

        $formUrl = '#';
        if ($leadOk) {
            if ($callOk && $updateName) $formUrl = route($updateName, [$lead->id, $call->id]);
            elseif (!$callOk && $storeName) $formUrl = route($storeName, [$lead->id]);
        }

        $formGuard = Utility::fetchLinkMessage($lang, VW::LD, 'lead_call_route_unavailable') ?? 'Lead Call route is unavailable. Please contact technical support or your domain administrator.';

        $settingsPlan = Utility::getChatGPTSettings();
        $gptFlag = null;
        if (is_object($settingsPlan) && isset($settingsPlan->{PlansConstants::COL_GPT})) $gptFlag = $settingsPlan->{PlansConstants::COL_GPT};
        elseif (is_array($settingsPlan) && array_key_exists(PlansConstants::COL_GPT, $settingsPlan)) $gptFlag = $settingsPlan[PlansConstants::COL_GPT];
        $gptEnabled = ((int)($gptFlag ?? 0) === 1);

        $hasUsers = isset($users) && ((is_array($users) && count($users) > 0) || (is_object($users) && method_exists($users,'isNotEmpty') && $users->isNotEmpty()));
        $assigneeOptions = [];
        if ($hasUsers) {
            $iter = is_array($users) ? $users : (method_exists($users,'all') ? $users->all() : []);
            foreach ($iter as $assignee) {
                $id = data_get($assignee, 'getLeadUser.id');
                $name = data_get($assignee, 'getLeadUser.name');
                if (!empty($id)) $assigneeOptions[$id] = $name ?: ('#' . $id);
            }
        }
        if (empty($assigneeOptions)) $assigneeOptions = ['' => __('No assignees available')];
        $assigneeDisabled = array_key_exists('', $assigneeOptions);
    } catch (\Throwable $e) {
        \Log::error('leads/calls — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($leadOk)
    {{ Form::open([
        'url' => $formUrl,
        'method' => $callOk ? 'PUT' : 'POST',
        'id' => $formId,
        'data-url' => $formUrl,
        'data-guard-msg' => $formGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            @if($gptEnabled)
                @php
                    $aiBase ??= 'generate';
                    try {
                        $aiKebab = Str::kebab($aiBase);
                        $aiName = Route::has($aiBase) ? $aiBase : (Route::has($aiKebab) ? $aiKebab : null);
                        $aiUrl = $aiName ? route($aiName, ['lead']) : '#';
                        $aiGuard = Utility::fetchLinkMessage($lang, VW::LD, 'ai_generate_unavailable') ?? 'AI content generation for leads is unavailable. Please contact technical support or your domain administrator.';
                    } catch (\Throwable $e) {
                        \Log::error('leads/calls — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <div class="{{ VC::TX_END }}">
                    <a
                        id="{{ $aiLinkId }}"
                        href="{{ $aiUrl }}"
                        data-url="{{ $aiUrl }}"
                        data-guard-msg="{{ base64_encode($aiGuard) }}"
                        data-sv-localized="true"
                        data-msg-key="ai_generate_unavailable"
                        class="{{ VC::BT_PRM }} btn-icon btn-sm"
                        data-ajax-popup-over="true"
                        data-size="md"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
            <div class="row">
                <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                    {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
                    {{ Form::text('subject', $callOk && isset($call->subject) ? $call->subject : null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                    {{ Form::label('call_type', __('Call Type'), ['class' => 'form-label']) }}
                    @php
 $ct = $callOk && isset($call->call_type) ? $call->call_type : null;
@endphp
                    <select name="call_type" id="call_type" class="{{ VC::FM_CT }}" required>
                        <option value="outbound" {{ $ct === 'outbound' ? 'selected' : '' }}>{{ __('Outbound') }}</option>
                        <option value="inbound" {{ $ct === 'inbound' ? 'selected' : '' }}>{{ __('Inbound') }}</option>
                    </select>
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
                    <small class="font-weight-bold">{{ __(' (Format h:m:s i.e 00:35:20 means 35 Minutes and 20 Sec)') }}</small>
                    {{ Form::time('duration', $callOk && isset($call->duration) ? $call->duration : null, ['class' => 'form-control', 'placeholder' => '00:35:20', 'step' => '2']) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('user_id', __('Assignee'), ['class' => 'form-label']) }}
                    {{ Form::select(
                        'user_id',
                        $assigneeOptions,
                        $callOk && isset($call->user_id) ? $call->user_id : null,
                        array_merge(
                            ['id' => 'user_id', 'class' => 'form-control', 'required' => 'required', 'data-placeholder' => __('Select Assignee')],
                            $assigneeDisabled ? ['disabled' => 'disabled'] : []
                        )
                    ) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                    {{ Form::textarea('description', $callOk && isset($call->description) ? $call->description : null, ['class' => 'form-control']) }}
                </div>
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('call_result', __('Call Result'), ['class' => 'form-label']) }}
                    {{ Form::textarea('call_result', $callOk && isset($call->call_result) ? $call->call_result : null, ['class' => 'summernote-simple', 'id' => 'summernote']) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            @if($callOk)
                <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
            @else
                <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
            @endif
        </div>
        <script async src="{{ asset('assets/js/routes/leads/lang/calls.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/calls.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No lead could be found') }}</div>
@endif
