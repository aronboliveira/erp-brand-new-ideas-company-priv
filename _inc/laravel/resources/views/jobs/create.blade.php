@php
    use App\Config\Constants\{
        PlansConstants,
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Create Job')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::JB.'.index')}}">{{__('Job')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Create')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link href="{{asset('css/bootstrap-tagsinput.css')}}" rel="stylesheet"/>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script async src="{{asset('js/bootstrap-tagsinput.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/lang/create.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/store.js') }}"></script>
@endpush
@php
    $plan = Utility::getChatGPTSettings();
@endphp
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <a href="#" data-size="lg" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['job']) }}"
               data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                <i class="{{ VC::FAS_RB }}"> </i> <span>{{__('Generate with AI')}}</span>
            </a>
        @endif
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    {{Form::open(array('url'=> ViewsConstants::JB,'method'=>'post'))}}
        <div class="row mt-3">
            <div class="col-md-6 ">
                <div class="card card-fluid">
                    <div class="card-body job-create ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('title', __('Job Title'),['class'=>'form-label']) !!}
                                {!! Form::text('title', old('title'), ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('branch', __('Branch'),['class'=>'form-label']) !!}
                                {{ Form::select('branch', $branches,null, array('class' => 'form-control select','required'=>'required')) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('category', __('Job Category'),['class'=>'form-label']) !!}
                                {{ Form::select('category', $categories,null, array('class' => 'form-control select','required'=>'required')) }}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('position', __('Positions'),['class'=>'form-label']) !!}
                                {!! Form::number('position', old('positions'), ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('status', __('Status'),['class'=>'form-label']) !!}
                                {{ Form::select('status', $status,null, array('class' => 'form-control select','required'=>'required')) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('start_date', __('Start Date'),['class'=>'form-label']) !!}
                                {!! Form::date('start_date', old('start_date'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('end_date', __('End Date'),['class'=>'form-label']) !!}
                                {!! Form::date('end_date', old('end_date'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-md-12">
                                <input type="text" class="form-control" value="" data-toggle="tags" name="skill" placeholder="Skill"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ">
                <div class="card card-fluid">
                    <div class="card-body job-create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <h6>{{__('Need to ask ?')}}</h6>
                                    <div class="my-4">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]" value="gender" id="check-gender">
                                            <label class="form-check-label" for="check-gender">{{__('Gender')}} </label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]" value="dob" id="check-dob">
                                            <label class="form-check-label" for="check-dob">{{__('Date Of Birth')}}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]" value="country" id="check-country">
                                            <label class="form-check-label" for="check-country">{{__('Country')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>{{__('Need to show option ?')}}</h6>
                                <div class="my-4">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="profile" id="check-profile">
                                        <label class="form-check-label" for="check-profile">{{__('Profile Image')}} </label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="resume" id="check-resume">
                                        <label class="form-check-label" for="check-resume">{{__('Resume')}}</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="letter" id="check-letter">
                                        <label class="form-check-label" for="check-letter">{{__('Cover Letter')}}</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="visibility[]" value="terms" id="check-terms">
                                        <label class="form-check-label" for="check-terms">{{__('Terms And Conditions')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div class="form-group col-md-12">
                                <h6>{{__('Custom Question')}}</h6>
                                <div class="my-4">
                                    @foreach($customQuestion as $question)
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="custom_question[]" value="{{$question->id}}" id="custom_question_{{$question->id}}">
                                            <label class="form-check-label" for="custom_question_{{$question->id}}">{{$question->question}} </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-fluid">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('description', __('Job Description'),['class'=>'form-label']) !!}
                                <textarea class="form-control summernote-simple-2" name="description" id="exampleFormControlTextarea1" rows="15"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-fluid">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-6 mb-2">
                                {!! Form::label('requirement', __('Job Requirement'),['class'=>'form-label']) !!}
                            </div>
                            <div class="col-6 text-end">
                                @if($plan->chatgpt == 1)
                                    <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['grammar']) }}"
                                    data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                        <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span>
                                    </a>
                                @endif
                            </div>
                            <div class="form-group col-md-12">
                                <textarea class="form-control summernote-simple" name="requirement" id="exampleFormControlTextarea2" rows="8"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-end">
                <div class="form-group">
                    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
                </div>
            </div>
        </div>
    {{Form::close()}}
@endsection

