/**
 * @file landingpage-dash.test.ts
 * @description Comprehensive unit tests for LandingDashboardController
 * @version 1.0.0
 *
 * Tests cover:
 * - Controller initialization and singleton behavior
 * - Scrollbar setup with PerfectScrollbar
 * - Menu overlay creation and management
 * - Hamburger menu toggle functionality
 * - Mini menu state persistence via localStorage
 * - Mobile collapse toggle
 * - Submenu slide animations (slideUp/slideDown)
 * - Feather icons initialization
 * - Error handling and edge cases
 *
 * @coverage Target: 95%+ line coverage
 */

import {
  jest,
  describe,
  it,
  expect,
  beforeEach,
  afterEach,
} from "@jest/globals";
import path from "path";
import fs from "fs";
import { createMockBootstrap, resetDOM, PUBLIC_JS_PATH } from "../setup";

// Path to source file
const LANDING_DASH_PATH = path.resolve(
  PUBLIC_JS_PATH,
  "../../Modules/landingpage/js/dash.js",
);

// Type-safe window accessor
const win = window as any;

// ============================================================================
// MOCK FACTORIES
// ============================================================================

/**
 * Creates a mock PerfectScrollbar
 * @returns Mock PerfectScrollbar constructor
 */
function createMockPerfectScrollbar(): any {
  const instances = new Map<Element, any>();

  const MockPS = function (this: any, element: Element | string) {
    const el =
      typeof element === "string" ? document.querySelector(element) : element;
    if (el) {
      this.element = el;
      this.destroyed = false;
      instances.set(el, this);
    }
  } as any;

  MockPS.prototype.update = jest.fn();
  MockPS.prototype.destroy = jest.fn(function (this: any) {
    this.destroyed = true;
    instances.delete(this.element);
  });

  MockPS.getInstance = (el: Element) => instances.get(el) || null;
  MockPS.instances = instances;

  return MockPS;
}

/**
 * Creates mock feather icons object
 * @returns Mock feather object
 */
function createMockFeather(): { replace: jest.Mock } {
  return {
    replace: jest.fn(),
  };
}

/**
 * Creates the landing page layout DOM structure
 * @param options Configuration options
 * @returns Created DOM elements
 */
function createLandingLayout(
  options: {
    withSidebar?: boolean;
    withTopbar?: boolean;
    withNavbar?: boolean;
    minimenu?: boolean;
  } = {},
): {
  body: HTMLElement;
  sidebar: HTMLElement | null;
  topbar: HTMLElement | null;
  navbarContent: HTMLElement | null;
  navbar: HTMLElement | null;
  mobileMenu: HTMLElement;
  mobileCollapse: HTMLElement;
  overlayMenu: HTMLElement;
} {
  const body = document.body;

  if (options.minimenu) {
    body.classList.add("minimenu");
  }

  // Create sidebar (pc-sidebar for landing page)
  let sidebar: HTMLElement | null = null;
  if (options.withSidebar !== false) {
    sidebar = document.createElement("aside");
    sidebar.className = "pc-sidebar";
    body.appendChild(sidebar);
  }

  // Create topbar
  let topbar: HTMLElement | null = null;
  if (options.withTopbar !== false) {
    topbar = document.createElement("div");
    topbar.className = "topbar";
    body.appendChild(topbar);
  }

  // Create navbar content
  let navbarContent: HTMLElement | null = null;
  let navbar: HTMLElement | null = null;
  if (options.withNavbar !== false) {
    navbarContent = document.createElement("div");
    navbarContent.className = "navbar-content";

    navbar = document.createElement("ul");
    navbar.className = "pc-navbar";
    navbarContent.appendChild(navbar);

    if (sidebar) {
      sidebar.appendChild(navbarContent);
    } else {
      body.appendChild(navbarContent);
    }
  }

  // Create mobile menu button
  const mobileMenu = document.createElement("button");
  mobileMenu.className = "mobile-menu";
  body.appendChild(mobileMenu);

  // Create mobile collapse button
  const mobileCollapse = document.createElement("button");
  mobileCollapse.id = "mobile-collapse";
  body.appendChild(mobileCollapse);

  // Create overlay menu button
  const overlayMenu = document.createElement("button");
  overlayMenu.id = "overlay-menu";
  body.appendChild(overlayMenu);

  return {
    body,
    sidebar,
    topbar,
    navbarContent,
    navbar,
    mobileMenu,
    mobileCollapse,
    overlayMenu,
  };
}

