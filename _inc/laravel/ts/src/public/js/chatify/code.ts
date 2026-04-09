/**
 * @fileoverview TypeScript version of public/js/chatify/code.js
 * Migração jQuery → vanilla JS. jQuery mantido apenas onde estritamente
 * necessário (scrollbar plugins, etc.).
 * @generated from original JavaScript - manual review recommended
 * @module code
 */
// @ts-nocheck

/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-base-to-string, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars, @typescript-eslint/restrict-plus-operands, no-constant-condition, prefer-const */
/* global bootstrap, $, jQuery */

// PULL REQUEST START — Alteração customizada: remoção parcial de jQuery no Chatify

"use strict";

/* ------------------------------------------------------------------ */
/*  Helpers                                                           */
/* ------------------------------------------------------------------ */
const qs  = (sel, root = document) => root.querySelector(sel);
const qsa = (sel, root = document) => root.querySelectorAll(sel);

function _meta(name) {
  const el = qs('meta[name="' + name + '"]');
  return el ? el : null;
}

/** POST fetch helper — replaces $.ajax with POST */
function _post(endpoint, data, opts) {
  const headers = { "X-Requested-With": "XMLHttpRequest" };
  let body;
  if (data instanceof FormData) {
    body = data;
  } else {
    headers["Content-Type"] = "application/x-www-form-urlencoded";
    body = new URLSearchParams(data).toString();
  }
  return fetch(endpoint, Object.assign({ method: "POST", headers: headers, body: body }, opts || {}))
    .then(function (r) { return r.json(); });
}

/** GET fetch helper */
function _get(endpoint, data) {
  const params = data ? "?" + new URLSearchParams(data).toString() : "";
  return fetch(endpoint + params, {
    headers: { "X-Requested-With": "XMLHttpRequest" },
  }).then(function (r) { return r.json(); });
}

/** Delegated event on body (replaces $("body").on) */
function onBody(event, selector, handler) {
  document.addEventListener(event, function (e) {
    const target = e.target.closest(selector);
    if (target) handler.call(target, e);
  });
}

/** Show element */
function _show(el) { if (el) el.style.display = ""; }
/** Hide element */
function _hide(el) { if (el) el.style.display = "none"; }
/** Toggle element */
function _toggle(el) { if (el) el.style.display = (el.style.display === "none" ? "" : "none"); }

/**
 *-------------------------------------------------------------
 * Global variables
 *-------------------------------------------------------------
 */
let messenger,
  auth_id = _meta("url") ? _meta("url").getAttribute("data-user") : "",
  route = _meta("route") ? _meta("route").getAttribute("content") : "",
  url = _meta("url") ? _meta("url").getAttribute("content") : "",
  access_token = _meta("csrf-token") ? _meta("csrf-token").getAttribute("content") : "",
  typingTimeout,
  typingNow = 0,
  temporaryMsgId = 0,
  defaultAvatarInSettings = null,
  messengerColor,
  dark_mode;

const messagesContainer = qs(".messenger-messagingView .m-body"),
  messengerTitleDefault = (qs(".messenger-headTitle") || {}).textContent || "",
  messageInput = qs("#message-form .m-send");

/**
 *-------------------------------------------------------------
 * Global Templates
 *-------------------------------------------------------------
 */
function loadingSVG(w_h = "25px", className = null) {
  return (
    '<svg class="loadingSVG ' + (className || "") +
    '" xmlns="http://www.w3.org/2000/svg" width="' + w_h +
    '" height="' + w_h +
    '" viewBox="0 0 40 40" stroke="#2196f3">' +
    '<g fill="none" fill-rule="evenodd"><g transform="translate(2 2)" stroke-width="3">' +
    '<circle stroke-opacity=".1" cx="18" cy="18" r="18"></circle>' +
    '<path d="M36 18c0-9.94-8.06-18-18-18" transform="rotate(349.311 18 18)">' +
    '<animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur=".8s" repeatCount="indefinite"></animateTransform>' +
    '</path></g></g></svg>'
  );
}

// PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
function _sanitizeHtml(html) {
  const tmp = document.createElement("div");
  tmp.innerHTML = html;
  const scripts = tmp.querySelectorAll("script");
  for (let i = 0; i < scripts.length; i++) scripts[i].remove();
  const allEls = tmp.querySelectorAll("*");
  for (let i = 0; i < allEls.length; i++) {
    const el = allEls[i];
    const attrs = el.getAttributeNames();
    for (let j = 0; j < attrs.length; j++) {
      if (attrs[j].toLowerCase().startsWith("on")) {
        el.removeAttribute(attrs[j]);
      }
    }
    if (el.tagName === "A" || el.tagName === "AREA" || el.tagName === "FORM") {
      const href = el.getAttribute("href") || el.getAttribute("action") || "";
      if (href.replace(/\s/g, "").toLowerCase().startsWith("javascript:")) {
        el.removeAttribute("href");
        el.removeAttribute("action");
      }
    }
    if (el.tagName === "IFRAME" || el.tagName === "OBJECT" || el.tagName === "EMBED") {
      el.remove();
    }
  }
  return tmp.innerHTML;
}

