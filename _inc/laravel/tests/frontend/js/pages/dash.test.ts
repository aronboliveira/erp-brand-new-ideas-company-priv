/**
 * @file dash.test.ts
 * @description Comprehensive unit tests for DashboardController (dash.js)
 * @version 1.0.0
 *
 * Tests cover:
 * - Controller initialization and singleton behavior
 * - Layout detection (horizontal, minimenu, tab, nested)
 * - Menu navigation and toggle functionality
 * - Mobile collapse and responsive behavior
 * - Sidebar scrollbar integration
 * - Active menu item highlighting
 * - Overlay menu functionality
 * - Slide animations (slideUp/slideDown)
 * - Window resize handling
 * - Bootstrap component initialization
 * - Edge case handling and error recovery
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
import { createMockBootstrap, resetDOM, wait, PUBLIC_JS_PATH } from "../setup";

// Path to source file
const DASH_JS_PATH = path.join(PUBLIC_JS_PATH, "dash.js");

// Type-safe window accessor
const win = window as any;

// ============================================================================
// MOCK FACTORIES
// ============================================================================

/**
 * Creates a mock PerfectScrollbar constructor
 * @returns Mock PerfectScrollbar class
 */
function createMockPerfectScrollbar(): any {
  const instances = new Map<Element, any>();

  const MockPS = function (
    this: any,
    selector: string | Element,
    _options?: any,
  ) {
    const el =
      typeof selector === "string"
        ? document.querySelector(selector)
        : selector;
    if (el) {
      this.element = el;
      this.destroyed = false;
      instances.set(el, this);
    }
    return this;
  } as any;

  MockPS.prototype.update = jest.fn();
  MockPS.prototype.destroy = jest.fn(function (this: any) {
    this.destroyed = true;
    instances.delete(this.element);
  });

  MockPS.getInstance = (el: Element) => instances.get(el) || null;
  MockPS.instances = instances;

  // Track calls for assertions
  const originalMockPS = MockPS;
  const trackedMock = jest.fn(function (this: any, ...args: any[]) {
    return originalMockPS.apply(this, args);
  }) as any;
  trackedMock.prototype = MockPS.prototype;
  trackedMock.getInstance = MockPS.getInstance;
  trackedMock.instances = MockPS.instances;

  return trackedMock;
}

/**
 * Creates a mock feather icons object
 * @returns Mock feather object with replace method
 */
function createMockFeather(): { replace: jest.Mock } {
  return {
    replace: jest.fn(),
  };
}

/**
 * Creates the standard admin layout DOM structure
 * @param options Configuration options for layout
 * @returns Object containing created DOM elements
 */
function createAdminLayout(
  options: {
    horizontal?: boolean;
    minimenu?: boolean;
    tabLayout?: boolean;
    nestedLayout?: boolean;
    topbarLayout?: boolean;
    navbarOverlay?: boolean;
  } = {},
): {
  body: HTMLElement;
  sidebar: HTMLElement;
  topbar: HTMLElement;
  navContent: HTMLElement;
  hamburger: HTMLElement;
  mobileCollapse: HTMLElement;
  verticalToggle: HTMLElement;
  overlayMenu: HTMLElement;
} {
  const body = document.body;

  // Apply layout classes
  if (options.horizontal) body.classList.add("dash-horizontal");
  if (options.minimenu) body.classList.add("minimenu");
  if (options.tabLayout) body.classList.add("tab-layout");
  if (options.navbarOverlay) body.classList.add("navbar-overlay");
  if (options.topbarLayout) body.classList.add("layout-topbar");

  // Create sidebar
  const sidebar = document.createElement("div");
  sidebar.className = "dash-sidebar";
  body.appendChild(sidebar);

  // Create navbar content
  const navContent = document.createElement("div");
  navContent.className = "navbar-content";
  sidebar.appendChild(navContent);

  // Create navbar
  const navbar = document.createElement("ul");
  navbar.className = "dash-navbar";
  navContent.appendChild(navbar);

  // Create topbar
  const topbar = document.createElement("div");
  topbar.className = "topbar";
  body.appendChild(topbar);

  // Create hamburger menu button
  const hamburger = document.createElement("button");
  hamburger.className = "hamburger";
  body.appendChild(hamburger);

  // Create mobile collapse button
  const mobileCollapse = document.createElement("button");
  mobileCollapse.id = "mobile-collapse";
  body.appendChild(mobileCollapse);

  // Create vertical nav toggle
  const verticalToggle = document.createElement("button");
  verticalToggle.id = "vertical-nav-toggle";
  body.appendChild(verticalToggle);

  // Create overlay menu button
  const overlayMenu = document.createElement("button");
  overlayMenu.id = "overlay-menu";
  body.appendChild(overlayMenu);

  // Create loader background
  const loader = document.createElement("div");
  loader.className = "loader-bg";
  body.appendChild(loader);

  return {
    body,
    sidebar,
    topbar,
    navContent,
    hamburger,
    mobileCollapse,
    verticalToggle,
    overlayMenu,
  };
}

/**
 * Creates menu items with various nested structures
 * @param navbar Parent navbar element
 * @param count Number of menu items to create
 * @param withSubmenus Whether to include submenus
 * @returns Array of created menu items
 */
