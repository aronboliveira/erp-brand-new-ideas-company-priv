<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Session};
    Log::debug('Loading admin footer data...');
    $settings = Utility::settings();
    Log::debug('Loading admin footer template...');
?>
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
            <p class="mb-0 text-muted"> &copy;
                <?php echo e(date('Y')); ?> <?php echo e($settings[SettingsConstants::FT_TXT] ? $settings[SettingsConstants::FT_TXT] : config('app.name', 'ERPNovaPrestech')); ?>

            </p>
        </div>
    </div>
</footer>
<!-- Required Js -->
<script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/dash.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/jquery.form.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/popper.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/perfect-scrollbar.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/simplebar.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/moment.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/bootstrap-switch-button.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/sweetalert2.all.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/simple-datatables.js')); ?>"></script>
<!-- Apex Chart -->
<script defer src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/choices.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/flatpickr.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/jscolor.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/popper.min.js')); ?>"></script>

<script>
    var site_currency_symbol_position = '<?php echo e($settings['site_currency_symbol_position']); ?>';
    var site_currency_symbol = '<?php echo e($settings['site_currency_symbol']); ?>';
</script>
<script src="<?php echo e(asset('js/custom.js')); ?>"></script>
<?php if($message = Session::get('success')): ?>
    <script>
        show_toastr('success', '<?php echo $message; ?>');
    </script>
<?php endif; ?>
<?php if($message = Session::get('error')): ?>
    <script>
        show_toastr('error', '<?php echo $message; ?>');
    </script>
