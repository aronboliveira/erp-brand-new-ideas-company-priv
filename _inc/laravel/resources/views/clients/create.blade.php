@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp

{{ Collective\Html\FormFacade::open(['url' => 'clients']) }}
<div class="modal-body">
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(
                'name',
                __('Name'),
                ['class' => ViewClassNamesConstants::FM_LB]
            ) }}
            {{ Collective\Html\FormFacade::text(
                'name',
                null,
                [
                    'class'       => ViewClassNamesConstants::FM_CT,
                    'placeholder' => __('Enter client Name'),
                    'required'    => 'required',
                ]
            ) }}
        </div>
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(
                'email',
                __('E-Mail Address'),
                ['class' => ViewClassNamesConstants::FM_LB]
            ) }}
            {{ Collective\Html\FormFacade::email(
                'email',
                null,
                [
                    'class'       => ViewClassNamesConstants::FM_CT,
                    'placeholder' => __('Enter Client Email'),
                    'required'    => 'required',
                ]
            ) }}
        </div>
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(
                'password',
                __('Password'),
                ['class' => ViewClassNamesConstants::FM_LB]
            ) }}
            {{ Collective\Html\FormFacade::password(
                'password',
                [
                    'class'       => ViewClassNamesConstants::FM_CT,
                    'placeholder' => __('Enter User Password'),
                    'required'    => 'required',
                    'minlength'   => 6,
                ]
            ) }}
            @error('password')
                <small class="invalid-password" role="alert">
                    <strong class="{{ ViewClassNamesConstants::TXT_MT }}">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        @if (! $customFields->isEmpty())
            @include('custom_fields.formBuilder')
        @endif
    </div>
</div>
<div class="modal-footer">
    <input
        type="button"
        value="{{ __('Cancel') }}"
        class="{{ ViewClassNamesConstants::BT_LG }}"
        data-bs-dismiss="modal"
    >
    <input
        type="submit"
        value="{{ __('Create') }}"
        class="{{ ViewClassNamesConstants::BT_PRM }}"
    >
</div>
{{ Collective\Html\FormFacade::close() }}
