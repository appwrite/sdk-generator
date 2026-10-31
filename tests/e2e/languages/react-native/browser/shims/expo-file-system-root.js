import { EncodingType } from "./expo-file-system.js";

const removed = (name) => async () => {
  throw new Error(
    `Method ${name} imported from "expo-file-system" is deprecated.`,
  );
};

export { EncodingType };
export const cacheDirectory = "";
export const getInfoAsync = removed("getInfoAsync");
export const readAsStringAsync = removed("readAsStringAsync");
export const writeAsStringAsync = removed("writeAsStringAsync");
export const deleteAsync = removed("deleteAsync");

export default {
  EncodingType,
  cacheDirectory,
  getInfoAsync,
  readAsStringAsync,
  writeAsStringAsync,
  deleteAsync,
};