<?php endif; ?>
<?php if($settings['enable_cookie'] == 'on'): ?>
    <?php if ($__env->exists(ExtendingLayoutsConstants::CKC)) echo $__env->make(ExtendingLayoutsConstants::CKC, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>
<?php echo $__env->yieldPushContent('script-page'); ?>
<?php echo $__env->yieldPushContent('old-datatable-js'); ?>
<script defer>
    (() => {
      const errFb = '# ERROR';
      const dataClientLocalized = 'data-client-localized';
      const dataGuardMsg = 'data-guard-msg';
      const langSessionKey = 'erp-np-lang';
    
      const getLocalizedMessage = (msgKey, el) => {
        let msg = errFb;
        if (
          el.getAttribute('data-sv-localized') === 'true' ||
          el.getAttribute(dataClientLocalized) === 'true'
        ) {
          msg = el.getAttribute(dataGuardMsg) ?? errFb;
        } else {
          let lang = (
            window.sessionStorage.getItem(langSessionKey) ??
            document.documentElement.lang ??
            'en'
          )
            .toLowerCase()
            .replace(/_/g, '-');
          lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
          msg =
            window.translations?.[lang]?.[msgKey] ??
            el.getAttribute(dataGuardMsg) ??
            window.translations?.['en']?.[msgKey] ??
            errFb;
          if (msg !== errFb) {
            el.setAttribute(dataGuardMsg, msg);
            el.setAttribute(dataClientLocalized, 'true');
          }
        }
        return msg;
      };
    
      const showError = message => {
        try {
          let container = document.querySelector('#bootstrap-toast-container');
          if (!container) {
            const hasBs =
              Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .some(l => /bootstrap/i.test(l.href)) &&
              window.bootstrap?.Toast;
            if (hasBs) {
              container = document.createElement('div');
              container.id = 'bootstrap-toast-container';
              container.setAttribute('aria-live', 'polite');
              container.setAttribute('aria-atomic', 'true');
              document.body.appendChild(container);
            }
          }
          if (container && window.bootstrap.Toast) {
            let toast = container.querySelector('.toast');
            if (!toast) {
              toast = document.createElement('div');
              toast.className = 'toast';
              toast.setAttribute('role', 'alert');
              toast.setAttribute('aria-live', 'assertive');
              toast.setAttribute('aria-atomic', 'true');
              const body = document.createElement('div');
              body.className = 'toast-body';
              toast.appendChild(body);
              container.appendChild(toast);
              if (toast.getAttribute('data-click-listener') !== 'true') {
                toast.addEventListener('click', () => body.textContent = message);
                toast.setAttribute('data-click-listener', 'true');
              }
            }
            toast.querySelector('.toast-body').textContent = message;
            new bootstrap.Toast(toast).show();
          } else {
            alert(message);
          }
        } catch {
          alert(message);
        }
      };
    
      const removeClassByPrefix = (node, prefix) => {
        node?.classList?.forEach(cls => {
          if (cls.startsWith(prefix)) node.classList.remove(cls);
        });
      };
    
      // feather replace
      try {
        window.feather?.replace?.();
      } catch {
        showError(getLocalizedMessage('feather_replace_failed', document.body));
      }
    
      // pctoggler
      const pctoggle = document.querySelector('#pct-toggler');
      if (pctoggle && pctoggle.dataset.listenerAttached !== 'true') {
        pctoggle.dataset.listenerAttached = 'true';
        const obs1 = new MutationObserver((ms, o) => {
          ms.forEach(m => m.removedNodes.forEach(n => {
            if (n === pctoggle) {
              pctoggle.removeEventListener('click', onPctoggle);
              o.disconnect();
            }
          }));
        });
        obs1.observe(document.body, { childList: true, subtree: true });
        pctoggle.addEventListener('click', onPctoggle);
      }
      function onPctoggle() {
        try {
          const customizer = document.querySelector('.pct-customizer');
          if (!customizer) throw new Error();
          customizer.classList.toggle('active');
        } catch {
          showError(getLocalizedMessage('pctoggle_failed', pctoggle));
        }
      }
    
      // theme color switches
      document.querySelectorAll('.themes-color > a').forEach(el => {
        if (el.dataset.listenerAttached === 'true') return;
        el.dataset.listenerAttached = 'true';
        const obs2 = new MutationObserver((ms, o) => {
          ms.forEach(m => m.removedNodes.forEach(n => {
            if (n === el) {
              el.removeEventListener('click', onThemeColor);
              o.disconnect();
            }
          }));
        });
        obs2.observe(document.body, { childList: true, subtree: true });
        el.addEventListener('click', onThemeColor);
      });
      function onThemeColor(e) {
        try {
          let tgt = e.target;
          if (tgt.tagName === 'SPAN') tgt = tgt.parentNode;
          const val = tgt.getAttribute('data-value') ?? '';
          removeClassByPrefix(document.body, 'theme-');
          document.body.classList.add(val);
        } catch {
          showError(getLocalizedMessage('themecolor_failed', e.currentTarget));
        }
      }
    
      // custom theme background
      const custthemebg = document.querySelector('#cust-theme-bg');
      if (custthemebg && custthemebg.dataset.listenerAttached !== 'true') {
        custthemebg.dataset.listenerAttached = 'true';
        const obs3 = new MutationObserver((ms, o) => {
          ms.forEach(m => m.removedNodes.forEach(n => {
            if (n === custthemebg) {
              custthemebg.removeEventListener('click', onCustThemeBg);
              o.disconnect();
            }
          }));
        });
        obs3.observe(document.body, { childList: true, subtree: true });
        custthemebg.addEventListener('click', onCustThemeBg);
      }
      function onCustThemeBg() {
        try {
          const sidebar = document.querySelector('.dash-sidebar');
          const header = document.querySelector('.dash-header:not(.dash-mob-header)');
          if (!sidebar || !header) throw new Error();
          if (custthemebg.checked) {
            sidebar.classList.add('transprent-bg');
            header.classList.add('transprent-bg');
          } else {
            sidebar.classList.remove('transprent-bg');
            header.classList.remove('transprent-bg');
          }
        } catch {
          showError(getLocalizedMessage('custthemebg_failed', custthemebg));
        }
      }
    
      // custom dark layout toggle
      const custdarklayout = document.querySelector('#cust-darklayout');
      if (custdarklayout && custdarklayout.dataset.listenerAttached !== 'true') {
        custdarklayout.dataset.listenerAttached = 'true';
        const obs4 = new MutationObserver((ms, o) => {
          ms.forEach(m => m.removedNodes.forEach(n => {
            if (n === custdarklayout) {
              custdarklayout.removeEventListener('click', onCustDark);
              o.disconnect();
            }
          }));
        });
        obs4.observe(document.body, { childList: true, subtree: true });
        custdarklayout.addEventListener('click', onCustDark);
      }
      function onCustDark() {
        try {
          const linkEl = document.querySelector('#main-style');
          const logoEl = document.querySelector('.m-header > .b-brand > .logo-lg');
          if (!linkEl || !logoEl) throw new Error();
          if (custdarklayout.checked) {
            linkEl.setAttribute('href', '<?php echo e(asset("assets/css/style-dark.css")); ?>');
            logoEl.setAttribute('src', '<?php echo e(asset("/storage/uploads/logo/{}")); ?>');
          } else {
            linkEl.setAttribute('href', '<?php echo e(asset("assets/css/style.css")); ?>');
            logoEl.setAttribute('src', '<?php echo e(asset("/storage/uploads/logo/logo-dark.webp")); ?>');
          }
        } catch {
          showError(getLocalizedMessage('custdarklayout_failed', custdarklayout));
        }
      }
    })();
</script>
    

<?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/partials/admin/footer.blade.php ENDPATH**/ ?>