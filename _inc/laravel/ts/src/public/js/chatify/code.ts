/**
 * @fileoverview TypeScript version of public/js/chatify/code.js
 * @generated from original JavaScript - manual review recommended
 * @module code
 */

/**
 *-------------------------------------------------------------
 * Global variables
 *-------------------------------------------------------------
 */
let messenger = "0";
const auth_id = $("meta[name=url]").attr("data-user");
const _route = $("meta[name=route]").attr("content");
const url = $("meta[name=url]").attr("content");
const access_token = $('meta[name="csrf-token"]').attr("content");
let typingTimeout: ReturnType<typeof setTimeout> | undefined;
let typingNow = 0;
let temporaryMsgId = 0;
let defaultAvatarInSettings: string | null = null;
let messengerColor: string | undefined;
let dark_mode: string | number | boolean | undefined;
const messagesContainer = $(".messenger-messagingView .m-body"),
  messengerTitleDefault = $(".messenger-headTitle").text(),
  messageInput = $("#message-form .m-send");

// console.log(auth_id);

/**
 *-------------------------------------------------------------
 * Global Templates
 *-------------------------------------------------------------
 */
// Loading svg
function loadingSVG(w_h = "25px", className: string | null = null): string {
  return `
    <svg class="loadingSVG ${String(className ?? "")}" xmlns="http://www.w3.org/2000/svg" width="${w_h}" height="${w_h}" viewBox="0 0 40 40" stroke="#2196f3">
      <g fill="none" fill-rule="evenodd">
        <g transform="translate(2 2)" stroke-width="3">
          <circle stroke-opacity=".1" cx="18" cy="18" r="18"></circle>
          <path d="M36 18c0-9.94-8.06-18-18-18" transform="rotate(349.311 18 18)">
              <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur=".8s" repeatCount="indefinite"></animateTransform>
          </path>
        </g>
      </g>
    </svg>
    `;
}

// loading placeholder for users list item
function listItemLoading(items: number): string {
  let template = "";
  for (let i = 0; i < items; i++) {
    template += `
        <div class="loadingPlaceholder">
          <div class="loadingPlaceholder-wrapper">
            <div class="loadingPlaceholder-body">
            <table class="loadingPlaceholder-header">
              <tr>
                <td style="width: 45px;"><div class="loadingPlaceholder-avatar"></div></td>
                <td>
                  <div class="loadingPlaceholder-name"></div>
                      <div class="loadingPlaceholder-date"></div>
                </td>
              </tr>
            </table>
            </div>
          </div>
      </div>
        `;
  }
  return template;
}

// loading placeholder for avatars
function avatarLoading(items: number): string {
  let template = "";
  for (let i = 0; i < items; i++) {
    template += `
        <div class="loadingPlaceholder">
        <div class="loadingPlaceholder-wrapper">
            <div class="loadingPlaceholder-body">
                <table class="loadingPlaceholder-header">
                    <tr>
                        <td style="width: 45px;">
                            <div class="loadingPlaceholder-avatar" style="margin: 2px;"></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        </div>
        `;
  }
  return template;
}

// While sending a message, show this temporary message card.
function sendigCard(message: string, id: string | number): string {
  return `
    <div class="message-card mc-sender" data-id="${id}">
        <p>${message}<sub><span class="far fa-clock"></span></sub></p>
    </div>
    `;
}

// upload image preview card.
function attachmentTemplate(
  fileType: string,
  fileName: string,
  imgURL: string | null = null,
): string {
  if (fileType != "image") {
    return `
        <div class="attachment-preview">
            <span class="fas fa-times cancel"></span>
            <p style="padding:0px 30px;"><span class="fas fa-file"></span> ${fileName}</p>
        </div>
        `;
  } else {
    return `
        <div class="attachment-preview">
            <span class="fas fa-times cancel"></span>
            <div class="image-file chat-image" style="background-image: url('${String(imgURL)}');"></div>
            <p><span class="fas fa-file-image"></span> ${fileName}</p>
        </div>
        `;
  }
}

// Active Status Circle
function activeStatusCircle(): string {
  return `<span class="activeStatus"></span>`;
}

/**
 *-------------------------------------------------------------
 * Css Media Queries [For responsive design]
 *-------------------------------------------------------------
 */
$(window as unknown as Element).on("resize", function (): void {
  cssMediaQueries();
});

function cssMediaQueries(): void {
  if (window.matchMedia("(min-width: 980px)").matches) {
    $(".messenger-listView").removeAttr("style");
  }
  const actionVal = window.matchMedia("(max-width: 980px)").matches ? "1" : "0";
  $("body")
    .find(".messenger-list-item")
    .find("tr[data-action]")
    .attr("data-action", actionVal);
  $("body")
    .find(".favorite-list-item")
    .find("div")
    .attr("data-action", actionVal);
}

