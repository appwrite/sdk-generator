module Appwrite
    class Functions < Service

        def list(search: nil, limit: nil, offset: nil, order_type: nil)
            path = '/functions'

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

        def create(name:, execute:, runtime:, vars: nil, events: nil, schedule: nil, timeout: nil)
            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            if execute.nil?
                raise Appwrite::Exception.new('Missing required parameter: "execute"')
            end

            if runtime.nil?
                raise Appwrite::Exception.new('Missing required parameter: "runtime"')
            end

            path = '/functions'

            params = {}

            if !name.nil?
                params[:name] = name
            end

            if !execute.nil?
                params[:execute] = execute
            end

            if !runtime.nil?
                params[:runtime] = runtime
            end

            if !vars.nil?
                params[:vars] = vars
            end

            if !events.nil?
                params[:events] = events
            end

            if !schedule.nil?
                params[:schedule] = schedule
            end

            if !timeout.nil?
                params[:timeout] = timeout
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get(function_id:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            path = '/functions/{functionId}'
                .gsub('{functionId}', function_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update(function_id:, name:, execute:, vars: nil, events: nil, schedule: nil, timeout: nil)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            if execute.nil?
                raise Appwrite::Exception.new('Missing required parameter: "execute"')
            end

            path = '/functions/{functionId}'
                .gsub('{functionId}', function_id)

            params = {}

            if !name.nil?
                params[:name] = name
            end

            if !execute.nil?
                params[:execute] = execute
            end

            if !vars.nil?
                params[:vars] = vars
            end

            if !events.nil?
                params[:events] = events
            end

            if !schedule.nil?
                params[:schedule] = schedule
            end

            if !timeout.nil?
                params[:timeout] = timeout
            end

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete(function_id:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            path = '/functions/{functionId}'
                .gsub('{functionId}', function_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def list_executions(function_id:, search: nil, limit: nil, offset: nil, order_type: nil)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            path = '/functions/{functionId}/executions'
                .gsub('{functionId}', function_id)

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

        def create_execution(function_id:, data: nil)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            path = '/functions/{functionId}/executions'
                .gsub('{functionId}', function_id)

            params = {}

            if !data.nil?
                params[:data] = data
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_execution(function_id:, execution_id:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            if execution_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "executionId"')
            end

            path = '/functions/{functionId}/executions/{executionId}'
                .gsub('{functionId}', function_id)
                .gsub('{executionId}', execution_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_tag(function_id:, tag:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            if tag.nil?
                raise Appwrite::Exception.new('Missing required parameter: "tag"')
            end

            path = '/functions/{functionId}/tag'
                .gsub('{functionId}', function_id)

            params = {}

            if !tag.nil?
                params[:tag] = tag
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def list_tags(function_id:, search: nil, limit: nil, offset: nil, order_type: nil)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            path = '/functions/{functionId}/tags'
                .gsub('{functionId}', function_id)

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

        def create_tag(function_id:, command:, code:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            if command.nil?
                raise Appwrite::Exception.new('Missing required parameter: "command"')
            end

            if code.nil?
                raise Appwrite::Exception.new('Missing required parameter: "code"')
            end

            path = '/functions/{functionId}/tags'
                .gsub('{functionId}', function_id)

            params = {}

            if !command.nil?
                params[:command] = command
            end

            if !code.nil?
                params[:code] = code
            end

            return @client.call('post', path, {
                'content-type' => 'multipart/form-data',
            }, params);
        end

        def get_tag(function_id:, tag_id:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            if tag_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "tagId"')
            end

            path = '/functions/{functionId}/tags/{tagId}'
                .gsub('{functionId}', function_id)
                .gsub('{tagId}', tag_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_tag(function_id:, tag_id:)
            if function_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "functionId"')
            end

            if tag_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "tagId"')
            end

            path = '/functions/{functionId}/tags/{tagId}'
                .gsub('{functionId}', function_id)
                .gsub('{tagId}', tag_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end