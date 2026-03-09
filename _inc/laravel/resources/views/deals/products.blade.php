@php
    try {
$lang = Utility::fetchUserLang();

        $routeKey          = ViewsConstants::DL . '.products.update';
        $kebabRouteKey     = Str::kebab($routeKey);
        $hasRoute          = Route::has($routeKey);
        $hasKebab          = Route::has($kebabRouteKey);
        $updateRouteName   = $hasRoute
            ? $routeKey
            : ($hasKebab ? $kebabRouteKey : null);
        $updateRouteArr    = $updateRouteName
            ? [$updateRouteName, $deal->id]
            : ['#'];
        $updateRouteUrl    = $updateRouteName
            ? route($updateRouteName, $deal->id)
            : '#';
        $updateGuardMsg    = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::DL,
            'deal_products_update_route_unavailable'
        ) ?? 'Deal products update route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('deals/products — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if(!empty($deal) && isset($deal->id))
    {!! Form::model($deal, [
        'route'          => $updateRouteArr,
        'method'         => 'PUT',
        'id'             => 'update-products-form-' . $deal->id,
        'data-url'       => $updateRouteUrl,
        'data-guard-msg' => $updateGuardMsg
    ]) !!}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('products', __('Products'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'products',
                        $products,
                        false,
                        [
                            'class'    => VC::FM_CT . ' select2',
                            'id'       => 'choices-multiple1',
                            'multiple' => '',
                            'required' => 'required'
                        ]
                    ) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer>
            (() => {
                const form = document.getElementById('update-products-form-{{ $deal->id }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', e => {
                    try {
                        const url = form.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                        form.setAttribute('data-failed-route', 'true');
                    } catch (error) {}
                });
            })();
        </script>
    {!! Form::close() !!}
@else
    <div class="{{ VC::ALT_WRN }}">{{ __('No deal data available') }}</div>
@endif
