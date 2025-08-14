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

{!! Form::model($promotion, [
    'route'  => [ViewsConstants::PRM . '.update', $promotion->id],
    'method' => 'PUT',
    'id'     => 'edit_promotion',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if ($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a  href="#"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-size="md"
                    data-url="{{ route('generate', ['promotion']) }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('designation_id', __('Designation'), ['class' => VC::FM_LB]) }}
                {{ Form::select('designation_id', $designations, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('promotion_title', __('Promotion Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('promotion_title', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="col-lg-6 {{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('promotion_date', __('Promotion Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('promotion_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
