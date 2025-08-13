{{Collective\Html\FormFacade::model($companyPolicy,array('route' => array('company-policy.update', $companyPolicy->id), 'method' => 'PUT','enctype' => "multipart/form-data")) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['company policy']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('branch',__('Branch'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('branch',$branch,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('title',__('Title'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('title',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('description', __('Description'),['class'=>'form-label'])}}
                {{ Collective\Html\FormFacade::textarea('description',null, array('class' => 'form-control')) }}
            </div>
        </div>
        <div class="col-md-12">
            {{Collective\Html\FormFacade::label('attachment',__('Attachment'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="attachment" class="form-label">
                    @php
                        $policyPath=\App\Models\Utility::getFile('uploads/companyPolicy/');
                        $logo=\App\Models\Utility::getFile('uploads/companyPolicy/');
                    @endphp
{{--                    <input type="file" class="form-control" name="attachment" id="attachment" data-filename="attachment_create">--}}
                    <input type="file" class="form-control " name="attachment" id="attachment" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">

{{--                    <img id="image" class="mt-3" style="width:25%;"/>--}}
                    <img id="image"  width="25%;" class="mt-3" src="@if($companyPolicy->attachment){{$policyPath.$companyPolicy->attachment}}@else{{$logo.'user-2_1654779769.jpg'}}@endif" />


                </label>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}


<script>
    document.getElementById('attachment').onchange = function () {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>



