import { File } from "node-fetch-native-with-agent";
import { realpathSync, readFileSync } from "fs";
import type { BinaryLike } from "crypto";

export class InputFile {
  static fromBuffer(
    parts: Blob | BinaryLike,
    name: string
  ): File {
    return new File([parts], name);
  }

  static fromPath(path: string, name: string): File {
    const realPath = realpathSync(path);
    const contents = readFileSync(realPath);
    return this.fromBuffer(contents, name);
  }

  static fromPlainText(content: string, name: string): File {
    const arrayBytes = new TextEncoder().encode(content);
    return this.fromBuffer(arrayBytes, name);
  }
}
