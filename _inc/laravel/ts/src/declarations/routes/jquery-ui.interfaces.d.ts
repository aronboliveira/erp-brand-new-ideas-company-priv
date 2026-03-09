/**
 * jQuery UI/Plugin Type Declarations
 * @file ts/src/declarations/routes/jquery-ui.interfaces.d.ts
 * @description Shared interfaces for jQuery UI extensions (sortable, etc.)
 */

/**
 * jQuery UI Sortable event object
 */
export interface JQuerySortableUI {
  item: JQuery<HTMLElement>;
}

/**
 * Extended jQuery with sortable/disableSelection methods
 */
export interface JQueryExtendedSortable extends JQuery<HTMLElement> {
  sortable(options?: Record<string, unknown>): this;
  sortable(method: "destroy"): this;
  disableSelection(): this;
}

/**
 * Extended jQuery static with sortable registration
 */
export interface JQueryStaticFn {
  sortable?: unknown;
}

/**
 * Extended jQuery static with modal destroy method
 */
export interface JQueryStaticExtended extends JQueryStatic {
  destroyModal?: (modal: JQuery<HTMLElement> | HTMLElement) => void;
}

/**
 * Extended jQuery with fireModal plugin
 */
export interface JQueryExtendedModal extends JQuery<HTMLElement> {
  fireModal?: (options: {
    title: string;
    body: string;
    buttons: {
      text: string;
      class: string;
      handler: (modal: JQuery<HTMLElement>) => void;
    }[];
  }) => void;
}
