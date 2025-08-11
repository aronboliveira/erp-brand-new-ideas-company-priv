{{ Collective\Html\FormFacade::model($lead, array('route' => array('leads.update', $lead->id), 'method' => 'PUT')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['lead']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('subject', __('Subject'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::text('subject', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('user_id', __('User'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('user_id', $users,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('email', __('Email'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::email('email', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('phone', __('Phone'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::text('phone', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('pipeline_id', __('Pipeline'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('pipeline_id', $pipelines,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('stage_id', __('Stage'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('stage_id', [''=>__('Select Stage')],null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('sources', __('Sources'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('sources[]', $sources,null, array('class' => 'form-control select2','id'=>'choices-multiple1','multiple'=>'','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('products', __('Products'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('products[]', $products,null, array('class' => 'form-control select2','id'=>'choices-multiple2','multiple'=>'','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('notes', __('Notes'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('notes',null, array('class' => 'summernote-simple')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}



<script>
    var stage_id = '{{$lead->stage_id}}';

    $(document).ready(function () {
        var pipeline_id = $('[name=pipeline_id]').val();
        getStages(pipeline_id);
    });

    $(document).on("change", "#commonModal select[name=pipeline_id]", function () {
        var currVal = $(this).val();
        console.log('current val ', currVal);
        getStages(currVal);
    });

    function getStages(id) {
        $.ajax({
            url: '{{route('leads.json')}}',
            data: {pipeline_id: id, _token: $('meta[name="csrf-token"]').attr('content')},
            type: 'POST',
            success: function (data) {
                var stage_cnt = Object.keys(data).length;
                $("#stage_id").empty();
                if (stage_cnt > 0) {
                    $.each(data, function (key, data1) {
                        var select = '';
                        if (key == '{{ $lead->stage_id }}') {
                            select = 'selected';
                        }
                        $("#stage_id").append('<option value="' + key + '" ' + select + '>' + data1 + '</option>');
                    });
                }
                $("#stage_id").val(stage_id);
                $('#stage_id').select2({
                    placeholder: "{{__('Select Stage')}}"
                });
            }
        })
    }
</script>
