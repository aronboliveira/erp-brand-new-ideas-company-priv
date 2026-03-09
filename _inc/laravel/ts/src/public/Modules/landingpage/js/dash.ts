/**
 * @fileoverview TypeScript version of public/Modules/landingpage/js/dash.js
 * @generated from original JavaScript - manual review recommended
 * @module dash
 */
// @ts-nocheck

/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, @typescript-eslint/prefer-for-of, no-console, no-var */
/* global feather, bootstrap */
"use strict";
let flg = "0";
document.addEventListener("DOMContentLoaded", function () {
  // feather icon start
  feather && typeof feather.replace === "function" && feather.replace();
  // feather icon end
  // remove pre-loader start
  setTimeout(function () {
    document.querySelector<HTMLElement>(".loader-bg").remove();
  }, 400);
  // remove pre-loader end
  if (
    !document
      .querySelector<HTMLElement>("body")
      .classList.contains("dash-horizontal")
  )
    addscroller();
  if (
    document
      .querySelector<HTMLElement>("body")
      .classList.contains("dash-horizontal")
  ) {
    if (
      document
        .querySelector(".dash-horizontal")
        .classList.contains("navbar-overlay")
    )
      addscroller();
  }
  const hamburger = document.querySelector<HTMLElement>(
    ".hamburger:not(.is-active)",
  );
  if (hamburger) {
    if (!hamburger.getAttribute("data-listener-bound-click")) {
      hamburger.setAttribute("data-listener-bound-click", "1");
      hamburger.addEventListener("click", function () {
        if (
          document
            .querySelector<HTMLElement>(".hamburger")
            .classList.contains("is-active")
        ) {
          document
            .querySelector<HTMLElement>(".hamburger")
            .classList.remove("is-active");
        } else {
          document
            .querySelector<HTMLElement>(".hamburger")
            .classList.add("is-active");
        }
      });
    }
  }
  // Menu overlay layout start
  const tempoverlaymenu = document.querySelector<HTMLElement>("#overlay-menu");
  if (tempoverlaymenu) {
    if (!tempoverlaymenu.getAttribute("data-listener-bound-click")) {
      tempoverlaymenu.setAttribute("data-listener-bound-click", "1");
      tempoverlaymenu.addEventListener("click", function () {
        menuclick();
        if (
          document
            .querySelector(".dash-sidebar")
            .classList.contains("dash-over-menu-active")
        ) {
          rmovermenu();
        } else {
          document
            .querySelector(".dash-sidebar")
            .classList.add("dash-over-menu-active");
          document
            .querySelector(".dash-sidebar")
            .insertAdjacentHTML(
              "beforeend",
              '<div class="dash-menu-overlay"></div>',
            );
          document
            .querySelector(".dash-menu-overlay")
            .addEventListener("click", function () {
              rmovermenu();
              document
                .querySelector<HTMLElement>(".hamburger")
                .classList.remove("is-active");
            });
        }
      });
    }
  }
  // Menu overlay layout end
  // vertical-nav-toggle start

  const verticalnavtoggle = document.querySelector<HTMLElement>(
    "#vertical-nav-toggle",
  );
  if (verticalnavtoggle) {
    if (!verticalnavtoggle.getAttribute("data-listener-bound-click")) {
      verticalnavtoggle.setAttribute("data-listener-bound-click", "1");
      verticalnavtoggle.addEventListener("click", function () {
        if (document.body.classList.contains("minimenu")) {
          document.body.classList.remove("minimenu");
          // menuclick();

          // ===============
          const elem = document.querySelectorAll(
            ".dash-navbar li:not(.dash-trigger) .dash-submenu",
          );
          for (let j = 0; j < elem.length; j++) {
            elem[j].style.display = "none";
          }
          // ===============
        } else {
          document.body.classList.add("minimenu");
          const tc = document.querySelectorAll(".dash-navbar li .dash-submenu");
          for (let t = 0; t < tc.length; t++) {
            const c = tc[t];
            c.removeAttribute("style");
          }
          collapseedge();
        }
      });
    }
  }
  // vertical-nav-toggle end
  // Menu collapse click start
  const mobilecollapsever =
    document.querySelector<HTMLElement>("#mobile-collapse");
  if (mobilecollapsever) {
    if (!mobilecollapsever.getAttribute("data-listener-bound-click")) {
      mobilecollapsever.setAttribute("data-listener-bound-click", "1");
      mobilecollapsever.addEventListener("click", function () {
        if (
          !document
            .querySelector<HTMLElement>("body")
            .classList.contains("dash-horizontal")
        ) {
          // menuclick();
        }
        const tempsdbr = document.querySelector<HTMLElement>(".dash-sidebar");
        if (tempsdbr) {
          if (
            document
              .querySelector(".dash-sidebar")
              .classList.contains("mob-sidebar-active")
          ) {
            rmmenu();
          } else {
            document
              .querySelector(".dash-sidebar")
              .classList.add("mob-sidebar-active");
            document
              .querySelector(".dash-sidebar")
              .insertAdjacentHTML(
                "beforeend",
                '<div class="dash-menu-overlay"></div>',
              );
            document
              .querySelector(".dash-menu-overlay")
              .addEventListener("click", function () {
                document
                  .querySelector(".hamburger")
                  .classList.remove("is-active");
                rmmenu();
              });
          }
        }
      });
    }
  }
  // Menu collapse click end

  // Menu collapse click start
  const mobilecollapse = document.querySelector<HTMLElement>(
    ".dash-horizontal #mobile-collapse",
  );
  if (mobilecollapse) {
    if (!mobilecollapse.getAttribute("data-listener-bound-click")) {
      mobilecollapse.setAttribute("data-listener-bound-click", "1");
      mobilecollapse.addEventListener("click", function () {
        if (
          document
            .querySelector(".topbar")
            .classList.contains("mob-sidebar-active")
        ) {
          rmmenu();
        } else {
          document
            .querySelector<HTMLElement>(".topbar")
            .classList.add("mob-sidebar-active");
          document
            .querySelector(".topbar")
            .insertAdjacentHTML(
              "beforeend",
              '<div class="dash-menu-overlay"></div>',
            );
          document
            .querySelector(".dash-menu-overlay")
            .addEventListener("click", function () {
              rmmenu();
              document
                .querySelector<HTMLElement>(".hamburger")
                .classList.remove("is-active");
            });
        }
      });
    }
  }
  // Menu collapse click end
  // mobile header click start
  // document
  //   .querySelector("#header-collapse")
  //   .addEventListener("click", function () {
  //     if (
  //       document
  //         .querySelector(".dash-header:not(.dash-mob-header)")
  //         .classList.contains("mob-header-active")
  //     ) {
  //       rmthead();
  //     } else {
  //       document
  //         .querySelector(".dash-header:not(.dash-mob-header)")
  //         .classList.add("mob-header-active");
  //       document
  //         .querySelector(".dash-header:not(.dash-mob-header)")
  //         .insertAdjacentHTML(
  //           "beforeend",
  //           '<div class="dash-md-overlay"></div>'
  //         );
  //       document
  //         .querySelector(".dash-md-overlay")
  //         .addEventListener("click", function () {
  //           rmthead();
  //         });
  //     }
  //   });
  // document
  //   .querySelector("#headerdrp-collapse")
  //   .addEventListener("click", function () {
  //     if (
  //       document
  //         .querySelector(".dash-header:not(.dash-mob-header) .dash-mob-drp")
  //         .classList.contains("mob-drp-active")
  //     ) {
  //       rmdrp();
  //     } else {
  //       document
  //         .querySelector(".dash-header:not(.dash-mob-header) .dash-mob-drp")
  //         .classList.add("mob-drp-active");
  //       document
  //         .querySelector(".dash-header:not(.dash-mob-header)")
  //         .insertAdjacentHTML(
  //           "beforeend",
  //           '<div class="dash-md-overlay"></div>'
  //         );
  //       document
  //         .querySelector(".dash-md-overlay")
  //         .addEventListener("click", function () {
  //           rmdrp();
  //         });
  //     }
  //   });
  // mobile header click end
  // Horizontal menu click js start
  const topbarlinklist = document.querySelector<HTMLElement>(
    ".dash-horizontal .topbar .dash-navbar>li>a",
  );
  if (topbarlinklist) {
    if (!topbarlinklist.getAttribute("data-listener-bound-click")) {
      topbarlinklist.setAttribute("data-listener-bound-click", "1");
      topbarlinklist.addEventListener("click", function (e) {
        const targetElement = e.target;
        setTimeout(function () {
          targetElement.parentNodes.children[1].removeAttribute("style");
        }, 1000);
      });
    }
  }
  // Horizontal menu click js end

  function formmat(e) {
    let temp = 0;
    try {
      temp = e.attr("placeholder").length;
    } catch (err) {
      temp = 0;
    }
    if (e.value.length > 0) {
      e.parentNode(".form-group").classList.add("fill");
    } else {
      e.parentNode(".form-group").classList.remove("fill");
    }
  }
  // Material form end
  if (
    document
      .querySelector<HTMLElement>("body")
      .classList.contains("dash-horizontal")
  )
    horizontalmobilemenuclick();
  if (
    document.querySelector<HTMLElement>("body").classList.contains("minimenu")
  )
    collapseedge();
  // notification scrollbar start
  if (document.querySelector<HTMLElement>(".drp-notification .noti-body")) {
    // var px = new PerfectScrollbar(".drp-notification .noti-body", {
    //   wheelSpeed: 0.5,
    //   swipeEasing: 0,
    //   suppressScrollX: !0,
    //   wheelPropagation: 1,
    //   minScrollbarLength: 40,
    // });
  }
  // notification scrollbar end
});

