<link
  rel="icon"
  href="<?php echo e(empty($faviconUrl) ? 'favicon.ico' : $faviconUrl); ?>"
  type="image/x-icon"
/>
<script id="fallbackIcon">
	(() => {
		const linkEl = document.querySelector("link[rel~='icon']");
		if (!linkEl) return;

		const candidates = [
			linkEl.href,
			'/favicon.ico',
			'/favicon.svg',
			'/favicon.png'
		];

		let idx = 0;
		const tryNext = () => {
			if (idx >= candidates.length) return;
			const img = new Image();
			img.onload = () => { linkEl.href = candidates[idx]; };
			img.onerror = () => { idx++; tryNext(); };
			img.src = candidates[idx];
		};

		tryNext();

		setTimeout(() => {
			document.getElementById('fallbackIcon')?.remove();
		}, 2000);
	})();
</script>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/fragments/favicon.blade.php ENDPATH**/ ?>