/**
 *-------------------------------------------------------------
 * App Modal
 *-------------------------------------------------------------
 */
interface AppModalOptions {
  show?: boolean;
  name: string;
  data?: unknown;
  buttons?: boolean;
  header?: string | null;
  body?: string | null;
}

const app_modal = function ({
  show = true,
  name,
  data = 0,
  buttons = true,
  header = null,
  body = null,
}: AppModalOptions): void {
  const modal = $(`.app-modal[data-name=${name}]`),
    modalCard = $(`.app-modal-card[data-name=${name}]`);
  if (header) modal.find(".app-modal-header").html(header);
  if (body) modal.find(".app-modal-body").html(body);
  buttons
    ? modal.find(".app-modal-footer").show()
    : modal.find(".app-modal-footer").hide();
  if (show) {
    modal.show();
    modalCard.addClass("app-show-modal");
  } else {
    modal.hide();
    modalCard.removeClass("app-show-modal");
  }
  modalCard.attr("data-modal", (data as string | number | null) ?? null);
};

/**
 *-------------------------------------------------------------
 * Slide to bottom on [action] - e.g. [message received, sent, loaded]
 *-------------------------------------------------------------
 */
function scrollBottom(container: HTMLElement | JQuery<HTMLElement>): void {
  const jqContainer =
    container instanceof HTMLElement ? $(container) : container;
  if (jqContainer.length > 0) {
    jqContainer.stop().animate({
      scrollTop: jqContainer[0].scrollHeight,
    });
  }
}

/**
 *-------------------------------------------------------------
 * click and drag to scroll - function
 *-------------------------------------------------------------
 */
function hScroller(scroller: string): void {
  const slider = document.querySelector<HTMLElement>(scroller);
  if (!slider || slider.dataset.hscrollerBound) return;
  slider.dataset.hscrollerBound = "1";
  let isDown = false,
    startX = 0,
    scrollLeft = 0;
  for (const [evt, fn] of Object.entries({
    mousedown: (e: MouseEvent): void => {
      isDown = true;
      startX = e.pageX - slider.offsetLeft;
      scrollLeft = slider.scrollLeft;
    },
    mouseleave: (): void => {
      isDown = false;
    },
    mouseup: (): void => {
      isDown = false;
    },
    mousemove: (e: MouseEvent): void => {
      if (!isDown) return;
      e.preventDefault();
      slider.scrollLeft = scrollLeft - (e.pageX - slider.offsetLeft - startX);
    },
  }))
    slider.addEventListener(evt, fn as EventListener);
}

/**
 *-------------------------------------------------------------
 * Disable/enable message form fields, messaging container...
 * on load info or if needed elsewhere.
 *
 * Default : true
 *-------------------------------------------------------------
 */
function disableOnLoad(action = true): void {
  if (action) {
    // hide star button
    $(".add-to-favorite").hide();
    // hide send card
    $(".messenger-sendCard").hide();
    // add loading opacity to messages container
    messagesContainer.css("opacity", ".5");
    // disable message form fields
    messageInput.attr("readonly", "readonly");
    $("#message-form button").attr("disabled", "disabled");
    $(".upload-attachment").attr("disabled", "disabled");
  } else {
    // show star button
    if (messenger.split("_")[1] != auth_id) {
      $(".add-to-favorite").show();
    }
    // show send card
    $(".messenger-sendCard").show();
    // remove loading opacity to messages container
    messagesContainer.css("opacity", "1");
    // enable message form fields
    messageInput.removeAttr("readonly");
    $("#message-form button").removeAttr("disabled");
    $(".upload-attachment").removeAttr("disabled");
  }
}

/**
 *-------------------------------------------------------------
 * Error message card
 *-------------------------------------------------------------
 */
function errorMessageCard(id: string | number): void {
  messagesContainer.find(`.message-card[data-id=${id}]`).addClass("mc-error");
  messagesContainer
    .find(`.message-card[data-id=${id}]`)
    .find("svg.loadingSVG")
    .remove();
  messagesContainer
    .find(`.message-card[data-id=${id}] p`)
    .prepend('<span class="fas fa-exclamation-triangle"></span>');
}

/**
 *-------------------------------------------------------------
 * Fetch id data (user/group) and update the view
 *-------------------------------------------------------------
 */
