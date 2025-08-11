@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp
{{ Collective\Html\FormFacade::model($formField, array('route' => array('form.bind.store', $form->id))) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 pb-3">
            <span class="text-xs"><b>{{__('It will auto convert from response on lead based on below setting. It will not convert old response.')}}</b></span>
        </div>
    </div>
    <div class="row px-2">
        <div class="col-4">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('active', __('Active'),['class'=>'form-label']) }}
            </div>
        </div>
        <div class="col-8">
            <div class="d-flex radio-check">
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input type="radio" id="on" value="1" name="is_lead_active" class="form-check-input lead_radio" {{($form->is_lead_active == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label form-label" for="on">{{__('On')}}</label>
                </div>
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input type="radio" id="off" value="0" name="is_lead_active" class="form-check-input lead_radio" {{($form->is_lead_active == 0) ? 'checked' : ''}}>
                    <label class="custom-control-label form-label" for="off">{{__('Off')}}</label>
                </div>
            </div>
        </div>
    </div>
    <div id="lead_activated" class="d-none">
        <div class="row px-2">
            <div class="col-4">
                <div class="form-group">
                     {{Collective\Html\FormFacade::label('subject_id', __('Subject'),['class'=>'form-label']) }}
                </div>
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('subject_id', $types,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('name_id', __('Name'),['class'=>'form-label']) }}
                </div>
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('name_id', $types,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
            </div>
            <div class="col-4 form-group">
                {{ Collective\Html\FormFacade::label('email_id', __('Email'),['class'=>'form-label']) }}
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('email_id', $types,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
                {{ Collective\Html\FormFacade::hidden('form_id',$form->id) }}
                {{ Collective\Html\FormFacade::hidden('form_response_id',(!empty($formField))? $formField->id : '') }}
            </div>
            <div class="col-4 form-group">
                {{ Collective\Html\FormFacade::label('user_id', __('User'),['class'=>'form-label']) }}
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('user_id', $users,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                    @if(count($users) == 0)
                        <div class="text-muted text-xs">
                            {{__('Please create new employee')}} <a href="{{ route('employee.index') }}" >{{__('here')}}</a>.
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-4 form-group">
                {{ Collective\Html\FormFacade::label('pipeline_id', __('Pipelines'),['class'=>'form-label']) }}
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('pipeline_id', $pipelines,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}


<script async>
  window.translations = {
    ar: {
      lead_toggle_failed: 'فشل تبديل حالة العميل المحتمل.'
    },
    da: {
      lead_toggle_failed: 'Kunne ikke ændre lead-tilstand.'
    },
    de: {
      lead_toggle_failed: 'Lead-Zustand konnte nicht geändert werden.'
    },
    en: {
      lead_toggle_failed: 'Failed to toggle lead status.'
    },
    es: {
      lead_toggle_failed: 'Error al cambiar el estado del lead.'
    },
    fr: {
      lead_toggle_failed: 'Échec du basculement de l’état du lead.'
    }
  };
</script>
<script>
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