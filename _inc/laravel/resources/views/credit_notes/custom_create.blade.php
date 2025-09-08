@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\{
        ViewsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC
    };
    use Illuminate\Support\Facades\Auth;

    $user           = Auth::user();
    $lang           = Utility::fetchUserLang(user: $user);
    $invoiceOptions = ['' => __('Select Invoice')] +
        collect($invoices)
            ->mapWithKeys(fn($inv, $key) => [$key => $user?->invoiceNumberFormat($inv) ?? ''])
            ->toArray();
    $fields = [
        [
            'name'    => 'invoice',
            'type'    => 'select',
            'label'   => __('Invoice'),
            'cols'    => 12,
            'options' => $invoiceOptions,
            'attrs'   => ['class' => VC::FM_CT . ' select', 'required' => 'required'],
        ],
        [
            'name'  => 'amount',
            'type'  => 'number',
            'label' => __('Amount'),
            'cols'  => 6,
            'attrs' => ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01'],
        ],
        [
            'name'  => 'date',
            'type'  => 'date',
            'label' => __('Date'),
            'cols'  => 6,
            'attrs' => ['class' => VC::FM_CT, 'required' => 'required'],
        ],
        [
            'name'  => 'description',
            'type'  => 'textarea',
            'label' => __('Description'),
            'cols'  => 12,
            'attrs' => ['class' => VC::FM_CT, 'rows' => 2],
        ],
    ];

    $routeName    = ViewsConstants::INV . '.custom.credit.note';
    $customRoute  = Route::has($routeName)
        ? route($routeName)
        : '#';
    $formId       = 'invoice_custom_credit_note_form';
    $guardMsg     = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'custom_credit_note_route_unavailable'
    ) ?? 'Add custom credit note route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'route'          => [$customRoute],
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $customRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            @foreach($fields as $f)
                <div class="{{ VC::FM_G }} col-md-{{ $f['cols'] }}">
                    {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                    @php $attrs = $f['attrs']; @endphp
                    @if($f['type'] === 'textarea')
                        {{ Form::textarea($f['name'], null, $attrs) }}
                    @elseif($f['type'] === 'select')
                        {{ Form::select($f['name'], $f['options'], null, $attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/creditNotes/customCreate.js') }}"></script>
{{ Form::close() }}

