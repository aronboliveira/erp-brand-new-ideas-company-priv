@php
    try {
$info = json_decode((string) data_get($users ?? null,'Details','')) ?: (object)[];
    } catch (\Throwable $e) {
        \Log::error('users/userlogview — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="modal-body">
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Status') }}</b></div>
            <p class="{{ VC::TXT_MT }} {{ VC::MB4 }}">{{ data_get($info,'status') ?: __('No status available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Country') }}</b></div>
            <p class="{{ VC::TXT_MT }} {{ VC::MB4 }}">{{ data_get($info,'country') ?: __('No country available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Country Code') }}</b></div>
            <p class="{{ VC::TXT_MT }} {{ VC::MB4 }}">{{ data_get($info,'countryCode') ?: __('No country code available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Region') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'region') ?: __('No region available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Region Name') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'regionName') ?: __('No region name available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('City') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'city') ?: __('No city available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Zip') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'zip') ?: __('No zip available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Latitude') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'lat') ?? __('No latitude available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Longitude') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'lon') ?? __('No longitude available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Timezone') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'timezone') ?: __('No timezone available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Isp') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'isp') ?: __('No ISP available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Org') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'org') ?: __('No organization available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('As') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'as') ?: __('No AS available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Query') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'query') ?: __('No query available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Browser Name') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'browser_name') ?: __('No browser name available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Os Name') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'os_name') ?: __('No OS name available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Browser Language') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'browser_language') ?: __('No browser language available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Device Type') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'device_type') ?: __('No device type available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Referrer Host') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'referrer_host') ?: __('No referrer host available') }}</p>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_CT_LB }}"><b>{{ __('Referrer Path') }}</b></div>
            <p class="{{ VC::MT1 }}">{{ data_get($info,'referrer_path') ?: __('No referrer path available') }}</p>
        </div>
    </div>
</div>
