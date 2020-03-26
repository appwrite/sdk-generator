import { Service } from "../service";

export class Database extends Service {

    /**
     * List Documents
     *
     * Get a list of all the user documents. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project documents. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param string collectionId
     * @param array filters
     * @param number offset
     * @param number limit
     * @param string orderField
     * @param string orderType
     * @param string orderCast
     * @param string search
     * @param number first
     * @param number last
     * @throws Exception
     * @return {}
     */
    async listDocuments(collectionId, filters = [], offset = 0, limit = 50, orderField = '$id', orderType = 'ASC', orderCast = 'string', search = '', first = 0, last = 0) {
        let path = '/database/collections/{collectionId}/documents'.replace(new RegExp('{collectionId}', 'g'), collectionId);
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
                'filters': filters,
                'offset': offset,
                'limit': limit,
                'order-field': orderField,
                'order-type': orderType,
                'order-cast': orderCast,
                'search': search,
                'first': first,
                'last': last
            });
    }

    /**
     * Create Document
     *
     * Create a new Document.
     *
     * @param string collectionId
     * @param object data
     * @param array read
     * @param array write
     * @param string parentDocument
     * @param string parentProperty
     * @param string parentPropertyType
     * @throws Exception
     * @return {}
     */
    async createDocument(collectionId, data, read, write, parentDocument = '', parentProperty = '', parentPropertyType = 'assign') {
        let path = '/database/collections/{collectionId}/documents'.replace(new RegExp('{collectionId}', 'g'), collectionId);
        
        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               },
               {
                'data': data,
                'read': read,
                'write': write,
                'parentDocument': parentDocument,
                'parentProperty': parentProperty,
                'parentPropertyType': parentPropertyType
            });
    }

    /**
     * Get Document
     *
     * Get document by its unique ID. This endpoint response returns a JSON object
     * with the document data.
     *
     * @param string collectionId
     * @param string documentId
     * @throws Exception
     * @return {}
     */
    async getDocument(collectionId, documentId) {
        let path = '/database/collections/{collectionId}/documents/{documentId}'.replace(new RegExp('{collectionId}', 'g'), collectionId).replace(new RegExp('{documentId}', 'g'), documentId);
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Update Document
     *
     * @param string collectionId
     * @param string documentId
     * @param object data
     * @param array read
     * @param array write
     * @throws Exception
     * @return {}
     */
    async updateDocument(collectionId, documentId, data, read, write) {
        let path = '/database/collections/{collectionId}/documents/{documentId}'.replace(new RegExp('{collectionId}', 'g'), collectionId).replace(new RegExp('{documentId}', 'g'), documentId);
        
        return await this.client.call('patch', path, {
                    'content-type': 'application/json',
               },
               {
                'data': data,
                'read': read,
                'write': write
            });
    }

    /**
     * Delete Document
     *
     * Delete document by its unique ID. This endpoint deletes only the parent
     * documents, his attributes and relations to other documents. Child documents
     * **will not** be deleted.
     *
     * @param string collectionId
     * @param string documentId
     * @throws Exception
     * @return {}
     */
    async deleteDocument(collectionId, documentId) {
        let path = '/database/collections/{collectionId}/documents/{documentId}'.replace(new RegExp('{collectionId}', 'g'), collectionId).replace(new RegExp('{documentId}', 'g'), documentId);
        
        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }
}