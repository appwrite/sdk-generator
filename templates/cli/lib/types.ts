import type { File } from "undici";
import type { ReadableStream } from "node:stream/web";

export type ResponseType = "json" | "arraybuffer";

export interface Headers {
  [key: string]: string;
}

export interface RequestParams {
  [key: string]: unknown;
}

export interface FileUpload {
  type: "file";
  file: File;
  filename: string;
}

export interface FileInput {
  type: "file";
  stream: ReadableStream;
  filename: string;
  size: number;
}

export interface UploadProgress {
  $id: string;
  progress: number;
  sizeUploaded: number;
  chunksTotal: number;
  chunksUploaded: number;
}

export interface ConfigData {
  [key: string]: unknown;
}

export interface Entity {
  $id: string;
  [key: string]: unknown;
}

export interface ParsedData {
  [key: string]: unknown;
}

export interface CommandDescription {
  [key: string]: string;
}

export interface CliConfig {
  verbose: boolean;
  json: boolean;
  force: boolean;
  all: boolean;
  ids: string[];
  report: boolean;
  reportData: Record<string, unknown>;
}

export interface SessionData {
  endpoint: string;
  email?: string;
  phone?: string;
  cookie?: string;
}

export interface GlobalConfigData extends ConfigData {
  sessions: {
    [key: string]: SessionData;
  };
  current: string;
  cookie?: string;
}
