import { readFile } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { manifest } from './manifest.js';
import type {
  TableOfContents,
  SearchResult,
  SearchOptions,
  SDKOptions,
} from './types.js';
import type { Language, MethodPaths, ServiceNames } from './generated-types.js';

export type {
  TableOfContents,
  SearchResult,
  SearchOptions,
  SDKOptions,
  Manifest,
  MethodEntry,
  ServiceEntry,
} from './types.js';

export type { Language, MethodPaths, ServiceNames } from './generated-types.js';

const __dirname = dirname(fileURLToPath(import.meta.url));

/**
 * SDK for accessing documentation programmatically.
 * Designed for AI consumption with lazy-loading and search capabilities.
 */
export class DocsSDK {
  private docsPath: string;
  private contentCache: Map<string, string> = new Map();

  constructor(options: SDKOptions = {}) {
    this.docsPath = options.docsPath ?? join(__dirname, '..', 'docs');
  }

  /**
   * Get the list of available SDK languages
   */
  getLanguages(): Language[] {
    return Object.keys(manifest) as Language[];
  }

  /**
   * Get the table of contents for a specific language.
   * This is lightweight and doesn't load any markdown content.
   *
   * @param language - SDK language (e.g., "typescript")
   * @param options - Optional filter options
   * @param options.service - Filter to a specific service
   * @param options.skipDeprecated - Exclude deprecated methods from results
   * @returns Table of contents with services and methods
   *
   * @example
   * ```ts
   * const toc = sdk.getTableOfContents("typescript");
   * // → { language: "typescript", services: [{ name: "account", methods: [...] }] }
   *
   * const accountToc = sdk.getTableOfContents("typescript", { service: "account" });
   * // → { language: "typescript", services: [{ name: "account", methods: [...] }] }
   *
   * const nonDeprecated = sdk.getTableOfContents("typescript", { skipDeprecated: true });
   * // → excludes deprecated methods
   * ```
   */
  getTableOfContents<L extends Language>(
    language: L,
    options?: { service?: ServiceNames[L]; skipDeprecated?: boolean }
  ): TableOfContents | null {
    const toc = manifest[language];
    if (!toc) return null;

    const { service, skipDeprecated } = options ?? {};

    let services = toc.services;

    if (service) {
      const filteredService = services.find((s) => s.name === service);
      if (!filteredService) return null;
      services = [filteredService];
    }

    if (skipDeprecated) {
      services = services.map((s) => ({
        ...s,
        methods: s.methods.filter((m) => !m.deprecated),
      }));
    }

    return {
      ...toc,
      services,
    };
  }

  /**
   * Get a specific markdown document by path.
   * Content is loaded lazily and cached for subsequent requests.
   *
   * @param language - SDK language (e.g., "typescript")
   * @param path - Document path (e.g., "account/create-session")
   * @returns Markdown content or null if not found
   *
   * @example
   * ```ts
   * const content = await sdk.getMarkdown("typescript", "account/create-session");
   * // → "# Create Session\n\nDescription: ..."
   * ```
   */
  async getMarkdown<L extends Language>(
    language: L,
    path: MethodPaths[L]
  ): Promise<string | null> {
    const cacheKey = `${language}/${path}`;

    if (this.contentCache.has(cacheKey)) {
      return this.contentCache.get(cacheKey)!;
    }

    try {
      const filePath = join(this.docsPath, language, `${path}.md`);
      const content = await readFile(filePath, 'utf-8');
      this.contentCache.set(cacheKey, content);
      return content;
    } catch {
      return null;
    }
  }

  /**
   * Get all markdown documents for a service.
   *
   * @param language - SDK language (e.g., "typescript")
   * @param service - Service name (e.g., "account")
   * @returns Map of method names to markdown content
   *
   * @example
   * ```ts
   * const docs = await sdk.getServiceDocs("typescript", "account");
   * // → Map { "create-session" => "# Create Session\n...", ... }
   * ```
   */
  async getServiceDocs<L extends Language>(
    language: L,
    service: ServiceNames[L]
  ): Promise<Map<string, string>> {
    const toc = this.getTableOfContents(language);
    const result = new Map<string, string>();

    if (!toc) return result;

    const serviceEntry = toc.services.find((s) => s.name === service);
    if (!serviceEntry) return result;

    await Promise.all(
      serviceEntry.methods.map(async (method) => {
        const path = `${service}/${method.name}` as MethodPaths[L];
        const content = await this.getMarkdown(language, path);
        if (content) {
          result.set(method.name, content);
        }
      })
    );

    return result;
  }

