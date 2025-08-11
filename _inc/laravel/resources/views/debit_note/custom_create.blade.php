@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\ViewsConstants;

    $fields = [
        [
            'name'     => 'bill',
            'type'     => 'select',
            'label'    => __('Bill'),
            'options'  => ['0' => __('Select Bill')] + $bills,
            'colClass' => 'col-md-12',
            'attrs'    => ['class'=>'form-control select','id'=>'bill','required'=>'required'],
            'format'   => fn($key, $bill) => Auth::user()->billNumberFormat($bill),
        ],
        [
            'name'     => 'amount',
            'type'     => 'number',
            'label'    => __('Amount'),
            'colClass' => 'col-md-6',
            'attrs'    => ['class'=>'form-control','required'=>'required','step'=>'0.01'],
        ],
        [
            'name'     => 'date',
            'type'     => 'date',
            'label'    => __('Date'),
            'colClass' => 'col-md-6',
            'attrs'    => ['class'=>'form-control','required'=>'required'],
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

{{ Form::open([
    'route'  => ViewsConstants::BIL . '.custom.debit.note',
    'method' => 'post',
]) }}
<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="form-group {{ $f['colClass'] }}">
                {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                @if($f['type'] === 'select')
                    @php
                        $opts = collect($f['options'])
                            ->mapWithKeys(function($val, $key) use ($f) {
                                $label = is_callable($f['format']) 
                                    ? $f['format']($key, $val) 
                                    : $val;
                                return [$key => $label];
                            })
                            ->toArray();
                    @endphp
                    {{ Form::select($f['name'], $opts, null, $f['attrs']) }}
                @elseif($f['type'] === 'textarea')
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
    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}
