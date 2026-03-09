@php
    $fields ??= [];
    try {
        $billsCustomDebitNoteBaseRouteName  = ViewsConstants::BIL.'.custom.debit.note';
        $billsCustomDebitNoteKebabRouteName = Str::kebab($billsCustomDebitNoteBaseRouteName);
        $billsCustomDebitNoteResolvedName   = Route::has($billsCustomDebitNoteBaseRouteName)
            ? $billsCustomDebitNoteBaseRouteName
            : (Route::has($billsCustomDebitNoteKebabRouteName) ? $billsCustomDebitNoteKebabRouteName : null);
        $billsCustomDebitNoteUrl            = $billsCustomDebitNoteResolvedName ? route($billsCustomDebitNoteResolvedName) : '#';
        $billsCustomDebitNoteFormId         = 'bills-custom-debit-note-create-form';
        $billsLangValue                     = isset($lang) ? $lang : Utility::fetchUserLang();
        $billsCustomDebitNoteGuardMessage   = Utility::fetchLinkMessage($billsLangValue, ViewsConstants::BIL, 'create_custom_debit_note_route_unavailable') ?? 'Create custom debit note route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('debit_notes/custom_create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'method'            => 'POST',
    'url'               => $billsCustomDebitNoteUrl,
    'id'                => $billsCustomDebitNoteFormId,
    'data-url'          => $billsCustomDebitNoteUrl,
    'data-guard-msg'    => $billsCustomDebitNoteGuardMessage,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @if(Utility::isFilled($fields) ?? [])
                @foreach($fields as $f)
                    <div class="{{ $f['colClass'] }}">
                        {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                        @if($f['name'] === 'bill')
                            {{ Form::select('bill', $billOptions, null, $f['attrs']) }}
                            @if(!$hasBills)
                                <small class="{{ VC::TXT_MT }}">{{ __('No bills found for this query.') }}</small>
                            @endif
                        @else
                            @php
 $__method = $f['type'] === 'textarea' ? 'textarea' : $f['type'];
@endphp
                            {!! call_user_func([Form::class, $__method], $f['name'], null, $f['attrs']) !!}
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
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/bills/debitNotes/customCreate.js') }}"></script>
{{ Form::close() }}