function _safeCssUrl(rawUrl) {
  const trimmed = (rawUrl || "").trim();
  if (trimmed.startsWith("http://") || trimmed.startsWith("https://") || trimmed.startsWith("/")) {
    return 'url("' + trimmed.replace(/"/g, "") + '")';
  }
  return 'url("")';
}
// PULL REQUEST END — Fim da alteração customizada

function listItemLoading(items) {
  let template = "";
  for (let i = 0; i < items; i++) {
    template +=
      '<div class="loadingPlaceholder"><div class="loadingPlaceholder-wrapper">' +
      '<div class="loadingPlaceholder-body"><table class="loadingPlaceholder-header"><tr>' +
      '<td style="width: 45px;"><div class="loadingPlaceholder-avatar"></div></td>' +
      '<td><div class="loadingPlaceholder-name"></div><div class="loadingPlaceholder-date"></div></td>' +
      '</tr></table></div></div></div>';
  }
  return template;
}

function avatarLoading(items) {
  let template = "";
  for (let i = 0; i < items; i++) {
    template +=
      '<div class="loadingPlaceholder"><div class="loadingPlaceholder-wrapper">' +
      '<div class="loadingPlaceholder-body"><table class="loadingPlaceholder-header"><tr>' +
      '<td style="width: 45px;"><div class="loadingPlaceholder-avatar" style="margin: 2px;"></div></td>' +
      '</tr></table></div></div></div>';
  }
  return template;
}

function sendigCard(message, id) {
  return '<div class="message-card mc-sender" data-id="' + id + '"><p>' + message + '<sub><span class="far fa-clock"></span></sub></p></div>';
}

function attachmentTemplate(fileType, fileName, imgURL) {
  if (fileType !== "image") {
    return '<div class="attachment-preview"><span class="fas fa-times cancel"></span>' +
      '<p style="padding:0px 30px;"><span class="fas fa-file"></span> ' + fileName + '</p></div>';
  } else {
    return '<div class="attachment-preview"><span class="fas fa-times cancel"></span>' +
      '<div class="image-file chat-image" style="background-image: url(\'' + imgURL + '\');"></div>' +
      '<p><span class="fas fa-file-image"></span> ' + fileName + '</p></div>';
  }
}

function activeStatusCircle() {
  return '<span class="activeStatus"></span>';
}

/**
 *-------------------------------------------------------------
 * Css Media Queries
 *-------------------------------------------------------------
 */
window.addEventListener("resize", cssMediaQueries);

function cssMediaQueries() {
  if (window.matchMedia("(min-width: 980px)").matches) {
    const lv = qs(".messenger-listView");
    if (lv) lv.removeAttribute("style");
  }
  if (window.matchMedia("(max-width: 980px)").matches) {
    qsa(".messenger-list-item tr[data-action]").forEach(function (tr) { tr.setAttribute("data-action", "1"); });
    qsa(".favorite-list-item div").forEach(function (d) { d.setAttribute("data-action", "1"); });
  } else {
    qsa(".messenger-list-item tr[data-action]").forEach(function (tr) { tr.setAttribute("data-action", "0"); });
    qsa(".favorite-list-item div").forEach(function (d) { d.setAttribute("data-action", "0"); });
  }
}

/**
 *-------------------------------------------------------------
 * App Modal
 *-------------------------------------------------------------
 */
const app_modal = function ({ show = true, name, data = 0, buttons = true, header = null, body = null }) {
  const modal = qs('.app-modal[data-name="' + name + '"]');
  if (!modal) return;
  // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
  if (header) { const h = qs(".app-modal-header", modal); if (h) h.innerHTML = _sanitizeHtml(header); }
  if (body) { const b = qs(".app-modal-body", modal); if (b) b.innerHTML = _sanitizeHtml(body); }
  // PULL REQUEST END — Fim da alteração customizada
  const footer = qs(".app-modal-footer", modal);
  if (footer) buttons ? _show(footer) : _hide(footer);

  const card = qs('.app-modal-card[data-name="' + name + '"]');
  if (show) {
    _show(modal);
    if (card) { card.classList.add("app-show-modal"); card.setAttribute("data-modal", data); }
  } else {
    _hide(modal);
    if (card) { card.classList.remove("app-show-modal"); card.setAttribute("data-modal", data); }
  }
};

