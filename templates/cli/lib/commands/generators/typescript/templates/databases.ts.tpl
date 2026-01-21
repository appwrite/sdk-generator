import { Client, TablesDB, ID, Query, type Models, Permission } from '{{appwriteDep}}';
import type { DatabaseId, DatabaseTables, QueryBuilder } from './types.js';

{{QUERY_BUILDER_IMPL}};

{{TABLE_ID_MAP}};

{{TABLES_WITH_RELATIONSHIPS}};

function createTableApi<T extends Models.Row>(
  tablesDB: TablesDB,
  databaseId: string,
  tableId: string,
) {
  return {
    create: (data: any, options?: { rowId?: string; permissions?: Permission[]; transactionId?: string }) =>
      tablesDB.createRow<T>({
        databaseId,
        tableId,
        rowId: options?.rowId ?? ID.unique(),
        data,
        permissions: options?.permissions?.map((p) => p.toString()),
        transactionId: options?.transactionId,
      }),
    get: (id: string) =>
      tablesDB.getRow<T>({
        databaseId,
        tableId,
        rowId: id,
      }),
    update: (id: string, data: any, options?: { permissions?: Permission[]; transactionId?: string }) =>
      tablesDB.updateRow<T>({
        databaseId,
        tableId,
        rowId: id,
        data,
        ...(options?.permissions ? { permissions: options.permissions.map((p) => p.toString()) } : {}),
        transactionId: options?.transactionId,
      }),
    delete: async (id: string, options?: { transactionId?: string }) => {
      await tablesDB.deleteRow({
        databaseId,
        tableId,
        rowId: id,
        transactionId: options?.transactionId,
      });
    },
    list: (options?: { queries?: (q: any) => string[] }) =>
      tablesDB.listRows<T>({
        databaseId,
        tableId,
        queries: options?.queries?.(createQueryBuilder<T>()),
      }),{{BULK_METHODS}}
  };
}

{{BULK_CHECK}}

const hasOwn = (obj: unknown, key: string): boolean =>
  obj != null && Object.prototype.hasOwnProperty.call(obj, key);

function createDatabaseProxy<D extends DatabaseId>(
  tablesDB: TablesDB,
  databaseId: D,
): DatabaseTables[D] {
  const tableApiCache = new Map<string, ReturnType<typeof createTableApi>>();
  const dbMap = tableIdMap[databaseId];

  return new Proxy({} as DatabaseTables[D], {
    get(_target, tableName: string) {
      if (typeof tableName === 'symbol') return undefined;

      if (!tableApiCache.has(tableName)) {
        if (!hasOwn(dbMap, tableName)) return undefined;
        const tableId = dbMap[tableName];

        const api = createTableApi(tablesDB, databaseId, tableId);
        {{BULK_REMOVAL}}
        tableApiCache.set(tableName, api);
      }
      return tableApiCache.get(tableName);
    },
    has(_target, tableName: string) {
      return typeof tableName === 'string' && hasOwn(dbMap, tableName);
    },
  });
}

export const createDatabases = (client: Client) => {
  const tablesDB = new TablesDB(client);
  const dbCache = new Map<DatabaseId, DatabaseTables[DatabaseId]>();

  return {
    from: <T extends DatabaseId>(databaseId: T): DatabaseTables[T] => {
      if (!dbCache.has(databaseId)) {
        dbCache.set(databaseId, createDatabaseProxy(tablesDB, databaseId));
      }
      return dbCache.get(databaseId) as DatabaseTables[T];
    },
  };
};
