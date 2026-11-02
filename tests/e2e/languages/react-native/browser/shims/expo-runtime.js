// Reproduces the part of Expo's native runtime that uploads go through since
// Expo SDK 57 made expo/fetch the global fetch: React Native's FormData, Expo's
// FormData patch, and Expo's multipart body encoder. Only the transport is the
// browser's fetch, sending the bytes Expo would hand to its native request.
//
// Expo ships these as TypeScript sources only, so the test build transpiles
// them out of node_modules with esbuild before bundling.
import { installFormDataPatch } from "./expo/winter/FormData";
import {
  normalizeBodyInitAsync,
  normalizeHeadersInit,
  overrideHeaders,
} from "./expo/winter/fetch/RequestUtils";

class ReactNativeFormData {
  constructor() {
    this._parts = [];
  }

  append(key, value) {
    this._parts.push([key, value]);
  }
}

globalThis.FormData = ReactNativeFormData;
installFormDataPatch(globalThis.FormData);

const browserFetch = globalThis.fetch.bind(globalThis);

globalThis.fetch = async (input, init = {}) => {
  if (!(init.body instanceof globalThis.FormData)) {
    return browserFetch(input, init);
  }
  const { body, overriddenHeaders } = await normalizeBodyInitAsync(init.body);
  const headers = overrideHeaders(
    normalizeHeadersInit(init.headers),
    overriddenHeaders ?? [],
  );
  return browserFetch(input, { ...init, headers, body });
};
