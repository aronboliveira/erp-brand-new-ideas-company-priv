<link
  rel="icon"
  href="{{ empty($faviconUrl) ? asset('favicon.ico') : preg_replace('#^(https?://[^/]+)/.*?([^/]+\.(ico|svg|png))$#', '$1/$2', $faviconUrl) }}"
  type="image/x-icon"
/>
<script id="fallbackIcon">
	(() => {
		const linkEl = document.querySelector("link[rel~='icon']");
		if (!linkEl) return;
		const origin = window.location.origin;
		const candidates = [
			`${origin}/favicon.ico`,
			`${origin}/favicon.svg`,
			`${origin}/favicon.png`
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
