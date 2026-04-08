/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/ac-notification.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-notification
 */

"use strict";

document.addEventListener("DOMContentLoaded", function () {
  // PULL REQUEST START
  // Defensiva: null guards em querySelector antes de addEventListener
  document.querySelector<HTMLElement>("#btn-default")?.addEventListener("click", function () {
    notifier.show("Hello!", "I am a default notification.", "", "", 0);
  });
  document.querySelector<HTMLElement>("#btn-info")?.addEventListener("click", function () {
    notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "", 0);
  });
  document.querySelector<HTMLElement>("#btn-success")?.addEventListener("click", function () {
    notifier.show("Well Done!", "You just submit your resume successfuly.", "success", "", 0);
  });
  document.querySelector<HTMLElement>("#btn-warning")?.addEventListener("click", function () {
    notifier.show("Warning!", "The data presented here can be change.", "warning", "", 0);
  });
  document.querySelector<HTMLElement>("#btn-danger")?.addEventListener("click", function () {
    notifier.show("Sorry!", "Could not complete your transaction.", "danger", "", 0);
  });

  document.querySelector<HTMLElement>("#btn-default-i")?.addEventListener("click", function () {
    notifier.show("Default!", "I am a default notification.", "", "../assets/images/notification/clock-48.png", 0);
  });
  document.querySelector<HTMLElement>("#btn-info-i")?.addEventListener("click", function () {
    notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "../assets/images/notification/survey-48.png", 0);
  });
  document.querySelector<HTMLElement>("#btn-success-i")?.addEventListener("click", function () {
    notifier.show("Well Done!", "You just submit your resume successfuly.", "success", "../assets/images/notification/ok-48.png", 0);
  });
  document.querySelector<HTMLElement>("#btn-warning-i")?.addEventListener("click", function () {
    notifier.show("Warning!", "The data presented here can be change.", "warning", "../assets/images/notification/medium_priority-48.png", 0);
  });
  document.querySelector<HTMLElement>("#btn-danger-i")?.addEventListener("click", function () {
    notifier.show("Sorry!", "Could not complete your transaction.", "danger", "../assets/images/notification/high_priority-48.png", 0);
  });

  document.querySelector<HTMLElement>("#btn-default-ac")?.addEventListener("click", function () {
    notifier.show("Default!", "I am a default notification.", "", "../assets/images/notification/clock-48.png", 4000);
  });
  document.querySelector<HTMLElement>("#btn-info-ac")?.addEventListener("click", function () {
    notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "../assets/images/notification/survey-48.png", 4000);
  });
  document.querySelector<HTMLElement>("#btn-success-ac")?.addEventListener("click", function () {
    notifier.show("Well Done!", "You just submit your resume successfuly.", "success", "../assets/images/notification/ok-48.png", 4000);
  });
  document.querySelector<HTMLElement>("#btn-warning-ac")?.addEventListener("click", function () {
    notifier.show("Warning!", "The data presented here can be change.", "warning", "../assets/images/notification/medium_priority-48.png", 4000);
  });
  document.querySelector<HTMLElement>("#btn-danger-ac")?.addEventListener("click", function () {
    notifier.show("Sorry!", "Could not complete your transaction.", "danger", "../assets/images/notification/high_priority-48.png", 4000);
  });

  let notificationId: string;

  const showNotification = function (): void {
    notificationId = String(notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "../assets/images/notification/survey-48.png", 4000));
  };

  const hideNotification = function (): void {
    notifier.hide(notificationId);
  };

  document.querySelector<HTMLElement>("#btn-nt-show")?.addEventListener("click", showNotification);
  document.querySelector<HTMLElement>("#btn-nt-hide")?.addEventListener("click", hideNotification);
  // PULL REQUEST END
});

export {};
