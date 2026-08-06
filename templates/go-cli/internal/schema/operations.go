package schema

import (
	"fmt"
	"net/url"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// The attribute, column and index endpoints.
//
// All of them go through the legacy /databases route, including the columns and
// indexes of a TABLE. That reads like a mistake and is not -- the TypeScript
// calls the databases service unconditionally, and tables and collections are
// the same objects behind two route prefixes. What the TypeScript sends is the
// contract, so re-record a push before changing this.
//
// A field ABSENT from the config is absent from the request body. The
// TypeScript passes `xdefault: attribute.default` and the JavaScript SDK drops
// undefined values, so a config that does not mention `encrypt` does not send
// it. A present null IS sent: `"default": null` clears the default.

// containerPath is the collection route a container's schema hangs off.
func containerPath(container Container) string {
	return "/databases/" + url.PathEscape(container.DatabaseID) +
		"/collections/" + url.PathEscape(container.ID)
}

// CreateAttribute creates one attribute or column.
func (r *Reconciler) CreateAttribute(container Container, attribute *jsonx.Object) error {
	route, parameters, err := createRoute(attribute)
	if err != nil {
		return err
	}

	body := jsonx.NewObject()
	for _, parameter := range parameters {
		parameter.apply(attribute, body)
	}

	return r.API.Call("POST", containerPath(container)+"/attributes/"+route, body, nil)
}

// UpdateAttribute changes one attribute in place, optionally renaming it.
//
// newKey is empty for a plain update; a non-empty value renames the attribute,
// which is the only way to preserve its data across a key change.
func (r *Reconciler) UpdateAttribute(
	container Container,
	attribute *jsonx.Object,
	newKey string,
) error {
	// Indexes have no update endpoint. Reaching here with one means the plan
	// classified an index difference as updatable, which IndexFieldRules makes
	// impossible -- so this guards a future edit, not a live path.
	if attribute.Has("attributes") || attribute.Has("columns") {
		return fmt.Errorf(
			"indexes cannot be updated in place (key: %s), recreate the index instead",
			attribute.GetString("key"))
	}

	route, parameters, err := updateRoute(attribute)
	if err != nil {
		return err
	}

	body := jsonx.NewObject()
	for _, parameter := range parameters {
		parameter.apply(attribute, body)
	}
	if newKey != "" {
		body.Set("newKey", newKey)
	}

	path := containerPath(container) + "/attributes/" + route + "/" +
		url.PathEscape(attribute.GetString("key"))

	return r.API.Call("PATCH", path, body, nil)
}

// DeleteAttribute removes one attribute or index.
func (r *Reconciler) DeleteAttribute(
	container Container,
	attribute *jsonx.Object,
	isIndex bool,
) error {
	noun := "attribute"
	collection := "/attributes/"
	if isIndex {
		noun = "index"
		collection = "/indexes/"
	}

	output.Log(r.Out, "Deleting %s %s of %s ( %s )",
		noun, attribute.GetString("key"), container.Name, container.ID)

	path := containerPath(container) + collection + url.PathEscape(attribute.GetString("key"))

	return r.API.Call("DELETE", path, nil, nil)
}

// CreateIndex creates one index.
//
// The body names the members as `attributes` even for a table, whose config
// calls them `columns` -- confirmed against the recorded trace.
func (r *Reconciler) CreateIndex(container Container, index *jsonx.Object) error {
	body := jsonx.NewObject()
	body.Set("key", index.GetString("key"))
	body.Set("type", index.GetString("type"))

	members, present := index.Get("columns")
	if !present {
		members, _ = index.Get("attributes")
	}
	body.Set("attributes", members)

	if orders, present := index.Get("orders"); present {
		body.Set("orders", orders)
	}

	return r.API.Call("POST", containerPath(container)+"/indexes", body, nil)
}

// parameter maps one config field onto one request field.
type parameter struct {
	// name is the request field.
	name string
	// sources are the config fields tried in order, first present wins. Only
	// a relationship needs more than one, where relatedTable and
	// relatedCollection are the same thing under two names.
	sources []string
}

// apply copies the field onto the body when the config carries it.
func (p parameter) apply(source, body *jsonx.Object) {
	for _, name := range p.sources {
		if value, present := source.Get(name); present {
			// Nullish coalescing in the TypeScript, so a present null falls
			// through to the next source rather than being sent.
			if value == nil && len(p.sources) > 1 {
				continue
			}
			body.Set(p.name, value)

			return
		}
	}
}

// named builds a parameter that keeps its config name.
func named(name string) parameter { return parameter{name: name, sources: []string{name}} }

// renamed names a parameter the request calls something else.
func renamed(name string, sources ...string) parameter {
	return parameter{name: name, sources: sources}
}

// The parameter sets, in the order the JavaScript SDK builds them. Order only
// affects the body's key order, which no assertion depends on, but keeping it
// makes a recorded trace diff cleanly against the TypeScript's.
var (
	createTyped     = []parameter{named("key"), named("required"), named("default"), named("array")}
	createEnum      = []parameter{named("key"), named("elements"), named("required"), named("default"), named("array")}
	createSized     = []parameter{named("key"), named("size"), named("required"), named("default"), named("array"), named("encrypt")}
	createEncrypted = []parameter{named("key"), named("required"), named("default"), named("array"), named("encrypt")}
	createNumeric   = []parameter{named("key"), named("required"), named("min"), named("max"), named("default"), named("array")}
	createSpatial   = []parameter{named("key"), named("required"), named("default")}
	createRelation  = []parameter{
		renamed("relatedCollectionId", "relatedTable", "relatedCollection"),
		renamed("type", "relationType"),
		named("twoWay"), named("key"), named("twoWayKey"), named("onDelete"),
	}

	updateTyped    = []parameter{named("required"), named("default")}
	updateEnum     = []parameter{named("elements"), named("required"), named("default")}
	updateSized    = []parameter{named("required"), named("default"), named("size")}
	updateNumeric  = []parameter{named("required"), named("min"), named("max"), named("default")}
	updateRelation = []parameter{named("onDelete")}
)

// createRoute picks the endpoint and body shape for a new attribute.
func createRoute(attribute *jsonx.Object) (string, []parameter, error) {
	switch attribute.GetString("type") {
	case TypeString:
		switch attribute.GetString("format") {
		case FormatEmail:
			return FormatEmail, createTyped, nil
		case FormatURL:
			return FormatURL, createTyped, nil
		case FormatIP:
			return FormatIP, createTyped, nil
		case FormatEnum:
			return FormatEnum, createEnum, nil
		default:
			return "string", createSized, nil
		}
	case TypeVarchar:
		return "varchar", createSized, nil
	case TypeText:
		return "text", createEncrypted, nil
	case TypeMediumText:
		return "mediumtext", createEncrypted, nil
	case TypeLongText:
		return "longtext", createEncrypted, nil
	case TypeInteger:
		return "integer", createNumeric, nil
	case TypeBigInt:
		return "bigint", createNumeric, nil
	case TypeDouble:
		// "double" in the config, /float on the wire.
		return "float", createNumeric, nil
	case TypeBoolean:
		return "boolean", createTyped, nil
	case TypeDatetime:
		return "datetime", createTyped, nil
	case TypeRelationship:
		return "relationship", createRelation, nil
	case TypePoint:
		return "point", createSpatial, nil
	case TypeLineString:
		// "linestring" in the config, /line on the wire.
		return "line", createSpatial, nil
	case TypePolygon:
		return "polygon", createSpatial, nil
	}

	return "", nil, fmt.Errorf("unsupported attribute type: %s", attribute.GetString("type"))
}

// updateRoute picks the endpoint and body shape for an in-place change.
func updateRoute(attribute *jsonx.Object) (string, []parameter, error) {
	switch attribute.GetString("type") {
	case TypeString:
		switch attribute.GetString("format") {
		case FormatEmail:
			return FormatEmail, updateTyped, nil
		case FormatURL:
			return FormatURL, updateTyped, nil
		case FormatIP:
			return FormatIP, updateTyped, nil
		case FormatEnum:
			return FormatEnum, updateEnum, nil
		default:
			return "string", updateSized, nil
		}
	case TypeVarchar:
		return "varchar", updateSized, nil
	case TypeText:
		return "text", updateTyped, nil
	case TypeMediumText:
		return "mediumtext", updateTyped, nil
	case TypeLongText:
		return "longtext", updateTyped, nil
	case TypeInteger:
		return "integer", updateNumeric, nil
	case TypeBigInt:
		return "bigint", updateNumeric, nil
	case TypeDouble:
		return "float", updateNumeric, nil
	case TypeBoolean:
		return "boolean", updateTyped, nil
	case TypeDatetime:
		return "datetime", updateTyped, nil
	case TypeRelationship:
		return "relationship", updateRelation, nil
	case TypePoint:
		return "point", updateTyped, nil
	case TypeLineString:
		return "line", updateTyped, nil
	case TypePolygon:
		return "polygon", updateTyped, nil
	}

	return "", nil, fmt.Errorf("unsupported attribute type: %s", attribute.GetString("type"))
}
