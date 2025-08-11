{{ Collective\Html\FormFacade::model($project, array('route' => array('project.copy.store', $project->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('project','all','', ['class' => 'form-check-input ','id'=>'all']) }}
                    {{ Collective\Html\FormFacade::label('all', __('All'),['class'=>'form-check-label'])}}
                </div>
            </div>
            {{-- project task --}}
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('task[]','task','', ['class' => 'form-check-input checkbox','id'=>'task']) }}
                    {{ Collective\Html\FormFacade::label('task', __('Task'),['class'=>'form-check-label'])}}
                </div>
            </div>
            <div class="row mx-4">
                <div class="col-4 form-group">
                    <div class="form-check">
                        {{ Collective\Html\FormFacade::checkbox('task[]','sub_task','', ['class' => 'form-check-input checkbox task','id'=>'sub_task']) }}
                        {{ Collective\Html\FormFacade::label('sub_task', __('Sub Task'),['class'=>'form-check-label'])}}
                    </div>
                </div>
                <div class="col-4 form-group">
                    <div class="form-check">
                        {{ Collective\Html\FormFacade::checkbox('task[]','task_comment','', ['class' => 'form-check-input checkbox task','id'=>'task_comment']) }}
                        {{ Collective\Html\FormFacade::label('task_comment', __('Comment'),['class'=>'form-check-label'])}}
                    </div>
                </div>
                <div class="col-4 form-group">
                    <div class="form-check">
                        {{ Collective\Html\FormFacade::checkbox('task[]','task_files','', ['class' => 'form-check-input checkbox task','id'=>'task_files']) }}
                        {{ Collective\Html\FormFacade::label('task_files', __('Files'),['class'=>'form-check-label'])}}
                    </div>
                </div>
            </div>
            {{-- project bug --}}
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('bug[]','bug','', ['class' => 'form-check-input checkbox','id'=>"bug"]) }}
                    {{ Collective\Html\FormFacade::label('bug', __('Bug'),['class'=>'form-check-label'])}}
                </div>
            </div>
            <div class="row mx-4">
                    <div class="col-6 form-group">
                        <div class="form-check">
                            {{ Collective\Html\FormFacade::checkbox('bug[]','bug_comment','', ['class' => 'form-check-input checkbox bug','id'=>'bug_comment']) }}
                            {{ Collective\Html\FormFacade::label('bug_comment', __('Comment'),['class'=>'form-check-label'])}}
                        </div>
                    </div>
                <div class="col-6 form-group">
                    <div class="form-check">
                        {{ Collective\Html\FormFacade::checkbox('bug[]','bug_files','', ['class' => 'form-check-input checkbox bug','id'=>'bug_files']) }}
                        {{ Collective\Html\FormFacade::label('bug_files', __('Files'),['class'=>'form-check-label'])}}
                    </div>
                </div>
            </div>

            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('user[]','user','', ['class' => 'form-check-input checkbox','id'=>"user"]) }}
                    {{ Collective\Html\FormFacade::label('user', __('Team Member'),['class'=>'form-check-label'])}}
                </div>
            </div>
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('client[]','client','', ['class' => 'form-check-input checkbox','id'=>"client"]) }}
                    {{ Collective\Html\FormFacade::label('client', __('Client'),['class'=>'form-check-label'])}}
                </div>
            </div>
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('milestone[]','milestone','', ['class' => 'form-check-input checkbox','id'=>"milestone"]) }}
                    {{ Collective\Html\FormFacade::label('milestone', __('Milestone'),['class'=>'form-check-label'])}}
                </div>
            </div>
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('project_file[]','project_file','', ['class' => 'form-check-input checkbox','id'=>"project_file"]) }}
                    {{ Collective\Html\FormFacade::label('project_file', __('Project File'),['class'=>'form-check-label'])}}
                </div>
            </div>
            <div class="form-group m-2">
                <div class="form-check">
                    {{ Collective\Html\FormFacade::checkbox('activity[]','activity','', ['class' => 'form-check-input checkbox','id'=>"activity"]) }}
                    {{ Collective\Html\FormFacade::label('activity', __('Activity'),['class'=>'form-check-label'])}}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn btn-primary">{{__('Copy')}}</button>
</div>

{{ Collective\Html\FormFacade::close() }}

<script>
    $(document).ready(function(){
        $('#all').on('click',function(){
            if(this.checked){
                $('.checkbox').each(function(){
                    this.checked = true;
                });
            }else{
                $('.checkbox').each(function(){
                    this.checked = false;
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function(){
        $("#sub_task").click(function(){
            $("#task").prop("checked", true);
        });
        $("#task_comment").click(function(){
            $("#task").prop("checked", true);
        });
        $("#task_files").click(function(){
            $("#task").prop("checked", true);
        });
        $("#bug_comment").click(function(){
            $("#bug").prop("checked", true);
        });
        $("#bug_files").click(function(){
            $("#bug").prop("checked", true);
        });

        $('#task').on('click',function(){
            $('.task').each(function(){
                this.checked = false;
            });
        });
        $('#bug').on('click',function(){
            $('.bug').each(function(){
                this.checked = false;
            });
        });
    });
</script>