/**
 *-------------------------------------------------------------
 * Slide to bottom
 *-------------------------------------------------------------
 */
function scrollBottom(container) {
  const el = typeof container === "string" ? qs(container) : container;
  if (!el) return;
  el.scrollTo({ top: el.scrollHeight, behavior: "smooth" });
}

/**
 *-------------------------------------------------------------
 * Click and drag to scroll
 *-------------------------------------------------------------
 */
function hScroller(scroller) {
  const slider = qs(scroller);
  let isDown = false, startX, scrollLeft;
  if (!slider) return;

  if (!slider.getAttribute("data-listener-bound-mousedown")) {
    slider.setAttribute("data-listener-bound-mousedown", "1");
    slider.addEventListener("mousedown", function (e) { isDown = true; startX = e.pageX - slider.offsetLeft; scrollLeft = slider.scrollLeft; });
  }
  if (!slider.getAttribute("data-listener-bound-mouseleave")) {
    slider.setAttribute("data-listener-bound-mouseleave", "1");
    slider.addEventListener("mouseleave", function () { isDown = false; });
  }
  slider.addEventListener("mouseup", function () { isDown = false; });
  if (!slider.getAttribute("data-listener-bound-mousemove")) {
    slider.setAttribute("data-listener-bound-mousemove", "1");
    slider.addEventListener("mousemove", function (e) {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - slider.offsetLeft, walk = x - startX;
      slider.scrollLeft = scrollLeft - walk;
    });
  }
}

/**
 *-------------------------------------------------------------
 * Disable/enable message form fields
 *-------------------------------------------------------------
 */
function disableOnLoad(action) {
  if (action === undefined) action = true;
  if (action) {
    _hide(qs(".add-to-favorite"));
    _hide(qs(".messenger-sendCard"));
    if (messagesContainer) messagesContainer.style.opacity = ".5";
    if (messageInput) messageInput.setAttribute("readonly", "readonly");
    const btn = qs("#message-form button");
    if (btn) btn.setAttribute("disabled", "disabled");
    const upload = qs(".upload-attachment");
    if (upload) upload.setAttribute("disabled", "disabled");
  } else {
    if (messenger.split("_")[1] != auth_id) _show(qs(".add-to-favorite"));
    _show(qs(".messenger-sendCard"));
    if (messagesContainer) messagesContainer.style.opacity = "1";
    if (messageInput) messageInput.removeAttribute("readonly");
    const btn = qs("#message-form button");
    if (btn) btn.removeAttribute("disabled");
    const upload = qs(".upload-attachment");
    if (upload) upload.removeAttribute("disabled");
  }
}

/**
 *-------------------------------------------------------------
 * Error message card
 *-------------------------------------------------------------
 */
function errorMessageCard(id) {
  if (!messagesContainer) return;
  const card = qs('.message-card[data-id="' + id + '"]', messagesContainer);
  if (!card) return;
  card.classList.add("mc-error");
  const svg = qs("svg.loadingSVG", card);
  if (svg) svg.remove();
  const p = qs("p", card);
  if (p) p.insertAdjacentHTML("afterbegin", '<span class="fas fa-exclamation-triangle"></span>');
}

function _logError(label, jqXHR, textStatus, errorThrown) {
  console.error(label);
  if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1")
    console.error(label + ":", { status: jqXHR?.status, statusText: jqXHR?.statusText, responseText: jqXHR?.responseText, textStatus: textStatus, errorThrown: errorThrown });
}

/**
 *-------------------------------------------------------------
 * Fetch id data and update the view
 *-------------------------------------------------------------
 */
function IDinfo(id, type) {
  temporaryMsgId = 0;
  typingNow = 0;
  disableOnLoad();
  if (messenger != 0) {
    getSharedPhotos(id);
    _post(url + "/idInfo", { _token: access_token, id: id, type: type })
      .then(function (data) {
        // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
        const infoAvatar = qs(".messenger-infoView .avatar");
        if (infoAvatar) infoAvatar.style.backgroundImage = _safeCssUrl(data.user_avatar);
        const headerAvatar = qs(".header-avatar");
        if (headerAvatar) headerAvatar.style.backgroundImage = _safeCssUrl(data.user_avatar);
        // PULL REQUEST END — Fim da alteração customizada
        _show(qs(".messenger-infoView-btns .delete-conversation"));
        _show(qs(".messenger-infoView-shared"));
        fetchMessages(id, type);
        if (messageInput) messageInput.focus();
        // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
        const infoName = qs(".messenger-infoView .info-name");
        if (infoName) infoName.textContent = data.fetch.name;
        const userName = qs(".m-header-messaging .user-name");
        if (userName) userName.textContent = data.fetch.name;
        // PULL REQUEST END — Fim da alteração customizada
        data.favorite > 0
          ? qs(".add-to-favorite")?.classList.add("favorite")
          : qs(".add-to-favorite")?.classList.remove("favorite");
        const form = qs("#message-form");
        if (form) form.reset();
        cancelAttachment();
        if (messageInput) messageInput.focus();
      })
      .catch(function (err) { _logError("Server error for getting id info", err); });
  }
}

