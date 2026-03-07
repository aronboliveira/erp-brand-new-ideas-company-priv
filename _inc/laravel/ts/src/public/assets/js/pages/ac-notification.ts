/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-notification.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-notification
 */

"use strict";

declare var notifier: {
  show: (
    title: string,
    message: string,
    type: string,
    icon: string,
    duration: number,
  ) => string | number | undefined;
  hide: (id: string | number | undefined) => void;
};
document.addEventListener("DOMContentLoaded", function (): void {
  document
    .querySelector<HTMLElement>("#btn-default")
    ?.addEventListener("click", function (): void {
      notifier.show("Hello!", "I am a default notification.", "", "", 0);
    });
  document
    .querySelector<HTMLElement>("#btn-info")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Reminder!",
        "You have a meeting at 10:30 AM.",
        "info",
        "",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-success")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Well Done!",
        "You just submit your resume successfuly.",
        "success",
        "",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-warning")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Warning!",
        "The data presented here can be change.",
        "warning",
        "",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-danger")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Sorry!",
        "Could not complete your transaction.",
        "danger",
        "",
        0,
      );
    });

  document
    .querySelector<HTMLElement>("#btn-default-i")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Default!",
        "I am a default notification.",
        "",
        "../assets/images/notification/clock-48.png",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-info-i")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Reminder!",
        "You have a meeting at 10:30 AM.",
        "info",
        "../assets/images/notification/survey-48.png",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-success-i")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Well Done!",
        "You just submit your resume successfuly.",
        "success",
        "../assets/images/notification/ok-48.png",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-warning-i")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Warning!",
        "The data presented here can be change.",
        "warning",
        "../assets/images/notification/medium_priority-48.png",
        0,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-danger-i")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Sorry!",
        "Could not complete your transaction.",
        "danger",
        "../assets/images/notification/high_priority-48.png",
        0,
      );
    });

  document
    .querySelector<HTMLElement>("#btn-default-ac")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Default!",
        "I am a default notification.",
        "",
        "../assets/images/notification/clock-48.png",
        4000,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-info-ac")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Reminder!",
        "You have a meeting at 10:30 AM.",
        "info",
        "../assets/images/notification/survey-48.png",
        4000,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-success-ac")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Well Done!",
        "You just submit your resume successfuly.",
        "success",
        "../assets/images/notification/ok-48.png",
        4000,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-warning-ac")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Warning!",
        "The data presented here can be change.",
        "warning",
        "../assets/images/notification/medium_priority-48.png",
        4000,
      );
    });
  document
    .querySelector<HTMLElement>("#btn-danger-ac")
    ?.addEventListener("click", function (): void {
      notifier.show(
        "Sorry!",
        "Could not complete your transaction.",
        "danger",
        "../assets/images/notification/high_priority-48.png",
        4000,
      );
    });

  let notificationId: string | number | undefined;

  const showNotification = function (): void {
    notificationId = notifier.show(
      "Reminder!",
      "You have a meeting at 10:30 AM.",
      "info",
      "../assets/images/notification/survey-48.png",
      4000,
    );
  };

  const hideNotification = function (): void {
    notifier.hide(notificationId);
  };

  document
    .querySelector<HTMLElement>("#btn-nt-show")
    ?.addEventListener("click", showNotification);
  document
    .querySelector<HTMLElement>("#btn-nt-hide")
    ?.addEventListener("click", hideNotification);
});

export {};