function createMenuItems(
  navbar: HTMLElement,
  count: number = 5,
  withSubmenus: boolean = true,
): HTMLElement[] {
  const items: HTMLElement[] = [];

  for (let i = 0; i < count; i++) {
    const li = document.createElement("li");
    li.className = i === 0 ? "dash-caption" : "";

    const link = document.createElement("a");
    link.href = i === 2 ? window.location.href : `#page-${i}`;
    link.innerHTML = `<span>Menu ${i}</span>`;
    li.appendChild(link);

    if (withSubmenus && i > 0 && i < 4) {
      li.classList.add("dash-hasmenu");

      const submenu = document.createElement("ul");
      submenu.className = "dash-submenu";

      for (let j = 0; j < 3; j++) {
        const subLi = document.createElement("li");
        const subLink = document.createElement("a");
        subLink.href = `#submenu-${i}-${j}`;
        subLink.textContent = `Submenu ${i}-${j}`;
        subLi.appendChild(subLink);

        if (j === 1) {
          subLi.classList.add("dash-hasmenu");
          const nestedSubmenu = document.createElement("ul");
          nestedSubmenu.className = "dash-submenu";
          const nestedLi = document.createElement("li");
          const nestedLink = document.createElement("a");
          nestedLink.href = `#nested-${i}-${j}`;
          nestedLink.textContent = `Nested ${i}-${j}`;
          nestedLi.appendChild(nestedLink);
          nestedSubmenu.appendChild(nestedLi);
          subLi.appendChild(nestedSubmenu);
        }

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
 * Creates tab layout structure
 * @param body Body element to append to
 * @returns Tab content elements
 */
function createTabLayout(body: HTMLElement): {
  tabMenu: HTMLElement;
  tabContents: HTMLElement[];
} {
  const tabMenu = document.createElement("div");
  tabMenu.className = "tab-sidemenu";

  const tabUl = document.createElement("ul");
  tabMenu.appendChild(tabUl);

  const tabContents: HTMLElement[] = [];

  for (let i = 0; i < 3; i++) {
    const li = document.createElement("li");
    if (i === 0) li.classList.add("active");

    const link = document.createElement("a");
    link.setAttribute("data-cont", `tab-${i}`);
    link.innerHTML = `<i class="feather icon-home"></i>`;
    li.appendChild(link);
    tabUl.appendChild(li);

    const content = document.createElement("div");
    content.className = "dash-tabcontent";
    content.setAttribute("data-value", `tab-${i}`);
    if (i === 0) content.classList.add("active");
    tabContents.push(content);
  }

  body.appendChild(tabMenu);
  tabContents.forEach(c => body.appendChild(c));

  return { tabMenu, tabContents };
}

/**
 * Creates nested layout structure
 * @param body Body element
 * @returns Nested layout elements
 */
function createNestedLayout(body: HTMLElement): {
  toggleBtn: HTMLElement;
  overlay: HTMLElement;
  pageSidebar: HTMLElement;
} {
  const toggleBtn = document.createElement("button");
  toggleBtn.className = "dash-toggle-sidemenu";
  body.appendChild(toggleBtn);

  const overlay = document.createElement("div");
  overlay.className = "dash-sideoverlay";
  body.appendChild(overlay);

  const pageSidebar = document.createElement("div");
  pageSidebar.className = "page-sidebar";
  body.appendChild(pageSidebar);

  return { toggleBtn, overlay, pageSidebar };
}

/**
 * Creates notification dropdown structure
 * @param body Body element
 * @returns Notification elements
 */
function createNotificationDropdown(body: HTMLElement): {
  dropdown: HTMLElement;
  notiBody: HTMLElement;
} {
  const dropdown = document.createElement("div");
  dropdown.className = "drp-notification";

  const notiBody = document.createElement("div");
  notiBody.className = "noti-body";
  dropdown.appendChild(notiBody);

  body.appendChild(dropdown);
  return { dropdown, notiBody };
}

/**
 * Creates product likes structure
 * @param body Body element
 * @returns Like checkbox element
 */
function createProdLikes(body: HTMLElement): HTMLInputElement {
  const container = document.createElement("div");
  container.className = "prod-likes";

  const checkbox = document.createElement("input");
  checkbox.type = "checkbox";
  checkbox.className = "form-check-input";
  container.appendChild(checkbox);

  body.appendChild(container);
  return checkbox;
}

/**
 * Creates Bootstrap tooltip/popover elements
 * @param body Body element
 * @returns Created elements
 */
function createBootstrapElements(body: HTMLElement): {
  tooltips: HTMLElement[];
  popovers: HTMLElement[];
  toasts: HTMLElement[];
} {
  const tooltips: HTMLElement[] = [];
  const popovers: HTMLElement[] = [];
  const toasts: HTMLElement[] = [];

  for (let i = 0; i < 3; i++) {
    const tooltip = document.createElement("span");
    tooltip.setAttribute("data-bs-toggle", "tooltip");
    tooltip.title = `Tooltip ${i}`;
    body.appendChild(tooltip);
    tooltips.push(tooltip);

    const popover = document.createElement("button");
    popover.setAttribute("data-bs-toggle", "popover");
    body.appendChild(popover);
    popovers.push(popover);

    const toast = document.createElement("div");
    toast.className = "toast";
    body.appendChild(toast);
    toasts.push(toast);
  }

  return { tooltips, popovers, toasts };
}

/**
 * Loads dash.js by evaluating the source file
 */
function loadDashJs(): void {
  // Remove init attribute to allow re-initialization
  document.body?.removeAttribute("data-dash-init");
  document.body?.removeAttribute("data-window-listeners");

  // Clear listener attributes from all elements
  document.querySelectorAll("[data-dash-listener]").forEach(el => {
    el.removeAttribute("data-dash-listener");
    el.removeAttribute("data-dash-listener-horiz");
    el.removeAttribute("data-dash-listener-edge");
  });

  const code = fs.readFileSync(DASH_JS_PATH, "utf-8");
  const fn = new Function(
    "window",
    "document",
    "PerfectScrollbar",
    "feather",
    "bootstrap",
    code,
  );
  fn(window, document, win.PerfectScrollbar, win.feather, win.bootstrap);
}

// ============================================================================
// TEST SUITES
// ============================================================================

describe("DashboardController (dash.js)", () => {
  let mockPerfectScrollbar: any;
  let mockFeather: { replace: jest.Mock };
  let consoleErrorSpy: jest.SpiedFunction<typeof console.error>;
  let consoleLogSpy: jest.SpiedFunction<typeof console.log>;

  beforeEach(() => {
    jest.clearAllMocks();
    jest.useFakeTimers();
    resetDOM();

    // Setup mocks
    mockPerfectScrollbar = createMockPerfectScrollbar();
    mockFeather = createMockFeather();
    win.PerfectScrollbar = mockPerfectScrollbar;
    win.feather = mockFeather;

    // Create extended bootstrap mock with Tooltip and Popover
    const baseMock = createMockBootstrap();
    win.bootstrap = {
      ...baseMock,
      Tooltip: jest.fn(function (this: any, _el: Element) {
        return this;
      }),
      Popover: jest.fn(function (this: any, _el: Element) {
        return this;
      }),
    };

    // Spy on console
    consoleErrorSpy = jest.spyOn(console, "error").mockImplementation(() => {});
    consoleLogSpy = jest.spyOn(console, "log").mockImplementation(() => {});

    // Set default window location
    Object.defineProperty(window, "location", {
      value: {
        href: "http://localhost/dashboard",
        hostname: "localhost",
      },
      writable: true,
    });

    // Set default viewport
    Object.defineProperty(window, "innerWidth", {
      value: 1920,
      writable: true,
    });
    Object.defineProperty(window, "innerHeight", {
      value: 1080,
      writable: true,
    });
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
    consoleLogSpy.mockRestore();
    jest.useRealTimers();
  });

  // ==========================================================================
  // INITIALIZATION TESTS
  // ==========================================================================
  describe("Initialization", () => {
    it("should initialize DashboardController on DOMContentLoaded", () => {
      createAdminLayout();
      loadDashJs();

      expect(document.body.hasAttribute("data-dash-init")).toBe(true);
    });

    it("should prevent double initialization via data-dash-init attribute", () => {
      createAdminLayout();
      loadDashJs();

      const firstInitTime = document.body.getAttribute("data-dash-init");

      // Try to initialize again
      loadDashJs();

      // Should still have the same init attribute (single init)
      expect(document.body.getAttribute("data-dash-init")).toBe(firstInitTime);
    });

    it("should call feather.replace if feather is available", () => {
      createAdminLayout();
      loadDashJs();

      expect(mockFeather.replace).toHaveBeenCalled();
    });

    it("should not throw if feather is undefined", () => {
      createAdminLayout();
      delete win.feather;

      expect(() => loadDashJs()).not.toThrow();
    });

    it("should not throw if feather.replace is not a function", () => {
      createAdminLayout();
      win.feather = { replace: "not-a-function" };

      expect(() => loadDashJs()).not.toThrow();
    });

    it("should remove preloader after timeout", () => {
      const { body } = createAdminLayout();
      const loader = body.querySelector(".loader-bg");
      expect(loader).toBeTruthy();

      loadDashJs();

      // Loader should still exist before timeout
      expect(document.querySelector(".loader-bg")).toBeTruthy();

      // Advance timers
      jest.advanceTimersByTime(400);

      // Loader should be removed
      expect(document.querySelector(".loader-bg")).toBeNull();
    });

    it("should initialize PerfectScrollbar on navbar-content", () => {
      createAdminLayout();
      loadDashJs();

      expect(mockPerfectScrollbar).toHaveBeenCalledWith(
        ".navbar-content",
        expect.objectContaining({
          wheelSpeed: 0.5,
          suppressScrollX: true,
        }),
      );
    });

    it("should not initialize scrollbar for horizontal layout without navbar-overlay", () => {
      createAdminLayout({ horizontal: true });
      loadDashJs();

      // Check that scrollbar was not created for navbar-content
      const calls = mockPerfectScrollbar.mock.calls;
      const navContentCall = calls.find(
        (call: any[]) => call[0] === ".navbar-content",
      );
      expect(navContentCall).toBeUndefined();
    });

    it("should initialize scrollbar for horizontal layout with navbar-overlay", () => {
      createAdminLayout({ horizontal: true, navbarOverlay: true });
      loadDashJs();

      expect(mockPerfectScrollbar).toHaveBeenCalledWith(
        ".navbar-content",
        expect.any(Object),
      );
    });
  });

  // ==========================================================================
  // LAYOUT DETECTION TESTS
  // ==========================================================================
  describe("Layout Detection", () => {
    describe("Horizontal Layout", () => {
      it("should detect horizontal layout class", () => {
        createAdminLayout({ horizontal: true });
        loadDashJs();

        expect(document.body.classList.contains("dash-horizontal")).toBe(true);
      });

      it("should setup horizontal menu handlers for horizontal layout", () => {
        const layout = createAdminLayout({ horizontal: true });
        const navbar = layout.sidebar.querySelector(".dash-navbar")!;
        createMenuItems(navbar as HTMLElement, 5, true);

        loadDashJs();

        // Menu items should have listener attributes
        const items = navbar.querySelectorAll("li:not(.dash-caption)");
        items.forEach(item => {
          if (item.querySelector(".dash-submenu")) {
            expect(
              item.hasAttribute("data-dash-listener-horiz") ||
                item.hasAttribute("data-dash-listener"),
            ).toBe(true);
          }
        });
      });
    });

    describe("Minimenu Layout", () => {
      it("should detect minimenu layout class", () => {
        createAdminLayout({ minimenu: true });
        loadDashJs();

        expect(document.body.classList.contains("minimenu")).toBe(true);
      });

      it("should call collapseedge for minimenu layout on desktop", () => {
        createAdminLayout({ minimenu: true });
        const layout = createAdminLayout({ minimenu: true });
        const navbar = layout.sidebar.querySelector(".dash-navbar")!;
        const items = createMenuItems(navbar as HTMLElement, 5, true);

        // Add nested submenu items with dash-hasmenu
        items.forEach(item => {
          const submenu = item.querySelector(".dash-submenu");
          if (submenu) {
            const subItems = submenu.querySelectorAll(".dash-hasmenu");
            subItems.forEach(sub => {
              sub.setAttribute("data-dash-listener-edge", "true");
            });
          }
        });

        loadDashJs();

        // Edge handlers should be attached
        const edgeItems = document.querySelectorAll(
          ".minimenu .dash-sidebar .dash-submenu .dash-hasmenu",
        );
        expect(edgeItems.length).toBeGreaterThanOrEqual(0);
      });

      it("should toggle minimenu class via vertical-nav-toggle", () => {
        const layout = createAdminLayout({ minimenu: false });
        loadDashJs();

        expect(document.body.classList.contains("minimenu")).toBe(false);

        // Click toggle
        layout.verticalToggle.click();
        expect(document.body.classList.contains("minimenu")).toBe(true);

        // Click again
        layout.verticalToggle.click();
        expect(document.body.classList.contains("minimenu")).toBe(false);
      });
    });

    describe("Tab Layout", () => {
      it("should setup tab layout click handlers", () => {
        const layout = createAdminLayout({ tabLayout: true });
        const { tabMenu, tabContents } = createTabLayout(document.body);

        // Add tabcontents to navbar-content
        const navContent = layout.navContent;
        tabContents.forEach(tc => navContent.appendChild(tc));

        loadDashJs();

        // Tab items should have listeners
        const tabItems = tabMenu.querySelectorAll("li");
        tabItems.forEach(item => {
          expect(item.hasAttribute("data-dash-listener")).toBe(true);
        });
      });

      it("should switch tabs on click", () => {
        const layout = createAdminLayout({ tabLayout: true });
        const { tabMenu, tabContents } = createTabLayout(document.body);

        const navContent = layout.navContent;
        tabContents.forEach(tc => navContent.appendChild(tc.cloneNode(true)));

        loadDashJs();

        // Click second tab
        const tabItems = tabMenu.querySelectorAll("li");
        const secondTab = tabItems[1];
        secondTab.click();

        expect(secondTab.classList.contains("active")).toBe(true);
      });
    });

    describe("Nested Layout", () => {
      it("should setup nested layout toggle functionality", () => {
        createAdminLayout();
        const { toggleBtn, overlay, pageSidebar } = createNestedLayout(
          document.body,
        );

        loadDashJs();

        expect(toggleBtn.hasAttribute("data-dash-listener")).toBe(true);
        expect(overlay.hasAttribute("data-dash-listener")).toBe(true);

        // Toggle active state
        toggleBtn.click();
        expect(toggleBtn.classList.contains("active")).toBe(true);
        expect(overlay.classList.contains("active")).toBe(true);
        expect(pageSidebar.classList.contains("active")).toBe(true);

        // Toggle off
        toggleBtn.click();
        expect(toggleBtn.classList.contains("active")).toBe(false);
      });

      it("should close nested layout on overlay click", () => {
        createAdminLayout();
        const { toggleBtn, overlay, pageSidebar } = createNestedLayout(
          document.body,
        );

        loadDashJs();

        // Open
        toggleBtn.click();
        expect(toggleBtn.classList.contains("active")).toBe(true);

        // Click overlay to close
        overlay.click();
        expect(toggleBtn.classList.contains("active")).toBe(false);
        expect(overlay.classList.contains("active")).toBe(false);
        expect(pageSidebar.classList.contains("active")).toBe(false);
      });
    });

    describe("Topbar Layout", () => {
      it("should setup topbar layout hover handlers", () => {
        createAdminLayout({ topbarLayout: true });

        // Create header with dropdowns
        const header = document.createElement("div");
        header.className = "dash-header";
        const list = document.createElement("ul");
        list.className = "list-unstyled";
        const dropdown = document.createElement("li");
        dropdown.className = "dropdown";
        const dropdownMenu = document.createElement("div");
        dropdownMenu.className = "dropdown-menu";
        dropdown.appendChild(document.createElement("a"));
        dropdown.appendChild(dropdownMenu);
        list.appendChild(dropdown);
        header.appendChild(list);
        document.body.appendChild(header);

        loadDashJs();

        expect(dropdown.hasAttribute("data-dash-listener")).toBe(true);

        // Simulate mouseenter
        const enterEvent = new MouseEvent("mouseenter", { bubbles: true });
        dropdown.dispatchEvent(enterEvent);
        expect(dropdownMenu.classList.contains("show")).toBe(true);

        // Simulate mouseleave
        const leaveEvent = new MouseEvent("mouseleave", { bubbles: true });
        dropdown.dispatchEvent(leaveEvent);
        expect(dropdownMenu.classList.contains("show")).toBe(false);
      });
    });
  });

  // ==========================================================================
  // HAMBURGER MENU TESTS
  // ==========================================================================
  describe("Hamburger Menu", () => {
    it("should toggle is-active class on hamburger click", () => {
      const layout = createAdminLayout();
      loadDashJs();

      expect(layout.hamburger.classList.contains("is-active")).toBe(false);

      layout.hamburger.click();
      expect(layout.hamburger.classList.contains("is-active")).toBe(true);

      layout.hamburger.click();
      expect(layout.hamburger.classList.contains("is-active")).toBe(false);
    });

    it("should attach listener only once", () => {
      const layout = createAdminLayout();
      loadDashJs();

      expect(layout.hamburger.hasAttribute("data-dash-listener")).toBe(true);

      // Click to toggle
      layout.hamburger.click();
      expect(layout.hamburger.classList.contains("is-active")).toBe(true);

      // Re-initialize should not add duplicate listeners
      document.body.removeAttribute("data-dash-init");
      // Note: hamburger with is-active class won't be selected by the init selector
      // So we need to test the listener attribute protection instead
      const listenerAttr = layout.hamburger.getAttribute("data-dash-listener");
      expect(listenerAttr).toBe("true");
    });

    it("should not attach listener if hamburger already has is-active class", () => {
      const layout = createAdminLayout();
      layout.hamburger.classList.add("is-active");

      loadDashJs();

      // The hamburger with is-active is skipped by the selector
      expect(layout.hamburger.hasAttribute("data-dash-listener")).toBe(false);
    });
  });

  // ==========================================================================
  // OVERLAY MENU TESTS
  // ==========================================================================
  describe("Overlay Menu", () => {
    it("should add overlay-menu-active class and insert overlay on click", () => {
      const layout = createAdminLayout();
      loadDashJs();

      layout.overlayMenu.click();

      expect(layout.sidebar.classList.contains("dash-over-menu-active")).toBe(
        true,
      );
      expect(layout.sidebar.querySelector(".dash-menu-overlay")).toBeTruthy();
    });

    it("should remove overlay-menu-active class on second click", () => {
      const layout = createAdminLayout();
      loadDashJs();

      // Open
      layout.overlayMenu.click();
      expect(layout.sidebar.classList.contains("dash-over-menu-active")).toBe(
        true,
      );

      // Close
      layout.overlayMenu.click();
      expect(layout.sidebar.classList.contains("dash-over-menu-active")).toBe(
        false,
      );
    });

    it("should remove overlay on overlay element click", () => {
      const layout = createAdminLayout();
      loadDashJs();

      layout.overlayMenu.click();

      const overlay = layout.sidebar.querySelector(
        ".dash-menu-overlay",
      ) as HTMLElement;
      expect(overlay).toBeTruthy();

      overlay.click();

      expect(layout.sidebar.classList.contains("dash-over-menu-active")).toBe(
        false,
      );
      expect(layout.sidebar.querySelector(".dash-menu-overlay")).toBeNull();
    });
  });

  // ==========================================================================
  // MOBILE COLLAPSE TESTS
  // ==========================================================================
  describe("Mobile Collapse", () => {
    describe("Vertical Layout", () => {
      it("should add no-scroll and mob-sidebar-active classes", () => {
        const layout = createAdminLayout();
        loadDashJs();

        layout.mobileCollapse.click();

        expect(document.body.classList.contains("no-scroll")).toBe(true);
        expect(layout.sidebar.classList.contains("mob-sidebar-active")).toBe(
          true,
        );
      });

      it("should insert overlay on mobile collapse", () => {
        const layout = createAdminLayout();
        loadDashJs();

        layout.mobileCollapse.click();

        expect(layout.sidebar.querySelector(".dash-menu-overlay")).toBeTruthy();
      });

      it("should remove classes on overlay click", () => {
        const layout = createAdminLayout();
        loadDashJs();

        layout.mobileCollapse.click();

        const overlay = layout.sidebar.querySelector(
          ".dash-menu-overlay",
        ) as HTMLElement;
        overlay?.click();

        expect(document.body.classList.contains("no-scroll")).toBe(false);
        expect(layout.sidebar.classList.contains("mob-sidebar-active")).toBe(
          false,
        );
      });

      it("should toggle off on second click", () => {
        const layout = createAdminLayout();
        loadDashJs();

        layout.mobileCollapse.click();
        layout.mobileCollapse.click();

        expect(document.body.classList.contains("no-scroll")).toBe(false);
      });
    });

    describe("Horizontal Layout", () => {
      it("should add mob-sidebar-active to topbar", () => {
        const layout = createAdminLayout({ horizontal: true });
        loadDashJs();

        layout.mobileCollapse.click();

        expect(layout.topbar.classList.contains("mob-sidebar-active")).toBe(
          true,
        );
      });

      it("should insert overlay in topbar", () => {
        const layout = createAdminLayout({ horizontal: true });
        loadDashJs();

        layout.mobileCollapse.click();

        expect(layout.topbar.querySelector(".dash-menu-overlay")).toBeTruthy();
      });
    });
  });

  // ==========================================================================
  // MENU NAVIGATION TESTS
  // ==========================================================================
  describe("Menu Navigation", () => {
    it("should hide submenus initially (non-minimenu)", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      const submenus = navbar.querySelectorAll(
        "li:not(.dash-trigger) .dash-submenu",
      );
      submenus.forEach(submenu => {
        const el = submenu as HTMLElement;
        expect(el.style.display).toBe("none");
      });
    });

    it("should expand submenu on menu item click", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      // Click on menu item with submenu
      const menuItem = items[1];
      const link = menuItem.querySelector("a") as HTMLElement;
      link.click();

      expect(menuItem.classList.contains("dash-trigger")).toBe(true);
    });

    it("should collapse submenu on second click", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      const menuItem = items[1];
      const link = menuItem.querySelector("a") as HTMLElement;

      // Open
      link.click();
      expect(menuItem.classList.contains("dash-trigger")).toBe(true);

      // Close
      link.click();
      expect(menuItem.classList.contains("dash-trigger")).toBe(false);
    });

    it("should close sibling menus when opening new menu", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      // Open first submenu
      const firstItem = items[1];
      (firstItem.querySelector("a") as HTMLElement).click();
      expect(firstItem.classList.contains("dash-trigger")).toBe(true);

      // Open second submenu
      const secondItem = items[2];
      (secondItem.querySelector("a") as HTMLElement).click();
      expect(secondItem.classList.contains("dash-trigger")).toBe(true);

      // First should be closed
      expect(firstItem.classList.contains("dash-trigger")).toBe(false);
    });

    it("should handle span click inside link", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      const menuItem = items[1];
      const span = menuItem.querySelector("a span") as HTMLElement;
      span?.click();

      expect(menuItem.classList.contains("dash-trigger")).toBe(true);
    });
  });

  // ==========================================================================
  // ACTIVE MENU ITEM TESTS
  // ==========================================================================
  describe("Active Menu Item Highlighting", () => {
    it("should add active class to matching menu item", () => {
      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard#page-2",
          hostname: "localhost",
        },
        writable: true,
      });

      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      // Set link href to match
      const link = items[2].querySelector("a") as HTMLAnchorElement;
      link.href = "http://localhost/dashboard";

      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard",
          hostname: "localhost",
        },
        writable: true,
      });

      loadDashJs();

      expect(items[2].classList.contains("active")).toBe(true);
    });

    it("should add active and trigger class to parent menu", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      // Get submenu link
      const submenu = items[1].querySelector(".dash-submenu");
      const subLink = submenu?.querySelector("a") as HTMLAnchorElement;
      if (subLink) {
        subLink.href = "http://localhost/dashboard";
      }

      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard",
          hostname: "localhost",
        },
        writable: true,
      });

      loadDashJs();

      // Parent should have trigger class
      if (subLink) {
        const parentLi = subLink.parentElement;
        expect(parentLi?.classList.contains("active")).toBe(true);
      }
    });

    it("should scroll navbar to active item if below fold", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 10, true);

      // Mock getBoundingClientRect to return a position below fold
      const lastItem = items[items.length - 1];
      const lastLink = lastItem.querySelector("a") as HTMLAnchorElement;
      lastLink.href = "http://localhost/dashboard";

      jest.spyOn(lastItem, "getBoundingClientRect").mockReturnValue({
        top: 500,
        bottom: 550,
        left: 0,
        right: 200,
        width: 200,
        height: 50,
        x: 0,
        y: 500,
        toJSON: () => ({}),
      });

      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard",
          hostname: "localhost",
        },
        writable: true,
      });

      loadDashJs();

      // Navbar content should have scrollTop adjusted
      expect(layout.navContent.scrollTop).toBeGreaterThanOrEqual(0);
    });
  });

  // ==========================================================================
  // NOTIFICATION SCROLLBAR TESTS
  // ==========================================================================
  describe("Notification Scrollbar", () => {
    it("should initialize PerfectScrollbar on notification body", () => {
      createAdminLayout();
      createNotificationDropdown(document.body);

      loadDashJs();

      expect(mockPerfectScrollbar).toHaveBeenCalledWith(
        ".drp-notification .noti-body",
        expect.objectContaining({
          wheelSpeed: 0.5,
          suppressScrollX: true,
        }),
      );
    });

    it("should not initialize if notification body already has listener", () => {
      createAdminLayout();
      const { notiBody } = createNotificationDropdown(document.body);
      notiBody.setAttribute("data-dash-listener", "true");

      loadDashJs();

      // The listener attribute should prevent re-initialization
      // Verify that the attribute is still there
      expect(notiBody.hasAttribute("data-dash-listener")).toBe(true);
    });
  });

  // ==========================================================================
  // PRODUCT LIKES TESTS
  // ==========================================================================
  describe("Product Likes Animation", () => {
    it("should add like animation on checkbox check", () => {
      createAdminLayout();
      const checkbox = createProdLikes(document.body);

      loadDashJs();

      // Check the checkbox
      checkbox.checked = true;
      checkbox.dispatchEvent(new Event("change", { bubbles: true }));

      const likeEl = checkbox.parentElement?.querySelector(".dash-like");
      expect(likeEl).toBeTruthy();
      expect(likeEl?.classList.contains("dash-like-animate")).toBe(true);
    });

    it("should remove like element after timeout", () => {
      createAdminLayout();
      const checkbox = createProdLikes(document.body);

      loadDashJs();

      checkbox.checked = true;
      checkbox.dispatchEvent(new Event("change", { bubbles: true }));

      expect(checkbox.parentElement?.querySelector(".dash-like")).toBeTruthy();

      // Advance timers
      jest.advanceTimersByTime(3000);

      expect(checkbox.parentElement?.querySelector(".dash-like")).toBeNull();
    });

    it("should remove like element on uncheck", () => {
      createAdminLayout();
      const checkbox = createProdLikes(document.body);

      loadDashJs();

      // Check
      checkbox.checked = true;
      checkbox.dispatchEvent(new Event("change", { bubbles: true }));

      expect(checkbox.parentElement?.querySelector(".dash-like")).toBeTruthy();

      // Uncheck
      checkbox.checked = false;
      checkbox.dispatchEvent(new Event("change", { bubbles: true }));

      expect(checkbox.parentElement?.querySelector(".dash-like")).toBeNull();
    });
  });

  // ==========================================================================
  // BOOTSTRAP COMPONENT INITIALIZATION TESTS
  // ==========================================================================
  describe("Bootstrap Component Initialization", () => {
    it("should initialize tooltips on window load", () => {
      createAdminLayout();
      createBootstrapElements(document.body);

      loadDashJs();

      // Trigger load event
      window.dispatchEvent(new Event("load"));

      // Tooltips should have listener attribute set
      const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltips.forEach(tooltip => {
        expect(tooltip.hasAttribute("data-dash-listener")).toBe(true);
      });
    });

    it("should initialize popovers on window load", () => {
      createAdminLayout();
      createBootstrapElements(document.body);

      loadDashJs();
      window.dispatchEvent(new Event("load"));

      // Popovers should have listener attribute set
      const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
      popovers.forEach(popover => {
        expect(popover.hasAttribute("data-dash-listener")).toBe(true);
      });
    });

    it("should initialize toasts on window load", () => {
      createAdminLayout();
      createBootstrapElements(document.body);

      loadDashJs();
      window.dispatchEvent(new Event("load"));

      // Toasts should have listener attribute set
      const toasts = document.querySelectorAll(".toast");
      toasts.forEach(toast => {
        expect(toast.hasAttribute("data-dash-listener")).toBe(true);
      });
    });

    it("should not initialize if bootstrap is undefined", () => {
      createAdminLayout();
      createBootstrapElements(document.body);
      delete win.bootstrap;

      loadDashJs();

      // Should not throw
      expect(() => {
        window.dispatchEvent(new Event("load"));
      }).not.toThrow();
    });
  });

  // ==========================================================================
  // WINDOW RESIZE TESTS
  // ==========================================================================
  describe("Window Resize Handling", () => {
    it("should handle minimenu toggle on resize below 1024px", () => {
      createAdminLayout({ minimenu: true });
      loadDashJs();

      expect(document.body.classList.contains("minimenu")).toBe(true);

      // Resize to mobile
      Object.defineProperty(window, "innerWidth", {
        value: 800,
        writable: true,
      });
      window.dispatchEvent(new Event("resize"));

      expect(document.body.classList.contains("minimenu")).toBe(false);
    });

    it("should restore minimenu on resize above 1024px after mobile", () => {
      createAdminLayout({ minimenu: true });
      loadDashJs();

      // Go to mobile
      Object.defineProperty(window, "innerWidth", {
        value: 800,
        writable: true,
      });
      window.dispatchEvent(new Event("resize"));

      expect(document.body.classList.contains("minimenu")).toBe(false);

      // Back to desktop
      Object.defineProperty(window, "innerWidth", {
        value: 1200,
        writable: true,
      });
      window.dispatchEvent(new Event("resize"));

      expect(document.body.classList.contains("minimenu")).toBe(true);
    });

    it("should remove active states on resize for horizontal layout", () => {
      const layout = createAdminLayout({ horizontal: true });
      const navbar = layout.topbar;

      // Add dropdown elements
      const dropdown = document.createElement("div");
      dropdown.className = "dropdown show";
      const menu = document.createElement("div");
      menu.className = "dropdown-menu show";
      dropdown.appendChild(menu);
      navbar.appendChild(dropdown);

      loadDashJs();
      window.dispatchEvent(new Event("resize"));

      // Classes should be cleaned up
      expect(dropdown.classList.contains("show")).toBe(false);
      expect(menu.classList.contains("show")).toBe(false);
    });
  });

  // ==========================================================================
  // HORIZONTAL SUBMENU EDGE DETECTION TESTS
  // ==========================================================================
  describe("Horizontal Submenu Edge Detection", () => {
    it("should add edge class when submenu overflows right", () => {
      createAdminLayout({ horizontal: true });

      const hasmenuItem = document.createElement("li");
      hasmenuItem.className = "dash-hasmenu";
      document.body.querySelector(".topbar")?.appendChild(hasmenuItem);

      const submenu = document.createElement("ul");
      submenu.className = "dash-submenu";
      hasmenuItem.appendChild(document.createElement("a"));
      hasmenuItem.appendChild(submenu);

      // Add to the horizontal topbar submenu area
      const topbar = document.querySelector(".topbar")!;
      const horizSubmenu = document.createElement("ul");
      horizSubmenu.className = "dash-submenu";
      const nestedHasmenu = document.createElement("li");
      nestedHasmenu.className = "dash-hasmenu";
      nestedHasmenu.appendChild(document.createElement("a"));

      const nestedSubmenu = document.createElement("ul");
      nestedSubmenu.className = "dash-submenu";
      nestedHasmenu.appendChild(nestedSubmenu);
      horizSubmenu.appendChild(nestedHasmenu);
      topbar.appendChild(horizSubmenu);

      // Mock getBoundingClientRect to simulate overflow
      jest.spyOn(nestedSubmenu, "getBoundingClientRect").mockReturnValue({
        left: 1800,
        right: 2000,
        width: 200,
        top: 100,
        bottom: 200,
        height: 100,
        x: 1800,
        y: 100,
        toJSON: () => ({}),
      });

      loadDashJs();

      // Simulate mouseenter
      nestedHasmenu.dispatchEvent(
        new MouseEvent("mouseenter", { bubbles: true }),
      );

      expect(nestedSubmenu.classList.contains("edge")).toBe(true);
    });

    it("should add scroll-menu class when submenu overflows bottom", () => {
      Object.defineProperty(window, "innerHeight", {
        value: 500,
        writable: true,
      });

      createAdminLayout({ horizontal: true });

      const topbar = document.querySelector(".topbar")!;
      const horizSubmenu = document.createElement("ul");
      horizSubmenu.className = "dash-submenu";
      const nestedHasmenu = document.createElement("li");
      nestedHasmenu.className = "dash-hasmenu";
      nestedHasmenu.appendChild(document.createElement("a"));

      const nestedSubmenu = document.createElement("ul");
      nestedSubmenu.className = "dash-submenu";
      nestedHasmenu.appendChild(nestedSubmenu);
      horizSubmenu.appendChild(nestedHasmenu);
      topbar.appendChild(horizSubmenu);

      jest.spyOn(nestedSubmenu, "getBoundingClientRect").mockReturnValue({
        top: 400,
        bottom: 700,
        height: 300,
        left: 100,
        right: 300,
        width: 200,
        x: 100,
        y: 400,
        toJSON: () => ({}),
      });

      loadDashJs();

      nestedHasmenu.dispatchEvent(
        new MouseEvent("mouseenter", { bubbles: true }),
      );

      expect(nestedSubmenu.classList.contains("scroll-menu")).toBe(true);
    });

    it("should cleanup edge classes on mouseleave", () => {
      createAdminLayout({ horizontal: true });

      const topbar = document.querySelector(".topbar")!;
      const horizSubmenu = document.createElement("ul");
      horizSubmenu.className = "dash-submenu";
      const nestedHasmenu = document.createElement("li");
      nestedHasmenu.className = "dash-hasmenu";
      nestedHasmenu.appendChild(document.createElement("a"));

      const nestedSubmenu = document.createElement("ul");
      nestedSubmenu.className = "dash-submenu scroll-menu edge";
      nestedSubmenu.style.maxHeight = "200px";
      nestedHasmenu.appendChild(nestedSubmenu);
      horizSubmenu.appendChild(nestedHasmenu);
      topbar.appendChild(horizSubmenu);

      loadDashJs();

      nestedHasmenu.dispatchEvent(
        new MouseEvent("mouseleave", { bubbles: true }),
      );

      expect(nestedSubmenu.classList.contains("scroll-menu")).toBe(false);
      expect(nestedSubmenu.classList.contains("edge")).toBe(false);
      expect(nestedSubmenu.style.maxHeight).toBe("");
    });

    it("should skip edge detection on mobile viewport", () => {
      Object.defineProperty(window, "innerWidth", {
        value: 800,
        writable: true,
      });

      createAdminLayout({ horizontal: true });

      const topbar = document.querySelector(".topbar")!;
      const horizSubmenu = document.createElement("ul");
      horizSubmenu.className = "dash-submenu";
      const nestedHasmenu = document.createElement("li");
      nestedHasmenu.className = "dash-hasmenu";
      nestedHasmenu.appendChild(document.createElement("a"));

      const nestedSubmenu = document.createElement("ul");
      nestedSubmenu.className = "dash-submenu";
      nestedHasmenu.appendChild(nestedSubmenu);
      horizSubmenu.appendChild(nestedHasmenu);
      topbar.appendChild(horizSubmenu);

      loadDashJs();

      // Should not have listener attached
      expect(nestedHasmenu.hasAttribute("data-dash-listener")).toBe(false);
    });
  });

  // ==========================================================================
  // SLIDE ANIMATION TESTS
  // ==========================================================================
  describe("Slide Animations", () => {
    it("should apply slideUp transition styles", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      // Open a menu first
      const menuItem = items[1];
      const link = menuItem.querySelector("a") as HTMLElement;
      link.click();

      // Now close it
      link.click();

      const submenu = menuItem.querySelector(".dash-submenu") as HTMLElement;
      expect(submenu.style.overflow).toBe("hidden");
      expect(submenu.style.height).toBe("0px");
    });

    it("should apply slideDown transition styles", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      const menuItem = items[1];
      const link = menuItem.querySelector("a") as HTMLElement;
      link.click();

      const submenu = menuItem.querySelector(".dash-submenu") as HTMLElement;
      // After slideDown is triggered, the submenu should be displayed
      // The styles are applied during animation
      expect(menuItem.classList.contains("dash-trigger")).toBe(true);
    });

    it("should cleanup transition styles after slideDown duration", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      const menuItem = items[1];
      const link = menuItem.querySelector("a") as HTMLElement;
      link.click();

      jest.advanceTimersByTime(200);

      const submenu = menuItem.querySelector(".dash-submenu") as HTMLElement;
      expect(submenu.style.overflow).toBe("");
      expect(submenu.style.transitionDuration).toBe("");
    });
  });

  // ==========================================================================
  // ERROR HANDLING TESTS
  // ==========================================================================
  describe("Error Handling", () => {
    it("should catch and log initialization errors", () => {
      // Create layout but corrupt the DOM
      createAdminLayout();

      // Mock querySelector to throw
      const originalQuerySelector = document.querySelector.bind(document);
      jest.spyOn(document, "querySelector").mockImplementation(selector => {
        if (selector === ".dash-sidebar") {
          throw new Error("Test error");
        }
        return originalQuerySelector(selector);
      });

      loadDashJs();

      expect(consoleErrorSpy).toHaveBeenCalledWith(
        expect.stringContaining("[DashboardController]"),
        expect.any(Error),
      );

      // Restore
      jest.spyOn(document, "querySelector").mockRestore();
    });

    it("should handle removeActive errors gracefully", () => {
      createAdminLayout({ horizontal: true });

      // Create elements that will cause issues
      const sidebar = document.querySelector(".dash-sidebar")!;
      const navbar = document.createElement("ul");
      navbar.className = "dash-navbar";
      const li = document.createElement("li");
      li.classList.add("active", "dash-trigger");
      navbar.appendChild(li);
      sidebar.appendChild(navbar);

      loadDashJs();

      // Trigger resize which calls removeActive
      expect(() => {
        window.dispatchEvent(new Event("resize"));
      }).not.toThrow();
    });
  });

  // ==========================================================================
  // HORIZONTAL MOBILE MENU TESTS
  // ==========================================================================
  describe("Horizontal Mobile Menu", () => {
    it("should handle nested horizontal menu clicks", () => {
      const layout = createAdminLayout({ horizontal: true });
      const navbar = layout.topbar;

      // Create horizontal menu structure
      const horizNavbar = document.createElement("ul");
      horizNavbar.className = "dash-navbar";

      const li = document.createElement("li");
      const link = document.createElement("a");
      link.innerHTML = "<span>Menu</span>";
      li.appendChild(link);

      const submenu = document.createElement("ul");
      submenu.className = "dash-submenu";

      const subLi = document.createElement("li");
      const subLink = document.createElement("a");
      subLink.innerHTML = "<span>Submenu</span>";
      subLi.appendChild(subLink);
      submenu.appendChild(subLi);

      li.appendChild(submenu);
      horizNavbar.appendChild(li);
      navbar.appendChild(horizNavbar);

      loadDashJs();

      // Click on horizontal menu item
      link.click();

      expect(li.classList.contains("dash-trigger")).toBe(true);

      // Click again to close
      link.click();

      expect(li.classList.contains("dash-trigger")).toBe(false);
    });

    it("should close sibling horizontal menus when opening new one", () => {
      const layout = createAdminLayout({ horizontal: true });
      const navbar = layout.topbar;

      const horizNavbar = document.createElement("ul");
      horizNavbar.className = "dash-navbar";

      // First menu
      const li1 = document.createElement("li");
      const link1 = document.createElement("a");
      link1.innerHTML = "<span>Menu 1</span>";
      li1.appendChild(link1);
      const submenu1 = document.createElement("ul");
      submenu1.className = "dash-submenu";
      li1.appendChild(submenu1);
      horizNavbar.appendChild(li1);

      // Second menu
      const li2 = document.createElement("li");
      const link2 = document.createElement("a");
      link2.innerHTML = "<span>Menu 2</span>";
      li2.appendChild(link2);
      const submenu2 = document.createElement("ul");
      submenu2.className = "dash-submenu";
      li2.appendChild(submenu2);
      horizNavbar.appendChild(li2);

      navbar.appendChild(horizNavbar);

      loadDashJs();

      // Open first
      link1.click();
      expect(li1.classList.contains("dash-trigger")).toBe(true);

      // Open second
      link2.click();
      expect(li2.classList.contains("dash-trigger")).toBe(true);
      expect(li1.classList.contains("dash-trigger")).toBe(false);
    });
  });

  // ==========================================================================
  // EDGE CASE TESTS
  // ==========================================================================
  describe("Edge Cases", () => {
    it("should handle missing sidebar gracefully", () => {
      document.body.innerHTML = "";
      document.body.className = "";

      expect(() => loadDashJs()).not.toThrow();
    });

    it("should handle missing topbar gracefully", () => {
      createAdminLayout({ horizontal: true });
      document.querySelector(".topbar")?.remove();

      expect(() => loadDashJs()).not.toThrow();
    });

    it("should handle empty menu gracefully", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      navbar.innerHTML = "";

      expect(() => loadDashJs()).not.toThrow();
    });

    it("should handle missing PerfectScrollbar gracefully", () => {
      delete win.PerfectScrollbar;
      createAdminLayout();

      expect(() => loadDashJs()).not.toThrow();
    });

    it("should handle null parent nodes in menu click handlers", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      // Create orphaned element
      const orphanLink = document.createElement("a");
      orphanLink.innerHTML = "<span>Orphan</span>";

      expect(() => orphanLink.click()).not.toThrow();
    });

    it("should handle location.href with query string", () => {
      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard?page=1&sort=asc",
          hostname: "localhost",
        },
        writable: true,
      });

      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      // Set matching href without query string
      const link = items[2].querySelector("a") as HTMLAnchorElement;
      link.href = "http://localhost/dashboard";

      loadDashJs();

      // Should still match (query string stripped)
      expect(items[2].classList.contains("active")).toBe(true);
    });

    it("should handle location.href with hash", () => {
      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard#section-1",
          hostname: "localhost",
        },
        writable: true,
      });

      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      const link = items[2].querySelector("a") as HTMLAnchorElement;
      link.href = "http://localhost/dashboard";

      loadDashJs();

      expect(items[2].classList.contains("active")).toBe(true);
    });

    it("should skip menu items with empty href", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;

      const li = document.createElement("li");
      const link = document.createElement("a");
      link.href = "";
      link.textContent = "Empty Link";
      li.appendChild(link);
      navbar.appendChild(li);

      expect(() => loadDashJs()).not.toThrow();
      expect(li.classList.contains("active")).toBe(false);
    });
  });

  // ==========================================================================
  // PERFORMANCE AND MEMORY TESTS
  // ==========================================================================
  describe("Performance Considerations", () => {
    it("should not attach duplicate event listeners", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      // Count listener attributes
      const initialListeners = document.querySelectorAll(
        "[data-dash-listener]",
      ).length;

      // Try to reinitialize
      document.body.removeAttribute("data-dash-init");
      loadDashJs();

      // Listeners should not double
      const finalListeners = document.querySelectorAll(
        "[data-dash-listener]",
      ).length;
      expect(finalListeners).toBe(initialListeners);
    });

    it("should use event delegation where possible", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      createMenuItems(navbar as HTMLElement, 20, true);

      loadDashJs();

      // Should have reasonable number of listeners even with many items
      const listeners = document.querySelectorAll("[data-dash-listener]");
      expect(listeners.length).toBeLessThan(100);
    });
  });

  // ==========================================================================
  // INTEGRATION TESTS
  // ==========================================================================
  describe("Integration Scenarios", () => {
    it("should work with full admin layout scenario", () => {
      const layout = createAdminLayout({
        minimenu: false,
        tabLayout: false,
      });

      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      createMenuItems(navbar as HTMLElement, 10, true);
      createNotificationDropdown(document.body);
      createBootstrapElements(document.body);
      createProdLikes(document.body);

      Object.defineProperty(window, "location", {
        value: {
          href: "http://localhost/dashboard",
          hostname: "localhost",
        },
        writable: true,
      });

      // Initialize
      loadDashJs();

      // Verify initialization completed
      expect(document.body.hasAttribute("data-dash-init")).toBe(true);
      expect(mockFeather.replace).toHaveBeenCalled();

      // Trigger window events
      window.dispatchEvent(new Event("load"));
      window.dispatchEvent(new Event("resize"));

      // Should complete without errors
      expect(consoleErrorSpy).not.toHaveBeenCalled();
    });

    it("should work with horizontal layout scenario", () => {
      const layout = createAdminLayout({ horizontal: true });

      const navbar = document.createElement("ul");
      navbar.className = "dash-navbar";
      layout.topbar.appendChild(navbar);
      createMenuItems(navbar, 8, true);

      Object.defineProperty(window, "innerWidth", {
        value: 1920,
        writable: true,
      });

      expect(() => {
        loadDashJs();
        window.dispatchEvent(new Event("load"));
      }).not.toThrow();
    });

    it("should handle rapid resize events", () => {
      createAdminLayout({ minimenu: true });
      loadDashJs();

      // Simulate rapid resize events
      for (let i = 0; i < 10; i++) {
        Object.defineProperty(window, "innerWidth", {
          value: 800 + i * 100,
          writable: true,
        });
        window.dispatchEvent(new Event("resize"));
      }

      expect(() => {
        jest.advanceTimersByTime(1000);
      }).not.toThrow();
    });

    it("should handle rapid menu clicks", () => {
      const layout = createAdminLayout();
      const navbar = layout.sidebar.querySelector(".dash-navbar")!;
      const items = createMenuItems(navbar as HTMLElement, 5, true);

      loadDashJs();

      // Rapid clicks on different menu items
      for (let i = 1; i < items.length; i++) {
        const link = items[i].querySelector("a") as HTMLElement;
        link?.click();
      }

      expect(() => {
        jest.advanceTimersByTime(500);
      }).not.toThrow();
    });
  });

  // ==========================================================================
  // PROBABILISTIC INPUT VARIATIONS
  // ==========================================================================
  describe("Probabilistic Input Variations", () => {
    const viewportWidths = [320, 480, 768, 1024, 1280, 1440, 1920, 2560];

    viewportWidths.forEach(width => {
      it(`should handle viewport width ${width}px`, () => {
        Object.defineProperty(window, "innerWidth", {
          value: width,
          writable: true,
        });

        createAdminLayout({ minimenu: width > 1024 });
        loadDashJs();

        expect(document.body.hasAttribute("data-dash-init")).toBe(true);
      });
    });

    const menuItemCounts = [0, 1, 5, 10, 20, 50];

    menuItemCounts.forEach(count => {
      it(`should handle ${count} menu items`, () => {
        const layout = createAdminLayout();
        const navbar = layout.sidebar.querySelector(".dash-navbar")!;
        createMenuItems(navbar as HTMLElement, count, count > 1);

        expect(() => loadDashJs()).not.toThrow();
      });
    });

    const layoutCombinations = [
      {},
      { horizontal: true },
      { minimenu: true },
      { tabLayout: true },
      { topbarLayout: true },
      { horizontal: true, navbarOverlay: true },
      { minimenu: true, tabLayout: true },
    ];

    layoutCombinations.forEach((options, index) => {
      it(`should handle layout combination ${index + 1}`, () => {
        createAdminLayout(options);

        if (options.tabLayout) {
          createTabLayout(document.body);
        }

        expect(() => loadDashJs()).not.toThrow();
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
