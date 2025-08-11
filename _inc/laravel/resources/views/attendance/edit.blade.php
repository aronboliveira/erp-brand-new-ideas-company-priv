@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants};
    $fields = [
        [
            'name'    => 'employee_id',
            'type'    => 'select',
            'label'   => __('Employee'),
            'options' => $employees,
            'col'     => 'col-lg-6',
            'class'   => 'form-control select',
        ],
        [
            'name'  => 'date',
            'type'  => 'date',
            'label' => __('Date'),
            'col'   => 'col-lg-6',
            'class' => 'form-control',
        ],
        [
            'name'  => 'clock_in',
            'type'  => 'time',
            'label' => __('Clock In'),
            'col'   => 'col-lg-6',
            'class' => 'form-control',
        ],
        [
            'name'  => 'clock_out',
            'type'  => 'time',
            'label' => __('Clock Out'),
            'col'   => 'col-lg-6',
            'class' => 'form-control',
        ],
    ];
@endphp
{{ Form::model($EmployeeAttendance, [
    'route'  => [ViewsConstants::EMP_ATD . '.update', $EmployeeAttendance->id],
    'method' => 'PUT',
]) }}
<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="form-group {{ $f['col'] }}">
                {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                @if($f['type'] === 'select')
                    {{ Form::select($f['name'], $f['options'], null, ['class'=>$f['class']]) }}
                @else
                    {{ Form::{ $f['type'] }($f['name'], null, ['class'=>$f['class']]) }}
                @endif
            </div>
        @endforeach
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="{{ ViewClassNamesConstants::BT_PRM }}">{{ __('Update') }}</button>
</div>
{{ Form::close() }}
