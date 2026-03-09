/**
 * Dragula Type Declarations
 * @file ts/src/declarations/routes/dragula.interfaces.d.ts
 * @description Shared interfaces for Dragula drag-and-drop library
 */

/**
 * Dragula instance with event binding and cleanup
 */
export interface DragulaInstance {
  on(event: string, callback: (...args: unknown[]) => void): DragulaInstance;
  destroy(): void;
}

/**
 * Dragula constructor function type
 */
export type DragulaStatic = (
  containers: Element[],
  options?: Record<string, unknown>,
) => DragulaInstance;

/**
 * Dictionary of localized translation strings
 */
export type TranslationsDict = Record<string, Record<string, string>>;
