@php
    use Collective\Html\FormFacade as Form;
    use App\Models\Utility;
    use App\Config\Constants\ViewsConstants;
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Config\Constants\StacksConstants;
    use Illuminate\Support\Collection;

    $fields = [
        [
            'name'     => 'date',
            'type'     => 'date',
            'label'    => __('Date'),
            'colClass' => VC::FM_GCB6,
            'attrs'    => ['class' => VC::FM_CT, 'required' => 'required'],
        ],
        [
            'name'     => 'amount',
            'type'     => 'number',
            'label'    => __('Amount'),
            'colClass' => VC::FM_GCB6,
            'attrs'    => ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01'],
        ],
        [
            'name'     => 'description',
            'type'     => 'textarea',
            'label'    => __('Description'),
            'colClass' => VC::FM_GCB12,
            'attrs'    => ['class' => VC::FM_CT, 'rows' => 2],
        ],
    ];
@endphp

@if(!empty($debitNote) && isset($debitNote->bill, $debitNote->id))
    @php
        $billsEditDebitNoteBaseRouteName   = ViewsConstants::BIL.'.edit.debit.note';
        $billsEditDebitNoteKebabRouteName  = Str::kebab($billsEditDebitNoteBaseRouteName);
        $billsEditDebitNoteResolvedName    = Route::has($billsEditDebitNoteBaseRouteName)
            ? $billsEditDebitNoteBaseRouteName
            : (Route::has($billsEditDebitNoteKebabRouteName) ? $billsEditDebitNoteKebabRouteName : null);

        $billIdValue                       = (string) ($debitNote->bill ?? '');
        $debitNoteIdValue                  = (string) ($debitNote->id ?? '');
        $billsEditDebitNoteUrl             = ($billsEditDebitNoteResolvedName && $billIdValue !== '' && $debitNoteIdValue !== '')
            ? route($billsEditDebitNoteResolvedName, [$billIdValue, $debitNoteIdValue])
            : '#';

        $billsEditDebitNoteFormId          = 'bills-edit-debit-note-form-'.$billIdValue.'-'.$debitNoteIdValue;
        $billsLangValue                    = isset($lang) ? $lang : Utility::fetchUserLang();
        $billsEditDebitNoteGuardMessage    = Utility::fetchLinkMessage($billsLangValue, ViewsConstants::BIL, 'edit_debit_note_route_unavailable')
            ?? 'Edit debit note route is unavailable. Please contact technical support or your domain administrator.';
    @endphp

    {{ Form::model($debitNote, [
        'method'            => 'POST',
        'url'               => $billsEditDebitNoteUrl,
        'id'                => $billsEditDebitNoteFormId,
        'data-url'          => $billsEditDebitNoteUrl,
        'data-guard-msg'    => $billsEditDebitNoteGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                @if((is_array($fields) && count($fields)) || ($fields instanceof Collection && $fields->isNotEmpty()))
                    @foreach($fields as $f)
                        <div class="{{ $f['colClass'] }}">
                            {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}

                            @if(($f['type'] ?? '') === 'textarea')
                                {{ Form::textarea($f['name'], null, $f['attrs']) }}
                            @else
                                @php $__method = $f['type'] ?? 'text'; @endphp
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
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer>
            (() => {
                try {
                    const fm = document.getElementById('{{ $billsEditDebitNoteFormId ?? "x" }}');
                    if (!fm) { return; }
                    if (fm.getAttribute('data-submit-guarded') === 'true') { return; }
                    fm.setAttribute('data-submit-guarded','true');

                    fm.addEventListener('submit', (e) => {
                        try {
                            const action = fm.getAttribute('action') ?? '#';
                            const url    = fm.getAttribute('data-url') ?? action ?? '#';
                            if (url !== '#' && action !== '#') { return; }

                            e.preventDefault();

                            const msg = fm.getAttribute('data-guard-msg')
                                ?? 'Edit debit note route is unavailable. Please contact technical support or your domain administrator.';

                            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                            let container = document.getElementById('toast-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.id = 'toast-container';
                                document.body.appendChild(container);
                            }

                            if (hasBootstrap) {
                                const toast = document.createElement('div');
                                toast.className = 'toast';
                                toast.setAttribute('role','alert');
                                toast.setAttribute('aria-live','assertive');
                                toast.setAttribute('aria-atomic','true');

                                const body = document.createElement('div');
                                body.className = 'toast-body';
                                body.textContent = msg;

                                toast.appendChild(body);
                                container.appendChild(toast);
                                bootstrap.Toast.getOrCreateInstance(toast).show();
                            } else {
                                alert(msg);
                            }

                            fm.setAttribute('data-failed-route','true');
                        } catch (err) {}
                    });
                } catch (err) {}
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <p class="{{ VC::TXT_MT }}">{{ __('No debit note found to edit.') }}</p>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@endif
