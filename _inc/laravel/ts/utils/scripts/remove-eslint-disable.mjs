import fs from 'fs';
import path from 'path';

function findTsFiles(dir) {
  const files = [];
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory() && !['node_modules', 'dist'].includes(entry.name)) {
      files.push(...findTsFiles(fullPath));
    } else if (entry.isFile() && entry.name.endsWith('.ts')) {
      files.push(fullPath);
    }
  }
  return files;
}

const files = findTsFiles('src');
let modified = 0;

for (const file of files) {
  const content = fs.readFileSync(file, 'utf8');
  const lines = content.split('\n');
  const newLines = [];
  let changed = false;
  
  for (const line of lines) {
    // Skip file-level eslint-disable comments at start of file
    if (/^\/\* eslint-disable/.test(line.trim())) {
      changed = true;
      continue;
    }
    // Skip eslint-disable-next-line comments
    if (/^\s*\/\/ eslint-disable-next-line/.test(line)) {
      changed = true;
      continue;
    }
    newLines.push(line);
  }
  
  if (changed) {
    fs.writeFileSync(file, newLines.join('\n'));
    modified++;
  }
}

console.log(`Removed eslint-disable comments from ${modified} files`);