function IDinfo(id: string | number, type: string): void {
  // clear temporary message id
  temporaryMsgId = 0;
  // clear typing now
  typingNow = 0;
  // show loading bar
  // NProgress.start();
  // disable mess
  // age form
  disableOnLoad();
  if (messenger !== "0") {
    // get shared photos
    getSharedPhotos(String(id));
    // Get info
    $.ajax({
      url: url + "/idInfo",
      method: "POST",
      data: { _token: access_token, id: id, type: type },
      dataType: "JSON",
      success: (data: Record<string, unknown>) => {
        // avatar photo
        $(".messenger-infoView")
          .find(".avatar")
          .css("background-image", `url("${String(data.user_avatar)}")`);
        $(".header-avatar").css(
          "background-image",
          `url("${String(data.user_avatar)}")`,
        );
        // Show shared and actions
        $(".messenger-infoView-btns .delete-conversation").show();
        $(".messenger-infoView-shared").show();
        // fetch messages
        fetchMessages(id, type);
        // focus on messaging input
        messageInput.focus();
        // update info in view
        const fetchData = data.fetch as { name: string };
        $(".messenger-infoView .info-name").html(fetchData.name);
        $(".m-header-messaging .user-name").html(fetchData.name);
        // Star status
        (data.favorite as number) > 0
          ? $(".add-to-favorite").addClass("favorite")
          : $(".add-to-favorite").removeClass("favorite");
        // form reset and focus
        $("#message-form").trigger("reset");
        cancelAttachment();
        messageInput.focus();
      },
      error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
        console.error("Server error for getting id info");
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        ) {
          console.error("Server error for getting id info:", {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            responseText: jqXHR.responseText,
            textStatus: textStatus,
            errorThrown: errorThrown,
          });
        }
      },
    });
  } else {
    // remove loading bar
    // NProgress.done();
    // NProgress.remove();
  }
}

/**
 *-------------------------------------------------------------
 * Send message function
 *-------------------------------------------------------------
 */
function sendMessage(): void {
  temporaryMsgId += 1;
  const tempID = `temp_${temporaryMsgId}`;
  const hasFile = Boolean($(".upload-attachment").val());
  const messageVal = String(messageInput.val() ?? "").trim();
  if (messageVal.length > 0 || hasFile) {
    const formEl = $("#message-form")[0] as HTMLFormElement | undefined;
    if (!formEl) return;
    const formData = new FormData(formEl);
    const messengerStr = String(messenger);
    formData.append("id", messengerStr.split("_")[1] ?? "");
    formData.append("type", messengerStr.split("_")[0] ?? "");
    formData.append("temporaryMsgId", tempID);
    formData.append("_token", access_token ?? "");
    $.ajax({
      url: $("#message-form").attr("action"),
      method: "POST",
      data: formData,
      dataType: "JSON",
      processData: false,
      contentType: false,
      beforeSend: (): void => {
        // remove message hint
        $(".message-hint").remove();
        // append message
        hasFile
          ? messagesContainer
              .find(".messages")
              .append(
                sendigCard(
                  `${String(messageInput.val())}\n${loadingSVG("28px")}`,
                  tempID,
                ),
              )
          : messagesContainer
              .find(".messages")
              .append(sendigCard(String(messageInput.val() ?? ""), tempID));
        // scroll to bottom
        scrollBottom(messagesContainer);
        messageInput.css({ height: "42px" });
        // form reset and focus
        $("#message-form").trigger("reset");
        cancelAttachment();
        messageInput.focus();
      },
      success: (data: Record<string, unknown>) => {
        // console.log(data.tempID);
        if ((data.error as number) > 0) {
          // message card error status
          errorMessageCard(tempID);
          // console.error(data.error_msg);
        } else {
          // update contact item
          updateContatctItem(messenger.split("_")[1]);
          messagesContainer.find('.mc-sender[data-id="sending"]').remove();
          // get message before the sending one [temporary]
          messagesContainer
            .find(`.message-card[data-id=${String(data.tempID)}]`)
            .before(data.message as string);
          // delete the temporary one
          messagesContainer
            .find(`.message-card[data-id=${String(data.tempID)}]`)
            .remove();
          // scroll to bottom
          scrollBottom(messagesContainer);
          // send contact item updates
          sendContactItemUpdates(true);
        }
      },
      error: (): void => {
        // message card error status
        errorMessageCard(tempID);
        // error log
        // console.error('Failed sending the message! Please, check your server response');
      },
    });
  }
  return;
}

/**
 *-------------------------------------------------------------
 * Fetch messages from database
 *-------------------------------------------------------------
 */
function fetchMessages(id: string | number, type: string): void {
  if (messenger !== "0") {
    $.ajax({
      url: url + "/fetchMessages",
      method: "POST",
      data: { _token: access_token, id: id, type: type },
      dataType: "JSON",
      success: (data: Record<string, unknown>) => {
        // Enable message form if messenger not = 0; means if data is valid
        if (messenger !== "0") {
          disableOnLoad(false);
        }
        messagesContainer.find(".messages").html(data.messages as string);
        // scroll to bottom
        scrollBottom(messagesContainer);
        // remove loading bar
        // NProgress.done();
        // NProgress.remove();

        // trigger seen event
        makeSeen(true);
      },
      error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
        console.error("Failed to fetch messages");
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        ) {
          console.error("Failed to fetch messages:", {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            responseText: jqXHR.responseText,
            textStatus: textStatus,
            errorThrown: errorThrown,
          });
        }
      },
    });
  }
}