/**
 *-------------------------------------------------------------
 * Send message
 *-------------------------------------------------------------
 */
function sendMessage() {
  temporaryMsgId += 1;
  const tempID = "temp_" + temporaryMsgId;
  const uploadEl = qs(".upload-attachment");
  const hasFile = uploadEl && uploadEl.value ? true : false;
  const inputVal = messageInput ? messageInput.value : "";

  if (inputVal.trim().length > 0 || hasFile) {
    const form = qs("#message-form");
    const formData = new FormData(form);
    formData.append("id", messenger.split("_")[1]);
    formData.append("type", messenger.split("_")[0]);
    formData.append("temporaryMsgId", tempID);
    formData.append("_token", access_token);

    // beforeSend
    const hint = qs(".message-hint");
    if (hint) hint.remove();
    const msgs = messagesContainer ? qs(".messages", messagesContainer) : null;
    if (msgs) {
      const card = hasFile ? sendigCard(inputVal + "\n" + loadingSVG("28px"), tempID) : sendigCard(inputVal, tempID);
      msgs.insertAdjacentHTML("beforeend", card);
    }
    scrollBottom(messagesContainer);
    if (messageInput) messageInput.style.height = "42px";
    if (form) form.reset();
    cancelAttachment();
    if (messageInput) messageInput.focus();

    _post(form.getAttribute("action"), formData)
      .then(function (data) {
        if (data.error > 0) {
          errorMessageCard(tempID);
        } else {
          updateContatctItem(messenger.split("_")[1]);
          if (messagesContainer) {
            const sending = qs('.mc-sender[data-id="sending"]', messagesContainer);
            if (sending) sending.remove();
            const tempCard = qs(".message-card[data-id=" + data.tempID + "]", messagesContainer);
            if (tempCard) tempCard.insertAdjacentHTML("beforebegin", data.message);
            const tempCardAgain = qs(".message-card[data-id=" + data.tempID + "]", messagesContainer);
            if (tempCardAgain) tempCardAgain.remove();
          }
          scrollBottom(messagesContainer);
          sendContactItemUpdates(true);
        }
      })
      .catch(function () { errorMessageCard(tempID); });
  }
  return false;
}

/**
 *-------------------------------------------------------------
 * Fetch messages
 *-------------------------------------------------------------
 */
function fetchMessages(id, type) {
  if (messenger != 0) {
    _post(url + "/fetchMessages", { _token: access_token, id: id, type: type })
      .then(function (data) {
        if (messenger != 0) disableOnLoad(false);
        // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
        const msgs = messagesContainer ? qs(".messages", messagesContainer) : null;
        if (msgs) msgs.innerHTML = _sanitizeHtml(data.messages);
        // PULL REQUEST END — Fim da alteração customizada
        scrollBottom(messagesContainer);
        makeSeen(true);
      })
      .catch(function (err) { _logError("Failed to fetch messages", err); });
  }
}

/**
 *-------------------------------------------------------------
 * Cancel file attached
 *-------------------------------------------------------------
 */
function cancelAttachment() {
  const preview = qs(".messenger-sendCard .attachment-preview");
  if (preview) preview.remove();
  const upload = qs(".upload-attachment");
  if (upload) upload.value = "";
}

/**
 *-------------------------------------------------------------
 * Cancel updating avatar
 *-------------------------------------------------------------
 */
function cancelUpdatingAvatar() {
  const preview = qs(".upload-avatar-preview");
  if (preview) preview.style.backgroundImage = defaultAvatarInSettings;
  const upload = qs(".upload-avatar");
  if (upload) upload.value = "";
}

/**
 *-------------------------------------------------------------
 * Pusher channels
 *-------------------------------------------------------------
 */
const pusher = window.__appPusher,
  channel = pusher?.subscribe("private-chatify");

