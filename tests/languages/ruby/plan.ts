type Params = { [key: string]: any };

type xFormDataValue = {
  headers: Record<string, string>;
  body: string | Blob;
};

const extensionToContentType: Record<string, string> = {
  txt: "text/plain",
  html: "text/html",
  json: "application/json",
  xml: "application/xml",
  pdf: "application/pdf",
  zip: "application/zip",
  tar: "application/x-tar",
  rar: "application/vnd.rar",
  gz: "application/gzip",
  bz: "application/x-bzip",
  bz2: "application/x-bzip2",
  "7z": "application/x-7z-compressed",
  xz: "application/x-xz",
  lz: "application/x-lzip",
  lzma: "application/x-lzma",
  zst: "application/zstd",
  png: "image/png",
  jpg: "image/jpeg",
  jpeg: "image/jpeg",
  gif: "image/gif",
  avif: "image/avif",
  webp: "image/webp",
  svg: "image/svg+xml",
  mp4: "video/mp4",
  webm: "video/webm",
  ogg: "video/ogg",
  mp3: "audio/mp3",
  wav: "audio/wav",
  flac: "audio/flac",
  aac: "audio/aac",
  oga: "audio/ogg",
  m4a: "audio/mp4",
  doc: "application/msword",
  docx: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  xls: "application/vnd.ms-excel",
  xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  ppt: "application/vnd.ms-powerpoint",
  pptx: "application/vnd.openxmlformats-officedocument.presentationml.presentation",
  csv: "text/csv",
};

class xFormData {
  values: Map<string, xFormDataValue>;

  constructor(values: Map<string, xFormDataValue> = new Map()) {
    this.values = values;
  }

  get(name: string): xFormDataValue | undefined {
    return this.values.get(name);
  }

  set(name: string, value: string | Blob, filename?: string): void {
    if (value instanceof Blob) {
      this.setFile(name, value, filename);
    }

    this.values.set(name, {
      headers: {
        "Content-Disposition": `form-data; name="${name}"`,
      },
      body: value,
    });
  }

  toFormData(): FormData {
    const formData = new FormData();
    for (const [name, value] of this.values.entries()) {
      if (value.body instanceof Blob) {
        const filename = xFormData.parseValueFilename(value);
        formData.append(name, value.body, filename);
      } else {
        formData.append(name, value.body);
      }
    }

    return formData;
  }

  private setFile(name: string, file: Blob, filename?: string): void {
    const headers = {
      "Content-Disposition": `form-data; name="${name}"; ${
        filename ? `filename="${filename}"` : ""
      }`,
    };

    if (filename) {
      const extension = filename.split(".").pop();
      if (extension) {
        const contentType = extensionToContentType[extension];
        if (contentType) headers["Content-Type"] = contentType;
      }
    }

    this.values.set(name, { headers, body: file });
  }

  static fromMultipart(body: string, boundary?: string): xFormData {
    if (!boundary) {
      const boundaryMatch = body.match(/boundary=(?:"([^"]+)"|([^;]+))/);
      if (!boundaryMatch) {
        throw new Error("No boundary found in Content-Type");
      }
      boundary = boundaryMatch[1] || boundaryMatch[2];
    }

    const rawParts = body
      .split(new RegExp(`--${boundary}(?:--)?\\r?\\n`))
      .slice(1, -1);

    const parsedParts: Map<string, xFormDataValue> = new Map();
    for (const rawPart of rawParts) {
      const value = this.parsePart(rawPart);
      const name = this.parseValueName(value);

      parsedParts.set(name, value);
    }

    return new xFormData(parsedParts);
  }

  private static parsePart(rawPart: string): xFormDataValue {
    const [rawHeaders, body] = rawPart.split(/\r?\n\r?\n/);

    const headers: Record<string, string> = {};
    for (const header of rawHeaders.split(/\r?\n/)) {
      const [key, value] = header.split(": ");
      headers[key.toLowerCase()] = value;
    }

    return { headers, body };
  }

  toMultipartString(boundary?: string): string {
    if (!boundary) {
      boundary = `----${Math.random().toString(36).slice(2)}`;
    }

    return (
      Array.from(this.values.values())
        .map((value) => xFormData.valueToMultipartString(value, boundary!))
        .join("") + `--${boundary}--\r\n`
    );
  }

  async toObject<T>(): Promise<T> {
    const obj = {} as T;

    for (const [name, value] of this.values.entries()) {
      // TODO: This is hacky. We need a way to differentiate between files and strings here.
      // Some kind of schema parameter is neccessary.
      if (name === "body") {
        value.body = new Blob([value.body]);
      }

      if (value.body instanceof Blob) {
        const body = await value.body.arrayBuffer();
        obj[name] = Payload.fromBinary(new Uint8Array(body), name);
      } else {
        obj[name] = value.body;
      }
    }

    return obj;
  }

  private static parseValueName(part: xFormDataValue): string {
    const disposition = part.headers["content-disposition"];
    if (!disposition) {
      throw new Error("Part is missing Content-Disposition header");
    }

    const nameMatch = disposition.match(/name="([^"]+)"/);
    if (!nameMatch) {
      throw new Error("Content-Disposition header is missing name");
    }

    return nameMatch[1];
  }

  private static parseValueFilename(part: xFormDataValue): string | undefined {
    const disposition = part.headers["content-disposition"];
    if (!disposition) {
      throw new Error("Part is missing Content-Disposition header");
    }

    const filenameMatch = disposition.match(/filename="([^"]+)"/);
    return filenameMatch ? filenameMatch[1] : undefined;
  }

  private static valueToMultipartString(
    value: xFormDataValue,
    boundary: string
  ): string {
    const headers = Object.entries(value.headers)
      .map(([key, value]) => `${key}: ${value}`)
      .join("\r\n");
    return `--${boundary}\r\n${headers}\r\n\r\n${value.body}\r\n`;
  }
}

