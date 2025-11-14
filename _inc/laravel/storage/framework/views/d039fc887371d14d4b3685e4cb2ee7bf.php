<?php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Models\Utility;
    use Illuminate\Support\Facades\Auth;
    $profile = Utility::getFile('uploads/avatar/');
    $avatarUrl = !empty($user?->avatar)
        ? ($profile . '/' . $user->avatar)
        : asset('/storage/' . config('chatify.user_avatar.folder') . '/avatar.png');
?>

<div class="<?php echo e(VC::AV_CC); ?> av-l"
     style="background-image: url('<?php echo e($avatarUrl); ?>');"
     role="img" aria-label="<?php echo e(__('User avatar')); ?>">
</div>

<p class="info-name"><?php echo e(config('chatify.name') ?? __('Failed to get app name')); ?></p>

<div class="messenger-infoView-btns">
    <a href="#" class="danger delete-conversation">
        <i class="<?php echo e(VC::TI_TRS); ?>"></i> <?php echo e(__('Delete Conversation')); ?>

    </a>
</div>

<div class="messenger-infoView-shared">
    <p class="messenger-title"><?php echo e(__('Shared photos')); ?></p>
    <div class="shared-photos-list"></div>
</div>





<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/vendor/Chatify/layouts/info.blade.php ENDPATH**/ ?>