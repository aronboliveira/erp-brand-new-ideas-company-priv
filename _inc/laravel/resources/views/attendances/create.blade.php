@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants};
    $fields = [
        [
            'name'    => 'employee_id',
            'type'    => 'select',
            'label'   => __('Employee'),
            'options' => $employees,
            'class'   => 'form-control select2',
        ],
        [
            'name'  => 'date',
            'type'  => 'text',
            'label' => __('Date'),
            'class' => 'form-control datepicker',
        ],
        [
            'name'  => 'clock_in',
            'type'  => 'time',
            'label' => __('Clock In'),
            'class' => 'form-control',
        ],
        [
            'name'  => 'clock_out',
            'type'  => 'time',
            'label' => __('Clock Out'),
            'class' => 'form-control',
        ],
    ];
@endphp
{{ Form::open(['url' => ViewsConstants::EMP_ATD, 'method' => 'post']) }}
    <div class="card-body p-0">
        <div class="row">
            @foreach($fields as $f)
                <div class="form-group col-lg-6 col-md-6">
                    {{ Form::label($f['name'], $f['label'], ['class' => 'form-label']) }}
                    @if($f['type'] === 'select')
                        {{ Form::select($f['name'], $f['options'], null, ['class' => $f['class']]) }}
                    @else
                        {{ Form::{ $f['type'] }($f['name'], null, ['class' => $f['class']]) }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="{{ ViewClassNamesConstants::BT_LG }}" data-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Create'), ['class' => ViewClassNamesConstants::BT_PRM]) }}
    </div>
{{ Form::close() }}
