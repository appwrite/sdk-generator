import { type Models } from 'node-appwrite';

export enum AuthorsStatus {
    ACTIVE = "active",
    IN_ACTIVE = "in-active",
    IN_ACTIVE_1 = "in active"
}
export type AuthorsCreate = {
    "full name": string;
    "bio"?: string | null;
    "age"?: number | null;
    "rating": number;
    "verified"?: boolean | null;
    "tags"?: string[] | null;
    "status": AuthorsStatus;
    "books"?: ((BooksZinesCreate & { $id?: string; $permissions?: string[] }) | string)[] | null;
}

export type Authors = Models.Row & {
    "full name": string;
    "bio"?: string | null;
    "age"?: number | null;
    "rating": number;
    "verified"?: boolean | null;
    "tags"?: string[] | null;
    "status": AuthorsStatus;
    "books"?: BooksZines[] | null;
}

export type BooksZinesCreate = {
    "title": string;
    "pages"?: number | null;
}

export type BooksZines = Models.Row & {
    "title": string;
    "pages"?: number | null;
}

export type EventsCreate = {
    "name": string;
    "at": string;
}

export type Events = Models.Row & {
    "name": string;
    "at": string;
}

export type EmptyCreate = Record<string, never>

export type Empty = Models.Row

declare const __roleStringBrand: unique symbol;
export type RoleString = string & { readonly [__roleStringBrand]: never };

export type RoleBuilder = {
  any: () => RoleString;
  user: (userId: string, status?: string) => RoleString;
  users: (status?: string) => RoleString;
  guests: () => RoleString;
  team: (teamId: string, role?: string) => RoleString;
  member: (memberId: string) => RoleString;
  label: (label: string) => RoleString;
}

export type PermissionBuilder = {
  read: (role: RoleString) => string;
  write: (role: RoleString) => string;
  create: (role: RoleString) => string;
  update: (role: RoleString) => string;
  delete: (role: RoleString) => string;
}

export type PermissionCallback = (permission: PermissionBuilder, role: RoleBuilder) => string[];

export type QueryValue = string | number | boolean;

export type ExtractQueryValue<T> = T extends (infer U)[]
  ? U extends QueryValue ? U : never
  : T extends QueryValue | null ? NonNullable<T> : never;

export type QueryableKeys<T> = {
  [K in keyof T]: ExtractQueryValue<T[K]> extends never ? never : K;
}[keyof T];

export type QueryableFieldValue<T, K> = K extends keyof T
  ? ExtractQueryValue<T[K]>
  : never;

export type QueryBuilder<T> = {
  equal: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  notEqual: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  lessThan: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  lessThanEqual: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  greaterThan: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  greaterThanEqual: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  contains: <K extends QueryableKeys<T>>(field: K, value: QueryableFieldValue<T, K>) => string;
  search: <K extends QueryableKeys<T>>(field: K, value: string) => string;
  isNull: <K extends QueryableKeys<T>>(field: K) => string;
  isNotNull: <K extends QueryableKeys<T>>(field: K) => string;
  startsWith: <K extends QueryableKeys<T>>(field: K, value: string) => string;
  endsWith: <K extends QueryableKeys<T>>(field: K, value: string) => string;
  between: <K extends QueryableKeys<T>>(field: K, start: QueryableFieldValue<T, K>, end: QueryableFieldValue<T, K>) => string;
  select: <K extends keyof T>(fields: K[]) => string;
  orderAsc: <K extends keyof T>(field: K) => string;
  orderDesc: <K extends keyof T>(field: K) => string;
  limit: (value: number) => string;
  offset: (value: number) => string;
  cursorAfter: (documentId: string) => string;
  cursorBefore: (documentId: string) => string;
  or: (...queries: string[]) => string;
  and: (...queries: string[]) => string;
  count: (attribute?: string, alias?: string) => string;
  countDistinct: (attribute: string, alias?: string) => string;
  sum: (attribute: string, alias?: string) => string;
  avg: (attribute: string, alias?: string) => string;
  min: (attribute: string, alias?: string) => string;
  max: (attribute: string, alias?: string) => string;
  stddev: (attribute: string, alias?: string) => string;
  stddevPop: (attribute: string, alias?: string) => string;
  stddevSamp: (attribute: string, alias?: string) => string;
  variance: (attribute: string, alias?: string) => string;
  varPop: (attribute: string, alias?: string) => string;
  varSamp: (attribute: string, alias?: string) => string;
  bitAnd: (attribute: string, alias?: string) => string;
  bitOr: (attribute: string, alias?: string) => string;
  bitXor: (attribute: string, alias?: string) => string;
  groupBy: (attributes: string[]) => string;
  having: (queries: string[]) => string;
  distinct: () => string;
  join: (table: string, left: string, right: string, operator?: string, alias?: string) => string;
  leftJoin: (table: string, left: string, right: string, operator?: string, alias?: string) => string;
  rightJoin: (table: string, left: string, right: string, operator?: string, alias?: string) => string;
  fullOuterJoin: (table: string, left: string, right: string, operator?: string, alias?: string) => string;
  crossJoin: (table: string, alias?: string) => string;
  vectorDot: (attribute: string, vector: number[]) => string;
  vectorCosine: (attribute: string, vector: number[]) => string;
  vectorEuclidean: (attribute: string, vector: number[]) => string;
  covers: (attribute: string, values: any[]) => string;
  notCovers: (attribute: string, values: any[]) => string;
  spatialEquals: (attribute: string, values: any[]) => string;
  notSpatialEquals: (attribute: string, values: any[]) => string;
}

