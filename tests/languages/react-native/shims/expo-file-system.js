const notImplemented = (name) => () => {
    throw new Error(
        `expo-file-system shim: ${name} was called, but uploads are not exercised by this test harness.`
    );
};

export const EncodingType = { UTF8: 'utf8', Base64: 'base64' };
export const documentDirectory = '';
export const cacheDirectory = '';

export const getInfoAsync = notImplemented('getInfoAsync');
export const readAsStringAsync = notImplemented('readAsStringAsync');
export const writeAsStringAsync = notImplemented('writeAsStringAsync');
export const deleteAsync = notImplemented('deleteAsync');
export const uploadAsync = notImplemented('uploadAsync');
export const FileSystemUploadType = { BINARY_CONTENT: 0, MULTIPART: 1 };

export default {
    EncodingType,
    documentDirectory,
    cacheDirectory,
    getInfoAsync,
    readAsStringAsync,
    writeAsStringAsync,
    deleteAsync,
    uploadAsync,
    FileSystemUploadType,
};
