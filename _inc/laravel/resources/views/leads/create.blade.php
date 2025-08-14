@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PlansConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
{{ Form::open(array('url' => ViewsConstants::LD)) }}
    <div class="modal-body">
        {{-- start for ai module--}}
        @php
            $plan= Utility::getChatGPTSettings();
        @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['lead']) }}"
                data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{__('Generate with AI')}}</span>
                </a>
            </div>
        @endif
        {{-- end for ai module--}}
        <div class="row">
            <div class="col-6 form-group">
                {{ Form::label('subject', __('Subject'),['class'=>'form-label']) }}
                {{ Form::text('subject', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('user_id', __('User'),['class'=>'form-label']) }}
                {{ Form::select('user_id', $users,null, array('class' => 'form-control select','required'=>'required')) }}
                @if(count($users) == 1)
                    <div class="text-muted text-xs">
                        {{__('Please create new users')}} <a href="{{route(ViewsConstants::USR.'.index')}}">{{__('here')}}</a>.
                    </div>
                @endif
            </div>
            <div class="col-6 form-group">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
                {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('email', __('Email'),['class'=>'form-label']) }}
                {{ Form::text('email', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('phone', __('Phone'),['class'=>'form-label']) }}
                {{ Form::text('phone', null, array('class' => 'form-control','required'=>'required')) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
{{Form::close()}}

