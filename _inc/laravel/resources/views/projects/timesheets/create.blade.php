@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $timesheetStoreBaseName     = ViewsConstants::TMS.'.store';
    $timesheetStoreKebabName    = Str::kebab($timesheetStoreBaseName);
    $timesheetStoreResolvedName = Route::has($timesheetStoreBaseName)
        ? $timesheetStoreBaseName
        : (Route::has($timesheetStoreKebabName) ? $timesheetStoreKebabName : null);
    $timesheetStoreRouteArray   = $timesheetStoreResolvedName ? [$timesheetStoreResolvedName] : ['#'];
    $timesheetStoreUrl          = $timesheetStoreResolvedName ? route($timesheetStoreResolvedName) : '#';
    $timesheetStoreGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::TMS, 'create_timesheet_route_unavailable') ?? 'Create timesheet route is unavailable. Please contact technical support or your domain administrator.';
    $timesheetStoreFormId       = 'project_form';
@endphp
{!! Form::open([
    'route'          => $timesheetStoreRouteArray,
    'method'         => 'post',
    'accept-charset' => 'UTF-8',
    'id'             => $timesheetStoreFormId,
    'data-url'       => $timesheetStoreUrl,
    'data-guard-msg' => $timesheetStoreGuardMsg
]) !!}
    @csrf
    @if (!empty($parseArray))
        <div class="modal-body">
            <input type="hidden" name="project_id" value="{{ !empty($parseArray['project_id']) ? $parseArray['project_id'] : __('No defined project id') }}">
            <input type="hidden" name="task_id" value="{{ !empty($parseArray['task_id']) ? $parseArray['task_id'] : __('No defined task id') }}">
            <input type="hidden" name="date" value="{{ !empty($parseArray['date']) ? $parseArray['date'] : __('No defined date') }}">
            <input type="hidden" id="totaltasktime" value="{{ !empty($parseArray['totaltaskhour']) && !empty($parseArray['totaltaskminute']) ? $parseArray['totaltaskhour'] . ':' . $parseArray['totaltaskminute'] : __('No defined total task time') }}">
            <div class="details {{ VC::MB3 }}">
                <div class="{{ VC::FM_G }} text-center">
                    <label for="descriptions" class="{{ VC::FM_LB }}">
                        {{ !empty($parseArray['project_name']) && !empty($parseArray['task_name']) ? $parseArray['project_name'] . ' : ' . $parseArray['task_name'] : __('No defined project or task name') }}
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
                                @php($h = str_pad((string)$i, 2, '0', STR_PAD_LEFT))
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="{{ VC::CM6 }} {{ VC::CS12 }}">
                    <div class="{{ VC::FM_G }}">
                        <select class="{{ VC::FM_CT }}" name="time_minute" id="time_minute" required>
                            <option value="">{{ __('Minutes') }}</option>
                            @for ($i = 0; $i <= 60; $i += 10)
                                @php($m = str_pad((string)$i, 2, '0', STR_PAD_LEFT))
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="{{ VC::FM_G }}">
                <label for="description" class="{{ VC::FM_LB }}">{{ __('Description') }}</label>
                <textarea class="{{ VC::FM_CT }}" id="description" rows="3" name="description"></textarea>
            </div>
            <div class="{{ VC::C12 }}">
                <div class="display-total-time">
                    <i class="{{ VC::TI }} {{ VC::TI }}-clock"></i>
                    <span>
                        {{ __('Total Time worked on this task') }}
                        : {{ !empty($parseArray['totaltaskhour']) && !empty($parseArray['totaltaskminute']) ? $parseArray['totaltaskhour'] . ' ' . __('Hours') . ' ' . $parseArray['totaltaskminute'] . ' ' . __('Minutes') : __('No defined total task time') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
        </div>
    @else
        <div class="alert alert-warning">
            {{ __('No data available to create timesheet') }}
        </div>
    @endif
{{ Form::close() }}
<script defer>
    (() => {
        try {
            const f = document.getElementById('{{ $timesheetStoreFormId }}');
            if (!f || f.getAttribute('data-listener-active') === 'true') return;
            f.setAttribute('data-listener-active', 'true');
            f.addEventListener('submit', e => {
                try {
                    const url = f.getAttribute('data-url') || '#';
                    const action = f.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = f.getAttribute('data-guard-msg') || 'Create timesheet route is unavailable. Please contact technical support or your domain administrator.';
                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                } catch (err) {}
            });
        } catch (error) {}
    })();
</script>