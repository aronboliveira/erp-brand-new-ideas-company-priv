
@php
    use App\Config\Constants\{
        ProjectsConstants, 
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
<div class="modal-dialog modal-vertical modal-lg side-modal" role="document" id="{{ (string) data_get($task,'id','') }}">
	<div class="modal-content">
		<div class="modal-header">
			<div class="col {{ VC::DFL_AIC }}">
				<div class="{{ VC::CST_CT_CB }} mt-n1">
					@php
                        $changeCompleteBase = VW::PRJ_TSK_C.'.change.complete';
                        $changeCompleteKebab = Str::kebab($changeCompleteBase);
                        $changeCompleteResolved = Route::has($changeCompleteBase) ? $changeCompleteBase : (Route::has($changeCompleteKebab) ? $changeCompleteKebab : null);
                        $projIdValue = data_get($task,'project_id','');
                        $taskIdValue = data_get($task,'id','');
                        $changeCompleteUrl = ($changeCompleteResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($changeCompleteResolved, [$projIdValue, $taskIdValue]) : '#';
                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                        $changeCompleteGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'change_complete_project_task_route_unavailable') ?? 'Change complete project task route is unavailable. Please contact technical support or your domain administrator.';
                        $chkId = 'complete-task-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                        $isComplete = (int)(data_get($task, ProjectsConstants::COL_IS_CP) ?? 0) === 1;
                    @endphp
                    <input type="checkbox"
                        class="custom-control-input"
                        id="{{ $chkId }}"
                        @if($isComplete) checked @endif
                        data-url="{{ $changeCompleteUrl }}"
                        data-guard-msg="{{ $changeCompleteGuardMsg }}"
                        data-sv-localized="true">

                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                try {
                                    const el = document.getElementById('{{ $chkId }}');
                                    if (!el) { return; }
                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                    el.setAttribute('data-listener-active','true');
                                    el.addEventListener('change',(e) => {
                                        try {
                                            const url = el.getAttribute('data-url') ?? '#';
                                            if (url !== '#') { return; }
                                            e.preventDefault();
                                            el.checked = !el.checked;
                                            const msg = el.getAttribute('data-guard-msg') ?? 'Change complete project task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                toast.setAttribute('role','alert');
                                                toast.setAttribute('aria-live','assertive');
                                                toast.setAttribute('aria-atomic','true');
                                                const body = document.createElement('div');
                                                body.className = 'toast-body';
                                                body.textContent = msg;
                                                toast.appendChild(body);
                                                container.appendChild(toast);
                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                            } else {
                                                alert(msg);
                                            }
                                            el.setAttribute('data-failed-route','true');
                                        } catch (err) {}
                                    });
                                } catch (err) {}
                            })();
                        </script>
                    @endpush
					<label class="{{ VC::CST_LB }}" for="complete_task"></label>
				</div>
				<h6 class="{{ VC::MB0 }}">{{ data_get($task,'name') ?: __('No task name available') }}</h6>
			</div>
			<div class="{{ VC::C_AT }}">
				<div class="actions text-end">
					<div class="float-left">
						@php
                            $changeFavBase = VW::PRJ_TSK_C.'.change.fav';
                            $changeFavKebab = Str::kebab($changeFavBase);
                            $changeFavResolved = Route::has($changeFavBase) ? $changeFavBase : (Route::has($changeFavKebab) ? $changeFavKebab : null);
                            $projIdValue = data_get($task,'project_id','');
                            $taskIdValue = data_get($task,'id','');
                            $changeFavUrl = ($changeFavResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($changeFavResolved, [$projIdValue, $taskIdValue]) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $changeFavGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'change_favorite_project_task_route_unavailable') ?? 'Change favorite project task route is unavailable. Please contact technical support or your domain administrator.';
                            $favAnchorId = 'add_favourite-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                        @endphp
                        <a id="{{ $favAnchorId }}"
                        href="{{ $changeFavUrl }}"
                        class="action-item {{ data_get($task,ProjectsConstants::COL_IS_FV) ? 'action-favorite' : '' }} active"
                        data-url="{{ $changeFavUrl }}"
                        data-toggle="tooltip"
                        data-original-title="{{ __('Mark as favorite') }}"
                        data-guard-msg="{{ $changeFavGuardMsg }}"
                        data-sv-localized="true">
                            <i class="ti ti-star"></i>
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    try {
                                        const el = document.getElementById('{{ $favAnchorId }}');
                                        if (!el) { return; }
                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                        el.setAttribute('data-listener-active','true');
                                        el.addEventListener('click',(e) => {
                                            try {
                                                const href = el.getAttribute('href') ?? '#';
                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                if (url !== '#' && href !== '#') { return; }
                                                e.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? 'Change favorite project task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route','true');
                                            } catch (err) {}
                                        });
                                    } catch (err) {}
                                })();
                            </script>
                        @endpush
					</div>
					<div class="priority-color float-right">
						<div class="colorPickSelector" style="background-color: {{ data_get($task,'priority_color','') }}"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="scrollbar-inner">
			<div class="modal-body">
				<div class="{{ VC::R_ALC }} {{ VC::MB4 }}">
					<div class="col-6">
						<label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('See Detail') }}</label>
					</div>
					<div class="col-6 text-end">
						<a href="#" class="{{ VC::BT_XS }} btn-secondary btn-icon rounded-pill" data-toggle="collapse" data-target="#overview">
							<span class="btn-inner--icon"><i class="{{ VC::TI_PLS }}"></i></span>
						</a>
					</div>
				</div>
				<div id="overview" class="collapse">
					<b>{{ __('Estimated Hours') }}</b> : <span>{{ !empty(data_get($task,'estimated_hrs')) ? number_format((float) data_get($task,'estimated_hrs',0)) : __('No estimated hours available') }}</span> <br>
					<b>{{ __('Milestone') }}</b> : <span>{{ data_get($task,'milestone.title') ?: __('No milestone available') }}</span> <br>
					<b>{{ __('Description') }}</b> <br> <span>{{ data_get($task,'description') ?: __('No description available') }}</span>
				</div>
				<hr/>
				@if((string) ($allow_progress ?? '') === 'false')
					<div class="{{ VC::R_ALC }}">
						<div class="{{ VC::C12 }} pb-2">
							<label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('Task Progress') }} : <b id="t_percentage">{{ (int) (data_get($task,'progress') ?? 0) }}</b>%</label>
						</div>
						<div class="{{ VC::C12 }}">
							<div id="progress-result" class="tab-pane tab-example-result fade show active" role="tabpanel" aria-labelledby="progress-result-tab">
                                @php
                                    $changeProgressBase = VW::PRJ_TSK_C.'.change.progress';
                                    $changeProgressKebab = Str::kebab($changeProgressBase);
                                    $changeProgressResolved = Route::has($changeProgressBase) ? $changeProgressBase : (Route::has($changeProgressKebab) ? $changeProgressKebab : null);
                                    $projIdValue = data_get($task,'project_id','');
                                    $taskIdValue = data_get($task,'id','');
                                    $changeProgressUrl = ($changeProgressResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($changeProgressResolved, [$projIdValue, $taskIdValue]) : '#';
                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                    $changeProgressGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'change_progress_project_task_route_unavailable') ?? 'Change progress project task route is unavailable. Please contact technical support or your domain administrator.';
                                    $rangeId = 'task-progress-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                                    $progressVal = (int) (data_get($task,'progress') ?? 0);
                                @endphp
                                <input type="range"
                                    class="task_progress custom-range"
                                    value="{{ $progressVal }}"
                                    id="{{ $rangeId }}"
                                    name="progress"
                                    data-url="{{ $changeProgressUrl }}"
                                    data-guard-msg="{{ $changeProgressGuardMsg }}"
                                    data-sv-localized="true"
                                    data-start-val="{{ $progressVal }}">
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            try {
                                                const el = document.getElementById('{{ $rangeId }}');
                                                if (!el) { return; }
                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                el.setAttribute('data-listener-active','true');
                                                el.addEventListener('change',(e) => {
                                                    try {
                                                        const url = el.getAttribute('data-url') ?? '#';
                                                        if (url !== '#') { return; }
                                                        e.preventDefault();
                                                        const start = el.getAttribute('data-start-val') ?? '';
                                                        if (start !== '') { el.value = start; }
                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Change progress project task route is unavailable. Please contact technical support or your domain administrator.';
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
                                                            toast.setAttribute('role','alert');
                                                            toast.setAttribute('aria-live','assertive');
                                                            toast.setAttribute('aria-atomic','true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toast.appendChild(body);
                                                            container.appendChild(toast);
                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route','true');
                                                    } catch (err) {}
                                                });
                                            } catch (err) {}
                                        })();
                                    </script>
                                @endpush
							</div>
						</div>
					</div>
					<hr/>
				@endif
				<div class="{{ VC::R_ALC }} {{ VC::MB4 }}">
					<div class="col-6">
						<label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('Checklist') }}</label>
					</div>
					<div class="col-6 text-end">
						<a href="#" class="{{ VC::BT_XS }} btn-secondary btn-icon rounded-pill" data-toggle="collapse" data-target="#form-checklist">
							<span class="btn-inner--icon"><i class="{{ VC::TI_PLS }}"></i></span>
							<span class="btn-inner--text">{{ __('Add item') }}</span>
						</a>
					</div>
				</div>
				<div class="checklist" id="checklist">
                    @php
                        $checklistStoreBase = VW::PRJ_TSK_C.'.checklist.store';
                        $checklistStoreKebab = Str::kebab($checklistStoreBase);
                        $checklistStoreResolved = Route::has($checklistStoreBase) ? $checklistStoreBase : (Route::has($checklistStoreKebab) ? $checklistStoreKebab : null);
                        $projIdValue = data_get($task,'project_id','');
                        $taskIdValue = data_get($task,'id','');
                        $checklistStoreUrl = ($checklistStoreResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($checklistStoreResolved, [$projIdValue, $taskIdValue]) : '#';
                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                        $checklistStoreGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'store_project_task_checklist_route_unavailable') ?? 'Store project task checklist route is unavailable. Please contact technical support or your domain administrator.';
                        $formId = 'form-checklist-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                        $submitId = 'checklist-submit-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                    @endphp
                    <form method="post"
                        id="{{ $formId }}"
                        class="collapse pb-2"
                        action="{{ $checklistStoreUrl }}"
                        data-url="{{ $checklistStoreUrl }}"
                        data-guard-msg="{{ $checklistStoreGuardMsg }}"
                        data-sv-localized="true">
                        @csrf
                        <div class="{{ VC::CD_NSD }}">
                            <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::PX3 }} {{ VC::PY2 }}">
                                <div class="col-10">
                                    <input type="text" name="name" required class="{{ VC::FM_CT }}" placeholder="{{ __('Checklist Name') }}"/>
                                </div>
                                <div class="{{ VC::CL_MT_VC }}">
                                    <button class="{{ VC::BT_XS_PM }}" type="submit" id="{{ $submitId }}"><i class="{{ VC::TI_PLS }}"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                try {
                                    const fm = document.getElementById('{{ $formId }}');
                                    if (!fm) { return; }
                                    if (fm.getAttribute('data-listener-active') === 'true') { return; }
                                    fm.setAttribute('data-listener-active','true');
                                    fm.addEventListener('submit',(e) => {
                                        try {
                                            const action = fm.getAttribute('action') ?? '#';
                                            const url = fm.getAttribute('data-url') ?? action ?? '#';
                                            if (url !== '#' && action !== '#') { return; }
                                            e.preventDefault();
                                            const msg = fm.getAttribute('data-guard-msg') ?? 'Store project task checklist route is unavailable. Please contact technical support or your domain administrator.';
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
                                                toast.setAttribute('role','alert');
                                                toast.setAttribute('aria-live','assertive');
                                                toast.setAttribute('aria-atomic','true');
                                                const body = document.createElement('div');
                                                body.className = 'toast-body';
                                                body.textContent = msg;
                                                toast.appendChild(body);
                                                container.appendChild(toast);
                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                            } else {
                                                alert(msg);
                                            }
                                            fm.setAttribute('data-failed-route','true');
                                        } catch (err) {}
                                    });
                                } catch (err) {}
                            })();
                        </script>
                    @endpush
					@php
						$__checklist = data_get($task,'checklist',[]);
						$__checklist = ($__checklist instanceof \Illuminate\Support\Collection || is_array($__checklist)) ? $__checklist : [];
					@endphp
					@forelse($__checklist as $checklist)
						<div class="{{ VC::CD_NSD }} checklist-member">
							<div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::PX3 }} {{ VC::PY2 }}">
								<div class="col-10">
									<div class="{{ VC::CST_CTL }} {{ VC::CST_CB }}">
										@php
                                            $checklistUpdateBase = VW::PRJ_TSK_C.'.checklist.update';
                                            $checklistUpdateKebab = Str::kebab($checklistUpdateBase);
                                            $checklistUpdateResolved = Route::has($checklistUpdateBase) ? $checklistUpdateBase : (Route::has($checklistUpdateKebab) ? $checklistUpdateKebab : null);
                                            $projIdValue = data_get($task,'project_id','');
                                            $checklistIdValue = (string) data_get($checklist,'id','');
                                            $checklistUpdateUrl = ($checklistUpdateResolved && $projIdValue !== '' && $checklistIdValue !== '') ? route($checklistUpdateResolved, [$projIdValue, $checklistIdValue]) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $checklistUpdateGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'update_project_task_checklist_route_unavailable') ?? 'Update project task checklist route is unavailable. Please contact technical support or your domain administrator.';
                                            $chkId = 'check-item-'.($checklistIdValue === '' ? 'x' : $checklistIdValue);
                                            $isChecked = (bool) data_get($checklist,'status');
                                        @endphp
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="{{ $chkId }}"
                                            @if($isChecked) checked @endif
                                            data-url="{{ $checklistUpdateUrl }}"
                                            data-guard-msg="{{ $checklistUpdateGuardMsg }}"
                                            data-sv-localized="true">

                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const el = document.getElementById('{{ $chkId }}');
                                                        if (!el) { return; }
                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                        el.setAttribute('data-listener-active','true');
                                                        el.addEventListener('change',(e) => {
                                                            try {
                                                                const url = el.getAttribute('data-url') ?? '#';
                                                                if (url !== '#') { return; }
                                                                e.preventDefault();
                                                                el.checked = !el.checked;
                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Update project task checklist route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                    toast.setAttribute('role','alert');
                                                                    toast.setAttribute('aria-live','assertive');
                                                                    toast.setAttribute('aria-atomic','true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toast.appendChild(body);
                                                                    container.appendChild(toast);
                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                el.setAttribute('data-failed-route','true');
                                                            } catch (err) {}
                                                        });
                                                    } catch (err) {}
                                                })();
                                            </script>
                                        @endpush
										<label class="{{ VC::CST_LB_SM }}" for="check-item-{{ (string) data_get($checklist,'id','') }}">{{ data_get($checklist,'name') ?: __('No checklist name available') }}</label>
									</div>
								</div>
								<div class="{{ VC::C_AT }} {{ VC::CD_MT }} {{ VC::DFL_IL_VC }} {{ VC::ML_SM_AT }}">
                                    @php
                                        $chkDestroyBase = VW::PRJ_TSK_C.'.checklist.destroy';
                                        $chkDestroyKebab = Str::kebab($chkDestroyBase);
                                        $chkDestroyResolved = Route::has($chkDestroyBase) ? $chkDestroyBase : (Route::has($chkDestroyKebab) ? $chkDestroyKebab : null);
                                        $projIdValue = data_get($task,'project_id','');
                                        $chkIdValue = data_get($checklist,'id','');
                                        $chkDestroyUrl = ($chkDestroyResolved && $projIdValue !== '' && $chkIdValue !== '') ? route($chkDestroyResolved, [$projIdValue, $chkIdValue]) : '#';
                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                        $chkDestroyGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'destroy_project_task_checklist_route_unavailable') ?? 'Destroy project task checklist route is unavailable. Please contact technical support or your domain administrator.';
                                        $chkDestroyAnchorId = 'delete-checklist-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($chkIdValue === '' ? 'y' : $chkIdValue);
                                    @endphp
                                    <a id="{{ $chkDestroyAnchorId }}"
                                    href="{{ $chkDestroyUrl }}"
                                    class="action-item delete-checklist"
                                    data-url="{{ $chkDestroyUrl }}"
                                    data-guard-msg="{{ $chkDestroyGuardMsg }}"
                                    data-sv-localized="true"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Delete') }}">
                                        <i class="{{ VC::TI_TRS_ALT }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const el = document.getElementById('{{ $chkDestroyAnchorId }}');
                                                    if (!el) { return; }
                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                    el.setAttribute('data-listener-active','true');
                                                    el.addEventListener('click',(e) => {
                                                        try {
                                                            const href = el.getAttribute('href') ?? '#';
                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                            if (url !== '#' && href !== '#') { return; }
                                                            e.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Destroy project task checklist route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                toast.setAttribute('role','alert');
                                                                toast.setAttribute('aria-live','assertive');
                                                                toast.setAttribute('aria-atomic','true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toast.appendChild(body);
                                                                container.appendChild(toast);
                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route','true');
                                                        } catch (err) {}
                                                    });
                                                } catch (err) {}
                                            })();
                                        </script>
                                    @endpush
								</div>
							</div>
						</div>
					@empty
						<p class="text-muted">{{ __('No checklist items available') }}</p>
					@endforelse
				</div>
				<hr/>
				<div class="{{ VC::R_ALC }} {{ VC::MB4 }}">
					<div class="col-6">
						<label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('Attachments ewrwr') }}</label>
					</div>
					<div class="col-6 text-end">
						<a href="#" class="{{ VC::BT_XS }} btn-secondary btn-icon rounded-pill" data-toggle="collapse" data-target="#add_file">
							<span class="btn-inner--icon"><i class="{{ VC::TI_PLS }}"></i></span>
							<span class="btn-inner--text">{{ __('Add item') }}</span>
						</a>
					</div>
				</div>
				<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
				<div class="card mb-3 border shadow-none collapse" id="add_file">
					<div class="card border-0 shadow-none {{ VC::MB0 }}">
						<div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::PX3 }} {{ VC::PY2 }}">
							<div class="col-10">
								<input type="file" name="task_attachment" id="task_attachment" required class="custom-input-file"/>
								<label for="task_attachment"><i class="fa fa-upload"></i><span class="attachment_text">{{ __('Choose a file…') }}</span></label>
							</div>
							<div class="{{ VC::CL_MT_VC }}">
								<button class="{{ VC::BT_XS_PM }}" type="submit" id="file_submit1234"><i class="{{ VC::TI_PLS }}"></i></button>
							</div>
						</div>
					</div>
				</div>
				<div id="comments-file">
					@php
						$__files = data_get($task,'taskFiles',[]);
						$__files = ($__files instanceof \Illuminate\Support\Collection || is_array($__files)) ? $__files : [];
					@endphp
					@forelse($__files as $file)
						<div class="{{ VC::CD_NSD }} {{ VC::MB3 }} task-file">
							<div class="{{ VC::PX3 }} {{ VC::PY2 }}">
								<div class="{{ VC::R_ALC }}">
									<div class="{{ VC::C_AT }}">
										<img src="{{ asset('assets/img/icons/files/'.(data_get($file,'extension') ?: 'file').'.png') }}" class="img-fluid" style="width: 40px;">
									</div>
									<div class="col ml-n2">
										<h6 class="{{ VC::TXSM }} {{ VC::MB0 }}"><a href="#">{{ data_get($file,'name') ?: __('No file name available') }}</a></h6>
										<p class="card-text small {{ VC::TXT_MT }}">{{ data_get($file,'file_size') ?: __('No file size available') }}</p>
									</div>
									<div class="{{ VC::C_AT }} actions">
										<a href="{{ asset(Storage::url('tasks/'.(data_get($file,'file') ?: ''))) }}" download class="action-item" role="button"><i class="{{ VC::TI_DWN }}"></i></a>
										@auth('web')
                                            @php
                                                $cmtFileDestroyBase = VW::PRJ_TSK_C.'.comment.destroy.file';
                                                $cmtFileDestroyKebab = Str::kebab($cmtFileDestroyBase);
                                                $cmtFileDestroyResolved = Route::has($cmtFileDestroyBase) ? $cmtFileDestroyBase : (Route::has($cmtFileDestroyKebab) ? $cmtFileDestroyKebab : null);
                                                $projIdValue = (string) data_get($task,'project_id','');
                                                $taskIdValue = (string) data_get($task,'id','');
                                                $fileIdValue = (string) data_get($file,'id','');
                                                $cmtFileDestroyUrl = ($cmtFileDestroyResolved && $projIdValue !== '' && $taskIdValue !== '' && $fileIdValue !== '') ? route($cmtFileDestroyResolved, [$projIdValue, $taskIdValue, $fileIdValue]) : '#';
                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                $cmtFileDestroyGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'destroy_project_task_comment_file_route_unavailable') ?? 'Destroy project task comment file route is unavailable. Please contact technical support or your domain administrator.';
                                                $cmtFileDestroyAnchorId = 'delete-comment-file-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue).'-'.($fileIdValue === '' ? 'z' : $fileIdValue);
                                            @endphp
                                            <a id="{{ $cmtFileDestroyAnchorId }}"
                                            href="{{ $cmtFileDestroyUrl }}"
                                            class="action-item delete-comment-file"
                                            role="button"
                                            data-url="{{ $cmtFileDestroyUrl }}"
                                            data-guard-msg="{{ $cmtFileDestroyGuardMsg }}"
                                            data-sv-localized="true">
                                                <i class="{{ VC::TI_TRS }}"></i>
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const el = document.getElementById('{{ $cmtFileDestroyAnchorId }}');
                                                            if (!el) { return; }
                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                            el.setAttribute('data-listener-active','true');
                                                            el.addEventListener('click',(e) => {
                                                                try {
                                                                    const href = el.getAttribute('href') ?? '#';
                                                                    const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                    if (url !== '#' && href !== '#') { return; }
                                                                    e.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? 'Destroy project task comment file route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                        toast.setAttribute('role','alert');
                                                                        toast.setAttribute('aria-live','assertive');
                                                                        toast.setAttribute('aria-atomic','true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toast.appendChild(body);
                                                                        container.appendChild(toast);
                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route','true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (err) {}
                                                    })();
                                                </script>
                                            @endpush
										@endauth
									</div>
								</div>
							</div>
						</div>
					@empty
						<p class="text-muted">{{ __('No attachments available') }}</p>
					@endforelse
				</div>
				<hr/>
				<label class="{{ VC::FM_LB }} {{ VC::MB4 }}">{{ __('Activity') }}</label>
				<div class="{{ VC::LG_FLSH_MB0 }}">
					@php
						$__activity = (is_object($task) && method_exists($task,'activityLog')) ? ($task->activityLog() ?? []) : [];
						$__activity = ($__activity instanceof \Illuminate\Support\Collection || is_array($__activity)) ? $__activity : [];
					@endphp
					@forelse($__activity as $activity)
						<div class="{{ VC::LGI }}">
							<div class="{{ VC::R_ALC }}">
								<div class="{{ VC::C_AT }}">
									<a href="#" class="{{ VC::AV_CC_SM }}"><img {{ data_get($activity,'user.img_avatar') }} class="{{ VC::AV_CC_SM }}"></a>
								</div>
								<div class="col ml-n2">
									<span class="text-dark {{ VC::TXSM }}">{{ __(data_get($activity,'log_type','')) ?: __('No activity type available') }}</span>
									<a class="d-block h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }}">{!! (is_object($activity) && method_exists($activity,'getRemark')) ? ($activity->getRemark() ?? '') : '' !!}</a>
									<small class="d-block">{{ (data_get($activity,'created_at') && method_exists(data_get($activity,'created_at'),'diffForHumans')) ? data_get($activity,'created_at')->diffForHumans() : __('No time available') }}</small>
								</div>
							</div>
						</div>
					@empty
						<p class="text-muted">{{ __('No activity available') }}</p>
					@endforelse
				</div>
				<hr/>
				<label class="{{ VC::FM_LB }} {{ VC::MB4 }}">{{ __('Comments') }}</label>
				<div class="{{ VC::LG_FLSH_MB0 }}" id="comments">
					@php
						$__comments = data_get($task,'comments',[]);
						$__comments = ($__comments instanceof \Illuminate\Support\Collection || is_array($__comments)) ? $__comments : [];
					@endphp
					@forelse($__comments as $comment)
						<div class="{{ VC::LGI }}">
							<div class="{{ VC::R_ALC }}">
								<div class="{{ VC::C_AT }}">
									<a href="#" class="{{ VC::AV_CC_SM }}"><img {{ data_get($comment,'user.img_avatar') }} title="{{ data_get($comment,'user.name') }}"></a>
								</div>
								<div class="col ml-n2">
									<p class="d-block h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break">{{ data_get($comment,'comment') ?: __('No comment text available') }}</p>
									<small class="d-block">{{ (data_get($comment,'created_at') && method_exists(data_get($comment,'created_at'),'diffForHumans')) ? data_get($comment,'created_at')->diffForHumans() : __('No time available') }}</small>
								</div>
								<div class="{{ VC::C_AT }}">
									@php
                                        $cmtDestroyBase = VW::PRJ_TSK_C.'.comment.destroy';
                                        $cmtDestroyKebab = Str::kebab($cmtDestroyBase);
                                        $cmtDestroyResolved = Route::has($cmtDestroyBase) ? $cmtDestroyBase : (Route::has($cmtDestroyKebab) ? $cmtDestroyKebab : null);
                                        $projIdValue = (string) data_get($task,'project_id','');
                                        $taskIdValue = (string) data_get($task,'id','');
                                        $commentIdValue = (string) data_get($comment,'id','');
                                        $cmtDestroyUrl = ($cmtDestroyResolved && $projIdValue !== '' && $taskIdValue !== '' && $commentIdValue !== '') ? route($cmtDestroyResolved, [$projIdValue, $taskIdValue, $commentIdValue]) : '#';
                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                        $cmtDestroyGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'destroy_project_task_comment_route_unavailable') ?? 'Destroy project task comment route is unavailable. Please contact technical support or your domain administrator.';
                                        $cmtDestroyAnchorId = 'delete-comment-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue).'-'.($commentIdValue === '' ? 'z' : $commentIdValue);
                                    @endphp
                                    <a id="{{ $cmtDestroyAnchorId }}"
                                    href="{{ $cmtDestroyUrl }}"
                                    class="action-item delete-comment"
                                    data-url="{{ $cmtDestroyUrl }}"
                                    data-guard-msg="{{ $cmtDestroyGuardMsg }}"
                                    data-sv-localized="true">
                                        <i class="{{ VC::TI_TRS_ALT }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const el = document.getElementById('{{ $cmtDestroyAnchorId }}');
                                                    if (!el) { return; }
                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                    el.setAttribute('data-listener-active','true');
                                                    el.addEventListener('click',(e) => {
                                                        try {
                                                            const href = el.getAttribute('href') ?? '#';
                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                            if (url !== '#' && href !== '#') { return; }
                                                            e.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Destroy project task comment route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                toast.setAttribute('role','alert');
                                                                toast.setAttribute('aria-live','assertive');
                                                                toast.setAttribute('aria-atomic','true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toast.appendChild(body);
                                                                container.appendChild(toast);
                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route','true');
                                                        } catch (err) {}
                                                    });
                                                } catch (err) {}
                                            })();
                                        </script>
                                    @endpush
								</div>
							</div>
						</div>
					@empty
						<p class="text-muted">{{ __('No comments available') }}</p>
					@endforelse
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="col-12 {{ VC::DFL }}">
				<div class="pr-3"><img {{ $user?->img_avatar ?? __('No image') }} title="{{ $user?->name ?? __('Anonymous user') }}" class="{{ VC::AV_CC_SM }}"></div>
                @php
                    $cmtStoreBase = VW::PRJ_TSK_C.'.comment.store';
                    $cmtStoreKebab = Str::kebab($cmtStoreBase);
                    $cmtStoreResolved = Route::has($cmtStoreBase) ? $cmtStoreBase : (Route::has($cmtStoreKebab) ? $cmtStoreKebab : null);
                    $projIdValue = (string) data_get($task,'project_id','');
                    $taskIdValue = (string) data_get($task,'id','');
                    $cmtStoreUrl = ($cmtStoreResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($cmtStoreResolved, [$projIdValue, $taskIdValue]) : '#';
                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                    $cmtStoreGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'store_project_task_comment_route_unavailable') ?? 'Store project task comment route is unavailable. Please contact technical support or your domain administrator.';
                    $formId = 'form-comment-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                @endphp
                <form method="post"
                    class="card-comment-box"
                    id="{{ $formId }}"
                    action="{{ $cmtStoreUrl }}"
                    data-url="{{ $cmtStoreUrl }}"
                    data-guard-msg="{{ $cmtStoreGuardMsg }}"
                    data-sv-localized="true">
                    @csrf
                    <textarea rows="1"
                            class="{{ VC::FM_CT }}"
                            name="comment"
                            data-toggle="autosize"
                            placeholder="{{ __('Add a comment...') }}"></textarea>
                </form>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const fm = document.getElementById('{{ $formId }}');
                                if (!fm) { return; }
                                if (fm.getAttribute('data-listener-active') === 'true') { return; }
                                fm.setAttribute('data-listener-active','true');
                                fm.addEventListener('submit',(e) => {
                                    try {
                                        const action = fm.getAttribute('action') ?? '#';
                                        const url = fm.getAttribute('data-url') ?? action ?? '#';
                                        if (url !== '#' && action !== '#') { return; }
                                        e.preventDefault();
                                        const msg = fm.getAttribute('data-guard-msg') ?? 'Store project task comment route is unavailable. Please contact technical support or your domain administrator.';
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
                                            toast.setAttribute('role','alert');
                                            toast.setAttribute('aria-live','assertive');
                                            toast.setAttribute('aria-atomic','true');
                                            const body = document.createElement('div');
                                            body.className = 'toast-body';
                                            body.textContent = msg;
                                            toast.appendChild(body);
                                            container.appendChild(toast);
                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                        } else {
                                            alert(msg);
                                        }
                                        fm.setAttribute('data-failed-route','true');
                                    } catch (err) {}
                                });
                            } catch (err) {}
                        })();
                    </script>
                @endpush
			</div>
			<div class="col-4 col-md-3 text-end">
				<div class="actions"><a href="#" id="comment_submit" class="action-item"><i class="ti ti-paper-plane"></i></a></div>
			</div>
		</div>
	</div>
