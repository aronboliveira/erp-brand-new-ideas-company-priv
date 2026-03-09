/**
 * Datepicker Type Declarations
 * @file ts/src/declarations/pages/datepicker.d.ts
 * @description Type declarations for vanillajs-datepicker library
 */

/**
 * Datepicker constructor type
 */
export type DatepickerConstructor = new (
  el: HTMLElement | null,
  options?: Record<string, unknown>,
) => unknown;

/**
 * DateRangePicker constructor type
 */
export type DateRangePickerConstructor = new (
  el: HTMLElement | null,
  options?: Record<string, unknown>,
) => unknown;
