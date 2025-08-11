@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Lead Stages')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script async>
        window.translations = {
            ar:  { lead_order_unavailable: 'فشل تحديث ترتيب المراحل' },
            da:  { lead_order_unavailable: 'Opdatering af rækkefølge mislykkedes' },
            de:  { lead_order_unavailable: 'Reihenfolgeaktualisierung fehlgeschlagen' },
            en:  { lead_order_unavailable: 'Failed to update lead stages order' },
            es:  { lead_order_unavailable: 'Error al actualizar el orden de etapas' },
            fr:  { lead_order_unavailable: 'Échec de la mise à jour de l’ordre des étapes' },
            he:  { lead_order_unavailable: 'עדכון סדר השלבים נכשל' },
            it:  { lead_order_unavailable: 'Aggiornamento dell’ordine delle fasi non riuscito' },
            ja:  { lead_order_unavailable: 'ステージ順序の更新に失敗しました' },
            nl:  { lead_order_unavailable: 'Bijwerken volgorde mislukt' },
            pl:  { lead_order_unavailable: 'Aktualizacja kolejności nieudana' },
            pt:  { lead_order_unavailable: 'Falha ao atualizar a ordem das etapas' },
            'pt-br': { lead_order_unavailable: 'Falha ao atualizar a ordem das etapas' },
            ru:  { lead_order_unavailable: 'Не удалось обновить порядок этапов' },
            tr:  { lead_order_unavailable: 'Aşamalar sırası güncellenemedi' },
            zh:  { lead_order_unavailable: '无法更新阶段顺序' }
        };
    </script>
    <script defer>
        (() => {
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (el?.getAttribute('data-sv-localized') === 'true'
                    || el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (sessionStorage.getItem('erp-np-lang')
                                || document.documentElement.lang
                                || 'en')
                                .toLowerCase()
                                .replace(/_/g,'-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg = window.translations?.[lang]?.[key]
                        || window.translations?.['en']?.[key]
                        || ERR_FB;
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
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                                    && window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
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

            try {
                if (typeof $ === 'undefined' || !$.fn.sortable) {
                    console.error('jQuery UI sortable is required');
                    return;
                }

                $('.sortable').each(function() {
                    try {
                        $(this).sortable();
                        $(this).disableSelection();
                        $(this).on('sortstop', function() {
                            const el = this;
                            const order = [];
                            $(el).find('li').each((i, item) => {
                                order[i] = $(item).attr('data-id') ?? '';
                            });
                            const url = "{{ route('lead_stages.order') }}";
                            if (!url) return;
                            $.ajax({
                                url,
                                type: 'POST',
                                data: {
                                    order,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: () => {},
                                error: () => {
                                    handleErrorDisplay(el, 'lead_order_unavailable');
                                }
                            });
                        });
                    } catch {
                        handleErrorDisplay(this, 'lead_order_unavailable');
                    }
                });
            } catch (e) {
                console.error('Initialization failed', e);
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
    <li class="breadcrumb-item">{{__('Lead Stage')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('lead_stages.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Lead Stage')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.crm_setup')
        </div>
        <div class="col-9">
            <div class="row justify-content-center">
                <div class="p-3 card">
                    <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                        @php($i=0)
                        @foreach($pipelines as $key => $pipeline)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($i==0) active @endif" id="pills-user-tab-1" data-bs-toggle="pill"
                                        data-bs-target="#tab{{$key}}" type="button">{{$pipeline['name']}}
                                </button>
                            </li>
                            @php($i++)
                        @endforeach
                    </ul>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content" id="pills-tabContent">
                            @php($i=0)
                            @foreach($pipelines as $key => $pipeline)
                                <div class="tab-pane fade show @if($i==0) active @endif" id="tab{{$key}}" role="tabpanel" aria-labelledby="pills-user-tab-1">
                                    <ul class="list-unstyled list-group sortable stage">
                                        @foreach ($pipeline['lead_stages'] as $lead_stages)
                                            <li class="d-flex align-items-center justify-content-between list-group-item" data-id="{{$lead_stages->id}}">
                                                <h6 class="mb-0">
                                                    <i class="me-3 ti ti-arrows-maximize" data-feather="move"></i>
                                                    <span>{{$lead_stages->name}}</span>
                                                </h6>
                                                <span class="float-end">
                                                    @can('edit lead stage')
                                                        <div class="action-btn bg-info ms-2"><a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('lead_stages/'.$lead_stages->id.'/edit') }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Lead Stages')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    @endcan
                                                    @if(count($pipeline['lead_stages']))
                                                        @can('delete lead stage')
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['lead_stages.destroy', $lead_stages->id]]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                            </div>
                                                        @endcan
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @php($i++)
                            @endforeach
                        </div>
                        <p class="mt-4"><strong>{{__('Note')}} : </strong><b>{{__('You can easily change order of lead stage using drag & drop.')}}</b></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
