@php
    use App\Config\Constants\{PlansConstants, ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Route, Gate, URL};
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();
    $settings = Utility::settings();
    $canCreate = Gate::check('create event');
    $storeName = VW::EVT;
    $storeUrl = $canCreate && Route::has($storeName) ? route($storeName) : (trim(VW::EVT,'/') ? URL::to(VW::EVT) : '#');
    $storeGuard = Utility::fetchLinkMessage($lang, VW::EVT, 'store_route_unavailable') ?? 'Event create route is unavailable. Please contact technical support or your domain administrator.';
    $depName = VW::EVT.'.departments';
    $empName = VW::EVT.'.employees';
    $depUrl  = Route::has($depName) ? route($depName) : '#';
    $empUrl  = Route::has($empName) ? route($empName) : '#';
    $depGuard = Utility::fetchLinkMessage($lang, VW::EVT, 'departments_route_unavailable') ?? 'Departments route is unavailable. Please contact technical support or your domain administrator.';
    $empGuard = Utility::fetchLinkMessage($lang, VW::EVT, 'employees_route_unavailable') ?? 'Employees route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Form::open([
    Route::has($storeName) && $canCreate ? 'route' : 'url' => Route::has($storeName) && $canCreate ? $storeName : $storeUrl,
    'method' => 'post',
    'id' => 'store_event_form',
    'data-action-url' => $storeUrl,
    'data-guard-msg' => $storeGuard,
    'data-sv-localized' => 'true'
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            @php
                $genHref   = Route::has('generate') ? route('generate',['event']) : '#';
                $genMsg    = Utility::fetchLinkMessage($lang, VW::EVT, 'ai_generate_route_unavailable')
                             ?? 'AI generate route is unavailable for events. Please contact technical support or your domain administrator.';
                $genLinkId = 'event-generate-ai-link';
            @endphp
            <div class="text-end">
                <a href="#"
                   id="{{ $genLinkId }}"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ $genHref }}"
                   data-sv-localized="true"
                   data-guard-msg="{{ $genMsg }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM4 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('branch_id', __('Branch'), ['class' => VC::FM_LB]) }}
                    <select class="{{ VC::FM_CT_SL }}" name="branch_id" id="branch_id" placeholder="{{ __('Select Branch') }}">
                        <option value="">{{ __('Select Branch') }}</option>
                        <option value="0">{{ __('All Branch') }}</option>
                        @if((is_array($branches ?? null) && count($branches ?? [])) || (($branches ?? null) instanceof Collection && $branches->isNotEmpty()))
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id ?? '' }}">{{ $branch->name ?? __('No name available for branch') }}</option>
                            @endforeach
                        @else
                            <option value="">{{ __('No Branch Found') }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="{{ VC::CM4 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
                    <div class="department_div" data-url="{{ $depUrl }}" data-guard-msg="{{ $depGuard }}" data-sv-localized="true">
                        <select class="{{ VC::FM_CT_SL }} department_id" name="department_id[]" placeholder="{{ __('Select Designation') }}">
                            <option value="">{{ __('Select Designation') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="{{ VC::CM4 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                    <div class="employee_div" data-url="{{ $empUrl }}" data-guard-msg="{{ $empGuard }}" data-sv-localized="true">
                        <select class="{{ VC::FM_CT_SL }} employee_id" name="employee_id[]" placeholder="{{ __('Select Employee') }}">
                            <option value="">{{ __('Select Employee') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="{{ VC::CM12 }} {{ VC::CS12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('title', __('Event Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Event Title')]) }}
                </div>
            </div>

            <div class="{{ VC::CM6 }} {{ VC::CS12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('start_date', __('Event start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('start_date', null, ['class' => VC::FM_CT.' datetime-local', 'autocomplete' => 'off']) }}
                </div>
            </div>

            <div class="{{ VC::CM6 }} {{ VC::CS12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('end_date', __('Event End Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('end_date', null, ['class' => VC::FM_CT.' datetime-local', 'autocomplete' => 'off']) }}
                </div>
            </div>

            <div class="{{ VC::CM12 }} {{ VC::CS12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('color', __('Event Select Color'), ['class' => VC::FM_LB.' d-block mb-3']) }}
                    <div class="btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                        <label class="btn bg-info active p-3"><input type="radio" name="color" value="event-info" checked class="d-none"></label>
                        <label class="btn bg-warning p-3"><input type="radio" name="color" value="event-warning" class="d-none"></label>
                        <label class="btn bg-danger p-3"><input type="radio" name="color" value="event-danger" class="d-none"></label>
                        <label class="btn bg-primary p-3"><input type="radio" name="color" value="event-success" class="d-none"></label>
                        <label class="btn p-3" style="background-color:#51459d!important"><input type="radio" name="color" class="d-none" value="event-primary"></label>
                    </div>
                </div>
            </div>

            <div class="{{ VC::FM_G }}">
                {{ Form::label('description', __('Event Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Event Description'), 'rows' => '5']) }}
            </div>

            @if(!empty($settings) && ($settings['google_calendar_enable'] ?? '') === 'on')
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                    <div class="form-switch">
                        <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                        <label class="form-check-label" for="switch-shadow"></label>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer>
        const toast = (msg) => {
            try{
                if(window.bootstrap && window.bootstrap.Toast){
                    let c=document.getElementById('toast-container');
                    if(!c){ c=document.createElement('div'); c.id='toast-container'; c.className='position-fixed bottom-0 end-0 p-3'; document.body.appendChild(c); }
                    const t=document.createElement('div');
                    t.className='toast align-items-center text-bg-danger border-0';
                    t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                    t.innerHTML='<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                    t.querySelector('.toast-body').textContent = msg;
                    c.appendChild(t);
                    bootstrap.Toast.getOrCreateInstance(t,{delay:4000}).show();
                    return;
                }
            }catch(_){}
            alert(msg);
        };

        const guardLink = (el) => {
            if(!el || el.getAttribute('data-listener-active')==='true') return;
            el.setAttribute('data-listener-active','true');
            el.addEventListener('click', (e) => {
                const url = el.getAttribute('data-url');
                if(!url || url === '#'){ e.preventDefault(); toast(el.getAttribute('data-guard-msg') || '#'); }
            }, {passive:false});
        };

        const guardForm = (form) => {
            if(!form || form.getAttribute('data-listener-active')==='true') return;
            form.setAttribute('data-listener-active','true');
            form.addEventListener('submit', (e) => {
                const a = form.getAttribute('action') || form.getAttribute('data-action-url');
                if(!a || a === '#'){ e.preventDefault(); toast(form.getAttribute('data-guard-msg') || '#'); }
            }, {passive:false});
        };

        const populateSelect = (selectEl, items, placeholder) => {
            if(!selectEl) return;
            const opts = [];
            opts.push(new Option(placeholder || '', ''));
            if(Array.isArray(items)){
                items.forEach(it => {
                    const id = (it && (it.id ?? it.value ?? '')) + '';
                    const name = (it && (it.name ?? it.label ?? '')) + '';
                    if(id) opts.push(new Option(name || id, id));
                });
            }else if(items && typeof items === 'object'){
                Object.keys(items).forEach(k => { opts.push(new Option(items[k] ?? k, k)); });
            }
            selectEl.innerHTML = '';
            opts.forEach(o => selectEl.appendChild(o));
        };

        const fetchJSON = async (url, params) => {
            const q = params ? ('?' + new URLSearchParams(params).toString()) : '';
            const res = await fetch(url + q, {headers:{'Accept':'application/json'}});
            if(!res.ok) throw new Error('HTTP '+res.status);
            return await res.json();
        };

        const form = document.getElementById('create_event_form');
        guardForm(form);

        const aiLink = document.getElementById('{{ $genLinkId ?? 'x' }}');
        if(aiLink) guardLink(aiLink);

        const branchSel = document.getElementById('branch_id');
        const depWrap = document.querySelector('.department_div');
        const empWrap = document.querySelector('.employee_div');
        const depSelect = depWrap ? depWrap.querySelector('select.department_id') : null;
        const empSelect = empWrap ? empWrap.querySelector('select.employee_id') : null;

        const depEndpoint = depWrap ? depWrap.getAttribute('data-url') : '#';
        const depGuardMsg = depWrap ? depWrap.getAttribute('data-guard-msg') : '';
        const empEndpoint = empWrap ? empWrap.getAttribute('data-url') : '#';
        const empGuardMsg = empWrap ? empWrap.getAttribute('data-guard-msg') : '';

        if(branchSel && depSelect){
            branchSel.addEventListener('change', async () => {
                const branch_id = branchSel.value || '';
                if(!depEndpoint || depEndpoint === '#'){ toast(depGuardMsg || '#'); populateSelect(depSelect, [], '{{ __('Select Designation') }}'); return; }
                try{
                    const data = await fetchJSON(depEndpoint, {branch_id});
                    populateSelect(depSelect, data, '{{ __('Select Designation') }}');
                    populateSelect(empSelect, [], '{{ __('Select Employee') }}');
                }catch(_){ toast(depGuardMsg || '#'); populateSelect(depSelect, [], '{{ __('Select Designation') }}'); }
            }, {passive:true});
        }

        if(depSelect && empSelect){
            depSelect.addEventListener('change', async () => {
                const branch_id = branchSel ? (branchSel.value || '') : '';
                const department_id = depSelect.value || '';
                if(!empEndpoint || empEndpoint === '#'){ toast(empGuardMsg || '#'); populateSelect(empSelect, [], '{{ __('Select Employee') }}'); return; }
                try{
                    const data = await fetchJSON(empEndpoint, {branch_id, department_id});
                    populateSelect(empSelect, data, '{{ __('Select Employee') }}');
                }catch(_){ toast(empGuardMsg || '#'); populateSelect(empSelect, [], '{{ __('Select Employee') }}'); }
            }, {passive:true});
        }
    </script>
{!! Form::close() !!}
