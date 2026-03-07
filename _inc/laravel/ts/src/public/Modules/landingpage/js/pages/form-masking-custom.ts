/**
 * @fileoverview TypeScript version of public/Modules/landingpage/js/pages/form-masking-custom.js
 * @generated from original JavaScript - manual review recommended
 * @module form-masking-custom
 */

"use strict";
const maskDate = IMask(document.querySelector<HTMLElement>(".date"), {
  mask: "00/00/0000",
});
const maskDate2 = IMask(document.querySelector<HTMLElement>(".date2"), {
  mask: "00-00-0000",
});
const maskHour = IMask(document.querySelector<HTMLElement>(".hour"), {
  mask: "00:00:00",
});
const maskDateHour = IMask(document.querySelector<HTMLElement>(".dateHour"), {
  mask: "00/00/0000 00:00:00",
});
const maskMobNo = IMask(document.querySelector<HTMLElement>(".mob_no"), {
  mask: "0000-000-000",
});
const maskPhone = IMask(document.querySelector<HTMLElement>(".phone"), {
  mask: "0000-0000",
});
const maskTelWithCode = IMask(
  document.querySelector<HTMLElement>(".telphone_with_code"),
  { mask: "(00) 0000-0000" },
);
const maskUsTel = IMask(document.querySelector<HTMLElement>(".us_telephone"), {
  mask: "(000) 000-0000",
});
const maskIp = IMask(document.querySelector<HTMLElement>(".ip"), {
  mask: "000.000.000.000",
});
const maskIpv4 = IMask(document.querySelector<HTMLElement>(".ipv4"), {
  mask: "000.000.000.0000",
});
const maskIpv6 = IMask(document.querySelector<HTMLElement>(".ipv6"), {
  mask: "0000:0000:0000:0:000:0000:0000:0000",
});
// Suppress unused variable warnings
void maskDate;
void maskDate2;
void maskHour;
void maskDateHour;
void maskMobNo;
void maskPhone;
void maskTelWithCode;
void maskUsTel;
void maskIp;
void maskIpv4;
void maskIpv6;
