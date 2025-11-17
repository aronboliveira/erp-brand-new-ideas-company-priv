<?php
	use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,SettingsConstants,
        StacksConstants, UsersConstants, ViewsConstants,
        ViewClassNamesConstants as VC,YieldingConstants};
	use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Route};
	use Illuminate\Support\Str;
	use Symfony\Component\Console\Output\ConsoleOutput;
	$settings??=[];
	$colorSettings??=[];
    $lang = Utility::fetchUserLang();
	$languages??=[$lang];
	$filePath??='';
	try {
        $user=auth()->user()?:null;
		$settings=Utility::settings()?:[];
		$colorSettings=$settings[SettingsConstants::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
        $lang = isset($user[UsersConstants::COL_LG])?$user[UsersConstants::COL_LG]:DatabaseConstants::DEFAULT_LANG;
        if (empty($lang)) $lang = DatabaseConstants::DEFAULT_LANG;
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Forgot Password Blade ({$filePath})",
			['route'=>request()?->getRequestUri()??'Undefined URI','user'=>optional(auth()->user())->id??'Unidentified User']
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Forgot Password Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Forgot Password Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching data for Forgot Password Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching data for Forgot Password Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	}
?>

<?php $__env->startSection(YieldingConstants::AUTH_PG_TTL); ?>
    <?php echo e(__('Reset Password')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
<?php if(!empty($settings[SettingsConstants::RCPT_MDL]) && $settings[SettingsConstants::RCPT_MDL] == 'on'): ?>
    <?php echo Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs(); ?>

<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::AUTH_LG_BAR); ?>
    <div class="<?php echo e(VC::LNG_DD_DSK); ?>">
        <li class="<?php echo e(VC::LNG_DD_IT); ?>">
            <a class="<?php echo e(VC::DRP_BTN); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> <?php echo e($languages[$lang]); ?>

                </span>
            </a>
            <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>">
                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $passwordRequestRoute         = Route::has('password.request')
                            ? route('password.request', $code)
                            : (Route::has(Str::kebab('password.request'))
                                ? route(Str::kebab('password.request'), $code)
                                : '#');
                        $passwordRequestLinkId        = 'password-request-link-' . $code;
                        $passwordRequestRouteMsg      = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::AUT,
                            'password_request_route_unavailable'
                        ) ?? 'Password request route is unavailable. Please contact technical support or your domain administrator.';
                    ?>
                    <a
                        id="<?php echo e($passwordRequestLinkId); ?>"
                        href="<?php echo e($passwordRequestRoute); ?>"
                        tabindex="0"
                        class="dropdown-item"
                        data-url="<?php echo e($passwordRequestRoute); ?>"
                        data-guard-msg="<?php echo e($passwordRequestRouteMsg); ?>"
                    >
                        <span><?php echo e(Str::ucfirst($language)); ?></span>
                    </a>
                    <?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
                        <script defer>
                            (() => {
                                const el = document.getElementById('<?php echo e($passwordRequestLinkId); ?>');
                                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                el.setAttribute('data-listener-active', 'true');
                                el.addEventListener('click', event => {
                                    try {
                                        const href = el.getAttribute('href');
                                        const url  = el.getAttribute('data-url');
                                        if ((href && href !== '#') || (url && url !== '#')) return;
                                        event.preventDefault();
                                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                        el.setAttribute('data-failed-route', 'true');
                                    } catch (e) {}
                                });
                            })();
                        </script>
                    <?php $__env->stopPush(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </li>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::AUTH_CTT); ?>
    <div class="card-body">
        <div>
            <h2 class="<?php echo e(VC::MB3_FW600); ?>><span class="text-primary"><?php echo e(__('Reset Password')); ?>"</span></h2>
            
        </div>
        <?php
            $lang                   = Utility::fetchUserLang();
            $passwordEmailRoute     = Route::has('password.email')
                ? route('password.email')
                : '#';
            $passwordEmailFormId    = 'password-email-form';
            $passwordEmailMsg       = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::AUT,
                'password_email_route_unavailable'
            ) ?? 'Password reset email route is unavailable. Please contact technical support or your domain administrator.';
        ?>
        <form
            method="POST"
            action="<?php echo e($passwordEmailRoute); ?>"
            id="<?php echo e($passwordEmailFormId); ?>"
            data-url="<?php echo e($passwordEmailRoute); ?>"
            data-guard-msg="<?php echo e($passwordEmailMsg); ?>"
            >
            <?php echo csrf_field(); ?>
            <div class="">
                <div class="<?php echo e(VC::FM_GB3); ?>">
                    <label for="email" class="form-label"><?php echo e(__('E-Mail')); ?></label>
                    <input id="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-feedback" role="alert">
                        <small><?php echo e($message); ?></small>
                    </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <?php if(!empty($settings[SettingsConstants::RCPT_MDL]) && $settings[SettingsConstants::RCPT_MDL] == 'on'): ?>
                    <div class="<?php echo e(VC::FM_GB3); ?>">
                     <?php echo Anhskohbo\NoCaptcha\Facades\NoCaptcha::display($colorSettings[SettingsConstants::CST_DRK]=='on' ? ['data-theme' => 'dark'] : []); ?>                        
                        <?php $__errorArgs = [SettingsConstants::G_RCPT_RES];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="small text-danger" role="alert">
                                <strong><?php echo e($message); ?></strong>
                            </span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                <?php endif; ?>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-block mt-2"><?php echo e(__('Send Password Reset Link')); ?></button>
                </div>
                <?php
                    $loginRoute        = Route::has('login') ? route('login') : '#';
                    $backLoginLinkId   = 'back-to-login-link';
                    $loginUnavailable  = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::AUT,
                        'login_unavailable'
                    ) ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
                ?>
                <p class="my-4 text-center">
                    <?php echo e(__('Back to')); ?>

                    <a
                        id="<?php echo e($backLoginLinkId); ?>"
                        href="<?php echo e($loginRoute); ?>"
                        class="text-primary"
                        data-url="<?php echo e($loginRoute); ?>"
                        data-guard-msg="<?php echo e($loginUnavailable); ?>"
                        data-event-alias="false"
                    >
                        <?php echo e(__('Login')); ?>

                    </a>
                </p>
                <?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
                    <script defer>
                        (() => {
                            const el = document.getElementById('<?php echo e($backLoginLinkId); ?>');
                            if (!el || el.getAttribute('data-event-alias') === 'true') return;
                            el.setAttribute('data-event-alias', 'true');
                            const url = el.getAttribute('data-url');
                            const msg = el.getAttribute('data-guard-msg');
                            if (!url || url === '#') {
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl = document.createElement('div');
                                    toastEl.className = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                el.setAttribute('data-failed-route', 'true');
                                return;
                            }
                            el.addEventListener('click', event => {
                                try {
                                    event.preventDefault();
                                    window.location.href = url;
                                } catch (e) {}
                            });
                        })();
                    </script>
                <?php $__env->stopPush(); ?>
            </div>
        </form>
        <?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
            <script defer src="<?php echo e(asset('assets/js/routes/auth/passwords/email.js')); ?>"></script>
        <?php $__env->stopPush(); ?>
    </div>
<?php $__env->stopSection(); ?>




<?php echo $__env->make(ExtendingLayoutsConstants::AUTH, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/auth/forgot_password.blade.php ENDPATH**/ ?>