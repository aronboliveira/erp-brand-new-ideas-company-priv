@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        PlansConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{User, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, File, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $attachments = Utility::getFile('contract_attachment');
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Contract Detail') }}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
    <script async>
        const _patch = {
            ar: {
                contract_notes_failed  : 'فشل حفظ وصف العقد.',
                contract_file_failed   : 'فشل معالجة مرفقات العقد.',
                comment_add_failed     : 'فشل إضافة التعليق.',
                comment_delete_failed  : 'فشل حذف التعليق.',
                status_update_failed   : 'فشل تحديث الحالة.'
            },
            da: {
                contract_notes_failed  : 'Kunne ikke gemme kontraktbeskrivelse.',
                contract_file_failed   : 'Kunne ikke behandle kontraktvedhæftning.',
                comment_add_failed     : 'Kunne ikke tilføje kommentar.',
                comment_delete_failed  : 'Kunne ikke slette kommentar.',
                status_update_failed   : 'Kunne ikke opdatere status.'
            },
            de: {
                contract_notes_failed  : 'Speichern der Vertragsbeschreibung fehlgeschlagen.',
                contract_file_failed   : 'Vertragsanhang konnte nicht verarbeitet werden.',
                comment_add_failed     : 'Kommentar konnte nicht hinzugefügt werden.',
                comment_delete_failed  : 'Kommentar konnte nicht gelöscht werden.',
                status_update_failed   : 'Status konnte nicht aktualisiert werden.'
            },
            en: {
                contract_notes_failed  : 'Failed to save contract description.',
                contract_file_failed   : 'Failed to process contract attachment.',
                comment_add_failed     : 'Failed to add comment.',
                comment_delete_failed  : 'Failed to delete comment.',
                status_update_failed   : 'Failed to update status.'
            },
            es: {
                contract_notes_failed  : 'Error al guardar la descripción del contrato.',
                contract_file_failed   : 'Error al procesar el adjunto del contrato.',
                comment_add_failed     : 'Error al añadir comentario.',
                comment_delete_failed  : 'Error al eliminar comentario.',
                status_update_failed   : 'Error al actualizar el estado.'
            },
            fr: {
                contract_notes_failed  : 'Échec de l’enregistrement de la description du contrat.',
                contract_file_failed   : 'Échec du traitement de la pièce jointe du contrat.',
                comment_add_failed     : 'Échec de l’ajout du commentaire.',
                comment_delete_failed  : 'Échec de la suppression du commentaire.',
                status_update_failed   : 'Échec de la mise à jour du statut.'
            },
            ja: {
                contract_notes_failed  : '契約内容の保存に失敗しました。',
                contract_file_failed   : '契約添付ファイルの処理に失敗しました。',
                comment_add_failed     : 'コメントの追加に失敗しました。',
                comment_delete_failed  : 'コメントの削除に失敗しました。',
                status_update_failed   : 'ステータスの更新に失敗しました。'
            },
            nl: {
                contract_notes_failed  : 'Kon contractbeschrijving niet opslaan.',
                contract_file_failed   : 'Kon contractbijlage niet verwerken.',
                comment_add_failed     : 'Kon commentaar niet toevoegen.',
                comment_delete_failed  : 'Kon commentaar niet verwijderen.',
                status_update_failed   : 'Kon status niet bijwerken.'
            },
            pl: {
                contract_notes_failed  : 'Nie udało się zapisać opisu umowy.',
                contract_file_failed   : 'Nie udało się przetworzyć załącznika umowy.',
                comment_add_failed     : 'Nie udało się dodać komentarza.',
                comment_delete_failed  : 'Nie udało się usunąć komentarza.',
                status_update_failed   : 'Nie udało się zaktualizować statusu.'
            },
            pt: {
                contract_notes_failed  : 'Falha ao salvar descrição do contrato.',
                contract_file_failed   : 'Falha ao processar anexo do contrato.',
                comment_add_failed     : 'Falha ao adicionar comentário.',
                comment_delete_failed  : 'Falha ao excluir comentário.',
                status_update_failed   : 'Falha ao atualizar o status.'
            },
            'pt-br': {
                contract_notes_failed  : 'Falha ao salvar a descrição do contrato.',
                contract_file_failed   : 'Falha ao processar o anexo do contrato.',
                comment_add_failed     : 'Falha ao adicionar o comentário.',
                comment_delete_failed  : 'Falha ao excluir o comentário.',
                status_update_failed   : 'Falha ao atualizar o status.'
            },
            ru: {
                contract_notes_failed  : 'Не удалось сохранить описание контракта.',
                contract_file_failed   : 'Не удалось обработать вложение контракта.',
                comment_add_failed     : 'Не удалось добавить комментарий.',
                comment_delete_failed  : 'Не удалось удалить комментарий.',
                status_update_failed   : 'Не удалось обновить статус.'
            },
            tr: {
                contract_notes_failed  : 'Sözleşme açıklaması kaydedilemedi.',
                contract_file_failed   : 'Sözleşme eki işlenemedi.',
                comment_add_failed     : 'Yorum eklenemedi.',
                comment_delete_failed  : 'Yorum silinemedi.',
                status_update_failed   : 'Durum güncellenemedi.'
            },
            zh: {
                contract_notes_failed  : '无法保存合同描述。',
                contract_file_failed   : '无法处理合同附件。',
                comment_add_failed     : '无法添加评论。',
                comment_delete_failed  : '无法删除评论。',
                status_update_failed   : '无法更新状态。'
            }
        };
        window.translations = Object.keys(window.translations || {}).length
        ? Object.keys(_patch).reduce((acc, l) => {
            acc[l] = { ...(acc[l] || {}), ..._patch[l] };
            return acc;
            }, window.translations)
        : _patch;
    </script>
    <script defer>
        (() => {
        const ERR_FB  = '# ERROR';
        const LANG_K  = 'erp-np-lang';
        const CL_FLAG = 'data-client-localized';
        const GD_FLAG = 'data-guard-msg';
        
        const langShort = () => {
            const l = (sessionStorage.getItem(LANG_K) || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g, '-');
            return l === 'pt-br' ? l : l.slice(0, 2);
        };
        
        const t = k => window.translations?.[langShort()]?.[k]
                    ?? window.translations?.en?.[k]
                    ?? ERR_FB;
        
        const toast = (msg, type='error') =>
            window.show_toastr ? window.show_toastr(type, msg, type) : alert(msg);
        
        const token = () =>
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const toastErr = key => toast(t(key), 'error');
        
        document.addEventListener('DOMContentLoaded', () => {
            const sn = $('.summernote-simple');
            if (!sn.length) return;
        
            @can('manage contract')
            sn.on('summernote.blur', function () {
            fetch("{{ route(ViewsConstants::CTC.'.contract_description.store', $contract->id) }}", {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': token(), 'Content-Type':'application/json' },
                body   : JSON.stringify({ contract_description: $(this).val() })
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(res => res.is_success
                ? toast(res.success, 'success')
                : toastErr('contract_notes_failed'))
            .catch(() => toastErr('contract_notes_failed'));
            });
            @else
            sn.summernote('disable');
            @endcan
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Dropzone) return toastErr('contract_file_failed');
        
            Dropzone.autoDiscover = false;
        
            const dz = new Dropzone('#dropzonewidget', {
            maxFiles       : 20,
            parallelUploads: 1,
            url            : "{{ route(ViewsConstants::CTC.'.file.upload', [$contract->id]) }}",
            init() {
                this.on('sending', (file, xhr, formData) => {
                formData.append('_token', token());
                formData.append('contract_id', {{ $contract->id }});
                });
            },
            success(file, res) {
                if (!res?.is_success) {
                this.removeFile(file);
                return toastErr('contract_file_failed');
                }
                if (res.status === 1) {
                toast(res.success_msg, 'success');
                } else {
                toast('{{ __('Attachment Create Successfully!') }}', 'success');
                attachBtns(file, res);
                }
            },
            error(file) {
                this.removeFile(file);
                toastErr('contract_file_failed');
            }
            });
        
            const attachBtns = (file, res) => {
            const dwn = document.createElement('a');
            dwn.href        = res.download;
            dwn.className   = 'action-btn btn-primary mx-1 mt-1 btn btn-sm d-inline-flex align-items-center';
            dwn.title       = '{{ __("Download") }}';
            dwn.innerHTML   = '<i class="fas fa-download"></i>';
        
            const del = document.createElement('a');
            del.href        = res.delete;
            del.className   = 'action-btn btn-danger  mx-1 mt-1 btn btn-sm d-inline-flex align-items-center';
            del.title       = '{{ __("Delete") }}';
            del.innerHTML   = '<i class="ti ti-trash"></i>';
        
            del.addEventListener('click', e => {
                e.preventDefault(); e.stopPropagation();
                if (!confirm('Are you sure?')) return;
                fetch(del.href, {
                method: 'DELETE',
                headers:{ 'X-CSRF-TOKEN': token() }
                })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(r => {
                if (r.is_success) file.previewElement.remove();
                else toastErr('contract_file_failed');
                location.reload();
                })
                .catch(() => toastErr('contract_file_failed'));
            });
        
            file.previewTemplate.appendChild(dwn);
            @can('manage contract') file.previewTemplate.appendChild(del); @endcan
            };
        });
        
        document.addEventListener('click', e => {
            if (e.target.closest('#comment_submit')) {
            const btn   = e.target.closest('#comment_submit');
            const form  = document.getElementById('form-comment');
            const txtEl = form.querySelector('textarea[name="comment"]');
            const val   = txtEl.value.trim();
            if (!val) return toast('{{ __("Please write comment!") }}');
        
            fetch(form.dataset.action, {
                method : 'POST',
                headers: {'X-CSRF-TOKEN': token(), 'Content-Type':'application/json'},
                body   : JSON.stringify({ comment: val })
            })
            .then(r => r.ok ? r.text() : Promise.reject())
            .then(raw => {
                try { return JSON.parse(raw); } catch { return {}; }
            })
            .then(data => {
                toast('{{ __("Comment Create Successfully!") }}', 'success');
                setTimeout(() => location.reload(), 500);
            })
            .catch(() => toastErr('comment_add_failed'));
            }
        
            if (e.target.closest('.delete-comment')) {
            e.preventDefault();
            const btn  = e.target.closest('.delete-comment');
            const row  = btn.closest('.list-group-item');
            fetch(btn.dataset.url, {
                method : 'DELETE',
                headers: { 'X-CSRF-TOKEN': token(), 'Content-Type':'application/json'}
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(res => {
                if (res?.message) toastErr('comment_delete_failed');
                else {
                row?.remove();
                toast('{{ __("Comment Deleted Successfully!") }}', 'success');
                }
            })
            .catch(() => toastErr('comment_delete_failed'));
            }
        });
        
        document.addEventListener('click', e => {
            const st = e.target.closest('.status');
            if (!st) return;
            fetch(st.dataset.url, {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': token(), 'Content-Type':'application/json'},
            body   : JSON.stringify({ status: st.dataset.id })
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => {
            toast('{{ __("Status Update Successfully!") }}', 'success');
            location.reload();
            })
            .catch(() => toastErr('status_update_failed'));
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            try {
            new bootstrap.ScrollSpy(document.body, {
                target : '#useradd-sidenav',
                offset : 300
            });
            } catch {}
            $(document).on('click', '.list-group-item', function () {
            $('.list-group-item').removeClass('text-primary');
            $(this).addClass('text-primary');
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
    @php
        $contractsIndexRoute          = Route::has(ViewsConstants::CTC . '.index')
            ? route(ViewsConstants::CTC . '.index')
            : '#';
        $contractsIndexLinkId         = 'contracts-index-link';
        $contractsIndexGuardMsg       = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CTC,
            'contract_index_route_unavailable'
        ) ?? 'Contract index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a
            id="{{ $contractsIndexLinkId }}"
            href="{{ $contractsIndexRoute }}"
            data-url="{{ $contractsIndexRoute }}"
            data-guard-msg="{{ $contractsIndexGuardMsg }}"
        >
            {{ __('contract') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const link = document.getElementById('{{ $contractsIndexLinkId }}');
                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', event => {
                    try {
                        const href = link.getAttribute('href');
                        const url  = link.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl      = document.createElement('div');
                            toastEl.className  = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body         = document.createElement('div');
                            body.className     = 'toast-body';
                            body.textContent   = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        link.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    @endpush
    <li class="breadcrumb-item active" aria-current="page">{{$user?->contractNumberFormat($contract->id)}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }} {{ VC::DFL_AIC }}">
        @php
            $downloadRoute      = Route::has(ViewsConstants::CTC . '.download.pdf')
                ? route(ViewsConstants::CTC . '.download.pdf', Crypt::encrypt($contract->id))
                : '#';
            $downloadLinkId     = 'contract-download-link-' . $contract->id;
            $downloadGuardMsg   = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CTC,
                'contract_download_pdf_route_unavailable'
            ) ?? 'Contract PDF download route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="{{ $downloadLinkId }}"
            href="{{ $downloadRoute }}"
            class="{{ VC::BT_SM_PM }} btn-icon m-1"
            data-url="{{ $downloadRoute }}"
            data-guard-msg="{{ $downloadGuardMsg }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="{{ __('Download') }}"
            target="_blank"
        >
            <i class="{{ VC::TI_DWN }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const link = document.getElementById('{{ $downloadLinkId }}');
                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                    link.setAttribute('data-listener-active', 'true');
                    link.addEventListener('click', event => {
                        try {
                            const href = link.getAttribute('href');
                            const url  = link.getAttribute('data-url');
                            if ((href && href !== '#') || (url && url !== '#')) return;
                            event.preventDefault();
                            const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                            let container       = document.getElementById('toast-container');
                            if (!container) {
                                container       = document.createElement('div');
                                container.id    = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (bootstrapLink && window.bootstrap) {
                                const toastEl      = document.createElement('div');
                                toastEl.className  = 'toast';
                                toastEl.setAttribute('role', 'alert');
                                toastEl.setAttribute('aria-live', 'assertive');
                                toastEl.setAttribute('aria-atomic', 'true');
                                const body         = document.createElement('div');
                                body.className     = 'toast-body';
                                body.textContent   = msg;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            } else {
                                alert(msg);
                            }
                            link.setAttribute('data-failed-route', 'true');
                        } catch (e) {}
                    });
                })();
            </script>
        @endpush
        @php
            $previewRoute        = Route::has(ViewsConstants::CTC . '.get')
                ? route(ViewsConstants::CTC . '.get', $contract->id)
                : '#';
            $previewLinkId       = 'contract-preview-link-' . $contract->id;
            $previewGuardMsg     = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CTC,
                'contract_preview_route_unavailable'
            ) ?? 'Contract preview route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="{{ $previewLinkId }}"
            href="{{ $previewRoute }}"
            target="_blank"
            class="{{ VC::BT_SM_PM }} btn-icon m-1"
            data-url="{{ $previewRoute }}"
            data-guard-msg="{{ $previewGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ __('Preview') }}"
        >
            <i class="{{ VC::TI_EYE_WT }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const link = document.getElementById('{{ $previewLinkId }}');
                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                    link.setAttribute('data-listener-active', 'true');
                    link.addEventListener('click', event => {
                        try {
                            const href = link.getAttribute('href');
                            const url  = link.getAttribute('data-url');
                            if ((href && href !== '#') || (url && url !== '#')) return;
                            event.preventDefault();
                            const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                            let container       = document.getElementById('toast-container');
                            if (!container) {
                                container       = document.createElement('div');
                                container.id    = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (bootstrapLink && window.bootstrap) {
                                const toastEl      = document.createElement('div');
                                toastEl.className  = 'toast';
                                toastEl.setAttribute('role', 'alert');
                                toastEl.setAttribute('aria-live', 'assertive');
                                toastEl.setAttribute('aria-atomic', 'true');
                                const body         = document.createElement('div');
                                body.className     = 'toast-body';
                                body.textContent   = msg;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            } else {
                                alert(msg);
                            }
                            link.setAttribute('data-failed-route', 'true');
                        } catch (e) {}
                    });
                })();
            </script>
        @endpush
        @if($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN)
            @php
                $sendMailRoute        = Route::has(ViewsConstants::CTC . '.send.mail')
                    ? route(ViewsConstants::CTC . '.send.mail', $contract->id)
                    : '#';
                $sendMailLinkId       = 'contract-send-mail-' . $contract->id;
                $sendMailGuardMsg     = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CTC,
                    'contract_send_mail_route_unavailable'
                ) ?? 'Send email route for Contracts is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="{{ VC::ACT_BTN_INF }}">
                <a
                    id="{{ $sendMailLinkId }}"
                    href="#"
                    class="{{ VC::BT_SM_PM }} btn-icon m-1"
                    data-url="{{ $sendMailRoute }}"
                    data-guard-msg="{{ $sendMailGuardMsg }}"
                    data-bs-toggle="tooltip"
                    title="{{ __('Send Email') }}"
                >
                    <i class="ti ti-mail text-white"></i>
                </a>
            </div>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const link = document.getElementById('{{ $sendMailLinkId }}');
                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                        link.setAttribute('data-listener-active', 'true');
                        link.addEventListener('click', event => {
                            try {
                                const url = link.getAttribute('data-url');
                                if (!url || url === '#') {
                                    event.preventDefault();
                                    const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                    let container       = document.getElementById('toast-container');
                                    if (!container) {
                                        container       = document.createElement('div');
                                        container.id    = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (bootstrapLink && window.bootstrap) {
                                        const toastEl      = document.createElement('div');
                                        toastEl.className  = 'toast';
                                        toastEl.setAttribute('role', 'alert');
                                        toastEl.setAttribute('aria-live', 'assertive');
                                        toastEl.setAttribute('aria-atomic', 'true');
                                        const body         = document.createElement('div');
                                        body.className     = 'toast-body';
                                        body.textContent   = msg;
                                        toastEl.appendChild(body);
                                        container.appendChild(toastEl);
                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                    } else {
                                        alert(msg);
                                    }
                                    link.setAttribute('data-failed-route', 'true');
                                    return;
                                }
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
            @php
                $copyRoute        = Route::has(ViewsConstants::CTC . '.copy')
                    ? route(ViewsConstants::CTC . '.copy', $contract->id)
                    : '#';
                $copyBtnId        = 'contract-copy-btn-' . $contract->id;
                $copyGuardMsg     = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CTC,
                    'contract_copy_route_unavailable'
                ) ?? 'Contract copy route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $copyBtnId }}"
                href="#"
                data-url="{{ $copyRoute }}"
                data-guard-msg="{{ $copyGuardMsg }}"
                data-size="lg"
                data-ajax-popup="true"
                class="{{ VC::BT_SM_PM }} btn-icon m-1"
                data-bs-toggle="tooltip"
                title="{{ __('Duplicate') }}"
            >
                <i class="{{ VC::TI_CC_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('{{ $copyBtnId }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', event => {
                            try {
                                const url = btn.getAttribute('data-url');
                                if (!url || url === '#') {
                                    event.preventDefault();
                                    const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                    let container       = document.getElementById('toast-container');
                                    if (!container) {
                                        container       = document.createElement('div');
                                        container.id    = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (bootstrapLink && window.bootstrap) {
                                        const toastEl      = document.createElement('div');
                                        toastEl.className  = 'toast';
                                        toastEl.setAttribute('role', 'alert');
                                        toastEl.setAttribute('aria-live', 'assertive');
                                        toastEl.setAttribute('aria-atomic', 'true');
                                        const body         = document.createElement('div');
                                        body.className     = 'toast-body';
                                        body.textContent   = msg;
                                        toastEl.appendChild(body);
                                        container.appendChild(toastEl);
                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                    } else {
                                        alert(msg);
                                    }
                                    btn.setAttribute('data-failed-route', 'true');
                                    return;
                                }
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
        @endif
        @if(
            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN) ||
            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CL && $contract->status == 'accept')
        )
        @php
            $signatureRoute           = Route::has(ViewsConstants::CTC.'.signature')
                ? route(ViewsConstants::CTC.'.signature', $contract->id)
                : '#';
            $signatureLinkId          = 'contract-signature-link-' . $contract->id;
            $signatureGuardMsg        = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CTC,
                'contract_signature_route_unavailable'
            ) ?? 'Add signature route for Contracts is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="{{ $signatureLinkId }}"
            href="#"
            data-url="{{ $signatureRoute }}"
            data-guard-msg="{{ $signatureGuardMsg }}"
            data-size="lg"
            data-ajax-popup="true"
            class="{{ VC::BT_SM_PM }} btn-icon m-1"
            data-bs-toggle="tooltip"
            data-title="{{ __('Add signature') }}"
            >
            <i class="{{ VC::TI_PC_WT }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const btn = document.getElementById('{{ $signatureLinkId }}');
                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                    btn.setAttribute('data-listener-active', 'true');
                    btn.addEventListener('click', event => {
                        try {
                            const url = btn.getAttribute('data-url');
                            if (!url || url === '#') {
                                event.preventDefault();
                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container       = document.getElementById('toast-container');
                                if (!container) {
                                    container       = document.createElement('div');
                                    container.id    = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl      = document.createElement('div');
                                    toastEl.className  = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body         = document.createElement('div');
                                    body.className     = 'toast-body';
                                    body.textContent   = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                                return;
                            }
                        } catch (e) {}
                    });
                })();
            </script>
        @endpush
        @endif
        @php $statusList = \App\Models\Contract::status(); @endphp
        @if($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CL)
            <ul class="list-unstyled m-0">
                <li class="{{ VC::STT_DD_IT }}">
                    <a class="{{ VC::DRP_NO_ARROW }}"
                    data-bs-toggle="dropdown"
                    role="button"
                    aria-haspopup="false"
                    aria-expanded="false">
                        <span class="drp-text hide-mob text-primary">
                            {{ ucfirst($contract->status) }}
                            <i class="{{ VC::TI_CHV_RT }} drp-arrow nocolor hide-mob"></i>
                        </span>
                    </a>
                    <div class="{{ VC::DRP_DSH_MN }}">
                        @php
                            $statusRoute       = Route::has(ViewsConstants::CTC.'.status')
                                ? route(ViewsConstants::CTC.'.status', $contract->id)
                                : '#';
                            $statusGuardMsg    = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::CTC,
                                'contract_status_route_unavailable'
                            ) ?? 'Contract status update route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        @foreach($statusList as $key => $label)
                            <a
                                id="contract-status-{{ $contract->id }}-{{ $key }}"
                                href="#"
                                class="dropdown-item status"
                                data-id="{{ $key }}"
                                data-url="{{ $statusRoute }}"
                                data-guard-msg="{{ $statusGuardMsg }}"
                            >
                                {{ ucfirst($label) }}
                            </a>
                        @endforeach
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const items = document.querySelectorAll('.dropdown-item.status');
                                    items.forEach(item => {
                                        const flag = 'data-listener-active';
                                        if (item.getAttribute(flag) === 'true') return;
                                        item.setAttribute(flag, 'true');
                                        item.addEventListener('click', event => {
                                            try {
                                                const url = item.getAttribute('data-url');
                                                if (!url || url === '#') {
                                                    event.preventDefault();
                                                    const msg           = item.getAttribute('data-guard-msg');
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container       = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container       = document.createElement('div');
                                                        container.id    = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl      = document.createElement('div');
                                                        toastEl.className  = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body         = document.createElement('div');
                                                        body.className     = 'toast-body';
                                                        body.textContent   = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    item.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (e) {}
                                        });
                                    });
                                })();
                            </script>
                        @endpush
                    </div>
                </li>
            </ul>
        @endif
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL3 }}">
            @php
                $sections = [__('General'), __('Attachment'), __('Comment'), __('Notes')];
            @endphp
            <div class="{{ VC::CD_STK }}" style="top:30px">
                <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                    @foreach($sections as $i => $label)
                        <a href="#useradd-{{ $i + 1 }}" class="{{ VC::LGI_ACT_NBD }}">
                            {{ $label }}
                            <div class="{{ VC::FEND }}"><i class="{{ VC::TI_CHV_RT }}"></i></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="{{ VC::CL9 }}">
            <div id="useradd-1">
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CL7 }}">
                        <div class="{{ VC::RW }}">
                            @php
                                $counters = [
                                    ['label'=>__('Attachment'),'icon'=>'ti ti-user-plus','bg'=>'bg-primary','count'=>count($contract->files)],
                                    ['label'=>__('Comment'),'icon'=>'ti ti-click','bg'=>'bg-info','count'=>count($contract->comment)],
                                    ['label'=>__('Notes'),'icon'=>'ti ti-file','bg'=>'bg-warning','count'=>count($contract->note)],
                                ];
                            @endphp
                            @foreach($counters as $box)
                                <div class="{{ VC::CL3 }} {{ VC::CS6 }}">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-body" style="min-height:205px">
                                            <div class="theme-avatar {{ $box['bg'] }}">
                                                <i class="{{ $box['icon'] }}"></i>
                                            </div>
                                            <h6 class="{{ VC::MB3 }} mt-4">{{ $box['label'] }}</h6>
                                            <h3 class="{{ VC::MB0 }}">{{ $box['count'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xxl-5">
                        <div class="{{ VC::CD }}">
                            <div class="card-body pt-0 mb-n4 mt-n2">
                                <address class="mb-0 text-sm">
                                    <dl class="row mt-4 align-items-center">
                                        <h5>{{ __('Contract Detail') }}</h5><br>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Subject') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $contract->subject }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Project') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $contract->projects->project_name ?? '-' }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Value') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $user?->priceFormat($contract->value) }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Type') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $contract->types->name ?? '-' }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Status') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $contract->status }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Start Date') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $user?->dateFormat($contract->start_date) }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('End Date') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{ $user?->dateFormat($contract->end_date) }}</dd>
                                    </dl>
                                </address>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD }}">
                    <div class="card-header"><h5 class="{{ VC::MB0 }}">{{ __('Contract Description ') }}</h5></div>
                    <div class="card-body">
                        <div class="{{ VC::C12 }}">
                            <div class="{{ VC::FM_G }} mt-3">
                                <textarea class="summernote-simple">{!! $contract->contract_description !!}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="useradd-2">
                <div class="{{ VC::CD }}">
                    <div class="card-header"><h5 class="{{ VC::MB0 }}">{{ __('Contract Attachments') }}</h5></div>
                    <div class="card-body">
                        @if(
                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN) ||
                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CL && $contract->status == 'accept')
                        )
                            <div class="{{ VC::C12 }} dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                        @endif
                        <div class="scrollbar-inner">
                            <div class="card-wrapper p-3 lead-common-box">
                                @foreach($contract->files as $file)
                                    <div class="{{ VC::CD }} mb-3 border shadow-none">
                                        <div class="{{ VC::PX3 }} {{ VC::PY2 }}">
                                            <div class="{{ VC::R_ALC }}">
                                                <div class="col">
                                                    <h6 class="{{ VC::MB0 }}">
                                                        <a href="#!">{{ $file->files }}</a>
                                                    </h6>
                                                    <p class="small text-muted">
                                                        @if(file_exists(storage_path('contract_attachment/'.$file->files)))
                                                            {{ number_format(\File::size(storage_path('contract_attachment/'.$file->files))/1048576,2).' '.__('MB') }}
                                                        @endif
                                                    </p>
                                                </div>

                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                    <a href="{{ $attachments.'/'.$file->files }}"
                                                    class="{{ VC::BT_SM_FL_CT }}"
                                                    download
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Download') }}">
                                                        <i class="{{ VC::TI_DWN }}"></i>
                                                    </a>
                                                </div>

                                                @if(
                                                    ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN && $contract->status == 'accept') ||
                                                    ($user && $user->id == $file[UsersConstants::COL_USER_ID])
                                                )
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        @php
                                                            $fileDeleteRoute          = Route::has(ViewsConstants::CTC . '.file.delete')
                                                                ? route(ViewsConstants::CTC . '.file.delete', [$contract->id, $file->id])
                                                                : '#';
                                                            $deleteFormId             = 'contract-file-delete-form-' . $file->id;
                                                            $deleteBtnId              = 'contract-file-delete-btn-' . $file->id;
                                                            $fileDeleteGuardMsg       = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::CTC,
                                                                'contract_file_delete_route_unavailable'
                                                            ) ?? 'Contract file delete route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        {!! Collective\Html\FormFacade::open([
                                                            'url'            => $fileDeleteRoute,
                                                            'method'         => 'DELETE',
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $fileDeleteRoute,
                                                            'data-guard-msg' => $fileDeleteGuardMsg,
                                                        ]) !!}
                                                            <a
                                                                id="{{ $deleteBtnId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-url="{{ $fileDeleteRoute }}"
                                                                data-guard-msg="{{ $fileDeleteGuardMsg }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $deleteBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const url = btn.getAttribute('data-url');
                                                                            if (url && url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="useradd-3">
                <div class="{{ VC::CD }}">
                    <div class="card-header"><h5 class="{{ VC::MB0 }}">{{ __('Comments') }}</h5></div>
                    <div class="card-body">
                        @if(
                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN) ||
                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CL && $contract->status == 'accept')
                        )
                            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                                <div class="{{ VC::FM_G }} w-100 mb-0">
                                    @php
                                        $commentStoreRoute     = Route::has(ViewsConstants::CTC . '.comment.store')
                                            ? route(ViewsConstants::CTC . '.comment.store', [$contract->id])
                                            : '#';
                                        $commentFormId         = 'form-comment';
                                        $commentStoreGuardMsg  = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CTC,
                                            'comment_store_route_unavailable'
                                        ) ?? 'Add comment route for Contracts is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <form
                                        method="post"
                                        class="card-comment-box"
                                        id="{{ $commentFormId }}"
                                        data-action="{{ $commentStoreRoute }}"
                                        data-guard-msg="{{ $commentStoreGuardMsg }}"
                                    >
                                        <textarea
                                            rows="1"
                                            class="{{ VC::FM_CT }}"
                                            name="comment"
                                            data-toggle="autosize"
                                            placeholder="{{ __('Add a comment...') }}"
                                        ></textarea>
                                    </form>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const form = document.getElementById('{{ $commentFormId }}');
                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                form.setAttribute('data-listener-active', 'true');
                                                form.addEventListener('submit', async event => {
                                                    try {
                                                        const url = form.getAttribute('data-action');
                                                        if (!url || url === '#') {
                                                            event.preventDefault();
                                                            const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container       = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container       = document.createElement('div');
                                                                container.id    = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl      = document.createElement('div');
                                                                toastEl.className  = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body         = document.createElement('div');
                                                                body.className     = 'toast-body';
                                                                body.textContent   = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            form.setAttribute('data-failed-route', 'true');
                                                            return;
                                                        }
                                                    } catch (e) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                </div>
                                <button id="comment_submit" class="{{ VC::BT_SM_PM }} mt-2">
                                    <i class="ti ti-brand-telegram"></i>
                                </button>
                            </div>
                        @endif
                        <div class="{{ VC::LG_FLSH }} mb-0" id="comments">
                            @foreach($contract->comment as $comment)
                                @php
                                    $cUser = User::find($comment[UsersConstants::COL_USER_ID]);
                                    $logo = Utility::getFile('uploads/avatar/');
                                @endphp
                                <div class="{{ VC::LGI }} border-0">
                                    <div class="{{ VC::R_ALC }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="{{ $logo.'/'.($cUser->avatar ?? 'avatar.png') }}" target="_blank">
                                                <img class="rounded-circle" width="40" height="40"
                                                    src="{{ $logo.'/'.($cUser->avatar ?? 'avatar.png') }}">
                                            </a>
                                        </div>
                                        <div class="{{ VC::C_AT }} flex-grow-1 ml-n2">
                                            <p class="h6 text-sm font-weight-light mb-0 text-break">{{ $comment->comment }}</p>
                                            <small>{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        @if(
                                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN && $contract->status == 'accept') ||
                                            ($user && $user->id == $comment[UsersConstants::COL_USER_ID])
                                        )
                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                @php
                                                    $commentDestroyRoute         = Route::has(ViewsConstants::CTC . '.comment.destroy')
                                                        ? route(ViewsConstants::CTC . '.comment.destroy', $comment->id)
                                                        : '#';
                                                    $commentDestroyFormId        = 'comment-destroy-form-' . $comment->id;
                                                    $commentDestroyBtnId         = 'comment-destroy-btn-' . $comment->id;
                                                    $commentDestroyGuardMsg      = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::CTC,
                                                        'contract_comment_destroy_route_unavailable'
                                                    ) ?? 'Comment delete route for Contracts is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                {!! Collective\Html\FormFacade::open([
                                                    'url'            => $commentDestroyRoute,
                                                    'method'         => 'DELETE',
                                                    'id'             => $commentDestroyFormId,
                                                    'data-url'       => $commentDestroyRoute,
                                                    'data-guard-msg' => $commentDestroyGuardMsg,
                                                ]) !!}
                                                    <a
                                                        id="{{ $commentDestroyBtnId }}"
                                                        href="#"
                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                    >
                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                    </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const btn = document.getElementById('{{ $commentDestroyBtnId }}');
                                                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                            btn.setAttribute('data-listener-active', 'true');
                                                            btn.addEventListener('click', event => {
                                                                try {
                                                                    const form = document.getElementById('{{ $commentDestroyFormId }}');
                                                                    const url  = form.getAttribute('data-url');
                                                                    if (url && url !== '#') return;
                                                                    event.preventDefault();
                                                                    const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container    = document.createElement('div');
                                                                        container.id = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl      = document.createElement('div');
                                                                        toastEl.className  = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body         = document.createElement('div');
                                                                        body.className     = 'toast-body';
                                                                        body.textContent   = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    btn.setAttribute('data-failed-route', 'true');
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div id="useradd-4">
                <div class="{{ VC::CD }}">
                    <div class="card-header d-flex justify-content-between">
                        <h5>{{ __('Notes') }}</h5>
                        @php
                            $owner = User::find($user?->creatorId());
                            $plan  = \App\Models\Plan::getPlan($owner?->plan);
                        @endphp
                        @if($plan?->{PlansConstants::COL_GPT} == 1)
                            @php
                                $grammarRoute           = Route::has('grammar')
                                    ? route('grammar', ['grammar'])
                                    : '#';
                                $grammarBtnId           = 'grammarCheck';
                                $grammarGuardMsg        = Utility::fetchLinkMessage(
                                    $lang,
                                    'generics',
                                    'grammar_check_route_unavailable'
                                ) ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <a
                                id="{{ $grammarBtnId }}"
                                href="#"
                                data-ajax-popup-over="true"
                                data-size="md"
                                class="{{ VC::BT_SM_PM }} btn-icon"
                                data-url="{{ $grammarRoute }}"
                                data-guard-msg="{{ $grammarGuardMsg }}"
                                data-bs-placement="top"
                                data-title="{{ __('Grammar check with AI') }}"
                                data-bs-toggle="tooltip"
                                title="{{ __('Grammar check with AI') }}"
                            >
                                <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const btn = document.getElementById('{{ $grammarBtnId }}');
                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                        btn.setAttribute('data-listener-active', 'true');
                                        btn.addEventListener('click', event => {
                                            try {
                                                const url = btn.getAttribute('data-url');
                                                if (!url || url === '#') {
                                                    event.preventDefault();
                                                    const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container       = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container       = document.createElement('div');
                                                        container.id    = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl      = document.createElement('div');
                                                        toastEl.className  = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body         = document.createElement('div');
                                                        body.className     = 'toast-body';
                                                        body.textContent   = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    btn.setAttribute('data-failed-route', 'true');
                                                    return;
                                                }
                                            } catch (e) {}
                                        });
                                    })();
                                </script>
                            @endpush
                        @endif
                    </div>
                    <div class="card-body">
                        @if(
                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN) ||
                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CL && $contract->status == 'accept')
                        )
                            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                                <div class="{{ VC::FM_G }} w-100 mb-0">
                                    @php
                                        $noteStoreRoute       = Route::has(ViewsConstants::CTC . '.note.store')
                                            ? route(ViewsConstants::CTC . '.note.store', [$contract->id])
                                            : '#';
                                        $noteFormId           = 'contract-note-form-' . $contract->id;
                                        $noteStoreGuardMsg    = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::CTC,
                                            'contract_note_store_route_unavailable'
                                        ) ?? 'Add note route for Contracts is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    {!! Form::open([
                                        'route'            => [ViewsConstants::CTC . '.note.store', $contract->id],
                                        'method'           => 'post',
                                        'id'               => $noteFormId,
                                        'data-url'         => $noteStoreRoute,
                                        'data-guard-msg'   => $noteStoreGuardMsg,
                                    ]) !!}
                                        <textarea
                                            rows="3"
                                            class="{{ VC::FM_CT }} grammer_textarea"
                                            name="notes"
                                            placeholder="{{ __('Add a Notes...') }}"
                                            required
                                        ></textarea>
                                        <div class="{{ VC::C12 }} text-end mb-0">
                                            {{ Form::submit(__('Add'), ['class' => VC::BT_PRM]) }}
                                        </div>
                                    {!! Form::close() !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const form = document.getElementById('{{ $noteFormId }}');
                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                form.setAttribute('data-listener-active', 'true');
                                                form.addEventListener('submit', event => {
                                                    try {
                                                        const action = form.getAttribute('action');
                                                        const url    = form.getAttribute('data-url');
                                                        if ((action && action !== '#') || (url && url !== '#')) return;
                                                        event.preventDefault();
                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container       = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container       = document.createElement('div');
                                                            container.id    = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl      = document.createElement('div');
                                                            toastEl.className  = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body         = document.createElement('div');
                                                            body.className     = 'toast-body';
                                                            body.textContent   = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        form.setAttribute('data-failed-route', 'true');
                                                    } catch (e) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                </div>
                            </div>
                        @endif
                        <div class="{{ VC::LG_FLSH }} mb-0" id="notes">
                            @foreach($contract->note as $note)
                                @php
                                    $nUser = User::find($note[UsersConstants::COL_USER_ID]);
                                @endphp
                                <div class="{{ VC::LGI }} border-0">
                                    <div class="{{ VC::R_ALC }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="{{ $logo.'/'.($nUser->avatar ?? 'avatar.png') }}" target="_blank">
                                                <img class="rounded-circle" width="40" height="40"
                                                    src="{{ $logo.'/'.($nUser->avatar ?? 'avatar.png') }}">
                                            </a>
                                        </div>
                                        <div class="{{ VC::C_AT }} flex-grow-1 ml-n2">
                                            <p class="h6 text-sm font-weight-light mb-0 text-break">{{ $note->notes }}</p>
                                            <small>{{ $note->created_at->diffForHumans() }}</small>
                                        </div>

                                        @if(
                                            ($user && $user[UsersConstants::COL_TP] == PermissionsConstants::CPN && $contract->status == 'accept') ||
                                            ($user && $user->id == $note[UsersConstants::COL_USER_ID])
                                        )
                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                @php
                                                    $noteDestroyRoute        = Route::has(ViewsConstants::CTC . '.note.destroy')
                                                        ? route(ViewsConstants::CTC . '.note.destroy', $note->id)
                                                        : '#';
                                                    $noteDestroyFormId       = 'contract-note-destroy-form-' . $note->id;
                                                    $noteDestroyBtnId        = 'contract-note-destroy-btn-' . $note->id;
                                                    $noteDestroyGuardMsg     = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::CTC,
                                                        'contract_note_destroy_route_unavailable'
                                                    ) ?? 'Comment delete route for Contracts is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                {!! Collective\Html\FormFacade::open([
                                                    'url'            => $noteDestroyRoute,
                                                    'method'         => 'DELETE',
                                                    'id'             => $noteDestroyFormId,
                                                    'data-url'       => $noteDestroyRoute,
                                                    'data-guard-msg' => $noteDestroyGuardMsg,
                                                ]) !!}
                                                    <a
                                                        id="{{ $noteDestroyBtnId }}"
                                                        href="#"
                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                    >
                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                    </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const btn = document.getElementById('{{ $noteDestroyBtnId }}');
                                                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                            btn.setAttribute('data-listener-active', 'true');
                                                            btn.addEventListener('click', event => {
                                                                try {
                                                                    const href = btn.getAttribute('href');
                                                                    const url  = btn.getAttribute('data-url');
                                                                    if ((href && href !== '#') || (url && url !== '#')) return;
                                                                    event.preventDefault();
                                                                    const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container       = document.createElement('div');
                                                                        container.id    = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl      = document.createElement('div');
                                                                        toastEl.className  = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body         = document.createElement('div');
                                                                        body.className     = 'toast-body';
                                                                        body.textContent   = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    btn.setAttribute('data-failed-route', 'true');
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
