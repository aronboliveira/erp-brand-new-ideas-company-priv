/**
 * DataTables Type Declarations
 * @file ts/src/declarations/routes/datatables.interfaces.d.ts
 * @description Shared interfaces for DataTables extensions
 */

/**
 * Extended DataTables static with isDataTable method
 */
export interface DataTablesStaticExt {
  (options?: DataTablesSettings): DataTablesApi;
  isDataTable(selector: JQuery | string): boolean;
}

/**
 * jQuery.fn extension for DataTables with Buttons
 */
export interface DataTablesJQueryFnExtension {
  dataTable?: {
    Buttons?: unknown;
  };
}

/**
 * Generic DOM event handler type
 */
export type EventHandler = (this: HTMLElement, e: JQueryEventObject) => void;