export type DatabaseId = "main" | "analytics";

export type DatabaseTableMap = {
  "main": {
    "Authors": {
      create: (data: {
        "full name": string;
        "bio"?: string | null;
        "age"?: number | null;
        "rating": number;
        "verified"?: boolean | null;
        "tags"?: string[] | null;
        "status": AuthorsStatus;
        "books"?: ((BooksZinesCreate & { $id?: string; $permissions?: string[] }) | string)[] | null;
      }, options?: { rowId?: string; permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Authors>;
      get: (id: string) => Promise<Authors>;
      update: (id: string, data: Partial<{
        "full name": string;
        "bio"?: string | null;
        "age"?: number | null;
        "rating": number;
        "verified"?: boolean | null;
        "tags"?: string[] | null;
        "status": AuthorsStatus;
        "books"?: ((BooksZinesCreate & { $id?: string; $permissions?: string[] }) | string)[] | null;
      }>, options?: { permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Authors>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<Authors>) => string[] }) => Promise<{ total: number; rows: Authors[] }>;
    };
    "Books & Zines": {
      create: (data: {
        "title": string;
        "pages"?: number | null;
      }, options?: { rowId?: string; permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<BooksZines>;
      get: (id: string) => Promise<BooksZines>;
      update: (id: string, data: Partial<{
        "title": string;
        "pages"?: number | null;
      }>, options?: { permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<BooksZines>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<BooksZines>) => string[] }) => Promise<{ total: number; rows: BooksZines[] }>;
      createMany: (rows: Array<{
        "title": string;
        "pages"?: number | null;
      } & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: BooksZines[] }>;
      updateMany: (data: Partial<{
        "title": string;
        "pages"?: number | null;
      }>, options?: { queries?: (q: QueryBuilder<BooksZines>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: BooksZines[] }>;
      deleteMany: (options?: { queries?: (q: QueryBuilder<BooksZines>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: BooksZines[] }>;
    }
  };
  "analytics": {
    "Events": {
      create: (data: {
        "name": string;
        "at": string;
      }, options?: { rowId?: string; permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Events>;
      get: (id: string) => Promise<Events>;
      update: (id: string, data: Partial<{
        "name": string;
        "at": string;
      }>, options?: { permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Events>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<Events>) => string[] }) => Promise<{ total: number; rows: Events[] }>;
      createMany: (rows: Array<{
        "name": string;
        "at": string;
      } & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: Events[] }>;
      updateMany: (data: Partial<{
        "name": string;
        "at": string;
      }>, options?: { queries?: (q: QueryBuilder<Events>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Events[] }>;
      deleteMany: (options?: { queries?: (q: QueryBuilder<Events>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Events[] }>;
    };
    "Empty": {
      create: (data: Record<string, never>, options?: { rowId?: string; permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Empty>;
      get: (id: string) => Promise<Empty>;
      update: (id: string, data: Partial<Record<string, never>>, options?: { permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Empty>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<Empty>) => string[] }) => Promise<{ total: number; rows: Empty[] }>;
      createMany: (rows: Array<Record<string, never> & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: Empty[] }>;
      updateMany: (data: Partial<Record<string, never>>, options?: { queries?: (q: QueryBuilder<Empty>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Empty[] }>;
      deleteMany: (options?: { queries?: (q: QueryBuilder<Empty>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Empty[] }>;
    }
  }
};

export type DatabaseHandle<D extends DatabaseId> = {
  use: <T extends keyof DatabaseTableMap[D] & string>(tableId: T) => DatabaseTableMap[D][T];
  create: (tableId: string, name: string, options?: { permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; rowSecurity?: boolean; enabled?: boolean; columns?: object[]; indexes?: object[] }) => Promise<Models.Table>;
  update: <T extends keyof DatabaseTableMap[D] & string>(tableId: T, options?: { name?: string; permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; rowSecurity?: boolean; enabled?: boolean }) => Promise<Models.Table>;
  delete: <T extends keyof DatabaseTableMap[D] & string>(tableId: T) => Promise<void>;
};

export type DatabaseTables = {
  use: <D extends DatabaseId>(databaseId: D) => DatabaseHandle<D>;
  create: (databaseId: string, name: string, options?: { enabled?: boolean }) => Promise<Models.Database>;
  update: <D extends DatabaseId>(databaseId: D, options?: { name?: string; enabled?: boolean }) => Promise<Models.Database>;
  delete: <D extends DatabaseId>(databaseId: D) => Promise<void>;
};