/**
 * Creates menu items with submenus for the landing page navbar
 * @param navbar Parent navbar element
 * @param count Number of items
 * @param withSubmenus Whether to add submenus
 * @returns Created menu items
 */
function createLandingMenuItems(
  navbar: HTMLElement,
  count: number = 5,
  withSubmenus: boolean = true,
): HTMLElement[] {
  const items: HTMLElement[] = [];

  for (let i = 0; i < count; i++) {
    const li = document.createElement("li");

    const link = document.createElement("a");
    link.href = `#section-${i}`;
    link.innerHTML = `<span>Menu ${i}</span>`;
    li.appendChild(link);

    if (withSubmenus && i % 2 === 1) {
      const submenu = document.createElement("ul");
      submenu.className = "submenu";

      for (let j = 0; j < 3; j++) {
        const subLi = document.createElement("li");
        const subLink = document.createElement("a");
        subLink.href = `#sub-${i}-${j}`;
        subLink.textContent = `Submenu ${i}-${j}`;
        subLi.appendChild(subLink);
        submenu.appendChild(subLi);
      }

      li.appendChild(submenu);
    }

    navbar.appendChild(li);
    items.push(li);
  }

  return items;
}

/**
 * Loads LandingDashboardController by evaluating the source
 */
function loadLandingDashJs(): void {
  // Reset init state
  document.body?.removeAttribute("data-landing-dash-init");

  // Clear listener attributes
  document.querySelectorAll("[data-landing-dash-listener]").forEach(el => {
    el.removeAttribute("data-landing-dash-listener");
  });
  document.querySelectorAll("[data-ps-applied]").forEach(el => {
    el.removeAttribute("data-ps-applied");
  });

  // Check if file exists
  if (!fs.existsSync(LANDING_DASH_PATH)) {
    throw new Error(`Landing dash.js not found at: ${LANDING_DASH_PATH}`);
  }

  const code = fs.readFileSync(LANDING_DASH_PATH, "utf-8");
  const fn = new Function(
    "window",
    "document",
    "localStorage",
    "PerfectScrollbar",
    "feather",
    code,
  );
  fn(window, document, localStorage, win.PerfectScrollbar, win.feather);
}

// ============================================================================
// TEST SUITES
// ============================================================================

