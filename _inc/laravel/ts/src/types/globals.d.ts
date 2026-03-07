/**
 * Global type declarations for third-party libraries loaded via CDN.
 * These are available in the browser but not installed via npm in this submodule.
 */

/* ==========================================================================
   Feather Icons
   ========================================================================== */

interface FeatherIcons {
  replace: (options?: { class?: string; "stroke-width"?: number }) => void;
  icons: Record<
    string,
    { toSvg: (options?: Record<string, string>) => string }
  >;
}

declare const feather: FeatherIcons | undefined;

/* ==========================================================================
   Bootstrap 5
   ========================================================================== */

declare namespace bootstrap {
  class Modal {
    constructor(element: Element | string, options?: Partial<ModalOptions>);
    show(): void;
    hide(): void;
    toggle(): void;
    dispose(): void;
    handleUpdate(): void;
    static getInstance(element: Element): Modal | null;
    static getOrCreateInstance(element: Element): Modal;
  }

  interface ModalOptions {
    backdrop: boolean | "static";
    keyboard: boolean;
    focus: boolean;
  }

  class Toast {
    constructor(element: Element, options?: Partial<ToastOptions>);
    show(): void;
    hide(): void;
    dispose(): void;
    static getInstance(element: Element): Toast | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<ToastOptions>,
    ): Toast;
  }

  interface ToastOptions {
    animation: boolean;
    autohide: boolean;
    delay: number;
  }

  class Dropdown {
    constructor(element: Element, options?: Partial<DropdownOptions>);
    toggle(): void;
    show(): void;
    hide(): void;
    dispose(): void;
    update(): void;
    static getInstance(element: Element): Dropdown | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<DropdownOptions>,
    ): Dropdown;
  }

  interface DropdownOptions {
    boundary: string | Element;
    reference: string | Element;
    display: string;
    offset: [number, number];
    autoClose: boolean | "inside" | "outside";
    popperConfig: Record<string, unknown> | null;
  }

  class Tooltip {
    constructor(element: Element, options?: Partial<TooltipOptions>);
    show(): void;
    hide(): void;
    toggle(): void;
    enable(): void;
    disable(): void;
    toggleEnabled(): void;
    update(): void;
    dispose(): void;
    static getInstance(element: Element): Tooltip | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<TooltipOptions>,
    ): Tooltip;
  }

  interface TooltipOptions {
    animation: boolean;
    container: string | Element | false;
    delay: number | { show: number; hide: number };
    html: boolean;
    placement: "auto" | "top" | "bottom" | "left" | "right";
    selector: string | false;
    template: string;
    title: string | Element | (() => string);
    trigger: string;
    fallbackPlacements: string[];
    boundary: string | Element;
    customClass: string | (() => string);
    sanitize: boolean;
    allowList: Record<string, string[]>;
    sanitizeFn: ((input: string) => string) | null;
    offset: [number, number] | string;
    popperConfig: Record<string, unknown> | null;
  }

  class Popover extends Tooltip {
    static getInstance(element: Element): Popover | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<TooltipOptions>,
    ): Popover;
  }

  class Collapse {
    constructor(element: Element, options?: Partial<CollapseOptions>);
    toggle(): void;
    show(): void;
    hide(): void;
    dispose(): void;
    static getInstance(element: Element): Collapse | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<CollapseOptions>,
    ): Collapse;
  }

  interface CollapseOptions {
    parent: string | Element;
    toggle: boolean;
  }

  class Tab {
    constructor(element: Element);
    show(): void;
    dispose(): void;
    static getInstance(element: Element): Tab | null;
    static getOrCreateInstance(element: Element): Tab;
  }

  class Alert {
    constructor(element: Element);
    close(): void;
    dispose(): void;
    static getInstance(element: Element): Alert | null;
    static getOrCreateInstance(element: Element): Alert;
  }

  class Carousel {
    constructor(element: Element, options?: Partial<CarouselOptions>);
    cycle(): void;
    pause(): void;
    prev(): void;
    next(): void;
    nextWhenVisible(): void;
    to(index: number): void;
    dispose(): void;
    static getInstance(element: Element): Carousel | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<CarouselOptions>,
    ): Carousel;
  }

  interface CarouselOptions {
    interval: number | false;
    keyboard: boolean;
    pause: "hover" | false;
    ride: "carousel" | boolean;
    wrap: boolean;
    touch: boolean;
  }

  class Offcanvas {
    constructor(element: Element, options?: Partial<OffcanvasOptions>);
    toggle(): void;
    show(): void;
    hide(): void;
    dispose(): void;
    static getInstance(element: Element): Offcanvas | null;
    static getOrCreateInstance(
      element: Element,
      options?: Partial<OffcanvasOptions>,
    ): Offcanvas;
  }

  interface OffcanvasOptions {
    backdrop: boolean | "static";
    keyboard: boolean;
    scroll: boolean;
  }

  class ScrollSpy {
    constructor(element: Element, options?: Partial<ScrollSpyOptions>);
    refresh(): void;
    dispose(): void;
    static getInstance(element: Element): ScrollSpy | null;
  }

  interface ScrollSpyOptions {
    target: string | Element;
    method: "auto" | "offset" | "position";
    offset: number;
    rootMargin: string;
    smoothScroll: boolean;
  }
}

/* ==========================================================================
   ApexCharts
   ========================================================================== */

interface ApexChartsOptions {
  chart?: {
    type?: string;
    height?: number | string;
    width?: number | string;
    id?: string;
    toolbar?: { show?: boolean };
    zoom?: { enabled?: boolean };
    animations?: { enabled?: boolean };
    background?: string;
    foreColor?: string;
    [key: string]: unknown;
  };
  series?: {
    name?: string;
    data: number[] | { x: string | number; y: number }[];
  }[];
  xaxis?: {
    categories?: string[] | number[];
    type?: string;
    labels?: { show?: boolean; format?: string };
    [key: string]: unknown;
  };
  yaxis?: {
    labels?: { show?: boolean };
    [key: string]: unknown;
  };
  title?: { text?: string; align?: string };
  subtitle?: { text?: string; align?: string };
  colors?: string[];
  stroke?: { curve?: string; width?: number };
  fill?: { type?: string; opacity?: number };
  legend?: { show?: boolean; position?: string };
  dataLabels?: { enabled?: boolean };
  tooltip?: { enabled?: boolean; shared?: boolean };
  grid?: { show?: boolean; borderColor?: string };
  responsive?: {
    breakpoint?: number;
    options?: Record<string, unknown>;
  }[];
  [key: string]: unknown;
}

