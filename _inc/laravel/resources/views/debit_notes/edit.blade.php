@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\ViewsConstants;

    $fields = [
        [
            'name'     => 'date',
            'type'     => 'date',
            'label'    => __('Date'),
            'colClass' => 'col-md-6',
            'attrs'    => ['class'=>'form-control','required'=>'required'],
        ],
        [
            'name'     => 'amount',
            'type'     => 'number',
            'label'    => __('Amount'),
            'colClass' => 'col-md-6',
            'attrs'    => ['class'=>'form-control','required'=>'required','step'=>'0.01'],
        ],
        [
            'name'     => 'description',
            'type'     => 'textarea',
            'label'    => __('Description'),
            'colClass' => 'col-md-12',
            'attrs'    => ['class'=>'form-control','rows'=>2],
        ],
    ];
@endphp

{{ Form::model($debitNote, [
    'route'  => [ViewsConstants::BIL . '.edit.debit.note', $debitNote->bill, $debitNote->id],
    'method' => 'post',
]) }}

<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="form-group {{ $f['colClass'] }}">
                {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}

                @if($f['type'] === 'textarea')
                    {{ Form::textarea($f['name'], null, $f['attrs']) }}
                @else
                    {{ Form::{ $f['type'] }($f['name'], null, $f['attrs']) }}
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
