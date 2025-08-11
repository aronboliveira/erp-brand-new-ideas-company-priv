{{ Collective\Html\FormFacade::open(['route' => ['projects.expenses.store',$project->id],'id' => 'create_expense','enctype' => 'multipart/form-data']) }}
<div class="modal-body">

<div class="row">
    <div class="col-12 col-md-12">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label('name', __('Name'),['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, ['class' => 'form-control','required'=>'required']) }}
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label('date', __('Date'),['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::date('date', null, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="form-group">
          {{Collective\Html\FormFacade::label('amount',__('Amount'),['class'=>'form-label'])}}
          <div class="form-group price-input input-group search-form">
              <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
              {{Collective\Html\FormFacade::number('amount',null,array('class'=>'form-control','required' => 'required','min' => '0'))}}
          </div>
      </div>

    </div>
    <div class="col-12 col-md-4">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label('task_id', __('Task'),['class' => 'form-label']) }}
            <select class="form-control select" name="task_id" id="task_id">
                <option value="0"  disabled selected>Choose Task</option>
                @foreach($project->tasks as $task)
                    <option value="{{ $task->id }}">{{ $task->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12 col-md-12">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label('description', __('Description'),['class' => 'form-label']) }}
            <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
            {{ Collective\Html\FormFacade::textarea('description', null, ['class' => 'form-control','rows' => '1','data-toggle' => 'autosize']) }}
        </div>
    </div>


    <div class="col-12 col-md-12">
        {{Collective\Html\FormFacade::label('attachment',__('Attachment'),['class'=>'form-label'])}}
        <div class="choose-file form-group">
            <label for="attachment" class="form-label">
                <div>{{__('Choose file here')}}</div>
                <input type="file" class="form-control" name="attachment" id="attachment" data-filename="attachment_create">
            </label>
            <p class="attachment_create"></p>
        </div>
    </div>


</div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{ Collective\Html\FormFacade::close() }}

