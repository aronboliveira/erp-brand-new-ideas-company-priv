@php
    try {
$lang       = Utility::fetchUserLang();
        $storeBase  = VW::WBH . '.store';
        $storeKebab = Str::kebab($storeBase);
        $storeName  = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeName ? route($storeName) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::WBH, 'store_webhook_route_unavailable')
            ?? 'Store webhook route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('webhooks/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{!! Form::open([
    'url'                  => $storeUrl,
    'method'               => 'post',
    'id'                   => 'create_webhook',
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('module', __('Module'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('module', ($modules ?? []), null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                    @error('module')
                        <span class="invalid-module" role="alert">
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('url', __('Url'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('url', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Webhook Url')]) }}
                    @error('url')
                        <span class="invalid-name" role="alert">
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('method', __('Method'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('method', ($methods ?? []), null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                    @error('method')
                        <span class="invalid-method" role="alert">
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/webhooks/store.js') }}"></script>
{!! Form::close() !!}
