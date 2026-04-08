"use strict";
// PULL REQUEST START — Alteração customizada em arquivo vendor (null guards)
// Defensiva: null guards em querySelector antes de addEventListener
document.addEventListener("DOMContentLoaded", function () {
  var btnDefault = document.querySelector("#btn-default");
  if (btnDefault)
    btnDefault.addEventListener("click", function () {
      notifier.show("Hello!", "I am a default notification.", "", "", 0);
    });
  var btnInfo = document.querySelector("#btn-info");
  if (btnInfo)
    btnInfo.addEventListener("click", function () {
      notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "", 0);
    });
  var btnSuccess = document.querySelector("#btn-success");
  if (btnSuccess)
    btnSuccess.addEventListener("click", function () {
      notifier.show("Well Done!", "You just submit your resume successfuly.", "success", "", 0);
    });
  var btnWarning = document.querySelector("#btn-warning");
  if (btnWarning)
    btnWarning.addEventListener("click", function () {
      notifier.show("Warning!", "The data presented here can be change.", "warning", "", 0);
    });
  var btnDanger = document.querySelector("#btn-danger");
  if (btnDanger)
    btnDanger.addEventListener("click", function () {
      notifier.show("Sorry!", "Could not complete your transaction.", "danger", "", 0);
    });

  var btnDefaultI = document.querySelector("#btn-default-i");
  if (btnDefaultI)
    btnDefaultI.addEventListener("click", function () {
      notifier.show("Default!", "I am a default notification.", "", "../assets/images/notification/clock-48.png", 0);
    });
  var btnInfoI = document.querySelector("#btn-info-i");
  if (btnInfoI)
    btnInfoI.addEventListener("click", function () {
      notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "../assets/images/notification/survey-48.png", 0);
    });
  var btnSuccessI = document.querySelector("#btn-success-i");
  if (btnSuccessI)
    btnSuccessI.addEventListener("click", function () {
      notifier.show("Well Done!", "You just submit your resume successfuly.", "success", "../assets/images/notification/ok-48.png", 0);
    });
  var btnWarningI = document.querySelector("#btn-warning-i");
  if (btnWarningI)
    btnWarningI.addEventListener("click", function () {
      notifier.show("Warning!", "The data presented here can be change.", "warning", "../assets/images/notification/medium_priority-48.png", 0);
    });
  var btnDangerI = document.querySelector("#btn-danger-i");
  if (btnDangerI)
    btnDangerI.addEventListener("click", function () {
      notifier.show("Sorry!", "Could not complete your transaction.", "danger", "../assets/images/notification/high_priority-48.png", 0);
    });

  var btnDefaultAc = document.querySelector("#btn-default-ac");
  if (btnDefaultAc)
    btnDefaultAc.addEventListener("click", function () {
      notifier.show("Default!", "I am a default notification.", "", "../assets/images/notification/clock-48.png", 4000);
    });
  var btnInfoAc = document.querySelector("#btn-info-ac");
  if (btnInfoAc)
    btnInfoAc.addEventListener("click", function () {
      notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "../assets/images/notification/survey-48.png", 4000);
    });
  var btnSuccessAc = document.querySelector("#btn-success-ac");
  if (btnSuccessAc)
    btnSuccessAc.addEventListener("click", function () {
      notifier.show("Well Done!", "You just submit your resume successfuly.", "success", "../assets/images/notification/ok-48.png", 4000);
    });
  var btnWarningAc = document.querySelector("#btn-warning-ac");
  if (btnWarningAc)
    btnWarningAc.addEventListener("click", function () {
      notifier.show("Warning!", "The data presented here can be change.", "warning", "../assets/images/notification/medium_priority-48.png", 4000);
    });
  var btnDangerAc = document.querySelector("#btn-danger-ac");
  if (btnDangerAc)
    btnDangerAc.addEventListener("click", function () {
      notifier.show("Sorry!", "Could not complete your transaction.", "danger", "../assets/images/notification/high_priority-48.png", 4000);
    });

  var notificationId;

  var showNotification = function () {
    notificationId = notifier.show("Reminder!", "You have a meeting at 10:30 AM.", "info", "../assets/images/notification/survey-48.png", 4000);
  };

  var hideNotification = function () {
    notifier.hide(notificationId);
  };

  var btnNtShow = document.querySelector("#btn-nt-show");
  if (btnNtShow) btnNtShow.addEventListener("click", showNotification);
  var btnNtHide = document.querySelector("#btn-nt-hide");
  if (btnNtHide) btnNtHide.addEventListener("click", hideNotification);
});
// PULL REQUEST END — Fim da alteração customizada
