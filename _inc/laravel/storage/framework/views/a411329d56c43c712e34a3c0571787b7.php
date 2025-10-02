<?php
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\SettingsConstants as LandingPageSettingsConstants;
	use Symfony\Component\Console\Output\ConsoleOutput;
	$output??=null;
	$backtrace??=[];
	$compiledPath??='';
	$filePath??='';
	$uri??='';
	$lpSettings??=[];
	$menubarStatus??='';
	$rawPages??='';
	$msg??='';
	$decoded??=null;
	$pages??=[];
	try {
		$output=new ConsoleOutput();
		$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$compiledPath=collect(
			array_column($backtrace,'file')
		)->first(fn($f)=>
			is_string($f)&&str_contains($f,storage_path('framework/views'))
		)?:'';
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
		$uri=request()?->getRequestUri()??'Undefined URI';
		Log::debug(
			"Rendering Menubar Layout Blade ({$filePath})",
			['route'=>$uri,'user'=>optional(auth()->user())->id??'Unidentified User']
		);
	} catch (Error|Exception|Throwable $e) {}
	try {
		$output->writeln(
			"Rendering Menubar Layout Blade ({$filePath}) for {$uri}"
		);
	} catch (Error|Exception|Throwable $e) {}
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$lpSettings=[];
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$lpSettings=[];
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$lpSettings=[];
	}
	$menubarStatus=$lpSettings[LandingPageSettingsConstants::MB_STT_K]??'off';
	$rawPages=$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]';
	$msg='Loaded settings for menubar';
	Log::debug($msg,$lpSettings);
	try {
		$decoded=json_decode($rawPages);
		$pages=is_array($decoded)||is_object($decoded)?$decoded:[];
		$msg="Loaded pages list";
		Log::debug($msg,$pages);
		$output->writeln($msg);
	} catch (Error|Exception|Throwable $e) {
		Log::warning(
			'Invalid menubar_page JSON',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'rawPages'=>$rawPages
			]
		);
		$pages=[];
	}
?>
<?php if($menubarStatus === 'on' && count((array) $pages)): ?>
    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $loginRequired??=false;
            $template??='';
            $name??='';
            try {
                $loginRequired=data_get($page,'login')==='on';
                $template=data_get($page,'template_name')?:'';
                $name=data_get($page,LandingPageSettingsConstants::MB_PG_NM,'')?:'';
            } catch (\Error $e) {
                Log::error(
                    'Error processing menubar page data',
                    [
                        'exception_class'=>get_class($e),
                        'message'=>$e->getMessage(),
                        'file'=>$e->getFile(),
                        'line'=>$e->getLine()
                    ]
                );
            } catch (\Exception $e) {
                Log::error(
                    'Exception processing menubar page data',
                    [
                        'exception_class'=>get_class($e),
                        'message'=>$e->getMessage(),
                        'file'=>$e->getFile(),
                        'line'=>$e->getLine()
                    ]
                );
            } catch (\Throwable $e) {
                Log::error(
                    'Throwable processing menubar page data',
                    [
                        'exception_class'=>get_class($e),
                        'message'=>$e->getMessage(),
                        'file'=>$e->getFile(),
                        'line'=>$e->getLine()
                    ]
                );
            }
        ?>
        <?php if($loginRequired && $template === 'page_content'): ?>
            <li class="nav-item">
                <a class="nav-link"
                href="<?php echo e((Route::has('custom.page') && data_get($page, LandingPageSettingsConstants::PG_SLG)) 
                        ? route('custom.page', data_get($page, LandingPageSettingsConstants::PG_SLG)) 
                        : '#'); ?>">
                    <?php echo e($name); ?>

                </a>
            </li>
        <?php elseif($loginRequired && $template === 'page_url'): ?>
            <li class="nav-item">
                <a class="nav-link" target="_blank"
                   href="<?php echo e(data_get($page, 'page_url', '#')); ?>">
                    <?php echo e($name); ?>

                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<script>
    console.log(
        'Current route:',
        '<?php echo e(Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() ?? 'unknown'); ?>'
    );
</script>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/layouts/buttons.blade.php ENDPATH**/ ?>