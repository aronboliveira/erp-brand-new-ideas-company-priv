@php
    use App\Config\Constants\ExtendingLayoutsConstants as EL;
@endphp

@extends(EL::ADM)

@section('page-title')
    {{ __('Permission Details') }}
@endsection

@section('content')
<div class="row">
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_HD }}">
                <h5>{{ __('Permission') }}: {{ $permission->name ?? __('Unknown') }}</h5>
            </div>
            <div class="{{ VC::CD_BD }}">
                <p><strong>{{ __('Name') }}:</strong> {{ $permission->name ?? '' }}</p>
                @if(isset($roles) && $roles->count())
                    <h6>{{ __('Assigned Roles') }}</h6>
                    <ul>
                        @foreach($roles as $role)
                            <li>{{ $role->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="{{ VC::TXT_MT }}">{{ __('No roles assigned.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
