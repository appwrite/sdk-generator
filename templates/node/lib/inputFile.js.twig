const { Readable } = require('stream');
const fs = require('fs');
const { promisify } = require('util');

class InputFile {
  stream; // Content of file, readable stream
  size; // Total final size of the file content
  filename; // File name

  static fromPath = (filePath, filename) => {
    const stream = fs.createReadStream(filePath);
    const { size } = fs.statSync(filePath);
    return new InputFile(stream, filename, size);
  };

  static fromBuffer = (buffer, filename) => {
    const stream = Readable.from(buffer.toString());
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, filename, size);
  };

  static fromBlob = (blob, filename) => {
    const buffer = blob.arrayBuffer();
    const stream = Readable.from(buffer.toString());
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, filename);
  };

  static fromStream = (stream, filename, size) => {
    return new InputFile(stream, filename, size);
  };

  static fromPlainText = (content, filename) => {
    const buffer = Buffer.from(content, "utf-8");
    const stream = Readable.from(buffer.toString());
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, filename, size);
  };

  constructor(stream, filename, size) {
    this.stream = stream;
    this.filename = filename;
    this.size = size;
  }
}

module.exports = InputFile;
