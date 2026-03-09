@php
    use App\Config\Constants\ExtendingLayoutsConstants as EL;
@endphp

@extends(EL::ADM)

@section('page-title')
    {{ __('Permissions') }}
@endsection

@section('content')
<div class="row">
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_HD }}">
                <h5>{{ __('Manage Permissions') }}</h5>
            </div>
            <div class="{{ VC::CD_BD }}">
                <p>{{ __('Permissions management page — stub view.') }}</p>
                @if(isset($permissions) && $permissions->count())
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->id }}</td>
                                        <td>{{ $permission->name }}</td>
                                        <td>
                                            <a href="{{ route('permissions.edit', $permission->id) }}" class="{{ VC::BT_SM_PM }}">{{ __('Edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="{{ VC::TXT_MT }}">{{ __('No permissions found.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
