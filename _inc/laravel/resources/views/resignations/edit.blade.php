@php
    use App\Config\Constants\{
        PlansConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Auth;

    $lang = Utility::fetchUserLang();
    $user = Auth::user();
@endphp

{!! Form::model($resignation, [
    'route'  => [ViewsConstants::RSG . '.update', $resignation->id],
    'method' => 'PUT',
    'id'     => 'edit_resignation',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon btn-sm"
                   data-ajax-popup-over="true"
                   data-url="{{ route('generate', ['resignation']) }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            @if(!empty($user?->{UsersConstants::COL_TP}) && strtolower($user->{UsersConstants::COL_TP}) !== 'employee')
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('notice_date', __('Notice Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('notice_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('resignation_date', __('Resignation Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('resignation_date', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB12 }}">
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
