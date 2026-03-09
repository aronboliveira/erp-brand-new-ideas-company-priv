{{Form::model($user,array('route' => array('user.password.update', $user->id), 'method' => 'post')) }}
<div class="modal-body">
    <div class="row">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('password', __('Password')) }}
            <input id="password" type="password" class="{{ VC::FM_CT }} @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
            @error('password')
            <span class="{{ VC::INV_FB }}" role="alert">
               <strong>{{ $message }}</strong>
           </span>
            @enderror
        </div>
        <div class="{{ VC::FM_G }}">
            {{ Form::label('password_confirmation', __('Confirm Password')) }}
            <input id="password-confirm" type="password" class="{{ VC::FM_CT }}" name="password_confirmation" required autocomplete="new-password">
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn {{ VC::BT_PM }}">
</div>
{{Form::close()}}