if (channel && typeof channel.bind === "function") {
  channel.bind("messaging", function (data) {
    if (data.from_id == messenger.split("_")[1] && data.to_id == auth_id) {
      const hint = qs(".message-hint");
      if (hint) hint.remove();
      const msgs = messagesContainer ? qs(".messages", messagesContainer) : null;
      if (msgs) msgs.insertAdjacentHTML("beforeend", data.message);
      scrollBottom(messagesContainer);
      makeSeen(true);
      const contactItem = qs('.messenger-list-item[data-contact="' + messenger.split("_")[1] + '"]');
      const badge = contactItem ? qs("tr>td>b", contactItem) : null;
      if (badge) badge.remove();
    }
  });

  channel.bind("client-typing", function (data) {
    if (data.from_id == messenger.split("_")[1] && data.to_id == auth_id) {
      const indicator = messagesContainer ? qs(".typing-indicator", messagesContainer) : null;
      if (indicator) data.typing ? _show(indicator) : _hide(indicator);
    }
    scrollBottom(messagesContainer);
  });

  channel.bind("client-seen", function (data) {
    if (data.from_id == messenger.split("_")[1] && data.to_id == auth_id) {
      if (data.seen == true) {
        qsa(".message-time .fa-check").forEach(function (check) {
          check.insertAdjacentHTML("beforebegin", '<span class="fas fa-check-double seen"></span> ');
          check.remove();
        });
      }
    }
  });

  channel.bind("client-contactItem", function (data) {
    if (data.update_for == auth_id && data.updating) updateContatctItem(data.update_to);
  });
} else console.warn('Pusher channel "private-chatify" not found!');

const activeStatusChannel = pusher?.subscribe("presence-activeStatus");
if (activeStatusChannel && typeof activeStatusChannel.bind === "function") {
  activeStatusChannel.bind("pusher:member_added", function (member) {
    setActiveStatus(1, member.id);
    const item = qs('.messenger-list-item[data-contact="' + member.id + '"]');
    if (item) {
      const active = qs(".activeStatus", item);
      if (active) active.remove();
      const avatar = qs(".avatar", item);
      if (avatar) avatar.insertAdjacentHTML("beforebegin", activeStatusCircle());
    }
  });
  activeStatusChannel.bind("pusher:member_removed", function (member) {
    setActiveStatus(0, member.id);
    const item = qs('.messenger-list-item[data-contact="' + member.id + '"]');
    if (item) {
      const active = qs(".activeStatus", item);
      if (active) active.remove();
    }
  });
} else console.warn('Pusher channel "presence-activeStatus" not found!');

/**
 *-------------------------------------------------------------
 * Trigger typing / seen events
 *-------------------------------------------------------------
 */
function isTyping(status) {
  return channel.trigger("client-typing", { from_id: auth_id, to_id: messenger.split("_")[1], typing: status });
}

function makeSeen(status) {
  const item = qs('.messenger-list-item[data-contact="' + messenger.split("_")[1] + '"]');
  if (item) { const b = qs("tr>td>b", item); if (b) b.remove(); }
  _post(url + "/makeSeen", { _token: access_token, id: messenger.split("_")[1] })
    .then(function (data) {
      const counter = qs(".custom_messanger_counter");
      if (counter) counter.textContent = data.messengerCount;
    })
    .catch(function () {});
  return channel.trigger("client-seen", { from_id: auth_id, to_id: messenger.split("_")[1], seen: status });
}

function sendContactItemUpdates(status) {
  return channel.trigger("client-contactItem", { update_for: messenger.split("_")[1], update_to: auth_id, updating: status });
}

/**
 *-------------------------------------------------------------
 * Check internet connection
 *-------------------------------------------------------------
 */
function checkInternet(state, selector) {
  let net_errs = 0;
  const messengerTitle = qs(".messenger-headTitle");
  switch (state) {
    case "connected":
      if (net_errs < 1) {
        if (messengerTitle) messengerTitle.textContent = messengerTitleDefault;
        if (selector) { selector.classList.add("successBG-rgba"); qsa("span", selector).forEach(function (s) { _hide(s); }); _show(selector); _show(qs(".ic-connected", selector)); }
        setTimeout(function () { _hide(qs(".internet-connection")); }, 3000);
      }
      break;
    case "connecting":
      if (messengerTitle) { const ic = qs(".ic-connecting"); messengerTitle.textContent = ic ? ic.textContent : ""; }
      if (selector) { selector.classList.remove("successBG-rgba"); qsa("span", selector).forEach(function (s) { _hide(s); }); _show(selector); _show(qs(".ic-connecting", selector)); }
      net_errs = 1;
      break;
    default:
      if (messengerTitle) { const ic = qs(".ic-noInternet"); messengerTitle.textContent = ic ? ic.textContent : ""; }
      if (selector) { selector.classList.remove("successBG-rgba"); qsa("span", selector).forEach(function (s) { _hide(s); }); _show(selector); _show(qs(".ic-noInternet", selector)); }
      net_errs = 1;
      break;
  }
}

/**
 *-------------------------------------------------------------
 * AJAX service functions
 *-------------------------------------------------------------
 */
