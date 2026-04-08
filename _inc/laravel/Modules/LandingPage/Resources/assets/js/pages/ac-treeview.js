"use strict";

// PULL REQUEST START — Alteração customizada em arquivo vendor (null guards)
// Defensiva: null guards em querySelector antes de addEventListener e uso de DOM
// [ html-demo ]
const main = document.querySelector("#tree-demo");
const info = document.querySelector("#tree-msg");
if (main) {
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
    if (info) info.innerHTML = evt.detail.id + " is opened";
  });

  main.addEventListener("vtree-close", function (evt) {
    if (info) info.innerHTML = evt.detail.id + " is closed";
  });

  main.addEventListener("vtree-select", function (evt) {
    if (info) info.innerHTML = evt.detail.id + " is selected";
  });
}
// PULL REQUEST END — Fim da alteração customizada
