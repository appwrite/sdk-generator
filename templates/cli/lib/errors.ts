/** Longest server-provided snippet we print on one line. */
const MAX_SNIPPET_LENGTH = 300;

/** Snippet length used when embedding a body in a bug report URL. */
export const MAX_REPORT_BODY_LENGTH = 500;

export type HttpFailure = {
  message: string;
  type: string;
};

/** Whether a response is an HTML page rather than an API response. */
export const looksLikeHtml = (body: string, contentType?: string): boolean => {
  if (contentType?.toLowerCase().includes("text/html")) {
    return true;
  }

  const start = body.trimStart().toLowerCase();
  return start.startsWith("<!doctype html") || start.startsWith("<html");
};

/** Collapse untrusted server text into a printable, bounded line. */
export const sanitizeErrorText = (
  text: string,
  maxLength: number = MAX_SNIPPET_LENGTH,
): string => {
  const collapsed = text
    .replace(/\u001b\[[0-9;]*[a-zA-Z]/g, "")
    .replace(/[\u0000-\u001f\u007f]+/g, " ")
    .replace(/\s+/g, " ")
    .trim();

  if (collapsed.length <= maxLength) {
    return collapsed;
  }

  return `${collapsed.slice(0, maxLength).trimEnd()}\u2026`;
};

const statusLabel = (status: number, statusText?: string): string => {
  const text = statusText?.trim();
  return text ? `HTTP ${status} ${text}` : `HTTP ${status}`;
};

/** Describe a failed non-JSON response without exposing an HTML document. */
export const describeHttpFailure = (
  status: number,
  body: string,
  statusText?: string,
  contentType?: string,
): HttpFailure => {
  const label = statusLabel(status, statusText);
  const message = looksLikeHtml(body, contentType)
    ? ""
    : sanitizeErrorText(body);

  return {
    message: message ? `${message} (${label})` : label,
    type: "",
  };
};

const formatBytes = (bytes: number): string =>
  bytes < 1024 ? `${bytes} bytes` : `${(bytes / 1024).toFixed(1)} KB`;

/** Render a response body for verbose output without flooding the terminal. */
export const summarizeErrorBody = (body: string): string => {
  const html = looksLikeHtml(body);
  const summary = html ? "" : sanitizeErrorText(body);

  if (!html && body.length <= MAX_SNIPPET_LENGTH) {
    return summary;
  }

  const kind = html ? "HTML body" : "body";
  return [`<${kind}, ${formatBytes(Buffer.byteLength(body))}>`, summary]
    .filter(Boolean)
    .join(" ");
};
