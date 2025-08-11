@php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R,SettingsConstants as LandingPageSettingsConstants};
	$lpSettings ??= [];
	$logo ??= '';
    $lang = Utility::fetchUserLang();
	try {
		$lpSettings = \Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting()?:[];
		$logo = Utility::getFile('uploads/landing_page_image')?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Landing Page') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ __('Landing Page') }}
    </li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
                        <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
                            @include(R::LP.'::'.E::LOS.'.tab')
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="{{ ViewClassNamesConstants::CD }}">
                        <div class="card-header">
                            <div class="row">
                                <div class="{{ ViewClassNamesConstants::CLMS10 }}">
                                    <h5>{{ __('Home Section') }}</h5>
                                </div>
                            </div>
                        </div>
                        @php
                            $uploadRoute = Route::has(R::HM.'.store') ? route(R::HM.'.store') : '#';
                            $message = Utility::fetchLinkMessage(
                                $lang,
                                R::HM,
                                'store_home_unavailable'
                            ) ?? 'Home settings save route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {!! Collective\Html\FormFacade::open([
                            'url'             => $uploadRoute,
                            'method'          => 'post',
                            'enctype'         => 'multipart/form-data',
                            'id'              => 'imageUploadForm',
                            'data-action'     => $uploadRoute,
                            'data-sv-localized' => 'true',
                            'data-guard-msg'  => $message,
                        ]) !!}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Offer Text', __('Offer Text'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_OFF_TXT_K, $lpSettings[LandingPageSettingsConstants::HM_OFF_TXT_K], ['class' => 'form-control', 'placeholder' => __('70% Special Offer')]) }}
                                            @error('mail_driver')
                                                <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Title', __('Title'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_TTL_K, $lpSettings[LandingPageSettingsConstants::HM_TTL_K], ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                                            @error('mail_host')
                                                <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_HDG_K, $lpSettings[LandingPageSettingsConstants::HM_HDG_K], ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
                                            @error('mail_host')
                                                <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Trusted by', __('Trusted by'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_TRST_BY_K, $lpSettings[LandingPageSettingsConstants::HM_TRST_BY_K], ['class' => 'form-control', 'placeholder' => __('1,000+ customers')]) }}
                                            @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_DESC_K, $lpSettings[LandingPageSettingsConstants::HM_DESC_K], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                            @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Live Demo Link', __('Live Demo Link'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_DEMO_LNK_K, $lpSettings[LandingPageSettingsConstants::HM_DEMO_LNK_K], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                            @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LandingPageSettingsConstants::HM_BUY_LNK_K, $lpSettings[LandingPageSettingsConstants::HM_BUY_LNK_K], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                            @error('mail_port')
                                                <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Banner', __('Banner'), ['class' => 'form-label',
                                            'id' => 'banner_image']) }}
                                            <div class="logo-content mt-4">
                                                <img id="banner_image" src="{{ asset('uploads/landing_page_image/'.$lpSettings[LandingPageSettingsConstants::HM_BNR_K]) }}"
                                                    class="big-logo">
                                            </div>
                                            <div class="choose-files mt-5">
                                                <label for="home_banner">
                                                    <div class="{{ ViewClassNamesConstants::BG_P }} company_logo_update" style="cursor: pointer;">
                                                        <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                    </div>
                                                    <input type="file" name="home_banner" id="home_banner"
                                                        class="form-control file" data-filename="home_banner">
                                                </label>
                                            </div>
                                            @error(LandingPageSettingsConstants::HM_BNR_K)
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </span>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Collective\Html\FormFacade::label('Logo', __('Logo'), ['class' => 'form-label']) }}
                                                    <div class="logo-content mt-4">
                                                        <img id="image1" src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}"
                                                            class="big-logo img_setting">
                                                    </div>
                                                    <div class="choose-files mt-5">
                                                        <label for="home_logo">
                                                            <div class=" {{ ViewClassNamesConstants::BG_P }} dark_logo_update" style="cursor: pointer;"> <i class="ti ti-upload px-1">
                                                                </i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" name="home_logo" id="home_logo" class="form-control file" data-filename="home_logo">
                                                        </label>
                                                    </div>
                                                    @error(LandingPageSettingsConstants::HM_LGO_K)
                                                    <div class="row">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div> --}}
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Logo', __('Logo'), ['class' => 'form-label']) }}
                                            <div class="text-end">
                                                <button class="{{ ViewClassNamesConstants::BT_SM_PM }} btn-icon m-1" data-repeater-create
                                                    type="button"><i class="{{ ViewClassNamesConstants::TI_PLS }}"></i></button>
                                            </div>
                                            <div data-repeater-list="home_logo">
                                                <div data-repeater-item class="text-end">
                                                    <div class="{{ ViewClassNamesConstants::CD_NSD }}product_Image">
                                                        <div class="px-2 py-2">
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <input type="file" class="form-control" name="home_logo"
                                                                        accept="image/*" onchange="updateImagePreview(this)">
                                                                </div>
                                                                <div class="col-auto">
                                                                    <p class="card-text small text-muted">
                                                                        {{-- <img class="rounded" src="{{ $logo.'/placeholder.png' }}" width="70px" alt="Image placeholder" data-dz-thumbnail=""> --}}
                                                                        <img src="{{ asset('uploads/landing_page_image/home_logo.png') }}" width="70px"
                                                                            alt="Image placeholder" data-dz-thumbnail="">
                                                                    </p>
                                                                </div>
                                                                <div class="col-auto actions">
                                                                    <a data-repeater-delete href="javascript:void(0)"
                                                                        class="action-item {{ ViewClassNamesConstants::BT_SM }} btn-icon btn-light-secondary repeater-action-btn ms-2">
                                                                        <i class="{{ ViewClassNamesConstants::BT_SM_PM }}"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>

                                        @if ($lpSettings[LandingPageSettingsConstants::HM_LGO_K] != '')
                                            <div id="imageContainer">
                                                @foreach (explode(',', $lpSettings[LandingPageSettingsConstants::HM_LGO_K]) as $k => $home_logo)
                                                    <div class="{{ ViewClassNamesConstants::CD_NSD }}product_Image">
                                                        <div class="px-2 py-2">
                                                            <div class="row align-items-center">
                                                                <div class="col ml-n2">
                                                                    <p class="card-text small text-muted">
                                                                        <img src="{{ asset('uploads/landing_page_image/home_logo.png') }}"
                                                                            width="70px" alt="Image placeholder"
                                                                            data-dz-thumbnail="">
                                                                    </p>
                                                                </div>
                                                                <div class="col-auto actions">
                                                                    <a class="action-item {{ ViewClassNamesConstants::BT_SM }} btn-icon btn-light-secondary"
                                                                        href="{{ $logo . '/' . $home_logo }}" download=""
                                                                        data-toggle="tooltip" data-original-title="Download">
                                                                        <i class="{{ ViewClassNamesConstants::TI_DWN }}"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="col-auto actions">
                                                                    <a class="action-item {{ ViewClassNamesConstants::BT_SM }} btn-icon btn-light-secondary delete-button"
                                                                        data-image="{{ $home_logo }}">
                                                                        <i class="{{ ViewClassNamesConstants::BT_SM_PM }}"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        <input type="hidden" class="form-control" id="imageNames" name="savedlogo"
                                            value="{{ $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}" />
                            </div>
                        {!! Collective\Html\FormFacade::close() !!}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const dataSVLocalized = 'data-sv-localized';
                                    const dataClientLocalized = 'data-client-localized';
                                    const dataGuardMsg = 'data-guard-msg';
                                    const listenerAttr = 'data-submit-listener-active';
                                    const el = document.getElementById('imageUploadForm');
                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                    el.setAttribute(listenerAttr, 'true');
                                    el.addEventListener('submit', event => {
                                        try {
                                            const url = el.getAttribute('data-action');
                                            const href = el.action;
                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                event.preventDefault();
                                                const message = el.getAttribute(dataGuardMsg) ?? '# ERROR';
                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                const containerId = 'toast-container';
                                                let container = document.getElementById(containerId);
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = containerId;
                                                    document.body.appendChild(container);
                                                }
                                                if (bootstrapLink && window.bootstrap) {
                                                    const toastEl = document.createElement('div');
                                                    toastEl.className = 'toast';
                                                    toastEl.setAttribute('role','alert');
                                                    toastEl.setAttribute('aria-live','assertive');
                                                    toastEl.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = message;
                                                    toastEl.appendChild(body);
                                                    container.appendChild(toastEl);
                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                } else {
                                                    alert(message);
                                                }
                                                el.setAttribute('data-failed-route', 'true');
                                            }
                                        } catch (error) {}
                                    });
                                    const observer = new MutationObserver(() => {
                                        if (!document.body.contains(el)) {
                                            observer.disconnect();
                                            el.removeEventListener('submit', () => {});
                                        }
                                    });
                                    observer.observe(document.body, { childList: true, subtree: true });
                                })();
                            </script>
                        @endpush
                    </div>
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar: {
            are_you_sure: "هل أنت متأكد أنك تريد حذف هذا العنصر؟",
            image_preview_unavailable: "معاينة الصورة غير متاحة. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            image_delete_unavailable: "حذف الصورة غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            home_banner_preview_unavailable: "معاينة بانر الصفحة الرئيسية غير متاحة. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            home_logo_preview_unavailable: "معاينة شعار الصفحة الرئيسية غير متاحة. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
            store_image_upload_unavailable: "مسار تحميل الصورة غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال."
        },
        da: {
            are_you_sure: "Er du sikker på, at du vil slette dette element?",
            image_preview_unavailable: "Billedeksempel er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
            image_delete_unavailable: "Billedesletning er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
            home_banner_preview_unavailable: "Hjemmesidebanner eksempel er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
            home_logo_preview_unavailable: "Hjemmesidelogo eksempel er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.",
            store_image_upload_unavailable: "Billedupload-rute er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator."
        },
        de: {
            are_you_sure: "Sind Sie sicher, dass Sie dieses Element löschen möchten?",
            image_preview_unavailable: "Bildvorschau nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.",
            image_delete_unavailable: "Bildlöschung nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.",
            home_banner_preview_unavailable: "Startseiten-Banner-Vorschau nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.",
            home_logo_preview_unavailable: "Startseiten-Logo-Vorschau nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator.",
            store_image_upload_unavailable: "Bild-Upload-Route nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domain-Administrator."
        },
        en: {
            are_you_sure: "Are you sure you want to delete this element?",
            image_preview_unavailable: "Image preview unavailable. Please contact technical support or your domain administrator.",
            image_delete_unavailable: "Image deletion unavailable. Please contact technical support or your domain administrator.",
            home_banner_preview_unavailable: "Home banner preview unavailable. Please contact technical support or your domain administrator.",
            home_logo_preview_unavailable: "Home logo preview unavailable. Please contact technical support or your domain administrator.",
            store_image_upload_unavailable: "Image upload route is unavailable. Please contact technical support or your domain administrator."
        },
        es: {
            are_you_sure: "¿Estás seguro de que quieres eliminar este elemento?",
            image_preview_unavailable: "Vista previa de imagen no disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.",
            image_delete_unavailable: "Eliminación de imagen no disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.",
            home_banner_preview_unavailable: "Vista previa del banner de inicio no disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.",
            home_logo_preview_unavailable: "Vista previa del logo de inicio no disponible. Por favor, contacte al soporte técnico o a su administrador de dominio.",
            store_image_upload_unavailable: "La ruta de carga de imágenes no está disponible. Por favor, contacte al soporte técnico o a su administrador de dominio."
        },
        fr: {
            are_you_sure: "Êtes-vous sûr de vouloir supprimer cet élément ?",
            image_preview_unavailable: "Aperçu de l'image non disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            image_delete_unavailable: "Suppression d'image non disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            home_banner_preview_unavailable: "Aperçu de la bannière d'accueil non disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            home_logo_preview_unavailable: "Aperçu du logo d'accueil non disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
            store_image_upload_unavailable: "La route de téléchargement d'image n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine."
        },
        he: {
            are_you_sure: "האם אתה בטוח שברצונך למחוק פריט זה?",
            image_preview_unavailable: "תצוגה מקדימה של התמונה אינה זמינה. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.",
            image_delete_unavailable: "מחיקת תמונה אינה זמינה. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.",
            home_banner_preview_unavailable: "תצוגה מקדימה של הבאנר הראשי אינה זמינה. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.",
            home_logo_preview_unavailable: "תצוגה מקדימה של הלוגו הראשי אינה זמינה. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך.",
            store_image_upload_unavailable: "נתיב העלאת התמונה אינו זמין. אנא צור קשר עם התמיכה הטכנית או עם מנהל הדומיין שלך."
        },
        it: {
            are_you_sure: "Sei sicuro di voler eliminare questo elemento?",
            image_preview_unavailable: "Anteprima immagine non disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio.",
            image_delete_unavailable: "Eliminazione immagine non disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio.",
            home_banner_preview_unavailable: "Anteprima banner home non disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio.",
            home_logo_preview_unavailable: "Anteprima logo home non disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio.",
            store_image_upload_unavailable: "La rotta di caricamento dell'immagine non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore del dominio."
        },
        ja: {
            are_you_sure: "この要素を削除してもよろしいですか？",
            image_preview_unavailable: "画像プレビューが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。",
            image_delete_unavailable: "画像削除が利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。",
            home_banner_preview_unavailable: "ホームバナープレビューが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。",
            home_logo_preview_unavailable: "ホームロゴプレビューが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。",
            store_image_upload_unavailable: "画像アップロードルートが利用できません。テクニカルサポートまたはドメイン管理者に連絡してください。"
        },
        nl: {
            are_you_sure: "Weet u zeker dat u dit element wilt verwijderen?",
            image_preview_unavailable: "Afbeeldingvoorbeeld niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
            image_delete_unavailable: "Afbeeldingverwijdering niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
            home_banner_preview_unavailable: "Homebanner-voorbeeld niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
            home_logo_preview_unavailable: "Homelogo-voorbeeld niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.",
            store_image_upload_unavailable: "Afbeeldinguploadroute niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder."
        },
        pl: {
            are_you_sure: "Czy na pewno chcesz usunąć ten element?",
            image_preview_unavailable: "Podgląd obrazu niedostępny. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            image_delete_unavailable: "Usuwanie obrazu niedostępne. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            home_banner_preview_unavailable: "Podgląd banera głównego niedostępny. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            home_logo_preview_unavailable: "Podgląd logo głównego niedostępny. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
            store_image_upload_unavailable: "Trasa przesyłania obrazu niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny."
        },
        pt: {
            are_you_sure: "Tem certeza de que deseja excluir este elemento?",
            image_preview_unavailable: "Pré-visualização da imagem indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            image_delete_unavailable: "Exclusão de imagem indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            home_banner_preview_unavailable: "Pré-visualização do banner inicial indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            home_logo_preview_unavailable: "Pré-visualização do logotipo inicial indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            store_image_upload_unavailable: "A rota de upload de imagem não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio."
        },
        'pt-br': {
            are_you_sure: "Tem certeza de que deseja excluir este elemento?",
            image_preview_unavailable: "Pré-visualização da imagem indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            image_delete_unavailable: "Exclusão de imagem indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            home_banner_preview_unavailable: "Pré-visualização do banner inicial indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            home_logo_preview_unavailable: "Pré-visualização do logotipo inicial indisponível. Entre em contato com o suporte técnico ou o administrador do domínio.",
            store_image_upload_unavailable: "A rota de upload de imagem não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio."
        },
        ru: {
            are_you_sure: "Вы уверены, что хотите удалить этот элемент?",
            image_preview_unavailable: "Предпросмотр изображения недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
            image_delete_unavailable: "Удаление изображения недоступно. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
            home_banner_preview_unavailable: "Предпросмотр баннера главной страницы недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
            home_logo_preview_unavailable: "Предпросмотр логотипа главной страницы недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.",
            store_image_upload_unavailable: "Маршрут загрузки изображения недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена."
        },
        tr: {
            are_you_sure: "Bu öğeyi silmek istediğinizden emin misiniz?",
            image_preview_unavailable: "Görüntü önizleme kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            image_delete_unavailable: "Görüntü silme kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            home_banner_preview_unavailable: "Ana sayfa banner önizleme kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            home_logo_preview_unavailable: "Ana sayfa logo önizleme kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.",
            store_image_upload_unavailable: "Görüntü yükleme rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin."
        },
        zh: {
            are_you_sure: "您确定要删除此元素吗？",
            image_preview_unavailable: "图片预览不可用。请联系技术支持或您的域管理员。",
            image_delete_unavailable: "图片删除不可用。请联系技术支持或您的域管理员。",
            home_banner_preview_unavailable: "主页横幅预览不可用。请联系技术支持或您的域管理员。",
            home_logo_preview_unavailable: "主页徽标预览不可用。请联系技术支持或您的域管理员。",
            store_image_upload_unavailable: "图片上传路由不可用。请联系技术支持或您的域管理员。"
        }
        };
    </script>
    <script async src="{{ asset('assets/js/jquery.repeater.min.js') }}"></script>
    <script async>
        (() => {
          const errFb = "# ERROR";
          const dataClientLocalized = "data-client-localized";
          const dataGuardMsg = "data-guard-msg";
        
          const getLocalizedMessage = (el, msgKey) => {
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            ) {
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
              )
                .toLowerCase()
                .replace(/_/g, "-");
              lang = lang === "pt-br" ? lang : lang.slice(0, 2);
              msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
            return msg;
          };
        
          const showErrorUI = (msg) => {
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            if (bsLink && window.bootstrap?.Toast) {
              if (!document.querySelector("#fail-toast")) {
                const toast = document.createElement("div");
                toast.id = "fail-toast";
                toast.className =
                  "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `
                  <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>`;
                document.body.appendChild(toast);
                new bootstrap.Toast(toast).show();
              }
            } else {
              alert(msg);
            }
          };
        
          document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("#imageUploadForm");
            if (!form) return;
            try {
              $(form).repeater({
                show() {
                  $(this).slideDown();
                },
                hide(deleteElement) {
                  if (confirm("Are you sure you want to delete this element?")) {
                    $(this).slideUp(deleteElement);
                  }
                },
              });
            } catch {
              const msg = getLocalizedMessage(form, "image_upload_unavailable");
              showErrorUI(msg);
            }
          });
        
          window.updateImagePreview = (input) => {
            try {
              const img = input.parentElement
                .parentElement.querySelector("img");
              if (!img) return;
              img.src = input.files?.[0]
                ? URL.createObjectURL(input.files[0])
                : "{{ $logo . '/placeholder.png' }}";
            } catch {
              const msg = getLocalizedMessage(
                input,
                "image_preview_unavailable"
              );
              showErrorUI(msg);
            }
          };
        
          document.addEventListener("DOMContentLoaded", () => {
            const key = "deleteRepeaterBound";
            if (document.body.dataset[key]) return;
            document.body.addEventListener("click", (e) => {
              const tgt = e.target;
              if (tgt?.classList.contains("delete-repeater-item")) {
                e.preventDefault();
                const item = tgt.closest("[data-repeater-item]");
                item?.remove();
              }
            });
            document.body.dataset[key] = "true";
          });
        })();
    </script>
    <script async>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        
        const getLocalizedMessage = (el, msgKey) => {
            let msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        
        const showErrorUI = (msg) => {
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            if (bsLink && window.bootstrap?.Toast) {
            if (!document.querySelector("#fail-toast")) {
                const toast = document.createElement("div");
                toast.id = "fail-toast";
                toast.className =
                "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
                document.body.appendChild(toast);
                new bootstrap.Toast(toast).show();
            }
            } else {
            alert(msg);
            }
        };
        
        document.addEventListener("DOMContentLoaded", () => {
            const input = document.getElementById("imageNames");
            if (!input) return;
        
            const bindBtn = (btn) => {
            const flag = "deleteBtnBound";
            if (btn.dataset[flag]) return;
            btn.addEventListener("click", () => {
                try {
                const card = btn.closest(".card");
                const name = btn.getAttribute("data-image");
                card?.remove();
                const arr = input.value.split(",").filter(Boolean);
                input.value = arr.filter((n) => n !== name).join(",");
                } catch {
                const msg = getLocalizedMessage(
                    btn,
                    "image_delete_unavailable"
                );
                showErrorUI(msg);
                }
            });
            btn.dataset[flag] = "true";
            };
        
            document.querySelectorAll(".delete-button").forEach(bindBtn);
        
            new MutationObserver((mutations) => {
            mutations.forEach((m) => {
                m.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) return;
                if (node.matches(".delete-button")) bindBtn(node);
                node.querySelectorAll(".delete-button").forEach(bindBtn);
                });
            });
            }).observe(document.body, { childList: true, subtree: true });
        });
        })();
    </script>
    <script defer>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        
        const getLocalizedMessage = (el, msgKey) => {
            let msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        
        const showErrorUI = (msg) => {
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            if (bsLink && window.bootstrap?.Toast) {
            if (!document.querySelector("#fail-toast")) {
                const toast = document.createElement("div");
                toast.id = "fail-toast";
                toast.className =
                "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
                document.body.appendChild(toast);
                new bootstrap.Toast(toast).show();
            }
            } else {
            alert(msg);
            }
        };
        
        const bindPreview = (inputId, imgId, msgKey) => {
            const inp = document.getElementById(inputId);
            const img = document.getElementById(imgId);
            if (!inp || !img) return;
            const flag = "previewBound";
            if (inp.dataset[flag]) return;
            inp.addEventListener("change", () => {
            try {
                const file = inp.files?.[0];
                img.src = file
                ? URL.createObjectURL(file)
                : "{{ $logo . '/placeholder.png' }}";
            } catch {
                const msg = getLocalizedMessage(inp, msgKey);
                showErrorUI(msg);
            }
            });
            inp.dataset[flag] = "true";
        };
        
        document.addEventListener("DOMContentLoaded", () => {
            bindPreview(
            "home_banner",
            "image",
            "home_banner_preview_unavailable"
            );
            bindPreview(
            "home_logo",
            "image1",
            "home_logo_preview_unavailable"
            );
        });
        })();
    </script>
    <script defer>
        (() => {
            const errFb = "# ERROR";
            const dataSVLocalized = "data-sv-localized";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const listenerAttr = "data-submit-listener-active";
            const msgKey = "store_image_upload_unavailable";
            const getMessage = (el, key) => {
                let msg = errFb;
                if (
                    el.getAttribute(dataSVLocalized) === "true" ||
                    el.getAttribute(dataClientLocalized) === "true"
                ) {
                    msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                    let lang = (
                        window.sessionStorage.getItem("erp-np-lang") ||
                        document.documentElement.lang ||
                        "en"
                    )
                        .toLowerCase()
                        .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    msg =
                        window.translations?.[lang]?.[key] ||
                        el.getAttribute(dataGuardMsg) ||
                        window.translations?.["en"]?.[key] ||
                        errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, "true");
                    }
                }
                return msg;
            };
            const $form = $("#imageUploadForm");
            if (!$form.length) return;
            const form = $form.get(0);
            if (form.getAttribute(listenerAttr) === "true") return;
            form.setAttribute(listenerAttr, "true");
            const onSubmit = event => {
                try {
                    const url = form.getAttribute("data-url");
                    const href = form.action;
                    if ((!url || url === "#") && (!href || href === "#")) {
                        event.preventDefault();
                        const message = getMessage(form, msgKey);
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        const containerId = "toast-container";
                        let container = document.getElementById(containerId);
                        if (!container) {
                            container = document.createElement("div");
                            container.id = containerId;
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement("div");
                            toastEl.className = "toast";
                            toastEl.setAttribute("role","alert");
                            toastEl.setAttribute("aria-live","assertive");
                            toastEl.setAttribute("aria-atomic","true");
                            const body = document.createElement("div");
                            body.className = "toast-body";
                            body.textContent = message;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(message);
                        }
                    }
                } catch (error) {}
            };
            $form.on("submit", onSubmit);
            const observer = new MutationObserver(() => {
                if (!document.body.contains(form)) {
                    observer.disconnect();
                    $form.off("submit", onSubmit);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        })();
    </script>
@endpush