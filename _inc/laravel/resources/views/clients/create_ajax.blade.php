@php
    try {
$lang = Utility::fetchUserLang();
        $storeRouteName = VW::CLT;

        $storeGuard = Utility::fetchLinkMessage($lang, VW::CLT, 'store_route_unavailable')
            ?? __('Store client route is unavailable. Please contact technical support or your domain administrator.');

        $formParams = [
            'method'             => 'post',
            'id'                 => 'store_client',
            'data-sv-localized'  => 'true',
            'data-guard-msg'     => $storeGuard,
            'data-action-href'   => Route::has($storeRouteName) ? route($storeRouteName) : '#',
        ];

        if (Route::has($storeRouteName)) {
            $formParams['route'] = [$storeRouteName];
        } else {
            $formParams['url'] = '#';
        }
    } catch (\Throwable $e) {
        \Log::error('clients/create_ajax — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="{{ VC::CD_BGN_BX }}">
    {!! Form::open($formParams) !!}
        <div class="{{ VC::RW }}">
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                @error('name')
                    <small class="invalid-name" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></small>
                @enderror
            </div>

            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('email', __('E-Mail Address'), ['class' => VC::FM_LB]) }}
                {{ Form::email('email', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                @error('email')
                    <small class="invalid-email" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></small>
                @enderror
            </div>

            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('password', __('Password'), ['class' => VC::FM_LB]) }}
                {{ Form::password('password', ['class' => VC::FM_CT, 'required' => 'required']) }}
                @error('password')
                    <small class="invalid-password" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></small>
                @enderror
            </div>

            <div class="{{ VC::FM_G }} {{ VC::MT4 }} {{ VC::MB0 }}">
                {{ Form::hidden('ajax', true) }}
                <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
            </div>
        </div>

        <script defer src="{{ asset('assets/js/routes/clients/store.js') }}"></script>
    {!! Form::close() !!}
</div>
