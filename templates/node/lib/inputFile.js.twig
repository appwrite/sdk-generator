const { Readable } = require('stream');
const fs = require('fs');
const { promisify } = require('util');

class InputFile {
  stream; // Content of file, readable stream
  size; // Total final size of the file content
  name; // File name

  static fromPath = (filePath, name) => {
    const stream = fs.createReadStream(filePath);
    const { size } = fs.statSync(filePath);
    return new InputFile(stream, name, size);
  };

  static fromBuffer = (buffer, name) => {
    const stream = Readable.from(buffer.toString());
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, name, size);
  };

  static fromBlob = (blob, name) => {
    const buffer = blob.arrayBuffer();
    const stream = Readable.from(buffer.toString());
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, name);
  };

  static fromStream = (stream, name, size) => {
    return new InputFile(stream, name, size);
  };

  static fromPlainText = (content, name) => {
    const buffer = Buffer.from(content, "utf-8");
    const stream = Readable.from(buffer.toString());
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, name, size);
  };

  constructor(stream, name, size) {
    this.stream = stream;
    this.name = name;
    this.size = size;
  }
}

module.exports = InputFile;
