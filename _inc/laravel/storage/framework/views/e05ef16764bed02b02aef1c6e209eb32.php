<?php
	use App\Config\Constants\{DatabaseConstants,SettingsConstants,ViewClassNamesConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{RoutesResourcesConstants,SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
	use Symfony\Component\Console\Output\ConsoleOutput;

	if (!$page) {
        Log::error('Page variable is not set, defaulting to about_us');
        $page = 'about_us';
    }
	$user??=null;
	$creatorId??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$logo??='';
	$sup_logo??='';
	$adminSettings??=[];
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$siteRtl??=false;
	$color??='';
	$faviconUrl??='';
	$lpSettings??=[];
    $lang = DatabaseConstants::DEFAULT_LANG;
	try {
		$user=auth()->user();
        $lang = Utility::fetchUserLang(user: $user);
		$creatorId=$user?->id?:DatabaseConstants::DEFAULT_UUID;
		$data=Utility::prepareCommonViewData($creatorId,'uploads/landing_page_image')?:[];
		$setting=$data[SettingsConstants::ENTITY]??[];
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$sup_logo=$data[SettingsConstants::SC_LOGO]??'';
		$adminSettings=$data[SettingsConstants::CPN_CFG]??[];
		$meta_title=$data[SettingsConstants::MT_TTL_K]??'';
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??'';
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$color=$data[SettingsConstants::THM_CLR]??'';
		$faviconUrl=Utility::getCompanyLogo()?:'';
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
?>
<!DOCTYPE html>
    <html lang="<?php echo e($lang ? str_replace('_', '-', app()->getLocale() ?? DatabaseConstants::DEFAULT_LANG) : ''); ?>" dir="<?php echo e($siteRtl == 'on' ? 'rtl' : 'ltr'); ?>">
        <head>
            <title><?php echo e(env('APP_NAME')); ?></title>
            <?php echo $__env->make('fragments.std', [
                'meta_title' => $meta_title,
                'meta_desc' => $meta_desc
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('fragments.og', [
                'meta_title' => $meta_title, 
                'meta_desc' => $meta_desc, 
                'meta_image' => $meta_image,
                'meta_logo' => $meta_logo
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('fragments.x', [
                'meta_title' => $meta_title, 
                'meta_desc' => $meta_desc, 
                'meta_image' => $meta_image,
                'meta_logo' => $meta_logo
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('fragments.favicon', ['faviconUrl' => $faviconUrl], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <link rel="stylesheet" href="<?php echo e(Module::asset('LandingPage:fonts/tabler-icons.min.css')); ?>" />
            <link rel="stylesheet" href="<?php echo e(Module::asset('LandingPage:fonts/feather.css')); ?>" />
            <link rel="stylesheet" href="<?php echo e(Module::asset('LandingPage:fonts/fontawesome.css')); ?>" />
            <link rel="stylesheet" href="<?php echo e(Module::asset('LandingPage:fonts/material.css')); ?>" />
            <?php if($siteRtl == 'on'): ?>
                <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-rtl.css')); ?>">
            <?php endif; ?>
            <?php if($setting['cust_darklayout'] == 'on'): ?>
                <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-dark.css')); ?>">
            <?php else: ?>
                <link rel="stylesheet" href="<?php echo e(Module::asset('LandingPage:css/style.css')); ?>" id="main-style-link">
            <?php endif; ?>
            <link rel="stylesheet" href=" <?php echo e(Module::asset('LandingPage:css/customizer.css')); ?>" />
            <link rel="stylesheet" href=" <?php echo e(Module::asset('LandingPage:css/landing-page.css')); ?>" />
            <link rel="stylesheet" href=" <?php echo e(Module::asset('LandingPage:css/custom.css')); ?>" />
            <link rel="stylesheet" href=" <?php echo e(Module::asset('LandingPage:css/landing-page.css')); ?>" />
            <?php echo $__env->make('fragments.stylesheets', ['settings' => $colorSettings], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </head>
        <?php if(!empty($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] === 'on'): ?>
            <body class="<?php echo e($color); ?> landing-dark">
        <?php else: ?>
            <body class="<?php echo e($color); ?>">
        <?php endif; ?>
            <!-- [ Header ] start -->
            <header class="main-header">
                <?php if(!empty($lpSettings[LandingPageSettingsConstants::TB_STT_K])
                && !empty($lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K])): ?>
                    <?php if($lpSettings[LandingPageSettingsConstants::TB_STT_K] === 'on'): ?>
                        <div class="announcement bg-dark text-center p-2">
                            <p class="mb-0">
                                <?php if(! empty($lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K])): ?>
                                    <?php echo $lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K]; ?>

                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if(!empty($lpSettings[LandingPageSettingsConstants::MB_STT_K]) && $lpSettings[LandingPageSettingsConstants::MB_STT_K] === 'on'): ?>
                    <div class="<?php echo e(ViewClassNamesConstants::CT); ?>">
                        <nav class="<?php echo e(ViewClassNamesConstants::NVB_DEF_TOP); ?>">
                            <div class="header-left">
                                <a class="<?php echo e(ViewClassNamesConstants::NVB_BR_TPR); ?>" href="#">
                                    <img src="<?php echo e($lpSettings[LandingPageSettingsConstants::SL_K] ? asset('assets/images/'.$lpSettings[LandingPageSettingsConstants::SL_K]) : asset('assets/images/logo-light.webp')); ?>" 
                                         alt="logo" 
                                         id="headerLogo"
                                         data-fallback-index="0"
                                         style="border-radius: 0.5rem 0.5rem 1rem 1rem; clip-path: inset(-8px 0px 0px 0px); width: 12rem;
                                         transform: scale(1.1) translateY(1%);"
                                         onload="this.style.opacity = '1'"
                                         onerror="
                                            const fallbacks = [
                                                '<?php echo e(asset('assets/images/logo-light.webp')); ?>',
                                                '<?php echo e(asset('assets/logo-light.webp')); ?>',
                                                '<?php echo e(asset('logo-light.webp')); ?>'
                                            ];
                                            let currentIndex = parseInt(this.getAttribute('data-fallback-index')) || 0;
                                            if (currentIndex < fallbacks.length - 1) {
                                                if (currentIndex === 0) {
                                                    this.style.opacity = '0';
                                                    this.style.transition = 'opacity 0.5s ease-in-out';
                                                }
                                                currentIndex++;
                                                this.setAttribute('data-fallback-index', currentIndex);
                                                this.src = fallbacks[currentIndex];
                                            } else {
                                                this.onerror = null;
                                                this.style.opacity = '1';
                                            }
                                         ">
                                </a>
                            </div>
                            <div class="<?php echo e(ViewClassNamesConstants::NVB_CLP); ?>" id="navbarTogglerDemo01">
                                <?php
                                    $menuItems??=[];
                                    try {
                                        $menuItems=json_decode(
                                            $lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]',true
                                        )?:[];
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                            ]
                                        );
                                    }
                                ?>
                                <ul class="<?php echo e(ViewClassNamesConstants::NVB_NAV); ?>">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="<?php echo e(url('/#home')); ?>">
                                            <?php echo e($lpSettings[LandingPageSettingsConstants::HM_TTL_K] ?? ''); ?>

                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?php echo e(url('/#features')); ?>">
                                            <?php echo e($lpSettings[LandingPageSettingsConstants::FT_TTL_K] ?? ''); ?>

                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?php echo e(url('/#plan')); ?>">
                                            <?php echo e($lpSettings[LandingPageSettingsConstants::PN_TTL_K] ?? ''); ?>

                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?php echo e(url('/#faq')); ?>">
                                            <?php echo e($lpSettings[LandingPageSettingsConstants::FAQ_TTL_K] ?? ''); ?>

                                        </a>
                                    </li>
                                    <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $header??='';
                                        $template??='';
                                        $slug??='';
                                        $name??='';
                                        $cstNm??='';
                                        $cstRt??='';
                                        $url??='';
                                        try {
                                            $header=$item['header']??'';
                                            $template=$item['template_name']??'';
                                            $slug=$item[LandingPageSettingsConstants::PG_SLG]??'';
                                            $name=$item[LandingPageSettingsConstants::MB_PG_NM]??'';
                                            if($header==='on'&&$template==='page_content'){
                                                $cstNm='custom.page';
                                                $cstRt=Route::has($cstNm)?$cstNm:'#';
                                                $url=route('custom.page',$slug);
                                            }elseif($header==='on'&&$template==='page_url'){
                                                $url=$item['page_url']??'#';
                                            }else{
                                                $url='#';
                                            }
                                        } catch(Error $e) {
                                            Log::error(
                                                'Error processing menubar item data',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine()
                                                ]
                                            );
                                        } catch(Exception $e) {
                                            Log::error(
                                                'Exception processing menubar item data',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine()
                                                ]
                                            );
                                        } catch(Throwable $e) {
                                            Log::error(
                                                'Throwable processing menubar item data',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine()
                                                ]
                                            );
                                        }
                                    ?>
                                        <?php if($header === 'on' && $name): ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="<?php echo e($url); ?>">
                                                    <?php echo $name; ?>

                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <button class="<?php echo e(ViewClassNamesConstants::NVB_TG_P); ?>" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                    <span class="<?php echo e(ViewClassNamesConstants::NVB_TG_IC); ?>"></span>
                                </button>
                            </div>
                            <div class="ms-auto d-flex justify-content-end gap-2">
                                <a href="<?php echo e(Route::has('login') ? route('login') : '#'); ?>" class="btn btn-outline-dark rounded">
                                    <span class="hide-mob me-2"><?php echo e(__('Login')); ?></span>
                                    <i data-feather="log-in"></i>
                                </a>
                                <a href="<?php echo e(Route::has('register') ? route('register') : '#'); ?>" class="btn btn-outline-dark rounded">
                                    <span class="hide-mob me-2"><?php echo e(__('Register')); ?></span>
                                    <i data-feather="user-check"></i>
                                </a>
                                <button class="<?php echo e(ViewClassNamesConstants::NVB_TG); ?>" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                    <span class="<?php echo e(ViewClassNamesConstants::NVB_TG_IC); ?>"></span>
                                </button>
                            </div>
                        </nav>
                    </div>
                <?php endif; ?>
            </header>
            <!-- [ Header ] End -->
            <!-- [ common banner ] start -->
            <section class="common-banner bg-primary">
                <div class="<?php echo e(ViewClassNamesConstants::CT); ?>">
                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <div class="title">
                                <h1 class="text-white">
                                    <?php if(! empty($page[LandingPageSettingsConstants::MB_PG_NM])): ?>
                                        <?php echo $page[LandingPageSettingsConstants::MB_PG_NM]; ?>

                                    <?php endif; ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- [ common banner ] end -->
            <!-- [ Static content ] start -->
                <section class="static-content section-gap">
                    <div class="<?php echo e(ViewClassNamesConstants::CT); ?>">
                        <div class="mb-5">
                            <?php if(!empty($page[LandingPageSettingsConstants::MB_PG_CT])): ?>
                                <?php echo $page[LandingPageSettingsConstants::MB_PG_CT]; ?>

                            <?php endif; ?>
                        </div>
                        <?php if(!empty($lpSettings[LandingPageSettingsConstants::TM_STT_K]) && $lpSettings[LandingPageSettingsConstants::TM_STT_K] === 'on'): ?>
                            <?php if(is_array(json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K], true)) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K], true))): ?>
                                <?php
                                    $decodedTestimonials??=[];
                                    try {
                                        $decodedTestimonials=json_decode(
                                            $lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]',
                                            true
                                        )?:[];
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error decoding testimonials JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception decoding testimonials JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable decoding testimonials JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                            ]
                                        );
                                    }
                                ?>
                                <?php if($decodedTestimonials && count($decodedTestimonials) > 0): ?>
                                    <?php
                                        $decodedTestimonials??=[];
                                        $testimonial??=[];
                                        try {
                                            $decodedTestimonials=json_decode(
                                                $lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]',
                                                true
                                            )?:[];
                                            $testimonial=!empty($decodedTestimonials)
                                                ?$decodedTestimonials[array_rand($decodedTestimonials,1)]
                                                :[];
                                        } catch (\Error $e) {
                                            Log::error(
                                                'Error selecting testimonial',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                                ]
                                            );
                                        } catch (\Exception $e) {
                                            Log::error(
                                                'Exception selecting testimonial',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                                ]
                                            );
                                        } catch (\Throwable $e) {
                                            Log::error(
                                                'Throwable selecting testimonial',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                                ]
                                            );
                                        }
                                    ?>
                                    <div>
                                        <div class="row gy-4">
                                            <div class="col-12">
                                                <div class="bg-primary p-4 rounded">
                                                    <div class="row gy-3 align-items-center">
                                                        <div class="col-xxl-6 col-lg-6">
                                                            <div class="d-flex flex-column flex-sm-row gap-3">
                                                                <span class="theme-avatar avatar avatar-xl bg-light-dark rounded-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="23" viewBox="0 0 36 23" fill="none">
                                                                        <path d="M12.4728 22.6171H0.770508L10.6797 0.15625H18.2296L12.4728 22.6171ZM29.46 22.6171H17.7577L27.6669 0.15625H35.2168L29.46 22.6171Z" fill="black"></path>
                                                                        </svg>
                                                                </span>
                                                                <div>
                                                                    <h2>
                                                                        <?php if(! empty($testimonial[LandingPageSettingsConstants::TM_TTL_K])): ?>
                                                                            <?php echo $testimonial[LandingPageSettingsConstants::TM_TTL_K]; ?>

                                                                        <?php endif; ?>
                                                                    </h2>
                                                                    <p class="mb-0">
                                                                        <?php if(! empty($testimonial[LandingPageSettingsConstants::TM_DESC_K])): ?>
                                                                            <?php echo $testimonial[LandingPageSettingsConstants::TM_DESC_K]; ?>

                                                                        <?php endif; ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-6 col-lg-6">
                                                        <div class="d-flex align-items-center gap-3 justify-content-center justify-content-sm-end">
                                                            <div class="text-end">
                                                                <b class="d-block"><?php echo e($testimonial[LandingPageSettingsConstants::TM_USR] ?? 'Anonymous'); ?> </b>
                                                                <span class="d-block">
                                                                    <?php if(! empty($testimonial[LandingPageSettingsConstants::TM_USR_DSG])): ?>
                                                                        <?php echo $testimonial[LandingPageSettingsConstants::TM_USR_DSG]; ?>

                                                                    <?php else: ?>
                                                                        Customer
                                                                    <?php endif; ?>
                                                                </span>
                                                                <span>
                                                                    <?php for($i = 1; $i <= (int) $testimonial[LandingPageSettingsConstants::TM_STR] ?? 5; $i++): ?>
                                                                        <i data-feather="star"></i>
                                                                    <?php endfor; ?>
                                                                </span>
                                                            </div>
                                                            <?php
                                                                try {
                                                                    Log::debug('Fetching avatar URL for testimonial: '.json_encode(array_keys($testimonial)));
                                                                    $avatarKey = LandingPageSettingsConstants::TM_USR_AV;
                                                                    $avatar    = $testimonial[$avatarKey] ?? null;
                                                                    $avatarUrl = $avatar
                                                                        ? asset('assets/images/'.$testimonial[$avatarKey])
                                                                        : asset('uploads/avatar/avatar.png');
                                                                } catch (\Throwable) {
                                                                    Log::debug('Error fetching avatar URL for testimonial: '.$testimonial[LandingPageSettingsConstants::TM_USR_AV] ?? 'No avatar URL found');
                                                                }
                                                            ?>
                                                            <span class="theme-avatar avatar avatar-l rounded-circle">
                                                                <img
                                                                    src="<?php echo e($avatarUrl); ?>"
                                                                    class="img-fluid rounded-circle"
                                                                    alt="User avatar"
                                                                >
                                                            </span>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <!-- [ Static content ] end -->
            <!-- [ Footer ] start -->
            <footer class="site-footer bg-gray-100">
                <div class="<?php echo e(ViewClassNamesConstants::CT); ?>">
                    <div class="footer-row">
                        <div class="ftr-col cmp-detail">
                            <div class="footer-logo mb-3">
                                <a rel="external" href="https://prestech.com.br/site/" hreflang="pt-BR" target="_blank">
                                    <img src="<?php echo e(asset($lpSettings[LandingPageSettingsConstants::SL_K] ?? 'assets/images/favicon.ico')); ?>" 
                                         alt="logo" 
                                         id="footerLogo"
                                         data-fallback-index="0"
                                         style="border-radius: 1rem;
                                         transform: scale(0.8);"
                                         onload="this.style.opacity = '1'"
                                         onerror="
                                            const fallbacks = [
                                                '<?php echo e(asset('assets/images/favicon.ico')); ?>',
                                                '<?php echo e(asset('assets/favicon.ico')); ?>',
                                                '<?php echo e(asset('favicon.ico')); ?>'
                                            ];
                                            let currentIndex = parseInt(this.getAttribute('data-fallback-index')) || 0;
                                            if (currentIndex < fallbacks.length - 1) {
                                                if (currentIndex === 0) {
                                                    this.style.opacity = '0';
                                                    this.style.transition = 'opacity 0.5s ease-in-out';
                                                }
                                                currentIndex++;
                                                this.setAttribute('data-fallback-index', currentIndex);
                                                this.src = fallbacks[currentIndex];
                                            } else {
                                                this.style.opacity = '1';
                                                this.onerror = null;
                                            }
                                         ">
                                </a>
                            </div>
                            <p>
                                <?php if(!empty($lpSettings[LandingPageSettingsConstants::SD_K])): ?>
                                    <?php echo $lpSettings[LandingPageSettingsConstants::SD_K]; ?>

                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="ftr-col">
                            <?php
                                $menuItems??=[];
                                try {
                                    $menuItems=json_decode(
                                        $lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]',
                                        true
                                    )?:[];
                                } catch (\Error $e) {
                                    Log::error(
                                        'Error decoding menubar pages JSON',
                                        [
                                            'exception_class'=>get_class($e),
                                            'message'=>$e->getMessage(),
                                            'file'=>$e->getFile(),
                                            'line'=>$e->getLine(),
                                            'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                        ]
                                    );
                                } catch (\Exception $e) {
                                    Log::error(
                                        'Exception decoding menubar pages JSON',
                                        [
                                            'exception_class'=>get_class($e),
                                            'message'=>$e->getMessage(),
                                            'file'=>$e->getFile(),
                                            'line'=>$e->getLine(),
                                            'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                        ]
                                    );
                                } catch (\Throwable $e) {
                                    Log::error(
                                        'Throwable decoding menubar pages JSON',
                                        [
                                            'exception_class'=>get_class($e),
                                            'message'=>$e->getMessage(),
                                            'file'=>$e->getFile(),
                                            'line'=>$e->getLine(),
                                            'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                        ]
                                    );
                                }
                            ?>
                            <ul class="list-unstyled">
                                <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $footer??=false;
                                        $template??='';
                                        $slug??='';
                                        $name??='';
                                        $url??='';
                                        try {
                                            $footer=($item['footer']??'')==='on';
                                            $template=$item['template_name']??'';
                                            $slug=$item[LandingPageSettingsConstants::PG_SLG]??'';
                                            $name=$item[LandingPageSettingsConstants::MB_PG_NM]??'';
                                            if($footer&&$template==='page_content'){
                                                $cstNm='custom.page';
                                                $url=Route::has($cstNm)
                                                    ?route($cstNm,['slug'=>$slug])
                                                    :'#';
                                            }elseif($footer&&$template==='page_url'){
                                                $url=$item['page_url']??'#';
                                            }else{
                                                $url='#';
                                            }
                                        } catch (\Error $e) {
                                            Log::error(
                                                'Error processing footer item',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'footer'=>$footer,
                                                    'template'=>$template,
                                                    'slug'=>$slug,
                                                    'name'=>$name
                                                ]
                                            );
                                        } catch (\Exception $e) {
                                            Log::error(
                                                'Exception processing footer item',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'footer'=>$footer,
                                                    'template'=>$template,
                                                    'slug'=>$slug,
                                                    'name'=>$name
                                                ]
                                            );
                                        } catch (\Throwable $e) {
                                            Log::error(
                                                'Throwable processing footer item',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'footer'=>$footer,
                                                    'template'=>$template,
                                                    'slug'=>$slug,
                                                    'name'=>$name
                                                ]
                                            );
                                        }
                                    ?>
                                    <?php if($footer && $name): ?>
                                        <li>
                                            <a href="<?php echo e($url); ?>">
                                                <?php echo $name; ?>

                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <?php if( $lpSettings[LandingPageSettingsConstants::JU_STT_K] == 'on'): ?>
                        <div class="ftr-col ftr-subscribe">
                            <h2>
                                <?php if(! empty($lpSettings[LandingPageSettingsConstants::JU_HDG_K])): ?>
                                    <?php echo $lpSettings[LandingPageSettingsConstants::JU_HDG_K]; ?>

                                <?php endif; ?>
                            </h2>
                            <p>
                                <?php if(! empty($lpSettings[LandingPageSettingsConstants::JU_DESC_K])): ?>
                                    <?php echo $lpSettings[LandingPageSettingsConstants::JU_DESC_K]; ?>

                                <?php endif; ?>
                            </p>
                            <?php
                                $juRt = RoutesResourcesConstants::JU.'.store';
                                $juSt = Route::has($juRt) ? $juRt : '#';
                            ?>
                            <form method="post" action="<?php echo e($juSt); ?>">
                                <?php echo csrf_field(); ?>
                                <div class="input-wrapper border border-dark" style="border-color: transparent !important; margin-bottom: 1rem">
                                    <input type="text" name="email" placeholder="Type your email address...">
                                    <button type="submit" class="btn btn-dark rounded-pill"><?php echo e(__('Join Us')); ?>!</button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="border-top border-dark text-center p-2">
                    <p class="mb-0">  &copy;
                        <?php echo e(date('Y')); ?> <?php echo e(Utility::getValByName(SettingsConstants::FT_TXT) ? Utility::getValByName(SettingsConstants::FT_TXT) : config('app.name', 'ERPNovaPrestech')); ?>

                    </p>
                </div>
            </footer>
            <!-- [ Footer ] end -->
            <!-- Required Js -->
            <script src="<?php echo e(Module::asset('LandingPage:js/plugins/popper.min.js')); ?>"></script>
            <script src="<?php echo e(Module::asset('LandingPage:js/plugins/bootstrap.min.js')); ?>"></script>
            <script src="<?php echo e(Module::asset('LandingPage:js/plugins/feather.min.js')); ?>"></script>
            <script>
                // Start [ Menu hide/show on scroll ]
                let ost = 0;
                document.addEventListener("scroll", function () {
                    let cOst = document.documentElement.scrollTop;
                    const navbar = document.querySelector(".navbar");
                    if (nabvbar instanceof HTMLElement) {
                        if (cOst == 0)
                            navbar.classList.add("top-nav-collapse");
                        else if (cOst > ost) {
                            navbar.classList.add("top-nav-collapse");
                            navbar.classList.remove("default");
                        } else {
                            navbar.classList.add("default");
                            navbar.classList.remove("top-nav-collapse");
                        }
                        ost = cOst;
                    }
                });
                if (document.getElementById('#navbar-example')) {
                    const scrollSpy = new bootstrap.ScrollSpy(document.body, {
                        target: "#navbar-example",
                    });
                }
                feather && typeof feather.replace === 'function' && feather.replace();
            </script>
            <script>
                (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                    'Current route:',
                    '<?php echo e(Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction()); ?>'
                );
            </script>
        </body>
    </html>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/layouts/custompage.blade.php ENDPATH**/ ?>