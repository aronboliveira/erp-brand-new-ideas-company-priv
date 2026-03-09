@php
@endphp
{{Collective\Html\FormFacade::open(array('url'=>'roles','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="{{ VC::CL12 }} {{ VC::CM12 }} {{ VC::CS12 }}">
            <div class="{{ VC::FM_G }}">
                {{Collective\Html\FormFacade::label('name',__('Name'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Role Name')))}}
                @error('name')
                <small class="invalid-name" role="alert">
                    <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                </small>
                @enderror
            </div>
            @php
                try {
                    $tabs = [
                        ['key' => 'staff',        'label' => __('Staff')],
                        ['key' => 'crm',          'label' => __('CRM')],
                        ['key' => 'project',      'label' => __('Project')],
                        ['key' => 'hrmpermission','label' => __('HRM')],
                        ['key' => 'account',      'label' => __('Account')],
                        ['key' => 'pos',          'label' => __('POS')],
                    ];
                } catch (\Throwable $e) {
                    \Log::error('roles/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                @foreach($tabs as $tab)
                    <li class="{{ VC::NV_IT }}">
                        <a
                            class="{{ VC::NV_LK }} @if($loop->first) active @endif"
                            id="pills-{{ $tab['key'] }}-tab"
                            data-bs-toggle="pill"
                            href="#{{ $tab['key'] }}"
                            role="tab"
                            aria-controls="pills-{{ $tab['key'] }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        >
                            {{ $tab['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="{{ VC::TAB_FD_SH }} active" id="staff" role="tabpanel" aria-labelledby="pills-home-tab">
                    @php
                        try {
                            $modules=['user','role','client','product & service','constant unit','constant tax','constant category','company settings'];
                                                   if( Auth::user()->type == 'company'){
                               $modules[] = 'permission';
                                                   }
                        } catch (\Throwable $e) {
                            \Log::error('roles/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6 class="{{ VC::MY3 }}">{{__('Assign General Permission to Roles')}}</h6>
                                <table class="table table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input" name="staff_checkall"  id="staff_checkall" >
                                        </th>
                                        <th>{{__('Module')}} </th>
                                        <th>{{__('Permissions')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck staff_checkall"  data-id="{{str_replace(' ', '', str_replace('&', '', $module))}}" ></td>
                                            <td><label class="ischeck staff_checkall" data-id="{{str_replace(' ', '', str_replace('&', '', $module))}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '',  str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                        @if($key = array_search('income vs expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="crm" role="tabpanel" aria-labelledby="pills-profile-tab">
                    @php
                        $modules=['crm dashboard','lead','pipeline','lead stage','source','label','deal','stage','task','form builder','form response','contract','contract type'];
@endphp
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6 class="{{ VC::MY3 }}">{{__('Assign CRM related Permission to Roles')}}</h6>
                                <table class="table table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input custom_align_middle" name="crm_heckall"  id="crm_checkall" >
                                        </th>
                                        <th>{{__('Module')}} </th>
                                        <th>{{__('Permissions')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck crm_checkall"  data-id="{{str_replace(' ', '', $module)}}" ></td>
                                            <td><label class="ischeck crm_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                        @if($key = array_search('income vs expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="project" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['project dashboard','project','milestone','grant chart','project stage','timesheet','expense','project task','activity','CRM activity','project task stage','bug report','bug status'];
@endphp
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6 class="{{ VC::MY3 }}">{{__('Assign Project related Permission to Roles')}}</h6>
                                <table class="table table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input align-middle custom_align_middle" name="project_checkall"  id="project_checkall" >
                                        </th>
                                        <th>{{__('Module')}} </th>
                                        <th>{{__('Permissions')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input align-middle ischeck project_checkall"  data-id="{{str_replace(' ', '', $module)}}" ></td>
                                            <td><label class="ischeck project_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input  isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                        @if($key = array_search('income vs expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="hrmpermission" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['hrm dashboard','employee','employee profile','department','designation','branch','document type','document','payslip type','allowance','commission','allowance option','loan option','deduction option','loan','saturation deduction','other payment','overtime','set salary','pay slip','company policy','appraisal','goal tracking','goal type','indicator','event','meeting','training','trainer','training type','award','award type','resignation','travel','promotion','complaint','warning','termination','termination type','job application','job application note','job onBoard','job category','job','job stage','custom question','interview schedule','estimation','holiday','transfer','announcement','leave','leave type','attendance'];
@endphp

                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6 class="{{ VC::MY3 }}">{{__('Assign HRM related Permission to Roles')}}
                                </h6>

                                <table class="table table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input align-middle custom_align_middle" name="hrm_checkall"  id="hrm_checkall" >
                                        </th>
                                        <th>{{__('Module')}} </th>
                                        <th>{{__('Permissions')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input align-middle ischeck hrm_checkall"  data-id="{{str_replace(' ', '', $module)}}" ></td>
                                            <td><label class="ischeck hrm_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">

                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                        @if($key = array_search('income vs expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['account dashboard','proposal','invoice','bill','revenue','payment','proposal product','invoice product','bill product','goal','credit note','debit note','bank account','bank transfer','transaction','customer','vendor','constant custom field','assets','chart of account','journal entry','report'];
@endphp
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6 class="{{ VC::MY3 }}">{{__('Assign Account related Permission to Roles')}}</h6>
                                <table class="table table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input custom_align_middle" name="account_checkall"  id="account_checkall" >
                                        </th>
                                        <th>{{__('Module')}} </th>
                                        <th>{{__('Permissions')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck"  data-id="{{str_replace(' ', '', $module)}}" ></td>
                                            <td><label class="ischeck" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                    @if($key = array_search('income vs expense '.$module,$permissions))
                                                        <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                            {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                            {{Collective\Html\FormFacade::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                        </div>
                                                    @endif
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pos" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['warehouse','purchase','pos','barcode'];
@endphp
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::FM_G }}">
                            @if(!empty($permissions))
                                <h6 class="{{ VC::MY3 }}">{{__('Assign POS related Permission to Roles')}}</h6>
                                <table class="table table-striped {{ VC::MB0 }}" id="dataTable-1">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input custom_align_middle" name="pos_checkall"  id="pos_checkall" >
                                        </th>
                                        <th>{{__('Module')}} </th>
                                        <th>{{__('Permissions')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck"  data-id="{{str_replace(' ', '', $module)}}" ></td>
                                            <td><label class="ischeck" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="{{ ViewClassNamesConstants::CST_CT_CB_MD3 }}">
                                                                {{Collective\Html\FormFacade::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Collective\Html\FormFacade::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="{{ VC::BT_PRM }}">
</div>

{{Collective\Html\FormFacade::close()}}

<script>
    $(document).ready(function () {
        $("#staff_checkall").click(function(){
            $('.staff_checkall').not(this).prop('checked', this.checked);
        });
        $("#crm_checkall").click(function(){
            $('.crm_checkall').not(this).prop('checked', this.checked);
        });
        $("#project_checkall").click(function(){
            $('.project_checkall').not(this).prop('checked', this.checked);
        });
        $("#hrm_checkall").click(function(){
            $('.hrm_checkall').not(this).prop('checked', this.checked);
        });
        $("#account_checkall").click(function(){
            $('.account_checkall').not(this).prop('checked', this.checked);
        });
        $("#pos_checkall").click(function(){
            $('.pos_checkall').not(this).prop('checked', this.checked);
        });
        $(".ischeck").click(function(){
            var ischeck = $(this).data('id');
            $('.isscheck_'+ ischeck).prop('checked', this.checked);
        });
    });
</script>
