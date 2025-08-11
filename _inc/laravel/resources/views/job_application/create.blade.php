@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp
{{Collective\Html\FormFacade::open(array('url'=>'job-application','method'=>'post', 'enctype' => "multipart/form-data"))}}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Collective\Html\FormFacade::label('job',__('Job'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::select('job',$jobs,null,array('class'=>'form-control select2','id'=>'jobs'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('name',__('Name'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control name'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('email',__('Email'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('email',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('phone',__('Phone'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('phone',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6 dob d-none">
            {!! Collective\Html\FormFacade::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}
            {!! Collective\Html\FormFacade::date('dob', old('dob'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-6 gender d-none">
            {!! Collective\Html\FormFacade::label('gender', __('Gender'),['class'=>'form-label']) !!}
            <div class="d-flex radio-check">
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input">
                    <label class="form-check-label" for="g_male">{{__('Male')}}</label>
                </div>
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input">
                    <label class="form-check-label" for="g_female">{{__('Female')}}</label>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6 country d-none">
            {{Collective\Html\FormFacade::label('country',__('Country'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('country',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6 country d-none">
            {{Collective\Html\FormFacade::label('state',__('State'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('state',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6 country d-none">
            {{Collective\Html\FormFacade::label('city',__('City'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('city',null,array('class'=>'form-control'))}}
        </div>

        <div class="form-group col-md-6 profile d-none">
            {{Collective\Html\FormFacade::label('profile',__('Profile'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="profile" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" class="form-control" name="profile" id="profile" data-filename="profile_create">
                </label>
                <p class="profile_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 resume d-none">
            {{Collective\Html\FormFacade::label('resume',__('CV / Resume'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="resume" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" class="form-control" name="resume" id="resume" data-filename="resume_create">
                </label>
                <p class="resume_create"></p>
            </div>
        </div>
        <div class="form-group col-md-12 letter d-none">
            {{Collective\Html\FormFacade::label('cover_letter',__('Cover Letter'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('cover_letter',null,array('class'=>'form-control'))}}
        </div>

        @foreach($questions as $question)
            <div class="form-group col-md-12  question question_{{$question->id}} d-none">
                {{Collective\Html\FormFacade::label($question->question,$question->question,['class'=>'form-label'])}}
                <input type="text" class="form-control" name="question[{{$question->question}}]" {{($question->is_required=='yes')? '':''}}>
            </div
        @endforeach

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}


