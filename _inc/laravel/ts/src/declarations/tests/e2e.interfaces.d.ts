/**
 * E2E Test Interfaces
 * @file ts/src/declarations/tests/e2e.interfaces.d.ts
 * @description Shared interfaces for Playwright E2E tests
 */

/**
 * Options for asserting page content and structure in E2E tests
 */
export interface AssertPageOptions {
  expectTable?: boolean;
  expectCard?: boolean;
  expectForm?: boolean;
  expectBreadcrumb?: boolean;
  expectText?: string;
  expectKanban?: boolean;
  expectSelect2?: boolean;
}