function horizontalmobilemenuclick() {
  const vw = window.innerWidth,
    pcnavlinklist = document.querySelector<HTMLElement>(".dash-navbar li");
  if (pcnavlinklist) pcnavlinklist.removeEventListener("click", function () {});

  const pclinkclick = document.querySelectorAll(
    ".dash-navbar > li:not(.dash-caption)",
  );
  for (let i = 0; i < pclinkclick.length; i++) {
    pclinkclick[i].addEventListener("click", function (event) {
      let targetElement = event.target;
      if (targetElement.tagName == "SPAN")
        targetElement = targetElement.parentNode;
      targetElement.parentNode.children[1].removeAttribute("style");
      if (targetElement.parentNode.classList.contains("dash-trigger")) {
        targetElement.parentNode.classList.remove("dash-trigger");
      } else {
        const tc = document.querySelectorAll("li.dash-trigger");
        for (let t = 0; t < tc.length; t++) {
          const c = tc[t];
          c.classList.remove("dash-trigger");
        }
        targetElement.parentNode.classList.add("dash-trigger");
      }
    });
  }
  const pcsublinkclick = document.querySelectorAll(
    ".dash-navbar > li:not(.dash-caption) > .dash-submenu > li",
  );
  for (var n = 0; n < pcsublinkclick.length; n++) {
    pcsublinkclick[n].addEventListener("click", function (event) {
      event.stopPropagation();
      let targetElement = event.target;
      if (targetElement.tagName == "SPAN")
        targetElement = targetElement.parentNode;
      targetElement.parentNode.children[1].removeAttribute("style");
      if (targetElement.parentNode.classList.contains("dash-trigger")) {
        targetElement.parentNode.classList.remove("dash-trigger");
      } else {
        const tc = document.querySelectorAll(".dash-submenu li.dash-trigger");
        for (let t = 0; t < tc.length; t++) {
          const c = tc[t];
          c.classList.remove("dash-trigger");
        }
        targetElement.parentNode.classList.add("dash-trigger");
      }
    });
  }
  const pcsubchildlinkclick = document.querySelectorAll(
    ".dash-navbar > li:not(.dash-caption) > .dash-submenu >  li > .dash-submenu >  li",
  );
  for (var n = 0; n < pcsubchildlinkclick.length; n++) {
    pcsubchildlinkclick[n].addEventListener("click", function (event) {
      event.stopPropagation();
      let targetElement = event.target;
      if (targetElement.tagName == "SPAN")
        targetElement = targetElement.parentNode;
      targetElement.parentNode.children[1].removeAttribute("style");
      if (targetElement.parentNode.classList.contains("dash-trigger")) {
        targetElement.parentNode.classList.remove("dash-trigger");
      } else {
        const tc = document.querySelectorAll(
          ".dash-submenu .dash-submenu li.dash-trigger",
        );
        for (let t = 0; t < tc.length; t++) {
          const c = tc[t];
          c.classList.remove("dash-trigger");
        }
        targetElement.parentNode.classList.add("dash-trigger");
      }
    });
  }
}

