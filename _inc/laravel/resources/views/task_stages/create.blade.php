@include('partials.helpers.route_helpers')
@php
    try {
$lang = Utility::fetchUserLang();
        $formId = 'store-project-task-stage-form';

        $stageStore = resolveRouteWithGuard(VW::PRJ_TSK_STG.'.new', $lang, VW::PRJ_TSK_STG, 'store_project_task_stage_route_unavailable', [], 'Store stage route unavailable.');

        $nameLabel = __('Project Task Stage Name') ?: __('No stage name label available');
        $colorLabel = __('Color') ?: __('No color label available');
        $chartHelp = __('For chart representation') ?: __('No help text available');
        $cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
        $createLabel = __('Create') ?: __('Failed to get create label');
    } catch (\Throwable $e) {
        \Log::error('task_stages/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url' => $stageStore['url'],
    'method' => 'post',
    'id' => $formId,
    'data-resolved-action' => $stageStore['url'],
    'data-guard-msg' => $stageStore['guardMsg'],
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', $nameLabel, ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('color', $colorLabel, ['class' => VC::FM_LB]) }}
                <input class="jscolor {{ VC::FM_CT }}" value="FFFFFF" name="color" id="color" required>
                <small class="{{ VC::TXSM }}">{{ $chartHelp }}</small>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ $createLabel }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/projects/tasks/stages/store.js') }}"></script>
