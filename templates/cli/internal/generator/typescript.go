package generator

import (
	"fmt"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/typegen"
)

// Four files come out: types.ts, databases.ts, index.ts and constants.ts. Each
// is one of the embedded .hbs templates with large pre-rendered strings
// substituted in -- the templates hold the fixed scaffolding, this file builds
// the parts that depend on the user's schema.

// SDKTitle and ExecutableName are baked into the generated headers.
//
// Constants rather than values threaded through, because the SDK generator
// fills them in from the same params at generation time.
const (
	SDKTitle       = "Appwrite"
	SDKDirectory   = "appwrite"
	ExecutableName = "appwrite"
)

// noEntitiesNotice is emitted in place of both generated files when the config
// declares no tables and no collections.
const noEntitiesNotice = "// No tables or collections found in configuration\n"

// permissionCallbackInline is the permission-callback type, inlined rather than
// referenced so editors show the full shape on hover.
const permissionCallbackInline = `(permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]`

// queryCallbackInline is the same idea for the query callback, parameterised by
// the row type so field names are checked.
func queryCallbackInline(typeName string) string {
	return `(q: QueryBuilder<` + typeName + `>) => string[]`
}

// TypeScript generates a typed SDK for a TypeScript project.
type TypeScript struct {
	// ServerSide overrides bulk-method emission: "auto", "true" or "false".
	ServerSide string
}

// Language implements Generator.
func (g *TypeScript) Language() Language { return LanguageTypeScript }

// quote renders a JSON string literal the way JSON.stringify does -- no HTML
// escaping, so a table name containing `&` is not rewritten.
func quote(value string) string {
	encoded, err := jsonx.Marshal(value)
	if err != nil {
		// Marshalling a string cannot fail; a quoted fallback keeps the
		// generated file syntactically intact if it somehow does.
		return `"` + value + `"`
	}

	return string(encoded)
}

// databaseGroup is one database and the entities declared under it.
type databaseGroup struct {
	id       string
	entities []Entity
}

// groupByDatabase groups entities by databaseId, first-seen order.
//
// Go maps do not iterate in insertion order, so the order is carried
// explicitly -- otherwise the generated DatabaseTableMap would be shuffled on
// every run and every regeneration would produce a diff.
func groupByDatabase(entities []Entity) []databaseGroup {
	var groups []databaseGroup
	index := map[string]int{}

	for _, entity := range entities {
		position, seen := index[entity.DatabaseID]
		if !seen {
			index[entity.DatabaseID] = len(groups)
			groups = append(groups, databaseGroup{id: entity.DatabaseID})
			position = len(groups) - 1
		}
		groups[position].entities = append(groups[position].entities, entity)
	}

	return groups
}

// dedupeEntities drops duplicates keyed on databaseId and $id.
//
// Matches Map.set: a repeated key keeps its ORIGINAL position and takes the
// LAST value. Rebuilding the list in last-seen order would reorder the output
// of any config that has a duplicate.
func dedupeEntities(entities []Entity) []Entity {
	deduped := make([]Entity, 0, len(entities))
	index := map[string]int{}

	for _, entity := range entities {
		key := entity.DatabaseID + ":" + entity.ID
		if position, seen := index[key]; seen {
			deduped[position] = entity

			continue
		}
		index[key] = len(deduped)
		deduped = append(deduped, entity)
	}

	return deduped
}

// asCollections adapts entities for typegen's relationship resolution, which
// needs only the id and name.
func asCollections(entities []Entity) []typegen.Collection {
	collections := make([]typegen.Collection, 0, len(entities))
	for _, entity := range entities {
		collections = append(collections, typegen.Collection{ID: entity.ID, Name: entity.Name})
	}

	return collections
}

// sameDatabase returns the entities sharing an entity's databaseId.
//
// Relationships are resolved within a database, so an entity in another
// database is not a candidate -- two databases may hold same-named tables.
func sameDatabase(entities []Entity, databaseID string) []typegen.Collection {
	var scoped []Entity
	for _, entity := range entities {
		if entity.DatabaseID == databaseID {
			scoped = append(scoped, entity)
		}
	}

	return asCollections(scoped)
}