// Menu click start
function addscroller() {
  rmmini();
  menuclick();
  // Menu scrollbar start
  if (document.querySelector<HTMLElement>(".navbar-content")) {
    const px = new SimpleBar(
      document.querySelector<HTMLElement>(".navbar-content"),
      {
        autoHide: true,
      },
    );
    // var px = new PerfectScrollbar(".navbar-content", {
    //   wheelSpeed: 0.5,
    //   swipeEasing: 0,
    //   suppressScrollX: !0,
    //   wheelPropagation: 1,
    //   minScrollbarLength: 40,
    // });
  }
  // Menu scrollbar end
}
// Menu click start
function menuclick() {
  const vw = window.innerWidth;
  var elem = document.querySelectorAll(".dash-navbar li");
  for (var j = 0; j < elem.length; j++) {
    elem[j].removeEventListener("click", function () {});
  }

  if (
    !document.querySelector<HTMLElement>("body").classList.contains("minimenu")
  ) {
    var elem = document.querySelectorAll(
      ".dash-navbar li:not(.dash-trigger) .dash-submenu",
    );
    for (var j = 0; j < elem.length; j++) {
      elem[j].style.display = "none";
    }
    const pclinkclick = document.querySelectorAll(
      ".dash-navbar > li:not(.dash-caption)",
    );
    for (var i = 0; i < pclinkclick.length; i++) {
      pclinkclick[i].addEventListener("click", function (event) {
        event.stopPropagation();
        let targetElement = event.target;
        if (targetElement.tagName == "SPAN")
          targetElement = targetElement.parentNode;
        if (targetElement.parentNode.classList.contains("dash-trigger")) {
          targetElement.parentNode.classList.remove("dash-trigger");
          // targetElement.parentNode.children[1].style.display = "none";
          slideUp(targetElement.parentNode.children[1], 200);
        } else {
          const tc = document.querySelectorAll("li.dash-trigger");
          for (let t = 0; t < tc.length; t++) {
            const c = tc[t];
            c.classList.remove("dash-trigger");
            slideUp(c.children[1], 200);
          }
          targetElement.parentNode.classList.add("dash-trigger");
          const tmp = targetElement.children[1];
          if (tmp) slideDown(targetElement.parentNode.children[1], 200);
        }
      });
    }
    const pcsublinkclick = document.querySelectorAll(
      ".dash-navbar > li:not(.dash-caption) li",
    );
    for (var i = 0; i < pcsublinkclick.length; i++) {
      pcsublinkclick[i].addEventListener("click", function (event) {
        let targetElement = event.target;
        if (targetElement.tagName == "SPAN")
          targetElement = targetElement.parentNode;
        event.stopPropagation();
        if (targetElement.parentNode.classList.contains("dash-trigger")) {
          targetElement.parentNode.classList.remove("dash-trigger");
          slideUp(targetElement.parentNode.children[1], 200);
        } else {
          const tc = targetElement.parentNode.parentNode.children;
          for (let t = 0; t < tc.length; t++) {
            let c = tc[t];
            c.classList.remove("dash-trigger");
            if (c.tagName == "LI") c = c.children[0];
            if (c.parentNode.classList.contains("dash-hasmenu"))
              slideUp(c.parentNode.children[1], 200);
          }
          targetElement.parentNode.classList.add("dash-trigger");
          const tmp = targetElement.parentNode.children[1];
          if (tmp) {
            tmp.removeAttribute("style");
            slideDown(tmp, 200);
          }
        }
      });
    }
  }
}

