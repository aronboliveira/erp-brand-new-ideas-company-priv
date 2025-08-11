@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\ViewsConstants;

    $fields = [
        [
            'name'  => 'date',
            'type'  => 'date',
            'label' => __('Date'),
            'cols'  => 6,
            'attrs' => ['class'=>'form-control','required'=>'required'],
        ],
        [
            'name'  => 'amount',
            'type'  => 'number',
            'label' => __('Amount'),
            'cols'  => 6,
            'attrs' => ['class'=>'form-control','required'=>'required','step'=>'0.01'],
        ],
        [
            'name'  => 'description',
            'type'  => 'textarea',
            'label' => __('Description'),
            'cols'  => 12,
            'attrs' => ['class'=>'form-control','rows'=>3],
        ],
    ];
@endphp

{{ Form::model($creditNote, [
    'route'  => [ViewsConstants::INV . '.edit.credit.note', $creditNote->invoice, $creditNote->id],
    'method' => 'post'
]) }}
<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="form-group col-md-{{ $f['cols'] }}">
                {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                @php $attrs = $f['attrs'] ?? []; @endphp
                @if($f['type'] === 'textarea')
                    {{ Form::textarea($f['name'], null, $attrs) }}
                @else
                    {{ Form::{ $f['type'] }($f['name'], null, $attrs) }}
                @endif
            </div>
        @endforeach
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>
{{ Form::close() }}
