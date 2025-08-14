@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{ViewsConstants, PermissionsConstants, UsersConstants};
    $fields = [
        [
            'name'        => 'name',
            'type'        => 'text',
            'label'       => __('Name'),
            'cols'        => 6,
            'attrs'       => ['class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'],
            'error_key'   => 'name',
        ],
        [
            'name'        => 'email',
            'type'        => 'text',
            'label'       => __('Email'),
            'cols'        => 6,
            'attrs'       => ['class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'],
            'error_key'   => 'email',
        ],
        [
            'name'        => 'password',
            'type'        => 'password',
            'label'       => __('Password'),
            'cols'        => 6,
            'attrs'       => ['class'=>'form-control','placeholder'=>__('Enter User Password'),'required'=>'required','minlength'=>6],
            'error_key'   => 'password',
        ],
    ];
@endphp

{{ Form::open(['url'=>'users','method'=>'post']) }}
<div class="modal-body">
    <div class="row">
        @foreach($fields as $f)
            <div class="col-md-{{ $f['cols'] }}">
                <div class="form-group">
                    {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                    {{ Form::{ $f['type'] }($f['name'], null, $f['attrs']) }}
                    @error($f['error_key'])
                        <small class="text-danger" role="alert"><strong>{{ $message }}</strong></small>
                    @enderror
                </div>
            </div>
        @endforeach

        @if(Auth::user()[UsersConstants::COL_TP] != PermissionsConstants::SA)
            <div class="form-group col-md-6">
                {{ Form::label('role', __('User Role'), ['class'=>'form-label']) }}
                {{ Form::select('role', $roles, null, ['class'=>'form-control select','required'=>'required']) }}
                @error('role')
                    <small class="text-danger" role="alert"><strong>{{ $message }}</strong></small>
                @enderror
            </div>
        @else
            {{ Form::hidden('role', PermissionsConstants::CPN) }}
        @endif

        @if(!$customFields->isEmpty())
            <div class="col-md-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include(ViewsConstants::CST_FD . '.formBuilder')
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}
