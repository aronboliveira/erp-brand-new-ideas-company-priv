@php
    try {
$lang = Utility::fetchUserLang();

        $routeKey         = ViewsConstants::DL . '.sources.update';
        $kebabRouteKey    = Str::kebab($routeKey);
        $hasRoute         = Route::has($routeKey);
        $hasKebab         = Route::has($kebabRouteKey);
        $updateRouteName  = $hasRoute
            ? $routeKey
            : ($hasKebab ? $kebabRouteKey : null);
        $updateRouteArr   = $updateRouteName
            ? [$updateRouteName, $deal->id]
            : ['#'];
        $updateRouteUrl   = $updateRouteName
            ? route($updateRouteName, $deal->id)
            : '#';
        $updateGuardMsg   = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::DL,
            'deal_sources_update_route_unavailable'
        ) ?? 'Deal sources update route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('deals/sources — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if(!empty($deal) && isset($deal->id))
    {!! Form::model($deal, [
        'route'          => $updateRouteArr,
        'method'         => 'PUT',
        'id'             => 'update-sources-form-' . $deal->id,
        'data-url'       => $updateRouteUrl,
        'data-guard-msg' => $updateGuardMsg
    ]) !!}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    <div class="row gutters-xs">
                        @foreach ($sources as $source)
                            <div class="{{ VC::C12 }} {{ VC::CST_CT_CB }} {{ VC::MT2 }} {{ VC::MB2 }}">
                                {{ Form::checkbox(
                                    'sources[' . $source->id . ']',
                                    $source->id,
                                    ($selected && array_key_exists($source->id, !empty($selected) ?? [])) ? true : false,
                                    ['class' => 'form-check-input', 'id' => 'sources_' . $source->id]
                                ) }}
                                {{ Form::label(
                                    'sources_' . $source->id,
                                    ucfirst(isset($source->name) ? $source->name : __('No name available for source')),
                                    ['class' => 'custom-control-label ml-4 text-sm font-weight-bold']
                                ) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer>
            (() => {
                const form = document.getElementById('update-sources-form-{{ $deal->id }}');
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
    <div class="{{ VC::ALT_WRN }}">{{ __('Deal not found') }}</div>
@endif
