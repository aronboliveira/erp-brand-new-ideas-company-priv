@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\StacksConstants;

    $lang                 = Utility::fetchUserLang();
    $generateAiRoute      = Route::has('generate')
        ? route('generate', ['coupon'])
        : '#';
    $generateAiBtnId      = 'coupon-update-ai-btn';
    $generateAiGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        'generics',
        'coupon_generate_ai_route_unavailable'
    ) ?? 'AI generate route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($coupon, ['route' => [ViewsConstants::CPN . '.update', $coupon->id], 'method' => 'PUT']) }}
<div class="modal-body">
    @php
        $settings = \App\Models\Utility::settings();
    @endphp
    @if(!empty($settings['chat_gpt_key']))
        <div class="{{ VC::FEND }}">
            <a
                id="{{ $generateAiBtnId }}"
                href="#"
                data-url="{{ $generateAiRoute }}"
                data-guard-msg="{{ $generateAiGuardMsg }}"
                data-ajax-popup-over="true"
                data-size="md"
                class="{{ VC::BT_SM_PM }} btn-icon"
                data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}"
                data-bs-toggle="tooltip"
            >
                <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif
    {{-- end for ai module --}}

    <div class="{{ VC::RW }}">
        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
            {{ Form::text('name', null, ['class' => VC::FM_CT . ' font-style', 'required' => 'required']) }}
        </div>
        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
            {{ Form::label('discount', __('Discount'), ['class' => VC::FM_LB]) }}
            {{ Form::number('discount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
            <span class="small">{{ __('Note: Discount in Percentage') }}</span>
        </div>
        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
            {{ Form::label('limit', __('Limit'), ['class' => VC::FM_LB]) }}
            {{ Form::number('limit', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
        </div>
        <div class="{{ VC::FM_G }} col-md-12">
            {{ Form::label('code', __('Code'), ['class' => VC::FM_LB]) }}
            {{ Form::text('code', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
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
            const btn = document.getElementById('{{ $generateAiBtnId }}');
            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
            btn.setAttribute('data-listener-active', 'true');
            btn.addEventListener('click', event => {
                try {
                    const url = btn.getAttribute('data-url');
                    if (!url || url === '#') {
                        event.preventDefault();
                        const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                        btn.setAttribute('data-failed-route', 'true');
                        return;
                    }
                } catch (e) {}
            });
        })();
    </script>
@endpush
