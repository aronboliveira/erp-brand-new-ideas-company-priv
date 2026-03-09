@php
    try {
$lang                 = Utility::fetchUserLang();
        $sourceStoreBaseName  = ViewsConstants::SRC;
        $sourceStoreKebabName = Str::kebab($sourceStoreBaseName);
        $sourceStoreResolved  = Route::has($sourceStoreBaseName)
            ? $sourceStoreBaseName
            : (Route::has($sourceStoreKebabName) ? $sourceStoreKebabName : null);
        $sourceStoreUrl       = $sourceStoreResolved ? route($sourceStoreResolved) : '#';
        $sourceStoreGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::SRC, 'source_store_route_unavailable') ?? 'Source store route is unavailable. Please contact technical support or your domain administrator.';
        $sourceStoreFormId    = 'source-store-form';
    } catch (\Throwable $e) {
        \Log::error('sources/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{!! Form::open([
    'url'            => $sourceStoreUrl,
    'id'             => $sourceStoreFormId,
    'method'         => 'post',
    'data-url'       => $sourceStoreUrl,
    'data-guard-msg' => $sourceStoreGuardMsg
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Source Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $sourceStoreFormId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const dataUrl = form.getAttribute('data-url') || '#';
                    const action  = form.getAttribute('action') || '#';
                    if (dataUrl !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                    form.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        })();
    </script>
@endpush
