/**
 * @fileoverview TypeScript version of public/Modules/landingpage/js/dash.js
 * @generated from original JavaScript - manual review recommended
 * @module dash
 */

/* global feather, bootstrap */
"use strict";

// Make this file a module to avoid duplicate identifier conflicts with another dash.ts
export {};

// Type declarations for external libraries
declare const SimpleBar: new (
  el: HTMLElement | null,
  options?: { autoHide?: boolean },
) => unknown;

let flg = "0";
document.addEventListener("DOMContentLoaded", function (): void {
  // feather icon start
  feather && typeof feather.replace === "function" && feather.replace();
  // feather icon end
  // remove pre-loader start
  setTimeout(function (): void {
    document.querySelector<HTMLElement>(".loader-bg")?.remove();
  }, 400);
  // remove pre-loader end
  if (
    !document
      .querySelector<HTMLElement>("body")
      ?.classList.contains("dash-horizontal")
  ) {
    addscroller();
  }
  if (
    document
      .querySelector<HTMLElement>("body")
      ?.classList.contains("dash-horizontal")
  ) {
    if (
      document
        .querySelector(".dash-horizontal")
        ?.classList.contains("navbar-overlay")
    ) {
      addscroller();
    }
  }
  const hamburger = document.querySelector<HTMLElement>(
    ".hamburger:not(.is-active)",
  );
  if (hamburger) {
    hamburger.addEventListener("click", function (): void {
      if (
        document
          .querySelector<HTMLElement>(".hamburger")
          ?.classList.contains("is-active")
      ) {
        document
          .querySelector<HTMLElement>(".hamburger")
          ?.classList.remove("is-active");
      } else {
        document
          .querySelector<HTMLElement>(".hamburger")
          ?.classList.add("is-active");
      }
    });
  }
  // Menu overlay layout start
  const tempoverlaymenu = document.querySelector<HTMLElement>("#overlay-menu");
  if (tempoverlaymenu) {
    tempoverlaymenu.addEventListener("click", function (): void {
      menuclick();
      if (
        document
          .querySelector(".dash-sidebar")
          ?.classList.contains("dash-over-menu-active")
      ) {
        rmovermenu();
      } else {
        document
          .querySelector(".dash-sidebar")
          ?.classList.add("dash-over-menu-active");
        document
          .querySelector(".dash-sidebar")
          ?.insertAdjacentHTML(
            "beforeend",
            '<div class="dash-menu-overlay"></div>',
          );
        document
          .querySelector(".dash-menu-overlay")
          ?.addEventListener("click", function (): void {
            rmovermenu();
            document
              .querySelector<HTMLElement>(".hamburger")
              ?.classList.remove("is-active");
          });
      }
    });
  }
  // Menu overlay layout end
  // vertical-nav-toggle start

  const verticalnavtoggle = document.querySelector<HTMLElement>(
    "#vertical-nav-toggle",
  );
  if (verticalnavtoggle) {
    verticalnavtoggle.addEventListener("click", function (): void {
      if (document.body.classList.contains("minimenu")) {
        document.body.classList.remove("minimenu");
        // menuclick();

        // ===============
        const elem = document.querySelectorAll(
          ".dash-navbar li:not(.dash-trigger) .dash-submenu",
        );
        for (let j = 0; j < elem.length; j++) {
          (elem[j] as HTMLElement).style.display = "none";
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
  // vertical-nav-toggle end
  // Menu collapse click start
  const mobilecollapsever =
    document.querySelector<HTMLElement>("#mobile-collapse");
  if (mobilecollapsever) {
    mobilecollapsever.addEventListener("click", function (): void {
      if (
        !document
          .querySelector<HTMLElement>("body")
          ?.classList.contains("dash-horizontal")
      ) {
        // menuclick();
      }
      const tempsdbr = document.querySelector<HTMLElement>(".dash-sidebar");
      if (tempsdbr) {
        if (
          document
            .querySelector(".dash-sidebar")
            ?.classList.contains("mob-sidebar-active")
        ) {
          rmmenu();
        } else {
          document
            .querySelector(".dash-sidebar")
            ?.classList.add("mob-sidebar-active");
          document
            .querySelector(".dash-sidebar")
            ?.insertAdjacentHTML(
              "beforeend",
              '<div class="dash-menu-overlay"></div>',
            );
          document
            .querySelector(".dash-menu-overlay")
            ?.addEventListener("click", function (): void {
              document
                .querySelector(".hamburger")
                ?.classList.remove("is-active");
              rmmenu();
            });
        }
      }
    });
  }
  // Menu collapse click end

  // Menu collapse click start
  const mobilecollapse = document.querySelector<HTMLElement>(
    ".dash-horizontal #mobile-collapse",
  );
  if (mobilecollapse) {
    mobilecollapse.addEventListener("click", function (): void {
      if (
        document
          .querySelector(".topbar")
          ?.classList.contains("mob-sidebar-active")
      ) {
        rmmenu();
      } else {
        document
          .querySelector<HTMLElement>(".topbar")
          ?.classList.add("mob-sidebar-active");
        document
          .querySelector(".topbar")
          ?.insertAdjacentHTML(
            "beforeend",
            '<div class="dash-menu-overlay"></div>',
          );
        document
          .querySelector(".dash-menu-overlay")
          ?.addEventListener("click", function (): void {
            rmmenu();
            document
              .querySelector<HTMLElement>(".hamburger")
              ?.classList.remove("is-active");
          });
      }
    });
  }
  // Menu collapse click end
  // mobile header click start
  // document
  //   .querySelector("#header-collapse")
  //   .addEventListener("click", function (): void {
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
  //         .addEventListener("click", function (): void {
  //           rmthead();
  //         });
  //     }
  //   });
  // document
  //   .querySelector("#headerdrp-collapse")
  //   .addEventListener("click", function (): void {
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
  //         .addEventListener("click", function (): void {
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
    topbarlinklist.addEventListener("click", function (e: Event) {
      const targetElement = e.target as HTMLElement | null;
      setTimeout(function (): void {
        (
          targetElement?.parentNode as HTMLElement | null
        )?.children[1]?.removeAttribute("style");
      }, 1000);
    });
  }
  // Horizontal menu click js end

  function formmat(e: HTMLInputElement) {
    let temp = 0;
    try {
      // @ts-expect-error attr is jQuery method pattern
      temp = e.attr("placeholder").length;
    } catch (err) {
      temp = 0;
    }
    if (e.value.length > 0) {
      (e.parentNode as HTMLElement | null)?.classList.add("fill");
    } else {
      (e.parentNode as HTMLElement | null)?.classList.remove("fill");
    }
  }
  // Material form end
  if (
    document
      .querySelector<HTMLElement>("body")
      ?.classList.contains("dash-horizontal")
  ) {
    horizontalmobilemenuclick();
  }
  if (
    document.querySelector<HTMLElement>("body")?.classList.contains("minimenu")
  ) {
    collapseedge();
  }
  // notification scrollbar start
  if (document.querySelector<HTMLElement>(".drp-notification .noti-body")) {
    // let px = new PerfectScrollbar(".drp-notification .noti-body", {
    //   wheelSpeed: 0.5,
    //   swipeEasing: false,
    //   suppressScrollX: true,
    //   wheelPropagation: true,
    //   minScrollbarLength: 40,
    // });
  }
  // notification scrollbar end
});

function horizontalmobilemenuclick() {
  const vw = window.innerWidth;
  const pcnavlinklist = document.querySelector<HTMLElement>(".dash-navbar li");
  if (pcnavlinklist) {
    pcnavlinklist.removeEventListener("click", function (): void {});
  }

  const pclinkclick = document.querySelectorAll(
    ".dash-navbar > li:not(.dash-caption)",
  );
  for (let i = 0; i < pclinkclick.length; i++) {
    pclinkclick[i].addEventListener("click", function (event: Event) {
      let targetElement = event.target as HTMLElement | null;
      if (targetElement?.tagName == "SPAN") {
        targetElement = targetElement.parentNode as HTMLElement | null;
      }
      (
        targetElement?.parentNode as HTMLElement | null
      )?.children[1]?.removeAttribute("style");
      if (
        (targetElement?.parentNode as HTMLElement | null)?.classList.contains(
          "dash-trigger",
        )
      ) {
        (targetElement?.parentNode as HTMLElement | null)?.classList.remove(
          "dash-trigger",
        );
      } else {
        const tc = document.querySelectorAll("li.dash-trigger");
        for (let t = 0; t < tc.length; t++) {
          const c = tc[t];
          c.classList.remove("dash-trigger");
        }
        (targetElement?.parentNode as HTMLElement | null)?.classList.add(
          "dash-trigger",
        );
      }
    });
  }
  const pcsublinkclick = document.querySelectorAll(
    ".dash-navbar > li:not(.dash-caption) > .dash-submenu > li",
  );
  for (let n = 0; n < pcsublinkclick.length; n++) {
    pcsublinkclick[n].addEventListener("click", function (event: Event) {
      event.stopPropagation();
      let targetElement = event.target as HTMLElement | null;
      if (targetElement?.tagName == "SPAN") {
        targetElement = targetElement.parentNode as HTMLElement | null;
      }
      (
        targetElement?.parentNode as HTMLElement | null
      )?.children[1]?.removeAttribute("style");
      if (
        (targetElement?.parentNode as HTMLElement | null)?.classList.contains(
          "dash-trigger",
        )
      ) {
        (targetElement?.parentNode as HTMLElement | null)?.classList.remove(
          "dash-trigger",
        );
      } else {
        const tc = document.querySelectorAll(".dash-submenu li.dash-trigger");
        for (let t = 0; t < tc.length; t++) {
          const c = tc[t];
          c.classList.remove("dash-trigger");
        }
        (targetElement?.parentNode as HTMLElement | null)?.classList.add(
          "dash-trigger",
        );
      }
    });
  }
  const pcsubchildlinkclick = document.querySelectorAll(
    ".dash-navbar > li:not(.dash-caption) > .dash-submenu >  li > .dash-submenu >  li",
  );
  for (let n = 0; n < pcsubchildlinkclick.length; n++) {
    pcsubchildlinkclick[n].addEventListener("click", function (event: Event) {
      event.stopPropagation();
      let targetElement = event.target as HTMLElement | null;
      if (targetElement?.tagName == "SPAN") {
        targetElement = targetElement.parentNode as HTMLElement | null;
      }
      (
        targetElement?.parentNode as HTMLElement | null
      )?.children[1]?.removeAttribute("style");
      if (
        (targetElement?.parentNode as HTMLElement | null)?.classList.contains(
          "dash-trigger",
        )
      ) {
        (targetElement?.parentNode as HTMLElement | null)?.classList.remove(
          "dash-trigger",
        );
      } else {
        const tc = document.querySelectorAll(
          ".dash-submenu .dash-submenu li.dash-trigger",
        );
        for (let t = 0; t < tc.length; t++) {
          const c = tc[t];
          c.classList.remove("dash-trigger");
        }
        (targetElement?.parentNode as HTMLElement | null)?.classList.add(
          "dash-trigger",
        );
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
    // let px = new PerfectScrollbar(".navbar-content", {
    //   wheelSpeed: 0.5,
    //   swipeEasing: false,
    //   suppressScrollX: true,
    //   wheelPropagation: true,
    //   minScrollbarLength: 40,
    // });
  }
  // Menu scrollbar end
}
// Menu click start
function menuclick() {
  const vw = window.innerWidth;
  const elem = document.querySelectorAll(".dash-navbar li");
  for (let j = 0; j < elem.length; j++) {
    elem[j].removeEventListener("click", function (): void {});
  }

  if (
    !document.querySelector<HTMLElement>("body")?.classList.contains("minimenu")
  ) {
    const elem = document.querySelectorAll(
      ".dash-navbar li:not(.dash-trigger) .dash-submenu",
    );
    for (let j = 0; j < elem.length; j++) {
      (elem[j] as HTMLElement).style.display = "none";
    }
    const pclinkclick = document.querySelectorAll(
      ".dash-navbar > li:not(.dash-caption)",
    );
    for (let i = 0; i < pclinkclick.length; i++) {
      pclinkclick[i].addEventListener("click", function (event: Event) {
        event.stopPropagation();
        let targetElement = event.target as HTMLElement | null;
        if (targetElement?.tagName == "SPAN") {
          targetElement = targetElement.parentNode as HTMLElement | null;
        }
        if (
          (targetElement?.parentNode as HTMLElement | null)?.classList.contains(
            "dash-trigger",
          )
        ) {
          (targetElement?.parentNode as HTMLElement | null)?.classList.remove(
            "dash-trigger",
          );
          // targetElement.parentNode?.children[1].style.display = "none";
          slideUp(
            (targetElement?.parentNode as HTMLElement | null)
              ?.children[1] as HTMLElement,
            200,
          );
        } else {
          const tc = document.querySelectorAll("li.dash-trigger");
          for (let t = 0; t < tc.length; t++) {
            const c = tc[t];
            c.classList.remove("dash-trigger");
            slideUp(c.children[1] as HTMLElement, 200);
          }
          (targetElement?.parentNode as HTMLElement | null)?.classList.add(
            "dash-trigger",
          );
          const tmp = targetElement?.children[1];
          if (tmp) {
            slideDown(
              (targetElement?.parentNode as HTMLElement | null)
                ?.children[1] as HTMLElement,
              200,
            );
          }
        }
      });
    }
    const pcsublinkclick = document.querySelectorAll(
      ".dash-navbar > li:not(.dash-caption) li",
    );
    for (let i = 0; i < pcsublinkclick.length; i++) {
      pcsublinkclick[i].addEventListener("click", function (event: Event) {
        let targetElement = event.target as HTMLElement | null;
        if (targetElement?.tagName == "SPAN") {
          targetElement = targetElement.parentNode as HTMLElement | null;
        }
        event.stopPropagation();
        if (
          (targetElement?.parentNode as HTMLElement | null)?.classList.contains(
            "dash-trigger",
          )
        ) {
          (targetElement?.parentNode as HTMLElement | null)?.classList.remove(
            "dash-trigger",
          );
          slideUp(
            (targetElement?.parentNode as HTMLElement | null)
              ?.children[1] as HTMLElement,
            200,
          );
        } else {
          const tc = (targetElement?.parentNode as HTMLElement | null)
            ?.parentNode?.children;
          if (tc) {
            for (let t = 0; t < tc.length; t++) {
              let c = tc[t] as HTMLElement;
              c.classList.remove("dash-trigger");
              if (c.tagName == "LI") {
                c = c.children[0] as HTMLElement;
              }
              if (
                (c.parentNode as HTMLElement | null)?.classList.contains(
                  "dash-hasmenu",
                )
              ) {
                slideUp(
                  (c.parentNode as HTMLElement | null)
                    ?.children[1] as HTMLElement,
                  200,
                );
              }
            }
          }
          (targetElement?.parentNode as HTMLElement | null)?.classList.add(
            "dash-trigger",
          );
          const tmp = (targetElement?.parentNode as HTMLElement | null)
            ?.children[1];
          if (tmp) {
            tmp.removeAttribute("style");
            slideDown(tmp as HTMLElement, 200);
          }
        }
      });
    }
  }
}

function rmdrp() {
  document
    .querySelector(".dash-header:not(.dash-mob-header) .dash-mob-drp")
    ?.classList.remove("mob-drp-active");
  document
    .querySelector(".dash-header:not(.dash-mob-header) .dash-md-overlay")
    ?.remove();
}

function rmthead() {
  document
    .querySelector(".dash-header:not(.dash-mob-header)")
    ?.classList.remove("mob-header-active");
  document
    .querySelector(".dash-header:not(.dash-mob-header) .dash-md-overlay")
    ?.remove();
}

function rmmenu() {
  const tempov = document.querySelector<HTMLElement>(".dash-sidebar");
  if (tempov) {
    document
      .querySelector(".dash-sidebar")
      ?.classList.remove("mob-sidebar-active");
  }
  if (document.querySelector<HTMLElement>(".topbar")) {
    document
      .querySelector<HTMLElement>(".topbar")
      ?.classList.remove("mob-sidebar-active");
  }

  document
    .querySelector<HTMLElement>(".dash-sidebar .dash-menu-overlay")
    ?.remove();
  document.querySelector<HTMLElement>(".topbar .dash-menu-overlay")?.remove();
}

function rmovermenu() {
  document
    .querySelector(".dash-sidebar")
    ?.classList.remove("dash-over-menu-active");
  if (document.querySelector<HTMLElement>(".topbar")) {
    document
      .querySelector<HTMLElement>(".topbar")
      ?.classList.remove("mob-sidebar-active");
  }
  document
    .querySelector<HTMLElement>(".dash-sidebar .dash-menu-overlay")
    ?.remove();
  document.querySelector<HTMLElement>(".topbar .dash-menu-overlay")?.remove();
}

function rmactive() {
  document
    .querySelector(".dash-sidebar .dash-navbar li")
    ?.classList.remove("active");
  document
    .querySelector(".dash-sidebar .dash-navbar li")
    ?.classList.remove("dash-trigger");
  document
    .querySelector<HTMLElement>(".topbar .dropdown")
    ?.classList.remove("show");
  document
    .querySelector<HTMLElement>(".topbar .dropdown-menu")
    ?.classList.remove("show");
  document
    .querySelector<HTMLElement>(".dash-sidebar .dash-menu-overlay")
    ?.remove();
  document.querySelector<HTMLElement>(".topbar .dash-menu-overlay")?.remove();
}

function rmmini() {
  // let vw = document.querySelector(window)[0].innerWidth;
  const vw = window.innerWidth;
  if (vw <= 1024) {
    if (
      document
        .querySelector<HTMLElement>("body")
        ?.classList.contains("minimenu")
    ) {
      document.querySelector<HTMLElement>("body")?.classList.remove("minimenu");
      flg = "1";
      2;
    }
  } else {
    if (vw > 1024) {
      if (flg == "1") {
        document.querySelector<HTMLElement>("body")?.classList.add("minimenu");
        flg = "0";
      }
    }
  }
}
const emailmorelink = document.querySelector<HTMLElement>(".email-more-link");
if (emailmorelink) {
  emailmorelink.addEventListener(
    "click",
    function (this: HTMLElement, e: Event) {
      // @ts-expect-error jQuery-like pattern - children() and slideToggle are not native DOM
      document.querySelector(this)?.children("span").slideToggle(1);
    },
  );
}

// Menu click end
window.addEventListener("resize", function (): void {
  if (
    !document
      .querySelector<HTMLElement>("body")
      ?.classList.contains("dash-horizontal")
  ) {
    rmmini();
    // menuclick();
  }
  if (
    document
      .querySelector<HTMLElement>("body")
      ?.classList.contains("dash-horizontal")
  ) {
    rmactive();
  }
});

window.addEventListener("load", function (): void {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  const tooltipList = tooltipTriggerList.map(function (
    tooltipTriggerEl: Element,
  ) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  const popoverList = popoverTriggerList.map(function (
    popoverTriggerEl: Element,
  ) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
  const toastElList = [].slice.call(document.querySelectorAll(".toast"));
  const toastList = toastElList.map(function (toastEl: Element) {
    return new bootstrap.Toast(toastEl);
  });
});
// active menu item list start
const elem = document.querySelectorAll<HTMLAnchorElement>(
  ".dash-sidebar .dash-navbar a",
);
for (let l = 0; l < elem.length; l++) {
  const pageUrl = window.location.href.split(/[?#]/)[0];
  if (elem[l].href == pageUrl && elem[l].getAttribute("href") != "") {
    (elem[l].parentNode as HTMLElement | null)?.classList.add("active");
    scrolltargetmenu(elem[l].parentNode as HTMLElement | null);
    (
      (elem[l].parentNode as HTMLElement | null)
        ?.parentNode as HTMLElement | null
    )?.parentElement?.classList.add("active");
    (
      (elem[l].parentNode as HTMLElement | null)
        ?.parentNode as HTMLElement | null
    )?.parentElement?.classList.add("dash-trigger");
    ((elem[l].parentNode as HTMLElement | null)
      ?.parentNode as HTMLElement | null)!.style.display = "block";

    (
      (
        (elem[l].parentNode as HTMLElement | null)
          ?.parentNode as HTMLElement | null
      )?.parentElement as HTMLElement | null
    )?.parentElement?.parentElement?.classList.add("active");
    (
      (
        (elem[l].parentNode as HTMLElement | null)
          ?.parentNode as HTMLElement | null
      )?.parentElement as HTMLElement | null
    )?.parentElement?.parentElement?.classList.add("dash-trigger");
    ((
      (
        (elem[l].parentNode as HTMLElement | null)
          ?.parentNode as HTMLElement | null
      )?.parentElement as HTMLElement | null
    )?.parentElement as HTMLElement | null)!.style.display = "block";

    // elem[i].parentNode('li').parentNode().parentNode('.sidelink').classList.add("active");
    // elem[i].parentNodes('.dash-tabcontent').classList.add('active');
    if (document.body.classList.contains("tab-layout")) {
      const temp = document
        .querySelector(".dash-tabcontent.active")
        ?.getAttribute("data-value");
      document
        .querySelector(".tab-sidemenu > ul > li")
        ?.classList.remove("active");
      (
        document.querySelector(
          '.tab-sidemenu > ul > li > a[data-cont="' + temp + '"]',
        )?.parentNode as HTMLElement | null
      )?.classList.add("active");
    }
  }
}
// scroll to active menu
function scrolltargetmenu(value: HTMLElement | null) {
  document.addEventListener("DOMContentLoaded", function (): void {
    if (document.querySelector<HTMLElement>(".navbar-content") && value) {
      const elm = value;
      const off = elm.getBoundingClientRect();
      const t = off.top;
      if (t > 300) {
        const navContent =
          document.querySelector<HTMLElement>(".navbar-content");
        if (navContent) navContent.scrollTop = t - 300;
      }
    }
  });
}

// Menu click for tab Layout start
const tablayclick = document.querySelector<HTMLElement>(
  ".tab-sidemenu > ul > li",
);
if (tablayclick) {
  console.log("condition");
  const tc = document.querySelectorAll(".tab-sidemenu > ul > li");
  for (let t = 0; t < tc.length; t++) {
    const c = tc[t];
    c.addEventListener("click", function (event: Event) {
      let targetElement = event.target as HTMLElement | null;
      if (targetElement?.tagName == "A") {
        targetElement = targetElement.parentNode as HTMLElement | null;
      }
      if (targetElement?.tagName == "I") {
        targetElement = (targetElement.parentNode as HTMLElement | null)
          ?.parentNode as HTMLElement | null;
      }
      const tempcont = targetElement?.children[0]?.getAttribute("data-cont");
      document
        .querySelector(".navbar-content .dash-tabcontent.active")
        ?.classList.remove("active");
      document
        .querySelector(".tab-sidemenu > ul > li.active")
        ?.classList.remove("active");
      targetElement?.classList.add("active");
      console.log(tempcont);
      document
        .querySelector(
          '.navbar-content .dash-tabcontent[data-value="' + tempcont + '"]',
        )
        ?.classList.add("active");
    });
  }
}
// Menu click for tab Layout end
// nested Layout start
const pctogglesidemenu = document.querySelector<HTMLElement>(
  ".dash-toggle-sidemenu",
);
if (pctogglesidemenu) {
  pctogglesidemenu.addEventListener("click", function (): void {
    if (
      !document
        .querySelector(".dash-toggle-sidemenu")
        ?.classList.contains("active")
    ) {
      document
        .querySelector<HTMLElement>(".dash-sideoverlay")
        ?.classList.add("active");
      document
        .querySelector<HTMLElement>(".page-sidebar")
        ?.classList.add("active");
      document
        .querySelector<HTMLElement>(".dash-toggle-sidemenu")
        ?.classList.add("active");
    } else {
      document
        .querySelector<HTMLElement>(".dash-sideoverlay")
        ?.classList.remove("active");
      document
        .querySelector<HTMLElement>(".page-sidebar")
        ?.classList.remove("active");
      document
        .querySelector(".dash-toggle-sidemenu")
        ?.classList.remove("active");
    }
  });
}
const pcovelayclk = document.querySelector<HTMLElement>(
  ".dash-sideoverlay, .dash-toggle-sidemenu.active",
);
if (pcovelayclk) {
  pcovelayclk.addEventListener("click", function (): void {
    document
      .querySelector<HTMLElement>(".dash-sideoverlay")
      ?.classList.remove("active");
    document
      .querySelector<HTMLElement>(".page-sidebar")
      ?.classList.remove("active");
    document
      .querySelector<HTMLElement>(".dash-toggle-sidemenu")
      ?.classList.remove("active");
  });
}
// nested Layout end

if (
  document
    .querySelector<HTMLElement>("body")
    ?.classList.contains("layout-topbar")
) {
  const tplink = document.querySelectorAll(
    ".dash-header .list-unstyled > .dropdown",
  );
  for (let t = 0; t < tplink.length; t++) {
    const c = tplink[t];
    c.addEventListener("mouseenter", showmenu);
    c.addEventListener("mouseleave", hidemenu);
  }
}

function showmenu(event: Event) {
  (
    (event.target as HTMLElement | null)?.children[1] as HTMLElement | undefined
  )?.classList.add("show");
}

function hidemenu(event: Event) {
  (
    (event.target as HTMLElement | null)?.children[1] as HTMLElement | undefined
  )?.classList.remove("show");
}
// topbar Layout end
// horizontal submenu edge start
if (
  document
    .querySelector<HTMLElement>("body")
    ?.classList.contains("dash-horizontal")
) {
  let hpx;
  const docH = window.innerHeight;
  const docW = window.innerWidth;

  if (docW > 1024) {
    const topbarhasmenu = document.querySelector<HTMLElement>(
      ".dash-horizontal .topbar .dash-submenu .dash-hasmenu",
    );
    if (topbarhasmenu) {
      topbarhasmenu.addEventListener(
        "mouseenter",
        function (this: HTMLElement, event: Event): void {
          const targetElement = event.target as HTMLElement | null;
          const elm = targetElement?.children[1] as HTMLElement | undefined;
          if (!elm) return;
          const off = elm.getBoundingClientRect();
          const l = off.left;
          const t = off.top;
          const w = off.width;
          const h = off.height;
          const scrw = document.documentElement.scrollTop;

          const edgepos = l + w <= docW;
          if (!edgepos) {
            elm.classList.add("edge");
          }
          const isEntirelyVisible = t + h <= docH;
          if (!isEntirelyVisible) {
            const th = t - scrw;
            elm.classList.add("scroll-menu");
            elm.style.maxHeight = "calc(100vh - " + th + "px)";
          }
        },
      );
      topbarhasmenu.addEventListener("mouseleave", function (): void {
        document
          .querySelector<HTMLElement>(".scroll-menu")
          ?.removeAttribute("style");
        document.querySelector(".scroll-menu")?.classList.remove("scroll-menu");
      });
    }
  }
}
// horizontal submenu edge end
// Collapse meni edge start
function collapseedge() {
  const docH = window.innerHeight;
  const docW = window.innerWidth;
  if (docW > 1024) {
    const minimenuhasmenu = document.querySelector<HTMLElement>(
      ".minimenu .dash-sidebar .dash-submenu .dash-hasmenu",
    );
    if (minimenuhasmenu) {
      minimenuhasmenu.addEventListener("mouseenter", function (event: Event) {
        const targetElement = event.target as HTMLElement | null;
        const elm = targetElement?.children[1] as HTMLElement | undefined;
        if (!elm) return;
        const off = elm.getBoundingClientRect();
        const l = off.left;
        const t = off.top;
        const w = off.width;
        const h = off.height;
        const scrw = document.documentElement.scrollTop;

        const isEntirelyVisible = t + h <= docH;
        if (!isEntirelyVisible) {
          const th = t - scrw;
          elm.classList.add("scroll-menu");
          elm.style.maxHeight = "calc(100vh - " + th + "px)";
        }
      });
      minimenuhasmenu.addEventListener("mouseleave", function (): void {
        document
          .querySelector<HTMLElement>(".scroll-menu")
          ?.removeAttribute("style");
        document.querySelector(".scroll-menu")?.classList.remove("scroll-menu");
      });
    }
  }
}
// Collapse meni edge end
const tcProdLikes = document.querySelectorAll<HTMLInputElement>(
  ".prod-likes .form-check-input",
);
for (let t = 0; t < tcProdLikes.length; t++) {
  let prodlike: HTMLInputElement | HTMLElement = tcProdLikes[t];
  prodlike.addEventListener("change", function (event: Event) {
    const currentTarget = event.currentTarget as HTMLInputElement | null;
    if (currentTarget?.checked) {
      prodlike = event.target as HTMLElement;
      // console.log(prodlike.parentNode);
      (prodlike.parentNode as HTMLElement | null)?.insertAdjacentHTML(
        "beforeend",
        '<div class="dash-like"><div class="like-wrapper"><span><span class="dash-group"><span class="dash-dots"></span><span class="dash-dots"></span><span class="dash-dots"></span><span class="dash-dots"></span></span></span></div></div>',
      );
      (prodlike.parentNode as HTMLElement | null)
        ?.querySelector(".dash-like")
        ?.classList.add("dash-like-animate");
      setTimeout(function (): void {
        (prodlike.parentNode as HTMLElement | null)
          ?.querySelector(".dash-like")
          ?.remove();
      }, 3000);
    } else {
      prodlike = event.target as HTMLElement;
      (prodlike.parentNode as HTMLElement | null)
        ?.querySelector(".dash-like")
        ?.remove();
    }
  });
}

// =======================================================
// =======================================================
const slideUp = (target: HTMLElement, duration = 0) => {
  if (!target) return;
  target.style.transitionProperty = "height, margin, padding";
  target.style.transitionDuration = duration + "ms";
  target.style.boxSizing = "border-box";
  target.style.height = target.offsetHeight + "px";
  target.offsetHeight;
  target.style.overflow = "hidden";
  target.style.height = "0";
  target.style.paddingTop = "0";
  target.style.paddingBottom = "0";
  target.style.marginTop = "0";
  target.style.marginBottom = "0";
};
const slideDown = (target: HTMLElement, duration = 0) => {
  if (!target) return;
  target.style.removeProperty("display");
  let display = window.getComputedStyle(target).display;

  if (display === "none") display = "block";

  target.style.display = display;
  const height = target.offsetHeight;
  target.style.overflow = "hidden";
  target.style.height = "0";
  target.style.paddingTop = "0";
  target.style.paddingBottom = "0";
  target.style.marginTop = "0";
  target.style.marginBottom = "0";
  target.offsetHeight;
  target.style.boxSizing = "border-box";
  target.style.transitionProperty = "height, margin, padding";
  target.style.transitionDuration = duration + "ms";
  target.style.height = height + "px";
  target.style.removeProperty("padding-top");
  target.style.removeProperty("padding-bottom");
  target.style.removeProperty("margin-top");
  target.style.removeProperty("margin-bottom");
  window.setTimeout((): void => {
    target.style.removeProperty("height");
    target.style.removeProperty("overflow");
    target.style.removeProperty("transition-duration");
    target.style.removeProperty("transition-property");
  }, duration);
};
const slideToggle = (target: HTMLElement, duration = 0) => {
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
