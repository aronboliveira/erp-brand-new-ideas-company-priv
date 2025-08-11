@php
    use App\Config\Constants\{
        PlansConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::ANC.'.update')
        ? route(ViewsConstants::ANC.'.update', $announcement->id)
        : '#';
    $formId = 'announcement-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ANC,
        'announcement_update_route_unavailable'
    ) ?? 'Announcement update route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{{ Form::model($announcement, [
    'route'             => [ViewsConstants::ANC.'.update', $announcement->id],
    'method'            => 'PUT',
    'id'                => $formId,
    'data-url'          => $updateRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $updateMsg,
]) }}
    <div class="modal-body">
        @php $plan=\App\Models\Utility::getChatGPTSettings(); @endphp
        @if($plan->chatgpt==1)
            @php
                $lang = Utility::fetchUserLang();
                $aiGenerateRoute = Route::has('generate')
                    ? route('generate', ['announcement'])
                    : '#';
                $aiGenerateId = 'announcement-ai-generate-link';
                $aiGenerateMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::ANC,
                    'announcement_generate_route_unavailable'
                ) ?? 'Generate with AI route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="{{ ViewClassNamesConstants::DFL_JCE }}">
                <a href="#"
                data-size="md"
                class="{{ ViewClassNamesConstants::BT_SM_PM }} btn-icon btn-sm"
                data-ajax-popup-over="true"
                data-url="{{ route('generate',['announcement']) }}"
                data-bs-placement="top"
                title="{{ __('Generate content with AI') }}">
                    <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('title',__('Announcement Title'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::text('title',null,['class'=>ViewClassNamesConstants::FM_CT,'placeholder'=>__('Enter Announcement Title')]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('branch_id',__('Branch'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::select('branch_id',$branch,null,['class'=>ViewClassNamesConstants::FM_CT_SL]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('department_id',__('Department'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::select('department_id',$departments,null,['class'=>ViewClassNamesConstants::FM_CT_SL]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('start_date',__('Announcement Start Date'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::date('start_date',null,['class'=>ViewClassNamesConstants::FM_CT]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('end_date',__('Announcement End Date'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::date('end_date',null,['class'=>ViewClassNamesConstants::FM_CT]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('description',__('Announcement Description'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::textarea('description',null,['class'=>ViewClassNamesConstants::FM_CT,'placeholder'=>__('Enter Announcement Description')]) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
{{ Form::close() }}
<script defer>
    (() => {
        const form = document.getElementById('{{ $formId }}');
        if (!form || form.getAttribute('data-listener-active') === 'true') return;
        form.setAttribute('data-listener-active', 'true');
        form.addEventListener('submit', event => {
            try {
                const action = form.getAttribute('action');
                const url    = form.getAttribute('data-url');
                if ((!action || action === '#') && (!url || url === '#')) {
                    event.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bootstrapLink && window.bootstrap) {
                        const toastEl = document.createElement('div');
                        toastEl.className = 'toast';
                        toastEl.setAttribute('role', 'alert');
                        toastEl.setAttribute('aria-live', 'assertive');
                        toastEl.setAttribute('aria-atomic', 'true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toastEl.appendChild(body);
                        container.appendChild(toastEl);
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    } else {
                        alert(msg);
                    }
                }
            } catch {}
        });
        const observer = new MutationObserver(() => {
            if (!document.getElementById('{{ $formId }}')) observer.disconnect();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>