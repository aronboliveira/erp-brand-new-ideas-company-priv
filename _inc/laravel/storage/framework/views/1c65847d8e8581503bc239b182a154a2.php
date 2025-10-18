<?php
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();

    $sendBase  = 'send.message';
    $sendKebab = Str::kebab($sendBase);
    $sendName  = Route::has($sendBase) ? $sendBase : (Route::has($sendKebab) ? $sendKebab : null);
    $sendUrl   = $sendName ? route($sendName) : '#';
    $sendGuard = Utility::fetchLinkMessage($lang, 'messenger', 'send_message_route_unavailable') ?? 'Send message route is unavailable. Please contact technical support or your domain administrator.';
    $formId    = 'message-form';
?>

<?php echo Form::open([
    'url'                  => $sendUrl,
    'method'               => 'POST',
    'id'                   => $formId,
    'enctype'              => 'multipart/form-data',
    'data-resolved-action' => $sendUrl,
    'data-guard-msg'       => $sendGuard,
    'data-sv-localized'    => 'true',
]); ?>

    <div class="messenger-sendCard">
        <label>
            <i style="margin-top: 65%" class="fas fa-paperclip fa-sm"></i>
            <input type="file" class="upload-attachment" name="file" accept="image/*, .txt, .rar, .zip" />
        </label>
        <textarea style="height: 39px; width: 92%; padding: 9px;" name="message" class="m-send app-scroll" placeholder="<?php echo e(__('Type a message..')); ?>"></textarea>
        <button type="submit"><i class="fas fa-paper-plane fa-xs text-primary"></i></button>
    </div>
    <script defer src="<?php echo e(asset('assets/js/routes/messages/send.js')); ?>"></script>
<?php echo Form::close(); ?>

<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/vendor/Chatify/layouts/send_form.blade.php ENDPATH**/ ?>