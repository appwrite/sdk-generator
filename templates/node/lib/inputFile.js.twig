const { Readable } = require('stream');
const fs = require('fs');

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
    const stream = Readable.from(buffer);
    const size = Buffer.byteLength(buffer);
    return new InputFile(stream, filename, size);
  };

  static fromBlob = async (blob, filename) => {
    const arrayBuffer = await blob.arrayBuffer();
    const buffer = Buffer.from(arrayBuffer);
    return InputFile.fromBuffer(buffer, filename);
  };

  static fromStream = (stream, filename, size) => {
    return new InputFile(stream, filename, size);
  };

  static fromPlainText = (content, filename) => {
    const buffer = Buffer.from(content, "utf-8");
    const stream = Readable.from(buffer);
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