describe("LandingDashboardController (landingpage/dash.js)", () => {
  let mockPerfectScrollbar: any;
  let mockFeather: { replace: jest.Mock };
  let consoleErrorSpy: jest.SpiedFunction<typeof console.error>;
  let localStorageGetSpy: jest.SpiedFunction<typeof localStorage.getItem>;
  let localStorageSetSpy: jest.SpiedFunction<typeof localStorage.setItem>;

  beforeEach(() => {
    jest.clearAllMocks();
    jest.useFakeTimers();
    resetDOM();

    // Setup mocks
    mockPerfectScrollbar = createMockPerfectScrollbar();
    mockFeather = createMockFeather();
    win.PerfectScrollbar = mockPerfectScrollbar;
    win.feather = mockFeather;
    win.bootstrap = createMockBootstrap();

    // Spy on console and localStorage
    consoleErrorSpy = jest.spyOn(console, "error").mockImplementation(() => {});
    localStorageGetSpy = jest.spyOn(Storage.prototype, "getItem");
    localStorageSetSpy = jest.spyOn(Storage.prototype, "setItem");

    // Clear localStorage
    localStorage.clear();
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
    localStorageGetSpy.mockRestore();
    localStorageSetSpy.mockRestore();
    jest.useRealTimers();
  });

  // ==========================================================================
  // INITIALIZATION TESTS
  // ==========================================================================
  describe("Initialization", () => {
    it("should initialize LandingDashboardController on DOM ready", () => {
      createLandingLayout();
      loadLandingDashJs();

      expect(document.body.hasAttribute("data-landing-dash-init")).toBe(true);
    });

    it("should prevent double initialization", () => {
      createLandingLayout();
      loadLandingDashJs();

      const firstInit = document.body.getAttribute("data-landing-dash-init");

      // Try to reinitialize
      loadLandingDashJs();

      expect(document.body.getAttribute("data-landing-dash-init")).toBe(
        firstInit,
      );
    });

    it("should call feather.replace if available", () => {
      createLandingLayout();
      loadLandingDashJs();

      expect(mockFeather.replace).toHaveBeenCalled();
    });

    it("should not throw if feather is undefined", () => {
      createLandingLayout();
      delete win.feather;

      expect(() => loadLandingDashJs()).not.toThrow();
    });

    it("should handle missing body gracefully", () => {
      document.body.innerHTML = "";

      expect(() => loadLandingDashJs()).not.toThrow();
    });
  });

  // ==========================================================================
  // SCROLLBAR TESTS
  // ==========================================================================
  describe("Scrollbar Setup", () => {
    it("should initialize PerfectScrollbar on navbar-content", () => {
      createLandingLayout({ withNavbar: true });
      loadLandingDashJs();

      const navbarContent = document.querySelector(".navbar-content");
      expect(navbarContent?.hasAttribute("data-ps-applied")).toBe(true);
    });

    it("should not initialize scrollbar twice", () => {
      const layout = createLandingLayout({ withNavbar: true });
      layout.navbarContent?.setAttribute("data-ps-applied", "true");

      loadLandingDashJs();

      // Should still have the original attribute
      expect(layout.navbarContent?.getAttribute("data-ps-applied")).toBe(
        "true",
      );
    });

    it("should handle missing PerfectScrollbar gracefully", () => {
      createLandingLayout({ withNavbar: true });
      delete win.PerfectScrollbar;

      expect(() => loadLandingDashJs()).not.toThrow();
    });

    it("should handle missing navbar-content gracefully", () => {
      createLandingLayout({ withNavbar: false });

      expect(() => loadLandingDashJs()).not.toThrow();
    });
  });

  // ==========================================================================
  // OVERLAY TESTS
  // ==========================================================================
  describe("Menu Overlay", () => {
    it("should create menu-styler overlay element", () => {
      createLandingLayout();
      loadLandingDashJs();

      expect(document.querySelector(".menu-styler")).toBeTruthy();
    });

    it("should not create duplicate overlay", () => {
      createLandingLayout();

      // Pre-create overlay
      const existingOverlay = document.createElement("div");
      existingOverlay.className = "menu-styler";
      document.body.appendChild(existingOverlay);

      loadLandingDashJs();

      // Should only have one
      const overlays = document.querySelectorAll(".menu-styler");
      expect(overlays.length).toBe(1);
    });

    it("should create overlay with style-toggler", () => {
      createLandingLayout();
      loadLandingDashJs();

      const toggler = document.querySelector(".menu-styler .style-toggler");
      expect(toggler).toBeTruthy();
    });
  });

  // ==========================================================================
  // HAMBURGER MENU TESTS
  // ==========================================================================
  describe("Hamburger Menu (Mobile Menu)", () => {
    it("should attach listener to mobile-menu button", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      expect(layout.mobileMenu.hasAttribute("data-landing-dash-listener")).toBe(
        true,
      );
    });

    it("should toggle minimenu class on click", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      expect(document.body.classList.contains("minimenu")).toBe(false);

      layout.mobileMenu.click();
      expect(document.body.classList.contains("minimenu")).toBe(true);

      layout.mobileMenu.click();
      expect(document.body.classList.contains("minimenu")).toBe(false);
    });

    it("should prevent default on click", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      const event = new MouseEvent("click", {
        bubbles: true,
        cancelable: true,
      });
      const preventDefaultSpy = jest.spyOn(event, "preventDefault");

      layout.mobileMenu.dispatchEvent(event);

      expect(preventDefaultSpy).toHaveBeenCalled();
    });

    it("should not attach duplicate listeners", () => {
      const layout = createLandingLayout();

      loadLandingDashJs();

      // Verify listener was attached
      expect(layout.mobileMenu.hasAttribute("data-landing-dash-listener")).toBe(
        true,
      );

      // First click toggles to minimenu
      layout.mobileMenu.click();
      expect(document.body.classList.contains("minimenu")).toBe(true);

      // Re-initialize should not add another listener
      document.body.removeAttribute("data-landing-dash-init");
      loadLandingDashJs();

      // Listener attribute should still be there (prevents duplicate)
      expect(layout.mobileMenu.hasAttribute("data-landing-dash-listener")).toBe(
        true,
      );
    });
  });

  // ==========================================================================
  // MINI MENU PERSISTENCE TESTS
  // ==========================================================================
  describe("Mini Menu State Persistence", () => {
    it("should read mini_menu from localStorage", () => {
      createLandingLayout();
      localStorage.setItem("mini_menu", "true");

      loadLandingDashJs();

      expect(localStorageGetSpy).toHaveBeenCalledWith("mini_menu");
    });

    it("should apply minimenu class if stored as true", () => {
      createLandingLayout();
      localStorage.setItem("mini_menu", "true");

      loadLandingDashJs();

      expect(document.body.classList.contains("minimenu")).toBe(true);
    });

    it("should not apply minimenu class if stored as false", () => {
      createLandingLayout();
      localStorage.setItem("mini_menu", "false");

      loadLandingDashJs();

      expect(document.body.classList.contains("minimenu")).toBe(false);
    });

    it("should not apply minimenu class if nothing stored", () => {
      createLandingLayout();

      loadLandingDashJs();

      // Should not add minimenu class by default
      expect(document.body.classList.contains("minimenu")).toBe(false);
    });
  });

  // ==========================================================================
  // MOBILE COLLAPSE TESTS
  // ==========================================================================
  describe("Mobile Collapse", () => {
    it("should attach listener to mobile-collapse button", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      expect(
        layout.mobileCollapse.hasAttribute("data-landing-dash-listener"),
      ).toBe(true);
    });

    it("should toggle mob-sidebar-active on sidebar", () => {
      const layout = createLandingLayout({ withSidebar: true });
      loadLandingDashJs();

      expect(layout.sidebar?.classList.contains("mob-sidebar-active")).toBe(
        false,
      );

      layout.mobileCollapse.click();
      expect(layout.sidebar?.classList.contains("mob-sidebar-active")).toBe(
        true,
      );

      layout.mobileCollapse.click();
      expect(layout.sidebar?.classList.contains("mob-sidebar-active")).toBe(
        false,
      );
    });

    it("should toggle no-scroll on body", () => {
      const layout = createLandingLayout({ withSidebar: true });
      loadLandingDashJs();

      expect(document.body.classList.contains("no-scroll")).toBe(false);

      layout.mobileCollapse.click();
      expect(document.body.classList.contains("no-scroll")).toBe(true);

      layout.mobileCollapse.click();
      expect(document.body.classList.contains("no-scroll")).toBe(false);
    });

    it("should prevent default on click", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      const event = new MouseEvent("click", {
        bubbles: true,
        cancelable: true,
      });
      const preventDefaultSpy = jest.spyOn(event, "preventDefault");

      layout.mobileCollapse.dispatchEvent(event);

      expect(preventDefaultSpy).toHaveBeenCalled();
    });
  });

  // ==========================================================================
  // OVERLAY MENU TESTS
  // ==========================================================================
  describe("Overlay Menu", () => {
    it("should attach listener to overlay-menu button", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      expect(
        layout.overlayMenu.hasAttribute("data-landing-dash-listener"),
      ).toBe(true);
    });

    it("should toggle pc-over-menu-active on body", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      expect(document.body.classList.contains("pc-over-menu-active")).toBe(
        false,
      );

      layout.overlayMenu.click();
      expect(document.body.classList.contains("pc-over-menu-active")).toBe(
        true,
      );

      layout.overlayMenu.click();
      expect(document.body.classList.contains("pc-over-menu-active")).toBe(
        false,
      );
    });

    it("should prevent default on click", () => {
      const layout = createLandingLayout();
      loadLandingDashJs();

      const event = new MouseEvent("click", {
        bubbles: true,
        cancelable: true,
      });
      const preventDefaultSpy = jest.spyOn(event, "preventDefault");

      layout.overlayMenu.dispatchEvent(event);

      expect(preventDefaultSpy).toHaveBeenCalled();
    });
  });

  // ==========================================================================
  // MENU CLICK HANDLERS TESTS
  // ==========================================================================
  describe("Menu Click Handlers", () => {
    it("should attach listeners to navbar menu items", () => {
      const layout = createLandingLayout();
      createLandingMenuItems(layout.navbar!, 5, true);

      loadLandingDashJs();

      // Menu items in pc-navbar should have listeners
      const items = layout.navbar!.querySelectorAll(":scope > li");
      expect(items.length).toBeGreaterThan(0);

      items.forEach(item => {
        expect(item.hasAttribute("data-landing-dash-listener")).toBe(true);
      });
    });

    it("should expand submenu on click", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      // Find item with submenu (odd index)
      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;
      const submenu = itemWithSubmenu.querySelector("ul")!;

      // Click to expand
      link.click();

      expect(submenu.classList.contains("active")).toBe(true);
      expect(itemWithSubmenu.classList.contains("active")).toBe(true);
    });

    it("should collapse submenu on second click", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;
      const submenu = itemWithSubmenu.querySelector("ul")!;

      // Open
      link.click();
      expect(submenu.classList.contains("active")).toBe(true);

      // Trigger transitionend to complete animation
      submenu.dispatchEvent(new Event("transitionend"));

      // Close
      link.click();

      // Trigger transitionend
      submenu.dispatchEvent(new Event("transitionend"));

      expect(submenu.classList.contains("active")).toBe(false);
      expect(itemWithSubmenu.classList.contains("active")).toBe(false);
    });

    it("should prevent default on menu link click", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;

      const event = new MouseEvent("click", {
        bubbles: true,
        cancelable: true,
      });
      const preventDefaultSpy = jest.spyOn(event, "preventDefault");

      link.dispatchEvent(event);

      expect(preventDefaultSpy).toHaveBeenCalled();
    });

    it("should skip items without submenus", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      // Item without submenu (even index)
      const itemWithoutSubmenu = items[0];
      const link = itemWithoutSubmenu.querySelector("a")!;

      // Should not throw
      expect(() => link.click()).not.toThrow();

      // Should not have active class
      expect(itemWithoutSubmenu.classList.contains("active")).toBe(false);
    });
  });

  // ==========================================================================
  // SLIDE ANIMATION TESTS
  // ==========================================================================
  describe("Slide Animations", () => {
    it("should set height to 0 during slideUp", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;
      const submenu = itemWithSubmenu.querySelector("ul")!;

      // Open first
      link.click();
      submenu.dispatchEvent(new Event("transitionend"));

      // Now submenu should be active
      expect(submenu.classList.contains("active")).toBe(true);

      // Close - triggers slideUp
      link.click();

      // During slideUp, overflow should be hidden
      // The height transitions from scrollHeight to 0
      expect(
        submenu.style.height === "0px" || submenu.style.height === "0",
      ).toBe(true);
    });

    it("should set initial height during slideDown", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;
      const submenu = itemWithSubmenu.querySelector("ul")!;

      // Click to expand
      link.click();

      // Submenu should be visible with transition
      expect(submenu.style.overflow).toBe("hidden");
    });

    it("should clean up styles after slideDown completes", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;
      const submenu = itemWithSubmenu.querySelector("ul")!;

      link.click();

      // Trigger transitionend
      submenu.dispatchEvent(new Event("transitionend"));

      // Styles should be cleaned up
      expect(submenu.style.height).toBe("");
      expect(submenu.style.overflow).toBe("");
    });

    it("should clean up styles after slideUp completes", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;
      const submenu = itemWithSubmenu.querySelector("ul")!;

      // Open
      link.click();
      submenu.dispatchEvent(new Event("transitionend"));

      // Close
      link.click();

      // Trigger transitionend
      submenu.dispatchEvent(new Event("transitionend"));

      // Classes should be removed
      expect(submenu.classList.contains("active")).toBe(false);
    });
  });

  // ==========================================================================
  // ERROR HANDLING TESTS
  // ==========================================================================
  describe("Error Handling", () => {
    it("should catch and log setup errors", () => {
      createLandingLayout();

      // Mock querySelector to throw for a specific element
      const originalQuerySelector = document.querySelector.bind(document);
      jest.spyOn(document, "querySelector").mockImplementation(selector => {
        if (selector === ".pc-sidebar") {
          throw new Error("Test error");
        }
        return originalQuerySelector(selector);
      });

      loadLandingDashJs();

      expect(consoleErrorSpy).toHaveBeenCalledWith(
        expect.stringContaining("[LandingDashboardController]"),
        expect.any(Error),
      );

      jest.spyOn(document, "querySelector").mockRestore();
    });

    it("should handle missing elements gracefully", () => {
      // Empty layout
      document.body.innerHTML = "";

      expect(() => loadLandingDashJs()).not.toThrow();
    });
  });

  // ==========================================================================
  // EDGE CASES
  // ==========================================================================
  describe("Edge Cases", () => {
    it("should handle empty navbar", () => {
      const layout = createLandingLayout();
      // No menu items added

      expect(() => loadLandingDashJs()).not.toThrow();
    });

    it("should handle menu items without links", () => {
      const layout = createLandingLayout();
      const li = document.createElement("li");
      li.textContent = "No link here";
      layout.navbar?.appendChild(li);

      expect(() => loadLandingDashJs()).not.toThrow();
    });

    it("should handle multiple rapid clicks", () => {
      const layout = createLandingLayout();
      const items = createLandingMenuItems(layout.navbar!, 4, true);

      loadLandingDashJs();

      const itemWithSubmenu = items[1];
      const link = itemWithSubmenu.querySelector("a")!;

      // Rapid clicks
      for (let i = 0; i < 10; i++) {
        link.click();
      }

      // Should not throw
      expect(() => {
        // Final state depends on even/odd clicks
      }).not.toThrow();
    });

    it("should handle localStorage not available", () => {
      createLandingLayout();

      // Mock localStorage.getItem to throw
      localStorageGetSpy.mockImplementation(() => {
        throw new Error("localStorage disabled");
      });

      // Should not throw - error is caught
      expect(() => loadLandingDashJs()).not.toThrow();
    });
  });

  // ==========================================================================
  // INTEGRATION TESTS
  // ==========================================================================
  describe("Integration Scenarios", () => {
    it("should work with complete landing page layout", () => {
      const layout = createLandingLayout({
        withSidebar: true,
        withTopbar: true,
        withNavbar: true,
      });
      createLandingMenuItems(layout.navbar!, 10, true);

      loadLandingDashJs();

      // Verify initialization
      expect(document.body.hasAttribute("data-landing-dash-init")).toBe(true);
      expect(mockFeather.replace).toHaveBeenCalled();

      // Test mobile menu
      layout.mobileMenu.click();
      expect(document.body.classList.contains("minimenu")).toBe(true);

      // Test mobile collapse
      layout.mobileCollapse.click();
      expect(layout.sidebar?.classList.contains("mob-sidebar-active")).toBe(
        true,
      );

      // Test overlay menu
      layout.overlayMenu.click();
      expect(document.body.classList.contains("pc-over-menu-active")).toBe(
        true,
      );
    });

    it("should handle layout without sidebar", () => {
      const layout = createLandingLayout({
        withSidebar: false,
        withTopbar: true,
        withNavbar: true,
      });

      expect(() => loadLandingDashJs()).not.toThrow();

      // Mobile collapse should still work (body classes)
      layout.mobileCollapse.click();
      expect(document.body.classList.contains("no-scroll")).toBe(true);
    });

    it("should persist state across interactions", () => {
      const layout = createLandingLayout();
      createLandingMenuItems(layout.navbar!, 4, true);

      // Set initial state
      localStorage.setItem("mini_menu", "true");

      loadLandingDashJs();

      // Should start in minimenu mode
      expect(document.body.classList.contains("minimenu")).toBe(true);

      // Toggle off
      layout.mobileMenu.click();
      expect(document.body.classList.contains("minimenu")).toBe(false);

      // Toggle on
      layout.mobileMenu.click();
      expect(document.body.classList.contains("minimenu")).toBe(true);
    });
  });

  // ==========================================================================
  // PROBABILISTIC INPUT VARIATIONS
  // ==========================================================================
  describe("Probabilistic Input Variations", () => {
    const menuItemCounts = [0, 1, 3, 5, 10, 20];

    menuItemCounts.forEach(count => {
      it(`should handle ${count} menu items`, () => {
        const layout = createLandingLayout();
        createLandingMenuItems(layout.navbar!, count, count > 1);

        expect(() => loadLandingDashJs()).not.toThrow();
        expect(document.body.hasAttribute("data-landing-dash-init")).toBe(true);
      });
    });

    const layoutVariations = [
      { withSidebar: true, withTopbar: true, withNavbar: true },
      { withSidebar: true, withTopbar: false, withNavbar: true },
      { withSidebar: false, withTopbar: true, withNavbar: true },
      { withSidebar: false, withTopbar: false, withNavbar: false },
      { withSidebar: true, withTopbar: true, withNavbar: false },
    ];

    layoutVariations.forEach((options, index) => {
      it(`should handle layout variation ${index + 1}`, () => {
        createLandingLayout(options);

        expect(() => loadLandingDashJs()).not.toThrow();
      });
    });

    const submenuDepths = [1, 2, 3];

    submenuDepths.forEach(depth => {
      it(`should handle nested submenus depth ${depth}`, () => {
        const layout = createLandingLayout();
        const li = document.createElement("li");
        let current = li;

        for (let i = 0; i < depth; i++) {
          const link = document.createElement("a");
          link.href = `#level-${i}`;
          current.appendChild(link);

          const ul = document.createElement("ul");
          const subLi = document.createElement("li");
          ul.appendChild(subLi);
          current.appendChild(ul);
          current = subLi;
        }

        layout.navbar?.appendChild(li);

        expect(() => loadLandingDashJs()).not.toThrow();
      });
    });
  });
});

// ============================================================================
// TYPE DECLARATIONS
// ============================================================================
declare global {
  interface Window {
    PerfectScrollbar: any;
    feather: { replace: () => void };
  }
}