// buildAttributes renders one property per field.
//
// Optionality here is `required` alone -- a `?` on the property rather than the
// `| null` union that typegen's `types` output uses.
func buildAttributes(entity Entity, scope []typegen.Collection, indent string, forCreate bool) (string, error) {
	fields := entity.Fields()
	lines := make([]string, 0, len(fields))
	meta := typegen.TypeScript{}

	for _, field := range fields {
		var (
			literal string
			err     error
		)
		if forCreate {
			literal, err = meta.CreateType(field, scope, entity.Name)
		} else {
			literal, err = meta.Type(field, scope, entity.Name)
		}
		if err != nil {
			return "", err
		}

		optional := "?"
		if field.Required {
			optional = ""
		}

		lines = append(lines, fmt.Sprintf("%s%s%s: %s;", indent, quote(field.Key), optional, literal))
	}

	return strings.Join(lines, "\n"), nil
}

// generateEnums renders every enum declared across the entities.
func generateEnums(entities []Entity) string {
	meta := typegen.TypeScript{}
	var declarations []string

	for _, entity := range entities {
		for _, field := range entity.Fields() {
			if field.Format != typegen.AttributeTypeEnum || len(field.Elements) == 0 {
				continue
			}

			definition := meta.Enum(entity.Name, field.Key, field.Elements)
			values := make([]string, 0, len(definition.Members))
			for index, member := range definition.Members {
				separator := ","
				if index == len(definition.Members)-1 {
					separator = ""
				}
				values = append(values,
					fmt.Sprintf("    %s = %s%s", member.Key, quote(member.Value), separator))
			}

			declarations = append(declarations,
				fmt.Sprintf("export enum %s {\n%s\n}", definition.Name, strings.Join(values, "\n")))
		}
	}

	return strings.Join(declarations, "\n\n")
}

// generateEntityType renders the Create and Row types for one entity.
func generateEntityType(entity Entity, entities []Entity) (string, error) {
	if entity.Fields() == nil {
		return "", nil
	}

	typeName := typegen.ToPascalCase(entity.Name)
	scope := sameDatabase(entities, entity.DatabaseID)

	create, err := buildAttributes(entity, scope, "    ", true)
	if err != nil {
		return "", err
	}
	row, err := buildAttributes(entity, scope, "    ", false)
	if err != nil {
		return "", err
	}

	// An entity with no fields becomes Record<string, never> rather than an
	// empty object type, which TypeScript treats as "any non-null value".
	createType := fmt.Sprintf("export type %sCreate = {\n%s\n}", typeName, create)
	if strings.TrimSpace(create) == "" {
		createType = fmt.Sprintf("export type %sCreate = Record<string, never>", typeName)
	}

	rowType := fmt.Sprintf("export type %s = Models.Row & {\n%s\n}", typeName, row)
	if strings.TrimSpace(row) == "" {
		rowType = fmt.Sprintf("export type %s = Models.Row", typeName)
	}

	return createType + "\n\n" + rowType, nil
}

// generateDatabaseTablesType renders DatabaseTableMap and its two handles.
func (g *TypeScript) generateDatabaseTablesType(groups []databaseGroup, dependency string) (string, error) {
	serverSide := SupportsServerSideMethods(dependency, g.ServerSide)

	databases := make([]string, 0, len(groups))
	for _, group := range groups {
		scope := asCollections(group.entities)

		tables := make([]string, 0, len(group.entities))
		for _, entity := range group.entities {
			typeName := typegen.ToPascalCase(entity.Name)

			create, err := buildAttributes(entity, scope, "        ", true)
			if err != nil {
				return "", err
			}

			// Note the closing brace is indented six spaces while the fields
			// are indented eight: the inline object is spliced into an
			// already-indented position.
			createInline := "{\n" + create + "\n      }"
			if strings.TrimSpace(create) == "" {
				createInline = "Record<string, never>"
			}

			queries := queryCallbackInline(typeName)
			methods := fmt.Sprintf(`      create: (data: %s, options?: { rowId?: string; permissions?: %s; transactionId?: string }) => Promise<%s>;
      get: (id: string) => Promise<%s>;
      update: (id: string, data: Partial<%s>, options?: { permissions?: %s; transactionId?: string }) => Promise<%s>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: %s }) => Promise<{ total: number; rows: %s[] }>;`,
				createInline, permissionCallbackInline, typeName,
				typeName,
				createInline, permissionCallbackInline, typeName,
				queries, typeName)

			// Bulk methods are withheld from a table with relationships: the
			// API cannot apply them atomically across related rows.
			if serverSide && !entity.HasRelationship() {
				methods += fmt.Sprintf(`
      createMany: (rows: Array<%s & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: %s[] }>;
      updateMany: (data: Partial<%s>, options?: { queries?: %s; transactionId?: string }) => Promise<{ total: number; rows: %s[] }>;
      deleteMany: (options?: { queries?: %s; transactionId?: string }) => Promise<{ total: number; rows: %s[] }>;`,
					createInline, typeName,
					createInline, queries, typeName,
					queries, typeName)
			}

			tables = append(tables, fmt.Sprintf("    %s: {\n%s\n    }", quote(entity.Name), methods))
		}

		databases = append(databases,
			fmt.Sprintf("  %s: {\n%s\n  }", quote(group.id), strings.Join(tables, ";\n")))
	}

	handleMethods, tablesMethods := "", ""
	if serverSide {
		handleMethods = fmt.Sprintf(`  create: (tableId: string, name: string, options?: { permissions?: %s; rowSecurity?: boolean; enabled?: boolean; columns?: object[]; indexes?: object[] }) => Promise<Models.Table>;
  update: <T extends keyof DatabaseTableMap[D] & string>(tableId: T, options?: { name?: string; permissions?: %s; rowSecurity?: boolean; enabled?: boolean }) => Promise<Models.Table>;
  delete: <T extends keyof DatabaseTableMap[D] & string>(tableId: T) => Promise<void>;`,
			permissionCallbackInline, permissionCallbackInline)

		tablesMethods = `  create: (databaseId: string, name: string, options?: { enabled?: boolean }) => Promise<Models.Database>;
  update: <D extends DatabaseId>(databaseId: D, options?: { name?: string; enabled?: boolean }) => Promise<Models.Database>;
  delete: <D extends DatabaseId>(databaseId: D) => Promise<void>;`
	}

	return fmt.Sprintf(`export type DatabaseTableMap = {
%s
};

export type DatabaseHandle<D extends DatabaseId> = {
  use: <T extends keyof DatabaseTableMap[D] & string>(tableId: T) => DatabaseTableMap[D][T];
%s
};

export type DatabaseTables = {
  use: <D extends DatabaseId>(databaseId: D) => DatabaseHandle<D>;
%s
};`, strings.Join(databases, ";\n"), handleMethods, tablesMethods), nil
}

