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

    $lang       = Utility::fetchUserLang();
    $routeName  = ViewsConstants::CPL . '.store';
    $storeRoute = Route::has($routeName)
        ? route($routeName)
        : (Route::has(Str::kebab($routeName))
            ? route(Str::kebab($routeName))
            : '#');
    $formId     = 'complaintCreateForm';
    $guardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPL,
        'complaint_store_route_unavailable'
    ) ?? 'Complaint store route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'route'          => [ViewsConstants::CPL . '.store'],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
<div class="{{ VC::RW }} modal-body">
    @php $plan = \App\Models\Utility::getChatGPTSettings(); @endphp
    @if($plan->chatgpt == 1)
        <div class="{{ VC::FEND }}">
            <a href="#"
               data-size="md"
               class="{{ VC::BT_PRM }} {{ VC::BT_SM }} btn-icon"
               data-ajax-popup-over="true"
               data-url="{{ route('generate', ['complaint']) }}"
               data-bs-placement="top"
               data-title="{{ __('Generate content with AI') }}">
                <i class="fas fa-robot"></i>
                <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif

    <div class="{{ VC::RW }}">
        @if(\Auth::user()->type != 'employee')
            <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
                {{ Form::label('complaint_from', __('Complaint From'), ['class' => VC::FM_LB]) }}
                {{ Form::select('complaint_from', $employees, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
            </div>
        @endif

        <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
            {{ Form::label('complaint_against', __('Complaint Against'), ['class' => VC::FM_LB]) }}
            {{ Form::select('complaint_against', $employees, null, ['class' => VC::FM_CT_SL]) }}
        </div>

        <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
            {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
            {{ Form::text('title', null, ['class' => VC::FM_CT]) }}
        </div>

        <div class="{{ VC::FM_G }} col-md-6 col-lg-6">
            {{ Form::label('complaint_date', __('Complaint Date'), ['class' => VC::FM_LB]) }}
            {{ Form::date('complaint_date', null, ['class' => VC::FM_CT]) }}
        </div>

        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
            {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        {{ __('Cancel') }}
    </button>
    <button type="submit" class="{{ VC::BT_PRM }}">
        {{ __('Create') }}
    </button>
</div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', event => {
                try {
                    const url = form.getAttribute('data-url');
                    if (url && url !== '#') return;
                    event.preventDefault();
                    const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
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
                    form.setAttribute('data-failed-route', 'true');
                } catch (e) {}
            });
        })();
    </script>
@endpush
