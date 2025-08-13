@php
    use App\Config\Constants\{ActivitiesConstants, ProjectsConstants, SupportsConstants};
@endphp
{{ Collective\Html\FormFacade::open(array('url' => 'support','enctype'=>"multipart/form-data")) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['support']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label(SupportsConstants::COL_SBJ, __('Subject'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text(SupportsConstants::COL_SBJ, '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        @if(\Auth::user()->type !='client')
            <div class="form-group col-md-6">
                {{Collective\Html\FormFacade::label(SupportsConstants::COL_USR,__('Support for User'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select(SupportsConstants::COL_USR,$users,null,array('class'=>'form-control select'))}}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label(ProjectsConstants::COL_PRT,__('Priority'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::select(ProjectsConstants::COL_PRT,$priority,null,array('class'=>'form-control select'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label(ActivitiesConstants::COL_TSK_STT,__('Status'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::select(ActivitiesConstants::COL_TSK_STT,$status,null,array('class'=>'form-control select'))}}
        </div>

        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_E_DT, __('End Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::date(ProjectsConstants::COL_E_DT, '', array('class' => 'form-control','required'=>'required')) }}
        </div>


    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_DESC, __('Description'),['class'=>'form-label']) }}
            {!! Collective\Html\FormFacade::textarea(ActivitiesConstants::COL_DESC, null, ['class'=>'form-control','rows'=>'3']) !!}
        </div>
    </div>

    <div class="form-group col-md-6">
        {{Collective\Html\FormFacade::label(SupportsConstants::COL_ATC,__('Attachment'),['class'=>'form-label'])}}
        <label for="document" class="form-label">
            <input type="file" class="form-control" name="attachment" id="attachment" data-filename="attachment_create">
        </label>
        <img id="image" class="mt-2" style="width:25%;"/>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
    {{ Collective\Html\FormFacade::close() }}




<script>
    document.getElementById('attachment').onchange = function () {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
