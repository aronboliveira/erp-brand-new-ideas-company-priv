{{Form::open(array('url'=>'users','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_G }}">
                {{Form::label('name',__('Name'),['class'=>'form-label']) }}
                {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
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
                {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                @error('email')
                <small class="invalid-email" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        @if(\Auth::user()->type != 'super admin')
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('role', __('User Role'),['class'=>'form-label']) }}
                {!! Form::select('role', $roles, null,array('class' => 'form-control select','required'=>'required')) !!}
                @error('role')
                <small class="invalid-role" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        @elseif(\Auth::user()->type == 'super admin')
            {!! Form::hidden('role', 'company', null,array('class' => 'form-control select2','required'=>'required')) !!}
        @endif
        <div class="{{ VC::CM6 }}">
            <div class="{{ VC::FM_G }}">
                {{Form::label('password',__('Password'),['class'=>'form-label'])}}
                {{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter User Password'),'required'=>'required','minlength'=>"6"))}}
                @error('password')
                <small class="invalid-password" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
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
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn {{ VC::BT_PM }}">
</div>

{{Form::close()}}
