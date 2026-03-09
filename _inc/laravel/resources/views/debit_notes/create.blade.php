@php
    try {
$supported = ['text', 'textarea', 'number', 'email', 'password', 'date', 'hidden'];
        $billsDebitNoteBaseRouteName  = ViewsConstants::BIL.'.debit.note';
        $billsDebitNoteKebabRouteName = Str::kebab($billsDebitNoteBaseRouteName);
        $billsDebitNoteResolvedName   = Route::has($billsDebitNoteBaseRouteName)
            ? $billsDebitNoteBaseRouteName
            : (Route::has($billsDebitNoteKebabRouteName) ? $billsDebitNoteKebabRouteName : null);
        $billIdValue                  = (string) ($bill_id ?? '');
        $billsDebitNoteUrl            = ($billsDebitNoteResolvedName && $billIdValue !== '')
            ? route($billsDebitNoteResolvedName, $billIdValue)
            : '#';
        $userLang                     = isset($lang) ? $lang : Utility::fetchUserLang();
        $billsDebitNoteGuardMessage   = Utility::fetchLinkMessage($userLang, ViewsConstants::BIL, 'create_debit_note_route_unavailable')
            ?? 'Create debit note route is unavailable. Please contact technical support or your domain administrator.';
        $billsDebitNoteFormId         = 'bills-debit-note-create-form-'.($billIdValue === '' ? 'x' : $billIdValue);
    } catch (\Throwable $e) {
        \Log::error('debit_notes/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{{ Form::open([
    'method'            => 'POST',
    'url'               => $billsDebitNoteUrl,
    'id'                => $billsDebitNoteFormId,
    'data-url'          => $billsDebitNoteUrl,
    'data-guard-msg'    => $billsDebitNoteGuardMessage,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @if(Utility::isFilled($fields) ?? [])
                @foreach($fields as $f)
                    @php
                        try {
                            $name     = $f['name']     ?? '';
                            $type     = $f['type']     ?? 'text';
                            $label    = $f['label']    ?? ucfirst($name);
                            $colClass = $f['colClass'] ?? VC::FM_GCB12;
                            $value    = array_key_exists('value', $f) ? $f['value'] : null;
                            $attrs    = $f['attrs']    ?? ['class' => VC::FM_CT];
                            $method   = in_array($type, $supported, true) ? $type : 'text';
                        } catch (\Throwable $e) {
                            \Log::error('debit_notes/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ $colClass }}">
                        {{ Form::label($name, $label, ['class' => VC::FM_LB]) }}
                        @if($method === 'textarea')
                            {{ Form::textarea($name, $value, $attrs) }}
                        @elseif($method === 'date' || $method === 'number' || $method === 'email' || $method === 'password' || $method === 'text')
                            {{ Form::$method($name, $value, $attrs) }}
                        @else
                            {{ Form::text($name, $value, $attrs) }}
                        @endif
                    </div>
                @endforeach
            @else
                <div class="{{ VC::C12 }}">
                    <p class="{{ VC::TXT_MT }}">{{ __('No fields to display.') }}</p>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Add') }}</button>
    </div>
    <script defer>
        window.RouteGuard?.guardFormSubmit?.('{{ $billsDebitNoteFormId }}');
    </script>
{{ Form::close() }}
