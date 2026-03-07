/**
 * @fileoverview TypeScript version of public/js/buttons.html5.js
 * @generated from original JavaScript - manual review recommended
 * @module buttons.html5
 */

/* global $, jQuery */

// UMD/AMD type declarations
declare const define: {
  (deps: string[], factory: (...args: unknown[]) => unknown): void;
  amd?: boolean;
};

// Extended Navigator with legacy IE methods
interface NavigatorWithMsSave extends Navigator {
  msSaveOrOpenBlob?: (blob: Blob, defaultName?: string) => boolean;
}

// URL interface for FileSaver
interface URLLike {
  createObjectURL(blob: Blob): string;
  revokeObjectURL(url: string): void;
}

// Extended Window for third-party libraries
interface ButtonsWindow extends Window {
  JSZip?: unknown;
  pdfMake?: unknown;
  webkitURL?: URLLike;
  URL: URLLike;
  FileReader: typeof FileReader;
  Blob: typeof Blob;
  HTMLElement: typeof HTMLElement;
  safari?: unknown;
  setImmediate?: (fn: () => void) => void;
}

// Extended jQuery interface for utility methods used in DataTables
interface JQueryExtended extends JQueryStatic {
  each<T>(
    obj: T[] | Record<string, T>,
    fn: (key: string | number, val: T) => void,
  ): T[] | Record<string, T>;
  isPlainObject(obj: unknown): boolean;
  parseXML(data: string): Document;
  trim(str: string): string;
  map<T, U>(arr: T[], callback: (item: T, index: number) => U): U[];
}

// DataTable instance interface
interface DataTableInstance {
  i18n(
    key: string,
    def: string | Record<string, string>,
    count?: number,
  ): string;
  table(): { container(): HTMLElement; node(): HTMLElement };
  buttons: {
    info(close: false): void;
    info(
      title: string,
      message: string | Element | JQuery<HTMLElement>,
      time?: number,
    ): void;
    exportData(options?: Record<string, unknown>): ExportData;
  };
}

interface ExportData {
  str: string;
  rows: number;
  header: string[];
  body: string[][];
  footer: string[];
}

// JSZip interface for zip operations
interface JSZipLike {
  folder(name: string): JSZipLike | null;
  file(name: string, data: string): JSZipLike;
  generateAsync?(config: Record<string, unknown>): Promise<Blob>;
  generate?(config: Record<string, unknown>): Blob;
}

// Extended DataTable interface for buttons plugin
interface DataTableExt {
  buttons: Record<string, unknown>;
}

interface DataTableApi {
  ext: DataTableExt;
  Buttons: unknown;
}
/*!
 * HTML5 export buttons for Buttons and DataTables.
 * 2016 SpryMedia Ltd - datatables.net/license
 *
 * FileSaver.js (1.3.3) - MIT license
 * Copyright © 2016 Eli Grey - http://eligrey.com
 */

