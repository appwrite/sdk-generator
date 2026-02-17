import JSONbigModule from "json-bigint";

const JSONbigParser = JSONbigModule({ storeAsString: false });
const JSONbigSerializer = JSONbigModule({ useNativeBigInt: true });

const MAX_SAFE = BigInt(Number.MAX_SAFE_INTEGER);
const MIN_SAFE = BigInt(Number.MIN_SAFE_INTEGER);

function isBigNumber(value: any): boolean {
  return value !== null
    && typeof value === 'object'
    && value._isBigNumber === true
    && typeof value.isInteger === 'function'
    && typeof value.toFixed === 'function'
    && typeof value.toNumber === 'function';
}

function reviver(_key: string, value: any): any {
  if (isBigNumber(value)) {
    if (value.isInteger()) {
      const str = value.toFixed();
      const bi = BigInt(str);
      if (bi >= MIN_SAFE && bi <= MAX_SAFE) {
        return Number(str);
      }
      return bi;
    }
    return value.toNumber();
  }
  return value;
}

export const JSONBig = {
  parse: (text: string) => JSONbigParser.parse(text, reviver),
  stringify: JSONbigSerializer.stringify,
};
