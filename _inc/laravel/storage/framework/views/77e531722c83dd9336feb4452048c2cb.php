<?php
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Symfony\Component\Console\Output\ConsoleOutput;
	$settings??=[];
	$uri??='';
	$backtrace??=[];
	$filePath??='';
	try {
		$settings=Utility::settings()?:[];
	} catch (\Error $e) {
		Log::error(
			'Error in Utility::settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$settings=[];
	} catch (\Exception $e) {
		Log::error(
			'Exception in Utility::settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$settings=[];
	} catch (\Throwable $e) {
		Log::error(
			'Throwable in Utility::settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$settings=[];
	}
	$uri=request()?->getRequestUri()??'Undefined URI';
	try {
		$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$filePath=collect(array_column($backtrace,'file'))
			->first(fn($p)=>
				is_string($p)
				&&str_ends_with($p,'.blade.php')
			)??'unknown.blade.php';
	} catch (\Error $e) {
		Log::error(
			'Error getting blade file path',
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
			'Exception getting blade file path',
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
			'Throwable getting blade file path',
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
			"Rendering Cookie Consent Layout ({$filePath})",
			['route'=>$uri,'user'=>optional(auth()->user())->id??'Unidentified User']
		);
	} catch (Error|Exception|Throwable $e) {}
	try {
		(new ConsoleOutput)
			->writeln(
				"Rendering Cookie Consent Layout ({$filePath}) for {$uri}"
			);
	} catch (Error|Exception|Throwable $e) {}
?>

<link rel="stylesheet" href="<?php echo e(asset('css/cookieconsent.css')); ?>" media="screen" />
<script src="<?php echo e(asset('js/cookieconsent.js')); ?>"></script>
<script>
    let language_code = document.documentElement.getAttribute('lang') || 'en', 
        languages = window.languages || {};
    languages[language_code] = {
        consent_modal: {
            title: '<?php echo e($settings['cookie_title'] ?? 'Cookie Consent'); ?>',
            description: '<?php echo e($settings['cookie_description'] ?? 'This website uses cookies.'); ?>',
            primary_btn: { text: 'Accept all', role: 'accept_all' },
            secondary_btn: { text: 'Reject all', role: 'accept_necessary' }
        },
        settings_modal: {
            title: 'Cookie preferences',
            save_settings_btn: 'Save settings',
            accept_all_btn: 'Accept all',
            reject_all_btn: 'Reject all',
            close_btn_label: 'Close',
            blocks: [
                {
                    title: '<?php echo e($settings['cookie_title'] ?? ''); ?>',
                    description: '<?php echo e($settings['cookie_description'] ?? ''); ?>'
                },
                {
                    title: '<?php echo e($settings['strictly_cookie_title'] ?? 'Necessary cookies'); ?>',
                    description: '<?php echo e($settings['strictly_cookie_description'] ?? 'Always active'); ?>',
                    toggle: { value: 'necessary', enabled: true, readonly: true }
                },
                {
                    title: 'More information',
                    description: '<?php echo e($settings['more_information_description'] ?? ''); ?> <a class="cc-link" href="<?php echo e($settings['contactus_url'] ?? '#'); ?>">contact us</a>.'
                }
            ]
        }
    };
</script>
<script>
    function setCookie(cname, cvalue, exdays) {
        exdays = Number(exdays) || 0;
        const d = new Date();
        d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
        document.cookie = `${cname}=${cvalue};expires=${d.toUTCString()};path=/`;
    }
    function getCookie(cname) {
        const nameEQ = cname + "=";
        const ca = (document.cookie || "").split(';');
        for (let c of ca) {
            c = c.trim();
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
        }
        return "";
    }
    if (typeof initCookieConsent === 'function') {
        const cc = initCookieConsent();
        cc.run({
            current_lang: language_code,
            autoclear_cookies: true,
            page_scripts: true,
            gui_options: {
                consent_modal: { layout: 'cloud', position: 'bottom center', transition: 'slide', swap_buttons: false },
                settings_modal: { layout: 'box', transition: 'slide' }
            },
            onChange: function(cookie, changed_preferences) {
            },
            onAccept: function(cookie) {
                if (!getCookie('cookie_consent_logged')) {
                    const level = cookie.level;
                    if (window.jQuery) {
                        $.ajax({
                            url: '<?php echo e(Route::has("cookie-consent") ? route("cookie-consent") : ""); ?>',
                            dataType: 'json',
                            data: { cookie: level },
                        });
                    }
                    setCookie('cookie_consent_logged', '1', 182);
                }
            },
            languages: languages
        });
    }
</script>
<script>
    (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
        'Current route:',
        '<?php echo e(Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() ?? 'unknown'); ?>'
    );
</script>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/layouts/cookie_consent.blade.php ENDPATH**/ ?>