@php
    use App\Config\Constants\{ViewClassNamesConstants as VC, ViewsConstants as VW, StacksConstants as ST};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Route};
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $lang          = Utility::fetchUserLang();
    $hasForm       = !empty($form ?? null) && data_get($form, 'id');
    $formId        = 'fm-bind-store-form';

    if ($hasForm) {
        $storeBase       = VW::FM . '.bind.store';
        $storeKebab      = Str::kebab($storeBase);
        $storeResolved   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl        = ($storeResolved && $hasForm) ? route($storeResolved, data_get($form, 'id')) : '#';
        $storeGuardMsg   = Utility::fetchLinkMessage($lang, VW::FM, 'bind_store_route_unavailable') ?? __('Form bind store route is unavailable. Please contact technical support or your domain administrator.');

        $typesIsList     = (is_array($types ?? null) && count($types ?? []) > 0) || (($types ?? null) instanceof Collection && $types->isNotEmpty());
        $typeOptions     = $typesIsList ? (is_array($types) ? $types : $types->toArray()) : [__('No types available')];

        $usersIsList     = (is_array($users ?? null) && count($users ?? []) > 0) || (($users ?? null) instanceof Collection && $users->isNotEmpty());
        $userOptions     = $usersIsList ? (is_array($users) ? $users : $users->toArray()) : [__('No users available')];

        $pipesIsList     = (is_array($pipelines ?? null) && count($pipelines ?? []) > 0) || (($pipelines ?? null) instanceof Collection && $pipelines->isNotEmpty());
        $pipeOptions     = $pipesIsList ? (is_array($pipelines) ? $pipelines : $pipelines->toArray()) : [__('No pipelines available')];

        $leadActiveVal   = (int) data_get($form, 'is_lead_active', 0);
        $leadCls         = $leadActiveVal === 1 ? '' : 'd-none';

        $empIdxBase      = VW::EMP.'.index';
        $empIdxKebab     = Str::kebab($empIdxBase);
        $empIdxResolved  = Route::has($empIdxBase) ? $empIdxBase : (Route::has($empIdxKebab) ? $empIdxKebab : null);
        $empIdxUrl       = $empIdxResolved ? route($empIdxResolved) : '#';
        $empIdxGuardMsg  = Utility::fetchLinkMessage($lang, VW::EMP, 'index_employee_route_unavailable') ?? __('Employee index route is unavailable. Please contact technical support or your domain administrator.');
        $empIdxLinkId    = 'employee-index-link';
    }
@endphp

@if(!$hasForm)
    <div class="alert alert-danger mb-0" role="alert">{{ __('The requested form was not found or is unavailable.') }}</div>
