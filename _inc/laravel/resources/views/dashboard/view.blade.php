@php
    use App\Config\Constants\ViewClassNamesConstants as VC;

    $users = isset($users) && !empty($users) ? $users : [];
@endphp
@if(count($users) > 0)
    @foreach($users as $usr)
        @php
            $contacts      = data_get($usr, 'contacts', []);
            $projects      = data_get($usr, 'projects', []);
            $contactsCount = is_countable($contacts) ? count($contacts) : (method_exists($contacts, 'count') ? $contacts->count() : 0);
            $projectsCount = is_countable($projects) ? count($projects) : (method_exists($projects, 'count') ? $projects->count() : 0);
        @endphp
        <div class="{{ VC::CL3 }} {{ VC::CS6 }}">
            <div class="{{ VC::CD }} hover-shadow-lg">
                <div class="card-body text-center">
                    <div class="avatar-parent-child">
                        <img {{ $usr->img_avatar }} class="{{ VC::AV_CC }} avatar-lg" alt="{{ __('avatar') }}">
                    </div>
                    <h5 class="{{ VC::MB0 }} {{ VC::H6 }} {{ VC::MT3 }}">
                        <span>{{ $usr?->name }}</span>
                    </h5>
                    <p class="{{ VC::DBL }} {{ VC::TXSM }} {{ VC::TXT_MT }} {{ VC::MB3 }}">{{ $usr?->email }}</p>
                </div>
                <div class="card-body {{ VC::BD }}-top">
                    <div class="row justify-content-between {{ VC::ALC }}">
                        <div class="col-6 text-center">
                            <span class="d-block h4 {{ VC::MB0 }}">{{ $contactsCount }}</span>
                            <span class="{{ VC::DBL }} {{ VC::TXSM }} {{ VC::TXT_MT }}">{{ __('Contacts') }}</span>
                        </div>
                        <div class="col-6 text-center">
                            <span class="d-block h4 {{ VC::MB0 }}">{{ $projectsCount }}</span>
                            <span class="{{ VC::DBL }} {{ VC::TXSM }} {{ VC::TXT_MT }}">{{ __('Projects') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="{{ VC::C12 }}">
        <div class="{{ VC::CD }}">
            <div class="card-body">
                <h6 class="text-center {{ VC::MB0 }}">{{ __('No User Found.') }}</h6>
            </div>
        </div>
    </div>
@endif
