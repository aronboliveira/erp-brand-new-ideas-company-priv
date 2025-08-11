@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };

    $lang = Utility::fetchUserLang();
    $createName     = ViewsConstants::CPT . '.store';
    $createRoute    = Route::has($createName)
        ? route($createName)
        : '#';
    $createFormId   = 'competencyCreateForm';
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPT,
        'competency_store_route_unavailable'
    ) ?? 'Competency store route is unavailable. Please contact technical support or your domain administrator.';

    $updateName     = ViewsConstants::CPT . '.update';
    $updateRoute    = Route::has($updateName)
        ? route($updateName, $competencies->id)
        : '#';
    $updateFormId   = 'competencyUpdateForm_' . $competencies->id;
    $updateGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPT,
        'competency_update_route_unavailable'
    ) ?? 'Competency update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{-- Create Competency --}}
{{ Form::open([
    'route'          => [ViewsConstants::CPT . '.store'],
    'method'         => 'post',
    'id'             => $createFormId,
    'data-url'       => $createRoute,
    'data-guard-msg' => $createGuardMsg,
]) }}
<div class="modal-body">
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $performance, null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
</div>
{{ Form::close() }}

{{-- Update Competency --}}
{{ Form::model($competencies, [
    'route'          => [ViewsConstants::CPT . '.update', $competencies->id],
    'method'         => 'PUT',
    'id'             => $updateFormId,
    'data-url'       => $updateRoute,
    'data-guard-msg' => $updateGuardMsg,
]) }}
<div class="modal-body">
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $performance, null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
</div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const bindGuard = (el, event, urlAttr = 'data-url', msgAttr = 'data-guard-msg') => {
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener(event, e => {
                    try {
                        const url = el.getAttribute(urlAttr) ?? '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg           = el.getAttribute(msgAttr) ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl      = document.createElement('div');
                            toastEl.className  = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body         = document.createElement('div');
                            body.className     = 'toast-body';
                            body.textContent   = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch {}
                });
            };

            bindGuard(document.getElementById('{{ $createFormId }}'), 'submit');
            bindGuard(document.getElementById('{{ $updateFormId }}'), 'submit');
        })();
    </script>
@endpush
