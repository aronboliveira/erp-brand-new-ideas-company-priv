/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-treeview.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-treeview
 */
// @ts-nocheck

"use strict";

/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */
// [ html-demo ]
const main = document.querySelector<HTMLElement>("#tree-demo"),
  info = document.querySelector<HTMLElement>("#tree-msg");
const tree = new VanillaTree(main, {
  contextmenu: [
    {
      label: "Hey",
      action: function (id) {
        alert("Hey " + id);
      },
    },
    {
      label: "Blah",
      action: function (id) {
        alert("Blah " + id);
      },
    },
  ],
});

tree.add({
  label: "Label A",
  id: "a",
  opened: true,
});

tree.add({
  label: "Label B",
  id: "b",
});

tree.add({
  label: "Label A.A",
  parent: "a",
  id: "a.a",
  opened: true,
  selected: true,
});

tree.add({
  label: "Label A.A.A",
  parent: "a.a",
});

tree.add({
  label: "Label A.A.B",
  parent: "a.a",
});

tree.add({
  label: "Label B.A",
  parent: "b",
});

main.addEventListener("vtree-open", function (evt) {
  info.innerHTML = evt.detail.id + " is opened";
});

main.addEventListener("vtree-close", function (evt) {
  info.innerHTML = evt.detail.id + " is closed";
});

main.addEventListener("vtree-select", function (evt) {
  info.innerHTML = evt.detail.id + " is selected";
});
