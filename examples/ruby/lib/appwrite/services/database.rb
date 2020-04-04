module Appwrite
    class Database < Service

        def list_documents(collection_id:, filters: [], offset: 0, limit: 50, order_field: '$id', order_type: 'ASC', order_cast: 'string', search: '', first: 0, last: 0)
            path = '/database/collections/{collectionId}/documents'
                .gsub('{collection_id}', collection_id)

            params = {
                'filters': filters, 
                'offset': offset, 
                'limit': limit, 
                'order-field': order_field, 
                'order-type': order_type, 
                'order-cast': order_cast, 
                'search': search, 
                'first': first, 
                'last': last
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_document(collection_id:, data:, read:, write:, parent_document: '', parent_property: '', parent_property_type: 'assign')
            path = '/database/collections/{collectionId}/documents'
                .gsub('{collection_id}', collection_id)

            params = {
                'data': data, 
                'read': read, 
                'write': write, 
                'parentDocument': parent_document, 
                'parentProperty': parent_property, 
                'parentPropertyType': parent_property_type
            }

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_document(collection_id:, document_id:)
            path = '/database/collections/{collectionId}/documents/{documentId}'
                .gsub('{collection_id}', collection_id)
                .gsub('{document_id}', document_id)

            params = {
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_document(collection_id:, document_id:, data:, read:, write:)
            path = '/database/collections/{collectionId}/documents/{documentId}'
                .gsub('{collection_id}', collection_id)
                .gsub('{document_id}', document_id)

            params = {
                'data': data, 
                'read': read, 
                'write': write
            }

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_document(collection_id:, document_id:)
            path = '/database/collections/{collectionId}/documents/{documentId}'
                .gsub('{collection_id}', collection_id)
                .gsub('{document_id}', document_id)

            params = {
            }

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end