function rmdrp() {
  document
    .querySelector(".dash-header:not(.dash-mob-header) .dash-mob-drp")
    .classList.remove("mob-drp-active");
  document
    .querySelector(".dash-header:not(.dash-mob-header) .dash-md-overlay")
    .remove();
}

function rmthead() {
  document
    .querySelector(".dash-header:not(.dash-mob-header)")
    .classList.remove("mob-header-active");
  document
    .querySelector(".dash-header:not(.dash-mob-header) .dash-md-overlay")
    .remove();
}

function rmmenu() {
  const tempov = document.querySelector<HTMLElement>(".dash-sidebar");
  if (tempov)
    document
      .querySelector(".dash-sidebar")
      .classList.remove("mob-sidebar-active");
  if (document.querySelector<HTMLElement>(".topbar"))
    document
      .querySelector<HTMLElement>(".topbar")
      .classList.remove("mob-sidebar-active");

  document
    .querySelector<HTMLElement>(".dash-sidebar .dash-menu-overlay")
    .remove();
  document.querySelector<HTMLElement>(".topbar .dash-menu-overlay").remove();
}

function rmovermenu() {
  document
    .querySelector(".dash-sidebar")
    .classList.remove("dash-over-menu-active");
  if (document.querySelector<HTMLElement>(".topbar"))
    document
      .querySelector<HTMLElement>(".topbar")
      .classList.remove("mob-sidebar-active");
  document
    .querySelector<HTMLElement>(".dash-sidebar .dash-menu-overlay")
    .remove();
  document.querySelector<HTMLElement>(".topbar .dash-menu-overlay").remove();
}