/**
 *-------------------------------------------------------------
 * Cancel file attached in the message.
 *-------------------------------------------------------------
 */
function cancelAttachment(): void {
  $(".messenger-sendCard").find(".attachment-preview").remove();
  $(".upload-attachment").replaceWith(
    $(".upload-attachment").val("").clone(true),
  );
}

/**
 *-------------------------------------------------------------
 * Cancel updating avatar in settings
 *-------------------------------------------------------------
 */
function cancelUpdatingAvatar(): void {
  $(".upload-avatar-preview").css(
    "background-image",
    defaultAvatarInSettings ?? "",
  );
  $(".upload-avatar").replaceWith($(".upload-avatar").val("").clone(true));
}

/**
 *-------------------------------------------------------------
 * Pusher channels and event listening..
 *-------------------------------------------------------------
 */

// subscribe to the channel
interface PusherChannel {
  bind: (
    event: string,
    callback: (data: Record<string, unknown>) => void,
  ) => void;
  trigger: (event: string, data: Record<string, unknown>) => void;
}
const pusher = (
  window as unknown as {
    __appPusher?: { subscribe: (name: string) => PusherChannel | null };
  }
).__appPusher;
const channel = pusher?.subscribe("private-chatify");

if (channel && typeof channel.bind === "function") {
  // Listen to messages, and append if data received
  channel.bind("messaging", function (data: Record<string, unknown>) {
    // console.info(data.from_id+' - '+data.to_id+'\n'+auth_id+' - '+messenger);
    if (data.from_id == messenger.split("_")[1] && data.to_id == auth_id) {
      // remove message hint
      $(".message-hint").remove();
      // append message
      messagesContainer.find(".messages").append(data.message as string);
      // scroll to bottom
      scrollBottom(messagesContainer);
      // trigger seen event
      makeSeen(true);
      // remove unseen counter for the user from the contacts list
      $(`.messenger-list-item[data-contact=${messenger.split("_")[1]}]`)
        .find("tr>td>b")
        .remove();
    }
  });

  // listen to typing indicator
  channel.bind("client-typing", function (data: Record<string, unknown>) {
    if (data.from_id == messenger.split("_")[1] && data.to_id == auth_id) {
      data.typing == true
        ? messagesContainer.find(".typing-indicator").show()
        : messagesContainer.find(".typing-indicator").hide();
    }
    // scroll to bottom
    scrollBottom(messagesContainer);
  });

  // listen to seen event
  channel.bind("client-seen", function (data: Record<string, unknown>) {
    if (data.from_id == messenger.split("_")[1] && data.to_id == auth_id) {
      if (data.seen == true) {
        $(".message-time")
          .find(".fa-check")
          .before('<span class="fas fa-check-double seen"></span> ');
        $(".message-time").find(".fa-check").remove();
        // console.info('[seen] triggered!');
      } else {
        // console.error('[seen] event not triggered!');
      }
    }
  });

  // listen to contact item updates event
  channel.bind("client-contactItem", function (data: Record<string, unknown>) {
    if (data.update_for == auth_id) {
      data.updating == true
        ? updateContatctItem(data.update_to as string)
        : /*console.error('[Contact Item updates] Updating failed!')*/ "";
    }
  });
} else console.warn(`Pusher channel "private-chatify" not found!`);

// -------------------------------------
// presence channel [User Active Status]
const activeStatusChannel = pusher?.subscribe("presence-activeStatus");

if (activeStatusChannel && typeof activeStatusChannel.bind === "function") {
  activeStatusChannel.bind(
    "pusher:member_added",
    function (member: { id: string }) {
      setActiveStatus(true, member.id);
      const $memberItem = $(`.messenger-list-item[data-contact=${member.id}]`);
      $memberItem.find(".activeStatus").remove();
      $memberItem.find(".avatar").before(activeStatusCircle());
    },
  );

  // Leaved
  activeStatusChannel.bind(
    "pusher:member_removed",
    function (member: { id: string }) {
      setActiveStatus(false, member.id);
      $(`.messenger-list-item[data-contact=${member.id}]`)
        .find(".activeStatus")
        .remove();
    },
  );
} else console.warn(`Pusher channel "presence-activeStatus" not found!`);
// Joined

/**
 *-------------------------------------------------------------
 * Trigger typing event
 *-------------------------------------------------------------
 */
function isTyping(status: boolean): void {
  return channel?.trigger("client-typing", {
    from_id: auth_id, // Me
    to_id: messenger.split("_")[1], // Messenger
    typing: status,
  });
}

/**
 *-------------------------------------------------------------
 * Trigger seen event
 *-------------------------------------------------------------
 */
