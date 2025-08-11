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
            'value'    => $bill->getDue(),
            'attrs'    => ['class'=>'form-control','required'=>'required','step'=>'0.01'],
        ],
        [
            'name'     => 'account_id',
            'type'     => 'select',
            'label'    => __('Account'),
            'colClass' => 'col-md-6',
            'options'  => $accounts,
            'attrs'    => ['class'=>'form-control','required'=>'required'],
        ],
        [
            'name'     => 'reference',
            'type'     => 'text',
            'label'    => __('Reference'),
            'colClass' => 'col-md-6',
            'attrs'    => ['class'=>'form-control'],
        ],
        [
            'name'     => 'description',
            'type'     => 'textarea',
            'label'    => __('Description'),
            'colClass' => 'col-md-12',
            'attrs'    => ['class'=>'form-control','rows'=>3],
        ],
    ];
@endphp

{{ Form::open([
    'route'   => [ViewsConstants::BIL . '.payment', $bill->id],
    'method'  => 'post',
    'enctype' => 'multipart/form-data',
]) }}

<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="form-group {{ $f['colClass'] }}">
                {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                @if($f['type'] === 'select')
                    {{ Form::select($f['name'], $f['options'], $f['value'] ?? null, $f['attrs']) }}
                @elseif($f['type'] === 'textarea')
                    {{ Form::textarea($f['name'], $f['value'] ?? null, $f['attrs']) }}
                @else
                    {{ Form::{ $f['type'] }($f['name'], $f['value'] ?? null, $f['attrs']) }}
                @endif
            </div>
        @endforeach

        <div class="col-md-6 form-group">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class'=>'form-label']) }}
            <div class="choose-file">
                <label for="add_receipt">
                    <input type="file" name="add_receipt" id="add_receipt" class="form-control">
                </label>
                <p class="upload_file"></p>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
</div>

{{ Form::close() }}
