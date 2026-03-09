{{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_G }}">
                {{Form::label('name',__('Name'),['class'=>'form-label']) }}
                {{Form::text('name',null,array('class'=>'form-control font-style','placeholder'=>__('Enter User Name')))}}
                @error('name')
                <small class="invalid-name" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_G }}">
                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email')))}}
                @error('email')
                <small class="invalid-email" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        @if(\Auth::user()->type != 'super admin')
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('role', __('User Role'),['class'=>'form-label']) }}
                {!! Form::select('role', $roles, $user->roles,array('class' => 'form-control select','required'=>'required')) !!}
                @error('role')
                <small class="invalid-role" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        @endif
        @if(!$customFields->isEmpty())
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>

</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light"data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn {{ VC::BT_PM }}">
</div>

{{Form::close()}}
