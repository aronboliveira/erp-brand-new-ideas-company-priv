@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp

<div class="card bg-none card-box">
    {{ Collective\Html\FormFacade::open(['url' => 'clients']) }}
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="col-6 {{ ViewClassNamesConstants::FM_G }}">
            {{ Collective\Html\FormFacade::label(
                'name',
                __('Name'),
                ['class' => ViewClassNamesConstants::FM_LB]
            ) }}
            {{ Collective\Html\FormFacade::text(
                'name',
                null,
                [
                    'class'    => ViewClassNamesConstants::FM_CT,
                    'required' => 'required',
                ]
            ) }}
        </div>
        <div class="col-6 {{ ViewClassNamesConstants::FM_G }}">
            {{ Collective\Html\FormFacade::label(
                'email',
                __('E-Mail Address'),
                ['class' => ViewClassNamesConstants::FM_LB]
            ) }}
            {{ Collective\Html\FormFacade::email(
                'email',
                null,
                [
                    'class'    => ViewClassNamesConstants::FM_CT,
                    'required' => 'required',
                ]
            ) }}
        </div>
        <div class="col-6 {{ ViewClassNamesConstants::FM_G }}">
            {{ Collective\Html\FormFacade::label(
                'password',
                __('Password'),
                ['class' => ViewClassNamesConstants::FM_LB]
            ) }}
            {{ Collective\Html\FormFacade::password(
                'password',
                [
                    'class'    => ViewClassNamesConstants::FM_CT,
                    'required' => 'required',
                ]
            ) }}
        </div>
        <div class="{{ ViewClassNamesConstants::FM_G }} {{ ViewClassNamesConstants::MT4 }} {{ ViewClassNamesConstants::MB0 }}">
            {{ Collective\Html\FormFacade::hidden('ajax', true) }}
            <input
                type="submit"
                value="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_PRM }}"
            >
        </div>
    </div>
    {{ Collective\Html\FormFacade::close() }}
</div>
