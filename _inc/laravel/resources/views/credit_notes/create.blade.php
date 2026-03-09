@php
    $fields ??= [];
    try {
$lang        = Utility::fetchUserLang();
        $routeName   = ViewsConstants::INV . '.credit.note';
        $creditRoute = Route::has($routeName)
            ? route($routeName, $invoice_id)
            : '#';
        $formId      = 'invoiceCreditNoteForm_' . $invoice_id;
        $guardMsg    = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::INV,
            'credit_note_route_unavailable'
        ) ?? 'Add credit note route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('credit_notes/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{{ Form::open([
    'route'          => [$creditRoute],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $creditRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @foreach($fields as $f)
                <div class="{{ VC::CM6 }}{{ $f['cols'] === 12 ? ' ' . VC::C12 : '' }} {{ VC::FM_G }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                    @php
 $attrs = $f['attrs'];
@endphp
                    @if($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], $f['value'] ?? null, $attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'], $f['value'] ?? null, $attrs) }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Add') }}</button>
    </div>
    <script src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (window.RouteGuard?.guardFormSubmit) {
                window.RouteGuard.guardFormSubmit(form);
            }
        })();
    </script>
{{ Form::close() }}
