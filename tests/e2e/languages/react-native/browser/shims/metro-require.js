import * as legacy from "./expo-file-system.js";
import * as root from "./expo-file-system-root.js";

const modules = new Map();

export const useExpoFileSystem = (major) => {
  modules.clear();
  if (major >= 19) {
    modules.set("expo-file-system/legacy", legacy);
    modules.set("expo-file-system", root);
  } else {
    modules.set("expo-file-system", legacy);
  }
};

useExpoFileSystem(58);

globalThis.require = (name) => {
  if (!modules.has(name)) {
    throw new Error(`Requiring unknown module "${name}".`);
  }
  return modules.get(name);
};
