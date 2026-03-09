/**
 * AJAX Response Type Declarations
 * @file ts/src/declarations/routes/ajax-responses.interfaces.d.ts
 * @description Shared interfaces for AJAX response types
 */

/**
 * POS cart API response
 */
export interface PosCartResponse {
  ok?: boolean;
  subtotal_formatted?: string;
  total_formatted?: string;
}

/**
 * POS product item data structure
 */
export interface ProductItem {
  id: string | number;
  name?: string;
  price?: string | number;
  price_formatted?: string;
  add_label?: string;
}

/**
 * Grammar AI endpoint response
 */
export interface GrammarAjaxResponse {
  message?: string;
  [key: string]: unknown;
}

/**
 * Zoom meeting delete action response
 */
export interface DeleteAjaxResponse {
  flag?: number;
  msg?: string;
}

/**
 * Server-side localized language strings for Zoom meetings
 */
export interface SvLang {
  zoomMeetings?: {
    store?: {
      routeGuardDefault?: string;
    };
  };
}