declare class ApexCharts {
  constructor(el: Element | string, options: ApexChartsOptions);
  render(): Promise<void>;
  updateOptions(
    options: Partial<ApexChartsOptions>,
    redrawPaths?: boolean,
    animate?: boolean,
  ): Promise<void>;
  updateSeries(
    newSeries: ApexChartsOptions["series"],
    animate?: boolean,
  ): Promise<void>;
  appendSeries(newSeries: ApexChartsOptions["series"]): Promise<void>;
  appendData(data: { data: number[] }[]): Promise<void>;
  toggleSeries(seriesName: string): void;
  showSeries(seriesName: string): void;
  hideSeries(seriesName: string): void;
  resetSeries(): void;
  destroy(): void;
  dataURI(): Promise<{ imgURI: string }>;
  static exec(chartId: string, methodName: string, ...args: unknown[]): unknown;
}

/* ==========================================================================
   PerfectScrollbar
   ========================================================================== */

interface PerfectScrollbarOptions {
  handlers?: string[];
  maxScrollbarLength?: number;
  minScrollbarLength?: number;
  scrollingThreshold?: number;
  scrollXMarginOffset?: number;
  scrollYMarginOffset?: number;
  suppressScrollX?: boolean;
  suppressScrollY?: boolean;
  swipeEasing?: boolean;
  useBothWheelAxes?: boolean;
  wheelPropagation?: boolean;
  wheelSpeed?: number;
}

declare class PerfectScrollbar {
  constructor(element: Element | string, options?: PerfectScrollbarOptions);
  update(): void;
  destroy(): void;
}

/* ==========================================================================
   Flatpickr
   ========================================================================== */

interface FlatpickrOptions {
  altFormat?: string;
  altInput?: boolean;
  altInputClass?: string;
  allowInput?: boolean;
  appendTo?: Element;
  ariaDateFormat?: string;
  clickOpens?: boolean;
  dateFormat?: string;
  defaultDate?: string | Date | Date[];
  defaultHour?: number;
  defaultMinute?: number;
  disable?: (
    | string
    | Date
    | { from: Date; to: Date }
    | ((date: Date) => boolean)
  )[];
  disableMobile?: boolean;
  enable?: (
    | string
    | Date
    | { from: Date; to: Date }
    | ((date: Date) => boolean)
  )[];
  enableSeconds?: boolean;
  enableTime?: boolean;
  hourIncrement?: number;
  inline?: boolean;
  locale?: string | Record<string, unknown>;
  maxDate?: string | Date;
  maxTime?: string;
  minDate?: string | Date;
  minTime?: string;
  minuteIncrement?: number;
  mode?: "single" | "multiple" | "range";
  monthSelectorType?: "dropdown" | "static";
  nextArrow?: string;
  noCalendar?: boolean;
  onChange?: (
    selectedDates: Date[],
    dateStr: string,
    instance: Flatpickr,
  ) => void;
  onClose?: (
    selectedDates: Date[],
    dateStr: string,
    instance: Flatpickr,
  ) => void;
  onOpen?: (
    selectedDates: Date[],
    dateStr: string,
    instance: Flatpickr,
  ) => void;
  onReady?: (
    selectedDates: Date[],
    dateStr: string,
    instance: Flatpickr,
  ) => void;
  parseDate?: (datestr: string, format: string) => Date;
  position?: "auto" | "above" | "below";
  prevArrow?: string;
  shorthandCurrentMonth?: boolean;
  static?: boolean;
  time_24hr?: boolean;
  weekNumbers?: boolean;
  wrap?: boolean;
}

interface Flatpickr {
  selectedDates: Date[];
  currentYear: number;
  currentMonth: number;
  config: FlatpickrOptions;
  input: HTMLInputElement;
  changeMonth(offset: number, isOffset?: boolean): void;
  clear(triggerChange?: boolean): void;
  close(): void;
  destroy(): void;
  formatDate(date: Date, format: string): string;
  jumpToDate(date: string | Date, triggerChange?: boolean): void;
  open(): void;
  parseDate(dateStr: string, format?: string): Date | undefined;
  redraw(): void;
  set(option: string, value: unknown): void;
  setDate(
    date: string | Date | Date[],
    triggerChange?: boolean,
    format?: string,
  ): void;
  toggle(): void;
}

interface FlatpickrStatic {
  (
    selector: string | Element | NodeList,
    options?: FlatpickrOptions,
  ): Flatpickr | Flatpickr[];
  defaultConfig: FlatpickrOptions;
  l10ns: Record<string, Record<string, unknown>>;
  formatDate: (date: Date, format: string, locale?: string) => string;
  parseDate: (
    dateStr: string,
    format: string,
    altInput?: boolean,
    parsedDate?: Date,
  ) => Date | undefined;
}

declare const flatpickr: FlatpickrStatic;

/* ==========================================================================
   SweetAlert2
   ========================================================================== */

interface SweetAlertResult<T = unknown> {
  isConfirmed: boolean;
  isDenied: boolean;
  isDismissed: boolean;
  value?: T;
  dismiss?: "cancel" | "backdrop" | "close" | "esc" | "timer";
}