</div>

<script async src="{{ asset('assets/js/routes/tasks/lang/color.js') }}"></script>
<script defer>
    (function () {
    const $ = window.jQuery;
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    const dataSvLocalized = "data-sv-localized";
    const dataErrGuard = "data-error-guard";
    const dataBound = "data-colorpick-bound";
    const qs = (s, r = document) => r.querySelector(s);
    const ensureToastContainer = () => {
        let c = qs("#np-toast-container");
        if (c) {
        return c;
        }
        c = document.createElement("div");
        c.id = "np-toast-container";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        c.style.position = "fixed";
        c.style.top = "1rem";
        c.style.right = "1rem";
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = message => {
        const hasBootstrap =
        (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')) &&
        window.bootstrap &&
        window.bootstrap.Toast;
        if (hasBootstrap) {
        const container = ensureToastContainer();
        let t = qs("#np-toast", container);
        if (!t) {
            t = document.createElement("div");
            t.id = "np-toast";
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            t.innerHTML =
            '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
            container.appendChild(t);
        }
        const body = qs(".toast-body", t);
        if (body) {
            body.textContent = message ?? errFb;
        }
        try {
            new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
        } catch (_) {
            alert(message ?? errFb);
        }
        } else {
        alert(message ?? errFb);
        }
    };
    const scheduleInteractiveError = message => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true") {
        return;
        }
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
        try {
            showErrorNow(message);
        } finally {
            host.removeAttribute(dataErrGuard);
        }
        };
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((m, o) => {
        if (!document.body.contains(host)) {
            document.removeEventListener("pointerup", once);
            o.disconnect();
        }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    const getMsg = (el, key) => {
        let msg = errFb;
        if (
        el?.getAttribute?.(dataSvLocalized) === "true" ||
        el?.getAttribute?.(dataClientLocalized) === "true"
        ) {
        msg = el.getAttribute(dataGuardMsg) || errFb;
        } else {
        let lang = (
            window.sessionStorage.getItem("erp-np-lang") ||
            document.documentElement.lang ||
            "en"
        )
            .toLowerCase()
            .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        const msgKey = key;
        msg =
            window.translations?.[lang]?.[msgKey] ||
            el?.getAttribute?.(dataGuardMsg) ||
            window.translations?.en?.[msgKey] ||
            errFb;
        if (el && msg !== errFb) {
            el.setAttribute(dataGuardMsg, msg);
            el.setAttribute(dataClientLocalized, "true");
        }
        }
        return msg;
    };
    const initColorPickers = () => {
        if (!$ || !$.fn) {
        try {
            console.log("jQuery unavailable");
        } catch (_) {}
        scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
        return;
        }
        if (!$.fn.colorPick) {
        try {
            console.log("ColorPick plugin unavailable");
        } catch (_) {}
        scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
        return;
        }
        $(".colorPickSelector").each(function () {
        const el = this;
        if (el.getAttribute(dataBound) === "true") {
            return;
        }
        el.setAttribute(dataBound, "true");
        $(el).colorPick({
            onColorSelected: function () {
            const side = this.element?.parents?.(".side-modal");
            const task_id = side && side.length ? side.attr("id") : "";
            const color = this.color ?? "";
            if (task_id && color) {
                try {
                this.element.css({ backgroundColor: color });
                } catch (_) {}
                const explicit = '{{ route("update.task.priority.color") }}';
                const urlAttr = el.getAttribute("data-url");
                const hrefAttr =
                el.tagName === "FORM"
                    ? el.getAttribute("action") || ""
                    : el.getAttribute("href") || "";
                if (
                (!explicit || explicit === "#") &&
                (!urlAttr || urlAttr === "#") &&
                (!hrefAttr || hrefAttr === "#")
                ) {
                scheduleInteractiveError(getMsg(el, "color_update_unavailable"));
                return;
                }
                const endpoint =
                explicit && explicit !== "#"
                    ? explicit
                    : urlAttr && urlAttr !== "#"
                    ? urlAttr
                    : hrefAttr;
                $.ajax({
                url: endpoint,
                method: "PATCH",
                data: { task_id: task_id, color: color },
                cache: false,
                success: function () {
                    try {
                    $(".task-list-items")
                        .find("#" + task_id)
                        .attr(
                        "style",
                        "border-left:2px solid " + color + " !important"
                        );
                    } catch (_) {}
                },
                error: function () {
                    scheduleInteractiveError(getMsg(el, "ajax_unavailable"));
                },
                });
            } else {
                scheduleInteractiveError(getMsg(el, "color_update_unavailable"));
            }
            },
        });
        const mo = new MutationObserver((m, o) => {
            if (!document.body.contains(el)) {
            o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
        });
    };
    const init = () => {
        if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initColorPickers, {
            once: true,
        });
        } else {
        initColorPickers();
        }
    };
    init();
    })();
</script>
