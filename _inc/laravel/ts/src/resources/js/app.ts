/**
 * @fileoverview TypeScript version of resources/js/app.js
 * @generated from original JavaScript - manual review recommended
 * @module app
 */

/* global bootstrap */
import "./bootstrap";

// @ts-expect-error - alpinejs is a runtime dependency without type declarations
import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();
