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

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp

{!! Form::open([
    'url'  => ViewsConstants::WRN,
    'method' => 'post',
    'id'     => 'create_warning',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   data-size="md"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-url="{{ route('generate', ['warning']) }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            @if(!empty($user?->type) && strtolower($user->type) !== 'employee')
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('warning_by', __('Warning By'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('warning_by', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
                </div>
            @endif

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('warning_to', __('Warning To'), ['class' => VC::FM_LB]) }}
                {{ Form::select('warning_to', $employees, null, ['class' => VC::FM_CT_SL]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('warning_date', __('Warning Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date('warning_date', null, ['class' => VC::FM_CT]) }}
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
