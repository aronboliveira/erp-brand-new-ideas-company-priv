@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{PermissionsConstants, UsersConstants, ViewsConstants};

    $fields = [
        ['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>6,'attrs'=>['class'=>'form-control font-style','placeholder'=>__('Enter User Name')],'error'=>'name'],
        ['name'=>'email','type'=>'text','label'=>__('Email'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter User Email')],'error'=>'email'],
    ];
@endphp
{{ Form::model($user,['route'=>[ViewsConstants::USR.'.update',$user?->id],'method'=>'PUT']) }}
<div class="modal-body">
  <div class="row">
    @foreach($fields as $f)
      <div class="col-md-{{ $f['cols'] }}">
        <div class="form-group">
          {{ Form::label($f['name'],$f['label'],['class'=>'form-label']) }}
          {{ Form::{$f['type']}($f['name'],null,$f['attrs']) }}
          @error($f['error'])
            <small class="text-danger" role="alert"><strong>{{ $message }}</strong></small>
          @enderror
        </div>
      </div>
    @endforeach
    @if(Auth::user()[UsersConstants::COL_TP] != PermissionsConstants::SA)
      <div class="form-group col-md-12">
        {{ Form::label('role',__('User Role'),['class'=>'form-label']) }}
        {{ Form::select('role',$roles,$user?->roles,['class'=>'form-control select','required'=>'required']) }}
        @error('role')
          <small class="text-danger" role="alert"><strong>{{ $message }}</strong></small>
        @enderror
      </div>
    @endif
    @if(!$customFields->isEmpty())
      <div class="col-md-6">
        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
          @include(ViewsConstants::CST_FD.'.formBuilder')
        </div>
      </div>
    @endif
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
  <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>
{{ Form::close() }}