function makeSeen(status: boolean): void {
  const mId = messenger.split("_")[1];
  // remove unseen counter for the user from the contacts list
  $(`.messenger-list-item[data-contact=${mId}]`).find("tr>td>b").remove();
  // seen
  $.ajax({
    url: url + "/makeSeen",
    method: "POST",
    data: { _token: access_token, id: mId },
    dataType: "JSON",
    success: (data: Record<string, unknown>) => {
      $(".custom_messanger_counter").text(data.messengerCount as string);
    },
  });
  return channel?.trigger("client-seen", {
    from_id: auth_id, // Me
    to_id: mId, // Messenger
    seen: status,
  });
}

/**
 *-------------------------------------------------------------
 * Trigger contact item updates
 *-------------------------------------------------------------
 */
function sendContactItemUpdates(status: boolean): void {
  return channel?.trigger("client-contactItem", {
    update_for: messenger.split("_")[1], // Messenger
    update_to: auth_id, // Me
    updating: status,
  });
}

/**
 *-------------------------------------------------------------
 * Check internet connection using pusher states
 *-------------------------------------------------------------
 */
function checkInternet(state: string, selector: JQuery<HTMLElement>): void {
  let net_errs = 0;
  const messengerTitle = $(".messenger-headTitle");
  switch (state) {
    case "connected":
      if (net_errs < 1) {
        messengerTitle.text(messengerTitleDefault);
        selector.addClass("successBG-rgba");
        selector.find("span").hide();
        selector.slideDown("fast", function (): void {
          selector.find(".ic-connected").show();
        });
        setTimeout(function (): void {
          $(".internet-connection").slideUp("fast");
        }, 3000);
      }
      break;
    case "connecting":
      messengerTitle.text($(".ic-connecting").text());
      selector.removeClass("successBG-rgba");
      selector.find("span").hide();
      selector.slideDown("fast", function (): void {
        selector.find(".ic-connecting").show();
      });
      net_errs = 1;
      break;
    // Not connected
    default:
      messengerTitle.text($(".ic-noInternet").text());
      selector.removeClass("successBG-rgba");
      selector.find("span").hide();
      selector.slideDown("fast", function (): void {
        selector.find(".ic-noInternet").show();
      });
      net_errs = 1;
      break;
  }
}

/**
 *-------------------------------------------------------------
 * Get contacts
 *-------------------------------------------------------------
 */
function getContacts(): void {
  $(".listOfContacts").html(listItemLoading(4));
  $.ajax({
    url: url + "/getContacts",
    method: "GET",
    data: { _token: access_token, messenger_id: messenger.split("_")[1] },
    dataType: "JSON",
    success: (data: Record<string, unknown>) => {
      $(".listOfContacts").html(data.contacts as string);
      $(".all_members").html(data.allUsers as string);
      // update data-action required with [responsive design]
      cssMediaQueries();
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for getting contacts");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for getting contacts:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

/**
 *-------------------------------------------------------------
 * Update contact item
 *-------------------------------------------------------------
 */
function updateContatctItem(user_id: string): void {
  if (user_id != auth_id) {
    const listItem = $("body")
      .find(".listOfContacts")
      .find(`.messenger-list-item[data-contact=${user_id}]`);
    $.ajax({
      url: url + "/updateContacts",
      method: "POST",
      data: {
        _token: access_token,
        user_id: user_id,
        messenger_id: messenger.split("_")[1],
      },
      dataType: "JSON",
      success: (data: Record<string, unknown>) => {
        listItem.remove();
        $(".listOfContacts").prepend(data.contactItem as string);
        $(".custom_messanger_counter").text(data.messengerCount as string);
        // update data-action required with [responsive design]
        cssMediaQueries();
      },
      error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
        console.error("Server error for updating contact item");
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        ) {
          console.error("Server error for updating contact item:", {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            responseText: jqXHR.responseText,
            textStatus: textStatus,
            errorThrown: errorThrown,
          });
        }
      },
    });
  }
}

/**
 *-------------------------------------------------------------
 * Star
 *-------------------------------------------------------------
 */

function star(user_id: string): void {
  // console.log(messenger);
  if (messenger.split("_")[1] != auth_id) {
    $.ajax({
      url: url + "/star",
      method: "POST",
      data: { _token: access_token, user_id: user_id },
      dataType: "JSON",
      success: (data: Record<string, unknown>) => {
        (data.status as number) > 0
          ? $(".add-to-favorite").addClass("favorite")
          : $(".add-to-favorite").removeClass("favorite");
      },
      error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
        console.error("Server error for starring");
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        ) {
          console.error("Server error for starring:", {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            responseText: jqXHR.responseText,
            textStatus: textStatus,
            errorThrown: errorThrown,
          });
        }
      },
    });
  }
}

/**
 *-------------------------------------------------------------
 * Get favorite list
 *-------------------------------------------------------------
 */
