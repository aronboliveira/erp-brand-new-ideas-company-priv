@php
    use App\Config\Constants\ExtendingLayoutsConstants as EL;
@endphp

@extends(EL::ADM)

@section('page-title')
    {{ __('Edit Permission') }}
@endsection

@section('content')
<div class="row">
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_HD }}">
                <h5>{{ __('Edit Permission') }}: {{ $permission->name ?? '' }}</h5>
            </div>
            <div class="{{ VC::CD_BD }}">
                <form method="POST" action="{{ route('permissions.update', $permission->id ?? 0) }}">
                    @csrf
                    @method('PUT')
                    <div class="{{ VC::FM_GB3 }}">
                        <label for="name" class="{{ VC::FM_LB }}">{{ __('Permission Name') }}</label>
                        <input type="text" name="name" id="name" class="{{ VC::FM_CT }}" required maxlength="40" value="{{ $permission->name ?? '' }}">
                    </div>
                    @if(isset($roles) && $roles->count())
                        <div class="{{ VC::FM_GB3 }}">
                            <label class="{{ VC::FM_LB }}">{{ __('Roles') }}</label>
                            @foreach($roles as $role)
                                <div class="{{ VC::FM_CHK }}">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input" id="role_{{ $role->id }}"
                                        {{ isset($permission) && $permission->roles->contains($role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
