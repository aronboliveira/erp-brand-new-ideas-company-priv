/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/ac-treeview.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-treeview
 */

"use strict";

// PULL REQUEST START
// Defensiva: null guards em querySelector antes de addEventListener e uso de DOM
// [ html-demo ]
const main = document.querySelector<HTMLElement>("#tree-demo");
const info = document.querySelector<HTMLElement>("#tree-msg");
if (main) {
  const tree = new VanillaTree(main, {
    contextmenu: [
      {
        label: "Hey",
        action: function (id: string): void {
          alert("Hey " + id);
        },
      },
      {
        label: "Blah",
        action: function (id: string): void {
          alert("Blah " + id);
        },
      },
    ],
  });

  tree.add({ label: "Label A", id: "a", opened: true });
  tree.add({ label: "Label B", id: "b" });
  tree.add({ label: "Label A.A", parent: "a", id: "a.a", opened: true, selected: true });
  tree.add({ label: "Label A.A.A", parent: "a.a" });
  tree.add({ label: "Label A.A.B", parent: "a.a" });
  tree.add({ label: "Label B.A", parent: "b" });

  main.addEventListener("vtree-open", function (evt: Event) {
    if (info) info.innerHTML = (evt as CustomEvent<{ id: string }>).detail.id + " is opened";
  });

  main.addEventListener("vtree-close", function (evt: Event) {
    if (info) info.innerHTML = (evt as CustomEvent<{ id: string }>).detail.id + " is closed";
  });

  main.addEventListener("vtree-select", function (evt: Event) {
    if (info) info.innerHTML = (evt as CustomEvent<{ id: string }>).detail.id + " is selected";
  });
}
// PULL REQUEST END

export {};
