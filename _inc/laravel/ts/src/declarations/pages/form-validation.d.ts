/**
 * Form Validation Type Declarations
 * @file ts/src/declarations/pages/form-validation.d.ts
 * @description Type declarations for Bouncer form validation library
 */

/**
 * Bouncer constructor type for form validation
 */
export type BouncerConstructor = new (
  selector: string,
  options?: Record<string, unknown>,
) => unknown;