function rmactive() {
  document
    .querySelector(".dash-sidebar .dash-navbar li")
    .classList.remove("active");
  document
    .querySelector(".dash-sidebar .dash-navbar li")
    .classList.remove("dash-trigger");
  document
    .querySelector<HTMLElement>(".topbar .dropdown")
    .classList.remove("show");
  document
    .querySelector<HTMLElement>(".topbar .dropdown-menu")
    .classList.remove("show");
  document
    .querySelector<HTMLElement>(".dash-sidebar .dash-menu-overlay")
    .remove();
  document.querySelector<HTMLElement>(".topbar .dash-menu-overlay").remove();
}

function rmmini() {
  // var vw = document.querySelector(window)[0].innerWidth;
  const vw = window.innerWidth;
  if (vw <= 1024) {
    if (
      document.querySelector<HTMLElement>("body").classList.contains("minimenu")
    ) {
      document.querySelector<HTMLElement>("body").classList.remove("minimenu");
      flg = "1";
      2;
    }
  } else {
    if (vw > 1024) {
      if (flg == "1") {
        document.querySelector<HTMLElement>("body").classList.add("minimenu");
        flg = "0";
      }
    }
  }
}
const emailmorelink = document.querySelector<HTMLElement>(".email-more-link");
if (emailmorelink)
  if (!emailmorelink.getAttribute("data-listener-bound-click")) {
    emailmorelink.setAttribute("data-listener-bound-click", "1");
    emailmorelink.addEventListener("click", function (e) {
      document.querySelector(this).children("span").slideToggle(1);
    });
  }

