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
      list: (options?: { queries?: (q: { equal: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; notEqual: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; lessThan: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; lessThanEqual: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; greaterThan: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; greaterThanEqual: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; contains: <K extends QueryableKeys<Authors>>(field: K, value: QueryableFieldValue<Authors, K>) => string; search: <K extends QueryableKeys<Authors>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Authors>>(field: K) => string; isNotNull: <K extends QueryableKeys<Authors>>(field: K) => string; startsWith: <K extends QueryableKeys<Authors>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Authors>>(field: K, value: string) => string; between: <K extends QueryableKeys<Authors>>(field: K, start: QueryableFieldValue<Authors, K>, end: QueryableFieldValue<Authors, K>) => string; select: <K extends keyof Authors>(fields: K[]) => string; orderAsc: <K extends keyof Authors>(field: K) => string; orderDesc: <K extends keyof Authors>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[] }) => Promise<{ total: number; rows: Authors[] }>;
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
      list: (options?: { queries?: (q: { equal: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; notEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; lessThan: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; lessThanEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; greaterThan: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; greaterThanEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; contains: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; search: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<BooksZines>>(field: K) => string; isNotNull: <K extends QueryableKeys<BooksZines>>(field: K) => string; startsWith: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; between: <K extends QueryableKeys<BooksZines>>(field: K, start: QueryableFieldValue<BooksZines, K>, end: QueryableFieldValue<BooksZines, K>) => string; select: <K extends keyof BooksZines>(fields: K[]) => string; orderAsc: <K extends keyof BooksZines>(field: K) => string; orderDesc: <K extends keyof BooksZines>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[] }) => Promise<{ total: number; rows: BooksZines[] }>;
      createMany: (rows: Array<{
        "title": string;
        "pages"?: number | null;
      } & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: BooksZines[] }>;
      updateMany: (data: Partial<{
        "title": string;
        "pages"?: number | null;
      }>, options?: { queries?: (q: { equal: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; notEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; lessThan: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; lessThanEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; greaterThan: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; greaterThanEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; contains: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; search: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<BooksZines>>(field: K) => string; isNotNull: <K extends QueryableKeys<BooksZines>>(field: K) => string; startsWith: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; between: <K extends QueryableKeys<BooksZines>>(field: K, start: QueryableFieldValue<BooksZines, K>, end: QueryableFieldValue<BooksZines, K>) => string; select: <K extends keyof BooksZines>(fields: K[]) => string; orderAsc: <K extends keyof BooksZines>(field: K) => string; orderDesc: <K extends keyof BooksZines>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]; transactionId?: string }) => Promise<{ total: number; rows: BooksZines[] }>;
      deleteMany: (options?: { queries?: (q: { equal: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; notEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; lessThan: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; lessThanEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; greaterThan: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; greaterThanEqual: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; contains: <K extends QueryableKeys<BooksZines>>(field: K, value: QueryableFieldValue<BooksZines, K>) => string; search: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<BooksZines>>(field: K) => string; isNotNull: <K extends QueryableKeys<BooksZines>>(field: K) => string; startsWith: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<BooksZines>>(field: K, value: string) => string; between: <K extends QueryableKeys<BooksZines>>(field: K, start: QueryableFieldValue<BooksZines, K>, end: QueryableFieldValue<BooksZines, K>) => string; select: <K extends keyof BooksZines>(fields: K[]) => string; orderAsc: <K extends keyof BooksZines>(field: K) => string; orderDesc: <K extends keyof BooksZines>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]; transactionId?: string }) => Promise<{ total: number; rows: BooksZines[] }>;
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
      list: (options?: { queries?: (q: { equal: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; notEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; lessThan: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; lessThanEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; greaterThan: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; greaterThanEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; contains: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; search: <K extends QueryableKeys<Events>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Events>>(field: K) => string; isNotNull: <K extends QueryableKeys<Events>>(field: K) => string; startsWith: <K extends QueryableKeys<Events>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Events>>(field: K, value: string) => string; between: <K extends QueryableKeys<Events>>(field: K, start: QueryableFieldValue<Events, K>, end: QueryableFieldValue<Events, K>) => string; select: <K extends keyof Events>(fields: K[]) => string; orderAsc: <K extends keyof Events>(field: K) => string; orderDesc: <K extends keyof Events>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[] }) => Promise<{ total: number; rows: Events[] }>;
      createMany: (rows: Array<{
        "name": string;
        "at": string;
      } & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: Events[] }>;
      updateMany: (data: Partial<{
        "name": string;
        "at": string;
      }>, options?: { queries?: (q: { equal: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; notEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; lessThan: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; lessThanEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; greaterThan: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; greaterThanEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; contains: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; search: <K extends QueryableKeys<Events>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Events>>(field: K) => string; isNotNull: <K extends QueryableKeys<Events>>(field: K) => string; startsWith: <K extends QueryableKeys<Events>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Events>>(field: K, value: string) => string; between: <K extends QueryableKeys<Events>>(field: K, start: QueryableFieldValue<Events, K>, end: QueryableFieldValue<Events, K>) => string; select: <K extends keyof Events>(fields: K[]) => string; orderAsc: <K extends keyof Events>(field: K) => string; orderDesc: <K extends keyof Events>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Events[] }>;
      deleteMany: (options?: { queries?: (q: { equal: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; notEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; lessThan: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; lessThanEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; greaterThan: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; greaterThanEqual: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; contains: <K extends QueryableKeys<Events>>(field: K, value: QueryableFieldValue<Events, K>) => string; search: <K extends QueryableKeys<Events>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Events>>(field: K) => string; isNotNull: <K extends QueryableKeys<Events>>(field: K) => string; startsWith: <K extends QueryableKeys<Events>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Events>>(field: K, value: string) => string; between: <K extends QueryableKeys<Events>>(field: K, start: QueryableFieldValue<Events, K>, end: QueryableFieldValue<Events, K>) => string; select: <K extends keyof Events>(fields: K[]) => string; orderAsc: <K extends keyof Events>(field: K) => string; orderDesc: <K extends keyof Events>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Events[] }>;
    };
    "Empty": {
      create: (data: Record<string, never>, options?: { rowId?: string; permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Empty>;
      get: (id: string) => Promise<Empty>;
      update: (id: string, data: Partial<Record<string, never>>, options?: { permissions?: (permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]; transactionId?: string }) => Promise<Empty>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: { equal: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; notEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; lessThan: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; lessThanEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; greaterThan: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; greaterThanEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; contains: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; search: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Empty>>(field: K) => string; isNotNull: <K extends QueryableKeys<Empty>>(field: K) => string; startsWith: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; between: <K extends QueryableKeys<Empty>>(field: K, start: QueryableFieldValue<Empty, K>, end: QueryableFieldValue<Empty, K>) => string; select: <K extends keyof Empty>(fields: K[]) => string; orderAsc: <K extends keyof Empty>(field: K) => string; orderDesc: <K extends keyof Empty>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[] }) => Promise<{ total: number; rows: Empty[] }>;
      createMany: (rows: Array<Record<string, never> & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: Empty[] }>;
      updateMany: (data: Partial<Record<string, never>>, options?: { queries?: (q: { equal: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; notEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; lessThan: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; lessThanEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; greaterThan: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; greaterThanEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; contains: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; search: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Empty>>(field: K) => string; isNotNull: <K extends QueryableKeys<Empty>>(field: K) => string; startsWith: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; between: <K extends QueryableKeys<Empty>>(field: K, start: QueryableFieldValue<Empty, K>, end: QueryableFieldValue<Empty, K>) => string; select: <K extends keyof Empty>(fields: K[]) => string; orderAsc: <K extends keyof Empty>(field: K) => string; orderDesc: <K extends keyof Empty>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Empty[] }>;
      deleteMany: (options?: { queries?: (q: { equal: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; notEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; lessThan: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; lessThanEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; greaterThan: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; greaterThanEqual: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; contains: <K extends QueryableKeys<Empty>>(field: K, value: QueryableFieldValue<Empty, K>) => string; search: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<Empty>>(field: K) => string; isNotNull: <K extends QueryableKeys<Empty>>(field: K) => string; startsWith: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<Empty>>(field: K, value: string) => string; between: <K extends QueryableKeys<Empty>>(field: K, start: QueryableFieldValue<Empty, K>, end: QueryableFieldValue<Empty, K>) => string; select: <K extends keyof Empty>(fields: K[]) => string; orderAsc: <K extends keyof Empty>(field: K) => string; orderDesc: <K extends keyof Empty>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]; transactionId?: string }) => Promise<{ total: number; rows: Empty[] }>;
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