interface SweetAlertOptions {
  title?: string | HTMLElement;
  titleText?: string;
  text?: string;
  html?: string | HTMLElement;
  icon?: "success" | "error" | "warning" | "info" | "question";
  iconColor?: string;
  iconHtml?: string;
  showCancelButton?: boolean;
  showConfirmButton?: boolean;
  showDenyButton?: boolean;
  confirmButtonText?: string;
  cancelButtonText?: string;
  denyButtonText?: string;
  confirmButtonColor?: string;
  cancelButtonColor?: string;
  denyButtonColor?: string;
  buttonsStyling?: boolean;
  reverseButtons?: boolean;
  focusConfirm?: boolean;
  focusDeny?: boolean;
  focusCancel?: boolean;
  showCloseButton?: boolean;
  closeButtonHtml?: string;
  closeButtonAriaLabel?: string;
  showLoaderOnConfirm?: boolean;
  showLoaderOnDeny?: boolean;
  preConfirm?: (inputValue: unknown) => unknown | Promise<unknown>;
  preDeny?: (inputValue: unknown) => unknown | Promise<unknown>;
  returnFocus?: boolean;
  imageUrl?: string;
  imageWidth?: number | string;
  imageHeight?: number | string;
  imageAlt?: string;
  input?:
    | "text"
    | "email"
    | "password"
    | "number"
    | "tel"
    | "range"
    | "textarea"
    | "select"
    | "radio"
    | "checkbox"
    | "file"
    | "url";
  inputPlaceholder?: string;
  inputLabel?: string;
  inputValue?:
    | string
    | number
    | File
    | FileList
    | Promise<string | number | File | FileList>;
  inputOptions?:
    | Record<string, string>
    | Map<string, string>
    | Promise<Record<string, string> | Map<string, string>>;
  inputAutoTrim?: boolean;
  inputAttributes?: Record<string, string>;
  inputValidator?: (value: string) => string | null | Promise<string | null>;
  validationMessage?: string;
  grow?: false | "row" | "column" | "fullscreen";
  width?: number | string;
  padding?: number | string;
  background?: string;
  position?:
    | "top"
    | "top-start"
    | "top-end"
    | "center"
    | "center-start"
    | "center-end"
    | "bottom"
    | "bottom-start"
    | "bottom-end";
  toast?: boolean;
  timer?: number;
  timerProgressBar?: boolean;
  heightAuto?: boolean;
  allowOutsideClick?: boolean | (() => boolean);
  allowEscapeKey?: boolean | (() => boolean);
  allowEnterKey?: boolean | (() => boolean);
  stopKeydownPropagation?: boolean;
  backdrop?: boolean | string;
  target?: string | Element;
  customClass?: {
    container?: string;
    popup?: string;
    header?: string;
    title?: string;
    closeButton?: string;
    icon?: string;
    image?: string;
    htmlContainer?: string;
    input?: string;
    inputLabel?: string;
    validationMessage?: string;
    actions?: string;
    confirmButton?: string;
    denyButton?: string;
    cancelButton?: string;
    loader?: string;
    footer?: string;
    timerProgressBar?: string;
  };
  didOpen?: (popup: HTMLElement) => void;
  willClose?: (popup: HTMLElement) => void;
  didClose?: () => void;
  didDestroy?: () => void;
  scrollbarPadding?: boolean;
  // Animation classes
  showClass?: {
    popup?: string;
    backdrop?: string;
    icon?: string;
  };
  hideClass?: {
    popup?: string;
    backdrop?: string;
    icon?: string;
  };
  // Lifecycle callbacks (deprecated aliases)
  willOpen?: (popup: HTMLElement) => void;
  onClose?: () => void;
  // Footer
  footer?: string | HTMLElement;
  // ARIA labels
  confirmButtonAriaLabel?: string;
  cancelButtonAriaLabel?: string;
  denyButtonAriaLabel?: string;
}

interface SweetAlertStatic {
  fire<T = unknown>(options: SweetAlertOptions): Promise<SweetAlertResult<T>>;
  fire<T = unknown>(
    title?: string,
    html?: string,
    icon?: SweetAlertOptions["icon"],
  ): Promise<SweetAlertResult<T>>;
  mixin(options: SweetAlertOptions): SweetAlertStatic;
  isVisible(): boolean;
  update(options: Partial<SweetAlertOptions>): void;
  close(): void;
  getPopup(): HTMLElement | null;
  getTitle(): HTMLElement | null;
  getHtmlContainer(): HTMLElement | null;
  getImage(): HTMLElement | null;
  getCloseButton(): HTMLElement | null;
  getIcon(): HTMLElement | null;
  getConfirmButton(): HTMLElement | null;
  getDenyButton(): HTMLElement | null;
  getCancelButton(): HTMLElement | null;
  getActions(): HTMLElement | null;
  getFooter(): HTMLElement | null;
  getTimerProgressBar(): HTMLElement | null;
  getFocusableElements(): HTMLElement[];
  enableButtons(): void;
  disableButtons(): void;
  showLoading(): void;
  hideLoading(): void;
  isLoading(): boolean;
  getTimerLeft(): number | undefined;
  stopTimer(): number | undefined;
  resumeTimer(): number | undefined;
  toggleTimer(): number | undefined;
  isTimerRunning(): boolean | undefined;
  increaseTimer(n: number): number | undefined;
  clickConfirm(): void;
  clickDeny(): void;
  clickCancel(): void;
  // Deprecated method (alias for getHtmlContainer)
  getContent(): HTMLElement | null;
  // Validation message
  showValidationMessage(message: string): void;
  // Dismiss reasons enum
  DismissReason: {
    cancel: "cancel";
    backdrop: "backdrop";
    close: "close";
    esc: "esc";
    timer: "timer";
  };
}

declare const Swal: SweetAlertStatic;

/* ==========================================================================
   jQuery (minimal typing for legacy code)
   ========================================================================== */

interface JQueryStatic {
  (selector: string | Element | Document | (() => void)): JQuery;
  ajax(settings: Record<string, unknown>): JQueryXHR;
  get(
    url: string,
    data?: Record<string, unknown>,
    success?: (data: unknown) => void,
  ): JQueryXHR;
  post(
    url: string,
    data?: Record<string, unknown>,
    success?: (data: unknown) => void,
  ): JQueryXHR;
  getJSON(
    url: string,
    data?: Record<string, unknown>,
    success?: (data: unknown) => void,
  ): JQueryXHR;
  extend<T, U>(target: T, object1: U): T & U;
  extend<T, U, V>(target: T, object1: U, object2: V): T & U & V;
  fn: JQuery;
  Deferred<T>(): JQueryDeferred<T>;
  when<T>(...deferreds: (JQueryDeferred<T> | Promise<T>)[]): JQueryPromise<T>;
}

interface JQuery<TElement = HTMLElement> extends Iterable<TElement> {
  length: number;
  [index: number]: TElement;

  // DOM manipulation
  html(): string;
  html(htmlString: string): this;
  text(): string;
  text(text: string): this;
  val(): string | number | string[] | undefined;
  val(value: string | number | string[]): this;
  attr(attributeName: string): string | undefined;
  attr(attributeName: string, value: string | number | null): this;
  attr(attributes: Record<string, string | number | null>): this;
  removeAttr(attributeName: string): this;
  data(key: string): unknown;
  data(key: string, value: unknown): this;
  prop(propertyName: string): unknown;
  prop(propertyName: string, value: unknown): this;
  css(propertyName: string): string;
  css(propertyName: string, value: string | number): this;
  css(properties: Record<string, string | number>): this;

  // Class manipulation
  addClass(className: string): this;
  removeClass(className?: string): this;
  toggleClass(className: string, state?: boolean): this;
  hasClass(className: string): boolean;

  // DOM traversal
  find(selector: string): JQuery;
  parent(selector?: string): JQuery;
  parents(selector?: string): JQuery;
  closest(selector: string): JQuery;
  children(selector?: string): JQuery;
  siblings(selector?: string): JQuery;
  first(): JQuery;
  last(): JQuery;
  eq(index: number): JQuery;
  filter(
    selector: string | ((index: number, element: TElement) => boolean),
  ): JQuery;
  not(selector: string): JQuery;
  is(selector: string): boolean;
  each(callback: (index: number, element: TElement) => void | false): this;