// Menu click end
window.addEventListener("resize", function () {
  if (
    !document
      .querySelector<HTMLElement>("body")
      .classList.contains("dash-horizontal")
  )
    rmmini();
  // menuclick();
  if (
    document
      .querySelector<HTMLElement>("body")
      .classList.contains("dash-horizontal")
  )
    rmactive();
});

window.addEventListener("load", function () {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
  const toastElList = [].slice.call(document.querySelectorAll(".toast"));
  const toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl);
  });
});
// active menu item list start
const elem = document.querySelectorAll(".dash-sidebar .dash-navbar a");
for (let l = 0; l < elem.length; l++) {
  const pageUrl = window.location.href.split(/[?#]/)[0];
  if (elem[l].href == pageUrl && elem[l].getAttribute("href") != "") {
    elem[l].parentNode.classList.add("active");
    scrolltargetmenu(elem[l].parentNode);
    elem[l].parentNode.parentNode.parentNode.classList.add("active");
    elem[l].parentNode.parentNode.parentNode.classList.add("dash-trigger");
    elem[l].parentNode.parentNode.style.display = "block";

    elem[
      l
    ].parentNode.parentNode.parentNode.parentNode.parentNode.classList.add(
      "active",
    );
    elem[
      l
    ].parentNode.parentNode.parentNode.parentNode.parentNode.classList.add(
      "dash-trigger",
    );
    elem[l].parentNode.parentNode.parentNode.parentNode.style.display = "block";

    // elem[i].parentNode('li').parentNode().parentNode('.sidelink').classList.add("active");
    // elem[i].parentNodes('.dash-tabcontent').classList.add('active');
    if (document.body.classList.contains("tab-layout")) {
      const temp = document
        .querySelector(".dash-tabcontent.active")
        .getAttribute("data-value");
      document
        .querySelector(".tab-sidemenu > ul > li")
        .classList.remove("active");
      document
        .querySelector('.tab-sidemenu > ul > li > a[data-cont="' + temp + '"]')
        .parentNode.classList.add("active");
    }
  }
}
// scroll to active menu
function scrolltargetmenu(value) {
  document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector<HTMLElement>(".navbar-content")) {
      const elm = value,
        off = elm.getBoundingClientRect(),
        t = off.top;
      if (t > 300)
        document.querySelector<HTMLElement>(".navbar-content").scrollTop =
          t - 300;
    }
  });
}

