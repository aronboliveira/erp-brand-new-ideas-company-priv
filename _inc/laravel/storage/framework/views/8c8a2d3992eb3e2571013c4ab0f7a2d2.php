<link rel="icon"
      href="<?php echo e(empty($faviconUrl) ? 'favicon.ico' : $faviconUrl); ?>"
      type="image/x-icon"
/>
<script id="fallbackIcon">
	(function(){
			const link = document.querySelector("link[rel*='icon']");
			if (!link) return;
			const originals = [
					link.href,
					'/favicon.ico',
					'/favicon.svg',
					'/favicon.png'
			];
			function tryNext(idx) {
					if (idx >= originals.length) return;
					const img = new Image();
					img.onload = function(){ link.href = originals[idx]; };
					img.onerror = function(){ tryNext(idx + 1); };
					img.src    = originals[idx];
			}
			tryNext(0);
			setTimeout(() => {
					document.getElementById('fallbackIcon')?.remove()
			}, 2000);
	})();
</script><?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/fragments/favicon.blade.php ENDPATH**/ ?>