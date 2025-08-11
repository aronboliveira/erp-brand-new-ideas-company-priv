@php
    use App\Config\Constants\{ViewClassNamesConstants,ViewsConstants};
    $chatEnabled = \App\Models\Utility::getChatGPTSettings()->chatgpt ?? 0;
    $row = ViewClassNamesConstants::RW;
    $colMd6 = ViewClassNamesConstants::CM6;
    $col12 = ViewClassNamesConstants::C12;
    $formGroup = ViewClassNamesConstants::FM_G;
    $formControl = ViewClassNamesConstants::FM_CT;
    $formLabel = ViewClassNamesConstants::FM_LB;
@endphp

{{ Collective\Html\FormFacade::model($asset, ['route'=>[ViewsConstants::ACC_AST.'.update',$asset->id],'method'=>'PUT']) }}
    <div class="modal-body">
        @if($chatEnabled)
            <div class="text-end">
                <a href="#" data-size="md" class="{{ ViewClassNamesConstants::BT_SM_PM }} btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['account asset']) }}" data-bs-placement="top" title="{{ __('Generate with AI') }}">
                    <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        <div class="{{ $row }}">
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Collective\Html\FormFacade::label('employee_id',__('Employee'),['class'=>$formLabel]) }}
                {{ Collective\Html\FormFacade::select('employee_id[]',$employee,$asset->employee_id,[
                    'class'=>"$formControl select2",'id'=>'choices-multiple'
                ]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Collective\Html\FormFacade::label('name',__('Name'),['class'=>$formLabel]) }}
                {{ Collective\Html\FormFacade::text('name',null,['class'=>$formControl,'required']) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Collective\Html\FormFacade::label('amount',__('Amount'),['class'=>$formLabel]) }}
                {{ Collective\Html\FormFacade::number('amount',null,['class'=>$formControl,'required','step'=>'0.01']) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Collective\Html\FormFacade::label('purchase_date',__('Purchase Date'),['class'=>$formLabel]) }}
                {{ Collective\Html\FormFacade::date('purchase_date',null,['class'=>$formControl]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Collective\Html\FormFacade::label('supported_date',__('Supported Date'),['class'=>$formLabel]) }}
                {{ Collective\Html\FormFacade::date('supported_date',null,['class'=>$formControl]) }}
            </div>
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Collective\Html\FormFacade::label('description',__('Description'),['class'=>$formLabel]) }}
                {{ Collective\Html\FormFacade::textarea('description',null,['class'=>$formControl,'rows'=>3]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ ViewClassNamesConstants::BT_PRM }}">{{ __('Update') }}</button>
    </div>
{{ Collective\Html\FormFacade::close() }}