@else
    {{ Form::model($formField ?? null, [
        'url'               => $storeUrl,
        'id'                => $formId,
        'data-url'          => $storeUrl,
        'data-guard-msg'    => $storeGuardMsg,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="col-12 pb-3">
                    <span class="text-xs"><b>{{ __('It will auto convert from response on lead based on below setting. It will not convert old response.') ?: __('Lead conversion notice unavailable') }}</b></span>
                </div>
            </div>

            <div class="row px-2">
                <div class="col-4">
                    <div class="form-group">
                        {{ Form::label('active', __('Active') ?: __('Failed to get label: Active'), ['class' => VC::FM_LB]) }}
                    </div>
                </div>
                <div class="col-8">
                    <div class="d-flex radio-check">
                        <div class="{{ VC::FM_CHK_IL }}">
                            <input type="radio" id="on" value="1" name="is_lead_active" class="form-check-input lead_radio" {{ $leadActiveVal === 1 ? 'checked' : '' }}>
                            <label class="custom-control-label form-label" for="on">{{ __('On') ?: __('Failed to get label: On') }}</label>
                        </div>
                        <div class="{{ VC::FM_CHK_IL }}">
                            <input type="radio" id="off" value="0" name="is_lead_active" class="form-check-input lead_radio" {{ $leadActiveVal === 0 ? 'checked' : '' }}>
                            <label class="custom-control-label form-label" for="off">{{ __('Off') ?: __('Failed to get label: Off') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="lead_activated" class="{{ $leadCls }}">
                <div class="row px-2">
                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('subject_id', __('Subject') ?: __('Failed to get label: Subject'), ['class' => VC::FM_LB]) }}
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            {{ Form::select(
                                'subject_id',
                                $typeOptions,
                                null,
                                array_merge(['class' => VC::FM_CT.' select2', 'data-toggle' => 'select', 'placeholder' => __('Select subject') ?: __('Failed to get placeholder: subject')], $typesIsList ? [] : ['disabled' => 'disabled'])
                            ) }}
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('name_id', __('Name') ?: __('Failed to get label: Name'), ['class' => VC::FM_LB]) }}
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            {{ Form::select(
                                'name_id',
                                $typeOptions,
                                null,
                                array_merge(['class' => VC::FM_CT.' select2', 'data-toggle' => 'select', 'placeholder' => __('Select name') ?: __('Failed to get placeholder: name')], $typesIsList ? [] : ['disabled' => 'disabled'])
                            ) }}
                        </div>
                    </div>

                    <div class="col-4 form-group">
                        {{ Form::label('email_id', __('Email') ?: __('Failed to get label: Email'), ['class' => VC::FM_LB]) }}
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            {{ Form::select(
                                'email_id',
                                $typeOptions,
                                null,
                                array_merge(['class' => VC::FM_CT.' select2', 'data-toggle' => 'select', 'placeholder' => __('Select email') ?: __('Failed to get placeholder: email')], $typesIsList ? [] : ['disabled' => 'disabled'])
                            ) }}
                        </div>
                        {{ Form::hidden('form_id', data_get($form, 'id')) }}
                        {{ Form::hidden('form_response_id', data_get($formField ?? null, 'id', '')) }}
                    </div>

                    <div class="col-4 form-group">
                        {{ Form::label('user_id', __('User') ?: __('Failed to get label: User'), ['class' => VC::FM_LB]) }}
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            {{ Form::select(
                                'user_id',
                                $userOptions,
                                null,
                                array_merge(['class' => VC::FM_CT.' select2', 'data-toggle' => 'select', 'placeholder' => __('Select user') ?: __('Failed to get placeholder: user')], $usersIsList ? [] : ['disabled' => 'disabled'])
                            ) }}
                            @unless($usersIsList)
                                <div class="text-muted text-xs">
                                    {{ __('Please create new employee') }}
                                    <a
                                        id="{{ $empIdxLinkId }}"
                                        href="{{ $empIdxUrl }}"
                                        data-url="{{ $empIdxUrl }}"
                                        data-guard-msg="{{ $empIdxGuardMsg }}"
                                        data-sv-localized="true"
                                    >{{ __('here') }}</a>.
                                </div>
                            @endunless
                        </div>
                    </div>

                    <div class="col-4 form-group">
                        {{ Form::label('pipeline_id', __('Pipelines') ?: __('Failed to get label: Pipelines'), ['class' => VC::FM_LB]) }}
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            {{ Form::select(
                                'pipeline_id',
                                $pipeOptions,
                                null,
                                array_merge(['class' => VC::FM_CT.' select2', 'data-toggle' => 'select', 'placeholder' => __('Select pipeline') ?: __('Failed to get placeholder: pipeline')], $pipesIsList ? [] : ['disabled' => 'disabled'])
                            ) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') ?: __('Failed to get label: Create') }}" class="btn btn-primary">
        </div>
        <script async src="{{ asset('assets/js/routes/formBuilders/lang/formField.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/formBuilders/bindStore.js') }}"></script>
        <script defer>
            (() => {
                const ERROR_KEY = 'lead_toggle_failed';
                const HIDE_CLASS = 'd-none';
                const RADIO_SELECTOR = '.lead_radio';
                const TARGET_ID = 'lead_activated';
                const ATTR_ACTIVE = 'data-listener-active';
        
                const showError = msg => {
                    const toastEl = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast
                    ? (() => {
                        const t = document.createElement('div');
                        t.className = 'toast align-items-center text-white bg-danger border-0';
                        t.setAttribute('role','alert');
                        t.innerHTML = `<div class="d-flex">
                                        <div class="toast-body">${msg}</div>
                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                                        </div>`;
                        document.body.append(t);
                        return t;
                        })()
                    : null;
                    if (toastEl) {
                    new bootstrap.Toast(toastEl).show();
                    } else {
                    alert(msg);
                    }
                };
        
                const getMsg = key => {
                    let lang = (sessionStorage.getItem('erp-np-lang') 
                            || document.documentElement.lang 
                            || 'en')
                            .toLowerCase().replace(/_/g,'-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    return window.translations?.[lang]?.[key]
                        || window.translations?.['en']?.[key]
                        || '# ERROR';
                };
        
                const init = () => {
                    const target = document.getElementById(TARGET_ID);
                    if (!target) return;
        
                    try {
                    const leadActive = Number.parseInt('{{$form->is_lead_active}}', 10);
                    if (leadActive === 1) target.classList.remove(HIDE_CLASS);
                    } catch {
                    showError(getMsg(ERROR_KEY));
                    }
        
                    document.addEventListener('click', onClick);
                };
        
                const onClick = e => {
                    const radios = document.querySelectorAll(RADIO_SELECTOR);
                    if ([...radios].some(r => r.contains(e.target) || r === e.target)) {
                    radios.forEach(r => {
                        if (r.getAttribute(ATTR_ACTIVE) !== 'true') {
                        r.setAttribute(ATTR_ACTIVE, 'true');
                        r.addEventListener('click', onRadioClick);
                        }
                    });
                    new MutationObserver((_, obs) => {
                        if (![...radios].some(r => document.body.contains(r))) {
                        radios.forEach(r => r.removeEventListener('click', onRadioClick));
                        obs.disconnect();
                        }
                    }).observe(document.body, { childList: true, subtree: true });
                    }
                };
        
                const onRadioClick = e => {
                    try {
                    const val = e.currentTarget.value;
                    const target = document.getElementById(TARGET_ID);
                    if (!target) throw new Error();
                    if (val === '1') {
                        target.classList.remove(HIDE_CLASS);
                    } else {
                        target.classList.add(HIDE_CLASS);
                    }
                    document.querySelectorAll(RADIO_SELECTOR)
                        .forEach(r => r.checked = false);
                    e.currentTarget.checked = true;
                    } catch {
                    showError(getMsg(ERROR_KEY));
                    }
                };
                
                document.readyState === 'loading'
                ? document.addEventListener('DOMContentLoaded', init)
                : init();
            })();
        </script>
    {{ Form::close() }}

@endif