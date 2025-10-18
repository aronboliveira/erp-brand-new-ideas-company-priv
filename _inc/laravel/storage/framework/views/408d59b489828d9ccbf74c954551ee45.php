<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        ViewsConstants as VW,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Illuminate\Support\{
        Facades\Auth, 
        Facades\Log,
        Facades\Route,
        Facades\View as ViewFacade, 
        Str
    };

    $user = Auth::user();
    $setting = Utility::colorset();
    $color   = (!empty($setting['color'])) ? $setting['color'] : 'theme-3';

    $dashBase  = 'dashboard';
    $dashKebab = Str::kebab($dashBase);
    $dashName  = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl   = $dashName ? route($dashName) : '#';
?>



<?php $__env->startPush('css-page'); ?>
    <?php echo $__env->make('Chatify::layouts.head_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YW::ADM_PG_TTL); ?>
    <?php echo e(__('Messenger')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YW::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e($dashUrl); ?>" <?php echo e($dashUrl === '#' ? 'aria-disabled=true' : ''); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Messenger')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YW::ADM_CTT); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="messenger">
                        <div class="messenger-listView">
                            <div class="m-header">
                                <nav>
                                    <nav class="m-header-right">
                                        <a href="#" class="listView-x"><i class="fas fa-times"></i></a>
                                    </nav>
                                </nav>

                                <input type="text" class="messenger-search" placeholder="<?php echo e(__('Search')); ?>" />

                                <div class="messenger-listView-tabs">
                                    <a href="#"
                                       <?php if($route == 'user'): ?> class="active-tab" <?php endif; ?>
                                       data-view="users"
                                       aria-label="<?php echo e(__('Recent')); ?>"
                                       title="<?php echo e(__('Recent')); ?>">
                                        <svg class="svg-inline--fa fa-clock fa-w-16" role="img" viewBox="0 0 512 512" aria-hidden="true">
                                            <path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm57.1 350.1L224.9 294c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h48c6.6 0 12 5.4 12 12v137.7l63.5 46.2c5.4 3.9 6.5 11.4 2.6 16.8l-28.2 38.8c-3.9 5.3-11.4 6.5-16.8 2.6z"></path>
                                        </svg>
                                    </a>

                                    <a href="#"
                                       <?php if($route == 'group'): ?> class="active-tab" <?php endif; ?>
                                       data-view="groups"
                                       aria-label="<?php echo e(__('Members')); ?>"
                                       title="<?php echo e(__('Members')); ?>">
                                        <svg class="svg-inline--fa fa-users fa-w-20" role="img" viewBox="0 0 640 512" aria-hidden="true">
                                            <path fill="currentColor" d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <div class="m-body">
                                <div class="<?php if($route == VW::USR): ?> show <?php endif; ?> messenger-tab app-scroll" data-view="users">
                                    <div class="favorites-section">
                                        <p class="messenger-title"><?php echo e(__('Favorites')); ?></p>
                                        <div class="messenger-favorites app-scroll-thin"></div>
                                    </div>

                                    <?php echo view('Chatify::layouts.listItem', ['get' => 'saved','id' => $id])->render(); ?>


                                    <div class="listOfContacts" style="width: 100%;height: calc(100% - 200px);position: relative;"></div>
                                </div>

                                <div class="all_members <?php if($route == 'group'): ?> show <?php endif; ?> messenger-tab app-scroll" data-view="groups">
                                    <p style="text-align: center;color:grey;"><?php echo e(__('Soon will be available')); ?></p>
                                </div>

                                <div class="messenger-tab app-scroll" data-view="search">
                                    <p class="messenger-title"><?php echo e(__('Search')); ?></p>
                                    <div class="search-records">
                                        <p class="message-hint center-el"><span><?php echo e(__('Type to search..')); ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="messenger-messagingView">
                            <div class="m-header m-header-messaging">
                                <nav>
                                    <div style="display: inline-flex;">
                                        <a href="#" class="show-listView"><i class="ti ti-arrow-left"></i></a>

                                        <?php if(!empty($user?->avatar)): ?>
                                            <div class="avatar av-s header-avatar"
                                                 style="margin: 0 10px; margin-top: -5px; margin-bottom: -5px; background-image: url('<?php echo e(asset('/storage/avatars/'.$user?->avatar)); ?>');">
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar av-s header-avatar"
                                                 style="margin: 0 10px; margin-top: -5px; margin-bottom: -5px; background-image: url('<?php echo e(asset('/storage/avatars/avatar.png')); ?>');">
                                            </div>
                                        <?php endif; ?>

                                        <a href="#" class="user-name"><?php echo e(config('chatify.name') ?? __('Failed to get app name')); ?></a>
                                    </div>

                                    <nav class="m-header-right">
                                        <a href="#" id="messenger-fav-toggle" class="add-to-favorite" aria-pressed="false" aria-label="<?php echo e(__('Favorite')); ?>">
                                            <i id="messenger-fav-icon" class="fa fa-star"></i>
                                        </a>
                                        <a href="#" class="show-infoSide header-icon" aria-label="<?php echo e(__('Info')); ?>">
                                            <svg class="svg-inline--fa fa-info-circle fa-w-16" role="img" viewBox="0 0 512 512" aria-hidden="true">
                                                <path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
                                            </svg>
                                        </a>
                                    </nav>
                                </nav>
                            </div>

                            <div class="internet-connection">
                                <span class="ic-connected"><?php echo e(__('Connected')); ?></span>
                                <span class="ic-connecting"><?php echo e(__('Connecting...')); ?></span>
                                <span class="ic-noInternet"><?php echo e(__('Please add pusher settings for using messenger.')); ?></span>
                            </div>

                            <div class="m-body app-scroll">
                                <div class="messages">
                                    <p class="message-hint" style="margin-top: calc(30% - 126.2px);">
                                        <span><?php echo e(__('Please select a chat to start messaging')); ?></span>
                                    </p>
                                </div>

                                <div class="typing-indicator">
                                    <div class="message-card typing">
                                        <p>
                                            <span class="typing-dots">
                                                <span class="dot dot-1"></span>
                                                <span class="dot dot-2"></span>
                                                <span class="dot dot-3"></span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <?php echo $__env->make('Chatify::layouts.send_form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            </div>
                        </div>

                        <div class="messenger-infoView app-scroll text-center">
                            <nav>
                                <a href="#"><i class="fas fa-times"></i></a>
                            </nav>
                            <?php
                                if (ViewFacade::exists('Chatify::layouts.info')) {
                                    try {
                                        echo ViewFacade::make('Chatify::layouts.info')->render();
                                    } catch (Exception $e) {
                                        Log::error('Failed to render Chatify::layouts.info view', [
                                            'error' => $e->getMessage(),
                                            'file' => $e->getFile(),
                                            'line' => $e->getLine()
                                        ]);
                                    }
                                } else {
                                    Log::warning('Chatify::layouts.info view does not exist');
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <?php echo $__env->make('Chatify::layouts.modals', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script defer src="<?php echo e(asset('assets/js/routes/messenger/favorite.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php if(preg_match('/^theme-([1-9]|10)$/', $color)): ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/routes/vendors/chatify/' . $color . '.css')); ?>" />
<?php endif; ?>
















































































































































































<?php echo $__env->make(EL::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/vendor/Chatify/pages/app.blade.php ENDPATH**/ ?>