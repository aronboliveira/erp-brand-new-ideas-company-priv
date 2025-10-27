@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
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
                        $name     = $f['name']     ?? '';
                        $type     = $f['type']     ?? 'text';
                        $label    = $f['label']    ?? ucfirst($name);
                        $colClass = $f['colClass'] ?? VC::FM_GCB12;
                        $value    = array_key_exists('value', $f) ? $f['value'] : null;
                        $attrs    = $f['attrs']    ?? ['class' => VC::FM_CT];
                        $method   = in_array($type, $supported, true) ? $type : 'text';
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
        (() => {
            try {
                const fm = document.getElementById('{{ $billsDebitNoteFormId }}');
                if (!fm) { return; }
                if (fm.getAttribute('data-submit-guarded') === 'true') { return; }
                fm.setAttribute('data-submit-guarded','true');
                fm.addEventListener('submit',(e) => {
                    try {
                        const action = fm.getAttribute('action') ?? '#';
                        const url = fm.getAttribute('data-url') ?? action ?? '#';
                        if (url !== '#' && action !== '#') { return; }
                        e.preventDefault();
                        const msg = fm.getAttribute('data-guard-msg') ?? 'Create debit note route is unavailable. Please contact technical support or your domain administrator.';
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