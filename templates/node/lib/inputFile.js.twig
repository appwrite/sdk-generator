const fs = require("fs");
const { ReadableStream } = require("stream/web");

/**
 * @param {fs.ReadStream} readStream
 * @returns {ReadableStream}
 */
function convertReadStreamToReadableStream(readStream) {
  return new ReadableStream({
    start(controller) {
      readStream.on("data", (chunk) => {
        controller.enqueue(chunk);
      });
      readStream.on("end", () => {
        controller.close();
      });
      readStream.on("error", (err) => {
        controller.error(err);
      });
    },
    cancel() {
      readStream.destroy();
    },
  });
}

/**
 * @param {Buffer} buffer
 * @returns {ReadableStream}
 */
function bufferToReadableStream(buffer) {
  return new ReadableStream({
    start(controller) {
      controller.enqueue(buffer);
      controller.close();
    },
  });
}

class InputFile {
  /** @type {ReadableStream} Content of file as a stream */
  stream;

  /** @type {number} Total final size of the file content */
  size;

  /** @type {string} File name */
  filename;

  /**
   * @param {string} filePath
   * @param {string} filename
   * @returns {InputFile}
   */
  static fromPath = (filePath, filename) => {
    const nodeStream = fs.createReadStream(filePath);
    const stream = convertReadStreamToReadableStream(nodeStream);
    const size = fs.statSync(filePath).size;
    return new InputFile(stream, filename, size);
  };

  /**
   * @param {Buffer} buffer
   * @param {string} filename
   * @returns {InputFile}
   */
  static fromBuffer = (buffer, filename) => {
    const stream = bufferToReadableStream(buffer);
    const size = buffer.byteLength;
    return new InputFile(stream, filename, size);
  };

  /**
   * @param {string} content
   * @param {string} filename
   * @returns {InputFile}
   */
  static fromPlainText = (content, filename) => {
    const array = new TextEncoder().encode(content);
    const buffer = Buffer.from(array);
    return InputFile.fromBuffer(buffer, filename);
  };

  /**
   * @param {ReadableStream} stream
   * @param {string} filename
   * @param {number} size
   * @returns {InputFile}
   */
  static fromStream = (stream, filename, size) => {
    return new InputFile(stream, filename, size);
  };

  /**
   * @param {Blob} blob
   * @param {string} filename
   * @returns {InputFile}
   */
  static fromBlob = (blob, filename) => {
    const stream = blob.stream();
    const size = blob.size;
    return new InputFile(stream, filename, size);
  };

  /**
   * @param {ReadableStream} stream
   * @param {string} filename
   * @param {number} size
   */
  constructor(stream, filename, size) {
    this.stream = stream;
    this.filename = filename;
    this.size = size;
  }
}

module.exports = InputFile;
