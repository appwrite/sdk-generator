import JSONbigModule from "json-bigint";
import { BigNumber } from "bignumber.js";

const JSONbigParser = JSONbigModule({ storeAsString: false });
const JSONbigSerializer = JSONbigModule({ useNativeBigInt: true });

const MAX_SAFE = BigInt(Number.MAX_SAFE_INTEGER);
const MIN_SAFE = BigInt(Number.MIN_SAFE_INTEGER);

function reviver(_key: string, value: any): any {
  if (BigNumber.isBigNumber(value)) {
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
