#!/usr/bin/env node
/**
 * TypeScript Migration Script
 * Converts JavaScript files to TypeScript with proper type annotations.
 * 
 * This script reads the original .js/.cjs files and creates TypeScript versions
 * with appropriate type annotations and module syntax.
 * 
 * Usage:
 *   node convert-to-ts.mjs [--dry-run] [--verbose]
 */

import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const LARAVEL_ROOT = path.resolve(__dirname, '..');
const TS_SRC = path.resolve(__dirname, 'src');

// Read the file list
const FILE_LIST_PATH = '/tmp/app_js_clean.txt';

/**
 * Convert JavaScript code to TypeScript with minimal but useful type annotations.
 * @param {string} content - Original JS content
 * @param {string} filePath - Path to the original file
 * @returns {string} - TypeScript content
 */
function convertToTypeScript(content, filePath) {
  let ts = content;
  const isIIFE = ts.trim().startsWith('(() =>') || ts.trim().startsWith('(function');
  const isCJS = filePath.endsWith('.cjs');
  const isTestFile = filePath.includes('/tests/') || filePath.includes('.spec.') || filePath.includes('.test.');
  
  // Add file header
  const header = `/**
 * @fileoverview TypeScript version of ${path.relative(LARAVEL_ROOT, filePath)}
 * @generated from original JavaScript - manual review recommended
 * @module ${path.basename(filePath, path.extname(filePath))}
 */

`;

  // Convert CommonJS requires to ESM imports for non-test files
  if (isCJS && !isTestFile) {
    // Convert require statements
    ts = ts.replace(
      /const\s+(\{[^}]+\})\s*=\s*require\s*\(\s*["']([^"']+)["']\s*\)/g,
      'import $1 from "$2"'
    );
    ts = ts.replace(
      /const\s+(\w+)\s*=\s*require\s*\(\s*["']([^"']+)["']\s*\)/g,
      'import $1 from "$2"'
    );
    
    // Convert module.exports
    ts = ts.replace(/module\.exports\s*=\s*/, 'export default ');
    
    // Convert exports.something
    ts = ts.replace(/exports\.(\w+)\s*=/g, 'export const $1 =');
  }
  
  // For test files (CJS), keep the require syntax but add @ts-check and types
  if (isCJS && isTestFile) {
    if (!ts.includes('@ts-check')) {
      ts = '// @ts-check\n' + ts;
    }
  }
  
  // Add type annotations to common patterns
  
  // Event handlers: (event) => -> (event: Event) =>
  ts = ts.replace(/\(\s*event\s*\)\s*=>/g, '(event: Event): void =>');
  ts = ts.replace(/\(\s*e\s*\)\s*=>/g, '(e: Event): void =>');
  
  // Mouse events
  ts = ts.replace(/\(\s*mouseEvent\s*\)\s*=>/g, '(mouseEvent: MouseEvent): void =>');
  ts = ts.replace(/\(\s*clickEvent\s*\)\s*=>/g, '(clickEvent: MouseEvent): void =>');
  
  // Keyboard events
  ts = ts.replace(/\(\s*keyEvent\s*\)\s*=>/g, '(keyEvent: KeyboardEvent): void =>');
  ts = ts.replace(/\(\s*keydownEvent\s*\)\s*=>/g, '(keydownEvent: KeyboardEvent): void =>');
  
  // Element type assertions for querySelector results
  ts = ts.replace(
    /document\.querySelector\s*\(\s*["']([^"']+)["']\s*\)/g,
    'document.querySelector<HTMLElement>("$1")'
  );
  
  // Form elements
  ts = ts.replace(
    /document\.querySelector<HTMLElement>\s*\(\s*["']input([^"']*)["']\s*\)/g,
    'document.querySelector<HTMLInputElement>("input$1")'
  );
  ts = ts.replace(
    /document\.querySelector<HTMLElement>\s*\(\s*["']select([^"']*)["']\s*\)/g,
    'document.querySelector<HTMLSelectElement>("select$1")'
  );
  ts = ts.replace(
    /document\.querySelector<HTMLElement>\s*\(\s*["']textarea([^"']*)["']\s*\)/g,
    'document.querySelector<HTMLTextAreaElement>("textarea$1")'
  );
  ts = ts.replace(
    /document\.querySelector<HTMLElement>\s*\(\s*["']button([^"']*)["']\s*\)/g,
    'document.querySelector<HTMLButtonElement>("button$1")'
  );
  ts = ts.replace(
    /document\.querySelector<HTMLElement>\s*\(\s*["']a([^"']*)["']\s*\)/g,
    'document.querySelector<HTMLAnchorElement>("a$1")'
  );
  ts = ts.replace(
    /document\.querySelector<HTMLElement>\s*\(\s*["']form([^"']*)["']\s*\)/g,
    'document.querySelector<HTMLFormElement>("form$1")'
  );
  
  // NodeList iteration type
  ts = ts.replace(
    /\.forEach\s*\(\s*el\s*=>/g,
    '.forEach((el: Element): void =>'
  );
  ts = ts.replace(
    /\.forEach\s*\(\s*\(\s*el\s*\)\s*=>/g,
    '.forEach((el: Element): void =>'
  );
  ts = ts.replace(
    /\.forEach\s*\(\s*element\s*=>/g,
    '.forEach((element: Element): void =>'
  );
  
  // Add null checks for common patterns
  // Change .getAttribute to have proper return type handling
  
  // Type the 'this' in callbacks (convert function to arrow or add annotation)
  
  // For IIFE patterns, wrap in a void expression or add export {}
  if (isIIFE && !isCJS) {
    // Add export {} to make it a module
    ts = ts + '\n\nexport {};\n';
  }
  
  // Add global declarations comment for browser globals used
  const usesFeather = ts.includes('feather');
  const usesBootstrap = ts.includes('bootstrap') || ts.includes('Modal');
  const usesApex = ts.includes('ApexCharts');
  const usesFlatpickr = ts.includes('flatpickr');
  const usesSwal = ts.includes('Swal');
  const usesJQuery = ts.includes('$') || ts.includes('jQuery');
  
  let globalComment = '';
  const globals = [];
  if (usesFeather) globals.push('feather');
  if (usesBootstrap) globals.push('bootstrap');
  if (usesApex) globals.push('ApexCharts');
  if (usesFlatpickr) globals.push('flatpickr');
  if (usesSwal) globals.push('Swal');
  if (usesJQuery) globals.push('$, jQuery');
  
  if (globals.length > 0) {
    globalComment = `/* global ${globals.join(', ')} */\n`;
  }
  
  return header + globalComment + ts;
}

/**
 * Main conversion function
 */
async function main() {
  const args = process.argv.slice(2);
  const dryRun = args.includes('--dry-run');
  const verbose = args.includes('--verbose');
  
  console.log('TypeScript Migration Script');
  console.log('===========================');
  console.log(`Mode: ${dryRun ? 'DRY RUN' : 'LIVE'}`);
  console.log(`Laravel root: ${LARAVEL_ROOT}`);
  console.log(`TS source: ${TS_SRC}`);
  console.log('');
  
  // Read file list
  let fileList;
  try {
    const content = await fs.readFile(FILE_LIST_PATH, 'utf-8');
    fileList = content.trim().split('\n').filter(Boolean);
  } catch (err) {
    console.error(`Error reading file list: ${err.message}`);
    console.log('Make sure to run the file discovery first');
    process.exit(1);
  }
  
  console.log(`Found ${fileList.length} files to convert`);
  console.log('');
  
  let converted = 0;
  let errors = 0;
  
  for (const relativePath of fileList) {
    const srcPath = path.join(LARAVEL_ROOT, relativePath);
    
    // Determine target path
    const ext = path.extname(relativePath);
    const tsRelativePath = relativePath.replace(/\.(js|cjs)$/, '.ts');
    const destPath = path.join(TS_SRC, tsRelativePath);
    
    if (verbose) {
      console.log(`Converting: ${relativePath}`);
    }
    
    try {
      // Read original file
      const content = await fs.readFile(srcPath, 'utf-8');
      
      // Convert to TypeScript
      const tsContent = convertToTypeScript(content, srcPath);
      
      if (dryRun) {
        console.log(`[DRY RUN] Would create: ${destPath}`);
      } else {
        // Ensure directory exists
        await fs.mkdir(path.dirname(destPath), { recursive: true });
        
        // Write TypeScript file
        await fs.writeFile(destPath, tsContent, 'utf-8');
      }
      
      converted++;
    } catch (err) {
      console.error(`Error converting ${relativePath}: ${err.message}`);
      errors++;
    }
  }
  
  console.log('');
  console.log('=== Summary ===');
  console.log(`Converted: ${converted}`);
  console.log(`Errors: ${errors}`);
  
  if (dryRun) {
    console.log('');
    console.log('This was a dry run. Run without --dry-run to actually convert files.');
  }
}

main().catch(console.error);
