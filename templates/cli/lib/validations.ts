export const VARIABLE_KEY_MAX_LENGTH = 255;

const VARIABLE_KEY_PATTERN = /^[A-Za-z_][A-Za-z0-9_]*$/;

/**
 * Variable keys become environment variable names at build and runtime, so the
 * API only accepts C-style identifiers. Checking locally keeps a bad key in a
 * .env file from being reported as a server error halfway through a push.
 */
export const validateVariableKey = (key: string): string | true => {
  if (key.length > VARIABLE_KEY_MAX_LENGTH) {
    return `Variable key "${key}" is longer than ${VARIABLE_KEY_MAX_LENGTH} allowed characters`;
  }

  if (!VARIABLE_KEY_PATTERN.test(key)) {
    return `Variable key "${key}" is invalid, keys must contain only letters, digits and underscores and must not start with a digit`;
  }

  return true;
};

export const validateVariableKeys = (keys: string[]): string[] =>
  keys
    .map((key) => validateVariableKey(key))
    .filter((result): result is string => result !== true);

export const validateRequired = (
  resource: string,
  value: unknown,
): string | true => {
  if (Array.isArray(value)) {
    if (value.length <= 0) {
      return `Please select at least one ${resource}`;
    }
  } else {
    if (
      value === undefined ||
      value === null ||
      (typeof value === "string" && value.trim() === "")
    ) {
      return `${resource} is required`;
    }
  }

  return true;
};
