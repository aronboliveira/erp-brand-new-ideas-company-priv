@php
    $data ??= [];
    try {
$lang                         = Utility::fetchUserLang();
        $ttSendMailBaseName           = ViewsConstants::TT . '.send.mail';
        $ttSendMailKebabName          = Str::kebab($ttSendMailBaseName);
        $ttSendMailResolvedName       = Route::has($ttSendMailBaseName)
            ? $ttSendMailBaseName
            : (Route::has($ttSendMailKebabName) ? $ttSendMailKebabName : null);
        $ttSendMailActionUrl          = $ttSendMailResolvedName ? route($ttSendMailResolvedName) : '#';
        $ttSendMailGuardMsg           = Utility::fetchLinkMessage($lang, ViewsConstants::TT, 'send_test_mail_route_unavailable') ?? 'Send mail test route is unavailable. Please contact technical support or your domain administrator.';
        $ttSendMailFormId             = 'test_email';
    } catch (\Throwable $e) {
        \Log::error('settings/test_mail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
    {{--{{ Collective\Html\FormFacade::open(array('route' => array('ViewsConstants::TT . .send.mail'))) }}--}}
    {{--<div class="modal-body">--}}
    {{--    <div class="row">--}}
    {{--        <div class="{{ VC::FM_GCB12 }}">--}}
    {{--            {{ Collective\Html\FormFacade::label('email', __('Email'),['class'=>'form-label']) }}--}}
    {{--            {{ Collective\Html\FormFacade::text('email', '', array('class' => 'form-control','required'=>'required')) }}--}}
    {{--            @error('email')--}}
    {{--            <span class="invalid-email" role="alert">--}}
    {{--            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>--}}
    {{--        </span>--}}
    {{--            @enderror--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--</div>--}}
    {{--<div class="modal-footer">--}}
    {{--    <input type="button" value="{{__('Cancel')}}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">--}}
    {{--    <input type="submit" value="{{__('Create')}}" class="{{ VC::BT_PRM }}">--}}
    {{--</div>--}}
    {{--{{ Collective\Html\FormFacade::close() }}--}}
<form
    class="{{ VC::PX3 }}"
    method="post"
    action="{{ $ttSendMailActionUrl }}"
    id="{{ $ttSendMailFormId }}"
    data-url="{{ $ttSendMailActionUrl }}"
    data-guard-msg="{{ base64_encode($ttSendMailGuardMsg) }}"
>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{asset('assets/js/routes/settings/testMail.js')}}"></script>
    @endpush
    @csrf
    <input type="hidden" name="mail_driver" value="{{ $data['mail_driver'] ?? '' }}" />
    <input type="hidden" name="mail_host" value="{{ $data['mail_host'] ?? '' }}" />
    <input type="hidden" name="mail_port" value="{{ $data['mail_port'] ?? '' }}" />
    <input type="hidden" name="mail_username" value="{{ $data['mail_username'] ?? '' }}" />
    <input type="hidden" name="mail_password" value="{{ $data['mail_password'] ?? '' }}" />
    <input type="hidden" name="mail_encryption" value="{{ $data['mail_encryption'] ?? '' }}" />
    <input type="hidden" name="mail_from_address" value="{{ $data['mail_from_address'] ?? '' }}" />
    <input type="hidden" name="mail_from_name" value="{{ $data['mail_from_name'] ?? '' }}" />
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
