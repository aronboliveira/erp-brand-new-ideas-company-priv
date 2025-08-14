@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use Illuminate\Support\Facades\{Auth, Route, Storage};
    use Illuminate\Support\{Collection,Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
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
    <div class="{{ VC::FEND }}">
        @if(!empty($view) && $view === 'grid')
          @php
              $projectBugListViewBaseName     = ViewsConstants::PRJ_BUG.'.view';
              $projectBugListViewKebabName    = Str::kebab($projectBugListViewBaseName);
              $projectBugListViewResolvedName = Route::has($projectBugListViewBaseName)
                  ? $projectBugListViewBaseName
                  : (Route::has($projectBugListViewKebabName) ? $projectBugListViewKebabName : null);
              $projectBugListViewParam        = 'list';
              $projectBugListViewUrl          = $projectBugListViewResolvedName ? route($projectBugListViewResolvedName, $projectBugListViewParam) : '#';
              $projectBugListViewGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_BUG, 'bug_list_view_route_unavailable') ?? 'Bug list view route is unavailable. Please contact technical support or your domain administrator.';
              $projectBugListViewLinkId       = 'project-bug-view-list-link';
              $projectBugListViewTitle        = __('List View');
          @endphp
          <a href="{{ $projectBugListViewUrl }}"
            id="{{ $projectBugListViewLinkId }}"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $projectBugListViewUrl }}"
            data-guard-msg="{{ $projectBugListViewGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ $projectBugListViewTitle }}">
              <span class="btn-inner--text">
                  <i class="{{ VC::TI }} ti-list"></i>
              </span>
          </a>
          @push(StacksConstants::ADM_SCR_PG)
              <script defer>
                  (() => {
                      try {
                          const l = document.getElementById('{{ $projectBugListViewLinkId }}');
                          if (!l || l.getAttribute('data-listener-active') === 'true') return;
                          l.setAttribute('data-listener-active', 'true');
                          l.addEventListener('click', e => {
                              try {
                                  const href = l.getAttribute('href') || '#';
                                  const url = l.getAttribute('data-url') || href || '#';
                                  if (href !== '#' || url !== '#') return;
                                  e.preventDefault();
                                  const msg = l.getAttribute('data-guard-msg') || 'Open bug list view route is unavailable. Please contact technical support or your domain administrator.';
                                  const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                  let container = document.getElementById('toast-container');
                                  if (!container) {
                                      container = document.createElement('div');
                                      container.id = 'toast-container';
                                      document.body.appendChild(container);
                                  }
                                  if (hasBootstrap) {
                                      const toast = document.createElement('div');
                                      toast.className = 'toast';
                                      toast.setAttribute('role', 'alert');
                                      toast.setAttribute('aria-live', 'assertive');
                                      toast.setAttribute('aria-atomic', 'true');
                                      const body = document.createElement('div');
                                      body.className = 'toast-body';
                                      body.textContent = msg;
                                      toast.appendChild(body);
                                      container.appendChild(toast);
                                      bootstrap.Toast.getOrCreateInstance(toast).show();
                                  } else {
                                      alert(msg);
                                  }
                                  l.setAttribute('data-failed-route', 'true');
                              } catch (err) {}
                          });
                      } catch (error) {}
                  })();
              </script>
          @endpush
        @else
          @php
              $projectBugCardViewBaseName     = ViewsConstants::PRJ_BUG.'.view';
              $projectBugCardViewKebabName    = Str::kebab($projectBugCardViewBaseName);
              $projectBugCardViewResolvedName = Route::has($projectBugCardViewBaseName)
                  ? $projectBugCardViewBaseName
                  : (Route::has($projectBugCardViewKebabName) ? $projectBugCardViewKebabName : null);
              $projectBugCardViewParam        = 'grid';
              $projectBugCardViewUrl          = $projectBugCardViewResolvedName ? route($projectBugCardViewResolvedName, $projectBugCardViewParam) : '#';
              $projectBugCardViewGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_BUG, 'bug_card_view_route_unavailable') ?? 'Bug card view route is unavailable. Please contact technical support or your domain administrator.';
              $projectBugCardViewLinkId       = 'project-bug-view-card-link';
          @endphp
          <a href="{{ $projectBugCardViewUrl }}"
            id="{{ $projectBugCardViewLinkId }}"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $projectBugCardViewUrl }}"
            data-guard-msg="{{ $projectBugCardViewGuardMsg }}">
              <span class="btn-inner--text">
                  <i class="{{ VC::TI }} ti-table"></i>
                  {{ __('Card View') }}
              </span>
          </a>
          @push(StacksConstants::ADM_SCR_PG)
              <script defer>
                  (() => {
                      try {
                          const l = document.getElementById('{{ $projectBugCardViewLinkId }}');
                          if (!l || l.getAttribute('data-listener-active') === 'true') return;
                          l.setAttribute('data-listener-active', 'true');
                          l.addEventListener('click', e => {
                              try {
                                  const href = l.getAttribute('href') || '#';
                                  const url = l.getAttribute('data-url') || href || '#';
                                  if (href !== '#' || url !== '#') return;
                                  e.preventDefault();
                                  const msg = l.getAttribute('data-guard-msg') || 'Open bug card view route is unavailable. Please contact technical support or your domain administrator.';
                                  const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                  let container = document.getElementById('toast-container');
                                  if (!container) {
                                      container = document.createElement('div');
                                      container.id = 'toast-container';
                                      document.body.appendChild(container);
                                  }
                                  if (hasBootstrap) {
                                      const toast = document.createElement('div');
                                      toast.className = 'toast';
                                      toast.setAttribute('role', 'alert');
                                      toast.setAttribute('aria-live', 'assertive');
                                      toast.setAttribute('aria-atomic', 'true');
                                      const body = document.createElement('div');
                                      body.className = 'toast-body';
                                      body.textContent = msg;
                                      toast.appendChild(body);
                                      container.appendChild(toast);
                                      bootstrap.Toast.getOrCreateInstance(toast).show();
                                  } else {
                                      alert(msg);
                                  }
                                  l.setAttribute('data-failed-route', 'true');
                              } catch (err) {}
                          });
                      } catch (error) {}
                  })();
              </script>
          @endpush
        @endif
        @can(PermissionsConstants::MNG_PRJ)
          @php
              $projectIndexBaseName     = ViewsConstants::PRJ.'.index';
              $projectIndexKebabName    = Str::kebab($projectIndexBaseName);
              $projectIndexResolvedName = Route::has($projectIndexBaseName)
                  ? $projectIndexBaseName
                  : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
              $projectIndexUrl          = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
              $projectIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
              $projectIndexLinkId       = 'project-index-back-link';
              $projectIndexTitle        = __('Back');
          @endphp
          <a href="{{ $projectIndexUrl }}"
            id="{{ $projectIndexLinkId }}"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $projectIndexUrl }}"
            data-guard-msg="{{ $projectIndexGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ $projectIndexTitle }}">
              <span class="btn-inner--icon">
                  <i class="{{ VC::TI }} ti-arrow-left"></i>
              </span>
          </a>
          @push(StacksConstants::ADM_SCR_PG)
              <script defer>
                  (() => {
                      try {
                          const l = document.getElementById('{{ $projectIndexLinkId }}');
                          if (!l || l.getAttribute('data-listener-active') === 'true') return;
                          l.setAttribute('data-listener-active', 'true');
                          l.addEventListener('click', e => {
                              try {
                                  const href = l.getAttribute('href') || '#';
                                  const url = l.getAttribute('data-url') || href || '#';
                                  if (href !== '#' || url !== '#') return;
                                  e.preventDefault();
                                  const msg = l.getAttribute('data-guard-msg') || 'Open project index route is unavailable. Please contact technical support or your domain administrator.';
                                  const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                  let container = document.getElementById('toast-container');
                                  if (!container) {
                                      container = document.createElement('div');
                                      container.id = 'toast-container';
                                      document.body.appendChild(container);
                                  }
                                  if (hasBootstrap) {
                                      const toast = document.createElement('div');
                                      toast.className = 'toast';
                                      toast.setAttribute('role', 'alert');
                                      toast.setAttribute('aria-live', 'assertive');
                                      toast.setAttribute('aria-atomic', 'true');
                                      const body = document.createElement('div');
                                      body.className = 'toast-body';
                                      body.textContent = msg;
                                      toast.appendChild(body);
                                      container.appendChild(toast);
                                      bootstrap.Toast.getOrCreateInstance(toast).show();
                                  } else {
                                      alert(msg);
                                  }
                                  l.setAttribute('data-failed-route', 'true');
                              } catch (err) {}
                          });
                      } catch (error) {}
                  })();
              </script>
          @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if(isset($bugs) && (is_countable($bugs) && count($bugs) > 0 || $bugs instanceof Collection && $bugs->count() > 0))
                            @foreach($bugs as $bug)
                                @if(isset($bug) && is_object($bug))
                                    <div class="col-md-4 col-lg-3">
                                        <div
                                            id="{{ data_get($bug, 'id', 'bug-' . uniqid()) }}"
                                            class="{{ VC::CD }} {{ VC::BD }} {{ VC::SNN }} card-progress"
                                            style="{{ !empty(data_get($bug, 'priority_color')) 
                                                ? 'border-left: 2px solid ' . e(data_get($bug, 'priority_color')) . ' !important'
                                                : '' }}"
                                        >
                                            <div class="card-body">
                                                <div>
                                                    <div class="mb-2 {{ VC::DFL_AIC_JCB }}">
                                                        <span>
                                                            @if(isset($bug->project_id) && !empty($bug->project_id))
                                                              @php
                                                                  $bugKanbanBaseName     = ViewsConstants::PRJ_TSK_BUG.'.kanban';
                                                                  $bugKanbanKebabName    = Str::kebab($bugKanbanBaseName);
                                                                  $bugKanbanResolvedName = Route::has($bugKanbanBaseName)
                                                                      ? $bugKanbanBaseName
                                                                      : (Route::has($bugKanbanKebabName) ? $bugKanbanKebabName : null);
                                                                  $projectId             = isset($bug) && !empty($bug->project_id) ? $bug->project_id : null;
                                                                  $bugKanbanUrl          = ($bugKanbanResolvedName && $projectId) ? route($bugKanbanResolvedName, $projectId) : '#';
                                                                  $bugKanbanGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'access_bug_kanban_route_unavailable') ?? 'Access bug kanban route is unavailable. Please contact technical support or your domain administrator.';
                                                                  $bugKanbanLinkId       = 'project-bug-kanban-link-'.($projectId ?? 'x');
                                                                  $bugTitle              = e(data_get($bug, 'title', __('Untitled Bug')));
                                                              @endphp
                                                              <a href="{{ $bugKanbanUrl }}"
                                                                id="{{ $bugKanbanLinkId }}"
                                                                class="text-body {{ VC::H6 }}"
                                                                data-url="{{ $bugKanbanUrl }}"
                                                                data-guard-msg="{{ $bugKanbanGuardMsg }}">
                                                                  {{ $bugTitle }}
                                                              </a>
                                                              @push(StacksConstants::ADM_SCR_PG)
                                                                  <script defer>
                                                                      (() => {
                                                                          try {
                                                                              const l = document.getElementById('{{ $bugKanbanLinkId }}');
                                                                              if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                              l.setAttribute('data-listener-active', 'true');
                                                                              l.addEventListener('click', e => {
                                                                                  try {
                                                                                      const href = l.getAttribute('href') || '#';
                                                                                      const url = l.getAttribute('data-url') || href || '#';
                                                                                      if (href !== '#' || url !== '#') return;
                                                                                      e.preventDefault();
                                                                                      const msg = l.getAttribute('data-guard-msg') || 'Access bug kanban route is unavailable. Please contact technical support or your domain administrator.';
                                                                                      const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                                      let container = document.getElementById('toast-container');
                                                                                      if (!container) {
                                                                                          container = document.createElement('div');
                                                                                          container.id = 'toast-container';
                                                                                          document.body.appendChild(container);
                                                                                      }
                                                                                      if (hasBootstrap) {
                                                                                          const toast = document.createElement('div');
                                                                                          toast.className = 'toast';
                                                                                          toast.setAttribute('role', 'alert');
                                                                                          toast.setAttribute('aria-live', 'assertive');
                                                                                          toast.setAttribute('aria-atomic', 'true');
                                                                                          const body = document.createElement('div');
                                                                                          body.className = 'toast-body';
                                                                                          body.textContent = msg;
                                                                                          toast.appendChild(body);
                                                                                          container.appendChild(toast);
                                                                                          bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                      } else {
                                                                                          alert(msg);
                                                                                      }
                                                                                      l.setAttribute('data-failed-route', 'true');
                                                                                  } catch (err) {}
                                                                              });
                                                                          } catch (error) {}
                                                                      })();
                                                                  </script>
                                                              @endpush
                                                            @else
                                                                <span class="text-body {{ VC::H6 }}">
                                                                    {{ e(data_get($bug, 'title', __('Untitled Bug'))) }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                        @php
                                                            $priority = data_get($bug, 'priority');
                                                            $priorityClasses = [
                                                                'low' => 'bg-success',
                                                                'medium' => 'bg-warning', 
                                                                'high' => 'bg-danger'
                                                            ];
                                                            $priorityClass = data_get($priorityClasses, $priority, 'bg-secondary');
                                                        @endphp
                                                        @if(!empty($priority) && in_array($priority, ['low', 'medium', 'high']))
                                                            <span class="{{ VC::BDG }} {{ $priorityClass }} p-2 px-3 rounded">
                                                                {{ e(ucfirst($priority)) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="mb-3 {{ VC::DFL_AIC_JCB }}">
                                                        <p class="{{ VC::MB0 }}">
                                                            <span class="d-inline-block {{ VC::TXSM }}">
                                                                {{ e(data_get($bug, 'description') ?: '-') }}
                                                            </span>
                                                        </p>
                                                        <p class="{{ VC::MB0 }}">
                                                            @if(method_exists($bug, 'users'))
                                                                @php 
                                                                    try {
                                                                        $users = $bug->users();
                                                                        $users = is_array($users) || is_object($users) ? $users : [];
                                                                    } catch (Exception $e) {
                                                                        $users = [];
                                                                    }
                                                                @endphp
                                                                @if(!empty($users) && (is_array($users) || (is_object($users) && method_exists($users, 'count') && $users->count() > 0)))
                                                                    <a href="#" class="btn {{ VC::MS2 }} p-0 {{ VC::AV_CC_SM }}">
                                                                        @foreach($users as $user)
                                                                            @if(isset($user) && is_object($user))
                                                                                @php
                                                                                    $avatar = data_get($user, 'avatar');
                                                                                    $avatarPath = !empty($avatar) && Storage::exists("uploads/avatar/{$avatar}")
                                                                                        ? asset(Storage::url("uploads/avatar/{$avatar}"))
                                                                                        : asset(Storage::url("uploads/avatar/avatar.png"));
                                                                                @endphp
                                                                                <img
                                                                                    src="{{ $avatarPath }}"
                                                                                    class="{{ VC::AV_CC_SM }}"
                                                                                    width="25" 
                                                                                    height="25"
                                                                                    alt="{{ e(data_get($user, 'name', __('Anonymous User'))) }}"
                                                                                    onerror='this.src="{{ asset(Storage::url("uploads/avatar/avatar.png")) }}"'
                                                                                >
                                                                            @endif
                                                                        @endforeach
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="{{ VC::RW }}">
                                                        <div class="col-6 {{ VC::TXS }}">
                                                            <i class="far fa-clock"></i>
                                                            <span>
                                                                @if(isset($user) && is_object($user) && method_exists($user, 'dateFormat'))
                                                                    @php
                                                                        $startDate = data_get($bug, 'start_date');
                                                                        try {
                                                                            $formattedStartDate = !empty($startDate) ? $user->dateFormat($startDate) : '-';
                                                                        } catch (Exception $e) {
                                                                            $formattedStartDate = !empty($startDate) ? e($startDate) : '-';
                                                                        }
                                                                    @endphp
                                                                    {{ $formattedStartDate }}
                                                                @else
                                                                    {{ e(data_get($bug, 'start_date', __('No start date could be found.'))) }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="col-6 text-end {{ VC::TXS }} {{ VC::FW600 }}">
                                                            <i class="far fa-clock"></i>
                                                            <span>
                                                                @if(isset($user) && is_object($user) && method_exists($user, 'dateFormat'))
                                                                    @php
                                                                        $dueDate = data_get($bug, 'due_date');
                                                                        try {
                                                                            $formattedDueDate = !empty($dueDate) ? $user->dateFormat($dueDate) : '-';
                                                                        } catch (Exception $e) {
                                                                            $formattedDueDate = !empty($dueDate) ? e($dueDate) : '-';
                                                                        }
                                                                    @endphp
                                                                    {{ $formattedDueDate }}
                                                                @else
                                                                    {{ e(data_get($bug, 'due_date', __('No due date could be found.'))) }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-md-12">
                                <h6 class="text-center m-3">{{ __('No tasks found') }}</h6>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
