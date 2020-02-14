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

// ListDocuments get a list of all the user documents. You can use the query
// params to filter your results. On admin mode, this endpoint will return a
// list of all of the project documents. [Learn more about different API
// modes](/docs/admin).
func (srv *Database) ListDocuments(CollectionId string, Filters []interface{}, Offset int, Limit int, OrderField string, OrderType string, OrderCast string, Search string, First int, Last int) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId)
	path := r.Replace("/database/collections/{collectionId}/documents")

	params := map[string]interface{}{
		"filters": Filters,
		"offset": Offset,
		"limit": Limit,
		"order-field": OrderField,
		"order-type": OrderType,
		"order-cast": OrderCast,
		"search": Search,
		"first": First,
		"last": Last,
	}

	return srv.client.Call("GET", path, nil, params)
}

// CreateDocument create a new Document.
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

// GetDocument get document by its unique ID. This endpoint response returns a
// JSON object with the document data.
func (srv *Database) GetDocument(CollectionId string, DocumentId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId, "{documentId}", DocumentId)
	path := r.Replace("/database/collections/{collectionId}/documents/{documentId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// UpdateDocument
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

// DeleteDocument delete document by its unique ID. This endpoint deletes only
// the parent documents, his attributes and relations to other documents.
// Child documents **will not** be deleted.
func (srv *Database) DeleteDocument(CollectionId string, DocumentId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{collectionId}", CollectionId, "{documentId}", DocumentId)
	path := r.Replace("/database/collections/{collectionId}/documents/{documentId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}