function getContacts() {
  const list = qs(".listOfContacts");
  if (list) list.innerHTML = listItemLoading(4);
  _get(url + "/getContacts", { _token: access_token, messenger_id: messenger.split("_")[1] })
    .then(function (data) {
      // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
      if (list) list.innerHTML = _sanitizeHtml(data.contacts);
      const allMembers = qs(".all_members");
      if (allMembers) allMembers.innerHTML = _sanitizeHtml(data.allUsers);
      // PULL REQUEST END — Fim da alteração customizada
      cssMediaQueries();
    })
    .catch(function (err) { _logError("Server error for getting contacts", err); });
}

function updateContatctItem(user_id) {
  if (user_id != auth_id) {
    const listItem = qs('.listOfContacts .messenger-list-item[data-contact="' + user_id + '"]');
    _post(url + "/updateContacts", { _token: access_token, user_id: user_id, messenger_id: messenger.split("_")[1] })
      .then(function (data) {
        if (listItem) listItem.remove();
        const list = qs(".listOfContacts");
        if (list) list.insertAdjacentHTML("afterbegin", data.contactItem);
        const counter = qs(".custom_messanger_counter");
        if (counter) counter.textContent = data.messengerCount;
        cssMediaQueries();
      })
      .catch(function (err) { _logError("Server error for updating contact item", err); });
  }
}

function star(user_id) {
  if (messenger.split("_")[1] != auth_id) {
    _post(url + "/star", { _token: access_token, user_id: user_id })
      .then(function (data) {
        const fav = qs(".add-to-favorite");
        if (fav) data.status > 0 ? fav.classList.add("favorite") : fav.classList.remove("favorite");
      })
      .catch(function (err) { _logError("Server error for starring", err); });
  }
}

function getFavoritesList() {
  const favs = qs(".messenger-favorites");
  if (favs) favs.innerHTML = avatarLoading(4);
  _post(url + "/favorites", { _token: access_token })
    .then(function (data) {
      // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
      if (favs) favs.innerHTML = _sanitizeHtml(data.favorites);
      // PULL REQUEST END — Fim da alteração customizada
      cssMediaQueries();
    })
    .catch(function (err) { _logError("Server error for getting favorites list", err); });
}

function getSharedPhotos(user_id) {
  _post(url + "/shared", { _token: access_token, user_id: user_id })
    .then(function (data) {
      // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
      const list = qs(".shared-photos-list");
      if (list) list.innerHTML = _sanitizeHtml(data.shared);
      // PULL REQUEST END — Fim da alteração customizada
    })
    .catch(function (err) { _logError("Server error for getting shared photos", err); });
}

function messengerSearch(input) {
  const records = qs(".search-records");
  if (records) records.innerHTML = listItemLoading(4);
  _post(url + "/search", { _token: access_token, input: input })
    .then(function (data) {
      if (records) {
        const svg = qs("svg", records);
        if (svg) svg.remove();
        // PULL REQUEST START — Alteração customizada em arquivo vendor (correção de XSS)
        if (data.addData === "append") records.insertAdjacentHTML("beforeend", _sanitizeHtml(data.records));
        else records.innerHTML = _sanitizeHtml(data.records);
        // PULL REQUEST END — Fim da alteração customizada
      }
      cssMediaQueries();
    })
    .catch(function (err) { _logError("Server error for searching with messenger", err); });
}

function deleteConversation(id) {
  app_modal({ show: false, name: "delete" });
  app_modal({ show: true, name: "alert", buttons: false, body: loadingSVG("32px") });
  _post(url + "/deleteConversation", { _token: access_token, id: id })
    .then(function (data) {
      const item = qs('.listOfContacts .messenger-list-item[data-contact="' + id + '"]');
      if (item) item.remove();
      IDinfo(id, messenger.split("_")[0]);
      if (!data.deleted) console.error("Error occured!");
      app_modal({ show: false, name: "alert", buttons: true, body: "" });
    })
    .catch(function (err) { _logError("Server error for deleting conversation", err); });
}

function updateSettings() {
  const form = qs("#updateAvatar");
  const formData = new FormData(form);
  if (messengerColor) formData.append("messengerColor", messengerColor);
  if (dark_mode) formData.append("dark_mode", dark_mode);
  app_modal({ show: false, name: "settings" });
  app_modal({ show: true, name: "alert", buttons: false, body: loadingSVG("32px") });
  _post(url + "/updateSettings", formData)
    .then(function (data) {
      if (data.error) {
        app_modal({ show: true, name: "alert", buttons: true, body: data.msg });
      } else {
        app_modal({ show: false, name: "alert", buttons: true, body: "" });
        location.reload(true);
      }
    })
    .catch(function (err) { _logError("Server error for updating settings", err); });
}

