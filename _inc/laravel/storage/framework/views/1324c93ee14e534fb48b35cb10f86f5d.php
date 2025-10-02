<!DOCTYPE html>
<?php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		StacksConstants,
		ViewClassNamesConstants,
		YieldingConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	use Modules\LandingPage\Config\Constants\{
		ExtendingLandingPageLayoutConstants as E,
		RoutesResourcesConstants       as R
	};
	use Symfony\Component\Console\Output\ConsoleOutput;
	$uri??='';
	$backtrace??=[];
	$compiledPath??='';
	$filePath??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$company_logo_dk??='';
	$company_logo_lt??='';
	$company_favicon??='';
	$logo??='';
	$color??='';
	$siteRtl??='';
	$lang = Utility::fetchUserLang();
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$get_cookie??='';
	$faviconUrl??='';
	try {
		$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$compiledPath=collect(
			array_column($backtrace,'file')
		)->first(fn($f)=>is_string($f)
			&&str_contains($f,storage_path('framework/views'))
		)??'';
		if($compiledPath&&file_exists($compiledPath)){
			$contents=file_get_contents($compiledPath);
			$filePath=preg_match(
				'/\*\*PATH\s+(.+\.blade\.php)\s+ENDPATH\*\*/',
				$contents,$m
			)?$m[1]:'unknown.blade.php';
		}else$filePath='unknown.blade.php';
	} catch (\Error $e) {
		Log::error(
			'Error resolving blade file path',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$filePath='unknown.blade.php';
	} catch (\Exception $e) {
		Log::error(
			'Exception resolving blade file path',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$filePath='unknown.blade.php';
	} catch (\Throwable $e) {
		Log::error(
			'Throwable resolving blade file path',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$filePath='unknown.blade.php';
	}
	try {
		Log::debug(
			"Rendering Authentication Layout Blade ({$filePath})",
			['route'=>$uri,'user'=>optional(auth()->user())->id??'Unidentified User']
		);
	} catch (Error|Exception|Throwable $e) {}
	try {
		(new ConsoleOutput)
			->writeln(
				"Rendering Authentication Layout Blade ({$filePath}) for {$uri}"
			);
	} catch (Error|Exception|Throwable $e) {}
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SettingsConstants::ENTITY]??
			SettingsConstants::DFT_SETTINGS;
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$company_logo_dk=$setting[SettingsConstants::CPN_LG_DK]??
			$setting[SettingsConstants::CPN_LG_LT]??'';
		$company_logo_lt=$setting[SettingsConstants::CPN_LG_LT]??
			$setting[SettingsConstants::CPN_LG_DK]??'';
		$company_favicon=$data[SettingsConstants::FAV_ICN]??
			asset('favicon.ico');
		$logo=$data[SettingsConstants::LOGO]??asset('favicon.ico');
		$color=$data[SettingsConstants::THM_CLR]??
			SettingsConstants::THM_CLR_DEF;
		$siteRtl=$data[SettingsConstants::RTL]??'off';
		$lang=$data[SettingsConstants::LCL]?? Utility::fetchUserLang() ??
			str_replace('_','-',app()->getLocale())??
			DatabaseConstants::DEFAULT_LANG;
		$meta_title=$data[SettingsConstants::MT_TTL_K]??
			config('app.name','ERPNovaPrestech');
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??
			config('app.desc','A brand new ERP!');
		$meta_image=$data[SettingsConstants::MT_IMG_K]??
			$setting[SettingsConstants::CPN_LG_LT]??
			$setting[SettingsConstants::CPN_LG_DK]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??
			$data[SettingsConstants::MT_IMG_K]??
			$setting[SettingsConstants::CPN_LG_LT]??
			$setting[SettingsConstants::CPN_LG_DK]??'';
		$get_cookie=$data[SettingsConstants::CK_STG]??'off';
		$faviconUrl=Utility::getCompanyLogo()?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching common view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching common view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching common view data',
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
<html lang="<?php echo e($lang); ?>" dir="<?php echo e($siteRtl === 'on' ? 'rtl' : 'ltr'); ?>">
    <head>
        <title>
            <?php echo e(Utility::getValByName('title_text') ? Utility::getValByName('title_text') : config('app.name', 'ERPNovaPrestech')); ?>

            - <?php echo $__env->yieldContent(YieldingConstants::AUTH_PG_TTL); ?></title>
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
        <?php echo $__env->make('fragments.stylesheets', ['settings' => $colorSettings], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> 
        <?php if($colorSettings[SettingsConstants::CST_DRK] ==='on' && is_file(asset('assets/css/custom-auth-dark.css'))): ?>
            <link rel="stylesheet" href="<?php echo e(asset('assets/css/custom-auth-dark.css')); ?>" id="custom-auth-style-link">
        <?php else: ?>
            <link rel="stylesheet" href="<?php echo e(asset('assets/css/custom-auth.css')); ?>" id="custom-auth-style-link">
        <?php endif; ?>
        <?php if($siteRtl === 'on'): ?>
            <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-rtl.css')); ?>" id="style-rtl-link">
            <link rel="stylesheet" href="<?php echo e(asset('assets/css/custom-auth-rtl.css')); ?>" id="custom-auth-rtl-link">
        <?php endif; ?>
    </head>
    <body class="<?php echo e($color); ?>">
        <div class="custom-login">
            <div class="login-bg-img">
                <img src="<?php echo e(asset('assets/images/auth/' . ($color ?: 'default') . '.svg')); ?>" class="login-bg-1">
                <img src="<?php echo e(asset('assets/images/auth/common.svg')); ?>" class="login-bg-2">
            </div>
            <div class="bg-login <?php echo e(ViewClassNamesConstants::BG_P); ?>"></div>
            <div class="custom-login-inner">
                <header class="<?php echo e(ViewClassNamesConstants::DSH); ?>">
                    <nav class="<?php echo e(ViewClassNamesConstants::NVB_DEF); ?>">
                        <div class="<?php echo e(ViewClassNamesConstants::CT); ?>">
                            <div class="<?php echo e(ViewClassNamesConstants::NVB_BR); ?>">
                            <a class="<?php echo e(ViewClassNamesConstants::NVB_BR); ?>" href="#">
                                <?php
                                    $srcDark='';
                                    $srcLight='';
                                    $bgDark='';
                                    $bgLight='';
                                    try {
                                        $srcDark=!empty($company_logo_dk)?$company_logo_dk:'logo-dark.webp';
                                        $srcLight=!empty($company_logo_lt)?$company_logo_lt:'logo-light.webp';
                                        $bgDark=($company_logo_dk===$company_logo_lt)?'background-color: #181818':'';
                                        $bgLight=($company_logo_lt===$company_logo_dk)?'background-color: #fff':'';
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error computing logo sources/backgrounds',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception computing logo sources/backgrounds',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable computing logo sources/backgrounds',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    }
                                ?>
                                <?php if($colorSettings[SettingsConstants::CST_DRK] === 'on'): ?>
                                    <img
                                        class="<?php echo e(ViewClassNamesConstants::LOGO); ?>"
                                        src="<?php echo e(asset($srcDark)); ?>"
                                        alt="Company Logo"
                                        loading="lazy"
                                        <?php if($bgDark): ?>
                                            style="<?php echo e($bgDark); ?>"
                                        <?php endif; ?>
                                    />
                                <?php else: ?>
                                    <img
                                        class="<?php echo e(ViewClassNamesConstants::LOGO); ?>"
                                        src="<?php echo e(asset($srcLight)); ?>"
                                        alt="Company Logo"
                                        loading="lazy"
                                        <?php if($bgLight): ?>
                                            style="<?php echo e($bgLight); ?>"
                                        <?php endif; ?>
                                    />
                                <?php endif; ?>
                            </a>
                            </div>
                            <button class="<?php echo e(ViewClassNamesConstants::NVB_TG); ?>" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarlogin">
                                <span class="<?php echo e(ViewClassNamesConstants::NVB_TG_IC); ?>"></span>
                            </button>
                            <div class="<?php echo e(ViewClassNamesConstants::NVB_CLP); ?>" id="navbarlogin">
                                <ul class="<?php echo e(ViewClassNamesConstants::NVB_NAV_LG); ?>">
                                    <?php if ($__env->exists(R::LP.'::'.E::LOS.'.buttons')) echo $__env->make(R::LP.'::'.E::LOS.'.buttons', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    <?php echo $__env->yieldContent(YieldingConstants::AUTH_LG_BAR); ?>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </header>
                <main class="custom-wrapper">
                    <div class="custom-row">
                        <div class="<?php echo e(ViewClassNamesConstants::CD); ?>">
                            <?php echo $__env->yieldContent(YieldingConstants::AUTH_CTT); ?>
                        </div>
                    </div>
                </main>
                <footer>
                    <div class="<?php echo e(ViewClassNamesConstants::AUT_FT); ?>">
                        <div class="<?php echo e(ViewClassNamesConstants::CT); ?>">
                            <div class="<?php echo e(ViewClassNamesConstants::RW); ?>">
                                <div class="col-12">
                                    <span>&copy; <?php echo e(date('Y')); ?>

                                        <?php echo e(Utility::getValByName(SettingsConstants::FT_TXT) ?: config('app.name', 'Storego Saas')); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <?php if($get_cookie === 'on'): ?>
            <?php if ($__env->exists(ExtendingLayoutsConstants::CKC)) echo $__env->make(ExtendingLayoutsConstants::CKC, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
        <div
        id="loginToast"
        class="toast position-fixed top-0 end-0 m-3"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        data-bs-delay="5000"
        style="display: none;"
        >
            <div class="toast-header">
                <strong class="me-auto text-danger">Oops!</strong>
                <button
                type="button"
                class="btn-close"
                data-bs-dismiss="toast"
                aria-label="Close"
                ></button>
            </div>
            <div class="toast-body">
                <?php echo $__env->yieldPushContent('toasts'); ?>
            </div>
        </div>
        
        <script src="<?php echo e(asset('assets/js/vendor-all.js')); ?>"></script>
        <script defer src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
        <script defer src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                feather.replace();
            });
        </script>
        <?php if($colorSettings[SettingsConstants::CST_DRK] === 'on'): ?>
            <style>
                .g-recaptcha {
                    filter: invert(1) hue-rotate(180deg) !important;
                }
            </style>
        <?php endif; ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                feather.replace();
                document.querySelector("#pct-toggler")?.addEventListener("click", () => {
                    const cust = document.querySelector(".pct-customizer");
                    cust && cust.classList.toggle("active");
                });
                document.querySelectorAll(".themes-color > a").forEach(c => {
                    c.addEventListener("click", event => {
                        let target = event.target.tagName === "SPAN" ? event.target.parentNode : event.target;
                        const val = target.getAttribute("data-value");
                        if (val) {
                            document.body.classList.forEach(cls => {
                                if (cls.startsWith("theme-")) document.body.classList.remove(cls);
                            });
                            document.body.classList.add(val);
                        }
                    });
                });
            });
        </script>
        <?php echo $__env->yieldPushContent(StacksConstants::AUTH_CST_SCR); ?>
        <script>
            console.log(
                'Current route:',
                '<?php echo e(Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction()); ?>'
            );
        </script>
    </body>
</html>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/layouts/auth.blade.php ENDPATH**/ ?>