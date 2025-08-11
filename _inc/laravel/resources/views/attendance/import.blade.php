{{ Collective\Html\FormFacade::open(array('route' => array('attendances.import'),'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12 mb-6">
                {{Collective\Html\FormFacade::label('file',__('Download sample employee CSV file'),['class'=>'form-label'])}}
                <a href="{{asset(Storage::url('uploads/sample')).'/sample_attendance.csv'}}" class="{{ ViewClassNamesConstants::BT_SM_PM }}">
                    <i class="{{ ViewClassNamesConstants::TI_DWN }}"></i> {{__('Download')}}
                </a>
            </div>
            <div class="col-md-12">
                {{Collective\Html\FormFacade::label('file',__('Select CSV File'),['class'=>'form-label'])}}
                <div class="choose-file form-group">
                    <label for="file" class="form-label">
                        <input type="file" class="form-control" name="file" id="file" data-filename="upload_file" required>
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Upload')}}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
{{ Collective\Html\FormFacade::close() }}
