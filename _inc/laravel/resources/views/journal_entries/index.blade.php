@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Journal Entry')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Journal Entry')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_JNL)
            @php
                $createJournalUrl = Route::has(ViewsConstants::JRN_ET.'.create')
                    ? route(ViewsConstants::JRN_ET.'.create')
                    : '#';
                $createJournalMsg = Utility::fetchUserLang(
                    $lang,
                    ViewsConstants::JRN_ET,
                    'create_journal_entry_unavailable'
                ) ?? 'Create New Journal route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                href="{{ $createJournalUrl }}"
                data-guard-url="{{ $createJournalUrl }}"
                data-create-listener-added="false"
                data-title="{{ __('Create New Journal') }}"
                data-sv-localized="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
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
                                <th> {{__('Journal ID')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Description')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($journalEntries as $journalEntry)
                                    @php
                                        $formId        = 'delete-journal-entry-form-'.$journalEntry->id;
                                    @endphp
                                    <tr>
                                        @can(PermissionsConstants::SHW_JNL)
                                            @php
                                                $showJournalUrl = Route::has(ViewsConstants::JRN_ET.'.show')
                                                    ? route(ViewsConstants::JRN_ET.'.show', $journalEntry->id)
                                                    : '#';
                                                $showJournalMsg = Utility::fetchUserLang(
                                                    $lang,
                                                    ViewsConstants::JRN_ET,
                                                    'show_journal_entry_unavailable'
                                                ) ?? 'Show Journal Entry route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <td class="Id">
                                                <a
                                                    href="{{ $showJournalUrl }}"
                                                    data-sv-localized="true"
                                                    data-guard-url="{{ $showJournalUrl }}"
                                                    data-show-listener-added="false"
                                                    class="{{ ViewClassNamesConstants::BT_OUTPM }}"
                                                >
                                                    {{ $user?->journalNumberFormat($journalEntry->journal_id) }}
                                                </a>
                                            </td>
                                        @endcan
                                        <td>{{ $user?->dateFormat($journalEntry->date) }}</td>
                                        <td>{{ $user?->priceFormat($journalEntry->totalCredit()) }}</td>
                                        <td>{{ !empty($journalEntry->description) ? $journalEntry->description : '-' }}</td>
                                        <td>
                                            @php
                                                $linkId  = 'edit-journal-entry-link-'.$journalEntry->id;
                                                $editUrl = Route::has(ViewsConstants::JRN_ET.'.edit')
                                                    ? route(ViewsConstants::JRN_ET.'.edit', [$journalEntry->id])
                                                    : '#';
                                            @endphp
                                            @can(PermissionsConstants::ED_JNL)
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="{{ $editUrl }}"
                                                        id="{{ $linkId }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-original-title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can(PermissionsConstants::DEL_JNL)
                                                @php
                                                    $linkId        = 'delete-journal-entry-link-'.$journalEntry->id;
                                                    $destroyRoute  = Route::has(ViewsConstants::JRN_ET.'.destroy')
                                                        ? [ViewsConstants::JRN_ET.'.destroy', $journalEntry->id]
                                                        : ['#'];
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method' => 'DELETE',
                                                        'route'  => $destroyRoute,
                                                        'id'     => $formId
                                                    ]) !!}
                                                        <a href="#"
                                                           id="{{ $linkId }}"
                                                           class="{{ ViewClassNamesConstants::TRS_PARA }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-original-title="{{ __('Delete') }}">
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
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
@push
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar: {
                edit_journal_entry_unavailable: 'مسار التعديل غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.',
                delete_journal_entry_unavailable: 'مسار الحذف غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.'
            },
            da: {
                edit_journal_entry_unavailable: 'Redigeringsruten er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.',
                delete_journal_entry_unavailable: 'Sletningsruten er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.'
            },
            de: {
                edit_journal_entry_unavailable: 'Bearbeitungsroute nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.',
                delete_journal_entry_unavailable: 'Löschroute nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.'
            },
            en: {
                edit_journal_entry_unavailable: 'Edit route is unavailable. Please contact technical support or your domain administrator.',
                delete_journal_entry_unavailable: 'Delete route is unavailable. Please contact technical support or your domain administrator.'
            },
            es: {
                edit_journal_entry_unavailable: 'La ruta de edición no está disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.',
                delete_journal_entry_unavailable: 'La ruta de eliminación no está disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.'
            },
            fr: {
                edit_journal_entry_unavailable: 'La route d\'édition n\'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.',
                delete_journal_entry_unavailable: 'La route de suppression n\'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.'
            },
            he: {
                edit_journal_entry_unavailable: 'נתיב העריכה אינו זמין. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.',
                delete_journal_entry_unavailable: 'נתיב המחיקה אינו זמין. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.'
            },
            it: {
                edit_journal_entry_unavailable: 'La rotta di modifica non è disponibile. Si prega di contattare il supporto tecnico o l\'amministratore del dominio.',
                delete_journal_entry_unavailable: 'La rotta di eliminazione non è disponibile. Si prega di contattare il supporto tecnico o l\'amministratore del dominio.'
            },
            ja: {
                edit_journal_entry_unavailable: '編集ルートが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。',
                delete_journal_entry_unavailable: '削除ルートが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。'
            },
            nl: {
                edit_journal_entry_unavailable: 'Bewerkingsroute is niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.',
                delete_journal_entry_unavailable: 'Verwijderingsroute is niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.'
            },
            pl: {
                edit_journal_entry_unavailable: 'Trasa edycji jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.',
                delete_journal_entry_unavailable: 'Trasa usuwania jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.'
            },
            pt: {
                edit_journal_entry_unavailable: 'A rota de edição não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.',
                delete_journal_entry_unavailable: 'A rota de exclusão não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.'
            },
            'pt-br': {
                edit_journal_entry_unavailable: 'A rota de edição não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.',
                delete_journal_entry_unavailable: 'A rota de exclusão não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.'
            },
            ru: {
                edit_journal_entry_unavailable: 'Маршрут редактирования недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.',
                delete_journal_entry_unavailable: 'Маршрут удаления недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.'
            },
            tr: {
                edit_journal_entry_unavailable: 'Düzenleme rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.',
                delete_journal_entry_unavailable: 'Silme rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.'
            },
            zh: {
                edit_journal_entry_unavailable: '编辑路由不可用。请联系技术支持或您的域管理员。',
                delete_journal_entry_unavailable: '删除路由不可用。请联系技术支持或您的域管理员。'
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
    @can(PermissionsConstants::SHW_JNL)
        <script defer>
            (() => {
                try {
                    const link = document.querySelector('td.Id a.{{ ViewClassNamesConstants::BT_OUTPM }}');
                    const alias = 'data-show-listener-added';
                    if (link && link.getAttribute(alias) !== 'true') {
                        link.setAttribute(alias, 'true');
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            const url = link.getAttribute('data-guard-url')
                                ?? link.getAttribute('href');
                            if (url === '#') {
                                const msg = "{{ $showJournalMsg }}";
                                const toastEl = document.querySelector('.toast');
                                if (
                                    toastEl &&
                                    window.bootstrap &&
                                    typeof bootstrap.Toast === 'function'
                                ) {
                                    const toast = new bootstrap.Toast(toastEl);
                                    const body = toastEl.querySelector('.toast-body');
                                    if (body) {
                                        body.textContent = msg;
                                    }
                                    toast.show();
                                } else {
                                    alert(msg);
                                }
                                return;
                            }
                            window.location.href = url;
                        });
                    }
                } catch (error) {
                }
            })();
        </script>
    @endcan
    @can(PermissionsConstants::CR_JNL)
        <script defer>
            (() => {
                try {
                    const btn = document.querySelector(
                        'a.{{ ViewClassNamesConstants::BT_SM_PM }}[data-guard-url]'
                    );
                    const alias = 'data-create-listener-added';
                    if (btn && btn.getAttribute(alias) !== 'true') {
                        btn.setAttribute(alias, 'true');
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const url = btn.getAttribute('data-guard-url')
                                ?? btn.getAttribute('href');
                            if (url === '#') {
                                const msg = "{{ $createJournalMsg }}";
                                const toastEl = document.querySelector('.toast');
                                if (
                                    toastEl &&
                                    window.bootstrap &&
                                    typeof bootstrap.Toast === 'function'
                                ) {
                                    const toast = new bootstrap.Toast(toastEl);
                                    const body = toastEl.querySelector('.toast-body');
                                    if (body) {
                                        body.textContent = msg;
                                    }
                                    toast.show();
                                } else {
                                    alert(msg);
                                }
                                return;
                            }
                            window.location.href = url;
                        });
                    }
                } catch (error) {
                }
            })();
        </script>
    @endcan
    @can(PermissionsConstants::ED_JNL)
        <script defer>
            (() => {
                const link  = document.getElementById('{{ $linkId }}');
                const alias = 'data-listening-editjournalentryclick';
                if (!link.hasAttribute(alias)) {
                    link.addEventListener('click', event => {
                        if (link.getAttribute(alias) !== 'true') return;
                        const url = link.getAttribute('data-url');
                        const href = link.getAttribute('href');
                        if ((!url || url === '#') && (!href || href === '#')) {
                            const errFb = "# ERROR";
                            const dataClientLocalized = "data-client-localized";
                            const dataGuardMsg = "data-guard-msg";
                            let msg = errFb;
                            if (link.getAttribute("data-sv-localized") === "true" || link.getAttribute(dataClientLocalized) === "true")
                                msg = link.getAttribute(dataGuardMsg) || errFb;
                            else {
                                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                                .toLowerCase()
                                .replace(/_/g, "-");
                                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                                const msgKey = 'edit_journal_entry_unavailable'; 
                                msg =
                                window.translations?.[lang]?.[msgKey] ||
                                link.getAttribute(dataGuardMsg) ||
                                window.translations?.["en"]?.[msgKey] ||
                                errFb;
                                if (msg !== errFb) {
                                    link.setAttribute(dataGuardMsg, msg);
                                    link.setAttribute(dataClientLocalized, "true");
                                }
                            }
                            const hasBS = Array.from(document.scripts)
                                .some(s => s.src && s.src.includes('bootstrap.min.js')
                                        && window.bootstrap
                                        && typeof window.bootstrap.Modal === 'function');
                            if (hasBS) {
                                const wrapper = document.createElement('div');
                                wrapper.innerHTML = `
                                    <div class="modal fade" tabindex="-1">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Error</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body"><p>${msg}</p></div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                                document.body.appendChild(wrapper);
                                new window.bootstrap.Modal(wrapper.querySelector('.modal')).show();
                            } else {
                                alert(msg);
                            }
                            el.setAttribute('data-failed-route', 'true');
                            event.preventDefault();
                        }
                    });
                    link.setAttribute(alias, 'true');
                }
            })();
        </script>
    @endcan
    @can(PermissionsConstants::DEL_JNL)
        <script defer>
            (() => {
                const link  = document.getElementById('{{ $linkId }}');
                const alias = 'data-listening-deletejournalentryclick';
                if (!link.hasAttribute(alias)) {
                    link.addEventListener('click', event => {
                        if (link.getAttribute(alias) !== 'true') return;
                        event.preventDefault();
                        const form   = document.getElementById('{{ $formId }}');
                        const url = form.getAttribute('data-url');
                        const dataAction = form.getAttribute('data-action');
                        const action = form.getAttribute('action');
                        if ((!url || url === '#') && (!dataAction || dataAction === '#') && (!action || action === '#')) {
                            const errFb = "# ERROR";
                            const dataClientLocalized = "data-client-localized";
                            const dataGuardMsg = "data-guard-msg";
                            let msg = errFb;
                            if (form.getAttribute("data-sv-localized") === "true" || form.getAttribute(dataClientLocalized) === "true")
                                msg = form.getAttribute(dataGuardMsg) || errFb;
                            else {
                                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                                .toLowerCase()
                                .replace(/_/g, "-");
                                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                                const msgKey = 'delete_journal_entry_unavailable'; 
                                msg =
                                window.translations?.[lang]?.[msgKey] ||
                                form.getAttribute(dataGuardMsg) ||
                                window.translations?.["en"]?.[msgKey] ||
                                errFb;
                                if (msg !== errFb) {
                                    form.setAttribute(dataGuardMsg, msg);
                                    form.setAttribute(dataClientLocalized, "true");
                                }
                            }
                            const hasBS = Array.from(document.scripts)
                                .some(s => s.src
                                    && s.src.includes('bootstrap.min.js')
                                    && window.bootstrap
                                    && typeof window.bootstrap.Modal === 'function');
                            if (hasBS) {
                                const wrapper = document.createElement('div');
                                wrapper.innerHTML = `
                                    <div class="modal fade" tabindex="-1">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Error</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>${msg}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                                document.body.appendChild(wrapper);
                                new window.bootstrap.Modal(wrapper.querySelector('.modal')).show();
                            } else {
                                alert(msg);
                            }
                            return;
                        }
                        const confirmMsg = 'Are You Sure? This action cannot be undone. Do you want to continue?';
                        if (confirm(confirmMsg)) form.submit();
                    });
                    link.setAttribute(alias, 'true');
                }
            })();
        </script>
    @endcan
@endpush
