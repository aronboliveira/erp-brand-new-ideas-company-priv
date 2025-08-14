@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
@endphp

{!! Form::open([
    'url'  => ViewsConstants::TRF,
    'method' => 'post',
    'id'     => 'create_transfer',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ route('generate', ['transfer']) }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('branch_id', __('Branch'), ['class' => VC::FM_LB]) }}
                {{ Form::select('branch_id', $branches, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
                {{ Form::select('department_id', $departments, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('transfer_date', __('Transfer Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('transfer_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
