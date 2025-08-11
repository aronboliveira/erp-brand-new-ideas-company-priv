@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\ViewsConstants;
    use Illuminate\Support\Facades\Auth;
    $invoiceOptions = ['' => __('Select Invoice')] +
        collect($invoices)
            ->mapWithKeys(fn($inv, $key) => [$key => Auth::user()->invoiceNumberFormat($inv)])
            ->toArray();

    $fields = [
        [
            'name'    => 'invoice',
            'type'    => 'select',
            'label'   => __('Invoice'),
            'cols'    => 12,
            'options' => $invoiceOptions,
            'attrs'   => ['class' => 'form-control select', 'required' => 'required'],
        ],
        [
            'name'    => 'amount',
            'type'    => 'number',
            'label'   => __('Amount'),
            'cols'    => 6,
            'attrs'   => ['class' => 'form-control', 'required' => 'required', 'step' => '0.01'],
        ],
        [
            'name'    => 'date',
            'type'    => 'date',
            'label'   => __('Date'),
            'cols'    => 6,
            'attrs'   => ['class' => 'form-control', 'required' => 'required'],
        ],
        [
            'name'    => 'description',
            'type'    => 'textarea',
            'label'   => __('Description'),
            'cols'    => 12,
            'attrs'   => ['class' => 'form-control', 'rows' => 2],
        ],
    ];
@endphp

{{ Form::open(['route' => [ViewsConstants::INV . '.custom.credit.note'], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="form-group col-md-{{ $f['cols'] }}">
                {{ Form::label($f['name'], $f['label'], ['class' => 'form-label']) }}
                @php $attrs = $f['attrs'] ?? []; @endphp
                @if($f['type'] === 'textarea')
                    {{ Form::textarea($f['name'], null, $attrs) }}
                @elseif($f['type'] === 'select')
                    {{ Form::select($f['name'], $f['options'], null, $attrs) }}
                @else
                    {{ Form::{ $f['type'] }($f['name'], null, $attrs) }}
                @endif
            </div>
        @endforeach
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}