// generateTypesFile renders types.ts.
func (g *TypeScript) generateTypesFile(config Config, dependency string) (string, error) {
	entities := config.Entities()
	if len(entities) == 0 {
		return noEntitiesNotice, nil
	}

	declarations := make([]string, 0, len(entities))
	for _, entity := range entities {
		declaration, err := generateEntityType(entity, entities)
		if err != nil {
			return "", err
		}
		declarations = append(declarations, declaration)
	}

	groups := groupByDatabase(entities)
	identifiers := make([]string, 0, len(groups))
	for _, group := range groups {
		identifiers = append(identifiers, quote(group.id))
	}

	tablesType, err := g.generateDatabaseTablesType(groups, dependency)
	if err != nil {
		return "", err
	}

	enums := generateEnums(entities)
	if enums != "" {
		enums += "\n"
	}

	return typegen.RenderTemplate(typegen.TemplateTypes, typegen.Values{
		"appwriteDep":          dependency,
		"ENUMS":                enums,
		"TYPES":                strings.Join(declarations, "\n\n") + "\n",
		"databaseIdType":       strings.Join(identifiers, " | "),
		"DATABASE_TABLES_TYPE": tablesType,
	})
}

// generateTableIDMap renders the name-to-id lookup the runtime uses.
//
// Object.create(null) rather than {}: a table named "constructor" or
// "__proto__" would otherwise collide with an inherited property.
func generateTableIDMap(groups []databaseGroup) string {
	lines := []string{
		"const tableIdMap: Record<string, Record<string, string>> = Object.create(null);",
	}

	for _, group := range groups {
		lines = append(lines, fmt.Sprintf("tableIdMap[%s] = Object.create(null);", quote(group.id)))
		for _, entity := range group.entities {
			lines = append(lines, fmt.Sprintf("tableIdMap[%s][%s] = %s;",
				quote(group.id), quote(entity.Name), quote(entity.ID)))
		}
	}

	return strings.Join(lines, "\n")
}

// generateTablesWithRelationships renders the set the runtime consults before
// exposing bulk methods.
func generateTablesWithRelationships(groups []databaseGroup) string {
	var keys []string
	for _, group := range groups {
		for _, entity := range group.entities {
			if entity.HasRelationship() {
				keys = append(keys, quote(group.id+":"+entity.Name))
			}
		}
	}

	if len(keys) == 0 {
		return "const tablesWithRelationships = new Set<string>();"
	}

	return "const tablesWithRelationships = new Set<string>([" + strings.Join(keys, ", ") + "]);"
}

