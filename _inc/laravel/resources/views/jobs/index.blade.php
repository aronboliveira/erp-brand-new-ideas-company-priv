@extends(ExtendingLayoutsConstants::ADM)
@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Job')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Job')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        window.translations = {
            ar:  { copy_success: 'تم نسخ الرابط إلى الحافظة', copy_unavailable: 'لا يمكن النسخ إلى الحافظة' },
            da:  { copy_success: 'Link kopieret til udklipsholder', copy_unavailable: 'Kan ikke kopiere til udklipsholder' },
            de:  { copy_success: 'Link in die Zwischenablage kopiert', copy_unavailable: 'Kann nicht in die Zwischenablage kopieren' },
            en:  { copy_success: 'URL copied to clipboard', copy_unavailable: 'Cannot copy to clipboard' },
            es:  { copy_success: 'URL copiada al portapapeles', copy_unavailable: 'No se puede copiar al portapapeles' },
            fr:  { copy_success: 'URL copiée dans le presse-papiers', copy_unavailable: 'Impossible de copier dans le presse-papiers' },
            he:  { copy_success: 'הקישור הועתק ללוח', copy_unavailable: 'לא ניתן להעתיק ללוח' },
            it:  { copy_success: "URL copiata negli appunti", copy_unavailable: 'Impossibile copiare negli appunti' },
            ja:  { copy_success: 'URLをクリップボードにコピーしました', copy_unavailable: 'クリップボードにコピーできません' },
            nl:  { copy_success: 'URL gekopieerd naar klembord', copy_unavailable: 'Kan niet kopiëren naar klembord' },
            pl:  { copy_success: 'Adres URL skopiowany do schowka', copy_unavailable: 'Nie można skopiować do schowka' },
            pt:  { copy_success: 'URL copiado para a área de transferência', copy_unavailable: 'Não é possível copiar para a área de transferência' },
            'pt-br': { copy_success: 'URL copiada para a área de transferência', copy_unavailable: 'Não é possível copiar para a área de transferência' },
            ru:  { copy_success: 'URL скопирован в буфер обмена', copy_unavailable: 'Не удалось скопировать в буфер обмена' },
            tr:  { copy_success: 'URL panoya kopyalandı', copy_unavailable: 'Panoya kopyalanamıyor' },
            zh:  { copy_success: 'URL 已复制到剪贴板', copy_unavailable: '无法复制到剪贴板' }
        };
    </script>
    <script defer>
        (() => {
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (
                    el?.getAttribute('data-sv-localized') === 'true' ||
                    el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true'
                ) {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (
                        sessionStorage.getItem('erp-np-lang') ||
                        document.documentElement.lang ||
                        'en'
                    )
                        .toLowerCase()
                        .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg =
                        window.translations?.[lang]?.[key] ||
                        el.getAttribute(DATA_GUARD_MSG) ||
                        window.translations?.['en']?.[key] ||
                        ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap =
                    document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="Close"></button>
                            </div>`;
                        document.body.appendChild(toast);
                    }
                    new bootstrap.Toast(
                        document.querySelector('#error-toast')
                    ).show();
                } else {
                    alert(message);
                }
            };

            const copyToClipboard = (el) => {
                if (!el?.id) return;
                try {
                    navigator.clipboard.writeText(el.id);
                    const msg = getLocalizedMessage(el, 'copy_success');
                    show_toastr('success', msg, 'success');
                } catch {
                    handleErrorDisplay(el, 'copy_unavailable');
                }
            };

            window.copyToClipboard = copyToClipboard;
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_JB)
            <a href="{{ route(ViewsConstants::JB.'.create') }}" class="btn btn-sm btn-primary"  data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Job')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avatar bg-primary">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Total')}}</small>
                                        <h6 class="m-0">{{__('Jobs')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['total']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avatar bg-info">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Active')}}</small>
                                        <h6 class="m-0">{{__('Jobs')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['active']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avatar bg-warning">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Inactive')}}</small>
                                        <h6 class="m-0">{{__('Jobs')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['in_active']}}</h4>
                            </div>
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
                                    <th>{{__('Branch')}}</th>
                                    <th>{{__('Title')}}</th>
                                    <th>{{__('Start Date')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Created At')}}</th>
                                    @if( Gate::check('edit job') ||Gate::check('delete job') ||Gate::check('show job'))
                                        <th width="200px">{{__('Action')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="font-style">
                                @foreach ($jobs as $job)
                                    <tr>
                                        <td>{{ !empty($job->branches)?$job->branches->name:__('All') }}</td>
                                        <td>{{$job->title}}</td>
                                        <td>{{ $user?->dateFormat($job->start_date) }}</td>
                                        <td>{{ $user?->dateFormat($job->end_date) }}</td>
                                        <td>
                                            @if($job->status=='active')
                                                <span class="status_badge badge bg-primary p-2 px-3 rounded">{{App\Models\Job::$status[$job->status]}}</span>
                                            @else
                                                <span class="status_badge badge bg-danger p-2 px-3 rounded">{{App\Models\Job::$status[$job->status]}}</span>
                                            @endif
                                        </td>
                                        <td>{{ $user?->dateFormat($job->created_at) }}</td>
                                        @if( Gate::check('edit job') ||Gate::check('delete job') || Gate::check('show job'))
                                            <td>
                                            @if($job->status!='in_active')
                                                    {{--                                            <div class="action-btn bg-warning ms-2">--}}
                                                    {{--                                                <a href="{{ route(ViewsConstants::JB.'.requirement',[$job->code,!empty($job)?$job->createdBy->lang:DatabaseConstants::DEFAULT_LANG]) }}" class="mx-3 btn btn-sm align-items-center " onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Click to copy')}}">--}}
                                                    {{--                                                    <i class="ti ti-link text-white"></i></a>--}}

                                                    {{--                                                <a href="#" id="{{ route(ViewsConstants::INV.'.link.copy',[$invoiceID]) }}" class="mx-3 btn btn-sm align-items-center"   onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>--}}

                                                    {{--                                            </div>--}}

                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="#" id="{{ route(ViewsConstants::JB.'.requirement',[$job->code,!empty($job)?$job->createdBy->lang:DatabaseConstants::DEFAULT_LANG]) }}" class="mx-3 btn btn-sm align-items-center"  onclick="copyToClipboard(this)" data-bs-toggle="tooltip" title="{{__('Copy')}}" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>
                                                    </div>

                                                @endif
                                                @can('show job')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route(ViewsConstants::JB.'.show',$job->id) }}" data-title="{{__('Job Detail')}}" title="{{__('View')}}"  class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('View Detail')}}">
                                                        <i class="ti ti-eye text-white"></i></a>
                                                </div>
                                                    @endcan
                                                @can('edit job')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route(ViewsConstants::JB.'.edit',$job->id) }}" data-title="{{__('Edit Job')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                </div>
                                                    @endcan
                                                @can('delete job')
                                                <div class="action-btn bg-danger ms-2">
                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::JB.'.destroy', $job->id],'id'=>'delete-form-'.$job->id]) !!}
                                                    <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$job->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i></a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
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
