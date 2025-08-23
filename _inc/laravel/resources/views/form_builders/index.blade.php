@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Form Builder')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
            link_copy_success:    'تم نسخ الرابط إلى الحافظة.',
            link_copy_failed:     'فشل نسخ الرابط.'
        },
        da: {
            link_copy_success:    'Link kopieret til udklipsholder.',
            link_copy_failed:     'Kunne ikke kopiere link.'
        },
        de: {
            link_copy_success:    'Link in die Zwischenablage kopiert.',
            link_copy_failed:     'Kopieren des Links fehlgeschlagen.'
        },
        en: {
            link_copy_success:    'Link copied to clipboard.',
            link_copy_failed:     'Failed to copy link.'
        },
        es: {
            link_copy_success:    'Enlace copiado al portapapeles.',
            link_copy_failed:     'Error al copiar el enlace.'
        },
        fr: {
            link_copy_success:    'Lien copié dans le presse-papiers.',
            link_copy_failed:     'Échec de la copie du lien.'
        }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script>
        (() => {
            const SUCCESS_KEY = 'link_copy_success';
            const FAILURE_KEY = 'link_copy_failed';
            const LISTENER_ATTR = 'data-copy-listener';
            const SELECTOR = ['.cp_link', '.iframe_link'];
            const showMsg = (key, isError = false) => {
            const msg = (() => {
                let lang = (sessionStorage.getItem('erp-np-lang') 
                        || document.documentElement.lang 
                        || 'en')
                        .toLowerCase().replace(/_/g,'-');
                lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                return window.translations?.[lang]?.[key]
                    || window.translations?.['en']?.[key]
                    || '# ERROR';
            })();
            show_toastr(isError ? 'error' : 'success', msg);
            };
        
            const copyText = async text => {
            try {
                if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
                } else {
                const tmp = document.createElement('input');
                document.body.append(tmp);
                tmp.value = text;
                tmp.select();
                document.execCommand('copy');
                tmp.remove();
                }
                showMsg(SUCCESS_KEY);
            } catch {
                showMsg(FAILURE_KEY, true);
            }
            };
        
            const attach = el => {
            if (el.getAttribute(LISTENER_ATTR) === 'true') return;
            el.setAttribute(LISTENER_ATTR, 'true');
            el.addEventListener('click', e => {
                e.preventDefault();
                const link = el.getAttribute('data-link');
                if (!link) {
                showMsg(FAILURE_KEY, true);
                return;
                }
                copyText(link);
            });
            };
        
            const init = () => {
            SELECTOR.forEach(sel => {
                document.querySelectorAll(sel).forEach(attach);
            });
        
            const mo = new MutationObserver(() => {
                SELECTOR.forEach(sel => {
                document.querySelectorAll(sel).forEach(attach);
                });
            });
            mo.observe(document.body, { childList: true, subtree: true });
            };
        
            if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            } else {
            init();
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
    <li class="breadcrumb-item">{{__('Form Builder')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('form_builder.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Form')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Response')}}</th>
                                @if($user?->type=='company')
                                    <th class="text-end" width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($forms as $form)
                                <tr>
                                    <td>{{ $form->name }}</td>
                                    <td>
                                        {{ $form->response->count() }}
                                    </td>
                                    @if($user?->type=='company')
                                        <td class="text-end">
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center cp_link" data-link="<iframe src='{{url('/form/'.$form->code)}}' title='{{ $form->name }}'></iframe>" data-bs-toggle="tooltip" title="{{__('Click to copy iframe link')}}"><i class="ti ti-frame text-white"></i></a>
                                            </div>
                                            <div class="action-btn bg-secondary ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('form.field.bind',$form->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Convert into Lead Setting')}}" data-title="{{__('Convert into Lead Setting')}}">
                                                    <i class="ti ti-exchange text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center cp_link" data-link="{{url('/form/'.$form->code)}}" data-bs-toggle="tooltip" title="{{__('Click to copy link')}}"><i class="ti ti-copy text-white"></i></a>
                                            </div>
                                            @can('manage form field')
                                                <div class="action-btn bg-secondary ms-2">
                                                    <a href="{{route('form_builder.show',$form->id)}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Form field')}}"><i class="ti ti-table text-white"></i></a>
                                                </div>
                                            @endcan
                                            @can('view form response')
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{route('form.response',$form->id)}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('View Response')}}"><i class="ti ti-eye text-white"></i></a>
                                                </div>
                                            @endcan
                                            @can('edit form builder')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('form_builder.edit',$form->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Form Builder Edit')}}">
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete form builder')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['form_builder.destroy', $form->id],'id'=>'delete-form-'.$form->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
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
