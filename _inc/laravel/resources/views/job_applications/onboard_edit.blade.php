@php
    try {
$lang      = Utility::fetchUserLang();
        $hasModel  = !empty($jobOnBoard ?? null) && data_get($jobOnBoard, 'id');

        $updateBase     = VW::JB . '.on.board.update';
        $updateKebab    = Str::kebab($updateBase);
        $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
        $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $jobOnBoard->id) : '#';
        $updateGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'on_board_update_route_unavailable')
                            ?? __('Update Job On Board route is unavailable. Please contact technical support or your domain administrator.');
    } catch (\Throwable $e) {
        \Log::error('job_applications/onboard_edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($hasModel)
    {{ Form::model($jobOnBoard, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'jobOnBoard-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('joining_date', __('Joining Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('joining_date', null, ['class' => VC::FM_CT . ' d_week','autocomplete'=>'off']) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('days_of_week', __('Days Of Week'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('days_of_week', null, ['class' => VC::FM_CT,'autocomplete'=>'off']) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('salary', __('Salary'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('salary', null, ['class' => VC::FM_CT,'autocomplete'=>'off']) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('salary_type', __('Salary Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('salary_type', $salary_type ?? [], null, ['class' => VC::FM_CT_SL]) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('salary_duration', __('Salary Duration'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('salary_duration', $salary_duration ?? [], null, ['class' => VC::FM_CT_SL]) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('job_type', __('Job Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('job_type', $job_type ?? [], null, ['class' => VC::FM_CT_SL]) }}
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('status', $status ?? [], null, ['class' => VC::FM_CT_SL]) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/jobs/boards/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('The requested onboarding record was not found.') }}</div>
@endif
