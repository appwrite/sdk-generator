import JSONbigModule from "json-bigint";

const JSONbigParser = JSONbigModule({ storeAsString: false });
const JSONbigSerializer = JSONbigModule({ useNativeBigInt: true });

const MAX_SAFE = BigInt(Number.MAX_SAFE_INTEGER);
const MIN_SAFE = BigInt(Number.MIN_SAFE_INTEGER);
const MAX_INT64 = BigInt("9223372036854775807");
const MIN_INT64 = BigInt("-9223372036854775808");

interface BigNumberLike {
  _isBigNumber: boolean;
  isInteger: () => boolean;
  toFixed: () => string;
  toNumber: () => number;
}

function isBigNumber(value: unknown): value is BigNumberLike {
  return (
    value !== null &&
    typeof value === "object" &&
    (value as BigNumberLike)._isBigNumber === true &&
    typeof (value as BigNumberLike).isInteger === "function" &&
    typeof (value as BigNumberLike).toFixed === "function" &&
    typeof (value as BigNumberLike).toNumber === "function"
  );
}

function reviver(_key: string, value: unknown): unknown {
  if (isBigNumber(value)) {
    if (value.isInteger()) {
      const str = value.toFixed();
      const bi = BigInt(str);
      if (bi >= MIN_SAFE && bi <= MAX_SAFE) {
        return Number(str);
      }
      if (bi >= MIN_INT64 && bi <= MAX_INT64) {
        return bi;
      }
      return value.toNumber();
    }
    return value.toNumber();
  }
  return value;
}

export const JSONBig = {
  parse: <T = unknown>(text: string): T =>
    JSONbigParser.parse(text, reviver) as T,
  stringify: JSONbigSerializer.stringify,
};
