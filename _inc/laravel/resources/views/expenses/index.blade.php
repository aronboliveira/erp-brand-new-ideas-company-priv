@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Expenses')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        window.translations = {
        ar: {
            url_copy_success: 'تم نسخ الرابط إلى الحافظة.',
            url_copy_failed:  'فشل نسخ الرابط.'
        },
        da: {
            url_copy_success: 'URL kopieret til udklipsholder.',
            url_copy_failed:  'Kunne ikke kopiere URL.'
        },
        de: {
            url_copy_success: 'URL in die Zwischenablage kopiert.',
            url_copy_failed:  'Konnte URL nicht kopieren.'
        },
        en: {
            url_copy_success: 'URL copied to clipboard.',
            url_copy_failed:  'Failed to copy URL.'
        },
        es: {
            url_copy_success: 'URL copiada al portapapeles.',
            url_copy_failed:  'Error al copiar la URL.'
        },
        fr: {
            url_copy_success: 'URL copiée dans le presse-papiers.',
            url_copy_failed:  'Échec de la copie de l’URL.'
        }
        };
    </script>
    <script defer>
        (() => {
            const SUCCESS_KEY = 'url_copy_success';
            const ERROR_KEY   = 'url_copy_failed';
            const ATTR_ACTIVE = 'data-listener-active';
            const SELECTOR    = '.copy_link';
        
            const showError = msg => {
            const hasBs = window.bootstrap?.Toast;
            if (hasBs) {
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-white bg-danger border-0';
                toast.setAttribute('role','alert');
                toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                </div>`;
                document.body.append(toast);
                new bootstrap.Toast(toast).show();
            } else {
                alert(msg);
            }
            };
        
            const showSuccess = msg => show_toastr('success', msg, 'success');
        
            const getMsg = key => {
            let lang = (sessionStorage.getItem('erp-np-lang') 
                    || document.documentElement.lang 
                    || 'en')
                        .toLowerCase().replace(/_/g,'-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            return window.translations?.[lang]?.[key]
                || window.translations?.['en']?.[key]
                || '# ERROR';
            };
        
            const els = document.querySelectorAll(SELECTOR);
            if (!els.length) return;
        
            els.forEach(el => {
            if (el.getAttribute(ATTR_ACTIVE) === 'true') return;
            el.setAttribute(ATTR_ACTIVE, 'true');
        
            el.addEventListener('click', async e => {
                e.preventDefault();
                try {
                const href = el.getAttribute('href');
                if (!href) throw new Error();
                await navigator.clipboard.writeText(href);
                showSuccess(getMsg(SUCCESS_KEY));
                } catch {
                showError(getMsg(ERROR_KEY));
                }
            });
            });
        
            const mo = new MutationObserver((_, obs) => {
            if (![...els].some(el => document.body.contains(el))) {
                els.forEach(el => el.removeEventListener('click'));
                obs.disconnect();
            }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Expense')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create bill')
            <a href="{{ route(ViewsConstants::EXP.'.create',0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::EXP.'.index'),'method' => 'GET','id'=>'frm_submit')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col-3"></div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{Collective\Html\FormFacade::label('bill_date',__('Payment Date'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::text('bill_date', isset($_GET['bill_date'])?$_GET['bill_date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1','readonly')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('category', __('Category'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('frm_submit').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route(ViewsConstants::EXP.'.index')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Expense')}}</th>
                                <th> {{__('Category')}}</th>
                                <th> {{__('Date')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($expenses as $expense)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route(ViewsConstants::EXP.'.show',Crypt::encrypt($expense->id)) }}" class="btn btn-outline-primary">{{ $user?->expenseNumberFormat($expense->bill_id) }}</a>
                                    </td>
                                    <td>{{ !empty($expense->category)?$expense->category->name:'-'}}</td>
                                    <td>{{ $user?->dateFormat($expense->bill_date) }}</td>
                                    <td>
                                        <span class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statuses[$expense->status]) }}</span>
                                    </td>
                                    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                        <td class="Action">
                                            <span>

                                                @can('show bill')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route(ViewsConstants::EXP.'.show',Crypt::encrypt($expense->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('edit bill')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route(ViewsConstants::EXP.'.edit',Crypt::encrypt($expense->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete bill')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::EXP.'.destroy', $expense->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$expense->id]) !!}
                                                        <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$expense->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

