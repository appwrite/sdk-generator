#!/usr/bin/env node
/**
 * Build-time script to patch giget's node-fetch-native import for Bun compatibility.
 * This is called before compilation to ensure the bundled code works.
 */

import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Check both locations where giget might be installed
const dirs = [
  path.join(__dirname, "..", "node_modules", "giget", "dist", "shared"),
  path.join(__dirname, "..", "node_modules", "c12", "node_modules", "giget", "dist", "shared"),
];

let patched = false;

for (const dir of dirs) {
  if (!fs.existsSync(dir)) continue;

  const files = fs.readdirSync(dir);
  const gigetFiles = files.filter((f) => /^giget\..*\.mjs$/.test(f));

  for (const file of gigetFiles) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, "utf-8");

    const importPattern = /import\s+{\s*fetch\s*}\s+from\s+['"]node-fetch-native\/proxy['"];/;
    if (importPattern.test(content)) {
      content = content.replace(
        importPattern,
        "// Patched: using global fetch instead of node-fetch-native/proxy"
      );
      fs.writeFileSync(filePath, content, "utf-8");
      console.log(`✓ Patched ${filePath}`);
      patched = true;
    }
  }
}

if (!patched) {
  console.log("ℹ No giget files needed patching");
}