// Menu click for tab Layout start
const tablayclick = document.querySelector<HTMLElement>(
  ".tab-sidemenu > ul > li",
);
if (tablayclick) {
  console.log("condition");
  var tc = document.querySelectorAll(".tab-sidemenu > ul > li");
  for (var t = 0; t < tc.length; t++) {
    var c = tc[t];
    c.addEventListener("click", function (event) {
      let targetElement = event.target;
      if (targetElement.tagName == "A")
        targetElement = targetElement.parentNode;
      if (targetElement.tagName == "I")
        targetElement = targetElement.parentNode.parentNode;
      const tempcont = targetElement.children[0].getAttribute("data-cont");
      document
        .querySelector(".navbar-content .dash-tabcontent.active")
        .classList.remove("active");
      document
        .querySelector(".tab-sidemenu > ul > li.active")
        .classList.remove("active");
      targetElement.classList.add("active");
      console.log(tempcont);
      document
        .querySelector(
          '.navbar-content .dash-tabcontent[data-value="' + tempcont + '"]',
        )
        .classList.add("active");
    });
  }
}
// Menu click for tab Layout end
// nested Layout start
const pctogglesidemenu = document.querySelector<HTMLElement>(
  ".dash-toggle-sidemenu",
);
if (pctogglesidemenu) {
  if (!pctogglesidemenu.getAttribute("data-listener-bound-click")) {
    pctogglesidemenu.setAttribute("data-listener-bound-click", "1");
    pctogglesidemenu.addEventListener("click", function () {
      if (
        !document
          .querySelector(".dash-toggle-sidemenu")
          .classList.contains("active")
      ) {
        document
          .querySelector<HTMLElement>(".dash-sideoverlay")
          .classList.add("active");
        document
          .querySelector<HTMLElement>(".page-sidebar")
          .classList.add("active");
        document
          .querySelector<HTMLElement>(".dash-toggle-sidemenu")
          .classList.add("active");
      } else {
        document
          .querySelector<HTMLElement>(".dash-sideoverlay")
          .classList.remove("active");
        document
          .querySelector<HTMLElement>(".page-sidebar")
          .classList.remove("active");
        document
          .querySelector(".dash-toggle-sidemenu")
          .classList.remove("active");
      }
    });
  }
}
const pcovelayclk = document.querySelector<HTMLElement>(
  ".dash-sideoverlay, .dash-toggle-sidemenu.active",
);
if (pcovelayclk)
  if (!pcovelayclk.getAttribute("data-listener-bound-click")) {
    pcovelayclk.setAttribute("data-listener-bound-click", "1");
    pcovelayclk.addEventListener("click", function () {
      document
        .querySelector<HTMLElement>(".dash-sideoverlay")
        .classList.remove("active");
      document
        .querySelector<HTMLElement>(".page-sidebar")
        .classList.remove("active");
      document
        .querySelector<HTMLElement>(".dash-toggle-sidemenu")
        .classList.remove("active");
    });
  }
// nested Layout end

if (
  document
    .querySelector<HTMLElement>("body")
    .classList.contains("layout-topbar")
) {
  const tplink = document.querySelectorAll(
    ".dash-header .list-unstyled > .dropdown",
  );
  for (var t = 0; t < tplink.length; t++) {
    var c = tplink[t];
    c.addEventListener("mouseenter", showmenu);
    c.addEventListener("mouseleave", hidemenu);
  }
}

function showmenu(event) {
  event.target.children[1].classList.add("show");
}

