@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bills')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
            ar:  { copy_link_unavailable: "نسخ الرابط غير متاح" },
            da:  { copy_link_unavailable: "Kopiering af link ikke tilgængelig" },
            de:  { copy_link_unavailable: "Link kopieren nicht verfügbar" },
            en:  { copy_link_unavailable: "Copy link unavailable" },
            es:  { copy_link_unavailable: "Copia de enlace no disponible" },
            fr:  { copy_link_unavailable: "Copie du lien non disponible" },
            he:  { copy_link_unavailable: "העתקת הקישור אינה זמינה" },
            it:  { copy_link_unavailable: "Copia del link non disponibile" },
            ja:  { copy_link_unavailable: "リンクのコピーは利用できません" },
            nl:  { copy_link_unavailable: "Kopiëren van de link niet beschikbaar" },
            pl:  { copy_link_unavailable: "Kopiowanie linku niedostępne" },
            pt:  { copy_link_unavailable: "Cópia do link indisponível" },
            "pt-br": { copy_link_unavailable: "Cópia do link indisponível" },
            ru:  { copy_link_unavailable: "Копирование ссылки недоступно" },
            tr:  { copy_link_unavailable: "Bağlantı kopyalama kullanılamıyor" },
            zh:  { copy_link_unavailable: "无法复制链接" }
        };
    </script>
    <script defer>
        (() => {
        const BS_LINK = 'link[href*="bootstrap"]';
        const toastContainer = (() => {
            const c = document.createElement('div');
            c.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.append(c);
            return c;
        })();

        const showError = key => {
            const errFb = '# ERROR';
            let lang = (window.sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g,'-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            const msg = window.translations?.[lang]?.[key]
            || window.translations?.['en']?.[key]
            || errFb;
            if (toastContainer.querySelector(`.toast[data-error-key="${key}"]`)) return;
            if (document.querySelector(BS_LINK) && window.bootstrap?.Toast) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-danger border-0';
            toast.dataset.errorKey = key;
            toast.setAttribute('role','alert');
            toast.setAttribute('aria-live','assertive');
            toast.setAttribute('aria-atomic','true');
            toast.innerHTML = `
                <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
            toastContainer.append(toast);
            new window.bootstrap.Toast(toast).show();
            } else {
            alert(msg);
            }
        };

        try {
            document.querySelectorAll('.copy_link').forEach(el => {
            if (el.dataset.copyListener) return;
            el.dataset.copyListener = 'true';
            el.addEventListener('click', e => {
                e.preventDefault();
                const href = el.getAttribute('href') ?? '';
                if (!href) {
                console.error('No href to copy');
                showError('copy_link_unavailable');
                return;
                }
                try {
                const onCopy = evt => {
                    evt.clipboardData.setData('text/plain', href);
                    evt.preventDefault();
                };
                document.addEventListener('copy', onCopy, true);
                const success = document.execCommand('copy');
                document.removeEventListener('copy', onCopy, true);
                if (!success) throw new Error('execCommand returned false');
                show_toastr('success', window.translations?.['en']?.copy_link_success || 'Link copied', 'success');
                } catch (err) {
                console.error('Copy command failed:', err);
                showError('copy_link_unavailable');
                }
            });
            });
        } catch (err) {
            console.error('Failed to bind copy_link handlers:', err);
        }
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
    <li class="breadcrumb-item">{{__('Bill')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="{{ route(ViewsConstants::BIL.'.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">
            <i class="ti ti-file-export"></i>
        </a>
        @can('create bill')
            <a href="{{ route(ViewsConstants::BIL.'.create',0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::BIL.'.index'),'method' => 'GET','id'=>'frm_submit')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col-3"></div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{Collective\Html\FormFacade::label('bill_date',__('Bill Date'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::text('bill_date', isset($_GET['bill_date'])?$_GET['bill_date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1','readonly')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('status', __('Status'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('status', [''=>'Select Status'] + $status,isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select')) }}
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
                                        <a href="{{route(ViewsConstants::BIL.'.index')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                                <th> {{__('Bill')}}</th>
                                <th> {{__('Category')}}</th>
                                <th> {{__('Bill Date')}}</th>
                                <th> {{__('Due Date')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($bills as $bill)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route(ViewsConstants::BIL.'.show', Crypt::encrypt($bill->id)) }}" class="btn btn-outline-primary">{{ $user?->billNumberFormat($bill->bill_id) }}</a>
                                    </td>
                                    <td>{{ !empty($bill->category)?$bill->category->name:'-'}}</td>
                                    <td>{{ $user?->dateFormat($bill->bill_date) }}</td>
                                    <td>{{ $user?->dateFormat($bill->due_date) }}</td>
                                    @php
                                        $statusClasses = [
                                            0 => 'bg-secondary',
                                            1 => 'bg-warning',
                                            2 => 'bg-danger',
                                            3 => 'bg-info',
                                            4 => 'bg-primary',
                                        ];
                                        $statusLabel  = \App\Models\Invoice::$statuses[$bill->status] ?? '';
                                        $badgeClass   = $statusClasses[$bill->status] ?? 'bg-secondary';
                                    @endphp
                                    <td>
                                        <span class="status_badge badge {{ $badgeClass }} p-2 px-3 rounded">
                                            {{ __($statusLabel) }}
                                        </span>
                                    </td>
                                    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                        <td class="Action">
                                            <span>
                                                @can('duplicate bill')
                                                    <div class="action-btn bg-primary ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'get', 'route' => [ViewsConstants::BIL.'.duplicate', $bill->id],'id'=>'duplicate-form-'.$bill->id]) !!}
                                                        <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" data-original-title="{{__('Duplicate')}}" data-bs-toggle="tooltip" title="{{__('Duplicate Bill')}}" data-original-title="{{__('Delete')}}" data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$bill->id}}').submit();">
                                                        <i class="ti ti-copy text-white"></i>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('show bill')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route(ViewsConstants::BIL.'.show', Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('edit bill')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route(ViewsConstants::BIL.'.edit', Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete bill')
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::BIL.'.destroy', $bill->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$bill->id]) !!}
                                                        <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$bill->id}}').submit();">
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

