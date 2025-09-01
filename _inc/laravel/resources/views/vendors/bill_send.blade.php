@php
    use App\Config\Constants\{
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};

    $lang   = Utility::fetchUserLang();
    $billId = (string) ($bill_id ?? '');

    $sendMailBase  = VW::VND . '.bill.send.mail';
    $sendMailKebab = Str::kebab($sendMailBase);
    $sendMailName  = Route::has($sendMailBase) ? $sendMailBase : (Route::has($sendMailKebab) ? $sendMailKebab : null);
    $sendMailUrl   = ($sendMailName && $billId) ? route($sendMailName, [$billId]) : '#';
    $sendMailGuard = Utility::fetchLinkMessage($lang, VW::VND, 'send_vendor_bill_mail_route_unavailable')
        ?? 'Send vendor bill mail route is unavailable. Please contact technical support or your domain administrator.';
    $formId = 'vendor-bill-send-mail-form';
@endphp

<div class="card bg-none card-box">
    {!! Form::open([
        'url'                  => $sendMailUrl,
        'method'               => 'POST',
        'id'                   => $formId,
        'data-resolved-action' => $sendMailUrl,
        'data-guard-msg'       => $sendMailGuard,
        'data-sv-localized'    => 'true',
    ]) !!}
        <div class="row">
            <div class="form-group col-md-12">
                {{ Form::label('email', __('Email')) }}
                {{ Form::text('email', '', ['class' => 'form-control', 'required' => 'required']) }}
                @error('email')
                    <span class="invalid-email" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-md-12 px-0">
            <input type="submit" value="{{ __('Create') }}" class="btn-create badge-blue">
            <input type="button" value="{{ __('Cancel') }}" class="btn-create bg-gray" data-dismiss="modal">
        </div>

        <script defer src="{{ asset('assets/js/routes/vendors/bills/sendMail.js') }}"></script>
    {!! Form::close() !!}
</div>
