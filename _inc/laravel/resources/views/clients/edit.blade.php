@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp

{{ Collective\Html\FormFacade::model($client, ['route' => ['clients.update', $client->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::FM_G }}">
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
                    'placeholder' => __('Enter Client Name'),
                    'required'    => 'required',
                ]
            ) }}
        </div>
        <div class="{{ ViewClassNamesConstants::FM_G }}">
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

        @if(!$customFields->isEmpty())
            @include('custom_fields.formBuilder')
        @endif
    </div>
</div>
<div class="modal-footer">
    <button
        type="button"
        class="{{ ViewClassNamesConstants::BT_LG }}"
        data-bs-dismiss="modal"
    >{{ __('Cancel') }}</button>
    <button
        type="submit"
        class="{{ ViewClassNamesConstants::BT_PRM }}"
    >{{ __('Update') }}</button>
</div>
{{ Collective\Html\FormFacade::close() }}
