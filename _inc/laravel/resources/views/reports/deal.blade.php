@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Deal')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Deal Report')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_deals_report_unavailable') ?? 'Download function for deals report is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-deals-pdf-link"
        class="{{ VC::BT_SM_PM }} download-deals-report"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/deals/download.js') }}" defer></script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}" id="printableArea">
        <div class="{{ VC::CS12 }}">
            <input type="hidden" value="{{ __('Deal Report') }}" id="filename">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CXL3 }}">
                    @php
                        $reports = [
                            ['id' => 'general-report',  'label' => __('General Report')],
                            ['id' => 'staff-report',    'label' => __('Staff Report')],
                            ['id' => 'pipeline-report', 'label' => __('Pipelines Report')],
                        ];
                    @endphp
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                        <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                            @forelse($reports as $report)
                                <a href="#{{ $report['id'] }}" class="{{ VC::LGI_ACT_NBD }}">
                                    {{ $report['label'] }}
                                    <div class="{{ VC::FEND }}">
                                        <i class="{{ VC::TI_CHV_RT }}"></i>
                                    </div>
                                </a>
                            @empty
                                <span class="d-block px-3 py-2">{{ __('No report sections available') }}</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div id="general-report">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('This Week Deals Conversions') }}</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div id="deals-this-week" data-color="primary" data-height="280">
                                    <div class="text-muted small">{{ __('Chart will appear here when data is available') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Sources Conversion') }}</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="deals-sources-report" id="deals-sources-report" data-color="primary" data-height="280">
                                    <div class="text-muted small">{{ __('Chart will appear here when data is available') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <div class="{{ VC::RW }}">
                                    <div class="col-9">
                                        <h5>{{ __('Monthly') }}</h5>
                                    </div>
                                    <div class="col-3 {{ VC::FEND }}">
                                        @php $selMonth = (string)request('month', ''); @endphp
                                        <select name="month" class="{{ VC::FM_CT }} selectpicker" id="selectmonth" data-none-selected-text="{{ __('Nothing selected') }}">
                                            <option value="">{{ __('Select Month') }}</option>
                                            <option value="1"  {{ $selMonth==='1'  ? 'selected' : '' }}>{{ __('January') }}</option>
                                            <option value="2"  {{ $selMonth==='2'  ? 'selected' : '' }}>{{ __('February') }}</option>
                                            <option value="3"  {{ $selMonth==='3'  ? 'selected' : '' }}>{{ __('March') }}</option>
                                            <option value="4"  {{ $selMonth==='4'  ? 'selected' : '' }}>{{ __('April') }}</option>
                                            <option value="5"  {{ $selMonth==='5'  ? 'selected' : '' }}>{{ __('May') }}</option>
                                            <option value="6"  {{ $selMonth==='6'  ? 'selected' : '' }}>{{ __('June') }}</option>
                                            <option value="7"  {{ $selMonth==='7'  ? 'selected' : '' }}>{{ __('July') }}</option>
                                            <option value="8"  {{ $selMonth==='8'  ? 'selected' : '' }}>{{ __('August') }}</option>
                                            <option value="9"  {{ $selMonth==='9'  ? 'selected' : '' }}>{{ __('September') }}</option>
                                            <option value="10" {{ $selMonth==='10' ? 'selected' : '' }}>{{ __('October') }}</option>
                                            <option value="11" {{ $selMonth==='11' ? 'selected' : '' }}>{{ __('November') }}</option>
                                            <option value="12" {{ $selMonth==='12' ? 'selected' : '' }}>{{ __('December') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="{{ VC::MT3 }}">
                                    <div id="deals-monthly" data-color="primary" data-height="280">
                                        <div class="text-muted small">{{ __('Chart will appear here when data is available') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="staff-report" class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Staff Report') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM4 }}">
                                    {{ Form::label('from_date', __('From Date'), ['class'=>'col-form-label']) }}
                                    {{ Form::date('from_date', request('from_date'), ['class' => VC::FM_CT.' from_date','id'=>'data_picker1']) }}
                                    <span id="fromDate" class="d-block small" style="color: red;"></span>
                                </div>
                                <div class="{{ VC::CM4 }}">
                                    {{ Form::label('to_date', __('To Date'), ['class'=>'col-form-label']) }}
                                    {{ Form::date('to_date', request('to_date'), ['class' => VC::FM_CT.' to_date','id'=>'data_picker2']) }}
                                    <span id="toDate" class="d-block small" style="color: red;"></span>
                                </div>
                                <div class="{{ VC::CM4 }}" id="filter_type" style="padding-top:38px;">
                                    <button class="{{ VC::BT_PRM }} label-margin generate_button" type="button">{{ __('Generate') }}</button>
                                </div>
                            </div>
                            <div id="deals-staff-report" class="{{ VC::MT3 }}" data-color="primary" data-height="280">
                                <div class="text-muted small">{{ __('Chart will appear here when data is available') }}</div>
                            </div>
                        </div>
                    </div>

                    <div id="pipeline-report" class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Pipeline Report') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="{{ VC::RW }}">
                                <div id="deals-piplines-report" data-color="primary" data-height="280">
                                    <div class="text-muted small">{{ __('Chart will appear here when data is available') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/reports/deals/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/deals/pdf.js') }}"></script>
    <script async>
        (function(){
            const $=window.jQuery;
            const qs=(s,r=document)=>r.querySelector(s);
            const errFb="# ERROR";
            const dataClientLocalized="data-client-localized";
            const dataGuardMsg="data-guard-msg";
            const dataErrGuard="data-error-guard";
            const dataListenerGuard="data-listener-guard";
            const ensureToastContainer=()=>{const id="np-toast-container";let c=qs("#"+id);if(c){return c;}c=document.createElement("div");c.id=id;c.setAttribute("aria-live","polite");c.setAttribute("aria-atomic","true");c.style.position="fixed";c.style.top="1rem";c.style.right="1rem";document.body.appendChild(c);return c;};
            const showErrorNow=(message)=>{const hasBootstrap=(qs('link[rel="stylesheet"][href*="bootstrap"]')||qs('link[href*="bootstrap"]'))&&window.bootstrap&&window.bootstrap.Toast;if(hasBootstrap){const container=ensureToastContainer();const tid="np-toast";let t=qs("#"+tid,container);if(!t){t=document.createElement("div");t.id=tid;t.className="toast";t.setAttribute("role","alert");t.setAttribute("aria-live","assertive");t.setAttribute("aria-atomic","true");t.innerHTML='<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';container.appendChild(t);}const body=qs(".toast-body",t);if(body){body.textContent=message??errFb;}try{new window.bootstrap.Toast(t,{autohide:true,delay:4000}).show();}catch(_){alert(message??errFb);}}else{alert(message??errFb);}};
            const scheduleInteractiveError=(message)=>{const host=document.body;if(!host||host.getAttribute(dataErrGuard)==="true"){return;}host.setAttribute(dataErrGuard,"true");const once=()=>{try{showErrorNow(message);}finally{host.removeAttribute(dataErrGuard);}};document.addEventListener("pointerup",once,{once:true});};
            const getMsgFor=(el,key)=>{const errFbL=errFb;const dataClientLocalizedL=dataClientLocalized;const dataGuardMsgL=dataGuardMsg;let msg=errFbL;if(el.getAttribute("data-sv-localized")==="true"||el.getAttribute(dataClientLocalizedL)==="true"){msg=el.getAttribute(dataGuardMsgL)||errFbL;}else{let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-");lang=lang==="pt-br"?lang:lang.slice(0,2);const msgKey=key;msg=window.translations?.[lang]?.[msgKey]||el.getAttribute(dataGuardMsgL)||window.translations?.["en"]?.[msgKey]||errFbL;if(msg!==errFbL){el.setAttribute(dataGuardMsgL,msg);el.setAttribute(dataClientLocalizedL,"true");}}return msg;};
            const bindWithObserver=(el,evt,handler,flag)=>{if(!el||el.getAttribute(flag)==="true"){return;}el.setAttribute(flag,"true");$(el).on(evt,handler);const mo=new MutationObserver((m,o)=>{if(!document.body.contains(el)){$(el).off(evt,handler);o.disconnect();}});mo.observe(document.body,{childList:true,subtree:true});};
            const renderBarChart=(selector,options)=>{const target=qs(selector);if(!target){return;}if(typeof window.ApexCharts!=="function"){try{
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("ApexCharts unavailable");
            }catch(_){ }scheduleInteractiveError(getMsgFor(target,"plugin_unavailable"));return;}try{target.innerHTML="";const chart=new window.ApexCharts(target,options);chart.render();}catch(_){scheduleInteractiveError(getMsgFor(target,"chart_unavailable"));}};
            const onSelectMonthChange=function(){const start_month=$(".selectpicker").val();try{$.ajax({url:"{{route(VW::RPT.'.deal')}}",type:"get",data:{start_month:start_month,_token:"{{ csrf_token() }}"},cache:false,success:function(data){$("#deals-monthly").empty();renderBarChart("#deals-monthly",{series:[{name:"Deal",data:data?.data??[]}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:data?.name??[],title:{text:'{{ __("Deal Per Month") }}'}},colors:["#c53da9","#c53da9"],grid:{strokeDashArray:4},legend:{show:false}});},error:function(){scheduleInteractiveError(getMsgFor(document.body,"endpoint_unavailable"));}});}catch(_){scheduleInteractiveError(getMsgFor(document.body,"endpoint_unavailable"));}};
            const onGenerateStaff=function(){const from_date=$(".from_date").val();const to_date=$(".to_date").val();if(from_date===""){$("#fromDate").text("Please select date");}else{$("#fromDate").empty();}if(to_date===""){$("#toDate").text("Please select date");}else{$("#toDate").empty();}try{$.ajax({url:"{{ route(VW::RPT.'.deal') }}",type:"get",data:{From_Date:from_date,To_Date:to_date,type:"deal_staff_repport",_token:"{{ csrf_token() }}"},cache:false,success:function(data){$("#deals-staff-report").empty();renderBarChart("#deals-staff-report",{series:[{name:"Deal",data:data?.data??[]}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:data?.name??[]},colors:["#6fd944","#6fd944"],grid:{strokeDashArray:4},legend:{show:false}});},error:function(){scheduleInteractiveError(getMsgFor(document.body,"endpoint_unavailable"));}});}catch(_){scheduleInteractiveError(getMsgFor(document.body,"endpoint_unavailable"));}};
            const onGenerateClients=function(){const from_date=$(".from_date1").val();const to_date=$(".to_date1").val();if(from_date===""){$("#fromDate1").text("Please select date");}else{$("#fromDate1").empty();}if(to_date===""){$("#toDate1").text("Please select date");}else{$("#toDate1").empty();}try{$.ajax({url:"{{route(VW::RPT.'.deal')}}",type:"get",data:{from_date:from_date,to_date:to_date,type:"client_repport",_token:"{{ csrf_token() }}"},cache:false,success:function(data){$("#deals-clients-report").empty();renderBarChart("#deals-clients-report",{series:[{name:"Deal",data:data?.data??[]}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:data?.name??[]},colors:["#6fd944","#6fd944"],grid:{strokeDashArray:4},legend:{show:false}});},error:function(){scheduleInteractiveError(getMsgFor(document.body,"endpoint_unavailable"));}});}catch(_){scheduleInteractiveError(getMsgFor(document.body,"endpoint_unavailable"));}};
            const initDynamic=()=>{bindWithObserver(document.getElementById("selectmonth"),"change",onSelectMonthChange,dataListenerGuard+"-month");document.querySelectorAll(".generate_button").forEach(el=>bindWithObserver(el,"click",onGenerateStaff,dataListenerGuard+"-gen-staff"));bindWithObserver(document.getElementById("generatebtn"),"click",onGenerateClients,dataListenerGuard+"-gen-client");};
            const initStaticCharts=()=>{const pieTarget=qs("#deals-this-week");if(pieTarget){if(typeof window.ApexCharts!=="function"){try{
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("ApexCharts unavailable");
            }catch(_){ }scheduleInteractiveError(getMsgFor(pieTarget,"plugin_unavailable"));}else{try{const options={series:{!! json_encode($devicearray['data']) !!},chart:{width:350,type:"pie"},colors:["#35abb6","#ffa21d","#ff3a6e","#6fd943","#5c636a","#181e28","#0288d1"],labels:{!! json_encode($devicearray['label']) !!},responsive:[{breakpoint:480,options:{chart:{width:200},legend:{position:"bottom"}}}]};pieTarget.innerHTML="";const chart=new window.ApexCharts(pieTarget,options);chart.render();}catch(_){scheduleInteractiveError(getMsgFor(pieTarget,"chart_unavailable"));}}}
            renderBarChart("#deals-sources-report",{series:[{name:"Source",data:{!! json_encode($dealsourceeData) !!}}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:{!! json_encode($dealsourceName) !!},title:{text:'{{ __("Source") }}'}},colors:["#6fd944","#6fd944"],grid:{strokeDashArray:4},legend:{show:false}});
            renderBarChart("#deals-monthly",{series:[{name:"Deal",data:{!! json_encode($data) !!}}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:{!! json_encode($labels) !!},title:{text:'{{ __("Deal Per Month") }}'}},colors:["#ffa21d","#ffa21d"],grid:{strokeDashArray:4},legend:{show:false}});
            renderBarChart("#deals-piplines-report",{series:[{name:"Pipeline",data:{!! json_encode($dealpipelineeData) !!}}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:{!! json_encode($dealpipelineName) !!},title:{text:'{{ __("Pipelines") }}'}},yaxis:{title:{text:'{{ __("Deals") }}'}},colors:["#6fd944","#6fd944"],grid:{strokeDashArray:4},legend:{show:false}});
            renderBarChart("#deals-staff-report",{series:[{name:"Deal",data:{!! json_encode($dealUserData) !!}}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:{!! json_encode($dealUserName) !!},title:{text:'{{ __("User") }}'}},colors:["#6fd944","#6fd944"],grid:{strokeDashArray:4},legend:{show:false}});
            renderBarChart("#deals-clients-report",{series:[{name:"Deal",data:{!! json_encode($dealClientData) !!}}],chart:{height:300,type:"bar",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},dataLabels:{enabled:false},stroke:{width:2,curve:"smooth"},title:{text:"",align:"left"},xaxis:{categories:{!! json_encode($dealClientName) !!}},colors:["#6fd944","#6fd944"],grid:{strokeDashArray:4},legend:{show:false}});
            };
            const init=()=>{if(!window.jQuery){try{
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
            }catch(_){ }}initDynamic();initStaticCharts();};
            if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",init,{once:true});}else{init();}
        })();
    </script>
@endpush

