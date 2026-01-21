#!/usr/bin/env tsx
/**
 * Build script to generate the manifest from markdown files.
 * Run with: npx tsx scripts/build-manifest.ts
 */

import { readdir, readFile, writeFile, stat } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import type {
  Manifest,
  TableOfContents,
  ServiceEntry,
  MethodEntry,
} from '../src/types.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const DOCS_DIR = join(ROOT, 'docs');
const MANIFEST_OUTPUT = join(ROOT, 'src', 'manifest.ts');
const GENERATED_TYPES_OUTPUT = join(ROOT, 'src', 'generated-types.ts');

/**
 * Convert kebab-case to Title Case
 */
function kebabToTitle(kebab: string): string {
  return kebab
    .split('-')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

/**
 * Extract metadata from markdown content
 */
function extractMetadata(content: string): {
  title: string;
  description: string;
  deprecated: boolean;
} {
  const lines = content.split('\n');

  // Extract title from first H1
  const titleLine = lines.find((line) => line.startsWith('# '));
  const title = titleLine ? titleLine.replace(/^#\s+/, '').trim() : '';

  // Check for deprecation notice
  const deprecated = content.includes('> ⚠️ This method is deprecated');

  // Extract description - look for first paragraph after title
  let description = '';
  let foundTitle = false;
  for (const line of lines) {
    if (line.startsWith('# ')) {
      foundTitle = true;
      continue;
    }
    if (foundTitle && line.trim() && !line.startsWith('#') && !line.startsWith('>')) {
      description = line
        .replace(/\*\*/g, '')
        .replace(/`/g, '')
        .trim()
        .slice(0, 200);
      break;
    }
  }

  return { title, description, deprecated };
}

/**
 * Check if a path is a directory
 */
async function isDirectory(path: string): Promise<boolean> {
  try {
    const stats = await stat(path);
    return stats.isDirectory();
  } catch {
    return false;
  }
}

/**
 * Build manifest for a single language
 */
async function buildLanguageManifest(
  language: string,
  languagePath: string
): Promise<TableOfContents> {
  const services: ServiceEntry[] = [];

  // Read all service directories
  const entries = await readdir(languagePath);

  for (const serviceName of entries.sort()) {
    const servicePath = join(languagePath, serviceName);

    if (!(await isDirectory(servicePath))) continue;

    const methods: MethodEntry[] = [];
    const files = await readdir(servicePath);

    for (const file of files.sort()) {
      if (!file.endsWith('.md')) continue;

      const methodName = file.replace('.md', '');
      const filePath = join(servicePath, file);
      const content = await readFile(filePath, 'utf-8');
      const { title, description, deprecated } = extractMetadata(content);

      methods.push({
        name: methodName,
        title: title || kebabToTitle(methodName),
        ...(description && { description }),
        ...(deprecated && { deprecated }),
      });
    }

    if (methods.length > 0) {
      services.push({
        name: serviceName,
        title: kebabToTitle(serviceName),
        methods,
      });
    }
  }

  return {
    language,
    generatedAt: new Date().toISOString(),
    services,
  };
}

/**
 * Generate TypeScript type definitions from the manifest
 */
function generateTypeDefinitions(manifest: Manifest): string {
  const languages = Object.keys(manifest);

  if (languages.length === 0) {
    return `/**
 * Auto-generated type definitions for type-safe SDK usage.
 * Generated at: ${new Date().toISOString()}
 */

/** No languages available - manifest is empty */
export type Language = never;

/** Method paths mapped by language */
export type MethodPaths = Record<string, never>;

/** Service names mapped by language */
export type ServiceNames = Record<string, never>;
`;
  }

  // Generate Language union type
  const languageUnion = languages.map((l) => `'${l}'`).join(' | ');

  // Generate MethodPaths type - maps each language to its valid paths
  const methodPathsEntries = languages.map((lang) => {
    const toc = manifest[lang];
    const paths: string[] = [];
    for (const service of toc.services) {
      for (const method of service.methods) {
        paths.push(`${service.name}/${method.name}`);
      }
    }
    if (paths.length === 0) {
      return `  ${lang}: never;`;
    }
    const pathUnion = paths.map((p) => `'${p}'`).join(' | ');
    return `  ${lang}: ${pathUnion};`;
  });

  // Generate ServiceNames type - maps each language to its valid service names
  const serviceNamesEntries = languages.map((lang) => {
    const toc = manifest[lang];
    const services = toc.services.map((s) => s.name);
    if (services.length === 0) {
      return `  ${lang}: never;`;
    }
    const serviceUnion = services.map((s) => `'${s}'`).join(' | ');
    return `  ${lang}: ${serviceUnion};`;
  });

  return `/**
 * Auto-generated type definitions for type-safe SDK usage.
 * This file is generated by scripts/build-manifest.ts
 *
 * Generated at: ${new Date().toISOString()}
 */

/** Available SDK languages */
export type Language = ${languageUnion};

/** Method paths mapped by language */
export interface MethodPaths {
${methodPathsEntries.join('\n')}
}

/** Service names mapped by language */
export interface ServiceNames {
${serviceNamesEntries.join('\n')}
}
`;
}

/**
 * Main build function
 */
async function build(): Promise<void> {
  console.log('Building manifest from markdown files...\n');

  const manifest: Manifest = {};

  // Check if docs directory exists
  let docsExists = false;
  try {
    await stat(DOCS_DIR);
    docsExists = true;
  } catch {
    console.log(`No docs directory found at ${DOCS_DIR}`);
    console.log('Creating empty manifest...\n');
  }

  if (docsExists) {
    // Read all language directories
    const languages = await readdir(DOCS_DIR);

    for (const language of languages) {
      const languagePath = join(DOCS_DIR, language);

      if (!(await isDirectory(languagePath))) continue;

      console.log(`Processing language: ${language}`);
      manifest[language] = await buildLanguageManifest(language, languagePath);

      const methodCount = manifest[language].services.reduce(
        (sum, s) => sum + s.methods.length,
        0
      );
      console.log(
        `  → ${manifest[language].services.length} services, ${methodCount} methods\n`
      );
    }
  }

  // Generate the manifest.ts file
  const output = `import type { Manifest } from './types.js';

/**
 * Pre-generated manifest containing table of contents for all languages.
 * This is populated at build time by scripts/build-manifest.ts
 *
 * Generated at: ${new Date().toISOString()}
 */
export const manifest: Manifest = ${JSON.stringify(manifest, null, 2)};
`;

  await writeFile(MANIFEST_OUTPUT, output, 'utf-8');
  console.log(`Manifest written to ${MANIFEST_OUTPUT}`);

  // Generate and write type definitions
  const typesOutput = generateTypeDefinitions(manifest);
  await writeFile(GENERATED_TYPES_OUTPUT, typesOutput, 'utf-8');
  console.log(`Type definitions written to ${GENERATED_TYPES_OUTPUT}`);

  // Print summary
  const totalLanguages = Object.keys(manifest).length;
  const totalServices = Object.values(manifest).reduce(
    (sum, lang) => sum + lang.services.length,
    0
  );
  const totalMethods = Object.values(manifest).reduce(
    (sum, lang) =>
      sum + lang.services.reduce((s, svc) => s + svc.methods.length, 0),
    0
  );

  console.log('\nSummary:');
  console.log(`  Languages: ${totalLanguages}`);
  console.log(`  Services: ${totalServices}`);
  console.log(`  Methods: ${totalMethods}`);
}

build().catch((error) => {
  console.error('Build failed:', error);
  process.exit(1);
});
