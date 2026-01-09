interface PaginateArgs {
  [key: string]: any;
}

// Overload for when wrapper is empty string - returns array
export function paginate<T = any>(
  action: (args: PaginateArgs) => Promise<any>,
  args: PaginateArgs,
  limit: number,
  wrapper: "",
  queries?: string[],
): Promise<T[]>;

// Overload for when wrapper is specified - returns object with that key
export function paginate<T = any, K extends string = string>(
  action: (args: PaginateArgs) => Promise<any>,
  args: PaginateArgs,
  limit: number,
  wrapper: K,
  queries?: string[],
): Promise<Record<K, T[]> & { total: number }>;

// Implementation
export async function paginate<T = any>(
  action: (args: PaginateArgs) => Promise<any>,
  args: PaginateArgs = {},
  limit: number = 100,
  wrapper: string = "",
  queries: string[] = [],
): Promise<T[] | (Record<string, T[]> & { total: number })> {
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
      if (response.length === 0) {
        break;
      }
      results = results.concat(response);
    } else {
      if (response[wrapper].length === 0) {
        break;
      }
      results = results.concat(response[wrapper]);
    }

    total = response.total;

    if (results.length >= total) {
      break;
    }

    pageNumber++;
  }

  if (wrapper === "") {
    return results;
  }

  return {
    [wrapper]: results,
    total,
  } as Record<string, T[]> & { total: number };
}