  /**
   * Search documentation by keywords.
   * Performs a simple text-based search across all documents.
   *
   * @param language - SDK language (e.g., "typescript")
   * @param query - Search query
   * @param options - Search options
   * @returns Array of search results sorted by relevance
   *
   * @example
   * ```ts
   * const results = await sdk.searchDocs("typescript", "MFA authentication");
   * // → [{ path: "account/create-mfa-...", title: "...", snippet: "..." }]
   * ```
   */
  async searchDocs<L extends Language>(
    language: L,
    query: string,
    options: SearchOptions = {}
  ): Promise<SearchResult[]> {
    const { limit = 10, minScore = 0.1, services: filterServices } = options;
    const toc = this.getTableOfContents(language);

    if (!toc) return [];

    const results: SearchResult[] = [];
    const queryTerms = query.toLowerCase().split(/\s+/).filter(Boolean);

    const servicesToSearch = filterServices
      ? toc.services.filter((s) => filterServices.includes(s.name))
      : toc.services;

    await Promise.all(
      servicesToSearch.flatMap((service) =>
        service.methods.map(async (method) => {
          const path = `${service.name}/${method.name}` as MethodPaths[L];
          const content = await this.getMarkdown(language, path);

          if (!content) return;

          const score = this.calculateScore(content, queryTerms, method.title);

          if (score >= minScore) {
            results.push({
              path,
              title: method.title,
              snippet: this.extractSnippet(content, queryTerms),
              service: service.name,
              method: method.name,
              score,
            });
          }
        })
      )
    );

    return results.sort((a, b) => b.score - a.score).slice(0, limit);
  }

  /**
   * Calculate relevance score for a document
   */
  private calculateScore(
    content: string,
    queryTerms: string[],
    title: string
  ): number {
    const lowerContent = content.toLowerCase();
    const lowerTitle = title.toLowerCase();

    let score = 0;
    let matchedTerms = 0;

    for (const term of queryTerms) {
      // Title matches are weighted higher
      if (lowerTitle.includes(term)) {
        score += 0.4;
        matchedTerms++;
      }

      // Count content occurrences
      const regex = new RegExp(term, 'gi');
      const matches = lowerContent.match(regex);
      if (matches) {
        // Diminishing returns for multiple matches
        score += Math.min(matches.length * 0.1, 0.3);
        matchedTerms++;
      }
    }

    // Bonus for matching all terms
    if (matchedTerms === queryTerms.length * 2) {
      score += 0.2;
    }

    return Math.min(score, 1);
  }

  /**
   * Extract a relevant snippet containing query terms
   */
  private extractSnippet(content: string, queryTerms: string[]): string {
    const lines = content.split('\n').filter((line) => line.trim());
    const snippetLength = 150;

    // Find the first line containing any query term
    for (const line of lines) {
      const lowerLine = line.toLowerCase();
      if (queryTerms.some((term) => lowerLine.includes(term))) {
        // Clean markdown formatting
        const cleaned = line
          .replace(/^#+\s*/, '')
          .replace(/\*\*/g, '')
          .replace(/`/g, '')
          .trim();

        if (cleaned.length <= snippetLength) {
          return cleaned;
        }

        // Find the position of the first matching term
        const termPositions = queryTerms
          .map((term) => lowerLine.indexOf(term))
          .filter((pos) => pos !== -1);
        const firstMatch = Math.min(...termPositions);

        const start = Math.max(0, firstMatch - 50);
        const end = Math.min(cleaned.length, start + snippetLength);

        let snippet = cleaned.slice(start, end);
        if (start > 0) snippet = '...' + snippet;
        if (end < cleaned.length) snippet = snippet + '...';

        return snippet;
      }
    }

    // Fallback: return the first meaningful content line
    const descriptionLine = lines.find(
      (line) =>
        !line.startsWith('#') && !line.startsWith('|') && line.length > 20
    );

    if (descriptionLine) {
      const cleaned = descriptionLine.replace(/\*\*/g, '').replace(/`/g, '');
      return cleaned.length > snippetLength
        ? cleaned.slice(0, snippetLength) + '...'
        : cleaned;
    }

    return '';
  }

  /**
   * Clear the content cache to free memory
   */
  clearCache(): void {
    this.contentCache.clear();
  }

  /**
   * Get cache statistics
   */
  getCacheStats(): { size: number; entries: string[] } {
    return {
      size: this.contentCache.size,
      entries: Array.from(this.contentCache.keys()),
    };
  }
}

// Convenience functions for direct usage without instantiation
let defaultInstance: DocsSDK | null = null;

function getDefaultInstance(): DocsSDK {
  if (!defaultInstance) {
    defaultInstance = new DocsSDK();
  }
  return defaultInstance;
}

/**
 * Get the table of contents for a specific language
 */
export function getTableOfContents<L extends Language>(
  language: L,
  options?: { service?: ServiceNames[L]; skipDeprecated?: boolean }
): TableOfContents | null {
  return getDefaultInstance().getTableOfContents(language, options);
}

/**
 * Get a specific markdown document
 */
export function getMarkdown<L extends Language>(
  language: L,
  path: MethodPaths[L]
): Promise<string | null> {
  return getDefaultInstance().getMarkdown(language, path);
}

/**
 * Search documentation by keywords
 */
export function searchDocs<L extends Language>(
  language: L,
  query: string,
  options?: SearchOptions
): Promise<SearchResult[]> {
  return getDefaultInstance().searchDocs(language, query, options);
}

/**
 * Get list of available languages
 */
export function getLanguages(): Language[] {
  return getDefaultInstance().getLanguages();
}

// Default export
export default DocsSDK;