// generateDatabasesFile renders databases.ts.
func (g *TypeScript) generateDatabasesFile(config Config, extension, dependency string) (string, error) {
	entities := config.Entities()
	if len(entities) == 0 {
		return noEntitiesNotice, nil
	}

	groups := groupByDatabase(entities)
	serverSide := SupportsServerSideMethods(dependency, g.ServerSide)

	return typegen.RenderTemplate(typegen.TemplateDatabases, typegen.Values{
		"appwriteDep":               dependency,
		"supportsServerSide":        serverSide,
		"importExt":                 extension,
		"TABLE_ID_MAP":              generateTableIDMap(groups),
		"TABLES_WITH_RELATIONSHIPS": generateTablesWithRelationships(groups),
		"BULK_METHODS":              bulkMethods(serverSide),
		"BULK_CHECK":                bulkCheck(serverSide),
		"BULK_REMOVAL":              bulkRemoval(serverSide),
	})
}

func bulkMethods(serverSide bool) string {
	if !serverSide {
		return ""
	}

	return `
    createMany: (rows: object[], options?: { transactionId?: string }) =>
      tablesDB.createRows({
        databaseId,
        tableId,
        rows,
        transactionId: options?.transactionId,
      }),
    updateMany: (data: object, options?: { queries?: (q: QueryBuilder<T>) => string[]; transactionId?: string }) =>
      tablesDB.updateRows({
        databaseId,
        tableId,
        data,
        queries: options?.queries?.(createQueryBuilder<T>()),
        transactionId: options?.transactionId,
      }),
    deleteMany: (options?: { queries?: (q: QueryBuilder<T>) => string[]; transactionId?: string }) =>
      tablesDB.deleteRows({
        databaseId,
        tableId,
        queries: options?.queries?.(createQueryBuilder<T>()),
        transactionId: options?.transactionId,
      }),`
}

func bulkCheck(serverSide bool) string {
	if !serverSide {
		return ""
	}

	return "const hasBulkMethods = (dbId: string, tableId: string) => " +
		"!tablesWithRelationships.has(`${dbId}:${tableId}`);\n"
}

func bulkRemoval(serverSide bool) string {
	if !serverSide {
		return ""
	}

	return `
        // Remove bulk methods for tables with relationships
        if (!hasBulkMethods(databaseId, tableId)) {
          delete (api as Record<string, unknown>).createMany;
          delete (api as Record<string, unknown>).updateMany;
          delete (api as Record<string, unknown>).deleteMany;
        }`
}

// generateIndexFile renders index.ts.
func generateIndexFile(extension string) (string, error) {
	return typegen.RenderTemplate(typegen.TemplateIndex, typegen.Values{
		"sdkTitle":       SDKTitle,
		"executableName": ExecutableName,
		"importExt":      extension,
	})
}

// generateConstantsFile renders constants.ts.
func (g *TypeScript) generateConstantsFile(config Config, dependency string) (string, error) {
	return typegen.RenderTemplate(typegen.TemplateConstants, typegen.Values{
		"sdkTitle":       SDKTitle,
		"projectId":      config.ProjectID,
		"endpoint":       config.Endpoint,
		"requiresApiKey": SupportsServerSideMethods(dependency, g.ServerSide),
	})
}

// Generate implements Generator.
func (g *TypeScript) Generate(config Config, options Options) (Result, error) {
	if config.ProjectID == "" {
		return Result{}, ErrProjectIDRequired
	}

	if options.ServerSide != "" {
		g.ServerSide = options.ServerSide
	}

	dependency := options.ImportSource
	if dependency == "" {
		dependency = typegen.AppwriteDependency(".")
	}

	extension := ""
	if options.ImportExtension != nil {
		extension = *options.ImportExtension
	} else {
		extension = DetectImportExtension(".")
	}

	// Guards a hand-edited or merge-mangled config: two entries for the same
	// table would otherwise emit the same property twice.
	config.Tables = dedupeEntities(config.Tables)
	config.Collections = dedupeEntities(config.Collections)

	index, err := generateIndexFile(extension)
	if err != nil {
		return Result{}, err
	}
	constants, err := g.generateConstantsFile(config, dependency)
	if err != nil {
		return Result{}, err
	}

	if len(config.Tables) == 0 && len(config.Collections) == 0 {
		return Result{
			Databases: noEntitiesNotice,
			Types:     noEntitiesNotice,
			Index:     index,
			Constants: constants,
		}, nil
	}

	databases, err := g.generateDatabasesFile(config, extension, dependency)
	if err != nil {
		return Result{}, err
	}
	types, err := g.generateTypesFile(config, dependency)
	if err != nil {
		return Result{}, err
	}

	return Result{
		Databases: databases,
		Types:     types,
		Index:     index,
		Constants: constants,
	}, nil
}