  // DOM insertion
  append(content: string | Element | JQuery): this;
  prepend(content: string | Element | JQuery): this;
  after(content: string | Element | JQuery): this;
  before(content: string | Element | JQuery): this;
  appendTo(target: string | Element | JQuery): this;
  prependTo(target: string | Element | JQuery): this;
  insertAfter(target: string | Element | JQuery): this;
  insertBefore(target: string | Element | JQuery): this;
  wrap(wrappingElement: string | Element | JQuery): this;
  unwrap(): this;
  clone(withDataAndEvents?: boolean): JQuery;
  remove(selector?: string): this;
  empty(): this;
  detach(selector?: string): JQuery;
  replaceWith(newContent: string | Element | JQuery): this;

  // Events
  on(
    events: string,
    handler: (event: JQueryEventObject, ...args: unknown[]) => void,
  ): this;
  on(
    events: string,
    selector: string,
    handler: (event: JQueryEventObject, ...args: unknown[]) => void,
  ): this;
  off(events?: string, selector?: string): this;
  off(events: string, handler: (event: JQueryEventObject) => void): this;
  one(events: string, handler: (event: JQueryEventObject) => void): this;
  trigger(eventType: string, extraParameters?: unknown[]): this;
  click(handler?: (event: JQueryEventObject) => void): this;
  submit(handler?: (event: JQueryEventObject) => void): this;
  change(handler?: (event: JQueryEventObject) => void): this;
  focus(handler?: (event: JQueryEventObject) => void): this;
  blur(handler?: (event: JQueryEventObject) => void): this;
  keydown(handler?: (event: JQueryEventObject) => void): this;
  keyup(handler?: (event: JQueryEventObject) => void): this;
  keypress(handler?: (event: JQueryEventObject) => void): this;
  mouseenter(handler?: (event: JQueryEventObject) => void): this;
  mouseleave(handler?: (event: JQueryEventObject) => void): this;
  hover(
    handlerIn: (event: JQueryEventObject) => void,
    handlerOut: (event: JQueryEventObject) => void,
  ): this;
  ready(handler: () => void): this;

  // Effects
  show(duration?: number | string, callback?: () => void): this;
  hide(duration?: number | string, callback?: () => void): this;
  toggle(duration?: number | string, callback?: () => void): this;
  fadeIn(duration?: number | string, callback?: () => void): this;
  fadeOut(duration?: number | string, callback?: () => void): this;
  fadeToggle(duration?: number | string, callback?: () => void): this;
  fadeTo(
    duration: number | string,
    opacity: number,
    callback?: () => void,
  ): this;
  slideDown(duration?: number | string, callback?: () => void): this;
  slideUp(duration?: number | string, callback?: () => void): this;
  slideToggle(duration?: number | string, callback?: () => void): this;
  animate(
    properties: Record<string, unknown>,
    duration?: number | string,
    easing?: string,
    callback?: () => void,
  ): this;
  stop(clearQueue?: boolean, jumpToEnd?: boolean): this;
  delay(duration: number): this;

  // Dimensions
  width(): number;
  width(value: number | string): this;
  height(): number;
  height(value: number | string): this;
  innerWidth(): number;
  innerHeight(): number;
  outerWidth(includeMargin?: boolean): number;
  outerHeight(includeMargin?: boolean): number;
  offset(): { top: number; left: number } | undefined;
  offset(coordinates: { top: number; left: number }): this;
  position(): { top: number; left: number };
  scrollTop(): number;
  scrollTop(value: number): this;
  scrollLeft(): number;
  scrollLeft(value: number): this;

  // Utilities
  get(): TElement[];
  get(index: number): TElement;
  toArray(): TElement[];
  index(): number;
  index(element: string | Element | JQuery): number;
  serialize(): string;
  serializeArray(): { name: string; value: string }[];
  map<U>(callback: (index: number, element: TElement) => U): JQuery<U>;

  // jQuery UI
  sortable(options?: string | Record<string, unknown>): this;
  disableSelection(): this;

  // AJAX
  load(
    url: string,
    data?: Record<string, unknown>,
    complete?: (
      responseText: string,
      textStatus: string,
      xhr: JQueryXHR,
    ) => void,
  ): this;
}

interface JQueryEventObject extends Event {
  delegateTarget: Element;
  data: unknown;
  namespace: string;
  originalEvent: Event;
  pageX: number;
  pageY: number;
  result: unknown;
  which: number;
  isDefaultPrevented(): boolean;
  isImmediatePropagationStopped(): boolean;
  isPropagationStopped(): boolean;
}

interface JQueryXHR {
  responseText: string;
  responseJSON?: unknown;
  status: number;
  statusText: string;
  done(
    callback: (data: unknown, textStatus: string, jqXHR: JQueryXHR) => void,
  ): this;
  fail(
    callback: (
      jqXHR: JQueryXHR,
      textStatus: string,
      errorThrown: string,
    ) => void,
  ): this;
  always(callback: () => void): this;
  then<U>(successFilter: (data: unknown) => U): JQueryPromise<U>;
  abort(): void;
}

interface JQueryDeferred<T> {
  resolve(value?: T): this;
  reject(reason?: unknown): this;
  notify(value?: unknown): this;
  promise(): JQueryPromise<T>;
  state(): "pending" | "resolved" | "rejected";
}

interface JQueryPromise<T> {
  done(callback: (value: T) => void): this;
  fail(callback: (reason: unknown) => void): this;
  always(callback: () => void): this;
  then<U>(successFilter: (value: T) => U): JQueryPromise<U>;
  catch<U>(errorFilter: (reason: unknown) => U): JQueryPromise<U>;
}

declare const $: JQueryStatic;
declare const jQuery: JQueryStatic;

/* ==========================================================================
   DataTables
   ========================================================================== */

interface DataTablesSettings {
  ajax?: string | Record<string, unknown>;
  columns?: {
    data?: string | number | null;
    name?: string;
    title?: string;
    render?: (
      data: unknown,
      type: string,
      row: unknown,
      meta: unknown,
    ) => string;
    orderable?: boolean;
    searchable?: boolean;
    visible?: boolean;
    className?: string;
    width?: string;
    defaultContent?: string;
  }[];
  columnDefs?: {
    targets: number | string | number[];
    orderable?: boolean;
    searchable?: boolean;
    visible?: boolean;
    render?: (
      data: unknown,
      type: string,
      row: unknown,
      meta: unknown,
    ) => string;
  }[];
  data?: unknown[];
  dom?: string;
  language?: Record<string, string | Record<string, string>>;
  lengthMenu?: number[] | number[][];
  order?: [number, "asc" | "desc"][];
  ordering?: boolean;
  paging?: boolean;
  pageLength?: number;
  processing?: boolean;
  scrollX?: boolean;
  scrollY?: string;
  searching?: boolean;
  serverSide?: boolean;
  stateSave?: boolean;
  responsive?: boolean;
  buttons?: (string | Record<string, unknown>)[];
  select?: boolean | Record<string, unknown>;
  initComplete?: (settings: unknown, json: unknown) => void;
  drawCallback?: (settings: unknown) => void;
  rowCallback?: (row: Element, data: unknown, displayIndex: number) => void;
  createdRow?: (row: Element, data: unknown, dataIndex: number) => void;
}

