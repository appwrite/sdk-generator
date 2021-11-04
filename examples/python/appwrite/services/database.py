from ..service import Service
from ..exception import AppwriteException

class Database(Service):

    def __init__(self, client):
        super(Database, self).__init__(client)

    def list_collections(self, search = None, limit = None, offset = None, order_type = None):
        """List Collections"""

        params = {}
        path = '/database/collections'

        if search is not None: 
            params['search'] = search

        if limit is not None: 
            params['limit'] = limit

        if offset is not None: 
            params['offset'] = offset

        if order_type is not None: 
            params['orderType'] = order_type

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def create_collection(self, name, read, write, rules):
        """Create Collection"""

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        if read is None: 
            raise AppwriteException('Missing required parameter: "read"')

        if write is None: 
            raise AppwriteException('Missing required parameter: "write"')

        if rules is None: 
            raise AppwriteException('Missing required parameter: "rules"')

        params = {}
        path = '/database/collections'

        if name is not None: 
            params['name'] = name

        if read is not None: 
            params['read'] = read

        if write is not None: 
            params['write'] = write

        if rules is not None: 
            params['rules'] = rules

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def get_collection(self, collection_id):
        """Get Collection"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        params = {}
        path = '/database/collections/{collectionId}'
        path = path.replace('{collectionId}', collection_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_collection(self, collection_id, name, read = None, write = None, rules = None):
        """Update Collection"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        params = {}
        path = '/database/collections/{collectionId}'
        path = path.replace('{collectionId}', collection_id)                

        if name is not None: 
            params['name'] = name

        if read is not None: 
            params['read'] = read

        if write is not None: 
            params['write'] = write

        if rules is not None: 
            params['rules'] = rules

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)

    def delete_collection(self, collection_id):
        """Delete Collection"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        params = {}
        path = '/database/collections/{collectionId}'
        path = path.replace('{collectionId}', collection_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def list_documents(self, collection_id, filters = None, limit = None, offset = None, order_field = None, order_type = None, order_cast = None, search = None):
        """List Documents"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        params = {}
        path = '/database/collections/{collectionId}/documents'
        path = path.replace('{collectionId}', collection_id)                

        if filters is not None: 
            params['filters'] = filters

        if limit is not None: 
            params['limit'] = limit

        if offset is not None: 
            params['offset'] = offset

        if order_field is not None: 
            params['orderField'] = order_field

        if order_type is not None: 
            params['orderType'] = order_type

        if order_cast is not None: 
            params['orderCast'] = order_cast

        if search is not None: 
            params['search'] = search

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def create_document(self, collection_id, data, read = None, write = None, parent_document = None, parent_property = None, parent_property_type = None):
        """Create Document"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        if data is None: 
            raise AppwriteException('Missing required parameter: "data"')

        params = {}
        path = '/database/collections/{collectionId}/documents'
        path = path.replace('{collectionId}', collection_id)                

        if data is not None: 
            params['data'] = data

        if read is not None: 
            params['read'] = read

        if write is not None: 
            params['write'] = write

        if parent_document is not None: 
            params['parentDocument'] = parent_document

        if parent_property is not None: 
            params['parentProperty'] = parent_property

        if parent_property_type is not None: 
            params['parentPropertyType'] = parent_property_type

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def get_document(self, collection_id, document_id):
        """Get Document"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        if document_id is None: 
            raise AppwriteException('Missing required parameter: "document_id"')

        params = {}
        path = '/database/collections/{collectionId}/documents/{documentId}'
        path = path.replace('{collectionId}', collection_id)                
        path = path.replace('{documentId}', document_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_document(self, collection_id, document_id, data, read = None, write = None):
        """Update Document"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        if document_id is None: 
            raise AppwriteException('Missing required parameter: "document_id"')

        if data is None: 
            raise AppwriteException('Missing required parameter: "data"')

        params = {}
        path = '/database/collections/{collectionId}/documents/{documentId}'
        path = path.replace('{collectionId}', collection_id)                
        path = path.replace('{documentId}', document_id)                

        if data is not None: 
            params['data'] = data

        if read is not None: 
            params['read'] = read

        if write is not None: 
            params['write'] = write

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def delete_document(self, collection_id, document_id):
        """Delete Document"""

        if collection_id is None: 
            raise AppwriteException('Missing required parameter: "collection_id"')

        if document_id is None: 
            raise AppwriteException('Missing required parameter: "document_id"')

        params = {}
        path = '/database/collections/{collectionId}/documents/{documentId}'
        path = path.replace('{collectionId}', collection_id)                
        path = path.replace('{documentId}', document_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)
