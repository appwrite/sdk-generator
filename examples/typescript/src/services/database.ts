import { Service } from "../service";

interface assoc {
    [key: string]: any;
}

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
     * @param Array<string> filters
     * @param number offset
     * @param number limit
     * @param string orderField
     * @param string orderType
     * @param string orderCast
     * @param string search
     * @param number first
     * @param number last
     * @throws Exception
     * @return Promise<string>
     */
    async listDocuments(collectionId: string, filters: Array<string> = [], offset: number = 0, limit: number = 50, orderField: string = '$id', orderType: string = 'ASC', orderCast: string = 'string', search: string = '', first: number = 0, last: number = 0): Promise<string> {
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
     * @param assoc data
     * @param Array<string> read
     * @param Array<string> write
     * @param string parentDocument
     * @param string parentProperty
     * @param string parentPropertyType
     * @throws Exception
     * @return Promise<string>
     */
    async createDocument(collectionId: string, data: assoc, read: Array<string>, write: Array<string>, parentDocument: string = '', parentProperty: string = '', parentPropertyType: string = 'assign'): Promise<string> {
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
     * @return Promise<string>
     */
    async getDocument(collectionId: string, documentId: string): Promise<string> {
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
     * @param assoc data
     * @param Array<string> read
     * @param Array<string> write
     * @throws Exception
     * @return Promise<string>
     */
    async updateDocument(collectionId: string, documentId: string, data: assoc, read: Array<string>, write: Array<string>): Promise<string> {
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
     * @return Promise<string>
     */
    async deleteDocument(collectionId: string, documentId: string): Promise<string> {
        let path = '/database/collections/{collectionId}/documents/{documentId}'.replace(new RegExp('{collectionId}', 'g'), collectionId).replace(new RegExp('{documentId}', 'g'), documentId);
        
        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }
}