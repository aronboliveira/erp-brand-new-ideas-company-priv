<?php
	use App\Config\Constants\{DatabaseConstants,ExtendingLayoutsConstants,
        SettingsConstants as SC,StacksConstants,ViewClassNamesConstants as VC,
        YieldingConstants};
	use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log,Facades\Route,Str};
	use Symfony\Component\Console\Output\ConsoleOutput;
	$filePath??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$languages??=[DatabaseConstants::DEFAULT_LANG];
	$lang??=DatabaseConstants::DEFAULT_LANG;
	try {
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Login Blade ({$filePath})",
			[
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Login Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data['settings']??[];
		$colorSettings=$data[SC::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		$lang= Utility::fetchUserLang() ?? $data[SC::LCL] ?? DatabaseConstants::DEFAULT_LANG;
	} catch (\Error $e) {
		Log::error(
			'Error in Login Blade rendering',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath,
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception in Login Blade rendering',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath,
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable in Login Blade rendering',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath,
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	}
    $data = Utility::fallbackSettings($data);
?>

<?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
<?php if(!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on'): ?>
        <?php echo Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs(); ?>

<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::AUTH_PG_TTL); ?>
    <?php echo e(__('Login')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::AUTH_LG_BAR); ?>
    <div class="<?php echo e(VC::LNG_DD_DSK); ?>">
        <li class="<?php echo e(VC::LNG_DD_IT); ?>">
            <a class="<?php echo e(VC::DRP_BTN); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> <?php echo e(!empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DatabaseConstants::DEFAULT_LANG)); ?>

                </span>
            </a>
            <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>">
                <?php if(!empty($languages) && ((is_array($languages) && count($languages)) || ($languages instanceof Collection && $languages->isNotEmpty()))): ?>
                    <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $loginBase = 'login';
                            $loginKebab = Str::kebab($loginBase);
                            $loginResolved = Route::has($loginBase) ? $loginBase : (Route::has($loginKebab) ? $loginKebab : null);
                            $loginUrl = $loginResolved ? route($loginResolved, $code) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $loginGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'login_lang_route_unavailable') ?? 'Login language route is unavailable. Please contact technical support or your domain administrator.';
                            $anchorId = 'login-lang-'.Str::slug((string)$code,'-');
                            $label = Str::upper($language);
                        ?>
                        <a id="<?php echo e($anchorId); ?>"
                        href="<?php echo e($loginUrl); ?>"
                        tabindex="0"
                        class="dropdown-item"
                        data-url="<?php echo e($loginUrl); ?>"
                        data-guard-msg="<?php echo e($loginGuardMsg); ?>"
                        data-sv-localized="true">
                            <span><?php echo e($label); ?></span>
                        </a>
                        <?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
                            <script defer>
                                (() => {
                                    try {
                                        const el = document.getElementById('<?php echo e($anchorId); ?>');
                                        if (!el) { return; }
                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                        el.setAttribute('data-listener-active','true');
                                        el.addEventListener('click',(e) => {
                                            try {
                                                const href = el.getAttribute('href') ?? '#';
                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                if (url !== '#' && href !== '#') { return; }
                                                e.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
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
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route','true');
                                            } catch (err) {}
                                        });
                                    } catch (err) {}
                                })();
                            </script>
                        <?php $__env->stopPush(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <span class="drp-text"> <?php echo e(__(DatabaseConstants::DEFAULT_LANG)); ?></span>
                <?php endif; ?>
            </div>
        </li>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::AUTH_CTT); ?>
    <div class="card-body">
        <div>
            <h2 class="<?php echo e(VC::MB3_FW600); ?>"><?php echo e(__('Login')); ?></h2>
        </div>
        <?php
            $loginStoreBase = 'login.store';
            $loginStoreKebab = Str::kebab($loginStoreBase);
            $loginStoreResolved = Route::has($loginStoreBase) ? $loginStoreBase : (Route::has($loginStoreKebab) ? $loginStoreKebab : null);
            $loginStoreUrl = $loginStoreResolved ? route($loginStoreResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $loginStoreGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'store_login_auth_route_unavailable') ?? 'Login submit route is unavailable. Please contact technical support or your domain administrator.';
            $pwdReqBase = 'password.request';
            $pwdReqKebab = Str::kebab($pwdReqBase);
            $pwdReqResolved = Route::has($pwdReqBase) ? $pwdReqBase : (Route::has($pwdReqKebab) ? $pwdReqKebab : null);
            $pwdReqUrl = $pwdReqResolved ? route($pwdReqResolved) : '#';
            $pwdReqGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'password_request_auth_route_unavailable') ?? 'Password request route is unavailable. Please contact technical support or your domain administrator.';
            $pwdReqAnchorId = 'password-request-link';
            $registerBase = 'register';
            $registerKebab = Str::kebab($registerBase);
            $registerResolved = Route::has($registerBase) ? $registerBase : (Route::has($registerKebab) ? $registerKebab : null);
            $registerUrl = $registerResolved ? route($registerResolved) : (url('register') ?: '#');
            $registerGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'register_auth_route_unavailable') ?? 'Register route is unavailable. Please contact technical support or your domain administrator.';
            $registerAnchorId = 'register-link';
        ?>
        <?php echo e(Form::open([
            'method' => 'post',
            'url' => $loginStoreUrl,
            'id' => 'loginForm',
            'class' => 'login-form',
            'data-url' => $loginStoreUrl,
            'data-guard-msg' => $loginStoreGuardMsg,
            'data-sv-localized' => 'true',
        ])); ?>

            <?php echo e(csrf_field()); ?>

            <div class="custom-login-form">
                <div class="<?php echo e(VC::FM_GB3); ?>">
                    <label class="form-label" for="email-input"><?php echo e(__('Email')); ?></label>
                    <?php echo e(Form::text('email', null, [
                        'class' => 'form-control',
                        'placeholder' => __('Enter Your Email'),
                        'id' => 'email-input',
                        'autocomplete' => 'email',
                        'required' => 'required',
                        'autofocus' => 'autofocus',
                        'title' => __('Please enter a valid email address.'),
                        'aria-label' => __('Email Address'),
                    ])); ?>

                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error invalid-email text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="<?php echo e(VC::FM_GB3); ?>">
                    <label class="form-label" for="pw-input"><?php echo e(__('Password')); ?></label>
                    <div class="input-group">
                        <?php echo e(Form::password('password', [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Your Password'),
                            'id' => 'pw-input',
                            'autocomplete' => 'current-password',
                            'required' => 'required',
                            'autofocus' => 'autofocus',
                            'title' => __('Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters.'),
                        ])); ?>

                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="<?php echo e(__('Toggle password visibility')); ?>">
                            <svg id="toggleIcon" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"></path>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"></path>
                            </svg>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error invalid-password text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <script defer src="<?php echo e(asset('assets/js/routes/auth/login/toggle.js')); ?>"></script>
                <link rel="stylesheet" href="<?php echo e(asset('assets/css/routes/auth/login/toggle.css')); ?>"></link>
                <div class="form-group mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <span>
                            <a id="<?php echo e($pwdReqAnchorId); ?>"
                            href="<?php echo e($pwdReqUrl); ?>"
                            tabindex="0"
                            data-url="<?php echo e($pwdReqUrl); ?>"
                            data-guard-msg="<?php echo e($pwdReqGuardMsg); ?>"
                            data-sv-localized="true">
                                <?php echo e(__('Forgot your password?')); ?>

                            </a>
                        </span>
                    </div>
                </div>
                <div class="d-grid">
                    <?php echo e(Form::submit(__('Login'), [
                        'class' => 'btn btn-primary mt-2',
                        'id' => 'saveBtn',
                    ])); ?>

                </div>
                <?php if(!empty($setting[SC::ENB_SGU]) && $setting[SC::ENB_SGU] == 'on'): ?>
                    <p class="my-4 text-center">
                        <?php echo e(__("Don't have an account?")); ?>

                        <a id="<?php echo e($registerAnchorId); ?>"
                        href="<?php echo e($registerUrl); ?>"
                        tabindex="0"
                        data-url="<?php echo e($registerUrl); ?>"
                        data-guard-msg="<?php echo e($registerGuardMsg); ?>"
                        data-sv-localized="true"><?php echo e(__('Register')); ?></a>
                    </p>
                <?php endif; ?>
                <?php if(!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on'): ?>
                    <div class="form-group col-lg-12 col-md-12 mt-3">
                        <?php echo Anhskohbo\NoCaptcha\Facades\NoCaptcha::display(
                            $colorSettings[SC::CST_DRK] == 'on'
                                ? ['data-theme' => 'dark']
                                : []
                        ); ?>

                        <?php $__errorArgs = [SC::G_RCPT_RES];
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
            </div>
        <?php echo e(Form::close()); ?>

        <?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
            <script defer src=<?php echo e(asset('assets/js/routes/auth/login/store.js')); ?>></script>
        <?php $__env->stopPush(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::AUTH_CST_SCR); ?>
    <script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
    <script async src="<?php echo e(asset('js/routes/auth/login/lang/submit.js')); ?>"></script>
    <script async src="<?php echo e(asset('js/routes/auth/login/submit.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make(ExtendingLayoutsConstants::AUTH, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/auth/login.blade.php ENDPATH**/ ?>