/**
 * @file ac-treeview.js
 * @description VanillaTree treeview configuration
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Treeview controller using VanillaTree
   * @class TreeviewController
   */
  class TreeviewController {
    /** @type {string} */
    static #DATA_INIT = "data-treeview-init";
    /** @type {string} */
    static #CONTAINER_ID = "myTree";
    /** @type {VanillaTree|null} */
    #tree = null;

    /**
     * Initialize treeview
     */
    init() {
      if (document.body?.hasAttribute(TreeviewController.#DATA_INIT)) return;
      if (typeof VanillaTree === "undefined") return console.warn("[TreeviewController] VanillaTree not loaded");

      const container = document.getElementById(TreeviewController.#CONTAINER_ID);
      if (!container || container.hasAttribute("data-tree-applied")) return;

      document.body?.setAttribute(TreeviewController.#DATA_INIT, "true");
      container.setAttribute("data-tree-applied", "true");
      this.#setupTree(container);
    }

    /**
     * Setup tree structure with nested nodes
     * @private
     * @param {HTMLElement} container
     */
    #setupTree(container) {
      try {
        this.#tree = new VanillaTree(container, { placeholder: "Awesome Vanilla Tree" });
        this.#buildNodes();
      } catch (err) {
        console.error("[TreeviewController] Error setting up tree:", err);
      }
    }

    /**
     * Build tree nodes with nested structure
     * @private
     */
    #buildNodes() {
      if (!this.#tree) return;

      const nodes = [
        { id: "test_id", label: "Test <strong>1</strong>", opened: true },
        { id: "test_id2", label: "Test 2", parent: "test_id" },
        { id: "test_id3", label: "Test 3", parent: "test_id" },
        { id: "test_id4", label: "Test 4", parent: "test_id2" },
        { id: "test_id5", label: "Test 5", parent: "test_id2" },
        { id: "test_id6", label: "Test 6", parent: "test_id3" },
        { id: "test_id7", label: "Test 7", parent: "test_id3" },
        { id: "test_id8", label: "Test 8" },
        { id: "test_id9", label: "Test 9" },
        { id: "test_id10", label: "Test 10", parent: "test_id8" },
        { id: "test_id11", label: "Test 11", parent: "test_id8" },
        { id: "test_id12", label: "Test 12", parent: "test_id10" },
        { id: "test_id13", label: "Test 13", parent: "test_id10" },
        { id: "test_id14", label: "Test 14", parent: "test_id10" },
        { id: "test_id15", label: "Test 15" },
      ];

      nodes.forEach(({ id, label, parent, opened }) => {
        this.#tree.add({ id, label, parent, opened });
      });
    }

    /**
     * Destroy tree instance
     */
    destroy() {
      this.#tree = null;
      document.body?.removeAttribute(TreeviewController.#DATA_INIT);
    }
  }

  /**
   * Initialize treeview when DOM ready
   */
  const initTreeview = () => {
    try {
      new TreeviewController().init();
    } catch (err) {
      console.error("[TreeviewController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initTreeview)
    : initTreeview();
})();