interface DataTablesApi {
  draw(reset?: boolean | string): this;
  ajax: {
    reload(callback?: () => void, resetPaging?: boolean): this;
    url(url: string): this;
  };
  row(selector: string | number | Element): {
    data(): unknown;
    data(d: unknown): this;
    remove(): this;
    invalidate(): this;
    node(): Element;
  };
  rows(selector?: string | number[]): {
    data(): unknown[];
    remove(): this;
    invalidate(): this;
    nodes(): Element[];
  };
  column(selector: number | string): {
    data(): unknown[];
    visible(show?: boolean): boolean | this;
    search(input: string): this;
  };
  columns(selector?: number[] | string): {
    visible(show?: boolean): boolean[] | this;
    search(input: string): this;
  };
  search(input: string): this;
  page: {
    (): number;
    (page: number | "first" | "next" | "previous" | "last"): this;
    len(): number;
    len(length: number): this;
  };
  order(order?: [number, "asc" | "desc"][]): this | [number, "asc" | "desc"][];
  clear(): this;
  destroy(remove?: boolean): void;
  on(event: string, callback: (...args: unknown[]) => void): this;
  off(event: string): this;
  data(): unknown[];
  settings(): [DataTablesSettings];
}

interface JQuery {
  DataTable(options?: DataTablesSettings): DataTablesApi;
  dataTable(options?: DataTablesSettings): JQuery;
}

/* ==========================================================================
   Select2
   ========================================================================== */

interface Select2Options {
  ajax?: {
    url: string | (() => string);
    dataType?: string;
    delay?: number;
    data?: (params: { term: string; page?: number }) => Record<string, unknown>;
    processResults?: (
      data: unknown,
      params: { page?: number },
    ) => { results: { id: string | number; text: string }[] };
    cache?: boolean;
  };
  allowClear?: boolean;
  closeOnSelect?: boolean;
  containerCss?: Record<string, string>;
  containerCssClass?: string;
  data?: { id: string | number; text: string }[];
  disabled?: boolean;
  dropdownCss?: Record<string, string>;
  dropdownCssClass?: string;
  dropdownParent?: JQuery | Element;
  language?: string | Record<string, () => string>;
  maximumInputLength?: number;
  maximumSelectionLength?: number;
  minimumInputLength?: number;
  minimumResultsForSearch?: number;
  multiple?: boolean;
  placeholder?: string | { id: string; text: string };
  tags?: boolean;
  templateResult?: (item: {
    id: string | number;
    text: string;
    loading?: boolean;
  }) => string | Element | JQuery;
  templateSelection?: (item: {
    id: string | number;
    text: string;
  }) => string | Element | JQuery;
  theme?: string;
  tokenSeparators?: string[];
  width?: string;
}

interface Select2Api {
  open(): void;
  close(): void;
  destroy(): void;
  focus(): void;
  val(): string | string[] | null;
  val(value: string | string[] | null): void;
  data(): { id: string | number; text: string }[];
  trigger(event: string, data?: unknown): void;
}

interface JQuery {
  select2(options?: Select2Options): JQuery;
  select2(method: "open" | "close" | "destroy" | "focus"): JQuery;
  select2(method: "val"): string | string[] | null;
  select2(method: "val", value: string | string[] | null): JQuery;
  select2(method: "data"): { id: string | number; text: string }[];
}

/* ==========================================================================
   Summernote
   ========================================================================== */

interface SummernoteOptions {
  airMode?: boolean;
  callbacks?: {
    onInit?: () => void;
    onChange?: (contents: string) => void;
    onImageUpload?: (files: FileList) => void;
    onEnter?: () => void;
    onFocus?: () => void;
    onBlur?: () => void;
    onKeydown?: (e: KeyboardEvent) => void;
    onKeyup?: (e: KeyboardEvent) => void;
    onPaste?: (e: ClipboardEvent) => void;
  };
  codeviewFilter?: boolean;
  codeviewFilterRegex?: RegExp;
  codemirror?: Record<string, unknown>;
  dialogsInBody?: boolean;
  dialogsFade?: boolean;
  direction?: "ltr" | "rtl";
  disableDragAndDrop?: boolean;
  disableResizeEditor?: boolean;
  focus?: boolean;
  fontNames?: string[];
  fontNamesIgnoreCheck?: string[];
  fontSizes?: string[];
  height?: number;
  inheritPlaceholder?: boolean;
  insertTableMaxSize?: { col: number; row: number };
  lang?: string;
  lineHeights?: string[];
  maxHeight?: number;
  minHeight?: number;
  placeholder?: string;
  popover?: Record<string, unknown>;
  shortcuts?: boolean;
  styleTags?: string[];
  tabDisable?: boolean;
  tabSize?: number;
  tableClassName?: string;
  toolbar?: [string, string[]][];
  width?: number;
}

interface JQuery {
  summernote(options?: SummernoteOptions): JQuery;
  summernote(method: "code"): string;
  summernote(method: "code", code: string): JQuery;
  summernote(method: "destroy"): JQuery;
  summernote(method: "disable"): JQuery;
  summernote(method: "enable"): JQuery;
  summernote(method: "focus"): JQuery;
  summernote(method: "isEmpty"): boolean;
  summernote(method: "reset"): JQuery;
}

/* ==========================================================================
   Toastr
   ========================================================================== */

interface ToastrOptions {
  closeButton?: boolean;
  debug?: boolean;
  newestOnTop?: boolean;
  progressBar?: boolean;
  preventDuplicates?: boolean;
  positionClass?: string;
  onclick?: () => void;
  showDuration?: number;
  hideDuration?: number;
  timeOut?: number;
  extendedTimeOut?: number;
  showEasing?: string;
  hideEasing?: string;
  showMethod?: string;
  hideMethod?: string;
  tapToDismiss?: boolean;
  closeHtml?: string;
  closeMethod?: string;
  closeDuration?: number;
  closeEasing?: string;
  escapeHtml?: boolean;
  target?: string;
  iconClasses?: {
    error: string;
    info: string;
    success: string;
    warning: string;
  };
}

