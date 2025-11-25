const _bufferToString = (buffer: Uint8Array): ReadableStream<Uint8Array> => {
    return new ReadableStream({
        start(controller) {
            controller.enqueue(buffer);
            controller.close();
        }
    });
};

export class InputFile {
    stream: ReadableStream<Uint8Array>; // Content of file as a stream
    size: number; // Total final size of the file content
    filename: string; // File name

    static fromPath = (filePath: string, filename: string): InputFile => {
        const file = Deno.openSync(filePath);
        const stream = file.readable;
        const size = Deno.statSync(filePath).size;
        return new InputFile(stream, filename, size);
    };

    static fromBlob = async (blob: Blob, filename: string) => {
        const arrayBuffer = await blob.arrayBuffer();
        const buffer = new Uint8Array(arrayBuffer);
        return InputFile.fromBuffer(buffer, filename);
    };

    static fromBuffer = (buffer: Uint8Array, filename: string): InputFile => {
        const stream = _bufferToString(buffer);
        const size = buffer.byteLength;
        return new InputFile(stream, filename, size);
    };

    static fromPlainText = (content: string, filename: string): InputFile => {
        const buffer = new TextEncoder().encode(content);
        const stream = _bufferToString(buffer);
        const size = buffer.byteLength;
        return new InputFile(stream, filename, size);
    };

    constructor(stream: ReadableStream<Uint8Array>, filename: string, size: number) {
        this.stream = stream;
        this.filename = filename;
        this.size = size;
    }
}