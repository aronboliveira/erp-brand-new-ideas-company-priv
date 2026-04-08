/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/form-masking-custom.js
 * @generated from original JavaScript - manual review recommended
 * @module form-masking-custom
 */

"use strict";
/* eslint-disable @typescript-eslint/no-unused-vars */

const regExpMask1 = IMask(document.querySelector<HTMLElement>(".date")!, { mask: "00/00/0000" });
const regExpMask2 = IMask(document.querySelector<HTMLElement>(".date2")!, { mask: "00-00-0000" });
const regExpMask3 = IMask(document.querySelector<HTMLElement>(".hour")!, { mask: "00:00:00" });
const regExpMask4 = IMask(document.querySelector<HTMLElement>(".dateHour")!, { mask: "00/00/0000 00:00:00" });
const regExpMask5 = IMask(document.querySelector<HTMLElement>(".mob_no")!, { mask: "0000-000-000" });
const regExpMask6 = IMask(document.querySelector<HTMLElement>(".phone")!, { mask: "0000-0000" });
const regExpMask7 = IMask(document.querySelector<HTMLElement>(".telphone_with_code")!, { mask: "(00) 0000-0000" });
const regExpMask8 = IMask(document.querySelector<HTMLElement>(".us_telephone")!, { mask: "(000) 000-0000" });
const regExpMask9 = IMask(document.querySelector<HTMLElement>(".ip")!, { mask: "000.000.000.000" });
const regExpMask10 = IMask(document.querySelector<HTMLElement>(".ipv4")!, { mask: "000.000.000.0000" });

export {};