interface Toastr {
  success(message: string, title?: string, options?: ToastrOptions): JQuery;
  info(message: string, title?: string, options?: ToastrOptions): JQuery;
  warning(message: string, title?: string, options?: ToastrOptions): JQuery;
  error(message: string, title?: string, options?: ToastrOptions): JQuery;
  clear(toast?: JQuery, clearOptions?: { force?: boolean }): void;
  remove(): void;
  options: ToastrOptions;
}

declare const toastr: Toastr;

/* ==========================================================================
   Bootstrap Slider (bootstrap-slider)
   ========================================================================== */

interface SliderOptions {
  id?: string;
  min?: number;
  max?: number;
  step?: number;
  precision?: number;
  value?: number | [number, number];
  range?: boolean;
  orientation?: "horizontal" | "vertical";
  reversed?: boolean;
  tooltip?: "show" | "hide" | "always";
  tooltip_position?: "top" | "bottom" | "left" | "right";
  formatter?: (value: number | [number, number]) => string;
  ticks?: number[];
  ticks_labels?: string[];
  ticks_positions?: number[];
  ticks_snap_bounds?: number;
  ticks_tooltip?: boolean;
  scale?: "linear" | "logarithmic";
  focus?: boolean;
  labelledby?: string | string[];
  rangeHighlights?: { start: number; end: number; class?: string }[];
  enabled?: boolean;
  selection?: "before" | "after" | "none";
  handle?: "round" | "square" | "triangle" | "custom";
  lock_to_ticks?: boolean;
}

declare class Slider {
  constructor(selector: string | Element, options?: SliderOptions);
  getValue(): number | [number, number];
  setValue(
    newValue: number | [number, number],
    triggerSlideEvent?: boolean,
    triggerChangeEvent?: boolean,
  ): this;
  destroy(): void;
  disable(): this;
  enable(): this;
  toggle(): this;
  isEnabled(): boolean;
  setAttribute(attribute: string, value: unknown): this;
  getAttribute(attribute: string): unknown;
  refresh(options?: Partial<SliderOptions>): this;
  on(
    eventName: string,
    callback: (value: number | [number, number]) => void,
  ): this;
  off(
    eventName: string,
    callback?: (value: number | [number, number]) => void,
  ): this;
  relayout(): this;
}

/* ==========================================================================
   Tiny Slider (tiny-slider)
   ========================================================================== */

interface TnsOptions {
  container?: string | Element;
  items?: number;
  slideBy?: number | "page";
  autoplay?: boolean;
  autoplayTimeout?: number;
  autoplayButton?: string | Element | false;
  axis?: "horizontal" | "vertical";
  center?: boolean;
  gutter?: number;
  edgePadding?: number;
  controls?: boolean;
  controlsContainer?: string | Element | false;
  nav?: boolean;
  navContainer?: string | Element | false;
  navAsThumbnails?: boolean;
  arrowKeys?: boolean;
  speed?: number;
  autoplayHoverPause?: boolean;
  autoplayResetOnVisibility?: boolean;
  autoplayText?: [string, string];
  rewind?: boolean;
  loop?: boolean;
  mode?: "carousel" | "gallery";
  lazyload?: boolean;
  lazyloadSelector?: string;
  touch?: boolean;
  mouseDrag?: boolean;
  swipeAngle?: number | false;
  preventActionWhenRunning?: boolean;
  preventScrollOnTouch?: "auto" | "force" | false;
  nested?: "inner" | "outer" | false;
  freezable?: boolean;
  disable?: boolean;
  startIndex?: number;
  onInit?: () => void;
  responsive?: Record<number, Partial<TnsOptions>>;
}

interface TnsInstance {
  getInfo(): {
    index: number;
    slideCount: number;
    cloneCount: number;
    slideCountNew: number;
    navItems: Element[];
    slideItems: Element[];
  };
  goTo(target: number | "next" | "prev" | "first" | "last"): void;
  play(): void;
  pause(): void;
  isOn: boolean;
  updateSliderHeight(): void;
  refresh(): void;
  destroy(): void;
  rebuild(): TnsInstance;
  events: {
    on(type: string, handler: (info: unknown) => void): void;
    off(type: string, handler: (info: unknown) => void): void;
  };
}

declare function tns(options: TnsOptions): TnsInstance;

/* ==========================================================================
   Intro.js
   ========================================================================== */

interface IntroJsStep {
  element?: Element | string | null;
  intro: string;
  title?: string;
  position?: "top" | "bottom" | "left" | "right" | "auto";
  tooltipClass?: string;
  highlightClass?: string;
  scrollTo?: "element" | "tooltip" | "off";
  disableInteraction?: boolean;
}

interface IntroJsOptions {
  steps?: IntroJsStep[];
  nextLabel?: string;
  prevLabel?: string;
  skipLabel?: string;
  doneLabel?: string;
  hidePrev?: boolean;
  hideNext?: boolean;
  nextToDone?: boolean;
  tooltipPosition?: string;
  tooltipClass?: string;
  highlightClass?: string;
  exitOnEsc?: boolean;
  exitOnOverlayClick?: boolean;
  showStepNumbers?: boolean;
  keyboardNavigation?: boolean;
  showButtons?: boolean;
  showBullets?: boolean;
  showProgress?: boolean;
  scrollToElement?: boolean;
  scrollTo?: "element" | "tooltip" | "off";
  scrollPadding?: number;
  overlayOpacity?: number;
  disableInteraction?: boolean;
  dontShowAgain?: boolean;
  dontShowAgainLabel?: string;
  dontShowAgainCookie?: string;
  dontShowAgainCookieDays?: number;
}

interface IntroJsInstance {
  start(): this;
  goToStep(step: number): this;
  goToStepNumber(stepNumber: number): this;
  nextStep(): this;
  previousStep(): this;
  exit(force?: boolean): this;
  setOptions(options: IntroJsOptions): this;
  setOption(option: string, value: unknown): this;
  refresh(): this;
  addHints(): this;
  showHint(hintIndex: number): this;
  showHints(): this;
  hideHint(hintIndex: number): this;
  hideHints(): this;
  removeHint(hintIndex: number): this;
  removeHints(): this;
  showHintDialog(hintIndex: number): this;
  onbeforechange(callback: (targetElement: Element) => void | boolean): this;
  onchange(callback: (targetElement: Element) => void): this;
  onafterchange(callback: (targetElement: Element) => void): this;
  oncomplete(callback: () => void): this;
  onexit(callback: () => void): this;
  onhintsadded(callback: () => void): this;
  onhintclick(
    callback: (hintElement: Element, item: IntroJsStep, stepId: number) => void,
  ): this;
  onhintclose(callback: (stepId: number) => void): this;
  onskip(callback: () => void): this;
  onbeforeexit(callback: () => boolean | void): this;
}

