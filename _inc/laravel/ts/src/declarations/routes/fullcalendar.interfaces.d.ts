/**
 * FullCalendar Type Declarations
 * @file ts/src/declarations/routes/fullcalendar.interfaces.d.ts
 * @description Shared interfaces for FullCalendar integration
 */

/**
 * FullCalendar instance with basic lifecycle methods
 */
export interface FullCalendarInstance {
  render(): void;
  destroy(): void;
}

/**
 * FullCalendar static constructor
 */
export interface FullCalendarStatic {
  Calendar: new (
    el: HTMLElement,
    options: Record<string, unknown>,
  ) => FullCalendarInstance;
}

/**
 * HTML element extended with FullCalendar instance reference
 */
export interface CalendarHTMLElement extends HTMLElement {
  _fcInstance?: FullCalendarInstance | null;
}
