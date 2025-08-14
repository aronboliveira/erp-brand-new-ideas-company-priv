@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $timesheetUpdateBaseName     = ViewsConstants::TMS . '.update';
    $timesheetUpdateKebabName    = Str::kebab($timesheetUpdateBaseName);
    $timesheetUpdateResolvedName = Route::has($timesheetUpdateBaseName)
        ? $timesheetUpdateBaseName
        : (Route::has($timesheetUpdateKebabName) ? $timesheetUpdateKebabName : null);
    $timesheetId = $timesheet->id ?? null;
    $timesheetUpdateRouteArray = ($timesheetUpdateResolvedName && $timesheetId)
        ? [$timesheetUpdateResolvedName, $timesheetId]
        : ['#'];
    $timesheetUpdateUrl = ($timesheetUpdateResolvedName && $timesheetId)
        ? route($timesheetUpdateResolvedName, $timesheetId)
        : '#';
    $timesheetUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMS, 'update_timesheet_route_unavailable')
        ?? 'Update timesheet route is unavailable. Please contact technical support or your domain administrator.';
    $timesheetUpdateFormId = 'timesheet_update_form_' . ($timesheetId ?? 'unknown');
@endphp

{!! Form::model($timesheet, [
    'route'          => $timesheetUpdateRouteArray,
    'method'         => 'put',
    'accept-charset' => 'UTF-8',
    'id'             => $timesheetUpdateFormId,
    'data-url'       => $timesheetUpdateUrl,
    'data-guard-msg' => $timesheetUpdateGuardMsg,
]) !!}
    @csrf

    @if (!empty($parseArray))
        <div class="modal-body">
            <input type="hidden" name="project_id" value="{{ !empty($parseArray['project_id']) ? $parseArray['project_id'] : __('No defined project id') }}">
            <input type="hidden" name="task_id"    value="{{ !empty($parseArray['task_id'])    ? $parseArray['task_id']    : __('No defined task id') }}">
            <input type="hidden" name="date"       value="{{ !empty($timesheet->date)          ? $timesheet->date          : __('No defined date') }}">
            <input type="hidden" id="totaltasktime"
                   value="{{ !empty($parseArray['totaltaskhour']) && !empty($parseArray['totaltaskminute'])
                                ? $parseArray['totaltaskhour'] . ':' . $parseArray['totaltaskminute']
                                : __('No defined total task time') }}">
            <div class="details {{ VC::MB3 }}">
                <div class="{{ VC::FM_G }} text-center">
                    <label for="descriptions" class="{{ VC::FM_LB }}">
                        {{ !empty($parseArray['project_name']) && !empty($parseArray['task_name'])
                            ? $parseArray['project_name'] . ' : ' . $parseArray['task_name']
                            : __('No defined project or task name') }}
                    </label>
                </div>
            </div>
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }} {{ VC::MB0 }}">
                        <label for="time_hour" class="{{ VC::FM_LB }}">{{ __('Time') }}</label>
                    </div>
                </div>

                <div class="{{ VC::CM6 }} {{ VC::CS12 }}">
                    <div class="{{ VC::FM_G }}">
                        <select class="{{ VC::FM_CT }} select2" name="time_hour" id="time_hour" required>
                            <option value="">{{ __('Hours') }}</option>
                            @for ($i = 0; $i < 24; $i++)
                                @php($h = str_pad((string) $i, 2, '0', STR_PAD_LEFT))
                                <option value="{{ $h }}" {{ (!empty($parseArray['time_hour']) && (string)$parseArray['time_hour'] === $h) ? 'selected' : '' }}>
                                    {{ $h }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="{{ VC::CM6 }} {{ VC::CS12 }}">
                    <div class="{{ VC::FM_G }}">
                        <select class="{{ VC::FM_CT }}" name="time_minute" id="time_minute" required>
                            <option value="">{{ __('Minutes') }}</option>
                            @for ($i = 0; $i <= 60; $i += 10)
                                @php($m = str_pad((string) $i, 2, '0', STR_PAD_LEFT))
                                <option value="{{ $m }}" {{ (!empty($parseArray['time_minute']) && (string)$parseArray['time_minute'] === $m) ? 'selected' : '' }}>
                                    {{ $m }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="{{ VC::FM_G }}">
                <label for="description" class="{{ VC::FM_LB }}">{{ __('Description') }}</label>
                <textarea class="{{ VC::FM_CT }}" id="description" rows="3" name="description">{{ old('description', $timesheet->description ?? '') }}</textarea>
            </div>
            <div class="{{ VC::C12 }}">
                <div class="display-total-time">
                    <i class="{{ VC::TI }} {{ VC::TI }}-clock"></i>
                    <span>
                        {{ __('Total Time worked on this task') }} :
                        {{ !empty($parseArray['totaltaskhour']) && !empty($parseArray['totaltaskminute'])
                            ? $parseArray['totaltaskhour'] . ' ' . __('Hours') . ' ' . $parseArray['totaltaskminute'] . ' ' . __('Minutes')
                            : __('No defined total task time') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
    @else
        <div class="modal-body">
            <div class="alert alert-warning">
                {{ __('No data available to update timesheet') }}
            </div>
        </div>
    @endif
{{ Form::close() }}

<script defer>
(() => {
    try {
        const f = document.getElementById('{{ $timesheetUpdateFormId }}');
        if (!f || f.getAttribute('data-listener-active') === 'true') return;
        f.setAttribute('data-listener-active', 'true');

        f.addEventListener('submit', (e) => {
            try {
                const url    = f.getAttribute('data-url') || '#';
                const action = f.getAttribute('action') || '#';
                if (url !== '#' || action !== '#') return;

                e.preventDefault();

                const msg = f.getAttribute('data-guard-msg') ||
                    'Update timesheet route is unavailable. Please contact technical support or your domain administrator.';

                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    document.body.appendChild(container);
                }

                if (hasBootstrap) {
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');

                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    body.textContent = msg;

                    toast.appendChild(body);
                    container.appendChild(toast);
                    bootstrap.Toast.getOrCreateInstance(toast).show();
                } else {
                    alert(msg);
                }

                f.setAttribute('data-failed-route', 'true');
            } catch (_) {}
        });
    } catch (_) {}
})();
</script>