declare function introJs(targetElement?: string | Element): IntroJsInstance;

/* ==========================================================================
   VanillaTree
   ========================================================================== */

interface VanillaTreeContextMenuItem {
  label: string;
  action: (id: string) => void;
}

interface VanillaTreeOptions {
  placeholder?: string;
  contextmenu?: VanillaTreeContextMenuItem[];
}

interface VanillaTreeAddOptions {
  label: string;
  id?: string;
  parent?: string;
  opened?: boolean;
  selected?: boolean;
}

interface VanillaTreeEvent extends Event {
  detail: {
    id: string;
  };
}

declare class VanillaTree {
  constructor(element: Element | null, options?: VanillaTreeOptions);
  add(options: VanillaTreeAddOptions): this;
  move(id: string, parentId: string): this;
  remove(id: string): this;
  open(id: string): this;
  close(id: string): this;
  toggle(id: string): this;
  select(id: string): this;
}

/* ==========================================================================
   IMask
   ========================================================================== */

interface IMaskOptions {
  mask:
    | string
    | NumberConstructor
    | DateConstructor
    | RegExp
    | ((value: string) => boolean)
    | IMaskOptions[];
  lazy?: boolean;
  eager?: boolean | "append" | "remove";
  overwrite?: boolean | "shift";
  prepare?: (value: string, masked: unknown) => string;
  commit?: (value: string, masked: unknown) => void;
  validate?: (value: string, masked: unknown) => boolean;
  format?: (value: unknown) => string;
  parse?: (str: string) => unknown;
  // Pattern mask options
  definitions?: Record<
    string,
    { mask: string | RegExp; displayChar?: string; placeholderChar?: string }
  >;
  blocks?: Record<string, IMaskOptions>;
  placeholderChar?: string;
  displayChar?: string;
  // Number mask options
  scale?: number;
  signed?: boolean;
  thousandsSeparator?: string;
  padFractionalZeros?: boolean;
  normalizeZeros?: boolean;
  radix?: string;
  mapToRadix?: string[];
  min?: number;
  max?: number;
  // Date mask options
  pattern?: string;
  autofix?: boolean | "pad";
}

interface IMaskInstance {
  value: string;
  unmaskedValue: string;
  typedValue: unknown;
  masked: unknown;
  el: { value: string };
  updateValue(): void;
  updateControl(): void;
  updateOptions(opts: Partial<IMaskOptions>): void;
  updateCursor(cursorPos: number): void;
  alignCursor(): void;
  alignCursorFriendly(): void;
  on(ev: string, handler: (...args: unknown[]) => void): this;
  off(ev: string, handler?: (...args: unknown[]) => void): this;
  destroy(): void;
}

declare function IMask(
  element: Element | null,
  options: IMaskOptions,
): IMaskInstance;
declare namespace IMask {
  export const Masked: unknown;
  export const MaskedPattern: unknown;
  export const MaskedNumber: unknown;
  export const MaskedDate: unknown;
  export const MaskedRange: unknown;
  export const MaskedEnum: unknown;
  export const MaskedRegExp: unknown;
  export const MaskedFunction: unknown;
  export const MaskedDynamic: unknown;
  export const InputMask: unknown;
  export const PIPE_TYPE: unknown;
  export const pipe: unknown;
  export const createMask: unknown;
}

/* ==========================================================================
   Datepicker (vanillajs-datepicker)
   ========================================================================== */

interface DatepickerOptions {
  autohide?: boolean;
  beforeShowDay?: (
    date: Date,
  ) =>
    | { enabled?: boolean; classes?: string; tooltip?: string }
    | string
    | boolean
    | undefined;
  beforeShowDecade?: (
    date: Date,
  ) => { enabled?: boolean; classes?: string } | string | boolean | undefined;
  beforeShowMonth?: (
    date: Date,
  ) => { enabled?: boolean; classes?: string } | string | boolean | undefined;
  beforeShowYear?: (
    date: Date,
  ) => { enabled?: boolean; classes?: string } | string | boolean | undefined;
  buttonClass?: string;
  calendarWeeks?: boolean;
  clearBtn?: boolean;
  container?: string | Element;
  dateDelimiter?: string;
  datesDisabled?: (string | Date)[];
  daysOfWeekDisabled?: number[];
  daysOfWeekHighlighted?: number[];
  defaultViewDate?:
    | string
    | Date
    | { year: number; month: number; day: number };
  disableTouchKeyboard?: boolean;
  format?: string;
  language?: string;
  maxDate?: string | Date | null;
  maxNumberOfDates?: number;
  maxView?: number;
  minDate?: string | Date | null;
  nextArrow?: string;
  orientation?:
    | "auto"
    | "top"
    | "bottom"
    | "left"
    | "right"
    | "top left"
    | "top right"
    | "bottom left"
    | "bottom right";
  pickLevel?: 0 | 1 | 2;
  prevArrow?: string;
  showDaysOfWeek?: boolean;
  showOnClick?: boolean;
  showOnFocus?: boolean;
  startView?: number;
  title?: string;
  todayBtn?: boolean | "link";
  todayBtnMode?: 0 | 1;
  todayHighlight?: boolean;
  updateOnBlur?: boolean;
  weekStart?: number;
}

declare class Datepicker {
  constructor(element: Element, options?: DatepickerOptions);
  static locales: Record<string, Record<string, unknown>>;
  static formatDate(date: Date, format: string, lang?: string): string;
  static parseDate(dateStr: string | Date, format: string, lang?: string): Date;
  dates: Date[];
  destroy(): void;
  getDate(format?: string): Date | Date[] | string | string[] | undefined;
  hide(): void;
  refresh(target?: "picker" | "input", forceRender?: boolean): void;
  setDate(
    ...args: (
      | string
      | Date
      | { clear?: boolean; render?: boolean; autohide?: boolean }
    )[]
  ): void;
  setOptions(options: Partial<DatepickerOptions>): void;
  show(): void;
  toggle(): void;
  update(options?: { autohide?: boolean }): void;
  element: Element;
  inputField: HTMLInputElement;
}

/* ==========================================================================
   DateRangePicker (vanillajs-datepicker)
   ========================================================================== */

declare class DateRangePicker {
  constructor(element: Element, options?: DatepickerOptions);
  dates: Date[];
  datepickers: [Datepicker, Datepicker];
  destroy(): void;
  getDates(
    format?: string,
  ): [Date | string | undefined, Date | string | undefined];
  setDates(
    rangeStart?: string | Date | { clear?: boolean },
    rangeEnd?: string | Date | { clear?: boolean },
  ): void;
  setOptions(options: Partial<DatepickerOptions>): void;
  element: Element;
  inputs: [HTMLInputElement, HTMLInputElement];
}

