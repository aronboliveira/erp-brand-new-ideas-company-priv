@php
    use App\Config\Constants\{PlansConstants,ViewClassNamesConstants as VC,ViewsConstants};
    use Collective\Html\FormFacade as Form;
    $chatEnabled = \App\Models\Utility::getChatGPTSettings()?->{PlansConstants::COL_GPT} ?? 0;
    $row = VC::RW;
    $colMd6 = VC::CM6;
    $col12 = VC::C12;
    $formGroup = VC::FM_G;
    $formControl = VC::FM_CT;
    $formLabel = VC::FM_LB;
@endphp

{{ Form::model($asset, ['route'=>[ViewsConstants::ACC_AST.'.update',$asset->id],'method'=>'PUT']) }}
    <div class="modal-body">
        @if($chatEnabled)
            <div class="text-end">
                <a href="#" data-size="md" class="{{ VC::BT_SM_PM }} btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['account asset']) }}" data-bs-placement="top" title="{{ __('Generate with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        <div class="{{ $row }}">
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Form::label('employee_id',__('Employee'),['class'=>$formLabel]) }}
                {{ Form::select('employee_id[]',$employee,$asset->employee_id,[
                    'class'=>"$formControl select2",'id'=>'choices-multiple'
                ]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('name',__('Name'),['class'=>$formLabel]) }}
                {{ Form::text('name',null,['class'=>$formControl,'required']) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('amount',__('Amount'),['class'=>$formLabel]) }}
                {{ Form::number('amount',null,['class'=>$formControl,'required','step'=>'0.01']) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('purchase_date',__('Purchase Date'),['class'=>$formLabel]) }}
                {{ Form::date('purchase_date',null,['class'=>$formControl]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('supported_date',__('Supported Date'),['class'=>$formLabel]) }}
                {{ Form::date('supported_date',null,['class'=>$formControl]) }}
            </div>
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Form::label('description',__('Description'),['class'=>$formLabel]) }}
                {{ Form::textarea('description',null,['class'=>$formControl,'rows'=>3]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>
{{ Form::close() }}
