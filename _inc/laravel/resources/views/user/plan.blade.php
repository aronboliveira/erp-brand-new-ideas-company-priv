<div class="modal-body">
    <div class="card">
        <div class="{{ VC::CD_BD_TB_BD }}">
            <div class="{{ VC::TB_RSP }}">
                <table class="table datatable">
                    @foreach($plans as $plan)
                        <tr>
                            <td><h6>{{$plan->name}} ({{($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : '$'}}{{$plan->price}}) {{' / '. $plan->duration}}</h6></td>
                            <td>{{__('Users')}} : {{$plan->max_users}}</td>
                            <td>{{__('Customers')}} : {{$plan->max_customers}}</td>
                            <td>{{__('Vendors')}} : {{$plan->max_vendors}}</td>
                            <td>
                                @if($user->plan==$plan->id)
                                    <span class="{{ VC::BT_SM_PM }} my-auto"><i class="ti ti-check "></i></span>
                                @else
                                    <a href="{{route('plan.active',[$user->id,$plan->id])}}" class="{{ VC::BT_SM }} btn-warning my-auto" title="{{__('Click to Upgrade Plan')}}"><i class="ti ti-shopping-cart-plus"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
