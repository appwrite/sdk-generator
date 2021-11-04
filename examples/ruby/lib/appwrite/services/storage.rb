module Appwrite
    class Storage < Service

        def list_files(search: nil, limit: nil, offset: nil, order_type: nil)
            path = '/storage/files'

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

        def create_file(file:, read: nil, write: nil)
            if file.nil?
                raise Appwrite::Exception.new('Missing required parameter: "file"')
            end

            path = '/storage/files'

            params = {}

            if !file.nil?
                params[:file] = file
            end

            if !read.nil?
                params[:read] = read
            end

            if !write.nil?
                params[:write] = write
            end

            return @client.call('post', path, {
                'content-type' => 'multipart/form-data',
            }, params);
        end

        def get_file(file_id:)
            if file_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "fileId"')
            end

            path = '/storage/files/{fileId}'
                .gsub('{fileId}', file_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_file(file_id:, read:, write:)
            if file_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "fileId"')
            end

            if read.nil?
                raise Appwrite::Exception.new('Missing required parameter: "read"')
            end

            if write.nil?
                raise Appwrite::Exception.new('Missing required parameter: "write"')
            end

            path = '/storage/files/{fileId}'
                .gsub('{fileId}', file_id)

            params = {}

            if !read.nil?
                params[:read] = read
            end

            if !write.nil?
                params[:write] = write
            end

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_file(file_id:)
            if file_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "fileId"')
            end

            path = '/storage/files/{fileId}'
                .gsub('{fileId}', file_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_file_download(file_id:)
            if file_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "fileId"')
            end

            path = '/storage/files/{fileId}/download'
                .gsub('{fileId}', file_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_file_preview(file_id:, width: nil, height: nil, gravity: nil, quality: nil, border_width: nil, border_color: nil, border_radius: nil, opacity: nil, rotation: nil, background: nil, output: nil)
            if file_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "fileId"')
            end

            path = '/storage/files/{fileId}/preview'
                .gsub('{fileId}', file_id)

            params = {}

            if !width.nil?
                params[:width] = width
            end

            if !height.nil?
                params[:height] = height
            end

            if !gravity.nil?
                params[:gravity] = gravity
            end

            if !quality.nil?
                params[:quality] = quality
            end

            if !border_width.nil?
                params[:borderWidth] = border_width
            end

            if !border_color.nil?
                params[:borderColor] = border_color
            end

            if !border_radius.nil?
                params[:borderRadius] = border_radius
            end

            if !opacity.nil?
                params[:opacity] = opacity
            end

            if !rotation.nil?
                params[:rotation] = rotation
            end

            if !background.nil?
                params[:background] = background
            end

            if !output.nil?
                params[:output] = output
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_file_view(file_id:)
            if file_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "fileId"')
            end

            path = '/storage/files/{fileId}/view'
                .gsub('{fileId}', file_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end