(function (factory) {
  if (typeof define === "function" && define.amd) {
    // AMD
    define(["jquery", "datatables.net", "datatables.net-buttons"], function (
      $: JQueryStatic,
    ) {
      return factory($, window, document, undefined, undefined, undefined);
    });
  } else if (typeof exports === "object") {
    // CommonJS
    module.exports = function (
      root: Window | undefined,
      $: JQueryStatic,
      jszip: unknown,
      pdfmake: unknown,
    ) {
      if (!root) {
        root = window;
      }

      if (!$?.fn.dataTable) {
        $ = (require("datatables.net")(root, $) as { $: JQueryStatic }).$;
      }

      if (!($.fn.dataTable as unknown as { Buttons?: unknown }).Buttons) {
        require("datatables.net-buttons")(root, $);
      }

      return factory($, root, root.document, jszip, pdfmake, undefined);
    };
  } else {
    // Browser
    factory(jQuery, window, document, undefined, undefined, undefined);
  }
})(function (
  $: JQueryStatic,
  window: Window,
  document: Document,
  jszip: unknown,
  pdfmake: unknown,
  undefined?: undefined,
) {
  "use strict";
  const DataTable = $.fn.dataTable;
  const DataTableTyped = DataTable as unknown as DataTableApi;
  const bWindow = window as unknown as ButtonsWindow;
  const $ext = $ as unknown as JQueryExtended;

  // Allow the constructor to pass in JSZip and PDFMake from external requires.
  // Otherwise, use globally defined variables, if they are available.
  function _jsZip() {
    return jszip || bWindow.JSZip;
  }
  function _pdfMake() {
    return pdfmake || bWindow.pdfMake;
  }

  /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   * FileSaver.js dependency
   */

  /*jslint bitwise: true, indent: 4, laxbreak: true, laxcomma: true, smarttabs: true, plusplus: true */

  const _saveAs = (function (view: ButtonsWindow | undefined) {
    "use strict";
    // IE <10 is explicitly unsupported
    if (
      typeof view === "undefined" ||
      (typeof navigator !== "undefined" &&
        /MSIE [1-9]\./.test(navigator.userAgent))
    ) {
      return;
    }
    const doc = view.document,
      // only get URL when necessary in case Blob.js hasn't overridden it yet
      get_URL = function (): URLLike {
        return view.URL || view.webkitURL || (view as unknown as URLLike);
      },
      save_link = doc.createElementNS(
        "http://www.w3.org/1999/xhtml",
        "a",
      ) as HTMLAnchorElement,
      can_use_save_link = "download" in save_link,
      click = function (node: Node) {
        const event = new MouseEvent("click");
        node.dispatchEvent(event);
      },
      is_safari = /constructor/i.test(String(view.HTMLElement)) || view.safari,
      is_chrome_ios = /CriOS\/[\d]+/.test(navigator.userAgent),
      throw_outside = function (ex: unknown) {
        if (view.setImmediate) {
          view.setImmediate(function (): void {
            throw ex;
          });
        } else {
          view.setTimeout(function (): void {
            throw ex;
          }, 0);
        }
      },
      force_saveable_type = "application/octet-stream",
      // the Blob API is fundamentally broken as there is no "downloadfinished" event to subscribe to
      arbitrary_revoke_timeout = 1000 * 40, // in ms
      revoke = function (file: string | { remove: () => void }) {
        const revoker = function (): void {
          if (typeof file === "string") {
            // file is an object URL
            get_URL().revokeObjectURL(file);
          } else {
            // file is a File
            file.remove();
          }
        };
        setTimeout(revoker, arbitrary_revoke_timeout);
      },
      dispatch = function (
        filesaver: Record<string, unknown>,
        event_types: string | string[],
        event: Event,
      ) {
        event_types = ([] as string[]).concat(event_types);
        let i = event_types.length;
        while (i--) {
          const listener = filesaver["on" + event_types[i]];
          if (typeof listener === "function") {
            try {
              (listener as (e: Event | Record<string, unknown>) => void).call(
                filesaver,
                event || filesaver,
              );
            } catch (ex) {
              throw_outside(ex);
            }
          }
        }
      },
      auto_bom = function (blob: Blob): Blob {
        // prepend BOM for UTF-8 XML and text/* types (including HTML)
        // note: your browser will automatically convert UTF-16 U+FEFF to EF BB BF
        if (
          /^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(
            blob.type,
          )
        ) {
          return new Blob([String.fromCharCode(0xfeff), blob], {
            type: blob.type,
          });
        }
        return blob;
      },
      FileSaver = function (
        this: Record<string, unknown>,
        blob: Blob,
        name: string,
        no_auto_bom: boolean,
      ) {
        if (!no_auto_bom) {
          blob = auto_bom(blob);
        }
        // First try a.download, then web filesystem, then object URLs
        const filesaver = this,
          type = blob.type,
          force = type === force_saveable_type;
        let object_url: string | undefined;
        const dispatch_all = function (): void {
            dispatch(
              filesaver,
              "writestart progress write writeend".split(" "),
              new Event("write"),
            );
          },
          // on any filesys errors revert to saving with object URLs
          fs_error = function (): void {
            if ((is_chrome_ios || (force && is_safari)) && view.FileReader) {
              // Safari doesn't allow downloading of blob urls
              const reader = new FileReader();
              reader.onloadend = function (): void {
                const result = reader.result as string | null;
                let url: string | undefined = is_chrome_ios
                  ? (result ?? undefined)
                  : (result ?? "").replace(
                      /^data:[^;]*;/,
                      "data:attachment/file;",
                    );
                const popup = view.open(url ?? "", "_blank");
                if (!popup) view.location.href = url ?? "";
                url = undefined; // release reference before dispatching
                filesaver.readyState = filesaver.DONE;
                dispatch_all();
              };
              reader.readAsDataURL(blob);
              filesaver.readyState = filesaver.INIT;
              return;
            }
            // don't create more object URLs than needed
            if (!object_url) {
              object_url = get_URL().createObjectURL(blob);
            }
            if (force) {
              view.location.href = object_url;
            } else {
              const opened = view.open(object_url, "_blank");
              if (!opened) {
                // Apple does not allow window.open, see https://developer.apple.com/library/safari/documentation/Tools/Conceptual/SafariExtensionGuide/WorkingwithWindowsandTabs/WorkingwithWindowsandTabs.html
                view.location.href = object_url;
              }
            }
            filesaver.readyState = filesaver.DONE;
            dispatch_all();
            revoke(object_url);
          };
        filesaver.readyState = filesaver.INIT;

        if (can_use_save_link) {
          object_url = get_URL().createObjectURL(blob);
          setTimeout(function (): void {
            if (object_url) {
              save_link.href = object_url;
              save_link.download = name;
              click(save_link);
              dispatch_all();
              revoke(object_url);
              filesaver.readyState = filesaver.DONE;
            }
          });
          return;
        }

        fs_error();
      },
      FS_proto = FileSaver.prototype as Record<string, unknown>,
      saveAs = function (
        blob: Blob & { name?: string },
        name: string,
        no_auto_bom: boolean,
      ) {
        return new (FileSaver as unknown as new (
          blob: Blob,
          name: string,
          no_auto_bom: boolean,
        ) => void)(blob, name || (blob.name ?? "download"), no_auto_bom);
      };
    // IE 10+ (native saveAs)
    const navWithMs = navigator as NavigatorWithMsSave;
    if (navWithMs.msSaveOrOpenBlob) {
      return function (
        blob: Blob & { name?: string },
        name: string,
        no_auto_bom: boolean,
      ) {
        name = name || (blob.name ?? "download");

        if (!no_auto_bom) {
          blob = auto_bom(blob);
        }
        return navWithMs.msSaveOrOpenBlob!(blob, name);
      };
    }

    FS_proto.abort = function (): void {};
    FS_proto.readyState = FS_proto.INIT = 0;
    FS_proto.WRITING = 1;
    FS_proto.DONE = 2;

    FS_proto.error =
      FS_proto.onwritestart =
      FS_proto.onprogress =
      FS_proto.onwrite =
      FS_proto.onabort =
      FS_proto.onerror =
      FS_proto.onwriteend =
        null;

    return saveAs;
  })(
    (typeof self !== "undefined" && self) ||
      (typeof window !== "undefined" && window) ||
      this.content,
  );

  // Expose file saver on the DataTables API. Can't attach to `DataTables.Buttons`
  // since this file can be loaded before Button's core!
  (DataTable as unknown as Record<string, unknown>).fileSave = _saveAs;

  /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   * Local (private) functions
   */

  /**
   * Get the file name for an exported file.
   *
   * @param {object}	config Button configuration
   * @param {boolean} incExtension Include the file name extension
   */
  const _filename = function (
    config: Record<string, unknown>,
    incExtension?: boolean,
  ) {
    // Backwards compatibility
    let filename: string =
      config.filename === "*" &&
      config.title !== "*" &&
      config.title !== undefined
        ? String(config.title)
        : String(config.filename ?? "");

    if (typeof config.filename === "function") {
      filename = String((config.filename as () => unknown)());
    }

    if (filename.indexOf("*") !== -1) {
      filename = filename.replace("*", $("title").text() ?? "").trim();
    }

    // Strip characters which the OS will object to
    filename = filename.replace(/[^a-zA-Z0-9_\u00A1-\uFFFF\.,\-_ !\(\)]/g, "");

    return incExtension === undefined || incExtension === true
      ? filename + String(config.extension ?? "")
      : filename;
  };

  /**
   * Get the sheet name for Excel exports.
   *
   * @param {object}	config Button configuration
   */
  const _sheetname = function (config: Record<string, unknown>) {
    let sheetName = "Sheet1";

    if (config.sheetName) {
      sheetName = String(config.sheetName).replace(/[\[\]\*\/\\\?\:]/g, "");
    }

    return sheetName;
  };

  /**
   * Get the title for an exported file.
   *
   * @param {object} config	Button configuration
   */
  const _title = function (config: Record<string, unknown>) {
    let title: string = String(config.title ?? "");

    if (typeof config.title === "function") {
      title = String((config.title as () => unknown)());
    }

    return title.indexOf("*") !== -1
      ? title.replace("*", $("title").text() ?? "Exported data")
      : title;
  };

  /**
   * Get the newline character(s)
   *
   * @param {object}	config Button configuration
   * @return {string}				Newline character
   */
  const _newLine = function (config: Record<string, unknown>): string {
    return config.newline
      ? String(config.newline)
      : navigator.userAgent.match(/Windows/)
        ? "\r\n"
        : "\n";
  };

  /**
   * Combine the data from the `buttons.exportData` method into a string that
   * will be used in the export file.
   *
   * @param	{DataTable.Api} dt		 DataTables API instance
   * @param	{object}				config Button configuration
   * @return {object}							 The data to export
   */
  const _exportData = function (
    dt: {
      buttons: {
        exportData: (opts: unknown) => {
          header: unknown[];
          footer: unknown[];
          body: unknown[][];
        };
      };
    },
    config: Record<string, unknown>,
  ) {
    const newLine = _newLine(config);
    const data = dt.buttons.exportData(config.exportOptions);
    const boundary = String(config.fieldBoundary ?? "");
    const separator = String(config.fieldSeparator ?? ",");
    const reBoundary = new RegExp(boundary, "g");
    const escapeChar: string =
      config.escapeChar !== undefined ? String(config.escapeChar) : "\\";
    const join = function (a: unknown[]): string {
      let s = "";

      // If there is a field boundary, then we might need to escape it in
      // the source data
      for (let i = 0, ien = a.length; i < ien; i++) {
        if (i > 0) {
          s += separator;
        }

        s += boundary
          ? boundary +
            ("" + a[i]).replace(reBoundary, escapeChar + boundary) +
            boundary
          : String(a[i]);
      }

      return s;
    };

    const header = config.header ? join(data.header) + newLine : "";
    const footer =
      config.footer && data.footer ? newLine + join(data.footer) : "";
    const body: string[] = [];

    for (let i = 0, ien = data.body.length; i < ien; i++) {
      body.push(join(data.body[i]));
    }

    return {
      str: header + body.join(newLine) + footer,
      rows: body.length,
    };
  };

  /**
   * Older versions of Safari (prior to tech preview 18) don't support the
   * download option required.
   *
   * @return {Boolean} `true` if old Safari
   */
  const _isDuffSafari = function (): boolean {
    const safari =
      navigator.userAgent.includes("Safari") &&
      !navigator.userAgent.includes("Chrome") &&
      !navigator.userAgent.includes("Opera");

    if (!safari) {
      return false;
    }

    const version = navigator.userAgent.match(/AppleWebKit\/(\d+\.\d+)/);
    if (version && version.length > 1 && Number(version[1]) < 603.1) {
      return true;
    }

    return false;
  };

  /**
   * Convert from numeric position to letter for column names in Excel
   * @param  {int} n Column number
   * @return {string} Column letter(s) name
   */
  function createCellPos(n: number) {
    const ordA = "A".charCodeAt(0);
    const ordZ = "Z".charCodeAt(0);
    const len = ordZ - ordA + 1;
    let s = "";

    while (n >= 0) {
      s = String.fromCharCode((n % len) + ordA) + s;
      n = Math.floor(n / len) - 1;
    }

    return s;
  }

  let _serialiser: XMLSerializer | null = null;
  let _ieExcel: boolean | undefined;
  try {
    _serialiser = new XMLSerializer();
  } catch (t) {}

  /**
   * Recursively add XML files from an object's structure to a ZIP file. This
   * allows the XSLX file to be easily defined with an object's structure matching
   * the files structure.
   *
   * @param {JSZip} zip ZIP package
   * @param {object} obj Object to add (recursive)
   */
  function _addToZip(
    zip: {
      folder: (name: string) => unknown;
      file: (name: string, content: string) => void;
    },
    obj: Record<string, unknown>,
  ) {
    if (_ieExcel === undefined && _serialiser) {
      // Detect if we are dealing with IE's _awful_ serialiser by seeing if it
      // drop attributes
      _ieExcel = !_serialiser
        .serializeToString(
          $ext.parseXML(excelStrings["xl/worksheets/sheet1.xml"]),
        )
        .includes("xmlns:r");
    }

    $ext.each(obj, function (name: string | number, val: unknown) {
      if ($ext.isPlainObject(val)) {
        const newDir = zip.folder(String(name)) as {
          folder: (name: string) => unknown;
          file: (name: string, content: string) => void;
        };
        _addToZip(newDir, val as Record<string, unknown>);
      } else {
        const xmlDoc = val as Document;
        if (_ieExcel) {
          // IE's XML serialiser will drop some name space attributes from
          // from the root node, so we need to save them. Do this by
          // replacing the namespace nodes with a regular attribute that
          // we convert back when serialised. Edge does not have this
          // issue
          const worksheet = xmlDoc.childNodes[0] as Element;
          let i: number, ien: number;
          const attrs: { name: string; value: string | null }[] = [];

          for (i = worksheet.attributes.length - 1; i >= 0; i--) {
            const attrName = worksheet.attributes[i].nodeName;
            const attrValue = worksheet.attributes[i].nodeValue;

            if (attrName.indexOf(":") !== -1) {
              attrs.push({ name: attrName, value: attrValue });

              worksheet.removeAttribute(attrName);
            }
          }

          for (i = 0, ien = attrs.length; i < ien; i++) {
            const attr = xmlDoc.createAttribute(
              attrs[i].name.replace(":", "_dt_b_namespace_token_"),
            );
            attr.value = attrs[i].value ?? "";
            worksheet.setAttributeNode(attr);
          }
        }

        let str = _serialiser!.serializeToString(xmlDoc);

        // Fix IE's XML
        if (_ieExcel) {
          // IE doesn't include the XML declaration
          if (!str.includes("<?xml")) {
            str =
              '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' + str;
          }

          // Return namespace attributes to being as such
          str = str.replace(/_dt_b_namespace_token_/g, ":");
        }

        // Safari, IE and Edge will put empty name space attributes onto
        // various elements making them useless. This strips them out
        str = str.replace(/<(.*?) xmlns=""(.*?)>/g, "<$1 $2>");

        zip.file(String(name), str);
      }
    });
  }

  /**
   * Create an XML node and add any children, attributes, etc without needing to
   * be verbose in the DOM.
   *
   * @param  {object} doc      XML document
   * @param  {string} nodeName Node name
   * @param  {object} opts     Options - can be `attr` (attributes), `children`
   *   (child nodes) and `text` (text content)
   * @return {node}            Created node
   */
  function _createNode(
    doc: Document,
    nodeName: string,
    opts?: {
      attr?: Record<string, string | number>;
      children?: Node[] | Record<string, Node>;
      text?: string;
    },
  ): Element {
    const tempNode = doc.createElement(nodeName);

    if (opts) {
      if (opts.attr) {
        $(tempNode).attr(opts.attr as Record<string, string | number>);
      }

      if (opts.children) {
        $ext.each(
          opts.children,
          function (key: string | number, value: unknown) {
            tempNode.appendChild(value as Node);
          },
        );
      }

      if (opts.text) {
        tempNode.appendChild(doc.createTextNode(opts.text));
      }
    }

    return tempNode;
  }

  /**
   * Get the width for an Excel column based on the contents of that column
   * @param  {object} data Data for export
   * @param  {int}    col  Column index
   * @return {int}         Column width
   */
  function _excelColWidth(
    data: { header: string[]; footer?: string[]; body: string[][] },
    col: number,
  ) {
    let max = data.header[col].length;
    let len: number, lineSplit: string[], str: string;

    if (data.footer && data.footer[col].length > max) {
      max = data.footer[col].length;
    }

    for (let i = 0, ien = data.body.length; i < ien; i++) {
      str = data.body[i][col].toString();

      // If there is a newline character, workout the width of the column
      // based on the longest line in the string
      if (str.indexOf("\n") !== -1) {
        lineSplit = str.split("\n");
        lineSplit.sort(function (a: string, b: string) {
          return b.length - a.length;
        });

        len = lineSplit[0].length;
      } else {
        len = str.length;
      }

      if (len > max) {
        max = len;
      }

      // Max width rather than having potentially massive column widths
      if (max > 40) {
        break;
      }
    }

    max *= 1.3;

    // And a min width
    return max > 6 ? max : 6;
  }

  // Excel - Pre-defined strings to build a basic XLSX file
  const excelStrings = {
    "_rels/.rels":
      '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
      '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
      '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' +
      "</Relationships>",

    "xl/_rels/workbook.xml.rels":
      '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
      '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
      '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' +
      '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' +
      "</Relationships>",

    "[Content_Types].xml":
      '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
      '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' +
      '<Default Extension="xml" ContentType="application/xml" />' +
      '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml" />' +
      '<Default Extension="jpeg" ContentType="image/jpeg" />' +
      '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml" />' +
      '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml" />' +
      '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml" />' +
      "</Types>",

    "xl/workbook.xml":
      '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
      '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' +
      '<fileVersion appName="xl" lastEdited="5" lowestEdited="5" rupBuild="24816"/>' +
      '<workbookPr showInkAnnotation="0" autoCompressPictures="0"/>' +
      "<bookViews>" +
      '<workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="19020" tabRatio="500"/>' +
      "</bookViews>" +
      "<sheets>" +
      '<sheet name="" sheetId="1" r:id="rId1"/>' +
      "</sheets>" +
      "</workbook>",

    "xl/worksheets/sheet1.xml":
      '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
      '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">' +
      "<sheetData/>" +
      "</worksheet>",

    "xl/styles.xml":
      '<?xml version="1.0" encoding="UTF-8"?>' +
      '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">' +
      '<numFmts count="6">' +
      '<numFmt numFmtId="164" formatCode="#,##0.00_-\ [$$-45C]"/>' +
      '<numFmt numFmtId="165" formatCode="&quot;£&quot;#,##0.00"/>' +
      '<numFmt numFmtId="166" formatCode="[$€-2]\ #,##0.00"/>' +
      '<numFmt numFmtId="167" formatCode="0.0%"/>' +
      '<numFmt numFmtId="168" formatCode="#,##0;(#,##0)"/>' +
      '<numFmt numFmtId="169" formatCode="#,##0.00;(#,##0.00)"/>' +
      "</numFmts>" +
      '<fonts count="5" x14ac:knownFonts="1">' +
      "<font>" +
      '<sz val="11" />' +
      '<name val="Calibri" />' +
      "</font>" +
      "<font>" +
      '<sz val="11" />' +
      '<name val="Calibri" />' +
      '<color rgb="FFFFFFFF" />' +
      "</font>" +
      "<font>" +
      '<sz val="11" />' +
      '<name val="Calibri" />' +
      "<b />" +
      "</font>" +
      "<font>" +
      '<sz val="11" />' +
      '<name val="Calibri" />' +
      "<i />" +
      "</font>" +
      "<font>" +
      '<sz val="11" />' +
      '<name val="Calibri" />' +
      "<u />" +
      "</font>" +
      "</fonts>" +
      '<fills count="6">' +
      "<fill>" +
      '<patternFill patternType="none" />' +
      "</fill>" +
      "<fill/>" + // Excel appears to use this as a dotted background regardless of values
      "<fill>" +
      '<patternFill patternType="solid">' +
      '<fgColor rgb="FFD9D9D9" />' +
      '<bgColor indexed="64" />' +
      "</patternFill>" +
      "</fill>" +
      "<fill>" +
      '<patternFill patternType="solid">' +
      '<fgColor rgb="FFD99795" />' +
      '<bgColor indexed="64" />' +
      "</patternFill>" +
      "</fill>" +
      "<fill>" +
      '<patternFill patternType="solid">' +
      '<fgColor rgb="ffc6efce" />' +
      '<bgColor indexed="64" />' +
      "</patternFill>" +
      "</fill>" +
      "<fill>" +
      '<patternFill patternType="solid">' +
      '<fgColor rgb="ffc6cfef" />' +
      '<bgColor indexed="64" />' +
      "</patternFill>" +
      "</fill>" +
      "</fills>" +
      '<borders count="2">' +
      "<border>" +
      "<left />" +
      "<right />" +
      "<top />" +
      "<bottom />" +
      "<diagonal />" +
      "</border>" +
      '<border diagonalUp="false" diagonalDown="false">' +
      '<left style="thin">' +
      '<color auto="1" />' +
      "</left>" +
      '<right style="thin">' +
      '<color auto="1" />' +
      "</right>" +
      '<top style="thin">' +
      '<color auto="1" />' +
      "</top>" +
      '<bottom style="thin">' +
      '<color auto="1" />' +
      "</bottom>" +
      "<diagonal />" +
      "</border>" +
      "</borders>" +
      '<cellStyleXfs count="1">' +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" />' +
      "</cellStyleXfs>" +
      '<cellXfs count="67">' +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="1" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="2" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="3" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>' +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">' +
      '<alignment horizontal="left"/>' +
      "</xf>" +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">' +
      '<alignment horizontal="center"/>' +
      "</xf>" +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">' +
      '<alignment horizontal="right"/>' +
      "</xf>" +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">' +
      '<alignment horizontal="fill"/>' +
      "</xf>" +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">' +
      '<alignment textRotation="90"/>' +
      "</xf>" +
      '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">' +
      '<alignment wrapText="1"/>' +
      "</xf>" +
      '<xf numFmtId="9"   fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="166" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="167" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="168" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="169" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="4" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="1" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      '<xf numFmtId="2" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>' +
      "</cellXfs>" +
      '<cellStyles count="1">' +
      '<cellStyle name="Normal" xfId="0" builtinId="0" />' +
      "</cellStyles>" +
      '<dxfs count="0" />' +
      '<tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleMedium4" />' +
      "</styleSheet>",
  };
  // Note we could use 3 `for` loops for the styles, but when gzipped there is
  // virtually no difference in size, since the above can be easily compressed

  // Pattern matching for special number formats. Perhaps this should be exposed
  // via an API in future?
  // Ref: section 3.8.30 - built in formatters in open spreadsheet
  //   https://www.ecma-international.org/news/TC45_current_work/Office%20Open%20XML%20Part%204%20-%20Markup%20Language%20Reference.pdf
  const _excelSpecials: Array<{
    match: RegExp;
    style: number;
    fmt?: (d: string | number) => number;
  }> = [
    {
      match: /^\-?\d+\.\d%$/,
      style: 60,
      fmt: function (d) {
        return Number(d) / 100;
      },
    }, // Precent with d.p.
    {
      match: /^\-?\d+\.?\d*%$/,
      style: 56,
      fmt: function (d) {
        return Number(d) / 100;
      },
    }, // Percent
    { match: /^\-?\$[\d,]+.?\d*$/, style: 57 }, // Dollars
    { match: /^\-?£[\d,]+.?\d*$/, style: 58 }, // Pounds
    { match: /^\-?€[\d,]+.?\d*$/, style: 59 }, // Euros
    { match: /^\-?\d+$/, style: 65 }, // Numbers without thousand separators
    { match: /^\-?\d+\.\d{2}$/, style: 66 }, // Numbers 2 d.p. without thousands separators
    {
      match: /^\([\d,]+\)$/,
      style: 61,
      fmt: function (d) {
        return -1 * Number(String(d).replace(/[\(\)]/g, ""));
      },
    }, // Negative numbers indicated by brackets
    {
      match: /^\([\d,]+\.\d{2}\)$/,
      style: 62,
      fmt: function (d) {
        return -1 * Number(String(d).replace(/[\(\)]/g, ""));
      },
    }, // Negative numbers indicated by brackets - 2d.p.
    { match: /^\-?[\d,]+$/, style: 63 }, // Numbers with thousand separators
    { match: /^\-?[\d,]+\.\d{2}$/, style: 64 }, // Numbers with 2 d.p. and thousands separators
  ];

  /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   * Buttons
   */

  //
  // Copy to clipboard
  //
  DataTableTyped.ext.buttons.copyHtml5 = {
    className: "buttons-copy buttons-html5",

    text: function (dt: DataTableInstance): string {
      return dt.i18n("buttons.copy", "Copy");
    },

    action: function (
      e: Event,
      dt: DataTableInstance,
      button: HTMLButtonElement,
      config: Record<string, unknown>,
    ): void {
      const exportData = _exportData(dt, config);
      let output = exportData.str;
      const hiddenDiv = $("<div/>").css({
        height: 1,
        width: 1,
        overflow: "hidden",
        position: "fixed",
        top: 0,
        left: 0,
      });

      if (config.customize) {
        output = (
          config.customize as (
            output: string,
            config: Record<string, unknown>,
          ) => string
        )(output, config);
      }

      const textarea = $("<textarea readonly/>")
        .val(output)
        .appendTo(hiddenDiv);

      // For browsers that support the copy execCommand, try to use it
      if (document.queryCommandSupported("copy")) {
        hiddenDiv.appendTo(dt.table().container());
        (textarea[0] as HTMLTextAreaElement).focus();
        (textarea[0] as HTMLTextAreaElement).select();

        try {
          const successful = document.execCommand("copy");
          hiddenDiv.remove();

          if (successful) {
            dt.buttons.info(
              dt.i18n("buttons.copyTitle", "Copy to clipboard"),
              dt.i18n(
                "buttons.copySuccess",
                {
                  1: "Copied one row to clipboard",
                  _: "Copied %d rows to clipboard",
                },
                exportData.rows,
              ),
              2000,
            );
            return;
          }
        } catch (t) {}
      }

      // Otherwise we show the text box and instruct the user to use it
      const message = $(
        "<span>" +
          dt.i18n(
            "buttons.copyKeys",
            "Press <i>ctrl</i> or <i>\u2318</i> + <i>C</i> to copy the table data<br>to your system clipboard.<br><br>" +
              "To cancel, click this message or press escape.",
          ) +
          "</span>",
      ).append(hiddenDiv);

      dt.buttons.info(
        dt.i18n("buttons.copyTitle", "Copy to clipboard"),
        message,
        0,
      );

      // Select the text so when the user activates their system clipboard
      // it will copy that text
      (textarea[0] as HTMLTextAreaElement).focus();
      (textarea[0] as HTMLTextAreaElement).select();

      // Event to hide the message when the user is done
      const container = message.closest(".dt-button-info");
      const close = function (): void {
        container.off("click.buttons-copy");
        $(document).off(".buttons-copy");
        dt.buttons.info(false);
      };

      container.on("click.buttons-copy", close);
      $(document)
        .on("keydown.buttons-copy", function (e) {
          if ((e as unknown as KeyboardEvent).keyCode === 27) {
            // esc
            close();
          }
        })
        .on("copy.buttons-copy cut.buttons-copy", function (): void {
          close();
        });
    },

    exportOptions: {},

    fieldSeparator: "\t",

    fieldBoundary: "",

    header: true,

    footer: false,
  };

  //
  // CSV export
  //
  DataTableTyped.ext.buttons.csvHtml5 = {
    bom: false,

    className: "buttons-csv buttons-html5",

    available: function (): boolean {
      return bWindow.FileReader !== undefined && !!bWindow.Blob;
    },

    text: function (dt: DataTableInstance): string {
      return dt.i18n("buttons.csv", "CSV");
    },

    action: function (
      e: Event,
      dt: DataTableInstance,
      button: HTMLButtonElement,
      config: Record<string, unknown>,
    ): void {
      // Set the text
      let output = _exportData(dt, config).str;
      let charset = config.charset as string | false | null;

      if (config.customize) {
        output = (
          config.customize as (
            output: string,
            config: Record<string, unknown>,
          ) => string
        )(output, config);
      }

      if (charset !== false) {
        if (!charset) {
          charset =
            document.characterSet ||
            ((document as Document & { charset?: string }).charset as
              | string
              | null);
        }

        if (charset) {
          charset = ";charset=" + charset;
        }
      } else {
        charset = "";
      }

      if (config.bom) {
        output = "\ufeff" + output;
      }

      _saveAs?.(
        new Blob([output], { type: "text/csv" + charset }),
        _filename(config),
        true,
      );
    },

    filename: "*",

    extension: ".csv",

    exportOptions: {},

    fieldSeparator: ",",

    fieldBoundary: '"',

    escapeChar: '"',

    charset: null,

    header: true,

    footer: false,
  };

  //
  // Excel (xlsx) export
  //
  DataTableTyped.ext.buttons.excelHtml5 = {
    className: "buttons-excel buttons-html5",

    available: function (): boolean {
      return (
        bWindow.FileReader !== undefined &&
        _jsZip() !== undefined &&
        !_isDuffSafari() &&
        !!_serialiser
      );
    },

    text: function (dt: DataTableInstance): string {
      return dt.i18n("buttons.excel", "Excel");
    },

    action: function (
      e: Event,
      dt: DataTableInstance,
      button: HTMLButtonElement,
      config: Record<string, unknown>,
    ): void {
      let rowPos = 0;
      const getXml = function (type: keyof typeof excelStrings): Document {
        const str = excelStrings[type];

        //str = str.replace( /xmlns:/g, 'xmlns_' ).replace( /mc:/g, 'mc_' );

        return $ext.parseXML(str);
      };
      const rels = getXml("xl/worksheets/sheet1.xml");
      const relsGet = rels.getElementsByTagName("sheetData")[0];

      const xlsx = {
        _rels: {
          ".rels": getXml("_rels/.rels"),
        },
        xl: {
          _rels: {
            "workbook.xml.rels": getXml("xl/_rels/workbook.xml.rels"),
          },
          "workbook.xml": getXml("xl/workbook.xml"),
          "styles.xml": getXml("xl/styles.xml"),
          worksheets: {
            "sheet1.xml": rels,
          },
        },
        "[Content_Types].xml": getXml("[Content_Types].xml"),
      };

      const data = dt.buttons.exportData(
        config.exportOptions as Record<string, unknown> | undefined,
      );
      let currentRow: number, rowNode: Node;
      const addRow = function (row: string[]): void {
        currentRow = rowPos + 1;
        rowNode = _createNode(rels, "row", { attr: { r: currentRow } });

        for (let i = 0, ien = row.length; i < ien; i++) {
          // Concat both the Cell Columns as a letter and the Row of the cell.
          const cellId = createCellPos(i) + "" + currentRow;
          let cell = null;

          // For null, undefined of blank cell, continue so it doesn't create the _createNode
          if (row[i] === null || row[i] === undefined || row[i] === "") {
            continue;
          }

          row[i] = $ext.trim(row[i]);

          // Special number formatting options
          for (let j = 0, jen = _excelSpecials.length; j < jen; j++) {
            const special = _excelSpecials[j];

            if (row[i].match?.(special.match)) {
              let val: string = row[i].replace(/[^\d\.\-]/g, "");

              if (special.fmt) {
                val = String(special.fmt(val as unknown as string | number));
              }

              cell = _createNode(rels, "c", {
                attr: {
                  r: cellId,
                  s: special.style,
                },
                children: [_createNode(rels, "v", { text: val })],
              });

              break;
            }
          }

          if (!cell) {
            if (
              typeof row[i] === "number" ||
              (row[i].match?.(/^-?\d+(\.\d+)?$/) && !row[i].match(/^0\d+/))
            ) {
              // Detect numbers - don't match numbers with leading zeros
              // or a negative anywhere but the start
              cell = _createNode(rels, "c", {
                attr: {
                  t: "n",
                  r: cellId,
                },
                children: [_createNode(rels, "v", { text: row[i] })],
              });
            } else {
              // String output - replace non standard characters for text output
              const text = !row[i].replace
                ? row[i]
                : row[i].replace(/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F-\x9F]/g, "");

              cell = _createNode(rels, "c", {
                attr: {
                  t: "inlineStr",
                  r: cellId,
                },
                children: {
                  row: _createNode(rels, "is", {
                    children: {
                      row: _createNode(rels, "t", {
                        text: text,
                      }),
                    },
                  }),
                },
              });
            }
          }

          rowNode.appendChild(cell);
        }

        relsGet.appendChild(rowNode);
        rowPos++;
      };

      $(xlsx.xl["workbook.xml"])
        .find("sheets sheet")
        .attr("name", _sheetname(config));

      if (config.customizeData) {
        (config.customizeData as (data: ExportData) => void)(data);
      }

      if (config.header) {
        addRow(data.header);
        $(rels).find("row c").attr("s", "2"); // bold
      }

      for (let n = 0, ie = data.body.length; n < ie; n++) {
        addRow(data.body[n]);
      }

      if (config.footer && data.footer) {
        addRow(data.footer);
        $(rels).find("row:last c").attr("s", "2"); // bold
      }

      // Set column widths
      const cols = _createNode(rels, "cols");
      $(rels).find("worksheet").prepend(cols);

      for (let i = 0, ien = data.header.length; i < ien; i++) {
        cols.appendChild(
          _createNode(rels, "col", {
            attr: {
              min: i + 1,
              max: i + 1,
              width: _excelColWidth(data, i),
              customWidth: 1,
            },
          }),
        );
      }

      // Let the developer customise the document if they want to
      if (config.customize) {
        (config.customize as (xlsx: Record<string, unknown>) => void)(xlsx);
      }

      const jsZipCtor = _jsZip() as { new (): JSZipLike };
      const zip = new jsZipCtor();
      const zipConfig = {
        type: "blob",
        mimeType:
          "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      };

      _addToZip(zip, xlsx);

      if (zip.generateAsync) {
        // JSZip 3+
        zip.generateAsync(zipConfig).then(function (blob: Blob): void {
          _saveAs?.(blob, _filename(config), false);
        });
      } else {
        // JSZip 2.5
        _saveAs?.(zip.generate?.(zipConfig) as Blob, _filename(config), false);
      }
    },

    filename: "*",

    extension: ".xlsx",

    exportOptions: {},

    header: true,

    footer: false,
  };

  //
  // PDF export - using pdfMake - http://pdfmake.org
  //
  DataTableTyped.ext.buttons.pdfHtml5 = {
    className: "buttons-pdf buttons-html5",

    available: function (): boolean {
      return bWindow.FileReader !== undefined && !!_pdfMake();
    },

    text: function (dt: DataTableInstance): string {
      return dt.i18n("buttons.pdf", "PDF");
    },

    action: function (
      e: Event,
      dt: DataTableInstance,
      button: HTMLButtonElement,
      config: Record<string, unknown>,
    ): void {
      const newLine = _newLine(config);
      const data = dt.buttons.exportData(
        config.exportOptions as Record<string, unknown> | undefined,
      );
      const rows: Array<Array<{ text: string; style: string }>> = [];

      if (config.header) {
        rows.push(
          $ext.map(data.header, function (d: string): {
            text: string;
            style: string;
          } {
            return {
              text: typeof d === "string" ? d : d + "",
              style: "tableHeader",
            };
          }),
        );
      }

      for (let i = 0, ien = data.body.length; i < ien; i++) {
        rows.push(
          $ext.map(data.body[i], function (d: string): {
            text: string;
            style: string;
          } {
            return {
              text: typeof d === "string" ? d : d + "",
              style: i % 2 ? "tableBodyEven" : "tableBodyOdd",
            };
          }),
        );
      }

      if (config.footer && data.footer) {
        rows.push(
          $ext.map(data.footer, function (d: string): {
            text: string;
            style: string;
          } {
            return {
              text: typeof d === "string" ? d : d + "",
              style: "tableFooter",
            };
          }),
        );
      }

      const doc: {
        pageSize: unknown;
        pageOrientation: unknown;
        content: Array<Record<string, unknown>>;
        styles: Record<string, Record<string, unknown>>;
        defaultStyle: { fontSize: number };
      } = {
        pageSize: config.pageSize,
        pageOrientation: config.orientation,
        content: [
          {
            table: {
              headerRows: 1,
              body: rows,
            },
            layout: "noBorders",
          },
        ],
        styles: {
          tableHeader: {
            bold: true,
            fontSize: 11,
            color: "white",
            fillColor: "#2d4154",
            alignment: "center",
          },
          tableBodyEven: {},
          tableBodyOdd: {
            fillColor: "#f3f3f3",
          },
          tableFooter: {
            bold: true,
            fontSize: 11,
            color: "white",
            fillColor: "#2d4154",
          },
          title: {
            alignment: "center",
            fontSize: 15,
          },
          message: {},
        },
        defaultStyle: {
          fontSize: 10,
        },
      };

      if (config.message) {
        doc.content.unshift({
          text:
            typeof config.message == "function"
              ? config.message(dt, button, config)
              : config.message,
          style: "message",
          margin: [0, 0, 0, 12],
        });
      }

      if (config.title) {
        doc.content.unshift({
          text: _title(config),
          style: "title",
          margin: [0, 0, 0, 12],
        });
      }

      if (config.customize) {
        (
          config.customize as (
            doc: Record<string, unknown>,
            config: Record<string, unknown>,
          ) => void
        )(doc, config);
      }

      const pdfMakeLib = _pdfMake() as {
        createPdf: (doc: unknown) => {
          open: () => void;
          getBuffer: (fn: (buffer: ArrayBuffer) => void) => void;
        };
      };
      const pdf = pdfMakeLib.createPdf(doc);

      if (config.download === "open" && !_isDuffSafari()) {
        pdf.open();
      } else {
        pdf.getBuffer(function (buffer: ArrayBuffer): void {
          const blob = new Blob([buffer], { type: "application/pdf" });

          _saveAs?.(blob, _filename(config), false);
        });
      }
    },

    title: "*",

    filename: "*",

    extension: ".pdf",

    exportOptions: {},

    orientation: "portrait",

    pageSize: "A4",

    header: true,

    footer: false,

    message: null,

    customize: null,

    download: "download",
  };

  return DataTableTyped.Buttons;
});
