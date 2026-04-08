/**
 * @file autosize.ts — Textarea auto-resize (vendor shim)
 * @description Type-annotated mirror of public/js/chatify/autosize.js.
 *              This is a third-party UMD library; the TS version preserves
 *              behaviour and adds minimal type safety.
 */

type AutosizeFn = {
  (el: HTMLTextAreaElement | HTMLTextAreaElement[] | NodeListOf<HTMLTextAreaElement>): void;
  destroy(el: HTMLTextAreaElement | HTMLTextAreaElement[] | NodeListOf<HTMLTextAreaElement>): void;
  update(el: HTMLTextAreaElement | HTMLTextAreaElement[] | NodeListOf<HTMLTextAreaElement>): void;
};

const map: Map<HTMLTextAreaElement, { destroy: () => void; update: () => void }> = new Map();

let createEvent = (name: string): Event => new Event(name, { bubbles: true });
try {
  new Event("test");
} catch {
  createEvent = (name: string): Event => {
    const evt = document.createEvent("Event");
    evt.initEvent(name, true, false);
    return evt;
  };
}

function assign(ta: HTMLTextAreaElement): void {
  if (!ta || ta.nodeName !== "TEXTAREA" || map.has(ta)) return;

  let heightOffset: number = 0,
    clientWidth: number | null = null,
    cachedHeight: number | null = null;

  function init(): void {
    const style = window.getComputedStyle(ta, null);
    if (style.resize === "vertical") ta.style.resize = "none";
    else if (style.resize === "both") ta.style.resize = "horizontal";

    if (style.boxSizing === "content-box") {
      heightOffset = -(parseFloat(style.paddingTop) + parseFloat(style.paddingBottom));
    } else {
      heightOffset = parseFloat(style.borderTopWidth) + parseFloat(style.borderBottomWidth);
    }
    if (isNaN(heightOffset)) heightOffset = 0;
    update();
  }

  function changeOverflow(value: string): void {
    const width = ta.style.width;
    ta.style.width = "0px";
    void ta.offsetWidth; // force reflow
    ta.style.width = width;
    ta.style.overflowY = value;
  }

  function getParentOverflows(el: Element): Array<{ node: Element; scrollTop: number }> {
    const arr: Array<{ node: Element; scrollTop: number }> = [];
    let current: Element | null = el;
    while (current?.parentNode && current.parentNode instanceof Element) {
      if (current.parentNode.scrollTop) {
        arr.push({ node: current.parentNode, scrollTop: current.parentNode.scrollTop });
      }
      current = current.parentNode;
    }
    return arr;
  }

  function resize(): void {
    if (ta.scrollHeight === 0) return;
    const overflows = getParentOverflows(ta),
      docTop = document.documentElement?.scrollTop;
    ta.style.height = "";
    ta.style.height = ta.scrollHeight + heightOffset + "px";
    clientWidth = ta.clientWidth;
    overflows.forEach(el => {
      (el.node as HTMLElement).scrollTop = el.scrollTop;
    });
    if (docTop) document.documentElement.scrollTop = docTop;
  }

  function update(): void {
    resize();
    const styleHeight = Math.round(parseFloat(ta.style.height)),
      computed = window.getComputedStyle(ta, null);
    let actualHeight = computed.boxSizing === "content-box" ? Math.round(parseFloat(computed.height)) : ta.offsetHeight;

    if (actualHeight < styleHeight) {
      if (computed.overflowY === "hidden") {
        changeOverflow("scroll");
        resize();
        actualHeight = computed.boxSizing === "content-box" ? Math.round(parseFloat(window.getComputedStyle(ta, null).height)) : ta.offsetHeight;
      }
    } else {
      if (computed.overflowY !== "hidden") {
        changeOverflow("hidden");
        resize();
        actualHeight = computed.boxSizing === "content-box" ? Math.round(parseFloat(window.getComputedStyle(ta, null).height)) : ta.offsetHeight;
      }
    }

    if (cachedHeight !== actualHeight) {
      cachedHeight = actualHeight;
      const evt = createEvent("autosize:resized");
      try {
        ta.dispatchEvent(evt);
      } catch {
        /* detached element — Firefox bug */
      }
    }
  }

  const pageResize = (): void => {
    if (ta.clientWidth !== clientWidth) update();
  };

  const destroy = (style: CSSStyleDeclaration): void => {
    window.removeEventListener("resize", pageResize, false);
    ta.removeEventListener("input", update, false);
    ta.removeEventListener("keyup", update, false);
    ta.removeEventListener("autosize:destroy", destroy as unknown as EventListener, false);
    ta.removeEventListener("autosize:update", update, false);
    Object.keys(style).forEach(key => {
      ta.style.setProperty(key, style.getPropertyValue(key));
    });
    map.delete(ta);
  };

  ta.addEventListener("autosize:destroy", destroy as unknown as EventListener, false);
  ta.addEventListener("autosize:update", update, false);
  ta.addEventListener("input", update, false);
  ta.addEventListener("keyup", update, false);
  ta.style.overflowY = "hidden";
  ta.style.overflowX = "hidden";
  ta.style.wordWrap = "break-word";

  map.set(ta, { destroy: destroy.bind(null, ta.style), update });
  init();
}

function destroyEl(ta: HTMLTextAreaElement): void {
  const methods = map.get(ta);
  methods?.destroy();
}

function updateEl(ta: HTMLTextAreaElement): void {
  const methods = map.get(ta);
  methods?.update();
}

const autosize: AutosizeFn = Object.assign(
  (el: HTMLTextAreaElement | HTMLTextAreaElement[] | NodeListOf<HTMLTextAreaElement>): void => {
    if (el instanceof HTMLTextAreaElement) {
      assign(el);
    } else {
      Array.from(el).forEach(assign);
    }
  },
  {
    destroy(el: HTMLTextAreaElement | HTMLTextAreaElement[] | NodeListOf<HTMLTextAreaElement>): void {
      if (el instanceof HTMLTextAreaElement) {
        destroyEl(el);
      } else {
        Array.from(el).forEach(destroyEl);
      }
    },
    update(el: HTMLTextAreaElement | HTMLTextAreaElement[] | NodeListOf<HTMLTextAreaElement>): void {
      if (el instanceof HTMLTextAreaElement) {
        updateEl(el);
      } else {
        Array.from(el).forEach(updateEl);
      }
    },
  },
);

export default autosize;
