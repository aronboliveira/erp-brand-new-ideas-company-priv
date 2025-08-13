@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang                         = Utility::fetchUserLang();
    $ttSendMailBaseName           = ViewsConstants::TT . '.send.mail';
    $ttSendMailKebabName          = Str::kebab($ttSendMailBaseName);
    $ttSendMailResolvedName       = Route::has($ttSendMailBaseName)
        ? $ttSendMailBaseName
        : (Route::has($ttSendMailKebabName) ? $ttSendMailKebabName : null);
    $ttSendMailActionUrl          = $ttSendMailResolvedName ? route($ttSendMailResolvedName) : '#';
    $ttSendMailGuardMsg           = Utility::fetchLinkMessage($lang, ViewsConstants::TT, 'send_test_mail_route_unavailable') ?? 'Send mail test route is unavailable. Please contact technical support or your domain administrator.';
    $ttSendMailFormId             = 'test_email';
@endphp
    {{--{{ Collective\Html\FormFacade::open(array('route' => array('ViewsConstants::TT . .send.mail'))) }}--}}
    {{--<div class="modal-body">--}}
    {{--    <div class="row">--}}
    {{--        <div class="form-group col-md-12">--}}
    {{--            {{ Collective\Html\FormFacade::label('email', __('Email'),['class'=>'form-label']) }}--}}
    {{--            {{ Collective\Html\FormFacade::text('email', '', array('class' => 'form-control','required'=>'required')) }}--}}
    {{--            @error('email')--}}
    {{--            <span class="invalid-email" role="alert">--}}
    {{--            <strong class="text-danger">{{ $message }}</strong>--}}
    {{--        </span>--}}
    {{--            @enderror--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--</div>--}}
    {{--<div class="modal-footer">--}}
    {{--    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">--}}
    {{--    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">--}}
    {{--</div>--}}
    {{--{{ Collective\Html\FormFacade::close() }}--}}


<form
    class="{{ VC::PX3 }}"
    method="post"
    action="{{ $ttSendMailActionUrl }}"
    id="{{ $ttSendMailFormId }}"
    data-url="{{ $ttSendMailActionUrl }}"
    data-guard-msg="{{ $ttSendMailGuardMsg }}"
>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const form = document.getElementById('{{ $ttSendMailFormId }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', e => {
                    try {
                        const url = form.getAttribute('data-url') || '#';
                        const action = form.getAttribute('action') || '#';
                        if (url !== '#' || action !== '#') return;
                        e.preventDefault();
                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
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
                        form.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                });
            })();
        </script>
    @endpush
    @csrf
    <input type="hidden" name="mail_driver" value="{{ $data['mail_driver'] }}" />
    <input type="hidden" name="mail_host" value="{{ $data['mail_host'] }}" />
    <input type="hidden" name="mail_port" value="{{ $data['mail_port'] }}" />
    <input type="hidden" name="mail_username" value="{{ $data['mail_username'] }}" />
    <input type="hidden" name="mail_password" value="{{ $data['mail_password'] }}" />
    <input type="hidden" name="mail_encryption" value="{{ $data['mail_encryption'] }}" />
    <input type="hidden" name="mail_from_address" value="{{ $data['mail_from_address'] }}" />
    <input type="hidden" name="mail_from_name" value="{{ $data['mail_from_name'] }}" />
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} col-md-12">
                <label for="email" class="{{ VC::FM_LB }}">{{ __('E-Mail Address') }}</label>
                <input type="text" class="{{ VC::FM_CT }}" id="email" name="email" required />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        {{-- <label id="email_sending" style="display: none;"><i class="fas fa-clock"></i></label> --}}
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Send') }}" class="btn-create {{ VC::BT_PRM }}">
    </div>
</form>