/* ==========================================================================
   Notifier
   ========================================================================== */

interface NotifierOptions {
  position?:
    | "top-left"
    | "top-right"
    | "bottom-left"
    | "bottom-right"
    | "top-center"
    | "bottom-center";
  durations?: Record<string, number>;
}

interface Notifier {
  show(
    title: string,
    message: string,
    type?: string,
    imageUrl?: string,
    duration?: number,
  ): string;
  hide(notificationId: string): void;
  success(title: string, message: string, duration?: number): string;
  error(title: string, message: string, duration?: number): string;
  warning(title: string, message: string, duration?: number): string;
  info(title: string, message: string, duration?: number): string;
}

declare const notifier: Notifier;

/* ==========================================================================
   Choices.js
   ========================================================================== */

interface ChoicesOptions {
  silent?: boolean;
  items?: (
    | string
    | {
        value: string;
        label?: string;
        id?: number;
        selected?: boolean;
        disabled?: boolean;
        customProperties?: Record<string, unknown>;
      }
  )[];
  choices?: (
    | string
    | {
        value: string;
        label?: string;
        id?: number;
        selected?: boolean;
        disabled?: boolean;
        customProperties?: Record<string, unknown>;
        groupId?: number;
      }
  )[];
  renderChoiceLimit?: number;
  maxItemCount?: number;
  addItems?: boolean;
  addItemFilter?: string | RegExp | ((value: string) => boolean) | null;
  removeItems?: boolean;
  removeItemButton?: boolean;
  editItems?: boolean;
  allowHTML?: boolean;
  duplicateItemsAllowed?: boolean;
  delimiter?: string;
  paste?: boolean;
  searchEnabled?: boolean;
  searchChoices?: boolean;
  searchFloor?: number;
  searchResultLimit?: number;
  searchFields?: string[];
  position?: "auto" | "top" | "bottom";
  resetScrollPosition?: boolean;
  shouldSort?: boolean;
  shouldSortItems?: boolean;
  sorter?: (a: unknown, b: unknown) => number;
  placeholder?: boolean;
  placeholderValue?: string | null;
  searchPlaceholderValue?: string | null;
  prependValue?: string | null;
  appendValue?: string | null;
  renderSelectedChoices?: "always" | "auto";
  loadingText?: string;
  noResultsText?: string | (() => string);
  noChoicesText?: string | (() => string);
  itemSelectText?: string;
  addItemText?: string | ((value: string) => string);
  maxItemText?: string | ((maxItemCount: number) => string);
  uniqueItemText?: string;
  customAddItemText?: string;
  valueComparer?: (a: unknown, b: unknown) => boolean;
  classNames?: Record<string, string>;
  fuseOptions?: Record<string, unknown>;
  callbackOnInit?: () => void;
  callbackOnCreateTemplates?: (
    template: (arg: string) => Element,
  ) => Record<string, (...args: unknown[]) => Element>;
}

declare class Choices {
  constructor(element: string | Element, options?: ChoicesOptions);
  static readonly defaults: { options: ChoicesOptions };
  init(): void;
  destroy(): void;
  enable(): this;
  disable(): this;
  highlightItem(item: Element, runEvent?: boolean): this;
  unhighlightItem(item: Element): this;
  highlightAll(): this;
  unhighlightAll(): this;
  removeActiveItemsByValue(value: string): this;
  removeActiveItems(excludedId?: number): this;
  removeHighlightedItems(runEvent?: boolean): this;
  showDropdown(focusInput?: boolean): this;
  hideDropdown(blurInput?: boolean): this;
  getValue(
    valueOnly?: boolean,
  ): string | string[] | { value: string; label: string }[];
  setValue(items: (string | { value: string; label?: string })[]): this;
  setChoiceByValue(value: string | string[]): this;
  setChoices(
    choices: ChoicesOptions["choices"],
    value?: string,
    label?: string,
    replaceChoices?: boolean,
  ): this;
  clearChoices(): this;
  clearStore(): this;
  clearInput(): this;
  ajax(
    fn: (
      callback: (results: unknown[], value: string, label: string) => void,
    ) => void,
  ): this;
  passedElement: { element: Element };
  containerOuter: { element: Element };
  containerInner: { element: Element };
  choiceList: { element: Element };
  itemList: { element: Element };
  input: { element: HTMLInputElement };
  dropdown: { element: Element };
}

/* ==========================================================================
   Window extensions
   ========================================================================== */

interface Window {
  bootstrap?: typeof bootstrap;
  $?: JQueryStatic;
  jQuery?: JQueryStatic;
  feather?: FeatherIcons;
  ApexCharts?: typeof ApexCharts;
  PerfectScrollbar?: typeof PerfectScrollbar;
  flatpickr?: FlatpickrStatic;
  Swal?: SweetAlertStatic;
  toastr?: Toastr;
  Slider?: typeof Slider;
  tns?: typeof tns;
  introJs?: typeof introJs;
  VanillaTree?: typeof VanillaTree;
  IMask?: typeof IMask;
  Datepicker?: typeof Datepicker;
  DateRangePicker?: typeof DateRangePicker;
  notifier?: Notifier;
  Choices?: typeof Choices;
  show_toastr?: (type: string, message: string, status?: string) => void;
  html2pdf?: unknown;
  saveAsPDF?: (element?: Element, options?: Record<string, unknown>) => void;
  svLang?: Record<string, string>;
  __confirmHandlers?: Record<string, (() => void)[]>;
  svToastOrAlert?: (message: string, type?: string) => void;
  FullCalendar?: unknown;
  __mockUser?: unknown;
  currentYear?: number;
  _afterPrintBound?: boolean;
  Swiper?: unknown;
  Pusher?: unknown;
  get_data?: (url: string, callback: (data: unknown) => void) => void;
  dataTabelLang?: Record<string, unknown>;
  webpackChunkerp_nova_prestech?: unknown[];
  showDatabaseSettings?: () => void;
  showApplicationSettings?: () => void;
  date_picker_locale?: string;
  copyText?: (text: string) => void;
  copySelectedText?: () => void;
  copyGrammerText?: () => void;
  copyToClipboard?: (text: string) => void;
  csrfToken?: string;
  Alpine?: unknown;
  __appPusher?: unknown;
  Brick?: unknown;
  checkEnvironment?: () => void;
  check_theme?: () => void;
  cookieNoticeJS?: unknown;
  JOBS_I18N?: Record<string, string>;
  LetterAvatar?: unknown;
  PAYSLIP_I18N?: Record<string, string>;
  PAYSLIP_SHOW_I18N?: Record<string, string>;
  RBACTestUtils?: unknown;
  translations?: Record<string, Record<string, string>>;
}
