/**
 * @fileoverview TypeScript version of resources/js/app.js
 * @generated from original JavaScript - manual review recommended
 * @module app
 */

import "./bootstrap";

// @ts-ignore - alpinejs is a runtime dependency without type declarations
import Alpine from "alpinejs";

window.Alpine = Alpine;

// eslint-disable-next-line @typescript-eslint/no-unsafe-call
// eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
Alpine.start();
