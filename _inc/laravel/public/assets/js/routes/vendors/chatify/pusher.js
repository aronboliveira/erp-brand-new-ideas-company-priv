(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  if (!guard || !utils) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);
  const scheduleError = msg => {
    let errorMessage = msg;
    const handler = () => {
      if (errorMessage) {
        showError(errorMessage);
        errorMessage = null;
      }
    };
    document.body.addEventListener("pointerup", handler, { once: true });
  };

  const initGuard = "data-pusher-init";
  const initPusher = () => {
    if (document.body.getAttribute(initGuard) === "true") return;
    document.body.setAttribute(initGuard, "true");
    try {
      if (!window.Pusher) {
        scheduleError(getMsg("plugin_unavailable"));
        return;
      }
      const key = "{{ config('chatify.pusher.key') }}";
      const cluster = "{{ config('chatify.pusher.options.cluster') }}";
      if (!key || !cluster || key === "#" || cluster === "#") {
        scheduleError(getMsg("pusher_unavailable"));
        return;
      }
      const authEndpoint = '{{route("pusher.auth")}}';
      if (!authEndpoint || authEndpoint === "#") {
        scheduleError(getMsg("pusher_auth_unavailable"));
        return;
      }
      const token = utils.getCsrfToken();
      const pusher = new window.Pusher(key, {
        encrypted: true,
        cluster: cluster,
        authEndpoint: authEndpoint,
        auth: { headers: { "X-CSRF-TOKEN": token } },
      });
      if (!pusher?.connection) {
        scheduleError(getMsg("pusher_unavailable"));
        return;
      }
      pusher.connection.bind("error", () => {
        scheduleError(getMsg("pusher_connect_failed"));
      });
      window.__appPusher = pusher;
    } catch {
      scheduleError(getMsg("pusher_unavailable"));
    }
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initPusher, { once: true });
  } else {
    initPusher();
  }
})();