function hidemenu(event) {
  event.target.children[1].classList.remove("show");
}
// topbar Layout end
// horizontal submenu edge start
if (
  document
    .querySelector<HTMLElement>("body")
    .classList.contains("dash-horizontal")
) {
  let hpx;
  const docH = window.innerHeight,
    docW = window.innerWidth;
  if (docW > 1024) {
    const topbarhasmenu = document.querySelector<HTMLElement>(
      ".dash-horizontal .topbar .dash-submenu .dash-hasmenu",
    );
    if (topbarhasmenu) {
      topbarhasmenu.addEventListener(
        "mouseenter",
        function () {
          const elm = targetElement.children[1],
            off = elm.getBoundingClientRect(),
            l = off.left,
            t = off.top,
            w = off.width,
            h = off.height,
            scrw = document.documentElement.scrollTop;
          if (!l + w <= docW) elm.classList.add("edge");
          const isEntirelyVisible = t + h <= docH;
          if (!isEntirelyVisible) {
            const th = t - scrw;
            elm.classList.add("scroll-menu");
            elm.css("max-height", "calc(100vh - " + th + "px)");
          }
        },
        function () {
          document
            .querySelector<HTMLElement>(".scroll-menu")
            .removeAttribute("style");
          document
            .querySelector(".scroll-menu")
            .classList.remove("scroll-menu");
        },
      );
    }
  }
}
// horizontal submenu edge end
// Collapse meni edge start
function collapseedge() {
  let hpx;
  const docH = window.innerHeight,
    docW = window.innerWidth;
  if (docW > 1024) {
    const minimenuhasmenu = document.querySelector<HTMLElement>(
      ".minimenu .dash-sidebar .dash-submenu .dash-hasmenu",
    );
    if (minimenuhasmenu) {
      minimenuhasmenu.addEventListener(
        "mouseenter",
        function (event) {
          const targetElement = event.target,
            elm = targetElement.children[1],
            off = elm.getBoundingClientRect(),
            l = off.left,
            t = off.top,
            w = off.width,
            h = off.height,
            scrw = document.documentElement.scrollTop;
          if (!t + h <= docH) {
            const th = t - scrw;
            elm.classList.add("scroll-menu");
            elm.css("max-height", "calc(100vh - " + th + "px)");
          }
        },
        function () {
          document
            .querySelector<HTMLElement>(".scroll-menu")
            .removeAttribute("style");
          document
            .querySelector(".scroll-menu")
            .classList.remove("scroll-menu");
        },
      );
    }
  }
}
// Collapse meni edge end
var tc = document.querySelectorAll(".prod-likes .form-check-input");
for (var t = 0; t < tc.length; t++) {
  var prodlike = tc[t];
  if (!prodlike.getAttribute("data-listener-bound-change")) {
    prodlike.setAttribute("data-listener-bound-change", "1");
    prodlike.addEventListener("change", function (event) {
      if (event.currentTarget.checked) {
        prodlike = event.target;
        // console.log(prodlike.parentNode);
        prodlike.parentNode.insertAdjacentHTML(
          "beforeend",
          '<div class="dash-like"><div class="like-wrapper"><span><span class="dash-group"><span class="dash-dots"></span><span class="dash-dots"></span><span class="dash-dots"></span><span class="dash-dots"></span></span></span></div></div>',
        );
        prodlike.parentNode
          .querySelector(".dash-like")
          .classList.add("dash-like-animate");
        setTimeout(function () {
          prodlike.parentNode.querySelector(".dash-like").remove();
        }, 3000);
      } else {
        prodlike = event.target;
        prodlike.parentNode.querySelector(".dash-like").remove();
      }
    });
  }
}

// =======================================================
// =======================================================
const slideUp = (target, duration = 0) => {
  if (!target) return;
  Object.assign(target.style, {
    transitionProperty: "height, margin, padding",
    transitionDuration: duration + "ms",
    boxSizing: "border-box",
    height: target.offsetHeight + "px",
  });
  target.offsetHeight;
  Object.assign(target.style, {
    overflow: "hidden",
    height: 0,
    paddingTop: 0,
    paddingBottom: 0,
    marginTop: 0,
    marginBottom: 0,
  });
};
const slideDown = (target, duration = 0) => {
  if (!target) return;
  target.style.removeProperty("display");
  let display = window.getComputedStyle(target).display;

  if (display === "none") display = "block";

  target.style.display = display;
  const height = target.offsetHeight;
  Object.assign(target.style, {
    overflow: "hidden",
    height: 0,
    paddingTop: 0,
    paddingBottom: 0,
    marginTop: 0,
    marginBottom: 0,
  });
  target.offsetHeight;
  Object.assign(target.style, {
    boxSizing: "border-box",
    transitionProperty: "height, margin, padding",
    transitionDuration: duration + "ms",
    height: height + "px",
  });
  target.style.removeProperty("padding-top");
  target.style.removeProperty("padding-bottom");
  target.style.removeProperty("margin-top");
  target.style.removeProperty("margin-bottom");
  window.setTimeout(() => {
    target.style.removeProperty("height");
    target.style.removeProperty("overflow");
    target.style.removeProperty("transition-duration");
    target.style.removeProperty("transition-property");
  }, duration);
};
const slideToggle = (target, duration = 0) => {
  if (window.getComputedStyle(target).display === "none") {
    slideDown(target, duration);
    return;
  } else {
    slideUp(target, duration);
    return;
  }
};
// =======================================================
// =======================================================