function getFavoritesList(): void {
  $(".messenger-favorites").html(avatarLoading(4));
  $.ajax({
    url: url + "/favorites",
    method: "POST",
    data: { _token: access_token },
    dataType: "JSON",
    success: (data: Record<string, unknown>) => {
      $(".messenger-favorites").html(data.favorites as string);
      // update data-action required with [responsive design]
      cssMediaQueries();
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for getting favorites list");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for getting favorites list:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

/**
 *-------------------------------------------------------------
 * Get shared photos
 *-------------------------------------------------------------
 */
function getSharedPhotos(user_id: string): void {
  $.ajax({
    url: url + "/shared",
    method: "POST",
    data: { _token: access_token, user_id: user_id },
    dataType: "JSON",
    success: (data: Record<string, unknown>) => {
      $(".shared-photos-list").html(data.shared as string);
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for getting shared photos");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for getting shared photos:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

/**
 *-------------------------------------------------------------
 * Search in messenger
 *-------------------------------------------------------------
 */
function messengerSearch(input: string): void {
  $.ajax({
    url: url + "/search",
    method: "POST",
    data: { _token: access_token, input: input },
    dataType: "JSON",
    beforeSend: (): void => {
      $(".search-records").html(listItemLoading(4));
    },
    success: (data: Record<string, unknown>) => {
      $(".search-records").find("svg").remove();
      (data.addData as string) == "append"
        ? $(".search-records").append(data.records as string)
        : $(".search-records").html(data.records as string);
      // update data-action required with [responsive design]
      cssMediaQueries();
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for searching with messenger");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for searching with messenger:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

/**
 *-------------------------------------------------------------
 * Delete Conversation
 *-------------------------------------------------------------
 */
function deleteConversation(id: string | number): void {
  $.ajax({
    url: url + "/deleteConversation",
    method: "POST",
    data: { _token: access_token, id: id },
    dataType: "JSON",
    beforeSend: (): void => {
      // hide delete modal
      app_modal({
        show: false,
        name: "delete",
      });
      // Show waiting alert modal
      app_modal({
        show: true,
        name: "alert",
        buttons: false,
        body: loadingSVG("32px"),
      });
    },
    success: (data: Record<string, unknown>) => {
      // delete contact from the list
      $(".listOfContacts")
        .find(`.messenger-list-item[data-contact=${id}]`)
        .remove();
      // refresh info
      IDinfo(id, messenger.split("_")[0]);

      data.deleted ? "" : console.error("Error occured!");

      // Hide waiting alert modal
      app_modal({
        show: false,
        name: "alert",
        buttons: true,
        body: "",
      });
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for deleting conversation");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for deleting conversation:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

function updateSettings(): void {
  const formData = new FormData($("#updateAvatar")[0] as HTMLFormElement);
  if (messengerColor) {
    formData.append("messengerColor", messengerColor);
  }
  // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
  if (dark_mode) {
    formData.append("dark_mode", String(dark_mode));
  }
  $.ajax({
    url: url + "/updateSettings",
    method: "POST",
    data: formData,
    dataType: "JSON",
    processData: false,
    contentType: false,
    beforeSend: (): void => {
      // close settings modal
      app_modal({
        show: false,
        name: "settings",
      });
      // Show waiting alert modal
      app_modal({
        show: true,
        name: "alert",
        buttons: false,
        body: loadingSVG("32px"),
      });
    },
    success: (data: Record<string, unknown>) => {
      if (data.error) {
        // Show error message in alert modal
        app_modal({
          show: true,
          name: "alert",
          buttons: true,
          body: (data.msg as string | null) ?? null,
        });
      } else {
        // Hide alert modal
        app_modal({
          show: false,
          name: "alert",
          buttons: true,
          body: "",
        });

        // reload the page
        location.reload();
      }
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for updating settings");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for updating settings:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

/**
 *-------------------------------------------------------------
 * Set Active status
 *-------------------------------------------------------------
 */
function setActiveStatus(status: boolean, user_id: string): void {
  $.ajax({
    url: url + "/setActiveStatus",
    method: "POST",
    data: { _token: access_token, user_id: user_id, status: status },
    dataType: "JSON",
    success: (_data: Record<string, unknown>) => {
      // Nothing to do
    },
    error: (jqXHR: JQueryXHR, textStatus: string, errorThrown: string) => {
      console.error("Server error for setting active status");
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      ) {
        console.error("Server error for setting active status:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          responseText: jqXHR.responseText,
          textStatus: textStatus,
          errorThrown: errorThrown,
        });
      }
    },
  });
}

/**
 *-------------------------------------------------------------
 * On DOM ready
 *-------------------------------------------------------------
 */
$(document).ready(function (): void {
  try {
    // get contacts list
    getContacts();

    // get contacts list
    getFavoritesList();

    // Clear typing timeout
    clearTimeout(typingTimeout);

    // NProgress configurations
    // NProgress.configure({showSpinner: false, minimum: 0.7, speed: 500});

    // make message input autosize.
    // autosize($('.m-send'));

    // check if pusher has access to the channel [Internet status]
    const pusher = (
      window as unknown as {
        __appPusher?: {
          connection?: {
            bind: (
              event: string,
              callback: (states: { current: string }) => void,
            ) => void;
          };
        };
      }
    ).__appPusher;
    pusher?.connection?.bind(
      "state_change",
      function (states: { current: string }) {
        const selector = $(".internet-connection");
        checkInternet(states.current, selector);
        // listening for pusher:subscription_succeeded
        channel &&
          typeof channel.bind === "function" &&
          channel.bind("pusher:subscription_succeeded", function (): void {
            // On connection state change [Updating] and get [info & msgs]
            IDinfo(messenger.split("_")[1], messenger.split("_")[0]);
          });
      },
    );
    if (!pusher?.connection) console.warn("Pusher could not be connected!");

    // tabs on click, show/hide...
    $(".messenger-listView-tabs a").on(
      "click",
      function (this: HTMLElement): void {
        const dataView = $(this).attr("data-view");
        $(".messenger-listView-tabs a").removeClass("active-tab");
        $(this).addClass("active-tab");
        $(".messenger-tab").hide();
        $(`.messenger-tab[data-view=${dataView}]`).show();
      },
    );

    // set item active on click
    $("body").on(
      "click",
      ".messenger-list-item",
      function (this: HTMLElement): void {
        $(".messenger-list-item").removeClass("m-list-active");
        $(this).addClass("m-list-active");
      },
    );

    // show info side button
    $(".messenger-infoView nav a , .show-infoSide").on(
      "click",
      function (): void {
        $(".messenger-infoView").toggle();
      },
    );

    // x button for info section to show the main button.
    $(".messenger-infoView nav a").on("click", function (): void {
      $(".show-infoSide").show();
    });

    // hide showing button for info section.
    $(".show-infoSide").on("click", function (this: HTMLElement): void {
      $(this).hide();
    });

    // make favorites card dragable on click to slide.
    hScroller(".messenger-favorites");

    // click action for list item [user/group]
    $("body").on(
      "click",
      ".messenger-list-item",
      function (this: HTMLElement): void {
        if ($(this).find("tr[data-action]").attr("data-action") == "1") {
          $(".messenger-listView").hide();
        }
        messenger = $(this).find("p[data-id]").attr("data-id") ?? "0";
        IDinfo(messenger.split("_")[1], messenger.split("_")[0]);
      },
    );

    // click action for favorite button
    $("body").on(
      "click",
      ".favorite-list-item",
      function (this: HTMLElement): void {
        if ($(this).find("div").attr("data-action") == "1") {
          $(".messenger-listView").hide();
        }
        messenger = `user_${String($(this).find("div.avatar").attr("data-id"))}`;
        IDinfo(messenger.split("_")[1], messenger.split("_")[0]);
      },
    );

    // list view buttons
    $(".listView-x").on("click", function (): void {
      $(".messenger-listView").hide();
    });
    $(".show-listView").on("click", function (): void {
      $(".messenger-listView").show();
    });

    // click action for [add to favorite] button.
    $(".add-to-favorite").on("click", function (): void {
      star(messenger.split("_")[1]);
    });

    // calling Css Media Queries
    cssMediaQueries();

    // message form on submit.
    $("#message-form").on("submit", (e: Event) => {
      e.preventDefault();
      sendMessage();
    });

    // message input on keyup [Enter to send, Enter+Shift for new line]
    $("#message-form .m-send").on("keyup", function (e) {
      const evt = e as unknown as KeyboardEvent;
      // if enter key pressed.
      if (evt.which == 13 || evt.keyCode == 13) {
        // if shift + enter key pressed, do nothing (new line).
        // if only enter key pressed, send message.
        if (!evt.shiftKey) {
          isTyping(false);
          sendMessage();
        }
      }
    });

    // On [upload attachment] input change, show a preview of the image/file.
    $("body").on("change", ".upload-attachment", e => {
      const target = e.target as HTMLInputElement;
      const file = target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      const sendCard = $(".messenger-sendCard");
      reader.readAsDataURL(file);
      reader.addEventListener("loadstart", (_e: Event) => {
        $("#message-form").before(loadingSVG());
      });
      reader.addEventListener("load", (e: Event) => {
        $(".messenger-sendCard").find(".loadingSVG").remove();
        if (!file.type.match("image.*")) {
          // if the file not image
          sendCard.find(".attachment-preview").remove(); // older one
          sendCard.prepend(attachmentTemplate("file", file.name));
        } else {
          // if the file is an image
          sendCard.find(".attachment-preview").remove(); // older one
          sendCard.prepend(
            attachmentTemplate(
              "image",
              file.name,
              (e.target as FileReader).result as string | null,
            ),
          );
        }
      });
    });

    // Attachment preview cancel button.
    $("body").on("click", ".attachment-preview .cancel", _e => {
      cancelAttachment();
    });

    // typing indicator on [input] keyDown
    $("#message-form .m-send").on("keydown", (): void => {
      if (typingNow < 1) {
        // Trigger typing
        const _triggered = isTyping(true);
        /*triggered ? console.info('[+] Triggered')
                  : console.error('[+] Not triggered');*/
        // Typing now
        typingNow = 1;
      }
      // Clear typing timeout
      clearTimeout(typingTimeout);
      // Typing timeout
      typingTimeout = setTimeout(function (): void {
        isTyping(false);
        /*triggered ? console.info('[-] Triggered')
                  : console.error('[-] Not triggered');*/
        // Clear typing now
        typingNow = 0;
      }, 1000);
    });

    // Image modal
    $("body").on("click", ".chat-image", function (this: HTMLElement): void {
      const src = $(this).css("background-image").split(/"/)[1];
      $("#imageModalBox").show();
      $("#imageModalBoxSrc").attr("src", src);
    });
    $(".imageModal-close").on("click", function (): void {
      $("#imageModalBox").hide();
    });

    // Search input on focus
    $(".messenger-search").on("focus", function (): void {
      $(".messenger-tab").hide();
      $('.messenger-tab[data-view="search"]').show();
    });
    // Search action on keyup
    $(".messenger-search").on("keyup", function (this: HTMLElement, _e: Event) {
      const val = String($(this).val() ?? "").trim();
      if (val.length > 0) {
        $(".messenger-search").trigger("focus");
        messengerSearch(val);
      } else {
        $(".messenger-tab").hide();
        $('.messenger-listView-tabs a[data-view="users"]').trigger("click");
      }
    });

    // Delete Conversation button
    $(".messenger-infoView-btns .delete-conversation").on(
      "click",
      function (): void {
        app_modal({
          name: "delete",
        });
      },
    );
    // delete modal [delete button]
    $(".app-modal[data-name=delete]")
      .find(".app-modal-footer .delete")
      .on("click", function (): void {
        deleteConversation(messenger.split("_")[1]);
        app_modal({
          show: false,
          name: "delete",
        });
      });
    // delete modal [cancel button]
    $(".app-modal[data-name=delete]")
      .find(".app-modal-footer .cancel")
      .on("click", function (): void {
        app_modal({
          show: false,
          name: "delete",
        });
      });

    // Settings button action to show settings modal
    $(".settings-btn").on("click", function (): void {
      app_modal({
        name: "settings",
      });
    });

    // on submit settings' form
    $("#updateAvatar").on("submit", function (e) {
      e.preventDefault();
      updateSettings();
    });
    // Settings modal [cancel button]
    $(".app-modal[data-name=settings]")
      .find(".app-modal-footer .cancel")
      .on("click", function (): void {
        app_modal({
          show: false,
          name: "settings",
        });
        cancelUpdatingAvatar();
      });
    // upload avatar on change
    $("body").on("change", ".upload-avatar", e => {
      // store the original avatar
      if (defaultAvatarInSettings == null) {
        defaultAvatarInSettings = $(".upload-avatar-preview").css(
          "background-image",
        );
      }
      const target = e.target as HTMLInputElement;
      const file = target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.addEventListener("loadstart", (_e: Event) => {
        $(".upload-avatar-preview").append(
          loadingSVG("42px", "upload-avatar-loading"),
        );
      });
      reader.addEventListener("load", (e: Event) => {
        $(".upload-avatar-preview").find(".loadingSVG").remove();
        if (!file.type.match("image.*")) {
          // if the file is not an image
          // console.error('File you selected is not an image!');
        } else {
          // if the file is an image
          $(".upload-avatar-preview").css(
            "background-image",
            `url("${String((e.target as FileReader).result ?? "")}")`,
          );
        }
      });
    });
    // change messenger color button
    $("body").on(
      "click",
      ".update-messengerColor a",
      function (this: HTMLElement): void {
        messengerColor = ($(this).attr("class") ?? "").split(" ")[0];
        $(".update-messengerColor a").removeClass("m-color-active");
        $(this).addClass("m-color-active");
      },
    );
    // Switch to Dark/Light mode
    $("body").on(
      "click",
      ".dark-mode-switch",
      function (this: HTMLElement): void {
        if ($(this).attr("data-mode") == "0") {
          $(this).attr("data-mode", "1");
          $(this).removeClass("far");
          $(this).addClass("fas");
          dark_mode = "dark";
        } else {
          $(this).attr("data-mode", "0");
          $(this).removeClass("fas");
          $(this).addClass("far");
          dark_mode = "light";
        }
      },
    );
  } catch (__moduleErr) {
    console.error("[code] failed to initialise:", __moduleErr);
  }
});
