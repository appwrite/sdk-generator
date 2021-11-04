module Appwrite
    class Database < Service

        def list_collections(search: nil, limit: nil, offset: nil, order_type: nil)
            path = '/database/collections'

            params = {}

            if !search.nil?
                params[:search] = search
            end

            if !limit.nil?
                params[:limit] = limit
            end

            if !offset.nil?
                params[:offset] = offset
            end

            if !order_type.nil?
                params[:orderType] = order_type
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_collection(name:, read:, write:, rules:)
            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            if read.nil?
                raise Appwrite::Exception.new('Missing required parameter: "read"')
            end

            if write.nil?
                raise Appwrite::Exception.new('Missing required parameter: "write"')
            end

            if rules.nil?
                raise Appwrite::Exception.new('Missing required parameter: "rules"')
            end

            path = '/database/collections'

            params = {}

            if !name.nil?
                params[:name] = name
            end

            if !read.nil?
                params[:read] = read
            end

            if !write.nil?
                params[:write] = write
            end

            if !rules.nil?
                params[:rules] = rules
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_collection(collection_id:)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            path = '/database/collections/{collectionId}'
                .gsub('{collectionId}', collection_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_collection(collection_id:, name:, read: nil, write: nil, rules: nil)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            path = '/database/collections/{collectionId}'
                .gsub('{collectionId}', collection_id)

            params = {}

            if !name.nil?
                params[:name] = name
            end

            if !read.nil?
                params[:read] = read
            end

            if !write.nil?
                params[:write] = write
            end

            if !rules.nil?
                params[:rules] = rules
            end

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_collection(collection_id:)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            path = '/database/collections/{collectionId}'
                .gsub('{collectionId}', collection_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def list_documents(collection_id:, filters: nil, limit: nil, offset: nil, order_field: nil, order_type: nil, order_cast: nil, search: nil)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            path = '/database/collections/{collectionId}/documents'
                .gsub('{collectionId}', collection_id)

            params = {}

            if !filters.nil?
                params[:filters] = filters
            end

            if !limit.nil?
                params[:limit] = limit
            end

            if !offset.nil?
                params[:offset] = offset
            end

            if !order_field.nil?
                params[:orderField] = order_field
            end

            if !order_type.nil?
                params[:orderType] = order_type
            end

            if !order_cast.nil?
                params[:orderCast] = order_cast
            end

            if !search.nil?
                params[:search] = search
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_document(collection_id:, data:, read: nil, write: nil, parent_document: nil, parent_property: nil, parent_property_type: nil)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            if data.nil?
                raise Appwrite::Exception.new('Missing required parameter: "data"')
            end

            path = '/database/collections/{collectionId}/documents'
                .gsub('{collectionId}', collection_id)

            params = {}

            if !data.nil?
                params[:data] = data
            end

            if !read.nil?
                params[:read] = read
            end

            if !write.nil?
                params[:write] = write
            end

            if !parent_document.nil?
                params[:parentDocument] = parent_document
            end

            if !parent_property.nil?
                params[:parentProperty] = parent_property
            end

            if !parent_property_type.nil?
                params[:parentPropertyType] = parent_property_type
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_document(collection_id:, document_id:)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            if document_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "documentId"')
            end

            path = '/database/collections/{collectionId}/documents/{documentId}'
                .gsub('{collectionId}', collection_id)
                .gsub('{documentId}', document_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_document(collection_id:, document_id:, data:, read: nil, write: nil)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            if document_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "documentId"')
            end

            if data.nil?
                raise Appwrite::Exception.new('Missing required parameter: "data"')
            end

            path = '/database/collections/{collectionId}/documents/{documentId}'
                .gsub('{collectionId}', collection_id)
                .gsub('{documentId}', document_id)

            params = {}

            if !data.nil?
                params[:data] = data
            end

            if !read.nil?
                params[:read] = read
            end

            if !write.nil?
                params[:write] = write
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_document(collection_id:, document_id:)
            if collection_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "collectionId"')
            end

            if document_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "documentId"')
            end

            path = '/database/collections/{collectionId}/documents/{documentId}'
                .gsub('{collectionId}', collection_id)
                .gsub('{documentId}', document_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end