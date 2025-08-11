@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use Collective\Html\FormFacade as Form;
@endphp

@if(!$customFields->isEmpty())
    @foreach($customFields as $field)
        <div class="{{ VC::FM_G }}">
            {{ Form::label("customField-{$field->id}", __($field->name), ['class' => VC::FM_LB]) }}
            <div class="input-group">
                @php
                    $inputName = "customField[{$field->id}]";
                    $attrs     = ['class' => VC::FM_CT];
                @endphp
                @switch($field->type)
                    @case('text')
                        {{ Form::text($inputName, null, $attrs) }}
                        @break
                    @case('email')
                        {{ Form::email($inputName, null, $attrs) }}
                        @break
                    @case('number')
                        {{ Form::number($inputName, null, $attrs) }}
                        @break
                    @case('date')
                        {{ Form::date($inputName, null, $attrs) }}
                        @break
                    @case('textarea')
                        {{ Form::textarea($inputName, null, $attrs) }}
                        @break
                    @default
                        {{ Form::text($inputName, null, $attrs) }}
                @endswitch
            </div>
        </div>
    @endforeach
@endif
