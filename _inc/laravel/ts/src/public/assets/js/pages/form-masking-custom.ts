/**
 * @fileoverview TypeScript version of public/assets/js/pages/form-masking-custom.js
 * @generated from original JavaScript - manual review recommended
 * @module form-masking-custom
 */

"use strict";
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unused-vars, no-var */
var regExpMask = IMask(document.querySelector<HTMLElement>(".date"), {
    mask: "00/00/0000",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".date2"), {
    mask: "00-00-0000",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".hour"), {
    mask: "00:00:00",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".dateHour"), {
    mask: "00/00/0000 00:00:00",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".mob_no"), {
    mask: "0000-000-000",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".phone"), {
    mask: "0000-0000",
  }),
  regExpMask = IMask(
    document.querySelector<HTMLElement>(".telphone_with_code"),
    { mask: "(00) 0000-0000" },
  ),
  regExpMask = IMask(document.querySelector<HTMLElement>(".us_telephone"), {
    mask: "(000) 000-0000",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".ip"), {
    mask: "000.000.000.000",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".ipv4"), {
    mask: "000.000.000.0000",
  }),
  regExpMask = IMask(document.querySelector<HTMLElement>(".ipv6"), {
    mask: "0000:0000:0000:0:000:0000:0000:0000",
  });