function setActiveStatus(status, user_id) {
  _post(url + "/setActiveStatus", { _token: access_token, user_id: user_id, status: status })
    .then(function () {})
    .catch(function (err) { _logError("Server error for setting active status", err); });
}

/**
 *-------------------------------------------------------------
 * On DOM ready
 *-------------------------------------------------------------
 */
document.addEventListener("DOMContentLoaded", function () {
  getContacts();
  getFavoritesList();
  clearTimeout(typingTimeout);

  const localPusher = window.__appPusher;
  if (localPusher?.connection) {
    localPusher.connection.bind("state_change", function (states) {
      checkInternet(states.current, qs(".internet-connection"));
      channel && typeof channel.bind === "function" && channel.bind("pusher:subscription_succeeded", function () {
        IDinfo(messenger.split("_")[1], messenger.split("_")[0]);
      });
    });
  } else {
    console.warn("Pusher could not be connected!");
  }

  // Tabs
  qsa(".messenger-listView-tabs a").forEach(function (tab) {
    tab.addEventListener("click", function () {
      const dataView = this.getAttribute("data-view");
      qsa(".messenger-listView-tabs a").forEach(function (t) { t.classList.remove("active-tab"); });
      this.classList.add("active-tab");
      qsa(".messenger-tab").forEach(function (t) { _hide(t); });
      const target = qs('.messenger-tab[data-view="' + dataView + '"]');
      if (target) _show(target);
    });
  });

  // Set item active
  onBody("click", ".messenger-list-item", function () {
    qsa(".messenger-list-item").forEach(function (item) { item.classList.remove("m-list-active"); });
    this.classList.add("m-list-active");
  });

  // Show info side
  qsa(".messenger-infoView nav a, .show-infoSide").forEach(function (el) {
    el.addEventListener("click", function () { _toggle(qs(".messenger-infoView")); });
  });
  const infoNavA = qs(".messenger-infoView nav a");
  if (infoNavA) infoNavA.addEventListener("click", function () { _show(qs(".show-infoSide")); });
  const showInfoSide = qs(".show-infoSide");
  if (showInfoSide) showInfoSide.addEventListener("click", function () { _hide(this); });

  // Favorites drag
  hScroller(".messenger-favorites");

  // Click list item
  onBody("click", ".messenger-list-item", function () {
    const trAction = qs("tr[data-action]", this);
    if (trAction && trAction.getAttribute("data-action") === "1") _hide(qs(".messenger-listView"));
    const pId = qs("p[data-id]", this);
    if (pId) messenger = pId.getAttribute("data-id");
    IDinfo(messenger.split("_")[1], messenger.split("_")[0]);
  });

  // Click favorite item
  onBody("click", ".favorite-list-item", function () {
    const div = qs("div", this);
    if (div && div.getAttribute("data-action") === "1") _hide(qs(".messenger-listView"));
    const avatar = qs("div.avatar", this);
    if (avatar) messenger = "user_" + avatar.getAttribute("data-id");
    IDinfo(messenger.split("_")[1], messenger.split("_")[0]);
  });

  // List view buttons
  const listViewX = qs(".listView-x");
  if (listViewX) listViewX.addEventListener("click", function () { _hide(qs(".messenger-listView")); });
  const showListView = qs(".show-listView");
  if (showListView) showListView.addEventListener("click", function () { _show(qs(".messenger-listView")); });

  // Favorite button
  const favBtn = qs(".add-to-favorite");
  if (favBtn) favBtn.addEventListener("click", function () { star(messenger.split("_")[1]); });

  cssMediaQueries();

  // Message form submit
  const msgForm = qs("#message-form");
  if (msgForm) msgForm.addEventListener("submit", function (e) { e.preventDefault(); sendMessage(); });

  // Message input keyup (Enter to send)
  const mSend = qs("#message-form .m-send");
  if (mSend) {
    mSend.addEventListener("keyup", function (e) {
      if (e.which === 13 || e.keyCode === 13) {
        if (!e.shiftKey) { isTyping(false); sendMessage(); }
      }
    });

    // Typing indicator on keydown
    mSend.addEventListener("keydown", function () {
      if (typingNow < 1) {
        isTyping(true);
        typingNow = 1;
      }
      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(function () { isTyping(false); typingNow = 0; }, 1000);
    });
  }

  // Upload attachment change
  onBody("change", ".upload-attachment", function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    const sendCard = qs(".messenger-sendCard");
    reader.readAsDataURL(file);
    reader.addEventListener("loadstart", function () {
      const form = qs("#message-form");
      if (form) form.insertAdjacentHTML("beforebegin", loadingSVG());
    });
    reader.addEventListener("load", function (ev) {
      if (sendCard) {
        const svg = qs(".loadingSVG", sendCard);
        if (svg) svg.remove();
        const oldPreview = qs(".attachment-preview", sendCard);
        if (oldPreview) oldPreview.remove();
        if (!file.type.match("image.*")) {
          sendCard.insertAdjacentHTML("afterbegin", attachmentTemplate("file", file.name));
        } else {
          sendCard.insertAdjacentHTML("afterbegin", attachmentTemplate("image", file.name, ev.target.result));
        }
      }
    });
  });

  // Attachment cancel
  onBody("click", ".attachment-preview .cancel", function () { cancelAttachment(); });

  // Image modal
  onBody("click", ".chat-image", function () {
    const src = this.style.backgroundImage.split('"')[1] || "";
    const box = qs("#imageModalBox");
    if (box) _show(box);
    const boxSrc = qs("#imageModalBoxSrc");
    if (boxSrc) boxSrc.setAttribute("src", src);
  });
  const imgClose = qs(".imageModal-close");
  if (imgClose) imgClose.addEventListener("click", function () { _hide(qs("#imageModalBox")); });

  // Search
  const searchInput = qs(".messenger-search");
  if (searchInput) {
    searchInput.addEventListener("focus", function () {
      qsa(".messenger-tab").forEach(function (t) { _hide(t); });
      _show(qs('.messenger-tab[data-view="search"]'));
    });
    searchInput.addEventListener("keyup", function () {
      if (this.value.trim().length > 0) {
        this.dispatchEvent(new Event("focus"));
        messengerSearch(this.value);
      } else {
        qsa(".messenger-tab").forEach(function (t) { _hide(t); });
        const usersTab = qs('.messenger-listView-tabs a[data-view="users"]');
        if (usersTab) usersTab.click();
      }
    });
  }

  // Delete conversation
  const deleteBtn = qs(".messenger-infoView-btns .delete-conversation");
  if (deleteBtn) deleteBtn.addEventListener("click", function () { app_modal({ name: "delete" }); });

  const deleteModal = qs('.app-modal[data-name="delete"]');
  if (deleteModal) {
    const confirmDel = qs(".app-modal-footer .delete", deleteModal);
    if (confirmDel) confirmDel.addEventListener("click", function () {
      deleteConversation(messenger.split("_")[1]);
      app_modal({ show: false, name: "delete" });
    });
    const cancelDel = qs(".app-modal-footer .cancel", deleteModal);
    if (cancelDel) cancelDel.addEventListener("click", function () { app_modal({ show: false, name: "delete" }); });
  }

  // Settings
  const settingsBtn = qs(".settings-btn");
  if (settingsBtn) settingsBtn.addEventListener("click", function () { app_modal({ name: "settings" }); });
  const updateForm = qs("#updateAvatar");
  if (updateForm) updateForm.addEventListener("submit", function (e) { e.preventDefault(); updateSettings(); });
  const settingsModal = qs('.app-modal[data-name="settings"]');
  if (settingsModal) {
    const cancelSettings = qs(".app-modal-footer .cancel", settingsModal);
    if (cancelSettings) cancelSettings.addEventListener("click", function () {
      app_modal({ show: false, name: "settings" });
      cancelUpdatingAvatar();
    });
  }

  // Upload avatar
  onBody("change", ".upload-avatar", function (e) {
    if (defaultAvatarInSettings == null) {
      const preview = qs(".upload-avatar-preview");
      if (preview) defaultAvatarInSettings = preview.style.backgroundImage;
    }
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.addEventListener("loadstart", function () {
      const preview = qs(".upload-avatar-preview");
      if (preview) preview.insertAdjacentHTML("beforeend", loadingSVG("42px", "upload-avatar-loading"));
    });
    reader.addEventListener("load", function (ev) {
      const preview = qs(".upload-avatar-preview");
      if (preview) {
        const svg = qs(".loadingSVG", preview);
        if (svg) svg.remove();
        if (file.type.match("image.*")) {
          preview.style.backgroundImage = 'url("' + ev.target.result + '")';
        }
      }
    });
  });

  // Messenger color
  onBody("click", ".update-messengerColor a", function () {
    messengerColor = this.getAttribute("class").split(" ")[0];
    qsa(".update-messengerColor a").forEach(function (a) { a.classList.remove("m-color-active"); });
    this.classList.add("m-color-active");
  });

  // Dark mode switch
  onBody("click", ".dark-mode-switch", function () {
    if (this.getAttribute("data-mode") === "0") {
      this.setAttribute("data-mode", "1");
      this.classList.remove("far");
      this.classList.add("fas");
      dark_mode = "dark";
    } else {
      this.setAttribute("data-mode", "0");
      this.classList.remove("fas");
      this.classList.add("far");
      dark_mode = "light";
    }
  });
});

// PULL REQUEST END — Alteração customizada: remoção parcial de jQuery no Chatify
