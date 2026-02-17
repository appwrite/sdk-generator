/**
 * Represents a single method/endpoint documentation entry
 */
export interface MethodEntry {
  /** Method name in kebab-case (e.g., "create-session") */
  name: string;
  /** Human-readable title (e.g., "Create Session") */
  title: string;
  /** Brief description of the method */
  description?: string;
  /** Whether this method is deprecated */
  deprecated?: boolean;
}

/**
 * Represents a service containing multiple methods
 */
export interface ServiceEntry {
  /** Service name in lowercase (e.g., "account") */
  name: string;
  /** Human-readable service title (e.g., "Account") */
  title: string;
  /** Brief description of the service */
  description?: string;
  /** List of methods in this service */
  methods: MethodEntry[];
}

/**
 * Table of contents for a specific SDK language
 */
export interface TableOfContents {
  /** SDK language identifier (e.g., "typescript") */
  language: string;
  /** Version of the SDK documentation */
  version?: string;
  /** Timestamp when the manifest was generated */
  generatedAt: string;
  /** List of services with their methods */
  services: ServiceEntry[];
}

/**
 * Search result for a documentation query
 */
export interface SearchResult {
  /** Full path to the document (e.g., "account/create-session") */
  path: string;
  /** Document title */
  title: string;
  /** Matching text snippet */
  snippet: string;
  /** Service name */
  service: string;
  /** Method name */
  method: string;
  /** Relevance score (higher is better) */
  score: number;
}

/**
 * Options for search queries
 */
export interface SearchOptions {
  /** Maximum number of results to return */
  limit?: number;
  /** Minimum relevance score threshold (0-1) */
  minScore?: number;
  /** Filter to specific services */
  services?: string[];
}

/**
 * Complete manifest structure stored in manifest.json
 */
export interface Manifest {
  [language: string]: TableOfContents;
}

/**
 * Options for initializing the SDK
 */
export interface SDKOptions {
  /** Custom path to the docs directory */
  docsPath?: string;
}
