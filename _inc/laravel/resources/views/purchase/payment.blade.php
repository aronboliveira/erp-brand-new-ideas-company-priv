@php
    use App\Config\Constants\ViewsConstants;
@endphp
{{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::PRC.'.payment', $purchase->id),'method'=>'post','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('date', __('Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::date('date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('amount',$purchase->getDue(), array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('account_id', __('Account'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('account_id',$accounts,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>

        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('reference', '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Collective\Html\FormFacade::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('description', '', array('class' => 'form-control','rows'=>3)) }}
        </div>


        <div class="col-md-6 form-group">
            {{ Collective\Html\FormFacade::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
            <div class="choose-file ">
                <label for="file" class="form-label">
                    <input type="file" name="add_receipt" id="image" class="form-control"  >
                </label>
                <p class="upload_file"></p>

            </div>
        </div>


    </div>
    <div class="modal-footer">

        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Add')}}" class="btn btn-primary">
    </div>

</div>
{{ Collective\Html\FormFacade::close() }}

