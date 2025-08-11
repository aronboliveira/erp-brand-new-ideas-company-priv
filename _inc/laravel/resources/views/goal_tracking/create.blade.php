{{Collective\Html\FormFacade::open(array('url'=>'goal_trackings','method'=>'post'))}}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['goal tracking']) }}"
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
                {{Collective\Html\FormFacade::select('branch',$brances,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('goal_type',__('GoalTypes'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('goal_type',$goalTypes,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::date('start_date',null,array('class' => 'form-control'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::date('end_date',null,array('class' => 'form-control'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('subject',__('Subject'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('subject',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('target_achievement',__('Target Achievement'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('target_achievement',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('description',__('Description'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::textarea('description',null,array('class'=>'form-control description'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('status',__('Status'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('status',$status,null,array('class'=>'form-control select'))}}
            </div>
        </div>

        <div class="col-md-12">
            <fieldset id='demo1' class="rating">
                <input class="stars" type="radio" id="rating-5" name="rating" value="5" >
                <label class="full" for="rating-5" title="Awesome - 5 stars"></label>
                <input class="stars" type="radio" id="rating-4" name="rating" value="4" >
                <label class="full" for="rating-4" title="Pretty good - 4 stars"></label>
                <input class="stars" type="radio" id="rating-3" name="rating" value="3" >
                <label class="full" for="rating-3" title="Meh - 3 stars"></label>
                <input class="stars" type="radio" id="rating-2" name="rating" value="2" >
                <label class="full" for="rating-2" title="Kinda bad - 2 stars"></label>
                <input class="stars" type="radio" id="technical-1" name="rating" value="1" >
                <label class="full" for="technical-1" title="Sucks big time - 1 star"></label>
            </fieldset>
        </div>



    </div>
    <div class="modal-footer">

        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
    </div>

{{Collective\Html\FormFacade::close()}}
