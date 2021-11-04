package appwrite

import (
	"strings"
)

// Database service
type Database struct {
	client Client
}

func NewDatabase(clt Client) Database {  
    service := Database{
		client: clt,
	}

    return service
}

// ListCollections get a list of all the user collections. You can use the
// query params to filter your results. On admin mode, this endpoint will
// return a list of all of the project's collections. [Learn more about
// different API modes](/docs/admin).
func (srv *Database) ListCollections(Search string, Limit int, Offset int, OrderType string) (map[string]interface{}, error) {
	path := "/database/collections"

	params := map[string]interface{}{
		"search": Search,
		"limit": Limit,
		"offset": Offset,
		"orderType": OrderType,
	}

	return srv.client.Call("GET", path, nil, params)
}

// CreateCollection create a new Collection.
func (srv *Database) CreateCollection(Name string, Read []interface{}, Write []interface{}, Rules []interface{}) (map[string]interface{}, error) {
	path := "/database/collections"

	params := map[string]interface{}{
		"name": Name,
		"read": Read,
		"write": Write,
		"rules": Rules,
	}

	return srv.client.Call("POST", path, nil, params)
}

// GetCollection get a collection by its unique ID. This endpoint response
// returns a JSON object with the collection metadata.
func (srv *Database) GetCollection(CollectionId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId)
	path := r.Replace("/database/collections/{collectionId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// UpdateCollection update a collection by its unique ID.
func (srv *Database) UpdateCollection(CollectionId string, Name string, Read []interface{}, Write []interface{}, Rules []interface{}) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId)
	path := r.Replace("/database/collections/{collectionId}")

	params := map[string]interface{}{
		"name": Name,
		"read": Read,
		"write": Write,
		"rules": Rules,
	}

	return srv.client.Call("PUT", path, nil, params)
}

// DeleteCollection delete a collection by its unique ID. Only users with
// write permissions have access to delete this resource.
func (srv *Database) DeleteCollection(CollectionId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId)
	path := r.Replace("/database/collections/{collectionId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}

// ListDocuments get a list of all the user documents. You can use the query
// params to filter your results. On admin mode, this endpoint will return a
// list of all of the project's documents. [Learn more about different API
// modes](/docs/admin).
func (srv *Database) ListDocuments(CollectionId string, Filters []interface{}, Limit int, Offset int, OrderField string, OrderType string, OrderCast string, Search string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId)
	path := r.Replace("/database/collections/{collectionId}/documents")

	params := map[string]interface{}{
		"filters": Filters,
		"limit": Limit,
		"offset": Offset,
		"orderField": OrderField,
		"orderType": OrderType,
		"orderCast": OrderCast,
		"search": Search,
	}

	return srv.client.Call("GET", path, nil, params)
}

// CreateDocument create a new Document. Before using this route, you should
// create a new collection resource using either a [server
// integration](/docs/server/database#databaseCreateCollection) API or
// directly from your database console.
func (srv *Database) CreateDocument(CollectionId string, Data object, Read []interface{}, Write []interface{}, ParentDocument string, ParentProperty string, ParentPropertyType string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId)
	path := r.Replace("/database/collections/{collectionId}/documents")

	params := map[string]interface{}{
		"data": Data,
		"read": Read,
		"write": Write,
		"parentDocument": ParentDocument,
		"parentProperty": ParentProperty,
		"parentPropertyType": ParentPropertyType,
	}

	return srv.client.Call("POST", path, nil, params)
}

// GetDocument get a document by its unique ID. This endpoint response returns
// a JSON object with the document data.
func (srv *Database) GetDocument(CollectionId string, DocumentId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId, "{documentId}", DocumentId)
	path := r.Replace("/database/collections/{collectionId}/documents/{documentId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// UpdateDocument update a document by its unique ID. Using the patch method
// you can pass only specific fields that will get updated.
func (srv *Database) UpdateDocument(CollectionId string, DocumentId string, Data object, Read []interface{}, Write []interface{}) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId, "{documentId}", DocumentId)
	path := r.Replace("/database/collections/{collectionId}/documents/{documentId}")

	params := map[string]interface{}{
		"data": Data,
		"read": Read,
		"write": Write,
	}

	return srv.client.Call("PATCH", path, nil, params)
}

// DeleteDocument delete a document by its unique ID. This endpoint deletes
// only the parent documents, its attributes and relations to other documents.
// Child documents **will not** be deleted.
func (srv *Database) DeleteDocument(CollectionId string, DocumentId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId, "{documentId}", DocumentId)
	path := r.Replace("/database/collections/{collectionId}/documents/{documentId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}
