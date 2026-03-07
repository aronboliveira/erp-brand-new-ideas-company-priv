/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-treeview.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-treeview
 */

"use strict";

interface VanillaTreeInstance {
  add: (options: {
    label: string;
    id?: string;
    parent?: string;
    opened?: boolean;
    selected?: boolean;
  }) => void;
}
interface VanillaTreeEvent extends Event {
  detail: { id: string };
}
declare var VanillaTree: new (
  el: Element | null,
  options?: unknown,
) => VanillaTreeInstance;

// [ html-demo ]
(function (): void {
  const treeMain = document.querySelector<HTMLElement>("#tree-demo");
  const treeInfo = document.querySelector<HTMLElement>("#tree-msg");

  if (treeMain) {
    const tree = new VanillaTree(treeMain, {
      contextmenu: [
        {
          label: "Hey",
          action: function (id: string | number) {
            alert("Hey " + id);
          },
        },
        {
          label: "Blah",
          action: function (id: string | number) {
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

    treeMain.addEventListener("vtree-open", function (evt: Event) {
      if (treeInfo) {
        treeInfo.innerHTML = (evt as VanillaTreeEvent).detail.id + " is opened";
      }
    });

    treeMain.addEventListener("vtree-close", function (evt: Event) {
      if (treeInfo) {
        treeInfo.innerHTML = (evt as VanillaTreeEvent).detail.id + " is closed";
      }
    });

    treeMain.addEventListener("vtree-select", function (evt: Event) {
      if (treeInfo) {
        treeInfo.innerHTML =
          (evt as VanillaTreeEvent).detail.id + " is selected";
      }
    });
  }
})();

export {};
