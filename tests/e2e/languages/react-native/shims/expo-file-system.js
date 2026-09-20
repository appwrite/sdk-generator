// Serves files over HTTP from the test server so the SDK's upload code can
// read them the way it reads a device file in Expo.
const written = new Map();

const bytesOf = async (uri) => {
  if (written.has(uri)) {
    return written.get(uri);
  }
  const response = await fetch(uri);
  return new Uint8Array(await response.arrayBuffer());
};

const toBase64 = (bytes) => {
  let binary = "";
  for (let index = 0; index < bytes.length; index += 0x8000) {
    binary += String.fromCharCode(...bytes.subarray(index, index + 0x8000));
  }
  return btoa(binary);
};

export const EncodingType = { UTF8: "utf8", Base64: "base64" };
export const documentDirectory = "";
export const cacheDirectory = "";

export const getInfoAsync = async (uri) => {
  const bytes = await bytesOf(uri);
  return { exists: true, size: bytes.length, uri };
};

export const readAsStringAsync = async (uri, options = {}) => {
  const bytes = await bytesOf(uri);
  const position = options.position ?? 0;
  const length = options.length ?? bytes.length - position;
  const slice = bytes.subarray(position, position + length);
  return options.encoding === EncodingType.Base64
    ? toBase64(slice)
    : new TextDecoder().decode(slice);
};

export const writeAsStringAsync = async (uri, data, options = {}) => {
  const bytes =
    options.encoding === EncodingType.Base64
      ? Uint8Array.from(atob(data), (character) => character.charCodeAt(0))
      : new TextEncoder().encode(data);
  written.set(uri, bytes);
};

export const deleteAsync = async (uri) => {
  written.delete(uri);
};

export default {
  EncodingType,
  documentDirectory,
  cacheDirectory,
  getInfoAsync,
  readAsStringAsync,
  writeAsStringAsync,
  deleteAsync,
};
