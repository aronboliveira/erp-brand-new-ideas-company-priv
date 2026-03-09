@php
    try {
$lang = Utility::fetchUserLang();
        $routeKey        = ViewsConstants::DL . '.emails.store';
        $kebabRouteKey   = Str::kebab($routeKey);
        $hasRoute        = Route::has($routeKey);
        $hasKebab        = Route::has($kebabRouteKey);
        $storeRouteName  = $hasRoute
            ? $routeKey
            : ($hasKebab ? $kebabRouteKey : null);
        $storeRouteArr   = $storeRouteName
            ? [$storeRouteName, $deal->id]
            : ['#'];
        $storeRouteUrl   = $storeRouteName
            ? route($storeRouteName, $deal->id)
            : '#';
        $storeGuardMsg   = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::DL,
            'deal_emails_store_route_unavailable'
        ) ?? 'Deal emails store route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('deals/emails — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{!! Form::open([
    'route'          => $storeRouteArr,
    'id'             => 'create-email-form-' . $deal->id,
    'data-url'       => $storeRouteUrl,
    'data-guard-msg' => $storeGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('to', __('Mail To'), ['class' => VC::FM_LB]) }}
                {{ Form::email('to', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => 'summernote-simple']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer>
        (() => {
            const form = document.getElementById('create-email-form-{{ $deal->id }}');
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
