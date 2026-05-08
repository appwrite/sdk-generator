type PaginateArgs = Record<string, unknown>;

type WrappedPaginateResponse<T, K extends string = string> = Record<K, T[]> & {
  total: number;
};

// Overload for when wrapper is empty string - returns array
export function paginate<T = unknown>(
  action: (args: PaginateArgs) => Promise<T[]>,
  args: PaginateArgs,
  limit: number,
  wrapper: "",
  queries?: string[],
): Promise<T[]>;

// Overload for when wrapper is specified - returns object with that key
export function paginate<T = unknown, K extends string = string>(
  action: (args: PaginateArgs) => Promise<WrappedPaginateResponse<T, K>>,
  args: PaginateArgs,
  limit: number,
  wrapper: K,
  queries?: string[],
): Promise<WrappedPaginateResponse<T, K>>;

// Implementation
export async function paginate<T = unknown>(
  action: (
    args: PaginateArgs,
  ) => Promise<T[] | WrappedPaginateResponse<T, string>>,
  args: PaginateArgs = {},
  limit: number = 100,
  wrapper: string = "",
  queries: string[] = [],
): Promise<T[] | WrappedPaginateResponse<T, string>> {
  let pageNumber = 0;
  let results: T[] = [];
  let total = 0;

  while (true) {
    const offset = pageNumber * limit;

    // Merge the limit and offset into the args
    const response = await action({
      ...args,
      queries: [
        ...queries,
        JSON.stringify({ method: "limit", values: [limit] }),
        JSON.stringify({ method: "offset", values: [offset] }),
      ],
    });

    if (wrapper === "") {
      const listResponse = response as T[];

      if (listResponse.length === 0) {
        break;
      }

      results = results.concat(listResponse);
    } else {
      const wrappedResponse = response as WrappedPaginateResponse<T, string>;
      const wrappedResults = wrappedResponse[wrapper] ?? [];

      if (wrappedResults.length === 0) {
        break;
      }

      results = results.concat(wrappedResults);
      total = wrappedResponse.total;

      if (results.length >= total) {
        break;
      }
    }

    pageNumber++;
  }

  if (wrapper === "") {
    return results;
  }

  return {
    [wrapper]: results,
    total,
  } as WrappedPaginateResponse<T, string>;
}
