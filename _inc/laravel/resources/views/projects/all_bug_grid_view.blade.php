@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bug Report')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('assets/libs/dragula/dist/dragula.min.js')}}"></script>
    <script defer>
        (() => {
          const errFb = '# ERROR';
          const dataClientLocalized = 'data-client-localized';
          const dataGuardMsg = 'data-guard-msg';
          const langSessionKey = 'erp-np-lang';
          const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
              msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
              let lang = (window.sessionStorage.getItem(langSessionKey) ?? document.documentElement.lang ?? 'en')
                .toLowerCase().replace(/_/g, '-');
              lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
              msg = window.translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    window.translations?.['en']?.[msgKey] ??
                    errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
              }
            }
            return msg;
          };
          const showError = message => {
            try {
              let container = document.querySelector('#bootstrap-toast-container');
              if (!container) {
                const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                  .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
                if (hasBs) {
                  container = document.createElement('div');
                  container.id = 'bootstrap-toast-container';
                  container.setAttribute('aria-live', 'polite');
                  container.setAttribute('aria-atomic', 'true');
                  document.body.appendChild(container);
                }
              }
              if (container && window.bootstrap.Toast) {
                let toast = container.querySelector('.toast');
                if (!toast) {
                  toast = document.createElement('div');
                  toast.className = 'toast';
                  toast.setAttribute('role', 'alert');
                  toast.setAttribute('aria-live', 'assertive');
                  toast.setAttribute('aria-atomic', 'true');
                  const body = document.createElement('div');
                  body.className = 'toast-body';
                  toast.appendChild(body);
                  container.appendChild(toast);
                  if (toast.getAttribute('data-click-listener') !== 'true') {
                    toast.addEventListener('click', () => body.textContent = message);
                    toast.setAttribute('data-click-listener', 'true');
                  }
                }
                toast.querySelector('.toast-body').textContent = message;
                new bootstrap.Toast(toast).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          };
        
          // Dragula init
          const dragEls = document.querySelectorAll('[data-plugin="dragula"]');
          dragEls.forEach(el => {
            if (el.dataset.listenerAttached === 'true') return;
            el.dataset.listenerAttached = 'true';
            const obs = new MutationObserver((ms, o) => {
              ms.forEach(m => m.removedNodes.forEach(n => {
                if (n === el) {
                  o.disconnect();
                }
              }));
            });
            obs.observe(document.body, { childList: true, subtree: true });
            try {
              const containers = el.dataset.containers?.split(',') || [];
              const cols = containers.length
                ? containers.map(id => document.getElementById(id)).filter(Boolean)
                : [el];
              const handleClass = el.dataset.handleclass;
              const drake = handleClass
                ? dragula(cols, { moves: (_, __, handle) => handle.classList.contains(handleClass) })
                : dragula(cols);
              drake.on('drop', (item, target, source) => {
                try {
                  const order = [];
                  Array.from(target.children).forEach((c, i) => order[i] = c.getAttribute('data-id'));
                  const bugId = item.getAttribute('data-id');
                  const stageId = target.getAttribute('data-id');
                  source.parentElement.querySelector('.count')?.textContent = source.children.length;
                  target.parentElement.querySelector('.count')?.textContent = target.children.length;
                  $.ajax({
                    url: '{{ route("bug.kanban.order") }}',
                    type: 'POST',
                    data: { bug_id: bugId, status_id: stageId, order, _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                  })
                  .fail(() => showError(getLocalizedMessage('kanban_order_failed', el)));
                } catch {
                  showError(getLocalizedMessage('kanban_order_failed', el));
                }
              });
            } catch {
              showError(getLocalizedMessage('dragula_init_failed', el));
            }
          });
        
          // comment add
          const commentBtn = document.querySelector('#form-comment button');
          if (commentBtn && commentBtn.dataset.listenerAttached !== 'true') {
            commentBtn.dataset.listenerAttached = 'true';
            const obsC = new MutationObserver((ms, o) => {
              ms.forEach(m => m.removedNodes.forEach(n => {
                if (n === commentBtn) {
                  commentBtn.removeEventListener('click', onCommentClick);
                  o.disconnect();
                }
              }));
            });
            obsC.observe(document.body, { childList: true, subtree: true });
            commentBtn.addEventListener('click', onCommentClick);
          }
          function onCommentClick() {
            const el = commentBtn;
            try {
              const txt = document.querySelector("#form-comment textarea[name='comment']").value.trim();
              if (!txt) {
                showError(getLocalizedMessage('comment_empty_error', el));
                return;
              }
              $.ajax({
                url: document.querySelector('#form-comment').dataset.action,
                type: 'POST',
                data: { comment: txt, _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
              })
              .done(resp => {
                const data = typeof resp === 'string' ? JSON.parse(resp) : resp;
                const name = '{{ $user?->name }}';
                const li = document.createElement('li');
                li.className = 'media mb-20';
                li.innerHTML = `
                  <div class="media-body">
                    <div class="d-flex justify-content-between align-items-end">
                      <div><h5 class="mt-0">${name}</h5><p class="mb-0 text-xs">${data.comment}</p></div>
                      <div class="comment-trash"><a href="#" class="btn btn-outline btn-sm text-danger delete-comment" data-url="${data.deleteUrl}"><i class="fa fa-trash"></i></a></div>
                    </div>
                  </div>`;
                document.getElementById('comments').prepend(li);
                document.querySelector("#form-comment textarea[name='comment']").value = '';
                show_toastr('{{ __("Success") }}', '{{ __("Comment Added Successfully!") }}', 'success');
              })
              .fail(() => showError(getLocalizedMessage('comment_add_failed', el)));
            } catch {
              showError(getLocalizedMessage('comment_add_failed', el));
            }
          }
        
          // delete comment
          document.body.querySelectorAll('.delete-comment').forEach(btn => {
            if (btn.dataset.listenerAttached === 'true') return;
            btn.dataset.listenerAttached = 'true';
            btn.addEventListener('click', e => {
              e.preventDefault();
              if (!confirm('Are You Sure ?')) return;
              try {
                $.ajax({
                  url: btn.getAttribute('data-url'),
                  type: 'DELETE',
                  data: { _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                  dataType: 'JSON'
                })
                .done(() => {
                  show_toastr('{{ __("Success") }}', '{{ __("Comment Deleted Successfully!") }}', 'success');
                  btn.closest('.media').remove();
                })
                .fail(xhr => {
                  const msg = xhr.responseJSON?.message;
                  showError(msg ? msg : getLocalizedMessage('comment_delete_failed', btn));
                });
              } catch {
                showError(getLocalizedMessage('comment_delete_failed', btn));
              }
            });
          });
        
          // file upload
          const fileForm = document.getElementById('form-file');
          if (fileForm && fileForm.dataset.listenerAttached !== 'true') {
            fileForm.dataset.listenerAttached = 'true';
            fileForm.addEventListener('submit', e => {
              e.preventDefault();
              try {
                $.ajax({
                  url: fileForm.dataset.url,
                  type: 'POST',
                  data: new FormData(fileForm),
                  dataType: 'JSON',
                  contentType: false,
                  cache: false,
                  processData: false
                })
                .done(data => {
                  show_toastr('{{ __("Success") }}', '{{ __("File Added Successfully!") }}', 'success');
                  document.querySelectorAll('.file_update').forEach(n => n.innerHTML = '');
                  document.getElementById('file-error').textContent = '';
                  const html = `
                    <div class="col-8 mb-2 file-${data.id}">
                      <h5 class="mt-0 mb-1 font-weight-bold text-sm">${data.name}</h5>
                      <p class="m-0 text-xs">${data.file_size}</p>
                    </div>
                    <div class="col-4 mb-2 file-${data.id}">
                      <div class="comment-trash">
                        <a download href="{{ asset(Storage::url('bugs')) }}/${data.file}" class="btn btn-outline btn-sm text-primary"><i class="fa fa-download"></i></a>
                        <a href="#" class="btn btn-outline btn-sm red text-danger delete-comment-file" data-id="${data.id}" data-url="${data.deleteUrl}"><i class="ti ti-trash"></i></a>
                      </div>
                    </div>`;
                  document.getElementById('comments-file').insertAdjacentHTML('afterbegin', html);
                })
                .fail(xhr => {
                  const err = xhr.responseJSON;
                  if (err?.errors?.file?.[0]) {
                    document.getElementById('file-error').textContent = err.errors.file[0];
                  } else {
                    showError(getLocalizedMessage('file_add_failed', fileForm));
                  }
                });
              } catch {
                showError(getLocalizedMessage('file_add_failed', fileForm));
              }
            });
          }
          document.body.querySelectorAll('.delete-comment-file').forEach(btn => {
            if (btn.dataset.listenerAttached === 'true') return;
            btn.dataset.listenerAttached = 'true';
            btn.addEventListener('click', e => {
              e.preventDefault();
              if (!confirm('Are You Sure ?')) return;
              try {
                $.ajax({
                  url: btn.getAttribute('data-url'),
                  type: 'DELETE',
                  data: { _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                  dataType: 'JSON'
                })
                .done(() => {
                  show_toastr('{{ __("Success") }}', '{{ __("File Deleted Successfully!") }}', 'success');
                  document.querySelectorAll(`.file-${btn.dataset.id}`).forEach(n => n.remove());
                })
                .fail(xhr => {
                  const msg = xhr.responseJSON?.message;
                  showError(msg ? msg : getLocalizedMessage('file_delete_failed', btn));
                });
              } catch {
                showError(getLocalizedMessage('file_delete_failed', btn));
              }
            });
          });
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
    <li class="breadcrumb-item">{{__('Project')}}</li>
    <li class="breadcrumb-item">{{__('Bug Report')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @if($view === 'grid')
            <a href="{{ route(ViewsConstants::BUG . '.view', 'list') }}"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('List View') }}">
                <span class="btn-inner--text">
                    <i class="{{ ViewClassNamesConstants::TI }} ti-list"></i>
                </span>
            </a>
        @else
            <a href="{{ route(ViewsConstants::BUG . '.view', 'grid') }}"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}">
                <span class="btn-inner--text">
                    <i class="{{ ViewClassNamesConstants::TI }} ti-table"></i>
                    {{ __('Card View') }}
                </span>
            </a>
        @endif
        @can(PermissionsConstants::MNG_PRJ)
            <a href="{{ route(ViewsConstants::PRJ . '.index') }}"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Back') }}">
                <span class="btn-inner--icon">
                    <i class="{{ ViewClassNamesConstants::TI }} ti-arrow-left"></i>
                </span>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if(count($bugs) > 0)
                            @foreach($bugs as $bug)
                                <div class="col-md-4 col-lg-3">
                                    <div
                                        id="{{ $bug->id }}"
                                        class="{{ ViewClassNamesConstants::CD }} {{ ViewClassNamesConstants::BD }} {{ ViewClassNamesConstants::SNN }} card-progress"
                                        style="{{ !empty($bug->priority_color)
                                            ? 'border-left: 2px solid '.$bug->priority_color.' !important'
                                            : '' }}"
                                    >
                                        <div class="card-body">
                                            <div>
                                                <div class="mb-2 {{ ViewClassNamesConstants::DFL_AIC_JCB }}">
                                                    <span>
                                                        <a
                                                            href="{{ route(ViewsConstants::TSK . '.bug.kanban', $bug->project_id) }}"
                                                            class="text-body {{ ViewClassNamesConstants::H6 }}"
                                                        >
                                                            {{ $bug->title }}
                                                        </a>
                                                    </span>
                                    
                                                    @if($bug->priority === 'low')
                                                        <span class="{{ ViewClassNamesConstants::BDG }} bg-success p-2 px-3 rounded">
                                                            {{ ucfirst($bug->priority) }}
                                                        </span>
                                                    @elseif($bug->priority === 'medium')
                                                        <span class="{{ ViewClassNamesConstants::BDG }} bg-warning p-2 px-3 rounded">
                                                            {{ ucfirst($bug->priority) }}
                                                        </span>
                                                    @elseif($bug->priority === 'high')
                                                        <span class="{{ ViewClassNamesConstants::BDG }} bg-danger p-2 px-3 rounded">
                                                            {{ ucfirst($bug->priority) }}
                                                        </span>
                                                    @endif
                                                </div>
                                    
                                                <div class="mb-3 {{ ViewClassNamesConstants::DFL_AIC_JCB }}">
                                                    <p class="{{ ViewClassNamesConstants::MB0 }}">
                                                        <span class="d-inline-block {{ ViewClassNamesConstants::TXSM }}">
                                                            {{ $bug->description ?: '-' }}
                                                        </span>
                                                    </p>
                                                    <p class="{{ ViewClassNamesConstants::MB0 }}">
                                                        @php $users = $bug->users(); @endphp
                                                        <a href="#"
                                                        class="btn {{ ViewClassNamesConstants::MS2 }} p-0 {{ ViewClassNamesConstants::AV_CC_SM }}"
                                                        >
                                                            @foreach($users as $user)
                                                                <img
                                                                    src="{{ $user->avatar
                                                                        ? asset(Storage::url("uploads/avatar/{$user->avatar}"))
                                                                        : asset(Storage::url("uploads/avatar/avatar.png")) }}"
                                                                    class="{{ ViewClassNamesConstants::AV_CC_SM }}"
                                                                    width="25" height="25"
                                                                >
                                                            @endforeach
                                                        </a>
                                                    </p>
                                                </div>
                                    
                                                <div class="{{ ViewClassNamesConstants::RW }}">
                                                    <div class="col-6 {{ ViewClassNamesConstants::TXS }}">
                                                        <i class="far fa-clock"></i>
                                                        <span>{{ $user?->dateFormat($bug->start_date) }}</span>
                                                    </div>
                                                    <div class="col-6 text-end {{ ViewClassNamesConstants::TXS }} {{ ViewClassNamesConstants::FW600 }}">
                                                        <i class="far fa-clock"></i>
                                                        <span>{{ $user?->dateFormat($bug->due_date) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-md-12">
                                <h6 class="text-center m-3">{{__('No tasks found')}}</h6>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
