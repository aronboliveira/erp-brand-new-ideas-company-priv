/**
 * @fileoverview Shared immutable string constants across TS front-end modules.
 * Every value in SHARED must be truly cross-file; file-local constants
 * belong at the top of their own module.
 *
 * NOTE: custom.ts and code.ts are currently loaded as global scripts
 * (not ES modules). Until they are migrated to modules, they duplicate
 * SHARED values locally. When converting them, replace local copies with:
 *   import { SHARED } from "./.constants";
 *
 * @module .constants
 */

export const SHARED = Object.freeze({
  /** Selector for the CSRF-token meta tag. */
  SEL_CSRF_META: 'meta[name="csrf-token"]',
} as const);
