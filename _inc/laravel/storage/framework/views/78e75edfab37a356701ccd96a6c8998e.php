<?php
	use App\Config\Constants\{DatabaseConstants,ExtendingLayoutsConstants,
        SettingsConstants,StacksConstants,ViewClassNamesConstants,
        YieldingConstants};
	use App\Models\Utility;
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
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		$lang=$data[SettingsConstants::LCL]??DatabaseConstants::DEFAULT_LANG;
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
<?php if(!empty($setting[SettingsConstants::RCPT_MDL]) && $setting[SettingsConstants::RCPT_MDL] == 'on'): ?>
        <?php echo Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs(); ?>

<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::AUTH_PG_TTL); ?>
    <?php echo e(__('Login')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::AUTH_LG_BAR); ?>
    <div class="<?php echo e(ViewClassNamesConstants::LNG_DD_DSK); ?>">
        <li class="<?php echo e(ViewClassNamesConstants::LNG_DD_IT); ?>">
            <a class="<?php echo e(ViewClassNamesConstants::DRP_BTN); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> <?php echo e($languages[$lang]); ?>

                </span>
            </a>
            <div class="<?php echo e(ViewClassNamesConstants::DRP_MN_DSH_END); ?>">
                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('login',$code)); ?>"tabindex="0" class="dropdown-item">
                        <span><?php echo e(Str::upper($language)); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </li>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::AUTH_CTT); ?>
    <div class="card-body">
        <div>
            <h2 class="<?php echo e(ViewClassNamesConstants::MB3_FW600); ?>"><?php echo e(__('Login')); ?></h2>
        </div>
        <?php echo e(Collective\Html\FormFacade::open([
            'route'  => 'login.store',
            'method' => 'post',
            'id'     => 'loginForm',
            'class'  => 'login-form'
        ])); ?>

            <?php echo e(csrf_field()); ?>

            <div class="custom-login-form">
                <div class="<?php echo e(ViewClassNamesConstants::FM_GB3); ?>">
                    <label class="form-label" for="email-input"><?php echo e(__('Email')); ?></label>
                    <?php echo e(Collective\Html\FormFacade::text('email', null, [
                        'class'       => 'form-control',
                        'placeholder' => __('Enter Your Email'),
                        'id'          => 'email-input',
                        'autocomplete' => 'email',
                        'required'    => 'required',
                        'autofocus'   => 'autofocus',
                        'title'      => __('Please enter a valid email address.'),
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
                <div class="<?php echo e(ViewClassNamesConstants::FM_GB3); ?>">
                    <label class="form-label" for="pw-input"><?php echo e(__('Password')); ?></label>
                    <div class="input-group">
                        <?php echo e(Collective\Html\FormFacade::password('password', [
                            'class'       => 'form-control',
                            'placeholder' => __('Enter Your Password'),
                            'id'          => 'pw-input',
                            'autocomplete' => 'current-password',
                            'required'    => 'required',
                            'autofocus'   => 'autofocus',
                            'title'      => __('Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters.')
                        ])); ?>

                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="<?php echo e(__('Toggle password visibility')); ?>">
                            <svg id="toggleIcon" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
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
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const togglePassword = document.getElementById('togglePassword'), 
                    passwordInput = document.getElementById('pw-input'), 
                    toggleIcon = document.getElementById('toggleIcon');
                    togglePassword.addEventListener('click', function() {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        if (type === 'password') {
                            toggleIcon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>';
                        } else {
                            toggleIcon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>';
                        }
                    });
                });
                </script>
                <style>
                    .input-group .btn-outline-secondary {
                        border-left: 0;
                    }
                    
                    .input-group .form-control:focus {
                        border-color: #86b7fe;
                        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                    }

                    #togglePassword {
                        cursor: pointer;
                        border: 1px solid #ced4da;
                    }
                </style>

                <div class="form-group mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <?php if(Route::has('password.request')): ?>
                            <span>
                                <a href="<?php echo e(route('password.request')); ?>" tabindex="0">
                                    <?php echo e(__('Forgot your password?')); ?>

                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
        
                <div class="d-grid">
                    <?php echo e(Collective\Html\FormFacade::submit(__('Login'), [
                        'class' => 'btn btn-primary mt-2',
                        'id'    => 'saveBtn'
                    ])); ?>

                </div>
        
                <?php if(!empty($setting[SettingsConstants::ENB_SGU]) && $setting[SettingsConstants::ENB_SGU] == 'on'): ?>
                    <p class="my-4 text-center">
                        <?php echo e(__("Don't have an account?")); ?>

                        <a href="<?php echo e(url('register')); ?>" tabindex="0"><?php echo e(__('Register')); ?></a>
                    </p>
                <?php endif; ?>
        
                <?php if(!empty($setting[SettingsConstants::RCPT_MDL]) && $setting[SettingsConstants::RCPT_MDL] == 'on'): ?>
                    <div class="form-group col-lg-12 col-md-12 mt-3">
                        <?php echo Anhskohbo\NoCaptcha\Facades\NoCaptcha::display(
                            $colorSettings[SettingsConstants::CST_DRK] == 'on'
                                ? ['data-theme' => 'dark']
                                : []
                        ); ?>

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
            </div>
        <?php echo e(Collective\Html\FormFacade::close()); ?>

    </div>
<?php $__env->stopSection(); ?>

<script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
<script>
    $(document).ready(function() {
        $("#form_data").submit(function(e) {
            $("#login_button").attr("disabled", true);
            return true;
        });
    });
</script>

<?php echo $__env->make(ExtendingLayoutsConstants::AUTH, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/auth/login.blade.php ENDPATH**/ ?>