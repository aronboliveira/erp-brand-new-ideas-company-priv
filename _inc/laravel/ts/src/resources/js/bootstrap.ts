/**
 * @file bootstrap.ts — Laravel Mix bootstrap entry point
 * @description Mirrors resources/js/bootstrap.js.
 *              Loads lodash and axios, sets CSRF header.
 */

declare global {
  interface Window {
    _: typeof import("lodash");
    axios: typeof import("axios").default;
    // Pusher?: typeof import("pusher-js").default;
    // Echo?: import("laravel-echo").default;
  }
}

import _ from "lodash";
window._ = _;

/**
 * Axios HTTP library — automatically sends CSRF token via XSRF cookie.
 */
import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Laravel Echo + Pusher — uncomment when broadcasting is enabled.
 *
 * import Echo from 'laravel-echo';
 * import Pusher from 'pusher-js';
 * window.Pusher = Pusher;
 * window.Echo = new Echo({
 *     broadcaster: 'pusher',
 *     key: process.env.MIX_PUSHER_APP_KEY,
 *     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
 *     forceTLS: true
 * });
 */

export {};