class Payload extends Blob {
  name?: string;

  constructor(parts: BlobPart[], name?: string) {
    super(parts);
    this.name = name;
  }

  static fromFile(file: File): Payload {
    return new Payload([file], file.name);
  }

  static fromString(string: string, name?: string): Payload {
    return new Payload([string], name);
  }

  static fromJson(json: unknown, name?: string): Payload {
    return new Payload([JSON.stringify(json)], name);
  }

  static fromBinary(binary: Uint8Array, name?: string): Payload {
    return new Payload([binary], name);
  }

  toFile(): File {
    return new File([this], this.name || "file");
  }

  async toString(): Promise<string> {
    return new TextDecoder().decode(await this.arrayBuffer());
  }

  async toJson<T = unknown>(): Promise<T> {
    return JSON.parse(await this.toString());
  }

  async toBinary(): Promise<Uint8Array> {
    return new Uint8Array(await this.arrayBuffer());
  }
}

class Client {
  CHUNK_SIZE = 1024 * 1024 * 5;

  prepareUrl = (
    method: string,
    url: string,
    params?: Record<string, string | string[] | Payload>
  ): string => {
    if (method === "GET" && params) {
      const queryString = new URLSearchParams(
        params as Record<string, string>
      ).toString();
      url += `?${queryString}`;
    }

    return url;
  };

  async call<T = unknown>(
    method: string,
    url: string,
    headers: Record<string, string>,
    params?: Record<string, string | string[] | Payload>
  ): Promise<T> {
    url = this.prepareUrl(method, url, params);

    let body: xFormData | undefined = undefined;
    if (method === "POST" && params) {
      let largestSize = 0;
      body = new xFormData();
      for (const [key, value] of Object.entries(params)) {
        if (value instanceof Payload) {
          body.set(key, value, value.name);
          largestSize = Math.max(largestSize, value.size);
        } else {
          body.set(key, value as string);
        }
      }

      if (largestSize > this.CHUNK_SIZE) {
        return await this.callChunked<T>(url, method, headers, body);
      }
    }

    const response = await fetch(url, {
      method,
      headers,
      body: body ? body.toFormData() : undefined,
    });

    return await this.parseResponse<T>(response);
  }

  private async callChunked<T = unknown>(
    url: string,
    method: string,
    headers: Record<string, string>,
    body: xFormData,
    onProgress?: (progress: number) => void
  ): Promise<T> {
    const [key, value] = Array.from(body.values.entries()).find(
      ([_, file]) => file.body instanceof Blob
    )!;

    const file = value.body as Blob;

    let start = 0;
    let response: T | undefined = undefined;

    while (start < file.size) {
      const end = Math.min(start + this.CHUNK_SIZE, file.size);
      headers["content-range"] = `bytes ${start}-${end - 1}/${file.size}`;

      const chunk = file.slice(start, end);
      body.set(key, chunk);

      const result = await fetch(url, {
        method,
        headers,
        body: body.toFormData(),
      });

      response = await this.parseResponse<T>(result);
      if (response?.["$id"]) {
        headers["x-appwrite-id"] = response["$id"];
      }

      onProgress?.(end / file.size);

      start = end;
    }

    return response!;
  }

  private async parseResponse<T>(response: Response): Promise<T> {
    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }

    if (
      response.headers.get("Content-Type")?.startsWith("multipart/form-data")
    ) {
      const boundary = response.headers
        .get("Content-Type")
        ?.split("boundary=")[1];
      const body = await response.text();
      const multipart = xFormData.fromMultipart(body, boundary);
      return multipart.toObject<T>();
    }

    return await response.json();
  }

  static flatten(data: Params, prefix = ""): Params {
    let output: Params = {};

    for (const [key, value] of Object.entries(data)) {
      let finalKey = prefix ? prefix + "[" + key + "]" : key;
      if (Array.isArray(value)) {
        output = { ...output, ...Client.flatten(value, finalKey) };
      } else {
        output[finalKey] = value;
      }
    }

    return output;
  }
}

type Resource = {
  $id: string;
  value: string;
};

type xFile = {
  $id: string;
  name: string;
  file: Payload;
};

class ResourceService {
  client: Client;

  constructor(client: Client) {
    this.client = client;
  }

  async getResource($id: string): Promise<Resource> {
    if (!$id) {
      throw new Error("$id is required");
    }

    return await this.client.call<Resource>("GET", `/v1/resource/${$id}`, {});
  }

  async createResource(
    $id: string,
    name: string,
    file: Payload
  ): Promise<{ $id: string }> {
    if (!$id) {
      throw new Error("$id is required");
    }

    //.. further validation

    return await this.client.call<Resource>(
      "POST",
      "/v1/resource",
      {},
      {
        $id,
        name,
        file,
      }
    );
  }

  async getFile($id: string): Promise<xFile> {
    return await this.client.call<xFile>("GET", `/v1/resource/file/${$id}`, {});
  }

  async createFile(
    $id: string,
    name: string,
    file: Payload
  ): Promise<{ $id: string }> {
    if (!$id) {
      throw new Error("$id is required");
    }

    //.. further validation

    return await this.client.call<{ $id: string }>(
      "POST",
      "/v1/resource/file",
      {},
      {
        $id,
        name,
        file,
      }
    );
  }
}
