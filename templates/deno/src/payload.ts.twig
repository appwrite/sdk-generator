import { basename, dirname } from "https://deno.land/std@0.224.0/path/mod.ts";

export class Payload {
  private data: Uint8Array;
  public filename?: string;
  public size: number;

  constructor(data: Uint8Array, filename?: string) {
    this.data = data;
    this.filename = filename;
    this.size = data.byteLength;
  }

  public toBinary(offset: number = 0, length?: number): Uint8Array {
    if (offset === 0 && length === undefined) {
      return this.data;
    } else if (length === undefined) {
      return this.data.subarray(offset);
    } else {
      return this.data.subarray(offset, offset + length);
    }
  }

  public toJson<T = unknown>(): T {
    return JSON.parse(this.toString());
  }

  public toString(): string {
    return new TextDecoder().decode(this.data);
  }

  public async toFile(path: string): Promise<void> {
    await Deno.mkdir(dirname(path), { recursive: true });
    await Deno.writeFile(path, this.data);
  }

  public static fromBinary(bytes: Uint8Array, filename?: string): Payload {
    return new Payload(bytes, filename);
  }

  public static fromJson(object: any, filename?: string): Payload {
    const data = new TextEncoder().encode(JSON.stringify(object));
    return new Payload(data, filename);
  }

  public static fromString(text: string, filename?: string): Payload {
    const data = new TextEncoder().encode(text);
    return new Payload(data, filename);
  }

  public static async fromFile(path: string, filename?: string): Promise<Payload> {
    const data = await Deno.readFile(path);

    if(!filename) {
      filename = basename(path);
    }

    return new Payload(data, filename);
  }
}