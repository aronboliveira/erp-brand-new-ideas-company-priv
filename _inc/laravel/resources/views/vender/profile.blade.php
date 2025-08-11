@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants,
        YieldingConstants,
    };
    $profile=\App\Models\Utility::getFile('uploads/avatar/');
//  $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Profile Account')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-12">
            <div class="card profile-card">
                <div class="icon-user avatar rounded-circle">
                    <img alt="" src="{{(!empty($userDetail[UsersConstants::COL_AV]))? $profile.'/'.$userDetail[UsersConstants::COL_AV] : $profile.'/avatar.png'}}" class="">
                </div>
                <h4 class="h4 mb-0 mt-2"> {{$userDetail[UsersConstants::COL_NM]}}</h4>
                <div class="sal-right-card">
                    <span class="badge badge-pill badge-blue">{{$userDetail[UsersConstants::COL_TP]}}</span>
                </div>
                <h6 class="office-time mb-0 mt-4">{{$userDetail[UsersConstants::COL_EM]}}</h6>
            </div>
        </div>
        <div class="col-xl-9 col-lg-8 col-md-8 col-sm-12">
            <section class="col-lg-12 pricing-plan card">
                <div class="our-system password-card p-3">
                    @php
                        use Collective\Html\FormFacade as Form;
                        use App\Config\Constants\ViewsConstants;
                        $tabs = [
                            [
                                'id'      => 'personal-info',
                                'label'   => __('Personal Info'),
                                'route'   => ['vendor.update.profile'],
                                'method'  => 'post',
                                'enctype' => 'multipart/form-data',
                                'fields'  => [
                                    ['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>6,'attrs'=>['class'=>'form-control font-style','placeholder'=>__('Enter User Name')],'error'=>'name'],
                                    ['name'=>'email','type'=>'text','label'=>__('Email'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter User Email')],'error'=>'email'],
                                    ['name'=>'contact','type'=>'text','label'=>__('Contact'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter User Contact')],'error'=>'contact'],
                                    ['name'=>'profile','type'=>'file','label'=>__('Choose file here'),'cols'=>6,'fileWrapper'=>true,'fileInput'=>['id'=>'avatar','data-filename'=>'profiles']],
                                ],
                            ],
                            [
                                'id'      => 'billing-info',
                                'label'   => __('Billing Info'),
                                'route'   => ['vendor.update.billing.info'],
                                'method'  => 'post',
                                'fields'  => [
                                    ['name'=>'billing_name','type'=>'text','label'=>__('Billing Name'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Name')],'error'=>'billing_name'],
                                    ['name'=>'billing_phone','type'=>'text','label'=>__('Billing Phone'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Phone')],'error'=>'billing_phone'],
                                    ['name'=>'billing_zip','type'=>'text','label'=>__('Billing Zip'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Zip')],'error'=>'billing_zip'],
                                    ['name'=>'billing_country','type'=>'text','label'=>__('Billing Country'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Country')],'error'=>'billing_country'],
                                    ['name'=>'billing_state','type'=>'text','label'=>__('Billing State'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing State')],'error'=>'billing_state'],
                                    ['name'=>'billing_city','type'=>'text','label'=>__('Billing City'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing City')],'error'=>'billing_city'],
                                    ['name'=>'billing_address','type'=>'textarea','label'=>__('Billing Address'),'cols'=>12,'attrs'=>['class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Billing Address')],'error'=>'billing_address'],
                                ],
                            ],
                            [
                                'id'      => 'shipping-info',
                                'label'   => __('Shipping Info'),
                                'route'   => ['vendor.update.shipping.info'],
                                'method'  => 'post',
                                'fields'  => [
                                    ['name'=>'shipping_name','type'=>'text','label'=>__('Shipping Name'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Name')],'error'=>'shipping_name'],
                                    ['name'=>'shipping_phone','type'=>'text','label'=>__('Shipping Phone'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Phone')],'error'=>'shipping_phone'],
                                    ['name'=>'shipping_zip','type'=>'text','label'=>__('Shipping Zip'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Zip')],'error'=>'shipping_zip'],
                                    ['name'=>'shipping_country','type'=>'text','label'=>__('Shipping Country'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Country')],'error'=>'shipping_country'],
                                    ['name'=>'shipping_state','type'=>'text','label'=>__('Shipping State'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping State')],'error'=>'shipping_state'],
                                    ['name'=>'shipping_city','type'=>'text','label'=>__('Shipping City'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping City')],'error'=>'shipping_city'],
                                    ['name'=>'shipping_address','type'=>'textarea','label'=>__('Shipping Address'),'cols'=>12,'attrs'=>['class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Shipping Address')],'error'=>'shipping_address'],
                                ],
                            ],
                            [
                                'id'      => 'change-password',
                                'label'   => __('Change Password'),
                                'route'   => ['vendor.update.password', $userDetail->id],
                                'method'  => 'post',
                                'fields'  => [
                                    ['name'=>'current_password','type'=>'password','label'=>__('Current Password'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Current Password')],'error'=>'current_password'],
                                    ['name'=>'new_password','type'=>'password','label'=>__('New Password'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter New Password')],'error'=>'new_password'],
                                    ['name'=>'confirm_password','type'=>'password','label'=>__('Re-type New Password'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Re-type New Password')],'error'=>'confirm_password'],
                                ],
                            ],
                        ];
                    @endphp
                    <div class="row">
                        <ul class="nav nav-tabs my-4">
                            @foreach($tabs as $tab)
                                <li class="{{ $loop->index > 0 ? 'annual-billing' : '' }}">
                                    <a data-toggle="tab"
                                    href="#{{ $tab['id'] }}"
                                    class="{{ $loop->first ? 'active' : '' }}">
                                        {{ $tab['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    
                        <div class="tab-content w-100">
                            @foreach($tabs as $tab)
                                <div id="{{ $tab['id'] }}"
                                    class="tab-pane fade {{ $loop->first ? 'in active show' : '' }}">
                                    {{ Form::model($userDetail, [
                                        'route'     => $tab['route'],
                                        'method'    => $tab['method'],
                                        'enctype'   => $tab['enctype'] ?? null
                                    ]) }}
                                    <div class="row">
                                        @foreach($tab['fields'] as $f)
                                            <div class="form-group col-md-{{ $f['cols'] }}">
                                                {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                                                @php $attrs = $f['attrs'] ?? ['class'=>'form-control']; @endphp
                                                @if($f['type']==='textarea')
                                                    {{ Form::textarea($f['name'], null, $attrs) }}
                                                @elseif(!empty($f['fileWrapper']))
                                                    <div class="choose-file">
                                                        <label for="{{ $f['fileInput']['id'] }}">
                                                            <div>{{ $f['label'] }}</div>
                                                            <input type="file"
                                                                name="{{ $f['name'] }}"
                                                                id="{{ $f['fileInput']['id'] }}"
                                                                class="form-control"
                                                                data-filename="{{ $f['fileInput']['data-filename'] }}">
                                                        </label>
                                                        <p class="{{ $f['fileInput']['data-filename'] }}"></p>
                                                    </div>
                                                @else
                                                    {{ Form::{ $f['type'] }($f['name'], null, $attrs) }}
                                                @endif
                                                @error($f['error'])
                                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        @endforeach
                                        @if($tab['id']==='personal-info' && !$customFields->isEmpty())
                                            <div class="col-md-6">
                                                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                                    @include(ViewsConstants::CST_FD.'.formBuilder')
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-12 text-end mt-3">
                                        <button type="submit" class="btn-create badge-blue">
                                            {{ $loop->last ? __('Save Changes') : __('Save Changes') }}
                                        </button>
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
