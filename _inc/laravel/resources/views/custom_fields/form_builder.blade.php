@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use Collective\Html\FormFacade as Form;
@endphp
@php
    $list = ((is_array($customFields ?? null) && count($customFields ?? [])) || (($customFields ?? null) instanceof Collection && ($customFields)->isNotEmpty())) ? $customFields : [];
@endphp
@forelse($list as $field)
    @php
        $fid = isset($field->id) ? (string)$field->id : '0';
        $fname = isset($field->name) && $field->name !== '' ? $field->name : __('No field name available');
        $ftype = isset($field->type) && $field->type !== '' ? $field->type : 'text';
        $inputName = "customField[{$fid}]";
        $forId = "customField-{$fid}";
    @endphp
    <div class="{{ VC::FM_G }}">
        {{ Form::label($forId, __($fname), ['class' => VC::FM_LB]) }}
        <div class="input-group">
            @switch($ftype)
                @case('email')
                    {{ Form::email($inputName, null, ['class' => VC::FM_CT, 'id' => $forId]) }}
                    @break
                @case('number')
                    {{ Form::number($inputName, null, ['class' => VC::FM_CT, 'id' => $forId]) }}
                    @break
                @case('date')
                    {{ Form::date($inputName, null, ['class' => VC::FM_CT, 'id' => $forId]) }}
                    @break
                @case('textarea')
                    {{ Form::textarea($inputName, null, ['class' => VC::FM_CT, 'id' => $forId]) }}
                    @break
                @case('text')
                @default
                    {{ Form::text($inputName, null, ['class' => VC::FM_CT, 'id' => $forId]) }}
            @endswitch
        </div>
    </div>
@empty
    <div class="alert alert-info">
        {{ __('No custom fields available.') }}
    </div>
@endforelse
